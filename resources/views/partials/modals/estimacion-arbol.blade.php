    {{-- MODALES ESPECÍFICOS POR PARCELA --}}
    @foreach($parcelas as $parcela)
          {{-- MODAL PARA ESTIMACIÓN DE ÁRBOL --}}
        <div class="modal fade wood-modal" id="estimacionArbolModal{{ $parcela->id_parcela }}" tabindex="-1" aria-hidden="true">
            <div class="modal-dialog modal-dialog-centered">
                <div class="modal-content border-0 wood-modal-content">
                    <div class="modal-header wood-modal-header wood-bg-warning">
                        <div class="d-flex align-items-center">
                            <div class="wood-modal-icon me-3"><i class="fas fa-calculator"></i></div>
                            <div>
                                <h5 class="modal-title wood-modal-title text-white">Estimación para Árbol</h5>
                                <p class="wood-modal-subtitle mb-0">Parcela: {{ $parcela->nom_parcela }}</p>
                            </div>
                        </div>
                        <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal" aria-label="Close"></button>
                    </div>
                    <div class="modal-body p-4 wood-modal-body">
                        <form method="POST" action="{{ route('tecnico.estimacion-arbol.store') }}">
                            @csrf
                            <input type="hidden" name="id_parcela" value="{{ $parcela->id_parcela }}">
                            <input type="hidden" name="calculo" value="0">
                            <div class="mb-3">
                                <label class="wood-form-label">Seleccionar Árbol</label>
                                <select class="wood-form-select select-arbol-estimacion" name="id_arbol" data-parcela="{{ $parcela->id_parcela }}" required>
                                    <option value="" selected disabled>Seleccione un árbol</option>
                                    @foreach($parcela->arboles as $arbol)
                                        <option value="{{ $arbol->id_arbol }}" data-especie="{{ $arbol->id_especie }}">
                                            Árbol #{{ $arbol->id_arbol }} - {{ $arbol->especie->nom_cientifico ?? 'Sin especie' }} 
                                            (H: {{ $arbol->altura_total }}m, DAP: {{ $arbol->diametro_pecho }}m)
                                        </option>
                                    @endforeach
                                </select>
                                @if($parcela->arboles->count() == 0)
                                    <small class="text-warning">No hay árboles registrados. Primero registre un árbol.</small>
                                @endif
                            </div>
                            <div class="row g-3">
                                <div class="col-md-6">
                                    <label class="wood-form-label">Tipo de Estimación</label>
                                    <select class="wood-form-select select-tipo-estimacion" id="tipoEstimacion{{ $parcela->id_parcela }}" name="id_tipo_e" required>
                                        <option value="">Seleccione un tipo</option>
                                        @foreach($tiposEstimacion as $tipo)
                                            <option value="{{ $tipo->id_tipo_e }}" data-desc="{{ $tipo->desc_estimacion }}">{{ $tipo->desc_estimacion }}</option>
                                        @endforeach
                                    </select>
                                </div>
                                <div class="col-md-6">
                                    <label class="wood-form-label">Fórmula de Biomasa</label>
                                    <select class="wood-form-select select-formula-arbol" id="formulaArbol{{ $parcela->id_parcela }}" name="id_formula" data-parcela="{{ $parcela->id_parcela }}" required disabled>
                                        <option value="" selected disabled>Seleccione un árbol primero</option>
                                        @foreach($formulas->whereIn('nom_formula', ['Biomasa Pinus montezumae', 'Biomasa Quercus crassifolia', 'Biomasa Quercus rugosa', 'Biomasa Pinus pseudostrobus']) as $formula)
                                            <option value="{{ $formula->id_formula }}" data-especie="{{ $formula->nom_formula }}">{{ $formula->nom_formula }}</option>
                                        @endforeach
                                    </select>
                                    <small class="text-muted d-block mt-1">Se auto-completa según la especie del árbol.</small>
                                </div>
                            </div>
                            <div class="wood-modal-footer mt-4">
                                <button type="button" class="btn btn-wood-outline" data-bs-dismiss="modal">
                                    <i class="fas fa-times me-1"></i> Cancelar
                                </button>
                                <button type="submit" class="btn btn-wood-warning" {{ $parcela->arboles_count == 0 ? 'disabled' : '' }}>
                                    <i class="fas fa-calculator me-1"></i> Crear Estimación
                                </button>
                            </div>
                        </form>
                    </div>
                </div>
            </div>
        </div>
    @endforeach

<script>
document.addEventListener('DOMContentLoaded', function() {
    // Mapeo de especies a fórmulas
    const especieFormulaMap = {
        '1': { formulaId: '8', formulaNombre: 'Biomasa Pinus pseudostrobus' },      // Pinus pseudostrobus
        '2': { formulaId: '7', formulaNombre: 'Biomasa Quercus rugosa' },           // Quercus rugosa
        '3': { formulaId: '5', formulaNombre: 'Biomasa Pinus montezumae' },         // Pinus montezumae
        '4': { formulaId: '6', formulaNombre: 'Biomasa Quercus crassifolia' }       // Quercus crassifolia
    };

    // Escuchadores para cada modal de árbol
    document.querySelectorAll('.select-arbol-estimacion').forEach(selectArbol => {
        const parcela = selectArbol.dataset.parcela;
        const tipoSelect = document.getElementById(`tipoEstimacion${parcela}`);
        const formulaSelect = document.getElementById(`formulaArbol${parcela}`);

        selectArbol.addEventListener('change', function() {
            const especieId = this.options[this.selectedIndex].dataset.especie;
            
            if (especieId && especieFormulaMap[especieId]) {
                const { formulaId, formulaNombre } = especieFormulaMap[especieId];
                
                // Auto-seleccionar la fórmula correspondiente
                formulaSelect.value = formulaId;
                formulaSelect.disabled = false;
                
                // Auto-seleccionar Biomasa (id_tipo_e = 2)
                const biomasa = Array.from(tipoSelect.options).find(opt => opt.textContent.toLowerCase().includes('biomasa'));
                if (biomasa) {
                    tipoSelect.value = biomasa.value;
                    tipoSelect.disabled = true;
                }
                
                // Feedback visual
                formulaSelect.classList.add('is-valid');
                tipoSelect.classList.add('is-valid');
            }
        });

        // Evitar cambios en el tipo de estimación
        tipoSelect?.addEventListener('change', function(e) {
            const biomasa = Array.from(tipoSelect.options).find(opt => opt.textContent.toLowerCase().includes('biomasa'));
            if (this.value !== (biomasa?.value || '2')) {
                this.value = biomasa?.value || '2';
                this.disabled = true;
            }
        });

        // Evitar cambios manuales en la fórmula una vez seleccionada
        formulaSelect?.addEventListener('mousedown', function(e) {
            if (this.value) {
                e.preventDefault();
                formulaSelect.disabled = true;
            }
        });
    });
});
</script>