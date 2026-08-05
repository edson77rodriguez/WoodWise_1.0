<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Log;
use Symfony\Component\HttpFoundation\Response;

class VerifyHmacSignature
{
    // Ventana de tolerancia para evitar "replay attacks" (peticiones capturadas y reenviadas)
    private const TOLERANCIA_SEGUNDOS = 60;

    public function handle(Request $request, Closure $next): Response
    {
        $secret = (string) env('N8N_HMAC_SECRET', '');

        // Si no configuraste el secreto todavía, no bloqueamos (para no romper en despliegue).
        // Quita este "if" cuando ya hayas configurado n8n y quieras exigirlo siempre.
        if ($secret === '') {
            Log::warning('VerifyHmacSignature: N8N_HMAC_SECRET no configurado, se omite validación.');
            return $next($request);
        }

        $timestamp = $request->header('X-Timestamp', '');
        $firmaRecibida = (string) $request->header('X-Signature', '');

        if ($timestamp === '' || $firmaRecibida === '') {
            Log::warning('VerifyHmacSignature: faltan headers de firma.', [
                'ip' => $request->ip(),
            ]);

            return response()->json([
                'autorizado' => false,
                'mensaje' => 'Firma requerida.',
            ], 401);
        }

        // Anti-replay: si el timestamp es muy viejo (o del futuro), se rechaza.
        if (!ctype_digit($timestamp) || abs(time() - (int) $timestamp) > self::TOLERANCIA_SEGUNDOS) {
            Log::warning('VerifyHmacSignature: timestamp fuera de ventana.', [
                'ip' => $request->ip(),
                'timestamp_recibido' => $timestamp,
                'timestamp_servidor' => time(),
            ]);

            return response()->json([
                'autorizado' => false,
                'mensaje' => 'Petición expirada.',
            ], 401);
        }

        // El "raw body" tiene que ser EXACTAMENTE lo que n8n usó para firmar.
        $rawBody = $request->getContent();
        $firmaEsperada = hash_hmac('sha256', $timestamp . $rawBody, $secret);

        if (!hash_equals($firmaEsperada, $firmaRecibida)) {
            Log::warning('VerifyHmacSignature: firma inválida.', [
                'ip' => $request->ip(),
            ]);

            return response()->json([
                'autorizado' => false,
                'mensaje' => 'Firma inválida.',
            ], 401);
        }

        return $next($request);
    }
}