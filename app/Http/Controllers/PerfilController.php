<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Hash;

class PerfilController extends Controller
{
    public function index()
    {
        return view('perfil.index');
    }

    public function update(Request $request)
    {
        $user = Auth::user();
        $persona = $user->persona;

        $request->validate([
            'nom' => 'required|string|max:255',
            'ap' => 'required|string|max:255',
            'correo' => 'required|email|unique:personas,correo,' . $persona->id_persona . ',id_persona',
            'telefono' => 'nullable|string|max:15',
        ]);

        $persona->update([
            'nom' => $request->nom,
            'ap' => $request->ap,
            'correo' => $request->correo,
            'telefono' => $request->telefono,
        ]);

        return redirect()->route('perfil.index')->with('success', 'Perfil actualizado correctamente.');
    }

    public function updatePassword(Request $request)
    {
        $user = Auth::user();

        $request->validate([
            'current_password' => 'required',
            'new_password' => 'required|min:8|confirmed',
        ]);

        if (!Hash::check($request->current_password, $user->password)) {
            return back()->with('error', 'La contraseña actual no es correcta.');
        }

        $user->update([
            'password' => Hash::make($request->new_password),
        ]);

        return redirect()->route('perfil.index')->with('success', 'Contraseña actualizada correctamente.');
    }
}
