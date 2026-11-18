<div class="modal fade" id="viewEspecieModal<?php echo e($especie->id_especie); ?>" tabindex="-1" aria-hidden="true">
    <div class="modal-dialog modal-dialog-centered modal-lg">
        <div class="modal-content border-0 shadow-lg">
            <div class="modal-header text-white" style="background-color: var(--succulent-medium);">
                <h5 class="modal-title"><i class="fas fa-info-circle me-2"></i>Detalles de la Especie</h5>
                <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal"></button>
            </div>
            <div class="modal-body p-4">
                 <div class="row">
                    <div class="col-md-7">
                        <p class="mb-2"><strong class="text-muted">ID:</strong> <span class="fs-5 fw-bold"><?php echo e($especie->id_especie); ?></span></p>
                        <p class="mb-2"><strong class="text-muted">Nombre Común:</strong> <span class="fs-5 fw-bold"><?php echo e($especie->nom_comun); ?></span></p>
                        <p class="mb-2"><strong class="text-muted">Nombre Científico:</strong> <em><?php echo e($especie->nom_cientifico); ?></em></p>
                        <p class="mb-2"><strong class="text-muted">Fecha de Registro:</strong> <span><?php echo e($especie->created_at?->format('d/m/Y')); ?></span></p>
                    </div>
                    <div class="col-md-5">
                        <?php if($especie->imagen): ?>
                            <img src="<?php echo e(asset('storage/' . $especie->imagen)); ?>" class="img-fluid rounded shadow-sm" alt="<?php echo e($especie->nom_comun); ?>">
                        <?php else: ?>
                            <div class="d-flex align-items-center justify-content-center bg-light rounded h-100 text-center">
                                <p class="text-muted">Sin imagen</p>
                            </div>
                        <?php endif; ?>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div><?php /**PATH /home/u535412430/domains/woodwise.me/woodwise_src/resources/views/especies/modal-view.blade.php ENDPATH**/ ?>