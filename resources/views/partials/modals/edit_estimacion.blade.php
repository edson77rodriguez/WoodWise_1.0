@foreach($estimaciones as $estimacion)
<div class="modal fade" id="editEstimacionModal{{ $estimacion->id_estimacion }}" tabindex="-1" 
     aria-labelledby="editEstimacionLabel{{ $estimacion->id_estimacion }}" aria-hidden="true">
    <div class="modal-dialog modal-dialog-centered">
        <div class="modal-content border-0 shadow">
            <div class="modal-header bg-gradient-primary text-white">
                <h5 class="modal-title text-white">
                    <i class="fas fa-edit me-2"></i>Editar Estimación #{{ $estimacion->id_estimacion }}
                </h5>
                <button type="button" class="btn-close btn-close-white" 
                        data-bs-dismiss="modal" aria-label="Close"></button>
            </div>
            <div class="modal-body p-4">
                <form method="POST" action="{{ route('estimaciones.update', $estimacion->id_estimacion) }}">
                    @csrf
                    @method('PUT')
                    <div class="mb-3">
                        <label class="form-label text-muted">Tipo de Estimación</label>
                        <select name="id_tipo_e" class="form-select border-2" required>
                            @foreach ($tiposEstimacion as $tipo)
                            <option value="{{ $tipo->id_tipo_e }}" {{ $tipo->id_tipo_e == $estimacion->id_tipo_e ? 'selected' : '' }}>
                                {{ $tipo->desc_estimacion }}
                            </option>
                            @endforeach
                        </select>
                    </div>
                    <div class="mb-3">
                        <label class="form-label text-muted">Fórmula</label>
                        <select name="id_formula" class="form-select border-2" required>
                            @foreach ($formulas as $formula)
                            <option value="{{ $formula->id_formula }}" {{ $formula->id_formula == $estimacion->id_formula ? 'selected' : '' }}>
                                {{ $formula->nom_formula }}
                            </option>
                            @endforeach
                        </select>
                    </div>
                    <div class="mb-3">
                        <label class="form-label text-muted">Troza</label>
                        <select name="id_troza" class="form-select border-2" required>
                            @foreach ($trozas as $troza)
                            <option value="{{ $troza->id_troza }}" {{ $troza->id_troza == $estimacion->id_troza ? 'selected' : '' }}>
                                {{ $troza->codigo_troza }}
                            </option>
                            @endforeach
                        </select>
                    </div>
                    <div class="mb-3">
                        <label class="form-label text-muted">Cálculo</label>
                        <input type="number" step="0.01" class="form-control border-2"
                               name="calculo" value="{{ $estimacion->calculo }}" required>
                    </div>
                    <div class="d-grid gap-2 d-md-flex justify-content-md-end mt-4">
                        <button type="button" class="btn btn-outline-secondary me-md-2 rounded-pill" 
                                data-bs-dismiss="modal">Cancelar</button>
                        <button type="submit" class="btn btn-primary rounded-pill">
                            <i class="fas fa-save me-1"></i>Guardar Cambios
                        </button>
                    </div>
                </form>
            </div>
        </div>
    </div>
</div>
@endforeach