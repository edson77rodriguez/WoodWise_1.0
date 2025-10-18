@extends('layouts.app') {{-- O tu layout principal, ej: 'dashboard' --}}

@section('template_title', 'Dashboard del Productor')

@push('styles')
    <link href="{{ asset('css/WW/productor-dashboard.css') }}" rel="stylesheet">
@endpush

@section('content')
<div class="dashboard-container">
    <div class="hero-section">
        <div class="hero-content">
            <h1 class="hero-title">
                <img src="{{ asset('img/woodwise.png') }}" class="hero-logo" alt="WoodWise Logo">
                Wood<span class="text-gradient">Wise</span>
            </h1>
            <p class="hero-subtitle">Bienvenido, Productor {{ Auth::user()->persona->nom }}. Aquí tienes el resumen de tu actividad.</p>
        </div>
        <a class="export-btn" href="{{ route('exportar.general') }}">
            <i class="fas fa-download me-2"></i> Reporte General
        </a>
    </div>

    <div class="stats-grid">
        <div class="stat-card">
            <div class="stat-icon-wrapper" style="--icon-color: var(--succulent-dark);"><i class="fas fa-draw-polygon"></i></div>
            <div class="stat-content">
                <span class="stat-label">Parcelas Registradas</span>
                <span class="stat-value">{{ $stats['total_parcelas'] }}</span>
            </div>
        </div>
        <div class="stat-card">
            <div class="stat-icon-wrapper" style="--icon-color: var(--succulent-medium);"><i class="fas fa-ruler-horizontal"></i></div>
            <div class="stat-content">
                <span class="stat-label">Trozas Registradas</span>
                <span class="stat-value">{{ $stats['total_trozas'] }}</span>
            </div>
        </div>
        <div class="stat-card">
            <div class="stat-icon-wrapper" style="--icon-color: var(--succulent-accent);"><i class="fas fa-chart-bar"></i></div>
            <div class="stat-content">
                <span class="stat-label">Estimaciones Realizadas</span>
                <span class="stat-value">{{ $stats['total_estimaciones'] }}</span>
            </div>
        </div>
    </div>

    <div class="section-header">
        <h2><i class="fas fa-layer-group"></i>Mis Parcelas</h2>
        <span class="badge">{{ count($parcelas) }} en total</span>
    </div>

    <div class="parcelas-container">
        @forelse($parcelas as $parcela)
            <div class="parcela-card" x-data="{ open: false }">
                <div class="parcela-header">
<div class="parcela-badge"><i class="fas fa-tree me-1"></i> {{ $parcela->arboles_count }} árboles</div>
                    <div class="parcela-hero" style="background-image: url('https://source.unsplash.com/random/600x400/?forest,{{ $loop->index }}')"></div>
                </div>

                <div class="parcela-body">
                    <h3 class="parcela-title">{{ $parcela->nom_parcela }}</h3>
                    <p class="parcela-location"><i class="fas fa-map-marker-alt"></i> {{ $parcela->ubicacion }}</p>

                    <div class="parcela-stats">
                        <div class="stat-item">
                            <span class="stat-number">{{ $parcela->extension }}</span>
                            <span class="stat-label">Hectáreas</span>
                        </div>
                        <div class="stat-item">
                            <span class="stat-number">{{ round($parcela->arboles->avg('altura_total') ?? 0, 2) }}</span>
                            <span class="stat-label">Altura Prom. (m)</span>
                        </div>
                        <div class="stat-item">
                            <span class="stat-number">{{ round($parcela->arboles->avg('diametro_pecho') ?? 0, 2) }}</span>
                            <span class="stat-label">DAP Prom. (cm)</span>
                        </div>
                    </div>
                </div>

                <div class="parcela-footer">
                    <button class="btn-details" @click="open = !open">
                        <i class="fas" :class="open ? 'fa-chevron-up' : 'fa-chevron-down'"></i>
                        <span x-text="open ? 'Ocultar Detalles' : 'Ver Detalles'"></span>
                    </button>
                    <a href="{{-- {{ route('parcela.pdf', $parcela->id_parcela) }} --}}" class="btn-export">
                        <i class="fas fa-file-pdf"></i> Exportar
                    </a>
                </div>

                <div class="parcela-details" x-show="open" x-collapse>
                    <div class="details-section">
                        <h4><i class="fas fa-info-circle"></i> Información General</h4>
                        <div class="info-grid">
                            <div class="info-item"><span class="info-label">Dirección</span><span class="info-value">{{ $parcela->direccion }}</span></div>
                            <div class="info-item"><span class="info-label">Código Postal</span><span class="info-value">{{ $parcela->CP }}</span></div>
                        </div>
                    </div>
                    <div class="details-section">
                        <h4><i class="fas fa-chart-line"></i> Estimaciones Recientes</h4>
                        @if($parcela->estimaciones->count() > 0)
                            <div class="details-table">
                                <div class="details-table-header">
                                    <div>ID</div><div>Tipo</div><div>Resultado</div>
                                </div>
                                @foreach($parcela->estimaciones->take(3) as $estimacion)
                                <div class="details-table-row">
                                    <div>#{{ $estimacion->id_estimacion }}</div>
                                    <div>{{ $estimacion->tipoEstimacion->desc_estimacion }}</div>
                                    <div class="fw-bold">{{ number_format($estimacion->calculo, 4) }} m³</div>
                                </div>
                                @endforeach
                            </div>
                        @else
                            <p class="text-muted small">No hay estimaciones para esta parcela.</p>
                        @endif
                    </div>
                </div>
            </div>
        @empty
            <div class="col-12 text-center py-5">
                <i class="fas fa-exclamation-circle fa-3x text-muted mb-3"></i>
                <h4 class="text-muted">Aún no tienes parcelas registradas.</h4>
            </div>
        @endforelse
    </div>
</div>
@endsection

@push('scripts')
    {{-- Alpine.js es necesario para la interactividad de las tarjetas --}}
    <script defer src="https://cdn.jsdelivr.net/npm/alpinejs@3.x.x/dist/cdn.min.js"></script>
        <script defer src="https://cdn.jsdelivr.net/npm/@alpinejs/collapse@3.x.x/dist/cdn.min.js"></script>
@endpush