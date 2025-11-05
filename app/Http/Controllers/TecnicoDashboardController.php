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
    
    

    public function index()
    {
        return $this->dashboard();
    }

    /**
     * Dashboard principal del técnico - ACTUALIZADO CON ÁRBOLES
     */
 // En App/Http/Controllers/TecnicoDashboardController.php

// En App/Http/Controllers/TecnicoDashboardController.php

public function dashboard()
{
   

    // --- 1. IDs de Origen (Correcto) ---
    $parcelaIds = Asigna_Parcela::where('id_tecnico', $this->tecnico->id_tecnico)
                                ->pluck('id_parcela');

    // --- 2. IDs Intermedios (Correcto) ---
    $trozaIds = Troza::whereIn('id_parcela', $parcelaIds)->pluck('id_troza');
    $arbolIds = Arbol::whereIn('id_parcela', $parcelaIds)->pluck('id_arbol');

    // --- 3. Cálculos TOTALES (para las "Stat Cards") ---
    // (Total Trozas y Árboles)
    $totalTrozas = $trozaIds->count();
    $totalArboles = $arbolIds->count();

    // (Total Estimaciones)
    $countEstimaciones = Estimacion::whereIn('id_troza', $trozaIds)->count();
    $countEstimaciones1 = Estimacion1::whereIn('id_arbol', $arbolIds)->count();
    $totalEstimaciones = $countEstimaciones + $countEstimaciones1;

    // (Volumen Total Maderable - LA CORRECCIÓN CLAVE)
    $volumenEstimaciones = Estimacion::whereIn('id_troza', $trozaIds)->sum('calculo');
    $volumenEstimaciones1 = Estimacion1::whereIn('id_arbol', $arbolIds)->sum('calculo');
    
    // El volumen total es SÓLO la suma de las estimaciones
    $totalVolumenMaderable = $volumenEstimaciones + $volumenEstimaciones1;
    
    // --- 4. Consulta PAGINADA (para la tabla) ---
    // (HEMOS ELIMINADO withSum('trozas', 'volumen') de aquí)
    $parcelas = Parcela::with(['productor.persona'])
        ->whereIn('id_parcela', $parcelaIds)
        ->withCount([
            'trozas', 
            'arboles', 
            'estimaciones',
            'estimaciones1'
        ])
        ->paginate(10); // NOTA: 'withSum' para trozas se ha quitado.

    // --- 5. Optimización N+1 para SUMAS ANIDADAS (Correcto) ---
    // Esto carga las sumas de 'calculo' para las 10 parcelas de la página
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

    // Adjuntamos las sumas a la colección paginada
    $parcelas->each(function ($parcela) use ($estimacionSums, $estimacion1Sums) {
        $parcela->estimaciones_sum_calculo = $estimacionSums->get($parcela->id_parcela, 0);
        $parcela->estimaciones1_sum_calculo = $estimacion1Sums->get($parcela->id_parcela, 0);
    });

    // --- 6. Enviar Datos a la Vista (Correcto) ---
    $data = [
        'parcelas' => $parcelas,
        'totalTrozas' => $totalTrozas,
        'totalArboles' => $totalArboles,
        'totalEstimaciones' => $totalEstimaciones,
        'totalVolumenMaderable' => $totalVolumenMaderable, // Corregido
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