<div class="modal fade" id="createFormulaModal" tabindex="-1" aria-hidden="true">
    <div class="modal-dialog modal-dialog-centered">
        <div class="modal-content border-0 shadow-lg">
            <div class="modal-header text-white" style="background-color: var(--succulent-medium);">
                <h5 class="modal-title"><i class="fas fa-plus-circle me-2"></i>Nueva Fórmula</h5>
                <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal"></button>
            </div>
            <div class="modal-body p-4">
                <form method="POST" action="{{ route('formulas.store') }}" data-formula-form data-initial-variables="[]">
                    @csrf
                    <div class="form-floating mb-3">
                        <input type="text" name="nom_formula" class="form-control" required placeholder="Nombre">
                        <label>Nombre de la Fórmula*</label>
                    </div>
                    <div class="form-floating mb-3">
                        <select name="id_tipo_e" class="form-select" required><option value="" disabled selected>Selecciona...</option>@foreach($tiposEstimacion as $tipo)<option value="{{ $tipo->id_tipo_e }}">{{ $tipo->desc_estimacion }}</option>@endforeach</select>
                        <label>Tipo de Estimación*</label>
                    </div>
                    <div class="form-floating mb-3">
                        <select name="id_cat" class="form-select" required><option value="" disabled selected>Selecciona...</option>@foreach($catalogos as $catalogo)<option value="{{ $catalogo->id_cat }}">{{ $catalogo->nom_cat }}</option>@endforeach</select>
                        <label>Catálogo*</label>
                    </div>
                    <div class="row g-3 mb-3">
                        <div class="col-md-4 d-flex align-items-center">
                            <input type="hidden" name="modo_ejecucion" value="app">
                            <div>
                                <span class="badge bg-info text-dark"><i class="fas fa-robot me-1"></i>Modo: Aplicación</span>
                                <small class="d-block text-muted mt-1">Las fórmulas nuevas siempre se calculan con el motor de la app.</small>
                            </div>
                        </div>
                        <div class="col-md-4">
                            <div class="form-floating">
                                <select name="estado_revision" class="form-select" required>
                                    <option value="revision" selected>Revision</option>
                                    <option value="aprobada">Aprobada</option>
                                    <option value="rechazada">Rechazada</option>
                                </select>
                                <label>Estado*</label>
                            </div>
                        </div>
                    </div>
                    <div class="formula-tree-only d-none">
                        <div class="row g-3 mb-3">
                            <div class="col-md-6">
                                <label class="form-label">Factor de biomasa</label>
                                <input type="number" step="0.000001" name="biomasa_factor" class="form-control" value="1">
                            </div>
                            <div class="col-md-6">
                                <label class="form-label">Factor de carbono</label>
                                <input type="number" step="0.000001" name="carbono_factor" class="form-control" value="0.5">
                            </div>
                        </div>
                        <div class="mb-3">
                            <label class="form-label">Especies relacionadas</label>
                            <select name="especies_relacionadas[]" class="form-select" multiple size="4">
                                @foreach($especies as $especie)
                                    <option value="{{ $especie->id_especie }}">{{ $especie->nom_comun }} ({{ $especie->nom_cientifico }})</option>
                                @endforeach
                            </select>
                            <small class="text-muted">Solo se usa cuando la fórmula corresponde a árboles.</small>
                        </div>
                    </div>
                    <div class="formula-troza-only alert alert-info py-2 mb-3 d-none">
                        Esta fórmula queda como cálculo de troza. El resultado se maneja automáticamente como <strong>cálculo</strong>.
                    </div>
                     <div class="mb-3">
                        <label class="form-label">Expresión*</label>
                        <textarea name="expresion" id="expresionCreate" class="form-control" rows="3" required placeholder="(DAP^2 * 0.7854 * altura)"></textarea>
                        <button type="button" class="btn btn-sm btn-outline-secondary mt-2" onclick="testExpression('expresionCreate')"><i class="fas fa-check me-1"></i> Probar</button>
                    </div>
                    <div class="mb-3">
                        <div class="d-flex justify-content-between align-items-center mb-2">
                            <label class="form-label mb-0">Variables estructuradas</label>
                            <button type="button" class="btn btn-sm btn-outline-secondary js-add-variable-row">Agregar variable</button>
                        </div>
                        <div class="table-responsive">
                            <table class="table table-sm align-middle">
                                <thead>
                                    <tr>
                                        <th>Nombre</th>
                                        <th>Etiqueta</th>
                                        <th>Origen</th>
                                        <th>Default</th>
                                        <th class="text-center">Req.</th>
                                        <th></th>
                                    </tr>
                                </thead>
                                <tbody class="js-variable-rows"></tbody>
                            </table>
                        </div>
                        <input type="hidden" name="variables_schema" class="js-variables-schema">
                        <small class="text-muted">El sistema tomará estas variables como fuente para validar y calcular la fórmula.</small>
                    </div>
                    <div class="mb-3">
                        <label class="form-label">Notas de revisión</label>
                        <textarea name="revision_notas" class="form-control" rows="2" placeholder="Opcional"></textarea>
                    </div>
                    <div class="d-flex justify-content-end mt-4">
                        <button type="button" class="btn btn-secondary me-2" data-bs-dismiss="modal">Cancelar</button>
                        <button type="submit" class="btn text-white" style="background-color: var(--succulent-medium);"><i class="fas fa-check-circle me-2"></i>Crear</button>
                    </div>
                </form>
            </div>
        </div>
    </div>
</div>