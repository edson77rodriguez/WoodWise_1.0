<?php

namespace App\Http\Controllers;

use App\Models\FieldObservation;
use App\Models\Parcela;
use App\Models\User;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Validation\Rule;

/**
 * SIGMAD Campo V0.3: panel exclusivo de Administrador.
 * Datos de campo inmutables: el panel solo modifica estado, nota e historial de revisión.
 */
class FieldRecordsController extends Controller
{
    private const STATUSES = ['pendiente', 'en_revision', 'validado', 'observado'];
    private const GROUPS = ['A', 'B', 'C', 'H'];
    private const SPECIES = ['PL', 'QR', 'O', 'ND'];

    private function filters(Request $request): array
    {
        $f = $request->validate([
            'parcela' => ['nullable', 'integer', 'exists:parcelas,id_parcela'],
            'brigadista' => ['nullable', 'integer', 'exists:users,id'],
            'grupo' => ['nullable', Rule::in(self::GROUPS)],
            'especie' => ['nullable', Rule::in(self::SPECIES)],
            'revision' => ['nullable', Rule::in(self::STATUSES)],
            'desde' => ['nullable', 'date_format:Y-m-d'],
            'hasta' => ['nullable', 'date_format:Y-m-d'],
            'buscar' => ['nullable', 'string', 'max:80'],
        ]);
        if (!empty($f['desde']) && !empty($f['hasta']) && $f['hasta'] < $f['desde']) {
            throw \Illuminate\Validation\ValidationException::withMessages([
                'hasta' => 'La fecha final no puede ser anterior a la inicial.',
            ]);
        }
        return $f;
    }

    private function recordsQuery(array $f): Builder
    {
        $q = FieldObservation::query()
            ->leftJoin('users as brig', 'brig.id', '=', 'field_observations.user_id')
            ->leftJoin('parcelas as parc', 'parc.id_parcela', '=', 'field_observations.id_parcela')
            ->select('field_observations.*', 'brig.name as brigadista_nombre', 'parc.nom_parcela as parcela_nombre');

        if (!empty($f['parcela'])) $q->where('field_observations.id_parcela', $f['parcela']);
        if (!empty($f['brigadista'])) $q->where('field_observations.user_id', $f['brigadista']);
        if (!empty($f['grupo'])) $q->where('field_observations.grupo', $f['grupo']);
        if (!empty($f['revision'])) $q->where('field_observations.review_status', $f['revision']);
        if (!empty($f['especie'])) $q->where('field_observations.payload->especie', $f['especie']);
        if (!empty($f['desde'])) $q->whereDate('field_observations.client_created_at', '>=', $f['desde']);
        if (!empty($f['hasta'])) $q->whereDate('field_observations.client_created_at', '<=', $f['hasta']);
        if (!empty($f['buscar'])) {
            $term = str_replace(['%', '_', '\\'], ['\\%', '\\_', '\\\\'], trim($f['buscar']));
            $q->where(function (Builder $query) use ($term) {
                $query->where('field_observations.tree_id', 'like', '%'.$term.'%')
                    ->orWhere('field_observations.candidate_id', 'like', '%'.$term.'%')
                    ->orWhere('field_observations.uuid', 'like', '%'.$term.'%');
            });
        }
        return $q;
    }

    private function qualityWarnings(FieldObservation $r): array
    {
        $p = $r->payload ?? [];
        $warn = [];
        if (($r->grupo === 'A') && ($p['localizado'] ?? null) === 'no') {
            return ['Candidato IA no localizado; no se exige medición dasométrica.'];
        }
        if (!isset($p['dap_cm']) || $p['dap_cm'] === null) $warn[] = 'DAP no registrado';
        if (!isset($p['altura_m']) || $p['altura_m'] === null) $warn[] = 'Altura no registrada';
        foreach (['r_n_m' => 'N', 'r_s_m' => 'S', 'r_e_m' => 'E', 'r_o_m' => 'O'] as $key => $dir) {
            if (!isset($p[$key]) || $p[$key] === null) $warn[] = 'Radio '.$dir.' sin medir';
        }
        if (!is_numeric($p['latitud'] ?? null) || !is_numeric($p['longitud'] ?? null)) $warn[] = 'Sin posición GPS';
        elseif (is_numeric($p['gps_precision_m'] ?? null) && (float)$p['gps_precision_m'] > 15) {
            $warn[] = 'Precisión GNSS reportada >15 m (umbral operativo para revisión)';
        }
        if (($r->grupo === 'B') && ($p['especie'] ?? null) === 'PL'
            && is_numeric($p['dap_cm'] ?? null) && (float)$p['dap_cm'] < 7.5) {
            $warn[] = 'DAP inferior al umbral operativo del censo de Pinus (7.5 cm)';
        }
        return $warn;
    }

    public function index(Request $request)
    {
        $filters = $this->filters($request);
        $base = $this->recordsQuery($filters);
        $rows = (clone $base)->orderByDesc('field_observations.client_created_at')
            ->orderByDesc('field_observations.id')->paginate(25)->withQueryString();

        $totals = (clone $base)->reorder()->select([])->selectRaw(
            "COUNT(*) as total, " .
            "SUM(CASE WHEN field_observations.review_status = 'pendiente' THEN 1 ELSE 0 END) as pendientes, " .
            "SUM(CASE WHEN field_observations.review_status = 'validado' THEN 1 ELSE 0 END) as validados, " .
            "COUNT(DISTINCT field_observations.user_id) as brigadistas"
        )->first();

        return view('campo.registros.index', [
            'rows' => $rows,
            'totals' => $totals,
            'filters' => $filters,
            'parcelas' => Parcela::orderBy('nom_parcela')->get(['id_parcela','nom_parcela']),
            'brigadistas' => User::whereIn('id', FieldObservation::query()->select('user_id'))
                ->orderBy('name')->get(['id', 'name']),
        ]);
    }

    public function show(FieldObservation $record)
    {
        $events = DB::table('field_observation_review_events as ev')
            ->join('users as usr', 'usr.id', '=', 'ev.reviewer_id')
            ->where('ev.field_observation_id', $record->id)
            ->orderByDesc('ev.id')
            ->get(['ev.*', 'usr.name as reviewer_name']);

        return view('campo.registros.show', [
            'record' => $record,
            'brigadista' => User::find($record->user_id)?->name ?? 'Usuario no disponible',
            'parcela' => Parcela::find($record->id_parcela)?->nom_parcela ?? 'Parcela no disponible',
            'reviewer' => $record->reviewed_by ? User::find($record->reviewed_by)?->name : null,
            'warnings' => $this->qualityWarnings($record),
            'events' => $events,
            'statuses' => self::STATUSES,
        ]);
    }

    public function review(Request $request, FieldObservation $record)
    {
        $v = $request->validate([
            'review_status' => ['required', Rule::in(self::STATUSES)],
            'review_note' => ['nullable', 'string', 'max:1000', 'required_if:review_status,observado'],
        ]);
        $status = $v['review_status'];
        $note = trim((string)($v['review_note'] ?? ''));

        DB::transaction(function () use ($request, $record, $status, $note) {
            $current = FieldObservation::whereKey($record->id)->lockForUpdate()->firstOrFail();
            if ($current->review_status === $status && (string)$current->review_note === $note) return;
            DB::table('field_observation_review_events')->insert([
                'field_observation_id' => $current->id,
                'reviewer_id' => $request->user()->id,
                'previous_status' => $current->review_status,
                'new_status' => $status,
                'note' => $note !== '' ? $note : null,
                'created_at' => now(),
            ]);
            $current->review_status = $status;
            $current->review_note = $note !== '' ? $note : null;
            $current->reviewed_by = $request->user()->id;
            $current->reviewed_at = now();
            $current->save();
        });

        return redirect()->route('campo.records.show', ['record' => $record->id])
            ->with('status', 'Revisión registrada. Las mediciones originales no se modificaron.');
    }

    public function map(Request $request)
    {
        $rows = $this->recordsQuery($this->filters($request))
            ->orderByDesc('field_observations.id')->limit(500)->get();
        $points = [];
        foreach ($rows as $r) {
            $p = $r->payload ?? [];
            if (!is_numeric($p['latitud'] ?? null) || !is_numeric($p['longitud'] ?? null)) continue;
            $lat = (float)$p['latitud']; $lon = (float)$p['longitud'];
            if ($lat < -90 || $lat > 90 || $lon < -180 || $lon > 180) continue;
            $points[] = [
                'id' => $r->id, 'lat' => $lat, 'lon' => $lon,
                'label' => $r->tree_id ?: ($r->candidate_id ?: 'Registro '.$r->id),
                'grupo' => $r->grupo,
                'status' => $r->review_status,
                'accuracy_m' => is_numeric($p['gps_precision_m'] ?? null) ? (float)$p['gps_precision_m'] : null,
                'url' => route('campo.records.show', ['record' => $r->id]),
            ];
        }
        return response()->json(['points' => $points, 'sample_limit' => 500])
            ->header('Cache-Control', 'private, no-store');
    }

    private function safeCell($value): string
    {
        if ($value === null) return '';
        if (is_int($value) || is_float($value)) return (string)$value;
        if (is_string($value) && preg_match('/^-\d+(?:\.\d+)?$/D', $value)) return $value;
        $v = is_scalar($value) ? (string)$value : json_encode($value, JSON_UNESCAPED_UNICODE);
        // Evitar fórmulas inyectadas al abrir CSV en hojas de cálculo.
        if (preg_match('/^[\x00-\x20]*[=+@\-\t\r]/u', $v)) $v = "'".$v;
        return $v;
    }

    public function export(Request $request)
    {
        $query = $this->recordsQuery($this->filters($request))
            ->orderBy('field_observations.id');
        $name = 'sigmad_inventario_campo_'.now()->format('Ymd_His').'.csv';
        $cols = [
            'registro_id','uuid','parcela_id','parcela','brigadista','grupo','tree_id','candidate_id',
            'especie','estado_arbol','localizado','dap_cm','altura_m','conf_altura',
            'radio_n_m','radio_s_m','radio_e_m','radio_o_m','dominancia','solapamiento','visibilidad',
            'latitud','longitud','precision_gnss_m','fecha_gps','metodo_gps',
            'mission_uuid','detection_ref','motivo_sin_medicion','observaciones',
            'estado_revision','nota_revision','fecha_campo','fecha_sincronizacion','hash_payload_sha256',
        ];
        return response()->streamDownload(function () use ($query, $cols) {
            $out = fopen('php://output', 'w');
            fwrite($out, "\xEF\xBB\xBF"); // BOM: Excel UTF-8.
            fputcsv($out, $cols);
            foreach ($query->cursor() as $r) {
                $p = $r->payload ?? [];
                $values = [
                    $r->id, $r->uuid, $r->id_parcela, $r->parcela_nombre, $r->brigadista_nombre,
                    $r->grupo, $r->tree_id, $r->candidate_id,
                    $p['especie'] ?? null, $p['estado'] ?? null, $p['localizado'] ?? null,
                    $p['dap_cm'] ?? null, $p['altura_m'] ?? null, $p['conf_altura'] ?? null,
                    $p['r_n_m'] ?? null, $p['r_s_m'] ?? null, $p['r_e_m'] ?? null, $p['r_o_m'] ?? null,
                    $p['dominancia'] ?? null, $p['solapamiento'] ?? null, $p['visibilidad'] ?? null,
                    $p['latitud'] ?? null, $p['longitud'] ?? null, $p['gps_precision_m'] ?? null,
                    $p['gps_fecha'] ?? null, $p['gps_metodo'] ?? null,
                    $p['mission_uuid'] ?? null, $p['detection_ref'] ?? null,
                    $p['motivo_sin_medicion'] ?? null, $p['observaciones'] ?? null,
                    $r->review_status, $r->review_note,
                    $r->client_created_at?->toIso8601String(),
                    $r->synced_at?->toIso8601String(), $r->payload_sha256,
                ];
                fputcsv($out, array_map(fn ($v) => $this->safeCell($v), $values));
            }
            fclose($out);
        }, $name, [
            'Content-Type' => 'text/csv; charset=UTF-8',
            'Cache-Control' => 'private, no-store',
            'X-Content-Type-Options' => 'nosniff',
        ]);
    }
}
