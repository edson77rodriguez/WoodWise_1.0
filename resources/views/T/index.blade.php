@extends('layouts.app')

@push('styles')
    <link href="{{ asset('css/WW/tecnico-dashboard.css') }}" rel="stylesheet">
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
                <div class="d-flex justify-content-between align-items-center flex-wrap gap-4">
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
                                Panel del Técnico Forestal
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
                                    <span>{{ $tecnico->clave_tecnico }}</span>
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
                        
                        <!-- Acciones rápidas -->
                        <div class="quick-actions">
                            <button class="quick-btn" data-bs-toggle="tooltip" title="Estadísticas">
                                <i class="fas fa-chart-bar"></i>
                            </button>
                            <button class="quick-btn" data-bs-toggle="tooltip" title="Reportes">
                                <i class="fas fa-file-alt"></i>
                            </button>
                            <button class="quick-btn" data-bs-toggle="tooltip" title="Configuración">
                                <i class="fas fa-cog"></i>
                            </button>
                        </div>
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
                        <h1 class="profile-title">
                            Técnico <span class="gradient-name">{{ $user->persona->nom ?? 'Usuario' }}</span>
                        </h1>
                        <p class="profile-subtitle">Especialista en Gestión Forestal</p>
                        
                        <div class="profile-metadata">
                            <div class="meta-item">
                                <i class="fas fa-fingerprint"></i>
                                <span>{{ $tecnico->cedula_p ?? 'No disponible' }}</span>
                            </div>
                            <div class="meta-item">
                                <i class="fas fa-key"></i>
                                <span>{{ $tecnico->clave_tecnico }}</span>
                            </div>
                            <div class="meta-item">
                                <i class="fas fa-calendar"></i>
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
                        <span class="preview-value">{{ $totalVolumenMaderable ?? 0 }}m³</span>
                        <span class="preview-label">Volumen</span>
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
                        <h3 class="metric-value" data-count="{{ $parcelas->total() }}">0</h3>
                        <p class="metric-label">Parcelas Asignadas</p>
                        <div class="metric-progress">
                            <div class="progress-track">
                                <div class="progress-fill" style="width: 100%"></div>
                            </div>
                            <span class="progress-text">Completo</span>
                        </div>
                    </div>
                    <div class="metric-footer">
                        <span class="metric-info">
                            <i class="fas fa-info-circle"></i>
                            Gestión activa
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
                        <h3 class="metric-value" data-count="{{ $totalTrozas }}">0</h3>
                        <p class="metric-label">Trozas Registradas</p>
                        <div class="metric-progress">
                            <div class="progress-track">
                                <div class="progress-fill" style="width: 75%"></div>
                            </div>
                            <span class="progress-text">75% capacidad</span>
                        </div>
                    </div>
                    <div class="metric-footer">
                        <span class="metric-info">
                            <i class="fas fa-clock"></i>
                            Actualizado hoy
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
                        <h3 class="metric-value" data-count="{{ $totalEstimaciones ?? 0 }}">0</h3>
                        <p class="metric-label">Estimaciones Realizadas</p>
                        <div class="metric-progress">
                            <div class="progress-track">
                                <div class="progress-fill" style="width: 60%"></div>
                            </div>
                            <span class="progress-text">60% del objetivo</span>
                        </div>
                    </div>
                    <div class="metric-footer">
                        <span class="metric-info">
                            <i class="fas fa-chart-line"></i>
                            En crecimiento
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
                    {{-- 
                    AQUÍ ESTÁ LA CORRECCIÓN:
                    Quitamos number_format() del 'data-count'.
                    El script de animación usará el número puro (ej. 1234.5)
                    y el '0.0' es solo el valor inicial antes de que el script se ejecute.
                    --}}
                    <h3 class="metric-value" data-count="{{ $totalVolumenMaderable ?? 0 }}">0.0</h3>
                    
                    <p class="metric-label">Volumen Total (m³)</p>
                    <div class="metric-progress">
                        <div class="progress-track">
                            <div class="progress-fill" style="width: 85%"></div>
                        </div>
                        <span class="progress-text">85% récord</span>
                    </div>
                </div>
                <div class="metric-footer">
                    <span class="metric-info">
                        <i class="fas fa-trophy"></i>
                        Récord personal
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
                        <!-- Búsqueda Avanzada -->
                        <div class="search-advanced">
                            <div class="search-icon">
                                <i class="fas fa-search"></i>
                            </div>
                            <input type="text" class="search-input" placeholder="Buscar parcela, productor, ubicación...">
                            <div class="search-actions">
                                <button class="search-clear" type="button">
                                    <i class="fas fa-times"></i>
                                </button>
                            </div>
                        </div>
                        
                        <!-- Filtros Avanzados -->
                        <div class="filters-group">
                            <div class="dropdown filter-dropdown">
                                <button class="btn-filter" data-bs-toggle="dropdown">
                                    <i class="fas fa-filter"></i>
                                    Filtros
                                    <span class="filter-badge">3</span>
                                </button>
                                <div class="dropdown-menu filter-menu">
                                    <div class="filter-section">
                                        <h6>Estado</h6>
                                        <div class="filter-options">
                                            <label class="filter-checkbox">
                                                <input type="checkbox" checked>
                                                <span class="checkmark"></span>
                                                Activas
                                            </label>
                                            <label class="filter-checkbox">
                                                <input type="checkbox">
                                                <span class="checkmark"></span>
                                                Inactivas
                                            </label>
                                        </div>
                                    </div>
                                    <div class="filter-section">
                                        <h6>Extensión</h6>
                                        <div class="range-inputs">
                                            <input type="number" placeholder="Mín" class="form-control">
                                            <span>-</span>
                                            <input type="number" placeholder="Máx" class="form-control">
                                        </div>
                                    </div>
                                    <div class="filter-actions">
                                        <button class="btn-apply">Aplicar</button>
                                        <button class="btn-reset">Limpiar</button>
                                    </div>
                                </div>
                            </div>
                            
                            <button class="btn-export">
                                <i class="fas fa-download"></i>
                                Exportar
                            </button>
                        </div>
                    </div>
                </div>
                
                <!-- Stats Rápidas -->
                <div class="table-stats">
                    <div class="stat-item">
                        <span class="stat-value">{{ $parcelas->total() }}</span>
                        <span class="stat-label">Total Parcelas</span>
                    </div>
                    <div class="stat-item">
                        <span class="stat-value">{{ $totalTrozas }}</span>
                        <span class="stat-label">Trozas Totales</span>
                    </div>
                    <div class="stat-item">
                        <span class="stat-value">{{ number_format($totalVolumenMaderable ?? 0, 1) }}m³</span>
                        <span class="stat-label">Volumen Total</span>
                    </div>
                    <div class="stat-item">
                        <span class="stat-value">{{ $parcelas->count() }}</span>
                        <span class="stat-label">Mostrando</span>
                    </div>
                </div>
            </div>

            <!-- Contenedor de Tabla -->
            <div class="table-wrapper">
                <div class="table-scroll-container">
                    <table class="advanced-data-table">
                        <thead class="table-header-sticky">
                            <tr>
                                <th class="column-parcela">
                                    <div class="column-header">
                                        <span>Parcela</span>
                                        <button class="sort-btn" data-sort="name">
                                            <i class="fas fa-sort"></i>
                                        </button>
                                    </div>
                                </th>
                                <th class="column-productor">
                                    <div class="column-header">
                                        <span>Productor</span>
                                        <button class="sort-btn" data-sort="producer">
                                            <i class="fas fa-sort"></i>
                                        </button>
                                    </div>
                                </th>
                                <th class="column-ubicacion">
                                    <div class="column-header">
                                        <span>Ubicación</span>
                                    </div>
                                </th>
                                <th class="column-extension">
                                    <div class="column-header">
                                        <span>Extensión</span>
                                        <button class="sort-btn" data-sort="extension">
                                            <i class="fas fa-sort"></i>
                                        </button>
                                    </div>
                                </th>
                                <th class="column-arboles">
                                    <div class="column-header">
                                        <i class="fas fa-tree"></i>
                                        <span>Árboles</span>
                                    </div>
                                </th>
                                <th class="column-trozas">
                                    <div class="column-header">
                                        <i class="fas fa-cut"></i>
                                        <span>Trozas</span>
                                    </div>
                                </th>
                                <th class="column-estimaciones">
                                    <div class="column-header">
                                        <i class="fas fa-calculator"></i>
                                        <span>Estimaciones</span>
                                    </div>
                                </th>
                                <th class="column-volumen">
                                    <div class="column-header">
                                        <i class="fas fa-cubes"></i>
                                        <span>Volumen</span>
                                        <button class="sort-btn" data-sort="volume">
                                            <i class="fas fa-sort"></i>
                                        </button>
                                    </div>
                                </th>
                                <th class="column-actions">
                                    <div class="column-header">
                                        <span>Acciones</span>
                                    </div>
                                </th>
                            </tr>
                        </thead>
                        <tbody>
                            @forelse($parcelas as $index => $parcela)
                                <tr class="table-data-row" data-parcela-id="{{ $parcela->id_parcela }}">
                                    <!-- Columna Parcela -->
                                    <td class="cell-parcela">
                                        <div class="parcela-card">
                                            <div class="parcela-avatar">
                                                <i class="fas fa-map-marker-alt"></i>
                                                <div class="parcela-status active"></div>
                                            </div>
                                            <div class="parcela-info">
                                                <h6 class="parcela-name">{{ $parcela->nom_parcela }}</h6>
                                                <div class="parcela-meta">
                                                    <span class="parcela-code">#{{ $parcela->id_parcela }}</span>
                                                    <span class="parcela-badge">Activa</span>
                                                </div>
                                            </div>
                                        </div>
                                    </td>

                                    <!-- Columna Productor -->
                                    <td class="cell-productor">
                                        @if($parcela->productor && $parcela->productor->persona)
                                            <div class="producer-card">
                                                <div class="producer-avatar">
                                                    <i class="fas fa-user"></i>
                                                </div>
                                                <div class="producer-info">
                                                    <span class="producer-name">{{ $parcela->productor->persona->nom }}</span>
                                                    <span class="producer-id">{{ $parcela->productor->persona->cedula }}</span>
                                                </div>
                                            </div>
                                        @else
                                            <div class="empty-state-cell">
                                                <i class="fas fa-user-slash"></i>
                                                <span>No asignado</span>
                                            </div>
                                        @endif
                                    </td>

                                    <!-- Columna Ubicación -->
                                    <td class="cell-ubicacion">
                                        <div class="location-cell">
                                            <i class="fas fa-location-dot"></i>
                                            <span class="location-text">{{ $parcela->ubicacion }}</span>
                                        </div>
                                    </td>

                                    <!-- Columna Extensión -->
                                    <td class="cell-extension">
                                        <div class="extension-display">
                                            <div class="extension-value">{{ $parcela->extension }}</div>
                                            <div class="extension-unit">hectáreas</div>
                                        </div>
                                    </td>

                                    <!-- Columna Árboles -->
                                    <td class="cell-arboles">
                                        <div class="metric-cell trees">
                                            <div class="metric-icon">
                                                <i class="fas fa-tree"></i>
                                            </div>
                                            <div class="metric-content">
                                                <span class="metric-value">{{ $parcela->arboles_count ?? 0 }}</span>
                                                <div class="metric-progress">
                                                    <div class="progress-bar" style="width: {{ min(($parcela->arboles_count ?? 0) / 50 * 100, 100) }}%"></div>
                                                </div>
                                            </div>
                                        </div>
                                    </td>

                                    <!-- Columna Trozas -->
                                    <td class="cell-trozas">
                                        <div class="metric-cell trozas">
                                            <div class="metric-icon">
                                                <i class="fas fa-cut"></i>
                                            </div>
                                            <div class="metric-content">
                                                <span class="metric-value">{{ $parcela->trozas_count }}</span>
                                                <div class="metric-progress">
                                                    <div class="progress-bar" style="width: {{ min($parcela->trozas_count / 30 * 100, 100) }}%"></div>
                                                </div>
                                            </div>
                                        </div>
                                    </td>

                                    <!-- Columna Estimaciones -->
                                    {{-- DESPUÉS (Suma estimaciones + estimaciones1) --}}
                                    <td class="cell-estimaciones">
                                        @php
                                            // Sumamos ambos conteos que nos da el controlador
                                            $total_estimaciones_parcela = ($parcela->estimaciones_count ?? 0) + ($parcela->estimaciones1_count ?? 0);
                                        @endphp
                                        <div class="metric-cell estimations">
                                            <div class="metric-icon">
                                                <i class="fas fa-calculator"></i>
                                            </div>
                                            <div class="metric-content">
                                                <span class="metric-value">{{ $total_estimaciones_parcela }}</span>
                                                <div class="metric-progress">
                                                    {{-- Usamos el nuevo total para la barra de progreso --}}
                                                    <div class="progress-bar" style="width: {{ min(($total_estimaciones_parcela) / 20 * 100, 100) }}%"></div>
                                                </div>
                                            </div>
                                        </div>
                                    </td>

                             <td>
                                    @php
                                        // El volumen total de la parcela es la suma de sus dos tipos de estimaciones
                                        $volumen_parcela = ($parcela->estimaciones_sum_calculo ?? 0) + 
                                                        ($parcela->estimaciones1_sum_calculo ?? 0);
                                    @endphp
                                    <span class="modern-badge bg-success" 
                                        data-bs-toggle="tooltip" 
                                        title="Volumen total (Suma de Estimaciones)">
                                        <i class="fas fa-cubes me-1"></i>{{ number_format($volumen_parcela, 2) }}
                                    </span>
                                </td>

                                    <!-- Columna Acciones -->
                                        <td class="cell-actions">
                                            <div class="advanced-actions">
                                                <!-- Grupo PDF -->
                                                <div class="action-group">
                                                    <a href="{{ url('/T/parcelas/' . $parcela->id_parcela . '/export-pdf') }}" 
                                                       class="action-btn pdf" 
                                                       data-bs-toggle="tooltip" title="Exportar PDF">
                                                        <i class="fas fa-file-pdf"></i>
                                                    </a>
                                                </div>

                                                <!-- Grupo Troza + Estimación Troza -->
                                                <div class="action-group">
                                                    <button class="action-btn cut" 
                                                            data-bs-toggle="modal" 
                                                            data-bs-target="#addTrozaModal{{ $parcela->id_parcela }}"
                                                            data-bs-toggle="tooltip" title="Nueva Troza">
                                                        <i class="fas fa-cut"></i>
                                                    </button>
                                                    <button class="action-btn calc" 
                                                            data-bs-toggle="modal" 
                                                            data-bs-target="#estimacionTrozaModal{{ $parcela->id_parcela }}"
                                                            data-bs-toggle="tooltip" title="Estimación de Troza">
                                                        <i class="fas fa-cube"></i>
                                                    </button>
                                                </div>

                                                <!-- Grupo Árbol + Estimación Árbol -->
                                                <div class="action-group">
                                                    <button class="action-btn tree" 
                                                            data-bs-toggle="modal" 
                                                            data-bs-target="#addArbolModal{{ $parcela->id_parcela }}"
                                                            data-bs-toggle="tooltip" title="Nuevo Árbol">
                                                        <i class="fas fa-tree"></i>
                                                    </button>
                                                    <button class="action-btn calc" 
                                                            data-bs-toggle="modal" 
                                                            data-bs-target="#estimacionArbolModal{{ $parcela->id_parcela }}"
                                                            data-bs-toggle="tooltip" title="Estimación de Árbol">
                                                        <i class="fas fa-tree"></i>
                                                    </button>
                                                </div>

                                                <!-- Grupo Ver Detalles -->
                                                <div class="action-group">
                                                    <a href="{{ route('parcelas.show', $parcela->id_parcela) }}" 
                                                       class="action-btn view" 
                                                       data-bs-toggle="tooltip" title="Ver Detalles">
                                                        <i class="fas fa-eye"></i>
                                                    </a>
                                                </div>
                                            </div>
                                        </td>
                                </tr>
                            @empty
                                <tr>
                                    <td colspan="9">
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
                                    </td>
                                </tr>
                            @endforelse
                        </tbody>
                    </table>
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

    <!-- Los modales permanecen similares pero con clases modernizadas -->
    @include('partials.modals.parcela-create')
    @foreach($parcelas as $parcela)
        @include('partials.modals.arbol-create', ['parcela' => $parcela])
        @include('partials.modals.troza-create', ['parcela' => $parcela])
        @include('partials.modals.estimacion-troza', ['parcela' => $parcela])
        @include('partials.modals.estimacion-arbol', ['parcela' => $parcela])
    @endforeach
@endsection

@push('scripts')
<script>
    document.addEventListener('DOMContentLoaded', function() {
        // Tooltips
        var tooltipTriggerList = [].slice.call(document.querySelectorAll('[data-bs-toggle="tooltip"]'));
        var tooltipList = tooltipTriggerList.map(function (tooltipTriggerEl) {
            return new bootstrap.Tooltip(tooltipTriggerEl);
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
    });
</script>
@endpush