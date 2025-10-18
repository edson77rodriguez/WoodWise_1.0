<?php

namespace App\Http\Controllers;

use App\Models\Estimacion1;
use App\Models\Tipo_Estimacion;
use App\Models\Formula;
use App\Models\Arbol;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Validation\Rule;

class Estimacion1Controller extends Controller
{
    public function __construct()
    {
        $this->middleware('auth');
        $this->middleware(function ($request, $next) {
            if (Auth::user()->persona->rol->nom_rol !== 'Administrador' && 
                Auth::user()->persona->rol->nom_rol !== 'Tecnico') {
                return response()->view('denegado', [], 403);
            }
            return $next($request);
        });
    }

    /**
     * Mostrar listado de estimaciones con paginación
     */
    public function index(Request $request)
{
  
   
    
$estimaciones = Estimacion1::with(['tipoEstimacion', 'formula', 'arbol.especie', 'arbol.parcela'])
                            ->paginate(10);
    
    // CORRECCIÓN: Usar whereIn directamente con get()
    $tiposEstimacion = Tipo_Estimacion::whereIn('desc_estimacion', ['Volumen Maderable'])->get();
    
   $formulas = Formula::whereIn('nom_formula', [
    'Biomasa Pinus montezumae',
    'Biomasa Quercus crassifolia',
    'Biomasa Quercus rugosa',
    'Biomasa Pinus pseudostrobus'
])->get();
    
    $arboles = Arbol::with(['especie', 'parcela'])->get();
    
    return view('estimaciones1.index', compact('estimaciones', 'tiposEstimacion', 'formulas', 'arboles'));
}

    /**
     * Obtener fórmulas por tipo (para AJAX)
     */
    public function getFormulasByTipo($tipoId)
    {
        $formulas = Formula::where('id_tipo_e', $tipoId)->get();
        return response()->json($formulas);
    }

    /**
     * Almacenar nueva estimación
     */
    public function store(Request $request)
    {
        $validatedData = $request->validate([
            'id_tipo_e' => 'required|exists:tipo_estimaciones,id_tipo_e',
            'id_formula' => [
                'required',
                'exists:formulas,id_formula',
                Rule::unique('estimaciones1')->where(function ($query) use ($request) {
                    return $query->where('id_arbol', $request->id_arbol)
                               ->where('id_tipo_e', $request->id_tipo_e);
                })
            ],
            'id_arbol' => 'required|exists:arboles,id_arbol',
            'calculo' => 'nullable|numeric'
        ]);

       

        Estimacion1::create($validatedData);

        return redirect()->route('estimaciones1.index')
               ->with('success', 'Estimación creada correctamente');
    }

    /**
     * Actualizar estimación existente
     */
    public function update(Request $request, $id)
    {
        $estimacion = Estimacion1::findOrFail($id);
        
        $validatedData = $request->validate([
            'id_tipo_e' => 'required|exists:tipo_estimaciones,id_tipo_e',
            'id_formula' => [
                'required',
                'exists:formulas,id_formula',
                Rule::unique('estimaciones1')->where(function ($query) use ($request, $estimacion) {
                    return $query->where('id_arbol', $request->id_arbol)
                               ->where('id_tipo_e', $request->id_tipo_e)
                               ->where('id_estimacion1', '!=', $estimacion->id_estimacion1);
                })
            ],
            'id_arbol' => 'required|exists:arboles,id_arbol',
            'calculo' => 'nullable|numeric'
        ]);


        $estimacion->update($validatedData);

        return redirect()->route('estimaciones1.index')
               ->with('success', 'Estimación actualizada correctamente');
    }

    /**
     * Eliminar estimación
     */
    public function destroy($id)
    {
        $estimacion = Estimacion1::findOrFail($id);
        $estimacion->delete();

        return redirect()->route('estimaciones1.index')
               ->with('success', 'Estimación eliminada correctamente');
    }


}