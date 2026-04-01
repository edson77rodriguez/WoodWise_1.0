<?php

namespace App\Http\Controllers\API;

use App\Http\Controllers\Controller;
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
