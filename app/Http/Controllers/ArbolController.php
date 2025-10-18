<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\Arbol;
use App\Models\Especie;
use App\Models\Parcela;
use Illuminate\Support\Facades\Auth;

class ArbolController extends Controller
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
     * Listar árboles con paginación y búsqueda
     */
    public function index(Request $request)
    {
        $query = Arbol::with(['especie', 'parcela']);
        
        // Búsqueda
        if ($request->has('search')) {
            $search = $request->search;
            $query->where(function($q) use ($search) {
                $q->where('altura_total', 'like', "%$search%")
                  ->orWhere('diametro_pecho', 'like', "%$search%")
                  ->orWhereHas('especie', function($q) use ($search) {
                      $q->where('nom_cientifico', 'like', "%$search%");
                  })
                  ->orWhereHas('parcela', function($q) use ($search) {
                      $q->where('nom_parcela', 'like', "%$search%");
                  });
            });
        }
        
        // Ordenación
        $sortField = $request->get('sort', 'id_arbol');
        $sortDirection = $request->get('direction', 'asc');
        $query->orderBy($sortField, $sortDirection);
        
        $arboles = $query->paginate(10);
        $especies = Especie::all();
        $parcelas = Parcela::all();
        
        return view('arboles.index', compact('arboles', 'especies', 'parcelas'));
    }

    /**
     * Guardar un nuevo árbol
     */
    public function store(Request $request)
    {
        $validatedData = $request->validate([
            'altura_total' => 'required|numeric|min:0.1|max:150', // Altura en metros (0.1m a 150m)
            'diametro_pecho' => 'required|numeric|min:0.1|max:500', // DAP en cm (0.1cm a 500cm)
            'id_especie' => 'required|exists:especies,id_especie',
            'id_parcela' => 'required|exists:parcelas,id_parcela',
        ]);

        Arbol::create($validatedData);

        return redirect()->route('arboles.index')
            ->with('register', 'Árbol registrado exitosamente.');
    }

    /**
     * Mostrar detalles de un árbol
     */
    public function show($id)
    {
        $arbol = Arbol::with(['especie', 'parcela'])->findOrFail($id);
        return response()->json($arbol);
    }

    /**
     * Actualizar árbol
     */
    public function update(Request $request, $id_arbol)
    {
        $arbol = Arbol::findOrFail($id_arbol);

        $validatedData = $request->validate([
            'altura_total' => 'required|numeric|min:0.1|max:150',
            'diametro_pecho' => 'required|numeric|min:0.1|max:500',
            'id_especie' => 'required|exists:especies,id_especie',
            'id_parcela' => 'required|exists:parcelas,id_parcela',
        ]);

        $arbol->update($validatedData);

        return redirect()->route('arboles.index')
            ->with('modify', 'Árbol actualizado exitosamente.');
    }

    /**
     * Eliminar árbol
     */
    public function destroy($id_arbol)
    {
        $arbol = Arbol::findOrFail($id_arbol);
        $arbol->delete();

        return redirect()->route('arboles.index')
            ->with('destroy', 'Árbol eliminado exitosamente.');
    }
}