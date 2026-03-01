<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Symfony\Component\HttpFoundation\Response;

class CheckRole
{
    /**
     * Handle an incoming request.
     * Verifica que el usuario tenga uno de los roles permitidos.
     *
     * @param  \Illuminate\Http\Request  $request
     * @param  \Closure  $next
     * @param  string  ...$roles  Roles permitidos (separados por coma)
     * @return \Symfony\Component\HttpFoundation\Response
     */
    public function handle(Request $request, Closure $next, string ...$roles): Response
    {
        // Verificar autenticación
        if (!Auth::check()) {
            return redirect()->route('login');
        }

        $user = Auth::user();

        // Verificar que el usuario tenga persona y rol
        if (!$user->persona || !$user->persona->rol) {
            Auth::logout();
            return redirect()->route('login')
                ->with('error', 'Tu cuenta no tiene un perfil configurado correctamente.');
        }

        $userRole = $user->persona->rol->nom_rol;

        // Si no se especifican roles, permitir cualquier rol autenticado
        if (empty($roles)) {
            return $next($request);
        }

        // Verificar si el usuario tiene uno de los roles permitidos
        if (in_array($userRole, $roles)) {
            return $next($request);
        }

        // Redirigir al dashboard correspondiente según su rol
        return $this->redirectToDashboard($userRole);
    }

    /**
     * Redirige al usuario a su dashboard correspondiente.
     */
    protected function redirectToDashboard(string $role): Response
    {
        $routes = [
            'Administrador' => 'dashboard1',
            'Tecnico' => 'tecnico.dashboard',
            'Productor' => 'productor.dashboard',
        ];

        $routeName = $routes[$role] ?? 'welcome';

        return redirect()->route($routeName)
            ->with('warning', 'No tienes permisos para acceder a esa sección.');
    }
}
