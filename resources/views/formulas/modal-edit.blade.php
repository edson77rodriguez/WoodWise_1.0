<div class="modal fade" id="editFormulaModal{{ $formula->id_formula }}" tabindex="-1" aria-hidden="true">
    <div class="modal-dialog modal-dialog-centered">
        <div class="modal-content border-0 shadow-lg">
            <div class="modal-header text-white" style="background-color: var(--succulent-dark);">
                <h5 class="modal-title"><i class="fas fa-edit me-2"></i>Editar Fórmula</h5>
                <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal"></button>
            </div>
            <div class="modal-body p-4">
                <form method="POST" action="{{ route('formulas.update', $formula->id_formula) }}" data-formula-form data-initial-variables='@json($formula->variables_schema ?? [])'>
                    @csrf
                    @method('PUT')
                    <div class="form-floating mb-3">
                        <input type="text" name="nom_formula" class="form-control" value="{{ $formula->nom_formula }}" required placeholder="Nombre">
                        <label>Nombre de la Fórmula</label>
                    </div>
                    <div class="form-floating mb-3">
                        <select name="id_tipo_e" class="form-select">
                            @foreach($tiposEstimacion as $tipo)<option value="{{ $tipo->id_tipo_e }}" {{ $formula->id_tipo_e == $tipo->id_tipo_e ? 'selected' : '' }}>{{ $tipo->desc_estimacion }}</option>@endforeach
                        </select>
                        <label>Tipo de Estimación</label>
                    </div>
                    <div class="form-floating mb-3">
                        <select name="id_cat" class="form-select">
                            @foreach($catalogos as $catalogo)<option value="{{ $catalogo->id_cat }}" {{ $formula->id_cat == $catalogo->id_cat ? 'selected' : '' }}>{{ $catalogo->nom_cat }}</option>@endforeach
                        </select>
                        <label>Catálogo</label>
                    </div>
                    <div class="row g-3 mb-3">
                        <div class="col-md-4 d-flex align-items-center">
                            @if(($formula->modo_ejecucion ?? 'app') === 'trigger')
                                <input type="hidden" name="modo_ejecucion" value="trigger">
                                <div>
                                    <span class="badge bg-warning text-dark"><i class="fas fa-database me-1"></i>Modo: Trigger (heredado)</span>
                                    <small class="d-block text-muted mt-1">Fórmula legacy, se calcula en MySQL. No se puede cambiar de modo.</small>
                                </div>
                            @else
                                <input type="hidden" name="modo_ejecucion" value="app">
                                <div>
                                    <span class="badge bg-info text-dark"><i class="fas fa-robot me-1"></i>Modo: Aplicación</span>
                                </div>
                            @endif
                        </div>
                        <div class="col-md-4">
                            <div class="form-floating">
                                <select name="estado_revision" class="form-select" required>
                                    <option value="revision" {{ ($formula->estado_revision ?? 'revision') === 'revision' ? 'selected' : '' }}>Revision</option>
                                    <option value="aprobada" {{ ($formula->estado_revision ?? 'revision') === 'aprobada' ? 'selected' : '' }}>Aprobada</option>
                                    <option value="rechazada" {{ ($formula->estado_revision ?? 'revision') === 'rechazada' ? 'selected' : '' }}>Rechazada</option>
                                </select>
                                <label>Estado*</label>
                            </div>
                        </div>
                    </div>
                    <div class="formula-tree-only d-none">
                        <div class="row g-3 mb-3">
                            <div class="col-md-6">
                                <label class="form-label">Factor de biomasa</label>
                                <input type="number" step="0.000001" name="biomasa_factor" class="form-control" value="{{ $formula->biomasa_factor ?? 1 }}">
                            </div>
                            <div class="col-md-6">
                                <label class="form-label">Factor de carbono</label>
                                <input type="number" step="0.000001" name="carbono_factor" class="form-control" value="{{ $formula->carbono_factor ?? 0.5 }}">
                            </div>
                        </div>
                        <div class="mb-3">
                            <label class="form-label">Especies relacionadas</label>
                            <select name="especies_relacionadas[]" class="form-select" multiple size="4">
                                @foreach($especies as $especie)
                                    <option value="{{ $especie->id_especie }}" {{ in_array($especie->id_especie, $formula->especies_relacionadas ?? []) ? 'selected' : '' }}>{{ $especie->nom_comun }} ({{ $especie->nom_cientifico }})</option>
                                @endforeach
                            </select>
                        </div>
                    </div>
                    <div class="formula-troza-only alert alert-info py-2 mb-3 d-none">
                        Esta fórmula queda como cálculo de troza. El resultado se maneja automáticamente como <strong>cálculo</strong>.
                    </div>
                    <div class="mb-3">
                        <label class="form-label">Expresión</label>
                        <textarea name="expresion" id="expresionEdit{{ $formula->id_formula }}" class="form-control" rows="3" required>{{ $formula->expresion }}</textarea>
                        <button type="button" class="btn btn-sm btn-outline-secondary mt-2" onclick="testExpression('expresionEdit{{ $formula->id_formula }}')"><i class="fas fa-check me-1"></i> Probar</button>
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
                    </div>
                    <div class="mb-3">
                        <label class="form-label">Notas de revisión</label>
                        <textarea name="revision_notas" class="form-control" rows="2" placeholder="Opcional">{{ $formula->revision_notas }}</textarea>
                    </div>
                    <div class="d-flex justify-content-end mt-4">
                        <button type="button" class="btn btn-secondary me-2" data-bs-dismiss="modal">Cancelar</button>
                        <button type="submit" class="btn text-white" style="background-color: var(--succulent-dark);"><i class="fas fa-save me-2"></i>Guardar</button>
                    </div>
                </form>
            </div>
        </div>
    </div>
</div>