<?php

namespace App\Http\Controllers;

use App\Models\Productor;
use App\Models\Persona;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class ProductorController extends Controller
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
    public function index()
    {
        $productores = Productor::with('persona')->get();
                $personas = Persona::all();  // Obtener todas las personas para asignarlas al productor
        return view('productores.index', compact('productores', 'personas')); // Vista con productores y personas
    }

    /**
     * Store a newly created resource in storage.
     */
    public function store(Request $request)
    {
        $request->validate([
            'id_persona' => 'required|exists:personas,id_persona', // Validación de persona
        ]);

        Productor::create([
            'id_persona' => $request->id_persona, // Guardar la relación con persona
        ]);

        return redirect()->route('productores.index')->with('success', 'Productor creado con éxito.');
    }

    /**
     * Update the specified resource in storage.
     */
    public function update(Request $request, $id)
    {
        $request->validate([
            'id_persona' => 'required|exists:personas,id_persona',
        ]);

        $productor = Productor::findOrFail($id);
        $productor->update([
            'id_persona' => $request->id_persona,
        ]);

        return redirect()->route('productores.index')->with('success', 'Productor actualizado con éxito.');
    }

    /**
     * Remove the specified resource from storage.
     */
    public function destroy($id)
    {
        $productor = Productor::findOrFail($id);
        $productor->delete();

        return redirect()->route('productores.index')->with('success', 'Productor eliminado con éxito.');
    }
}
