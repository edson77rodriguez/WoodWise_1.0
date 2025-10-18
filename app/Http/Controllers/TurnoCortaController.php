<?php

namespace App\Http\Controllers;

use App\Models\Turno_Corta;
use App\Models\Parcela;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Str;
use Carbon\Carbon;

class TurnoCortaController extends Controller
{
    public function __construct()
    {
        $this->middleware('auth');
        $this->middleware(function ($request, $next) {
            if (Auth::user()->persona->rol->nom_rol !== 'Administrador' && 
                Auth::user()->persona->rol->nom_rol !== 'Tecnico') {
                return response()->view('denegado', [], 403);
            }
            return $next($request);
        });
    }

    public function index()
    {
        $turnos = Turno_Corta::with(['parcela.productor.persona'])
            ->orderBy('fecha_corta', 'desc')
            ->paginate(10);
            
        $parcelas = Parcela::with('productor.persona')->get();
        
        return view('turno_cortas.index', compact('turnos', 'parcelas'));
    }

    public function store(Request $request)
    {
        $validatedData = $request->validate([
            'id_parcela' => 'required|exists:parcelas,id_parcela',
            'fecha_corta' => 'nullable|date',
            'fecha_fin' => 'nullable|date|after_or_equal:fecha_corta'
        ]);

        $validatedData['codigo_corta'] = Str::uuid()->toString();
        $validatedData['fecha_corta'] = $validatedData['fecha_corta'] ?? Carbon::now();

        Turno_Corta::create($validatedData);

        return redirect()->route('turno_cortas.index')
            ->with('success', 'Turno de corta creado exitosamente.');
    }

    public function update(Request $request, $id_turno)
    {
        $turno = Turno_Corta::findOrFail($id_turno);

        $validatedData = $request->validate([
            'id_parcela' => 'required|exists:parcelas,id_parcela',
            'fecha_corta' => 'nullable|date',
            'fecha_fin' => 'nullable|date|after_or_equal:fecha_corta'
        ]);

        $turno->update($validatedData);

        return redirect()->route('turno_cortas.index')
            ->with('success', 'Turno de corta actualizado exitosamente.');
    }

    public function destroy($id_turno)
    {
        try {
            $turno = Turno_Corta::findOrFail($id_turno);
            
            // Verificar si hay datos relacionados antes de eliminar
            if ($turno->hasRelatedData()) {
                return redirect()->back()
                    ->with('error', 'No se puede eliminar el turno porque tiene datos relacionados');
            }
            
            $turno->delete();
            
            return redirect()->route('turno_cortas.index')
                ->with('success', 'Turno de corta eliminado exitosamente.');
                
        } catch (\Exception $e) {
            return redirect()->route('turno_cortas.index')
                ->with('error', 'Error al eliminar el turno: ' . $e->getMessage());
        }
    }
}