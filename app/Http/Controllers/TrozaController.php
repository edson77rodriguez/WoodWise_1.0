<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\Troza;
use App\Models\Especie;
use App\Models\Parcela;
use Illuminate\Support\Facades\Auth;

class TrozaController extends Controller
{
    public function __construct()
    {
        $this->middleware('auth');
        $this->middleware(function ($request, $next) {
            if (Auth::user()->persona->rol->nom_rol !== 'Administrador' ) {
                return response()->view('denegado', [], 403);
            }
            return $next($request);
        });
    }

    /**
     * Listar trozas con paginación y búsqueda
     */
    public function index(Request $request)
    {
        $query = Troza::with(['especie', 'parcela']);
        
        // Búsqueda
        if ($request->has('search')) {
            $search = $request->search;
            $query->where(function($q) use ($search) {
                $q->where('longitud', 'like', "%$search%")
                  ->orWhere('diametro', 'like', "%$search%")
                  ->orWhereHas('especie', function($q) use ($search) {
                      $q->where('nom_cientifico', 'like', "%$search%");
                  })
                  ->orWhereHas('parcela', function($q) use ($search) {
                      $q->where('nom_parcela', 'like', "%$search%");
                  });
            });
        }
        
        // Ordenación
        $sortField = $request->get('sort', 'id_troza');
        $sortDirection = $request->get('direction', 'asc');
        $query->orderBy($sortField, $sortDirection);
        
        $trozas = $query->paginate(10);
        $especies = Especie::all();
        $parcelas = Parcela::all();
        
        return view('trozas.index', compact('trozas', 'especies', 'parcelas'));
    }

    /**
     * Guardar una nueva troza
     */
    public function store(Request $request)
    {
        $validatedData = $request->validate([
            'longitud' => 'required|numeric|min:0.01|max:50',
            'diametro' => 'required|numeric|min:0.01|max:5',
            'diametro_otro_extremo' => 'nullable|numeric|min:0.01|max:5',
            'diametro_medio' => 'nullable|numeric|min:0.01|max:5',
            'densidad' => 'required|numeric|min:0.1|max:1000',
            'id_especie' => 'required|exists:especies,id_especie',
            'id_parcela' => 'required|exists:parcelas,id_parcela',
        ]);

        // Calcular volumen automáticamente si no se proporciona
        $validatedData['volumen'] = $this->calcularVolumen($validatedData);

        Troza::create($validatedData);

        return redirect()->route('trozas.index')
            ->with('register', 'Troza agregada exitosamente.');
    }

    /**
     * Mostrar detalles de una troza
     */
    public function show($id)
    {
        $troza = Troza::with(['especie', 'parcela'])->findOrFail($id);
        return response()->json($troza);
    }

    /**
     * Actualizar troza
     */
    public function update(Request $request, $id_troza)
    {
        $troza = Troza::findOrFail($id_troza);

        $validatedData = $request->validate([
            'longitud' => 'required|numeric|min:0.01|max:50',
            'diametro' => 'required|numeric|min:0.01|max:5',
            'diametro_otro_extremo' => 'nullable|numeric|min:0.01|max:5',
            'diametro_medio' => 'nullable|numeric|min:0.01|max:5',
            'densidad' => 'required|numeric|min:0.1|max:1000',
            'id_especie' => 'required|exists:especies,id_especie',
            'id_parcela' => 'required|exists:parcelas,id_parcela',
        ]);

        // Recalcular volumen
        $validatedData['volumen'] = $this->calcularVolumen($validatedData);

        $troza->update($validatedData);

        return redirect()->route('trozas.index')
            ->with('modify', 'Troza actualizada exitosamente.');
    }

    public function update1(Request $request, $id_troza)
    {
        $troza = Troza::findOrFail($id_troza);

        $validatedData = $request->validate([
            'longitud' => 'required|numeric|min:0.01|max:50',
            'diametro' => 'required|numeric|min:0.01|max:5',
            'diametro_otro_extremo' => 'nullable|numeric|min:0.01|max:5',
            'diametro_medio' => 'nullable|numeric|min:0.01|max:5',
            'densidad' => 'required|numeric|min:0.1|max:1000',
            'id_especie' => 'required|exists:especies,id_especie',
            'id_parcela' => 'required|exists:parcelas,id_parcela',
        ]);

        // Recalcular volumen
        $validatedData['volumen'] = $this->calcularVolumen($validatedData);

        $troza->update($validatedData);

        return redirect("/parcelas/{$validatedData['id_parcela']}/detalle")
    ->with('modify', 'Troza actualizada exitosamente.');

    }
    /**
     * Eliminar troza
     */
    public function destroy($id_troza)
    {
        $troza = Troza::findOrFail($id_troza);
        $troza->delete();

        return redirect()->route('trozas.index')
            ->with('destroy', 'Troza eliminada exitosamente.');
    }

    /**
     * Calcular volumen de la troza
     */
    private function calcularVolumen($data)
    {
        // Si se proporciona diámetro medio, usarlo
        $diametro = $data['diametro_medio'] ?? $data['diametro'];
        
        // Fórmula del volumen de un cilindro: π * r² * h
        $radio = $diametro / 2;
        return pi() * pow($radio, 2) * $data['longitud'] * $data['densidad'];
    }
}