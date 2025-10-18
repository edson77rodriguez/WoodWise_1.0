@extends('dashboard')

@section('template_title', 'Catálogo de Especies')

@push('styles')
    <link href="{{ asset('css/WW/catalogo.css') }}" rel="stylesheet">
@endpush

@section('crud_content')
<div class="catalog-container">

    <div class="container pt-5 pb-4">
        <div class="text-center">
            <h1 class="display-4 fw-bold text-gradient">
                <i class="fas fa-leaf me-2"></i>Catálogo de Especies
            </h1>
            <p class="lead text-muted">La diversidad de nuestra colección forestal.</p>
        </div>
    </div>

    <div class="container">
        <div class="row">
            @forelse($especies as $especie)
                <div class="col-lg-4 col-md-6 mb-4 species-card">
                    <div class="card species-card-inner h-100 shadow-sm">
                        <div class="card-img-container">
                            <img src="{{ asset('storage/' . $especie->imagen) }}" class="card-img-top" alt="{{ $especie->nom_comun }}" loading="lazy">
                            <div class="img-overlay"></div>
                            
                            {{-- Este botón ahora tiene 'data-attributes' con la info de la especie --}}
                            <div class="quick-view js-open-modal"
                                 data-nombre-comun="{{ $especie->nom_comun }}"
                                 data-nombre-cientifico="{{ $especie->nom_cientifico }}"
                                 data-imagen-url="{{ asset('storage/' . $especie->imagen) }}">
                                <i class="fas fa-eye"></i> Vista Rápida
                            </div>
                        </div>
                        <div class="card-body d-flex flex-column text-center">
                            <h5 class="card-title">{{ $especie->nom_comun }}</h5>
                            <p class="card-subtitle text-muted flex-grow-1">
                                <em>{{ $especie->nom_cientifico }}</em>
                            </p>
                        </div>
                    </div>
                </div>
            @empty
                <div class="col-12 text-center py-5">
                    <i class="fas fa-box-open fa-3x text-muted mb-3"></i>
                    <h4 class="text-muted">No hay especies registradas</h4>
                    <p class="text-muted">Aún no se ha añadido ninguna especie al catálogo.</p>
                </div>
            @endforelse
        </div>
    </div>

    <div class="modal fade" id="speciesModal" tabindex="-1" aria-labelledby="speciesModalLabel" aria-hidden="true">
        <div class="modal-dialog modal-dialog-centered modal-lg">
            <div class="modal-content">
                <div class="modal-header border-0">
                    <div>
                        {{-- El JavaScript llenará estos campos --}}
                        <h5 class="modal-title" id="modalSpeciesName"></h5>
                        <small class="text-muted" id="modalSpeciesScientificName"></small>
                    </div>
                    <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                </div>
                <div class="modal-body text-center">
                    {{-- El JavaScript pondrá la URL de la imagen aquí --}}
                    <img src="" id="modalSpeciesImage" class="img-fluid rounded shadow-sm mb-3" style="max-height: 400px;" alt="">
                </div>
                <div class="modal-footer border-0">
                    <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Cerrar</button>
                </div>
            </div>
        </div>
    </div>
</div>
@endsection

@push('scripts')
{{-- Este script maneja la lógica para abrir y llenar el modal único --}}
<script>
    document.addEventListener('DOMContentLoaded', function () {
        // 1. Seleccionar los elementos del modal una sola vez
        const modalElement = document.getElementById('speciesModal');
        const speciesModal = new bootstrap.Modal(modalElement);
        const modalName = document.getElementById('modalSpeciesName');
        const modalScientificName = document.getElementById('modalSpeciesScientificName');
        const modalImage = document.getElementById('modalSpeciesImage');

        // 2. Añadir un listener a todos los botones de "Vista Rápida"
        document.querySelectorAll('.js-open-modal').forEach(button => {
            button.addEventListener('click', function () {
                // 3. Obtener los datos desde los atributos 'data-*' del botón presionado
                const nombreComun = this.dataset.nombreComun;
                const nombreCientifico = this.dataset.nombreCientifico;
                const imagenUrl = this.dataset.imagenUrl;
                
                // 4. Llenar el contenido del modal con los datos obtenidos
                modalName.textContent = nombreComun;
                modalScientificName.textContent = nombreCientifico;
                modalImage.src = imagenUrl;
                modalImage.alt = nombreComun;
                
                // 5. Mostrar el modal
                speciesModal.show();
            });
        });
    });
</script>
@endpush