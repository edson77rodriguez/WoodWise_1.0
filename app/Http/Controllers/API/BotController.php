<?php

namespace App\Http\Controllers\API;

use App\Http\Controllers\Controller;
use App\Models\BotSesion;
use App\Models\Persona;
use App\Models\Parcela;
use App\Models\Tecnico;
use Barryvdh\DomPDF\Facade\Pdf;
use Carbon\Carbon;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Str;

class BotController extends Controller
{
    public const ESPERANDO_PARCELA_ESTIMACION = 'esperando_parcela_estimacion';
    public const ESPERANDO_ALCANCE_ESTIMACION = 'esperando_alcance_estimacion';
    public const ESPERANDO_FORMULA_ESTIMACION = 'esperando_formula_estimacion';
    public const ESPERANDO_PARCELA_EXCEL = 'esperando_parcela_excel';
    public const ESPERANDO_ARCHIVO_EXCEL = 'esperando_archivo_excel';

    public function asistenteGuiado(Request $request)
    {
        $this->limpiarSesionesExcelExpiradas();

        $data = $request->validate([
            'telefono' => ['required', 'string', 'max:30'],
            'mensaje' => ['required', 'string'],
        ]);

        $telefono = $this->normalizarTelefono($data['telefono']);
        $mensajeCrudo = trim($data['mensaje']);
        $mensajeLimpio = mb_strtolower($mensajeCrudo);

        $persona = $this->findPersonaByTelefono($telefono);
        if (!$persona) {
            return response()->json(['error' => 'Usuario no encontrado'], 404);
        }

        [$rol, $parcelasIds] = $this->resolveParcelasIdsForPersona($persona);
        if ($rol !== 'Tecnico' && $rol !== 'Productor') {
            return response()->json(['error' => 'Tu perfil no tiene acceso al Asistente.'], 403);
        }

        // Regla de acceso desde el inicio: el registro guiado es exclusivo para Técnicos.
        if ($rol === 'Productor') {
            return response()->json([
                'ok' => false,
                'acceso' => [
                    'rol' => $rol,
                    'puede_registrar' => false,
                ],
                'mensaje' => "🔐 *Verificación de acceso*\nRol detectado: *{$rol}*\n❌ No tienes acceso para registrar inventario en el Asistente Guiado.\n\nEsta tarea está habilitada solo para usuarios con rol *Técnico*.\nSi necesitas registrar datos, solicita apoyo a un técnico asignado.",
            ], 200);
        }

        $sesion = BotSesion::where('telefono', $telefono)->first();

        if (in_array($mensajeLimpio, ['cancelar', 'salir', 'detener', 'abortar'], true)) {
            if ($sesion) {
                $sesion->delete();
            }

            return response()->json([
                'ok' => true,
                'mensaje' => "🛑 *Asistente cancelado.*\nTu progreso ha sido borrado. Puedes volver a iniciar desde el Menú Principal.",
            ], 200);
        }

        // Permite disparar importación de Excel desde cualquier estado.
        $mensajeClaveGlobal = trim($mensajeLimpio, " \t\n\r\0\x0B\"'");
        if (in_array($mensajeClaveGlobal, ['menu_importar_excel', 'importar excel'], true)) {
            if ($sesion) {
                $sesion->delete();
            }

            return $this->iniciarFlujoImportacionExcel($telefono, $parcelasIds);
        }

        if (in_array($mensajeClaveGlobal, ['menu_impacto_ambiental', 'impacto ambiental'], true)) {
            if ($sesion) {
                $sesion->delete();
            }

            return $this->responderImpactoAmbiental($persona, $rol, $parcelasIds, null);
        }

        if (in_array($mensajeClaveGlobal, ['menu_ingreso_archivo', 'subir archivo', 'carga archivo', 'cargar archivo'], true)) {
            return $this->responderIngresoArchivo();
        }

        // 3. LA MÁQUINA DE ESTADOS (El flujo conversacional)
        if (!$sesion) {
            // Normalizamos comillas/espacios para tolerar variaciones típicas de n8n/Meta.
            $mensajeClave = trim($mensajeLimpio, " \t\n\r\0\x0B\"'");

            // Aceptamos palabra clave o IDs de botón comunes del flujo guiado.
            if (in_array($mensajeClave, ['iniciar', 'menu_ingreso_guiado', 'ingreso_guiado', 'asistente_guiado'], true)) {
                return $this->iniciarAsistente($telefono, $parcelasIds);
            }

            // Disparador del flujo de generación de estimaciones pendientes.
            if (in_array($mensajeClave, ['menu_generar_estimaciones', 'generar estimaciones'], true)) {
                return $this->iniciarFlujoEstimaciones($telefono, $parcelasIds);
            }

            if (in_array($mensajeClave, ['menu_importar_excel', 'importar excel'], true)) {
                return $this->iniciarFlujoImportacionExcel($telefono, $parcelasIds);
            }

            if (in_array($mensajeClave, ['menu_impacto_ambiental', 'impacto ambiental'], true)) {
                return $this->responderImpactoAmbiental($persona, $rol, $parcelasIds, null);
            }

            if (in_array($mensajeClave, ['menu_ingreso_archivo', 'subir archivo', 'carga archivo', 'cargar archivo'], true)) {
                return $this->responderIngresoArchivo();
            }

            // Si mandó cualquier texto random y no tiene plática activa, lo mandamos al menú.
            return response()->json([
                'ok' => true,
                'mensaje' => "👋 No tienes ninguna tarea activa en este momento.\n\nEscribe *'Hola'* para abrir el Menú Principal de SIGMAD.",
            ], 200);
        }

        switch ($sesion->estado) {
            case 'ESPERANDO_PARCELA':
                return $this->procesarPasoParcela($sesion, $mensajeCrudo, $parcelasIds);

            case 'ESPERANDO_TIPO':
                return $this->procesarPasoTipo($sesion, $mensajeCrudo);

            case 'ESPERANDO_ESPECIE':
                return $this->procesarPasoEspecie($sesion, $mensajeCrudo);

            case 'ESPERANDO_MEDIDAS_ARBOL':
                return $this->procesarPasoMedidasArbol($sesion, $mensajeCrudo);

            case 'ESPERANDO_MEDIDAS_TROZA':
                return $this->procesarPasoMedidasTroza($sesion, $mensajeCrudo);

            case self::ESPERANDO_PARCELA_ESTIMACION:
                return $this->procesarParcelaEstimacion($sesion, $mensajeCrudo, $parcelasIds);

            case self::ESPERANDO_ALCANCE_ESTIMACION:
                return $this->procesarAlcanceEstimacion($sesion, $mensajeCrudo);

            case self::ESPERANDO_FORMULA_ESTIMACION:
                return $this->procesarFormulaEstimacion($sesion, $mensajeCrudo);

            case self::ESPERANDO_PARCELA_EXCEL:
                return $this->procesarParcelaExcel($sesion, $mensajeCrudo, $parcelasIds);

            case self::ESPERANDO_ARCHIVO_EXCEL:
                return $this->procesarArchivoExcel($sesion, $mensajeCrudo);

            default:
                $sesion->delete();
                return response()->json(['error' => 'Sesión corrupta. Por favor, inicia de nuevo.'], 500);
        }
    }

    public function recibirExcelWebhook(Request $request)
    {
        $this->limpiarSesionesExcelExpiradas();

        $data = $request->validate([
            'telefono' => ['required', 'string', 'max:30'],
            'excel_data' => ['required', 'array'],
        ]);

        $telefono = $this->normalizarTelefono($data['telefono']);
        $persona = $this->findPersonaByTelefono($telefono);
        if (!$persona) {
            return response()->json(['error' => 'Usuario no encontrado'], 404);
        }

        [$rol, $parcelasIds] = $this->resolveParcelasIdsForPersona($persona);
        if ($rol !== 'Tecnico' && $rol !== 'Productor') {
            return response()->json(['error' => 'Tu perfil no tiene acceso al Asistente.'], 403);
        }

        BotSesion::updateOrCreate(
            ['telefono' => $telefono],
            [
                'estado' => self::ESPERANDO_PARCELA_EXCEL,
                'payload' => [
                    'excel_data' => $data['excel_data'],
                ],
            ]
        );

        $trozasCount = count($data['excel_data']['trozas'] ?? []);
        $arbolesCount = count($data['excel_data']['arboles'] ?? []);
        $totalCount = $trozasCount + $arbolesCount;

        $parcelas = $parcelasIds->isEmpty()
            ? collect()
            : DB::table('parcelas')
                ->whereIn('id_parcela', $parcelasIds)
                ->select('id_parcela', 'nom_parcela')
                ->orderBy('nom_parcela')
                ->get();

        $parcelasTexto = $parcelas->isEmpty()
            ? 'No tienes parcelas asignadas.'
            : $parcelas->map(fn ($p, $i) => ($i + 1) . '. ' . $p->nom_parcela)->implode("\n");

        $interactivePayload = null;
        if ($parcelas->isNotEmpty()) {
            $rows = $parcelas->take(10)->map(function ($parcela) {
                return [
                    'id' => 'excel_parcela_' . $parcela->id_parcela,
                    'title' => $parcela->nom_parcela,
                    'description' => 'Seleccionar parcela',
                ];
            })->values()->all();

            $interactivePayload = [
                'type' => 'list',
                'header' => [
                    'type' => 'text',
                    'text' => '📍 Selecciona la parcela',
                ],
                'body' => [
                    'text' => "Se detectaron {$totalCount} registros.\nElige la parcela para continuar:",
                ],
                'footer' => [
                    'text' => 'SIGMAD | Importacion Excel',
                ],
                'action' => [
                    'button' => 'Ver parcelas',
                    'sections' => [
                        [
                            'title' => 'Parcelas asignadas',
                            'rows' => $rows,
                        ],
                    ],
                ],
            ];
        }

        return response()->json([
            'ok' => true,
            'estado' => self::ESPERANDO_PARCELA_EXCEL,
            'resumen' => [
                'trozas_recibidas' => $trozasCount,
                'arboles_recibidos' => $arbolesCount,
                'total_registros' => $totalCount,
            ],
            'parcelas' => $parcelas,
            'interactive_payload' => $interactivePayload,
            'mensaje' => "✅ Archivo recibido.\n\n"
                . "📊 Registros detectados: {$totalCount} (🌲 {$arbolesCount} arboles, 🪵 {$trozasCount} trozas).\n\n"
                . "¿A que parcela corresponden los datos? Responde con el nombre o numero:\n{$parcelasTexto}",
        ], 200);
    }

    public function listarParcelas(Request $request)
    {
        $data = $request->validate([
            'telefono' => ['required', 'string', 'max:30'],
        ]);

        $persona = $this->findPersonaByTelefono($data['telefono']);

        if (!$persona) {
            return response()->json(['error' => 'Usuario no encontrado'], 404);
        }

        [$rol, $parcelasIds] = $this->resolveParcelasIdsForPersona($persona);

        if ($rol === null) {
            return response()->json([
                'usuario' => trim(($persona->nom ?? '') . ' ' . ($persona->ap ?? '') . ' ' . ($persona->am ?? '')),
                'rol' => null,
                'mensaje' => 'Tu cuenta no tiene rol o perfil válido.',
            ], 409);
        }

        if ($parcelasIds->isEmpty()) {
            return response()->json([
                'usuario' => trim(($persona->nom ?? '') . ' ' . ($persona->ap ?? '') . ' ' . ($persona->am ?? '')),
                'rol' => $rol,
                'total_parcelas' => 0,
                'parcelas' => [],
                'mensaje' => 'No tienes parcelas asignadas actualmente.',
            ], 200);
        }

        $parcelas = DB::table('parcelas')
            ->whereIn('id_parcela', $parcelasIds)
            ->select('id_parcela', 'nom_parcela')
            ->orderBy('nom_parcela')
            ->get();

        return response()->json([
            'usuario' => trim(($persona->nom ?? '') . ' ' . ($persona->ap ?? '') . ' ' . ($persona->am ?? '')),
            'rol' => $rol,
            'total_parcelas' => $parcelas->count(),
            'parcelas' => $parcelas,
        ], 200);
    }

    public function verificarUsuario(Request $request)
    {
        $data = $request->validate([
            'telefono' => ['required', 'string', 'max:30'],
        ]);

        $persona = $this->findPersonaByTelefono($data['telefono']);

        if (!$persona) {
            return response()->json([
                'autorizado' => false,
                'mensaje' => 'Este número no está registrado en el sistema SIGMAD.',
            ], 404);
        }

        $rol = $persona->rol?->nom_rol;

        if (!$rol) {
            return response()->json([
                'autorizado' => false,
                'mensaje' => 'Tu cuenta no tiene un rol asignado.',
            ], 409);
        }

        return response()->json([
            'autorizado' => true,
            'id_persona' => $persona->id_persona,
            'telefono' => $persona->telefono,
            'nombre_completo' => trim(($persona->nom ?? '') . ' ' . ($persona->ap ?? '') . ' ' . ($persona->am ?? '')),
            'rol' => $rol,
        ], 200);
    }

    public function obtenerMenuPrincipal(Request $request)
    {
        $data = $request->validate([
            'telefono' => ['required', 'string', 'max:30'],
        ]);

        $persona = $this->findPersonaByTelefono($data['telefono']);

        if (!$persona) {
            return response()->json([
                'ok' => false,
                'mensaje' => 'Este numero no esta registrado en SIGMAD.',
            ], 404);
        }

        $rol = $persona->rol?->nom_rol;

        if (!$rol) {
            return response()->json([
                'ok' => false,
                'mensaje' => 'Tu cuenta no tiene un rol asignado. Contacta al administrador.',
            ], 403);
        }

        $nombre = trim($persona->nom ?? 'Usuario');
        $etiquetaRol = $rol === 'Tecnico' ? 'Tecnico de campo' : 'Productor forestal';

        $mensajeCuerpo = "Hola *{$nombre}* 👋\n"
            . "Soy tu asistente virtual de *SIGMAD* 🌿\n\n"
            . "🧾 Perfil detectado: *{$etiquetaRol}*\n"
            . "Desde aqui puedes consultar inventarios, revisar impacto ambiental y generar reportes de forma rapida.\n\n"
            . "✨ Selecciona la opcion que deseas usar:";

        $secciones = [];

        if ($rol === 'Tecnico') {
            $secciones[] = [
                'title' => '🛡️ Preparacion de Campo',
                'rows' => [
                    [
                        'id' => 'menu_kit_campo',
                        'title' => '🎒 Kit de Campo',
                        'description' => 'Parcelas, especies y guia para captura',
                    ],
                ],
            ];

            $secciones[] = [
                'title' => '📥 Registro de Datos',
                'rows' => [
                    [
                        'id' => 'menu_ingreso_guiado',
                        'title' => '🤖 Asistente Guiado',
                        'description' => 'Captura asistida paso a paso',
                    ],
                    [
                        'id' => 'menu_importar_excel',
                        'title' => '📥 Importar Excel',
                        'description' => 'Carga inventario con plantilla oficial',
                    ],
                    [
                        'id' => 'menu_ingreso_archivo',
                        'title' => '📎 Subir Archivo',
                        'description' => 'Carga archivos Excel o PDF',
                    ],
                ],
            ];
        }

        if (in_array($rol, ['Tecnico', 'Productor'], true)) {
            $secciones[] = [
                'title' => '📊 Consultas y Calculos',
                'rows' => [
                    [
                        'id' => 'menu_generar_estimaciones',
                        'title' => '🧮 Generar Estimaciones',
                        'description' => 'Procesa pendientes por parcela y alcance',
                    ],
                    [
                        'id' => 'btn_inventario',
                        'title' => '🪵 Ver Inventarios',
                        'description' => 'Consulta trozas y arboles registrados',
                    ],
                    [
                        'id' => 'menu_impacto_ambiental',
                        'title' => '🌍 Impacto Ambiental',
                        'description' => 'Biomasa, carbono y resultados clave',
                    ],
                ],
            ];

            $secciones[] = [
                'title' => '📂 Reportes',
                'rows' => [
                    [
                        'id' => 'btn_reporte',
                        'title' => '📥 Descargar PDF',
                        'description' => 'Genera y descarga el reporte oficial',
                    ],
                ],
            ];
        }

        $interactiveObject = [
            'type' => 'list',
            'header' => [
                'type' => 'text',
                'text' => '🍃 Bienvenido a SIGMAD',
            ],
            'body' => [
                'text' => $mensajeCuerpo,
            ],
            'footer' => [
                'text' => 'SIGMAD | Sistema Inteligente de Gestion Maderable 🌲',
            ],
            'action' => [
                'button' => '☰ Menu Principal',
                'sections' => $secciones,
            ],
        ];

        return response()->json([
            'ok' => true,
            'rol' => $rol,
            'interactive_payload' => $interactiveObject,
        ], 200);
    }

    public function obtenerResumenTrozas(Request $request)
    {
        $data = $request->validate([
            'telefono' => ['required', 'string', 'max:30'],
            // Opcional: permite filtrar a UNA parcela. Si no se envía, se usan todas.
            // Acepta: null/""/0 => todas. Acepta string "todas" => todas. Acepta "123" => 123.
            'id_parcela' => ['nullable'],
        ]);

        $persona = $this->findPersonaByTelefono($data['telefono']);

        if (!$persona) {
            return response()->json(['error' => 'Usuario no encontrado'], 404);
        }

        [$rol, $parcelasIds] = $this->resolveParcelasIdsForPersona($persona);

        if ($rol === null) {
            return response()->json([
                'usuario' => trim(($persona->nom ?? '') . ' ' . ($persona->ap ?? '') . ' ' . ($persona->am ?? '')),
                'rol' => null,
                'mensaje' => 'Tu cuenta no tiene rol o perfil válido.',
            ], 409);
        }

        if ($parcelasIds->isEmpty()) {
            return response()->json([
                'usuario' => trim(($persona->nom ?? '') . ' ' . ($persona->ap ?? '') . ' ' . ($persona->am ?? '')),
                'rol' => $rol,
                'mensaje' => 'No tienes parcelas asignadas actualmente.',
            ], 200);
        }

        [$idParcela, $selectorError] = $this->parseParcelaSelector($request->input('id_parcela'));
        if ($selectorError) {
            return response()->json(['error' => $selectorError], 422);
        }

        if ($idParcela !== null && !$parcelasIds->contains($idParcela)) {
            return response()->json(['error' => 'No tienes acceso a esa parcela'], 403);
        }

        $parcelasFiltro = $idParcela !== null ? collect([$idParcela]) : $parcelasIds;

        $resumenTrozas = DB::table('trozas')
            ->join('especies', 'trozas.id_especie', '=', 'especies.id_especie')
            ->whereIn('trozas.id_parcela', $parcelasFiltro)
            ->select(
                'especies.nom_comun as especie',
                DB::raw('count(*) as total_trozas'),
                DB::raw('avg(trozas.diametro) as diametro_promedio'),
                DB::raw('avg(trozas.longitud) as longitud_promedio')
            )
            ->groupBy('especies.nom_comun')
            ->orderByDesc('total_trozas')
            ->get();

        $totalGeneral = (int) $resumenTrozas->sum('total_trozas');

        return response()->json([
            'usuario' => trim(($persona->nom ?? '') . ' ' . ($persona->ap ?? '') . ' ' . ($persona->am ?? '')),
            'rol' => $rol,
            'filtro' => [
                'id_parcela' => $idParcela,
                'modo' => $idParcela !== null ? 'una' : 'todas',
            ],
            'total_parcelas' => $parcelasFiltro->count(),
            'total_trozas_inventario' => $totalGeneral,
            'desglose_por_especie' => $resumenTrozas,
        ], 200);
    }

    public function obtenerResumenEstimacionesTrozas(Request $request)
    {
        $data = $request->validate([
            'telefono' => ['required', 'string', 'max:30'],
            // Opcional: permite filtrar a UNA parcela. Si no se envía, se usan todas.
            // Acepta: null/""/0 => todas. Acepta string "todas" => todas. Acepta "123" => 123.
            'id_parcela' => ['nullable'],
        ]);

        $persona = $this->findPersonaByTelefono($data['telefono']);

        if (!$persona) {
            return response()->json(['error' => 'Usuario no encontrado'], 404);
        }

        [$rol, $parcelasIds] = $this->resolveParcelasIdsForPersona($persona);

        if ($rol === null) {
            return response()->json([
                'usuario' => trim(($persona->nom ?? '') . ' ' . ($persona->ap ?? '') . ' ' . ($persona->am ?? '')),
                'rol' => null,
                'mensaje' => 'Tu cuenta no tiene rol o perfil válido.',
            ], 409);
        }

        if ($parcelasIds->isEmpty()) {
            return response()->json([
                'usuario' => trim(($persona->nom ?? '') . ' ' . ($persona->ap ?? '') . ' ' . ($persona->am ?? '')),
                'rol' => $rol,
                'mensaje' => 'No tienes parcelas asignadas actualmente.',
            ], 200);
        }

        [$idParcela, $selectorError] = $this->parseParcelaSelector($request->input('id_parcela'));
        if ($selectorError) {
            return response()->json(['error' => $selectorError], 422);
        }

        if ($idParcela !== null && !$parcelasIds->contains($idParcela)) {
            return response()->json(['error' => 'No tienes acceso a esa parcela'], 403);
        }

        $parcelasFiltro = $idParcela !== null ? collect([$idParcela]) : $parcelasIds;

        $rows = DB::table('estimaciones')
            ->join('trozas', 'estimaciones.id_troza', '=', 'trozas.id_troza')
            ->join('especies', 'trozas.id_especie', '=', 'especies.id_especie')
            ->join('tipo_estimaciones', 'estimaciones.id_tipo_e', '=', 'tipo_estimaciones.id_tipo_e')
            ->whereIn('trozas.id_parcela', $parcelasFiltro)
            ->select(
                'especies.nom_comun as especie',
                'tipo_estimaciones.desc_estimacion as tipo_estimacion',
                DB::raw('count(*) as total_estimaciones'),
                DB::raw('sum(estimaciones.calculo) as sum_calculo'),
                DB::raw('sum(estimaciones.biomasa) as sum_biomasa'),
                DB::raw('sum(estimaciones.carbono) as sum_carbono')
            )
            ->groupBy('especies.nom_comun', 'tipo_estimaciones.desc_estimacion')
            ->orderBy('especies.nom_comun')
            ->orderBy('tipo_estimaciones.desc_estimacion')
            ->get();

        $totales = DB::table('estimaciones')
            ->join('trozas', 'estimaciones.id_troza', '=', 'trozas.id_troza')
            ->whereIn('trozas.id_parcela', $parcelasFiltro)
            ->selectRaw('count(*) as total_estimaciones, sum(estimaciones.calculo) as sum_calculo, sum(estimaciones.biomasa) as sum_biomasa, sum(estimaciones.carbono) as sum_carbono')
            ->first();

        return response()->json([
            'usuario' => trim(($persona->nom ?? '') . ' ' . ($persona->ap ?? '') . ' ' . ($persona->am ?? '')),
            'rol' => $rol,
            'filtro' => [
                'id_parcela' => $idParcela,
                'modo' => $idParcela !== null ? 'una' : 'todas',
            ],
            'totales' => [
                'total_estimaciones' => (int) ($totales->total_estimaciones ?? 0),
                'sum_calculo' => (float) ($totales->sum_calculo ?? 0),
                'sum_biomasa' => (float) ($totales->sum_biomasa ?? 0),
                'sum_carbono' => (float) ($totales->sum_carbono ?? 0),
            ],
            'desglose' => $rows,
        ], 200);
    }

    public function obtenerResumenArboles(Request $request)
    {
        $data = $request->validate([
            'telefono' => ['required', 'string', 'max:30'],
            // Opcional: permite filtrar a UNA parcela. Si no se envía, se usan todas.
            // Acepta: null/""/0 => todas. Acepta string "todas" => todas. Acepta "123" => 123.
            'id_parcela' => ['nullable'],
        ]);

        $persona = $this->findPersonaByTelefono($data['telefono']);

        if (!$persona) {
            return response()->json(['error' => 'Usuario no encontrado'], 404);
        }

        [$rol, $parcelasIds] = $this->resolveParcelasIdsForPersona($persona);

        if ($rol === null) {
            return response()->json([
                'usuario' => trim(($persona->nom ?? '') . ' ' . ($persona->ap ?? '') . ' ' . ($persona->am ?? '')),
                'rol' => null,
                'mensaje' => 'Tu cuenta no tiene rol o perfil válido.',
            ], 409);
        }

        if ($parcelasIds->isEmpty()) {
            return response()->json([
                'usuario' => trim(($persona->nom ?? '') . ' ' . ($persona->ap ?? '') . ' ' . ($persona->am ?? '')),
                'rol' => $rol,
                'mensaje' => 'No tienes parcelas asignadas actualmente.',
            ], 200);
        }

        [$idParcela, $selectorError] = $this->parseParcelaSelector($request->input('id_parcela'));
        if ($selectorError) {
            return response()->json(['error' => $selectorError], 422);
        }

        if ($idParcela !== null && !$parcelasIds->contains($idParcela)) {
            return response()->json(['error' => 'No tienes acceso a esa parcela'], 403);
        }

        $parcelasFiltro = $idParcela !== null ? collect([$idParcela]) : $parcelasIds;

        $resumenArboles = DB::table('arboles')
            ->join('especies', 'arboles.id_especie', '=', 'especies.id_especie')
            ->whereIn('arboles.id_parcela', $parcelasFiltro)
            ->select(
                'especies.nom_comun as especie',
                DB::raw('count(*) as total_arboles'),
                DB::raw('avg(arboles.altura_total) as altura_promedio'),
                DB::raw('avg(arboles.diametro_pecho) as dap_promedio')
            )
            ->groupBy('especies.nom_comun')
            ->orderByDesc('total_arboles')
            ->get();

        $totalGeneral = (int) $resumenArboles->sum('total_arboles');

        return response()->json([
            'usuario' => trim(($persona->nom ?? '') . ' ' . ($persona->ap ?? '') . ' ' . ($persona->am ?? '')),
            'rol' => $rol,
            'filtro' => [
                'id_parcela' => $idParcela,
                'modo' => $idParcela !== null ? 'una' : 'todas',
            ],
            'total_parcelas' => $parcelasFiltro->count(),
            'total_arboles_inventario' => $totalGeneral,
            'desglose_por_especie' => $resumenArboles,
        ], 200);
    }

    public function obtenerImpactoAmbiental(Request $request)
    {
        $data = $request->validate([
            'telefono' => ['required', 'string', 'max:30'],
            'id_parcela' => ['nullable'],
        ]);

        $telefono = $this->normalizarTelefono($data['telefono']);
        $persona = $this->findPersonaByTelefono($telefono);

        if (!$persona) {
            return response()->json(['error' => 'Usuario no encontrado'], 404);
        }

        [$rol, $parcelasIds] = $this->resolveParcelasIdsForPersona($persona);

        if ($rol === null) {
            return response()->json([
                'usuario' => trim(($persona->nom ?? '') . ' ' . ($persona->ap ?? '') . ' ' . ($persona->am ?? '')),
                'rol' => null,
                'mensaje' => 'Tu cuenta no tiene rol o perfil válido.',
            ], 409);
        }

        if ($parcelasIds->isEmpty()) {
            return response()->json([
                'usuario' => trim(($persona->nom ?? '') . ' ' . ($persona->ap ?? '') . ' ' . ($persona->am ?? '')),
                'rol' => $rol,
                'mensaje' => 'No tienes parcelas asignadas actualmente.',
            ], 200);
        }

        [$idParcela, $selectorError] = $this->parseParcelaSelector($request->input('id_parcela'));
        if ($selectorError) {
            return response()->json(['error' => $selectorError], 422);
        }

        if ($idParcela !== null && !$parcelasIds->contains($idParcela)) {
            return response()->json(['error' => 'No tienes acceso a esa parcela'], 403);
        }

        return $this->responderImpactoAmbiental($persona, $rol, $parcelasIds, $idParcela);
    }

    public function obtenerResumenEstimacionesArboles(Request $request)
    {
        $data = $request->validate([
            'telefono' => ['required', 'string', 'max:30'],
            // Opcional: permite filtrar a UNA parcela. Si no se envía, se usan todas.
            // Acepta: null/""/0 => todas. Acepta string "todas" => todas. Acepta "123" => 123.
            'id_parcela' => ['nullable'],
        ]);

        $persona = $this->findPersonaByTelefono($data['telefono']);

        if (!$persona) {
            return response()->json(['error' => 'Usuario no encontrado'], 404);
        }

        [$rol, $parcelasIds] = $this->resolveParcelasIdsForPersona($persona);

        if ($rol === null) {
            return response()->json([
                'usuario' => trim(($persona->nom ?? '') . ' ' . ($persona->ap ?? '') . ' ' . ($persona->am ?? '')),
                'rol' => null,
                'mensaje' => 'Tu cuenta no tiene rol o perfil válido.',
            ], 409);
        }

        if ($parcelasIds->isEmpty()) {
            return response()->json([
                'usuario' => trim(($persona->nom ?? '') . ' ' . ($persona->ap ?? '') . ' ' . ($persona->am ?? '')),
                'rol' => $rol,
                'mensaje' => 'No tienes parcelas asignadas actualmente.',
            ], 200);
        }

        [$idParcela, $selectorError] = $this->parseParcelaSelector($request->input('id_parcela'));
        if ($selectorError) {
            return response()->json(['error' => $selectorError], 422);
        }

        if ($idParcela !== null && !$parcelasIds->contains($idParcela)) {
            return response()->json(['error' => 'No tienes acceso a esa parcela'], 403);
        }

        $parcelasFiltro = $idParcela !== null ? collect([$idParcela]) : $parcelasIds;

        $rows = DB::table('estimaciones1')
            ->join('arboles', 'estimaciones1.id_arbol', '=', 'arboles.id_arbol')
            ->join('especies', 'arboles.id_especie', '=', 'especies.id_especie')
            ->join('tipo_estimaciones', 'estimaciones1.id_tipo_e', '=', 'tipo_estimaciones.id_tipo_e')
            ->whereIn('arboles.id_parcela', $parcelasFiltro)
            ->select(
                'especies.nom_comun as especie',
                'tipo_estimaciones.desc_estimacion as tipo_estimacion',
                DB::raw('count(*) as total_estimaciones'),
                DB::raw('sum(estimaciones1.calculo) as sum_calculo'),
                DB::raw('sum(estimaciones1.biomasa) as sum_biomasa'),
                DB::raw('sum(estimaciones1.carbono) as sum_carbono')
            )
            ->groupBy('especies.nom_comun', 'tipo_estimaciones.desc_estimacion')
            ->orderBy('especies.nom_comun')
            ->orderBy('tipo_estimaciones.desc_estimacion')
            ->get();

        $totales = DB::table('estimaciones1')
            ->join('arboles', 'estimaciones1.id_arbol', '=', 'arboles.id_arbol')
            ->whereIn('arboles.id_parcela', $parcelasFiltro)
            ->selectRaw('count(*) as total_estimaciones, sum(estimaciones1.calculo) as sum_calculo, sum(estimaciones1.biomasa) as sum_biomasa, sum(estimaciones1.carbono) as sum_carbono')
            ->first();

        return response()->json([
            'usuario' => trim(($persona->nom ?? '') . ' ' . ($persona->ap ?? '') . ' ' . ($persona->am ?? '')),
            'rol' => $rol,
            'filtro' => [
                'id_parcela' => $idParcela,
                'modo' => $idParcela !== null ? 'una' : 'todas',
            ],
            'totales' => [
                'total_estimaciones' => (int) ($totales->total_estimaciones ?? 0),
                'sum_calculo' => (float) ($totales->sum_calculo ?? 0),
                'sum_biomasa' => (float) ($totales->sum_biomasa ?? 0),
                'sum_carbono' => (float) ($totales->sum_carbono ?? 0),
            ],
            'desglose' => $rows,
        ], 200);
    }

    public function obtenerKitCampo(Request $request)
    {
        $data = $request->validate([
            'telefono' => ['required', 'string', 'max:30'],
        ]);

        $persona = $this->findPersonaByTelefono($data['telefono']);

        if (!$persona) {
            return response()->json(['error' => 'Usuario no encontrado'], 404);
        }

        [$rol, $parcelasIds] = $this->resolveParcelasIdsForPersona($persona);

        if ($rol !== 'Tecnico' && $rol !== 'Productor') {
            return response()->json([
                'error' => 'Tu perfil no tiene acceso al Kit de Campo.',
                'rol' => $rol,
            ], 403);
        }

        $parcelas = $parcelasIds->isEmpty()
            ? collect()
            : DB::table('parcelas')
                ->whereIn('id_parcela', $parcelasIds)
                ->orderBy('nom_parcela')
                ->pluck('nom_parcela');

        $especies = DB::table('especies')
            ->orderBy('nom_comun')
            ->pluck('nom_comun');

        // Formato amigable para WhatsApp / n8n
        $parcelasLista = $parcelas->isEmpty()
            ? 'No tienes parcelas asignadas.'
            : '• ' . $parcelas->implode("\n• ");

        $especiesLista = $especies->isEmpty()
            ? 'No hay especies registradas.'
            : $especies->implode(', ');

        return response()->json([
            'ok' => true,
            'usuario' => trim(($persona->nom ?? '') . ' ' . ($persona->ap ?? '') . ' ' . ($persona->am ?? '')),
            'rol' => $rol,
            'parcelas_lista' => $parcelasLista,
            'especies_lista' => $especiesLista,
        ], 200);
    }

    private function responderImpactoAmbiental($persona, string $rol, $parcelasIds, ?int $idParcela)
    {
        if ($rol !== 'Tecnico' && $rol !== 'Productor') {
            return response()->json([
                'ok' => false,
                'mensaje' => 'Tu perfil no tiene acceso al Impacto Ambiental.',
            ], 403);
        }

        if ($parcelasIds->isEmpty()) {
            return response()->json([
                'ok' => false,
                'mensaje' => 'No tienes parcelas asignadas actualmente.',
            ], 200);
        }

        $impacto = $this->construirImpactoAmbiental($persona, $rol, $parcelasIds, $idParcela);

        $interactivePayload = [
            'type' => 'button',
            'body' => [
                'text' => "✨ ¿Quieres llevar este diagnóstico a un PDF formal?\n\nPuedo generar un informe listo para compartir o archivar.",
            ],
            'footer' => [
                'text' => 'SIGMAD | Reporte ambiental',
            ],
            'action' => [
                'buttons' => [
                    [
                        'type' => 'reply',
                        'reply' => [
                            'id' => 'impacto_generar_pdf',
                            'title' => '📄 Generar PDF',
                        ],
                    ],
                    [
                        'type' => 'reply',
                        'reply' => [
                            'id' => 'impacto_no_pdf',
                            'title' => 'Ahora no',
                        ],
                    ],
                ],
            ],
        ];

        return response()->json([
            'ok' => true,
            'tipo' => 'impacto_ambiental',
            'mensaje' => $impacto['resumen_whatsapp'],
            'interactive_payload' => $interactivePayload,
            'reporte' => $impacto,
        ], 200);
    }

    private function construirImpactoAmbiental($persona, string $rol, $parcelasIds, ?int $idParcela): array
    {
        $parcelasFiltro = $idParcela !== null ? collect([$idParcela]) : $parcelasIds;
        $nombreUsuario = trim(($persona->nom ?? '') . ' ' . ($persona->ap ?? '') . ' ' . ($persona->am ?? ''));
        $modo = $idParcela !== null ? 'una' : 'todas';

        $totalesArboles = DB::table('arboles')
            ->whereIn('id_parcela', $parcelasFiltro)
            ->selectRaw('count(*) as total_arboles, avg(altura_total) as altura_promedio, avg(diametro_pecho) as dap_promedio')
            ->first();

        $totalesTrozas = DB::table('trozas')
            ->whereIn('id_parcela', $parcelasFiltro)
            ->selectRaw('count(*) as total_trozas, avg(diametro) as diametro_promedio, avg(longitud) as longitud_promedio')
            ->first();

        $estimacionesArboles = DB::table('estimaciones1 as e1')
            ->join('arboles as a', 'e1.id_arbol', '=', 'a.id_arbol')
            ->whereIn('a.id_parcela', $parcelasFiltro)
            ->selectRaw('count(*) as total_estimaciones, sum(e1.calculo) as sum_calculo, sum(e1.biomasa) as sum_biomasa, sum(e1.carbono) as sum_carbono')
            ->first();

        $estimacionesTrozas = DB::table('estimaciones as e')
            ->join('trozas as t', 'e.id_troza', '=', 't.id_troza')
            ->whereIn('t.id_parcela', $parcelasFiltro)
            ->selectRaw('count(*) as total_estimaciones, sum(e.calculo) as sum_calculo, sum(e.biomasa) as sum_biomasa, sum(e.carbono) as sum_carbono')
            ->first();

        $especiesArboles = DB::table('arboles')
            ->join('especies', 'arboles.id_especie', '=', 'especies.id_especie')
            ->whereIn('arboles.id_parcela', $parcelasFiltro)
            ->select('especies.nom_comun as especie', DB::raw('count(*) as total'))
            ->groupBy('especies.nom_comun')
            ->orderByDesc('total')
            ->get();

        $especiesTrozas = DB::table('trozas')
            ->join('especies', 'trozas.id_especie', '=', 'especies.id_especie')
            ->whereIn('trozas.id_parcela', $parcelasFiltro)
            ->select('especies.nom_comun as especie', DB::raw('count(*) as total'))
            ->groupBy('especies.nom_comun')
            ->orderByDesc('total')
            ->get();

        $totalArboles = (int) ($totalesArboles->total_arboles ?? 0);
        $totalTrozas = (int) ($totalesTrozas->total_trozas ?? 0);
        $totalEstimacionesArboles = (int) ($estimacionesArboles->total_estimaciones ?? 0);
        $totalEstimacionesTrozas = (int) ($estimacionesTrozas->total_estimaciones ?? 0);

        $biomasaTotal = (float) (($estimacionesArboles->sum_biomasa ?? 0) + ($estimacionesTrozas->sum_biomasa ?? 0));
        $carbonoTotal = (float) (($estimacionesArboles->sum_carbono ?? 0) + ($estimacionesTrozas->sum_carbono ?? 0));
        $calculoTotal = (float) (($estimacionesArboles->sum_calculo ?? 0) + ($estimacionesTrozas->sum_calculo ?? 0));

        $coberturaArboles = $totalArboles > 0 ? round(($totalEstimacionesArboles / $totalArboles) * 100, 1) : 0.0;
        $coberturaTrozas = $totalTrozas > 0 ? round(($totalEstimacionesTrozas / $totalTrozas) * 100, 1) : 0.0;

        $especieDominanteArboles = $especiesArboles->first();
        $especieDominanteTrozas = $especiesTrozas->first();

        $diversidadArboles = $especiesArboles->count();
        $diversidadTrozas = $especiesTrozas->count();

        $alertas = [];
        $recomendaciones = [];

        if ($totalArboles === 0 && $totalTrozas === 0) {
            $alertas[] = 'No hay inventario suficiente para emitir un diagnóstico ambiental confiable.';
            $recomendaciones[] = 'Registra primero árboles o trozas en la parcela para obtener métricas útiles.';
        }

        if ($totalArboles > 0 && $coberturaArboles < 80) {
            $alertas[] = "Cobertura de estimación en árboles todavía incompleta ({$coberturaArboles}%).";
            $recomendaciones[] = 'Completa las estimaciones de árboles pendientes para mejorar el diagnóstico.';
        }

        if ($totalTrozas > 0 && $coberturaTrozas < 80) {
            $alertas[] = "Cobertura de estimación en trozas todavía incompleta ({$coberturaTrozas}%).";
            $recomendaciones[] = 'Completa las estimaciones de trozas pendientes para afinar biomasa y carbono.';
        }

        if ($especieDominanteArboles && $totalArboles > 0) {
            $dominancia = round(((int) $especieDominanteArboles->total / $totalArboles) * 100, 1);
            if ($dominancia >= 70) {
                $alertas[] = "Alta dominancia de una sola especie en árboles ({$dominancia}%).";
                $recomendaciones[] = 'Evalúa si conviene diversificar la composición para reducir vulnerabilidad.';
            }
        }

        if ($especieDominanteTrozas && $totalTrozas > 0) {
            $dominancia = round(((int) $especieDominanteTrozas->total / $totalTrozas) * 100, 1);
            if ($dominancia >= 70) {
                $alertas[] = "Alta dominancia de una sola especie en trozas ({$dominancia}%).";
                $recomendaciones[] = 'Revisa si la estructura del aprovechamiento está demasiado concentrada en una especie.';
            }
        }

        if ($biomasaTotal > 0 && $carbonoTotal > 0) {
            $recomendaciones[] = 'Mantén el monitoreo periódico: la biomasa y el carbono permiten seguir la evolución del rodal.';
        }

        if (empty($recomendaciones)) {
            $recomendaciones[] = 'La información disponible luce consistente. Puedes generar un reporte formal para seguimiento.';
        }

        $nivel = 'verde';
        if ($totalArboles === 0 && $totalTrozas === 0) {
            $nivel = 'rojo';
        } elseif (($totalArboles > 0 && $coberturaArboles < 80) || ($totalTrozas > 0 && $coberturaTrozas < 80) || count($alertas) >= 2) {
            $nivel = 'amarillo';
        }

        $alcanceTexto = $modo === 'una' ? 'una parcela' : 'todas tus parcelas';

        $analisisIA = $this->generarAnalisisImpactoAmbientalConIA([
            'usuario' => $nombreUsuario,
            'rol' => $rol,
            'filtro' => [
                'id_parcela' => $idParcela,
                'modo' => $modo,
            ],
            'totales' => [
                'arboles' => $totalArboles,
                'trozas' => $totalTrozas,
                'estimaciones_arboles' => $totalEstimacionesArboles,
                'estimaciones_trozas' => $totalEstimacionesTrozas,
                'biomasa_total' => $biomasaTotal,
                'carbono_total' => $carbonoTotal,
                'calculo_total' => $calculoTotal,
            ],
            'cobertura' => [
                'arboles' => $coberturaArboles,
                'trozas' => $coberturaTrozas,
            ],
            'especies_dominantes' => [
                'arboles' => $especiesArboles->take(5)->values(),
                'trozas' => $especiesTrozas->take(5)->values(),
            ],
            'alertas' => $alertas,
            'recomendaciones' => $recomendaciones,
            'nivel' => $nivel,
        ]);

        $resumenWhatsapp = "🌿 *Diagnóstico de Impacto Ambiental*\n"
            . "Usuario: *{$nombreUsuario}*\n"
            . "Alcance: *{$alcanceTexto}*\n\n"
            . "📊 *Métricas principales*\n"
            . "• Árboles registrados: *{$totalArboles}*\n"
            . "• Trozas registradas: *{$totalTrozas}*\n"
            . "• Estimaciones en árboles: *{$totalEstimacionesArboles}* ({$coberturaArboles}% cobertura)\n"
            . "• Estimaciones en trozas: *{$totalEstimacionesTrozas}* ({$coberturaTrozas}% cobertura)\n"
            . "• Biomasa total estimada: *" . number_format($biomasaTotal, 2, '.', ',') . "*\n"
            . "• Carbono total estimado: *" . number_format($carbonoTotal, 2, '.', ',') . "*\n"
            . "• Cálculo total: *" . number_format($calculoTotal, 2, '.', ',') . "*\n\n"
            . "🔎 *Lectura rápida*\n"
            . "• Árbol dominante: *" . ($especieDominanteArboles?->especie ?? 'Sin datos') . "*\n"
            . "• Troza dominante: *" . ($especieDominanteTrozas?->especie ?? 'Sin datos') . "*\n"
            . "• Diversidad observada: *{$diversidadArboles}* especies en árboles y *{$diversidadTrozas}* en trozas\n\n"
            . "🧭 *Estado general:* *" . strtoupper($nivel) . "*\n";

        if (!empty($alertas)) {
            $resumenWhatsapp .= "\n⚠️ *Alertas*\n• " . implode("\n• ", $alertas) . "\n";
        }

        if (!empty($analisisIA['resumen'])) {
            $resumenWhatsapp .= "\n🧠 *Lectura inteligente*\n{$analisisIA['resumen']}\n";
        }

        $resumenWhatsapp .= "\n✅ *Qué puedes hacer ahora*\n• Completa los registros faltantes\n• Revisa si conviene diversificar especies\n• Genera un reporte PDF para seguimiento técnico\n• Si quieres, puedo convertir esto en un texto más ejecutivo o más técnico\n";

        return [
            'ok' => true,
            'usuario' => $nombreUsuario,
            'rol' => $rol,
            'filtro' => [
                'id_parcela' => $idParcela,
                'modo' => $modo,
            ],
            'totales' => [
                'arboles' => $totalArboles,
                'trozas' => $totalTrozas,
                'estimaciones_arboles' => $totalEstimacionesArboles,
                'estimaciones_trozas' => $totalEstimacionesTrozas,
                'biomasa_total' => $biomasaTotal,
                'carbono_total' => $carbonoTotal,
                'calculo_total' => $calculoTotal,
            ],
            'cobertura' => [
                'arboles' => $coberturaArboles,
                'trozas' => $coberturaTrozas,
            ],
            'especies_dominantes' => [
                'arboles' => $especiesArboles->take(5)->values(),
                'trozas' => $especiesTrozas->take(5)->values(),
            ],
            'alertas' => $alertas,
            'recomendaciones' => $recomendaciones,
            'nivel' => $nivel,
            'analisis_ia' => $analisisIA,
            'resumen_whatsapp' => $resumenWhatsapp,
        ];
    }

    private function generarAnalisisImpactoAmbientalConIA(array $reporte): array
    {
        $apiKey = (string) config('services.openai.key', '');
        $model = (string) config('services.openai.model', 'gpt-5.4-mini');
        $baseUrl = rtrim((string) config('services.openai.base_url', 'https://api.openai.com/v1'), '/');

        if (trim($apiKey) === '') {
            return [
                'provider' => null,
                'model' => null,
                'resumen' => '',
                'recomendacion' => '',
                'raw' => null,
            ];
        }

        $prompt = [
            'system' => 'Eres un analista forestal experto. Debes interpretar únicamente los datos entregados, sin inventar cifras. Responde en español claro, profesional y breve. Devuelve SOLO JSON válido con claves: resumen, que_pasa, que_hacer, riesgo, nota.',
            'user' => json_encode($reporte, JSON_UNESCAPED_UNICODE | JSON_UNESCAPED_SLASHES),
        ];

        try {
            $response = Http::withToken($apiKey)
                ->acceptJson()
                ->timeout(25)
                ->post($baseUrl . '/chat/completions', [
                    'model' => $model,
                    'temperature' => 0.2,
                    'messages' => [
                        ['role' => 'system', 'content' => $prompt['system']],
                        ['role' => 'user', 'content' => $prompt['user']],
                    ],
                ]);

            if (!$response->successful()) {
                return [
                    'provider' => 'openai',
                    'model' => $model,
                    'resumen' => '',
                    'recomendacion' => '',
                    'raw' => [
                        'error' => 'http_error',
                        'status' => $response->status(),
                    ],
                ];
            }

            $content = (string) data_get($response->json(), 'choices.0.message.content', '');
            $decoded = json_decode($content, true);

            if (!is_array($decoded)) {
                return [
                    'provider' => 'openai',
                    'model' => $model,
                    'resumen' => trim($content),
                    'recomendacion' => '',
                    'raw' => $response->json(),
                ];
            }

            return [
                'provider' => 'openai',
                'model' => $model,
                'resumen' => trim((string) ($decoded['resumen'] ?? '')),
                'que_pasa' => trim((string) ($decoded['que_pasa'] ?? '')),
                'que_hacer' => trim((string) ($decoded['que_hacer'] ?? '')),
                'riesgo' => trim((string) ($decoded['riesgo'] ?? '')),
                'nota' => trim((string) ($decoded['nota'] ?? '')),
                'recomendacion' => trim((string) ($decoded['que_hacer'] ?? '')),
                'raw' => $response->json(),
            ];
        } catch (\Throwable $e) {
            return [
                'provider' => 'openai',
                'model' => $model,
                'resumen' => '',
                'recomendacion' => '',
                'raw' => [
                    'error' => 'exception',
                ],
            ];
        }
    }

    private function iniciarAsistente(string $telefono, $parcelasIds)
    {
        BotSesion::where('telefono', $telefono)->delete();

        BotSesion::create([
            'telefono' => $telefono,
            'estado' => 'ESPERANDO_PARCELA',
            'payload' => [],
        ]);

        $parcelas = $parcelasIds->isEmpty()
            ? collect()
            : DB::table('parcelas')
                ->whereIn('id_parcela', $parcelasIds)
                ->orderBy('nom_parcela')
                ->pluck('nom_parcela');

        $parcelasTexto = $parcelas->isEmpty()
            ? 'No tienes parcelas asignadas.'
            : '• ' . $parcelas->implode("\n• ");

        return response()->json([
            'ok' => true,
            'mensaje' => "🤖 *Asistente de Captura Iniciado*\n\n🔐 Acceso verificado: *Técnico* ✅\nHola. Te guiaré paso a paso para registrar tus datos.\n_Escribe 'cancelar' en cualquier momento para salir._\n\n📍 *Tus Parcelas:*\n{$parcelasTexto}\n\n👉 *Escribe el nombre de la parcela en la que estás trabajando:*",
        ], 200);
    }

    private function iniciarFlujoEstimaciones(string $telefono, $parcelasIds)
    {
        BotSesion::where('telefono', $telefono)->delete();

        BotSesion::create([
            'telefono' => $telefono,
            'estado' => self::ESPERANDO_PARCELA_ESTIMACION,
            'payload' => [],
        ]);

        $parcelas = $parcelasIds->isEmpty()
            ? collect()
            : DB::table('parcelas')
                ->whereIn('id_parcela', $parcelasIds)
                ->orderBy('nom_parcela')
                ->pluck('nom_parcela');

        $parcelasTexto = $parcelas->isEmpty()
            ? 'No tienes parcelas asignadas.'
            : '• ' . $parcelas->implode("\n• ");

        return response()->json([
            'ok' => true,
            'estado' => self::ESPERANDO_PARCELA_ESTIMACION,
            'mensaje' => "🧮 *Generar Estimaciones*\n\nSelecciona la parcela para procesar pendientes.\n\n📍 *Tus Parcelas:*\n{$parcelasTexto}\n\n👉 Escribe el nombre de la parcela.",
        ], 200);
    }

    private function iniciarFlujoImportacionExcel(string $telefono, $parcelasIds)
    {
        $sesion = BotSesion::where('telefono', $telefono)->first();
        $payload = $sesion?->payload ?? [];
        $especiesTexto = $this->obtenerEspeciesDisponiblesTexto();

        BotSesion::updateOrCreate(
            ['telefono' => $telefono],
            [
                'estado' => self::ESPERANDO_PARCELA_EXCEL,
                'payload' => $payload,
            ]
        );

        return response()->json([
            'tipo' => 'envio_documento',
            'url' => 'https://woodwise.me/storage/plantillas/plantilla_inventario_sigmad.xlsx',
            'mensaje' => "📄 *Plantilla Oficial de Inventario SIGMAD*\n\n"
                . "📚 *Especies disponibles (actualizado):*\n{$especiesTexto}\n\n"
                . "¿A que parcela pertenecen los datos que vas a subir?",
        ], 200);
    }

    private function responderIngresoArchivo()
    {
        return response()->json([
            'ok' => true,
            'mensaje' => "📎 *Ingreso por archivo listo*\n\n"
                . "Solo envía tu archivo directamente en este chat y yo lo detectaré automáticamente para registrarlo.\n\n"
                . "✨ Puedes mandar imágenes, PDF o documentos compatibles sin llenar formularios.\n"
                . "En cuanto llegue el archivo, el sistema lo tomará y comenzará el registro de forma inteligente y segura.",
        ], 200);
    }

    private function procesarParcelaExcel(BotSesion $sesion, string $mensajeCrudo, $parcelasIds)
    {
        $selector = trim($mensajeCrudo);
        if (str_starts_with($selector, 'excel_parcela_')) {
            $selector = substr($selector, strlen('excel_parcela_'));
        }
        $busquedaNormalizada = $this->normalizarTextoBusqueda($selector);

        $parcela = DB::table('parcelas')
            ->whereIn('id_parcela', $parcelasIds)
            ->select('id_parcela', 'nom_parcela')
            ->get()
            ->first(function ($row) use ($selector, $busquedaNormalizada) {
                if (is_numeric($selector) && (int) $selector > 0 && (int) $row->id_parcela === (int) $selector) {
                    return true;
                }

                if ($busquedaNormalizada === '') {
                    return false;
                }

                $nombreNormalizado = $this->normalizarTextoBusqueda((string) $row->nom_parcela);

                return $nombreNormalizado === $busquedaNormalizada
                    || str_contains($nombreNormalizado, $busquedaNormalizada)
                    || str_contains($busquedaNormalizada, $nombreNormalizado);
            });

        if (!$parcela) {
            return response()->json([
                'ok' => false,
                'estado' => self::ESPERANDO_PARCELA_EXCEL,
                'mensaje' => "❌ No tienes asignada una parcela con ese nombre. Por favor, verifica e intenta de nuevo:",
            ], 200);
        }

        $payload = $sesion->payload ?? [];
        if (isset($payload['excel_data'])) {
            return $this->finalizarCargaExcel($sesion, $parcela, $payload['excel_data']);
        }
        $payload['id_parcela'] = (int) $parcela->id_parcela;
        $payload['nom_parcela'] = (string) $parcela->nom_parcela;

        $sesion->update([
            'estado' => self::ESPERANDO_ARCHIVO_EXCEL,
            'payload' => $payload,
        ]);

        return response()->json([
            'ok' => true,
            'estado' => self::ESPERANDO_ARCHIVO_EXCEL,
            'mensaje' => "✅ Parcela *{$parcela->nom_parcela}* seleccionada con exito.\n\n"
                . "Por favor, adjunta tu archivo de Excel (.xlsx) o CSV completado en este chat para iniciar la carga masiva.",
        ], 200);
    }

    private function procesarArchivoExcel(BotSesion $sesion, string $mensajeCrudo)
    {
        return response()->json([
            'ok' => false,
            'estado' => self::ESPERANDO_ARCHIVO_EXCEL,
            'mensaje' => "⚠️ Por favor, sube el archivo de Excel (.xlsx o .csv). Si deseas cancelar el proceso, escribe *Menu*.",
        ], 200);
    }

    private function finalizarCargaExcel(BotSesion $sesion, object $parcela, array $excelData)
    {
        $trozas = collect($excelData['trozas'] ?? [])->map(function ($row) {
            if (!isset($row['especie_texto']) && isset($row['especie'])) {
                $row['especie_texto'] = $row['especie'];
            }

            if (!isset($row['diametro']) && isset($row['diametro_1'])) {
                $row['diametro'] = $row['diametro_1'];
            }

            if (!isset($row['diametro_otro_extremo']) && isset($row['diametro_2'])) {
                $row['diametro_otro_extremo'] = $row['diametro_2'];
            }

            return $row;
        });

        $arboles = collect($excelData['arboles'] ?? [])->map(function ($row) {
            if (!isset($row['especie_texto']) && isset($row['especie'])) {
                $row['especie_texto'] = $row['especie'];
            }

            return $row;
        });

        if ($trozas->isEmpty() && $arboles->isEmpty()) {
            return response()->json([
                'ok' => false,
                'estado' => self::ESPERANDO_PARCELA_EXCEL,
                'mensaje' => '⚠️ No se detectaron filas validas en el archivo. Por favor, revisa la plantilla e intenta de nuevo.',
            ], 200);
        }

        $idParcela = (int) $parcela->id_parcela;
        $nomParcela = (string) $parcela->nom_parcela;

        $resultado = DB::transaction(function () use ($trozas, $arboles, $idParcela) {
            $receiptTrozas = [];
            $receiptArboles = [];

            foreach ($trozas->values() as $idx => $row) {
                try {
                    [$idEspecie, $especieNombre, $especieError] = $this->resolveEspecieForRow($row['id_especie'] ?? null, $row['especie_texto'] ?? null);
                    if ($especieError) {
                        $receiptTrozas[] = [
                            'ok' => false,
                            'fila' => $idx + 1,
                            'error' => $especieError,
                        ];
                        continue;
                    }

                    $densidad = $row['densidad'] ?? null;
                    if ($densidad === null || (float) $densidad <= 0) {
                        $receiptTrozas[] = [
                            'ok' => false,
                            'fila' => $idx + 1,
                            'error' => 'Densidad invalida o faltante.',
                        ];
                        continue;
                    }

                    $payload = [
                        'id_especie' => (int) $idEspecie,
                        'id_parcela' => $idParcela,
                        'diametro' => (float) ($row['diametro'] ?? 0),
                        'longitud' => (float) ($row['longitud'] ?? 0),
                        'densidad' => (float) $densidad,
                        'diametro_otro_extremo' => isset($row['diametro_otro_extremo']) ? (float) $row['diametro_otro_extremo'] : null,
                        'diametro_medio' => isset($row['diametro_medio']) ? (float) $row['diametro_medio'] : null,
                        'created_at' => now(),
                        'updated_at' => now(),
                    ];

                    if ($payload['diametro'] <= 0 || $payload['longitud'] <= 0) {
                        $receiptTrozas[] = [
                            'ok' => false,
                            'fila' => $idx + 1,
                            'error' => 'Diametro o longitud invalidos.',
                        ];
                        continue;
                    }

                    $idTroza = (int) DB::table('trozas')->insertGetId($payload);

                    $receiptTrozas[] = [
                        'ok' => true,
                        'fila' => $idx + 1,
                        'id_troza' => $idTroza,
                        'especie' => $especieNombre,
                    ];
                } catch (\Throwable $e) {
                    $receiptTrozas[] = [
                        'ok' => false,
                        'fila' => $idx + 1,
                        'error' => 'Error inesperado al insertar la troza.',
                    ];
                }
            }

            foreach ($arboles->values() as $idx => $row) {
                try {
                    [$idEspecie, $especieNombre, $especieError] = $this->resolveEspecieForRow($row['id_especie'] ?? null, $row['especie_texto'] ?? null);
                    if ($especieError) {
                        $receiptArboles[] = [
                            'ok' => false,
                            'fila' => $idx + 1,
                            'error' => $especieError,
                        ];
                        continue;
                    }

                    $payload = [
                        'id_especie' => (int) $idEspecie,
                        'id_parcela' => $idParcela,
                        'altura_total' => (float) ($row['altura_total'] ?? 0),
                        'diametro_pecho' => (float) ($row['diametro_pecho'] ?? 0),
                        'created_at' => now(),
                        'updated_at' => now(),
                    ];

                    if ($payload['altura_total'] <= 0 || $payload['diametro_pecho'] <= 0) {
                        $receiptArboles[] = [
                            'ok' => false,
                            'fila' => $idx + 1,
                            'error' => 'Altura o DAP invalidos.',
                        ];
                        continue;
                    }

                    $idArbol = (int) DB::table('arboles')->insertGetId($payload);

                    $receiptArboles[] = [
                        'ok' => true,
                        'fila' => $idx + 1,
                        'id_arbol' => $idArbol,
                        'especie' => $especieNombre,
                    ];
                } catch (\Throwable $e) {
                    $receiptArboles[] = [
                        'ok' => false,
                        'fila' => $idx + 1,
                        'error' => 'Error inesperado al insertar el arbol.',
                    ];
                }
            }

            return [
                'trozas' => $receiptTrozas,
                'arboles' => $receiptArboles,
            ];
        });

        $sesion->delete();

        $trozasOk = collect($resultado['trozas'])->where('ok', true)->count();
        $trozasErr = collect($resultado['trozas'])->where('ok', false)->count();
        $arbolesOk = collect($resultado['arboles'])->where('ok', true)->count();
        $arbolesErr = collect($resultado['arboles'])->where('ok', false)->count();

        $errores = collect($resultado['trozas'])
            ->concat($resultado['arboles'])
            ->where('ok', false)
            ->values();

        $detalleErrores = '';
        if ($errores->isNotEmpty()) {
            $detalleErrores = "\n\n⚠️ Errores detectados (resumen):";
            $resumen = $errores->groupBy('error')->map->count();
            foreach ($resumen as $motivo => $total) {
                $detalleErrores .= "\n• {$motivo}: {$total} fila(s)";
            }
        }

        return response()->json([
            'ok' => true,
            'estado' => 'FINALIZADO',
            'mensaje' => "✅ Carga finalizada para *{$nomParcela}*.\n\n"
                . "🌲 Arboles guardados: *{$arbolesOk}* (errores: {$arbolesErr})\n"
                . "🪵 Trozas guardadas: *{$trozasOk}* (errores: {$trozasErr})"
                . $detalleErrores,
            'detalle' => $resultado,
        ], 200);
    }

    private function procesarParcelaEstimacion(BotSesion $sesion, string $mensajeCrudo, $parcelasIds)
    {
        $selector = trim($mensajeCrudo);
        $busquedaNormalizada = $this->normalizarTextoBusqueda($selector);

        $parcela = DB::table('parcelas')
            ->whereIn('id_parcela', $parcelasIds)
            ->select('id_parcela', 'nom_parcela')
            ->get()
            ->first(function ($row) use ($selector, $busquedaNormalizada) {
                if (is_numeric($selector) && (int) $selector > 0 && (int) $row->id_parcela === (int) $selector) {
                    return true;
                }

                if ($busquedaNormalizada === '') {
                    return false;
                }

                $nombreNormalizado = $this->normalizarTextoBusqueda((string) $row->nom_parcela);

                return $nombreNormalizado === $busquedaNormalizada
                    || str_contains($nombreNormalizado, $busquedaNormalizada)
                    || str_contains($busquedaNormalizada, $nombreNormalizado);
            });

        if (!$parcela) {
            return response()->json([
                'ok' => false,
                'estado' => self::ESPERANDO_PARCELA_ESTIMACION,
                'mensaje' => "⚠️ No encontré la parcela '*{$selector}*' en tus asignaciones.\n\nEscribe el nombre correcto o envía *cancelar* para salir.",
            ], 200);
        }

        $idParcela = (int) $parcela->id_parcela;

        $pendientesArboles = (int) DB::table('arboles as a')
            ->where('a.id_parcela', $idParcela)
            ->whereNotExists(function ($q) {
                $q->select(DB::raw(1))
                    ->from('estimaciones1 as e1')
                    ->whereColumn('e1.id_arbol', 'a.id_arbol');
            })
            ->count();

        $pendientesTrozas = (int) DB::table('trozas as t')
            ->where('t.id_parcela', $idParcela)
            ->whereNotExists(function ($q) {
                $q->select(DB::raw(1))
                    ->from('estimaciones as e')
                    ->whereColumn('e.id_troza', 't.id_troza');
            })
            ->count();

        $payload = $sesion->payload ?? [];
        $payload['id_parcela'] = $idParcela;
        $payload['nom_parcela'] = (string) $parcela->nom_parcela;
        $payload['pendientes_arboles'] = $pendientesArboles;
        $payload['pendientes_trozas'] = $pendientesTrozas;

        $sesion->update([
            'estado' => self::ESPERANDO_ALCANCE_ESTIMACION,
            'payload' => $payload,
        ]);

        return response()->json([
            'ok' => true,
            'estado' => self::ESPERANDO_ALCANCE_ESTIMACION,
            'mensaje' => "✅ Parcela *{$parcela->nom_parcela}* seleccionada.\n\n📌 Pendientes detectados:\n• Árboles sin estimar: *{$pendientesArboles}*\n• Trozas sin estimar: *{$pendientesTrozas}*\n\n¿Qué deseas estimar?\n*1* Árboles\n*2* Trozas\n*3* Ambos",
        ], 200);
    }

    private function procesarAlcanceEstimacion(BotSesion $sesion, string $mensajeCrudo)
    {
        $entrada = $this->normalizarTextoBusqueda($mensajeCrudo);

        $opcion = match (true) {
            in_array($entrada, ['1', 'arbol', 'arboles'], true) => '1',
            in_array($entrada, ['2', 'troza', 'trozas'], true) => '2',
            in_array($entrada, ['3', 'ambos', 'todo', 'todas'], true) => '3',
            default => trim($mensajeCrudo),
        };

        if (!in_array($opcion, ['1', '2', '3'], true)) {
            return response()->json([
                'ok' => false,
                'estado' => self::ESPERANDO_ALCANCE_ESTIMACION,
                'mensaje' => "⚠️ Opción no válida. Responde con número o texto:\n*1* / *Arboles*\n*2* / *Trozas*\n*3* / *Ambos*",
            ], 200);
        }

        $payload = $sesion->payload ?? [];
        $idParcela = (int) ($payload['id_parcela'] ?? 0);
        $nomParcela = (string) ($payload['nom_parcela'] ?? $idParcela);

        if ($idParcela <= 0) {
            $sesion->delete();

            return response()->json([
                'ok' => false,
                'mensaje' => '⚠️ Se perdió el contexto de parcela. Inicia de nuevo desde el menú principal.',
            ], 200);
        }

        if ($opcion === '1') {
            try {
                $resultado = $this->ejecutarCalculosPendientes($idParcela, $opcion, null);
            } catch (\Throwable $e) {
                return response()->json([
                    'ok' => false,
                    'estado' => self::ESPERANDO_ALCANCE_ESTIMACION,
                    'mensaje' => '❌ No se pudieron generar estimaciones en este momento. Intenta nuevamente.',
                ], 200);
            }

            $sesion->delete();

            return response()->json([
                'ok' => true,
                'estado' => 'FINALIZADO',
                'mensaje' => "✅ Estimaciones generadas para *{$nomParcela}*.\n\n🌳 Árboles procesados: *{$resultado['arboles']}*\n🪵 Trozas procesadas: *{$resultado['trozas']}*\n\nPuedes volver al menú para consultar resultados.",
            ], 200);
        }

        $payload['alcance_estimacion'] = $opcion;
        $sesion->update([
            'estado' => self::ESPERANDO_FORMULA_ESTIMACION,
            'payload' => $payload,
        ]);

        return response()->json([
            'ok' => true,
            'estado' => self::ESPERANDO_FORMULA_ESTIMACION,
            'mensaje' => "✏️ Selecciona la fórmula para estimar *trozas*:\n*1* Smalian\n*2* Huber\n*3* Newton\n*4* Cono truncado",
        ], 200);
    }

    private function procesarFormulaEstimacion(BotSesion $sesion, string $mensajeCrudo)
    {
        $entradaFormula = $this->normalizarTextoBusqueda($mensajeCrudo);

        $opcionFormula = match (true) {
            in_array($entradaFormula, ['1', 'smalian'], true) => '1',
            in_array($entradaFormula, ['2', 'huber'], true) => '2',
            in_array($entradaFormula, ['3', 'newton'], true) => '3',
            in_array($entradaFormula, ['4', 'cono truncado', 'cono', 'truncado'], true) => '4',
            default => trim($mensajeCrudo),
        };

        if (!in_array($opcionFormula, ['1', '2', '3', '4'], true)) {
            return response()->json([
                'ok' => false,
                'estado' => self::ESPERANDO_FORMULA_ESTIMACION,
                'mensaje' => "⚠️ Fórmula no válida. Responde con número o nombre:\n*1* / *Smalian*\n*2* / *Huber*\n*3* / *Newton*\n*4* / *Cono truncado*.",
            ], 200);
        }

        $payload = $sesion->payload ?? [];
        $idParcela = (int) ($payload['id_parcela'] ?? 0);
        $nomParcela = (string) ($payload['nom_parcela'] ?? $idParcela);
        $alcance = (string) ($payload['alcance_estimacion'] ?? '');

        if ($idParcela <= 0 || !in_array($alcance, ['2', '3'], true)) {
            $sesion->delete();

            return response()->json([
                'ok' => false,
                'mensaje' => '⚠️ Se perdió el contexto de estimación. Inicia de nuevo desde el menú principal.',
            ], 200);
        }

        try {
            $resultado = $this->ejecutarCalculosPendientes($idParcela, $alcance, (int) $opcionFormula);
        } catch (\Throwable $e) {
            return response()->json([
                'ok' => false,
                'estado' => self::ESPERANDO_FORMULA_ESTIMACION,
                'mensaje' => '❌ No se pudieron generar estimaciones en este momento. Intenta nuevamente.',
            ], 200);
        }

        $sesion->delete();

        $mapaFormulas = [
            '1' => 'Smalian',
            '2' => 'Huber',
            '3' => 'Newton',
            '4' => 'Cono truncado',
        ];

        return response()->json([
            'ok' => true,
            'estado' => 'FINALIZADO',
            'mensaje' => "✅ Estimaciones generadas para *{$nomParcela}* con fórmula *{$mapaFormulas[$opcionFormula]}*.\n\n🌳 Árboles procesados: *{$resultado['arboles']}*\n🪵 Trozas procesadas: *{$resultado['trozas']}*\n\nPuedes volver al menú para consultar resultados.",
        ], 200);
    }

    private function ejecutarCalculosPendientes(int $idParcela, string $alcance, ?int $idFormulaTroza): array
    {
        return DB::transaction(function () use ($idParcela, $alcance, $idFormulaTroza) {
            $idTipoEstimacion = DB::table('tipo_estimaciones')
                ->whereRaw('LOWER(desc_estimacion) = ?', [mb_strtolower('Volumen maderable')])
                ->value('id_tipo_e');

            if (!$idTipoEstimacion) {
                $idTipoEstimacion = DB::table('tipo_estimaciones')->min('id_tipo_e');
            }

            if (!$idTipoEstimacion) {
                throw new \RuntimeException('No existe un tipo de estimación configurado.');
            }

            $ahora = now();
            $insertadosArboles = 0;
            $insertadosTrozas = 0;

            if (in_array($alcance, ['1', '3'], true)) {
                $arbolesPendientes = DB::table('arboles as a')
                    ->where('a.id_parcela', $idParcela)
                    ->whereNotExists(function ($q) {
                        $q->select(DB::raw(1))
                            ->from('estimaciones1 as e1')
                            ->whereColumn('e1.id_arbol', 'a.id_arbol');
                    })
                    ->pluck('a.id_arbol');

                if ($arbolesPendientes->isNotEmpty()) {
                    $rows = $arbolesPendientes->map(function ($idArbol) use ($idTipoEstimacion, $ahora) {
                        return [
                            'id_tipo_e' => (int) $idTipoEstimacion,
                            'id_formula' => null,
                            'calculo' => 0,
                            'area_basal' => 0,
                            'biomasa' => 0,
                            'carbono' => 0,
                            'id_arbol' => (int) $idArbol,
                            'created_at' => $ahora,
                            'updated_at' => $ahora,
                        ];
                    })->all();

                    DB::table('estimaciones1')->insert($rows);
                    $insertadosArboles = count($rows);
                }
            }

            if (in_array($alcance, ['2', '3'], true)) {
                if (!$idFormulaTroza || !in_array($idFormulaTroza, [1, 2, 3, 4], true)) {
                    throw new \InvalidArgumentException('Fórmula de troza inválida para el alcance seleccionado.');
                }

                $trozasPendientes = DB::table('trozas as t')
                    ->where('t.id_parcela', $idParcela)
                    ->whereNotExists(function ($q) {
                        $q->select(DB::raw(1))
                            ->from('estimaciones as e')
                            ->whereColumn('e.id_troza', 't.id_troza');
                    })
                    ->pluck('t.id_troza');

                if ($trozasPendientes->isNotEmpty()) {
                    $rows = $trozasPendientes->map(function ($idTroza) use ($idTipoEstimacion, $idFormulaTroza, $ahora) {
                        return [
                            'id_tipo_e' => (int) $idTipoEstimacion,
                            'id_formula' => (int) $idFormulaTroza,
                            'calculo' => 0,
                            'biomasa' => 0,
                            'carbono' => 0,
                            'id_troza' => (int) $idTroza,
                            'created_at' => $ahora,
                            'updated_at' => $ahora,
                        ];
                    })->all();

                    DB::table('estimaciones')->insert($rows);
                    $insertadosTrozas = count($rows);
                }
            }

            return [
                'arboles' => $insertadosArboles,
                'trozas' => $insertadosTrozas,
            ];
        });
    }

    private function procesarPasoParcela(BotSesion $sesion, string $mensajeCrudo, $parcelasIds)
    {
        $nomParcela = trim($mensajeCrudo);
        $busquedaNormalizada = $this->normalizarTextoBusqueda($nomParcela);

        // 1. Verificamos solo dentro de las parcelas asignadas y hacemos una búsqueda tolerante.
        $parcela = DB::table('parcelas')
            ->whereIn('id_parcela', $parcelasIds)
            ->select('id_parcela', 'nom_parcela')
            ->get()
            ->first(function ($row) use ($busquedaNormalizada) {
                $nombreNormalizado = $this->normalizarTextoBusqueda((string) $row->nom_parcela);

                if ($busquedaNormalizada === '') {
                    return false;
                }

                return $nombreNormalizado === $busquedaNormalizada
                    || str_contains($nombreNormalizado, $busquedaNormalizada)
                    || str_contains($busquedaNormalizada, $nombreNormalizado);
            });

        if (!$parcela) {
            return response()->json([
                'ok' => false,
                'estado' => $sesion->estado,
                'mensaje' => "⚠️ No encontré una parcela llamada '*{$nomParcela}*' en tus asignaciones.\n\nPor favor, escribe el nombre correcto o envía 'cancelar' para salir.",
            ], 200);
        }

        // 2. Guardamos el dato en el payload temporal
        $payload = $sesion->payload ?? [];
        $payload['id_parcela'] = $parcela->id_parcela;
        $payload['nom_parcela'] = $parcela->nom_parcela;

        // 3. Avanzamos la sesión al siguiente estado
        $sesion->update([
            'estado' => 'ESPERANDO_TIPO',
            'payload' => $payload,
        ]);

        // 4. Preguntamos lo que sigue
        return response()->json([
            'ok' => true,
            'estado' => 'ESPERANDO_TIPO',
            'mensaje' => "✅ Parcela *{$parcela->nom_parcela}* seleccionada.\n\n¿Qué deseas registrar?\nEscribe *'Arbol'* o *'Troza'*.",
        ], 200);
    }

    private function normalizarTextoBusqueda(string $texto): string
    {
        $limpio = Str::of($texto)
            ->trim(" \t\n\r\0\x0B\"'`*")
            ->lower()
            ->ascii()
            ->replaceMatches('/[^a-z0-9]+/u', ' ')
            ->squish()
            ->toString();

        return $limpio;
    }

    private function limpiarSesionesExcelExpiradas(): void
    {
        $limite = now()->subHours(24);

        BotSesion::whereIn('estado', [self::ESPERANDO_PARCELA_EXCEL, self::ESPERANDO_ARCHIVO_EXCEL])
            ->where('updated_at', '<', $limite)
            ->delete();
    }

    private function normalizarTelefono(string $telefono): string
    {
        $raw = trim($telefono);
        $digits = preg_replace('/\D+/', '', $raw);

        if ($digits === '') {
            return $raw;
        }

        if (str_starts_with($digits, '521')) {
            return '52' . substr($digits, 3);
        }

        return $digits;
    }

    private function obtenerEspeciesDisponiblesTexto(int $max = 20): string
    {
        $especies = DB::table('especies')
            ->orderBy('nom_comun')
            ->pluck('nom_comun')
            ->filter(fn ($nombre) => is_string($nombre) && trim($nombre) !== '')
            ->values();

        if ($especies->isEmpty()) {
            return 'No hay especies registradas.';
        }

        $total = $especies->count();
        $muestra = $especies->take($max);
        $texto = '• ' . $muestra->implode("\n• ");

        if ($total > $max) {
            $texto .= "\n• ... y " . ($total - $max) . ' más';
        }

        return $texto;
    }

    private function procesarPasoTipo(BotSesion $sesion, string $mensajeCrudo)
    {
        $tipoLimpio = mb_strtolower(trim($mensajeCrudo));

        if (str_contains($tipoLimpio, 'arbol') || str_contains($tipoLimpio, 'árbol')) {
            $tipo = 'arboles';
        } elseif (str_contains($tipoLimpio, 'troza')) {
            $tipo = 'trozas';
        } else {
            return response()->json([
                'ok' => false,
                'estado' => $sesion->estado,
                'mensaje' => "⚠️ No entendí. Por favor, escribe *'Arbol'* o *'Troza'*.",
            ], 200);
        }

        $payload = $sesion->payload ?? [];
        $payload['tipo'] = $tipo;

        $sesion->update([
            'estado' => 'ESPERANDO_ESPECIE',
            'payload' => $payload,
        ]);

        $especiesTexto = $this->obtenerEspeciesDisponiblesTexto();

        return response()->json([
            'ok' => true,
            'estado' => 'ESPERANDO_ESPECIE',
            'mensaje' => "✅ Modo *{$tipo}* activado.\n\n¿De qué especie se trata?\n_(Escribe el nombre común, ej: Pino lacio)_\n\n📚 *Especies registradas:*\n{$especiesTexto}",
        ], 200);
    }

    private function procesarPasoEspecie(BotSesion $sesion, string $mensajeCrudo)
    {
        $texto = trim($mensajeCrudo);
        [$idEspecie, $nombreComun, $error] = $this->resolveEspecieForRow(null, $texto);

        if ($error) {
            $especiesTexto = $this->obtenerEspeciesDisponiblesTexto();

            return response()->json([
                'ok' => false,
                'estado' => $sesion->estado,
                'mensaje' => "⚠️ {$error}\n\nPor favor escribe el nombre de la especie nuevamente.\n\n📚 *Especies registradas:*\n{$especiesTexto}",
            ], 200);
        }

        $payload = $sesion->payload ?? [];
        $payload['id_especie'] = $idEspecie;
        $payload['nom_especie'] = $nombreComun;

        $tipo = $payload['tipo'] ?? null;
        if ($tipo === 'arboles') {
            $sesion->update([
                'estado' => 'ESPERANDO_MEDIDAS_ARBOL',
                'payload' => $payload,
            ]);

            return response()->json([
                'ok' => true,
                'estado' => 'ESPERANDO_MEDIDAS_ARBOL',
                'mensaje' => "✅ Especie *{$nombreComun}* seleccionada.\n\nAhora envía estas medidas en metros, separadas por coma:\n• *DAP*: diámetro a la altura del pecho (medido a 1.30 m del suelo).\n• *Altura Total*: altura completa del árbol desde la base hasta la punta.\n\n_Ejemplo: 0.35, 18.5_",
            ], 200);
        }

        if ($tipo === 'trozas') {
            $sesion->update([
                'estado' => 'ESPERANDO_MEDIDAS_TROZA',
                'payload' => $payload,
            ]);

            return response()->json([
                'ok' => true,
                'estado' => 'ESPERANDO_MEDIDAS_TROZA',
                'mensaje' => "✅ Especie *{$nombreComun}* seleccionada.\n\nAhora envía estas medidas, separadas por coma:\n• *Diámetro Superior*: diámetro del extremo delgado de la troza (m).\n• *Longitud*: largo total de la troza (m).\n• *Densidad*: densidad de la madera para el cálculo (usa el valor de tu inventario).\n• *Opcional* Diámetro Inferior: diámetro del extremo grueso (m).\n• *Opcional* Diámetro Medio: diámetro en la parte media (m).\n\n_Ejemplo mínimo: 0.45, 3.2, 0.65_\n_Ejemplo completo: 0.45, 3.2, 0.65, 0.52, 0.48_",
            ], 200);
        }

        $sesion->update([
            'estado' => 'ESPERANDO_TIPO',
            'payload' => $payload,
        ]);

        return response()->json([
            'ok' => false,
            'estado' => 'ESPERANDO_TIPO',
            'mensaje' => "⚠️ Para continuar necesito saber qué deseas registrar. Escribe *'Arbol'* o *'Troza'*.",
        ], 200);
    }

    private function procesarPasoMedidasArbol(BotSesion $sesion, string $mensajeCrudo)
    {
        $payload = $sesion->payload ?? [];
        $idParcela = $payload['id_parcela'] ?? null;
        $idEspecie = $payload['id_especie'] ?? null;

        if (!$idParcela || !$idEspecie) {
            $sesion->delete();
            return response()->json([
                'ok' => false,
                'estado' => 'ESPERANDO_PARCELA',
                'mensaje' => "⚠️ Perdí el contexto de la sesión. Por favor inicia de nuevo desde el menú.",
            ], 200);
        }

        $numeros = $this->extraerNumeros($mensajeCrudo);
        if (count($numeros) < 2) {
            return response()->json([
                'ok' => false,
                'estado' => $sesion->estado,
                'mensaje' => "⚠️ No pude leer los datos. Envía *DAP* y *Altura Total* separados por coma.\n_Ej: 0.35, 18.5_",
            ], 200);
        }

        $dap = (float) $numeros[0];
        $altura = (float) $numeros[1];
        if ($dap <= 0 || $altura <= 0) {
            return response()->json([
                'ok' => false,
                'estado' => $sesion->estado,
                'mensaje' => "⚠️ Los valores deben ser mayores a 0.\n_Ej: 0.35, 18.5_",
            ], 200);
        }

        $persona = $this->findPersonaByTelefono((string) $sesion->telefono);
        $rol = $persona?->rol?->nom_rol;
        if ($rol !== 'Tecnico') {
            $sesion->delete();
            return response()->json([
                'ok' => false,
                'estado' => 'FINALIZADO',
                'mensaje' => "🔐 *Verificación de acceso*\nRol detectado: *" . ($rol ?: 'Sin rol') . "*\n❌ Solo un Técnico puede registrar inventario usando el asistente.",
            ], 200);
        }

        $idArbol = (int) DB::table('arboles')->insertGetId([
            'id_especie' => (int) $idEspecie,
            'id_parcela' => (int) $idParcela,
            'altura_total' => $altura,
            'diametro_pecho' => $dap,
            'created_at' => now(),
            'updated_at' => now(),
        ]);

        $sesion->update([
            'estado' => 'ESPERANDO_ESPECIE',
            'payload' => $payload,
        ]);

        $nomParcela = $payload['nom_parcela'] ?? null;
        $nomEspecie = $payload['nom_especie'] ?? null;

        return response()->json([
            'ok' => true,
            'estado' => 'ESPERANDO_ESPECIE',
            'mensaje' => "✅ Registro guardado correctamente.\n\n📍 Parcela: *" . ($nomParcela ?: (string) $idParcela) . "*\n🌲 Especie: *" . ($nomEspecie ?: (string) $idEspecie) . "*\nDAP: *{$dap}*\nAltura Total: *{$altura}*\n\nID Árbol: *{$idArbol}*\n\n👉 Puedes registrar otro. Escribe la *especie* del siguiente registro o envía *cancelar* para finalizar.",
        ], 200);
    }

    private function procesarPasoMedidasTroza(BotSesion $sesion, string $mensajeCrudo)
    {
        $payload = $sesion->payload ?? [];
        $idParcela = $payload['id_parcela'] ?? null;
        $idEspecie = $payload['id_especie'] ?? null;

        if (!$idParcela || !$idEspecie) {
            $sesion->delete();
            return response()->json([
                'ok' => false,
                'estado' => 'ESPERANDO_PARCELA',
                'mensaje' => "⚠️ Perdí el contexto de la sesión. Por favor inicia de nuevo desde el menú.",
            ], 200);
        }

        $numeros = $this->extraerNumeros($mensajeCrudo);
        if (count($numeros) < 3) {
            return response()->json([
                'ok' => false,
                'estado' => $sesion->estado,
                'mensaje' => "⚠️ Envía *Diámetro Superior*, *Longitud* y *Densidad* separados por coma.\n_Ej: 0.45, 3.2, 0.65_",
            ], 200);
        }

        $diametro = (float) $numeros[0];
        $longitud = (float) $numeros[1];
        $densidad = (float) $numeros[2];
        $diametroOtroExtremo = isset($numeros[3]) ? (float) $numeros[3] : null;
        $diametroMedio = isset($numeros[4]) ? (float) $numeros[4] : null;

        if ($diametro <= 0 || $longitud <= 0 || $densidad <= 0) {
            return response()->json([
                'ok' => false,
                'estado' => $sesion->estado,
                'mensaje' => "⚠️ Los valores deben ser mayores a 0.\n_Ej: 0.45, 3.2, 0.65_",
            ], 200);
        }

        $persona = $this->findPersonaByTelefono((string) $sesion->telefono);
        $rol = $persona?->rol?->nom_rol;
        if ($rol !== 'Tecnico') {
            $sesion->delete();
            return response()->json([
                'ok' => false,
                'estado' => 'FINALIZADO',
                'mensaje' => "🔐 *Verificación de acceso*\nRol detectado: *" . ($rol ?: 'Sin rol') . "*\n❌ Solo un Técnico puede registrar inventario usando el asistente.",
            ], 200);
        }

        $idTroza = (int) DB::table('trozas')->insertGetId([
            'id_especie' => (int) $idEspecie,
            'id_parcela' => (int) $idParcela,
            'diametro' => $diametro,
            'longitud' => $longitud,
            'densidad' => $densidad,
            'diametro_otro_extremo' => $diametroOtroExtremo,
            'diametro_medio' => $diametroMedio,
            'created_at' => now(),
            'updated_at' => now(),
        ]);

        $sesion->update([
            'estado' => 'ESPERANDO_ESPECIE',
            'payload' => $payload,
        ]);

        $nomParcela = $payload['nom_parcela'] ?? null;
        $nomEspecie = $payload['nom_especie'] ?? null;

        return response()->json([
            'ok' => true,
            'estado' => 'ESPERANDO_ESPECIE',
            'mensaje' => "✅ Registro guardado correctamente.\n\n📍 Parcela: *" . ($nomParcela ?: (string) $idParcela) . "*\n🌲 Especie: *" . ($nomEspecie ?: (string) $idEspecie) . "*\nDiámetro: *{$diametro}*\nLongitud: *{$longitud}*\nDensidad: *{$densidad}*\n\nID Troza: *{$idTroza}*\n\n👉 Puedes registrar otra. Escribe la *especie* del siguiente registro o envía *cancelar* para finalizar.",
        ], 200);
    }

    /**
     * Extrae números del mensaje (acepta decimales con . o ,).
     * @return float[]
     */
    private function extraerNumeros(string $texto): array
    {
        preg_match_all('/-?\d+(?:[\.,]\d+)?/', $texto, $matches);

        return array_values(array_map(
            fn ($n) => (float) str_replace(',', '.', $n),
            $matches[0] ?? []
        ));
    }

    public function registroMasivo(Request $request)
    {
        $data = $request->validate([
            'telefono' => ['required', 'string', 'max:30'],
            // Se usa para que n8n pueda mandar el nombre del botón.
            'nom_parcela' => ['required', 'string', 'max:255'],

            // Ambos son opcionales, pero al menos uno debe venir con datos.
            'trozas' => ['nullable', 'array'],
            'trozas.*.longitud' => ['required_with:trozas', 'numeric', 'min:0.00001'],
            'trozas.*.diametro' => ['required_with:trozas', 'numeric', 'min:0.00001'],
            'trozas.*.diametro_otro_extremo' => ['nullable', 'numeric', 'min:0.00001'],
            'trozas.*.diametro_medio' => ['nullable', 'numeric', 'min:0.00001'],
            // densidad es obligatoria a nivel DB (trozas.densidad). Si no se envía, se reporta error por fila.
            'trozas.*.densidad' => ['nullable', 'numeric', 'min:0.00001'],
            // Puedes enviar id_especie o especie_texto.
            'trozas.*.id_especie' => ['nullable', 'integer', 'exists:especies,id_especie'],
            'trozas.*.especie_texto' => ['nullable', 'string', 'max:255'],

            'arboles' => ['nullable', 'array'],
            'arboles.*.altura_total' => ['required_with:arboles', 'numeric', 'min:0.00001'],
            'arboles.*.diametro_pecho' => ['required_with:arboles', 'numeric', 'min:0.00001'],
            'arboles.*.id_especie' => ['nullable', 'integer', 'exists:especies,id_especie'],
            'arboles.*.especie_texto' => ['nullable', 'string', 'max:255'],
        ]);

        $trozas = collect($data['trozas'] ?? []);
        $arboles = collect($data['arboles'] ?? []);

        if ($trozas->isEmpty() && $arboles->isEmpty()) {
            return response()->json([
                'error' => 'Debes enviar al menos una troza o un árbol.',
            ], 422);
        }

        $persona = $this->findPersonaByTelefono($data['telefono']);
        if (!$persona) {
            return response()->json(['error' => 'Usuario no encontrado'], 404);
        }

        $rol = $persona->rol?->nom_rol;
        if ($rol !== 'Tecnico') {
            return response()->json([
                'error' => 'Solo un Técnico puede registrar inventario por este endpoint.',
                'rol' => $rol,
            ], 403);
        }

        $tecnico = $persona->tecnico;
        if (!$tecnico) {
            return response()->json(['error' => 'Perfil de técnico no configurado'], 409);
        }

        $nomParcela = trim($data['nom_parcela']);
        if ($nomParcela === '') {
            return response()->json(['error' => 'nom_parcela es requerido'], 422);
        }

        // Validación segura: la parcela debe existir y estar asignada al técnico.
        $parcelasAsignadasIds = DB::table('asigna_parcelas')
            ->where('id_tecnico', $tecnico->id_tecnico)
            ->pluck('id_parcela');

        if ($parcelasAsignadasIds->isEmpty()) {
            return response()->json(['error' => 'No tienes parcelas asignadas actualmente.'], 403);
        }

        $parcelasMatch = DB::table('parcelas')
            ->whereIn('id_parcela', $parcelasAsignadasIds)
            ->where('nom_parcela', $nomParcela)
            ->select('id_parcela', 'nom_parcela')
            ->get();

        if ($parcelasMatch->isEmpty()) {
            return response()->json([
                'error' => 'No se encontró la parcela o no tienes acceso a ella.',
                'nom_parcela' => $nomParcela,
            ], 404);
        }

        if ($parcelasMatch->count() > 1) {
            return response()->json([
                'error' => 'Existe más de una parcela con ese nombre en tus asignaciones. Usa un nombre único o ajusta el flujo para enviar id_parcela.',
                'nom_parcela' => $nomParcela,
                'opciones' => $parcelasMatch,
            ], 409);
        }

        $idParcela = (int) $parcelasMatch->first()->id_parcela;

        $receiptTrozas = [];
        $receiptArboles = [];

        foreach ($trozas->values() as $idx => $row) {
            try {
                [$idEspecie, $especieNombre, $especieError] = $this->resolveEspecieForRow($row['id_especie'] ?? null, $row['especie_texto'] ?? null);
                if ($especieError) {
                    $receiptTrozas[] = [
                        'index' => $idx,
                        'ok' => false,
                        'error' => "Fila {$idx}: {$especieError}",
                        'especie_texto' => $row['especie_texto'] ?? null,
                    ];
                    continue;
                }

                $densidad = $row['densidad'] ?? null;
                if ($densidad === null) {
                    $receiptTrozas[] = [
                        'index' => $idx,
                        'ok' => false,
                        'error' => "Fila {$idx}: densidad requerida para registrar trozas (la tabla especies no tiene densidad configurada)",
                        'especie' => $especieNombre,
                        'id_especie' => $idEspecie,
                    ];
                    continue;
                }

                $payload = [
                    'longitud' => $row['longitud'],
                    'diametro' => $row['diametro'],
                    'diametro_otro_extremo' => $row['diametro_otro_extremo'] ?? null,
                    'diametro_medio' => $row['diametro_medio'] ?? null,
                    'densidad' => $densidad,
                    'id_especie' => $idEspecie,
                    'id_parcela' => $idParcela,
                    'created_at' => now(),
                    'updated_at' => now(),
                ];

                $idTroza = (int) DB::table('trozas')->insertGetId($payload);

                $receiptTrozas[] = [
                    'index' => $idx,
                    'ok' => true,
                    'id_troza' => $idTroza,
                    'id_especie' => $idEspecie,
                    'especie' => $especieNombre,
                    'mensaje' => 'Troza guardada correctamente (sin estimaciones).',
                ];
            } catch (\Throwable $e) {
                $receiptTrozas[] = [
                    'index' => $idx,
                    'ok' => false,
                    'error' => "Fila {$idx}: error al guardar la troza",
                ];
            }
        }

        foreach ($arboles->values() as $idx => $row) {
            try {
                [$idEspecie, $especieNombre, $especieError] = $this->resolveEspecieForRow($row['id_especie'] ?? null, $row['especie_texto'] ?? null);
                if ($especieError) {
                    $receiptArboles[] = [
                        'index' => $idx,
                        'ok' => false,
                        'error' => "Fila {$idx}: {$especieError}",
                        'especie_texto' => $row['especie_texto'] ?? null,
                    ];
                    continue;
                }

                $payload = [
                    'id_especie' => $idEspecie,
                    'id_parcela' => $idParcela,
                    'altura_total' => $row['altura_total'],
                    'diametro_pecho' => $row['diametro_pecho'],
                    'created_at' => now(),
                    'updated_at' => now(),
                ];

                $idArbol = (int) DB::table('arboles')->insertGetId($payload);

                $receiptArboles[] = [
                    'index' => $idx,
                    'ok' => true,
                    'id_arbol' => $idArbol,
                    'id_especie' => $idEspecie,
                    'especie' => $especieNombre,
                    'mensaje' => 'Árbol guardado correctamente (sin estimaciones).',
                ];
            } catch (\Throwable $e) {
                $receiptArboles[] = [
                    'index' => $idx,
                    'ok' => false,
                    'error' => "Fila {$idx}: error al guardar el árbol",
                ];
            }
        }

        return response()->json([
            'ok' => true,
            'usuario' => trim(($persona->nom ?? '') . ' ' . ($persona->ap ?? '') . ' ' . ($persona->am ?? '')),
            'rol' => $rol,
            'parcela' => [
                'id_parcela' => $idParcela,
                'nom_parcela' => $nomParcela,
            ],
            'resumen' => [
                'trozas_recibidas' => $trozas->count(),
                'trozas_guardadas' => collect($receiptTrozas)->where('ok', true)->count(),
                'arboles_recibidos' => $arboles->count(),
                'arboles_guardados' => collect($receiptArboles)->where('ok', true)->count(),
                'estimaciones_creadas' => 0,
                'nota' => 'Este endpoint solo inserta inventario (no genera estimaciones automáticamente).',
            ],
            'trozas' => $receiptTrozas,
            'arboles' => $receiptArboles,
        ], 201);
    }

    /**
     * Resuelve especie por id_especie o por texto (LIKE) para flujos de WhatsApp.
     *
     * @return array{0: int|null, 1: string|null, 2: string|null} [$idEspecie, $nombreComun, $error]
     */
    private function resolveEspecieForRow(mixed $idEspecie, mixed $especieTexto): array
    {
        if (is_int($idEspecie) && $idEspecie > 0) {
            $row = DB::table('especies')->where('id_especie', $idEspecie)->select('id_especie', 'nom_comun')->first();
            if (!$row) {
                return [null, null, "Especie con id {$idEspecie} no existe."];
            }

            return [(int) $row->id_especie, (string) $row->nom_comun, null];
        }

        if (is_string($idEspecie) && ctype_digit(trim($idEspecie))) {
            $asInt = (int) trim($idEspecie);
            if ($asInt > 0) {
                return $this->resolveEspecieForRow($asInt, $especieTexto);
            }
        }

        $textoOriginal = is_string($especieTexto) ? trim($especieTexto) : '';
        $texto = trim($textoOriginal, " \t\n\r\0\x0B\"'`*");
        $textoNormalizado = $this->normalizarTextoBusqueda($texto);

        if ($textoNormalizado === '') {
            return [null, null, "Debes enviar id_especie o especie_texto."];
        }

        // Búsqueda tolerante: mayúsculas/minúsculas, acentos, comillas y separadores.
        $matches = DB::table('especies')
            ->select('id_especie', 'nom_comun', 'nom_cientifico')
            ->get()
            ->filter(function ($row) use ($textoNormalizado) {
                $nomComun = $this->normalizarTextoBusqueda((string) ($row->nom_comun ?? ''));
                $nomCientifico = $this->normalizarTextoBusqueda((string) ($row->nom_cientifico ?? ''));

                if ($nomComun === '' && $nomCientifico === '') {
                    return false;
                }

                return $nomComun === $textoNormalizado
                    || $nomCientifico === $textoNormalizado
                    || str_contains($nomComun, $textoNormalizado)
                    || str_contains($nomCientifico, $textoNormalizado)
                    || str_contains($textoNormalizado, $nomComun)
                    || str_contains($textoNormalizado, $nomCientifico);
            })
            ->values();

        if ($matches->isEmpty()) {
            return [null, null, "Especie '{$texto}' no reconocida."];
        }

        if ($matches->count() > 1) {
            $suggestions = $matches
                ->take(5)
                ->map(fn ($m) => (string) $m->nom_comun)
                ->values()
                ->all();

            return [null, null, "Especie '{$texto}' ambigua. Opciones: " . implode(', ', $suggestions)];
        }

        $m = $matches->first();
        return [(int) $m->id_especie, (string) $m->nom_comun, null];
    }

    public function descargarReporteParcelaPdf(Request $request, int $id_parcela)
    {
        $data = $request->validate([
            'telefono' => ['required', 'string', 'max:30'],
        ]);

        // Para n8n/WhatsApp, normalmente conviene devolver JSON con URL pública.
        // Puedes forzarlo con ?link=1 o con header Accept: application/json
        $returnLink = (bool) $request->boolean('link') || $request->wantsJson();

        $persona = $this->findPersonaByTelefono($data['telefono']);

        if (!$persona) {
            return response()->json(['error' => 'Usuario no encontrado'], 404);
        }

        $rol = $persona->rol?->nom_rol;
        if (!$rol) {
            return response()->json(['error' => 'Rol no asignado'], 409);
        }

        if ($rol === 'Productor') {
            $productor = $persona->productor;
            if (!$productor) {
                return response()->json(['error' => 'Perfil de productor no configurado'], 409);
            }

            $parcela = Parcela::where('id_parcela', $id_parcela)
                ->where('id_productor', $productor->id_productor)
                ->with([
                    'trozas.estimaciones.tipoEstimacion',
                    'trozas.especie',
                    'arboles.estimaciones.tipoEstimacion',
                    'arboles.especie',
                    'turnosCorta',
                    'productor.persona',
                ])
                ->firstOrFail();

            $logoBase64 = '';
            if (extension_loaded('gd')) {
                $logoPath = public_path('assets/images/SIGMAD.svg');
                if (file_exists($logoPath)) {
                    $logoBase64 = 'data:image/svg+xml;base64,' . base64_encode(file_get_contents($logoPath));
                }
            }

            $viewData = [
                'parcela' => $parcela,
                'logo' => $logoBase64,
                'fecha' => Carbon::now()->format('d/m/Y H:i'),
                'productor' => $productor,
            ];

            $pdf = Pdf::loadView('P.pdf.parcela-v2', $viewData)
                ->setPaper('letter', 'portrait');

            $fileName = 'Parcela_' . $parcela->nom_parcela . '_' . now()->format('Y-m-d') . '.pdf';

            if ($returnLink) {
                $safeName = Str::slug($parcela->nom_parcela) ?: 'parcela';
                $path = 'reportes/' . now()->format('Ymd') . '/' . $safeName . '_' . now()->format('His') . '_' . Str::random(10) . '.pdf';
                Storage::disk('public')->put($path, $pdf->output());

                return response()->json([
                    'ok' => true,
                    'tipo' => 'pdf',
                    'file_name' => $fileName,
                    'path' => $path,
                    'url' => asset('storage/' . $path),
                    'expires_suggestion' => 'Recomendación: borrar reportes antiguos (ej. >24h) con un cron.',
                ], 200);
            }

            return $pdf->stream($fileName);
        }

        if ($rol === 'Tecnico') {
            $tecnico = Tecnico::where('id_persona', $persona->id_persona)->first();
            if (!$tecnico) {
                return response()->json(['error' => 'Perfil de técnico no configurado'], 409);
            }

            $isAssigned = DB::table('asigna_parcelas')
                ->where('id_tecnico', $tecnico->id_tecnico)
                ->where('id_parcela', $id_parcela)
                ->exists();

            if (!$isAssigned) {
                return response()->json(['error' => 'No tienes acceso a esta parcela'], 403);
            }

            $parcela = Parcela::with([
                'productor.persona',
                'trozas.especie',
                'trozas.estimaciones.tipoEstimacion',
                'trozas.estimaciones.formula',
                'arboles.especie',
                'arboles.estimaciones1.tipoEstimacion',
                'arboles.estimaciones1.formula',
            ])->findOrFail($id_parcela);

            $totalVolumenTrozas = $parcela->trozas->flatMap->estimaciones->sum('calculo');
            $totalBiomasaTrozas = $parcela->trozas->flatMap->estimaciones->sum('biomasa');
            $totalCarbonoTrozas = $parcela->trozas->flatMap->estimaciones->sum('carbono');

            $totalVolumenArboles = $parcela->arboles->flatMap->estimaciones1->sum('calculo');
            $totalBiomasaArboles = $parcela->arboles->flatMap->estimaciones1->sum('biomasa');
            $totalCarbonoArboles = $parcela->arboles->flatMap->estimaciones1->sum('carbono');

            $totales = [
                'volumen' => $totalVolumenTrozas + $totalVolumenArboles,
                'biomasa' => $totalBiomasaTrozas + $totalBiomasaArboles,
                'carbono' => $totalCarbonoTrozas + $totalCarbonoArboles,
                'trozas' => $parcela->trozas->count(),
                'arboles' => $parcela->arboles->count(),
                'estimaciones' => $parcela->trozas->flatMap->estimaciones->count()
                    + $parcela->arboles->flatMap->estimaciones1->count(),
            ];

            $estadisticas = [
                'volumen_trozas' => $totalVolumenTrozas,
                'volumen_arboles' => $totalVolumenArboles,
                'biomasa_trozas' => $totalBiomasaTrozas,
                'biomasa_arboles' => $totalBiomasaArboles,
                'carbono_trozas' => $totalCarbonoTrozas,
                'carbono_arboles' => $totalCarbonoArboles,
                'altura_promedio' => $parcela->arboles->avg('altura_total') ?? 0,
                'dap_promedio' => $parcela->arboles->avg('diametro_pecho') ?? 0,
            ];

            $pdf = Pdf::loadView('pdf.parcela-tecnico', [
                'parcela' => $parcela,
                'totales' => $totales,
                'estadisticas' => $estadisticas,
                'tecnico' => $tecnico,
                'fecha_generacion' => now(),
            ])->setPaper('A4', 'portrait');

            $fileName = 'Reporte_Parcela_' . $parcela->nom_parcela . '_' . now()->format('Y-m-d') . '.pdf';

            if ($returnLink) {
                $safeName = Str::slug($parcela->nom_parcela) ?: 'parcela';
                $path = 'reportes/' . now()->format('Ymd') . '/' . $safeName . '_' . now()->format('His') . '_' . Str::random(10) . '.pdf';
                Storage::disk('public')->put($path, $pdf->output());

                return response()->json([
                    'ok' => true,
                    'tipo' => 'pdf',
                    'file_name' => $fileName,
                    'path' => $path,
                    'url' => asset('storage/' . $path),
                    'expires_suggestion' => 'Recomendación: borrar reportes antiguos (ej. >24h) con un cron.',
                ], 200);
            }

            return $pdf->stream($fileName);
        }

        return response()->json([
            'error' => 'Rol no soportado para este endpoint',
        ], 422);
    }

    public function descargarInformeBotPdf(Request $request)
    {
        $data = $request->validate([
            'telefono' => ['required', 'string', 'max:30'],
        ]);

        $returnLink = (bool) $request->boolean('link') || $request->wantsJson();

        $persona = $this->findPersonaByTelefono($data['telefono']);
        if (!$persona) {
            return response()->json(['error' => 'Usuario no encontrado'], 404);
        }

        $rol = $persona->rol?->nom_rol;
        if (!$rol) {
            return response()->json(['error' => 'Rol no asignado'], 409);
        }

        [$rol, $parcelasIds] = $this->resolveParcelasIdsForPersona($persona);

        $parcelas = $parcelasIds->isEmpty()
            ? collect()
            : DB::table('parcelas')
                ->whereIn('id_parcela', $parcelasIds)
                ->select('id_parcela', 'nom_parcela', 'ubicacion')
                ->orderBy('nom_parcela')
                ->get();

        $especies = DB::table('especies')
            ->select('nom_comun', 'nom_cientifico')
            ->orderBy('nom_comun')
            ->get();

        $pdf = Pdf::loadView('pdf.bot-informe', [
            'persona' => $persona,
            'rol' => $rol,
            'parcelas' => $parcelas,
            'especies' => $especies,
            'fecha' => Carbon::now()->format('d/m/Y H:i'),
        ])->setPaper('letter', 'portrait');

        $fileName = 'Informe_SIGMAD_' . now()->format('Y-m-d_His') . '.pdf';

        if ($returnLink) {
            $path = 'reportes/' . now()->format('Ymd') . '/informe_' . now()->format('His') . '_' . Str::random(10) . '.pdf';
            Storage::disk('public')->put($path, $pdf->output());

            return response()->json([
                'ok' => true,
                'tipo' => 'pdf',
                'file_name' => $fileName,
                'path' => $path,
                'url' => asset('storage/' . $path),
                'expires_suggestion' => 'Recomendacion: borrar reportes antiguos (ej. >24h) con un cron.',
            ], 200);
        }

        return $pdf->stream($fileName);
    }

    public function descargarImpactoAmbientalPdf(Request $request)
    {
        $data = $request->validate([
            'telefono' => ['required', 'string', 'max:30'],
            'id_parcela' => ['nullable'],
        ]);

        $returnLink = (bool) $request->boolean('link') || $request->wantsJson();

        $telefono = $this->normalizarTelefono($data['telefono']);
        $persona = $this->findPersonaByTelefono($telefono);

        if (!$persona) {
            return response()->json(['error' => 'Usuario no encontrado'], 404);
        }

        [$rol, $parcelasIds] = $this->resolveParcelasIdsForPersona($persona);

        if ($rol === null) {
            return response()->json([
                'usuario' => trim(($persona->nom ?? '') . ' ' . ($persona->ap ?? '') . ' ' . ($persona->am ?? '')),
                'rol' => null,
                'mensaje' => 'Tu cuenta no tiene rol o perfil válido.',
            ], 409);
        }

        [$idParcela, $selectorError] = $this->parseParcelaSelector($data['id_parcela'] ?? null);
        if ($selectorError) {
            return response()->json(['error' => $selectorError], 422);
        }

        if ($idParcela !== null && !$parcelasIds->contains($idParcela)) {
            return response()->json(['error' => 'No tienes acceso a esa parcela'], 403);
        }

        $impacto = $this->construirImpactoAmbiental($persona, $rol, $parcelasIds, $idParcela);

        $viewData = [
            'impacto' => $impacto,
            'persona' => $persona,
            'fecha' => Carbon::now()->format('d/m/Y H:i'),
        ];

        $pdf = Pdf::loadView('pdf.impacto', $viewData)->setPaper('A4', 'portrait');

        $fileName = 'Impacto_Ambiental_' . ($idParcela ? 'parcela_' . $idParcela . '_' : '') . now()->format('Y-m-d_His') . '.pdf';

        if ($returnLink) {
            $safeName = $idParcela ? ('impacto_parcela_' . $idParcela) : 'impacto_general';
            $path = 'reportes/' . now()->format('Ymd') . '/' . $safeName . '_' . now()->format('His') . '_' . Str::random(8) . '.pdf';
            Storage::disk('public')->put($path, $pdf->output());

            return response()->json([
                'ok' => true,
                'tipo' => 'pdf',
                'file_name' => $fileName,
                'path' => $path,
                'url' => asset('storage/' . $path),
                'expires_suggestion' => 'Recomendación: borrar reportes antiguos (ej. >24h) con un cron.',
            ], 200);
        }

        return $pdf->stream($fileName);
    }

    private function findPersonaByTelefono(string $telefono): ?Persona
    {
        $raw = trim($telefono);
        $digits = preg_replace('/\D+/', '', $raw);

        $query = Persona::query()->with(['rol', 'productor', 'tecnico']);

        $query->where('telefono', $raw);

        if ($digits !== '' && $digits !== $raw) {
            $query->orWhere('telefono', $digits);
        }

        return $query->first();
    }

    /**
     * Normaliza el selector de parcela para soportar flujos de chat.
     * - null/""/0 => todas las parcelas (return null)
     * - "todas"/"toda" => todas (return null)
     * - "123" o 123 => 123
     *
     * @return array{0: int|null, 1: string|null} [$idParcela, $error]
     */
    private function parseParcelaSelector(mixed $value): array
    {
        if ($value === null) {
            return [null, null];
        }

        if (is_int($value)) {
            return $value > 0 ? [$value, null] : [null, null];
        }

        if (is_string($value)) {
            $trimmed = trim($value);
            if ($trimmed === '' || $trimmed === '0') {
                return [null, null];
            }

            $lower = mb_strtolower($trimmed);
            if (in_array($lower, ['todas', 'toda', 'todo', 'todas las parcelas', 'toda la parcela'], true)) {
                return [null, null];
            }

            if (ctype_digit($trimmed)) {
                $id = (int) $trimmed;
                return $id > 0 ? [$id, null] : [null, null];
            }
        }

        return [null, 'El campo id_parcela debe ser un número o "todas".'];
    }

    /**
     * @return array{0: string|null, 1: \Illuminate\Support\Collection} [$rol, $parcelasIds]
     */
    private function resolveParcelasIdsForPersona(Persona $persona): array
    {
        $rol = $persona->rol?->nom_rol;

        if (!$rol) {
            return [null, collect()];
        }

        if ($rol === 'Productor') {
            $productor = $persona->productor;
            if (!$productor) {
                return [$rol, collect()];
            }

            $parcelasIds = DB::table('parcelas')
                ->where('id_productor', $productor->id_productor)
                ->pluck('id_parcela');

            return [$rol, $parcelasIds];
        }

        if ($rol === 'Tecnico') {
            $tecnico = $persona->tecnico;
            if (!$tecnico) {
                return [$rol, collect()];
            }

            $parcelasIds = DB::table('asigna_parcelas')
                ->where('id_tecnico', $tecnico->id_tecnico)
                ->pluck('id_parcela');

            return [$rol, $parcelasIds];
        }

        // Para otros roles, por ahora no devolvemos parcelas.
        return [$rol, collect()];
    }
}
