<?php

namespace App\Http\Controllers\API;

use App\Http\Controllers\Controller;
use App\Models\Tipo_Estimacion;
use Illuminate\Http\Request;

class MobileTipoEstimacionController extends Controller
{
    public function index(Request $request)
    {
        $tipos = Tipo_Estimacion::query()
            ->select([
                'id_tipo_e',
                'desc_estimacion',
                'created_at',
                'updated_at',
            ])
            ->orderBy('desc_estimacion')
            ->get();

        return response()->json([
            'data' => $tipos->map(function ($tipo) {
                return [
                    'id_tipo_e' => $tipo->id_tipo_e,
                    'desc_estimacion' => $tipo->desc_estimacion,
                    'created_at' => $tipo->created_at,
                    'updated_at' => $tipo->updated_at,
                ];
            }),
            'total' => $tipos->count(),
        ]);
    }
}