<div class="modal fade wood-modal" id="helpFormulaModal" tabindex="-1" aria-hidden="true">
    <div class="modal-dialog modal-lg modal-dialog-centered">
        <div class="modal-content border-0 wood-modal-content">
            <div class="modal-header wood-modal-header wood-bg-info">
                <div class="d-flex align-items-center">
                    <div class="wood-modal-icon me-3"><i class="fas fa-question-circle"></i></div>
                    <div>
                        <h5 class="modal-title wood-modal-title text-white">Ayuda - Editor de Fórmulas</h5>
                        <p class="wood-modal-subtitle mb-0 text-white-75">Operadores, variables y ejemplos</p>
                    </div>
                </div>
                <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal"></button>
            </div>
            <div class="modal-body wood-modal-body">
                <div class="row g-4">
                    <div class="col-md-6">
                        <h6 class="fw-800 text-dark mb-3" style="font-size: 1rem;">Operadores Permitidos</h6>
                        <div class="d-flex flex-column gap-2">
                            <div class="d-flex align-items-center p-2 rounded-2" style="background: rgba(16, 185, 129, 0.05);">
                                <span style="color: #64748b; flex: 1;">Suma</span>
                                <code style="background: rgba(16, 185, 129, 0.1); padding: 0.25rem 0.6rem; border-radius: 6px; color: #059669; font-weight: 700;">+</code>
                            </div>
                            <div class="d-flex align-items-center p-2 rounded-2" style="background: rgba(16, 185, 129, 0.05);">
                                <span style="color: #64748b; flex: 1;">Resta</span>
                                <code style="background: rgba(16, 185, 129, 0.1); padding: 0.25rem 0.6rem; border-radius: 6px; color: #059669; font-weight: 700;">-</code>
                            </div>
                            <div class="d-flex align-items-center p-2 rounded-2" style="background: rgba(16, 185, 129, 0.05);">
                                <span style="color: #64748b; flex: 1;">Multiplicación</span>
                                <code style="background: rgba(16, 185, 129, 0.1); padding: 0.25rem 0.6rem; border-radius: 6px; color: #059669; font-weight: 700;">*</code>
                            </div>
                            <div class="d-flex align-items-center p-2 rounded-2" style="background: rgba(16, 185, 129, 0.05);">
                                <span style="color: #64748b; flex: 1;">División</span>
                                <code style="background: rgba(16, 185, 129, 0.1); padding: 0.25rem 0.6rem; border-radius: 6px; color: #059669; font-weight: 700;">/</code>
                            </div>
                            <div class="d-flex align-items-center p-2 rounded-2" style="background: rgba(16, 185, 129, 0.05);">
                                <span style="color: #64748b; flex: 1;">Potencia</span>
                                <code style="background: rgba(16, 185, 129, 0.1); padding: 0.25rem 0.6rem; border-radius: 6px; color: #059669; font-weight: 700;">^</code>
                            </div>
                            <div class="d-flex align-items-center p-2 rounded-2" style="background: rgba(16, 185, 129, 0.05);">
                                <span style="color: #64748b; flex: 1;">Agrupación</span>
                                <code style="background: rgba(16, 185, 129, 0.1); padding: 0.25rem 0.6rem; border-radius: 6px; color: #059669; font-weight: 700;">()</code>
                            </div>
                        </div>
                    </div>
                    <div class="col-md-6">
                        <h6 class="fw-800 text-dark mb-3" style="font-size: 1rem;">Variables Disponibles</h6>
                        <div class="d-flex flex-column gap-2">
                            <div class="p-3 rounded-3" style="background: rgba(59, 130, 246, 0.05); border-left: 3px solid #3b82f6;">
                                <code style="color: #2563eb; font-weight: 700; font-size: 0.95rem;">DAP</code>
                                <p style="color: #64748b; font-size: 0.85rem; margin: 0.35rem 0 0 0;">Diámetro a la altura del pecho</p>
                            </div>
                            <div class="p-3 rounded-3" style="background: rgba(59, 130, 246, 0.05); border-left: 3px solid #3b82f6;">
                                <code style="color: #2563eb; font-weight: 700; font-size: 0.95rem;">altura</code>
                                <p style="color: #64748b; font-size: 0.85rem; margin: 0.35rem 0 0 0;">Altura total del árbol</p>
                            </div>
                            <div class="p-3 rounded-3" style="background: rgba(59, 130, 246, 0.05); border-left: 3px solid #3b82f6;">
                                <code style="color: #2563eb; font-weight: 700; font-size: 0.95rem;">factor</code>
                                <p style="color: #64748b; font-size: 0.85rem; margin: 0.35rem 0 0 0;">Factor de forma o conversión</p>
                            </div>
                        </div>
                    </div>
                </div>

                <div style="border-top: 1px solid rgba(16, 185, 129, 0.1); margin: 2rem 0;"></div>

                <h6 class="fw-800 text-dark mb-3" style="font-size: 1rem;">Ejemplos de Fórmulas</h6>
                <div class="row g-3">
                    <div class="col-md-6">
                        <p style="color: #64748b; font-size: 0.9rem; margin: 0 0 0.5rem 0;"><strong>Volumen Cilíndrico:</strong></p>
                        <pre style="background: rgba(16, 185, 129, 0.05); border: 1px solid rgba(16, 185, 129, 0.15); border-radius: 12px; padding: 1rem; color: #059669; font-weight: 600; font-size: 0.9rem; margin: 0; font-family: 'Monaco', 'Courier New', monospace;">(DAP^2 * 0.7854 * altura)</pre>
                    </div>
                    <div class="col-md-6">
                        <p style="color: #64748b; font-size: 0.9rem; margin: 0 0 0.5rem 0;"><strong>Fórmula de Smalian:</strong></p>
                        <pre style="background: rgba(16, 185, 129, 0.05); border: 1px solid rgba(16, 185, 129, 0.15); border-radius: 12px; padding: 1rem; color: #059669; font-weight: 600; font-size: 0.9rem; margin: 0; font-family: 'Monaco', 'Courier New', monospace;">((AS + ab) / 2) * L</pre>
                    </div>
                </div>
            </div>
            <div class="wood-modal-footer">
                <button type="button" class="btn btn-wood" data-bs-dismiss="modal">
                    <i class="fas fa-check me-1"></i> Entendido
                </button>
            </div>
        </div>
    </div>
</div>