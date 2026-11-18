<div class="modal fade" id="createFormulaModal" tabindex="-1" aria-hidden="true">
    <div class="modal-dialog modal-dialog-centered">
        <div class="modal-content border-0 shadow-lg">
            <div class="modal-header text-white" style="background-color: var(--succulent-medium);">
                <h5 class="modal-title"><i class="fas fa-plus-circle me-2"></i>Nueva Fórmula</h5>
                <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal"></button>
            </div>
            <div class="modal-body p-4">
                <form method="POST" action="<?php echo e(route('formulas.store')); ?>" data-formula-form data-initial-variables="[]">
                    <?php echo csrf_field(); ?>
                    <div class="form-floating mb-3">
                        <input type="text" name="nom_formula" class="form-control" required placeholder="Nombre">
                        <label>Nombre de la Fórmula*</label>
                    </div>
                    <div class="form-floating mb-3">
                        <select name="id_tipo_e" class="form-select" required><option value="" disabled selected>Selecciona...</option><?php $__currentLoopData = $tiposEstimacion; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $tipo): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); ?><option value="<?php echo e($tipo->id_tipo_e); ?>"><?php echo e($tipo->desc_estimacion); ?></option><?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); ?></select>
                        <label>Tipo de Estimación*</label>
                    </div>
                    <div class="form-floating mb-3">
                        <select name="id_cat" class="form-select" required><option value="" disabled selected>Selecciona...</option><?php $__currentLoopData = $catalogos; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $catalogo): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); ?><option value="<?php echo e($catalogo->id_cat); ?>"><?php echo e($catalogo->nom_cat); ?></option><?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); ?></select>
                        <label>Catálogo*</label>
                    </div>
                    <div class="row g-3 mb-3">
                        <div class="col-md-4 d-flex align-items-center">
                            <input type="hidden" name="modo_ejecucion" value="app">
                            <div>
                                <span class="badge bg-info text-dark"><i class="fas fa-robot me-1"></i>Modo: Aplicación</span>
                                <small class="d-block text-muted mt-1">Las fórmulas nuevas siempre se calculan con el motor de la app.</small>
                            </div>
                        </div>
                        <div class="col-md-4">
                            <div class="form-floating">
                                <select name="estado_revision" class="form-select" required>
                                    <option value="revision" selected>Revision</option>
                                    <option value="aprobada">Aprobada</option>
                                    <option value="rechazada">Rechazada</option>
                                </select>
                                <label>Estado*</label>
                            </div>
                        </div>
                    </div>
                    <div class="formula-tree-only d-none">
                        <div class="row g-3 mb-3">
                            <div class="col-md-6">
                                <label class="form-label">Factor de biomasa</label>
                                <input type="number" step="0.000001" name="biomasa_factor" class="form-control" value="1">
                            </div>
                            <div class="col-md-6">
                                <label class="form-label">Factor de carbono</label>
                                <input type="number" step="0.000001" name="carbono_factor" class="form-control" value="0.5">
                            </div>
                        </div>
                        <div class="mb-3">
                            <label class="form-label">Especies relacionadas</label>
                            <select name="especies_relacionadas[]" class="form-select" multiple size="4">
                                <?php $__currentLoopData = $especies; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $especie): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); ?>
                                    <option value="<?php echo e($especie->id_especie); ?>"><?php echo e($especie->nom_comun); ?> (<?php echo e($especie->nom_cientifico); ?>)</option>
                                <?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); ?>
                            </select>
                            <small class="text-muted">Solo se usa cuando la fórmula corresponde a árboles.</small>
                        </div>
                    </div>
                    <div class="formula-troza-only alert alert-info py-2 mb-3 d-none">
                        Esta fórmula queda como cálculo de troza. El resultado se maneja automáticamente como <strong>cálculo</strong>.
                    </div>
                     <div class="mb-3">
                        <label class="form-label">Expresión*</label>
                        <textarea name="expresion" id="expresionCreate" class="form-control" rows="3" required placeholder="(DAP^2 * 0.7854 * altura)"></textarea>
                        <button type="button" class="btn btn-sm btn-outline-secondary mt-2" onclick="testExpression('expresionCreate')"><i class="fas fa-check me-1"></i> Probar</button>
                    </div>
                    <div class="mb-3">
                        <div class="d-flex justify-content-between align-items-center mb-2">
                            <label class="form-label mb-0">Variables estructuradas</label>
                            <button type="button" class="btn btn-sm btn-outline-secondary js-add-variable-row">Agregar variable</button>
                        </div>
                        <div class="table-responsive">
                            <table class="table table-sm align-middle">
                                <thead>
                                    <tr>
                                        <th>Nombre</th>
                                        <th>Etiqueta</th>
                                        <th>Origen</th>
                                        <th>Default</th>
                                        <th class="text-center">Req.</th>
                                        <th></th>
                                    </tr>
                                </thead>
                                <tbody class="js-variable-rows"></tbody>
                            </table>
                        </div>
                        <input type="hidden" name="variables_schema" class="js-variables-schema">
                        <small class="text-muted">El sistema tomará estas variables como fuente para validar y calcular la fórmula.</small>
                    </div>
                    <div class="mb-3">
                        <label class="form-label">Notas de revisión</label>
                        <textarea name="revision_notas" class="form-control" rows="2" placeholder="Opcional"></textarea>
                    </div>
                    <div class="d-flex justify-content-end mt-4">
                        <button type="button" class="btn btn-secondary me-2" data-bs-dismiss="modal">Cancelar</button>
                        <button type="submit" class="btn text-white" style="background-color: var(--succulent-medium);"><i class="fas fa-check-circle me-2"></i>Crear</button>
                    </div>
                </form>
            </div>
        </div>
    </div>
</div><?php /**PATH /home/u535412430/domains/woodwise.me/woodwise_src/resources/views/formulas/modal-create.blade.php ENDPATH**/ ?>