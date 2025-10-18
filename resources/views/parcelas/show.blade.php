@extends('layouts.app')

{{-- [REFACTORIZACIÓN] 1. Cargar el CSS de tu tema unificado en el <head> --}}
@push('styles')
    <link href="{{ asset('css/WW/theme.css') }}" rel="stylesheet">
@endpush

@section('content')
<div class="container-fluid py-4">
    <div class="wood-card wood-shadow-lg">
        <div class="wood-card-header">
            <div class="d-flex justify-content-between align-items-center flex-wrap gap-2">
                <div>
                    <h2 class="text-white mb-1"><i class="fas fa-map-marked-alt me-2"></i>Parcela: {{ $parcela->nom_parcela }}</h2>
                    <p class="text-white opacity-8 mb-0">Gestión detallada de recursos forestales</p>
                </div>
                <a href="{{ route('tecnico.dashboard') }}" class="btn btn-wood-light rounded-pill">
                    <i class="fas fa-arrow-left me-1"></i> Regresar
                </a>
            </div>
        </div>
        
        <div class="card-body p-4"> {{-- Añadido un padding base al body del card --}}
            
            <div class="row mb-4 g-4"> {{-- Reducido el margen inferior de mb-5 a mb-4 --}}
                <div class="col-md-3"> {{-- Ajustado a 4 columnas para los 4 stats --}}
                    <div class="wood-info-card wood-card-hover">
                        <div class="card-body text-center p-4">
                            <div class="wood-icon-wrapper bg-wood-light-20 mb-4">
                                <i class="fas fa-map-marker-alt wood-icon text-wood-medium"></i>
                            </div>
                            <h5 class="wood-card-title mb-3">Ubicación</h5>
                            <p class="wood-card-text">{{ $parcela->ubicacion }}</p>
                            <div class="wood-card-decoration"></div>
                        </div>
                    </div>
                </div>
                
                <div class="col-md-3">
                    <div class="wood-info-card wood-card-hover">
                        <div class="card-body text-center p-4">
                            <div class="wood-icon-wrapper bg-wood-medium-20 mb-4">
                                <i class="fas fa-ruler-combined wood-icon text-wood-medium"></i>
                            </div>
                            <h5 class="wood-card-title mb-3">Área</h5>
                            <p class="wood-card-text">{{ $parcela->extension }} <span class="wood-unit">hectáreas</span></p> {{-- Corregido 'area' por 'extension' --}}
                            <div class="wood-card-decoration"></div>
                        </div>
                    </div>
                </div>
                
                <div class="col-md-3">
                    <div class="wood-info-card wood-card-hover">
                        <div class="card-body text-center p-4">
                            <div class="wood-icon-wrapper bg-wood-accent-20 mb-4">
                                <i class="fas fa-tree wood-icon text-wood-accent"></i>
                            </div>
                            <h5 class="wood-card-title mb-3">Trozas</h5>
                            <p class="wood-card-text">{{ $parcela->trozas_count }} <span class="wood-unit">registradas</span></p> {{-- Usando el _count de la consulta optimizada --}}
                            <div class="wood-card-decoration"></div>
                        </div>
                    </div>
                </div>

                <div class="col-md-3">
                    <div class="wood-info-card wood-card-hover">
                        <div class="card-body text-center p-4">
                            <div class="wood-icon-wrapper bg-wood-accent-20 mb-4">
                                <i class="fas fa-cubes wood-icon text-wood-accent"></i>
                            </div>
                            <h5 class="wood-card-title mb-3">Volumen Total</h5>
                            <p class="wood-card-text">{{ number_format($parcela->volumen_maderable_sum_calculo ?? 0, 4) }} <span class="wood-unit">m³</span></p>
                            <div class="wood-card-decoration"></div>
                        </div>
                    </div>
                </div>
            </div>
            
            <ul class="nav nav-tabs wood-tabs mb-0" id="parcelaTabs" role="tablist"> {{-- Eliminado mb-4, el tab-content lo manejará --}}
                <li class="nav-item" role="presentation">
                    <button class="nav-link active wood-tab-btn" id="trozas-tab" data-bs-toggle="tab" data-bs-target="#trozas" type="button" role="tab" aria-selected="true">
                        <i class="fas fa-tree me-2"></i>Trozas
                        <span class="wood-badge ms-2">{{ $parcela->trozas_count }}</span>
                    </button>
                </li>
                <li class="nav-item" role="presentation">
                    <button class="nav-link wood-tab-btn" id="estimaciones-tab" data-bs-toggle="tab" data-bs-target="#estimaciones" type="button" role="tab" aria-selected="false">
                        <i class="fas fa-calculator me-2"></i>Estimaciones
                        <span class="wood-badge wood-badge-secondary ms-2">{{ $parcela->estimaciones_count }}</span>
                    </button>
                </li>
                <li class="nav-item" role="presentation">
                    <button class="nav-link wood-tab-btn" id="turnos-tab" data-bs-toggle="tab" data-bs-target="#turnos" type="button" role="tab" aria-selected="false">
                        <i class="fas fa-calendar-alt me-2"></i>Turnos
                        <span class="wood-badge wood-badge-dark ms-2">{{ $parcela->turnos_corta_count }}</span>
                    </button>
                </li>
            </ul>

            <div class="tab-content wood-tab-content" id="parcelaTabsContent">
                
                <div class="tab-pane fade show active" id="trozas" role="tabpanel">
                    @if($parcela->trozas->count() > 0)
                    <div class="table-responsive">
                        <table class="wood-table">
                            <thead>
                                <tr>
                                    <th class="ps-4">ID</th>
                                    <th>Especie</th>
                                    <th>Medidas</th>
                                    <th>Volumen</th>
                                    <th>Densidad</th>
                                    <th class="text-end pe-4">Acciones</th>
                                </tr>
                            </thead>
                            <tbody>
                                @foreach($parcela->trozas as $troza)
                                <tr>
                                    <td class="ps-4">{{ $troza->id_troza }}</td>
                                    <td>
                                        <span class="text-forest-dark">{{ $troza->especie->nom_cientifico }}</span>
                                    </td>
                                    <td>
                                        <span class="text-muted">{{ number_format($troza->longitud, 2) }}m × {{ number_format($troza->diametro, 2) }}m</span> {{-- Asumiendo que diametro es metros --}}
                                    </td>
                                    <td>
                                        <span class="text-forest-accent fw-bold">
                                            {{ $troza->estimacion ? number_format($troza->estimacion->calculo, 4) : 'N/A' }} m³
                                        </span>
                                    </td>
                                    <td>
                                        <span class="text-muted">{{ number_format($troza->densidad, 2) }}</span>
                                    </td>
                                    <td class="text-end pe-4">
                                        <div class="d-flex flex-wrap gap-2 justify-content-end">
                                            <button class="btn btn-wood-action btn-view" data-bs-toggle="modal" data-bs-target="#trozaModal{{ $troza->id_troza }}">
                                                <i class="fas fa-eye me-1"></i> Detalles
                                            </button>
                                            <button class="btn btn-wood-action btn-edit" data-bs-toggle="modal" data-bs-target="#editTrozaModal{{ $troza->id_troza }}">
                                                <i class="fas fa-edit me-1"></i> Editar
                                            </button>
                                            <form action="{{ route('trozas.destroy', $troza->id_troza) }}" method="POST" class="d-inline" onsubmit="return confirm('¿Estás seguro de eliminar esta troza?');">
                                                @csrf
                                                @method('DELETE')
                                                <button type="submit" class="btn btn-wood-action btn-delete">
                                                    <i class="fas fa-trash me-1"></i> Eliminar
                                                </button>
                                            </form>
                                        </div>
                                    </td>
                                </tr>
                                @endforeach
                            </tbody>
                        </table>
                    </div>
                    @else
                    <div class="text-center py-5">
                        <i class="fas fa-tree fa-4x text-forest-light mb-4"></i>
                        <h5 class="text-forest-medium mb-3">No hay trozas registradas</h5>
                        <button class="btn btn-wood rounded-pill px-4" data-bs-toggle="modal" data-bs-target="#addTrozaModal{{$parcela->id_parcela}}"> {{-- Se necesita ID de parcela aquí aunque no haya trozas --}}
                            <i class="fas fa-plus me-2"></i> Agregar Troza
                        </button>
                    </div>
                    @endif
                </div>

                <div class="tab-pane fade" id="estimaciones" role="tabpanel">
                    @if($parcela->estimaciones->count() > 0)
                    <div class="table-responsive">
                        <table class="wood-table">
                            <thead>
                                <tr>
                                    <th class="ps-4">ID</th>
                                    <th>Tipo</th>
                                    <th>Fórmula</th>
                                    <th>Troza ID</th>
                                    <th>Resultado</th>
                                    <th class="text-end pe-4">Acciones</th>
                                </tr>
                            </thead>
                            <tbody>
                                @foreach($parcela->estimaciones as $estimacion)
                                <tr>
                                    <td class="ps-4">{{ $estimacion->id_estimacion }}</td>
                                    <td>{{ $estimacion->tipoEstimacion->desc_estimacion }}</td>
                                    <td>{{ $estimacion->formula->nom_formula }}</td>
                                    <td>#{{ $estimacion->troza->id_troza }}</td>
                                    <td class="text-forest-accent fw-bold">{{ number_format($estimacion->calculo, 4) }} m³</td>
                                    <td class="text-end pe-4">
                                        <div class="d-flex flex-wrap gap-2 justify-content-end">
                                            <button class="btn btn-wood-action btn-view" data-bs-toggle="modal" data-bs-target="#estimacionModal{{ $estimacion->id_estimacion }}">
                                                <i class="fas fa-eye me-1"></i> Detalles
                                            </button>
                                            <button class="btn btn-wood-action btn-edit" data-bs-toggle="modal" data-bs-target="#editEstimacionModal{{ $estimacion->id_estimacion }}">
                                                <i class="fas fa-edit me-1"></i> Editar
                                            </button>
                                            <form action="{{ route('estimaciones.destroy', $estimacion->id_estimacion) }}" method="POST" class="d-inline" onsubmit="return confirm('¿Estás seguro de eliminar esta estimación?');">
                                                @csrf
                                                @method('DELETE')
                                                <button type="submit" class="btn btn-wood-action btn-delete">
                                                    <i class="fas fa-trash me-1"></i> Eliminar
                                                </button>
                                            </form>
                                        </div>
                                    </td>
                                </tr>
                                @endforeach
                            </tbody>
                        </table>
                    </div>
                    @else
                    <div class="text-center py-5">
                        <i class="fas fa-calculator fa-4x text-forest-light mb-4"></i>
                        <h5 class="text-forest-medium mb-3">No hay estimaciones registradas</h5>
                    </div>
                    @endif
                </div>

                <div class="tab-pane fade" id="turnos" role="tabpanel">
                    @if($parcela->turnosCorta->count() > 0)
                    <div class="table-responsive">
                        <table class="wood-table">
                            <thead>
                                <tr>
                                    <th class="ps-4">Código</th>
                                    <th>Fecha</th>
                                    <th>Estado</th>
                                    <th class="text-end pe-4">Acciones</th>
                                </tr>
                            </thead>
                            <tbody>
                                @foreach($parcela->turnosCorta as $turno)
                                <tr>
                                    <td class="ps-4">{{ $turno->codigo_corta }}</td>
                                    <td>{{ \Carbon\Carbon::parse($turno->fecha_corta)->format('d/m/Y') }}</td>
                                    <td>
                                        <span class="badge {{ $turno->completado ? 'bg-forest-accent text-dark' : 'bg-forest-medium text-white' }}">
                                            {{ $turno->completado ? 'Completado' : 'Pendiente' }}
                                        </span>
                                    </td>
                                    <td class="text-end pe-4">
                                        <button class="btn btn-wood-action btn-view" data-bs-toggle="modal" data-bs-target="#turnoModal{{ $turno->id_turno }}">
                                            <i class="fas fa-eye me-1"></i> Detalles
                                        </button>
                                    </td>
                                </tr>
                                @endforeach
                            </tbody>
                        </table>
                    </div>
                    @else
                    <div class="text-center py-5">
                        <i class="fas fa-calendar-alt fa-4x text-forest-light mb-4"></i>
                        <h5 class="text-forest-medium mb-3">No hay turnos programados</h5>
                         {{-- Este botón debería ir a un modal de creación de turno que no está en este bucle --}}
                        {{-- <button class="btn btn-wood rounded-pill px-4" data-bs-toggle="modal" data-bs-target="#addTurnoModal">
                            <i class="fas fa-plus me-2"></i> Programar Turno
                        </button> --}}
                    </div>
                    @endif
                </div>
            </div>
            
        </div> {{-- Cierre de .card-body --}}
    </div> {{-- Cierre de .wood-card --}}
</div>
{{-- (Los modales van aquí) --}}

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

@foreach($parcela->trozas as $troza)
    {{-- Este modal está duplicado. El modal "AddTroza" debería estar fuera del bucle foreach de trozas, solo necesita el id_parcela. 
         Lo muevo fuera del bucle y lo asocio a la parcela, no a la troza. --}}

    <div class="modal fade wood-detail-modal" id="trozaModal{{ $troza->id_troza }}" tabindex="-1" aria-hidden="true">
        <div class="modal-dialog modal-lg">
            <div class="modal-content wood-detail-card">
                <div class="modal-header wood-detail-header wood-bg-primary">
                    <div class="d-flex align-items-center w-100">
                        <div class="wood-detail-icon"><i class="fas fa-tree"></i></div>
                        <div class="flex-grow-1 ms-3">
                            <h5 class="wood-detail-title mb-0"> Detalles de Troza</h5>
                            <p class="wood-detail-subtitle mb-0">#{{ $troza->id_troza }} - {{ $troza->especie->nom_cientifico }}</p>
                        </div>
                        <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal" aria-label="Close"></button>
                    </div>
                </div>
                <div class="modal-body p-4">
                    <div class="row g-4">
                        <div class="col-md-6">
                            <div class="wood-detail-section">
                                <h6 class="wood-detail-section-title"><i class="fas fa-ruler-combined me-2"></i>Medidas Principales</h6>
                                <div class="wood-detail-list">
                                    <div class="wood-detail-item">
                                        <span class="wood-detail-label">Longitud:</span>
                                        <span class="wood-detail-value">{{ $troza->longitud }} m</span>
                                    </div>
                                    <div class="wood-detail-item">
                                        <span class="wood-detail-label">Diámetro:</span>
                                        <span class="wood-detail-value">{{ $troza->diametro }} m</span>
                                    </div>
                                    <div class="wood-detail-item">
                                        <span class="wood-detail-label">Densidad:</span>
                                        <span class="wood-detail-value">{{ $troza->densidad }}</span>
                                    </div>
                                </div>
                            </div>
                        </div>
                        <div class="col-md-6">
                            <div class="wood-detail-section">
                                <h6 class="wood-detail-section-title"><i class="fas fa-calculator me-2"></i>Cálculos</h6>
                                <div class="wood-detail-list">
                                    <div class="wood-detail-item">
                                        <span class="wood-detail-label">Volumen estimado:</span>
                                        <span class="wood-detail-value wood-highlight">
                                            {{ $troza->estimacion ? number_format($troza->estimacion->calculo, 4) : 'Sin cálculo' }} m³
                                        </span>
                                    </div>
                                    <div class="wood-detail-item">
                                        <span class="wood-detail-label">Fecha registro:</span>
                                        <span class="wood-detail-value">{{ $troza->created_at->format('d/m/Y') }}</span>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>
                    <div class="wood-detail-footer mt-4 pt-3 border-top">
                        <button type="button" class="btn btn-wood-outline" data-bs-dismiss="modal"><i class="fas fa-times me-2"></i>Cerrar</button>
                    </div>
                </div>
            </div>
        </div>
    </div>
    
    <div class="modal fade wood-edit-modal" id="editTrozaModal{{ $troza->id_troza }}" tabindex="-1" aria-hidden="true">
        <div class="modal-dialog modal-dialog-centered"> {{-- Modal-lg quitado para consistencia --}}
            <div class="modal-content wood-edit-card">
                <div class="modal-header wood-edit-header wood-bg-primary">
                    <div class="d-flex align-items-center w-100">
                        <div class="wood-edit-icon"><i class="fas fa-tree"></i></div>
                        <div class="flex-grow-1 ms-3">
                            <h5 class="wood-edit-title mb-0">Editar Troza</h5>
                            <p class="wood-edit-subtitle mb-0">#{{ $troza->id_troza }}</p>
                        </div>
                        <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal" aria-label="Close"></button>
                    </div>
                </div>
                <div class="modal-body p-4">
                    <form method="POST" action="{{ route('gestion.trozas.update', $troza->id_troza) }}">
                        @csrf
                        @method('PUT')
                        <input type="hidden" name="id_parcela" value="{{ $parcela->id_parcela }}">
                        
                        <div class="row g-3">
                            <div class="col-md-6">
                                <div class="wood-form-group">
                                    <label class="wood-form-label">Longitud (m)</label>
                                    <input type="number" step="0.01" class="wood-form-control" name="longitud" value="{{ $troza->longitud }}" required>
                                </div>
                            </div>
                            <div class="col-md-6">
                                <div class="wood-form-group">
                                    <label class="wood-form-label">Diámetro (m)</label>
                                    <input type="number" step="0.01" class="wood-form-control" name="diametro" value="{{ $troza->diametro }}" required>
                                </div>
                            </div>
                            <div class="col-12">
                                <div class="wood-form-group">
                                    <label class="wood-form-label">Densidad</label>
                                    <input type="number" step="0.01" class="wood-form-control" name="densidad" value="{{ $troza->densidad }}" required>
                                </div>
                            </div>
                            <div class="col-md-6">
                                <div class="wood-form-group">
                                    <label class="wood-form-label">Especie</label>
                                    <select class="wood-form-select" name="id_especie" required>
                                        @foreach ($especies as $especie)
                                            <option value="{{ $especie->id_especie }}" {{ $troza->id_especie == $especie->id_especie ? 'selected' : '' }}>
                                                {{ $especie->nom_cientifico }}
                                            </option>
                                        @endforeach
                                    </select>
                                </div>
                            </div>
                            <div class="col-md-6">
                                <div class="wood-form-group">
                                    <label class="wood-form-label">Parcela (Bloqueado)</label>
                                    <select class="wood-form-select" name="id_parcela" required disabled>
                                        <option value="{{ $parcela->id_parcela }}" selected>{{ $parcela->nom_parcela }}</option>
                                    </select>
                                </div>
                            </div>
                        </div>
                        
                        <div class="wood-edit-footer mt-4">
                            <button type="button" class="btn btn-wood-outline me-2" data-bs-dismiss="modal"><i class="fas fa-times me-2"></i> Cancelar</button>
                            <button type="submit" class="btn btn-wood-primary"><i class="fas fa-save me-2"></i> Guardar Cambios</button>
                        </div>
                    </form>
                </div>
            </div>
        </div>
    </div>
@endforeach

@foreach($parcela->estimaciones as $estimacion)
    <div class="modal fade wood-detail-modal" id="estimacionModal{{ $estimacion->id_estimacion }}" tabindex="-1" aria-hidden="true">
        <div class="modal-dialog modal-lg">
            <div class="modal-content wood-detail-card">
                <div class="modal-header wood-detail-header wood-bg-info">
                    <div class="d-flex align-items-center w-100">
                        <div class="wood-detail-icon"><i class="fas fa-calculator"></i></div>
                        <div class="flex-grow-1 ms-3">
                            <h5 class="wood-detail-title mb-0">Detalles de Estimación</h5>
                            <p class="wood-detail-subtitle mb-0">#{{ $estimacion->id_estimacion }} - {{ $estimacion->tipoEstimacion->desc_estimacion }}</p>
                        </div>
                        <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal" aria-label="Close"></button>
                    </div>
                </div>
                <div class="modal-body p-4">
                    <div class="row g-4">
                        <div class="col-md-6">
                            <div class="wood-detail-section">
                                <h6 class="wood-detail-section-title"><i class="fas fa-info-circle me-2"></i>Información Básica</h6>
                                <div class="wood-detail-list">
                                    <div class="wood-detail-item">
                                        <span class="wood-detail-label">Tipo:</span>
                                        <span class="wood-detail-value">{{ $estimacion->tipoEstimacion->desc_estimacion }}</span>
                                    </div>
                                    <div class="wood-detail-item">
                                        <span class="wood-detail-label">Fórmula:</span>
                                        <span class="wood-detail-value">{{ $estimacion->formula->nom_formula }}</span>
                                    </div>
                                    <div class="wood-detail-item">
                                        <span class="wood-detail-label">Troza asociada:</span>
                                        <span class="wood-detail-value">#{{ $estimacion->troza->id_troza }}</span>
                                    </div>
                                </div>
                            </div>
                        </div>
                        <div class="col-md-6">
                            <div class="wood-detail-section">
                                <h6 class="wood-detail-section-title"><i class="fas fa-chart-line me-2"></i>Resultados</h6>
                                <div class="wood-detail-list">
                                    <div class="wood-detail-item">
                                        <span class="wood-detail-label">Volumen calculado:</span>
                                        <span class="wood-detail-value wood-highlight">{{ number_format($estimacion->calculo, 4) }} m³</span>
                                    </div>
                                    <div class="wood-detail-item">
                                        <span class="wood-detail-label">Fecha cálculo:</span>
                                        <span class="wood-detail-value">{{ $estimacion->created_at->format('d/m/Y H:i') }}</span>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>
                    <div class="wood-detail-footer mt-4 pt-3 border-top">
                        <button type="button" class="btn btn-wood-outline me-2" data-bs-dismiss="modal"><i class="fas fa-times me-2"></i>Cerrar</button>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <div class="modal fade wood-edit-modal" id="editEstimacionModal{{ $estimacion->id_estimacion }}" tabindex="-1" aria-hidden="true">
        <div class="modal-dialog modal-dialog-centered modal-lg">
            <div class="modal-content wood-edit-card">
                <div class="modal-header wood-edit-header wood-bg-info"> {{-- Color cambiado a info --}}
                    <div class="d-flex align-items-center w-100">
                        <div class="wood-edit-icon"><i class="fas fa-calculator"></i></div>
                        <div class="flex-grow-1 ms-3">
                            <h5 class="wood-edit-title mb-0">Editar Estimación</h5>
                            <p class="wood-edit-subtitle mb-0">#{{ $estimacion->id_estimacion }}</p>
                        </div>
                        <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal" aria-label="Close"></button>
                    </div>
                </div>
                <div class="modal-body p-4">
                    <form method="POST" action="{{ route('gestion.estimaciones.update', $estimacion->id_estimacion) }}">
                        @csrf
                        @method('PUT')
                        <input type="hidden" name="id_parcela" value="{{ $parcela->id_parcela }}">
                        
                        <div class="row g-3">
                            <div class="col-md-6">
                                <div class="wood-form-group">
                                    <label class="wood-form-label">Tipo de Estimación</label>
                                    <select name="id_tipo_e" class="wood-form-select" required>
                                        @foreach ($tiposEstimacion as $tipo)
                                        <option value="{{ $tipo->id_tipo_e }}" {{ $tipo->id_tipo_e == $estimacion->id_tipo_e ? 'selected' : '' }}>
                                            {{ $tipo->desc_estimacion }}
                                        </option>
                                        @endforeach
                                    </select>
                                </div>
                            </div>
                            <div class="col-md-6">
                                <div class="wood-form-group">
                                    <label class="wood-form-label">Fórmula</label>
                                    <select name="id_formula" class="wood-form-select" required>
                                        @foreach ($formulas as $formula)
                                        <option value="{{ $formula->id_formula }}" {{ $formula->id_formula == $estimacion->id_formula ? 'selected' : '' }}>
                                            {{ $formula->nom_formula }}
                                        </option>
                                        @endforeach
                                    </select>
                                </div>
                            </div>
                            <div class="col-md-6">
                                <div class="wood-form-group">
                                    <label class="wood-form-label">Troza</label>
                                    <select name="id_troza" class="wood-form-select" required>
                                        @foreach ($parcela->trozas as $troza)
                                        <option value="{{ $troza->id_troza }}" {{ $troza->id_troza == $estimacion->id_troza ? 'selected' : '' }}>
                                            Troza #{{ $troza->id_troza }} ({{ $troza->especie->nom_cientifico }})
                                        </option>
                                        @endforeach
                                    </select>
                                </div>
                            </div>
                            <div class="col-md-6">
                                <div class="wood-form-group">
                                    <label class="wood-form-label">Cálculo (m³)</label>
                                    <input type="number" step="0.0001" class="wood-form-control" name="calculo" value="{{ old('calculo', $estimacion->calculo) }}" required>
                                </div>
                            </div>
                        </div>
                        
                        <div class="wood-edit-footer mt-4">
                            <button type="button" class="btn btn-wood-outline me-2" data-bs-dismiss="modal"><i class="fas fa-times me-2"></i> Cancelar</button>
                            <button type="submit" class="btn btn-wood-info"><i class="fas fa-save me-2"></i> Guardar Cambios</button>
                        </div>
                    </form>
                </div>
            </div>
        </div>
    </div>
@endforeach

@foreach($parcela->turnosCorta as $turno)
    <div class="modal fade wood-detail-modal" id="turnoModal{{ $turno->id_turno }}" tabindex="-1" aria-hidden="true">
        <div class="modal-dialog">
            <div class="modal-content wood-detail-card">
                <div class="modal-header wood-detail-header wood-bg-warning">
                    <div class="d-flex align-items-center w-100">
                        <div class="wood-detail-icon"><i class="fas fa-calendar-alt"></i></div>
                        <div class="flex-grow-1 ms-3">
                            <h5 class="wood-detail-title mb-0 text-dark">Turno de Corta</h5>
                            <p class="wood-detail-subtitle mb-0 text-dark opacity-75">{{ $turno->codigo_corta }}</p>
                        </div>
                        <button type="button" class="btn-close btn-close-dark" data-bs-dismiss="modal" aria-label="Close"></button>
                    </div>
                </div>
                <div class="modal-body p-4">
                    <div class="wood-detail-section">
                        <h6 class="wood-detail-section-title"><i class="fas fa-info-circle me-2"></i>Información del Turno</h6>
                        <div class="wood-detail-list">
                            <div class="wood-detail-item">
                                <span class="wood-detail-label">Parcela:</span>
                                <span class="wood-detail-value">{{ $parcela->nom_parcela }}</span>
                            </div>
                            <div class="wood-detail-item">
                                <span class="wood-detail-label">Fecha programada:</span>
                                <span class="wood-detail-value">{{ \Carbon\Carbon::parse($turno->fecha_corta)->format('d/m/Y') }}</span>
                            </div>
                            <div class="wood-detail-item">
                                <span class="wood-detail-label">Estado:</span>
                                <span class="wood-detail-badge {{ $turno->completado ? 'wood-badge-success' : 'wood-badge-warning' }}">
                                    {{ $turno->completado ? 'Completado' : 'Pendiente' }}
                                </span>
                            </div>
                            @if($turno->notas)
                            <div class="wood-detail-item d-block">
                                <span class="wood-detail-label d-block mb-1">Notas:</span>
                                <span class="wood-detail-value d-block" style="text-align: left;">{{ $turno->notas }}</span>
                            </div>
                            @endif
                        </div>
                    </div>
                    <div class="wood-detail-footer mt-4 pt-3 border-top">
                        <button type="button" class="btn btn-wood-outline" data-bs-dismiss="modal"><i class="fas fa-times me-2"></i>Cerrar</button>
                    </div>
                </div>
            </div>
        </div>
    </div>
@endforeach

{{-- [REFACTORIZACIÓN] El bloque <style> ha sido eliminado de aquí y movido a 'parcela-show.css' --}}
@endsection

{{-- [REFACTORIZACIÓN] El script de Tooltips se mueve al stack de scripts para cargar al final del body --}}
@push('scripts')
<script>
    document.addEventListener('DOMContentLoaded', function() {
        // Inicializar todos los tooltips de Bootstrap en la página
        var tooltipTriggerList = [].slice.call(document.querySelectorAll('[data-bs-toggle="tooltip"]'));
        var tooltipList = tooltipTriggerList.map(function (tooltipTriggerEl) {
            return new bootstrap.Tooltip(tooltipTriggerEl);
        });
    });
</script>
@endpush