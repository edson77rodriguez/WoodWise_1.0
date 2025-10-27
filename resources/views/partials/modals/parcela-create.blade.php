{{-- Modal para crear Parcela (moderno) --}}
<div class="modal fade wood-modal" id="createParcelaModal" tabindex="-1" aria-hidden="true">
    <div class="modal-dialog modal-dialog-centered modal-lg">
        <div class="modal-content border-0 wood-modal-content">
            <div class="modal-header wood-modal-header wood-bg-primary">
                <div class="d-flex align-items-center">
                    <div class="wood-modal-icon me-3"><i class="fas fa-plus-circle"></i></div>
                    <div>
                        <h5 class="modal-title wood-modal-title text-white">Nueva Parcela</h5>
                        <p class="wood-modal-subtitle mb-0 text-white-50">Registre la parcela y asígnela automáticamente a su cuenta.</p>
                    </div>
                </div>
                <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal" aria-label="Close"></button>
            </div>
            <div class="modal-body p-4 wood-modal-body">
                <form method="POST" action="{{ route('parcelas.store') }}">
                    @csrf
                    <div class="row g-3">
                        <div class="col-md-6">
                            <label class="wood-form-label">Nombre Parcela *</label>
                            <input type="text" name="nom_parcela" class="wood-form-control" required placeholder="Nombre de la parcela">
                        </div>
                        <div class="col-md-6">
                            <label class="wood-form-label">Ubicación *</label>
                            <input type="text" name="ubicacion" class="wood-form-control" required placeholder="Ubicación">
                        </div>
                        <div class="col-md-6">
                            <label class="wood-form-label">Productor *</label>
                            <select name="id_productor" class="wood-form-select" required>
                                <option value="" selected disabled>Seleccione un productor...</option>
                                @foreach($productores as $productor)
                                    <option value="{{ $productor->id_productor }}">{{ $productor->persona->nom }} {{ $productor->persona->ap }}</option>
                                @endforeach
                            </select>
                        </div>
                        <div class="col-md-3">
                            <label class="wood-form-label">Extensión (ha) *</label>
                            <input type="number" step="0.01" name="extension" class="wood-form-control" required placeholder="0.00">
                        </div>
                        <div class="col-md-3">
                            <label class="wood-form-label">Código Postal *</label>
                            <input type="text" name="CP" class="wood-form-control" required placeholder="C.P.">
                        </div>
                        <div class="col-12">
                            <label class="wood-form-label">Dirección</label>
                            <textarea name="direccion" class="wood-form-control" rows="3" placeholder="Dirección completa"></textarea>
                        </div>
                        <div class="col-md-6">
                            <label class="wood-form-label">Especie principal *</label>
                            <select name="id_especie" class="wood-form-select" required>
                                <option value="" selected disabled>Seleccione una especie...</option>
                                @foreach($especies as $especie)
                                    <option value="{{ $especie->id_especie }}">{{ $especie->nom_cientifico }}</option>
                                @endforeach
                            </select>
                        </div>
                    </div>

                    <div class="wood-modal-footer mt-4 d-flex justify-content-end">
                        <button type="button" class="btn btn-wood-outline me-2" data-bs-dismiss="modal"><i class="fas fa-times me-1"></i> Cancelar</button>
                        <button type="submit" class="btn btn-wood"><i class="fas fa-check-circle me-1"></i> Crear Parcela</button>
                    </div>
                </form>
            </div>
        </div>
    </div>
</div>
