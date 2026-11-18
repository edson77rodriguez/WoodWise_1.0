<?php $__env->startSection('template_title', 'Gestión de Estimaciones de Árboles'); ?>

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
        .table-hover tbody tr:hover { background-color: var(--succulent-lightest); }
        .btn-icon {
            width: 35px; height: 35px; display: inline-flex;
            align-items: center; justify-content: center;
            padding: 0; border-radius: 50% !important;
            margin: 0 2px; border: 1px solid #dee2e6;
        }
        .btn-icon:hover { background-color: #e9ecef; }
        .data-label {
            font-size: 0.75rem;
            color: #6c757d;
            text-transform: uppercase;
        }
        .data-value {
            font-weight: 600;
            font-family: monospace;
            font-size: 1.1em;
            color: var(--succulent-dark);
        }
        .badge.bg-type { background-color: var(--succulent-medium); }
    </style>
<?php $__env->stopPush(); ?>

<?php $__env->startSection('crud_content'); ?>
<div class="container-fluid py-4">
    <div class="card border-0 shadow-lg">
        <div class="card-header bg-gradient-succulent text-white d-flex flex-column flex-md-row justify-content-between align-items-center p-4">
            <div class="header-title mb-3 mb-md-0">
                <div class="d-flex align-items-center">
                    <i class="fas fa-chart-line fa-2x me-3"></i>
                    <div>
                        <h5 class="mb-0 fw-bold text-white">Estimaciones de Árboles</h5>
                        <small>Consulta y administra los cálculos de biomasa y carbono</small>
                    </div>
                </div>
            </div>
             <div class="header-actions d-flex align-items-center w-100 w-md-auto">
                <form method="GET" action="<?php echo e(route('estimaciones1.index')); ?>" class="input-group me-3 rounded-pill shadow-sm bg-white">
                    <span class="input-group-text bg-transparent border-0"><i class="fas fa-search"></i></span>
                    <input type="text" name="search" class="form-control border-0" placeholder="Buscar por ID..." value="<?php echo e(request('search')); ?>">
                </form>
                <button class="btn rounded-pill flex-shrink-0" data-bs-toggle="modal" data-bs-target="#createEstimacionModal" style="background-color: var(--succulent-lightest); color: var(--succulent-dark);">
                    <i class="fas fa-plus me-2"></i>Nueva Estimación
                </button>
            </div>
        </div>
        
        <div class="card-body p-0">
            <div class="table-responsive">
                <table class="table table-hover align-middle mb-0">
                    <thead class="bg-light">
                        <tr>
                            <th class="py-3 ps-4">Estimación</th>
                            <th class="py-3">Árbol de Origen</th>
                            <th class="py-3">Biomasa / Carbono</th>
                            <th class="py-3 pe-4 text-center">Acciones</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php $__empty_1 = true; $__currentLoopData = $estimaciones; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $estimacion): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); $__empty_1 = false; ?>
                        <tr class="border-bottom">
                            <td class="ps-4">
                                <p class="fw-bold mb-1">ID: <span class="font-monospace"><?php echo e($estimacion->id_estimacion1); ?></span></p>
                                <p class="mb-1"><span class="badge rounded-pill text-white bg-type"><?php echo e($estimacion->tipoEstimacion->desc_estimacion); ?></span></p>
                                <small class="text-muted" title="Fórmula usada"><?php echo e($estimacion->formula->nom_formula ?? 'Sin fórmula'); ?></small>
                            </td>
                            <td>
                                <p class="mb-1"><i class="fas fa-tree me-2" style="color: var(--succulent-medium);"></i>Árbol ID: <?php echo e($estimacion->arbol->id_arbol ?? 'N/A'); ?></p>
                                <p class="mb-0 text-muted"><i class="fas fa-seedling me-2"></i><?php echo e($estimacion->arbol->especie->nom_comun ?? 'N/A'); ?> / <i class="fas fa-draw-polygon ms-1 me-2"></i><?php echo e($estimacion->arbol->parcela->nom_parcela ?? 'N/A'); ?></p>
                            </td>
                            <td>
                                <p class="mb-1"><span class="data-label">Biomasa:</span> <span class="data-value"><?php echo e(number_format($estimacion->biomasa ?? $estimacion->calculo, 10)); ?> t</span></p>
                                <p class="mb-0"><span class="data-label">Carbono:</span> <span class="data-value"><?php echo e(number_format($estimacion->carbono ?? 0, 10)); ?> t</span></p>
                            </td>
                            <td class="pe-4 text-center">
                                <div class="btn-group" role="group">
                                    <button class="btn btn-sm btn-icon btn-light" data-bs-toggle="modal" data-bs-target="#viewEstimacionModal<?php echo e($estimacion->id_estimacion1); ?>" title="Ver Detalles">
                                        <i class="fas fa-eye" style="color: var(--succulent-medium);"></i>
                                    </button>
                                    <button class="btn btn-sm btn-icon btn-light" data-bs-toggle="modal" data-bs-target="#editEstimacionModal<?php echo e($estimacion->id_estimacion1); ?>" title="Editar">
                                        <i class="fas fa-pencil-alt" style="color: var(--succulent-dark);"></i>
                                    </button>
                                    <button type="button" class="btn btn-sm btn-icon btn-light js-delete-btn" title="Eliminar"
                                            data-url="<?php echo e(route('estimaciones1.destroy', $estimacion->id_estimacion1)); ?>"
                                            data-name="la estimación #<?php echo e($estimacion->id_estimacion1); ?>">
                                        <i class="fas fa-trash-alt text-danger"></i>
                                    </button>
                                </div>
                            </td>
                        </tr>
                        <?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); if ($__empty_1): ?>
                        <tr><td colspan="4" class="text-center py-5"><p class="mb-0 text-muted">No hay estimaciones registradas.</p></td></tr>
                        <?php endif; ?>
                    </tbody>
                </table>
            </div>
             <?php if($estimaciones->hasPages()): ?>
                <div class="p-3 border-top"><?php echo e($estimaciones->appends(request()->query())->links('pagination::bootstrap-5')); ?></div>
            <?php endif; ?>
        </div>
    </div> 
</div>

<?php $__currentLoopData = $estimaciones; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $estimacion): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); ?>
<div class="modal fade" id="viewEstimacionModal<?php echo e($estimacion->id_estimacion1); ?>" tabindex="-1" aria-hidden="true">
    <div class="modal-dialog modal-dialog-centered modal-lg">
        <div class="modal-content border-0 shadow-lg">
            <div class="modal-header text-white" style="background-color: var(--succulent-medium);">
                <h5 class="modal-title"><i class="fas fa-info-circle me-2"></i>Detalles de Estimación #<?php echo e($estimacion->id_estimacion1); ?></h5>
                <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal"></button>
            </div>
            <div class="modal-body p-4">
                <div class="row">
                    <div class="col-md-6">
                        <h6>Parámetros de Entrada</h6>
                        <hr class="mt-1">
                        <p><strong class="text-muted">Tipo de Estimación:</strong> <?php echo e($estimacion->tipoEstimacion->desc_estimacion); ?></p>
                        <p><strong class="text-muted">Fórmula Utilizada:</strong> <?php echo e($estimacion->formula->nom_formula ?? 'Sin fórmula'); ?></p>
                        <p><strong class="text-muted">Árbol de Origen (ID):</strong> <?php echo e($estimacion->arbol->id_arbol); ?></p>
                        <p><strong class="text-muted">Área Basal:</strong> <?php echo e(number_format($estimacion->area_basal ?? 0, 4)); ?> m²</p>
                    </div>
                    <div class="col-md-6">
                        <h6>Resultados Calculados</h6>
                        <hr class="mt-1">
                        <p><strong class="text-muted">Biomasa:</strong> <span class="fs-5 fw-bold text-success"><?php echo e(number_format($estimacion->biomasa ?? $estimacion->calculo, 10)); ?> t</span></p>
                        <p><strong class="text-muted">Carbono:</strong> <span class="fs-5 fw-bold text-primary"><?php echo e(number_format($estimacion->carbono ?? 0, 10)); ?> t</span></p>
                        <hr>
                        <p><strong class="text-muted">Especie:</strong> <?php echo e($estimacion->arbol->especie->nom_comun ?? 'N/A'); ?></p>
                        <p><strong class="text-muted">Parcela:</strong> <?php echo e($estimacion->arbol->parcela->nom_parcela ?? 'N/A'); ?></p>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>

<div class="modal fade" id="editEstimacionModal<?php echo e($estimacion->id_estimacion1); ?>" tabindex="-1" aria-hidden="true">
    <div class="modal-dialog modal-dialog-centered">
        <div class="modal-content border-0 shadow-lg">
            <div class="modal-header text-white" style="background-color: var(--succulent-dark);">
                <h5 class="modal-title"><i class="fas fa-edit me-2"></i>Editar Estimación</h5>
                <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal"></button>
            </div>
            <div class="modal-body p-4">
                <form method="POST" action="<?php echo e(route('estimaciones1.update', $estimacion->id_estimacion1)); ?>">
                    <?php echo csrf_field(); ?>
                    <?php echo method_field('PUT'); ?>
                    <div class="form-floating mb-3">
                        <select name="id_tipo_e" class="form-select" required>
                            <?php $__currentLoopData = $tiposEstimacion; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $tipo): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); ?><option value="<?php echo e($tipo->id_tipo_e); ?>" <?php echo e($tipo->id_tipo_e == $estimacion->id_tipo_e ? 'selected' : ''); ?>><?php echo e($tipo->desc_estimacion); ?></option><?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); ?>
                        </select>
                        <label>Tipo de Estimación*</label>
                    </div>
                    <div class="form-floating mb-3">
                        <select name="id_arbol" class="form-select edit-arbol-select" data-formula-select="editFormula<?php echo e($estimacion->id_estimacion1); ?>" required>
                           <?php $__currentLoopData = $arboles; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $arbol): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); ?><option value="<?php echo e($arbol->id_arbol); ?>" data-especie="<?php echo e($arbol->id_especie); ?>" <?php echo e($arbol->id_arbol == $estimacion->id_arbol ? 'selected' : ''); ?>>#<?php echo e($arbol->id_arbol); ?> - <?php echo e($arbol->especie->nom_comun); ?></option><?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); ?>
                        </select>
                        <label>Árbol*</label>
                    </div>
                    <div class="form-floating mb-3">
                        <select name="id_formula" id="editFormula<?php echo e($estimacion->id_estimacion1); ?>" class="form-select" required>
                            <?php $__currentLoopData = $formulas; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $formula): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); ?>
                                <?php
                                    $formulaSpecies = collect($formula->especies_relacionadas ?? [])->map(fn ($value) => (int) $value)->values()->all();
                                ?>
                                <option value="<?php echo e($formula->id_formula); ?>" data-species='<?php echo json_encode($formulaSpecies, 15, 512) ?>' <?php echo e($formula->id_formula == $estimacion->id_formula ? 'selected' : ''); ?>><?php echo e($formula->nom_formula); ?></option>
                            <?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); ?>
                        </select>
                        <label>Fórmula* (auto-seleccionada)</label>
                    </div>
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

<div class="modal fade" id="createEstimacionModal" tabindex="-1" aria-hidden="true">
    <div class="modal-dialog modal-dialog-centered">
        <div class="modal-content border-0 shadow-lg">
            <div class="modal-header text-white" style="background-color: var(--succulent-medium);">
                <h5 class="modal-title"><i class="fas fa-plus-circle me-2"></i>Nueva Estimación de Árbol</h5>
                <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal"></button>
            </div>
            <div class="modal-body p-4">
                 <form method="POST" action="<?php echo e(route('estimaciones1.store')); ?>">
                    <?php echo csrf_field(); ?>
                    <div class="form-floating mb-3">
                        <select name="id_tipo_e" id="createTipoE" class="form-select" required>
                            <?php $__currentLoopData = $tiposEstimacion; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $tipo): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); ?>
                                <option value="<?php echo e($tipo->id_tipo_e); ?>" <?php echo e($loop->first ? 'selected' : ''); ?>><?php echo e($tipo->desc_estimacion); ?></option>
                            <?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); ?>
                        </select>
                        <label>Tipo de Estimación*</label>
                    </div>
                    <div class="form-floating mb-3">
                        <select name="id_arbol" id="createArbol" class="form-select" required>
                            <option value="" disabled selected>Selecciona un árbol...</option>
                            <?php $__currentLoopData = $arboles; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $arbol): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); ?>
                                <option value="<?php echo e($arbol->id_arbol); ?>" data-especie="<?php echo e($arbol->id_especie); ?>">#<?php echo e($arbol->id_arbol); ?> - <?php echo e($arbol->especie->nom_comun); ?> (<?php echo e($arbol->parcela->nom_parcela); ?>)</option>
                            <?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); ?>
                        </select>
                        <label>Árbol*</label>
                    </div>
                    <div class="form-floating mb-3">
                        <select name="id_formula" id="createFormula" class="form-select" required>
                            <option value="" disabled selected>Selecciona un árbol primero...</option>
                            <?php $__currentLoopData = $formulas; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $formula): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); ?>
                                <?php
                                    $formulaSpecies = collect($formula->especies_relacionadas ?? [])->map(fn ($value) => (int) $value)->values()->all();
                                ?>
                                <option value="<?php echo e($formula->id_formula); ?>" data-species='<?php echo json_encode($formulaSpecies, 15, 512) ?>'><?php echo e($formula->nom_formula); ?></option>
                            <?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); ?>
                        </select>
                        <label>Fórmula* (auto-seleccionada)</label>
                    </div>
                    <div class="d-flex justify-content-end mt-4">
                        <button type="button" class="btn btn-secondary me-2" data-bs-dismiss="modal">Cancelar</button>
                        <button type="submit" class="btn text-white" style="background-color: var(--succulent-medium);"><i class="fas fa-check-circle me-2"></i>Crear Estimación</button>
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
    // Inicializar Tooltips
    const tooltipTriggerList = [].slice.call(document.querySelectorAll('[title]'));
    tooltipTriggerList.map(tooltipTriggerEl => new bootstrap.Tooltip(tooltipTriggerEl));

    const formulaByArbolUrlTemplate = <?php echo json_encode(route('estimaciones1.formulaPorArbol', ['arbolId' => '__ID__']), 512) ?>;

    function formulaUrlForArbol(arbolId) {
        return formulaByArbolUrlTemplate.replace('__ID__', arbolId);
    }

    function getAllowedFormulaId(speciesId) {
        const formulaMap = <?php echo json_encode($formulaIdsBySpecies ?? [], 15, 512) ?>;
        return formulaMap[String(speciesId)] ?? null;
    }

    function setFormulaOptionsState(formulaSelect, allowedFormulaId) {
        Array.from(formulaSelect.options).forEach(option => {
            if (!option.value) {
                option.disabled = false;
                return;
            }

            option.disabled = allowedFormulaId ? option.value !== String(allowedFormulaId) : true;
        });

        if (allowedFormulaId) {
            formulaSelect.value = String(allowedFormulaId);
        } else {
            formulaSelect.value = '';
        }
    }

    async function autoSelectFormula(arbolSelect, formulaSelect) {
        const arbolId = arbolSelect.value;

        if (!arbolId) {
            setFormulaOptionsState(formulaSelect, null);
            return;
        }

        try {
            const response = await fetch(formulaUrlForArbol(arbolId));
            const data = await response.json();

            const speciesId = data?.especie?.id_especie ?? arbolSelect.selectedOptions[0]?.dataset?.especie;
            const allowedFormulaId = getAllowedFormulaId(speciesId);

            setFormulaOptionsState(formulaSelect, allowedFormulaId);

            if (data.formula_id && String(data.formula_id) === String(allowedFormulaId)) {
                formulaSelect.value = data.formula_id;

                // Destacar visualmente que se auto-seleccionó
                formulaSelect.style.borderColor = 'var(--succulent-medium)';
                formulaSelect.style.boxShadow = '0 0 0 0.2rem rgba(124, 144, 112, 0.25)';

                setTimeout(() => {
                    formulaSelect.style.borderColor = '';
                    formulaSelect.style.boxShadow = '';
                }, 1500);
            }
        } catch (error) {
            console.error('No se pudo auto-seleccionar la fórmula:', error);
            setFormulaOptionsState(formulaSelect, null);
        }
    }

    function bindAutoFormulaSelect(arbolSelect, formulaSelect, modalElement) {
        if (!arbolSelect || !formulaSelect) {
            return;
        }

        const syncFormula = () => autoSelectFormula(arbolSelect, formulaSelect);

        arbolSelect.addEventListener('change', syncFormula);

        setFormulaOptionsState(formulaSelect, null);

        if (modalElement) {
            modalElement.addEventListener('shown.bs.modal', syncFormula);
        }
    }

    // Auto-seleccionar fórmula en modal de CREAR
    const createArbolSelect = document.getElementById('createArbol');
    const createFormulaSelect = document.getElementById('createFormula');
    const createModal = document.getElementById('createEstimacionModal');

    bindAutoFormulaSelect(createArbolSelect, createFormulaSelect, createModal);

    // Auto-seleccionar fórmula en modales de EDITAR
    document.querySelectorAll('.edit-arbol-select').forEach(arbolSelect => {
        const formulaSelectId = arbolSelect.dataset.formulaSelect;
        const formulaSelect = document.getElementById(formulaSelectId);
        const modalElement = arbolSelect.closest('.modal');
        
        bindAutoFormulaSelect(arbolSelect, formulaSelect, modalElement);
    });

    // Lógica de Eliminación
    document.querySelectorAll('.js-delete-btn').forEach(button => {
        button.addEventListener('click', function() {
            const url = this.dataset.url;
            const name = this.dataset.name;
            Swal.fire({
                title: '¿Estás seguro?',
                html: `Se eliminará <strong>${name}</strong>. Esta acción no se puede deshacer.`,
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
<?php echo $__env->make('dashboard', array_diff_key(get_defined_vars(), ['__data' => 1, '__path' => 1]))->render(); ?><?php /**PATH /home/u535412430/domains/woodwise.me/woodwise_src/resources/views/estimaciones1/index.blade.php ENDPATH**/ ?>