<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\Tecnico;
use App\Models\Especie;

use App\Models\Persona;
use Illuminate\Support\Facades\Auth;

class TecnicoController extends Controller
{
 public function __construct()
    {
        $this->middleware('auth');
        $this->middleware(function ($request, $next) {
            if (Auth::user()->persona->rol->nom_rol !== 'Administrador') {
                // Redirige a la vista 'denegado' con un código HTTP 403 (Forbidden)
                return response()->view('denegado', [], 403);
                
                // Opcional: Si prefieres usar abort (mostrará la vista 403 personalizada)
                // abort(403, 'No tienes permisos de administrador');
            }
            return $next($request);
        });
    }

    /**
     * Muestra el dashboard del técnico
     */
   
    /**
     * Lista todos los técnicos
     */
    public function index()
    {
        $tecnicos = Tecnico::with('persona')->get();
        $personas = Persona::whereDoesntHave('tecnico')->get(); // Solo personas sin técnico asignado
        $especies=Especie::all();
        return view('tecnicos.index', compact('tecnicos', 'personas','especies'));
    }

    /**
     * Almacena un nuevo técnico
     */
    public function store(Request $request)
    {
        $validatedData = $request->validate([
            'id_persona' => 'required|exists:personas,id_persona|unique:tecnicos,id_persona',
            'cedula_p'   => 'nullable|string|max:50|unique:tecnicos,cedula_p',
        ]);


        Tecnico::create($validatedData);

        return redirect()->route('tecnicos.index')
               ->with('success', 'Técnico registrado exitosamente.');
    }

    /**
     * Actualiza un técnico existente
     */
    public function update(Request $request, $id_tecnico)
    {
        $tecnico = Tecnico::findOrFail($id_tecnico);

        $validatedData = $request->validate([
            'cedula_p' => 'required|string|max:50|unique:tecnicos,cedula_p,'.$tecnico->id_tecnico.',id_tecnico',
            'clave_tecnico' => 'nullable|string|max:50|unique:tecnicos,clave_tecnico,'.$tecnico->id_tecnico.',id_tecnico',
        ]);

        $tecnico->update($validatedData);

        return redirect()->route('tecnicos.index')
               ->with('success', 'Técnico actualizado exitosamente.');
    }

    /**
     * Elimina un técnico
     */
    public function destroy($id_tecnico)
    {
        $tecnico = Tecnico::findOrFail($id_tecnico);
        $tecnico->delete();

        return redirect()->route('tecnicos.index')
               ->with('success', 'Técnico eliminado exitosamente.');
    }

    /**
     * Genera una clave única para técnicos
     */
    protected function generarClaveUnica()
    {
        do {
            $clave = strtoupper(str()->random(8));
        } while (Tecnico::where('clave_tecnico', $clave)->exists());

        return $clave;
    }
}