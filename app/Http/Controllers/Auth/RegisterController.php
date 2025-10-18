<?php

namespace App\Http\Controllers\Auth;

use App\Http\Controllers\Controller;
use App\Models\{User, Persona, Tecnico, Productor, Rol};
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Validator;
use Illuminate\Support\Str;
use Illuminate\Http\Request;
use Illuminate\Foundation\Auth\RegistersUsers;

class RegisterController extends Controller
{
    use RegistersUsers;

    public function __construct()
    {
        $this->middleware('guest');
    }

    // Redirige después del registro, según el rol del usuario
    protected function redirectTo()
    {
        if (auth()->check() && auth()->user()->persona) {
            $rol = auth()->user()->persona->rol->nom_rol ?? '';

            switch (strtolower(trim($rol))) {
                case 'tecnico':
                    return route('tecnico.dashboard');
                case 'productor':
                    return route('productor.dashboard');
                default:
                    return '/dashboard1';
            }
        }
        return '/dashboard1';
    }

    // Muestra el formulario de registro con los roles disponibles
    public function showRegistrationForm()
    {
        $roles = Rol::where('nom_rol', '!=', 'Administrador')->get();
        return view('auth.register', compact('roles'));
    }

    // Valida los datos del registro
    protected function validator(array $data)
    {
        return Validator::make($data, [
            'nom' => ['required', 'string', 'max:255'],
            'ap' => ['required', 'string', 'max:255'],
            'am' => ['required', 'string', 'max:255'],
            'telefono' => ['required', 'string', 'max:20'],
            'email' => ['required', 'string', 'email', 'max:255', 'unique:users,email', 'unique:personas,correo'],
            'password' => ['required', 'string', 'min:8', 'confirmed'],
            'cedula' => ['nullable', 'string', 'min:5'],
            'id_rol' => ['required', 'exists:roles,id_rol'],
        ]);
    }

    // Crea el usuario y las entidades asociadas (Persona, Tecnico, Productor)
    protected function create(array $data)
    {
        // Obtén el rol desde la base de datos
        $rol = Rol::findOrFail($data['id_rol']);
        $rolNombre = strtolower(trim($rol->nom_rol));

        // Crear la persona asociada al usuario
        $persona = Persona::create([
            'nom' => $data['nom'],
            'ap' => $data['ap'],
            'am' => $data['am'],
            'telefono' => $data['telefono'],
            'correo' => $data['email'],
            'contrasena' => Hash::make($data['password']),
            'id_rol' => $rol->id_rol,
            'cedula' => $data['cedula'] ?? null,
        ]);

        // Registrar según el rol del usuario
        switch ($rolNombre) {
            case 'tecnico':
                Tecnico::create([
                    'id_persona' => $persona->id_persona,
                    'cedula_p' => $data['cedula'] ?? null,
                ]);
                break;

            case 'productor':
                Productor::create([
                    'id_persona' => $persona->id_persona,
                ]);
                break;

            default:
                // Opcional: log o acción en caso de rol desconocido
                // throw new \Exception("Rol no reconocido: {$rol->nom_rol}");
                break;
        }

        // Crear el usuario
        return User::create([
            'name' => "{$data['nom']} {$data['ap']} {$data['am']}",
            'email' => $data['email'],
            'password' => Hash::make($data['password']),
            'id_persona' => $persona->id_persona,
        ]);
    }

    // Genera un código único para los técnicos (si se necesita)
    protected function generateTecnicoCode()
    {
        do {
            $code = strtoupper(Str::random(8)); // Código aleatorio de 8 caracteres
        } while (Tecnico::where('clave_tecnico', $code)->exists());

        return $code;
    }
}
