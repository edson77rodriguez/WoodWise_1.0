<?php

namespace App\Services;

use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\Http;
use RuntimeException;

class AiService
{
    private string $baseUrl;


    public function __construct()
    {
        $url = config('services.ai.url');

        if (!$url) {
            throw new RuntimeException(
                'AI_API_URL no está configurada.'
            );
        }

        $this->baseUrl = rtrim($url, '/');
    }


    /**
     * Verificar disponibilidad de FastAPI.
     */
    public function health(): array
    {
        $response = Http::timeout(10)
            ->acceptJson()
            ->get(
                $this->baseUrl . '/health'
            );

        $response->throw();

        $data = $response->json();

        if (!is_array($data)) {
            throw new RuntimeException(
                'Respuesta inválida del servicio de IA.'
            );
        }

        return $data;
    }


    /**
     * Inferencia YOLO-Seg.
     */
    public function predictYolo(
        UploadedFile $image,
        float $threshold = 0.25
    ): array {

        return $this->sendImage(
            '/predict-yolo',
            $image,
            [
                'threshold' => $threshold,
            ]
        );
    }


    /**
     * Inferencia Mask R-CNN.
     */
    public function predictMaskRcnn(
        UploadedFile $image,
        float $threshold = 0.40
    ): array {

        return $this->sendImage(
            '/predict-maskrcnn',
            $image,
            [
                'threshold' => $threshold,
            ]
        );
    }


    /**
     * Comparación YOLO-Seg vs Mask R-CNN.
     */
    public function compareModels(
        UploadedFile $image,
        float $yoloThreshold = 0.25,
        float $maskrcnnThreshold = 0.40,
        float $matchingIou = 0.50
    ): array {

        return $this->sendImage(
            '/compare-models',
            $image,
            [
                'yolo_threshold' =>
                    $yoloThreshold,

                'maskrcnn_threshold' =>
                    $maskrcnnThreshold,

                'matching_iou' =>
                    $matchingIou,
            ]
        );
    }


    /**
     * Método común para enviar imágenes
     * mediante multipart/form-data.
     */
    private function sendImage(
        string $endpoint,
        UploadedFile $image,
        array $parameters = []
    ): array {

        $stream = fopen(
            $image->getRealPath(),
            'r'
        );

        if ($stream === false) {
            throw new RuntimeException(
                'No fue posible abrir la imagen.'
            );
        }

        try {

            $response = Http::timeout(240)
                ->connectTimeout(20)
                ->acceptJson()
                ->attach(
                    'image',
                    $stream,
                    $image->getClientOriginalName()
                )
                ->post(
                    $this->baseUrl . $endpoint,
                    $parameters
                );

            $response->throw();

            $data = $response->json();

            if (!is_array($data)) {
                throw new RuntimeException(
                    'El servicio de IA devolvió una respuesta inválida.'
                );
            }

            return $data;

        } finally {

            if (is_resource($stream)) {
                fclose($stream);
            }
        }
    }



    public function inspectMosaic(
    string $mosaicUuid,
    string $objectKey
): array
{
    $response = Http::timeout(300)
        ->connectTimeout(20)
        ->acceptJson()
        ->post(
            $this->baseUrl . '/mosaic/inspect',
            [
                'mosaic_uuid' => $mosaicUuid,
                'object_key' => $objectKey,
            ]
        );

    $response->throw();

    return $response->json();
}


public function generateMosaicPreview(
    string $mosaicUuid,
    string $objectKey
): array
{
    $response = Http::timeout(300)
        ->connectTimeout(20)
        ->acceptJson()
        ->post(
            $this->baseUrl
            . '/mosaic/preview',
            [
                'mosaic_uuid' =>
                    $mosaicUuid,

                'object_key' =>
                    $objectKey,

                'max_width' =>
                    1400,

                'max_height' =>
                    1000,

                'jpeg_quality' =>
                    88,
            ]
        );

    $response->throw();

    return $response->json();
}



public function generateMosaicTilingPreview(
    string $mosaicUuid,
    string $objectKey
): array
{
    $response = Http::timeout(300)
        ->connectTimeout(20)
        ->acceptJson()
        ->post(
            $this->baseUrl . '/mosaic/tiling/preview',
            [
                'mosaic_uuid' => $mosaicUuid,
                'object_key' => $objectKey,
                'tile_size' => 1024,
                'overlap' => 256,
                'max_width' => 1400,
                'max_height' => 1000,
                'jpeg_quality' => 90,
            ]
        );

    $response->throw();

    return $response->json();
}


/**
 * Ejecutar análisis wall-to-wall completo
 * sobre un ortomosaico almacenado en R2.
 */
public function analyzeMosaicWallToWall(
    string $analysisUuid,
    string $mosaicUuid,
    string $objectKey,
    string $projectUuid
): array {

    $response = Http::timeout(900)
        ->connectTimeout(30)
        ->acceptJson()
        ->post(
            $this->baseUrl . '/mosaic/analyze/wall-to-wall',
            [
                'analysis_uuid' => $analysisUuid,
                'mosaic_uuid' => $mosaicUuid,
                'object_key' => $objectKey,
                'project_uuid' => $projectUuid,
                'analysis_version' => 'V0.7E',

                'configuration' => [
                    'source_size' => 2144,
                    'output_size' => 1024,
                    'output_overlap' => 256,

                    'yolo_threshold' => 0.25,
                    'maskrcnn_threshold' => 0.40,
                    'mask_threshold' => 0.50,

                    'intramodel_iou' => 0.50,
                    'intermodel_iou' => 0.50,
                ],
            ]
        );

    if (!$response->successful()) {

        throw new RuntimeException(
            'Error en análisis wall-to-wall. '
            . 'HTTP '
            . $response->status()
            . ': '
            . $response->body()
        );
    }

    $data = $response->json();

    if (
        !is_array($data)
        ||
        ($data['status'] ?? null) !== 'ok'
    ) {

        throw new RuntimeException(
            'FastAPI devolvió una respuesta '
            . 'wall-to-wall inválida.'
        );
    }

    return $data;
}
}