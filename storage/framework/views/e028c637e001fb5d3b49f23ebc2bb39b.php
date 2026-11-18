<?php $__currentLoopData = $estimaciones; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $estimacion): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); ?>
<div class="modal fade" id="editEstimacionModal<?php echo e($estimacion->id_estimacion); ?>" tabindex="-1" 
     aria-labelledby="editEstimacionLabel<?php echo e($estimacion->id_estimacion); ?>" aria-hidden="true">
    <div class="modal-dialog modal-dialog-centered">
        <div class="modal-content border-0 shadow">
            <div class="modal-header bg-gradient-primary text-white">
                <h5 class="modal-title text-white">
                    <i class="fas fa-edit me-2"></i>Editar Estimación #<?php echo e($estimacion->id_estimacion); ?>

                </h5>
                <button type="button" class="btn-close btn-close-white" 
                        data-bs-dismiss="modal" aria-label="Close"></button>
            </div>
            <div class="modal-body p-4">
                <form method="POST" action="<?php echo e(route('estimaciones.update', $estimacion->id_estimacion)); ?>">
                    <?php echo csrf_field(); ?>
                    <?php echo method_field('PUT'); ?>
                    <div class="mb-3">
                        <label class="form-label text-muted">Tipo de Estimación</label>
                        <select name="id_tipo_e" class="form-select border-2" required>
                            <?php $__currentLoopData = $tiposEstimacion; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $tipo): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); ?>
                            <option value="<?php echo e($tipo->id_tipo_e); ?>" <?php echo e($tipo->id_tipo_e == $estimacion->id_tipo_e ? 'selected' : ''); ?>>
                                <?php echo e($tipo->desc_estimacion); ?>

                            </option>
                            <?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); ?>
                        </select>
                    </div>
                    <div class="mb-3">
                        <label class="form-label text-muted">Fórmula</label>
                        <select name="id_formula" class="form-select border-2" required>
                            <?php $__currentLoopData = $formulas; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $formula): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); ?>
                            <option value="<?php echo e($formula->id_formula); ?>" <?php echo e($formula->id_formula == $estimacion->id_formula ? 'selected' : ''); ?>>
                                <?php echo e($formula->nom_formula); ?>

                            </option>
                            <?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); ?>
                        </select>
                    </div>
                    <div class="mb-3">
                        <label class="form-label text-muted">Troza</label>
                        <select name="id_troza" class="form-select border-2" required>
                            <?php $__currentLoopData = $trozas; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $troza): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); ?>
                            <option value="<?php echo e($troza->id_troza); ?>" <?php echo e($troza->id_troza == $estimacion->id_troza ? 'selected' : ''); ?>>
                                <?php echo e($troza->codigo_troza); ?>

                            </option>
                            <?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); ?>
                        </select>
                    </div>
                    <div class="mb-3">
                        <label class="form-label text-muted">Cálculo</label>
                        <input type="number" step="0.01" class="form-control border-2"
                               name="calculo" value="<?php echo e($estimacion->calculo); ?>" required>
                    </div>
                    <div class="d-grid gap-2 d-md-flex justify-content-md-end mt-4">
                        <button type="button" class="btn btn-outline-secondary me-md-2 rounded-pill" 
                                data-bs-dismiss="modal">Cancelar</button>
                        <button type="submit" class="btn btn-primary rounded-pill">
                            <i class="fas fa-save me-1"></i>Guardar Cambios
                        </button>
                    </div>
                </form>
            </div>
        </div>
    </div>
</div>
<?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); ?><?php /**PATH /home/u535412430/domains/woodwise.me/woodwise_src/resources/views/partials/modals/edit_estimacion.blade.php ENDPATH**/ ?>