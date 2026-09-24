<?php

namespace App\Http\Controllers\API;

use App\Http\Controllers\Controller;
use App\Models\Estimacion;
use App\Models\Formula;
use App\Models\Troza;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Validation\ValidationException;

class MobileEstimacionTrozaController extends Controller
{
    public function store(
        Request $request,
        int $idParcela,
        int $idTroza
    ) {
        $validated = $request->validate([
            'id_formula' => [
                'required',
                'integer',
                'exists:formulas,id_formula',
            ],
        ]);

        $idFormula = (int) $validated['id_formula'];

        // ==========================================
        // 1. SOLO FÓRMULAS DE TROZA
        //
        // Trigger actual:
        // 1 = Huber
        // 2 = Smalian
        // 3 = Tronco de Cono
        // 4 = Newton
        // ==========================================

        if (!in_array($idFormula, [1, 2, 3, 4], true)) {
            throw ValidationException::withMessages([
                'id_formula' =>
                    'La fórmula seleccionada no corresponde a una estimación de troza.',
            ]);
        }

        // ==========================================
        // 2. VALIDAR QUE LA TROZA PERTENEZCA
        // A LA PARCELA INDICADA
        // ==========================================

        $troza = Troza::query()
            ->where('id_troza', $idTroza)
            ->where('id_parcela', $idParcela)
            ->firstOrFail();

        // ==========================================
        // 3. OBTENER FÓRMULA
        // ==========================================

        $formula = Formula::query()
            ->where('id_formula', $idFormula)
            ->firstOrFail();

        if ($formula->modo_ejecucion === 'app') {
            throw ValidationException::withMessages([
                'id_formula' =>
                    'La fórmula seleccionada está configurada para ejecución desde la aplicación.',
            ]);
        }

        // ==========================================
        // 4. CREAR ESTIMACIÓN
        //
        // No enviamos cálculos desde React Native.
        // El trigger MySQL los genera.
        // ==========================================

        $estimacion = DB::transaction(
            function () use (
                $formula,
                $troza
            ) {
                $estimacion = Estimacion::create([
                    'id_tipo_e' =>
                        $formula->id_tipo_e,

                    'id_formula' =>
                        $formula->id_formula,

                    // Valores iniciales.
                    // El BEFORE INSERT trigger
                    // los sustituye por los
                    // resultados definitivos.
                    'calculo' => 0,
                    'biomasa' => 0,
                    'carbono' => 0,

                    'id_troza' =>
                        $troza->id_troza,
                ]);

                // IMPORTANTE:
                // recuperar los valores que
                // modificó el trigger MySQL.
                $estimacion->refresh();

                return $estimacion;
            }
        );

        return response()->json([
            'message' =>
                'Estimación de troza creada correctamente.',

            'data' => [
                'id_estimacion' =>
                    $estimacion->id_estimacion,

                'id_tipo_e' =>
                    $estimacion->id_tipo_e,

                'id_formula' =>
                    $estimacion->id_formula,

                'id_troza' =>
                    $estimacion->id_troza,

                'calculo' =>
                    $estimacion->calculo,

                'biomasa' =>
                    $estimacion->biomasa,

                'carbono' =>
                    $estimacion->carbono,

                'created_at' =>
                    $estimacion->created_at,

                'updated_at' =>
                    $estimacion->updated_at,
            ],
        ], 201);
    }
}