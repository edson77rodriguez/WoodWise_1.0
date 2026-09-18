<?php

namespace App\Http\Controllers\API;

use App\Http\Controllers\Controller;
use App\Models\Troza;
use Illuminate\Http\Request;

class MobileTrozaController extends Controller
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
                'message' => 'Solo los técnicos pueden registrar trozas.',
            ], 403);
        }

        if (!$persona->tecnico) {
            return response()->json([
                'message' => 'El usuario no tiene un perfil de técnico configurado.',
            ], 403);
        }

        /*
        |--------------------------------------------------------------------------
        | Verificar acceso a parcela
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
            'longitud' => ['required', 'numeric', 'gt:0'],
            'diametro' => ['required', 'numeric', 'gt:0'],

            'diametro_otro_extremo' => [
                'nullable',
                'numeric',
                'gt:0',
            ],

            'diametro_medio' => [
                'nullable',
                'numeric',
                'gt:0',
            ],

            'densidad' => ['required', 'numeric', 'gt:0'],

            'id_especie' => [
                'required',
                'integer',
                'exists:especies,id_especie',
            ],
        ]);

        /*
        |--------------------------------------------------------------------------
        | Crear troza
        |--------------------------------------------------------------------------
        */

        $troza = Troza::create([
            'longitud' => $validated['longitud'],
            'diametro' => $validated['diametro'],
            'diametro_otro_extremo' => $validated['diametro_otro_extremo'] ?? null,
            'diametro_medio' => $validated['diametro_medio'] ?? null,
            'densidad' => $validated['densidad'],
            'id_especie' => $validated['id_especie'],
            'id_parcela' => $parcela->id_parcela,
        ]);

        $troza->load('especie');

        return response()->json([
            'message' => 'Troza registrada correctamente.',
            'data' => [
                'id_troza' => $troza->id_troza,
                'longitud' => $troza->longitud,
                'diametro' => $troza->diametro,
                'diametro_otro_extremo' => $troza->diametro_otro_extremo,
                'diametro_medio' => $troza->diametro_medio,
                'densidad' => $troza->densidad,
                'id_especie' => $troza->id_especie,
                'id_parcela' => $troza->id_parcela,

                'especie' => $troza->especie ? [
                    'id_especie' => $troza->especie->id_especie,
                    'nom_cientifico' => $troza->especie->nom_cientifico,
                    'nom_comun' => $troza->especie->nom_comun,
                ] : null,

                'created_at' => $troza->created_at,
                'updated_at' => $troza->updated_at,
            ],
        ], 201);
    }
}