<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\Persona;
use App\Models\Rol;
use App\Models\User;
use App\Models\Tecnico;
use App\Models\Productor;


use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;

class PersonaController extends Controller
{
    /**
     * Asegurar que solo el administrador puede acceder a este controlador
     */
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
     * Mostrar la lista de usuarios
     */
    public function index()
    {
        $personas = Persona::with('rol')->get(); // Traer personas con sus roles
        $roles = Rol::all(); // Obtener roles disponibles

        return view('usuarios.index', compact('personas', 'roles'));
    }

    /**
     * Guardar un nuevo usuario
     */
    public function store(Request $request)
{
    $validatedData = $request->validate([
        'nom' => 'required|string|max:100',
        'ap' => 'required|string|max:100',
        'am' => 'nullable|string|max:100',
        'telefono' => 'required|string|max:15',
        'email' => 'required|email|unique:personas,correo|unique:users,email',
        'password' => 'required|string|min:6|confirmed',
        'id_rol' => 'required|exists:roles,id_rol',
        'cedula' => 'nullable|string|max:20' // Solo para técnicos
    ]);

    // Obtener el rol
    $rol = Rol::findOrFail($validatedData['id_rol']);
    $rolNombre = strtolower(trim($rol->nom_rol));

    // Crear la persona
    $persona = Persona::create([
        'nom' => $validatedData['nom'],
        'ap' => $validatedData['ap'],
        'am' => $validatedData['am'],
        'telefono' => $validatedData['telefono'],
        'correo' => $validatedData['email'],
        'contrasena' => Hash::make($validatedData['password']),
        'id_rol' => $validatedData['id_rol'],
        'cedula' => $validatedData['cedula'] ?? null,
    ]);

    // Registrar según el rol
    switch ($rolNombre) {
        case 'Tecnico':
            Tecnico::create([
                'id_persona' => $persona->id_persona,
                'cedula_p' => $validatedData['cedula'] ?? null,
            ]);
            break;

        case 'Productor':
            Productor::create([
                'id_persona' => $persona->id_persona,
            ]);
            break;
    }

    // Crear el usuario de autenticación
    User::create([
        'name' => "{$validatedData['nom']} {$validatedData['ap']} {$validatedData['am']}",
        'email' => $validatedData['email'],
        'password' => Hash::make($validatedData['password']),
        'id_persona' => $persona->id_persona,
    ]);

    return redirect()->route('usuarios.index')->with('register', 'Usuario creado exitosamente.');
}

    /**
     * Actualizar un usuario
     */
    public function update(Request $request, string $id)
    {
        $persona = Persona::findOrFail($id);

        $validatedData = $request->validate([
            'nom'       => 'required|string|max:100',
            'ap'        => 'required|string|max:100',
            'am'        => 'nullable|string|max:100',
            'telefono'  => 'required|string|max:15',
            'correo'    => 'required|email|unique:personas,correo,' . $persona->id_persona . ',id_persona',
            'id_rol'    => 'required|exists:roles,id_rol',
        ]);

        if ($request->filled('contrasena')) {
            $validatedData['contrasena'] = Hash::make($request->contrasena);
        }

        $persona->update($validatedData);

        return redirect()->route('usuarios.index')->with('modify', 'Usuario actualizado exitosamente.');
    }

    /**
     * Eliminar un usuario
     */
   public function destroy(string $id)
{
    try {
        $persona = Persona::with(['user', 'tecnico', 'productor'])->findOrFail($id);

        // Prevenir eliminación del usuario autenticado
        if ($persona->id_persona === Auth::user()->persona->id_persona) {
            return redirect()->route('usuarios.index')->with('error', 'No puedes eliminar tu propia cuenta.');
        }

        // Usar transacción para asegurar integridad de datos
        DB::transaction(function () use ($persona) {
            // Eliminar usuario asociado si existe
            if ($persona->user) {
                $persona->user->delete();
            }

            // Eliminar registro técnico si existe
            if ($persona->tecnico) {
                $persona->tecnico->delete();
            }

            // Eliminar registro productor si existe
            if ($persona->productor) {
                $persona->productor->delete();
            }

            // Finalmente eliminar la persona
            $persona->delete();
        });

        return redirect()->route('usuarios.index')->with('destroy', 'Usuario eliminado exitosamente.');

    } catch (\Exception $e) {
        return redirect()->route('usuarios.index')
               ->with('error', 'No se pudo eliminar el usuario: ' . $e->getMessage());
    }
}
}
