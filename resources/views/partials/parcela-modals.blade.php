@php
    $parcela = $parcela ?? null;
@endphp

@if($parcela)
<!-- Modal Agregar Troza a Parcela Específica -->
<div class="modal fade modern-modal" id="addTrozaModal{{ $parcela->id_parcela }}" tabindex="-1">
    <div class="modal-dialog modal-dialog-centered modal-lg">
        <div class="modal-content">
            <div class="modal-header modal-header-success">
                <div class="modal-icon">
                    <i class="fas fa-tree"></i>
                </div>
                <div class="modal-title-content">
                    <h5 class="modal-title">Nueva Troza1</h5>
                    <p class="modal-subtitle">Parcela: <strong>{{ $parcela->nom_parcela }}</strong></p>
                </div>
                <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal"></button>
            </div>
            <div class="modal-body">
                <form method="POST" action="{{ route('tecnico.trozas.store') }}" class="modern-form">
                    @csrf
                    <input type="hidden" name="id_parcela" value="{{ $parcela->id_parcela }}">
                    
                    <div class="parcela-info-alert mb-4">
                        <div class="alert alert-light border">
                            <div class="d-flex align-items-center">
                                <i class="fas fa-info-circle text-success me-2"></i>
                                <div>
                                    <small class="fw-bold">Registrando troza para:</small>
                                    <div class="text-success">{{ $parcela->nom_parcela }} - {{ $parcela->ubicacion }}</div>
                                </div>
                            </div>
                        </div>
                    </div>

                    <div class="row g-3">
                       
                        <div class="col-md-6">
                            <label class="form-label">Especie *</label>
                            <select class="form-select modern-select" name="id_especie" required>
                                <option value="" selected disabled>Seleccione especie</option>
                                @foreach($especies as $especie)
                                    <option value="{{ $especie->id_especie }}">{{ $especie->nom_cientifico }}</option>
                                @endforeach
                            </select>
                        </div>
                        <div class="col-md-4">
                            <label class="form-label">Longitud (m) *</label>
                            <input type="number" step="0.001" name="longitud" class="form-control modern-input" placeholder="0.000" min="0" required>
                        </div>
                        <div class="col-md-4">
                            <label class="form-label">Diámetro (m) *</label>
                            <input type="number" step="0.001" name="diametro" class="form-control modern-input" placeholder="0.000" min="0" required>
                        </div>
                        <div class="col-md-4">
                            <label class="form-label">Densidad *</label>
                            <input type="number" step="0.001" name="densidad" class="form-control modern-input" placeholder="0.000" min="0" required>
                        </div>
                        <div class="col-md-6">
                            <label class="form-label">Diámetro Otro Extremo (m)</label>
                            <input type="number" step="0.001" name="diametro_otro_extremo" class="form-control modern-input" placeholder="Opcional">
                        </div>
                        <div class="col-md-6">
                            <label class="form-label">Diámetro Medio (m)</label>
                            <input type="number" step="0.001" name="diametro_medio" class="form-control modern-input" placeholder="Opcional">
                        </div>
                    </div>

                    <div class="preview-card mt-4">
                        <div class="card border">
                            <div class="card-body">
                                <h6 class="card-title"><i class="fas fa-eye me-2"></i>Vista Previa</h6>
                                <div class="row g-2 text-sm">
                                    <div class="col-6"><small>Parcela:</small></div>
                                    <div class="col-6"><small class="fw-bold">{{ $parcela->nom_parcela }}</small></div>
                                    <div class="col-6"><small>Ubicación:</small></div>
                                    <div class="col-6"><small class="fw-bold">{{ $parcela->ubicacion }}</small></div>
                                    <div class="col-6"><small>Extensión:</small></div>
                                    <div class="col-6"><small class="fw-bold">{{ $parcela->extension }} ha</small></div>
                                </div>
                            </div>
                        </div>
                    </div>

                    <div class="modal-footer modern-modal-footer">
                        <button type="button" class="btn btn-light" data-bs-dismiss="modal">
                            <i class="fas fa-times me-2"></i>Cancelar
                        </button>
                        <button type="submit" class="btn btn-success">
                            <i class="fas fa-check-circle me-2"></i>Registrar Troza
                        </button>
                    </div>
                </form>
            </div>
        </div>
    </div>
</div>

<!-- Modal Estimación para Parcela Específica -->
<div class="modal fade modern-modal" id="estimacionModal{{ $parcela->id_parcela }}" tabindex="-1">
    <div class="modal-dialog modal-dialog-centered modal-lg">
        <div class="modal-content">
            <div class="modal-header modal-header-info">
                <div class="modal-icon">
                    <i class="fas fa-calculator"></i>
                </div>
                <div class="modal-title-content">
                    <h5 class="modal-title">Estimación Volumétrica</h5>
                    <p class="modal-subtitle">Parcela: <strong>{{ $parcela->nom_parcela }}</strong></p>
                </div>
                <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal"></button>
            </div>
            <div class="modal-body">
                <form method="POST" action="{{ route('tecnico.estimaciones.store') }}" class="modern-form" id="estimacionForm{{ $parcela->id_parcela }}">
                    @csrf
                    <input type="hidden" name="id_parcela" value="{{ $parcela->id_parcela }}">
                    
                    <div class="row g-3">
                        <div class="col-md-6">
                            <label class="form-label">Seleccionar Troza *</label>
                            <select class="form-select modern-select" name="id_troza" required id="trozaSelect{{ $parcela->id_parcela }}">
                                <option value="" selected disabled>Cargando trozas...</option>
                            </select>
                            <small class="form-text text-muted">Trozas disponibles en esta parcela</small>
                        </div>
                        <div class="col-md-6">
                            <label class="form-label">Tipo de Estimación *</label>
                            <select class="form-select modern-select" name="id_tipo_e" required>
                                <option value="" selected disabled>Seleccione tipo</option>
                                @foreach($tiposEstimacion as $tipo)
                                    <option value="{{ $tipo->id_tipo_e }}">{{ $tipo->desc_estimacion }}</option>
                                @endforeach
                            </select>
                        </div>
                        <div class="col-md-6">
                            <label class="form-label">Fórmula a Aplicar *</label>
                            <select class="form-select modern-select" name="id_formula" required>
                                <option value="" selected disabled>Seleccione fórmula</option>
                                @foreach($formulas as $formula)
                                    <option value="{{ $formula->id_formula }}" data-descripcion="{{ $formula->desc_formula ?? '' }}">
                                        {{ $formula->nom_formula }}
                                    </option>
                                @endforeach
                            </select>
                        </div>
                        <div class="col-md-6">
                            <label class="form-label">Cálculo (m³) *</label>
                            <input type="number" step="0.0001" name="calculo" class="form-control modern-input" placeholder="0.0000" required>
                        </div>
                        <div class="col-md-6">
                            <label class="form-label">Biomasa (kg)</label>
                            <input type="number" step="0.001" name="biomasa" class="form-control modern-input" placeholder="Opcional">
                        </div>
                        <div class="col-md-6">
                            <label class="form-label">Carbono (kg)</label>
                            <input type="number" step="0.001" name="carbono" class="form-control modern-input" placeholder="Opcional">
                        </div>
                    </div>

                    <!-- Información de la Troza Seleccionada -->
                    <div class="troza-info mt-4" id="trozaInfo{{ $parcela->id_parcela }}" style="display: none;">
                        <div class="card border-info">
                            <div class="card-header bg-info text-white py-2">
                                <small><i class="fas fa-info-circle me-1"></i>Información de la Troza Seleccionada</small>
                            </div>
                            <div class="card-body py-3">
                                <div class="row g-2 text-sm" id="trozaDetails{{ $parcela->id_parcela }}">
                                    <!-- Los detalles se cargarán via JavaScript -->
                                </div>
                            </div>
                        </div>
                    </div>

                    <div class="modal-footer modern-modal-footer">
                        <button type="button" class="btn btn-light" data-bs-dismiss="modal">
                            <i class="fas fa-times me-2"></i>Cancelar
                        </button>
                        <button type="submit" class="btn btn-info">
                            <i class="fas fa-calculator me-2"></i>Calcular Estimación
                        </button>
                    </div>
                </form>
            </div>
        </div>
    </div>
</div>

<!-- Modal Agregar Árbol a Parcela Específica -->
<div class="modal fade modern-modal" id="addArbolModal{{ $parcela->id_parcela }}" tabindex="-1">
    <div class="modal-dialog modal-dialog-centered">
        <div class="modal-content">
            <div class="modal-header modal-header-primary">
                <div class="modal-icon">
                    <i class="fas fa-seedling"></i>
                </div>
                <div class="modal-title-content">
                    <h5 class="modal-title">Registrar Árbol</h5>
                    <p class="modal-subtitle">Parcela: <strong>{{ $parcela->nom_parcela }}</strong></p>
                </div>
                <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal"></button>
            </div>
            <div class="modal-body">
                <form method="POST" action="{{ route('tecnico.arboles.store') }}" class="modern-form">
                    @csrf
                    <input type="hidden" name="id_parcela" value="{{ $parcela->id_parcela }}">
                    
                    <div class="row g-3">
                        <div class="col-md-6">
                            <label class="form-label">Especie *</label>
                            <select class="form-select modern-select" name="id_especie" required>
                                <option value="" selected disabled>Seleccione especie</option>
                                @foreach($especies as $especie)
                                    <option value="{{ $especie->id_especie }}">{{ $especie->nom_cientifico }}</option>
                                @endforeach
                            </select>
                        </div>
                        <div class="col-md-6">
                            <label class="form-label">Altura Total (m) *</label>
                            <input type="number" step="0.1" name="altura_total" class="form-control modern-input" placeholder="0.0" min="0" max="150" required>
                        </div>
                        <div class="col-12">
                            <label class="form-label">Diámetro a la Altura del Pecho (m) *</label>
                            <input type="number" step="0.01" name="diametro_pecho" class="form-control modern-input" placeholder="0.00" min="0" max="10" required>
                            <small class="form-text text-muted">Medición estándar a 1.3 metros del suelo</small>
                        </div>
                    </div>

                    <div class="specs-grid mt-4">
                        <div class="row g-2">
                            <div class="col-6">
                                <div class="spec-item text-center p-3 border rounded">
                                    <i class="fas fa-ruler-vertical text-primary mb-2"></i>
                                    <div class="spec-value">Altura Máx: 150m</div>
                                    <small class="text-muted">Límite permitido</small>
                                </div>
                            </div>
                            <div class="col-6">
                                <div class="spec-item text-center p-3 border rounded">
                                    <i class="fas fa-ruler-combined text-success mb-2"></i>
                                    <div class="spec-value">DAP Máx: 10m</div>
                                    <small class="text-muted">Diámetro máximo</small>
                                </div>
                            </div>
                        </div>
                    </div>

                    <div class="modal-footer modern-modal-footer">
                        <button type="button" class="btn btn-light" data-bs-dismiss="modal">
                            <i class="fas fa-times me-2"></i>Cancelar
                        </button>
                        <button type="submit" class="btn btn-primary">
                            <i class="fas fa-tree me-2"></i>Registrar Árbol
                        </button>
                    </div>
                </form>
            </div>
        </div>
    </div>
</div>

<!-- Modal Estimación para Árbol -->
<div class="modal fade modern-modal" id="estimacionArbolModal{{ $parcela->id_parcela }}" tabindex="-1">
    <div class="modal-dialog modal-dialog-centered">
        <div class="modal-content">
            <div class="modal-header modal-header-warning">
                <div class="modal-icon">
                    <i class="fas fa-calculator"></i>
                </div>
                <div class="modal-title-content">
                    <h5 class="modal-title">Estimación para Árbol</h5>
                    <p class="modal-subtitle">Parcela: <strong>{{ $parcela->nom_parcela }}</strong></p>
                </div>
                <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal"></button>
            </div>
            <div class="modal-body">
                <form method="POST" action="{{ route('tecnico.estimaciones-arbol.store') }}" class="modern-form">
                    @csrf
                    <div class="row g-3">
                        <div class="col-12">
                            <label class="form-label">Seleccionar Árbol *</label>
                            <select class="form-select modern-select" name="id_arbol" required>
                                <option value="" selected disabled>Seleccione árbol</option>
                                @foreach($parcela->arboles as $arbol)
                                    <option value="{{ $arbol->id_arbol }}">
                                        Árbol #{{ $arbol->id_arbol }} - {{ $arbol->especie->nom_cientifico ?? 'N/A' }} 
                                        ({{ $arbol->altura_total }}m x {{ $arbol->diametro_pecho }}m)
                                    </option>
                                @endforeach
                            </select>
                        </div>
                        <div class="col-md-6">
                            <label class="form-label">Tipo de Estimación *</label>
                            <select class="form-select modern-select" name="id_tipo_e" required>
                                <option value="" selected disabled>Seleccione tipo</option>
                                @foreach($tiposEstimacion as $tipo)
                                    <option value="{{ $tipo->id_tipo_e }}">{{ $tipo->desc_estimacion }}</option>
                                @endforeach
                            </select>
                        </div>
                        <div class="col-md-6">
                            <label class="form-label">Fórmula *</label>
                            <select class="form-select modern-select" name="id_formula" required>
                                <option value="" selected disabled>Seleccione fórmula</option>
                                @foreach($formulas as $formula)
                                    <option value="{{ $formula->id_formula }}">{{ $formula->nom_formula }}</option>
                                @endforeach
                            </select>
                        </div>
                        <div class="col-12">
                            <label class="form-label">Cálculo (m³) *</label>
                            <input type="number" step="0.0001" name="calculo" class="form-control modern-input" placeholder="0.0000" required>
                        </div>
                    </div>
                    <div class="modal-footer modern-modal-footer">
                        <button type="button" class="btn btn-light" data-bs-dismiss="modal">
                            <i class="fas fa-times me-2"></i>Cancelar
                        </button>
                        <button type="submit" class="btn btn-warning">
                            <i class="fas fa-calculator me-2"></i>Calcular Estimación
                        </button>
                    </div>
                </form>
            </div>
        </div>
    </div>
</div>

<!-- Modal Turno de Corta -->
<div class="modal fade modern-modal" id="turnoModal{{ $parcela->id_parcela }}" tabindex="-1">
    <div class="modal-dialog modal-dialog-centered">
        <div class="modal-content">
            <div class="modal-header modal-header-dark">
                <div class="modal-icon">
                    <i class="fas fa-calendar-alt"></i>
                </div>
                <div class="modal-title-content">
                    <h5 class="modal-title">Programar Turno de Corta</h5>
                    <p class="modal-subtitle">Parcela: <strong>{{ $parcela->nom_parcela }}</strong></p>
                </div>
                <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal"></button>
            </div>
            <div class="modal-body">
                <form method="POST" action="{{ route('tecnico.turno-cortas.store') }}" class="modern-form">
                    @csrf
                    <input type="hidden" name="id_parcela" value="{{ $parcela->id_parcela }}">
                    
                    <div class="row g-3">
                        <div class="col-md-6">
                            <label class="form-label">Código de Corta *</label>
                            <input type="text" name="codigo_corta" class="form-control modern-input" placeholder="CORT-{{ $parcela->id_parcela }}-001" required>
                        </div>
                        <div class="col-md-6">
                            <label class="form-label">Fecha de Corta *</label>
                            <input type="date" name="fecha_corta" class="form-control modern-input" required>
                        </div>
                        <div class="col-12">
                            <label class="form-label">Fecha de Finalización *</label>
                            <input type="date" name="fecha_fin" class="form-control modern-input" required>
                            <small class="form-text text-muted">Debe ser posterior a la fecha de inicio</small>
                        </div>
                        <div class="col-12">
                            <label class="form-label">Observaciones</label>
                            <textarea class="form-control modern-input" name="observaciones" rows="3" placeholder="Notas adicionales sobre el turno de corta..."></textarea>
                        </div>
                    </div>

                    <div class="alert alert-light border mt-3">
                        <div class="d-flex align-items-center">
                            <i class="fas fa-exclamation-triangle text-warning me-2"></i>
                            <div>
                                <small class="fw-bold">Información Importante:</small>
                                <div class="text-sm text-muted">
                                    El período de corta no debe exceder los 365 días según regulaciones forestales.
                                </div>
                            </div>
                        </div>
                    </div>

                    <div class="modal-footer modern-modal-footer">
                        <button type="button" class="btn btn-light" data-bs-dismiss="modal">
                            <i class="fas fa-times me-2"></i>Cancelar
                        </button>
                        <button type="submit" class="btn btn-dark">
                            <i class="fas fa-calendar-check me-2"></i>Programar Turno
                        </button>
                    </div>
                </form>
            </div>
        </div>
    </div>
</div>
@endif