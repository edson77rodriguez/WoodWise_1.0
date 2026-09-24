<?php

namespace App\Http\Controllers\API;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use App\Models\Estimacion;
use App\Models\Estimacion1;

class MobileParcelaController extends Controller
{
    public function index(Request $request)
    {
        $user = $request->user()->load([
            'persona.rol',
            'persona.tecnico',
            'persona.productor',
        ]);

        $persona = $user->persona;

        if (!$persona || !$persona->rol) {
            return response()->json([
                'message' => 'El usuario no tiene un perfil configurado correctamente.',
            ], 403);
        }

        $rol = $persona->rol->nom_rol;

        if ($rol === 'Tecnico') {

            if (!$persona->tecnico) {
                return response()->json([
                    'message' => 'El usuario no tiene un perfil de técnico configurado.',
                ], 403);
            }

            $parcelas = $persona->tecnico
                ->parcelas()
                ->select([
                    'parcelas.id_parcela',
                    'parcelas.nom_parcela',
                    'parcelas.ubicacion',
                    'parcelas.id_productor',
                    'parcelas.extension',
                    'parcelas.direccion',
                    'parcelas.CP',
                    'parcelas.created_at',
                    'parcelas.updated_at',
                ])
                ->distinct()
                ->get();

        } elseif ($rol === 'Productor') {

            if (!$persona->productor) {
                return response()->json([
                    'message' => 'El usuario no tiene un perfil de productor configurado.',
                ], 403);
            }

            $parcelas = $persona->productor
                ->parcelas()
                ->select([
                    'id_parcela',
                    'nom_parcela',
                    'ubicacion',
                    'id_productor',
                    'extension',
                    'direccion',
                    'CP',
                    'created_at',
                    'updated_at',
                ])
                ->get();

        } else {

            return response()->json([
                'message' => 'Este perfil no tiene acceso al módulo móvil de parcelas.',
            ], 403);
        }

        $data = $parcelas->map(function ($parcela) {
            return [
                'id_parcela' => $parcela->id_parcela,
                'nom_parcela' => $parcela->nom_parcela,
                'ubicacion' => $parcela->ubicacion,
                'id_productor' => $parcela->id_productor,
                'extension' => $parcela->extension,
                'direccion' => $parcela->direccion,

                // Normalizamos CP para la aplicación móvil.
                'cp' => $parcela->CP !== null
                    ? (string) $parcela->CP
                    : null,

                'created_at' => $parcela->created_at,
                'updated_at' => $parcela->updated_at,
            ];
        });

        return response()->json([
            'data' => $data,
            'total' => $data->count(),
        ]);
    }

    /////////////////////////SHOW
    public function show(Request $request, int $id)
{
    $user = $request->user()->load([
        'persona.rol',
        'persona.tecnico',
        'persona.productor',
    ]);

    $persona = $user->persona;

    if (!$persona || !$persona->rol) {
        return response()->json([
            'message' => 'El usuario no tiene un perfil configurado correctamente.',
        ], 403);
    }

    $rol = $persona->rol->nom_rol;

    /*
    |--------------------------------------------------------------------------
    | 1. VALIDAR ACCESO A LA PARCELA
    |--------------------------------------------------------------------------
    */

    if ($rol === 'Tecnico') {

        if (!$persona->tecnico) {
            return response()->json([
                'message' => 'El usuario no tiene un perfil de técnico configurado.',
            ], 403);
        }

        $parcela = $persona->tecnico
            ->parcelas()
            ->where('parcelas.id_parcela', $id)
            ->first();

    } elseif ($rol === 'Productor') {

        if (!$persona->productor) {
            return response()->json([
                'message' => 'El usuario no tiene un perfil de productor configurado.',
            ], 403);
        }

        $parcela = $persona->productor
            ->parcelas()
            ->where('id_parcela', $id)
            ->first();

    } else {

        return response()->json([
            'message' => 'Este perfil no tiene acceso al módulo móvil de parcelas.',
        ], 403);
    }

    /*
    |--------------------------------------------------------------------------
    | 2. PARCELA NO ENCONTRADA / SIN PERMISO
    |--------------------------------------------------------------------------
    */

    if (!$parcela) {
        return response()->json([
            'message' => 'Parcela no encontrada o sin autorización para consultarla.',
        ], 404);
    }

    /*
    |--------------------------------------------------------------------------
    | 3. CARGAR INVENTARIO
    |--------------------------------------------------------------------------
    */

    $parcela->load([
        'arboles.especie',
        'trozas.especie',
        'turnosCorta',
    ]);

    /*
    |--------------------------------------------------------------------------
    | 4. CARGAR ESTIMACIONES DE ÁRBOLES
    |--------------------------------------------------------------------------
    |
    | Una sola consulta para todos los árboles
    | de la parcela.
    |
    */

    $idsArboles = $parcela->arboles
        ->pluck('id_arbol');

    $estimacionesArbol =
        Estimacion1::query()
            ->with('formula')
            ->whereIn(
                'id_arbol',
                $idsArboles
            )
            ->get()
            ->groupBy(
                'id_arbol'
            );

    /*
    |--------------------------------------------------------------------------
    | 5. CARGAR ESTIMACIONES DE TROZAS
    |--------------------------------------------------------------------------
    |
    | Una sola consulta para todas las trozas
    | de la parcela.
    |
    */

    $idsTrozas = $parcela->trozas
        ->pluck('id_troza');

    $estimacionesTroza =
        Estimacion::query()
            ->with('formula')
            ->whereIn(
                'id_troza',
                $idsTrozas
            )
            ->get()
            ->groupBy(
                'id_troza'
            );

    /*
    |--------------------------------------------------------------------------
    | 6. RESPUESTA MÓVIL
    |--------------------------------------------------------------------------
    */

    return response()->json([
        'data' => [

            /*
            |--------------------------------------------------------------------------
            | PARCELA
            |--------------------------------------------------------------------------
            */

            'parcela' => [

                'id_parcela' =>
                    $parcela->id_parcela,

                'nom_parcela' =>
                    $parcela->nom_parcela,

                'ubicacion' =>
                    $parcela->ubicacion,

                'id_productor' =>
                    $parcela->id_productor,

                'extension' =>
                    $parcela->extension,

                'direccion' =>
                    $parcela->direccion,

                'cp' =>
                    $parcela->CP !== null
                        ? (string) $parcela->CP
                        : null,

                'created_at' =>
                    $parcela->created_at,

                'updated_at' =>
                    $parcela->updated_at,
            ],

            /*
            |--------------------------------------------------------------------------
            | ÁRBOLES + ESTIMACIONES
            |--------------------------------------------------------------------------
            */

            'arboles' =>
                $parcela->arboles->map(
                    function ($arbol) use (
                        $estimacionesArbol
                    ) {

                        $estimaciones =
                            $estimacionesArbol
                                ->get(
                                    $arbol->id_arbol,
                                    collect()
                                );

                        return [

                            'id_arbol' =>
                                $arbol->id_arbol,

                            'altura_total' =>
                                $arbol->altura_total,

                            'diametro_pecho' =>
                                $arbol->diametro_pecho,

                            'id_especie' =>
                                $arbol->id_especie,

                            'id_parcela' =>
                                $arbol->id_parcela,

                            'especie' =>
                                $arbol->especie
                                    ? [
                                        'id_especie' =>
                                            $arbol
                                                ->especie
                                                ->id_especie,

                                        'nom_cientifico' =>
                                            $arbol
                                                ->especie
                                                ->nom_cientifico,

                                        'nom_comun' =>
                                            $arbol
                                                ->especie
                                                ->nom_comun,
                                    ]
                                    : null,

                            /*
                            |----------------------------------------------
                            | ESTIMACIONES DEL ÁRBOL
                            |----------------------------------------------
                            */

                            'estimaciones' =>
                                $estimaciones
                                    ->map(
                                        function (
                                            $estimacion
                                        ) {

                                            return [

                                                'id_estimacion1' =>
                                                    $estimacion
                                                        ->id_estimacion1,

                                                'id_tipo_e' =>
                                                    $estimacion
                                                        ->id_tipo_e,

                                                'id_formula' =>
                                                    $estimacion
                                                        ->id_formula,

                                                'id_arbol' =>
                                                    $estimacion
                                                        ->id_arbol,

                                                'calculo' =>
                                                    $estimacion
                                                        ->calculo,

                                                'biomasa' =>
                                                    $estimacion
                                                        ->biomasa,

                                                'carbono' =>
                                                    $estimacion
                                                        ->carbono,

                                                'formula' =>
                                                    $estimacion->formula
                                                        ? [
                                                            'id_formula' =>
                                                                $estimacion
                                                                    ->formula
                                                                    ->id_formula,

                                                            'nom_formula' =>
                                                                $estimacion
                                                                    ->formula
                                                                    ->nom_formula,
                                                        ]
                                                        : null,

                                                'created_at' =>
                                                    $estimacion
                                                        ->created_at,

                                                'updated_at' =>
                                                    $estimacion
                                                        ->updated_at,
                                            ];
                                        }
                                    )
                                    ->values(),

                            'created_at' =>
                                $arbol->created_at,

                            'updated_at' =>
                                $arbol->updated_at,
                        ];
                    }
                ),

            /*
            |--------------------------------------------------------------------------
            | TROZAS + ESTIMACIONES
            |--------------------------------------------------------------------------
            */

            'trozas' =>
                $parcela->trozas->map(
                    function ($troza) use (
                        $estimacionesTroza
                    ) {

                        $estimaciones =
                            $estimacionesTroza
                                ->get(
                                    $troza->id_troza,
                                    collect()
                                );

                        return [

                            'id_troza' =>
                                $troza->id_troza,

                            'longitud' =>
                                $troza->longitud,

                            'diametro' =>
                                $troza->diametro,

                            'diametro_otro_extremo' =>
                                $troza
                                    ->diametro_otro_extremo,

                            'diametro_medio' =>
                                $troza
                                    ->diametro_medio,

                            'densidad' =>
                                $troza->densidad,

                            'id_especie' =>
                                $troza->id_especie,

                            'id_parcela' =>
                                $troza->id_parcela,

                            'especie' =>
                                $troza->especie
                                    ? [
                                        'id_especie' =>
                                            $troza
                                                ->especie
                                                ->id_especie,

                                        'nom_cientifico' =>
                                            $troza
                                                ->especie
                                                ->nom_cientifico,

                                        'nom_comun' =>
                                            $troza
                                                ->especie
                                                ->nom_comun,
                                    ]
                                    : null,

                            /*
                            |----------------------------------------------
                            | ESTIMACIONES DE LA TROZA
                            |----------------------------------------------
                            */

                            'estimaciones' =>
                                $estimaciones
                                    ->map(
                                        function (
                                            $estimacion
                                        ) {

                                            return [

                                                'id_estimacion' =>
                                                    $estimacion
                                                        ->id_estimacion,

                                                'id_tipo_e' =>
                                                    $estimacion
                                                        ->id_tipo_e,

                                                'id_formula' =>
                                                    $estimacion
                                                        ->id_formula,

                                                'id_troza' =>
                                                    $estimacion
                                                        ->id_troza,

                                                'calculo' =>
                                                    $estimacion
                                                        ->calculo,

                                                'biomasa' =>
                                                    $estimacion
                                                        ->biomasa,

                                                'carbono' =>
                                                    $estimacion
                                                        ->carbono,

                                                'formula' =>
                                                    $estimacion->formula
                                                        ? [
                                                            'id_formula' =>
                                                                $estimacion
                                                                    ->formula
                                                                    ->id_formula,

                                                            'nom_formula' =>
                                                                $estimacion
                                                                    ->formula
                                                                    ->nom_formula,
                                                        ]
                                                        : null,

                                                'created_at' =>
                                                    $estimacion
                                                        ->created_at,

                                                'updated_at' =>
                                                    $estimacion
                                                        ->updated_at,
                                            ];
                                        }
                                    )
                                    ->values(),

                            'created_at' =>
                                $troza->created_at,

                            'updated_at' =>
                                $troza->updated_at,
                        ];
                    }
                ),

            /*
            |--------------------------------------------------------------------------
            | TURNOS
            |--------------------------------------------------------------------------
            */

            'turnos_corta' =>
                $parcela->turnosCorta->map(
                    function ($turno) {

                        return [

                            'id_turno' =>
                                $turno->id_turno,

                            'id_parcela' =>
                                $turno->id_parcela,

                            'codigo_corta' =>
                                $turno->codigo_corta,

                            'fecha_corta' =>
                                $turno->fecha_corta,

                            'fecha_fin' =>
                                $turno->fecha_fin,

                            'created_at' =>
                                $turno->created_at,

                            'updated_at' =>
                                $turno->updated_at,
                        ];
                    }
                ),

            /*
            |--------------------------------------------------------------------------
            | RESUMEN
            |--------------------------------------------------------------------------
            */

            'resumen' => [

                'total_arboles' =>
                    $parcela->arboles->count(),

                'total_trozas' =>
                    $parcela->trozas->count(),

                'total_turnos_corta' =>
                    $parcela
                        ->turnosCorta
                        ->count(),
            ],
        ],
    ]);
}
}