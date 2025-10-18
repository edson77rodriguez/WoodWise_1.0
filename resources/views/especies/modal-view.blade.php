<div class="modal fade" id="viewEspecieModal{{ $especie->id_especie }}" tabindex="-1" aria-hidden="true">
    <div class="modal-dialog modal-dialog-centered modal-lg">
        <div class="modal-content border-0 shadow-lg">
            <div class="modal-header text-white" style="background-color: var(--succulent-medium);">
                <h5 class="modal-title"><i class="fas fa-info-circle me-2"></i>Detalles de la Especie</h5>
                <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal"></button>
            </div>
            <div class="modal-body p-4">
                 <div class="row">
                    <div class="col-md-7">
                        <p class="mb-2"><strong class="text-muted">ID:</strong> <span class="fs-5 fw-bold">{{ $especie->id_especie }}</span></p>
                        <p class="mb-2"><strong class="text-muted">Nombre Común:</strong> <span class="fs-5 fw-bold">{{ $especie->nom_comun }}</span></p>
                        <p class="mb-2"><strong class="text-muted">Nombre Científico:</strong> <em>{{ $especie->nom_cientifico }}</em></p>
                        <p class="mb-2"><strong class="text-muted">Fecha de Registro:</strong> <span>{{ $especie->created_at?->format('d/m/Y') }}</span></p>
                    </div>
                    <div class="col-md-5">
                        @if ($especie->imagen)
                            <img src="{{ asset('storage/' . $especie->imagen) }}" class="img-fluid rounded shadow-sm" alt="{{ $especie->nom_comun }}">
                        @else
                            <div class="d-flex align-items-center justify-content-center bg-light rounded h-100 text-center">
                                <p class="text-muted">Sin imagen</p>
                            </div>
                        @endif
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>