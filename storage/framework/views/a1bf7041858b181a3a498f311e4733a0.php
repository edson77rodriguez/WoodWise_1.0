    
    <?php $__currentLoopData = $parcelas; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $parcela): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); ?>
         
        <div class="modal fade wood-modal" id="addTrozaModal<?php echo e($parcela->id_parcela); ?>" tabindex="-1" aria-hidden="true">
            <div class="modal-dialog modal-dialog-centered modal-lg">
                <div class="modal-content border-0 wood-modal-content">
                    <div class="modal-header wood-modal-header wood-bg-primary">
                        <div class="d-flex align-items-center">
                            <div class="wood-modal-icon me-3"><i class="fas fa-tree"></i></div>
                            <div>
                                <h5 class="modal-title wood-modal-title text-white">Nueva Troza</h5>
                                <p class="wood-modal-subtitle mb-0">Parcela: <?php echo e($parcela->nom_parcela); ?></p>
                            </div>
                        </div>
                        <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal" aria-label="Close"></button>
                    </div>
                    <div class="modal-body p-4 wood-modal-body">
                        <form method="POST" action="<?php echo e(route('tecnico.troza.store')); ?>">
                            <?php echo csrf_field(); ?>
                            <input type="hidden" name="id_parcela" value="<?php echo e($parcela->id_parcela); ?>">
                            <div class="row g-3">
                                <div class="col-md-6"><label class="wood-form-label">Longitud (m)</label><input type="number" step="0.01" name="longitud" class="wood-form-control" required></div>
                                <div class="col-md-6"><label class="wood-form-label">Diámetro (m)</label><input type="number" step="0.01" name="diametro" class="wood-form-control" required></div>
                                <div class="col-md-6"><label class="wood-form-label">Diámetro otro extremo (m)</label><input type="number" step="0.01" name="diametro_otro_extremo" class="wood-form-control"></div>
                                <div class="col-md-6"><label class="wood-form-label">Diámetro medio (m)</label><input type="number" step="0.01" name="diametro_medio" class="wood-form-control"></div>
                                <div class="col-md-6"><label class="wood-form-label">Densidad</label><input type="number" step="0.01" name="densidad" class="wood-form-control" required></div>
                                <div class="col-md-6">
                                    <label class="wood-form-label">Especie</label>
                                    <select class="wood-form-select" name="id_especie" required>
                                        <option value="" selected disabled>Seleccione una especie</option>
                                        <?php $__currentLoopData = $especies; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $especie): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); ?>
                                            <option value="<?php echo e($especie->id_especie); ?>"><?php echo e($especie->nom_cientifico); ?></option>
                                        <?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); ?>
                                    </select>
                                </div>
                            </div>
                            <div class="wood-modal-footer mt-4">
                                <button type="button" class="btn btn-wood-outline" data-bs-dismiss="modal"><i class="fas fa-times me-1"></i> Cancelar</button>
                                <button type="submit" class="btn btn-wood"><i class="fas fa-check-circle me-1"></i> Registrar Troza</button>
                            </div>
                        </form>
                    </div>
                </div>
            </div>
        </div>

    <?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); ?><?php /**PATH /home/u535412430/domains/woodwise.me/woodwise_src/resources/views/partials/modals/troza-create.blade.php ENDPATH**/ ?>