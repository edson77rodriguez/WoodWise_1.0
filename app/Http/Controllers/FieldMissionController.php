<?php

namespace App\Http\Controllers;

use App\Models\AnalysisJob;
use App\Models\FieldMission;
use App\Models\Parcela;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Str;
use Illuminate\Validation\ValidationException;

class FieldMissionController extends Controller
{
    private function isAdmin(Request $request): bool
    {
        return $request->user()->loadMissing('persona.rol')->persona?->rol?->nom_rol === 'Administrador';
    }

    private function checkAccess(Request $request, FieldMission $mission): void
    {
        abort_unless($mission->status === 'active', 404);
        if ($this->isAdmin($request)) return;
        $tecnico = $request->user()->loadMissing('persona.tecnico')->persona?->tecnico;
        abort_unless($tecnico && $tecnico->parcelas()
            ->where('parcelas.id_parcela', $mission->id_parcela)->exists(), 403);
    }

    public function create(Request $request)
    {
        abort_unless($this->isAdmin($request), 403);
        $jobs = AnalysisJob::query()
            ->with(['mosaic', 'artifacts'])
            ->where('analysis_type', 'wall_to_wall_tree_crown')
            ->where('status', 'completed')
            ->latest('id')->limit(35)->get()
            ->filter(fn ($j) => $j->mosaic && $j->artifacts->contains('type', 'geojson'))
            ->map(function ($j) {
                $b = $j->mosaic->bounds;
                return [
                    'uuid' => $j->uuid, 'crs' => $j->mosaic->crs,
                    'label' => $j->mosaic->original_name . ' · ' . substr($j->uuid, 0, 8),
                    'bounds' => $b ? [(float)($b['left'] ?? 0), (float)($b['bottom'] ?? 0),
                                     (float)($b['right'] ?? 0), (float)($b['top'] ?? 0)] : null,
                    'geojson_url' => route('analysis.map.geojson', ['analysisUuid' => $j->uuid]),
                    'preview_url' => !empty($j->mosaic->preview_object_key)
                        ? route('analysis.map.preview', ['analysisUuid' => $j->uuid]) : null,
                    // Reutiliza el endpoint COG ya protegido y validado del analizador.
                    'cog_url' => filter_var(data_get($j->mosaic->metadata, 'web_cog.verified', false), FILTER_VALIDATE_BOOLEAN)
                        ? route('analysis.map.cog-url', ['analysisUuid' => $j->uuid]) : null,
                ];
            })->values();
        $parcelas = Parcela::orderBy('nom_parcela')->get(['id_parcela', 'nom_parcela']);
        $missions = FieldMission::latest()->limit(30)->get();
        return view('campo.missions', compact('jobs', 'parcelas', 'missions'));
    }

    public function store(Request $request)
    {
        abort_unless($this->isAdmin($request), 403);
        $v = $request->validate([
            'title' => ['required', 'string', 'min:3', 'max:120'],
            'id_parcela' => ['required', 'integer', 'exists:parcelas,id_parcela'],
            'analysis_uuid' => ['required', 'uuid'],
            'selected_ids' => ['required', 'array', 'min:1', 'max:200'],
            'selected_ids.*' => ['required', 'string', 'max:100', 'distinct'],
        ]);
        $job = AnalysisJob::with(['mosaic.project', 'artifacts'])
            ->where('uuid', $v['analysis_uuid'])->where('analysis_type', 'wall_to_wall_tree_crown')
            ->where('status', 'completed')->firstOrFail();
        $artifact = $job->artifacts->firstWhere('type', 'geojson');
        abort_unless($artifact && $job->result_prefix &&
            str_starts_with($artifact->object_key, $job->result_prefix), 422);
        $disk = Storage::disk('r2');
        abort_unless($disk->exists($artifact->object_key), 404);
        if ($disk->size($artifact->object_key) > 40 * 1024 * 1024) {
            throw ValidationException::withMessages(['analysis_uuid' => 'GeoJSON supera 40 MB; preparar una versión ligera antes de crear la misión.']);
        }
        $source = json_decode($disk->get($artifact->object_key), true);
        if (!is_array($source) || ($source['type'] ?? '') !== 'FeatureCollection' || !is_array($source['features'] ?? null)) {
            throw ValidationException::withMessages(['analysis_uuid' => 'El artifact no es un FeatureCollection válido.']);
        }
        $ids = array_fill_keys($v['selected_ids'], true);
        $found = [];
        foreach ($source['features'] as $f) {
            if (!is_array($f)) continue;
            $id = $f['properties']['operational_id'] ?? null;
            if (!is_scalar($id) || !isset($ids[(string)$id])) continue;
            $id = (string)$id;
            if (isset($found[$id])) {
                throw ValidationException::withMessages(['selected_ids' => "ID operativo duplicado en GeoJSON: $id"]);
            }
            if (!in_array($f['geometry']['type'] ?? '', ['Polygon', 'MultiPolygon'], true)) {
                throw ValidationException::withMessages(['selected_ids' => "Geometría no poligonal: $id"]);
            }
            $found[$id] = [
                'type' => 'Feature', 'id' => $id,
                'geometry' => $f['geometry'],
                'properties' => [
                    'operational_id' => $id,
                    'primary_model' => (string)($f['properties']['primary_model'] ?? ''),
                    'requires_review' => (bool)($f['properties']['requires_review'] ?? false),
                ],
            ];
        }
        if (count($found) !== count($ids)) {
            throw ValidationException::withMessages(['selected_ids' => 'Uno o más ID ya no existen en el GeoJSON original. Recarga el mapa.']);
        }
        $b = $job->mosaic?->bounds;
        $crs = $job->mosaic?->crs;
        if (!$b || !$crs || !preg_match('/^EPSG:(326|327)\d{2}$/', $crs)) {
            throw ValidationException::withMessages(['analysis_uuid' => 'Este piloto requiere un ortomosaico UTM EPSG:326xx/327xx.']);
        }
        $bounds = array_map('floatval', [$b['left'], $b['bottom'], $b['right'], $b['top']]);
        if (!($bounds[0] < $bounds[2] && $bounds[1] < $bounds[3])) {
            throw ValidationException::withMessages(['analysis_uuid' => 'Bounds del ortomosaico inválidos.']);
        }
        FieldMission::create([
            'uuid' => (string)Str::uuid(), 'created_by' => $request->user()->id,
            'id_parcela' => $v['id_parcela'], 'analysis_job_id' => $job->id,
            'title' => $v['title'], 'status' => 'active', 'crs' => $crs,
            'bounds' => $bounds,
            'geojson' => ['type' => 'FeatureCollection', 'features' => array_values($found)],
            'selected_count' => count($found),
        ]);
        return redirect()->route('campo.missions.create')->with('status', 'Misión creada: ' . count($found) . ' copas asignadas.');
    }

    public function data(Request $request, FieldMission $mission)
    {
        $this->checkAccess($request, $mission);
        return response()->json([
            'uuid' => $mission->uuid, 'title' => $mission->title,
            'id_parcela' => $mission->id_parcela,
            'analysis_uuid' => $mission->analysisJob->uuid,
            'crs' => $mission->crs, 'bounds' => $mission->bounds,
            'geojson' => $mission->geojson,
            'preview_url' => route('campo.mission.preview', ['mission' => $mission->uuid]),
            'tile_manifest_url' => route('campo.mission.tiles.manifest', ['mission' => $mission->uuid]),
            'selected_count' => $mission->selected_count,
        ])->header('Cache-Control', 'no-store, private');
    }

    public function preview(Request $request, FieldMission $mission)
    {
        $this->checkAccess($request, $mission);
        $mosaic = $mission->analysisJob->mosaic()->with('project')->firstOrFail();
        $key = $mosaic->preview_object_key;
        $pfx = 'projects/' . $mosaic->project?->uuid . '/mosaics/' . $mosaic->uuid . '/';
        abort_unless($mosaic->project && $key && str_starts_with($key, $pfx), 404);
        $disk = Storage::disk('r2');
        abort_unless($disk->exists($key), 404);
        abort_if($disk->size($key) > 20 * 1024 * 1024, 413, 'Vista previa mayor que 20 MB; generar una imagen reducida para campo.');
        $stream = $disk->readStream($key);
        abort_if($stream === false, 500);
        $ext = strtolower(pathinfo($key, PATHINFO_EXTENSION));
        $mime = match ($ext) {'png' => 'image/png', 'jpg', 'jpeg' => 'image/jpeg', 'webp' => 'image/webp', default => null};
        if (!$mime) { fclose($stream); abort(415); }
        return response()->stream(function () use ($stream) {
            fpassthru($stream);
            if (is_resource($stream)) fclose($stream);
        }, 200, [
            'Content-Type' => $mime, 'Cache-Control' => 'private, no-store',
            'X-Content-Type-Options' => 'nosniff',
        ]);
    }
    // Descarga JSON reproducible para generar teselas desde el GeoTIFF en la computadora.
    public function exportDefinition(Request $request, FieldMission $mission)
    {
        return $this->data($request, $mission)->header(
            'Content-Disposition', 'attachment; filename="SIGMAD_mision_' . $mission->uuid . '.json"'
        );
    }

    // Teselas privadas de una misión, generadas localmente y subidas mediante SFTP.
    // Ningún GeoTIFF ni paquete íntegro se expone bajo public_html.
    private function tileFolder(FieldMission $mission): string
    {
        return storage_path('app/private/sigmad-campo-tiles/' . $mission->uuid);
    }

    private function loadTileManifest(FieldMission $mission): array
    {
        $file = $this->tileFolder($mission) . '/manifest.json';
        abort_unless(is_file($file) && filesize($file) < 500_000, 404, 'Mapa HD no preparado para esta misión.');
        $json = json_decode((string) file_get_contents($file), true);
        abort_unless(is_array($json) &&
            ($json['schema'] ?? '') === 'sigmad-campo-utm-tiles-v1' &&
            ($json['mission_uuid'] ?? '') === $mission->uuid &&
            ($json['analysis_uuid'] ?? '') === $mission->analysisJob->uuid &&
            ($json['crs'] ?? '') === $mission->crs &&
            ($json['format'] ?? '') === 'webp' &&
            ($json['tile_size'] ?? 0) === 256 &&
            is_array($json['tiles'] ?? null) &&
            count($json['tiles']) === ($json['tile_count'] ?? -1) &&
            count($json['tiles']) <= 2000 &&
            count($json['resolutions'] ?? []) <= 13 &&
            count($json['resolutions'] ?? []) >= 1, 422, 'Manifiesto de teselas inválido.');
        $bounds = $json['bounds'] ?? [];
        abort_unless(is_array($bounds) && count($bounds) === 4 &&
            count($mission->bounds ?? []) === 4, 422);
        foreach ($mission->bounds as $i => $v) {
            abort_unless(abs((float) $v - (float) $bounds[$i]) < 0.5, 422,
                'Los límites del mapa HD no coinciden con la misión.');
        }
        return $json;
    }

    public function tilesManifest(Request $request, FieldMission $mission)
    {
        $this->checkAccess($request, $mission);
        $mission->loadMissing('analysisJob');
        $manifest = $this->loadTileManifest($mission);
        $manifest['tile_url_template'] = route('campo.mission.tiles.tile', [
            'mission' => $mission->uuid, 'z' => '__Z__', 'x' => '__X__', 'y' => '__Y__',
        ]);
        return response()->json($manifest)->header('Cache-Control', 'private, no-store');
    }

    public function tile(Request $request, FieldMission $mission, int $z, int $x, int $y)
    {
        $this->checkAccess($request, $mission);
        abort_unless($z >= 0 && $z <= 12 && $x >= 0 && $x <= 8192 &&
            $y >= 0 && $y <= 8192, 404);
        $file = $this->tileFolder($mission) . "/{$z}/{$x}/{$y}.webp";
        abort_unless(is_file($file) && is_readable($file), 404);
        return response()->file($file, [
            'Content-Type' => 'image/webp',
            'Cache-Control' => 'private, no-store',
            'X-Content-Type-Options' => 'nosniff',
        ]);
    }

}
