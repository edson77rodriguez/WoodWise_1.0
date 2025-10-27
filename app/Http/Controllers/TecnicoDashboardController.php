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
            if (Auth::user()->persona->rol->nom_rol !== 'Tecnico') {
                return response()->view('denegado', [], 403);
            }
            
            if (!Auth::user()->persona->tecnico) {
                return response()->view('denegado', [], 403);
            }
            
            $this->tecnico = Auth::user()->persona->tecnico;
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
        if (!$this->tecnico) {
            abort(403, 'No tienes permisos de técnico');
        }

        // Obtener parcelas asignadas al técnico
        $parcelas = Parcela::with([
                'productor.persona',
                'trozas' => function($query) {
                    $query->select('id_troza', 'longitud','diametro','diametro_otro_extremo', 'diametro_medio', 'densidad', 'id_especie', 'id_parcela');
                },
                'arboles' => function($query) {
                    $query->select('id_arbol', 'altura_total', 'diametro_pecho', 'id_especie', 'id_parcela');
                },
                'estimaciones' => function($query) {
                    $query->select('id_estimacion', 'id_parcela', 'calculo');
                },
                'asignaciones' => function($query) {
                    $query->where('id_tecnico', $this->tecnico->id_tecnico);
                }
            ])
            ->whereHas('asignaciones', function($query) {
                $query->where('id_tecnico', $this->tecnico->id_tecnico);
            })
            ->withCount(['trozas', 'arboles', 'estimaciones'])
            ->paginate(10);

        // Cálculos optimizados
        $totalTrozas = $parcelas->sum('trozas_count');
        $totalArboles = $parcelas->sum('arboles_count');
        $totalEstimaciones = $parcelas->sum('estimaciones_count');
        $totalVolumenMaderable = $this->calculateTotalVolumen($parcelas);

        $data = [
            'parcelas' => $parcelas,
            'totalTrozas' => $totalTrozas,
            'totalArboles' => $totalArboles,
            'totalEstimaciones' => $totalEstimaciones,
            'totalVolumenMaderable' => $totalVolumenMaderable,
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
            'id_especie' => 'required|exists:especies,id_especie'
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
     * EXPORTACIÓN DE DATOS - TÉCNICO
     */
    public function exportParcelaToPdf($id_parcela)
    {
        $parcela = Parcela::with(['productor.persona', 'trozas.especie', 'arboles.especie'])
            ->withCount(['trozas', 'arboles'])
            ->findOrFail($id_parcela);

        // Calcular volumen maderable
        $volumen_maderable = $parcela->trozas->sum('volumen');

        $parcela->volumen_maderable = $volumen_maderable;

        $pdf = PDF::loadView('pdf.parcela', compact('parcela'));

        return $pdf->download('reporte_parcela_'.$parcela->nom_parcela.'_'.now()->format('Ymd').'.pdf');
    }
}