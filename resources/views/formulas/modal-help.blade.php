<div class="modal fade" id="helpFormulaModal" tabindex="-1" aria-hidden="true">
    <div class="modal-dialog modal-lg modal-dialog-centered">
        <div class="modal-content border-0 shadow-lg">
            <div class="modal-header text-white" style="background-color: #0288d1;">
                <h5 class="modal-title"><i class="fas fa-question-circle me-2"></i>Ayuda - Editor de Fórmulas</h5>
                <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal"></button>
            </div>
            <div class="modal-body p-4">
                <div class="row">
                    <div class="col-md-6">
                        <h6>Operadores Permitidos</h6>
                        <ul class="list-group">
                            <li class="list-group-item d-flex justify-content-between align-items-center">Suma <code>+</code></li>
                            <li class="list-group-item d-flex justify-content-between align-items-center">Resta <code>-</code></li>
                            <li class="list-group-item d-flex justify-content-between align-items-center">Multiplicación <code>*</code></li>
                            <li class="list-group-item d-flex justify-content-between align-items-center">División <code>/</code></li>
                            <li class="list-group-item d-flex justify-content-between align-items-center">Potencia <code>^</code></li>
                            <li class="list-group-item d-flex justify-content-between align-items-center">Agrupación <code>()</code></li>
                        </ul>
                    </div>
                     <div class="col-md-6 mt-3 mt-md-0">
                        <h6>Variables Disponibles</h6>
                        <ul class="list-group">
                            <li class="list-group-item"><code>DAP</code> <small class="text-muted d-block">Diámetro a la altura del pecho</small></li>
                            <li class="list-group-item"><code>altura</code> <small class="text-muted d-block">Altura total del árbol</small></li>
                            <li class="list-group-item"><code>factor</code> <small class="text-muted d-block">Factor de forma o conversión</small></li>
                        </ul>
                    </div>
                </div>
                <hr>
                <h6>Ejemplos de Fórmulas</h6>
                <p><strong>Volumen Cilíndrico:</strong></p>
                <pre class="bg-light p-2 rounded"><code>(DAP^2 * 0.7854 * altura)</code></pre>
                <p class="mt-3"><strong>Fórmula de Smalian:</strong></p>
                <pre class="bg-light p-2 rounded"><code>((AS + ab) / 2) * L</code></pre>
            </div>
             <div class="modal-footer">
                <button type="button" class="btn btn-primary" data-bs-dismiss="modal">Entendido</button>
            </div>
        </div>
    </div>
</div>