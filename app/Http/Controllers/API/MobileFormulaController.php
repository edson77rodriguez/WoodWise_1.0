<?php

namespace App\Http\Controllers\API;

use App\Http\Controllers\Controller;
use App\Models\Formula;
use Illuminate\Http\Request;

class MobileFormulaController extends Controller
{
    public function index(Request $request)
    {
        $formulas = Formula::query()
            ->with([
                'tipoEstimacion:id_tipo_e,desc_estimacion',
                'catalogo:id_cat,nom_cat',
            ])
            ->select([
                'id_formula',
                'nom_formula',
                'expresion',
                'id_tipo_e',
                'id_cat',
                'modo_ejecucion',
                'estado_revision',
                'variables_schema',
                'especies_relacionadas',
                'resultado_tipo',
                'biomasa_factor',
                'carbono_factor',
                'revision_notas',
                'revision_at',
                'created_at',
                'updated_at',
            ])
            ->orderBy('nom_formula')
            ->get();

        return response()->json([
            'data' => $formulas->map(function ($formula) {
                return [
                    'id_formula' => $formula->id_formula,
                    'nom_formula' => $formula->nom_formula,
                    'expresion' => $formula->expresion,

                    'id_tipo_e' => $formula->id_tipo_e,
                    'id_cat' => $formula->id_cat,

                    'modo_ejecucion' => $formula->modo_ejecucion,
                    'estado_revision' => $formula->estado_revision,

                    'variables_schema' => $formula->variables_schema,
                    'especies_relacionadas' => $formula->especies_relacionadas,

                    'resultado_tipo' => $formula->resultado_tipo,

                    'biomasa_factor' => $formula->biomasa_factor !== null
                        ? (float) $formula->biomasa_factor
                        : null,

                    'carbono_factor' => $formula->carbono_factor !== null
                        ? (float) $formula->carbono_factor
                        : null,

                    'revision_notas' => $formula->revision_notas,
                    'revision_at' => $formula->revision_at,

                    'tipo_estimacion' => $formula->tipoEstimacion ? [
                        'id_tipo_e' => $formula->tipoEstimacion->id_tipo_e,
                        'desc_estimacion' => $formula->tipoEstimacion->desc_estimacion,
                    ] : null,

                    'catalogo' => $formula->catalogo ? [
                        'id_cat' => $formula->catalogo->id_cat,
                        'nom_cat' => $formula->catalogo->nom_cat,
                    ] : null,

                    'created_at' => $formula->created_at,
                    'updated_at' => $formula->updated_at,
                ];
            }),

            'total' => $formulas->count(),
        ]);
    }
}