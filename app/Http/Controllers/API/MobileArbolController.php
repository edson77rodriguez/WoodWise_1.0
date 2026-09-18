<?php

namespace App\Http\Controllers\API;

use App\Http\Controllers\Controller;
use App\Models\Arbol;
use Illuminate\Http\Request;

class MobileArbolController extends Controller
{
    public function store(Request $request, int $id)
    {
        $user = $request->user()->load([
            'persona.rol',
            'persona.tecnico',
        ]);

        $persona = $user->persona;

        if (!$persona || !$persona->rol) {
            return response()->json([
                'message' => 'El usuario no tiene un perfil configurado correctamente.',
            ], 403);
        }

        if ($persona->rol->nom_rol !== 'Tecnico') {
            return response()->json([
                'message' => 'Solo los técnicos pueden registrar árboles.',
            ], 403);
        }

        if (!$persona->tecnico) {
            return response()->json([
                'message' => 'El usuario no tiene un perfil de técnico configurado.',
            ], 403);
        }

        /*
        |--------------------------------------------------------------------------
        | Verificar que el técnico tenga asignada la parcela
        |--------------------------------------------------------------------------
        */

        $parcela = $persona->tecnico
            ->parcelas()
            ->where('parcelas.id_parcela', $id)
            ->first();

        if (!$parcela) {
            return response()->json([
                'message' => 'Parcela no encontrada o sin autorización para registrar información.',
            ], 404);
        }

        /*
        |--------------------------------------------------------------------------
        | Validación
        |--------------------------------------------------------------------------
        */

        $validated = $request->validate([
            'altura_total' => ['required', 'numeric', 'gt:0'],
            'diametro_pecho' => ['required', 'numeric', 'gt:0'],
            'id_especie' => ['required', 'integer', 'exists:especies,id_especie'],
        ]);

        /*
        |--------------------------------------------------------------------------
        | Crear árbol
        |--------------------------------------------------------------------------
        */

        $arbol = Arbol::create([
            'altura_total' => $validated['altura_total'],
            'diametro_pecho' => $validated['diametro_pecho'],
            'id_especie' => $validated['id_especie'],
            'id_parcela' => $parcela->id_parcela,
        ]);

        $arbol->load('especie');

        return response()->json([
            'message' => 'Árbol registrado correctamente.',
            'data' => [
                'id_arbol' => $arbol->id_arbol,
                'altura_total' => $arbol->altura_total,
                'diametro_pecho' => $arbol->diametro_pecho,
                'id_especie' => $arbol->id_especie,
                'id_parcela' => $arbol->id_parcela,

                'especie' => $arbol->especie ? [
                    'id_especie' => $arbol->especie->id_especie,
                    'nom_cientifico' => $arbol->especie->nom_cientifico,
                    'nom_comun' => $arbol->especie->nom_comun,
                ] : null,

                'created_at' => $arbol->created_at,
                'updated_at' => $arbol->updated_at,
            ],
        ], 201);
    }
}   