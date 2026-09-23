<?php

namespace App\Http\Controllers\API;

use App\Http\Controllers\Controller;
use App\Models\Arbol;
use App\Models\Estimacion1;
use App\Models\Formula;
use App\Services\FormulaEngineService;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Validation\ValidationException;

class MobileEstimacionArbolController extends Controller
{
    /**
     * Crear la estimación correspondiente
     * a un árbol de pie.
     *
     * La fórmula se determina automáticamente
     * según la especie del árbol.
     */
    public function store(
        Request $request,
        int $idParcela,
        int $idArbol
    ) {
        // ==========================================
        // 1. VALIDAR ÁRBOL Y PARCELA
        // ==========================================

        $arbol = Arbol::query()
            ->where(
                'id_arbol',
                $idArbol
            )
            ->where(
                'id_parcela',
                $idParcela
            )
            ->firstOrFail();

        // ==========================================
        // 2. DETERMINAR FÓRMULA SEGÚN ESPECIE
        // ==========================================

        $formula =
            $this->resolveFormulaForArbol(
                (int) $arbol->id_especie
            );

        if (!$formula) {
            throw ValidationException::withMessages([
                'id_formula' =>
                    'No existe una fórmula aprobada para la especie del árbol.',
            ]);
        }

        // ==========================================
        // 3. EVITAR DUPLICAR LA ESTIMACIÓN
        //
        // El sistema web permite una estimación
        // por árbol y tipo de estimación.
        //
        // También hace más segura una reintento
        // de sincronización móvil.
        // ==========================================

        $existente = Estimacion1::query()
            ->where(
                'id_arbol',
                $arbol->id_arbol
            )
            ->where(
                'id_tipo_e',
                $formula->id_tipo_e
            )
            ->first();

        if ($existente) {
            $existente->refresh();

            return response()->json([
                'message' =>
                    'El árbol ya cuenta con una estimación.',

                'data' =>
                    $this->formatearEstimacion(
                        $existente
                    ),
            ]);
        }

        // ==========================================
        // 4. CREAR ESTIMACIÓN
        // ==========================================

        $estimacion = DB::transaction(
            function () use (
                $formula,
                $arbol
            ) {
                // ==================================
                // DATOS BASE
                //
                // Si la fórmula funciona mediante
                // trigger, estos ceros permiten que
                // MySQL realice el cálculo.
                // ==================================

                $datos = [
                    'id_tipo_e' =>
                        $formula->id_tipo_e,

                    'id_formula' =>
                        $formula->id_formula,

                    'calculo' =>
                        0,

                    'biomasa' =>
                        0,

                    'carbono' =>
                        0,

                    'id_arbol' =>
                        $arbol->id_arbol,
                ];

                // ==================================
                // COMPATIBILIDAD CON EL SISTEMA WEB
                //
                // Si en el futuro alguna fórmula
                // vuelve a modo "app", mantenemos
                // el mismo comportamiento del
                // controlador web.
                // ==================================

                if (
                    $formula->modo_ejecucion ===
                    'app'
                ) {
                    try {
                        $outputs =
                            app(
                                FormulaEngineService::class
                            )
                            ->calculateForModel(
                                $formula,
                                $arbol
                            );

                        $datos =
                            array_merge(
                                $datos,
                                $outputs
                            );

                    } catch (
                        \InvalidArgumentException
                        $exception
                    ) {
                        throw ValidationException::withMessages([
                            'formula' =>
                                $exception->getMessage(),
                        ]);
                    }
                }

                // ==================================
                // INSERTAR
                //
                // Para las fórmulas actuales en
                // modo trigger, MySQL modifica:
                //
                // calculo
                // biomasa
                // carbono
                // ==================================

                $estimacion =
                    Estimacion1::create(
                        $datos
                    );

                // ==================================
                // RECUPERAR RESULTADO DEL TRIGGER
                // ==================================

                $estimacion->refresh();

                return $estimacion;
            }
        );

        // ==========================================
        // 5. RESPUESTA
        // ==========================================

        return response()->json([
            'message' =>
                'Estimación de árbol creada correctamente.',

            'data' =>
                $this->formatearEstimacion(
                    $estimacion
                ),
        ], 201);
    }

    /**
     * Resolver fórmula según la especie.
     *
     * Mantiene el mismo criterio utilizado
     * actualmente por Estimacion1Controller.
     */
    private function resolveFormulaForArbol(
        int $idEspecie
    ): ?Formula {
        $formulas =
            Formula::where(
                'id_tipo_e',
                2
            )
            ->where(
                'id_cat',
                2
            )
            ->where(
                'estado_revision',
                'aprobada'
            )
            ->orderBy(
                'nom_formula'
            )
            ->get();

        // ==========================================
        // PRIMERO:
        // especies_relacionadas si está configurado
        // ==========================================

        $formula =
            $formulas->first(
                function (
                    Formula $formula
                ) use (
                    $idEspecie
                ) {
                    $relacionadas =
                        collect(
                            $formula
                                ->especies_relacionadas
                                ?? []
                        )
                        ->map(
                            fn ($value) =>
                                (int) $value
                        );

                    return $relacionadas
                        ->contains(
                            $idEspecie
                        );
                }
            );

        if ($formula) {
            return $formula;
        }

        // ==========================================
        // FALLBACK ACTUAL DE SIGMAD
        // ==========================================

        $expectedFormulaId =
            $this
                ->formulaIdsBySpecies()[
                    $idEspecie
                ]
                ?? null;

        if (
            $expectedFormulaId
        ) {
            return $formulas
                ->firstWhere(
                    'id_formula',
                    $expectedFormulaId
                );
        }

        return null;
    }

    /**
     * Mapeo actual de SIGMAD.
     */
    private function formulaIdsBySpecies():
        array {
        return [
            // Pinus pseudostrobus
            1 => 8,

            // Quercus rugosa
            2 => 7,

            // Pinus montezumae
            3 => 5,

            // Quercus crassifolia
            4 => 6,
        ];
    }

    /**
     * Formato uniforme de la respuesta API.
     */
    private function formatearEstimacion(
        Estimacion1 $estimacion
    ): array {
        return [
            'id_estimacion1' =>
                $estimacion
                    ->id_estimacion1,

            'id_tipo_e' =>
                $estimacion
                    ->id_tipo_e,

            'id_formula' =>
                $estimacion
                    ->id_formula,

            'id_arbol' =>
                $estimacion
                    ->id_arbol,

            'calculo' =>
                $estimacion
                    ->calculo,

            'biomasa' =>
                $estimacion
                    ->biomasa,

            'carbono' =>
                $estimacion
                    ->carbono,

            'created_at' =>
                $estimacion
                    ->created_at,

            'updated_at' =>
                $estimacion
                    ->updated_at,
        ];
    }
}