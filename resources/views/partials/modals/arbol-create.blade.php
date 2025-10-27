    {{-- MODALES ESPECÍFICOS POR PARCELA --}}
    @foreach($parcelas as $parcela)
        
        {{-- MODAL PARA NUEVO ÁRBOL --}}
        <div class="modal fade wood-modal" id="addArbolModal{{ $parcela->id_parcela }}" tabindex="-1" aria-hidden="true">
            <div class="modal-dialog modal-dialog-centered">
                <div class="modal-content border-0 wood-modal-content">
                    <div class="modal-header wood-modal-header wood-bg-success">
                        <div class="d-flex align-items-center">
                            <div class="wood-modal-icon me-3"><i class="fas fa-tree"></i></div>
                            <div>
                                <h5 class="modal-title wood-modal-title text-white">Nuevo Árbol</h5>
                                <p class="wood-modal-subtitle mb-0">Parcela: {{ $parcela->nom_parcela }}</p>
                            </div>
                        </div>
                        <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal" aria-label="Close"></button>
                    </div>
                    <div class="modal-body p-4 wood-modal-body">
                        <form method="POST" action="{{ route('arboles.store') }}">
                            @csrf
                            <input type="hidden" name="id_parcela" value="{{ $parcela->id_parcela }}">
                            <div class="row g-3">
                                <div class="col-md-6">
                                    <label class="wood-form-label">Altura Total (m)</label>
                                    <input type="number" step="0.1" name="altura_total" class="wood-form-control" required min="0.1" max="100">
                                </div>
                                <div class="col-md-6">
                                    <label class="wood-form-label">Diámetro a la Altura del Pecho (m)</label>
                                    <input type="number" step="0.01" name="diametro_pecho" class="wood-form-control" required min="0.01" max="5">
                                </div>
                                <div class="col-12">
                                    <label class="wood-form-label">Especie</label>
                                    <select class="wood-form-select" name="id_especie" required>
                                        <option value="" selected disabled>Seleccione una especie</option>
                                        @foreach ($especies as $especie)
                                            <option value="{{ $especie->id_especie }}">{{ $especie->nom_cientifico }}</option>
                                        @endforeach
                                    </select>
                                </div>
                            </div>
                            <div class="wood-modal-footer mt-4">
                                <button type="button" class="btn btn-wood-outline" data-bs-dismiss="modal">
                                    <i class="fas fa-times me-1"></i> Cancelar
                                </button>
                                <button type="submit" class="btn btn-wood-success">
                                    <i class="fas fa-save me-1"></i> Registrar Árbol
                                </button>
                            </div>
                        </form>
                    </div>
                </div>
            </div>
        </div>
    @endforeach