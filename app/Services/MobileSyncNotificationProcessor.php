<?php

namespace App\Services;

use App\Models\MobileSyncNotification;
use App\Models\Productor;
use Illuminate\Support\Facades\Http;
use Throwable;

class MobileSyncNotificationProcessor
{
    public function procesar(
        MobileSyncNotification $evento
    ): bool {
        /*
         * Si ya fue notificado, no debe enviarse
         * nuevamente.
         */
        if ($evento->status === 'notified') {
            return true;
        }

        $url =
            config('services.n8n_sync.url');

        $secret =
            config('services.n8n_sync.secret');

        if (!$url || !$secret) {
            $this->marcarFallido(
                $evento,
                'La configuración de n8n está incompleta.'
            );

            return false;
        }

        try {
            $productor =
                Productor::with('persona')
                    ->findOrFail(
                        $evento->id_productor
                    );

            $telefono =
                $productor->persona?->telefono;

            if (!$telefono) {
                throw new \RuntimeException(
                    'El Productor no tiene teléfono configurado.'
                );
            }

            $payloadGuardado =
                $evento->payload;

            $totalParcelas =
                (int) $payloadGuardado['total_parcelas'];

            $totalArboles =
                (int) $payloadGuardado['total_arboles'];

            $totalTrozas =
                (int) $payloadGuardado['total_trozas'];

            $totalRegistros =
                (int) $payloadGuardado['total_registros'];


            // ============================================
            // MENSAJE CONSTRUIDO POR LARAVEL
            // ============================================

            $mensaje =
                'Hola. SIGMAD informa que se ha completado '
                . 'una nueva sincronización de información forestal.'
                . "\n\n"
                . 'Parcelas actualizadas: '
                . $totalParcelas
                . '.'
                . "\n"
                . 'Árboles registrados: '
                . $totalArboles
                . '.'
                . "\n"
                . 'Trozas registradas: '
                . $totalTrozas
                . '.'
                . "\n"
                . 'Total de nuevos registros: '
                . $totalRegistros
                . '.';


            // ============================================
            // PAYLOAD HACIA N8N
            // ============================================

            $payload = [
                'event' =>
                    'sigmad.sync.completed',

                'version' =>
                    1,

                'sync_id' =>
                    $evento->sync_id,

                'id_tecnico' =>
                    (int) $evento->id_tecnico,

                'productor' => [
                    'id_productor' =>
                        (int) $evento->id_productor,

                    'telefono' =>
                        $telefono,
                ],

                /*
                 * Campos directos para simplificar
                 * el workflow de n8n.
                 */
                'telefono' =>
                    $telefono,

                'mensaje' =>
                    $mensaje,

                'total_parcelas' =>
                    $totalParcelas,

                'total_arboles' =>
                    $totalArboles,

                'total_trozas' =>
                    $totalTrozas,

                'total_registros' =>
                    $totalRegistros,

                /*
                 * Se conserva también el resumen
                 * estructurado para trazabilidad.
                 */
                'resumen' => [
                    'total_parcelas' =>
                        $totalParcelas,

                    'total_arboles' =>
                        $totalArboles,

                    'total_trozas' =>
                        $totalTrozas,

                    'total_registros' =>
                        $totalRegistros,
                ],

                'parcelas' =>
                    $payloadGuardado['parcelas'],
            ];


            /*
             * Registrar el intento antes de enviar.
             */
            $evento->increment('attempts');

            $evento->update([
                'last_error' => null,
            ]);


            $response =
                Http::withHeaders([
                    'X-SIGMAD-Secret' =>
                        $secret,

                    'X-SIGMAD-Sync-Id' =>
                        $evento->sync_id,
                ])
                ->acceptJson()
                ->timeout(20)
                ->post(
                    $url,
                    $payload
                );


            if (!$response->successful()) {
                throw new \RuntimeException(
                    'n8n respondió HTTP '
                    . $response->status()
                );
            }


            $respuesta =
                $response->json();


            /*
             * Un HTTP 200 no basta.
             *
             * n8n debe confirmar:
             *
             * {
             *   "ok": true,
             *   "notified": true
             * }
             */
            if (
                ($respuesta['ok'] ?? false) !== true
                ||
                ($respuesta['notified'] ?? false) !== true
            ) {
                throw new \RuntimeException(
                    'n8n respondió, pero no confirmó el envío de WhatsApp.'
                );
            }


            /*
             * Solo aquí consideramos terminada
             * la notificación.
             */
            $evento->update([
                'status' =>
                    'notified',

                'notified_at' =>
                    now(),

                'last_error' =>
                    null,
            ]);


            return true;

        } catch (Throwable $error) {

            $this->marcarFallido(
                $evento,
                $error->getMessage()
            );

            return false;
        }
    }


    private function marcarFallido(
        MobileSyncNotification $evento,
        string $mensaje
    ): void {
        $evento->update([
            'status' =>
                'failed',

            'last_error' =>
                mb_substr(
                    $mensaje,
                    0,
                    2000
                ),
        ]);
    }
}