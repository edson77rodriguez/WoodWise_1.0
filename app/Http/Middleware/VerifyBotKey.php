<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;

class VerifyBotKey
{
    public function handle(Request $request, Closure $next): Response
    {
        // Seguridad opcional:
        // Si BOT_API_KEY está definido, se requiere header X-Bot-Key.
        $expected = env('BOT_API_KEY');

        if (is_string($expected) && $expected !== '') {
            $provided = (string) $request->header('X-Bot-Key', '');

            if (!hash_equals($expected, $provided)) {
                return response()->json([
                    'autorizado' => false,
                    'mensaje' => 'No autorizado.',
                ], 401);
            }
        }

        return $next($request);
    }
}
