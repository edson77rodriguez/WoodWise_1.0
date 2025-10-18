<?php

namespace App\Http\Controllers;


// Controlador: TipoEstimacionController.php

use Illuminate\Http\Request;
use App\Models\Tipo_Estimacion;
use Illuminate\Support\Facades\Auth;

class TipoEstimacionController extends Controller
{

    public function __construct()
    {
        $this->middleware('auth');
        $this->middleware(function ($request, $next) {
            if (Auth::user()->persona->rol->nom_rol !== 'Administrador' && Auth::user()->persona->rol->nom_rol !== '') {
                // Redirige a la vista 'denegado' con un código HTTP 403 (Forbidden)
                return response()->view('denegado', [], 403);

                // Opcional: Si prefieres usar abort (mostrará la vista 403 personalizada)
                // abort(403, 'No tienes permisos de administrador');
            }
            return $next($request);
        });
    }

    public function index()
    {
        $tipo_estimaciones = Tipo_Estimacion::all();
        return view('tipo_estimaciones.index', compact('tipo_estimaciones'));
    }

    public function store(Request $request)
    {
        $request->validate(['desc_estimacion' => 'required|string|max:255']);
        Tipo_Estimacion::create($request->all());
        return redirect()->route('tipo_estimaciones.index')->with('success', 'Tipo de estimación creado correctamente.');
    }

    public function update(Request $request, $id)
    {
        $request->validate(['desc_estimacion' => 'required|string|max:255']);
        $tipo = Tipo_Estimacion::findOrFail($id);
        $tipo->update($request->all());
        return redirect()->route('tipo_estimaciones.index')->with('success', 'Tipo de estimación actualizado.');
    }

    public function destroy($id)
{
    Tipo_Estimacion::findOrFail($id)->delete();
    return redirect()->route('tipo_estimaciones.index')->with('success', 'Tipo de estimación eliminado correctamente.');
}

}


