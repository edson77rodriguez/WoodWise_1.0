<?php

namespace App\Http\Controllers\API;

use App\Http\Controllers\Controller;
use App\Models\Especie;
use Illuminate\Http\Request;

class MobileEspecieController extends Controller
{
    public function index(Request $request)
    {
        $especies = Especie::query()
            ->select([
                'id_especie',
                'nom_cientifico',
                'nom_comun',
                'imagen',
                'created_at',
                'updated_at',
            ])
            ->orderBy('nom_comun')
            ->get();

        return response()->json([
            'data' => $especies->map(function ($especie) {
                return [
                    'id_especie' => $especie->id_especie,
                    'nom_cientifico' => $especie->nom_cientifico,
                    'nom_comun' => $especie->nom_comun,
                    'imagen' => $especie->imagen,
                    'created_at' => $especie->created_at,
                    'updated_at' => $especie->updated_at,
                ];
            }),
            'total' => $especies->count(),
        ]);
    }
}