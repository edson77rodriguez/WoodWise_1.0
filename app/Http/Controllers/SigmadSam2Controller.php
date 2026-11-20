<?php

namespace App\Http\Controllers;

use App\Models\AnalysisArtifact;
use App\Models\AnalysisJob;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Str;

/**
 * SIGMAD SAM2-R1 — Gestión de capa experimental derivada en R2.
 * No invoca GPU, no sustituye capas originales ni modifica árboles de campo.
 */
class SigmadSam2Controller extends Controller
{
    private function job(string $analysisUuid): AnalysisJob
    {
        $job = AnalysisJob::query()
            ->with('mosaic.project')
            ->where('uuid', $analysisUuid)
            ->firstOrFail();
        abort_unless($job->analysis_type === 'wall_to_wall_tree_crown' && $job->status === 'completed', 404);
        abort_unless($job->mosaic && $job->mosaic->project, 404);
        abort_unless(is_string($job->result_prefix) && $job->result_prefix !== '', 422);
        return $job;
    }

    private function existing(AnalysisJob $job): ?AnalysisArtifact
    {
        return AnalysisArtifact::query()
            ->where('analysis_job_id', $job->id)
            ->where('type', 'sam2_geojson')
            ->first();
    }

    public function index(Request $request, string $analysisUuid)
    {
        $job = $this->job($analysisUuid);
        $mosaic = $job->mosaic;
        $bounds = $mosaic->bounds ?: [];
        foreach (['left', 'bottom', 'right', 'top'] as $axis) {
            abort_unless(isset($bounds[$axis]) && is_numeric($bounds[$axis]), 422,
                'El mosaico no tiene límites geoespaciales válidos.');
        }
        abort_unless(is_string($mosaic->crs) && $mosaic->crs !== '', 422);
        $baseArtifact = AnalysisArtifact::query()->where('analysis_job_id',$job->id)
            ->where('type','geojson')->firstOrFail();
        abort_unless(str_starts_with($baseArtifact->object_key, $job->result_prefix), 403);
        $sam = $this->existing($job);
        $meta = is_array($mosaic->metadata) ? $mosaic->metadata : [];
        $webCog = data_get($meta, 'web_cog', []);
        $cogAvailable = is_array($webCog) && filter_var($webCog['verified'] ?? false, FILTER_VALIDATE_BOOLEAN);
        return view('analysis.sam2', [
            'job'=>$job, 'mosaic'=>$mosaic, 'sam'=>$sam,
            'bounds'=>array_map('floatval', [$bounds['left'],$bounds['bottom'],$bounds['right'],$bounds['top']]),
            'previewAvailable'=>filled($mosaic->preview_object_key),
            'cogAvailable'=>$cogAvailable,
        ]);
    }

    public function geojson(Request $request, string $analysisUuid)
    {
        $job = $this->job($analysisUuid);
        $artifact = $this->existing($job);
        abort_unless($artifact, 404, 'No hay una capa SAM 2 registrada.');
        $prefix = rtrim($job->result_prefix, '/') . '/sam2/runs/';
        abort_unless(str_starts_with($artifact->object_key, $prefix), 403);
        $stream = Storage::disk('r2')->readStream($artifact->object_key);
        abort_if($stream === false, 404, 'No se pudo leer la capa desde R2.');
        return response()->stream(function () use ($stream) {
            try { fpassthru($stream); }
            finally { if (is_resource($stream)) fclose($stream); }
        }, 200, [
            'Content-Type'=>'application/geo+json; charset=utf-8',
            'Cache-Control'=>'private, no-store',
            'X-Content-Type-Options'=>'nosniff',
        ]);
    }

    public function register(Request $request, string $analysisUuid)
    {
        $job = $this->job($analysisUuid);
        $data = $request->validate([
            'sam2_geojson_key'=>['required','string','max:1024'],
        ]);
        $key = trim($data['sam2_geojson_key']);
        $prefix = rtrim($job->result_prefix, '/') . '/sam2/runs/';
        if (!str_starts_with($key, $prefix)
            || basename($key) !== 'sam2_refinements.geojson'
            || !preg_match('~^'.preg_quote($prefix,'~').'r1_[0-9]{8}T[0-9]{6}Z_(?:full|pilot[0-9]+)/sam2_refinements\.geojson$~', $key)) {
            return back()->withErrors(['sam2_geojson_key'=>'Ruta inválida: debe ser una ejecución SAM2-R1 dentro de este análisis.']);
        }
        $dir = dirname($key) . '/';
        $manifestKey = $dir . 'sam2_manifest.json';
        $disk = Storage::disk('r2');
        if (!$disk->exists($key) || !$disk->exists($manifestKey)) {
            return back()->withErrors(['sam2_geojson_key'=>'No se encontró el GeoJSON y su manifiesto en R2.']);
        }
        $size = $disk->size($key);
        if ($size < 50 || $size > 40 * 1024 * 1024) {
            return back()->withErrors(['sam2_geojson_key'=>'El tamaño del GeoJSON no es válido.']);
        }
        $manifest = json_decode($disk->get($manifestKey), true);
        if (!is_array($manifest)
            || ($manifest['schema'] ?? null) !== 'sigmad-sam2-r1'
            || ($manifest['source_job_uuid'] ?? '') !== $job->uuid
            || ($manifest['not_validated'] ?? null) !== true
            || !str_starts_with((string)($manifest['sam2_model_id'] ?? ''), 'facebook/sam2.1-hiera-')) {
            return back()->withErrors(['sam2_geojson_key'=>'El manifiesto no corresponde a este análisis o modelo.']);
        }
        $base = AnalysisArtifact::query()->where('analysis_job_id',$job->id)->where('type','geojson')->firstOrFail();
        if (hash('sha256', $disk->get($base->object_key)) !== ($manifest['source_geojson_sha256'] ?? '')) {
            return back()->withErrors(['sam2_geojson_key'=>'El GeoJSON base no coincide con la versión usada para refinar.']);
        }
        $payload = json_decode($disk->get($key), true);
        if (!is_array($payload) || ($payload['type'] ?? '') !== 'FeatureCollection'
            || !is_array($payload['features'] ?? null)
            || count($payload['features']) !== (int)($manifest['sam2_proposals'] ?? -1)) {
            return back()->withErrors(['sam2_geojson_key'=>'La capa derivada no corresponde a su manifiesto.']);
        }
        $sourceIds = [];
        $baseData = json_decode($disk->get($base->object_key), true);
        foreach (($baseData['features'] ?? []) as $feature) {
            $id = (string)($feature['properties']['operational_id'] ?? $feature['id'] ?? '');
            if ($id !== '') $sourceIds[$id] = true;
        }
        foreach ($payload['features'] as $feature) {
            $p = $feature['properties'] ?? [];
            $id = (string)($p['operational_id'] ?? '');
            if ($id === '' || !isset($sourceIds[$id])
                || ($p['source_analysis_uuid'] ?? '') !== $job->uuid
                || ($p['scientific_status'] ?? '') !== 'provisional_no_verificado') {
                return back()->withErrors(['sam2_geojson_key'=>'La capa contiene un ID ajeno al análisis o un estado científico inválido.']);
            }
        }
        $artifact = AnalysisArtifact::query()->updateOrCreate(
            ['analysis_job_id'=>$job->id, 'type'=>'sam2_geojson'],
            ['uuid'=>(string)($this->existing($job)?->uuid ?? Str::uuid()),
             'object_key'=>$key, 'filename'=>'sam2_refinements.geojson',
             'mime_type'=>'application/geo+json', 'size_bytes'=>$size,
             'checksum_sha256'=>hash('sha256', $disk->get($key)),
             'metadata'=>[
                 'model'=>$manifest['sam2_model_id'],
                 'processed'=>$manifest['processed'] ?? null,
                 'sam2_proposals'=>$manifest['sam2_proposals'] ?? null,
                 'skipped'=>$manifest['skipped'] ?? null,
                 'not_validated'=>true,
                 'run'=>basename(rtrim($dir,'/')),
                 'manifest_key'=>$manifestKey,
             ]]
        );
        return redirect()->route('analysis.sam2.index',['analysisUuid'=>$job->uuid])
            ->with('sam2_success', 'Capa SAM 2.1 registrada para revisión visual. No modifica V0.7E.');
    }
}
