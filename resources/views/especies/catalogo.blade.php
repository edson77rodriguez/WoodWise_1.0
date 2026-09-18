@extends('layouts.app')

@section('title', 'Catálogo de Especies - SIGMAD')

@push('styles')
<style>
    .species-page {
        padding: 2rem 0 3rem;
    }

    .species-hero {
        position: relative;
        overflow: hidden;
        border-radius: 28px;
        padding: 2rem;
        margin-bottom: 1.5rem;
        color: #fff;
        background: linear-gradient(135deg, rgba(26, 58, 22, 0.98), rgba(45, 90, 39, 0.96));
        box-shadow: 0 24px 60px rgba(26, 58, 22, 0.22);
    }

    .species-hero::before {
        content: '';
        position: absolute;
        inset: 0;
        background:
            radial-gradient(circle at top right, rgba(16, 185, 129, 0.22), transparent 34%),
            radial-gradient(circle at bottom left, rgba(59, 130, 246, 0.16), transparent 32%);
        pointer-events: none;
    }

    .species-hero-grid {
        position: relative;
        z-index: 1;
        display: grid;
        grid-template-columns: minmax(0, 1.2fr) minmax(280px, 0.8fr);
        gap: 1.5rem;
        align-items: center;
    }

    .species-kicker {
        text-transform: uppercase;
        letter-spacing: 0.18em;
        font-size: 0.72rem;
        font-weight: 800;
        color: rgba(255, 255, 255, 0.75);
        margin-bottom: 0.35rem;
    }

    .species-hero h1 {
        margin: 0;
        font-size: clamp(2rem, 4vw, 3rem);
        font-weight: 800;
        line-height: 1.05;
    }

    .species-hero p {
        margin: 0.85rem 0 0;
        color: rgba(255, 255, 255, 0.88);
        font-size: 1rem;
        line-height: 1.6;
        max-width: 62ch;
    }

    .species-badges {
        display: flex;
        flex-wrap: wrap;
        gap: 0.6rem;
        margin-top: 1rem;
    }

    .species-pill {
        display: inline-flex;
        align-items: center;
        gap: 0.45rem;
        padding: 0.5rem 0.8rem;
        border-radius: 999px;
        background: rgba(255, 255, 255, 0.12);
        border: 1px solid rgba(255, 255, 255, 0.12);
        font-size: 0.82rem;
        font-weight: 700;
        color: #fff;
    }

    .species-panel {
        background: rgba(255, 255, 255, 0.12);
        border: 1px solid rgba(255, 255, 255, 0.14);
        border-radius: 24px;
        padding: 1.25rem;
        backdrop-filter: blur(18px);
    }

    .species-stats {
        display: grid;
        grid-template-columns: repeat(2, minmax(0, 1fr));
        gap: 0.85rem;
    }

    .species-stat {
        background: rgba(255, 255, 255, 0.12);
        border: 1px solid rgba(255, 255, 255, 0.12);
        border-radius: 18px;
        padding: 1rem;
        min-height: 94px;
    }

    .species-stat .label {
        display: block;
        text-transform: uppercase;
        letter-spacing: 0.12em;
        font-size: 0.72rem;
        font-weight: 800;
        color: rgba(255, 255, 255, 0.72);
        margin-bottom: 0.35rem;
    }

    .species-stat .value {
        font-size: 1.05rem;
        font-weight: 800;
        color: #fff;
        word-break: break-word;
        line-height: 1.3;
    }

    .species-toolbar {
        display: grid;
        grid-template-columns: minmax(0, 1fr) auto;
        gap: 1rem;
        margin-bottom: 1.5rem;
        align-items: center;
    }

    .search-box {
        background: rgba(255, 255, 255, 0.96);
        border: 1px solid rgba(76, 175, 80, 0.14);
        border-radius: 20px;
        padding: 1rem 1.1rem;
        box-shadow: 0 16px 30px rgba(26, 58, 22, 0.08);
        display: flex;
        align-items: center;
        gap: 0.85rem;
    }

    .search-box i {
        color: #2d5a27;
        font-size: 1.05rem;
    }

    .search-input {
        border: none;
        outline: none;
        width: 100%;
        background: transparent;
        font-size: 0.98rem;
        color: #1f2937;
    }

    .catalog-count {
        display: inline-flex;
        align-items: center;
        gap: 0.55rem;
        padding: 0.9rem 1rem;
        border-radius: 18px;
        background: linear-gradient(135deg, #10b981, #059669);
        color: #fff;
        font-weight: 800;
        box-shadow: 0 14px 30px rgba(16, 185, 129, 0.22);
        white-space: nowrap;
    }

    .species-grid {
        display: grid;
        grid-template-columns: repeat(3, minmax(0, 1fr));
        gap: 1.25rem;
    }

    .species-card {
        background: rgba(255, 255, 255, 0.98);
        border-radius: 24px;
        overflow: hidden;
        border: 1px solid rgba(76, 175, 80, 0.14);
        box-shadow: 0 18px 36px rgba(26, 58, 22, 0.09);
        transition: transform 0.25s ease, box-shadow 0.25s ease;
        height: 100%;
    }

    .species-card:hover {
        transform: translateY(-4px);
        box-shadow: 0 22px 44px rgba(26, 58, 22, 0.14);
    }

    .species-image-wrap {
        position: relative;
        aspect-ratio: 4 / 3;
        background: linear-gradient(135deg, rgba(16, 185, 129, 0.08), rgba(59, 130, 246, 0.06));
        overflow: hidden;
    }

    .species-image-wrap img {
        width: 100%;
        height: 100%;
        object-fit: cover;
        display: block;
    }

    .species-overlay {
        position: absolute;
        inset: 0;
        background: linear-gradient(180deg, transparent 40%, rgba(0, 0, 0, 0.55));
    }

    .species-badge {
        position: absolute;
        left: 1rem;
        bottom: 1rem;
        z-index: 1;
        display: inline-flex;
        align-items: center;
        gap: 0.45rem;
        padding: 0.45rem 0.7rem;
        border-radius: 999px;
        background: rgba(255, 255, 255, 0.16);
        color: #fff;
        backdrop-filter: blur(10px);
        font-weight: 800;
        font-size: 0.78rem;
    }

    .species-body {
        padding: 1.15rem;
    }

    .species-body h3 {
        font-size: 1.08rem;
        font-weight: 800;
        color: #1a3a16;
        margin-bottom: 0.35rem;
        line-height: 1.25;
    }

    .species-body .scientific {
        color: #64748b;
        font-style: italic;
        margin-bottom: 1rem;
        font-size: 0.94rem;
    }

    .species-actions {
        display: flex;
        gap: 0.7rem;
    }

    .species-btn {
        flex: 1;
        border: none;
        border-radius: 14px;
        padding: 0.8rem 0.9rem;
        font-weight: 800;
        transition: transform 0.25s ease, filter 0.25s ease;
    }

    .species-btn:hover {
        transform: translateY(-2px);
        filter: brightness(1.02);
    }

    .species-btn.primary {
        background: linear-gradient(135deg, #10b981, #059669);
        color: #fff;
    }

    .species-btn.secondary {
        background: linear-gradient(135deg, #3b82f6, #2563eb);
        color: #fff;
    }

    .empty-state {
        background: rgba(255, 255, 255, 0.96);
        border: 1px dashed rgba(76, 175, 80, 0.25);
        border-radius: 24px;
        padding: 2.5rem 1.5rem;
        text-align: center;
        color: #64748b;
    }

    .empty-state i {
        font-size: 3rem;
        color: #2d5a27;
        margin-bottom: 1rem;
        opacity: 0.5;
    }

    .species-modal .modal-content {
        border: none;
        border-radius: 24px;
        overflow: hidden;
    }

    .species-modal .modal-header {
        background: linear-gradient(135deg, #1a3a16, #2d5a27);
        color: #fff;
        border-bottom: none;
    }

    .species-modal .modal-title {
        font-weight: 800;
    }

    .species-modal .modal-body {
        background: #f8fbf8;
    }

    .species-modal-image {
        width: 100%;
        max-height: 420px;
        object-fit: cover;
        border-radius: 18px;
        box-shadow: 0 18px 40px rgba(26, 58, 22, 0.12);
    }

    @media (max-width: 992px) {
        .species-hero-grid,
        .species-toolbar {
            grid-template-columns: 1fr;
        }

        .species-grid {
            grid-template-columns: repeat(2, minmax(0, 1fr));
        }
    }

    @media (max-width: 576px) {
        .species-page {
            padding: 1rem 0 2rem;
        }

        .species-hero,
        .species-panel {
            border-radius: 20px;
        }

        .species-hero {
            padding: 1.25rem;
        }

        .species-stats {
            grid-template-columns: 1fr;
        }

        .species-grid {
            grid-template-columns: 1fr;
        }

        .species-actions {
            flex-direction: column;
        }
    }
</style>
@endpush

@section('content')
<div class="species-page">
    <div class="container">
        @php
            $speciesCount = $especies->count();
            $withImage = $especies->filter(fn ($item) => !empty($item->imagen))->count();
        @endphp

        <section class="species-hero">
            <div class="species-hero-grid">
                <div>
                    <div class="species-kicker">SIGMAD / Catálogo técnico</div>
                    <h1>Catálogo de Especies</h1>
                    <p>
                        Recorre tu colección forestal con una interfaz más clara, moderna y pensada para consulta rápida en campo.
                    </p>
                    <div class="species-badges">
                        <span class="species-pill"><i class="fas fa-leaf"></i>{{ $speciesCount }} especies</span>
                        <span class="species-pill"><i class="fas fa-image"></i>{{ $withImage }} con imagen</span>
                        <span class="species-pill"><i class="fas fa-magnifying-glass"></i>Búsqueda rápida</span>
                    </div>
                </div>

                <aside class="species-panel">
                    <div class="species-stats">
                        <div class="species-stat">
                            <span class="label">Total registros</span>
                            <div class="value">{{ $speciesCount }}</div>
                        </div>
                        <div class="species-stat">
                            <span class="label">Con imagen</span>
                            <div class="value">{{ $withImage }}</div>
                        </div>
                        <div class="species-stat">
                            <span class="label">Vista</span>
                            <div class="value">Tarjetas premium</div>
                        </div>
                        <div class="species-stat">
                            <span class="label">Acceso</span>
                            <div class="value">Técnico Forestal</div>
                        </div>
                    </div>
                </aside>
            </div>
        </section>

        <div class="species-toolbar">
            <div class="search-box">
                <i class="fas fa-magnifying-glass"></i>
                <input type="text" class="search-input" id="speciesSearch" placeholder="Buscar por nombre común o científico">
            </div>
            <div class="catalog-count">
                <i class="fas fa-seedling"></i>
                <span>{{ $speciesCount }} especies</span>
            </div>
        </div>

        @if($especies->count() > 0)
            <div class="species-grid" id="speciesGrid">
                @foreach($especies as $especie)
                    @php
                        $imageUrl = $especie->imagen ? asset('storage/' . $especie->imagen) : asset('assets/images/SIGMAD.svg');
                    @endphp
                    <article class="species-card species-item" data-name="{{ strtolower($especie->nom_comun . ' ' . $especie->nom_cientifico) }}">
                        <div class="species-image-wrap">
                            <img src="{{ $imageUrl }}" alt="{{ $especie->nom_comun }}" loading="lazy">
                            <div class="species-overlay"></div>
                            <span class="species-badge"><i class="fas fa-eye"></i> Vista rápida</span>
                        </div>
                        <div class="species-body">
                                <h3>{{ $especie->nom_comun }}</h3>
                                <div class="scientific">{{ $especie->nom_cientifico }}</div>
                                <div class="species-actions">
                                    <button type="button"
                                            class="species-btn primary js-open-species"
                                            data-nombre-comun="{{ $especie->nom_comun }}"
                                            data-nombre-cientifico="{{ $especie->nom_cientifico }}"
                                            data-imagen-url="{{ $imageUrl }}"
                                            data-descripcion="{{ $especie->descripcion ?? '' }}"
                                            data-has-image="{{ $especie->imagen ? 1 : 0 }}">
                                        Ver
                                    </button>
                                    <button type="button"
                                            class="species-btn secondary js-open-species"
                                            data-nombre-comun="{{ $especie->nom_comun }}"
                                            data-nombre-cientifico="{{ $especie->nom_cientifico }}"
                                            data-imagen-url="{{ $imageUrl }}"
                                            data-descripcion="{{ $especie->descripcion ?? '' }}"
                                            data-has-image="{{ $especie->imagen ? 1 : 0 }}">
                                        Detalle
                                    </button>
                                </div>
                            </div>
                    </article>
                @endforeach
            </div>

            <div class="empty-state d-none" id="speciesEmptyState">
                <i class="fas fa-box-open"></i>
                <h4>No hay coincidencias</h4>
                <p>Intenta con otro nombre común o científico.</p>
            </div>
        @else
            <div class="empty-state">
                <i class="fas fa-box-open"></i>
                <h4>No hay especies registradas</h4>
                <p>Aún no se ha añadido ninguna especie al catálogo.</p>
            </div>
        @endif
    </div>

    <div class="modal fade species-modal" id="speciesModal" tabindex="-1" aria-hidden="true">
        <div class="modal-dialog modal-dialog-centered modal-lg">
            <div class="modal-content">
                <div class="modal-header">
                    <div>
                        <h5 class="modal-title" id="modalSpeciesName">Especie</h5>
                        <small id="modalSpeciesScientificName">Nombre científico</small>
                    </div>
                    <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal" aria-label="Cerrar"></button>
                </div>
                <div class="modal-body">
                    <div class="row g-3">
                        <div class="col-md-6 text-center">
                            <img src="" id="modalSpeciesImage" class="species-modal-image" alt="Especie">
                            <div id="modalImageNote" class="mt-2 text-muted small"></div>
                        </div>
                        <div class="col-md-6">
                            <h5 id="modalSpeciesCommon" class="mb-1"></h5>
                            <p id="modalSpeciesScientific" class="mb-2 text-muted"></p>
                            <p id="modalSpeciesDescription" class="mb-0" style="white-space:pre-wrap;">Descripción no disponible.</p>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>
@endsection

@push('scripts')
<script>
    document.addEventListener('DOMContentLoaded', function () {
        const modalElement = document.getElementById('speciesModal');
        const speciesModal = new bootstrap.Modal(modalElement);
        const modalName = document.getElementById('modalSpeciesName');
        const modalScientificName = document.getElementById('modalSpeciesScientificName');
        const modalImage = document.getElementById('modalSpeciesImage');
        const searchInput = document.getElementById('speciesSearch');
        const items = Array.from(document.querySelectorAll('.species-item'));
        const grid = document.getElementById('speciesGrid');
        const emptyState = document.getElementById('speciesEmptyState');

        document.querySelectorAll('.js-open-species').forEach(function (button) {
            button.addEventListener('click', function () {
                const common = this.dataset.nombreComun || 'Especie';
                const scientific = this.dataset.nombreCientifico || '';
                const img = this.dataset.imagenUrl || '';
                const descripcion = this.dataset.descripcion || '';
                const hasImage = this.dataset.hasImage === '1';

                // Fill header/title
                modalName.textContent = common;
                modalScientificName.textContent = scientific;

                // Fill modal body fields
                const modalCommonEl = document.getElementById('modalSpeciesCommon');
                const modalScientificEl = document.getElementById('modalSpeciesScientific');
                const modalDescriptionEl = document.getElementById('modalSpeciesDescription');
                const modalImageNote = document.getElementById('modalImageNote');

                if (modalCommonEl) modalCommonEl.textContent = common;
                if (modalScientificEl) modalScientificEl.textContent = scientific;
                if (modalDescriptionEl) modalDescriptionEl.textContent = descripcion ? descripcion : 'Descripción no disponible.';

                if (modalImage) {
                    modalImage.src = img;
                    modalImage.alt = common;
                }

                if (modalImageNote) {
                    modalImageNote.textContent = hasImage ? 'Imagen proporcionada por el catálogo.' : 'Usando imagen por defecto.';
                }

                speciesModal.show();
            });
        });

        if (searchInput) {
            searchInput.addEventListener('input', function () {
                const term = this.value.trim().toLowerCase();
                let visible = 0;

                items.forEach(function (item) {
                    const match = item.dataset.name.includes(term);
                    item.classList.toggle('d-none', !match);
                    if (match) visible += 1;
                });

                if (grid && emptyState) {
                    emptyState.classList.toggle('d-none', visible !== 0 || term === '');
                    grid.classList.toggle('d-none', visible === 0 && term !== '');
                }
            });
        }
    });
</script>
@endpush