<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;

class CheckApiRole
{
    public function handle(
        Request $request,
        Closure $next,
        string ...$roles
    ): Response {
        $user = $request->user();

        // auth:sanctum debería impedir que llegue aquí
        // un usuario no autenticado, pero dejamos
        // una validación adicional.
        if (!$user) {
            return response()->json([
                'message' => 'No autenticado.',
            ], 401);
        }

        // Comprobar identidad completa
        if (!$user->persona || !$user->persona->rol) {
            return response()->json([
                'message' => 'La cuenta no tiene un perfil o rol configurado correctamente.',
            ], 403);
        }

        $userRole = $user->persona->rol->nom_rol;

        // Si no se indican roles, cualquier usuario
        // autenticado puede continuar.
        if (empty($roles)) {
            return $next($request);
        }

        // Comprobar que el rol esté permitido.
        if (!in_array($userRole, $roles, true)) {
            return response()->json([
                'message' => 'No tienes permisos para realizar esta operación.',
            ], 403);
        }

        return $next($request);
    }
}