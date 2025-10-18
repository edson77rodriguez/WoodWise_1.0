<?php

namespace App\Http\Controllers;
use App\Models\Asigna_Parcela;
use App\Models\Tecnico;
use App\Models\Parcela;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class AsignaParcelaController extends Controller
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

    public function index()
    {
        $asignaciones = Asigna_Parcela::with(['tecnico.persona', 'parcela.productor.persona'])->paginate(10);
        $tecnicos = Tecnico::with('persona')->get();
        $parcelas = Parcela::with('productor.persona')->get();
        
        return view('asigna_parcelas.index', compact('asignaciones', 'tecnicos', 'parcelas'));
    }

    public function store(Request $request)
    {
        $request->validate([
            'id_tecnico' => 'required|exists:tecnicos,id_tecnico',
            'id_parcela' => 'required|exists:parcelas,id_parcela',
        ]);

        Asigna_Parcela::create($request->all());

        return redirect()->route('asigna_parcelas.index')->with('success', 'Asignación creada con éxito.');
    }

    public function update(Request $request, $id)
    {
        $request->validate([
            'id_tecnico' => 'required|exists:tecnicos,id_tecnico',
            'id_parcela' => 'required|exists:parcelas,id_parcela',
        ]);

        $asignacion = Asigna_Parcela::findOrFail($id);
        $asignacion->update($request->all());

        return redirect()->route('asigna_parcelas.index')->with('success', 'Asignación actualizada con éxito.');
    }

    public function destroy($id)
    {
        $asignacion = Asigna_Parcela::findOrFail($id);
        $asignacion->delete();

        return redirect()->route('asigna_parcelas.index')->with('success', 'Asignación eliminada con éxito.');
    }
}
