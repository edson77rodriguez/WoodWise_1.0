<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\Formula;
use App\Models\Tipo_Estimacion;
use App\Models\Catalogo;
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
        
        return view('formulas.index', compact('formulas', 'tiposEstimacion', 'catalogos'));
    }

    /**
     * Almacenar una nueva fórmula
     */
    public function store(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'nom_formula' => 'required|string|max:255|unique:formulas,nom_formula',
            'expresion' => 'required|string',
            'id_tipo_e' => 'required|exists:tipo_estimaciones,id_tipo_e',
            'id_cat' => 'required|exists:catalogos,id_cat'
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
        return view('formulas.edit', compact('formula', 'tiposEstimacion', 'catalogos'));
    }

    /**
     * Actualizar una fórmula existente
     */
    public function update(Request $request, string $id)
    {
        $formula = Formula::findOrFail($id);
        
        $validator = Validator::make($request->all(), [
            'nom_formula' => 'required|string|max:255|unique:formulas,nom_formula,'.$formula->id_formula.',id_formula',
            'expresion' => 'required|string',
            'id_tipo_e' => 'required|exists:tipo_estimaciones,id_tipo_e',
            'id_cat' => 'required|exists:catalogos,id_cat'
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
            'expresion' => 'required|string'
        ]);
        
        // Aquí implementarías la lógica de validación de la expresión
        $esValida = $this->validarExpresionMatematica($request->expresion);
        
        return response()->json([
            'valida' => $esValida,
            'mensaje' => $esValida ? 'Expresión válida' : 'Expresión inválida'
        ]);
    }
    
    /**
     * Método privado para validar la expresión matemática
     */
    private function validarExpresionMatematica($expresion)
    {
        // Implementación básica - deberías adaptarla a tus necesidades
        try {
            // Ejemplo simple: verificar que solo contenga caracteres permitidos
            if (!preg_match('/^[0-9+\-*\/\s\.()xyz]+$/', $expresion)) {
                return false;
            }
            
            // Aquí podrías agregar más validaciones complejas
            return true;
        } catch (\Exception $e) {
            return false;
        }
    }
}