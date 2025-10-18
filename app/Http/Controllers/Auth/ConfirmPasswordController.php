<?php
namespace App\Http\Controllers\Auth;

use App\Http\Controllers\Controller;
use Illuminate\Foundation\Auth\ConfirmsPasswords;
use Illuminate\Support\Facades\Auth;

class ConfirmPasswordController extends Controller
{
    use ConfirmsPasswords;

    /**
     * Where to redirect users when the intended url fails.
     *
     * @var string
     */
    protected $redirectTo = '/dashboard1';

    /**
     * Get the post password confirmation redirect path.
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

    public function __construct()
    {
        $this->middleware('auth');
    }
}
