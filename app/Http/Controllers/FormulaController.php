<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\Formula;
use App\Models\Tipo_Estimacion;
use App\Models\Catalogo;
use App\Models\Especie;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Validator;

class FormulaController extends Controller
{
    public function __construct()
    {
        $this->middleware('auth');
        $this->middleware(function ($request, $next) {
            if (Auth::user()->persona->rol->nom_rol !== 'Administrador') {
                return response()->view('denegado', [], 403);
            }
            return $next($request);
        });
    }

    /**
     * Mostrar el listado de fórmulas con paginación y búsqueda
     */
    public function index()
    {
        $formulas = Formula::with(['tipoEstimacion', 'catalogo'])
            ->orderBy('nom_formula')
            ->paginate(10);
            
        $tiposEstimacion = Tipo_Estimacion::all();
        $catalogos = Catalogo::all();
        $especies = Especie::orderBy('nom_comun')->get();
        
        return view('formulas.index', compact('formulas', 'tiposEstimacion', 'catalogos', 'especies'));
    }

    /**
     * Almacenar una nueva fórmula
     */
    public function store(Request $request)
{
    $data = $this->normalizarDatosFormula($request->all());

    // Las fórmulas nuevas SIEMPRE se calculan con el motor de la app,
    // nunca deben usar 'trigger' (eso es exclusivo de las 8 fórmulas legacy en MySQL)
    $data['modo_ejecucion'] = 'app';

    $validator = Validator::make($data, [
        'nom_formula' => 'required|string|max:255|unique:formulas,nom_formula',
        'expresion' => 'required|string',
        'id_tipo_e' => 'required|exists:tipo_estimaciones,id_tipo_e',
        'id_cat' => 'required|exists:catalogos,id_cat',
        'modo_ejecucion' => 'required|in:trigger,app',
        'estado_revision' => 'required|in:revision,aprobada,rechazada',
        'biomasa_factor' => 'nullable|numeric',
        'carbono_factor' => 'nullable|numeric',
        'revision_notas' => 'nullable|string',
        'resultado_tipo' => 'nullable|string|in:calculo,biomasa,carbono',
        'variables_schema' => 'nullable|array',
        'especies_relacionadas' => 'nullable|array',
        'especies_relacionadas.*' => 'exists:especies,id_especie',
    ], [
        'nom_formula.unique' => 'El nombre de la fórmula ya existe',
        'id_tipo_e.exists' => 'El tipo de estimación seleccionado no es válido',
        'id_cat.exists' => 'El catálogo seleccionado no es válido'
    ]);

    if ($validator->fails()) {
        return redirect()->back()
            ->withErrors($validator)
            ->withInput()
            ->with('error', 'Error al crear la fórmula');
    }

    Formula::create($validator->validated());

    return redirect()->route('formulas.index')
        ->with('success', 'Fórmula creada exitosamente');
}

    /**
     * Mostrar detalles de una fórmula específica
     */
    public function show(string $id)
    {
        $formula = Formula::with(['tipoEstimacion', 'catalogo'])->findOrFail($id);
        return view('formulas.show', compact('formula'));
    }

    /**
     * Mostrar formulario de edición
     */
    public function edit(string $id)
    {
        $formula = Formula::findOrFail($id);
        $tiposEstimacion = Tipo_Estimacion::all();
        $catalogos = Catalogo::all();
        $especies = Especie::orderBy('nom_comun')->get();
        return view('formulas.edit', compact('formula', 'tiposEstimacion', 'catalogos', 'especies'));
    }

    /**
     * Actualizar una fórmula existente
     */
  public function update(Request $request, string $id)
{
    $formula = Formula::findOrFail($id);
    $data = $this->normalizarDatosFormula($request->all());

    // Protegemos el modo de las fórmulas legacy (trigger); cualquier otra siempre es 'app'
    $data['modo_ejecucion'] = $formula->modo_ejecucion === 'trigger' ? 'trigger' : 'app';

    $validator = Validator::make($data, [
        'nom_formula' => 'required|string|max:255|unique:formulas,nom_formula,'.$formula->id_formula.',id_formula',
        'expresion' => 'required|string',
        'id_tipo_e' => 'required|exists:tipo_estimaciones,id_tipo_e',
        'id_cat' => 'required|exists:catalogos,id_cat',
        'modo_ejecucion' => 'required|in:trigger,app',
        'estado_revision' => 'required|in:revision,aprobada,rechazada',
        'biomasa_factor' => 'nullable|numeric',
        'carbono_factor' => 'nullable|numeric',
        'revision_notas' => 'nullable|string',
        'resultado_tipo' => 'nullable|string|in:calculo,biomasa,carbono',
        'variables_schema' => 'nullable|array',
        'especies_relacionadas' => 'nullable|array',
        'especies_relacionadas.*' => 'exists:especies,id_especie',
    ]);

    if ($validator->fails()) {
        return redirect()->back()
            ->withErrors($validator)
            ->withInput()
            ->with('error', 'Error al actualizar la fórmula');
    }

    $formula->update($validator->validated());

    return redirect()->route('formulas.index')
        ->with('success', 'Fórmula actualizada exitosamente');
}

    public function aprobar(string $id)
    {
        $formula = Formula::findOrFail($id);

        $formula->update([
            'estado_revision' => 'aprobada',
            'revision_at' => now(),
        ]);

        return redirect()->route('formulas.index')
            ->with('success', 'Fórmula aprobada exitosamente');
    }

    public function rechazar(Request $request, string $id)
    {
        $request->validate([
            'revision_notas' => 'nullable|string|max:2000',
        ]);

        $formula = Formula::findOrFail($id);

        $formula->update([
            'estado_revision' => 'rechazada',
            'revision_notas' => $request->input('revision_notas'),
            'revision_at' => now(),
        ]);

        return redirect()->route('formulas.index')
            ->with('success', 'Fórmula rechazada correctamente');
    }

    /**
     * Eliminar una fórmula
     */
    public function destroy(string $id)
    {
        try {
            $formula = Formula::findOrFail($id);
            
            // Verificar si la fórmula está siendo usada antes de eliminar
            if ($formula->estaEnUso()) {
                return redirect()->route('formulas.index')
                    ->with('error', 'No se puede eliminar la fórmula porque está en uso');
            }
            
            $formula->delete();
            
            return redirect()->route('formulas.index')
                ->with('success', 'Fórmula eliminada exitosamente');
                
        } catch (\Exception $e) {
            return redirect()->route('formulas.index')
                ->with('error', 'Error al eliminar la fórmula: ' . $e->getMessage());
        }
    }

    /**
     * Validar la expresión matemática (API)
     */
    public function validarExpresion(Request $request)
{
    $request->validate([
        'expresion' => 'required|string',
        'variables_schema' => 'nullable|array',
    ]);

    try {
        $engine = app(\App\Services\FormulaEngineService::class);
        $expresion = $engine->normalizeExpression($request->input('expresion'));

        // Usamos las variables detectadas (o el schema si viene) con un valor de prueba (1)
        $variables = $engine->extractVariables($expresion);
        $dummy = array_fill_keys($variables, 1);

        $resultado = $engine->evaluate($expresion, $dummy);

        return response()->json([
            'valida' => true,
            'mensaje' => 'Expresión válida',
            'resultado_prueba' => $resultado, // con todas las variables = 1
        ]);
    } catch (\InvalidArgumentException $e) {
        return response()->json([
            'valida' => false,
            'mensaje' => $e->getMessage(),
        ]);
    }
}
    
    /**
     * Método privado para validar la expresión matemática
     */
    private function validarExpresionMatematica($expresion)
    {
        // Implementación básica - deberías adaptarla a tus necesidades
        try {
            // Ejemplo simple: verificar que solo contenga caracteres permitidos
            if (!preg_match('/^[0-9+\-*\/\^\s\.(),A-Za-z_]+$/', $expresion)) {
                return false;
            }
            
            // Aquí podrías agregar más validaciones complejas
            return true;
        } catch (\Exception $e) {
            return false;
        }
    }

    private function normalizarDatosFormula(array $data): array
    {
        $data['variables_schema'] = $this->decodeJsonField($data['variables_schema'] ?? null);
        $data['especies_relacionadas'] = $this->decodeJsonField($data['especies_relacionadas'] ?? null);
        $data['modo_ejecucion'] = $data['modo_ejecucion'] ?? 'trigger';
        $data['estado_revision'] = $data['estado_revision'] ?? 'revision';
        $data['revision_notas'] = $data['revision_notas'] ?? null;

        $idCat = (int) ($data['id_cat'] ?? 0);

        if ($idCat === 1) {
            $data['resultado_tipo'] = 'calculo';
            $data['biomasa_factor'] = null;
            $data['especies_relacionadas'] = null;
        } elseif ($idCat === 2) {
            $data['resultado_tipo'] = 'biomasa';
            $data['biomasa_factor'] = $data['biomasa_factor'] ?? 1;
        } else {
            $data['resultado_tipo'] = $data['resultado_tipo'] ?? 'calculo';
        }

        $data['carbono_factor'] = $data['carbono_factor'] ?? 0.5;

        return $data;
    }

    private function decodeJsonField(mixed $value): ?array
    {
        if ($value === null || $value === '') {
            return null;
        }

        if (is_array($value)) {
            return $value;
        }

        $decoded = json_decode((string) $value, true);

        return is_array($decoded) ? $decoded : null;
    }
}