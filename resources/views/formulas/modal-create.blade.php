<div class="modal fade" id="createFormulaModal" tabindex="-1" aria-hidden="true">
    <div class="modal-dialog modal-dialog-centered">
        <div class="modal-content border-0 shadow-lg">
            <div class="modal-header text-white" style="background-color: var(--succulent-medium);">
                <h5 class="modal-title"><i class="fas fa-plus-circle me-2"></i>Nueva Fórmula</h5>
                <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal"></button>
            </div>
            <div class="modal-body p-4">
                <form method="POST" action="{{ route('formulas.store') }}">
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
                     <div class="mb-3">
                        <label class="form-label">Expresión*</label>
                        <textarea name="expresion" id="expresionCreate" class="form-control" rows="3" required placeholder="(DAP^2 * 0.7854 * altura)"></textarea>
                        <button type="button" class="btn btn-sm btn-outline-secondary mt-2" onclick="testExpression('expresionCreate')"><i class="fas fa-check me-1"></i> Probar</button>
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