@extends('layouts.app')

@section('title', 'Dashboard - Productor Forestal')

@push('styles')
    <link href="{{ asset('css/WW/productor-dashboard-v2.css') }}" rel="stylesheet">
    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@400;500;600;700;800&display=swap" rel="stylesheet">
@endpush

@section('content')
<div class="producer-dashboard">
    {{-- HEADER HERO SECTION --}}
    <div class="dashboard-hero">
        <div class="hero-bg-pattern"></div>
        <div class="hero-content">
            <div class="hero-left">
                <div class="producer-avatar">
                    <span>{{ substr($user->persona->nom ?? 'P', 0, 1) }}{{ substr($user->persona->ap ?? '', 0, 1) }}</span>
                </div>
                <div class="hero-text">
                    <h1 class="hero-title">
                        ¡Bienvenido, <span class="text-gradient">{{ $user->persona->nom ?? 'Productor' }}</span>!
                    </h1>
                    <p class="hero-subtitle">
                        <i class="fas fa-leaf me-2"></i>Panel de Gestión Forestal Sostenible
                    </p>
                </div>
            </div>
            <div class="hero-actions">
                <button class="btn-hero-primary" data-bs-toggle="modal" data-bs-target="#nuevaParcelaModal">
                    <i class="fas fa-plus me-2"></i>Nueva Parcela
                </button>
                <a href="{{ route('productor.reporte.general') }}" class="btn-hero-secondary">
                    <i class="fas fa-file-pdf me-2"></i>Reporte General
                </a>
                
                {{-- User Menu --}}
                <div class="user-menu-wrapper">
                    <button class="user-menu-trigger" id="userMenuBtn" type="button">
                        <i class="fas fa-ellipsis-v"></i>
                    </button>
                    <div class="user-dropdown-menu" id="userDropdown">
                        <a href="{{ route('perfil.index') }}" class="dropdown-item">
                            <i class="fas fa-user"></i><span>Mi Perfil</span>
                        </a>
                        <a href="{{ route('especies.catalogo') }}" class="dropdown-item">
                            <i class="fas fa-seedling"></i><span>Catálogo Especies</span>
                        </a>
                        <div class="dropdown-divider"></div>
                        <form action="{{ route('logout') }}" method="POST">
                            @csrf
                            <button type="submit" class="dropdown-item logout-item">
                                <i class="fas fa-sign-out-alt"></i><span>Cerrar Sesión</span>
                            </button>
                        </form>
                    </div>
                </div>
            </div>
        </div>
    </div>

    {{-- ESTADÍSTICAS PRINCIPALES --}}
    <div class="stats-section">
        <div class="stats-grid">
            <div class="stat-card stat-parcelas">
                <div class="stat-icon">
                    <i class="fas fa-map"></i>
                </div>
                <div class="stat-content">
                    <span class="stat-value">{{ $stats['total_parcelas'] }}</span>
                    <span class="stat-label">Parcelas</span>
                </div>
                <div class="stat-decoration"></div>
            </div>
            
            <div class="stat-card stat-extension">
                <div class="stat-icon">
                    <i class="fas fa-ruler-combined"></i>
                </div>
                <div class="stat-content">
                    <span class="stat-value">{{ number_format($stats['total_extension'], 2) }}</span>
                    <span class="stat-label">Hectáreas</span>
                </div>
                <div class="stat-decoration"></div>
            </div>
            
            <div class="stat-card stat-arboles">
                <div class="stat-icon">
                    <i class="fas fa-tree"></i>
                </div>
                <div class="stat-content">
                    <span class="stat-value">{{ $stats['total_arboles'] }}</span>
                    <span class="stat-label">Árboles</span>
                </div>
                <div class="stat-decoration"></div>
            </div>
            
            <div class="stat-card stat-trozas">
                <div class="stat-icon">
                    <i class="fas fa-grip-lines"></i>
                </div>
                <div class="stat-content">
                    <span class="stat-value">{{ $stats['total_trozas'] }}</span>
                    <span class="stat-label">Trozas</span>
                </div>
                <div class="stat-decoration"></div>
            </div>
            
            <div class="stat-card stat-volumen">
                <div class="stat-icon">
                    <i class="fas fa-cubes"></i>
                </div>
                <div class="stat-content">
                    <span class="stat-value">{{ number_format($stats['total_volumen'], 2) }}</span>
                    <span class="stat-label">Volumen (m³)</span>
                </div>
                <div class="stat-decoration"></div>
            </div>
            
            <div class="stat-card stat-carbono">
                <div class="stat-icon">
                    <i class="fas fa-cloud"></i>
                </div>
                <div class="stat-content">
                    <span class="stat-value">{{ number_format($stats['total_carbono'], 2) }}</span>
                    <span class="stat-label">Carbono (ton)</span>
                </div>
                <div class="stat-decoration"></div>
            </div>
        </div>
    </div>

    {{-- GRÁFICAS Y ANALYTICS --}}
    <div class="analytics-section">
        <div class="row g-4">
            {{-- Gráfica de Volumen Mensual --}}
            <div class="col-lg-8">
                <div class="chart-card">
                    <div class="chart-header">
                        <h3><i class="fas fa-chart-area me-2"></i>Volumen Mensual</h3>
                        <span class="chart-subtitle">Últimos 6 meses</span>
                    </div>
                    <div class="chart-body">
                        <canvas id="volumenChart" height="280"></canvas>
                    </div>
                </div>
            </div>
            
            {{-- Gráfica de Distribución de Especies --}}
            <div class="col-lg-4">
                <div class="chart-card">
                    <div class="chart-header">
                        <h3><i class="fas fa-seedling me-2"></i>Especies</h3>
                        <span class="chart-subtitle">Distribución por especie</span>
                    </div>
                    <div class="chart-body">
                        <canvas id="especiesChart" height="280"></canvas>
                    </div>
                </div>
            </div>
            
            {{-- Gráfica de Parcelas --}}
            <div class="col-lg-6">
                <div class="chart-card">
                    <div class="chart-header">
                        <h3><i class="fas fa-chart-bar me-2"></i>Extensión por Parcela</h3>
                    </div>
                    <div class="chart-body">
                        <canvas id="parcelasChart" height="220"></canvas>
                    </div>
                </div>
            </div>
            
            {{-- Gráfica de Árboles vs Trozas --}}
            <div class="col-lg-6">
                <div class="chart-card">
                    <div class="chart-header">
                        <h3><i class="fas fa-balance-scale me-2"></i>Árboles vs Trozas</h3>
                    </div>
                    <div class="chart-body">
                        <canvas id="comparativoChart" height="220"></canvas>
                    </div>
                </div>
            </div>
        </div>
    </div>

    {{-- SECCIÓN DE PARCELAS --}}
    <div class="parcelas-section">
        <div class="section-header">
            <div class="section-title">
                <i class="fas fa-layer-group"></i>
                <h2>Mis Parcelas</h2>
                <span class="badge-count">{{ $parcelas->count() }}</span>
            </div>
            <div class="section-actions">
                <div class="search-box">
                    <i class="fas fa-search"></i>
                    <input type="text" id="searchParcelas" placeholder="Buscar parcela...">
                </div>
                <button class="btn-nuevo-turno" data-bs-toggle="modal" data-bs-target="#nuevoTurnoModal">
                    <i class="fas fa-calendar-plus me-2"></i>Registrar Turno de Corta
                </button>
            </div>
        </div>

        <div class="parcelas-grid" id="parcelasContainer">
            @forelse($parcelas as $parcela)
                <div class="parcela-card" data-nombre="{{ strtolower($parcela->nom_parcela) }}">
                    <div class="parcela-header">
                        <div class="parcela-badge-container">
                            <span class="parcela-badge arboles">
                                <i class="fas fa-tree"></i> {{ $parcela->arboles_count }}
                            </span>
                            <span class="parcela-badge trozas">
                                <i class="fas fa-grip-lines"></i> {{ $parcela->trozas_count }}
                            </span>
                        </div>
                        <div class="parcela-bg" style="background: linear-gradient(135deg, #1a5f2a{{ str_pad(dechex($loop->index * 15 + 100), 2, '0', STR_PAD_LEFT) }}, #2d8a3e{{ str_pad(dechex($loop->index * 20 + 80), 2, '0', STR_PAD_LEFT) }});"></div>
                    </div>
                    
                    <div class="parcela-body">
                        <h3 class="parcela-title">{{ $parcela->nom_parcela }}</h3>
                        <p class="parcela-location">
                            <i class="fas fa-map-marker-alt"></i> {{ $parcela->ubicacion }}
                        </p>
                        
                        <div class="parcela-stats-mini">
                            <div class="stat-mini">
                                <span class="value">{{ $parcela->extension }}</span>
                                <span class="label">ha</span>
                            </div>
                            <div class="stat-mini">
                                <span class="value">{{ $parcela->turnosCorta->count() }}</span>
                                <span class="label">turnos</span>
                            </div>
                            @php
                                $volParcela = 0;
                                foreach($parcela->trozas as $t) {
                                    $v = $t->estimaciones->where('tipoEstimacion.desc_estimacion', 'Volumen')->first();
                                    if($v) $volParcela += $v->calculo;
                                }
                                foreach($parcela->arboles as $a) {
                                    $v = $a->estimaciones->where('tipoEstimacion.desc_estimacion', 'Volumen')->first();
                                    if($v) $volParcela += $v->calculo;
                                }
                            @endphp
                            <div class="stat-mini highlight">
                                <span class="value">{{ number_format($volParcela, 2) }}</span>
                                <span class="label">m³</span>
                            </div>
                        </div>
                        
                        @if($parcela->tecnicos->count() > 0)
                            <div class="parcela-tecnico">
                                <i class="fas fa-user-tie"></i>
                                <span>{{ $parcela->tecnicos->first()->persona->nom ?? 'Sin asignar' }}</span>
                            </div>
                        @endif
                    </div>
                    
                    <div class="parcela-footer">
                        <a href="{{ route('productor.parcela.detalle', $parcela->id_parcela) }}" class="btn-ver-detalle">
                            <i class="fas fa-eye"></i> Ver Detalle
                        </a>
                        <a href="{{ route('productor.parcela.pdf', $parcela->id_parcela) }}" class="btn-exportar-pdf">
                            <i class="fas fa-file-pdf"></i>
                        </a>
                    </div>
                </div>
            @empty
                <div class="empty-state">
                    <div class="empty-icon">
                        <i class="fas fa-seedling"></i>
                    </div>
                    <h3>No tienes parcelas registradas</h3>
                    <p>Comienza registrando tu primera parcela para gestionar tu producción forestal.</p>
                    <button class="btn-empty-action" data-bs-toggle="modal" data-bs-target="#nuevaParcelaModal">
                        <i class="fas fa-plus me-2"></i>Registrar Primera Parcela
                    </button>
                </div>
            @endforelse
        </div>
    </div>
</div>

{{-- MODAL: NUEVA PARCELA --}}
<div class="modal fade" id="nuevaParcelaModal" tabindex="-1">
    <div class="modal-dialog modal-dialog-centered modal-lg">
        <div class="modal-content producer-modal">
            <div class="modal-header">
                <div class="modal-header-content">
                    <i class="fas fa-map-marked-alt"></i>
                    <div>
                        <h5 class="modal-title">Registrar Nueva Parcela</h5>
                        <p class="modal-subtitle">Añade una nueva parcela a tu inventario forestal</p>
                    </div>
                </div>
                <button type="button" class="btn-close-modal" data-bs-dismiss="modal">
                    <i class="fas fa-times"></i>
                </button>
            </div>
            <form action="{{ route('productor.parcela.store') }}" method="POST">
                @csrf
                <div class="modal-body">
                    <div class="row g-4">
                        <div class="col-md-6">
                            <div class="form-floating-custom">
                                <input type="text" name="nom_parcela" class="form-control" placeholder=" " required>
                                <label><i class="fas fa-tag me-2"></i>Nombre de la Parcela</label>
                            </div>
                        </div>
                        <div class="col-md-6">
                            <div class="form-floating-custom">
                                <input type="number" step="0.01" name="extension" class="form-control" placeholder=" " required>
                                <label><i class="fas fa-ruler me-2"></i>Extensión (hectáreas)</label>
                            </div>
                        </div>
                        <div class="col-12">
                            <div class="form-floating-custom">
                                <input type="text" name="ubicacion" class="form-control" placeholder=" " required>
                                <label><i class="fas fa-map-marker-alt me-2"></i>Ubicación / Municipio</label>
                            </div>
                        </div>
                        <div class="col-md-8">
                            <div class="form-floating-custom">
                                <input type="text" name="direccion" class="form-control" placeholder=" ">
                                <label><i class="fas fa-road me-2"></i>Dirección (opcional)</label>
                            </div>
                        </div>
                        <div class="col-md-4">
                            <div class="form-floating-custom">
                                <input type="text" name="CP" class="form-control" placeholder=" " maxlength="10">
                                <label><i class="fas fa-mail-bulk me-2"></i>C.P.</label>
                            </div>
                        </div>
                    </div>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn-modal-cancel" data-bs-dismiss="modal">Cancelar</button>
                    <button type="submit" class="btn-modal-submit">
                        <i class="fas fa-check me-2"></i>Registrar Parcela
                    </button>
                </div>
            </form>
        </div>
    </div>
</div>

{{-- MODAL: NUEVO TURNO DE CORTA --}}
<div class="modal fade" id="nuevoTurnoModal" tabindex="-1">
    <div class="modal-dialog modal-dialog-centered">
        <div class="modal-content producer-modal">
            <div class="modal-header">
                <div class="modal-header-content">
                    <i class="fas fa-calendar-check"></i>
                    <div>
                        <h5 class="modal-title">Registrar Turno de Corta</h5>
                        <p class="modal-subtitle">Programa el turno de corta para una parcela</p>
                    </div>
                </div>
                <button type="button" class="btn-close-modal" data-bs-dismiss="modal">
                    <i class="fas fa-times"></i>
                </button>
            </div>
            <form action="{{ route('productor.turno.store') }}" method="POST">
                @csrf
                <div class="modal-body">
                    <div class="row g-4">
                        <div class="col-12">
                            <div class="form-floating-custom">
                                <select name="id_parcela" class="form-control" required>
                                    <option value="">-- Selecciona una parcela --</option>
                                    @foreach($parcelas as $parcela)
                                        <option value="{{ $parcela->id_parcela }}">{{ $parcela->nom_parcela }}</option>
                                    @endforeach
                                </select>
                                <label><i class="fas fa-map me-2"></i>Parcela</label>
                            </div>
                        </div>
                        <div class="col-12">
                            <div class="form-floating-custom">
                                <input type="text" name="codigo_corta" class="form-control" placeholder=" " required>
                                <label><i class="fas fa-barcode me-2"></i>Código de Corta</label>
                            </div>
                        </div>
                        <div class="col-md-6">
                            <div class="form-floating-custom">
                                <input type="date" name="fecha_corta" class="form-control" required>
                                <label><i class="fas fa-calendar me-2"></i>Fecha Inicio</label>
                            </div>
                        </div>
                        <div class="col-md-6">
                            <div class="form-floating-custom">
                                <input type="date" name="fecha_fin" class="form-control">
                                <label><i class="fas fa-calendar-check me-2"></i>Fecha Fin (opcional)</label>
                            </div>
                        </div>
                    </div>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn-modal-cancel" data-bs-dismiss="modal">Cancelar</button>
                    <button type="submit" class="btn-modal-submit">
                        <i class="fas fa-check me-2"></i>Registrar Turno
                    </button>
                </div>
            </form>
        </div>
    </div>
</div>

{{-- Alertas --}}
@if(session('success'))
    <div class="toast-notification success" id="toastSuccess">
        <i class="fas fa-check-circle"></i>
        <span>{{ session('success') }}</span>
    </div>
@endif
@if(session('error'))
    <div class="toast-notification error" id="toastError">
        <i class="fas fa-exclamation-circle"></i>
        <span>{{ session('error') }}</span>
    </div>
@endif
@endsection

@push('scripts')
<script src="https://cdn.jsdelivr.net/npm/chart.js"></script>
<script src="https://cdn.jsdelivr.net/npm/chartjs-plugin-datalabels@2"></script>
<script>
document.addEventListener('DOMContentLoaded', function() {
    // Datos de PHP
    const chartData = @json($chartData);
    const volumenMensual = @json($volumenMensual);
    const especiesData = @json($especiesData);

    // Paleta de colores premium
    const palette = {
        forest: ['#0d2818', '#15412b', '#1a5a3a', '#1f7349', '#22915a', '#2fb470', '#51c98a', '#86e3ad'],
        accent: ['#d4a853', '#e8c068', '#f0d590'],
        gradient: {
            green: 'rgba(34, 145, 90, 0.15)',
            greenLine: '#22915a',
            gold: 'rgba(212, 168, 83, 0.15)',
            goldLine: '#d4a853'
        }
    };
    
    // Configuración global de Chart.js
    Chart.defaults.font.family = "'Inter', sans-serif";
    Chart.defaults.font.weight = 500;
    Chart.defaults.color = '#71717a';
    Chart.defaults.plugins.tooltip.backgroundColor = '#18181b';
    Chart.defaults.plugins.tooltip.padding = 12;
    Chart.defaults.plugins.tooltip.cornerRadius = 8;
    Chart.defaults.plugins.tooltip.titleFont = { size: 13, weight: 700 };
    Chart.defaults.plugins.tooltip.bodyFont = { size: 12 };

    // Gráfica de Volumen Mensual - Area Chart con gradiente
    const volumenCtx = document.getElementById('volumenChart');
    if (volumenCtx) {
        const gradient = volumenCtx.getContext('2d').createLinearGradient(0, 0, 0, 280);
        gradient.addColorStop(0, 'rgba(47, 180, 112, 0.35)');
        gradient.addColorStop(0.5, 'rgba(47, 180, 112, 0.1)');
        gradient.addColorStop(1, 'rgba(47, 180, 112, 0)');

        new Chart(volumenCtx, {
            type: 'line',
            data: {
                labels: volumenMensual.labels,
                datasets: [{
                    label: 'Volumen (m³)',
                    data: volumenMensual.data,
                    fill: true,
                    backgroundColor: gradient,
                    borderColor: '#22915a',
                    borderWidth: 3,
                    tension: 0.4,
                    pointBackgroundColor: '#ffffff',
                    pointBorderColor: '#22915a',
                    pointBorderWidth: 3,
                    pointRadius: 6,
                    pointHoverRadius: 9,
                    pointHoverBorderWidth: 4,
                    pointHoverBackgroundColor: '#22915a',
                    pointHoverBorderColor: '#ffffff'
                }]
            },
            options: {
                responsive: true,
                maintainAspectRatio: false,
                interaction: {
                    intersect: false,
                    mode: 'index'
                },
                animation: {
                    duration: 1500,
                    easing: 'easeOutQuart'
                },
                plugins: {
                    legend: { display: false },
                    tooltip: {
                        callbacks: {
                            label: (ctx) => `Volumen: ${ctx.parsed.y.toFixed(2)} m³`
                        }
                    }
                },
                scales: {
                    y: {
                        beginAtZero: true,
                        grid: { color: 'rgba(0,0,0,0.04)', drawBorder: false },
                        ticks: { padding: 10 }
                    },
                    x: {
                        grid: { display: false },
                        ticks: { padding: 8 }
                    }
                }
            }
        });
    }

    // Gráfica de Especies - Doughnut elegante
    const especiesCtx = document.getElementById('especiesChart');
    if (especiesCtx && especiesData.labels.length > 0) {
        const speciesColors = ['#1a5a3a', '#22915a', '#51c98a', '#86e3ad', '#bbf0ce', '#d4a853'];
        
        new Chart(especiesCtx, {
            type: 'doughnut',
            data: {
                labels: especiesData.labels,
                datasets: [{
                    data: especiesData.data,
                    backgroundColor: speciesColors.slice(0, especiesData.labels.length),
                    borderWidth: 0,
                    hoverOffset: 8
                }]
            },
            options: {
                responsive: true,
                maintainAspectRatio: false,
                cutout: '72%',
                animation: {
                    animateRotate: true,
                    animateScale: true,
                    duration: 1200,
                    easing: 'easeOutQuart'
                },
                plugins: {
                    legend: {
                        position: 'bottom',
                        labels: {
                            boxWidth: 14,
                            boxHeight: 14,
                            padding: 16,
                            font: { size: 12, weight: 500 },
                            usePointStyle: true,
                            pointStyle: 'circle'
                        }
                    },
                    tooltip: {
                        callbacks: {
                            label: (ctx) => {
                                const total = ctx.dataset.data.reduce((a, b) => a + b, 0);
                                const percentage = ((ctx.parsed / total) * 100).toFixed(1);
                                return `${ctx.label}: ${ctx.parsed} (${percentage}%)`;
                            }
                        }
                    }
                }
            }
        });
    }

    // Gráfica de Parcelas - Barras horizontales
    const parcelasCtx = document.getElementById('parcelasChart');
    if (parcelasCtx && chartData.parcelas.length > 0) {
        new Chart(parcelasCtx, {
            type: 'bar',
            data: {
                labels: chartData.parcelas,
                datasets: [{
                    label: 'Hectáreas',
                    data: chartData.extensiones,
                    backgroundColor: (ctx) => {
                        const gradient = parcelasCtx.getContext('2d').createLinearGradient(0, 0, 300, 0);
                        gradient.addColorStop(0, '#1a5a3a');
                        gradient.addColorStop(1, '#22915a');
                        return gradient;
                    },
                    borderRadius: 8,
                    borderSkipped: false,
                    barThickness: 24
                }]
            },
            options: {
                indexAxis: 'y',
                responsive: true,
                maintainAspectRatio: false,
                animation: {
                    duration: 1200,
                    easing: 'easeOutQuart'
                },
                plugins: {
                    legend: { display: false },
                    tooltip: {
                        callbacks: {
                            label: (ctx) => `${ctx.parsed.x.toFixed(2)} ha`
                        }
                    }
                },
                scales: {
                    x: {
                        grid: { color: 'rgba(0,0,0,0.04)', drawBorder: false },
                        beginAtZero: true,
                        ticks: { padding: 8 }
                    },
                    y: {
                        grid: { display: false },
                        ticks: { font: { weight: 600 }, padding: 8 }
                    }
                }
            }
        });
    }

    // Gráfica Comparativa - Árboles vs Trozas
    const comparativoCtx = document.getElementById('comparativoChart');
    if (comparativoCtx && chartData.parcelas.length > 0) {
        new Chart(comparativoCtx, {
            type: 'bar',
            data: {
                labels: chartData.parcelas,
                datasets: [
                    {
                        label: 'Árboles',
                        data: chartData.arbolesPorParcela,
                        backgroundColor: '#1a5a3a',
                        borderRadius: 6,
                        borderSkipped: false
                    },
                    {
                        label: 'Trozas',
                        data: chartData.trozasPorParcela,
                        backgroundColor: '#d4a853',
                        borderRadius: 6,
                        borderSkipped: false
                    }
                ]
            },
            options: {
                responsive: true,
                maintainAspectRatio: false,
                animation: {
                    duration: 1000,
                    easing: 'easeOutQuart',
                    delay: (ctx) => ctx.dataIndex * 100
                },
                plugins: {
                    legend: {
                        position: 'bottom',
                        labels: {
                            boxWidth: 14,
                            boxHeight: 14,
                            padding: 20,
                            font: { size: 12, weight: 600 },
                            usePointStyle: true,
                            pointStyle: 'circle'
                        }
                    }
                },
                scales: {
                    x: {
                        grid: { display: false },
                        ticks: { font: { weight: 500 }, padding: 8 }
                    },
                    y: {
                        grid: { color: 'rgba(0,0,0,0.04)', drawBorder: false },
                        beginAtZero: true,
                        ticks: { padding: 8, precision: 0 }
                    }
                }
            }
        });
    }

    // Búsqueda de parcelas con animación
    const searchInput = document.getElementById('searchParcelas');
    const parcelasCards = document.querySelectorAll('.parcela-card');
    if (searchInput) {
        searchInput.addEventListener('input', function() {
            const term = this.value.toLowerCase().trim();
            parcelasCards.forEach((card, i) => {
                const matches = card.dataset.nombre.includes(term);
                card.style.transition = 'all 0.3s ease';
                card.style.opacity = matches ? '1' : '0';
                card.style.transform = matches ? 'scale(1)' : 'scale(0.95)';
                setTimeout(() => {
                    card.style.display = matches ? '' : 'none';
                }, matches ? 0 : 300);
            });
        });
    }

    // User Menu Dropdown
    const userMenuBtn = document.getElementById('userMenuBtn');
    const userDropdown = document.getElementById('userDropdown');
    if (userMenuBtn && userDropdown) {
        userMenuBtn.addEventListener('click', function(e) {
            e.stopPropagation();
            userDropdown.classList.toggle('show');
        });
        document.addEventListener('click', function(e) {
            if (!userMenuBtn.contains(e.target) && !userDropdown.contains(e.target)) {
                userDropdown.classList.remove('show');
            }
        });
    }

    // Toast notifications con animación
    document.querySelectorAll('.toast-notification').forEach((toast, i) => {
        setTimeout(() => {
            toast.classList.add('show');
            setTimeout(() => {
                toast.classList.remove('show');
            }, 4500);
        }, i * 200);
    });

    // Efecto de contador animado en las estadísticas
    document.querySelectorAll('.stat-value').forEach(el => {
        const target = parseFloat(el.textContent.replace(/,/g, ''));
        if (!isNaN(target) && target > 0) {
            const duration = 1500;
            const start = performance.now();
            const isDecimal = el.textContent.includes('.');

            el.textContent = '0';
            
            const animate = (now) => {
                const progress = Math.min((now - start) / duration, 1);
                const eased = 1 - Math.pow(1 - progress, 4);
                const current = target * eased;
                
                el.textContent = isDecimal 
                    ? current.toLocaleString('es-MX', { minimumFractionDigits: 2, maximumFractionDigits: 2 })
                    : Math.floor(current).toLocaleString('es-MX');
                
                if (progress < 1) requestAnimationFrame(animate);
                else el.textContent = target.toLocaleString('es-MX', isDecimal ? { minimumFractionDigits: 2, maximumFractionDigits: 2 } : {});
            };
            
            requestAnimationFrame(animate);
        }
    });
});
</script>
@endpush