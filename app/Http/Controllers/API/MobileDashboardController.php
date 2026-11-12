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

        //* ============================================*

        //* 1. CONFIGURACIÓN*

        //* ============================================*



        $timezone =

            'America/Mexico_City';



        $ahoraLocal =

            Carbon::now($timezone);





        //* ============================================*

        //* 2. USUARIO AUTENTICADO*

        //* ============================================*



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





        //* ============================================*

        //* 3. PARCELAS Y SINCRONIZACIONES SEGÚN ROL*

        //* ============================================*



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

*             * Solo parcelas asignadas al Técnico.*

*             **

*             * También cargamos las estimaciones que*

*             * ya existen en MySQL.*

*             **

*             * NO recalculamos fórmulas aquí.*

*             **/

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

*             * Solo sincronizaciones realizadas*

*             * por este Técnico.*

*             **/

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

*             * Solo parcelas pertenecientes*

*             * al Productor autenticado.*

*             **/

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

*             * Solo sincronizaciones asociadas*

*             * al Productor autenticado.*

*             **/

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





        //* ============================================*

        //* ============================================
        //* 4. ESTADÍSTICAS FORESTALES POR PARCELA*
        //* ============================================

        /*
         * REGLAS DEL DASHBOARD
         *
         * VOLUMEN MADERABLE
         * - Fórmulas 1 a 4 son métodos alternativos.
         * - NO se suman entre sí.
         * - Para cada combinación troza + fórmula se
         *   conserva únicamente la estimación más reciente.
         *
         * BIOMASA / CARBONO
         * - Fórmulas 5 a 8 corresponden a biomasa
         *   según la especie.
         * - Se toma una estimación vigente por árbol.
         * - Biomasa sale de la columna biomasa.
         * - Carbono sale de la columna carbono.
         *
         * El Dashboard NO recalcula fórmulas.
         * Consume valores ya persistidos en MySQL.
         */

        $metodosVolumen = [
            1 => [
                'clave' => 'huber',
                'nombre' => 'Huber',
            ],
            2 => [
                'clave' => 'smalian',
                'nombre' => 'Smalian',
            ],
            3 => [
                'clave' => 'tronco_cono',
                'nombre' => 'Tronco de Cono',
            ],
            4 => [
                'clave' => 'newton',
                'nombre' => 'Newton',
            ],
        ];


        $estadisticasParcelas =
            $parcelas
                ->map(
                    function ($parcela) use (
                        $metodosVolumen
                    ) {

                        // --------------------------------
                        // VOLUMEN MADERABLE POR MÉTODO
                        // --------------------------------

                        $volumenPorMetodo = [];

                        foreach (
                            $metodosVolumen
                            as $idFormula => $metodo
                        ) {

                            $volumenPorMetodo[
                                $metodo['clave']
                            ] = [
                                'id_formula' =>
                                    $idFormula,

                                'nombre' =>
                                    $metodo['nombre'],

                                'trozas' =>
                                    0,

                                'volumen' =>
                                    0.0,
                            ];
                        }


                        foreach (
                            $parcela->trozas
                            as $troza
                        ) {

                            /*
                             * Una troza puede tener varias
                             * estimaciones del mismo método
                             * debido a una recalculación.
                             *
                             * Se conserva la estimación con
                             * mayor id_estimacion.
                             */

                            $ultimasPorFormula =
                                $troza
                                    ->estimaciones
                                    ->filter(
                                        fn ($estimacion) =>
                                            isset(
                                                $metodosVolumen[
                                                    (int) $estimacion
                                                        ->id_formula
                                                ]
                                            )
                                    )
                                    ->groupBy(
                                        fn ($estimacion) =>
                                            (int) $estimacion
                                                ->id_formula
                                    )
                                    ->map(
                                        fn ($estimaciones) =>
                                            $estimaciones
                                                ->sortByDesc(
                                                    fn ($estimacion) =>
                                                        (int) $estimacion
                                                            ->id_estimacion
                                                )
                                                ->first()
                                    );


                            foreach (
                                $ultimasPorFormula
                                as $idFormula => $estimacion
                            ) {

                                $metodo =
                                    $metodosVolumen[
                                        (int) $idFormula
                                    ];


                                $clave =
                                    $metodo['clave'];


                                $volumenPorMetodo[
                                    $clave
                                ]['trozas']++;


                                $volumenPorMetodo[
                                    $clave
                                ]['volumen'] +=
                                    (float) (
                                        $estimacion->calculo
                                        ?? 0
                                    );
                            }
                        }


                        // --------------------------------
                        // BIOMASA Y CARBONO
                        // --------------------------------

                        $biomasaParcela =
                            0.0;

                        $carbonoParcela =
                            0.0;


                        foreach (
                            $parcela->arboles
                            as $arbol
                        ) {

                            /*
                             * Fórmulas 5 a 8:
                             * biomasa según especie.
                             *
                             * Actualmente existe una
                             * estimación por árbol.
                             *
                             * Si existe una recalculación
                             * futura, se toma la de mayor
                             * id_estimacion1.
                             */

                            $estimacionBiomasa =
                                $arbol
                                    ->estimaciones
                                    ->filter(
                                        fn ($estimacion) =>
                                            in_array(
                                                (int) $estimacion
                                                    ->id_formula,
                                                [5, 6, 7, 8],
                                                true
                                            )
                                    )
                                    ->sortByDesc(
                                        fn ($estimacion) =>
                                            (int) $estimacion
                                                ->id_estimacion1
                                    )
                                    ->first();


                            if (
                                !$estimacionBiomasa
                            ) {
                                continue;
                            }


                            $biomasaParcela +=
                                (float) (
                                    $estimacionBiomasa
                                        ->biomasa
                                    ?? 0
                                );


                            $carbonoParcela +=
                                (float) (
                                    $estimacionBiomasa
                                        ->carbono
                                    ?? 0
                                );
                        }


                        foreach (
                            $volumenPorMetodo
                            as &$metodo
                        ) {

                            $metodo['volumen'] =
                                round(
                                    (float) $metodo[
                                        'volumen'
                                    ],
                                    10
                                );
                        }

                        unset($metodo);


                        return [
                            'id_parcela' =>
                                (int) $parcela
                                    ->id_parcela,

                            'nom_parcela' =>
                                $parcela
                                    ->nom_parcela,

                            'extension' =>
                                (float) (
                                    $parcela->extension
                                    ?? 0
                                ),

                            'arboles' =>
                                (int) $parcela
                                    ->arboles_count,

                            'trozas' =>
                                (int) $parcela
                                    ->trozas_count,

                            'total_elementos' =>
                                (int) $parcela
                                    ->arboles_count
                                +
                                (int) $parcela
                                    ->trozas_count,

                            /*
                             * Campo heredado para no romper
                             * el contrato móvil actual.
                             *
                             * NO debe mostrarse como un
                             * "volumen total".
                             */
                            'volumen' =>
                                0.0,

                            'volumen_maderable' =>
                                $volumenPorMetodo,

                            'biomasa' =>
                                round(
                                    $biomasaParcela,
                                    10
                                ),

                            'carbono' =>
                                round(
                                    $carbonoParcela,
                                    10
                                ),
                        ];
                    }
                )
                ->values();





        //* ============================================*
        //* 5. TOTALES FORESTALES*
        //* ============================================

        $totalParcelas =
            $estadisticasParcelas
                ->count();


        $totalArboles =
            (int) $estadisticasParcelas
                ->sum('arboles');


        $totalTrozas =
            (int) $estadisticasParcelas
                ->sum('trozas');


        $totalExtension =
            (float) $estadisticasParcelas
                ->sum('extension');


        $totalBiomasa =
            round(
                (float) $estadisticasParcelas
                    ->sum('biomasa'),
                10
            );


        $totalCarbono =
            round(
                (float) $estadisticasParcelas
                    ->sum('carbono'),
                10
            );


        /*
         * Volumen maderable global por método.
         *
         * No existe un total único porque Huber,
         * Smalian, Tronco de Cono y Newton son
         * métodos alternativos.
         */

        $volumenMaderable = [];

        foreach (
            $metodosVolumen
            as $idFormula => $metodo
        ) {

            $clave =
                $metodo['clave'];


            $volumenMaderable[
                $clave
            ] = [
                'id_formula' =>
                    $idFormula,

                'nombre' =>
                    $metodo['nombre'],

                'trozas' =>
                    (int) $estadisticasParcelas
                        ->sum(
                            fn ($parcela) =>
                                (int) (
                                    $parcela[
                                        'volumen_maderable'
                                    ][
                                        $clave
                                    ][
                                        'trozas'
                                    ]
                                    ?? 0
                                )
                        ),

                'volumen' =>
                    round(
                        (float) $estadisticasParcelas
                            ->sum(
                                fn ($parcela) =>
                                    (float) (
                                        $parcela[
                                            'volumen_maderable'
                                        ][
                                            $clave
                                        ][
                                            'volumen'
                                        ]
                                        ?? 0
                                    )
                            ),
                        10
                    ),
            ];
        }


        /*
         * Compatibilidad temporal con la versión
         * móvil actual.
         *
         * No utilizar para presentar un volumen
         * forestal único.
         */
        $totalVolumen =
            0.0;





        //* 6. OBTENER EVENTOS DE SINCRONIZACIÓN*

        //* ============================================*



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





        //* ============================================*

        //* 7. AGRUPAR POR sync_id*

        //

        //* Un mismo sync_id puede generar una fila por*

        //* Productor. Para el Dashboard debe contar como*

        //* UNA sincronización.*

        //* ============================================*



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

*                         * Estado consolidado:*

*                         **

*                         * - si alguna notificación falló:*

*                         *   failed*

*                         **

*                         * - si todas están notified:*

*                         *   notified*

*                         **

*                         * - cualquier otro caso:*

*                         *   pending*

*                         **/

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

*                             * Si el evento incluye varios*

*                             * Productores tomamos el mayor*

*                             * número de intentos.*

*                             **/

                            'attempts' =>

                                (int) $eventos

                                    ->max('attempts'),



                            /*

*                             * Los payloads son por Productor.*

*                             * Se suman para obtener el total*

*                             * real del sync completo.*

*                             **/

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

*                             * Número de Productores que*

*                             * participaron en ese sync.*

*                             **/

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





        //* ============================================*

        //* 8. ESTADÍSTICAS DE SINCRONIZACIÓN*

        //* ============================================*



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

*         * Total de registros pertenecientes a*

*         * sincronizaciones completadas.*

*         **/

        $totalRegistrosSincronizados =

            (int) $sincronizaciones

                ->where(

                    'status',

                    'notified'

                )

                ->sum(

                    'total_registros'

                );





        //* ============================================*

        //* 9. ÚLTIMA SINCRONIZACIÓN*

        //* ============================================*



        $ultimaSincronizacion =

            $sincronizaciones

                ->first();





        //* ============================================*

        //* 10. ACTIVIDAD DE LOS ÚLTIMOS 7 DÍAS*

        //

        //* Se convierte cada timestamp almacenado a*

        //* America/Mexico_City antes de agruparlo.*

        //* ============================================*



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





        //* ============================================*

        //* 11. DISTRIBUCIÓN DEL INVENTARIO*

        //

        //* Lista preparada para una gráfica.*

        //* ============================================*



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





        //* ============================================*

        //* 12. RESPUESTA PARA REACT NATIVE*

        //* ============================================*



        return response()->json([

            'data' => [



                'rol' =>

                    $rol,





                //* ====================================*

                //* RESUMEN FORESTAL*

                //* ====================================*



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


                //* ====================================*
                //* VOLUMEN MADERABLE POR MÉTODO*
                //* ====================================*

                'volumen_maderable' =>
                    $volumenMaderable,





                //* ====================================*

                //* SINCRONIZACIÓN*

                //* ====================================*



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





                //* ====================================*

                //* DATOS PARA GRÁFICAS*

                //* ====================================*



                'actividad_7_dias' =>

                    $actividad7Dias,



                'distribucion_inventario' =>

                    $distribucionInventario,



                /*

*                 * Puede utilizarse para una gráfica*

*                 * comparativa por parcela.*

*                 **/

                'parcelas_estadisticas' =>

                    $estadisticasParcelas,





                //* ====================================*

                //* METADATOS*

                //* ====================================*



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