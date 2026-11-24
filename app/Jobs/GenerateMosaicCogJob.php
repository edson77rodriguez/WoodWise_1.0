<?php

namespace App\Jobs;

use App\Http\Controllers\MosaicController;
use App\Models\Mosaic;
use App\Services\AiService;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Queue\SerializesModels;

/** Genera/reutiliza COG sin que el navegador espere y sin invalidar el original. */
class GenerateMosaicCogJob implements ShouldQueue
{
    use Dispatchable, InteractsWithQueue, Queueable, SerializesModels;

    public int $tries = 1;
    public bool $failOnTimeout = true;
    public int $timeout = 3500;

    public function __construct(public int $mosaicId, public bool $force = false) {}

    public function handle(MosaicController $controller, AiService $ai): void
    {
        $mosaic = Mosaic::find($this->mosaicId);
        if (!$mosaic) {
            return;
        }
        $controller->processCogJob($mosaic, $ai, $this->force);
    }
    public function failed(?\Throwable $exception): void
    {
        $mosaic = Mosaic::find($this->mosaicId);
        if (!$mosaic) return;
        $metadata = is_array($mosaic->metadata) ? $mosaic->metadata : [];
        $metadata['web_cog_generation'] = [
            'status' => 'failed', 'error' => $exception?->getMessage(),
            'attempted_at' => now()->toIso8601String(),
        ];
        $update = ['metadata' => $metadata];
        if ($mosaic->status === 'processing') $update['status'] = 'ready';
        $mosaic->update($update);
        \Illuminate\Support\Facades\Log::warning('Job de COG falló', [
            'mosaic_id' => $this->mosaicId,
            'error' => $exception?->getMessage(),
        ]);
    }

}
