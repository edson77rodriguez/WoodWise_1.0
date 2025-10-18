<div class="modal fade" id="viewFormulaModal{{ $formula->id_formula }}" tabindex="-1" aria-hidden="true">
    <div class="modal-dialog modal-dialog-centered">
        <div class="modal-content border-0 shadow-lg">
            <div class="modal-header text-white" style="background-color: var(--succulent-medium);">
                <h5 class="modal-title"><i class="fas fa-info-circle me-2"></i>Detalles de Fórmula</h5>
                <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal"></button>
            </div>
            <div class="modal-body p-4">
                <p><strong class="text-muted">Nombre:</strong> <span class="fs-5 fw-bold">{{ $formula->nom_formula }}</span></p>
                <p><strong class="text-muted">Tipo:</strong> {{ $formula->tipoEstimacion->desc_estimacion }}</p>
                <p><strong class="text-muted">Catálogo:</strong> {{ $formula->catalogo->nom_cat }}</p>
                <hr>
                <p class="text-muted mb-2">Expresión Matemática:</p>
                <div class="bg-light p-3 rounded">
                    <code class="expression fs-5">{{ $formula->expresion }}</code>
                </div>
            </div>
        </div>
    </div>
</div>