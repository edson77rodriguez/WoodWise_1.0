<?php

namespace App\Http\Controllers\API;

use App\Http\Controllers\Controller;
use App\Models\MobileSyncNotification;
use App\Models\Parcela;
use Carbon\Carbon;
use Illuminate\Http\Request;

class MobileDashboardController extends Controller
{
    public function summary(Request $request)
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
        // 2. PARCELAS SEGÚN ROL
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

            /*
             * Solo parcelas asignadas al Técnico.
             */
            $parcelas =
                $tecnico
                    ->parcelas()
                    ->withCount([
                        'arboles',
                        'trozas',
                    ])
                    ->get();


            /*
             * Solo sincronizaciones realizadas
             * por este Técnico.
             */
            $syncQuery =
                MobileSyncNotification::query()
                    ->where(
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

            /*
             * Solo parcelas pertenecientes
             * al Productor.
             */
            $parcelas =
                Parcela::query()
                    ->where(
                        'id_productor',
                        $productor->id_productor
                    )
                    ->withCount([
                        'arboles',
                        'trozas',
                    ])
                    ->get();


            /*
             * Solo sincronizaciones dirigidas
             * a este Productor.
             */
            $syncQuery =
                MobileSyncNotification::query()
                    ->where(
                        'id_productor',
                        $productor->id_productor
                    );

        } else {

            return response()->json([
                'message' =>
                    'El rol del usuario no puede consultar este dashboard.',
            ], 403);
        }


        // ============================================
        // 3. ESTADÍSTICAS FORESTALES
        // ============================================

        $totalParcelas =
            $parcelas->count();

        $totalArboles =
            (int) $parcelas->sum(
                'arboles_count'
            );

        $totalTrozas =
            (int) $parcelas->sum(
                'trozas_count'
            );

        $totalExtension =
            (float) $parcelas->sum(
                'extension'
            );


        // ============================================
        // 4. ESTADÍSTICAS DE SINCRONIZACIÓN
        // ============================================

        $totalSincronizaciones =
            (clone $syncQuery)
                ->count();

        $sincronizacionesNotificadas =
            (clone $syncQuery)
                ->where(
                    'status',
                    'notified'
                )
                ->count();

        $sincronizacionesFallidas =
            (clone $syncQuery)
                ->where(
                    'status',
                    'failed'
                )
                ->count();

        $sincronizacionesPendientes =
            (clone $syncQuery)
                ->where(
                    'status',
                    'pending'
                )
                ->count();


        // ============================================
        // 5. ÚLTIMA SINCRONIZACIÓN
        // ============================================

        $ultima =
            (clone $syncQuery)
                ->latest('created_at')
                ->first();

        $ultimaSincronizacion =
            null;

        if ($ultima) {

            $payload =
                $ultima->payload ?? [];

            $ultimaSincronizacion = [
                'sync_id' =>
                    $ultima->sync_id,

                'status' =>
                    $ultima->status,

                'attempts' =>
                    (int) $ultima->attempts,

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

                'created_at' =>
                    $ultima->created_at,

                'notified_at' =>
                    $ultima->notified_at,

                'has_error' =>
                    !empty(
                        $ultima->last_error
                    ),
            ];
        }


        // ============================================
        // 6. ACTIVIDAD DE LOS ÚLTIMOS 7 DÍAS
        // ============================================

        $inicio =
            now()
                ->subDays(6)
                ->startOfDay();

        $eventosRecientes =
            (clone $syncQuery)
                ->where(
                    'created_at',
                    '>=',
                    $inicio
                )
                ->get([
                    'created_at',
                    'status',
                ]);


        $actividad7Dias =
            collect(
                range(0, 6)
            )
            ->map(
                function ($offset)
                use ($eventosRecientes) {

                    $fecha =
                        Carbon::today()
                            ->subDays(
                                6 - $offset
                            );

                    $eventosDia =
                        $eventosRecientes
                            ->filter(
                                fn ($evento) =>
                                    $evento
                                        ->created_at
                                        ->toDateString()
                                    ===
                                    $fecha
                                        ->toDateString()
                            );

                    return [
                        'fecha' =>
                            $fecha->toDateString(),

                        'total' =>
                            $eventosDia->count(),

                        'notified' =>
                            $eventosDia
                                ->where(
                                    'status',
                                    'notified'
                                )
                                ->count(),

                        'failed' =>
                            $eventosDia
                                ->where(
                                    'status',
                                    'failed'
                                )
                                ->count(),
                    ];
                }
            )
            ->values();


        // ============================================
        // 7. RESPUESTA PARA REACT NATIVE
        // ============================================

        return response()->json([
            'data' => [

                'rol' =>
                    $rol,

                'forestal' => [
                    'total_parcelas' =>
                        $totalParcelas,

                    'total_arboles' =>
                        $totalArboles,

                    'total_trozas' =>
                        $totalTrozas,

                    'total_extension' =>
                        $totalExtension,

                    'total_elementos' =>
                        $totalArboles
                        +
                        $totalTrozas,
                ],

                'sincronizacion' => [
                    'total' =>
                        $totalSincronizaciones,

                    'notified' =>
                        $sincronizacionesNotificadas,

                    'failed' =>
                        $sincronizacionesFallidas,

                    'pending' =>
                        $sincronizacionesPendientes,

                    'ultima' =>
                        $ultimaSincronizacion,
                ],

                /*
                 * Directamente utilizable para
                 * una gráfica de actividad.
                 */
                'actividad_7_dias' =>
                    $actividad7Dias,

                /*
                 * Directamente utilizable para
                 * una gráfica Árboles vs Trozas.
                 */
                'distribucion_inventario' => [
                    [
                        'tipo' =>
                            'Árboles',

                        'total' =>
                            $totalArboles,
                    ],
                    [
                        'tipo' =>
                            'Trozas',

                        'total' =>
                            $totalTrozas,
                    ],
                ],
            ],
        ], 200);
    }
}