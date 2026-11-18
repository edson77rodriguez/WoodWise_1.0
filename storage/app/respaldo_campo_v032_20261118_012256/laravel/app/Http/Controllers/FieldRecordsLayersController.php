<?php

namespace App\Http\Controllers;

use App\Models\FieldMission;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

/**
 * SIGMAD Campo V0.3.2 — catálogo de misiones para el mapa administrativo.
 * Solo consulta: no modifica mediciones, misiones ni resultados de IA.
 * La autorización de Administrador también se aplica en routes/campo_registros_capas.php.
 */
class FieldRecordsLayersController extends Controller
{
    public function missions(Request $request): JsonResponse
    {
        $filter = $request->validate([
            'parcela' => ['nullable', 'integer', 'exists:parcelas,id_parcela'],
        ]);

        $query = FieldMission::query()->where('status', 'active');
        if (!empty($filter['parcela'])) {
            $query->where('id_parcela', $filter['parcela']);
        }

        $rows = $query->orderByDesc('id')->limit(100)
            ->get(['uuid', 'title', 'id_parcela', 'selected_count'])
            ->map(function (FieldMission $m) {
                return [
                    'uuid' => $m->uuid,
                    'title' => $m->title,
                    'parcela_id' => $m->id_parcela,
                    'selected_count' => (int) $m->selected_count,
                    // Reutiliza verificación de acceso y datos autorizados del módulo V0.2.1.
                    'data_url' => route('campo.mission.data', ['mission' => $m->uuid]),
                ];
            })->values();

        return response()->json(['missions' => $rows, 'limit' => 100])
            ->header('Cache-Control', 'private, no-store')
            ->header('X-Content-Type-Options', 'nosniff');
    }
}
