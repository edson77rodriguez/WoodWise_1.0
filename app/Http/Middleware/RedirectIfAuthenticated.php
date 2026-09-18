<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class RedirectIfAuthenticated
{
    /**
     * Handle an incoming request.
     * If the user is authenticated, redirect them to the dashboard according to their role.
     * Otherwise proceed to the next middleware.
     */
    public function handle(Request $request, Closure $next, ...$guards)
    {
        foreach ($guards as $guard) {
            if (Auth::guard($guard)->check()) {
                return $this->redirectBasedOnRole(Auth::guard($guard)->user());
            }
        }

        if (empty($guards) && Auth::check()) {
            return $this->redirectBasedOnRole(Auth::user());
        }

        return $next($request);
    }

    protected function redirectBasedOnRole($user)
    {
        if (!$user) {
            return redirect()->route('welcome');
        }

        // If user has persona and role, map to route
        $role = optional($user->persona->rol)->nom_rol;

        $map = [
            'Administrador' => 'dashboard1',
            'Tecnico' => 'tecnico.dashboard',
            'Productor' => 'productor.dashboard',
        ];

        $route = $map[$role] ?? 'dashboard1';

        return redirect()->route($route);
    }
}
