<div class="modal fade wood-modal" id="viewFormulaModal<?php echo e($formula->id_formula); ?>" tabindex="-1" aria-hidden="true">
    <div class="modal-dialog modal-dialog-centered">
        <div class="modal-content border-0 wood-modal-content">
            <div class="modal-header wood-modal-header wood-bg-primary">
                <div class="d-flex align-items-center">
                    <div class="wood-modal-icon me-3"><i class="fas fa-info-circle"></i></div>
                    <div>
                        <h5 class="modal-title wood-modal-title text-white">Detalles de Fórmula</h5>
                        <p class="wood-modal-subtitle mb-0"><?php echo e($formula->nom_formula); ?></p>
                    </div>
                </div>
                <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal"></button>
            </div>
            <div class="modal-body wood-modal-body">
                <div class="row g-3">
                    <div class="col-12">
                        <label class="wood-form-label">Nombre</label>
                        <div class="p-3 rounded-3" style="background: rgba(16, 185, 129, 0.05); border: 1px solid rgba(16, 185, 129, 0.15);">
                            <span class="fs-5 fw-800" style="color: #0f172a;"><?php echo e($formula->nom_formula); ?></span>
                        </div>
                    </div>
                    <div class="col-md-6">
                        <label class="wood-form-label">Tipo</label>
                        <div class="p-3 rounded-3" style="background: rgba(16, 185, 129, 0.05); border: 1px solid rgba(16, 185, 129, 0.15);">
                            <span style="color: #64748b;"><?php echo e($formula->tipoEstimacion->desc_estimacion); ?></span>
                        </div>
                    </div>
                    <div class="col-md-6">
                        <label class="wood-form-label">Catálogo</label>
                        <div class="p-3 rounded-3" style="background: rgba(16, 185, 129, 0.05); border: 1px solid rgba(16, 185, 129, 0.15);">
                            <span style="color: #64748b;"><?php echo e($formula->catalogo->nom_cat); ?></span>
                        </div>
                    </div>
                    <div class="col-md-6">
                        <label class="wood-form-label">Estado</label>
                        <div class="p-3 rounded-3" style="background: rgba(16, 185, 129, 0.05); border: 1px solid rgba(16, 185, 129, 0.15);">
                            <span style="color: #64748b;"><?php echo e(ucfirst($formula->estado_revision ?? 'revision')); ?></span>
                        </div>
                    </div>
                    <div class="col-md-6">
                        <label class="wood-form-label">Modo de ejecución</label>
                        <div class="p-3 rounded-3" style="background: rgba(16, 185, 129, 0.05); border: 1px solid rgba(16, 185, 129, 0.15);">
                            <span style="color: #64748b;"><?php echo e(strtoupper($formula->modo_ejecucion ?? 'trigger')); ?></span>
                        </div>
                    </div>
                    <div class="col-md-6">
                        <label class="wood-form-label">Resultado</label>
                        <div class="p-3 rounded-3" style="background: rgba(16, 185, 129, 0.05); border: 1px solid rgba(16, 185, 129, 0.15);">
                            <span style="color: #64748b;"><?php echo e(strtoupper($formula->resultado_tipo ?? 'calculo')); ?></span>
                        </div>
                    </div>
                    <div class="col-md-6">
                        <label class="wood-form-label">Carbono / Biomasa</label>
                        <div class="p-3 rounded-3" style="background: rgba(16, 185, 129, 0.05); border: 1px solid rgba(16, 185, 129, 0.15);">
                            <span style="color: #64748b;">Biomasa: <?php echo e($formula->biomasa_factor ?? 'auto'); ?> | Carbono: <?php echo e($formula->carbono_factor ?? 0.5); ?></span>
                        </div>
                    </div>
                    <div class="col-12">
                        <label class="wood-form-label">Expresión Matemática</label>
                        <div class="p-3 rounded-3" style="background: rgba(16, 185, 129, 0.05); border: 1px solid rgba(16, 185, 129, 0.15); font-family: 'Monaco', 'Courier New', monospace;">
                            <code class="expression fs-6 fw-500" style="color: #059669; letter-spacing: 0.5px;"><?php echo e($formula->expresion); ?></code>
                        </div>
                    </div>
                    <div class="col-12">
                        <label class="wood-form-label">Especies relacionadas</label>
                        <div class="p-3 rounded-3" style="background: rgba(16, 185, 129, 0.05); border: 1px solid rgba(16, 185, 129, 0.15);">
                            <?php ($relatedSpecies = $formula->especies_relacionadas ?? []); ?>
                            <?php $__empty_1 = true; $__currentLoopData = $relatedSpecies; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $especieId): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); $__empty_1 = false; ?>
                                <span class="badge bg-secondary me-1">#<?php echo e($especieId); ?></span>
                            <?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); if ($__empty_1): ?>
                                <span style="color: #64748b;">Sin especies asociadas</span>
                            <?php endif; ?>
                        </div>
                    </div>
                    <div class="col-12">
                        <label class="wood-form-label">Variables</label>
                        <div class="p-3 rounded-3" style="background: rgba(16, 185, 129, 0.05); border: 1px solid rgba(16, 185, 129, 0.15);">
                            <pre class="mb-0" style="white-space: pre-wrap; color: #64748b;"><?php echo e(json_encode($formula->variables_schema ?? [], JSON_PRETTY_PRINT | JSON_UNESCAPED_UNICODE)); ?></pre>
                        </div>
                    </div>
                    <?php if(!empty($formula->revision_notas)): ?>
                    <div class="col-12">
                        <label class="wood-form-label">Notas de revisión</label>
                        <div class="p-3 rounded-3" style="background: rgba(16, 185, 129, 0.05); border: 1px solid rgba(16, 185, 129, 0.15);">
                            <span style="color: #64748b;"><?php echo e($formula->revision_notas); ?></span>
                        </div>
                    </div>
                    <?php endif; ?>
                </div>
            </div>
            <div class="wood-modal-footer">
                <button type="button" class="btn btn-wood-outline" data-bs-dismiss="modal">
                    <i class="fas fa-times me-1"></i> Cerrar
                </button>
            </div>
        </div>
    </div>
</div><?php /**PATH /home/u535412430/domains/woodwise.me/woodwise_src/resources/views/formulas/modal-view.blade.php ENDPATH**/ ?>