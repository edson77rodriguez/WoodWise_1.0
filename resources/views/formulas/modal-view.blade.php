<div class="modal fade wood-modal" id="viewFormulaModal{{ $formula->id_formula }}" tabindex="-1" aria-hidden="true">
    <div class="modal-dialog modal-dialog-centered">
        <div class="modal-content border-0 wood-modal-content">
            <div class="modal-header wood-modal-header wood-bg-primary">
                <div class="d-flex align-items-center">
                    <div class="wood-modal-icon me-3"><i class="fas fa-info-circle"></i></div>
                    <div>
                        <h5 class="modal-title wood-modal-title text-white">Detalles de Fórmula</h5>
                        <p class="wood-modal-subtitle mb-0">{{ $formula->nom_formula }}</p>
                    </div>
                </div>
                <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal"></button>
            </div>
            <div class="modal-body wood-modal-body">
                <div class="row g-3">
                    <div class="col-12">
                        <label class="wood-form-label">Nombre</label>
                        <div class="p-3 rounded-3" style="background: rgba(16, 185, 129, 0.05); border: 1px solid rgba(16, 185, 129, 0.15);">
                            <span class="fs-5 fw-800" style="color: #0f172a;">{{ $formula->nom_formula }}</span>
                        </div>
                    </div>
                    <div class="col-md-6">
                        <label class="wood-form-label">Tipo</label>
                        <div class="p-3 rounded-3" style="background: rgba(16, 185, 129, 0.05); border: 1px solid rgba(16, 185, 129, 0.15);">
                            <span style="color: #64748b;">{{ $formula->tipoEstimacion->desc_estimacion }}</span>
                        </div>
                    </div>
                    <div class="col-md-6">
                        <label class="wood-form-label">Catálogo</label>
                        <div class="p-3 rounded-3" style="background: rgba(16, 185, 129, 0.05); border: 1px solid rgba(16, 185, 129, 0.15);">
                            <span style="color: #64748b;">{{ $formula->catalogo->nom_cat }}</span>
                        </div>
                    </div>
                    <div class="col-md-6">
                        <label class="wood-form-label">Estado</label>
                        <div class="p-3 rounded-3" style="background: rgba(16, 185, 129, 0.05); border: 1px solid rgba(16, 185, 129, 0.15);">
                            <span style="color: #64748b;">{{ ucfirst($formula->estado_revision ?? 'revision') }}</span>
                        </div>
                    </div>
                    <div class="col-md-6">
                        <label class="wood-form-label">Modo de ejecución</label>
                        <div class="p-3 rounded-3" style="background: rgba(16, 185, 129, 0.05); border: 1px solid rgba(16, 185, 129, 0.15);">
                            <span style="color: #64748b;">{{ strtoupper($formula->modo_ejecucion ?? 'trigger') }}</span>
                        </div>
                    </div>
                    <div class="col-md-6">
                        <label class="wood-form-label">Resultado</label>
                        <div class="p-3 rounded-3" style="background: rgba(16, 185, 129, 0.05); border: 1px solid rgba(16, 185, 129, 0.15);">
                            <span style="color: #64748b;">{{ strtoupper($formula->resultado_tipo ?? 'calculo') }}</span>
                        </div>
                    </div>
                    <div class="col-md-6">
                        <label class="wood-form-label">Carbono / Biomasa</label>
                        <div class="p-3 rounded-3" style="background: rgba(16, 185, 129, 0.05); border: 1px solid rgba(16, 185, 129, 0.15);">
                            <span style="color: #64748b;">Biomasa: {{ $formula->biomasa_factor ?? 'auto' }} | Carbono: {{ $formula->carbono_factor ?? 0.5 }}</span>
                        </div>
                    </div>
                    <div class="col-12">
                        <label class="wood-form-label">Expresión Matemática</label>
                        <div class="p-3 rounded-3" style="background: rgba(16, 185, 129, 0.05); border: 1px solid rgba(16, 185, 129, 0.15); font-family: 'Monaco', 'Courier New', monospace;">
                            <code class="expression fs-6 fw-500" style="color: #059669; letter-spacing: 0.5px;">{{ $formula->expresion }}</code>
                        </div>
                    </div>
                    <div class="col-12">
                        <label class="wood-form-label">Especies relacionadas</label>
                        <div class="p-3 rounded-3" style="background: rgba(16, 185, 129, 0.05); border: 1px solid rgba(16, 185, 129, 0.15);">
                            @php($relatedSpecies = $formula->especies_relacionadas ?? [])
                            @forelse($relatedSpecies as $especieId)
                                <span class="badge bg-secondary me-1">#{{ $especieId }}</span>
                            @empty
                                <span style="color: #64748b;">Sin especies asociadas</span>
                            @endforelse
                        </div>
                    </div>
                    <div class="col-12">
                        <label class="wood-form-label">Variables</label>
                        <div class="p-3 rounded-3" style="background: rgba(16, 185, 129, 0.05); border: 1px solid rgba(16, 185, 129, 0.15);">
                            <pre class="mb-0" style="white-space: pre-wrap; color: #64748b;">{{ json_encode($formula->variables_schema ?? [], JSON_PRETTY_PRINT | JSON_UNESCAPED_UNICODE) }}</pre>
                        </div>
                    </div>
                    @if(!empty($formula->revision_notas))
                    <div class="col-12">
                        <label class="wood-form-label">Notas de revisión</label>
                        <div class="p-3 rounded-3" style="background: rgba(16, 185, 129, 0.05); border: 1px solid rgba(16, 185, 129, 0.15);">
                            <span style="color: #64748b;">{{ $formula->revision_notas }}</span>
                        </div>
                    </div>
                    @endif
                </div>
            </div>
            <div class="wood-modal-footer">
                <button type="button" class="btn btn-wood-outline" data-bs-dismiss="modal">
                    <i class="fas fa-times me-1"></i> Cerrar
                </button>
            </div>
        </div>
    </div>
</div>