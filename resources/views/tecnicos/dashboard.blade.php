@extends('layouts.app')

@section('content')
<div class="container-fluid py-4">
    <!-- Encabezado Premium -->
    <div class="row mb-4">
        <div class="col-12">
            <div class="card bg-gradient-primary shadow-lg border-0">
                <div class="card-body p-4 d-flex justify-content-between align-items-center">
                    <div>
                        <h4 class="text-white mb-1"><i class="fas fa-tree me-2"></i> Panel del Técnico Forestal</h4>
                        <p class="text-white opacity-8 mb-0">Gestión especializada de recursos maderables</p>
                    </div>
                    <button class="btn btn-light rounded-pill shadow-sm" data-bs-toggle="modal" data-bs-target="#createParcelaModal">
                        <i class="fas fa-plus me-2"></i> Nueva Parcela
                    </button>
                </div>
            </div>
        </div>
    </div>

    <!-- Tarjeta de Información del Técnico -->
    <div class="row mb-4">
        <div class="col-md-12">
            <div class="card shadow-sm border-0 h-100">
                <div class="card-body p-4">
                    <div class="d-flex align-items-center mb-3">
                        <div class="icon icon-shape icon-lg bg-primary text-white rounded-circle me-3">
                            <i class="fas fa-user-tie"></i>
                        </div>
                        <div>
                            <h3 class="text-dark mb-0">Técnico {{ $user->persona->nom ?? 'Usuario' }}</h3>
                            <p class="text-sm text-muted mb-0">
                                <i class="fas fa-id-card me-1"></i>
                                Cédula: {{ $tecnico->cedula_p ?? 'No disponible' }}
                            </p>
                        </div>
                    </div>
                    <hr>
                    <div class="row">
                        <div class="col-md-4">
                            <div class="d-flex align-items-center mb-3">
                                <div class="icon icon-shape icon-sm bg-gradient-info text-white rounded-circle me-3">
                                    <i class="fas fa-map-marked-alt"></i>
                                </div>
                                <div>
                                    <h5 class="mb-0">{{ $parcelas->count() }}</h5>
                                    <p class="text-sm text-muted mb-0">Parcelas asignadas</p>
                                </div>
                            </div>
                        </div>
                        <div class="col-md-4">
                            <div class="d-flex align-items-center mb-3">
                                <div class="icon icon-shape icon-sm bg-gradient-success text-white rounded-circle me-3">
                                    <i class="fas fa-cut"></i>
                                </div>
                                <div>
                                    <h5 class="mb-0">{{ $totalTrozas }}</h5>
                                    <p class="text-sm text-muted mb-0">Trozas registradas</p>
                                </div>
                            </div>
                        </div>
                        <div class="col-md-4">
                            <div class="d-flex align-items-center mb-3">
                                <div class="icon icon-shape icon-sm bg-gradient-warning text-white rounded-circle me-3">
                                    <i class="fas fa-calendar-alt"></i>
                                </div>
                                <div>
                                    <h5 class="mb-0">{{ $turnosCount ?? 0 }}</h5>
                                    <p class="text-sm text-muted mb-0">Turnos programados</p>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <!-- Listado de Parcelas -->
    <div class="row">
        <div class="col-12">
            <div class="card shadow-sm border-0">
                <div class="card-header bg-white border-bottom">
                    <div class="d-flex justify-content-between align-items-center">
                        <h5 class="mb-0">
                            <i class="fas fa-map me-2 text-primary"></i>Parcelas Asignadas
                        </h5>
                        <div class="input-group input-group-outline" style="width: 300px;">
                            <input type="text" class="form-control form-control-sm" placeholder="Buscar parcela...">
                            <button class="btn btn-sm btn-primary" type="button">
                                <i class="fas fa-search"></i>
                            </button>
                        </div>
                    </div>
                </div>
                <div class="card-body p-0">
                    <div class="table-responsive">
                        <table class="table align-items-center mb-0">
                            <thead class="bg-gray-100">
                                <tr>
                                    <th class="text-uppercase text-secondary text-xs font-weight-bolder ps-4">Nombre</th>
                                    <th class="text-uppercase text-secondary text-xs font-weight-bolder">Ubicación</th>
                                    <th class="text-uppercase text-secondary text-xs font-weight-bolder">Trozas</th>
                                    <th class="text-uppercase text-secondary text-xs font-weight-bolder">Extensión</th>
                                    <th class="text-uppercase text-secondary text-xs font-weight-bolder text-end pe-4">Acciones</th>
                                </tr>
                            </thead>
                            <tbody>
                                @forelse($parcelas as $parcela)
                                <tr>
                                    <td class="ps-4">
                                        <div class="d-flex align-items-center">
                                            <div class="icon icon-shape icon-sm bg-gradient-info text-white rounded-circle me-2">
                                                <i class="fas fa-map"></i>
                                            </div>
                                            <div>
                                                <span class="font-weight-bold">{{ $parcela->nom_parcela }}</span>
                                                <p class="text-xs text-muted mb-0">Código: {{ $parcela->id_parcela }}</p>
                                            </div>
                                        </div>
                                    </td>
                                    <td>
                                        <span class="text-xs font-weight-bold">{{ $parcela->ubicacion }}</span>
                                    </td>
                                    <td>
                                        <span class="badge bg-gradient-success rounded-pill">{{ $parcela->trozas_count }}</span>
                                    </td>
                                    <td>
                                        <span class="text-xs font-weight-bold">{{ $parcela->extension }} ha</span>
                                    </td>
                                    <td class="pe-4 text-end">
                                        <div class="btn-group" role="group">
                                            <button class="btn btn-sm btn-outline-primary rounded-start-pill px-3" 
                                                    data-bs-toggle="modal" 
                                                    data-bs-target="#addTrozaModal{{ $parcela->id_parcela }}"
                                                    data-bs-tooltip="tooltip" data-bs-placement="top" title="Agregar troza">
                                                <i class="fas fa-plus"></i>
                                            </button>
                                            <button class="btn btn-sm btn-outline-info px-3" 
                                                    data-bs-toggle="modal" 
                                                    data-bs-target="#estimacionModal{{ $parcela->id_parcela }}"
                                                    data-bs-tooltip="tooltip" data-bs-placement="top" title="Realizar estimación">
                                                <i class="fas fa-calculator"></i>
                                            </button>
                                            <button class="btn btn-sm btn-outline-warning px-3" 
                                                    data-bs-toggle="modal" 
                                                    data-bs-target="#turnoModal{{ $parcela->id_parcela }}"
                                                    data-bs-tooltip="tooltip" data-bs-placement="top" title="Programar turno">
                                                <i class="fas fa-calendar-alt"></i>
                                            </button>
                                            <a href="{{ route('parcelas.show', $parcela->id_parcela) }}" 
                                               class="btn btn-sm btn-outline-secondary rounded-end-pill px-3"
                                               data-bs-tooltip="tooltip" data-bs-placement="top" title="Ver detalles">
                                                <i class="fas fa-eye"></i>
                                            </a>
                                        </div>
                                    </td>
                                </tr>
                                @empty
                                <tr>
                                    <td colspan="5" class="text-center py-4">
                                        <div class="text-center">
                                            <i class="fas fa-map-marked-alt fa-3x text-gray-300 mb-3"></i>
                                            <h5 class="text-gray-500">No tienes parcelas asignadas</h5>
                                            <button class="btn btn-primary rounded-pill mt-2" data-bs-toggle="modal" data-bs-target="#createParcelaModal">
                                                <i class="fas fa-plus me-1"></i> Asignar Parcela
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
                <div class="card-footer bg-white border-top">
                    {{ $parcelas->links() }}
                </div>
                @endif
            </div>
        </div>
    </div>
</div>

<!-- Modal para crear parcela -->
<div class="modal fade" id="createParcelaModal" tabindex="-1" aria-labelledby="createParcelaLabel" aria-hidden="true">
    <div class="modal-dialog modal-dialog-centered">
        <div class="modal-content border-0 shadow-lg">
            <div class="modal-header bg-gradient-primary text-white">
                <h5 class="modal-title">
                    <i class="fas fa-map-marked-alt me-2"></i>Nueva Parcela
                </h5>
                <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal" aria-label="Close"></button>
            </div>
            <div class="modal-body p-4">
                <form method="POST" action="{{ route('parcelas.store') }}">
                    @csrf
                    <div class="mb-3">
                        <label class="form-label">Nombre de la parcela</label>
                        <input type="text" name="nom_parcela" class="form-control" required>
                    </div>
                    <div class="row">
                        <div class="col-md-8 mb-3">
                            <label class="form-label">Ubicación</label>
                            <input type="text" name="ubicacion" class="form-control" required>
                        </div>
                        <div class="col-md-4 mb-3">
                            <label class="form-label">Extensión (ha)</label>
                            <input type="number" step="0.01" name="extension" class="form-control" required>
                        </div>
                    </div>
                    <div class="mb-3">
                        <label class="form-label">Dirección</label>
                        <input type="text" name="direccion" class="form-control">
                    </div>
                    <div class="row">
                        <div class="col-md-6 mb-3">
                            <label class="form-label">Código Postal</label>
                            <input type="text" name="CP" class="form-control">
                        </div>
                        <div class="col-md-6 mb-3">
                            <label class="form-label">Productor</label>
                            <select class="form-select" name="id_productor">
                                <option value="" selected disabled>Seleccionar productor</option>
                                <!-- Aquí irían los productores -->
                            </select>
                        </div>
                    </div>
                    <div class="d-grid gap-2 d-md-flex justify-content-md-end mt-4">
                        <button type="button" class="btn btn-outline-secondary me-md-2 rounded-pill"
                                data-bs-dismiss="modal">Cancelar</button>
                        <button type="submit" class="btn btn-primary rounded-pill">
                            <i class="fas fa-save me-1"></i> Registrar Parcela
                        </button>
                    </div>
                </form>
            </div>
        </div>
    </div>
</div>

<!-- Modales para acciones por parcela -->
@foreach($parcelas as $parcela)
<!-- Modal para agregar troza -->
<div class="modal fade" id="addTrozaModal{{ $parcela->id_parcela }}" tabindex="-1" aria-hidden="true">
    <div class="modal-dialog modal-dialog-centered">
        <div class="modal-content border-0 shadow-lg">
            <div class="modal-header bg-gradient-primary text-white">
                <h5 class="modal-title">
                    <i class="fas fa-plus me-2"></i>Nueva Troza en {{ $parcela->nom_parcela }}
                </h5>
                <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal" aria-label="Close"></button>
            </div>
            <div class="modal-body p-4">
                <form method="POST" action="{{ route('trozas.store') }}">
                    @csrf
                    <input type="hidden" name="id_parcela" value="{{ $parcela->id_parcela }}">
                    <div class="row">
                        <div class="col-md-6 mb-3">
                            <label class="form-label">Longitud (m)</label>
                            <input type="number" step="0.01" name="longitud" class="form-control" required>
                        </div>
                        <div class="col-md-6 mb-3">
                            <label class="form-label">Diámetro (m)</label>
                            <input type="number" step="0.01" name="diametro" class="form-control" required>
                        </div>
                    </div>
                    <div class="row">
                        <div class="col-md-6 mb-3">
                            <label class="form-label">Diámetro otro extremo (m)</label>
                            <input type="number" step="0.01" name="diametro_otro_extremo" class="form-control">
                        </div>
                        <div class="col-md-6 mb-3">
                            <label class="form-label">Diámetro medio (m)</label>
                            <input type="number" step="0.01" name="diametro_medio" class="form-control">
                        </div>
                    </div>
                    <div class="mb-3">
                        <label class="form-label">Densidad</label>
                        <input type="number" step="0.01" name="densidad" class="form-control" required>
                    </div>
                    <div class="mb-3">
                        <label class="form-label">Especie</label>
                        <select class="form-select" name="id_especie" required>
                            <option value="" selected disabled>Seleccionar especie</option>
                            <!-- Aquí irían las especies -->
                        </select>
                    </div>
                    <div class="d-grid gap-2 d-md-flex justify-content-md-end mt-4">
                        <button type="button" class="btn btn-outline-secondary me-md-2 rounded-pill"
                                data-bs-dismiss="modal">Cancelar</button>
                        <button type="submit" class="btn btn-primary rounded-pill">
                            <i class="fas fa-check-circle me-1"></i> Registrar Troza
                        </button>
                    </div>
                </form>
            </div>
        </div>
    </div>
</div>

<!-- Modal para estimación -->
<div class="modal fade" id="estimacionModal{{ $parcela->id_parcela }}" tabindex="-1" aria-hidden="true">
    <div class="modal-dialog modal-dialog-centered">
        <div class="modal-content border-0 shadow-lg">
            <div class="modal-header bg-gradient-info text-white">
                <h5 class="modal-title">
                    <i class="fas fa-calculator me-2"></i>Estimación para {{ $parcela->nom_parcela }}
                </h5>
                <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal" aria-label="Close"></button>
            </div>
            <div class="modal-body p-4">
                <form method="POST" action="{{ route('estimaciones.store') }}">
                    @csrf
                    <input type="hidden" name="id_parcela" value="{{ $parcela->id_parcela }}">
                    <div class="mb-3">
                        <label class="form-label">Seleccionar Troza</label>
                        <select class="form-select" name="id_troza" required>
                            <option value="" selected disabled>Seleccione una troza</option>
                            @foreach($parcela->trozas as $troza)
                            <option value="{{ $troza->id_troza }}">Troza #{{ $troza->id_troza }} ({{ $troza->longitud }}m x {{ $troza->diametro }}m)</option>
                            @endforeach
                        </select>
                    </div>
                    <div class="row">
                        <div class="col-md-6 mb-3">
                            <label class="form-label">Tipo de Estimación</label>
                            <select class="form-select" name="id_tipo_e" required>
                                <option value="" selected disabled>Seleccione un tipo</option>
                                @foreach($tiposEstimacion as $tipo)
                                <option value="{{ $tipo->id_tipo_e }}">{{ $tipo->desc_estimacion }}</option>
                                @endforeach
                            </select>
                        </div>
                        <div class="col-md-6 mb-3">
                            <label class="form-label">Fórmula a Aplicar</label>
                            <select class="form-select" name="id_formula" required>
                                <option value="" selected disabled>Seleccione una fórmula</option>
                                @foreach($formulas as $formula)
                                <option value="{{ $formula->id_formula }}">{{ $formula->nom_formula }}</option>
                                @endforeach
                            </select>
                        </div>
                    </div>
                    <div class="mb-3">
                        <label class="form-label">Cálculo (opcional)</label>
                        <textarea class="form-control" name="calculo" rows="2"></textarea>
                    </div>
                    <div class="d-grid gap-2 d-md-flex justify-content-md-end mt-4">
                        <button type="button" class="btn btn-outline-secondary me-md-2 rounded-pill"
                                data-bs-dismiss="modal">Cancelar</button>
                        <button type="submit" class="btn btn-info rounded-pill">
                            <i class="fas fa-calculator me-1"></i> Calcular Estimación
                        </button>
                    </div>
                </form>
            </div>
        </div>
    </div>
</div>

<!-- Modal para turno de corta -->
<div class="modal fade" id="turnoModal{{ $parcela->id_parcela }}" tabindex="-1" aria-hidden="true">
    <div class="modal-dialog modal-dialog-centered">
        <div class="modal-content border-0 shadow-lg">
            <div class="modal-header bg-gradient-warning text-white">
                <h5 class="modal-title">
                    <i class="fas fa-calendar-alt me-2"></i>Programar Turno de Corta para {{ $parcela->nom_parcela }}
                </h5>
                <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal" aria-label="Close"></button>
            </div>
            <div class="modal-body p-4">
                <form method="POST" action="{{ route('turno_cortas.store') }}">
                    @csrf
                    <input type="hidden" name="id_parcela" value="{{ $parcela->id_parcela }}">
                    <div class="mb-3">
                        <label class="form-label">Código de Corta</label>
                        <input type="text" name="codigo_corta" class="form-control" required>
                    </div>
                    <div class="mb-3">
                        <label class="form-label">Fecha de Corta</label>
                        <input type="date" name="fecha_corta" class="form-control" required>
                    </div>
                    <div class="mb-3">
                        <label class="form-label">Notas adicionales</label>
                        <textarea class="form-control" name="notas" rows="2"></textarea>
                    </div>
                    <div class="d-grid gap-2 d-md-flex justify-content-md-end mt-4">
                        <button type="button" class="btn btn-outline-secondary me-md-2 rounded-pill"
                                data-bs-dismiss="modal">Cancelar</button>
                        <button type="submit" class="btn btn-warning rounded-pill">
                            <i class="fas fa-calendar-check me-1"></i> Programar Turno
                        </button>
                    </div>
                </form>
            </div>
        </div>
    </div>
</div>
@endforeach

<!-- Scripts necesarios -->
<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/js/bootstrap.bundle.min.js"></script>
<script>
    // Inicializar tooltips
    document.addEventListener('DOMContentLoaded', function() {
        var tooltipTriggerList = [].slice.call(document.querySelectorAll('[data-bs-toggle="tooltip"]'));
        var tooltipList = tooltipTriggerList.map(function (tooltipTriggerEl) {
            return new bootstrap.Tooltip(tooltipTriggerEl);
        });
    });
</script>

<style>
    /* Estilos personalizados */
    .card {
        border: none;
        border-radius: 12px;
        transition: transform 0.3s ease, box-shadow 0.3s ease;
    }
    
    .card:hover {
        transform: translateY(-5px);
        box-shadow: 0 10px 20px rgba(0,0,0,0.1);
    }
    
    .icon-shape {
        display: inline-flex;
        align-items: center;
        justify-content: center;
    }
    
    .bg-gradient-primary {
        background: linear-gradient(135deg, #4e73df 0%, #224abe 100%);
    }
    
    .bg-gradient-info {
        background: linear-gradient(135deg, #36b9cc 0%, #258391 100%);
    }
    
    .bg-gradient-success {
        background: linear-gradient(135deg, #1cc88a 0%, #13855c 100%);
    }
    
    .bg-gradient-warning {
        background: linear-gradient(135deg, #f6c23e 0%, #dda20a 100%);
    }
    
    .table thead th {
        border-bottom-width: 1px;
        font-weight: 600;
    }
    
    .table tbody tr {
        transition: background-color 0.2s ease;
    }
    
    .table tbody tr:hover {
        background-color: rgba(0,0,0,0.02);
    }
    
    .btn-group .btn {
        transition: all 0.2s ease;
    }
    
    .btn-group .btn:hover {
        transform: translateY(-2px);
    }
    
    .modal-content {
        border-radius: 12px;
        overflow: hidden;
    }
    
    .form-control, .form-select {
        border-radius: 8px;
        padding: 10px 15px;
    }
    
    .form-control:focus, .form-select:focus {
        box-shadow: 0 0 0 0.25rem rgba(78, 115, 223, 0.25);
    }
</style>
@endsection