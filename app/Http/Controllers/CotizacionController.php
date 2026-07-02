<?php

namespace App\Http\Controllers;

use App\Models\PrecioMercado;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Log;

class CotizacionController extends Controller
{
    public function sincronizarPreciosIA(Request $request)
    {
        $request->validate([
            'precios' => 'required|array',
            'precios.*.especie' => 'required|string',
            'precios.*.estado' => 'required|string',
            'precios.*.precio_por_m3' => 'required|numeric',
            'precios.*.fuente' => 'nullable|string',
        ]);

        $preciosNuevos = $request->input('precios');
        $actualizados = 0;

        foreach ($preciosNuevos as $precio) {
            $especie = $this->normalizarEspecieMercado((string) $precio['especie']);
            $estado = $this->normalizarEstadoMercado((string) $precio['estado']);

            PrecioMercado::updateOrCreate(
                [
                    'especie' => $especie,
                    'estado' => $estado,
                ],
                [
                    'precio_por_m3' => $precio['precio_por_m3'],
                    'moneda' => 'MXN',
                    'fuente' => $precio['fuente'] ?? 'Gemini AI Web Search',
                    'ultima_actualizacion' => now(),
                ]
            );

            $actualizados++;
        }

        Log::info("Precios de mercado actualizados vía IA: {$actualizados} registros (especie/estado).");

        return response()->json([
            'ok' => true,
            'mensaje' => "Se actualizaron {$actualizados} precios regionales correctamente.",
            'datos' => $preciosNuevos,
        ]);
    }

    private function normalizarEspecieMercado(string $especie): string
    {
        $texto = strtolower(trim($especie));

        if (str_contains($texto, 'pino') || str_contains($texto, 'pinus')) {
            return 'pino';
        }

        if (str_contains($texto, 'encino') || str_contains($texto, 'quercus')) {
            return 'encino';
        }

        if (str_contains($texto, 'oyamel') || str_contains($texto, 'abies')) {
            return 'oyamel';
        }

        return $texto;
    }

    private function normalizarEstadoMercado(string $estado): string
    {
        $texto = preg_replace('/\s+/', ' ', trim($estado));
        $texto = strtolower($texto);

        if ($texto === '' || str_contains($texto, 'estado de mexico') || str_contains($texto, 'edomex') || str_contains($texto, 'mexico')) {
            return 'Estado de Mexico';
        }

        return ucwords($texto);
    }
}