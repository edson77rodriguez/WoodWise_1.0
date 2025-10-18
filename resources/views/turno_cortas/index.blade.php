@extends('dashboard')

@section('template_title', 'Gestión de Turnos de Corta')

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
        .avatar-circle {
            width: 45px; height: 45px; border-radius: 50%;
            background-color: var(--succulent-medium); color: white;
            display: flex; align-items: center; justify-content: center;
            font-weight: bold; font-size: 1.1rem; flex-shrink: 0;
        }
        .table-hover tbody tr:hover { background-color: var(--succulent-lightest); }
        .btn-icon {
            width: 35px; height: 35px; display: inline-flex;
            align-items: center; justify-content: center;
            padding: 0; border-radius: 50% !important;
            margin: 0 2px; border: 1px solid #dee2e6;
        }
        .btn-icon:hover { background-color: #e9ecef; }
        .status-badge.in-progress { background-color: var(--succulent-accent); color: var(--succulent-dark); }
        .status-badge.completed { background-color: var(--succulent-dark); color: white; }
    </style>
@endpush

@section('crud_content')
<div class="container-fluid py-4">
    <div class="card border-0 shadow-lg">
        <div class="card-header bg-gradient-succulent text-white d-flex flex-column flex-md-row justify-content-between align-items-center p-4">
            <div class="header-title mb-3 mb-md-0">
                <div class="d-flex align-items-center">
                    <i class="fas fa-clipboard-check fa-2x me-3"></i>
                    <div>
                        <h5 class="mb-0 fw-bold text-white">Turnos de Corta</h5>
                        <small>Planifica y gestiona los periodos de cosecha</small>
                    </div>
                </div>
            </div>
            <div class="d-flex">
                <button class="btn me-2 rounded-pill" data-bs-toggle="modal" data-bs-target="#createTurnoModal" style="background-color: var(--succulent-lightest); color: var(--succulent-dark);">
                    <i class="fas fa-plus me-2"></i>Nuevo Turno
                </button>
            </div>
        </div>
        
        <div class="card-body p-0">
            <div class="p-3 border-bottom bg-light">
                <div class="row g-2 align-items-center">
                    <div class="col-md-8"><input type="text" id="searchInput" class="form-control" placeholder="Buscar por código, parcela o productor..."></div>
                    <div class="col-md-4"><button id="resetFilters" class="btn btn-outline-secondary w-100"><i class="fas fa-undo me-1"></i> Limpiar Búsqueda</button></div>
                </div>
            </div>
            
            <div class="table-responsive">
                <table class="table table-hover align-middle mb-0">
                    <thead class="bg-light">
                        <tr>
                            <th class="py-3 ps-4">Turno / Parcela</th>
                            <th class="py-3">Productor</th>
                            <th class="py-3">Periodo de Corta</th>
                            <th class="py-3 pe-4 text-center">Acciones</th>
                        </tr>
                    </thead>
                    <tbody id="tableBody">
                        @forelse ($turnos as $turno)
                        <tr class="border-bottom">
                            <td class="ps-4">
                                <p class="fw-bold mb-1">Código: <span class="font-monospace">{{ $turno->codigo_corta }}</span></p>
                                <small class="text-muted"><i class="fas fa-draw-polygon me-1"></i>{{ $turno->parcela->nom_parcela }}</small>
                            </td>
                            <td>
                                <div class="d-flex align-items-center">
                                    <div class="avatar-circle me-3">
                                        {{ strtoupper(substr($turno->parcela->productor->persona->nom, 0, 1) . substr($turno->parcela->productor->persona->ap, 0, 1)) }}
                                    </div>
                                    <p class="mb-0">{{ $turno->parcela->productor->persona->nom_completo ?? 'N/A' }}</p>
                                </div>
                            </td>
                            <td>
                                <p class="mb-1">
                                    <i class="fas fa-calendar-check me-2 text-muted"></i>
                                    {{ $turno->fecha_corta->format('d/m/Y') }} - {{ $turno->fecha_fin ? $turno->fecha_fin->format('d/m/Y') : '...' }}
                                </p>
                                <span class="badge rounded-pill status-badge {{ $turno->fecha_fin ? 'completed' : 'in-progress' }}">
                                    {{ $turno->fecha_fin ? 'Finalizado' : 'En Progreso' }}
                                </span>
                            </td>
                            <td class="pe-4 text-center">
                                <div class="btn-group" role="group">
                                    <button class="btn btn-sm btn-icon btn-light" data-bs-toggle="modal" data-bs-target="#viewTurnoModal{{ $turno->id_turno }}" title="Ver Detalles">
                                        <i class="fas fa-eye" style="color: var(--succulent-medium);"></i>
                                    </button>
                                    <button class="btn btn-sm btn-icon btn-light" data-bs-toggle="modal" data-bs-target="#editTurnoModal{{ $turno->id_turno }}" title="Editar">
                                        <i class="fas fa-pencil-alt" style="color: var(--succulent-dark);"></i>
                                    </button>
                                    <button type="button" class="btn btn-sm btn-icon btn-light js-delete-btn" title="Eliminar"
                                            data-url="{{ route('turno_cortas.destroy', $turno->id_turno) }}"
                                            data-name="{{ $turno->codigo_corta }}">
                                        <i class="fas fa-trash-alt text-danger"></i>
                                    </button>
                                </div>
                            </td>
                        </tr>
                        @empty
                        <tr><td colspan="4" class="text-center py-5"><p class="mb-0 text-muted">No hay turnos de corta registrados.</p></td></tr>
                        @endforelse
                    </tbody>
                </table>
            </div>
             @if($turnos->hasPages())
                <div class="p-3 border-top">{{ $turnos->links() }}</div>
            @endif
        </div>
    </div>
</div>

@foreach ($turnos as $turno)
<div class="modal fade" id="viewTurnoModal{{ $turno->id_turno }}" tabindex="-1" aria-hidden="true">
    <div class="modal-dialog modal-dialog-centered">
        <div class="modal-content border-0 shadow-lg">
            <div class="modal-header text-white" style="background-color: var(--succulent-medium);">
                <h5 class="modal-title"><i class="fas fa-info-circle me-2"></i>Detalles del Turno</h5>
                <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal"></button>
            </div>
            <div class="modal-body p-4">
                <p><strong class="text-muted">Código:</strong> <span class="fs-5 fw-bold font-monospace">{{ $turno->codigo_corta }}</span></p>
                <p><strong class="text-muted">Parcela:</strong> {{ $turno->parcela->nom_parcela }}</p>
                <p><strong class="text-muted">Productor:</strong> {{ $turno->parcela->productor->persona->nom_completo ?? 'N/A' }}</p>
                <hr>
                <p><strong class="text-muted">Periodo:</strong> {{ $turno->fecha_corta->format('d/m/Y') }} al {{ $turno->fecha_fin ? $turno->fecha_fin->format('d/m/Y') : 'Presente' }}</p>
                <p><strong class="text-muted">Estado:</strong> <span class="badge rounded-pill status-badge {{ $turno->fecha_fin ? 'completed' : 'in-progress' }}">{{ $turno->fecha_fin ? 'Finalizado' : 'En Progreso' }}</span></p>
            </div>
        </div>
    </div>
</div>

<div class="modal fade" id="editTurnoModal{{ $turno->id_turno }}" tabindex="-1" aria-hidden="true">
    <div class="modal-dialog modal-dialog-centered">
        <div class="modal-content border-0 shadow-lg">
            <div class="modal-header text-white" style="background-color: var(--succulent-dark);">
                <h5 class="modal-title"><i class="fas fa-edit me-2"></i>Editar Turno de Corta</h5>
                <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal"></button>
            </div>
            <div class="modal-body p-4">
                <form method="POST" action="{{ route('turno_cortas.update', $turno->id_turno) }}">
                    @csrf
                    @method('PUT')
                    <div class="form-floating mb-3">
                        <select name="id_parcela" class="form-select" required>
                            @foreach ($parcelas as $parcela)<option value="{{ $parcela->id_parcela }}" {{ $parcela->id_parcela == $turno->id_parcela ? 'selected' : '' }}>{{ $parcela->nom_parcela }}</option>@endforeach
                        </select>
                        <label>Parcela*</label>
                    </div>
                     <div class="row">
                        <div class="col-md-6 mb-3"><div class="form-floating"><input type="date" name="fecha_corta" class="form-control" value="{{ $turno->fecha_corta->format('Y-m-d') }}" required><label>Fecha de Inicio*</label></div></div>
                        <div class="col-md-6 mb-3"><div class="form-floating"><input type="date" name="fecha_fin" class="form-control" value="{{ $turno->fecha_fin ? $turno->fecha_fin->format('Y-m-d') : '' }}"><label>Fecha de Fin (Opcional)</label></div></div>
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

<div class="modal fade" id="createTurnoModal" tabindex="-1" aria-hidden="true">
    <div class="modal-dialog modal-dialog-centered">
        <div class="modal-content border-0 shadow-lg">
            <div class="modal-header text-white" style="background-color: var(--succulent-medium);">
                <h5 class="modal-title"><i class="fas fa-plus-circle me-2"></i>Nuevo Turno de Corta</h5>
                <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal"></button>
            </div>
            <div class="modal-body p-4">
                <form method="POST" action="{{ route('turno_cortas.store') }}">
                    @csrf
                    <div class="form-floating mb-3">
                        <select name="id_parcela" class="form-select" required>
                            <option value="" disabled selected>Selecciona una parcela...</option>
                            @foreach ($parcelas as $parcela)<option value="{{ $parcela->id_parcela }}">{{ $parcela->nom_parcela }} (Productor: {{ $parcela->productor->persona->nom_completo ?? 'N/A' }})</option>@endforeach
                        </select>
                        <label>Parcela*</label>
                    </div>
                     <div class="row">
                        <div class="col-md-6 mb-3"><div class="form-floating"><input type="date" name="fecha_corta" class="form-control" value="{{ date('Y-m-d') }}" required><label>Fecha de Inicio*</label></div></div>
                        <div class="col-md-6 mb-3"><div class="form-floating"><input type="date" name="fecha_fin" class="form-control"><label>Fecha de Fin (Opcional)</label></div></div>
                    </div>
                    <div class="d-flex justify-content-end mt-4">
                        <button type="button" class="btn btn-secondary me-2" data-bs-dismiss="modal">Cancelar</button>
                        <button type="submit" class="btn text-white" style="background-color: var(--succulent-medium);"><i class="fas fa-check-circle me-2"></i>Crear Turno</button>
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
    // Inicializar Tooltips de Bootstrap
    const tooltipTriggerList = [].slice.call(document.querySelectorAll('[title]'));
    tooltipTriggerList.map(tooltipTriggerEl => new bootstrap.Tooltip(tooltipTriggerEl));

    // Lógica de Búsqueda y Filtros
    const searchInput = document.getElementById('searchInput');
    const tableBody = document.getElementById('tableBody');
    const tableRows = tableBody.getElementsByTagName('tr');
    const resetFiltersBtn = document.getElementById('resetFilters');

    function applyFilters() {
        const filter = searchInput.value.toLowerCase();
        for (const row of tableRows) {
            row.style.display = (row.textContent || row.innerText).toLowerCase().includes(filter) ? "" : "none";
        }
    }

    searchInput.addEventListener('keyup', applyFilters);
    resetFiltersBtn.addEventListener('click', () => {
        searchInput.value = '';
        applyFilters();
    });

    // Lógica de Eliminación con SweetAlert2
    document.querySelectorAll('.js-delete-btn').forEach(button => {
        button.addEventListener('click', function() {
            const url = this.dataset.url;
            const name = this.dataset.name;
            Swal.fire({
                title: '¿Estás seguro?',
                html: `Se eliminará el turno con código <strong>${name}</strong>.`,
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