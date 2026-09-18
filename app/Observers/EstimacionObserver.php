<?php

namespace App\Observers;

use App\Models\Estimacion;
use App\Services\FormulaEngineService;

class EstimacionObserver
{
    public function __construct(private FormulaEngineService $engine) {}

    public function creating(Estimacion $estimacion): void
    {
        $formula = $estimacion->formula ?? \App\Models\Formula::find($estimacion->id_formula);

        if (!$formula || $formula->modo_ejecucion !== 'app') {
            return; // lo maneja el trigger de MySQL
        }

        if ($formula->estado_revision !== 'aprobada') {
            throw new \RuntimeException('La fórmula seleccionada aún no está aprobada.');
        }

        $troza = \App\Models\Troza::findOrFail($estimacion->id_troza);
        $resultado = $this->engine->calculateForModel($formula, $troza);

        $estimacion->calculo = $resultado['calculo'];
        $estimacion->biomasa = $resultado['biomasa'];
        $estimacion->carbono = $resultado['carbono'];
    }
}