<?php

namespace App\Http\Controllers\API;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Illuminate\Support\Str;

class MobileSyncController extends Controller
{
    public function store(Request $request)
    {
        // ============================================
        // 1. USUARIO AUTENTICADO
        // ============================================

        $user = $request->user()->load([
            'persona.rol',
            'persona.tecnico',
        ]);

        $persona = $user->persona;


        if (!$persona || !$persona->rol) {

            return response()->json([
                'message' =>
                    'El usuario no tiene un perfil configurado correctamente.',
            ], 403);
        }


        if ($persona->rol->nom_rol !== 'Tecnico') {

            return response()->json([
                'message' =>
                    'Solo los técnicos pueden registrar sincronizaciones.',
            ], 403);
        }


        if (!$persona->tecnico) {

            return response()->json([
                'message' =>
                    'El usuario no tiene un perfil de técnico configurado.',
            ], 403);
        }


        // ============================================
        // 2. VALIDAR PAYLOAD
        // ============================================

        $validated = $request->validate([

            'parcelas' => [
                'required',
                'array',
                'min:1',
            ],

            'parcelas.*.id_parcela' => [
                'required',
                'integer',
                'distinct',
            ],

            'parcelas.*.arboles' => [
                'required',
                'integer',
                'min:0',
            ],

            'parcelas.*.trozas' => [
                'required',
                'integer',
                'min:0',
            ],
        ]);


        // ============================================
        // 3. EXTRAER IDs SOLICITADOS
        // ============================================

        $idsSolicitados =
            collect(
                $validated['parcelas']
            )
            ->pluck('id_parcela')
            ->map(
                fn ($id) =>
                    (int) $id
            )
            ->values();


        // ============================================
        // 4. OBTENER ÚNICAMENTE PARCELAS
        // AUTORIZADAS PARA ESTE TÉCNICO
        // ============================================

        $parcelasAutorizadas =
            $persona->tecnico
                ->parcelas()
                ->whereIn(
                    'parcelas.id_parcela',
                    $idsSolicitados
                )
                ->get();


        $idsAutorizados =
            $parcelasAutorizadas
                ->pluck('id_parcela')
                ->map(
                    fn ($id) =>
                        (int) $id
                );


        // ============================================
        // 5. COMPROBAR QUE TODAS ESTÉN AUTORIZADAS
        // ============================================

        $idsNoAutorizados =
            $idsSolicitados
                ->diff(
                    $idsAutorizados
                );


        if (
            $idsNoAutorizados->isNotEmpty()
        ) {

            return response()->json([
                'message' =>
                    'Una o más parcelas no existen o no están autorizadas para este técnico.',
            ], 404);
        }


        // ============================================
        // 6. EVITAR EVENTOS VACÍOS
        // ============================================

        $totalRegistros =
            collect(
                $validated['parcelas']
            )
            ->sum(
                function ($item) {

                    return
                        (int) $item['arboles']
                        +
                        (int) $item['trozas'];
                }
            );


        if ($totalRegistros <= 0) {

            return response()->json([
                'message' =>
                    'La sincronización no contiene registros nuevos.',
            ], 422);
        }


        // ============================================
        // 7. CONSTRUIR DETALLE VALIDADO
        //
        // IMPORTANTE:
        // id_productor se obtiene desde MySQL.
        // La app NO decide quién es el Productor.
        // ============================================

        $detalleParcelas =
            collect(
                $validated['parcelas']
            )
            ->map(
                function ($item)
                use ($parcelasAutorizadas) {

                    $parcela =
                        $parcelasAutorizadas
                            ->firstWhere(
                                'id_parcela',
                                (int) $item['id_parcela']
                            );


                    return [

                        'id_parcela' =>
                            (int) $parcela->id_parcela,

                        'nom_parcela' =>
                            $parcela->nom_parcela,

                        'id_productor' =>
                            (int) $parcela->id_productor,

                        'arboles' =>
                            (int) $item['arboles'],

                        'trozas' =>
                            (int) $item['trozas'],

                        'total_registros' =>
                            (int) $item['arboles']
                            +
                            (int) $item['trozas'],
                    ];
                }
            );


        // ============================================
        // 8. AGRUPAR POR PRODUCTOR
        //
        // Esto permitirá posteriormente enviar
        // UN mensaje consolidado por Productor.
        // ============================================

        $productores =
            $detalleParcelas
                ->groupBy(
                    'id_productor'
                )
                ->map(
                    function (
                        $parcelas,
                        $idProductor
                    ) {

                        return [

                            'id_productor' =>
                                (int) $idProductor,

                            'total_parcelas' =>
                                $parcelas->count(),

                            'total_arboles' =>
                                $parcelas->sum(
                                    'arboles'
                                ),

                            'total_trozas' =>
                                $parcelas->sum(
                                    'trozas'
                                ),

                            'total_registros' =>
                                $parcelas->sum(
                                    'total_registros'
                                ),

                            'parcelas' =>
                                $parcelas->values(),
                        ];
                    }
                )
                ->values();


        // ============================================
        // 9. IDENTIFICADOR DEL EVENTO
        //
        // De momento identifica esta recepción.
        // La persistencia/idempotencia vendrá en
        // el siguiente micro-paso.
        // ============================================

        $syncId =
            (string) Str::uuid();


        // ============================================
        // 10. RESPUESTA
        // ============================================

        return response()->json([

            'message' =>
                'Sincronización validada correctamente.',

            'data' => [

                'sync_id' =>
                    $syncId,

                'id_tecnico' =>
                    $persona->tecnico->getKey(),

                'total_parcelas' =>
                    $detalleParcelas->count(),

                'total_arboles' =>
                    $detalleParcelas->sum(
                        'arboles'
                    ),

                'total_trozas' =>
                    $detalleParcelas->sum(
                        'trozas'
                    ),

                'total_registros' =>
                    $totalRegistros,

                'productores' =>
                    $productores,
            ],

        ], 200);
    }
}