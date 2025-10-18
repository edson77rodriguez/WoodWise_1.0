<?php

namespace App\Http\Controllers\Auth;

use App\Http\Controllers\Controller;
use Illuminate\Foundation\Auth\VerifiesEmails;
use Illuminate\Support\Facades\Auth;

class VerificationController extends Controller
{
    use VerifiesEmails;

    /**
     * Where to redirect users after verification.
     *
     * @var string
     */
    protected $redirectTo = '/dashboard1';

    /**
     * Get the post verification redirect path.
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
        $this->middleware('signed')->only('verify');
        $this->middleware('throttle:6,1')->only('verify', 'resend');
    }
}