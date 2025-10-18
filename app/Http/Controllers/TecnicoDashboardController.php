<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;

// Se añaden los modelos que faltaban
use App\Models\Arbol;
use App\Models\Estimacion1;

// Modelos que ya tenías
use App\Models\Tecnico;
use App\Models\Parcela;
use App\Models\Tipo_Estimacion;
use App\Models\Formula;
use App\Models\Troza;
use App\Models\Especie;
use App\Models\Productor;
use App\Models\Estimacion;
use App\Models\Turno_Corta; // Corregido: App\Models\ en lugar de AppModels
use Barryvdh\DomPDF\Facade\Pdf;
use Carbon\Carbon;
class TecnicoDashboardController extends Controller
{
    public function __construct()
    {
        $this->middleware('auth');
        $this->middleware(function ($request, $next) {
            if (Auth::user()->persona->rol->nom_rol !== 'Tecnico') {
                return response()->view('denegado', [], 403);
            }
            return $next($request);
        });
    }

    public function index()
    {
        $persona = Auth::user()->persona;
       $tecnico = Tecnico::where('id_persona', Auth::id())->firstOrFail();

        $parcelas = $tecnico->parcelas()
            ->with(['productor.persona', 'trozas.estimacion'])
            ->withCount('trozas')
            ->orderBy('nom_parcela')
            ->paginate(10);

        // Calcular totales
        $totalTrozas = $parcelas->sum('trozas_count');
        $totalVolumenMaderable = 0;
        $totalEstimaciones = 0;

        foreach ($parcelas as $parcela) {
            // Reiniciamos los acumuladores para cada parcela
            $volumenParcela = 0;
            $estimacionesParcela = 0;

            // Verificamos cada troza de la parcela
            foreach ($parcela->trozas as $troza) {
                // Solo consideramos trozas con estimación válida
                if ($troza->estimacion && is_numeric($troza->estimacion->calculo)) {
                    $volumenParcela += (float)$troza->estimacion->calculo;
                    $estimacionesParcela++;
                }
            }

            // Asignamos los valores calculados a la parcela
            $parcela->volumen_maderable = round($volumenParcela, 2); // Redondeamos a 2 decimales
            $parcela->estimaciones_count = $estimacionesParcela;

            // Acumulamos los totales generales
            $totalVolumenMaderable += $parcela->volumen_maderable;
            $totalEstimaciones += $parcela->estimaciones_count;
        }

        // Datos para los modales (añadimos arboles)
        $tiposEstimacion = Tipo_Estimacion::all();
        $formulas = Formula::all();
        $especies = Especie::all();
        $productores = Productor::with('persona')->get();
        $arboles = Arbol::whereIn('id_parcela', $tecnico->parcelas->pluck('id_parcela'))->get(); // Solo árboles de parcelas asignadas

        return view('T.index', [
            'tecnico' => $tecnico,
            'parcelas' => $parcelas,
            'totalTrozas' => $totalTrozas,
            // ... otros totales ...
            'tiposEstimacion' => $tiposEstimacion,
            'formulas' => $formulas,
            'especies' => $especies,
            'productores' => $productores,
            'arboles' => $arboles, // Se pasa la variable a la vista
        ]);
    }
      public function storeArbol(Request $request)
    {
        $validated = $request->validate([
            'id_parcela' => 'required|exists:parcelas,id_parcela',
            'id_especie' => 'required|exists:especies,id_especie',
            'altura_total' => 'required|numeric|min:0',
            'diametro_pecho' => 'required|numeric|min:0',
        ]);

        Arbol::create($validated);

        return redirect()->route('tecnico.dashboard')->with('success', 'Árbol registrado correctamente.');
    }
      public function storeEstimacionArbol(Request $request)
    {
        $validated = $request->validate([
            'id_arbol' => 'required|exists:arboles,id_arbol',
            'id_formula' => 'required|exists:formulas,id_formula',
            'id_tipo_e' => 'required|exists:tipo_estimaciones,id_tipo_e',
        ]);

        Estimacion1::create($validated);
        
        return redirect()->route('tecnico.dashboard')->with('success', 'Estimación de árbol registrada correctamente.');
    }


    public function exportParcelaToPdf($id_parcela)
    {
        $parcela = Parcela::with(['productor.persona', 'trozas.estimacion'])
            ->withCount(['trozas'])
            ->findOrFail($id_parcela);

        // Calcular volumen maderable
        $volumen_maderable = 0;
        foreach ($parcela->trozas as $troza) {
            if ($troza->estimacion) {
                $volumen_maderable += (float)$troza->estimacion->calculo;
            }
        }
        $parcela->volumen_maderable = $volumen_maderable;

        $pdf = PDF::loadView('pdf.parcela', compact('parcela'));

        return $pdf->download('reporte_parcela_'.$parcela->nom_parcela.'_'.now()->format('Ymd').'.pdf');
    }

    public function storeEstimacion(Request $request)
    {
        $validated = $this->validateEstimacionRequest($request);

        try {
            DB::beginTransaction();

            $troza = Troza::find($validated['id_troza']);
            $this->createOrUpdateEstimacion($troza, $validated);

            DB::commit();
            return redirect()->route('tecnico.dashboard')->with('success', 'Estimación registrada correctamente');
        } catch (\Exception $e) {
            DB::rollBack();
            return redirect()->route('tecnico.dashboard')->with('error', 'Error al registrar la estimación: ' . $e->getMessage());
        }
    }
 public function storeTroza(Request $request)
    {
        $validated = $request->validate([
            'id_parcela' => 'required|exists:parcelas,id_parcela',
            'id_especie' => 'required|exists:especies,id_especie',
            'longitud' => 'required|numeric|min:0',
            'diametro' => 'required|numeric|min:0',
            'diametro_otro_extremo' => 'nullable|numeric|min:0',
            'diametro_medio' => 'nullable|numeric|min:0',
            'densidad' => 'required|numeric|min:0',
            'codigo_troza' => 'required|unique:trozas,codigo_troza'
        ]);

        try {
            DB::beginTransaction();

            Troza::create($validated);

            DB::commit();
            return redirect()->route('tecnico.dashboard')->with('success', 'Troza creada correctamente');
        } catch (\Exception $e) {
            DB::rollBack();
            return redirect()->route('tecnico.dashboard')->with('error', 'Error al crear la troza: ' . $e->getMessage());
        }
    }
    protected function validateEstimacionRequest(Request $request)
    {
        $persona = Auth::user()->persona;
        $tecnico = Tecnico::where('id_persona', $persona->id_persona)->firstOrFail();

        return $request->validate([
            'id_parcela' => [
                'required',
                'exists:parcelas,id_parcela',
                function ($attribute, $value, $fail) use ($tecnico) {
                    if (!$tecnico->parcelas()->where('id_parcela', $value)->exists()) {
                        $fail('La parcela no está asignada a este técnico');
                    }
                }
            ],
            'id_troza' => [
                'required',
                'exists:trozas,id_troza',
                function ($attribute, $value, $fail) use ($request) {
                    if (!Troza::where('id_troza', $value)
                        ->where('id_parcela', $request->id_parcela)
                        ->exists()) {
                        $fail('La troza seleccionada no pertenece a esta parcela');
                    }
                }
            ],
            'id_tipo_e' => 'required|exists:tipo_estimacion,id_tipo_e',
            'id_formula' => 'required|exists:formulas,id_formula',
            'calculo' => 'required|numeric|min:0'
        ]);
    }

    protected function createOrUpdateEstimacion($troza, $data)
    {
        return $troza->estimacion()->updateOrCreate(
            ['id_troza' => $data['id_troza']],
            [
                'id_tipo_e' => $data['id_tipo_e'],
                'id_formula' => $data['id_formula'],
                'calculo' => $data['calculo']
            ]
        );
    }

    public function getVolumenPorParcela($id_parcela)
    {
        try {
            $parcela = Parcela::with(['trozas.estimacion'])
                ->findOrFail($id_parcela);

            $volumenTotal = $this->calculateVolumenMaderable($parcela);

            return response()->json([
                'success' => true,
                'volumen_total' => $volumenTotal,
                'parcela' => $parcela->nom_parcela
            ]);
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Error al obtener el volumen: ' . $e->getMessage()
            ], 500);
        }
    }

    protected function calculateVolumenMaderable($parcela)
    {
        $volumen = 0;
        foreach ($parcela->trozas as $troza) {
            if ($troza->estimacion) {
                $volumen += (float)$troza->estimacion->calculo;
            }
        }
        return $volumen;
    }

    public function getTrozasPorParcela($id_parcela)
    {
        try {
            $trozas = Troza::with('estimacion')
                ->where('id_parcela', $id_parcela)
                ->get(['id_troza', 'codigo_troza']);

            return response()->json([
                'success' => true,
                'trozas' => $trozas
            ]);
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Error al obtener las trozas: ' . $e->getMessage()
            ], 500);
        }
    }
     public function storeParcela(Request $request)
    {
        $validated = $request->validate([
            'id_productor' => 'required|exists:productores,id_productor',
            'nom_parcela' => 'required|string|max:255',
            'ubicacion' => 'required|string|max:255',
            'hectareas' => 'required|numeric|min:0',
            'tipo_suelo' => 'required|string|max:255',
            'codigo_parcela' => 'required|unique:parcelas,codigo_parcela'
        ]);

        try {
            DB::beginTransaction();

            $parcela = Parcela::create($validated);
            
            // Asignar la parcela al técnico
            $persona = Auth::user()->persona;
            $tecnico = Tecnico::where('id_persona', $persona->id_persona)->firstOrFail();
            $tecnico->parcelas()->attach($parcela->id_parcela);

            DB::commit();
            return redirect()->route('tecnico.dashboard')->with('success', 'Parcela creada y asignada correctamente');
        } catch (\Exception $e) {
            DB::rollBack();
            return redirect()->route('tecnico.dashboard')->with('error', 'Error al crear la parcela: ' . $e->getMessage());
        }
    }
     public function storeTurnoCorta(Request $request)
    {
        $validated = $request->validate([
            'id_parcela' => 'required|exists:parcelas,id_parcela',
            'codigo_corta' => 'required|string|max:255|unique:turno_cortas,codigo_corta',
            'fecha_corta' => 'required|date',
            'fecha_fin' => 'required|date|after_or_equal:fecha_corta'
        ]);

        try {
            DB::beginTransaction();

            Turno_Corta::create($validated);

            DB::commit();
            return redirect()->route('tecnico.dashboard')->with('success', 'Turno de corta creado correctamente');
        } catch (\Exception $e) {
            DB::rollBack();
            return redirect()->route('tecnico.dashboard')->with('error', 'Error al crear el turno de corta: ' . $e->getMessage());
        }
    }
     public function updateEstimacion(Request $request, $id)
    {
        $validated = $this->validateEstimacionRequest($request);

        try {
            DB::beginTransaction();

            $estimacion = Estimacion::findOrFail($id);
            $estimacion->update($validated);

            DB::commit();
            return redirect()->route('tecnico.dashboard')->with('success', 'Estimación actualizada correctamente');
        } catch (\Exception $e) {
            DB::rollBack();
            return redirect()->route('tecnico.dashboard')->with('error', 'Error al actualizar la estimación: ' . $e->getMessage());
        }
    }

    public function updateTroza(Request $request, $id)
    {
        $validated = $request->validate([
            'id_especie' => 'required|exists:especies,id_especie',
            'longitud' => 'required|numeric|min:0',
            'diametro' => 'required|numeric|min:0',
            'diametro_otro_extremo' => 'nullable|numeric|min:0',
            'diametro_medio' => 'nullable|numeric|min:0',
            'densidad' => 'required|numeric|min:0'
        ]);

        try {
            DB::beginTransaction();

            $troza = Troza::findOrFail($id);
            $troza->update($validated);

            DB::commit();
            return redirect()->route('tecnico.dashboard')->with('success', 'Troza actualizada correctamente');
        } catch (\Exception $e) {
            DB::rollBack();
            return redirect()->route('tecnico.dashboard')->with('error', 'Error al actualizar la troza: ' . $e->getMessage());
        }
    }

    public function destroyEstimacion($id)
    {
        try {
            DB::beginTransaction();

            $estimacion = Estimacion::findOrFail($id);
            $estimacion->delete();

            DB::commit();
            return redirect()->route('tecnico.dashboard')->with('success', 'Estimación eliminada correctamente');
        } catch (\Exception $e) {
            DB::rollBack();
            return redirect()->route('tecnico.dashboard')->with('error', 'Error al eliminar la estimación: ' . $e->getMessage());
        }
    }

    public function destroyTroza($id)
    {
        try {
            DB::beginTransaction();

            $troza = Troza::findOrFail($id);
            $troza->delete();

            DB::commit();
            return redirect()->route('tecnico.dashboard')->with('success', 'Troza eliminada correctamente');
        } catch (\Exception $e) {
            DB::rollBack();
            return redirect()->route('tecnico.dashboard')->with('error', 'Error al eliminar la troza: ' . $e->getMessage());
        }
    }
}
