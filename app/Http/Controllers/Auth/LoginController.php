<?php

namespace App\Http\Controllers\Auth;

use App\Http\Controllers\Controller;
use Illuminate\Foundation\Auth\AuthenticatesUsers;
use App\Models\Rol;

class LoginController extends Controller
{
    use AuthenticatesUsers;

    /**
     * Create a new controller instance.
     *
     * @return void
     */
    public function __construct()
    {
        $this->middleware('guest')->except('logout');
    }

    /**
     * Determine donde redirigir al usuario después del login.
     *
     * @return string
     */
    protected function redirectTo()
    {
        // Verificar si el usuario tiene persona asociada
        if (!auth()->user()->persona) {
            return '/dashboard1'; // Ruta por defecto si no tiene persona
        }

        // Obtener el nombre del rol
        $rol = auth()->user()->persona->rol->nom_rol;

        // Redirigir según el rol
        switch ($rol) {
            case 'Tecnico':
                return route('tecnico.dashboard');
            case 'Productor':
               return route('productor.dashboard');
            case 'Administrador':
                return '/dashboard1';
            default:
               return route('productor.dashboard');
        }
    }
}