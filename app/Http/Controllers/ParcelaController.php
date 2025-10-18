<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\Parcela;
use App\Models\Productor;
use App\Models\Especie;
use App\Models\Troza;
use App\Models\Formula;
use App\Models\Tipo_Estimacion;
use App\Models\Estimacion;
use Illuminate\Support\Facades\Auth;

class ParcelaController extends Controller
{

    public function __construct()
    {
        $this->middleware('auth');
        $this->middleware(function ($request, $next) {
            if (Auth::user()->persona->rol->nom_rol !== 'Administrador' && Auth::user()->persona->rol->nom_rol !== 'Tecnico') {
                // Redirige a la vista 'denegado' con un código HTTP 403 (Forbidden)
                return response()->view('denegado', [], 403);

                // Opcional: Si prefieres usar abort (mostrará la vista 403 personalizada)
                // abort(403, 'No tienes permisos de administrador');
            }
            return $next($request);
        });
    }

    public function trozaedit($id_parcela)
{
    // Obtén solo las trozas de la parcela específica
    $trozas = Troza::where('id_parcela', $id_parcela)->get();
    $especies = Especie::all();
    $parcela = Parcela::findOrFail($id_parcela);

    return view('partials.modals.edit_troza', compact('parcela', 'especies', 'trozas'));
}


public function show($id)
{
    $parcela = Parcela::with([
        'trozas.especie', 
        'estimaciones.tipoEstimacion', 
        'estimaciones.formula',
        'estimaciones.troza',
        'turnosCorta'
    ])->findOrFail($id);

    return view('parcelas.show', [
        'parcela' => $parcela,
        'especies' => Especie::all(),
        'tiposEstimacion' => Tipo_Estimacion::all(),
        'formulas' => Formula::all(),
        'productores' => Productor::all() // 👈 aquí lo agregas
    ]);
}

    public function index()
    {
        $parcelas = Parcela::all();
        $productores = Productor::all(); // Obtener todos los productores para el select
        $especies=Especie::all();
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

    // Función para actualizar una estimación
    public function updateEstimacion(Request $request, $id_estimacion)
    {
        $estimacion = Estimacion::findOrFail($id_estimacion);
        
        $validatedData = $request->validate([
            'id_tipo_e' => 'required|exists:tipo_estimaciones,id_tipo_e',
            'id_formula' => 'required|exists:formulas,id_formula',
            'id_troza' => 'required|exists:trozas,id_troza',
            'calculo' => 'required|numeric|min:0',
        ]);

        $estimacion->update($validatedData);

        // Obtenemos el id_parcela a través de la troza relacionada
        $id_parcela = $estimacion->troza->id_parcela;

        return redirect()->route('parcelas.show', $id_parcela)
            ->with('success', 'Estimación actualizada exitosamente.');
    }
    /**
     * Guardar una nueva parcela.
     */
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

    /**
     * Actualizar parcela.
     */
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

    /**
     * Eliminar parcela.
     */
    public function destroy(int $id_parcela)
    {
        $parcela = Parcela::findOrFail($id_parcela);
        $parcela->delete();

        return redirect()->route('parcelas.index')->with('destroy', 'Parcela eliminada exitosamente.');
    }
}
