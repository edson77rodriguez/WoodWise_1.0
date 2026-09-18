<?php

namespace App\Http\Controllers\API;

use App\Http\Controllers\Controller;
use App\Models\Catalogo;
use Illuminate\Http\Request;

class MobileCatalogoController extends Controller
{
    public function index(Request $request)
    {
        $catalogos = Catalogo::query()
            ->select([
                'id_cat',
                'nom_cat',
                'created_at',
                'updated_at',
            ])
            ->orderBy('nom_cat')
            ->get();

        return response()->json([
            'data' => $catalogos->map(function ($catalogo) {
                return [
                    'id_cat' => $catalogo->id_cat,
                    'nom_cat' => $catalogo->nom_cat,
                    'created_at' => $catalogo->created_at,
                    'updated_at' => $catalogo->updated_at,
                ];
            }),
            'total' => $catalogos->count(),
        ]);
    }
}