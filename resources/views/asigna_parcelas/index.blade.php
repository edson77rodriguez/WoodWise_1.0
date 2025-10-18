@extends('dashboard')

@section('template_title', 'Asignación de Parcelas a Técnicos')

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
    </style>
@endpush

@section('crud_content')
<div class="container-fluid py-4">
    <div class="card border-0 shadow-lg">
        <div class="card-header bg-gradient-succulent text-white d-flex flex-column flex-md-row justify-content-between align-items-center p-4">
            <div class="header-title mb-3 mb-md-0">
                <div class="d-flex align-items-center">
                    <i class="fas fa-project-diagram fa-2x me-3"></i>
                    <div>
                        <h5 class="mb-0 fw-bold text-white">Asignaciones de Parcelas</h5>
                        <small>Asigna técnicos a las parcelas de los productores</small>
                    </div>
                </div>
            </div>
            <div class="header-actions d-flex align-items-center w-100 w-md-auto">
                 <div class="input-group me-3 search-bar rounded-pill shadow-sm bg-white">
                    <span class="input-group-text bg-transparent border-0"><i class="fas fa-search"></i></span>
                    <input type="text" id="searchInput" class="form-control border-0" placeholder="Buscar...">
                </div>
                <button class="btn rounded-pill flex-shrink-0" data-bs-toggle="modal" data-bs-target="#createAsignacionModal" style="background-color: var(--succulent-lightest); color: var(--succulent-dark);">
                    <i class="fas fa-plus me-2"></i>Nueva Asignación
                </button>
            </div>
        </div>
        
        <div class="card-body p-0">
            <div class="table-responsive">
                <table class="table table-hover align-middle mb-0">
                    <thead class="bg-light">
                        <tr>
                            <th class="py-3 ps-4">Técnico Asignado</th>
                            <th class="py-3">Parcela Asignada</th>
                            <th class="py-3 text-center">Fecha de Asignación</th>
                            <th class="py-3 pe-4 text-center">Acciones</th>
                        </tr>
                    </thead>
                    <tbody id="tableBody">
                        @forelse ($asignaciones as $asignacion)
                        <tr class="border-bottom">
                            <td class="ps-4">
                                <div class="d-flex align-items-center">
                                    <div class="avatar-circle me-3">
                                        {{ strtoupper(substr($asignacion->tecnico->persona->nom, 0, 1) . substr($asignacion->tecnico->persona->ap, 0, 1)) }}
                                    </div>
                                    <div>
                                        <p class="fw-bold mb-0">{{ $asignacion->tecnico->persona->nom_completo }}</p>
                                        <small class="text-muted">Cédula: {{ $asignacion->tecnico->cedula_p ?? 'N/A' }}</small>
                                    </div>
                                </div>
                            </td>
                            <td>
                                <p class="fw-bold mb-0">{{ $asignacion->parcela->nom_parcela }}</p>
                                <small class="text-muted"><i class="fas fa-user me-2"></i>{{ $asignacion->parcela->productor->persona->nom_completo }}</small>
                            </td>
                            <td class="text-center">{{ $asignacion->created_at?->format('d/m/Y') ?? 'N/A' }}</td>
                            <td class="pe-4 text-center">
                                <div class="btn-group" role="group">
                                    <button class="btn btn-sm btn-icon btn-light" data-bs-toggle="modal" data-bs-target="#viewAsignacionModal{{ $asignacion->id_asigna_p }}" title="Ver Detalles">
                                        <i class="fas fa-eye" style="color: var(--succulent-medium);"></i>
                                    </button>
                                    <button class="btn btn-sm btn-icon btn-light" data-bs-toggle="modal" data-bs-target="#editAsignacionModal{{ $asignacion->id_asigna_p }}" title="Editar">
                                        <i class="fas fa-pencil-alt" style="color: var(--succulent-dark);"></i>
                                    </button>
                                    <button type="button" class="btn btn-sm btn-icon btn-light js-delete-btn" title="Eliminar"
                                            data-url="{{ route('asigna_parcelas.destroy', $asignacion->id_asigna_p) }}"
                                            data-name="la asignación #{{ $asignacion->id_asigna_p }}">
                                        <i class="fas fa-trash-alt text-danger"></i>
                                    </button>
                                </div>
                            </td>
                        </tr>
                        @empty
                        <tr><td colspan="4" class="text-center py-5"><p class="mb-0 text-muted">No hay asignaciones registradas.</p></td></tr>
                        @endforelse
                    </tbody>
                </table>
            </div>
        </div>
    </div>
</div>

@foreach ($asignaciones as $asignacion)
<div class="modal fade" id="viewAsignacionModal{{ $asignacion->id_asigna_p }}" tabindex="-1" aria-hidden="true">
    <div class="modal-dialog modal-dialog-centered">
        <div class="modal-content border-0 shadow-lg">
            <div class="modal-header text-white" style="background-color: var(--succulent-medium);">
                <h5 class="modal-title"><i class="fas fa-info-circle me-2"></i>Detalles de Asignación</h5>
                <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal"></button>
            </div>
            <div class="modal-body p-4">
                <p><strong class="text-muted">ID Asignación:</strong> <span class="fs-5 fw-bold">{{ $asignacion->id_asigna_p }}</span></p>
                <hr>
                <h6>Técnico</h6>
                <p class="ps-3"><strong class="text-muted">Nombre:</strong> {{ $asignacion->tecnico->persona->nom_completo }}</p>
                <p class="ps-3"><strong class="text-muted">Cédula:</strong> {{ $asignacion->tecnico->cedula_p ?? 'N/A' }}</p>
                <hr>
                <h6>Parcela</h6>
                <p class="ps-3"><strong class="text-muted">Nombre:</strong> {{ $asignacion->parcela->nom_parcela }}</p>
                <p class="ps-3"><strong class="text-muted">Productor:</strong> {{ $asignacion->parcela->productor->persona->nom_completo }}</p>
            </div>
        </div>
    </div>
</div>

<div class="modal fade" id="editAsignacionModal{{ $asignacion->id_asigna_p }}" tabindex="-1" aria-hidden="true">
    <div class="modal-dialog modal-dialog-centered">
        <div class="modal-content border-0 shadow-lg">
            <div class="modal-header text-white" style="background-color: var(--succulent-dark);">
                <h5 class="modal-title"><i class="fas fa-edit me-2"></i>Editar Asignación</h5>
                <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal"></button>
            </div>
            <div class="modal-body p-4">
                <form method="POST" action="{{ route('asigna_parcelas.update', $asignacion->id_asigna_p) }}">
                    @csrf
                    @method('PUT')
                    <div class="form-floating mb-3">
                        <select name="id_tecnico" class="form-select" required>
                            @foreach ($tecnicos as $tecnico)<option value="{{ $tecnico->id_tecnico }}" {{ $tecnico->id_tecnico == $asignacion->id_tecnico ? 'selected' : '' }}>{{ $tecnico->persona->nom_completo }}</option>@endforeach
                        </select>
                        <label>Técnico*</label>
                    </div>
                    <div class="form-floating mb-3">
                         <select name="id_parcela" class="form-select" required>
                            @foreach ($parcelas as $parcela)<option value="{{ $parcela->id_parcela }}" {{ $parcela->id_parcela == $asignacion->id_parcela ? 'selected' : '' }}>{{ $parcela->nom_parcela }} (Prod: {{ $parcela->productor->persona->ap }})</option>@endforeach
                        </select>
                        <label>Parcela*</label>
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

<div class="modal fade" id="createAsignacionModal" tabindex="-1" aria-hidden="true">
    <div class="modal-dialog modal-dialog-centered">
        <div class="modal-content border-0 shadow-lg">
            <div class="modal-header text-white" style="background-color: var(--succulent-medium);">
                <h5 class="modal-title"><i class="fas fa-plus-circle me-2"></i>Nueva Asignación</h5>
                <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal"></button>
            </div>
            <div class="modal-body p-4">
                <form method="POST" action="{{ route('asigna_parcelas.store') }}">
                    @csrf
                    <div class="form-floating mb-3">
                        <select name="id_tecnico" class="form-select" required>
                            <option value="" disabled selected>Selecciona un técnico...</option>
                            @foreach ($tecnicos as $tecnico)<option value="{{ $tecnico->id_tecnico }}">{{ $tecnico->persona->nom_completo }}</option>@endforeach
                        </select>
                        <label>Técnico*</label>
                    </div>
                     <div class="form-floating mb-3">
                        <select name="id_parcela" class="form-select" required>
                            <option value="" disabled selected>Selecciona una parcela...</option>
                            @foreach ($parcelas as $parcela)<option value="{{ $parcela->id_parcela }}">{{ $parcela->nom_parcela }} (Prod: {{ $parcela->productor->persona->ap }})</option>@endforeach
                        </select>
                        <label>Parcela*</label>
                    </div>
                    <div class="d-flex justify-content-end mt-4">
                        <button type="button" class="btn btn-secondary me-2" data-bs-dismiss="modal">Cancelar</button>
                        <button type="submit" class="btn text-white" style="background-color: var(--succulent-medium);"><i class="fas fa-check-circle me-2"></i>Crear Asignación</button>
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
                html: `Se eliminará <strong>${name}</strong>.`,
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