<?php

namespace App\Http\Controllers;

use Illuminate\Support\Facades\Auth;
use App\Models\Arbol;
use App\Models\Parcela;
use App\Models\Troza;
use App\Models\Estimacion;
use App\Models\Estimacion1;
use App\Models\Turno_Corta;
use App\Models\Productor;
use App\Models\Especie;
use Illuminate\Http\Request;
use Barryvdh\DomPDF\Facade\Pdf;
use Carbon\Carbon;

class ProductorDashboardController extends Controller
{
    protected $productor;

    public function __construct()
    {
        $this->middleware('auth');
        $this->middleware(function ($request, $next) {
            if (Auth::user()->persona->rol->nom_rol !== 'Productor') {
                abort(403, 'Acceso exclusivo para Productores.');
            }
            $this->productor = Auth::user()->persona->productor;
            return $next($request);
        });
    }

    /**
     * Dashboard principal del productor
     */
    public function index()
    {
        $user = Auth::user();
        $productor = $user->persona->productor;

        if (!$productor) {
            return redirect()->route('welcome')->with('error', 'No tienes perfil de productor.');
        }

        // Parcelas con todas las relaciones necesarias
        $parcelas = Parcela::where('id_productor', $productor->id_productor)
            ->with([
                'trozas.estimaciones.tipoEstimacion',
                'arboles.estimaciones.tipoEstimacion',
                'turnosCorta',
                'tecnicos.persona'
            ])
            ->withCount(['trozas', 'arboles'])
            ->orderBy('created_at', 'desc')
            ->get();

        // Estadísticas generales
        $totalVolumenTrozas = 0;
        $totalVolumenArboles = 0;
        $totalBiomasa = 0;
        $totalCarbono = 0;

        foreach ($parcelas as $parcela) {
            foreach ($parcela->trozas as $troza) {
                $volumen = $troza->estimaciones->where('tipoEstimacion.desc_estimacion', 'Volumen')->first();
                if ($volumen) {
                    $totalVolumenTrozas += $volumen->calculo;
                }
            }
            foreach ($parcela->arboles as $arbol) {
                $volumen = $arbol->estimaciones->where('tipoEstimacion.desc_estimacion', 'Volumen')->first();
                $biomasa = $arbol->estimaciones->where('tipoEstimacion.desc_estimacion', 'Biomasa')->first();
                $carbono = $arbol->estimaciones->where('tipoEstimacion.desc_estimacion', 'Carbono')->first();
                
                if ($volumen) $totalVolumenArboles += $volumen->calculo;
                if ($biomasa) $totalBiomasa += $biomasa->calculo;
                if ($carbono) $totalCarbono += $carbono->calculo;
            }
        }

        // Datos para gráficas
        $chartData = [
            'parcelas' => $parcelas->pluck('nom_parcela')->toArray(),
            'extensiones' => $parcelas->pluck('extension')->toArray(),
            'trozasPorParcela' => $parcelas->pluck('trozas_count')->toArray(),
            'arbolesPorParcela' => $parcelas->pluck('arboles_count')->toArray(),
        ];

        // Volumen mensual (últimos 6 meses)
        $volumenMensual = $this->getVolumenMensual($productor->id_productor);

        // Especies más comunes
        $especiesData = $this->getEspeciesDistribucion($productor->id_productor);

        $stats = [
            'total_parcelas' => $parcelas->count(),
            'total_trozas' => $parcelas->sum('trozas_count'),
            'total_arboles' => $parcelas->sum('arboles_count'),
            'total_extension' => $parcelas->sum('extension'),
            'total_volumen' => $totalVolumenTrozas + $totalVolumenArboles,
            'total_biomasa' => $totalBiomasa,
            'total_carbono' => $totalCarbono,
        ];

        $especies = Especie::orderBy('nom_comun')->get();

        return view('P.index', compact(
            'user', 
            'productor', 
            'parcelas', 
            'stats', 
            'chartData', 
            'volumenMensual', 
            'especiesData',
            'especies'
        ));
    }

    /**
     * Crear nueva parcela (el productor es automáticamente el dueño)
     */
    public function parcelaStore(Request $request)
    {
        $validated = $request->validate([
            'nom_parcela' => 'required|string|max:255',
            'ubicacion' => 'required|string|max:255',
            'extension' => 'required|numeric|min:0.01|max:10000',
            'direccion' => 'nullable|string|max:255',
            'CP' => 'nullable|string|max:10',
        ]);

        $productor = Auth::user()->persona->productor;

        try {
            Parcela::create([
                'nom_parcela' => $validated['nom_parcela'],
                'ubicacion' => $validated['ubicacion'],
                'extension' => $validated['extension'],
                'direccion' => $validated['direccion'] ?? null,
                'CP' => $validated['CP'] ?? null,
                'id_productor' => $productor->id_productor,
            ]);

            return back()->with('success', 'Parcela registrada exitosamente.');
        } catch (\Exception $e) {
            return back()->with('error', 'Error al registrar parcela: ' . $e->getMessage());
        }
    }

    /**
     * Registrar turno de corta
     */
    public function turnoStore(Request $request)
    {
        $validated = $request->validate([
            'id_parcela' => 'required|exists:parcelas,id_parcela',
            'codigo_corta' => 'required|string|max:50',
            'fecha_corta' => 'required|date',
            'fecha_fin' => 'nullable|date|after_or_equal:fecha_corta',
        ]);

        // Verificar que la parcela pertenece al productor
        $productor = Auth::user()->persona->productor;
        $parcela = Parcela::where('id_parcela', $validated['id_parcela'])
            ->where('id_productor', $productor->id_productor)
            ->firstOrFail();

        try {
            Turno_Corta::create($validated);
            return back()->with('success', 'Turno de corta registrado exitosamente.');
        } catch (\Exception $e) {
            return back()->with('error', 'Error al registrar turno: ' . $e->getMessage());
        }
    }

    /**
     * Ver detalle de parcela
     */
    public function parcelaDetalle($id_parcela)
    {
        $productor = Auth::user()->persona->productor;
        
        $parcela = Parcela::where('id_parcela', $id_parcela)
            ->where('id_productor', $productor->id_productor)
            ->firstOrFail();
        
        // Obtener árboles con sus relaciones
        $arboles = Arbol::where('id_parcela', $id_parcela)
            ->with(['especie', 'estimaciones.tipoEstimacion'])
            ->get();
        
        // Obtener trozas con sus relaciones
        $trozas = Troza::where('id_parcela', $id_parcela)
            ->with(['especie', 'estimaciones.tipoEstimacion'])
            ->get();
        
        // Obtener turnos de corta
        $turnos = Turno_Corta::where('id_parcela', $id_parcela)
            ->orderBy('fecha_corta', 'desc')
            ->get();
        
        // Calcular estadísticas
        $totalVolumen = 0;
        $totalBiomasa = 0;
        $totalCarbono = 0;
        $especiesSet = [];
        
        // Calcular desde árboles
        foreach ($arboles as $arbol) {
            if ($arbol->especie) {
                $especiesSet[$arbol->especie->id_especie] = true;
            }
            foreach ($arbol->estimaciones as $est) {
                $tipo = $est->tipoEstimacion->desc_estimacion ?? '';
                if (stripos($tipo, 'volumen') !== false) {
                    $totalVolumen += $est->calculo ?? 0;
                } elseif (stripos($tipo, 'biomasa') !== false) {
                    $totalBiomasa += $est->calculo ?? 0;
                } elseif (stripos($tipo, 'carbono') !== false) {
                    $totalCarbono += $est->calculo ?? 0;
                }
            }
        }
        
        // Calcular desde trozas
        foreach ($trozas as $troza) {
            if ($troza->especie) {
                $especiesSet[$troza->especie->id_especie] = true;
            }
            foreach ($troza->estimaciones as $est) {
                $tipo = $est->tipoEstimacion->desc_estimacion ?? '';
                if (stripos($tipo, 'volumen') !== false) {
                    $totalVolumen += $est->calculo ?? 0;
                } elseif (stripos($tipo, 'biomasa') !== false) {
                    $totalBiomasa += $est->calculo ?? 0;
                } elseif (stripos($tipo, 'carbono') !== false) {
                    $totalCarbono += $est->calculo ?? 0;
                }
            }
        }
        
        $stats = [
            'arboles' => $arboles->count(),
            'trozas' => $trozas->count(),
            'volumen' => $totalVolumen,
            'biomasa' => $totalBiomasa,
            'carbono' => $totalCarbono,
            'especies' => count($especiesSet)
        ];

        return view('P.parcela-detalle', compact(
            'parcela', 
            'arboles',
            'trozas',
            'turnos',
            'stats'
        ));
    }

    /**
     * Exportar PDF de una parcela
     */
    public function exportParcelaPdf($id_parcela)
    {
        $productor = Auth::user()->persona->productor;
        
        $parcela = Parcela::where('id_parcela', $id_parcela)
            ->where('id_productor', $productor->id_productor)
            ->with([
                'trozas.estimaciones.tipoEstimacion',
                'trozas.especie',
                'arboles.estimaciones.tipoEstimacion',
                'arboles.especie',
                'turnosCorta',
                'productor.persona'
            ])
            ->firstOrFail();

        // Logo (solo si GD está disponible)
        $logoBase64 = '';
        if (extension_loaded('gd')) {
            $logoPath = public_path('assets/images/SIGMAD.svg');
            if (file_exists($logoPath)) {
                $logoBase64 = 'data:image/svg+xml;base64,' . base64_encode(file_get_contents($logoPath));
            }
        }

        $data = [
            'parcela' => $parcela,
            'logo' => $logoBase64,
            'fecha' => Carbon::now()->format('d/m/Y H:i'),
            'productor' => $productor,
        ];

        $pdf = Pdf::loadView('P.pdf.parcela-v2', $data)
            ->setPaper('letter', 'portrait');

        return $pdf->download('Parcela_' . $parcela->nom_parcela . '_' . now()->format('Y-m-d') . '.pdf');
    }

    /**
     * Exportar reporte general (todas las parcelas)
     */
    public function exportarGeneral()
    {
        $productor = Auth::user()->persona->productor;

        $parcelas = Parcela::where('id_productor', $productor->id_productor)
            ->with([
                'trozas.estimaciones.tipoEstimacion',
                'arboles.estimaciones.tipoEstimacion',
                'turnosCorta'
            ])
            ->withCount(['trozas', 'arboles'])
            ->get();

        if ($parcelas->isEmpty()) {
            return redirect()->back()->with('error', 'No hay datos para generar el reporte.');
        }

        // Calcular totales
        $totalVolumen = 0;
        $totalBiomasa = 0;
        $totalCarbono = 0;

        foreach ($parcelas as $parcela) {
            foreach ($parcela->trozas as $troza) {
                $vol = $troza->estimaciones->where('tipoEstimacion.desc_estimacion', 'Volumen')->first();
                if ($vol) $totalVolumen += $vol->calculo;
            }
            foreach ($parcela->arboles as $arbol) {
                $vol = $arbol->estimaciones->where('tipoEstimacion.desc_estimacion', 'Volumen')->first();
                $bio = $arbol->estimaciones->where('tipoEstimacion.desc_estimacion', 'Biomasa')->first();
                $car = $arbol->estimaciones->where('tipoEstimacion.desc_estimacion', 'Carbono')->first();
                if ($vol) $totalVolumen += $vol->calculo;
                if ($bio) $totalBiomasa += $bio->calculo;
                if ($car) $totalCarbono += $car->calculo;
            }
        }

        // Logo (solo si GD está disponible)
        $logoBase64 = '';
        if (extension_loaded('gd')) {
            $logoPath = public_path('assets/images/SIGMAD.svg');
            if (file_exists($logoPath)) {
                $logoBase64 = 'data:image/svg+xml;base64,' . base64_encode(file_get_contents($logoPath));
            }
        }

        $stats = [
            'total_parcelas' => $parcelas->count(),
            'total_trozas' => $parcelas->sum('trozas_count'),
            'total_arboles' => $parcelas->sum('arboles_count'),
            'total_extension' => $parcelas->sum('extension'),
            'total_volumen' => $totalVolumen,
            'total_biomasa' => $totalBiomasa,
            'total_carbono' => $totalCarbono,
        ];

        $data = [
            'parcelas' => $parcelas,
            'productor' => $productor,
            'stats' => $stats,
            'logo' => $logoBase64,
            'fecha' => Carbon::now()->format('d/m/Y H:i'),
        ];

        $pdf = Pdf::loadView('P.pdf.general-v2', $data)
            ->setPaper('letter', 'landscape');

        return $pdf->download('Reporte_General_' . now()->format('Y-m-d') . '.pdf');
    }

    /**
     * Obtener volumen mensual para gráficas
     */
    private function getVolumenMensual($productorId)
    {
        $meses = [];
        $volumenes = [];

        for ($i = 5; $i >= 0; $i--) {
            $fecha = Carbon::now()->subMonths($i);
            $meses[] = $fecha->translatedFormat('M Y');

            $volumen = Estimacion::whereHas('troza.parcela', function ($q) use ($productorId) {
                $q->where('id_productor', $productorId);
            })
            ->whereHas('tipoEstimacion', function ($q) {
                $q->where('desc_estimacion', 'Volumen');
            })
            ->whereYear('created_at', $fecha->year)
            ->whereMonth('created_at', $fecha->month)
            ->sum('calculo');

            $volumenes[] = round($volumen, 2);
        }

        return ['labels' => $meses, 'data' => $volumenes];
    }

    /**
     * Obtener distribución de especies
     */
    private function getEspeciesDistribucion($productorId)
    {
        $especies = \DB::table('arboles')
            ->join('parcelas', 'arboles.id_parcela', '=', 'parcelas.id_parcela')
            ->join('especies', 'arboles.id_especie', '=', 'especies.id_especie')
            ->where('parcelas.id_productor', $productorId)
            ->select('especies.nom_comun', \DB::raw('COUNT(*) as total'))
            ->groupBy('especies.nom_comun')
            ->orderByDesc('total')
            ->limit(5)
            ->get();

        return [
            'labels' => $especies->pluck('nom_comun')->toArray(),
            'data' => $especies->pluck('total')->toArray(),
        ];
    }
}