<?php

namespace App\Console\Commands;

use App\Jobs\GenerateMosaicCogJob;
use App\Jobs\InspectMosaicJob;
use App\Models\Mosaic;
use Illuminate\Console\Command;
use Illuminate\Support\Carbon;
use Illuminate\Support\Facades\Bus;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Log;

/**
 * SIGMAD: ejecución protegida de mosaic-long y recuperación de fallas de red.
 * No reintenta inferencia V0.9U ni toca objetos científicos de R2.
 */
class SigmadPipelineTick extends Command
{
    protected $signature = 'sigmad:pipeline:tick
        {--dry-run : Solo diagnostica, no encola ni ejecuta trabajos}
        {--no-work : Recupera trabajos elegibles, sin ejecutar el worker}
        {--max-recovery=1 : Maximo de trabajos reencolados por ciclo}';

    protected $description = 'Protege la cola mosaic-long frente a caidas de Colab y recupera errores transitorios';

    private const MAX_RETRIES = 5;
    private const LOCK_SECONDS = 3700;
    private const BACKOFF_SECONDS = [0, 300, 900, 1800, 3600];

    public function handle(): int
    {
        $lock = Cache::lock('sigmad:pipeline:tick:v1', self::LOCK_SECONDS);
        if (!$lock->get()) {
            $this->line('SIGMAD: otro ciclo/worker sigue activo. No se inicia un segundo.');
            return self::SUCCESS;
        }

        try {
            if (!$this->serviceReady()) {
                $this->warn('SIGMAD: Colab/ngrok no disponible. La cola permanece intacta.');
                return self::SUCCESS;
            }

            $max = max(0, min(5, (int) $this->option('max-recovery')));
            $this->recoverTransientJobs($max, (bool) $this->option('dry-run'));

            if ($this->option('dry-run') || $this->option('no-work')) {
                return self::SUCCESS;
            }

            // Se procesa UN trabajo por ciclo; el lock cubre toda la ejecucion.
            // Debe cumplirse timeout del job < timeout del worker < retry_after.
            $this->line('SIGMAD: procesando hasta un trabajo de mosaic-long...');
            $exit = $this->call('queue:work', [
                'connection' => 'database_mosaic',
                '--queue' => 'mosaic-long',
                '--once' => true,
                '--tries' => 1,
                '--timeout' => 3600,
                '--sleep' => 3,
            ]);

            if ($exit !== 0) {
                Log::warning('SIGMAD: worker mosaic-long devolvio codigo no cero', ['exit' => $exit]);
            }
            return (int) $exit;
        } catch (\Throwable $e) {
            Log::error('SIGMAD: tick de recuperacion fallo', ['error' => $e->getMessage()]);
            $this->error('SIGMAD: '.$e->getMessage());
            return self::FAILURE;
        } finally {
            $lock->release();
        }
    }

    private function serviceReady(): bool
    {
        $base = rtrim((string) config('services.ai.url', ''), '/');
        if ($base === '' || !str_starts_with($base, 'https://')) {
            $this->warn('SIGMAD: services.ai.url no contiene una direccion HTTPS valida.');
            return false;
        }

        try {
            $response = Http::connectTimeout(5)
                ->timeout(15)
                ->withHeaders(['ngrok-skip-browser-warning' => 'true'])
                ->acceptJson()
                ->get($base.'/mosaic/analyze/v09u/health');

            $version = (string) ($response->json('version') ?? '');
            if (!$response->ok() || $version !== 'V0.9U-UNIFICADO-1') {
                $this->warn("SIGMAD: health no valido (HTTP {$response->status()}, version {$version}).");
                return false;
            }
            $this->info('SIGMAD: Colab operativo, version '.$version.'.');
            return true;
        } catch (\Throwable $e) {
            $this->warn('SIGMAD: fallo de conectividad: '.$e->getMessage());
            return false;
        }
    }

    private function recoverTransientJobs(int $max, bool $dryRun): void
    {
        if ($max === 0) {
            return;
        }

        $count = 0;
        // No se depura ni altera la cola existente. Solo se buscan fuentes fallidas.
        Mosaic::query()->whereIn('status', ['ready', 'failed'])
            ->orderBy('id')
            ->chunkById(50, function ($mosaics) use (&$count, $max, $dryRun) {
                foreach ($mosaics as $mosaic) {
                    if ($count >= $max) {
                        return false;
                    }
                    if ($this->recoverOne($mosaic, $dryRun)) {
                        $count++;
                    }
                }
                return true;
            });
        $this->line("SIGMAD: candidatos recuperados o diagnosticados: {$count}.");
    }

    private function recoverOne(Mosaic $mosaic, bool $dryRun): bool
    {
        $metadata = $this->arrayMetadata($mosaic);
        $kind = null;
        $error = '';

        // COG: el mosaico es valido y solamente fallo el derivado de visualizacion.
        if ($mosaic->status === 'ready'
            && data_get($metadata, 'web_cog_generation.status') === 'failed'
            && !filter_var(data_get($metadata, 'web_cog.verified', false), FILTER_VALIDATE_BOOLEAN)) {
            $kind = 'cog';
            $error = (string) data_get($metadata, 'web_cog_generation.error', '');
        }
        // Inspeccion: solo reintentar cuando el motivo registrado fue una desconexion.
        elseif ($mosaic->status === 'failed'
            && is_string($mosaic->object_key)
            && $mosaic->object_key !== ''
            && filled(data_get($metadata, 'inspection_failure.error'))) {
            $kind = 'inspect';
            $error = (string) data_get($metadata, 'inspection_failure.error');
        }

        if ($kind === null || !$this->transientNetworkError($error)) {
            return false;
        }

        $retryKey = 'sigmad_recovery.'.$kind;
        $count = (int) data_get($metadata, $retryKey.'.retry_count', 0);
        if ($count >= self::MAX_RETRIES) {
            return false;
        }
        $lastDispatch = data_get($metadata, $retryKey.'.last_dispatched_at');
        if ($lastDispatch) {
            try {
                $last = Carbon::parse((string) $lastDispatch);
                $delay = self::BACKOFF_SECONDS[min($count, count(self::BACKOFF_SECONDS) - 1)];
                if ($last->addSeconds($delay)->isFuture()) {
                    return false;
                }
            } catch (\Throwable $e) {
                // Metadata malformada: no debe habilitar reintentos ilimitados.
                Log::warning('SIGMAD: fecha de recuperacion malformada', ['mosaic_id' => $mosaic->id]);
                return false;
            }
        }

        if ($this->alreadyQueued($mosaic->id, $kind)) {
            $this->line("SIGMAD: {$kind} ya figura en cola para mosaico {$mosaic->id}; se evita duplicado.");
            return false;
        }

        if ($dryRun) {
            $this->line("SIMULACION: recuperaria {$kind} del mosaico {$mosaic->uuid}.");
            return true;
        }

        $newCount = $count + 1;
        data_set($metadata, $retryKey, [
            'retry_count' => $newCount,
            'last_dispatched_at' => now()->toIso8601String(),
            'last_error' => substr($error, 0, 500),
            'reason' => 'transient_network_failure',
        ]);

        // Se marca antes de encolar para que el worker nunca compita con
        // una escritura tardia que reemplace el estado generating/ok.
        if ($kind === 'cog') {
            data_set($metadata, 'web_cog_generation', [
                'status' => 'queued',
                'queued_at' => now()->toIso8601String(),
                'recovered_automatically' => true,
            ]);
            $mosaic->update(['metadata' => $metadata]);
            $job = new GenerateMosaicCogJob((int) $mosaic->id, false);
        } else {
            $mosaic->update(['metadata' => $metadata, 'status' => 'inspecting']);
            $job = new InspectMosaicJob((int) $mosaic->id);
        }

        try {
            $job->onConnection('database_mosaic')->onQueue('mosaic-long');
            Bus::dispatch($job);
            $this->info("SIGMAD: {$kind} recuperado automaticamente para mosaico {$mosaic->uuid} (reintento {$newCount}/".self::MAX_RETRIES.").");
            return true;
        } catch (\Throwable $e) {
            // Restauracion defensiva. El worker normal no se ejecuta hasta
            // finalizar la orden de encolado, protegida por el mismo lock.
            $mosaic->refresh();
            $rollback = $this->arrayMetadata($mosaic);
            if ($kind === 'cog' && data_get($rollback, 'web_cog_generation.status') === 'queued') {
                data_set($rollback, 'web_cog_generation', [
                    'status' => 'failed',
                    'attempted_at' => now()->toIso8601String(),
                    'error' => 'No se pudo registrar el reintento: '.$e->getMessage(),
                ]);
                $mosaic->update(['metadata' => $rollback]);
            } elseif ($kind === 'inspect' && $mosaic->status === 'inspecting') {
                $mosaic->update(['status' => 'failed']);
            }
            Log::error('SIGMAD: error al reencolar trabajo', [
                'kind' => $kind, 'mosaic_id' => $mosaic->id,
                'error' => $e->getMessage(),
            ]);
            return false;
        }
    }

    private function arrayMetadata(Mosaic $mosaic): array
    {
        $value = $mosaic->metadata;
        if (is_array($value)) {
            return $value;
        }
        $parsed = is_string($value) ? json_decode($value, true) : null;
        return is_array($parsed) ? $parsed : [];
    }

    private function transientNetworkError(string $message): bool
    {
        if ($message === '') {
            return false;
        }
        return (bool) preg_match(
            '/ERR_NGROK_3200|ngrok.{0,120}offline|'
            .'cURL error (?:6|7|18|28|35|52|56)\b|'
            .'could not resolve host|connection refused|connection reset|'
            .'connection timed out|timed out|timeout|'
            .'HTTP (?:429|500|502|503|504)\b|'
            .'bad gateway|service unavailable|gateway timeout|temporarily unavailable/i',
            $message
        );
    }

    private function alreadyQueued(int $mosaicId, string $kind): bool
    {
        $name = $kind === 'cog' ? 'GenerateMosaicCogJob' : 'InspectMosaicJob';
        $queueDb = config('queue.connections.database_mosaic.connection');
        $queueTable = (string) config('queue.connections.database_mosaic.table', 'jobs');
        foreach (DB::connection($queueDb)->table($queueTable)->where('queue', 'mosaic-long')->pluck('payload') as $raw) {
            $payload = json_decode((string) $raw, true);
            $command = $payload['data']['command'] ?? '';
            if (!is_string($command)
                || !str_contains($command, $name)) {
                continue;
            }
            // No se deserializa el payload: solo se inspecciona su formato.
            if (preg_match('/s:\d+:"mosaicId";i:'.preg_quote((string) $mosaicId, '/').';/', $command)) {
                return true;
            }
        }
        return false;
    }
}
