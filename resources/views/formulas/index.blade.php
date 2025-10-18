@extends('dashboard')

@section('template_title', 'Gestión de Fórmulas')

@push('styles')
    {{-- CSS mejorado con la nueva paleta de colores y estilos para la tabla --}}
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
@endpush

@section('crud_content')
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
                        <select id="filterType" class="form-select"><option value="">Todos los tipos</option>@foreach($tiposEstimacion as $tipo)<option value="{{ $tipo->id_tipo_e }}">{{ $tipo->desc_estimacion }}</option>@endforeach</select>
                    </div>
                    <div class="col-md-3">
                        <select id="filterCatalog" class="form-select"><option value="">Todos los catálogos</option>@foreach($catalogos as $catalogo)<option value="{{ $catalogo->id_cat }}">{{ $catalogo->nom_cat }}</option>@endforeach</select>
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
                            <th class="py-3 pe-4 text-center">Acciones</th>
                        </tr>
                    </thead>
                    <tbody id="formulasTableBody">
                        @forelse ($formulas as $formula)
                        <tr data-type="{{ $formula->id_tipo_e }}" data-catalog="{{ $formula->id_cat }}">
                            <td class="ps-4">
                                <p class="fw-bold mb-1">{{ $formula->nom_formula }}</p>
                                <code class="expression">{{ $formula->expresion }}</code>
                            </td>
                            <td>
                                <span class="badge rounded-pill text-white bg-type">{{ $formula->tipoEstimacion->desc_estimacion }}</span>
                                <span class="badge rounded-pill bg-catalog">{{ $formula->catalogo->nom_cat }}</span>
                            </td>
                            <td class="pe-4 text-center">
                                <div class="btn-group" role="group">
                                    <button class="btn btn-sm btn-icon btn-light" data-bs-toggle="modal" data-bs-target="#viewFormulaModal{{ $formula->id_formula }}" title="Ver Detalles">
                                        <i class="fas fa-eye" style="color: var(--succulent-medium);"></i>
                                    </button>
                                    <button class="btn btn-sm btn-icon btn-light" data-bs-toggle="modal" data-bs-target="#editFormulaModal{{ $formula->id_formula }}" title="Editar Fórmula">
                                        <i class="fas fa-pencil-alt" style="color: var(--succulent-dark);"></i>
                                    </button>
                                    <button type="button" class="btn btn-sm btn-icon btn-light js-delete-btn" title="Eliminar Fórmula"
                                            data-url="{{ route('formulas.destroy', $formula->id_formula) }}"
                                            data-name="{{ $formula->nom_formula }}">
                                        <i class="fas fa-trash-alt text-danger"></i>
                                    </button>
                                </div>
                            </td>
                        </tr>
                        @empty
                        <tr><td colspan="3" class="text-center py-5"><p class="mb-0 text-muted">No hay fórmulas registradas.</p></td></tr>
                        @endforelse
                    </tbody>
                </table>
            </div>
             @if($formulas->hasPages())
                <div class="p-3 border-top">{{ $formulas->links() }}</div>
            @endif
        </div>
    </div>
</div>

@include('formulas.modal-help')
@include('formulas.modal-create', ['tiposEstimacion' => $tiposEstimacion, 'catalogos' => $catalogos])
@foreach ($formulas as $formula)
    @include('formulas.modal-view', ['formula' => $formula])
    @include('formulas.modal-edit', ['formula' => $formula, 'tiposEstimacion' => $tiposEstimacion, 'catalogos' => $catalogos])
@endforeach

@endsection

@push('scripts')
{{-- MathJS para probar expresiones y SweetAlert2 para notificaciones --}}
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
        const expression = document.getElementById(textareaId).value.trim();
        if (!expression) {
            Swal.fire('Expresión vacía', 'Por favor ingrese una expresión matemática.', 'warning');
            return;
        }
        try {
            const testScope = { DAP: 1.5, altura: 10, factor: 0.7 };
            const result = math.evaluate(expression, testScope);
            Swal.fire('Expresión Válida', `Resultado con valores de prueba: <strong>${result.toFixed(4)}</strong>`, 'success');
        } catch (error) {
            Swal.fire('Error en la Expresión', `<code>${error.message}</code>`, 'error');
        }
    };
    
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
                    form.innerHTML = `@csrf @method('DELETE')`;
                    document.body.appendChild(form);
                    form.submit();
                }
            });
        });
    });
});
</script>
@endpush