<?php $__currentLoopData = $parcela->trozas; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $troza): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); ?>
<div class="modal fade" id="editTrozaModal<?php echo e($troza->id_troza); ?>" tabindex="-1"
    aria-labelledby="editTrozaLabel<?php echo e($troza->id_troza); ?>" aria-hidden="true">
   <div class="modal-dialog modal-dialog-centered">
       <div class="modal-content border-0 shadow">
           <div class="modal-header bg-gradient-primary text-white">
               <h5 class="modal-title text-white">
                   <i class="fas fa-edit me-2"></i>Editar Troza #<?php echo e($troza->id_troza); ?>

               </h5>
               <button type="button" class="btn-close btn-close-white"
                       data-bs-dismiss="modal" aria-label="Close"></button>
           </div>
           <div class="modal-body p-4">
               <form method="POST" action="<?php echo e(route('trozas.update', $troza->id_troza)); ?>">
                   <?php echo csrf_field(); ?>
                   <?php echo method_field('PUT'); ?>
                   <div class="row">
                       <div class="col-md-6 mb-3">
                           <label class="form-label text-muted">Longitud (m)</label>
                           <input type="number" step="0.01" class="form-control border-2"
                                  name="longitud" value="<?php echo e($troza->longitud); ?>" required>
                       </div>
                       <div class="col-md-6 mb-3">
                           <label class="form-label text-muted">Diámetro (m)</label>
                           <input type="number" step="0.01" class="form-control border-2"
                                  name="diametro" value="<?php echo e($troza->diametro); ?>" required>
                       </div>
                   </div>
                   <div class="mb-3">
                       <label class="form-label text-muted">Densidad (ton/m³)</label>
                       <input type="number" step="0.01" class="form-control border-2"
                              name="densidad" value="<?php echo e($troza->densidad); ?>" required>
                   </div>
                   <div class="row">
                       <div class="col-md-6 mb-3">
                           <label class="form-label text-muted">Especie</label>
                           <select class="form-select border-2" name="id_especie" required>
                               <?php $__currentLoopData = $especies; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $especie): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); ?>
                               <option value="<?php echo e($especie->id_especie); ?>"
                                       <?php echo e($troza->id_especie == $especie->id_especie ? 'selected' : ''); ?>>
                                   <?php echo e($especie->nom_cientifico); ?>

                               </option>
                               <?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); ?>
                           </select>
                       </div>
                       <div class="col-md-6 mb-3">
                           <label class="form-label text-muted">Parcela</label>
                           <select class="form-select border-2" name="id_parcela" required>
                               <?php $__currentLoopData = $parcelas; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $parcelaItem): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); ?>
                               <option value="<?php echo e($parcelaItem->id_parcela); ?>"
                                       <?php echo e($troza->id_parcela == $parcelaItem->id_parcela ? 'selected' : ''); ?>>
                                   <?php echo e($parcelaItem->nom_parcela); ?>

                               </option>
                               <?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); ?>
                           </select>
                       </div>
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
<?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); ?><?php /**PATH /home/u535412430/domains/woodwise.me/woodwise_src/resources/views/partials/modals/edit_troza.blade.php ENDPATH**/ ?>