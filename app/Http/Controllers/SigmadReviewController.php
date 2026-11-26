<?php

namespace App\Http\Controllers;

use App\Models\FieldMission;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Validation\ValidationException;

/**
 * SIGMAD Revisión IA · V0.8.0.
 * Se guardan revisiones aditivas, sin modificar predicciones ni mediciones de campo.
 */
class SigmadReviewController extends Controller
{
    private function admin(Request $request): void
    {
        abort_unless(
            $request->user()?->loadMissing('persona.rol')->persona?->rol?->nom_rol === 'Administrador',
            403, 'La revisión y exportación de etiquetas está restringida al Administrador.'
        );
    }

    private function active(FieldMission $mission): void
    {
        abort_unless($mission->status === 'active', 404);
    }

    private function originalGeojson(FieldMission $mission): array
    {
        $source = $mission->geojson;
        if (is_string($source)) $source = json_decode($source, true);
        abort_unless(is_array($source) && ($source['type'] ?? null) === 'FeatureCollection'
            && is_array($source['features'] ?? null), 422, 'GeoJSON original inválido.');
        return $source;
    }

    private function sourceHash(array $geojson): string
    {
        return hash('sha256', json_encode($geojson,
            JSON_UNESCAPED_UNICODE | JSON_UNESCAPED_SLASHES | JSON_THROW_ON_ERROR));
    }

    private function latest(FieldMission $mission): ?object
    {
        return DB::table('sigmad_review_revisions')->where('field_mission_id', $mission->id)
            ->orderByDesc('revision_no')->first();
    }

    public function index(Request $request)
    {
        $this->admin($request);
        $missions = FieldMission::query()->where('status', 'active')->orderByDesc('id')
            ->limit(200)->get(['uuid', 'title', 'selected_count', 'id_parcela'])
            ->map(fn ($m) => [
                'uuid' => $m->uuid,
                'title' => $m->title,
                'selected_count' => (int) $m->selected_count,
                'id_parcela' => $m->id_parcela,
            ])->values();
        return view('campo.revision-ia', compact('missions'));
    }

    public function data(Request $request, FieldMission $mission)
    {
        $this->admin($request);
        $this->active($mission);
        $source = $this->originalGeojson($mission);
        $latest = $this->latest($mission);
        $manual = \App\Models\FieldMissionManualTarget::query()
            ->where('field_mission_id', $mission->id)->where('status', 'active')
            ->orderBy('id')->limit(501)->get();
        abort_if($manual->count() > 500, 422, 'Demasiadas marcas manuales para esta revisión.');

        return response()->json([
            'uuid' => $mission->uuid,
            'title' => $mission->title,
            'analysis_uuid' => $mission->analysisJob?->uuid,
            'crs' => $mission->crs,
            'bounds' => $mission->bounds,
            'original' => $source,
            'source_sha256' => $this->sourceHash($source),
            'review' => $latest ? json_decode($latest->geojson, true) : null,
            'revision_no' => $latest ? (int) $latest->revision_no : 0,
            'review_source_sha256' => $latest?->source_sha256,
            'manual_targets' => [
                'type' => 'FeatureCollection', 'crs' => $mission->crs,
                'features' => $manual->map(fn ($m) => $m->toMissionFeature())->values()->all(),
            ],
            'preview_url' => route('campo.mission.preview', ['mission' => $mission->uuid]),
        ])->header('Cache-Control', 'private, no-store');
    }

    /** Validación estructural estricta; nunca aceptar geometrías arbitrarias del cliente. */
    private function cleanGeometry(array $geom): array
    {
        $type = $geom['type'] ?? '';
        if (!in_array($type, ['Polygon', 'MultiPolygon'], true)) {
            throw ValidationException::withMessages(['features' => 'Solo se admiten polígonos y multipolígonos.']);
        }
        $polygons = $type === 'Polygon' ? [$geom['coordinates'] ?? null] : ($geom['coordinates'] ?? null);
        if (!is_array($polygons) || !$polygons || count($polygons) > 120) {
            throw ValidationException::withMessages(['features' => 'Estructura de polígonos inválida.']);
        }
        $clean = [];
        $vertexCount = 0;
        foreach ($polygons as $polygon) {
            if (!is_array($polygon) || !$polygon || count($polygon) > 80) {
                throw ValidationException::withMessages(['features' => 'Anillos de polígono inválidos.']);
            }
            $rings = [];
            foreach ($polygon as $ring) {
                if (!is_array($ring) || count($ring) < 4) {
                    throw ValidationException::withMessages(['features' => 'Un anillo debe tener al menos cuatro vértices.']);
                }
                $vertexCount += count($ring);
                if ($vertexCount > 30000) {
                    throw ValidationException::withMessages(['features' => 'Máscara excesivamente compleja.']);
                }
                $out = [];
                foreach ($ring as $pt) {
                    if (!is_array($pt) || count($pt) < 2 || !is_numeric($pt[0]) || !is_numeric($pt[1])) {
                        throw ValidationException::withMessages(['features' => 'Vértices no numéricos.']);
                    }
                    $x = (float) $pt[0]; $y = (float) $pt[1];
                    // El GeoJSON de salida del visor está en longitud/latitud WGS84.
                    if (!is_finite($x) || !is_finite($y) || abs($x) > 180 || abs($y) > 90) {
                        throw ValidationException::withMessages(['features' => 'Coordenadas fuera de EPSG:4326.']);
                    }
                    $out[] = [$x, $y];
                }
                if ($out[0] !== $out[count($out) - 1]) {
                    throw ValidationException::withMessages(['features' => 'El anillo debe estar cerrado.']);
                }
                $rings[] = $out;
            }
            $clean[] = $rings;
        }
        return ['type' => $type, 'coordinates' => $type === 'Polygon' ? $clean[0] : $clean];
    }

    public function save(Request $request, FieldMission $mission)
    {
        $this->admin($request);
        $this->active($mission);
        $v = $request->validate([
            'expected_revision' => ['required', 'integer', 'min:0'],
            'source_sha256' => ['required', 'string', 'regex:/^[a-f0-9]{64}$/'],
            'reason' => ['required', 'string', 'min:8', 'max:500'],
            'features' => ['required', 'array', 'max:500'],
        ]);

        $result = DB::transaction(function () use ($mission, $request, $v) {
            $m = FieldMission::query()->whereKey($mission->getKey())->lockForUpdate()->firstOrFail();
            abort_unless($m->status === 'active', 409);
            $hash = $this->sourceHash($this->originalGeojson($m));
            if (!hash_equals($hash, $v['source_sha256'])) {
                abort(409, 'La misión original cambió. Exporta tu revisión local y recarga antes de guardar.');
            }
            $latest = $this->latest($m);
            $current = $latest ? (int) $latest->revision_no : 0;
            if ($current !== (int) $v['expected_revision']) {
                abort(409, 'Otra persona guardó una revisión nueva. Exporta tus cambios y recarga para reconciliarlos.');
            }

            $originalIds = [];
            foreach ($this->originalGeojson($m)['features'] as $f) {
                $id = $f['properties']['operational_id'] ?? $f['id'] ?? null;
                if ($id !== null) $originalIds[(string)$id] = true;
            }
            $manualIds = \App\Models\FieldMissionManualTarget::query()
                ->where('field_mission_id', $m->id)->where('status', 'active')
                ->pluck('uuid')->flip()->all();
            $seen = [];
            $clean = [];
            $allowedStatuses = ['pending', 'accepted', 'edited', 'added', 'split', 'superseded', 'rejected'];
            foreach ($v['features'] as $f) {
                if (!is_array($f) || ($f['type'] ?? null) !== 'Feature' || !is_array($f['geometry'] ?? null)) {
                    throw ValidationException::withMessages(['features' => 'Objeto de anotación inválido.']);
                }
                $p = $f['properties'] ?? [];
                if (!is_array($p)) $p = [];
                $id = $p['annotation_uuid'] ?? null;
                if (!is_string($id) || strlen($id) < 5 || strlen($id) > 120 || isset($seen[$id])) {
                    throw ValidationException::withMessages(['features' => 'Identificador de anotación inválido o repetido.']);
                }
                $seen[$id] = true;
                $status = (string)($p['review_status'] ?? 'pending');
                if (!in_array($status, $allowedStatuses, true)) {
                    throw ValidationException::withMessages(['features' => 'Estado de revisión no permitido.']);
                }
                $source = $p['source_operational_id'] ?? null;
                $source = $source === '' ? null : $source;
                if ($source !== null && (!is_string($source) || !isset($originalIds[$source]))) {
                    throw ValidationException::withMessages(['features' => 'ID IA original no existe en esta misión.']);
                }
                $manual = $p['manual_target_uuid'] ?? null;
                $manual = $manual === '' ? null : $manual;
                if ($manual !== null && (!is_string($manual) || !isset($manualIds[$manual]))) {
                    throw ValidationException::withMessages(['features' => 'Marca MAN no pertenece a esta misión.']);
                }
                $species = (string)($p['species'] ?? 'ND');
                $visibility = (string)($p['visibility'] ?? 'ND');
                $confidence = (string)($p['boundary_confidence'] ?? 'ND');
                if (!in_array($species, ['PL', 'QR', 'O', 'ND'], true) ||
                    !in_array($visibility, ['F', 'M', 'D', 'MD', 'ND'], true) ||
                    !in_array($confidence, ['alta', 'media', 'baja', 'ND'], true)) {
                    throw ValidationException::withMessages(['features' => 'Atributo de especie, visibilidad o confianza inválido.']);
                }
                $treeId = trim((string)($p['field_tree_id'] ?? ''));
                $note = trim((string)($p['note'] ?? ''));
                if (mb_strlen($treeId) > 80 || mb_strlen($note) > 1000) {
                    throw ValidationException::withMessages(['features' => 'Texto de anotación demasiado largo.']);
                }
                $clean[] = [
                    'type' => 'Feature',
                    'geometry' => $this->cleanGeometry($f['geometry']),
                    'properties' => [
                        'annotation_uuid' => $id,
                        'source_operational_id' => $source,
                        'review_status' => $status,
                        'species' => $species,
                        'visibility' => $visibility,
                        'boundary_confidence' => $confidence,
                        'manual_target_uuid' => $manual,
                        'field_tree_id' => $treeId,
                        'note' => $note,
                    ],
                ];
            }
            $json = json_encode(['type' => 'FeatureCollection', 'features' => $clean],
                JSON_UNESCAPED_UNICODE | JSON_UNESCAPED_SLASHES | JSON_THROW_ON_ERROR);
            $version = $current + 1;
            DB::table('sigmad_review_revisions')->insert([
                'field_mission_id' => $m->id,
                'revision_no' => $version,
                'source_sha256' => $hash,
                'geojson' => $json,
                'created_by' => $request->user()->id,
                'reason' => trim($v['reason']),
                'created_at' => now(), 'updated_at' => now(),
            ]);
            return ['ok' => true, 'revision_no' => $version, 'saved_count' => count($clean)];
        });
        return response()->json($result)->header('Cache-Control', 'private, no-store');
    }

    public function history(Request $request, FieldMission $mission)
    {
        $this->admin($request); $this->active($mission);
        $rows = DB::table('sigmad_review_revisions')->where('field_mission_id', $mission->id)
            ->orderByDesc('revision_no')->limit(25)
            ->get(['revision_no', 'created_at', 'reason', 'created_by']);
        return response()->json(['revisions' => $rows])->header('Cache-Control', 'private, no-store');
    }

    public function revision(Request $request, FieldMission $mission, int $version)
    {
        $this->admin($request); $this->active($mission);
        $row = DB::table('sigmad_review_revisions')->where('field_mission_id', $mission->id)
            ->where('revision_no', $version)->firstOrFail();
        return response()->json([
            'revision_no' => (int) $row->revision_no,
            'source_sha256' => $row->source_sha256,
            'reason' => $row->reason,
            'geojson' => json_decode($row->geojson, true),
        ])->header('Cache-Control', 'private, no-store');
    }
}
