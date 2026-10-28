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
        // 1. CONFIGURACIÓN
        // ============================================

        $timezone =
            'America/Mexico_City';

        $ahoraLocal =
            Carbon::now($timezone);


        // ============================================
        // 2. USUARIO AUTENTICADO
        // ============================================

        $user = $request->user()->load([
            'persona.rol',
            'persona.tecnico',
            'persona.productor',
        ]);

        $persona =
            $user->persona;

        if (!$persona || !$persona->rol) {
            return response()->json([
                'message' =>
                    'El usuario no tiene un perfil configurado correctamente.',
            ], 403);
        }

        $rol =
            $persona->rol->nom_rol;


        // ============================================
        // 3. PARCELAS Y SINCRONIZACIONES SEGÚN ROL
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
             *
             * También cargamos las estimaciones que
             * ya existen en MySQL.
             *
             * NO recalculamos fórmulas aquí.
             */
            $parcelas =
                $tecnico
                    ->parcelas()
                    ->with([
                        'trozas.estimaciones.tipoEstimacion',
                        'arboles.estimaciones.tipoEstimacion',
                    ])
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
             * al Productor autenticado.
             */
            $parcelas =
                Parcela::query()
                    ->where(
                        'id_productor',
                        $productor->id_productor
                    )
                    ->with([
                        'trozas.estimaciones.tipoEstimacion',
                        'arboles.estimaciones.tipoEstimacion',
                    ])
                    ->withCount([
                        'arboles',
                        'trozas',
                    ])
                    ->get();


            /*
             * Solo sincronizaciones asociadas
             * al Productor autenticado.
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
        // 4. ESTADÍSTICAS POR PARCELA
        // ============================================

        $estadisticasParcelas =
            $parcelas
                ->map(
                    function ($parcela) {

                        $volumenParcela =
                            0.0;

                        $biomasaParcela =
                            0.0;

                        $carbonoParcela =
                            0.0;


                        // --------------------------------
                        // TROZAS
                        // --------------------------------

                        foreach (
                            $parcela->trozas
                            as $troza
                        ) {

                            $volumen =
                                $troza
                                    ->estimaciones
                                    ->where(
                                        'tipoEstimacion.desc_estimacion',
                                        'Volumen'
                                    )
                                    ->first();

                            if ($volumen) {
                                $volumenParcela +=
                                    (float) $volumen->calculo;
                            }
                        }


                        // --------------------------------
                        // ÁRBOLES
                        // --------------------------------

                        foreach (
                            $parcela->arboles
                            as $arbol
                        ) {

                            $volumen =
                                $arbol
                                    ->estimaciones
                                    ->where(
                                        'tipoEstimacion.desc_estimacion',
                                        'Volumen'
                                    )
                                    ->first();

                            $biomasa =
                                $arbol
                                    ->estimaciones
                                    ->where(
                                        'tipoEstimacion.desc_estimacion',
                                        'Biomasa'
                                    )
                                    ->first();

                            $carbono =
                                $arbol
                                    ->estimaciones
                                    ->where(
                                        'tipoEstimacion.desc_estimacion',
                                        'Carbono'
                                    )
                                    ->first();


                            if ($volumen) {
                                $volumenParcela +=
                                    (float) $volumen->calculo;
                            }

                            if ($biomasa) {
                                $biomasaParcela +=
                                    (float) $biomasa->calculo;
                            }

                            if ($carbono) {
                                $carbonoParcela +=
                                    (float) $carbono->calculo;
                            }
                        }


                        return [
                            'id_parcela' =>
                                (int) $parcela->id_parcela,

                            'nom_parcela' =>
                                $parcela->nom_parcela,

                            'extension' =>
                                (float) (
                                    $parcela->extension
                                    ?? 0
                                ),

                            'arboles' =>
                                (int) $parcela->arboles_count,

                            'trozas' =>
                                (int) $parcela->trozas_count,

                            'total_elementos' =>
                                (int) $parcela->arboles_count
                                +
                                (int) $parcela->trozas_count,

                            /*
                             * Valores ya calculados y
                             * persistidos en MySQL.
                             */
                            'volumen' =>
                                $volumenParcela,

                            'biomasa' =>
                                $biomasaParcela,

                            'carbono' =>
                                $carbonoParcela,
                        ];
                    }
                )
                ->values();


        // ============================================
        // 5. TOTALES FORESTALES
        // ============================================

        $totalParcelas =
            $estadisticasParcelas->count();

        $totalArboles =
            (int) $estadisticasParcelas
                ->sum('arboles');

        $totalTrozas =
            (int) $estadisticasParcelas
                ->sum('trozas');

        $totalExtension =
            (float) $estadisticasParcelas
                ->sum('extension');

        $totalVolumen =
            (float) $estadisticasParcelas
                ->sum('volumen');

        $totalBiomasa =
            (float) $estadisticasParcelas
                ->sum('biomasa');

        $totalCarbono =
            (float) $estadisticasParcelas
                ->sum('carbono');


        // ============================================
        // 6. OBTENER EVENTOS DE SINCRONIZACIÓN
        // ============================================

        $eventosSync =
            (clone $syncQuery)
                ->orderByDesc('created_at')
                ->get([
                    'id',
                    'sync_id',
                    'status',
                    'attempts',
                    'payload',
                    'notified_at',
                    'last_error',
                    'created_at',
                ]);


        // ============================================
        // 7. AGRUPAR POR sync_id
        //
        // Un mismo sync_id puede generar una fila por
        // Productor. Para el Dashboard debe contar como
        // UNA sincronización.
        // ============================================

        $sincronizaciones =
            $eventosSync
                ->groupBy('sync_id')
                ->map(
                    function (
                        $eventos,
                        $syncId
                    ) {

                        $estados =
                            $eventos
                                ->pluck('status');


                        /*
                         * Estado consolidado:
                         *
                         * - si alguna notificación falló:
                         *   failed
                         *
                         * - si todas están notified:
                         *   notified
                         *
                         * - cualquier otro caso:
                         *   pending
                         */
                        if (
                            $estados->contains(
                                'failed'
                            )
                        ) {

                            $estado =
                                'failed';

                        } elseif (
                            $estados->every(
                                fn ($estadoEvento) =>
                                    $estadoEvento
                                    ===
                                    'notified'
                            )
                        ) {

                            $estado =
                                'notified';

                        } else {

                            $estado =
                                'pending';
                        }


                        $ultimoEvento =
                            $eventos
                                ->sortByDesc(
                                    fn ($evento) =>
                                        $evento
                                            ->created_at
                                            ?->timestamp
                                        ?? 0
                                )
                                ->first();


                        $ultimoNotificado =
                            $eventos
                                ->filter(
                                    fn ($evento) =>
                                        $evento->notified_at
                                        !==
                                        null
                                )
                                ->sortByDesc(
                                    fn ($evento) =>
                                        $evento->notified_at
                                            ? Carbon::parse(
                                                $evento->notified_at
                                            )->timestamp
                                            : 0
                                )
                                ->first();


                        return [
                            'sync_id' =>
                                $syncId,

                            'status' =>
                                $estado,

                            /*
                             * Si el evento incluye varios
                             * Productores tomamos el mayor
                             * número de intentos.
                             */
                            'attempts' =>
                                (int) $eventos
                                    ->max('attempts'),

                            /*
                             * Los payloads son por Productor.
                             * Se suman para obtener el total
                             * real del sync completo.
                             */
                            'total_parcelas' =>
                                (int) $eventos
                                    ->sum(
                                        fn ($evento) =>
                                            (int) (
                                                ($evento->payload ?? [])
                                                ['total_parcelas']
                                                ?? 0
                                            )
                                    ),

                            'total_arboles' =>
                                (int) $eventos
                                    ->sum(
                                        fn ($evento) =>
                                            (int) (
                                                ($evento->payload ?? [])
                                                ['total_arboles']
                                                ?? 0
                                            )
                                    ),

                            'total_trozas' =>
                                (int) $eventos
                                    ->sum(
                                        fn ($evento) =>
                                            (int) (
                                                ($evento->payload ?? [])
                                                ['total_trozas']
                                                ?? 0
                                            )
                                    ),

                            'total_registros' =>
                                (int) $eventos
                                    ->sum(
                                        fn ($evento) =>
                                            (int) (
                                                ($evento->payload ?? [])
                                                ['total_registros']
                                                ?? 0
                                            )
                                    ),

                            /*
                             * Número de Productores que
                             * participaron en ese sync.
                             */
                            'notificaciones' =>
                                $eventos->count(),

                            'created_at' =>
                                $ultimoEvento
                                    ?->created_at,

                            'notified_at' =>
                                $ultimoNotificado
                                    ?->notified_at,

                            'has_error' =>
                                $eventos
                                    ->contains(
                                        fn ($evento) =>
                                            !empty(
                                                $evento->last_error
                                            )
                                    ),
                        ];
                    }
                )
                ->sortByDesc(
                    fn ($sync) =>
                        $sync['created_at']
                            ? Carbon::parse(
                                $sync['created_at']
                            )->timestamp
                            : 0
                )
                ->values();


        // ============================================
        // 8. ESTADÍSTICAS DE SINCRONIZACIÓN
        // ============================================

        $totalSincronizaciones =
            $sincronizaciones->count();

        $sincronizacionesNotificadas =
            $sincronizaciones
                ->where(
                    'status',
                    'notified'
                )
                ->count();

        $sincronizacionesFallidas =
            $sincronizaciones
                ->where(
                    'status',
                    'failed'
                )
                ->count();

        $sincronizacionesPendientes =
            $sincronizaciones
                ->where(
                    'status',
                    'pending'
                )
                ->count();


        $porcentajeExito =
            $totalSincronizaciones > 0
                ? round(
                    (
                        $sincronizacionesNotificadas
                        /
                        $totalSincronizaciones
                    )
                    * 100,
                    1
                )
                : 0.0;


        /*
         * Total de registros pertenecientes a
         * sincronizaciones completadas.
         */
        $totalRegistrosSincronizados =
            (int) $sincronizaciones
                ->where(
                    'status',
                    'notified'
                )
                ->sum(
                    'total_registros'
                );


        // ============================================
        // 9. ÚLTIMA SINCRONIZACIÓN
        // ============================================

        $ultimaSincronizacion =
            $sincronizaciones
                ->first();


        // ============================================
        // 10. ACTIVIDAD DE LOS ÚLTIMOS 7 DÍAS
        //
        // Se convierte cada timestamp almacenado a
        // America/Mexico_City antes de agruparlo.
        // ============================================

        $actividad7Dias =
            collect(
                range(0, 6)
            )
            ->map(
                function ($offset)
                use (
                    $sincronizaciones,
                    $ahoraLocal,
                    $timezone
                ) {

                    $fecha =
                        $ahoraLocal
                            ->copy()
                            ->subDays(
                                6 - $offset
                            )
                            ->startOfDay();


                    $sincronizacionesDia =
                        $sincronizaciones
                            ->filter(
                                function ($sync)
                                use (
                                    $fecha,
                                    $timezone
                                ) {

                                    if (
                                        empty(
                                            $sync['created_at']
                                        )
                                    ) {
                                        return false;
                                    }

                                    $fechaSync =
                                        Carbon::parse(
                                            $sync['created_at']
                                        )
                                        ->timezone(
                                            $timezone
                                        )
                                        ->toDateString();

                                    return
                                        $fechaSync
                                        ===
                                        $fecha
                                            ->toDateString();
                                }
                            );


                    return [
                        'fecha' =>
                            $fecha->toDateString(),

                        'total' =>
                            $sincronizacionesDia
                                ->count(),

                        'notified' =>
                            $sincronizacionesDia
                                ->where(
                                    'status',
                                    'notified'
                                )
                                ->count(),

                        'failed' =>
                            $sincronizacionesDia
                                ->where(
                                    'status',
                                    'failed'
                                )
                                ->count(),

                        'pending' =>
                            $sincronizacionesDia
                                ->where(
                                    'status',
                                    'pending'
                                )
                                ->count(),
                    ];
                }
            )
            ->values();


        // ============================================
        // 11. DISTRIBUCIÓN DEL INVENTARIO
        //
        // Lista preparada para una gráfica.
        // ============================================

        $distribucionInventario = [
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
        ];


        // ============================================
        // 12. RESPUESTA PARA REACT NATIVE
        // ============================================

        return response()->json([
            'data' => [

                'rol' =>
                    $rol,


                // ====================================
                // RESUMEN FORESTAL
                // ====================================

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

                    'total_volumen' =>
                        $totalVolumen,

                    'total_biomasa' =>
                        $totalBiomasa,

                    'total_carbono' =>
                        $totalCarbono,
                ],


                // ====================================
                // SINCRONIZACIÓN
                // ====================================

                'sincronizacion' => [

                    'total' =>
                        $totalSincronizaciones,

                    'notified' =>
                        $sincronizacionesNotificadas,

                    'failed' =>
                        $sincronizacionesFallidas,

                    'pending' =>
                        $sincronizacionesPendientes,

                    'porcentaje_exito' =>
                        $porcentajeExito,

                    'total_registros_sincronizados' =>
                        $totalRegistrosSincronizados,

                    'ultima' =>
                        $ultimaSincronizacion,
                ],


                // ====================================
                // DATOS PARA GRÁFICAS
                // ====================================

                'actividad_7_dias' =>
                    $actividad7Dias,

                'distribucion_inventario' =>
                    $distribucionInventario,

                /*
                 * Puede utilizarse para una gráfica
                 * comparativa por parcela.
                 */
                'parcelas_estadisticas' =>
                    $estadisticasParcelas,


                // ====================================
                // METADATOS
                // ====================================

                'meta' => [

                    'timezone' =>
                        $timezone,

                    'generado_at' =>
                        $ahoraLocal
                            ->toIso8601String(),
                ],
            ],
        ], 200);
    }
}