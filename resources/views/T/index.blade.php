@extends('layouts.app')

{{-- [REFACTOR] 1. Cargar el CSS específico de esta página en el stack 'styles' (en el <head>) --}}
@push('styles')
    <link href="{{ asset('css/WW/tecnico-dashboard.css') }}" rel="stylesheet">
@endpush


@section('content')
    <div class="container-fluid py-4">
        <div class="row mb-5">
            <div class="col-12">
                <div class="card border-0 wood-shadow-lg">
                    <div class="card-body p-4 wood-bg-gradient">
                        <div class="d-flex justify-content-between align-items-center flex-wrap gap-2">
                            <div>
                                <h2 class="text-white mb-2"><i class="fas fa-tree me-2"></i> Panel del Técnico Forestal</h2>
                                <p class="text-white opacity-8 mb-0">Gestión profesional de recursos maderables sostenibles</p>
                            </div>
                            <button class="btn btn-wood-light rounded-pill wood-shadow px-4" data-bs-toggle="modal" data-bs-target="#createParcelaModal">
                                <i class="fas fa-plus me-2"></i> Nueva Parcela
                            </button>
                        </div>
                    </div>
                </div>
            </div>
        </div>

        <div class="row mb-5">
            <div class="col-md-12">
                <div class="wood-card wood-shadow">
                    <div class="card-body p-4">
                        <div class="d-flex align-items-center mb-4">
                            <div class="icon icon-shape icon-lg bg-forest-dark text-white rounded-circle me-4 wood-shadow-sm">
                                <i class="fas fa-user-tie"></i>
                            </div>
                            <div>
                                <h3 class="text-forest-dark mb-1">Técnico {{ $user->persona->nom ?? 'Usuario' }}</h3>
                                <div class="d-flex align-items-center flex-wrap gap-2">
                                    <span class="badge bg-forest-light text-forest-dark">
                                        <i class="fas fa-id-card me-1"></i> {{ $tecnico->cedula_p ?? 'No disponible' }}
                                    </span>
                                    <span class="badge bg-forest-accent text-white">
                                        <i class="fas fa-key me-1"></i> {{ $tecnico->clave_tecnico }}
                                    </span>
                                </div>
                            </div>
                        </div>

                        <hr class="wood-divider my-4">

                        <div class="row">
                            <div class="col-lg-3 col-md-6 mb-3 mb-lg-0">
                                <div class="wood-card h-100 wood-shadow-sm">
                                    <div class="card-body text-center p-3">
                                        <div class="icon icon-shape icon-md bg-forest-light text-forest-dark rounded-circle mb-3 mx-auto wood-shadow-sm">
                                            <i class="fas fa-map-marked-alt"></i>
                                        </div>
                                        <h2 class="text-forest-dark mb-1">{{ $parcelas->total() }}</h2>
                                        <p class="text-muted mb-0">Parcelas asignadas</p>
                                    </div>
                                </div>
                            </div>

                            <div class="col-lg-3 col-md-6 mb-3 mb-lg-0">
                                <div class="wood-card h-100 wood-shadow-sm">
                                    <div class="card-body text-center p-3">
                                        <div class="icon icon-shape icon-md bg-forest-medium text-white rounded-circle mb-3 mx-auto wood-shadow-sm">
                                            <i class="fas fa-cut"></i>
                                        </div>
                                        <h2 class="text-forest-dark mb-1">{{ $totalTrozas }}</h2>
                                        <p class="text-muted mb-0">Trozas registradas</p>
                                    </div>
                                </div>
                            </div>

                            <div class="col-lg-3 col-md-6 mb-3 mb-md-0">
                                <div class="wood-card h-100 wood-shadow-sm">
                                    <div class="card-body text-center p-3">
                                        <div class="icon icon-shape icon-md bg-forest-accent text-white rounded-circle mb-3 mx-auto wood-shadow-sm">
                                            <i class="fas fa-calculator"></i>
                                        </div>
                                        <h2 class="text-forest-dark mb-1">{{ $totalEstimaciones ?? 0 }}</h2> {{-- Asegúrate de pasar esta var --}}
                                        <p class="text-muted mb-0">Estimaciones</p>
                                    </div>
                                </div>
                            </div>

                            <div class="col-lg-3 col-md-6">
                                <div class="wood-card h-100 wood-shadow-sm">
                                    <div class="card-body text-center p-3">
                                        <div class="icon icon-shape icon-md bg-forest-dark text-white rounded-circle mb-3 mx-auto wood-shadow-sm">
                                            <i class="fas fa-cubes"></i>
                                        </div>
                                        <h2 class="text-forest-dark mb-1">{{ number_format($totalVolumenMaderable ?? 0, 2) }} m³</h2> {{-- Asegúrate de pasar esta var --}}
                                        <p class="text-muted mb-0">Volumen total</p>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>

        <div class="row">
            <div class="col-12">
                <div class="wood-card wood-shadow">
                    <div class="wood-card-header">
                        <div class="d-flex justify-content-between align-items-center flex-wrap gap-2">
                            <h5 class="mb-0 text-white"><i class="fas fa-map me-2"></i>Parcelas Asignadas</h5>
                            <div class="input-group wood-search-input">
                                <input type="text" class="form-control" placeholder="Buscar parcela...">
                                <button class="btn btn-wood-outline" type="button"><i class="fas fa-search"></i></button>
                            </div>
                        </div>
                    </div>

                    <div class="card-body p-0">
                        <div class="table-responsive">
                            <table class="wood-table">
                                <thead>
                                <tr>
                                    <th class="text-uppercase ps-4">Nombre</th>
                                    <th class="text-uppercase">Productor</th>
                                    <th class="text-uppercase">Ubicación</th>
                                    <th class="text-uppercase">Extensión (ha)</th>
                                    <th class="text-uppercase">Trozas</th>
                                    <th class="text-uppercase">Estimaciones</th>
                                    <th class="text-uppercase">Volumen (m³)</th>
                                    <th class="text-uppercase text-end pe-4">Acciones</th>
                                </tr>
                                </thead>
                                <tbody>
                                @forelse($parcelas as $parcela)
                                    <tr>
                                        <td class="ps-4">
                                            <div class="d-flex align-items-center">
                                                <div class="icon icon-shape icon-sm bg-forest-light text-forest-dark rounded-circle me-3 wood-shadow-sm">
                                                    <i class="fas fa-map"></i>
                                                </div>
                                                <div>
                                                    <h6 class="mb-0 text-forest-dark">{{ $parcela->nom_parcela }}</h6>
                                                    <small class="text-muted">Código: {{ $parcela->id_parcela }}</small>
                                                </div>
                                            </div>
                                        </td>
                                        <td>
                                            @if($parcela->productor && $parcela->productor->persona)
                                                <div class="d-flex align-items-center">
                                                    <div class="icon icon-shape icon-sm bg-wood-light text-wood-dark rounded-circle me-2">
                                                        <i class="fas fa-user"></i>
                                                    </div>
                                                    <div>
                                                        <span class="d-block">{{ $parcela->productor->persona->nom }}</span>
                                                        <small class="text-muted">{{ $parcela->productor->persona->cedula }}</small>
                                                    </div>
                                                </div>
                                            @else
                                                <span class="text-muted">No asignado</span>
                                            @endif
                                        </td>
                                        <td><span class="text-forest-medium">{{ $parcela->ubicacion }}</span></td>
                                        <td><span class="text-forest-medium">{{ $parcela->extension }}</span></td>
                                        <td><span class="wood-badge">{{ $parcela->trozas_count }}</span></td>
                                        <td><span class="wood-badge bg-info">{{ $parcela->estimaciones_count ?? 0 }}</span></td>
                                        <td><span class="wood-badge bg-success">{{ number_format($parcela->volumen_maderable ?? 0, 2) }} m³</span></td>
                                        <td class="pe-4">
                                            {{-- [UX] Acciones verticales más limpias para tablas densas --}}
                                            <div class="vertical-actions">
                                                <a href="{{ route('parcelas.export.pdf', $parcela->id_parcela) }}" class="btn btn-sm btn-pdf mb-2 w-100 text-start" data-bs-tooltip="tooltip" title="Exportar a PDF">
                                                    <i class="fas fa-file-pdf fa-fw me-2"></i> Exportar PDF
                                                </a>
                                                <button class="btn btn-sm btn-wood-action mb-2 w-100 text-start" data-bs-toggle="modal" data-bs-target="#addTrozaModal{{ $parcela->id_parcela }}" data-bs-tooltip="tooltip" title="Agregar troza">
                                                    <i class="fas fa-plus fa-fw me-2"></i> Agregar Troza
                                                </button>
                                                <button class="btn btn-sm btn-wood-action mb-2 w-100 text-start" data-bs-toggle="modal" data-bs-target="#estimacionModal{{ $parcela->id_parcela }}" data-bs-tooltip="tooltip" title="Realizar estimación">
                                                    <i class="fas fa-calculator fa-fw me-2"></i> Estimación
                                                </button>
                                                <a href="{{ route('parcelas.show', $parcela->id_parcela) }}" class="btn btn-sm btn-wood-action w-100 text-start" data-bs-tooltip="tooltip" title="Ver detalles">
                                                    <i class="fas fa-eye fa-fw me-2"></i> Ver Detalles
                                                </a>
                                            </div>
                                        </td>
                                    </tr>
                                @empty
                                    <tr>
                                        <td colspan="8" class="text-center py-5">
                                            <div class="text-center">
                                                <i class="fas fa-map-marked-alt fa-4x text-forest-light mb-4"></i>
                                                <h5 class="text-forest-medium mb-3">No tienes parcelas asignadas</h5>
                                                <button class="btn btn-wood rounded-pill px-4" data-bs-toggle="modal" data-bs-target="#createParcelaModal">
                                                    <i class="fas fa-plus me-2"></i> Asignar Parcela
                                                </button>
                                            </div>
                                        </td>
                                    </tr>
                                @endforelse
                                </tbody>
                            </table>
                        </div>
                    </div>

                    @if($parcelas->hasPages())
                        <div class="wood-card-footer">
                            {{ $parcelas->links() }}
                        </div>
                    @endif
                </div>
            </div>
        </div>
    </div>

    {{-- (Los modales se quedan como estaban, pero usando la nueva estructura de clases CSS) --}}

    <div class="modal fade wood-modal" id="createParcelaModal" tabindex="-1" aria-labelledby="createParcelaLabel" aria-hidden="true">
        <div class="modal-dialog modal-dialog-centered">
            <div class="modal-content border-0 wood-modal-content">
                <div class="modal-header wood-modal-header wood-bg-primary text-white">
                    <div class="d-flex align-items-center">
                        <div class="wood-modal-icon me-3"><i class="fas fa-map-marked-alt"></i></div>
                        <div>
                            <h5 class="modal-title wood-modal-title">Nueva Parcela</h5>
                            <p class="wood-modal-subtitle mb-0">Ingresa los datos de la nueva parcela</p>
                        </div>
                    </div>
                    <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal" aria-label="Close"></button>
                </div>
                <div class="modal-body p-4 wood-modal-body">
                    <form method="POST" action="{{ route('parcelas.store') }}">
                        @csrf
                        <div class="mb-3">
                            <label class="wood-form-label">Nombre de la parcela</label>
                            <input type="text" name="nom_parcela" class="wood-form-control" required>
                        </div>
                        <div class="row g-3">
                            <div class="col-md-8 mb-3">
                                <label class="wood-form-label">Ubicación</label>
                                <input type="text" name="ubicacion" class="wood-form-control" required>
                            </div>
                            <div class="col-md-4 mb-3">
                                <label class="wood-form-label">Extensión (ha)</label>
                                <input type="number" step="0.01" name="extension" class="wood-form-control" required>
                            </div>
                        </div>
                        <div class="mb-3">
                            <label class="wood-form-label">Productor</label>
                            <select class="wood-form-select" name="id_productor" required>
                                <option value="" selected disabled>Seleccione un productor</option>
                                @foreach ($productores as $productor)
                                    <option value="{{ $productor->id_productor }}">
                                        {{ $productor->persona->nom }} ({{ $productor->persona->cedula }})
                                    </option>
                                @endforeach
                            </select>
                        </div>
                        <div class="mb-3">
                            <label class="wood-form-label">Dirección</label>
                            <input type="text" name="direccion" class="wood-form-control">
                        </div>
                        <div class="row g-3">
                            <div class="col-md-6 mb-3">
                                <label class="wood-form-label">Código Postal</label>
                                <input type="text" name="CP" class="wood-form-control">
                            </div>
                            <div class="col-md-6 mb-3">
                                <label class="wood-form-label">Especie Principal</label>
                                <select class="wood-form-select" name="id_especie" required>
                                    <option value="" selected disabled>Seleccione una especie</option>
                                    @foreach ($especies as $especie)
                                        <option value="{{ $especie->id_especie }}">{{ $especie->nom_cientifico }}</option>
                                    @endforeach
                                </select>
                            </div>
                        </div>
                        <div class="wood-modal-footer mt-4">
                            <button type="button" class="btn btn-wood-outline" data-bs-dismiss="modal"><i class="fas fa-times me-1"></i> Cancelar</button>
                            <button type="submit" class="btn btn-wood"><i class="fas fa-save me-1"></i> Registrar Parcela</button>
                        </div>
                    </form>
                </div>
            </div>
        </div>
    </div>

    @foreach($parcelas as $parcela)
        <div class="modal fade wood-modal" id="addTrozaModal{{ $parcela->id_parcela }}" tabindex="-1" aria-hidden="true">
            <div class="modal-dialog modal-dialog-centered modal-lg">
                <div class="modal-content border-0 wood-modal-content">
                    <div class="modal-header wood-modal-header wood-bg-primary">
                        <div class="d-flex align-items-center">
                            <div class="wood-modal-icon me-3"><i class="fas fa-tree"></i></div>
                            <div>
                                <h5 class="modal-title wood-modal-title text-white">Nueva Troza</h5>
                                <p class="wood-modal-subtitle mb-0">Parcela: {{ $parcela->nom_parcela }}</p>
                            </div>
                        </div>
                        <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal" aria-label="Close"></button>
                    </div>
                    <div class="modal-body p-4 wood-modal-body">
                        <form method="POST" action="{{ route('trozas.store') }}">
                            @csrf
                            <input type="hidden" name="id_parcela" value="{{ $parcela->id_parcela }}">
                            <div class="row g-3">
                                <div class="col-md-6"><label class="wood-form-label">Longitud (m)</label><input type="number" step="0.01" name="longitud" class="wood-form-control" required></div>
                                <div class="col-md-6"><label class="wood-form-label">Diámetro (m)</label><input type="number" step="0.01" name="diametro" class="wood-form-control" required></div>
                                <div class="col-md-6"><label class="wood-form-label">Diámetro otro extremo (m)</label><input type="number" step="0.01" name="diametro_otro_extremo" class="wood-form-control"></div>
                                <div class="col-md-6"><label class="wood-form-label">Diámetro medio (m)</label><input type="number" step="0.01" name="diametro_medio" class="wood-form-control"></div>
                                <div class="col-md-6"><label class="wood-form-label">Densidad</label><input type="number" step="0.01" name="densidad" class="wood-form-control" required></div>
                                <div class="col-md-6">
                                    <label class="wood-form-label">Especie</label>
                                    <select class="wood-form-select" name="id_especie" required>
                                        <option value="" selected disabled>Seleccione una especie</option>
                                        @foreach ($especies as $especie)
                                            <option value="{{ $especie->id_especie }}">{{ $especie->nom_cientifico }}</option>
                                        @endforeach
                                    </select>
                                </div>
                            </div>
                            <div class="wood-modal-footer mt-4">
                                <button type="button" class="btn btn-wood-outline" data-bs-dismiss="modal"><i class="fas fa-times me-1"></i> Cancelar</button>
                                <button type="submit" class="btn btn-wood"><i class="fas fa-check-circle me-1"></i> Registrar Troza</button>
                            </div>
                        </form>
                    </div>
                </div>
            </div>
        </div>

        <div class="modal fade wood-modal" id="estimacionModal{{ $parcela->id_parcela }}" tabindex="-1" aria-hidden="true">
            <div class="modal-dialog modal-dialog-centered">
                <div class="modal-content border-0 wood-modal-content">
                    <div class="modal-header wood-modal-header wood-bg-info">
                        <div class="d-flex align-items-center">
                            <div class="wood-modal-icon me-3"><i class="fas fa-calculator"></i></div>
                            <div>
                                <h5 class="modal-title wood-modal-title text-white">Estimación Volumétrica</h5>
                                <p class="wood-modal-subtitle mb-0">Parcela: {{ $parcela->nom_parcela }}</p>
                            </div>
                        </div>
                        <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal" aria-label="Close"></button>
                    </div>
                    <div class="modal-body p-4 wood-modal-body">
                        <form method="POST" action="{{ route('estimaciones.store') }}">
                            @csrf
                            <input type="hidden" name="id_parcela" value="{{ $parcela->id_parcela }}">
                            <div class="mb-3">
                                <label class="wood-form-label">Seleccionar Troza</label>
                                <select class="wood-form-select" name="id_troza" required>
                                    <option value="" selected disabled>Seleccione una troza</option>
                                    {{-- NOTA DE RENDIMIENTO: Esta consulta N+1 debe ser eager-loaded en el controlador. --}}
                                    @foreach($parcela->trozas as $troza)
                                        <option value="{{ $troza->id_troza }}">Troza #{{ $troza->id_troza }} ({{ $troza->longitud }}m x {{ $troza->diametro }}m)</option>
                                    @endforeach
                                </select>
                            </div>
                            <div class="row g-3">
                                <div class="col-md-6">
                                    <label class="wood-form-label">Tipo de Estimación</label>
                                    <select class="wood-form-select" name="id_tipo_e" required>
                                        <option value="" selected disabled>Seleccione un tipo</option>
                                        @foreach($tiposEstimacion as $tipo)
                                            <option value="{{ $tipo->id_tipo_e }}">{{ $tipo->desc_estimacion }}</option>
                                        @endforeach
                                    </select>
                                </div>
                                <div class="col-md-6">
                                    <label class="wood-form-label">Fórmula a Aplicar</label>
                                    <select class="wood-form-select" name="id_formula" required>
                                        <option value="" selected disabled>Seleccione una fórmula</option>
                                        @foreach($formulas as $formula)
                                            <option value="{{ $formula->id_formula }}">{{ $formula->nom_formula }}</option>
                                        @endforeach
                                    </select>
                                </div>
                            </div>
                            <div class="mt-3">
                                <label class="wood-form-label">Cálculo (m³)</label>
                                <input type="number" step="0.0001" class="wood-form-control" name="calculo" required>
                            </div>
                            <div class="wood-modal-footer mt-4">
                                <button type="button" class="btn btn-wood-outline" data-bs-dismiss="modal"><i class="fas fa-times me-1"></i> Cancelar</button>
                                <button type="submit" class="btn btn-wood-info"><i class="fas fa-calculator me-1"></i> Calcular Estimación</button>
                            </div>
                        </form>
                    </div>
                </div>
            </div>
        </div>

        <div class="modal fade wood-modal" id="turnoModal{{ $parcela->id_parcela }}" tabindex="-1" aria-hidden="true">
            <div class="modal-dialog modal-dialog-centered">
                <div class="modal-content border-0 wood-modal-content">
                    <div class="modal-header wood-modal-header wood-bg-warning">
                        <div class="d-flex align-items-center">
                            <div class="wood-modal-icon me-3"><i class="fas fa-calendar-alt"></i></div>
                            <div>
                                <h5 class="modal-title wood-modal-title text-dark">Programar Turno de Corta</h5>
                                <p class="wood-modal-subtitle text-dark opacity-75 mb-0">Parcela: {{ $parcela->nom_parcela }}</p>
                            </div>
                        </div>
                        <button type="button" class="btn-close btn-close-dark" data-bs-dismiss="modal" aria-label="Close"></button>
                    </div>
                    <div class="modal-body p-4 wood-modal-body">
                        <form method="POST" action="{{ route('turno_cortas.store') }}">
                            @csrf
                            <input type="hidden" name="id_parcela" value="{{ $parcela->id_parcela }}">
                            <div class="row g-3">
                                <div class="col-md-6">
                                    <label class="wood-form-label">Código de Corta</label>
                                    <input type="text" name="codigo_corta" class="wood-form-control" required>
                                </div>
                                <div class="col-md-6">
                                    <label class="wood-form-label">Fecha de Corta</label>
                                    <input type="date" name="fecha_corta" class="wood-form-control" required>
                                </div>
                            </div>
                            <div class="mt-3">
                                <label class="wood-form-label">Notas adicionales</label>
                                <textarea class="wood-form-control" name="notas" rows="3"></textarea>
                            </div>
                            <div class="wood-modal-footer mt-4">
                                <button type="button" class="btn btn-wood-outline" data-bs-dismiss="modal"><i class="fas fa-times me-1"></i> Cancelar</button>
                                <button type="submit" class="btn btn-wood-warning"><i class="fas fa-calendar-check me-1"></i> Programar Turno</button>
                            </div>
                        </form>
                    </div>
                </div>
            </div>
        </div>
    @endforeach

@endsection

{{-- [REFACTOR] 2. Script de Tooltips movido al stack 'scripts' (carga al final del <body>) --}}
@push('scripts')
<script>
    document.addEventListener('DOMContentLoaded', function() {
        // Inicializar tooltips de Bootstrap
        var tooltipTriggerList = [].slice.call(document.querySelectorAll('[data-bs-toggle="tooltip"]'));
        var tooltipList = tooltipTriggerList.map(function (tooltipTriggerEl) {
            return new bootstrap.Tooltip(tooltipTriggerEl);
        });
    });
</script>
@endpush

{{-- [REFACTOR] 3. El bloque <style> completo ha sido eliminado de aquí y movido a 'tecnico-dashboard.css' --}}