<?php

namespace App\Http\Controllers\API;

use App\Http\Controllers\Controller;
use App\Models\MobileSyncNotification;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
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

            /*
             * Temporalmente es opcional porque la versión
             * actual de React Native todavía no lo envía.
             *
             * En el siguiente paso la app generará
             * y conservará este identificador.
             */
            'sync_id' => [
                'sometimes',
                'uuid',
            ],

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


        if ($idsNoAutorizados->isNotEmpty()) {

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
        // id_productor SIEMPRE se obtiene desde MySQL.
        // La app no decide quién recibe la notificación.
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
        // Un evento consolidado por Productor.
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
        // 9. IDENTIFICADOR DE SINCRONIZACIÓN
        //
        // Si React Native lo manda, se conserva.
        // Si todavía no lo manda, Laravel genera uno.
        // ============================================

        $syncId =
            $validated['sync_id']
            ??
            (string) Str::uuid();


        $idTecnico =
            (int) $persona->tecnico->getKey();


        // ============================================
        // 10. PREPARAR EVENTO POR PRODUCTOR
        // ============================================

        $notificaciones =
            $productores
                ->map(
                    function ($productor)
                    use (
                        $syncId,
                        $idTecnico
                    ) {

                        /*
                         * Orden estable para que el hash
                         * siempre produzca el mismo resultado
                         * para el mismo contenido.
                         */
                        $parcelas =
                            collect(
                                $productor['parcelas']
                            )
                            ->sortBy(
                                'id_parcela'
                            )
                            ->values()
                            ->map(
                                function ($parcela) {

                                    return [

                                        'id_parcela' =>
                                            (int) $parcela['id_parcela'],

                                        'nom_parcela' =>
                                            $parcela['nom_parcela'],

                                        'id_productor' =>
                                            (int) $parcela['id_productor'],

                                        'arboles' =>
                                            (int) $parcela['arboles'],

                                        'trozas' =>
                                            (int) $parcela['trozas'],

                                        'total_registros' =>
                                            (int) $parcela['total_registros'],
                                    ];
                                }
                            )
                            ->all();


                        $payload = [

                            'sync_id' =>
                                $syncId,

                            'id_tecnico' =>
                                $idTecnico,

                            'id_productor' =>
                                (int) $productor['id_productor'],

                            'total_parcelas' =>
                                (int) $productor['total_parcelas'],

                            'total_arboles' =>
                                (int) $productor['total_arboles'],

                            'total_trozas' =>
                                (int) $productor['total_trozas'],

                            'total_registros' =>
                                (int) $productor['total_registros'],

                            'parcelas' =>
                                $parcelas,
                        ];


                        $payloadJson =
                            json_encode(
                                $payload,
                                JSON_UNESCAPED_UNICODE
                                |
                                JSON_UNESCAPED_SLASHES
                                |
                                JSON_PRESERVE_ZERO_FRACTION
                            );


                        return [

                            'id_productor' =>
                                (int) $productor['id_productor'],

                            'payload' =>
                                $payload,

                            'payload_hash' =>
                                hash(
                                    'sha256',
                                    $payloadJson
                                ),
                        ];
                    }
                );


        // ============================================
        // 11. PERSISTENCIA + IDEMPOTENCIA
        // ============================================

        $resultadoIdempotencia =
            DB::transaction(
                function ()
                use (
                    $syncId,
                    $idTecnico,
                    $notificaciones
                ) {

                    /*
                     * Bloqueamos cualquier evento existente
                     * con este sync_id mientras lo validamos.
                     */
                    $eventosExistentes =
                        MobileSyncNotification::query()
                            ->where(
                                'sync_id',
                                $syncId
                            )
                            ->lockForUpdate()
                            ->get();


                    // ====================================
                    // REINTENTO
                    // ====================================

                    if ($eventosExistentes->isNotEmpty()) {

                        /*
                         * El mismo sync_id no puede
                         * pertenecer a otro Técnico.
                         */
                        $tecnicoDiferente =
                            $eventosExistentes
                                ->contains(
                                    function ($evento)
                                    use ($idTecnico) {

                                        return
                                            (int) $evento->id_tecnico
                                            !==
                                            $idTecnico;
                                    }
                                );


                        if ($tecnicoDiferente) {

                            return [
                                'ok' => false,

                                'message' =>
                                    'El sync_id ya pertenece a otra sincronización.',
                            ];
                        }


                        /*
                         * Deben ser exactamente
                         * los mismos Productores.
                         */
                        $productoresEsperados =
                            $notificaciones
                                ->pluck(
                                    'id_productor'
                                )
                                ->sort()
                                ->values()
                                ->all();


                        $productoresExistentes =
                            $eventosExistentes
                                ->pluck(
                                    'id_productor'
                                )
                                ->map(
                                    fn ($id) =>
                                        (int) $id
                                )
                                ->sort()
                                ->values()
                                ->all();


                        if (
                            $productoresEsperados
                            !==
                            $productoresExistentes
                        ) {

                            return [
                                'ok' => false,

                                'message' =>
                                    'El sync_id fue reutilizado con productores diferentes.',
                            ];
                        }


                        /*
                         * El contenido de cada evento
                         * también debe coincidir.
                         */
                        foreach (
                            $notificaciones
                            as $notificacion
                        ) {

                            $eventoExistente =
                                $eventosExistentes
                                    ->firstWhere(
                                        'id_productor',
                                        $notificacion['id_productor']
                                    );


                            if (
                                !$eventoExistente
                                ||
                                !hash_equals(
                                    (string) $eventoExistente->payload_hash,
                                    (string) $notificacion['payload_hash']
                                )
                            ) {

                                return [
                                    'ok' => false,

                                    'message' =>
                                        'El sync_id fue reutilizado con información diferente.',
                                ];
                            }
                        }


                        return [
                            'ok' => true,
                            'replay' => true,
                        ];
                    }


                    // ====================================
                    // EVENTO NUEVO
                    // ====================================

                    foreach (
                        $notificaciones
                        as $notificacion
                    ) {

                        MobileSyncNotification::create([

                            'sync_id' =>
                                $syncId,

                            'id_tecnico' =>
                                $idTecnico,

                            'id_productor' =>
                                $notificacion['id_productor'],

                            'payload' =>
                                $notificacion['payload'],

                            'payload_hash' =>
                                $notificacion['payload_hash'],

                            'status' =>
                                'pending',

                            'attempts' =>
                                0,

                            'notified_at' =>
                                null,

                            'last_error' =>
                                null,
                        ]);
                    }


                    return [
                        'ok' => true,
                        'replay' => false,
                    ];
                }
            );


        // ============================================
        // 12. CONFLICTO DE IDEMPOTENCIA
        // ============================================

        if (!$resultadoIdempotencia['ok']) {

            return response()->json([

                'message' =>
                    $resultadoIdempotencia['message'],

            ], 409);
        }


        $esReintento =
            (bool) $resultadoIdempotencia['replay'];


        // ============================================
        // 13. RESPUESTA
        // ============================================

        return response()->json([

            'message' =>
                $esReintento
                    ? 'Sincronización ya registrada previamente.'
                    : 'Sincronización validada y registrada correctamente.',

            'data' => [

                'sync_id' =>
                    $syncId,

                'id_tecnico' =>
                    $idTecnico,

                /*
                 * false = evento nuevo
                 * true  = mismo evento reenviado
                 */
                'idempotent_replay' =>
                    $esReintento,

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