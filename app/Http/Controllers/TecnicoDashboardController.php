<?php

namespace App\Http\Controllers;

use App\Models\Parcela;
use App\Models\Troza;
use App\Models\Estimacion;
use App\Models\Arbol;
use App\Models\Estimacion1;
use App\Models\Productor;
use App\Models\Especie;
use App\Models\Formula;
use App\Models\Tipo_Estimacion;
use App\Models\TurnoCorta;
use App\Models\Asigna_Parcela;
use App\Models\Tecnico;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use PDF;

class TecnicoDashboardController extends Controller
{
    protected $tecnico;

    public function __construct()
    {
        $this->middleware('auth');
        $this->middleware(function ($request, $next) {
            $user = Auth::user();
            
            if (!$user || !$user->persona) {
                return redirect()->route('login');
            }

            // Verificar que el usuario es técnico
            if ($user->persona->rol->nom_rol !== 'Tecnico') {
                return response()->view('denegado', [], 403);
            }

            // Obtener el técnico asociado a la persona
            $this->tecnico = Tecnico::where('id_persona', $user->persona->id_persona)->first();

            if (!$this->tecnico) {
                return response()->view('denegado', ['message' => 'No tienes un perfil de técnico configurado'], 403);
            }

            return $next($request);
        });
    }

    public function index()
    {
        return $this->dashboard();
    }

    /**
     * Dashboard principal del técnico - ACTUALIZADO CON ÁRBOLES
     */
    public function dashboard()
    {
        // --- 1. IDs de Origen ---
        $parcelaIds = Asigna_Parcela::where('id_tecnico', $this->tecnico->id_tecnico)
                                    ->pluck('id_parcela');

        // --- 2. IDs Intermedios ---
        $trozaIds = Troza::whereIn('id_parcela', $parcelaIds)->pluck('id_troza');
        $arbolIds = Arbol::whereIn('id_parcela', $parcelaIds)->pluck('id_arbol');

        // --- 3. Cálculos TOTALES (para las "Stat Cards") ---
        $totalTrozas = $trozaIds->count();
        $totalArboles = $arbolIds->count();

        // Total Estimaciones
        $countEstimaciones = Estimacion::whereIn('id_troza', $trozaIds)->count();
        $countEstimaciones1 = Estimacion1::whereIn('id_arbol', $arbolIds)->count();
        $totalEstimaciones = $countEstimaciones + $countEstimaciones1;

        // Volumen Total Maderable
        $volumenEstimaciones = Estimacion::whereIn('id_troza', $trozaIds)->sum('calculo');
        $volumenEstimaciones1 = Estimacion1::whereIn('id_arbol', $arbolIds)->sum('calculo');
        $totalVolumenMaderable = $volumenEstimaciones + $volumenEstimaciones1;

        // Biomasa y Carbono totales
        $totalBiomasa = Estimacion::whereIn('id_troza', $trozaIds)->sum('biomasa')
                      + Estimacion1::whereIn('id_arbol', $arbolIds)->sum('biomasa');
        $totalCarbono = Estimacion::whereIn('id_troza', $trozaIds)->sum('carbono')
                      + Estimacion1::whereIn('id_arbol', $arbolIds)->sum('carbono');

        // --- 4. Consulta PAGINADA (para la tabla) ---
        $parcelas = Parcela::with(['productor.persona', 'trozas.especie', 'arboles.especie'])
            ->whereIn('id_parcela', $parcelaIds)
            ->withCount([
                'trozas',
                'arboles',
                'estimaciones',
                'estimaciones1'
            ])
            ->paginate(10);

        // --- 5. Optimización N+1 para SUMAS ANIDADAS ---
        $paginatedParcelaIds = $parcelas->pluck('id_parcela');

        $estimacionSums = Estimacion::join('trozas', 'estimaciones.id_troza', '=', 'trozas.id_troza')
            ->whereIn('trozas.id_parcela', $paginatedParcelaIds)
            ->groupBy('trozas.id_parcela')
            ->selectRaw('trozas.id_parcela, sum(estimaciones.calculo) as total_calculo')
            ->pluck('total_calculo', 'id_parcela');

        $estimacion1Sums = Estimacion1::join('arboles', 'estimaciones1.id_arbol', '=', 'arboles.id_arbol')
            ->whereIn('arboles.id_parcela', $paginatedParcelaIds)
            ->groupBy('arboles.id_parcela')
            ->selectRaw('arboles.id_parcela, sum(estimaciones1.calculo) as total_calculo')
            ->pluck('total_calculo', 'id_parcela');

        $parcelas->each(function ($parcela) use ($estimacionSums, $estimacion1Sums) {
            $parcela->estimaciones_sum_calculo = $estimacionSums->get($parcela->id_parcela, 0);
            $parcela->estimaciones1_sum_calculo = $estimacion1Sums->get($parcela->id_parcela, 0);
        });

        // --- 6. Enviar Datos a la Vista ---
        $data = [
            'parcelas' => $parcelas,
            'totalTrozas' => $totalTrozas,
            'totalArboles' => $totalArboles,
            'totalEstimaciones' => $totalEstimaciones,
            'totalVolumenMaderable' => $totalVolumenMaderable,
            'totalBiomasa' => $totalBiomasa,
            'totalCarbono' => $totalCarbono,
            'user' => Auth::user(),
            'tecnico' => $this->tecnico,
            'productores' => Productor::with('persona')->get(),
            'especies' => Especie::all(),
            'tiposEstimacion' => Tipo_Estimacion::all(),
            'formulas' => Formula::all(),
        ];

        return view('T.index', $data);
    }

    /**
     * VER DETALLE DE PARCELA
     */
    public function parcelaDetalle($id_parcela)
    {
        // Verificar que la parcela pertenece al técnico
        $parcela = $this->verifyParcelaOwnership($id_parcela);

        $parcela = Parcela::with([
            'productor.persona',
            'trozas.especie',
            'trozas.estimaciones.tipoEstimacion',
            'trozas.estimaciones.formula',
            'arboles.especie',
            'arboles.estimaciones1.tipoEstimacion',
            'arboles.estimaciones1.formula',
        ])->findOrFail($id_parcela);

        // Calcular totales
        $totalVolumenTrozas = $parcela->trozas->flatMap->estimaciones->sum('calculo');
        $totalBiomasaTrozas = $parcela->trozas->flatMap->estimaciones->sum('biomasa');
        $totalCarbonoTrozas = $parcela->trozas->flatMap->estimaciones->sum('carbono');

        $totalVolumenArboles = $parcela->arboles->flatMap->estimaciones1->sum('calculo');
        $totalBiomasaArboles = $parcela->arboles->flatMap->estimaciones1->sum('biomasa');
        $totalCarbonoArboles = $parcela->arboles->flatMap->estimaciones1->sum('carbono');

        $data = [
            'parcela' => $parcela,
            'totalVolumenTrozas' => $totalVolumenTrozas,
            'totalBiomasaTrozas' => $totalBiomasaTrozas,
            'totalCarbonoTrozas' => $totalCarbonoTrozas,
            'totalVolumenArboles' => $totalVolumenArboles,
            'totalBiomasaArboles' => $totalBiomasaArboles,
            'totalCarbonoArboles' => $totalCarbonoArboles,
            'user' => Auth::user(),
            'tecnico' => $this->tecnico,
            'especies' => Especie::all(),
            'tiposEstimacion' => Tipo_Estimacion::all(),
            'formulas' => Formula::all(),
        ];

        return view('T.parcela-detalle', $data);
    }

    /**
     * CREAR NUEVA PARCELA
     */
    public function parcelaStore(Request $request)
    {
        if (!$this->tecnico) {
            abort(403, 'No tienes permisos de técnico');
        }

        $validated = $request->validate([
            'nom_parcela' => 'required|string|max:255|unique:parcelas,nom_parcela',
            'ubicacion' => 'required|string|max:255',
            'id_productor' => 'required|exists:productores,id_productor',
            'extension' => 'required|numeric|min:0.01',
            'direccion' => 'required|string|max:255',
            'CP' => 'required|string|max:10',
        ]);

        try {
            DB::transaction(function () use ($validated) {
                // 1. Crear la parcela
                $parcela = Parcela::create($validated);
                
                // 2. Asignar la parcela al técnico
                Asigna_Parcela::create([
                    'id_tecnico' => $this->tecnico->id_tecnico,
                    'id_parcela' => $parcela->id_parcela
                ]);
            });

            return redirect()->route('tecnico.dashboard')
                ->with('success', 'Parcela creada y asignada exitosamente.');
                
        } catch (\Exception $e) {
            return back()->with('error', 'Error al crear la parcela: ' . $e->getMessage());
        }
    }

    /**
     * CREAR NUEVA TROZA
     */
    public function trozaStore(Request $request)
    {
        if (!$this->tecnico) {
            abort(403, 'No tienes permisos de técnico');
        }

        $validated = $request->validate([
            'id_parcela' => 'required|exists:parcelas,id_parcela',
            'longitud' => 'required|numeric|min:0.01|max:50',
            'diametro' => 'required|numeric|min:0.01|max:5',
            'diametro_otro_extremo' => 'nullable|numeric|min:0.01|max:5',
            'diametro_medio' => 'nullable|numeric|min:0.01|max:5',
            'densidad' => 'required|numeric|min:0.1|max:1000',
            'id_especie' => 'required|exists:especies,id_especie',
        ]);

        // Verificar que la parcela está asignada al técnico
        $this->verifyParcelaOwnership($validated['id_parcela']);

        try {
            $validated['volumen'] = $this->calcularVolumen($validated);
            Troza::create($validated);

            return back()->with('success', 'Troza registrada exitosamente.');
            
        } catch (\Exception $e) {
            return back()->with('error', 'Error al registrar troza: ' . $e->getMessage());
        }
    }

    /**
     * CREAR NUEVO ÁRBOL
     */
    public function arbolStore(Request $request)
    {
        if (!$this->tecnico) {
            abort(403, 'No tienes permisos de técnico');
        }

        $validated = $request->validate([
            'id_parcela' => 'required|exists:parcelas,id_parcela',
            'altura_total' => 'required|numeric|min:0.1|max:100',
            'diametro_pecho' => 'required|numeric|min:0.01|max:5',
            'id_especie' => 'required|exists:especies,id_especie',
        ]);

        // Verificar que la parcela está asignada al técnico
        $this->verifyParcelaOwnership($validated['id_parcela']);

        try {
            Arbol::create($validated);

            return back()->with('success', 'Árbol registrado exitosamente.');
            
        } catch (\Exception $e) {
            return back()->with('error', 'Error al registrar árbol: ' . $e->getMessage());
        }
    }

    /**
     * CREAR ESTIMACIÓN PARA TROZA
     */
    public function estimacionStore(Request $request)
    {
        if (!$this->tecnico) {
            abort(403, 'No tienes permisos de técnico');
        }

        $validated = $request->validate([
            'id_parcela' => 'required|exists:parcelas,id_parcela',
            'id_troza' => 'required|exists:trozas,id_troza',
            'id_tipo_e' => 'required|exists:tipo_estimaciones,id_tipo_e',
            'id_formula' => 'required|exists:formulas,id_formula',
            'calculo' => 'required|numeric|min:0',
        ]);

        // Verificar que la parcela y troza pertenecen al técnico
        $this->verifyParcelaOwnership($validated['id_parcela']);
        
        $troza = Troza::where('id_troza', $validated['id_troza'])
            ->where('id_parcela', $validated['id_parcela'])
            ->firstOrFail();

        try {
            Estimacion::create($validated);
            return back()->with('success', 'Estimación para troza creada exitosamente.');
            
        } catch (\Exception $e) {
            return back()->with('error', 'Error al crear estimación: ' . $e->getMessage());
        }
    }

    /**
     * CREAR ESTIMACIÓN PARA ÁRBOL (Estimacion1)
     */
    public function estimacionArbolStore(Request $request)
    {
        if (!$this->tecnico) {
            abort(403, 'No tienes permisos de técnico');
        }

        $validated = $request->validate([
            'id_parcela' => 'required|exists:parcelas,id_parcela',
            'id_arbol' => 'required|exists:arboles,id_arbol',
            'id_tipo_e' => 'required|exists:tipo_estimaciones,id_tipo_e',
            'id_formula' => 'required|exists:formulas,id_formula',
            'calculo' => 'required|numeric|min:0',
        ]);

        // Verificar que la parcela y árbol pertenecen al técnico
        $this->verifyParcelaOwnership($validated['id_parcela']);
        
        $arbol = Arbol::where('id_arbol', $validated['id_arbol'])
            ->where('id_parcela', $validated['id_parcela'])
            ->firstOrFail();

        try {
            Estimacion1::create($validated);
            return back()->with('success', 'Estimación para árbol creada exitosamente.');
            
        } catch (\Exception $e) {
            return back()->with('error', 'Error al crear estimación para árbol: ' . $e->getMessage());
        }
    }

    /**
     * MÉTODOS AUXILIARES PRIVADOS
     */
    private function calculateTotalVolumen($parcelas)
    {
        return $parcelas->sum(function($parcela) {
            return $parcela->trozas->sum('volumen');
        });
    }

    private function calcularVolumen(array $data)
    {
        $diametro = $data['diametro_medio'] ?? $data['diametro'];
        $radio = $diametro / 2;
        
        return pi() * pow($radio, 2) * $data['longitud'] * $data['densidad'];
    }

    /**
     * VERIFICAR PROPIEDAD DE PARCELA
     */
    private function verifyParcelaOwnership($parcelaId)
    {
        $parcela = Parcela::where('id_parcela', $parcelaId)
            ->whereHas('asignaciones', function($query) {
                $query->where('id_tecnico', $this->tecnico->id_tecnico);
            })
            ->firstOrFail();
        
        return $parcela;
    }

    /**
     * EXPORTACIÓN DE DATOS - REPORTE PDF PROFESIONAL
     */
    public function exportParcelaToPdf($id_parcela)
    {
        // Verificar que la parcela pertenece al técnico
        $this->verifyParcelaOwnership($id_parcela);

        $parcela = Parcela::with([
            'productor.persona',
            'trozas.especie',
            'trozas.estimaciones.tipoEstimacion',
            'trozas.estimaciones.formula',
            'arboles.especie',
            'arboles.estimaciones1.tipoEstimacion',
            'arboles.estimaciones1.formula',
        ])->findOrFail($id_parcela);

        // Calcular totales de TROZAS
        $totalVolumenTrozas = $parcela->trozas->flatMap->estimaciones->sum('calculo');
        $totalBiomasaTrozas = $parcela->trozas->flatMap->estimaciones->sum('biomasa');
        $totalCarbonoTrozas = $parcela->trozas->flatMap->estimaciones->sum('carbono');

        // Calcular totales de ÁRBOLES
        $totalVolumenArboles = $parcela->arboles->flatMap->estimaciones1->sum('calculo');
        $totalBiomasaArboles = $parcela->arboles->flatMap->estimaciones1->sum('biomasa');
        $totalCarbonoArboles = $parcela->arboles->flatMap->estimaciones1->sum('carbono');

        // Totales generales
        $totales = [
            'volumen' => $totalVolumenTrozas + $totalVolumenArboles,
            'biomasa' => $totalBiomasaTrozas + $totalBiomasaArboles,
            'carbono' => $totalCarbonoTrozas + $totalCarbonoArboles,
            'trozas' => $parcela->trozas->count(),
            'arboles' => $parcela->arboles->count(),
            'estimaciones' => $parcela->trozas->flatMap->estimaciones->count() 
                            + $parcela->arboles->flatMap->estimaciones1->count(),
        ];

        // Estadísticas adicionales
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

        $pdf = PDF::loadView('pdf.parcela-tecnico', [
            'parcela' => $parcela,
            'totales' => $totales,
            'estadisticas' => $estadisticas,
            'tecnico' => $this->tecnico,
            'fecha_generacion' => now(),
        ]);

        $pdf->setPaper('A4', 'portrait');

        return $pdf->download('Reporte_Parcela_'.$parcela->nom_parcela.'_'.now()->format('Y-m-d').'.pdf');
    }
}