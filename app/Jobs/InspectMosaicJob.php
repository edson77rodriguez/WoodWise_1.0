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

/** Ejecuta la inspección fuera del tiempo de espera HTTP de Hostinger. */
class InspectMosaicJob implements ShouldQueue
{
    use Dispatchable, InteractsWithQueue, Queueable, SerializesModels;

    public int $tries = 1;
    public bool $failOnTimeout = true;
    public int $timeout = 1200;

    public function __construct(public int $mosaicId) {}

    public function handle(MosaicController $controller, AiService $ai): void
    {
        $mosaic = Mosaic::find($this->mosaicId);
        if (!$mosaic) {
            return;
        }
        $controller->processInspectionJob($mosaic, $ai);
    }
    public function failed(?\Throwable $exception): void
    {
        Mosaic::whereKey($this->mosaicId)->where('status', 'inspecting')
            ->update(['status' => 'failed']);
        \Illuminate\Support\Facades\Log::error('Job de inspección falló', [
            'mosaic_id' => $this->mosaicId,
            'error' => $exception?->getMessage(),
        ]);
    }

}
