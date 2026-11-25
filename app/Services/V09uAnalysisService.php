<?php

namespace App\Services;

use App\Models\AnalysisArtifact;
use App\Models\AnalysisJob;
use App\Models\Mosaic;
use Illuminate\Http\Client\ConnectionException;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Str;
use RuntimeException;

/**
 * Adaptador V0.9U: una sola base URL (services.ai.url); SAM se ejecuta en FastAPI.
 * No invoca PyTorch desde PHP ni toca el flujo histórico V0.7E/V0.8S.
 */
class V09uAnalysisService
{
    private function baseUrl(): string
    {
        $url = rtrim(trim((string) config('services.ai.url')), '/');
        if (!str_starts_with($url, 'https://')) {
            throw new RuntimeException('services.ai.url debe contener la URL HTTPS del FastAPI UNIFICADO.');
        }
        return $url;
    }

    private function client()
    {
        $token = trim((string) config('services.ai_v09u.token'));
        if (strlen($token) < 32) {
            throw new RuntimeException('Configure AI_V09U_SHARED_TOKEN en Hostinger (min. 32 caracteres).');
        }
        return Http::acceptJson()->withHeaders(['X-SIGMAD-AI-Key' => $token])
            ->connectTimeout(12)->timeout(35);
    }

    public function start(Mosaic $mosaic): AnalysisJob
    {
        $mosaic->loadMissing('project');
        if (!$mosaic->project || $mosaic->status !== 'ready') {
            throw new RuntimeException('El ortomosaico no está listo.');
        }
        $expected = "projects/{$mosaic->project->uuid}/mosaics/{$mosaic->uuid}/original/orthomosaic.tif";
        if ($mosaic->object_key !== $expected) {
            throw new RuntimeException('Ruta R2 original incompatible con contrato V0.9U.');
        }

        // Bloquea la fila del mosaico para evitar dos trabajos V0.9U concurrentes.
        $job = DB::transaction(function () use ($mosaic) {
            Mosaic::whereKey($mosaic->getKey())->lockForUpdate()->firstOrFail();
            $active = AnalysisJob::where('mosaic_id', $mosaic->id)
                ->where('analysis_type', 'wall_to_wall_tree_crown')
                ->where('parameters->pipeline_version', 'V0.9U')
                ->whereIn('status', ['processing', 'queued'])->exists();
            if ($active) {
                throw new RuntimeException('Ya existe un V0.9U activo. Consulte su estado antes de iniciar otro.');
            }
            return AnalysisJob::create([
                'uuid' => (string) Str::uuid(), 'mosaic_id' => $mosaic->id,
                'analysis_type' => 'wall_to_wall_tree_crown', 'status' => 'processing',
                'progress' => 0, 'total_tiles' => 0, 'processed_tiles' => 0,
                'models' => [
                    'primary_geometry_model' => 'Mask R-CNN E2 PILOTO',
                    'secondary_model' => 'YOLO-Seg E1.1',
                    'refiner' => 'SAM 2.1 Small automático',
                    'pipeline_version' => 'V0.9U',
                ],
                'parameters' => ['pipeline_version' => 'V0.9U', 'not_validated' => true],
                'summary' => null, 'result_prefix' => null, 'error_message' => null,
                'started_at' => now(), 'completed_at' => null, 'heartbeat_at' => now(),
            ]);
        });

        try {
            $res = $this->client()->post($this->baseUrl().'/mosaic/analyze/v09u/jobs', [
                'analysis_uuid' => $job->uuid,
                'project_uuid' => $mosaic->project->uuid,
                'mosaic_uuid' => $mosaic->uuid,
                'object_key' => $mosaic->object_key,
                'analysis_version' => 'V0.9U',
                // Sin max_crowns: SAM intenta todas las copas elegibles.
            ]);
        } catch (ConnectionException $e) {
            // El remoto PUDO aceptar el trabajo. No marcar failed ni reenviar automáticamente.
            $job->update(['error_message' => 'Confirmación remota incierta; consultar estado antes de reintentar.']);
            throw new RuntimeException('No se confirmó el inicio en Colab. NO repita el clic; consulte el análisis.', 0, $e);
        } catch (\Throwable $e) {
            $job->update(['status' => 'failed', 'error_message' => mb_substr($e->getMessage(), 0, 500), 'completed_at' => now()]);
            throw $e;
        }

        $body = $res->json();
        if ($res->status() !== 202 || !is_array($body)
            || ($body['analysis_uuid'] ?? null) !== $job->uuid
            || !in_array($body['status'] ?? null, ['queued', 'processing'], true)) {
            $error = 'FastAPI V0.9U rechazó el inicio (HTTP '.$res->status().'): '.mb_substr($res->body(), 0, 240);
            $job->update(['status' => 'failed', 'error_message' => $error, 'completed_at' => now()]);
            throw new RuntimeException($error);
        }
        $job->update(['error_message' => null]);
        return $job;
    }

    public function refresh(AnalysisJob $job): AnalysisJob
    {
        $job->loadMissing('mosaic.project');
        $this->assertV09u($job);
        if (in_array($job->status, ['completed', 'failed'], true)) {
            return $job->loadMissing('artifacts');
        }
        $res = $this->client()->get($this->baseUrl().'/mosaic/analyze/v09u/jobs/'.$job->uuid, [
            'project_uuid' => $job->mosaic->project->uuid,
            'mosaic_uuid' => $job->mosaic->uuid,
        ]);
        if (!$res->successful()) {
            throw new RuntimeException('Consulta V0.9U HTTP '.$res->status().'. No se modifica el estado local.');
        }
        $data = $res->json();
        if (!is_array($data) || ($data['analysis_uuid'] ?? '') !== $job->uuid
            || ($data['project_uuid'] ?? '') !== $job->mosaic->project->uuid
            || ($data['mosaic_uuid'] ?? '') !== $job->mosaic->uuid
            || ($data['pipeline_version'] ?? '') !== 'V0.9U') {
            throw new RuntimeException('Respuesta remota V0.9U no coincide con el análisis solicitado.');
        }
        $status = $data['status'] ?? null;
        if ($status === 'completed') {
            $this->registerCompleted($job, $data['result'] ?? null);
            return $job->fresh('artifacts');
        }
        if ($status === 'failed') {
            $job->update([
                'status' => 'failed',
                'progress' => (int) ($data['progress'] ?? 0),
                'error_message' => mb_substr((string) ($data['message'] ?? 'Falló FastAPI V0.9U'), 0, 500),
                'completed_at' => now(), 'heartbeat_at' => now(),
            ]);
        } elseif ($status === 'interrupted') {
            // Mantener bloqueado hasta revisión: Colab pudo dejar resultados parciales.
            $job->update(['error_message' => 'INTERRUPTED: verificar archivos R2 y Colab antes de habilitar reintento.']);
        } elseif (in_array($status, ['queued', 'processing'], true)) {
            $job->update([
                'progress' => max(0, min(99, (int) ($data['progress'] ?? 0))),
                'processed_tiles' => (int) ($data['processed_tiles'] ?? 0),
                'total_tiles' => (int) ($data['total_tiles'] ?? 0),
                'heartbeat_at' => now(), 'error_message' => null,
            ]);
        } else {
            throw new RuntimeException('Estado remoto V0.9U no reconocido.');
        }
        return $job->fresh('artifacts');
    }

    /** Importa un piloto completado previamente en Colab, sin nueva inferencia. */
    public function importCompleted(Mosaic $mosaic, string $uuid): AnalysisJob
    {
        $mosaic->loadMissing('project');
        if (!$mosaic->project || !Str::isUuid($uuid)) {
            throw new RuntimeException('Mosaico o UUID inválido.');
        }
        $key = "projects/{$mosaic->project->uuid}/mosaics/{$mosaic->uuid}/analyses/{$uuid}/V09U/job_state.json";
        $disk = Storage::disk('r2');
        if (!$disk->exists($key)) {
            throw new RuntimeException('No se encontró estado persistente en R2.');
        }
        $data = json_decode($disk->get($key), true, 512, JSON_THROW_ON_ERROR);
        if (($data['status'] ?? '') !== 'completed'
            || ($data['analysis_uuid'] ?? '') !== $uuid
            || ($data['project_uuid'] ?? '') !== $mosaic->project->uuid
            || ($data['mosaic_uuid'] ?? '') !== $mosaic->uuid
            || ($data['pipeline_version'] ?? '') !== 'V0.9U'
            || ($data['input_object_key'] ?? '') !== $mosaic->object_key) {
            throw new RuntimeException('El estado R2 no es un V0.9U completado de este mosaico.');
        }
        $job = DB::transaction(function () use ($mosaic, $uuid) {
            $existing = AnalysisJob::where('uuid', $uuid)->lockForUpdate()->first();
            if ($existing) {
                if ((int) $existing->mosaic_id !== (int) $mosaic->id
                    || data_get($existing->parameters, 'pipeline_version') !== 'V0.9U') {
                    throw new RuntimeException('UUID existente vinculado a otro análisis.');
                }
                return $existing;
            }
            return AnalysisJob::create([
                'uuid' => $uuid, 'mosaic_id' => $mosaic->id,
                'analysis_type' => 'wall_to_wall_tree_crown', 'status' => 'processing',
                'progress' => 0, 'total_tiles' => 0, 'processed_tiles' => 0,
                'models' => ['primary_geometry_model' => 'Mask R-CNN E2 PILOTO',
                    'secondary_model' => 'YOLO-Seg E1.1', 'refiner' => 'SAM 2.1 Small automático',
                    'pipeline_version' => 'V0.9U'],
                'parameters' => ['pipeline_version' => 'V0.9U', 'not_validated' => true,
                    'imported_from_r2' => true],
                'summary' => null, 'result_prefix' => null,
                'error_message' => null, 'started_at' => now(),
                'heartbeat_at' => now(), 'completed_at' => null,
            ]);
        });
        $this->registerCompleted($job, $data['result'] ?? null);
        return $job->fresh('artifacts');
    }

    private function assertV09u(AnalysisJob $job): void
    {
        if (data_get($job->parameters, 'pipeline_version') !== 'V0.9U'
            || !$job->mosaic || !$job->mosaic->project) {
            throw new RuntimeException('Trabajo no corresponde a V0.9U o no tiene proyecto.');
        }
    }

    private function registerCompleted(AnalysisJob $job, $result): void
    {
        $this->assertV09u($job);
        $mosaic = $job->mosaic;
        $p = $mosaic->project->uuid;
        $m = $mosaic->uuid;
        $prefix = "projects/{$p}/mosaics/{$m}/analyses/{$job->uuid}/V09U/";
        if (!is_array($result) || ($result['status'] ?? '') !== 'ok'
            || ($result['analysis_uuid'] ?? '') !== $job->uuid
            || ($result['analysis_version'] ?? '') !== 'V0.9U'
            || ($result['project_uuid'] ?? '') !== $p
            || ($result['mosaic_uuid'] ?? '') !== $m
            || ($result['result_prefix'] ?? '') !== $prefix) {
            throw new RuntimeException('Identidad V0.9U o prefijo R2 inconsistente.');
        }
        $arr = $result['artifacts'] ?? null;
        $expectedTypes = ['e2_geojson', 'sam2_geojson', 'geojson', 'qa_csv', 'manifest'];
        if (!is_array($arr) || count($arr) !== 5) {
            throw new RuntimeException('V0.9U debe devolver exactamente cinco artefactos.');
        }
        $seen = [];
        $disk = Storage::disk('r2');
        $manifest = null;
        foreach ($arr as $a) {
            if (!is_array($a)) {
                throw new RuntimeException('Artefacto inválido.');
            }
            $type = $a['type'] ?? null;
            $key = $a['object_key'] ?? '';
            $filename = $a['filename'] ?? '';
            if (!in_array($type, $expectedTypes, true) || isset($seen[$type])
                || ($a['verified'] ?? false) !== true
                || !is_string($key) || !str_starts_with($key, $prefix)
                || !is_string($filename) || $filename !== basename($key)
                || !preg_match('/^[0-9a-f]{64}$/', (string) ($a['checksum_sha256'] ?? ''))
                || (int) ($a['size_bytes'] ?? 0) < 20) {
                throw new RuntimeException('Tipo, ruta o integridad declarada de artefacto inválidos.');
            }
            // Artifacts piloto son pequeños: se verifica hash/size efectivamente contra R2.
            $raw = $disk->get($key);
            if (strlen($raw) !== (int) $a['size_bytes']
                || !hash_equals($a['checksum_sha256'], hash('sha256', $raw))) {
                throw new RuntimeException('SHA256 o tamaño real R2 no coincide para '.$type);
            }
            if ($type === 'manifest') {
                $manifest = json_decode($raw, true, 512, JSON_THROW_ON_ERROR);
            }
            $seen[$type] = $a;
        }
        if (count($seen) !== 5 || !is_array($manifest)
            || ($manifest['pipeline_version'] ?? '') !== 'V0.9U'
            || ($manifest['analysis_uuid'] ?? '') !== $job->uuid
            || ($manifest['project_uuid'] ?? '') !== $p
            || ($manifest['mosaic_uuid'] ?? '') !== $m
            || ($manifest['input_object_key'] ?? '') !== $mosaic->object_key) {
            throw new RuntimeException('Manifiesto V0.9U inconsistente.');
        }
        // Un CRS no coincidente impide mapear errores como si fueran datos georreferenciados correctos.
        if (filled($mosaic->crs) && ($manifest['source_crs'] ?? '') !== $mosaic->crs) {
            throw new RuntimeException('CRS del manifiesto no coincide con el mosaico en Laravel.');
        }
        $summary = $result['summary'] ?? null;
        if (!is_array($summary) || ($summary['not_validated'] ?? null) !== true) {
            throw new RuntimeException('Resumen provisional V0.9U inválido.');
        }
        DB::transaction(function () use ($job, $arr, $summary, $prefix, $result) {
            $locked = AnalysisJob::whereKey($job->id)->lockForUpdate()->firstOrFail();
            foreach ($arr as $a) {
                $record = AnalysisArtifact::firstOrNew([
                    'analysis_job_id' => $locked->id, 'type' => $a['type'],
                ]);
                if (!$record->exists) {
                    $record->uuid = (string) Str::uuid();
                }
                $record->object_key = $a['object_key'];
                $record->filename = $a['filename'];
                $record->mime_type = $a['mime_type'];
                $record->size_bytes = (int) $a['size_bytes'];
                $record->checksum_sha256 = $a['checksum_sha256'];
                $record->metadata = [
                    'pipeline_version' => 'V0.9U', 'verified' => true,
                    'not_validated' => true,
                    'purpose' => $a['type'] === 'geojson' ? 'web_visualization_hybrid' : 'research_artifact',
                ];
                $record->save();
            }
            $n = (int) ($result['total_tiles'] ?? 0);
            $locked->update([
                'status' => 'completed', 'progress' => 100,
                'total_tiles' => $n, 'processed_tiles' => $n,
                'summary' => $summary, 'result_prefix' => $prefix,
                'error_message' => null, 'completed_at' => now(), 'heartbeat_at' => now(),
            ]);
        });
    }
}
