    
    <?php $__currentLoopData = $parcelas; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $parcela): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); ?>
          
        <div class="modal fade wood-modal" id="estimacionArbolModal<?php echo e($parcela->id_parcela); ?>" tabindex="-1" aria-hidden="true">
            <div class="modal-dialog modal-dialog-centered">
                <div class="modal-content border-0 wood-modal-content">
                    <div class="modal-header wood-modal-header wood-bg-warning">
                        <div class="d-flex align-items-center">
                            <div class="wood-modal-icon me-3"><i class="fas fa-calculator"></i></div>
                            <div>
                                <h5 class="modal-title wood-modal-title text-white">Estimación para Árbol</h5>
                                <p class="wood-modal-subtitle mb-0">Parcela: <?php echo e($parcela->nom_parcela); ?></p>
                            </div>
                        </div>
                        <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal" aria-label="Close"></button>
                    </div>
                    <div class="modal-body p-4 wood-modal-body">
                        <form method="POST" action="<?php echo e(route('tecnico.estimacion-arbol.store')); ?>">
                            <?php echo csrf_field(); ?>
                            <input type="hidden" name="id_parcela" value="<?php echo e($parcela->id_parcela); ?>">
                            <input type="hidden" name="calculo" value="0">
                            <input type="hidden" name="id_tipo_e" id="tipoEstimacionValue<?php echo e($parcela->id_parcela); ?>" value="2">
                            <input type="hidden" name="id_formula" id="formulaArbolValue<?php echo e($parcela->id_parcela); ?>" value="">
                            <div class="mb-3">
                                <label class="wood-form-label">Seleccionar Árbol</label>
                                <select class="wood-form-select select-arbol-estimacion" name="id_arbol" data-parcela="<?php echo e($parcela->id_parcela); ?>" required>
                                    <option value="" selected disabled>Seleccione un árbol</option>
                                    <?php $__currentLoopData = $parcela->arboles; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $arbol): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); ?>
                                        <option value="<?php echo e($arbol->id_arbol); ?>" data-especie="<?php echo e($arbol->id_especie); ?>">
                                            Árbol #<?php echo e($arbol->id_arbol); ?> - <?php echo e($arbol->especie->nom_cientifico ?? 'Sin especie'); ?> 
                                            (H: <?php echo e($arbol->altura_total); ?>m, DAP: <?php echo e($arbol->diametro_pecho); ?>m)
                                        </option>
                                    <?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); ?>
                                </select>
                                <?php if($parcela->arboles->count() == 0): ?>
                                    <small class="text-warning">No hay árboles registrados. Primero registre un árbol.</small>
                                <?php endif; ?>
                            </div>
                            <div class="row g-3">
                                <div class="col-md-6">
                                    <label class="wood-form-label">Tipo de Estimación</label>
                                    <select class="wood-form-select select-tipo-estimacion" id="tipoEstimacion<?php echo e($parcela->id_parcela); ?>" disabled>
                                        <?php $__currentLoopData = $tiposEstimacion->where('desc_estimacion', 'Biomasa'); $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $tipo): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); ?>
                                            <option value="<?php echo e($tipo->id_tipo_e); ?>" selected><?php echo e($tipo->desc_estimacion); ?></option>
                                        <?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); ?>
                                    </select>
                                </div>
                                <div class="col-md-6">
                                    <label class="wood-form-label">Fórmula de Biomasa</label>
                                    <select class="wood-form-select select-formula-arbol" id="formulaArbol<?php echo e($parcela->id_parcela); ?>" data-parcela="<?php echo e($parcela->id_parcela); ?>" disabled>
                                        <option value="" selected>Seleccione un árbol primero</option>
                                        <?php $__currentLoopData = $formulas->whereIn('nom_formula', ['Biomasa Pinus montezumae', 'Biomasa Quercus crassifolia', 'Biomasa Quercus rugosa', 'Biomasa Pinus pseudostrobus']); $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $formula): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); ?>
                                            <option value="<?php echo e($formula->id_formula); ?>"><?php echo e($formula->nom_formula); ?></option>
                                        <?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); ?>
                                    </select>
                                    <small class="text-muted d-block mt-1">Se auto-completa según la especie del árbol.</small>
                                </div>
                            </div>
                            <div class="wood-modal-footer mt-4">
                                <button type="button" class="btn btn-wood-outline" data-bs-dismiss="modal">
                                    <i class="fas fa-times me-1"></i> Cancelar
                                </button>
                                <button type="submit" class="btn btn-wood-warning" <?php echo e($parcela->arboles_count == 0 ? 'disabled' : ''); ?>>
                                    <i class="fas fa-calculator me-1"></i> Crear Estimación
                                </button>
                            </div>
                        </form>
                    </div>
                </div>
            </div>
        </div>
    <?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); ?>

<script>
document.addEventListener('DOMContentLoaded', function() {
    // Mapeo de especies a fórmulas
    const especieFormulaMap = {
        '1': { formulaId: '8', formulaNombre: 'Biomasa Pinus pseudostrobus' },      // Pinus pseudostrobus
        '2': { formulaId: '7', formulaNombre: 'Biomasa Quercus rugosa' },           // Quercus rugosa
        '3': { formulaId: '5', formulaNombre: 'Biomasa Pinus montezumae' },         // Pinus montezumae
        '4': { formulaId: '6', formulaNombre: 'Biomasa Quercus crassifolia' }       // Quercus crassifolia
    };

    // Escuchadores para cada modal de árbol
    document.querySelectorAll('.select-arbol-estimacion').forEach(selectArbol => {
        const parcela = selectArbol.dataset.parcela;
        const tipoSelect = document.getElementById(`tipoEstimacion${parcela}`);
        const tipoValue = document.getElementById(`tipoEstimacionValue${parcela}`);
        const formulaSelect = document.getElementById(`formulaArbol${parcela}`);
        const formulaValue = document.getElementById(`formulaArbolValue${parcela}`);

        selectArbol.addEventListener('change', function() {
            const especieId = this.options[this.selectedIndex].dataset.especie;
            
            if (especieId && especieFormulaMap[especieId]) {
                const { formulaId, formulaNombre } = especieFormulaMap[especieId];
                
                // Auto-seleccionar la fórmula correspondiente
                formulaSelect.value = formulaId;
                formulaValue.value = formulaId;
                formulaSelect.disabled = false;
                
                // Auto-seleccionar Biomasa (id_tipo_e = 2)
                const biomasa = Array.from(tipoSelect.options).find(opt => opt.textContent.toLowerCase().includes('biomasa'));
                if (biomasa) {
                    tipoSelect.value = biomasa.value;
                    tipoValue.value = biomasa.value;
                }
                tipoSelect.disabled = false;
                
                // Feedback visual
                formulaSelect.classList.add('is-valid');
                tipoSelect.classList.add('is-valid');
            }
        });
    });
});
</script><?php /**PATH /home/u535412430/domains/woodwise.me/woodwise_src/resources/views/partials/modals/estimacion-arbol.blade.php ENDPATH**/ ?>