@extends('dashboard')

@section('template_title', 'Gestión de Técnicos')

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
        .cedula-badge {
            background-color: var(--succulent-lightest);
            color: var(--succulent-dark);
            font-family: monospace;
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
                    <i class="fas fa-hard-hat fa-2x me-3"></i>
                    <div>
                        <h5 class="mb-0 fw-bold text-white">Gestión de Técnicos</h5>
                        <small>Administra a los técnicos forestales del sistema</small>
                    </div>
                </div>
            </div>
            <div class="header-actions d-flex align-items-center w-100 w-md-auto">
                <div class="input-group me-3 search-bar rounded-pill shadow-sm bg-white">
                    <span class="input-group-text bg-transparent border-0"><i class="fas fa-search"></i></span>
                    <input type="text" id="searchInput" class="form-control border-0" placeholder="Buscar técnico...">
                </div>
                <button class="btn rounded-pill flex-shrink-0" data-bs-toggle="modal" data-bs-target="#createTechnicianModal" style="background-color: var(--succulent-lightest); color: var(--succulent-dark);">
                    <i class="fas fa-plus me-2"></i>Nuevo Técnico
                </button>
            </div>
        </div>
        
        <div class="card-body p-0">
            <div class="table-responsive">
                <table class="table table-hover align-middle mb-0">
                    <thead class="bg-light">
                        <tr>
                            <th class="py-3 ps-4">Técnico</th>
                            <th class="py-3">Cédula / Contacto</th>
                            <th class="py-3 text-center">Registro</th>
                            <th class="py-3 pe-4 text-center">Acciones</th>
                        </tr>
                    </thead>
                    <tbody id="tableBody">
                        @forelse ($tecnicos as $tecnico)
                        <tr class="border-bottom">
                            <td class="ps-4">
                                <div class="d-flex align-items-center">
                                    <div class="avatar-circle me-3">
                                        {{ strtoupper(substr($tecnico->persona->nom, 0, 1) . substr($tecnico->persona->ap, 0, 1)) }}
                                    </div>
                                    <div>
                                        <p class="fw-bold mb-0">{{ $tecnico->persona->nom }} {{ $tecnico->persona->ap }}</p>
                                        <small class="text-muted">{{ $tecnico->persona->correo }}</small>
                                    </div>
                                </div>
                            </td>
                            <td>
                                <p class="mb-1"><span class="badge rounded-pill cedula-badge">{{ $tecnico->cedula_p ?? 'N/A' }}</span></p>
                                <p class="mb-0 text-muted small"><i class="fas fa-phone me-2"></i>{{ $tecnico->persona->telefono }}</p>
                            </td>
                            <td class="text-center">{{ $tecnico->created_at?->format('d/m/Y') ?? 'N/A' }}</td>
                            <td class="pe-4 text-center">
                                <div class="btn-group" role="group">
                                    <button class="btn btn-sm btn-icon btn-light" data-bs-toggle="modal" data-bs-target="#viewTechnicianModal{{ $tecnico->id_tecnico }}" title="Ver Detalles">
                                        <i class="fas fa-eye" style="color: var(--succulent-medium);"></i>
                                    </button>
                                    <button class="btn btn-sm btn-icon btn-light" data-bs-toggle="modal" data-bs-target="#editTechnicianModal{{ $tecnico->id_tecnico }}" title="Editar">
                                        <i class="fas fa-pencil-alt" style="color: var(--succulent-dark);"></i>
                                    </button>
                                    <button type="button" class="btn btn-sm btn-icon btn-light js-delete-btn" title="Eliminar"
                                            data-url="{{ route('tecnicos.destroy', $tecnico->id_tecnico) }}"
                                            data-name="{{ $tecnico->persona->nom }} {{ $tecnico->persona->ap }}">
                                        <i class="fas fa-trash-alt text-danger"></i>
                                    </button>
                                </div>
                            </td>
                        </tr>
                        @empty
                        <tr><td colspan="4" class="text-center py-5"><p class="mb-0 text-muted">No hay técnicos registrados.</p></td></tr>
                        @endforelse
                    </tbody>
                </table>
            </div>
        </div>
    </div>
</div>

@foreach ($tecnicos as $tecnico)
<div class="modal fade" id="viewTechnicianModal{{ $tecnico->id_tecnico }}" tabindex="-1" aria-hidden="true">
    <div class="modal-dialog modal-dialog-centered">
        <div class="modal-content border-0 shadow-lg">
            <div class="modal-header text-white" style="background-color: var(--succulent-medium);">
                <h5 class="modal-title"><i class="fas fa-info-circle me-2"></i>Detalles del Técnico</h5>
                <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal"></button>
            </div>
            <div class="modal-body p-4">
                <p><strong class="text-muted">ID Técnico:</strong> <span class="fs-5 fw-bold">{{ $tecnico->id_tecnico }}</span></p>
                <p><strong class="text-muted">Nombre:</strong> {{ $tecnico->persona->nom }} {{ $tecnico->persona->ap }} {{ $tecnico->persona->am }}</p>
                <p><strong class="text-muted">Cédula Profesional:</strong> {{ $tecnico->cedula_p ?? 'No registrada' }}</p>
                <hr>
                <p><strong class="text-muted">Correo:</strong> {{ $tecnico->persona->correo }}</p>
                <p><strong class="text-muted">Teléfono:</strong> {{ $tecnico->persona->telefono }}</p>
            </div>
        </div>
    </div>
</div>

<div class="modal fade" id="editTechnicianModal{{ $tecnico->id_tecnico }}" tabindex="-1" aria-hidden="true">
    <div class="modal-dialog modal-dialog-centered">
        <div class="modal-content border-0 shadow-lg">
            <div class="modal-header text-white" style="background-color: var(--succulent-dark);">
                <h5 class="modal-title"><i class="fas fa-edit me-2"></i>Editar Técnico</h5>
                <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal"></button>
            </div>
            <div class="modal-body p-4">
                <form method="POST" action="{{ route('tecnicos.update', $tecnico->id_tecnico) }}">
                    @csrf
                    @method('PUT')
                    <div class="form-floating mb-3">
                        <select name="id_persona" class="form-select" required>
                            @foreach ($personas as $persona)
                            <option value="{{ $persona->id_persona }}" {{ $persona->id_persona == $tecnico->id_persona ? 'selected' : '' }}>
                                {{ $persona->nom }} {{ $persona->ap }}
                            </option>
                            @endforeach
                        </select>
                        <label>Persona Asociada*</label>
                    </div>
                    <div class="form-floating mb-3">
                        <input type="text" name="cedula_p" class="form-control" value="{{ $tecnico->cedula_p }}" placeholder="Cédula">
                        <label>Cédula Profesional</label>
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

<div class="modal fade" id="createTechnicianModal" tabindex="-1" aria-hidden="true">
    <div class="modal-dialog modal-dialog-centered">
        <div class="modal-content border-0 shadow-lg">
            <div class="modal-header text-white" style="background-color: var(--succulent-medium);">
                <h5 class="modal-title"><i class="fas fa-plus-circle me-2"></i>Nuevo Técnico</h5>
                <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal"></button>
            </div>
            <div class="modal-body p-4">
                <form method="POST" action="{{ route('tecnicos.store') }}">
                    @csrf
                    <div class="form-floating mb-3">
                        <select name="id_persona" class="form-select" required>
                            <option value="" disabled selected>Selecciona una persona...</option>
                            @foreach ($personas as $persona)
                            <option value="{{ $persona->id_persona }}">{{ $persona->nom }} {{ $persona->ap }}</option>
                            @endforeach
                        </select>
                        <label>Persona a designar como técnico*</label>
                    </div>
                    <div class="form-floating mb-3">
                        <input type="text" name="cedula_p" class="form-control" placeholder="Cédula Profesional">
                        <label>Cédula Profesional</label>
                    </div>
                    <div class="d-flex justify-content-end mt-4">
                        <button type="button" class="btn btn-secondary me-2" data-bs-dismiss="modal">Cancelar</button>
                        <button type="submit" class="btn text-white" style="background-color: var(--succulent-medium);"><i class="fas fa-check-circle me-2"></i>Crear Técnico</button>
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
                html: `Se eliminará al técnico <strong>${name}</strong>.`,
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