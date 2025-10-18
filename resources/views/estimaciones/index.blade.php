@extends('dashboard')

@section('template_title', 'Gestión de Estimaciones')

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
@endpush

@section('crud_content')
<div class="container-fluid py-4">
    <div class="card border-0 shadow-lg">
        <div class="card-header bg-gradient-succulent text-white d-flex flex-column flex-md-row justify-content-between align-items-center p-4">
            <div class="header-title mb-3 mb-md-0">
                <div class="d-flex align-items-center">
                    <i class="fas fa-chart-bar fa-2x me-3"></i>
                    <div>
                        <h5 class="mb-0 fw-bold text-white">Gestión de Estimaciones</h5>
                        <small>Consulta y administra los cálculos de volumen</small>
                    </div>
                </div>
            </div>
            <button class="btn rounded-pill flex-shrink-0" data-bs-toggle="modal" data-bs-target="#createEstimacionModal" style="background-color: var(--succulent-lightest); color: var(--succulent-dark);">
                <i class="fas fa-plus me-2"></i>Nueva Estimación
            </button>
        </div>
        
        <div class="card-body p-0">
            <div class="table-responsive">
                <table class="table table-hover align-middle mb-0">
                    <thead class="bg-light">
                        <tr>
                            <th class="py-3 ps-4">Estimación</th>
                            <th class="py-3">Origen (Troza)</th>
                            <th class="py-3">Resultados</th>
                            <th class="py-3 pe-4 text-center">Acciones</th>
                        </tr>
                    </thead>
                    <tbody>
                        @forelse ($estimaciones as $estimacion)
                        <tr class="border-bottom">
                            <td class="ps-4">
                                <p class="fw-bold mb-1">ID: <span class="font-monospace">{{ $estimacion->id_estimacion }}</span></p>
                                <p class="mb-1"><span class="badge rounded-pill text-white bg-type">{{ $estimacion->tipoEstimacion->desc_estimacion }}</span></p>
                                <small class="text-muted" title="Fórmula usada">{{ $estimacion->formula->nom_formula }}</small>
                            </td>
                            <td>
                                <p class="mb-1"><i class="fas fa-ruler-horizontal me-2" style="color: var(--succulent-medium);"></i>Troza ID: {{ $estimacion->troza->id_troza ?? 'N/A' }}</p>
                                <p class="mb-0 text-muted"><i class="fas fa-seedling me-2"></i>{{ $estimacion->troza->especie->nom_comun ?? 'N/A' }} / <i class="fas fa-draw-polygon ms-1 me-2"></i>{{ $estimacion->troza->parcela->nom_parcela ?? 'N/A' }}</p>
                            </td>
                            <td>
                                <p class="mb-1"><span class="data-label">Cálculo:</span> <span class="data-value">{{ number_format($estimacion->calculo, 4) }} m³</span></p>
                                <p class="mb-1"><span class="data-label">Biomasa:</span> <span class="data-value">{{ number_format($estimacion->biomasa, 2) }} kg</span></p>
                                <p class="mb-0"><span class="data-label">Carbono:</span> <span class="data-value">{{ number_format($estimacion->carbono, 2) }} kg</span></p>
                            </td>
                            <td class="pe-4 text-center">
                                <div class="btn-group" role="group">
                                    <button class="btn btn-sm btn-icon btn-light" data-bs-toggle="modal" data-bs-target="#viewEstimacionModal{{ $estimacion->id_estimacion }}" title="Ver Detalles">
                                        <i class="fas fa-eye" style="color: var(--succulent-medium);"></i>
                                    </button>
                                    <button class="btn btn-sm btn-icon btn-light" data-bs-toggle="modal" data-bs-target="#editEstimacionModal{{ $estimacion->id_estimacion }}" title="Editar">
                                        <i class="fas fa-pencil-alt" style="color: var(--succulent-dark);"></i>
                                    </button>
                                    <button type="button" class="btn btn-sm btn-icon btn-light js-delete-btn" title="Eliminar"
                                            data-url="{{ route('estimaciones.destroy', $estimacion->id_estimacion) }}"
                                            data-name="la estimación #{{ $estimacion->id_estimacion }}">
                                        <i class="fas fa-trash-alt text-danger"></i>
                                    </button>
                                </div>
                            </td>
                        </tr>
                        @empty
                        <tr><td colspan="4" class="text-center py-5"><p class="mb-0 text-muted">No hay estimaciones registradas.</p></td></tr>
                        @endforelse
                    </tbody>
                </table>
            </div>
             @if($estimaciones->hasPages())
                <div class="p-3 border-top">{{ $estimaciones->links('pagination::bootstrap-5') }}</div>
            @endif
        </div>
    </div>
</div>

@foreach ($estimaciones as $estimacion)
<div class="modal fade" id="viewEstimacionModal{{ $estimacion->id_estimacion }}" tabindex="-1" aria-hidden="true">
    <div class="modal-dialog modal-dialog-centered modal-lg">
        <div class="modal-content border-0 shadow-lg">
            <div class="modal-header text-white" style="background-color: var(--succulent-medium);">
                <h5 class="modal-title"><i class="fas fa-info-circle me-2"></i>Detalles de Estimación #{{ $estimacion->id_estimacion }}</h5>
                <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal"></button>
            </div>
            <div class="modal-body p-4">
                <div class="row">
                    <div class="col-md-6">
                        <h6>Parámetros de Entrada</h6>
                        <hr class="mt-1">
                        <p><strong class="text-muted">Tipo de Estimación:</strong> {{ $estimacion->tipoEstimacion->desc_estimacion }}</p>
                        <p><strong class="text-muted">Fórmula Utilizada:</strong> {{ $estimacion->formula->nom_formula }}</p>
                        <p><strong class="text-muted">Troza de Origen (ID):</strong> {{ $estimacion->troza->id_troza }}</p>
                        <p><strong class="text-muted">Especie:</strong> {{ $estimacion->troza->especie->nom_comun ?? 'N/A' }}</p>
                    </div>
                    <div class="col-md-6">
                        <h6>Resultados Calculados</h6>
                        <hr class="mt-1">
                        <p><strong class="text-muted">Cálculo (Volumen):</strong> <span class="fs-5 fw-bold text-success">{{ number_format($estimacion->calculo, 4) }} m³</span></p>
                        <p><strong class="text-muted">Biomasa:</strong> <span class="fs-5 fw-bold text-success">{{ number_format($estimacion->biomasa, 2) }} kg</span></p>
                        <p><strong class="text-muted">Carbono Almacenado:</strong> <span class="fs-5 fw-bold text-success">{{ number_format($estimacion->carbono, 2) }} kg</span></p>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>

<div class="modal fade" id="editEstimacionModal{{ $estimacion->id_estimacion }}" tabindex="-1" aria-hidden="true">
    <div class="modal-dialog modal-dialog-centered">
        <div class="modal-content border-0 shadow-lg">
            <div class="modal-header text-white" style="background-color: var(--succulent-dark);">
                <h5 class="modal-title"><i class="fas fa-edit me-2"></i>Editar Estimación</h5>
                <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal"></button>
            </div>
            <div class="modal-body p-4">
                <form method="POST" action="{{ route('estimaciones.update', $estimacion->id_estimacion) }}">
                    @csrf
                    @method('PUT')
                    <div class="form-floating mb-3">
                        <select name="id_tipo_e" class="form-select" required>
                            @foreach ($tiposEstimacion as $tipo)<option value="{{ $tipo->id_tipo_e }}" {{ $tipo->id_tipo_e == $estimacion->id_tipo_e ? 'selected' : '' }}>{{ $tipo->desc_estimacion }}</option>@endforeach
                        </select>
                        <label>Tipo de Estimación*</label>
                    </div>
                    <div class="form-floating mb-3">
                        <select name="id_formula" class="form-select" required>
                            @foreach ($formulas as $formula)<option value="{{ $formula->id_formula }}" {{ $formula->id_formula == $estimacion->id_formula ? 'selected' : '' }}>{{ $formula->nom_formula }}</option>@endforeach
                        </select>
                        <label>Fórmula*</label>
                    </div>
                    <div class="form-floating mb-3">
                        <select name="id_troza" class="form-select" required>
                            @foreach ($trozas as $troza)<option value="{{ $troza->id_troza }}" {{ $troza->id_troza == $estimacion->id_troza ? 'selected' : '' }}>ID: {{ $troza->id_troza }} ({{ $troza->especie->nom_comun }})</option>@endforeach
                        </select>
                        <label>Troza*</label>
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
@endforeach

<div class="modal fade" id="createEstimacionModal" tabindex="-1" aria-hidden="true">
    <div class="modal-dialog modal-dialog-centered">
        <div class="modal-content border-0 shadow-lg">
            <div class="modal-header text-white" style="background-color: var(--succulent-medium);">
                <h5 class="modal-title"><i class="fas fa-plus-circle me-2"></i>Nueva Estimación</h5>
                <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal"></button>
            </div>
            <div class="modal-body p-4">
                <form method="POST" action="{{ route('estimaciones.store') }}">
                    @csrf
                    <div class="form-floating mb-3">
                        <select name="id_tipo_e" class="form-select" required>
                            <option value="" disabled selected>Selecciona un tipo...</option>
                            @foreach ($tiposEstimacion as $tipo)<option value="{{ $tipo->id_tipo_e }}">{{ $tipo->desc_estimacion }}</option>@endforeach
                        </select>
                        <label>Tipo de Estimación*</label>
                    </div>
                    <div class="form-floating mb-3">
                        <select name="id_formula" class="form-select" required>
                            <option value="" disabled selected>Selecciona una fórmula...</option>
                            @foreach ($formulas as $formula)<option value="{{ $formula->id_formula }}">{{ $formula->nom_formula }}</option>@endforeach
                        </select>
                        <label>Fórmula*</label>
                    </div>
                    <div class="form-floating mb-3">
                        <select name="id_troza" class="form-select" required>
                            <option value="" disabled selected>Selecciona una troza...</option>
                            @foreach ($trozas as $troza)<option value="{{ $troza->id_troza }}">ID: {{ $troza->id_troza }} ({{ $troza->especie->nom_comun }})</option>@endforeach
                        </select>
                        <label>Troza*</label>
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
@endsection

@push('scripts')
{{-- SweetAlert2 para notificaciones y confirmaciones --}}
<script src="https://cdn.jsdelivr.net/npm/sweetalert2@11"></script>
<script>
document.addEventListener('DOMContentLoaded', function() {
    // Inicializar Tooltips
    const tooltipTriggerList = [].slice.call(document.querySelectorAll('[title]'));
    tooltipTriggerList.map(tooltipTriggerEl => new bootstrap.Tooltip(tooltipTriggerEl));

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