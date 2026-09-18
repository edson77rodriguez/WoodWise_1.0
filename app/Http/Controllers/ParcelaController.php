<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\Parcela;
use App\Models\Productor;
use App\Models\Especie;
use App\Models\Troza;
use App\Models\Arbol;
use App\Models\Formula;
use App\Models\Tipo_Estimacion;
use App\Models\Estimacion;
use App\Models\Estimacion1;
use Illuminate\Support\Facades\Auth;

class ParcelaController extends Controller
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

    public function show($id)
    {
        $parcela = Parcela::with([
            'trozas.especie',
            'trozas.estimacion', // 👈 Cargar estimaciones de trozas
            'arboles.especie',
            'arboles.estimaciones1', // 👈 Cargar estimaciones de árboles
            'turnosCorta'
        ])->withCount([
            'trozas',
            'arboles',
            'turnosCorta'
        ])->findOrFail($id);

        return view('parcelas.show', [
            'parcela' => $parcela,
            'especies' => Especie::all(),
            'tiposEstimacion' => Tipo_Estimacion::all(),
            'formulas' => Formula::where('estado_revision', 'aprobada')->orderBy('nom_formula')->get(),
            'productores' => Productor::all()
        ]);
    }

    // Método para guardar árbol
    public function storeArbol(Request $request)
    {
        $validatedData = $request->validate([
            'id_parcela' => 'required|exists:parcelas,id_parcela',
            'altura_total' => 'required|numeric|min:0.1|max:100',
            'diametro_pecho' => 'required|numeric|min:0.01|max:5',
            'id_especie' => 'required|exists:especies,id_especie',
            'observaciones' => 'nullable|string|max:500',
        ]);

        $arbol = Arbol::create($validatedData);

        return redirect()->route('parcelas.show', $request->id_parcela)
            ->with('success', 'Árbol registrado exitosamente.');
    }

    // Método para actualizar árbol
    public function updateArbol(Request $request, $id_arbol)
    {
        $arbol = Arbol::findOrFail($id_arbol);
        
        $validatedData = $request->validate([
            'altura_total' => 'required|numeric|min:0.1|max:100',
            'diametro_pecho' => 'required|numeric|min:0.01|max:5',
            'id_especie' => 'required|exists:especies,id_especie',
            'activo' => 'required|boolean',
            'observaciones' => 'nullable|string|max:500',
        ]);

        $arbol->update($validatedData);

        return redirect()->route('parcelas.show', $arbol->id_parcela)
            ->with('success', 'Árbol actualizado exitosamente.');
    }

    // Método para eliminar árbol
    public function destroyArbol($id_arbol)
    {
        $arbol = Arbol::findOrFail($id_arbol);
        $id_parcela = $arbol->id_parcela;
        $arbol->delete();

        return redirect()->route('parcelas.show', $id_parcela)
            ->with('success', 'Árbol eliminado exitosamente.');
    }

    // Método para guardar estimación de árbol
    public function storeEstimacionArbol(Request $request)
    {
        $validatedData = $request->validate([
            'id_parcela' => 'required|exists:parcelas,id_parcela',
            'id_arbol' => 'required|exists:arboles,id_arbol',
            'id_tipo_e' => 'required|exists:tipo_estimaciones,id_tipo_e',
            'id_formula' => 'required|exists:formulas,id_formula',
            'calculo' => 'required|numeric|min:0',
        ]);

        $formula = Formula::findOrFail($validatedData['id_formula']);
        $arbol = Arbol::findOrFail($validatedData['id_arbol']);

        if ($formula->modo_ejecucion === 'app') {
            try {
                $outputs = app(\App\Services\FormulaEngineService::class)->calculateForModel($formula, $arbol);
                $validatedData = array_merge($validatedData, $outputs);
            } catch (\InvalidArgumentException $exception) {
                return back()->withInput()->with('error', $exception->getMessage());
            }
        }

        // Crear la estimación1 asociada al árbol (usando el modelo correcto para árboles)
        $estimacion = Estimacion1::create([
            'id_arbol' => $validatedData['id_arbol'],
            'id_tipo_e' => $validatedData['id_tipo_e'],
            'id_formula' => $validatedData['id_formula'],
            'calculo' => $validatedData['calculo'],
            'biomasa' => $validatedData['biomasa'] ?? null,
            'carbono' => $validatedData['carbono'] ?? null,
        ]);

        return redirect()->route('parcelas.show', $request->id_parcela)
            ->with('success', 'Estimación para árbol creada exitosamente.');
    }

    // ... (los demás métodos existentes se mantienen igual)
    public function trozaedit($id_parcela)
    {
        $trozas = Troza::where('id_parcela', $id_parcela)->get();
        $especies = Especie::all();
        $parcela = Parcela::findOrFail($id_parcela);

        return view('partials.modals.edit_troza', compact('parcela', 'especies', 'trozas'));
    }

    public function index()
    {
        $parcelas = Parcela::all();
        $productores = Productor::all();
        $especies = Especie::all();
        return view('parcelas.index1', compact('parcelas', 'productores','especies'));
    }

    public function updateTroza(Request $request, $id_troza)
    {
        $troza = Troza::findOrFail($id_troza);
        
        $validatedData = $request->validate([
            'longitud' => 'required|numeric|min:0.1',
            'diametro' => 'required|numeric|min:0.1',
            'densidad' => 'required|numeric|min:0.1',
            'id_especie' => 'required|exists:especies,id_especie',
            'id_parcela' => 'required|exists:parcelas,id_parcela',
        ]);

        $troza->update($validatedData);

        return redirect()->route('parcelas.show', $troza->id_parcela)
            ->with('success', 'Troza actualizada exitosamente.');
    }

    public function updateEstimacion(Request $request, $id_estimacion)
    {
        $estimacion = Estimacion::findOrFail($id_estimacion);
        
        $validatedData = $request->validate([
            'id_tipo_e' => 'required|exists:tipo_estimaciones,id_tipo_e',
            'id_formula' => 'required|exists:formulas,id_formula',
            'id_troza' => 'required|exists:trozas,id_troza',
            'calculo' => 'required|numeric|min:0',
        ]);

        $formula = Formula::findOrFail($validatedData['id_formula']);
        $troza = Troza::findOrFail($validatedData['id_troza']);

        if ($formula->modo_ejecucion === 'app') {
            try {
                $outputs = app(\App\Services\FormulaEngineService::class)->calculateForModel($formula, $troza);
                $validatedData = array_merge($validatedData, $outputs);
            } catch (\InvalidArgumentException $exception) {
                return back()->withInput()->with('error', $exception->getMessage());
            }
        }

        $estimacion->update($validatedData);

        $id_parcela = $estimacion->troza->id_parcela;

        return redirect()->route('parcelas.show', $id_parcela)
            ->with('success', 'Estimación actualizada exitosamente.');
    }

    public function store(Request $request)
    {
        $validatedData = $request->validate([
            'nom_parcela'  => 'required|string|max:255|unique:parcelas,nom_parcela',
            'ubicacion'    => 'required|string|max:255',
            'id_productor' => 'required|exists:productores,id_productor',
            'extension'    => 'required|string|max:50',
            'direccion'    => 'required|string|max:255',
            'CP'           => 'required|integer|min:10000|max:99999',
        ]);

        Parcela::create($validatedData);

        return redirect()->route('parcelas.index')->with('register', 'Parcela agregada exitosamente.');
    }

    public function update(Request $request, int $id_parcela)
    {
        $parcela = Parcela::findOrFail($id_parcela);

        $validatedData = $request->validate([
            'nom_parcela'  => 'required|string|max:255|unique:parcelas,nom_parcela,' . $parcela->id_parcela . ',id_parcela',
            'ubicacion'    => 'required|string|max:255',
            'id_productor' => 'required|exists:productores,id_productor',
            'extension'    => 'required|string|max:50',
            'direccion'    => 'required|string|max:255',
            'CP'           => 'required|integer|min:10000|max:99999',
        ]);

        $parcela->update($validatedData);

        return redirect()->route('parcelas.index')->with('modify', 'Parcela actualizada exitosamente.');
    }

    public function destroy(int $id_parcela)
    {
        $parcela = Parcela::findOrFail($id_parcela);
        $parcela->delete();

        return redirect()->route('parcelas.index')->with('destroy', 'Parcela eliminada exitosamente.');
    }
}