<?php

namespace App\Http\Controllers\API;

use App\Http\Controllers\Controller;
use App\Models\MobileSyncNotification;
use Illuminate\Http\Request;

class MobileSyncHistoryController extends Controller
{
    /**
     * Historial paginado de sincronizaciones
     * correspondiente al usuario autenticado.
     */
    public function index(Request $request)
    {
        // ============================================
        // 1. USUARIO AUTENTICADO
        // ============================================

        $user = $request->user()->load([
            'persona.rol',
            'persona.tecnico',
            'persona.productor',
        ]);

        $persona = $user->persona;

        if (!$persona || !$persona->rol) {
            return response()->json([
                'message' =>
                    'El usuario no tiene un perfil configurado correctamente.',
            ], 403);
        }

        $rol =
            $persona->rol->nom_rol;


        // ============================================
        // 2. CONSULTA BASE
        // ============================================

        $query =
            MobileSyncNotification::query()
                ->orderByDesc('created_at');


        // ============================================
        // 3. FILTRAR SEGÚN ROL
        // ============================================

        if ($rol === 'Tecnico') {

            $tecnico =
                $persona->tecnico;

            if (!$tecnico) {
                return response()->json([
                    'message' =>
                        'El usuario no tiene un perfil de técnico configurado.',
                ], 403);
            }

            $query->where(
                'id_tecnico',
                $tecnico->id_tecnico
            );

        } elseif ($rol === 'Productor') {

            $productor =
                $persona->productor;

            if (!$productor) {
                return response()->json([
                    'message' =>
                        'El usuario no tiene un perfil de productor configurado.',
                ], 403);
            }

            $query->where(
                'id_productor',
                $productor->id_productor
            );

        } else {

            return response()->json([
                'message' =>
                    'El rol del usuario no puede consultar el historial de sincronizaciones.',
            ], 403);
        }


        // ============================================
        // 4. LÍMITE
        // ============================================

        $limit =
            (int) $request->query(
                'limit',
                20
            );

        $limit =
            max(
                1,
                min(
                    $limit,
                    50
                )
            );


        // ============================================
        // 5. OBTENER REGISTROS
        // ============================================

        $eventos =
            $query
                ->limit($limit)
                ->get();


        // ============================================
        // 6. FORMATO PARA LA APP
        // ============================================

        $historial =
            $eventos
                ->map(
                    function (
                        MobileSyncNotification $evento
                    ) {

                        $payload =
                            $evento->payload ?? [];

                        return [
                            'id' =>
                                (int) $evento->id,

                            'sync_id' =>
                                $evento->sync_id,

                            'id_tecnico' =>
                                (int) $evento->id_tecnico,

                            'id_productor' =>
                                (int) $evento->id_productor,

                            'status' =>
                                $evento->status,

                            'attempts' =>
                                (int) $evento->attempts,

                            'total_parcelas' =>
                                (int) (
                                    $payload['total_parcelas']
                                    ?? 0
                                ),

                            'total_arboles' =>
                                (int) (
                                    $payload['total_arboles']
                                    ?? 0
                                ),

                            'total_trozas' =>
                                (int) (
                                    $payload['total_trozas']
                                    ?? 0
                                ),

                            'total_registros' =>
                                (int) (
                                    $payload['total_registros']
                                    ?? 0
                                ),

                            'notified_at' =>
                                $evento->notified_at,

                            'created_at' =>
                                $evento->created_at,

                            /*
                             * No exponemos detalles técnicos
                             * innecesarios al usuario final.
                             */
                            'has_error' =>
                                !empty(
                                    $evento->last_error
                                ),
                        ];
                    }
                )
                ->values();


        return response()->json([
            'data' =>
                $historial,

            'meta' => [
                'count' =>
                    $historial->count(),

                'limit' =>
                    $limit,
            ],
        ], 200);
    }


    /**
     * Última sincronización correspondiente
     * al usuario autenticado.
     */
    public function latest(Request $request)
    {
        // ============================================
        // 1. USUARIO
        // ============================================

        $user = $request->user()->load([
            'persona.rol',
            'persona.tecnico',
            'persona.productor',
        ]);

        $persona = $user->persona;

        if (!$persona || !$persona->rol) {
            return response()->json([
                'message' =>
                    'El usuario no tiene un perfil configurado correctamente.',
            ], 403);
        }

        $rol =
            $persona->rol->nom_rol;


        // ============================================
        // 2. CONSULTA
        // ============================================

        $query =
            MobileSyncNotification::query()
                ->orderByDesc('created_at');


        if ($rol === 'Tecnico') {

            $tecnico =
                $persona->tecnico;

            if (!$tecnico) {
                return response()->json([
                    'message' =>
                        'El usuario no tiene un perfil de técnico configurado.',
                ], 403);
            }

            $query->where(
                'id_tecnico',
                $tecnico->id_tecnico
            );

        } elseif ($rol === 'Productor') {

            $productor =
                $persona->productor;

            if (!$productor) {
                return response()->json([
                    'message' =>
                        'El usuario no tiene un perfil de productor configurado.',
                ], 403);
            }

            $query->where(
                'id_productor',
                $productor->id_productor
            );

        } else {

            return response()->json([
                'message' =>
                    'El rol del usuario no puede consultar sincronizaciones.',
            ], 403);
        }


        // ============================================
        // 3. ÚLTIMO EVENTO
        // ============================================

        $evento =
            $query->first();


        if (!$evento) {
            return response()->json([
                'data' => null,
                'message' =>
                    'No existen sincronizaciones registradas.',
            ], 200);
        }


        $payload =
            $evento->payload ?? [];


        return response()->json([
            'data' => [
                'sync_id' =>
                    $evento->sync_id,

                'status' =>
                    $evento->status,

                'attempts' =>
                    (int) $evento->attempts,

                'total_parcelas' =>
                    (int) (
                        $payload['total_parcelas']
                        ?? 0
                    ),

                'total_arboles' =>
                    (int) (
                        $payload['total_arboles']
                        ?? 0
                    ),

                'total_trozas' =>
                    (int) (
                        $payload['total_trozas']
                        ?? 0
                    ),

                'total_registros' =>
                    (int) (
                        $payload['total_registros']
                        ?? 0
                    ),

                'notified_at' =>
                    $evento->notified_at,

                'created_at' =>
                    $evento->created_at,

                'has_error' =>
                    !empty(
                        $evento->last_error
                    ),
            ],
        ], 200);
    }
}