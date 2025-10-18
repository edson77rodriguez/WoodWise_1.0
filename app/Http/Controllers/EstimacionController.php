<?php

namespace App\Http\Controllers;

use App\Models\Estimacion;
use App\Models\Tipo_Estimacion;
use App\Models\Formula;
use App\Models\Troza;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class EstimacionController extends Controller
{
    public function __construct()
    {
        $this->middleware('auth');
        $this->middleware(function ($request, $next) {
            if (Auth::user()->persona->rol->nom_rol !== 'Administrador' && Auth::user()->persona->rol->nom_rol !== 'Tecnico') {
                return response()->view('denegado', [], 403);
            }
            return $next($request);
        });
    }
    public function edit($id)
{
    $estimacion = Estimacion::with(['tipoEstimacion', 'formula', 'troza'])->findOrFail($id);
    
    return view('estimaciones.edit', [
        'estimacion' => $estimacion,
        'tiposEstimacion' => TipoEstimacion::all(),
        'formulas' => Formula::all(),
        'trozas' => Troza::all(),
    ]);
}
    public function index()
    {
$estimaciones = Estimacion::with(['tipoEstimacion', 'formula', 'troza.especie', 'troza.parcela'])
    ->paginate(10); // 10 por página
        $tiposEstimacion = Tipo_Estimacion::all()->WhereIn('desc_estimacion',['Volumen Maderable']);
        $trozas = Troza::with(['especie', 'parcela'])->get();
$formulas = Formula::all()  // Obtiene TODOS los registros
    ->whereIn('nom_formula', [
        'Formula de Smalian',
        'Formula de Huber',
        'Formula Newton',
        'formula Tronco Cono'
    ]);      
 return view('estimaciones.index', compact('estimaciones', 'tiposEstimacion', 'formulas', 'trozas'));
    }

    public function getFormulasByTipo($tipoId)
    {
        $tipo = Tipo_Estimacion::findOrFail($tipoId);
        $formulas = Formula::where('id_tipo_e', $tipoId)->get();
        
        return response()->json($formulas);
    }

    public function store(Request $request)
    {
        $request->validate([
            'id_tipo_e' => 'required|exists:tipo_estimaciones,id_tipo_e',
            'id_formula' => 'required|exists:formulas,id_formula',
            'id_troza' => 'required|exists:trozas,id_troza',
        ]);

        Estimacion::create($request->all());

        return redirect()->route('estimaciones.index')
               ->with('success', 'Estimación creada correctamente');
    }

    public function update(Request $request, $id)
    {
        $request->validate([
            'id_tipo_e' => 'required|exists:tipo_estimaciones,id_tipo_e',
            'id_formula' => 'required|exists:formulas,id_formula',
            'id_troza' => 'required|exists:trozas,id_troza',
        ]);

        $estimacion = Estimacion::findOrFail($id);
        $estimacion->update($request->all());

        return redirect()->route('estimaciones.index')
               ->with('success', 'Estimación actualizada correctamente');
    }

    public function destroy($id)
    {
        $estimacion = Estimacion::findOrFail($id);
        $estimacion->delete();

        return redirect()->route('estimaciones.index')
               ->with('success', 'Estimación eliminada con éxito.');
    }

    private function getUnidadMedida($tipo)
    {
        switch ($tipo) {
            case 'Volumen Maderable': return 'm³';
            case 'Carbono': return 'kg CO₂';
            case 'Biomasa': return 'kg';
            case 'Área Basal': return 'm²';
            default: return '';
        }
    }
}