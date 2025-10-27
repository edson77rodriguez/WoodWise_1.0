    {{-- MODALES ESPECÍFICOS POR PARCELA --}}
    @foreach($parcelas as $parcela)
        
        {{-- MODAL PARA ESTIMACIÓN DE TROZA --}}
        <div class="modal fade wood-modal" id="estimacionTrozaModal{{ $parcela->id_parcela }}" tabindex="-1" aria-hidden="true">
            <div class="modal-dialog modal-dialog-centered">
                <div class="modal-content border-0 wood-modal-content">
                    <div class="modal-header wood-modal-header wood-bg-info">
                        <div class="d-flex align-items-center">
                            <div class="wood-modal-icon me-3"><i class="fas fa-calculator"></i></div>
                            <div>
                                <h5 class="modal-title wood-modal-title text-white">Estimación Volumétrica</h5>
                                <p class="wood-modal-subtitle mb-0">Parcela: {{ $parcela->nom_parcela }}</p>
                            </div>
                        </div>
                        <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal" aria-label="Close"></button>
                    </div>
                    <div class="modal-body p-4 wood-modal-body">
                        <form method="POST" action="{{ route('estimaciones.store') }}">
                            @csrf
                            <input type="hidden" name="id_parcela" value="{{ $parcela->id_parcela }}">
                            <div class="mb-3">
                                <label class="wood-form-label">Seleccionar Troza</label>
                                <select class="wood-form-select" name="id_troza" required>
                                    <option value="" selected disabled>Seleccione una troza</option>
                                    @foreach($parcela->trozas as $troza)
                                        <option value="{{ $troza->id_troza }}">Troza #{{ $troza->id_troza }} ({{ $troza->longitud }}m x {{ $troza->diametro }}m)</option>
                                    @endforeach
                                </select>
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
                                    <label class="wood-form-label">Fórmula a Aplicar</label>
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
                                <input type="number" step="0.0001" class="wood-form-control" name="calculo" required>
                            </div>
                            <div class="wood-modal-footer mt-4">
                                <button type="button" class="btn btn-wood-outline" data-bs-dismiss="modal"><i class="fas fa-times me-1"></i> Cancelar</button>
                                <button type="submit" class="btn btn-wood-info"><i class="fas fa-calculator me-1"></i> Calcular Estimación</button>
                            </div>
                        </form>
                    </div>
                </div>
            </div>
        </div>
    @endforeach