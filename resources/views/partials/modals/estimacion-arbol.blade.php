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
                        <form method="POST" action="{{ route('estimaciones.arbol.store') }}">
                            @csrf
                            <input type="hidden" name="id_parcela" value="{{ $parcela->id_parcela }}">
                            <div class="mb-3">
                                <label class="wood-form-label">Seleccionar Árbol</label>
                                <select class="wood-form-select" name="id_arbol" required>
                                    <option value="" selected disabled>Seleccione un árbol</option>
                                    @foreach($parcela->arboles as $arbol)
                                        <option value="{{ $arbol->id_arbol }}">
                                            Árbol #{{ $arbol->id_arbol }} - {{ $arbol->especie->nom_cientifico ?? 'Sin especie' }} 
                                            ({{ $arbol->altura_total }}m x {{ $arbol->diametro_pecho }}m)
                                        </option>
                                    @endforeach
                                </select>
                                @if($parcela->arboles_count == 0)
                                    <small class="text-warning">No hay árboles registrados en esta parcela.</small>
                                @endif
                            </div>
                            <div class="row g-3">
                                <div class="col-md-6">
                                    <label class="wood-form-label">Tipo de Estimación</label>
                                    <select class="wood-form-select" name="id_tipo_e" required>
                                        <option value="" selected disabled>Seleccione un tipo</option>
                                        @foreach($tiposEstimacion as $tipo)
                                            <option value="{{ $tipo->id_tipo_e }}">{{ $tipo->desc_estimacion }}</option>
                                        @endforeach
                                    </select>
                                </div>
                                <div class="col-md-6">
                                    <label class="wood-form-label">Fórmula</label>
                                    <select class="wood-form-select" name="id_formula" required>
                                        <option value="" selected disabled>Seleccione una fórmula</option>
                                        @foreach($formulas as $formula)
                                            <option value="{{ $formula->id_formula }}">{{ $formula->nom_formula }}</option>
                                        @endforeach
                                    </select>
                                </div>
                            </div>
                            <div class="mt-3">
                                <label class="wood-form-label">Cálculo (m³)</label>
                                <input type="number" step="0.0001" class="wood-form-control" name="calculo" required min="0">
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