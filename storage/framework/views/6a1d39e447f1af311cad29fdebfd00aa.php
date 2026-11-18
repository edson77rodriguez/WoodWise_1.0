<?php $__env->startSection('template_title', 'Gestión de Parcelas'); ?>

<?php $__env->startPush('styles'); ?>
    
    <style>
        :root {
            --succulent-dark: #4e5a46;
            --succulent-medium: #7c9070;
            --succulent-light: #a8bba8;
            --succulent-lightest: #d3e0c9;
            --succulent-accent: #b2bf7f;
        }
        .bg-gradient-succulent {
            background: linear-gradient(135deg, var(--succulent-medium), var(--succulent-dark));
        }
        .search-bar:focus-within {
            box-shadow: 0 0 0 0.25rem rgba(124, 144, 112, 0.25);
        }
        .avatar-circle {
            width: 45px;
            height: 45px;
            border-radius: 50%;
            background-color: var(--succulent-medium);
            color: white;
            display: flex;
            align-items: center;
            justify-content: center;
            font-weight: bold;
            font-size: 1.1rem;
            flex-shrink: 0;
        }
        .table-hover tbody tr:hover {
            background-color: var(--succulent-lightest);
        }
        .btn-icon {
            width: 35px; height: 35px; display: inline-flex;
            align-items: center; justify-content: center;
            padding: 0; border-radius: 50% !important;
            margin: 0 2px; border: 1px solid #dee2e6;
        }
        .btn-icon:hover { background-color: #e9ecef; }
        .extension-badge {
            background-color: var(--succulent-lightest);
            color: var(--succulent-dark);
            font-size: 0.9em;
        }
    </style>
<?php $__env->stopPush(); ?>

<?php $__env->startSection('crud_content'); ?>
<div class="container-fluid py-4">
    <div class="card border-0 shadow-lg">
        <div class="card-header bg-gradient-succulent text-white d-flex flex-column flex-md-row justify-content-between align-items-center p-4">
            <div class="header-title mb-3 mb-md-0">
                <div class="d-flex align-items-center">
                    <i class="fas fa-draw-polygon fa-2x me-3"></i>
                    <div>
                        <h5 class="mb-0 fw-bold text-white">Gestión de Parcelas</h5>
                        <small>Administra las parcelas de los productores</small>
                    </div>
                </div>
            </div>
            <div class="header-actions d-flex align-items-center w-100 w-md-auto">
                <div class="input-group me-3 search-bar rounded-pill shadow-sm bg-white">
                    <span class="input-group-text bg-transparent border-0"><i class="fas fa-search"></i></span>
                    <input type="text" id="searchInput" class="form-control border-0" placeholder="Buscar parcela...">
                </div>
                <button class="btn rounded-pill flex-shrink-0" data-bs-toggle="modal" data-bs-target="#createParcelaModal" style="background-color: var(--succulent-lightest); color: var(--succulent-dark);">
                    <i class="fas fa-plus me-2"></i>Nueva Parcela
                </button>
            </div>
        </div>
        
        <div class="card-body p-0">
            <div class="table-responsive">
                <table class="table table-hover align-middle mb-0">
                    <thead class="bg-light">
                        <tr>
                            <th class="py-3 ps-4">Parcela</th>
                            <th class="py-3">Productor</th>
                            <th class="py-3 text-center">Extensión</th>
                            <th class="py-3 pe-4 text-center">Acciones</th>
                        </tr>
                    </thead>
                    <tbody id="tableBody">
                        <?php $__empty_1 = true; $__currentLoopData = $parcelas; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $parcela): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); $__empty_1 = false; ?>
                        <tr class="border-bottom">
                            <td class="ps-4">
                                <p class="fw-bold mb-0"><?php echo e($parcela->nom_parcela); ?></p>
                                <small class="text-muted"><i class="fas fa-map-marker-alt me-1"></i><?php echo e($parcela->ubicacion); ?></small>
                            </td>
                            <td>
                                <div class="d-flex align-items-center">
                                    <div class="avatar-circle me-3">
                                        <?php echo e(strtoupper(substr($parcela->productor->persona->nom, 0, 1) . substr($parcela->productor->persona->ap, 0, 1))); ?>

                                    </div>
                                    <div>
                                        <p class="fw-bold mb-0"><?php echo e($parcela->productor->persona->nom); ?> <?php echo e($parcela->productor->persona->ap); ?></p>
                                        <small class="text-muted">ID Prod: <?php echo e($parcela->id_productor); ?></small>
                                    </div>
                                </div>
                            </td>
                            <td class="text-center">
                                <span class="badge rounded-pill extension-badge"><?php echo e($parcela->extension); ?> ha</span>
                            </td>
                            <td class="pe-4 text-center">
                                <div class="btn-group" role="group">
                                    <button class="btn btn-sm btn-icon btn-light" data-bs-toggle="modal" data-bs-target="#viewParcelaModal<?php echo e($parcela->id_parcela); ?>" title="Ver Detalles">
                                        <i class="fas fa-eye" style="color: var(--succulent-medium);"></i>
                                    </button>
                                    <button class="btn btn-sm btn-icon btn-light" data-bs-toggle="modal" data-bs-target="#editParcelaModal<?php echo e($parcela->id_parcela); ?>" title="Editar">
                                        <i class="fas fa-pencil-alt" style="color: var(--succulent-dark);"></i>
                                    </button>
                                    <button type="button" class="btn btn-sm btn-icon btn-light js-delete-btn" title="Eliminar"
                                            data-url="<?php echo e(route('parcelas.destroy', $parcela->id_parcela)); ?>"
                                            data-name="<?php echo e($parcela->nom_parcela); ?>">
                                        <i class="fas fa-trash-alt text-danger"></i>
                                    </button>
                                </div>
                            </td>
                        </tr>
                        <?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); if ($__empty_1): ?>
                        <tr><td colspan="4" class="text-center py-5"><p class="mb-0 text-muted">No hay parcelas registradas.</p></td></tr>
                        <?php endif; ?>
                    </tbody>
                </table>
            </div>
        </div>
    </div>
</div>

<?php $__currentLoopData = $parcelas; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $parcela): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); ?>
<div class="modal fade" id="viewParcelaModal<?php echo e($parcela->id_parcela); ?>" tabindex="-1" aria-hidden="true">
    <div class="modal-dialog modal-dialog-centered">
        <div class="modal-content border-0 shadow-lg">
            <div class="modal-header text-white" style="background-color: var(--succulent-medium);">
                <h5 class="modal-title"><i class="fas fa-info-circle me-2"></i>Detalles de la Parcela</h5>
                <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal"></button>
            </div>
            <div class="modal-body p-4">
                <p><strong class="text-muted">Nombre:</strong> <span class="fs-5 fw-bold"><?php echo e($parcela->nom_parcela); ?></span></p>
                <p><strong class="text-muted">Ubicación:</strong> <?php echo e($parcela->ubicacion); ?></p>
                <p><strong class="text-muted">Productor:</strong> <?php echo e($parcela->productor->persona->nom); ?> <?php echo e($parcela->productor->persona->ap); ?></p>
                <p><strong class="text-muted">Extensión:</strong> <?php echo e($parcela->extension); ?> hectáreas</p>
                <hr>
                <p><strong class="text-muted">Dirección:</strong> <?php echo e($parcela->direccion); ?></p>
                <p><strong class="text-muted">Código Postal:</strong> <?php echo e($parcela->CP); ?></p>
            </div>
        </div>
    </div>
</div>

<div class="modal fade" id="editParcelaModal<?php echo e($parcela->id_parcela); ?>" tabindex="-1" aria-hidden="true">
    <div class="modal-dialog modal-dialog-centered modal-lg">
        <div class="modal-content border-0 shadow-lg">
            <div class="modal-header text-white" style="background-color: var(--succulent-dark);">
                <h5 class="modal-title"><i class="fas fa-edit me-2"></i>Editar Parcela</h5>
                <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal"></button>
            </div>
            <div class="modal-body p-4">
                <form method="POST" action="<?php echo e(route('parcelas.update', $parcela->id_parcela)); ?>">
                    <?php echo csrf_field(); ?>
                    <?php echo method_field('PUT'); ?>
                    <div class="row">
                        <div class="col-md-6 mb-3"><div class="form-floating"><input type="text" name="nom_parcela" class="form-control" value="<?php echo e($parcela->nom_parcela); ?>" required placeholder="Nombre"><label>Nombre de la Parcela*</label></div></div>
                        <div class="col-md-6 mb-3"><div class="form-floating"><input type="text" name="ubicacion" class="form-control" value="<?php echo e($parcela->ubicacion); ?>" required placeholder="Ubicación"><label>Ubicación*</label></div></div>
                    </div>
                    <div class="mb-3"><div class="form-floating">
                        <select name="id_productor" class="form-select" required>
                            <?php $__currentLoopData = $productores; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $productor): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); ?><option value="<?php echo e($productor->id_productor); ?>" <?php echo e($productor->id_productor == $parcela->id_productor ? 'selected' : ''); ?>><?php echo e($productor->persona->nom); ?> <?php echo e($productor->persona->ap); ?></option><?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); ?>
                        </select><label>Productor*</label>
                    </div></div>
                    <div class="row">
                        <div class="col-md-6 mb-3"><div class="form-floating"><input type="number" step="0.01" name="extension" class="form-control" value="<?php echo e($parcela->extension); ?>" required placeholder="Extensión"><label>Extensión (hectáreas)*</label></div></div>
                        <div class="col-md-6 mb-3"><div class="form-floating"><input type="number" name="CP" class="form-control" value="<?php echo e($parcela->CP); ?>" required placeholder="C.P."><label>Código Postal*</label></div></div>
                    </div>
                    <div class="form-floating mb-3"><textarea name="direccion" class="form-control" placeholder="Dirección" style="height: 100px"><?php echo e($parcela->direccion); ?></textarea><label>Dirección</label></div>
                    <div class="d-flex justify-content-end mt-4">
                        <button type="button" class="btn btn-secondary me-2" data-bs-dismiss="modal">Cancelar</button>
                        <button type="submit" class="btn text-white" style="background-color: var(--succulent-dark);"><i class="fas fa-save me-2"></i>Guardar Cambios</button>
                    </div>
                </form>
            </div>
        </div>
    </div>
</div>
<?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); ?>

<div class="modal fade" id="createParcelaModal" tabindex="-1" aria-hidden="true">
    <div class="modal-dialog modal-dialog-centered modal-lg">
        <div class="modal-content border-0 shadow-lg">
            <div class="modal-header text-white" style="background-color: var(--succulent-medium);">
                <h5 class="modal-title"><i class="fas fa-plus-circle me-2"></i>Nueva Parcela</h5>
                <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal"></button>
            </div>
            <div class="modal-body p-4">
                 <form method="POST" action="<?php echo e(route('parcelas.store')); ?>">
                    <?php echo csrf_field(); ?>
                    <div class="row">
                        <div class="col-md-6 mb-3"><div class="form-floating"><input type="text" name="nom_parcela" class="form-control" required placeholder="Nombre"><label>Nombre de la Parcela*</label></div></div>
                        <div class="col-md-6 mb-3"><div class="form-floating"><input type="text" name="ubicacion" class="form-control" required placeholder="Ubicación"><label>Ubicación*</label></div></div>
                    </div>
                    <div class="mb-3"><div class="form-floating">
                        <select name="id_productor" class="form-select" required>
                            <option value="" disabled selected>Selecciona un productor...</option>
                            <?php $__currentLoopData = $productores; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $productor): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); ?><option value="<?php echo e($productor->id_productor); ?>"><?php echo e($productor->persona->nom); ?> <?php echo e($productor->persona->ap); ?></option><?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); ?>
                        </select><label>Productor*</label>
                    </div></div>
                    <div class="row">
                        <div class="col-md-6 mb-3"><div class="form-floating"><input type="number" step="0.01" name="extension" class="form-control" required placeholder="Extensión"><label>Extensión (hectáreas)*</label></div></div>
                        <div class="col-md-6 mb-3"><div class="form-floating"><input type="number" name="CP" class="form-control" required placeholder="C.P."><label>Código Postal*</label></div></div>
                    </div>
                    <div class="form-floating mb-3"><textarea name="direccion" class="form-control" placeholder="Dirección" style="height: 100px"></textarea><label>Dirección</label></div>
                    <div class="d-flex justify-content-end mt-4">
                        <button type="button" class="btn btn-secondary me-2" data-bs-dismiss="modal">Cancelar</button>
                        <button type="submit" class="btn text-white" style="background-color: var(--succulent-medium);"><i class="fas fa-check-circle me-2"></i>Crear Parcela</button>
                    </div>
                </form>
            </div>
        </div>
    </div>
</div>
<?php $__env->stopSection(); ?>

<?php $__env->startPush('scripts'); ?>

<script src="https://cdn.jsdelivr.net/npm/sweetalert2@11"></script>
<script>
document.addEventListener('DOMContentLoaded', function() {
    // Inicializar Tooltips de Bootstrap
    const tooltipTriggerList = [].slice.call(document.querySelectorAll('[title]'));
    tooltipTriggerList.map(tooltipTriggerEl => new bootstrap.Tooltip(tooltipTriggerEl));

    // Lógica de Búsqueda en la Tabla
    const searchInput = document.getElementById('searchInput');
    const tableBody = document.getElementById('tableBody');
    const tableRows = tableBody.getElementsByTagName('tr');
    searchInput.addEventListener('keyup', () => {
        const filter = searchInput.value.toLowerCase();
        for (const row of tableRows) {
            row.style.display = (row.textContent || row.innerText).toLowerCase().includes(filter) ? "" : "none";
        }
    });

    // Lógica de Eliminación con SweetAlert2
    document.querySelectorAll('.js-delete-btn').forEach(button => {
        button.addEventListener('click', function() {
            const url = this.dataset.url;
            const name = this.dataset.name;
            Swal.fire({
                title: '¿Estás seguro?',
                html: `Se eliminará la parcela <strong>${name}</strong>.`,
                icon: 'warning',
                showCancelButton: true,
                confirmButtonColor: '#d33', cancelButtonColor: '#6c757d',
                confirmButtonText: 'Sí, eliminar', cancelButtonText: 'Cancelar'
            }).then((result) => {
                if (result.isConfirmed) {
                    const form = document.createElement('form');
                    form.method = 'POST';
                    form.action = url;
                    form.innerHTML = `<?php echo csrf_field(); ?> <?php echo method_field('DELETE'); ?>`;
                    document.body.appendChild(form);
                    form.submit();
                }
            });
        });
    });
});
</script>
<?php $__env->stopPush(); ?>
<?php echo $__env->make('dashboard', array_diff_key(get_defined_vars(), ['__data' => 1, '__path' => 1]))->render(); ?><?php /**PATH /home/u535412430/domains/woodwise.me/woodwise_src/resources/views/parcelas/index1.blade.php ENDPATH**/ ?>