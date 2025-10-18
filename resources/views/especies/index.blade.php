@extends('dashboard')

@section('template_title', 'Gestión de Especies')

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
        .search-bar:focus-within {
            box-shadow: 0 0 0 0.25rem rgba(124, 144, 112, 0.25);
        }
        .avatar-image {
            width: 60px;
            height: 60px;
            border-radius: 8px;
            object-fit: cover;
            flex-shrink: 0;
            border: 2px solid var(--succulent-lightest);
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
    </style>
@endpush

@section('crud_content')
<div class="container-fluid py-4">
    <div class="card border-0 shadow-lg">
        <div class="card-header bg-gradient-succulent text-white d-flex flex-column flex-md-row justify-content-between align-items-center p-4">
            <div class="header-title mb-3 mb-md-0">
                <div class="d-flex align-items-center">
                    <i class="fas fa-seedling fa-2x me-3"></i>
                    <div>
                        <h5 class="mb-0 fw-bold text-white">Gestión de Especies</h5>
                        <small>Administra el catálogo de especies forestales</small>
                    </div>
                </div>
            </div>
            <div class="header-actions d-flex align-items-center w-100 w-md-auto">
                <div class="input-group me-3 search-bar rounded-pill shadow-sm bg-white">
                    <span class="input-group-text bg-transparent border-0"><i class="fas fa-search"></i></span>
                    <input type="text" id="searchInput" class="form-control border-0" placeholder="Buscar especie...">
                </div>
                <button class="btn rounded-pill flex-shrink-0" data-bs-toggle="modal" data-bs-target="#createEspecieModal" style="background-color: var(--succulent-lightest); color: var(--succulent-dark);">
                    <i class="fas fa-plus me-2"></i>Nueva Especie
                </button>
            </div>
        </div>
        
        <div class="card-body p-0">
            <div class="table-responsive">
                <table class="table table-hover align-middle mb-0">
                    <thead class="bg-light">
                        <tr>
                            <th class="py-3 ps-4">Especie</th>
                            <th class="py-3 text-center">Registro</th>
                            <th class="py-3 pe-4 text-center">Acciones</th>
                        </tr>
                    </thead>
                    <tbody id="speciesTableBody">
                        @forelse ($especies as $especie)
                        <tr class="border-bottom">
                            <td class="ps-4">
                                <div class="d-flex align-items-center">
                                    <img src="{{ $especie->imagen ? asset('storage/' . $especie->imagen) : 'https://via.placeholder.com/60' }}" 
                                         class="avatar-image me-3" alt="{{ $especie->nom_comun }}">
                                    <div>
                                        <p class="fw-bold mb-0">{{ $especie->nom_comun }}</p>
                                        <small class="text-muted"><em>{{ $especie->nom_cientifico }}</em></small>
                                    </div>
                                </div>
                            </td>
                            <td class="text-center">{{ $especie->created_at?->format('d/m/Y') ?? 'N/A' }}</td>
                            <td class="pe-4 text-center">
                                <div class="btn-group" role="group">
                                    <button class="btn btn-sm btn-icon btn-light" data-bs-toggle="modal" data-bs-target="#viewEspecieModal{{ $especie->id_especie }}" title="Ver Detalles">
                                        <i class="fas fa-eye" style="color: var(--succulent-medium);"></i>
                                    </button>
                                    <button class="btn btn-sm btn-icon btn-light" data-bs-toggle="modal" data-bs-target="#editEspecieModal{{ $especie->id_especie }}" title="Editar Especie">
                                        <i class="fas fa-pencil-alt" style="color: var(--succulent-dark);"></i>
                                    </button>
                                    <button type="button" class="btn btn-sm btn-icon btn-light js-delete-btn" 
                                            title="Eliminar Especie"
                                            data-url="{{ route('especies.destroy', $especie->id_especie) }}"
                                            data-name="{{ $especie->nom_comun }}">
                                        <i class="fas fa-trash-alt text-danger"></i>
                                    </button>
                                </div>
                            </td>
                        </tr>
                        @empty
                        <tr><td colspan="3" class="text-center py-5"><p class="mb-0 text-muted">No hay especies registradas.</p></td></tr>
                        @endforelse
                    </tbody>
                </table>
            </div>
        </div>
    </div>
</div>

@foreach ($especies as $especie)
    @include('especies.modal-view', ['especie' => $especie])
    @include('especies.modal-edit', ['especie' => $especie])
@endforeach
@include('especies.modal-create')
@endsection

@push('scripts')
{{-- Asegúrate de que SweetAlert2 esté disponible en tu layout principal --}}
<script src="https://cdn.jsdelivr.net/npm/sweetalert2@11"></script>
<script>
document.addEventListener('DOMContentLoaded', function() {
    // 1. Lógica de Búsqueda
    const searchInput = document.getElementById('searchInput');
    const tableBody = document.getElementById('speciesTableBody');
    const tableRows = tableBody.getElementsByTagName('tr');

    searchInput.addEventListener('keyup', function() {
        const filter = searchInput.value.toLowerCase();
        for (const row of tableRows) {
            row.style.display = (row.textContent || row.innerText).toLowerCase().indexOf(filter) > -1 ? "" : "none";
        }
    });

    // 2. Lógica de Eliminación con SweetAlert2
    document.querySelectorAll('.js-delete-btn').forEach(button => {
        button.addEventListener('click', function() {
            const url = this.dataset.url;
            const name = this.dataset.name;

            Swal.fire({
                title: '¿Estás seguro?',
                html: `Se eliminará la especie <strong>${name}</strong>.<br>Esta acción no se puede deshacer.`,
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