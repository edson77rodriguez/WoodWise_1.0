<?php

namespace App\Services;

use App\Models\AnalysisArtifact;
use App\Models\AnalysisJob;
use App\Models\Mosaic;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Str;
use RuntimeException;

/**
 * SIGMAD V0.8S: adapter versionado y asíncrono.
 * No modifica los contratos V0.7E ni intenta ejecutar PyTorch en Hostinger.
 */
class V08sAnalysisService
{
    private function apiUrl(): string
    {
        $url = trim((string) config('services.ai_v08s.url'));
        if (!$url || !str_starts_with($url, 'https://')) {
            throw new RuntimeException('AI_V08S_URL debe ser HTTPS y estar configurada.');
        }
        return rtrim($url, '/');
    }

    private function apiKey(): string
    {
        $key = trim((string) config('services.ai_v08s.token'));
        if (strlen($key) < 32) {
            throw new RuntimeException('AI_V08S_SHARED_TOKEN no está configurado correctamente.');
        }
        return $key;
    }

    private function request()
    {
        return Http::withHeaders(['X-SIGMAD-AI-Key' => $this->apiKey()])
            ->acceptJson()->connectTimeout(12)->timeout(35);
    }

    public function start(Mosaic $mosaic): AnalysisJob
    {
        $mosaic->loadMissing('project');
        if (!$mosaic->project || $mosaic->status !== 'ready') {
            throw new RuntimeException('El mosaico no está listo o no tiene proyecto.');
        }
        $uuid = (string) Str::uuid();
        $job = AnalysisJob::create([
            'uuid' => $uuid,
            'mosaic_id' => $mosaic->id,
            'analysis_type' => 'wall_to_wall_tree_crown',
            'status' => 'processing',
            'progress' => 0,
            'total_tiles' => 0,
            'processed_tiles' => 0,
            'models' => [
                'primary_geometry_model' => 'Mask R-CNN E2 PILOTO',
                'secondary_model' => 'YOLO-Seg E1.1',
                'refiner' => 'SAM 2.1 Small',
                'pipeline_version' => 'V0.8S',
            ],
            'parameters' => ['pipeline_version'=>'V0.8S','not_validated'=>true],
            'summary' => null,
            'result_prefix' => null,
            'error_message' => null,
            'started_at' => now(),
            'completed_at' => null,
            'heartbeat_at' => now(),
        ]);
        try {
            $input = [
                'analysis_uuid'=>$uuid,
                'project_uuid'=>$mosaic->project->uuid,
                'mosaic_uuid'=>$mosaic->uuid,
                'object_key'=>$mosaic->object_key,
                'analysis_version'=>'V0.8S',
            ];
            $res = $this->request()->post($this->apiUrl().'/mosaic/analyze/v08s/jobs', $input);
            $data = $res->json();
            if ($res->status() !== 202 || !is_array($data) ||
                ($data['analysis_uuid'] ?? null) !== $uuid ||
                !in_array(($data['status'] ?? ''), ['queued','processing'],true)) {
                throw new RuntimeException('FastAPI V0.8S no aceptó el trabajo. HTTP '.$res->status());
            }
            return $job;
        } catch (\Throwable $e) {
            $job->update(['status'=>'failed','error_message'=>mb_substr($e->getMessage(),0,500),'heartbeat_at'=>now()]);
            throw $e;
        }
    }

    public function refresh(AnalysisJob $job): AnalysisJob
    {
        $job->loadMissing('mosaic.project');
        if (data_get($job->parameters,'pipeline_version')!=='V0.8S') {
            throw new RuntimeException('Trabajo no pertenece a V0.8S.');
        }
        if (in_array($job->status,['completed','failed'],true)) return $job;
        $res=$this->request()->get($this->apiUrl().'/mosaic/analyze/v08s/jobs/'.$job->uuid);
        if (!$res->successful()) {
            // Un 404 tras reiniciar Colab NO es éxito; el estado permanece processing para recuperación.
            throw new RuntimeException('No se pudo consultar Colab (HTTP '.$res->status().'). Verificar sesión.');
        }
        $body=$res->json();
        $state=$body['status'] ?? null;
        if ($state==='failed') {
            $job->update(['status'=>'failed',
                'error_message'=>mb_substr((string)($body['message'] ?? 'FastAPI falló'),0,500),
                'heartbeat_at'=>now()]);
            return $job;
        }
        if ($state!=='completed') {
            $job->update(['heartbeat_at'=>now()]);
            return $job;
        }
        $result=$body['result'] ?? null;
        $this->registerCompleted($job,$result);
        return $job->fresh('artifacts');
    }

    private function registerCompleted(AnalysisJob $job, $result): void
    {
        if (!is_array($result) || ($result['status'] ?? '')!=='ok' ||
            ($result['analysis_uuid'] ?? '')!==$job->uuid ||
            ($result['analysis_version'] ?? '')!=='V0.8S') {
            throw new RuntimeException('Respuesta V0.8S inconsistente');
        }
        $mosaic=$job->mosaic;
        $prefix='projects/'.$mosaic->project->uuid.'/mosaics/'.$mosaic->uuid.
            '/analyses/'.$job->uuid.'/V08S/';
        if (($result['result_prefix'] ?? '')!==$prefix) {
            throw new RuntimeException('Prefijo R2 V0.8S incorrecto');
        }
        $arr=$result['artifacts'] ?? null;
        if (!is_array($arr) || count($arr)!==5) {
            throw new RuntimeException('Se esperaban 5 artefactos en V0.8S');
        }
        $types=[];
        $allow=['geojson','e2_geojson','sam2_geojson','qa_csv','manifest'];
        foreach($arr as $a) {
            $type=$a['type']??'';
            if (!in_array($type,$allow,true) || isset($types[$type])) throw new RuntimeException('Tipo de archivo duplicado o inesperado');
            $types[$type]=true;
            if (($a['verified']??false)!==true ||
                !preg_match('/^[0-9a-f]{64}$/', (string)($a['checksum_sha256']??'')) ||
                !str_starts_with((string)($a['object_key']??''),$prefix) ||
                (int)($a['size_bytes']??0) < 20) {
                throw new RuntimeException('Un artefacto no está íntegro o no pertenece a este análisis');
            }
        }
        if (count($types)!==5) throw new RuntimeException('Faltan tipos de artefactos');
        $summary=$result['summary'] ?? null;
        if (!is_array($summary) || ($summary['not_validated']??null)!==true ||
            (int)($summary['primary_objects']??0)<1) {
            throw new RuntimeException('Resumen científico de V0.8S inválido');
        }
        DB::transaction(function () use($job,$arr,$summary,$prefix,$result) {
            foreach ($arr as $a) {
                AnalysisArtifact::updateOrCreate(
                    ['analysis_job_id'=>$job->id,'type'=>$a['type']],
                    ['uuid'=>(string)Str::uuid(),'object_key'=>$a['object_key'],
                     'filename'=>$a['filename'],'mime_type'=>$a['mime_type'],
                     'size_bytes'=>(int)$a['size_bytes'],
                     'checksum_sha256'=>$a['checksum_sha256'],
                     'metadata'=>['pipeline_version'=>'V0.8S','verified'=>true,
                                  'not_validated'=>true,'purpose'=>$a['type']==='geojson'?'web_visualization_hybrid':'research_artifact']]
                );
            }
            $n=(int)($result['total_tiles']??0);
            $job->update(['status'=>'completed','progress'=>100,'summary'=>$summary,
                'total_tiles'=>$n,'processed_tiles'=>$n,'result_prefix'=>$prefix,
                'error_message'=>null,'completed_at'=>now(),'heartbeat_at'=>now()]);
        });
    }
}
