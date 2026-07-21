<?php

namespace App\Observers;

use App\Models\Estimacion1;
use App\Services\FormulaEngineService;

class Estimacion1Observer
{
    public function __construct(private FormulaEngineService $engine) {}

    public function creating(Estimacion1 $estimacion): void
    {
        $formula = $estimacion->formula ?? \App\Models\Formula::find($estimacion->id_formula);

        if (!$formula || $formula->modo_ejecucion !== 'app') {
            return;
        }

        if ($formula->estado_revision !== 'aprobada') {
            throw new \RuntimeException('La fórmula seleccionada aún no está aprobada.');
        }

        $arbol = \App\Models\Arbol::findOrFail($estimacion->id_arbol);

        // Si la fórmula tiene especies_relacionadas, validamos que el árbol sea de una especie permitida
        if (!empty($formula->especies_relacionadas) && isset($arbol->id_especie)) {
            if (!in_array($arbol->id_especie, $formula->especies_relacionadas, true)) {
                throw new \RuntimeException('Esta fórmula no aplica para la especie del árbol seleccionado.');
            }
        }

        $resultado = $this->engine->calculateForModel($formula, $arbol);

        $estimacion->calculo = $resultado['calculo'];
        $estimacion->biomasa = $resultado['biomasa'];
        $estimacion->carbono = $resultado['carbono'];
    }
}