<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class ResumenEstionacionesController extends Controller
{
    public function __construct()
    {
        $this->middleware('auth');
        $this->middleware(function ($request, $next) {
            if (Auth::user()->persona->rol->nom_rol !== 'Administrador' && Auth::user()->persona->rol->nom_rol !== 'Tecnico' ) {
                return redirect()->route('home')->with('error', 'Acceso denegado.');
            }
            return $next($request);
        });
    }
}
