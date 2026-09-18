<?php

namespace App\Http\Controllers\API;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Hash;
use Illuminate\Validation\ValidationException;

class MobileAuthController extends Controller
{
    public function login(Request $request)
    {
        $request->validate([
            'email' => ['required', 'email'],
            'password' => ['required', 'string'],
        ]);

        $user = \App\Models\User::with([
            'persona.rol',
            'persona.tecnico',
            'persona.productor',
        ])->where('email', $request->email)->first();

        if (!$user || !Hash::check($request->password, $user->password)) {
            throw ValidationException::withMessages([
                'email' => ['Las credenciales proporcionadas no son correctas.'],
            ]);
        }

        $user->tokens()->delete();

        $token = $user->createToken('sigmad-mobile')->plainTextToken;

        return response()->json([
            'message' => 'Inicio de sesión exitoso.',
            'token' => $token,
            'token_type' => 'Bearer',
            'user' => [
                'id' => $user->id,
                'name' => $user->name,
                'email' => $user->email,
                'id_persona' => $user->id_persona,
            ],
            'persona' => $user->persona ? [
                'id_persona' => $user->persona->id_persona,
                'nom' => $user->persona->nom,
                'ap' => $user->persona->ap,
                'am' => $user->persona->am,
                'telefono' => $user->persona->telefono,
                'correo' => $user->persona->correo,
                'id_rol' => $user->persona->id_rol,
                'is_producer' => (bool) $user->persona->is_producer,
            ] : null,
            'rol' => $user->persona?->rol ? [
                'id_rol' => $user->persona->rol->id_rol,
                'nom_rol' => $user->persona->rol->nom_rol,
            ] : null,
            'tecnico' => $user->persona?->tecnico ? [
                'id_tecnico' => $user->persona->tecnico->id_tecnico,
                'cedula_p' => $user->persona->tecnico->cedula_p,
            ] : null,
            'productor' => $user->persona?->productor ? [
                'id_productor' => $user->persona->productor->id_productor,
            ] : null,
        ]);
    }

    public function me(Request $request)
    {
        $user = $request->user()->load([
            'persona.rol',
            'persona.tecnico',
            'persona.productor',
        ]);

        return response()->json([
            'user' => [
                'id' => $user->id,
                'name' => $user->name,
                'email' => $user->email,
                'id_persona' => $user->id_persona,
            ],
            'persona' => $user->persona ? [
                'id_persona' => $user->persona->id_persona,
                'nom' => $user->persona->nom,
                'ap' => $user->persona->ap,
                'am' => $user->persona->am,
                'telefono' => $user->persona->telefono,
                'correo' => $user->persona->correo,
                'id_rol' => $user->persona->id_rol,
                'is_producer' => (bool) $user->persona->is_producer,
            ] : null,
            'rol' => $user->persona?->rol ? [
                'id_rol' => $user->persona->rol->id_rol,
                'nom_rol' => $user->persona->rol->nom_rol,
            ] : null,
            'tecnico' => $user->persona?->tecnico ? [
                'id_tecnico' => $user->persona->tecnico->id_tecnico,
                'cedula_p' => $user->persona->tecnico->cedula_p,
            ] : null,
            'productor' => $user->persona?->productor ? [
                'id_productor' => $user->persona->productor->id_productor,
            ] : null,
        ]);
    }

    public function logout(Request $request)
    {
        $request->user()->currentAccessToken()->delete();

        return response()->json([
            'message' => 'Sesión cerrada correctamente.',
        ]);
    }
}