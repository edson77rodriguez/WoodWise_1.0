@extends('dashboard')

@section('template_title', 'Panel de Gestión de Usuarios')

@push('styles')
    {{-- CSS mejorado con la nueva paleta de colores "Succulent Tones" --}}
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
        .header-actions .search-bar:focus-within {
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
            width: 35px;
            height: 35px;
            display: inline-flex;
            align-items: center;
            justify-content: center;
            padding: 0;
            border-radius: 50% !important;
            margin: 0 2px;
            border: 1px solid #dee2e6;
        }
        .btn-icon:hover {
            background-color: #e9ecef;
        }
        .badge.bg-role-admin { background-color: var(--succulent-dark); }
        .badge.bg-role-tecnico { background-color: var(--succulent-medium); }
        .badge.bg-role-productor { background-color: var(--succulent-accent); color: var(--succulent-dark) !important; }
    </style>
@endpush

@section('crud_content')
<div class="container-fluid py-4">
    <div class="card border-0 shadow-lg">
        <div class="card-header bg-gradient-succulent text-white d-flex flex-column flex-md-row justify-content-between align-items:center p-4">
            <div class="header-title mb-3 mb-md-0">
                <div class="d-flex align-items-center">
                    <i class="fas fa-user-shield fa-2x me-3"></i>
                    <div>
                        <h5 class="mb-0 fw-bold text-white">Administración de Usuarios</h5>
                        <small>Gestiona, crea y edita los usuarios del sistema</small>
                    </div>
                </div>
            </div>
            <div class="header-actions d-flex align-items-center w-100 w-md-auto">
                <div class="input-group me-3 search-bar rounded-pill shadow-sm bg-white">
                    <span class="input-group-text bg-transparent border-0"><i class="fas fa-search"></i></span>
                    <input type="text" id="userSearchInput" class="form-control border-0" placeholder="Buscar usuario...">
                </div>
                <button class="btn rounded-pill flex-shrink-0" data-bs-toggle="modal" data-bs-target="#createUserModal" style="background-color: var(--succulent-lightest); color: var(--succulent-dark);">
                    <i class="fas fa-user-plus me-2"></i>Nuevo Usuario
                </button>
            </div>
        </div>
        
        <div class="card-body p-0">
            <div class="table-responsive">
                <table class="table table-hover align-middle mb-0">
                    <thead class="bg-light">
                        <tr>
                            <th class="py-3 ps-4">Usuario</th>
                            <th class="py-3">Contacto</th>
                            <th class="py-3">Rol</th>
                            <th class="py-3 text-center">Registro</th>
                            <th class="py-3 pe-4 text-center">Acciones</th>
                        </tr>
                    </thead>
                    <tbody id="userTableBody">
                        @forelse ($personas as $persona)
                        <tr class="border-bottom">
                            <td class="ps-4">
                                <div class="d-flex align-items-center">
                                    <div class="avatar-circle me-3">
                                        {{ strtoupper(substr($persona->nom, 0, 1) . substr($persona->ap, 0, 1)) }}
                                    </div>
                                    <div>
                                        <p class="fw-bold mb-0">{{ $persona->nom }} {{ $persona->ap }}</p>
                                        <small class="text-muted">ID: {{ $persona->id_persona }}</small>
                                    </div>
                                </div>
                            </td>
                            <td>
                                <p class="mb-0"><i class="fas fa-envelope me-2 text-muted"></i>{{ $persona->correo }}</p>
                                <p class="mb-0"><i class="fas fa-phone me-2 text-muted"></i>{{ $persona->telefono }}</p>
                            </td>
                            <td>
                                @php
                                    $rol = $persona->rol->nom_rol;
                                    $rolClass = 'bg-role-default';
                                    $rolIcon = 'fa-user-tag';
                                    if (stripos($rol, 'Administrador') !== false) {
                                        $rolClass = 'bg-role-admin'; $rolIcon = 'fa-user-shield';
                                    } elseif (stripos($rol, 'Tecnico') !== false) {
                                        $rolClass = 'bg-role-tecnico'; $rolIcon = 'fa-user-cog';
                                    } elseif (stripos($rol, 'Productor') !== false) {
                                        $rolClass = 'bg-role-productor'; $rolIcon = 'fa-user';
                                    }
                                @endphp
                                <span class="badge rounded-pill fs-6 text-white {{ $rolClass }}">
                                    <i class="fas {{ $rolIcon }} me-1"></i> {{ $rol }}
                                </span>
                            </td>
                            <td class="text-center">{{ $persona->created_at?->format('d/m/Y') ?? 'N/A' }}</td>
                            <td class="pe-4 text-center">
                                <div class="btn-group" role="group">
                                    <button class="btn btn-sm btn-icon btn-light" data-bs-toggle="modal" data-bs-target="#viewUserModal{{ $persona->id_persona }}" title="Ver Detalles">
                                        <i class="fas fa-eye" style="color: var(--succulent-medium);"></i>
                                    </button>
                                    <button class="btn btn-sm btn-icon btn-light" data-bs-toggle="modal" data-bs-target="#editUserModal{{ $persona->id_persona }}" title="Editar Usuario">
                                        <i class="fas fa-pencil-alt" style="color: var(--succulent-dark);"></i>
                                    </button>
                                    
                                    {{-- CAMBIO: Se elimina onsubmit y se prepara el botón para el script --}}
                                    <form action="{{ route('usuarios.destroy', $persona->id_persona) }}" method="POST" class="d-inline">
                                        @csrf
                                        @method('DELETE')
                                        <button type="button" class="btn btn-sm btn-icon btn-light js-delete-btn" 
                                                title="Eliminar Usuario"
                                                data-user-name="{{ $persona->nom }} {{ $persona->ap }}">
                                            <i class="fas fa-trash-alt text-danger"></i>
                                        </button>
                                    </form>
                                </div>
                            </td>
                        </tr>
                        @empty
                        <tr><td colspan="5" class="text-center py-5"><p class="mb-0 text-muted">No hay usuarios registrados.</p></td></tr>
                        @endforelse
                    </tbody>
                </table>
            </div>
        </div>
    </div>
</div>

@foreach ($personas as $persona)
    @include('usuarios.modal-view', ['persona' => $persona])
    @include('usuarios.modal-edit', ['persona' => $persona, 'roles' => $roles])
@endforeach
@include('usuarios.modal-create', ['roles' => $roles])
@endsection

@push('scripts')
{{-- Asegúrate de que SweetAlert2 esté disponible en tu layout principal o agrégalo aquí --}}
<script src="https://cdn.jsdelivr.net/npm/sweetalert2@11"></script>
<script>
document.addEventListener('DOMContentLoaded', function() {
    // 1. Lógica de Búsqueda
    const searchInput = document.getElementById('userSearchInput');
    const tableBody = document.getElementById('userTableBody');
    const tableRows = tableBody.getElementsByTagName('tr');

    searchInput.addEventListener('keyup', function() {
        const filter = searchInput.value.toLowerCase();
        for (const row of tableRows) {
            const textContent = row.textContent || row.innerText;
            row.style.display = textContent.toLowerCase().indexOf(filter) > -1 ? "" : "none";
        }
    });

    // 2. Lógica para campo Cédula en modal de CREACIÓN
    const rolSelectCreate = document.getElementById('rolSelect');
    if (rolSelectCreate) {
        const cedulaFieldCreate = document.getElementById('cedulaField');
        const handleRolChange = () => {
            const selectedOptionText = rolSelectCreate.options[rolSelectCreate.selectedIndex].text.toLowerCase();
            cedulaFieldCreate.style.display = selectedOptionText.includes('tecnico') ? 'block' : 'none';
        };
        rolSelectCreate.addEventListener('change', handleRolChange);
        handleRolChange();
    }
    
    // 3. NUEVA LÓGICA DE ELIMINACIÓN CON SWEETALERT2
    document.querySelectorAll('.js-delete-btn').forEach(button => {
        button.addEventListener('click', function(e) {
            e.preventDefault();
            
            const userName = this.dataset.userName;
            const form = this.closest('form');

            Swal.fire({
                title: '¿Estás seguro?',
                html: `Se eliminará al usuario <strong>${userName}</strong>.<br>Esta acción no se puede deshacer.`,
                icon: 'warning',
                showCancelButton: true,
                confirmButtonColor: '#d33',
                cancelButtonColor: '#6c757d',
                confirmButtonText: '<i class="fas fa-trash-alt me-2"></i>Sí, eliminar',
                cancelButtonText: 'Cancelar',
                customClass: {
                    popup: 'rounded-3',
                    confirmButton: 'btn btn-danger',
                    cancelButton: 'btn btn-secondary ms-2'
                },
                buttonsStyling: false
            }).then((result) => {
                if (result.isConfirmed) {
                    form.submit();
                }
            });
        });
    });
});
</script>
@endpush