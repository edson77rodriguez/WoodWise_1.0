<div class="modal fade" id="editEspecieModal<?php echo e($especie->id_especie); ?>" tabindex="-1" aria-hidden="true">
    <div class="modal-dialog modal-dialog-centered">
        <div class="modal-content border-0 shadow-lg">
            <div class="modal-header text-white" style="background-color: var(--succulent-dark);">
                <h5 class="modal-title"><i class="fas fa-edit me-2"></i>Editar Especie</h5>
                <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal"></button>
            </div>
            <div class="modal-body p-4">
                <form method="POST" action="<?php echo e(route('especies.update', $especie->id_especie)); ?>" enctype="multipart/form-data">
                    <?php echo csrf_field(); ?>
                    <?php echo method_field('PUT'); ?>
                    <div class="form-floating mb-3">
                        <input type="text" name="nom_cientifico" class="form-control" value="<?php echo e($especie->nom_cientifico); ?>" required placeholder="Nombre Científico">
                        <label>Nombre Científico</label>
                    </div>
                    <div class="form-floating mb-3">
                        <input type="text" name="nom_comun" class="form-control" value="<?php echo e($especie->nom_comun); ?>" required placeholder="Nombre Común">
                        <label>Nombre Común</label>
                    </div>
                    <div class="mb-3">
                        <label class="form-label">Cambiar Imagen</label>
                        <input type="file" name="imagen" class="form-control">
                        <?php if($especie->imagen): ?>
                            <div class="mt-2"><small class="text-muted">Imagen actual:</small><img src="<?php echo e(asset('storage/' . $especie->imagen)); ?>" class="img-thumbnail mt-1" style="width: 80px;"></div>
                        <?php endif; ?>
                    </div>
                    <div class="d-flex justify-content-end mt-4">
                        <button type="button" class="btn btn-secondary me-2" data-bs-dismiss="modal">Cancelar</button>
                        <button type="submit" class="btn text-white" style="background-color: var(--succulent-dark);"><i class="fas fa-save me-2"></i>Guardar Cambios</button>
                    </div>
                </form>
            </div>
        </div>
    </div>
</div><?php /**PATH /home/u535412430/domains/woodwise.me/woodwise_src/resources/views/especies/modal-edit.blade.php ENDPATH**/ ?>