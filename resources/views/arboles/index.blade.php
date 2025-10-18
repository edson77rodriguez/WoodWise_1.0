@extends('dashboard')

@section('template_title', 'Gestión de Árboles Forestales')

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
        }
    </style>
@endpush

@section('crud_content')
<div class="container-fluid py-4">
    <div class="card border-0 shadow-lg">
        <div class="card-header bg-gradient-succulent text-white d-flex flex-column flex-md-row justify-content-between align-items-center p-4">
            <div class="header-title mb-3 mb-md-0">
                <div class="d-flex align-items-center">
                    <i class="fas fa-tree fa-2x me-3"></i>
                    <div>
                        <h5 class="mb-0 fw-bold text-white">Gestión de Árboles</h5>
                        <small>Administra los árboles individuales de cada parcela</small>
                    </div>
                </div>
            </div>
            <div class="header-actions d-flex align-items-center w-100 w-md-auto">
                <form method="GET" action="{{ route('arboles.index') }}" class="input-group me-3 rounded-pill shadow-sm bg-white">
                    <span class="input-group-text bg-transparent border-0"><i class="fas fa-search"></i></span>
                    <input type="text" name="search" class="form-control border-0" placeholder="Buscar por ID..." value="{{ request('search') }}">
                </form>
                <button class="btn rounded-pill flex-shrink-0" data-bs-toggle="modal" data-bs-target="#createArbolModal" style="background-color: var(--succulent-lightest); color: var(--succulent-dark);">
                    <i class="fas fa-plus me-2"></i>Nuevo Árbol
                </button>
            </div>
        </div>
        
        <div class="card-body p-0">
            <div class="table-responsive">
                <table class="table table-hover align-middle mb-0">
                    <thead class="bg-light">
                        <tr>
                            <th class="py-3 ps-4">Árbol (ID y Especie)</th>
                            <th class="py-3">Mediciones</th>
                            <th class="py-3">Ubicación</th>
                            <th class="py-3 pe-4 text-center">Acciones</th>
                        </tr>
                    </thead>
                    <tbody>
                        @forelse ($arboles as $arbol)
                        <tr class="border-bottom">
                            <td class="ps-4">
                                <p class="fw-bold mb-1">ID: <span class="font-monospace">{{ $arbol->id_arbol }}</span></p>
                                <small class="text-muted"><i class="fas fa-seedling me-2" style="color: var(--succulent-medium);"></i>{{ $arbol->especie->nom_comun }}</small>
                            </td>
                            <td>
                                <p class="mb-1"><span class="data-label">Altura:</span> <span class="data-value">{{ number_format($arbol->altura_total, 2) }} m</span></p>
                                <p class="mb-0"><span class="data-label">DAP:</span> <span class="data-value">{{ number_format($arbol->diametro_pecho, 2) }} cm</span></p>
                            </td>
                            <td>
                                <p class="mb-0"><i class="fas fa-draw-polygon me-2 text-muted"></i>{{ $arbol->parcela->nom_parcela }}</p>
                            </td>
                            <td class="pe-4 text-center">
                                <div class="btn-group" role="group">
                                    <button class="btn btn-sm btn-icon btn-light" data-bs-toggle="modal" data-bs-target="#viewArbolModal{{ $arbol->id_arbol }}" title="Ver Detalles">
                                        <i class="fas fa-eye" style="color: var(--succulent-medium);"></i>
                                    </button>
                                    <button class="btn btn-sm btn-icon btn-light" data-bs-toggle="modal" data-bs-target="#editArbolModal{{ $arbol->id_arbol }}" title="Editar">
                                        <i class="fas fa-pencil-alt" style="color: var(--succulent-dark);"></i>
                                    </button>
                                    <button type="button" class="btn btn-sm btn-icon btn-light js-delete-btn" title="Eliminar"
                                            data-url="{{ route('arboles.destroy', $arbol->id_arbol) }}"
                                            data-name="el árbol #{{ $arbol->id_arbol }}">
                                        <i class="fas fa-trash-alt text-danger"></i>
                                    </button>
                                </div>
                            </td>
                        </tr>
                        @empty
                        <tr><td colspan="4" class="text-center py-5"><p class="mb-0 text-muted">No hay árboles registrados.</p></td></tr>
                        @endforelse
                    </tbody>
                </table>
            </div>
             @if($arboles->hasPages())
                <div class="p-3 border-top">{{ $arboles->appends(request()->query())->links('pagination::bootstrap-5') }}</div>
            @endif
        </div>
    </div>
</div>

@foreach ($arboles as $arbol)
<div class="modal fade" id="viewArbolModal{{ $arbol->id_arbol }}" tabindex="-1" aria-hidden="true">
    <div class="modal-dialog modal-dialog-centered">
        <div class="modal-content border-0 shadow-lg">
            <div class="modal-header text-white" style="background-color: var(--succulent-medium);">
                <h5 class="modal-title"><i class="fas fa-info-circle me-2"></i>Detalles del Árbol #{{ $arbol->id_arbol }}</h5>
                <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal"></button>
            </div>
            <div class="modal-body p-4">
                <p><strong class="text-muted">Altura Total:</strong> <span class="fs-5 fw-bold">{{ number_format($arbol->altura_total, 2) }} metros</span></p>
                <p><strong class="text-muted">Diámetro a la Altura del Pecho (DAP):</strong> <span class="fs-5 fw-bold">{{ number_format($arbol->diametro_pecho, 2) }} cm</span></p>
                <hr>
                <p><strong class="text-muted">Especie:</strong> {{ $arbol->especie->nom_comun }} (<em>{{ $arbol->especie->nom_cientifico }}</em>)</p>
                <p><strong class="text-muted">Parcela:</strong> {{ $arbol->parcela->nom_parcela }}</p>
            </div>
        </div>
    </div>
</div>

<div class="modal fade" id="editArbolModal{{ $arbol->id_arbol }}" tabindex="-1" aria-hidden="true">
    <div class="modal-dialog modal-dialog-centered">
        <div class="modal-content border-0 shadow-lg">
            <div class="modal-header text-white" style="background-color: var(--succulent-dark);">
                <h5 class="modal-title"><i class="fas fa-edit me-2"></i>Editar Árbol</h5>
                <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal"></button>
            </div>
            <div class="modal-body p-4">
                <form method="POST" action="{{ route('arboles.update', $arbol->id_arbol) }}">
                    @csrf
                    @method('PUT')
                    <div class="row">
                        <div class="col-md-6 mb-3"><div class="form-floating"><input type="number" step="0.01" name="altura_total" class="form-control" value="{{ $arbol->altura_total }}" required placeholder="0.00"><label>Altura Total (m)*</label></div></div>
                        <div class="col-md-6 mb-3"><div class="form-floating"><input type="number" step="0.01" name="diametro_pecho" class="form-control" value="{{ $arbol->diametro_pecho }}" required placeholder="0.00"><label>DAP (cm)*</label></div></div>
                    </div>
                    <div class="row">
                        <div class="col-md-6 mb-3"><div class="form-floating"><select name="id_especie" class="form-select" required>@foreach ($especies as $especie)<option value="{{ $especie->id_especie }}" {{ $arbol->id_especie == $especie->id_especie ? 'selected' : '' }}>{{ $especie->nom_comun }}</option>@endforeach</select><label>Especie*</label></div></div>
                        <div class="col-md-6 mb-3"><div class="form-floating"><select name="id_parcela" class="form-select" required>@foreach ($parcelas as $parcela)<option value="{{ $parcela->id_parcela }}" {{ $arbol->id_parcela == $parcela->id_parcela ? 'selected' : '' }}>{{ $parcela->nom_parcela }}</option>@endforeach</select><label>Parcela*</label></div></div>
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

<div class="modal fade" id="createArbolModal" tabindex="-1" aria-hidden="true">
    <div class="modal-dialog modal-dialog-centered">
        <div class="modal-content border-0 shadow-lg">
            <div class="modal-header text-white" style="background-color: var(--succulent-medium);">
                <h5 class="modal-title"><i class="fas fa-plus-circle me-2"></i>Nuevo Árbol</h5>
                <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal"></button>
            </div>
            <div class="modal-body p-4">
                <form method="POST" action="{{ route('arboles.store') }}">
                    @csrf
                    <div class="row">
                        <div class="col-md-6 mb-3"><div class="form-floating"><input type="number" step="0.01" name="altura_total" class="form-control" required placeholder="0.00"><label>Altura Total (m)*</label></div></div>
                        <div class="col-md-6 mb-3"><div class="form-floating"><input type="number" step="0.01" name="diametro_pecho" class="form-control" required placeholder="0.00"><label>DAP (cm)*</label></div></div>
                    </div>
                    <div class="row">
                        <div class="col-md-6 mb-3"><div class="form-floating"><select name="id_especie" class="form-select" required><option value="" disabled selected>Selecciona...</option>@foreach ($especies as $especie)<option value="{{ $especie->id_especie }}">{{ $especie->nom_comun }}</option>@endforeach</select><label>Especie*</label></div></div>
                        <div class="col-md-6 mb-3"><div class="form-floating"><select name="id_parcela" class="form-select" required><option value="" disabled selected>Selecciona...</option>@foreach ($parcelas as $parcela)<option value="{{ $parcela->id_parcela }}">{{ $parcela->nom_parcela }}</option>@endforeach</select><label>Parcela*</label></div></div>
                    </div>
                    <div class="d-flex justify-content-end mt-4">
                        <button type="button" class="btn btn-secondary me-2" data-bs-dismiss="modal">Cancelar</button>
                        <button type="submit" class="btn text-white" style="background-color: var(--succulent-medium);"><i class="fas fa-check-circle me-2"></i>Registrar Árbol</button>
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