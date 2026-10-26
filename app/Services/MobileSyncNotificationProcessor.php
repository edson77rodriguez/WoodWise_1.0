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
         * Si ya fue notificado correctamente,
         * no debe enviarse nuevamente.
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
            // ============================================
            // 1. OBTENER PRODUCTOR Y TELÉFONO
            // ============================================

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


            // ============================================
            // 2. RECUPERAR PAYLOAD PERSISTIDO
            // ============================================

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
            // 3. CONSTRUIR MENSAJE EN LARAVEL
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
            // 4. CONSTRUIR PAYLOAD HACIA N8N
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
                 * Campos directos para simplificar n8n.
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
                 * También conservamos el resumen
                 * estructurado.
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

                /*
                 * Detalle de parcelas para trazabilidad.
                 */
                'parcelas' =>
                    $payloadGuardado['parcelas'],
            ];


            // ============================================
            // 5. REGISTRAR INTENTO
            // ============================================

            $evento->increment('attempts');

            $evento->update([
                'last_error' => null,
            ]);


            // ============================================
            // 6. ENVIAR A N8N
            // ============================================

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


            // ============================================
            // 7. VALIDAR RESPUESTA HTTP
            // ============================================

            if (!$response->successful()) {
                throw new \RuntimeException(
                    'n8n respondió HTTP '
                    . $response->status()
                );
            }

            $respuesta =
                $response->json();


            /*
             * HTTP 200 por sí solo no significa
             * que WhatsApp haya sido aceptado.
             *
             * n8n debe devolver:
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


            // ============================================
            // 8. MARCAR COMO NOTIFICADO
            // ============================================

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