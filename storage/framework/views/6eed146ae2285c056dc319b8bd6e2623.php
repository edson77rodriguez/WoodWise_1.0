<?php $__env->startSection('template_title', 'Gestión de Fórmulas'); ?>

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
        .badge.bg-type { background-color: var(--succulent-medium); }
        .badge.bg-catalog { background-color: var(--succulent-light); color: var(--succulent-dark) !important; }
        code.expression {
            font-family: 'Courier New', Courier, monospace;
            background-color: var(--succulent-lightest);
            color: var(--succulent-dark);
            padding: 0.4em 0.6em;
            border-radius: 6px;
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
                    <i class="fas fa-square-root-alt fa-2x me-3"></i>
                    <div>
                        <h5 class="mb-0 fw-bold text-white">Gestión de Fórmulas</h5>
                        <small>Administra las expresiones de cálculo del sistema</small>
                    </div>
                </div>
            </div>
            <div class="d-flex">
                <button class="btn me-2 rounded-pill" data-bs-toggle="modal" data-bs-target="#createFormulaModal" style="background-color: var(--succulent-lightest); color: var(--succulent-dark);">
                    <i class="fas fa-plus me-2"></i>Nueva Fórmula
                </button>
                <button class="btn btn-outline-light rounded-pill" data-bs-toggle="modal" data-bs-target="#helpFormulaModal">
                    <i class="fas fa-question-circle me-2"></i>Ayuda
                </button>
            </div>
        </div>
        
        <div class="card-body p-0">
            <div class="p-3 border-bottom bg-light">
                <div class="row g-2 align-items-center">
                    <div class="col-md-4"><input type="text" id="searchInput" class="form-control" placeholder="Buscar por nombre o expresión..."></div>
                    <div class="col-md-3">
                        <select id="filterType" class="form-select"><option value="">Todos los tipos</option><?php $__currentLoopData = $tiposEstimacion; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $tipo): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); ?><option value="<?php echo e($tipo->id_tipo_e); ?>"><?php echo e($tipo->desc_estimacion); ?></option><?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); ?></select>
                    </div>
                    <div class="col-md-3">
                        <select id="filterCatalog" class="form-select"><option value="">Todos los catálogos</option><?php $__currentLoopData = $catalogos; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $catalogo): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); ?><option value="<?php echo e($catalogo->id_cat); ?>"><?php echo e($catalogo->nom_cat); ?></option><?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); ?></select>
                    </div>
                    <div class="col-md-2"><button id="resetFilters" class="btn btn-outline-secondary w-100"><i class="fas fa-undo me-1"></i> Limpiar</button></div>
                </div>
            </div>
            
            <div class="table-responsive">
                <table class="table table-hover align-middle mb-0">
                    <thead class="bg-light">
                        <tr>
                            <th class="py-3 ps-4">Fórmula</th>
                            <th class="py-3">Tipo / Catálogo</th>
                            <th class="py-3">Estado / Modo</th>
                            <th class="py-3 pe-4 text-center">Acciones</th>
                        </tr>
                    </thead>
                    <tbody id="formulasTableBody">
                        <?php $__empty_1 = true; $__currentLoopData = $formulas; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $formula): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); $__empty_1 = false; ?>
                        <tr data-type="<?php echo e($formula->id_tipo_e); ?>" data-catalog="<?php echo e($formula->id_cat); ?>">
                            <td class="ps-4">
                                <p class="fw-bold mb-1"><?php echo e($formula->nom_formula); ?></p>
                                <code class="expression"><?php echo e($formula->expresion); ?></code>
                            </td>
                            <td>
                                <span class="badge rounded-pill text-white bg-type"><?php echo e($formula->tipoEstimacion->desc_estimacion); ?></span>
                                <span class="badge rounded-pill bg-catalog"><?php echo e($formula->catalogo->nom_cat); ?></span>
                            </td>
                            <td>
                                <span class="badge rounded-pill text-bg-secondary"><?php echo e(ucfirst($formula->estado_revision ?? 'revision')); ?></span>
                                <span class="badge rounded-pill text-bg-info ms-1"><?php echo e(strtoupper($formula->modo_ejecucion ?? 'trigger')); ?></span>
                            </td>
                            <td class="pe-4 text-center">
                                <div class="btn-group" role="group">
                                    <button class="btn btn-sm btn-icon btn-light" data-bs-toggle="modal" data-bs-target="#viewFormulaModal<?php echo e($formula->id_formula); ?>" title="Ver Detalles">
                                        <i class="fas fa-eye" style="color: var(--succulent-medium);"></i>
                                    </button>
                                    <button class="btn btn-sm btn-icon btn-light" data-bs-toggle="modal" data-bs-target="#editFormulaModal<?php echo e($formula->id_formula); ?>" title="Editar Fórmula">
                                        <i class="fas fa-pencil-alt" style="color: var(--succulent-dark);"></i>
                                    </button>
                                    <button type="button" class="btn btn-sm btn-icon btn-light js-delete-btn" title="Eliminar Fórmula"
                                            data-url="<?php echo e(route('formulas.destroy', $formula->id_formula)); ?>"
                                            data-name="<?php echo e($formula->nom_formula); ?>">
                                        <i class="fas fa-trash-alt text-danger"></i>
                                    </button>
                                    <?php if(($formula->estado_revision ?? 'revision') !== 'aprobada'): ?>
                                    <button type="button" class="btn btn-sm btn-icon btn-light js-approve-btn" title="Aprobar Fórmula"
                                            data-url="<?php echo e(route('formulas.aprobar', $formula->id_formula)); ?>"
                                            data-name="<?php echo e($formula->nom_formula); ?>">
                                        <i class="fas fa-check text-success"></i>
                                    </button>
                                    <button type="button" class="btn btn-sm btn-icon btn-light js-reject-btn" title="Rechazar Fórmula"
                                            data-url="<?php echo e(route('formulas.rechazar', $formula->id_formula)); ?>"
                                            data-name="<?php echo e($formula->nom_formula); ?>">
                                        <i class="fas fa-times text-warning"></i>
                                    </button>
                                    <?php endif; ?>
                                </div>
                            </td>
                        </tr>
                        <?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); if ($__empty_1): ?>
                        <tr><td colspan="3" class="text-center py-5"><p class="mb-0 text-muted">No hay fórmulas registradas.</p></td></tr>
                        <?php endif; ?>
                    </tbody>
                </table>
            </div>
             <?php if($formulas->hasPages()): ?>
                <div class="p-3 border-top"><?php echo e($formulas->links()); ?></div>
            <?php endif; ?>
        </div>
    </div>
</div>

<?php echo $__env->make('formulas.modal-help', array_diff_key(get_defined_vars(), ['__data' => 1, '__path' => 1]))->render(); ?>
<?php echo $__env->make('formulas.modal-create', ['tiposEstimacion' => $tiposEstimacion, 'catalogos' => $catalogos, 'especies' => $especies], array_diff_key(get_defined_vars(), ['__data' => 1, '__path' => 1]))->render(); ?>
<?php $__currentLoopData = $formulas; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $formula): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); ?>
    <?php echo $__env->make('formulas.modal-view', ['formula' => $formula], array_diff_key(get_defined_vars(), ['__data' => 1, '__path' => 1]))->render(); ?>
    <?php echo $__env->make('formulas.modal-edit', ['formula' => $formula, 'tiposEstimacion' => $tiposEstimacion, 'catalogos' => $catalogos, 'especies' => $especies], array_diff_key(get_defined_vars(), ['__data' => 1, '__path' => 1]))->render(); ?>
<?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); ?>

<?php $__env->stopSection(); ?>

<?php $__env->startPush('scripts'); ?>

<script src="https://cdn.jsdelivr.net/npm/sweetalert2@11"></script>
<script src="https://cdnjs.cloudflare.com/ajax/libs/mathjs/11.7.0/math.min.js"></script>
<script>
document.addEventListener('DOMContentLoaded', function() {
    // 1. Lógica de Búsqueda y Filtros
    const searchInput = document.getElementById('searchInput');
    const filterType = document.getElementById('filterType');
    const filterCatalog = document.getElementById('filterCatalog');
    const resetFiltersBtn = document.getElementById('resetFilters');
    const tableBody = document.getElementById('formulasTableBody');
    const tableRows = tableBody.getElementsByTagName('tr');

    function applyFilters() {
        const searchTerm = searchInput.value.toLowerCase();
        const typeFilter = filterType.value;
        const catalogFilter = filterCatalog.value;
        
        for (const row of tableRows) {
            const name = row.cells[0].textContent.toLowerCase();
            const type = row.dataset.type;
            const catalog = row.dataset.catalog;
            
            const matchesSearch = name.includes(searchTerm);
            const matchesType = typeFilter === '' || type === typeFilter;
            const matchesCatalog = catalogFilter === '' || catalog === catalogFilter;
            
            row.style.display = (matchesSearch && matchesType && matchesCatalog) ? '' : 'none';
        }
    }

    searchInput.addEventListener('keyup', applyFilters);
    filterType.addEventListener('change', applyFilters);
    filterCatalog.addEventListener('change', applyFilters);
    resetFiltersBtn.addEventListener('click', () => {
        searchInput.value = '';
        filterType.value = '';
        filterCatalog.value = '';
        applyFilters();
    });

    // 2. Lógica para Probar Expresiones
    window.testExpression = function(textareaId) {
    const textarea = document.getElementById(textareaId);
    const expression = textarea.value.trim();

    if (!expression) {
        Swal.fire('Expresión vacía', 'Por favor ingrese una expresión matemática.', 'warning');
        return;
    }

    const form = textarea.closest('form');
    const token = form
        ? form.querySelector('input[name="_token"]')?.value
        : document.querySelector('meta[name="csrf-token"]')?.content;

    Swal.fire({
        title: 'Probando...',
        allowOutsideClick: false,
        didOpen: () => Swal.showLoading(),
    });

    fetch('<?php echo e(route("formulas.validarExpresion")); ?>', {
        method: 'POST',
        headers: {
            'Content-Type': 'application/json',
            'Accept': 'application/json',
            'X-CSRF-TOKEN': token,
        },
        body: JSON.stringify({ expresion: expression }),
    })
        .then(response => response.json())
        .then(data => {
            if (data.valida) {
                Swal.fire(
                    'Expresión Válida',
                    `Resultado con variables de prueba (valor = 1): <strong>${data.resultado_prueba}</strong>`,
                    'success'
                );
            } else {
                Swal.fire('Error en la Expresión', data.mensaje, 'error');
            }
        })
        .catch(() => {
            Swal.fire('Error', 'No se pudo contactar al servidor para validar la expresión.', 'error');
        });
};

    window.buildVariableRow = function(tableBody, variable = {}) {
        const row = document.createElement('tr');
        row.dataset.variableRow = 'true';
        row.innerHTML = `
            <td><input type="text" class="form-control form-control-sm js-var-name" placeholder="D" value="${variable.name ?? ''}"></td>
            <td><input type="text" class="form-control form-control-sm js-var-label" placeholder="Diámetro" value="${variable.label ?? ''}"></td>
            <td>
                <select class="form-select form-select-sm js-var-source">
                    <option value="">Manual</option>
                    <option value="diametro_pecho">diametro_pecho</option>
                    <option value="altura_total">altura_total</option>
                    <option value="longitud">longitud</option>
                    <option value="diametro">diametro</option>
                    <option value="diametro_otro_extremo">diametro_otro_extremo</option>
                    <option value="diametro_medio">diametro_medio</option>
                    <option value="densidad">densidad</option>
                </select>
            </td>
            <td><input type="number" step="any" class="form-control form-control-sm js-var-default" placeholder="0" value="${variable.default ?? ''}"></td>
            <td class="text-center"><input type="checkbox" class="form-check-input js-var-required" ${variable.required ? 'checked' : ''}></td>
            <td class="text-center"><button type="button" class="btn btn-sm btn-outline-danger js-remove-var-row">x</button></td>
        `;

        const sourceSelect = row.querySelector('.js-var-source');
        sourceSelect.value = variable.source ?? '';

        row.querySelector('.js-remove-var-row').addEventListener('click', () => row.remove());
        tableBody.appendChild(row);
    };

    function syncVariableSchema(form) {
        const rows = form.querySelectorAll('[data-variable-row="true"]');
        const schema = Array.from(rows).map(row => ({
            name: row.querySelector('.js-var-name').value.trim(),
            label: row.querySelector('.js-var-label').value.trim(),
            source: row.querySelector('.js-var-source').value,
            default: row.querySelector('.js-var-default').value,
            required: row.querySelector('.js-var-required').checked,
        })).filter(item => item.name !== '');

        const hidden = form.querySelector('.js-variables-schema');
        if (hidden) {
            hidden.value = JSON.stringify(schema);
        }
    }

    function initFormulaForm(form) {
        const tableBody = form.querySelector('.js-variable-rows');
        const addButton = form.querySelector('.js-add-variable-row');
        const initialVariables = JSON.parse(form.dataset.initialVariables || '[]');
        const catalogSelect = form.querySelector('[name="id_cat"]');
        const treeOnlyBlock = form.querySelector('.formula-tree-only');
        const trozaNotice = form.querySelector('.formula-troza-only');
        const biomassFactorInput = form.querySelector('[name="biomasa_factor"]');
        const speciesSelect = form.querySelector('[name="especies_relacionadas[]"]');

        function toggleFormulaSections() {
            const isTreeFormula = catalogSelect && catalogSelect.value === '2';

            if (treeOnlyBlock) {
                treeOnlyBlock.classList.toggle('d-none', !isTreeFormula);
            }

            if (trozaNotice) {
                trozaNotice.classList.toggle('d-none', isTreeFormula);
            }

            if (biomassFactorInput) {
                biomassFactorInput.value = isTreeFormula ? (biomassFactorInput.value || '1') : '';
            }

            if (speciesSelect && !isTreeFormula) {
                Array.from(speciesSelect.options).forEach(option => option.selected = false);
            }
        }

        if (tableBody && tableBody.children.length === 0) {
            if (initialVariables.length) {
                initialVariables.forEach(variable => buildVariableRow(tableBody, variable));
            } else {
                buildVariableRow(tableBody, {});
            }
        }

        if (addButton && tableBody) {
            addButton.addEventListener('click', () => buildVariableRow(tableBody, {}));
        }

        if (catalogSelect) {
            catalogSelect.addEventListener('change', toggleFormulaSections);
            toggleFormulaSections();
        }

        form.addEventListener('submit', () => syncVariableSchema(form));
    }

    document.querySelectorAll('[data-formula-form]').forEach(initFormulaForm);
    
    // 3. Lógica de Eliminación con SweetAlert2
    document.querySelectorAll('.js-delete-btn').forEach(button => {
        button.addEventListener('click', function() {
            const url = this.dataset.url;
            const name = this.dataset.name;
            Swal.fire({
                title: '¿Estás seguro?',
                html: `Se eliminará la fórmula <strong>${name}</strong>.`,
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

    document.querySelectorAll('.js-approve-btn').forEach(button => {
        button.addEventListener('click', function() {
            Swal.fire({
                title: 'Aprobar fórmula',
                html: `Se aprobará <strong>${this.dataset.name}</strong>.`,
                icon: 'question',
                showCancelButton: true,
                confirmButtonText: 'Sí, aprobar',
                cancelButtonText: 'Cancelar'
            }).then((result) => {
                if (result.isConfirmed) {
                    const form = document.createElement('form');
                    form.method = 'POST';
                    form.action = this.dataset.url;
                    form.innerHTML = `<?php echo csrf_field(); ?>`;
                    document.body.appendChild(form);
                    form.submit();
                }
            });
        });
    });

    document.querySelectorAll('.js-reject-btn').forEach(button => {
        button.addEventListener('click', function() {
            Swal.fire({
                title: 'Rechazar fórmula',
                input: 'textarea',
                inputLabel: 'Notas de revisión',
                inputPlaceholder: 'Explica por qué se rechaza...',
                showCancelButton: true,
                confirmButtonText: 'Rechazar',
                cancelButtonText: 'Cancelar'
            }).then((result) => {
                if (result.isConfirmed) {
                    const form = document.createElement('form');
                    form.method = 'POST';
                    form.action = this.dataset.url;
                    form.innerHTML = `<?php echo csrf_field(); ?> <input type="hidden" name="revision_notas" value="${(result.value || '').replace(/"/g, '&quot;')}">`;
                    document.body.appendChild(form);
                    form.submit();
                }
            });
        });
    });
});
</script>
<?php $__env->stopPush(); ?>
<?php echo $__env->make('dashboard', array_diff_key(get_defined_vars(), ['__data' => 1, '__path' => 1]))->render(); ?><?php /**PATH /home/u535412430/domains/woodwise.me/woodwise_src/resources/views/formulas/index.blade.php ENDPATH**/ ?>