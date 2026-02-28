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
                        <form method="POST" action="{{ route('tecnico.estimacion.store') }}">
                            @csrf
                            <input type="hidden" name="id_parcela" value="{{ $parcela->id_parcela }}">
                            <div class="mb-3">
                                <label class="wood-form-label">Seleccionar Troza</label>
                                <select class="wood-form-select" name="id_troza" required>
                                    <option value="" selected disabled>Seleccione una troza</option>
                                    @foreach($parcela->trozas as $troza)
                                        <option value="{{ $troza->id_troza }}">Troza #{{ $troza->id_troza }} - {{ $troza->especie->nom_cientifico ?? 'N/A' }} ({{ $troza->longitud }}m)</option>
                                    @endforeach
                                </select>
                                @if($parcela->trozas->count() == 0)
                                    <small class="text-warning">No hay trozas registradas. Primero registre una troza.</small>
                                @endif
                            </div>
                            <div class="row g-3">
                                <div class="col-md-6">
                                    <label class="wood-form-label">Tipo de Estimación</label>
                                    <select class="wood-form-select" name="id_tipo_e" required>
                                        @foreach($tiposEstimacion->where('desc_estimacion', 'Volumen Maderable') as $tipo)
                                            <option value="{{ $tipo->id_tipo_e }}" selected>{{ $tipo->desc_estimacion }}</option>
                                        @endforeach
                                    </select>
                                </div>
                                <div class="col-md-6">
                                    <label class="wood-form-label">Fórmula Volumétrica</label>
                                    <select class="wood-form-select" name="id_formula" required>
                                        <option value="" selected disabled>Seleccione una fórmula</option>
                                        @foreach($formulas->whereIn('nom_formula', ['Formula de Huber', 'Formula de Smalian', 'formula Tronco Cono', 'Formula Newton']) as $formula)
                                            <option value="{{ $formula->id_formula }}">{{ $formula->nom_formula }}</option>
                                        @endforeach
                                    </select>
                                    <small class="text-muted">El trigger calculará volumen, biomasa y carbono automáticamente.</small>
                                </div>
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