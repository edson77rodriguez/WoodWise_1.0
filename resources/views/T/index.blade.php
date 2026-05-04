@extends('layouts.app')

@push('styles')
    <link href="{{ asset('css/WW/tecnico-dashboard.css') }}?v={{ filemtime(public_path('css/WW/tecnico-dashboard.css')) }}" rel="stylesheet">
@endpush

@section('content')
    <div class="container-fluid py-4">
       <!-- Header Épico con Efectos Avanzados -->
<div class="row mb-5">
    <div class="col-12">
        <div class="epic-header-card">
            <!-- Efecto de partículas -->
            <div class="particles-container">
                <div class="particle"></div>
                <div class="particle"></div>
                <div class="particle"></div>
                <div class="particle"></div>
                <div class="particle"></div>
            </div>
            
            <!-- Contenido principal -->
            <div class="header-content-wrapper">
                <div class="d-flex justify-content-between align-items-center flex-wrap gap-3">
                    <!-- Lado izquierdo - Branding -->
                    <div class="d-flex align-items-center">
                        <div class="animated-logo">
                            <div class="logo-core">
                                <i class="fas fa-tree"></i>
                            </div>
                            <div class="logo-ring ring-1"></div>
                            <div class="logo-ring ring-2"></div>
                            <div class="logo-ring ring-3"></div>
                        </div>
                        <div class="header-text-content ms-4">
                            <h1 class="header-title gradient-text">
                                Panel del Técnico
                                <span class="title-underline"></span>
                            </h1>
                            <p class="header-subtitle">
                                <span class="typing-text">Gestión profesional de recursos maderables sostenibles</span>
                            </p>
                            <div class="header-stats">
                                <div class="stat-chip">
                                    <i class="fas fa-user-check me-1"></i>
                                    <span>Técnico {{ $user->persona->nom ?? 'Usuario' }}</span>
                                </div>
                                <div class="stat-chip">
                                    <i class="fas fa-id-card me-1"></i>
                                    <span>{{ $tecnico->clave_tecnico ?? 'Sin clave' }}</span>
                                </div>
                            </div>
                        </div>
                    </div>

                    <!-- Lado derecho - Acciones -->
                    <div class="header-actions">
                        <button class="btn-epic-primary" data-bs-toggle="modal" data-bs-target="#createParcelaModal">
                            <span class="btn-content">
                                <i class="fas fa-plus btn-icon"></i>
                                <span class="btn-text">Nueva Parcela</span>
                            </span>
                            <div class="btn-shine"></div>
                        </button>
                        
                        <!-- User menu moved to global navbar to avoid duplication -->
                    </div>
                </div>
            </div>

            <!-- Efecto de borde animado -->
            <div class="border-animation"></div>
        </div>
    </div>
</div>

       <!-- Dashboard de Métricas Épico -->
<div class="row mb-5">
    <div class="col-12">
        <div class="metrics-dashboard">
            <!-- Efectos de fondo -->
            <div class="dashboard-glow"></div>
            <div class="floating-shapes">
                <div class="shape shape-1"></div>
                <div class="shape shape-2"></div>
                <div class="shape shape-3"></div>
            </div>

            <!-- Header del Perfil Mejorado -->
            <div class="profile-hero">
                <div class="profile-main">
                    <div class="avatar-3d">
                        <div class="avatar-core">
                            <i class="fas fa-user-tie"></i>
                        </div>
                        <div class="avatar-ring"></div>
                        <div class="avatar-badge">
                            <i class="fas fa-check"></i>
                        </div>
                    </div>
                    <div class="profile-info">
                        <h1 class="profile-title">Técnico <span class="gradient-name">{{ $user->persona->nom ?? 'Usuario' }}</span></h1>
                        <p class="profile-subtitle">Especialista en Gestión Forestal</p>
                        <div class="profile-metadata">
                            <div class="meta-item">
                                <i class="fas fa-id-card"></i>
                                <span>{{ $tecnico->cedula_p ?? 'No disponible' }}</span>
                            </div>
                            <div class="meta-item">
                                <i class="fas fa-key"></i>
                                <span>{{ $tecnico->clave_tecnico ?? 'N/A' }}</span>
                            </div>
                            <div class="meta-item">
                                <i class="fas fa-check-circle"></i>
                                <span>Activo ahora</span>
                            </div>
                        </div>
                    </div>
                </div>
                <div class="profile-stats-preview">
                    <div class="preview-item">
                        <span class="preview-value">{{ $parcelas->total() }}</span>
                        <span class="preview-label">Parcelas</span>
                    </div>
                    <div class="preview-item">
                        <span class="preview-value">{{ $totalTrozas }}</span>
                        <span class="preview-label">Trozas</span>
                    </div>
                    <div class="preview-item">
                        <span class="preview-value">{{ number_format($totalVolumenMaderable ?? 0, 2) }}</span>
                        <span class="preview-label">Volumen (m³)</span>
                    </div>
                </div>
            </div>

            <!-- Grid de Métricas Animadas -->
            <div class="metrics-grid">
                <!-- Métrica 1: Parcelas -->
                <div class="metric-card" data-aos="fade-up">
                    <div class="metric-header">
                        <div class="metric-icon-wrapper">
                            <div class="metric-icon-bg"></div>
                            <i class="fas fa-map-marked-alt"></i>
                        </div>
                        <div class="metric-trend positive">
                            <i class="fas fa-arrow-up"></i>
                            <span>12%</span>
                        </div>
                    </div>
                    <div class="metric-content">
                        <h3 class="metric-value">{{ $parcelas->total() }}</h3>
                        <p class="metric-label">Parcelas Asignadas</p>
                        <div class="metric-progress">
                            <div class="progress-track"><div class="progress-fill" style="width: 100%"></div></div>
                            <span class="progress-text">Completo</span>
                        </div>
                    </div>
                    <div class="metric-footer">
                        <span class="metric-info">
                            <i class="fas fa-info-circle"></i> Gestión activa
                        </span>
                    </div>
                </div>

                <!-- Métrica 2: Trozas -->
                <div class="metric-card" data-aos="fade-up" data-aos-delay="100">
                    <div class="metric-header">
                        <div class="metric-icon-wrapper">
                            <div class="metric-icon-bg"></div>
                            <i class="fas fa-cut"></i>
                        </div>
                        <div class="metric-trend warning">
                            <i class="fas fa-minus"></i>
                            <span>5%</span>
                        </div>
                    </div>
                    <div class="metric-content">
                        <h3 class="metric-value">{{ $totalTrozas }}</h3>
                        <p class="metric-label">Trozas Registradas</p>
                        <div class="metric-progress">
                            <div class="progress-track"><div class="progress-fill" style="width: 75%"></div></div>
                            <span class="progress-text">75% Cap.</span>
                        </div>
                    </div>
                    <div class="metric-footer">
                        <span class="metric-info">
                            <i class="fas fa-clock"></i> Actualizado hoy
                        </span>
                    </div>
                </div>

                <!-- Métrica 3: Estimaciones -->
                <div class="metric-card" data-aos="fade-up" data-aos-delay="200">
                    <div class="metric-header">
                        <div class="metric-icon-wrapper">
                            <div class="metric-icon-bg"></div>
                            <i class="fas fa-calculator"></i>
                        </div>
                        <div class="metric-trend positive">
                            <i class="fas fa-arrow-up"></i>
                            <span>23%</span>
                        </div>
                    </div>
                    <div class="metric-content">
                        <h3 class="metric-value">{{ $totalEstimaciones ?? 0 }}</h3>
                        <p class="metric-label">Estimaciones Realizadas</p>
                        <div class="metric-progress">
                            <div class="progress-track"><div class="progress-fill" style="width: 60%"></div></div>
                            <span class="progress-text">60% Obj.</span>
                        </div>
                    </div>
                    <div class="metric-footer">
                        <span class="metric-info">
                            <i class="fas fa-chart-line"></i> En crecimiento
                        </span>
                    </div>
                </div>

                <!-- Métrica 4: Volumen -->
                <div class="metric-card" data-aos="fade-up" data-aos-delay="300">
                    <div class="metric-header">
                        <div class="metric-icon-wrapper">
                            <div class="metric-icon-bg"></div>
                            <i class="fas fa-cubes"></i>
                        </div>
                        <div class="metric-trend excellent">
                            <i class="fas fa-rocket"></i>
                            <span>45%</span>
                        </div>
                    </div>
                    <div class="metric-content">
                        <h3 class="metric-value">{{ number_format($totalVolumenMaderable ?? 0, 2) }}</h3>
                        <p class="metric-label">Volumen Total (m³)</p>
                        <div class="metric-progress">
                            <div class="progress-track"><div class="progress-fill" style="width: 85%"></div></div>
                            <span class="progress-text">85% Rec.</span>
                        </div>
                    </div>
                    <div class="metric-footer">
                        <span class="metric-info">
                            <i class="fas fa-trophy"></i> Récord personal
                        </span>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>
<!-- Tabla de Parcelas Épica -->
<div class="row">
    <div class="col-12">
        <div class="advanced-table-container">
            <!-- Header Avanzado -->
            <div class="table-hero-header">
                <div class="header-content">
                    <div class="header-brand">
                        <div class="table-icon-animated">
                            <i class="fas fa-map-marked-alt"></i>
                            <div class="icon-pulse"></div>
                        </div>
                        <div class="header-text">
                            <h2 class="table-title">Parcelas Asignadas</h2>
                            <p class="table-subtitle">Gestión integral de recursos forestales</p>
                        </div>
                    </div>
                    <div class="header-controls">
                        <div class="search-advanced">
                            <div class="search-icon"><i class="fas fa-search"></i></div>
                            <input type="text" class="search-input" placeholder="Buscar parcela, productor, ubicación...">
                            <div class="search-actions">
                                <button class="search-clear" type="button">
                                    <i class="fas fa-times"></i>
                                </button>
                            </div>
                        </div>
                        <div class="filters-group">
                            <div class="dropdown filter-dropdown">
                                <button class="btn-filter" data-bs-toggle="dropdown" aria-haspopup="true" aria-expanded="false">
                                    <i class="fas fa-sliders-h"></i>
                                    <span>Filtros</span>
                                    <span class="filter-badge">3</span>
                                </button>
                                <div class="dropdown-menu filter-menu">
                                    <div class="filter-section">
                                        <h6>Estado de Parcela</h6>
                                        <div class="filter-options">
                                            <label class="filter-checkbox">
                                                <input type="checkbox" checked>
                                                <span>Activas</span>
                                            </label>
                                            <label class="filter-checkbox">
                                                <input type="checkbox">
                                                <span>Inactivas</span>
                                            </label>
                                        </div>
                                    </div>
                                    <div class="filter-section">
                                        <h6>Extensión (hectáreas)</h6>
                                        <div class="range-inputs">
                                            <input type="number" placeholder="Mín" class="form-control">
                                            <span>—</span>
                                            <input type="number" placeholder="Máx" class="form-control">
                                        </div>
                                    </div>
                                    <div class="filter-actions">
                                        <button type="button" class="btn-apply">Aplicar</button>
                                        <button type="button" class="btn-reset">Limpiar</button>
                                    </div>
                                </div>
                            </div>
                            <button class="btn-export" type="button">
                                <i class="fas fa-download"></i>
                                <span>Exportar</span>
                            </button>
                        </div>
                    </div>
                </div>
                <!-- Stats Rápidas -->
                <div class="table-stats">
                    <div class="stat-item">
                        <span class="stat-value">{{ $parcelas->total() }}</span>
                        <span class="stat-label">Parcelas</span>
                    </div>
                    <div class="stat-item">
                        <span class="stat-value">{{ $totalTrozas }}</span>
                        <span class="stat-label">Trozas</span>
                    </div>
                    <div class="stat-item">
                        <span class="stat-value">{{ number_format($totalVolumenMaderable ?? 0, 1) }}</span>
                        <span class="stat-label">Volumen m³</span>
                    </div>
                    <div class="stat-item">
                        <span class="stat-value">{{ $parcelas->count() }}</span>
                        <span class="stat-label">Mostrando</span>
                    </div>
                </div>
            </div>

            <!-- Contenedor de Tarjetas -->
            <div class="parcel-cards-container">
                <div class="parcel-cards-grid" id="parcelasGrid">
                    @forelse($parcelas as $index => $parcela)
                        @php
                            $total_estimaciones_parcela = ($parcela->estimaciones_count ?? 0) + ($parcela->estimaciones1_count ?? 0);
                            $volumen_parcela = ($parcela->estimaciones_sum_calculo ?? 0) + ($parcela->estimaciones1_sum_calculo ?? 0);
                            $producerName = 'No asignado';
                            if ($parcela->productor && $parcela->productor->persona) {
                                $producerName = trim(($parcela->productor->persona->nom ?? '') . ' ' . ($parcela->productor->persona->ap ?? ''));
                            }
                        @endphp
                        <div class="parcela-float-card" data-parcela-id="{{ $parcela->id_parcela }}" data-parcela-name="{{ $parcela->nom_parcela }}" data-producer="{{ $producerName }}" data-location="{{ $parcela->ubicacion }}" data-extension="{{ (float) $parcela->extension }}" data-arboles="{{ (int) ($parcela->arboles_count ?? 0) }}" data-trozas="{{ (int) $parcela->trozas_count }}" data-estimaciones="{{ (int) $total_estimaciones_parcela }}" data-volumen="{{ number_format($volumen_parcela, 2, '.', '') }}">
                            <div class="parcela-float-card__top">
                                <div class="parcela-float-card__title">
                                    <div class="parcela-float-card__icon">
                                        <i class="fas fa-map-marker-alt"></i>
                                        <span class="parcela-float-card__status" aria-hidden="true"></span>
                                    </div>
                                    <div class="parcela-float-card__title-text">
                                        <div class="parcela-float-card__name">{{ $parcela->nom_parcela }}</div>
                                        <div class="parcela-float-card__meta">
                                            <span class="parcela-float-card__code">#{{ $parcela->id_parcela }}</span>
                                            <span class="parcela-float-card__badge">Activa</span>
                                        </div>
                                    </div>
                                </div>
                                <div class="action-menu-mobile action-menu-inline" aria-label="Acciones">
                                    <button class="action-menu-trigger" type="button" aria-haspopup="true" aria-expanded="false">
                                        <i class="fas fa-ellipsis-vertical"></i>
                                    </button>
                                    <div class="action-menu-list">
                                        <a href="{{ url('/T/parcelas/' . $parcela->id_parcela . '/export-pdf') }}" class="action-menu-item">
                                            <i class="fas fa-file-pdf"></i>
                                            <span>Exportar PDF</span>
                                        </a>
                                        <button class="action-menu-item" data-bs-toggle="modal" data-bs-target="#addTrozaModal{{ $parcela->id_parcela }}">
                                            <i class="fas fa-cut"></i>
                                            <span>Nueva Troza</span>
                                        </button>
                                        <button class="action-menu-item" data-bs-toggle="modal" data-bs-target="#estimacionTrozaModal{{ $parcela->id_parcela }}">
                                            <i class="fas fa-cube"></i>
                                            <span>Estimación de Troza</span>
                                        </button>
                                        <button class="action-menu-item" data-bs-toggle="modal" data-bs-target="#addArbolModal{{ $parcela->id_parcela }}">
                                            <i class="fas fa-tree"></i>
                                            <span>Nuevo Árbol</span>
                                        </button>
                                        <button class="action-menu-item" data-bs-toggle="modal" data-bs-target="#estimacionArbolModal{{ $parcela->id_parcela }}">
                                            <i class="fas fa-tree"></i>
                                            <span>Estimación de Árbol</span>
                                        </button>
                                        <a href="{{ route('tecnico.parcela.detalle', $parcela->id_parcela) }}" class="action-menu-item">
                                            <i class="fas fa-eye"></i>
                                            <span>Ver Detalles</span>
                                        </a>
                                    </div>
                                </div>
                            </div>
                            <div class="parcela-float-card__info">
                                <div class="parcela-float-card__info-row">
                                    <div class="parcela-float-card__info-k">
                                        <i class="fas fa-user"></i>
                                        <span>Productor</span>
                                    </div>
                                    <div class="parcela-float-card__info-v">{{ $producerName }}</div>
                                </div>
                                <div class="parcela-float-card__info-row">
                                    <div class="parcela-float-card__info-k">
                                        <i class="fas fa-location-dot"></i>
                                        <span>Ubicación</span>
                                    </div>
                                    <div class="parcela-float-card__info-v">{{ $parcela->ubicacion }}</div>
                                </div>
                                <div class="parcela-float-card__info-row">
                                    <div class="parcela-float-card__info-k">
                                        <i class="fas fa-ruler-combined"></i>
                                        <span>Extensión</span>
                                    </div>
                                    <div class="parcela-float-card__info-v">{{ number_format($parcela->extension, 2) }} ha</div>
                                </div>
                            </div>
                            <div class="parcela-float-card__metrics">
                                <div class="parcela-float-metric">
                                    <div class="parcela-float-metric__icon trees"><i class="fas fa-tree"></i></div>
                                    <div class="parcela-float-metric__val">{{ $parcela->arboles_count ?? 0 }}</div>
                                    <div class="parcela-float-metric__lbl">Árboles</div>
                                </div>
                                <div class="parcela-float-metric">
                                    <div class="parcela-float-metric__icon trozas"><i class="fas fa-cut"></i></div>
                                    <div class="parcela-float-metric__val">{{ $parcela->trozas_count }}</div>
                                    <div class="parcela-float-metric__lbl">Trozas</div>
                                </div>
                                <div class="parcela-float-metric">
                                    <div class="parcela-float-metric__icon est"><i class="fas fa-calculator"></i></div>
                                    <div class="parcela-float-metric__val">{{ $total_estimaciones_parcela }}</div>
                                    <div class="parcela-float-metric__lbl">Estimaciones</div>
                                </div>
                                <div class="parcela-float-metric">
                                    <div class="parcela-float-metric__icon vol"><i class="fas fa-cubes"></i></div>
                                    <div class="parcela-float-metric__val">{{ number_format($volumen_parcela, 2) }}</div>
                                    <div class="parcela-float-metric__lbl">Volumen</div>
                                </div>
                            </div>
                        </div>
                    @empty
                        <div class="empty-table-state">
                            <div class="empty-icon">
                                <i class="fas fa-map-marked-alt"></i>
                            </div>
                            <h4>No hay parcelas asignadas</h4>
                            <p>Comienza creando tu primera parcela para gestionar los recursos forestales</p>
                            <button class="btn-create-parcela" data-bs-toggle="modal" data-bs-target="#createParcelaModal">
                                <i class="fas fa-plus"></i>
                                Crear Primera Parcela
                            </button>
                        </div>
                    @endforelse
                </div>
            </div>

            <!-- Footer de Tabla -->
            @if($parcelas->hasPages())
                <div class="table-footer">
                    <div class="footer-info">
                        Mostrando {{ $parcelas->firstItem() ?? 0 }}-{{ $parcelas->lastItem() ?? 0 }} de {{ $parcelas->total() }} parcelas
                    </div>
                    <div class="footer-pagination">
                        {{ $parcelas->links() }}
                    </div>
                    <div class="footer-actions">
                        <select class="page-size-select">
                            <option value="10">10 por página</option>
                            <option value="25">25 por página</option>
                            <option value="50">50 por página</option>
                        </select>
                    </div>
                </div>
            @endif
        </div>
    </div>
</div>
    @foreach($parcelas as $parcela)
        @include('partials.modals.arbol-create', ['parcela' => $parcela])
        @include('partials.modals.troza-create', ['parcela' => $parcela])
        @include('partials.modals.estimacion-troza', ['parcela' => $parcela])
        @include('partials.modals.estimacion-arbol', ['parcela' => $parcela])
    @endforeach
@endsection

@push('scripts')
<script src="https://cdn.jsdelivr.net/npm/exceljs@4.4.0/dist/exceljs.min.js"></script>
<script>
    document.addEventListener('DOMContentLoaded', function() {
        // Tooltips
        var tooltipTriggerList = [].slice.call(document.querySelectorAll('[data-bs-toggle="tooltip"]'));
        var tooltipList = tooltipTriggerList.map(function (tooltipTriggerEl) {
            return new bootstrap.Tooltip(tooltipTriggerEl);
        });

        // User dropdown is handled globally in public/js/WW/layout.js

        // Acciones compactas por fila (móvil)
        document.querySelectorAll('.action-menu-trigger').forEach(trigger => {
            trigger.addEventListener('click', function(e) {
                e.stopPropagation();
                const menu = this.nextElementSibling;
                if (!menu) return;
                document.querySelectorAll('.action-menu-list.show').forEach(openMenu => {
                    if (openMenu !== menu) openMenu.classList.remove('show');
                });
                menu.classList.toggle('show');
                this.setAttribute('aria-expanded', menu.classList.contains('show') ? 'true' : 'false');
            });
        });

        document.addEventListener('click', function() {
            document.querySelectorAll('.action-menu-list.show').forEach(menu => menu.classList.remove('show'));
            document.querySelectorAll('.action-menu-trigger[aria-expanded="true"]').forEach(btn => btn.setAttribute('aria-expanded', 'false'));
        });

        // Efectos hover modernos
        document.querySelectorAll('.modern-stat-card').forEach(card => {
            card.addEventListener('mouseenter', function() {
                this.style.transform = 'translateY(-5px)';
            });
            card.addEventListener('mouseleave', function() {
                this.style.transform = 'translateY(0)';
            });
        });

        // =============== BÚSQUEDA EN TIEMPO REAL ===============
        const searchInput = document.querySelector('.search-input');
        const parcelCards = document.querySelectorAll('.parcela-float-card');
        
        if (searchInput) {
            searchInput.addEventListener('input', function() {
                const searchTerm = this.value.toLowerCase().trim();
                
                parcelCards.forEach(card => {
                    const text = card.textContent.toLowerCase();
                    card.style.display = text.includes(searchTerm) ? '' : 'none';
                });
            });
            
            // Botón limpiar búsqueda
            const clearBtn = document.querySelector('.search-clear');
            if (clearBtn) {
                clearBtn.addEventListener('click', function() {
                    searchInput.value = '';
                    searchInput.dispatchEvent(new Event('input'));
                });
            }
        }

        // =============== FILTROS FUNCIONALES ===============
        const filterCheckboxes = document.querySelectorAll('.filter-checkbox input');
        const rangeInputs = document.querySelectorAll('.range-inputs input');
        const applyBtn = document.querySelector('.btn-apply');
        const resetBtn = document.querySelector('.btn-reset');
        
        if (applyBtn) {
            applyBtn.addEventListener('click', function() {
                const minExt = parseFloat(document.querySelector('.range-inputs input:first-child')?.value) || 0;
                const maxExt = parseFloat(document.querySelector('.range-inputs input:last-child')?.value) || Infinity;
                
                parcelCards.forEach(card => {
                    const ext = parseFloat(card.dataset.extension || '0') || 0;
                    card.style.display = (ext >= minExt && ext <= maxExt) ? '' : 'none';
                });
                
                // Cerrar dropdown
                const dropdown = document.querySelector('.filter-dropdown .dropdown-menu');
                if (dropdown) {
                    bootstrap.Dropdown.getInstance(document.querySelector('.btn-filter'))?.hide();
                }
            });
        }
        
        if (resetBtn) {
            resetBtn.addEventListener('click', function() {
                filterCheckboxes.forEach(cb => cb.checked = true);
                rangeInputs.forEach(input => input.value = '');
                parcelCards.forEach(card => card.style.display = '');
            });
        }

        // =============== EXPORTAR (XLSX si es posible, si no CSV) ===============
        const exportBtn = document.querySelector('.btn-export');
        if (exportBtn) {
            exportBtn.addEventListener('click', async function() {
                const cards = Array.from(document.querySelectorAll('.parcela-float-card'));
                if (!cards.length) return;

                const visibleCards = cards.filter(c => c.style.display !== 'none');
                const today = new Date().toISOString().slice(0,10);
                let excelGenerated = false;

                // Preferimos XLSX (ExcelJS) para algo más profesional.
                if (typeof window.ExcelJS !== 'undefined' && visibleCards.length) {
                    const originalText = exportBtn.textContent;
                    exportBtn.disabled = true;
                    exportBtn.style.opacity = '0.75';
                    exportBtn.textContent = 'Exportando...';

                    try {
                        const workbook = new ExcelJS.Workbook();
                        workbook.creator = 'SIGMAD';
                        workbook.created = new Date();

                        const sheet = workbook.addWorksheet('Parcelas', {
                            views: [{ state: 'frozen', ySplit: 1 }]
                        });

                        const columns = [
                            { header: 'Parcela', key: 'parcela', width: 28 },
                            { header: 'ID', key: 'id', width: 10 },
                            { header: 'Productor', key: 'productor', width: 28 },
                            { header: 'Ubicación', key: 'ubicacion', width: 28 },
                            { header: 'Extensión (ha)', key: 'extension', width: 16 },
                            { header: 'Árboles', key: 'arboles', width: 10 },
                            { header: 'Trozas', key: 'trozas', width: 10 },
                            { header: 'Estimaciones', key: 'estimaciones', width: 14 },
                            { header: 'Volumen', key: 'volumen', width: 14 },
                        ];

                        sheet.columns = columns;

                        // Encabezado
                        sheet.getRow(1).values = columns.map(c => c.header);
                        const headerRow = sheet.getRow(1);
                        headerRow.height = 20;
                        headerRow.eachCell(cell => {
                            cell.font = { bold: true, color: { argb: 'FFFFFFFF' } };
                            cell.alignment = { vertical: 'middle', horizontal: 'center' };
                            cell.fill = { type: 'pattern', pattern: 'solid', fgColor: { argb: 'FF1A5F2A' } };
                        });

                        // Datos
                        visibleCards.forEach(card => {
                            sheet.addRow({
                                parcela: (card.dataset.parcelaName || '').trim(),
                                id: (card.dataset.parcelaId || '').trim(),
                                productor: (card.dataset.producer || '').trim(),
                                ubicacion: (card.dataset.location || '').trim(),
                                extension: parseFloat(card.dataset.extension || '0') || 0,
                                arboles: parseInt(card.dataset.arboles || '0', 10) || 0,
                                trozas: parseInt(card.dataset.trozas || '0', 10) || 0,
                                estimaciones: parseInt(card.dataset.estimaciones || '0', 10) || 0,
                                volumen: parseFloat(card.dataset.volumen || '0') || 0,
                            });
                        });

                        // Estilo filas (alternado suave sin usar Table)
                        const border = {
                            top: { style: 'thin', color: { argb: 'FFE2E8F0' } },
                            left: { style: 'thin', color: { argb: 'FFE2E8F0' } },
                            bottom: { style: 'thin', color: { argb: 'FFE2E8F0' } },
                            right: { style: 'thin', color: { argb: 'FFE2E8F0' } },
                        };

                        sheet.eachRow((row, rowNumber) => {
                            row.eachCell(cell => {
                                cell.border = border;
                                if (rowNumber !== 1) {
                                    cell.alignment = { vertical: 'middle' };
                                }
                            });

                            if (rowNumber > 1 && rowNumber % 2 === 0) {
                                row.eachCell(cell => {
                                    cell.fill = { type: 'pattern', pattern: 'solid', fgColor: { argb: 'FFF8FAFC' } };
                                });
                            }
                        });

                        // Formatos numéricos
                        const extensionCol = 5;
                        const volumeCol = 9;
                        for (let r = 2; r <= sheet.rowCount; r++) {
                            sheet.getRow(r).getCell(extensionCol).numFmt = '0.00';
                            sheet.getRow(r).getCell(volumeCol).numFmt = '0.00';
                        }

                        // Auto filtro
                        sheet.autoFilter = {
                            from: 'A1',
                            to: 'I1'
                        };

                        const buffer = await workbook.xlsx.writeBuffer();
                        const blob = new Blob([buffer], {
                            type: 'application/vnd.openxmlformats-officedocument.spreadsheetml.sheet'
                        });
                        const link = document.createElement('a');
                        link.href = URL.createObjectURL(blob);
                        link.download = `parcelas_tecnico_${today}.xlsx`;
                        document.body.appendChild(link);
                        link.click();
                        document.body.removeChild(link);
                        URL.revokeObjectURL(link.href);
                        excelGenerated = true;
                    } catch (err) {
                        console.error(err);
                        alert('No se pudo generar el Excel. Se exportará como CSV.');
                        // fallback CSV abajo
                    } finally {
                        exportBtn.disabled = false;
                        exportBtn.style.opacity = '';
                        exportBtn.textContent = originalText;
                    }

                    // Si el XLSX se generó correctamente, ya terminamos.
                    if (excelGenerated) return;
                }

                // Fallback CSV
                const headers = [
                    'Parcela',
                    'ID',
                    'Productor',
                    'Ubicación',
                    'Extensión (ha)',
                    'Árboles',
                    'Trozas',
                    'Estimaciones',
                    'Volumen'
                ];

                let csv = [];
                csv.push(headers.map(h => '"' + h.replace(/"/g, '""') + '"').join(','));

                visibleCards.forEach(card => {
                    const row = [
                        card.dataset.parcelaName || '',
                        card.dataset.parcelaId || '',
                        card.dataset.producer || '',
                        card.dataset.location || '',
                        card.dataset.extension || '',
                        card.dataset.arboles || '0',
                        card.dataset.trozas || '0',
                        card.dataset.estimaciones || '0',
                        card.dataset.volumen || '0'
                    ].map(v => '"' + String(v).trim().replace(/\s+/g, ' ').replace(/"/g, '""') + '"');
                    csv.push(row.join(','));
                });

                const blob = new Blob([csv.join('\n')], { type: 'text/csv;charset=utf-8;' });
                const link = document.createElement('a');
                link.href = URL.createObjectURL(blob);
                link.download = 'parcelas_tecnico_' + today + '.csv';
                link.click();
            });
        }

        // =============== AUTO-SELECCIÓN DE FÓRMULA POR ESPECIE ===============
        // Mapeo especie -> fórmula de biomasa
        const especieFormulaMap = {
            1: 8,  // Pinus pseudostrobus -> Biomasa Pinus pseudostrobus
            2: 7,  // Quercus rugosa -> Biomasa Quercus rugosa
            3: 5,  // Pinus montezumae -> Biomasa Pinus montezumae
            4: 6   // Quercus crassifolia -> Biomasa Quercus crassifolia
        };

        // Event listeners para selects de árboles
        document.querySelectorAll('.select-arbol-estimacion').forEach(selectArbol => {
            selectArbol.addEventListener('change', function() {
                const parcelaId = this.dataset.parcela;
                const selectedOption = this.options[this.selectedIndex];
                const especieId = selectedOption.dataset.especie;
                
                // Encontrar el select de fórmula correspondiente
                const selectFormula = document.querySelector(`.select-formula-arbol[data-parcela="${parcelaId}"]`);
                
                if (selectFormula && especieId && especieFormulaMap[especieId]) {
                    const formulaId = especieFormulaMap[especieId];
                    selectFormula.value = formulaId;
                    
                    // Efecto visual de cambio
                    selectFormula.style.transition = 'background-color 0.3s ease';
                    selectFormula.style.backgroundColor = '#d4edda';
                    setTimeout(() => {
                        selectFormula.style.backgroundColor = '';
                    }, 1000);
                }
            });
        });
    });
</script>
@endpush