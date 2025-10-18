<div class="modal fade" id="editFormulaModal{{ $formula->id_formula }}" tabindex="-1" aria-hidden="true">
    <div class="modal-dialog modal-dialog-centered">
        <div class="modal-content border-0 shadow-lg">
            <div class="modal-header text-white" style="background-color: var(--succulent-dark);">
                <h5 class="modal-title"><i class="fas fa-edit me-2"></i>Editar Fórmula</h5>
                <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal"></button>
            </div>
            <div class="modal-body p-4">
                <form method="POST" action="{{ route('formulas.update', $formula->id_formula) }}">
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
                    <div class="mb-3">
                        <label class="form-label">Expresión</label>
                        <textarea name="expresion" id="expresionEdit{{ $formula->id_formula }}" class="form-control" rows="3" required>{{ $formula->expresion }}</textarea>
                        <button type="button" class="btn btn-sm btn-outline-secondary mt-2" onclick="testExpression('expresionEdit{{ $formula->id_formula }}')"><i class="fas fa-check me-1"></i> Probar</button>
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