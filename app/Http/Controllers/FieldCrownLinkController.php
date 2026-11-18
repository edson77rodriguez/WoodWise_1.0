<?php

namespace App\Http\Controllers;

use App\Models\FieldMission;
use App\Models\FieldObservation;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Validation\Rule;
use Illuminate\Validation\ValidationException;

/**
 * SIGMAD Campo V0.4.0 — correspondencia humana, versionada y separada de los
 * datos originales de campo y de las máscaras/modelos de IA.
 * NO convierte "sin vincular" en FN ni "sin correspondencia" en FP.
 */
class FieldCrownLinkController extends Controller
{
    private const DECISIONS = ['confirmada', 'dudosa', 'sin_correspondencia'];
    private const EVIDENCE = ['revision_mapa', 'campo_independiente', 'fotografia', 'croquis', 'otra'];

    private function selectedMission(Request $request): ?FieldMission
    {
        $v = $request->validate(['mission' => ['nullable', 'uuid']]);
        if (empty($v['mission'])) return null;
        return FieldMission::query()->where('uuid', $v['mission'])
            ->where('status', 'active')->firstOrFail();
    }

    /** Copas de la instantánea de misión; no leer predicciones de otro análisis. */
    private function crowns(FieldMission $mission): array
    {
        $features = $mission->geojson['features'] ?? [];
        if (!is_array($features) || count($features) > 200) {
            abort(422, 'La instantánea de copas de la misión no es compatible.');
        }
        $crowns = [];
        foreach ($features as $feature) {
            if (!is_array($feature)) continue;
            $id = $feature['properties']['operational_id'] ?? null;
            if (!is_string($id) || $id === '' || strlen($id) > 100) continue;
            if (isset($crowns[$id])) abort(422, 'La misión contiene un ID de copa duplicado.');
            $crowns[$id] = [
                'id' => $id,
                'model' => (string) ($feature['properties']['primary_model'] ?? 'No informado'),
                'requires_review' => (bool) ($feature['properties']['requires_review'] ?? false),
            ];
        }
        ksort($crowns, SORT_NATURAL);
        return $crowns;
    }

    /** Registros de esa parcela; C es repetición y no un segundo individuo. */
    private function eligibleRecords(FieldMission $mission)
    {
        return FieldObservation::query()
            ->where('id_parcela', $mission->id_parcela)
            ->whereIn('grupo', ['A', 'B', 'H'])
            ->orderByDesc('id')->limit(1200)
            ->get(['id', 'id_parcela', 'grupo', 'tree_id', 'candidate_id', 'payload', 'review_status'])
            ->reject(fn ($r) => ($r->payload['localizado'] ?? null) === 'no')
            ->values();
    }

    public function index(Request $request)
    {
        $missions = FieldMission::query()->where('status', 'active')
            ->orderByDesc('id')->limit(100)
            ->get(['id', 'uuid', 'title', 'id_parcela', 'selected_count']);
        $mission = $this->selectedMission($request);
        $crowns = $mission ? $this->crowns($mission) : [];
        $records = $mission ? $this->eligibleRecords($mission) : collect();
        $links = $mission ? DB::table('field_crown_links')
            ->where('field_mission_id', $mission->id)->get()->keyBy('operational_id') : collect();
        $history = $mission ? DB::table('field_crown_link_events as ev')
            ->join('field_crown_links as l', 'l.id', '=', 'ev.field_crown_link_id')
            ->join('users as u', 'u.id', '=', 'ev.reviewer_id')
            ->where('l.field_mission_id', $mission->id)
            ->orderByDesc('ev.id')->limit(25)
            ->get(['ev.*', 'l.operational_id', 'u.name as reviewer_name']) : collect();
        $stats = [
            'total' => count($crowns),
            'confirmada' => $links->where('decision', 'confirmada')->count(),
            'dudosa' => $links->where('decision', 'dudosa')->count(),
            'sin_correspondencia' => $links->where('decision', 'sin_correspondencia')->count(),
            'sin_evaluar' => max(0, count($crowns) - $links->count()),
        ];
        return view('campo.vinculaciones.index', [
            'missions' => $missions,
            'mission' => $mission,
            'crowns' => $crowns,
            'records' => $records,
            'links' => $links,
            'history' => $history,
            'stats' => $stats,
            'selectedCrown' => $request->query('crown', ''),
        ]);
    }

    public function points(Request $request)
    {
        $mission = $this->selectedMission($request);
        abort_unless($mission !== null, 422, 'Selecciona una misión.');
        $records = $this->eligibleRecords($mission);
        $points = [];
        foreach ($records as $record) {
            $p = $record->payload ?? [];
            if (!is_numeric($p['latitud'] ?? null) || !is_numeric($p['longitud'] ?? null)) continue;
            $lat = (float) $p['latitud'];
            $lon = (float) $p['longitud'];
            if ($lat < -90 || $lat > 90 || $lon < -180 || $lon > 180) continue;
            $points[] = [
                'id' => $record->id,
                'label' => $record->tree_id ?: ($record->candidate_id ?: 'Registro #'.$record->id),
                'grupo' => $record->grupo,
                'lat' => $lat,
                'lon' => $lon,
                'accuracy_m' => is_numeric($p['gps_precision_m'] ?? null) ? (float) $p['gps_precision_m'] : null,
            ];
        }
        return response()->json(['points' => $points, 'limited_to' => 1200])
            ->header('Cache-Control', 'private, no-store');
    }

    public function store(Request $request)
    {
        $v = $request->validate([
            'mission_uuid' => ['required', 'uuid'],
            'operational_id' => ['required', 'string', 'max:100'],
            'field_observation_id' => ['nullable', 'integer', 'exists:field_observations,id'],
            'decision' => ['required', Rule::in(self::DECISIONS)],
            'evidence_type' => ['required', Rule::in(self::EVIDENCE)],
            'justification' => ['required', 'string', 'min:15', 'max:2000'],
            'verified_context' => ['accepted'],
        ]);
        $mission = FieldMission::query()->where('uuid', $v['mission_uuid'])
            ->where('status', 'active')->firstOrFail();
        $crowns = $this->crowns($mission);
        if (!array_key_exists($v['operational_id'], $crowns)) {
            throw ValidationException::withMessages(['operational_id' => 'La copa no pertenece a la instantánea de esta misión.']);
        }

        $obsId = isset($v['field_observation_id']) ? (int) $v['field_observation_id'] : null;
        if ($v['decision'] === 'sin_correspondencia' && $obsId !== null) {
            throw ValidationException::withMessages(['field_observation_id' => 'Sin correspondencia documentada no puede conservar un árbol vinculado.']);
        }
        if ($v['decision'] !== 'sin_correspondencia' && $obsId === null) {
            throw ValidationException::withMessages(['field_observation_id' => 'Selecciona una ficha para confirmar o marcar como dudosa.']);
        }
        if ($obsId !== null) {
            $obs = FieldObservation::query()->whereKey($obsId)
                ->where('id_parcela', $mission->id_parcela)
                ->whereIn('grupo', ['A', 'B', 'H'])->first();
            if (!$obs || ($obs->payload['localizado'] ?? null) === 'no') {
                throw ValidationException::withMessages(['field_observation_id' => 'La ficha no corresponde a un árbol elegible de la parcela. Las remediciones C no se vinculan como individuos nuevos.']);
            }
        }

        $note = trim($v['justification']);
        if (mb_strlen($note) < 15) {
            throw ValidationException::withMessages(['justification' => 'Explica la evidencia en al menos 15 caracteres.']);
        }

        DB::transaction(function () use ($request, $mission, $v, $obsId, $note) {
            // Serializa revisiones de una misma misión y evita decisiones simultáneas.
            DB::table('field_missions')->where('id', $mission->id)->lockForUpdate()->first();
            $current = DB::table('field_crown_links')->where('field_mission_id', $mission->id)
                ->where('operational_id', $v['operational_id'])->lockForUpdate()->first();
            if ($obsId !== null) {
                $other = DB::table('field_crown_links')
                    ->where('field_mission_id', $mission->id)
                    ->where('field_observation_id', $obsId)
                    ->when($current, fn ($q) => $q->where('id', '<>', $current->id))
                    ->first();
                if ($other) {
                    throw ValidationException::withMessages(['field_observation_id' => 'La ficha ya está vinculada a la copa '.$other->operational_id.' de esta misión. Revisa esa decisión primero.']);
                }
            }
            if ($current && $current->field_observation_id == $obsId &&
                $current->decision === $v['decision'] &&
                $current->evidence_type === $v['evidence_type'] &&
                $current->justification === $note) return;

            $now = now();
            $data = [
                'field_observation_id' => $obsId,
                'decision' => $v['decision'],
                'evidence_type' => $v['evidence_type'],
                'justification' => $note,
                'reviewed_by' => $request->user()->id,
                'reviewed_at' => $now,
                'updated_at' => $now,
            ];
            if ($current) {
                DB::table('field_crown_links')->where('id', $current->id)->update($data);
                $linkId = $current->id;
            } else {
                $linkId = DB::table('field_crown_links')->insertGetId($data + [
                    'field_mission_id' => $mission->id,
                    'operational_id' => $v['operational_id'],
                    'created_at' => $now,
                ]);
            }
            DB::table('field_crown_link_events')->insert([
                'field_crown_link_id' => $linkId,
                'reviewer_id' => $request->user()->id,
                'previous_observation_id' => $current?->field_observation_id,
                'new_observation_id' => $obsId,
                'previous_decision' => $current?->decision,
                'new_decision' => $v['decision'],
                'evidence_type' => $v['evidence_type'],
                'justification' => $note,
                'created_at' => $now,
            ]);
        }, 3);

        return redirect()->route('campo.links.index', [
            'mission' => $mission->uuid, 'crown' => $v['operational_id'],
        ])->with('status', 'Decisión guardada y registrada en el historial. No se modificó ningún dato original de campo ni de IA.');
    }

    private function csvSafe($value): string
    {
        if ($value === null) return '';
        $value = is_scalar($value) ? (string) $value : json_encode($value, JSON_UNESCAPED_UNICODE);
        return preg_match('/^[\x00-\x20]*[=+@\-\t\r]/u', $value) ? "'".$value : $value;
    }

    public function export(Request $request)
    {
        $mission = $this->selectedMission($request);
        abort_unless($mission !== null, 422, 'Selecciona una misión.');
        $crowns = $this->crowns($mission);
        $links = DB::table('field_crown_links as l')
            ->leftJoin('field_observations as r', 'r.id', '=', 'l.field_observation_id')
            ->leftJoin('users as u', 'u.id', '=', 'l.reviewed_by')
            ->where('l.field_mission_id', $mission->id)
            ->get(['l.*', 'r.tree_id', 'r.grupo', 'r.uuid as observation_uuid', 'u.name as reviewer_name'])
            ->keyBy('operational_id');
        return response()->streamDownload(function () use ($mission, $crowns, $links) {
            $out = fopen('php://output', 'w');
            fwrite($out, "\xEF\xBB\xBF");
            fputcsv($out, ['mission_uuid','analysis_job_id','parcela_id','operational_id','modelo_origen',
                'decision','field_observation_id','field_observation_uuid','tree_id','grupo','tipo_evidencia',
                'justificacion','revisor','revisado_en','estado_interpretacion']);
            foreach ($crowns as $c) {
                $r = $links->get($c['id']);
                $decision = $r?->decision ?? 'sin_evaluar';
                $values = [
                    $mission->uuid, $mission->analysis_job_id, $mission->id_parcela,
                    $c['id'], $c['model'], $decision,
                    $r?->field_observation_id, $r?->observation_uuid, $r?->tree_id, $r?->grupo,
                    $r?->evidence_type, $r?->justification, $r?->reviewer_name, $r?->reviewed_at,
                    'Validación manual; NO equivale automáticamente a TP/FP/FN',
                ];
                fputcsv($out, array_map(fn ($val) => $this->csvSafe($val), $values));
            }
            fclose($out);
        }, 'sigmad_vinculaciones_'.substr($mission->uuid, 0, 8).'_'.now()->format('Ymd_His').'.csv', [
            'Content-Type' => 'text/csv; charset=UTF-8', 'Cache-Control' => 'private, no-store',
            'X-Content-Type-Options' => 'nosniff',
        ]);
    }
}
