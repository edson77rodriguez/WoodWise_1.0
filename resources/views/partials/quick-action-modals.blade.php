<!-- Quick Add Troza Modal -->
<div class="modal fade modern-modal" id="quickTrozaModal" tabindex="-1">
    <div class="modal-dialog modal-dialog-centered modal-lg">
        <div class="modal-content">
            <div class="modal-header modal-header-success">
                <div class="modal-icon">
                    <i class="fas fa-tree"></i>
                </div>
                <div class="modal-title-content">
                    <h5 class="modal-title">Agregar Troza Rápida</h5>
                    <p class="modal-subtitle">Registro rápido de troza en parcela existente</p>
                </div>
                <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal"></button>
            </div>
            <div class="modal-body">
                <form method="POST" action="{{ route('tecnico.trozas.store') }}" class="modern-form" id="quickTrozaForm">
                    @csrf
                    <div class="row g-3">
                        <div class="col-md-6">
                            <label class="form-label">Seleccionar Parcela *</label>
                            <select class="form-select modern-select" name="id_parcela" required id="quickParcelaSelect">
                                <option value="" selected disabled>Seleccione parcela</option>
                                @foreach($parcelas as $parcela)
                                    <option value="{{ $parcela->id_parcela }}">{{ $parcela->nom_parcela }}</option>
                                @endforeach
                            </select>
                        </div>
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
                            <input type="number" step="0.001" name="longitud" class="form-control modern-input" placeholder="0.000" required>
                        </div>
                        <div class="col-md-4">
                            <label class="form-label">Diámetro (m) *</label>
                            <input type="number" step="0.001" name="diametro" class="form-control modern-input" placeholder="0.000" required>
                        </div>
                        <div class="col-md-4">
                            <label class="form-label">Densidad *</label>
                            <input type="number" step="0.001" name="densidad" class="form-control modern-input" placeholder="0.000" required>
                        </div>
                        <div class="col-md-6">
                            <label class="form-label">Código de Troza *</label>
                            <input type="text" name="codigo_troza" class="form-control modern-input" placeholder="TRZ-001" required>
                        </div>
                        <div class="col-md-6">
                            <label class="form-label">Diámetro Medio (m)</label>
                            <input type="number" step="0.001" name="diametro_medio" class="form-control modern-input" placeholder="Opcional">
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

<!-- Quick Add Árbol Modal -->
<div class="modal fade modern-modal" id="quickArbolModal" tabindex="-1">
    <div class="modal-dialog modal-dialog-centered">
        <div class="modal-content">
            <div class="modal-header modal-header-info">
                <div class="modal-icon">
                    <i class="fas fa-seedling"></i>
                </div>
                <div class="modal-title-content">
                    <h5 class="modal-title">Registrar Árbol</h5>
                    <p class="modal-subtitle">Agregar árbol individual a parcela existente</p>
                </div>
                <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal"></button>
            </div>
            <div class="modal-body">
                <form method="POST" action="{{ route('tecnico.arboles.store') }}" class="modern-form">
                    @csrf
                    <div class="row g-3">
                        <div class="col-md-6">
                            <label class="form-label">Parcela *</label>
                            <select class="form-select modern-select" name="id_parcela" required>
                                <option value="" selected disabled>Seleccione parcela</option>
                                @foreach($parcelas as $parcela)
                                    <option value="{{ $parcela->id_parcela }}">{{ $parcela->nom_parcela }}</option>
                                @endforeach
                            </select>
                        </div>
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
                            <small class="form-text text-muted">Máximo 150 metros</small>
                        </div>
                        <div class="col-md-6">
                            <label class="form-label">Diámetro Pecho (m) *</label>
                            <input type="number" step="0.01" name="diametro_pecho" class="form-control modern-input" placeholder="0.00" min="0" max="10" required>
                            <small class="form-text text-muted">Máximo 10 metros</small>
                        </div>
                    </div>
                    <div class="modal-footer modern-modal-footer">
                        <button type="button" class="btn btn-light" data-bs-dismiss="modal">
                            <i class="fas fa-times me-2"></i>Cancelar
                        </button>
                        <button type="submit" class="btn btn-info">
                            <i class="fas fa-tree me-2"></i>Registrar Árbol
                        </button>
                    </div>
                </form>
            </div>
        </div>
    </div>
</div>

<!-- Quick Estimación para Árbol Modal -->
<div class="modal fade modern-modal" id="quickEstimacionArbolModal" tabindex="-1">
    <div class="modal-dialog modal-dialog-centered">
        <div class="modal-content">
            <div class="modal-header modal-header-warning">
                <div class="modal-icon">
                    <i class="fas fa-calculator"></i>
                </div>
                <div class="modal-title-content">
                    <h5 class="modal-title">Estimación para Árbol</h5>
                    <p class="modal-subtitle">Cálculo volumétrico para árbol existente</p>
                </div>
                <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal"></button>
            </div>
            <div class="modal-body">
                <form method="POST" action="{{ route('tecnico.estimaciones-arbol.store') }}" class="modern-form">
                    @csrf
                    <div class="row g-3">
                        <div class="col-md-6">
                            <label class="form-label">Parcela *</label>
                            <select class="form-select modern-select" name="id_parcela" required id="arbolParcelaSelect">
                                <option value="" selected disabled>Seleccione parcela</option>
                                @foreach($parcelas as $parcela)
                                    <option value="{{ $parcela->id_parcela }}">{{ $parcela->nom_parcela }}</option>
                                @endforeach
                            </select>
                        </div>
                        <div class="col-md-6">
                            <label class="form-label">Árbol *</label>
                            <select class="form-select modern-select" name="id_arbol" required id="arbolSelect" disabled>
                                <option value="" selected disabled>Primero seleccione parcela</option>
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
                            <label class="form-label">Cálculo Volumétrico (m³) *</label>
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