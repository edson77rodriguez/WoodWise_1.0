<?php

namespace App\Http\Controllers;

use App\Models\Estimacion;
use App\Models\Tipo_Estimacion;
use App\Models\Formula;
use App\Models\Troza;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use App\Services\FormulaEngineService;

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
        'tiposEstimacion' => Tipo_Estimacion::all(),
        'formulas' => Formula::where('estado_revision', 'aprobada')->get(),
        'trozas' => Troza::all(),
    ]);
}
    public function index()
    {
$estimaciones = Estimacion::with(['tipoEstimacion', 'formula', 'troza.especie', 'troza.parcela'])
    ->paginate(10); // 10 por página
        $tiposEstimacion = Tipo_Estimacion::all()->WhereIn('desc_estimacion',['Volumen Maderable']);
        $trozas = Troza::with(['especie', 'parcela'])->get();
$formulas = Formula::where('estado_revision', 'aprobada')
    ->where('id_tipo_e', 1)
    ->where('id_cat', 1)
    ->orderBy('nom_formula')
    ->get();      
 return view('estimaciones.index', compact('estimaciones', 'tiposEstimacion', 'formulas', 'trozas'));
    }

    public function getFormulasByTipo($tipoId)
    {
        $tipo = Tipo_Estimacion::findOrFail($tipoId);
        $formulas = Formula::where('id_tipo_e', $tipoId)
            ->where('estado_revision', 'aprobada')
            ->get();
        
        return response()->json($formulas);
    }

    public function store(Request $request)
    {
        $validated = $request->validate([
            'id_tipo_e' => 'required|exists:tipo_estimaciones,id_tipo_e',
            'id_formula' => 'required|exists:formulas,id_formula',
            'id_troza' => 'required|exists:trozas,id_troza',
        ]);

        $formula = Formula::findOrFail($validated['id_formula']);
        $troza = Troza::findOrFail($validated['id_troza']);

        if ($formula->modo_ejecucion === 'app') {
            try {
                $outputs = app(FormulaEngineService::class)->calculateForModel($formula, $troza);
                $validated = array_merge($validated, $outputs);
            } catch (\InvalidArgumentException $exception) {
                return back()->withInput()->with('error', $exception->getMessage());
            }
        }

        Estimacion::create($validated);

        return redirect()->route('estimaciones.index')
               ->with('success', 'Estimación creada correctamente');
    }

    public function update(Request $request, $id)
    {
        $validated = $request->validate([
            'id_tipo_e' => 'required|exists:tipo_estimaciones,id_tipo_e',
            'id_formula' => 'required|exists:formulas,id_formula',
            'id_troza' => 'required|exists:trozas,id_troza',
        ]);

        $estimacion = Estimacion::findOrFail($id);
        $formula = Formula::findOrFail($validated['id_formula']);
        $troza = Troza::findOrFail($validated['id_troza']);

        if ($formula->modo_ejecucion === 'app') {
            try {
                $outputs = app(FormulaEngineService::class)->calculateForModel($formula, $troza);
                $validated = array_merge($validated, $outputs);
            } catch (\InvalidArgumentException $exception) {
                return back()->withInput()->with('error', $exception->getMessage());
            }
        }

        $estimacion->update($validated);

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