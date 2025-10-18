@extends('dashboard')

@section('template_title', 'Gestión de Trozas Forestales')

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
            letter-spacing: 0.5px;
        }
        .data-value {
            font-weight: 600;
            font-family: monospace;
            font-size: 1.1em;
        }
    </style>
@endpush

@section('crud_content')
<div class="container-fluid py-4">
    <div class="card border-0 shadow-lg">
        <div class="card-header bg-gradient-succulent text-white d-flex flex-column flex-md-row justify-content-between align-items-center p-4">
            <div class="header-title mb-3 mb-md-0">
                <div class="d-flex align-items-center">
                    <i class="fas fa-ruler-horizontal fa-2x me-3"></i>
                    <div>
                        <h5 class="mb-0 fw-bold text-white">Gestión de Trozas</h5>
                        <small>Administra los registros de madera cortada</small>
                    </div>
                </div>
            </div>
            <div class="header-actions d-flex align-items-center w-100 w-md-auto">
                <form method="GET" action="{{ route('trozas.index') }}" class="input-group me-3 rounded-pill shadow-sm bg-white">
                    <span class="input-group-text bg-transparent border-0"><i class="fas fa-search"></i></span>
                    <input type="text" name="search" class="form-control border-0" placeholder="Buscar por ID..." value="{{ request('search') }}">
                </form>
                <button class="btn rounded-pill flex-shrink-0" data-bs-toggle="modal" data-bs-target="#createTrozaModal" style="background-color: var(--succulent-lightest); color: var(--succulent-dark);">
                    <i class="fas fa-plus me-2"></i>Nueva Troza
                </button>
            </div>
        </div>
        
        <div class="card-body p-0">
            <div class="table-responsive">
                <table class="table table-hover align-middle mb-0">
                    <thead class="bg-light">
                        <tr>
                            <th class="py-3 ps-4">Troza (ID y Medidas)</th>
                            <th class="py-3">Propiedades</th>
                            <th class="py-3">Origen</th>
                            <th class="py-3 pe-4 text-center">Acciones</th>
                        </tr>
                    </thead>
                    <tbody>
                        @forelse ($trozas as $troza)
                        <tr class="border-bottom">
                            <td class="ps-4">
                                <p class="fw-bold mb-1">ID: <span class="font-monospace">{{ $troza->id_troza }}</span></p>
                                <small class="text-muted">
                                    <span class="me-2" title="Longitud">L: <strong>{{ number_format($troza->longitud, 2) }}m</strong></span>
                                    <span title="Diámetro">D: <strong>{{ number_format($troza->diametro, 2) }}m</strong></span>
                                </small>
                            </td>
                            <td>
                                <p class="mb-1"><span class="data-label">Volumen:</span> <span class="data-value">{{ number_format($troza->volumen ?? 0, 4) }} m³</span></p>
                                <p class="mb-0"><span class="data-label">Densidad:</span> <span class="data-value">{{ number_format($troza->densidad, 2) }}</span></p>
                            </td>
                            <td>
                                <p class="mb-1"><i class="fas fa-seedling me-2" style="color: var(--succulent-medium);"></i>{{ $troza->especie->nom_comun }}</p>
                                <p class="mb-0"><i class="fas fa-draw-polygon me-2 text-muted"></i>{{ $troza->parcela->nom_parcela }}</p>
                            </td>
                            <td class="pe-4 text-center">
                                <div class="btn-group" role="group">
                                    <button class="btn btn-sm btn-icon btn-light" data-bs-toggle="modal" data-bs-target="#viewTrozaModal{{ $troza->id_troza }}" title="Ver Detalles">
                                        <i class="fas fa-eye" style="color: var(--succulent-medium);"></i>
                                    </button>
                                    <button class="btn btn-sm btn-icon btn-light" data-bs-toggle="modal" data-bs-target="#editTrozaModal{{ $troza->id_troza }}" title="Editar">
                                        <i class="fas fa-pencil-alt" style="color: var(--succulent-dark);"></i>
                                    </button>
                                    <button type="button" class="btn btn-sm btn-icon btn-light js-delete-btn" title="Eliminar"
                                            data-url="{{ route('trozas.destroy', $troza->id_troza) }}"
                                            data-name="la troza con ID {{ $troza->id_troza }}">
                                        <i class="fas fa-trash-alt text-danger"></i>
                                    </button>
                                </div>
                            </td>
                        </tr>
                        @empty
                        <tr><td colspan="4" class="text-center py-5"><p class="mb-0 text-muted">No hay trozas registradas.</p></td></tr>
                        @endforelse
                    </tbody>
                </table>
            </div>
             @if($trozas->hasPages())
                <div class="p-3 border-top">{{ $trozas->appends(request()->query())->links() }}</div>
            @endif
        </div>
    </div>
</div>

@foreach ($trozas as $troza)
<div class="modal fade" id="viewTrozaModal{{ $troza->id_troza }}" tabindex="-1" aria-hidden="true">
    <div class="modal-dialog modal-dialog-centered modal-lg">
        <div class="modal-content border-0 shadow-lg">
            <div class="modal-header text-white" style="background-color: var(--succulent-medium);">
                <h5 class="modal-title"><i class="fas fa-info-circle me-2"></i>Detalles de Troza (ID: {{ $troza->id_troza }})</h5>
                <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal"></button>
            </div>
            <div class="modal-body p-4">
                <div class="row">
                    <div class="col-md-6">
                        <h6>Propiedades Físicas</h6>
                        <hr class="mt-1">
                        <p><strong class="text-muted">Longitud:</strong> {{ number_format($troza->longitud, 2) }} metros</p>
                        <p><strong class="text-muted">Diámetro Principal:</strong> {{ number_format($troza->diametro, 2) }} metros</p>
                        <p><strong class="text-muted">Diámetro Otro Extremo:</strong> {{ $troza->diametro_otro_extremo ? number_format($troza->diametro_otro_extremo, 2).' m' : 'N/A' }}</p>
                        <p><strong class="text-muted">Diámetro Medio:</strong> {{ $troza->diametro_medio ? number_format($troza->diametro_medio, 2).' m' : 'N/A' }}</p>
                    </div>
                    <div class="col-md-6">
                        <h6>Propiedades Calculadas y Origen</h6>
                        <hr class="mt-1">
                        <p><strong class="text-muted">Volumen Estimado:</strong> {{ number_format($troza->volumen ?? 0, 4) }} m³</p>
                        <p><strong class="text-muted">Densidad:</strong> {{ number_format($troza->densidad, 2) }}</p>
                        <p><strong class="text-muted">Especie:</strong> {{ $troza->especie->nom_comun }}</p>
                        <p><strong class="text-muted">Parcela:</strong> {{ $troza->parcela->nom_parcela }}</p>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>

<div class="modal fade" id="editTrozaModal{{ $troza->id_troza }}" tabindex="-1" aria-hidden="true">
    <div class="modal-dialog modal-dialog-centered modal-lg">
        <div class="modal-content border-0 shadow-lg">
            <div class="modal-header text-white" style="background-color: var(--succulent-dark);">
                <h5 class="modal-title"><i class="fas fa-edit me-2"></i>Editar Troza</h5>
                <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal"></button>
            </div>
            <div class="modal-body p-4">
                <form method="POST" action="{{ route('trozas.update', $troza->id_troza) }}">
                    @csrf
                    @method('PUT')
                    <div class="row">
                        <div class="col-md-6 mb-3"><div class="form-floating"><input type="number" step="0.01" name="longitud" class="form-control" value="{{ $troza->longitud }}" required placeholder="0.00"><label>Longitud (m)*</label></div></div>
                        <div class="col-md-6 mb-3"><div class="form-floating"><input type="number" step="0.01" name="diametro" class="form-control" value="{{ $troza->diametro }}" required placeholder="0.00"><label>Diámetro Principal (m)*</label></div></div>
                    </div>
                    <div class="row">
                        <div class="col-md-6 mb-3"><div class="form-floating"><input type="number" step="0.01" name="diametro_otro_extremo" class="form-control" value="{{ $troza->diametro_otro_extremo }}" placeholder="0.00"><label>Diámetro Otro Extremo (m)</label></div></div>
                        <div class="col-md-6 mb-3"><div class="form-floating"><input type="number" step="0.01" name="diametro_medio" class="form-control" value="{{ $troza->diametro_medio }}" placeholder="0.00"><label>Diámetro Medio (m)</label></div></div>
                    </div>
                    <div class="mb-3"><div class="form-floating"><input type="number" step="0.01" name="densidad" class="form-control" value="{{ $troza->densidad }}" required placeholder="0.00"><label>Densidad*</label></div></div>
                    <div class="row">
                        <div class="col-md-6 mb-3"><div class="form-floating"><select name="id_especie" class="form-select" required>@foreach ($especies as $especie)<option value="{{ $especie->id_especie }}" {{ $troza->id_especie == $especie->id_especie ? 'selected' : '' }}>{{ $especie->nom_comun }}</option>@endforeach</select><label>Especie*</label></div></div>
                        <div class="col-md-6 mb-3"><div class="form-floating"><select name="id_parcela" class="form-select" required>@foreach ($parcelas as $parcela)<option value="{{ $parcela->id_parcela }}" {{ $troza->id_parcela == $parcela->id_parcela ? 'selected' : '' }}>{{ $parcela->nom_parcela }}</option>@endforeach</select><label>Parcela*</label></div></div>
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

<div class="modal fade" id="createTrozaModal" tabindex="-1" aria-hidden="true">
    <div class="modal-dialog modal-dialog-centered modal-lg">
        <div class="modal-content border-0 shadow-lg">
            <div class="modal-header text-white" style="background-color: var(--succulent-medium);">
                 <h5 class="modal-title"><i class="fas fa-plus-circle me-2"></i>Nueva Troza</h5>
                <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal"></button>
            </div>
            <div class="modal-body p-4">
                <form method="POST" action="{{ route('trozas.store') }}">
                    @csrf
                     <div class="row">
                        <div class="col-md-6 mb-3"><div class="form-floating"><input type="number" step="0.01" name="longitud" class="form-control" required placeholder="0.00"><label>Longitud (m)*</label></div></div>
                        <div class="col-md-6 mb-3"><div class="form-floating"><input type="number" step="0.01" name="diametro" class="form-control" required placeholder="0.00"><label>Diámetro Principal (m)*</label></div></div>
                    </div>
                    <div class="row">
                        <div class="col-md-6 mb-3"><div class="form-floating"><input type="number" step="0.01" name="diametro_otro_extremo" class="form-control" placeholder="0.00"><label>Diámetro Otro Extremo (m)</label></div></div>
                        <div class="col-md-6 mb-3"><div class="form-floating"><input type="number" step="0.01" name="diametro_medio" class="form-control" placeholder="0.00"><label>Diámetro Medio (m)</label></div></div>
                    </div>
                    <div class="mb-3"><div class="form-floating"><input type="number" step="0.01" name="densidad" class="form-control" required placeholder="0.00"><label>Densidad*</label></div></div>
                    <div class="row">
                        <div class="col-md-6 mb-3"><div class="form-floating"><select name="id_especie" class="form-select" required><option value="" disabled selected>Selecciona...</option>@foreach ($especies as $especie)<option value="{{ $especie->id_especie }}">{{ $especie->nom_comun }}</option>@endforeach</select><label>Especie*</label></div></div>
                        <div class="col-md-6 mb-3"><div class="form-floating"><select name="id_parcela" class="form-select" required><option value="" disabled selected>Selecciona...</option>@foreach ($parcelas as $parcela)<option value="{{ $parcela->id_parcela }}">{{ $parcela->nom_parcela }}</option>@endforeach</select><label>Parcela*</label></div></div>
                    </div>
                    <div class="d-flex justify-content-end mt-4">
                        <button type="button" class="btn btn-secondary me-2" data-bs-dismiss="modal">Cancelar</button>
                        <button type="submit" class="btn text-white" style="background-color: var(--succulent-medium);"><i class="fas fa-check-circle me-2"></i>Crear Troza</button>
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

    // Lógica de Eliminación con SweetAlert2
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