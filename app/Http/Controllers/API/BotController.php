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
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Str;

class BotController extends Controller
{
    public function asistenteGuiado(Request $request)
    {
        $data = $request->validate([
            'telefono' => ['required', 'string', 'max:30'],
            'mensaje' => ['required', 'string'],
        ]);

        $telefono = $data['telefono'];
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

        // 3. LA MÁQUINA DE ESTADOS (El flujo conversacional)
        if (!$sesion) {
            // Aceptamos la palabra 'iniciar' (por si acaso) o el ID exacto del botón que viene de n8n
            if ($mensajeLimpio === 'iniciar' || $mensajeLimpio === 'menu_ingreso_guiado') {
                return $this->iniciarAsistente($telefono, $parcelasIds);
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

            default:
                $sesion->delete();
                return response()->json(['error' => 'Sesión corrupta. Por favor, inicia de nuevo.'], 500);
        }
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
                'mensaje' => 'Este número no está registrado en el sistema WoodWise.',
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
            'mensaje' => "🤖 *Asistente de Captura Iniciado*\n\nHola. Te guiaré paso a paso para registrar tus datos.\n_Escribe 'cancelar' en cualquier momento para salir._\n\n📍 *Tus Parcelas:*\n{$parcelasTexto}\n\n👉 *Escribe el nombre de la parcela en la que estás trabajando:*",
        ], 200);
    }

    private function procesarPasoParcela(BotSesion $sesion, string $mensajeCrudo, $parcelasIds)
    {
        $nomParcela = trim($mensajeCrudo);

        // 1. Verificamos si la parcela existe y si el usuario tiene acceso
        $parcela = DB::table('parcelas')
            ->whereIn('id_parcela', $parcelasIds)
            ->where('nom_parcela', 'like', '%' . $nomParcela . '%')
            ->first();

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

    private function procesarPasoTipo(BotSesion $sesion, string $mensajeCrudo)
    {
        $tipoLimpio = mb_strtolower(trim($mensajeCrudo));

        // 1. Validamos la entrada
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

        // 2. Guardamos en el payload
        $payload = $sesion->payload ?? [];
        $payload['tipo'] = $tipo;

        // 3. Avanzamos al siguiente estado
        $sesion->update([
            'estado' => 'ESPERANDO_ESPECIE',
            'payload' => $payload,
        ]);

        return response()->json([
            'ok' => true,
            'estado' => 'ESPERANDO_ESPECIE',
            'mensaje' => "✅ Modo *{$tipo}* activado.\n\n¿De qué especie se trata?\n_(Escribe el nombre, ej: Pino lacio)_",
        ], 200);
    }

    private function procesarPasoEspecie(BotSesion $sesion, string $mensajeCrudo)
    {
        $texto = trim($mensajeCrudo);
        [$idEspecie, $nombreComun, $error] = $this->resolveEspecieForRow(null, $texto);

        if ($error) {
            return response()->json([
                'ok' => false,
                'estado' => $sesion->estado,
                'mensaje' => "⚠️ {$error}\n\nPor favor escribe el nombre de la especie nuevamente.",
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
                'mensaje' => "✅ Especie *{$nombreComun}* seleccionada.\n\nEscribe el *DAP* y la *Altura Total* (en metros), separados por coma.\n_Ej: 0.35, 18.5_",
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
                'mensaje' => "✅ Especie *{$nombreComun}* seleccionada.\n\nEscribe *Diámetro Superior*, *Longitud* y *Densidad* (en metros), separados por coma.\n_Opcional: Diámetro Inferior, Diámetro Medio_\n_Ej: 0.45, 3.2, 0.65_",
            ], 200);
        }

        // Fallback: si por alguna razón no existe tipo en payload
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
                'mensaje' => 'Solo un Técnico puede registrar inventario usando el asistente.',
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

        $sesion->delete();

        $nomParcela = $payload['nom_parcela'] ?? null;
        $nomEspecie = $payload['nom_especie'] ?? null;

        return response()->json([
            'ok' => true,
            'estado' => 'FINALIZADO',
            'mensaje' => "✅ Registro guardado correctamente.\n\n📍 Parcela: *" . ($nomParcela ?: (string) $idParcela) . "*\n🌲 Especie: *" . ($nomEspecie ?: (string) $idEspecie) . "*\nDAP: *{$dap}*\nAltura Total: *{$altura}*\n\nID Árbol: *{$idArbol}*",
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
                'mensaje' => 'Solo un Técnico puede registrar inventario usando el asistente.',
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

        $sesion->delete();

        $nomParcela = $payload['nom_parcela'] ?? null;
        $nomEspecie = $payload['nom_especie'] ?? null;

        return response()->json([
            'ok' => true,
            'estado' => 'FINALIZADO',
            'mensaje' => "✅ Registro guardado correctamente.\n\n📍 Parcela: *" . ($nomParcela ?: (string) $idParcela) . "*\n🌲 Especie: *" . ($nomEspecie ?: (string) $idEspecie) . "*\nDiámetro: *{$diametro}*\nLongitud: *{$longitud}*\nDensidad: *{$densidad}*\n\nID Troza: *{$idTroza}*",
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

        $texto = is_string($especieTexto) ? trim($especieTexto) : '';
        if ($texto === '') {
            return [null, null, "Debes enviar id_especie o especie_texto."];
        }

        // 1) Intento de match exacto (si el collation es case-insensitive, esto ya ayuda mucho)
        $exact = DB::table('especies')
            ->where('nom_comun', $texto)
            ->orWhere('nom_cientifico', $texto)
            ->select('id_especie', 'nom_comun')
            ->first();

        if ($exact) {
            return [(int) $exact->id_especie, (string) $exact->nom_comun, null];
        }

        // 2) LIKE (búsqueda “inteligente”)
        $matches = DB::table('especies')
            ->where('nom_comun', 'like', '%' . $texto . '%')
            ->orWhere('nom_cientifico', 'like', '%' . $texto . '%')
            ->select('id_especie', 'nom_comun', 'nom_cientifico')
            ->limit(10)
            ->get();

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
                $logoPath = public_path('img/woodwise.png');
                if (file_exists($logoPath)) {
                    $logoBase64 = 'data:image/png;base64,' . base64_encode(file_get_contents($logoPath));
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
