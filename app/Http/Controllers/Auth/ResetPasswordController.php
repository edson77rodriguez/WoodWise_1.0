<?php

namespace App\Http\Controllers\Auth;

use App\Http\Controllers\Controller;
use Illuminate\Foundation\Auth\ResetsPasswords;
use Illuminate\Support\Facades\Auth;

class ResetPasswordController extends Controller
{
    use ResetsPasswords;

    /**
     * Where to redirect users after resetting their password.
     *
     * @var string
     */
    protected $redirectTo = '/dashboard1';

    /**
     * Get the post password reset redirect path.
     *
     * @return string
     */
    protected function redirectTo()
    {
        if (Auth::check() && Auth::user()->persona) {
            $rol = Auth::user()->persona->rol->nom_rol;
            
            switch ($rol) {
                case 'Tecnico':
                    return route('tecnico.dashboard');
                case 'Productor':
                    return '/P/Dashboard';
                default:
                    return $this->redirectTo;
            }
        }
        return $this->redirectTo;
    }
}