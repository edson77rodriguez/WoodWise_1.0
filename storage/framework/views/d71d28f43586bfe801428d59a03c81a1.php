    
    <?php $__currentLoopData = $parcelas; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $parcela): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); ?>
        
        
        <div class="modal fade wood-modal" id="estimacionTrozaModal<?php echo e($parcela->id_parcela); ?>" tabindex="-1" aria-hidden="true">
            <div class="modal-dialog modal-dialog-centered">
                <div class="modal-content border-0 wood-modal-content">
                    <div class="modal-header wood-modal-header wood-bg-info">
                        <div class="d-flex align-items-center">
                            <div class="wood-modal-icon me-3"><i class="fas fa-calculator"></i></div>
                            <div>
                                <h5 class="modal-title wood-modal-title text-white">Estimación Volumétrica</h5>
                                <p class="wood-modal-subtitle mb-0">Parcela: <?php echo e($parcela->nom_parcela); ?></p>
                            </div>
                        </div>
                        <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal" aria-label="Close"></button>
                    </div>
                    <div class="modal-body p-4 wood-modal-body">
                        <form method="POST" action="<?php echo e(route('tecnico.estimacion.store')); ?>">
                            <?php echo csrf_field(); ?>
                            <input type="hidden" name="id_parcela" value="<?php echo e($parcela->id_parcela); ?>">
                            <input type="hidden" name="calculo" value="0">
                            <div class="mb-3">
                                <label class="wood-form-label">Seleccionar Troza</label>
                                <select class="wood-form-select" name="id_troza" required>
                                    <option value="" selected disabled>Seleccione una troza</option>
                                    <?php $__currentLoopData = $parcela->trozas; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $troza): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); ?>
                                        <option value="<?php echo e($troza->id_troza); ?>">Troza #<?php echo e($troza->id_troza); ?> - <?php echo e($troza->especie->nom_cientifico ?? 'N/A'); ?> (<?php echo e($troza->longitud); ?>m)</option>
                                    <?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); ?>
                                </select>
                                <?php if($parcela->trozas->count() == 0): ?>
                                    <small class="text-warning">No hay trozas registradas. Primero registre una troza.</small>
                                <?php endif; ?>
                            </div>
                            <div class="row g-3">
                                <div class="col-md-6">
                                    <label class="wood-form-label">Tipo de Estimación</label>
                                    <select class="wood-form-select" name="id_tipo_e" required>
                                        <?php $__currentLoopData = $tiposEstimacion->where('desc_estimacion', 'Volumen Maderable'); $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $tipo): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); ?>
                                            <option value="<?php echo e($tipo->id_tipo_e); ?>" selected><?php echo e($tipo->desc_estimacion); ?></option>
                                        <?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); ?>
                                    </select>
                                </div>
                                <div class="col-md-6">
                                    <label class="wood-form-label">Fórmula Volumétrica</label>
                                    <select class="wood-form-select" name="id_formula" required>
                                        <option value="" selected disabled>Seleccione una fórmula</option>
                                        <?php $__currentLoopData = $formulas->whereIn('nom_formula', ['Formula de Huber', 'Formula de Smalian', 'formula Tronco Cono', 'Formula Newton']); $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $formula): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); ?>
                                            <option value="<?php echo e($formula->id_formula); ?>"><?php echo e($formula->nom_formula); ?></option>
                                        <?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); ?>
                                    </select>
                                    <small class="text-muted">El trigger calculará volumen, biomasa y carbono automáticamente.</small>
                                </div>
                            </div>
                            <div class="wood-modal-footer mt-4">
                                <button type="button" class="btn btn-wood-outline" data-bs-dismiss="modal"><i class="fas fa-times me-1"></i> Cancelar</button>
                                <button type="submit" class="btn btn-wood-info"><i class="fas fa-calculator me-1"></i> Calcular Estimación</button>
                            </div>
                        </form>
                    </div>
                </div>
            </div>
        </div>
    <?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); ?><?php /**PATH /home/u535412430/domains/woodwise.me/woodwise_src/resources/views/partials/modals/estimacion-troza.blade.php ENDPATH**/ ?>