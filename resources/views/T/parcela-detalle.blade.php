@extends('layouts.app')

@push('styles')
    <link href="{{ asset('css/WW/tecnico-dashboard.css') }}" rel="stylesheet">
    <style>
        .parcela-hero {
            background: linear-gradient(135deg, rgba(26, 58, 22, 0.97) 0%, rgba(45, 90, 39, 0.97) 50%, rgba(61, 122, 53, 0.97) 100%) !important;
            border-radius: 20px;
            padding: 2.5rem;
            color: white !important;
            margin-bottom: 2rem;
            position: relative;
            overflow: visible !important;
            box-shadow: 0 10px 40px rgba(26, 58, 22, 0.4);
            backdrop-filter: blur(10px);
        }
        .parcela-hero::before {
            content: '';
            position: absolute;
            top: 0;
            left: 0;
            right: 0;
            bottom: 0;
            background: url('/images/forest-background.jpg') center/cover;
            opacity: 0.15;
            z-index: 0;
            border-radius: 20px;
            pointer-events: none;
        }
        .parcela-hero::after {
            content: '';
            position: absolute;
            bottom: 0;
            left: 0;
            right: 0;
            height: 4px;
            background: linear-gradient(90deg, #4caf50, #81c784, #4caf50);
            z-index: 1;
            pointer-events: none;
        }
        .parcela-hero h1 { 
            font-size: 2.5rem; 
            font-weight: 700; 
            margin-bottom: 0.75rem; 
            text-shadow: 2px 2px 8px rgba(0,0,0,0.6), 0 0 20px rgba(0,0,0,0.4);
            position: relative;
            z-index: 5;
            color: #ffffff !important;
        }
        .parcela-hero .meta { 
            opacity: 1; 
            font-size: 1.1rem;
            font-weight: 500;
            position: relative;
            z-index: 5;
            color: #ffffff !important;
            text-shadow: 1px 1px 4px rgba(0,0,0,0.5);
        }
        .parcela-hero > .d-flex {
            position: relative;
            z-index: 10;
        }
        
        .stat-card-mini {
            background: white;
            border-radius: 16px;
            padding: 1.5rem;
            box-shadow: 0 4px 20px rgba(0,0,0,0.08);
            display: flex;
            align-items: center;
            gap: 1.25rem;
            transition: all 0.3s ease;
            border: 1px solid rgba(45, 90, 39, 0.1);
        }
        .stat-card-mini:hover { 
            transform: translateY(-5px); 
            box-shadow: 0 12px 30px rgba(0,0,0,0.15);
            border-color: rgba(45, 90, 39, 0.2);
        }
        .stat-card-mini .icon {
            width: 60px; 
            height: 60px; 
            border-radius: 16px;
            display: flex; 
            align-items: center; 
            justify-content: center;
            font-size: 1.75rem;
            flex-shrink: 0;
        }
        .stat-card-mini .icon.volumen { 
            background: linear-gradient(135deg, rgba(45, 90, 39, 0.15), rgba(45, 90, 39, 0.25)); 
            color: #2d5a27; 
        }
        .stat-card-mini .icon.biomasa { 
            background: linear-gradient(135deg, rgba(76, 175, 80, 0.15), rgba(76, 175, 80, 0.25)); 
            color: #4caf50; 
        }
        .stat-card-mini .icon.carbono { 
            background: linear-gradient(135deg, rgba(33, 150, 243, 0.15), rgba(33, 150, 243, 0.25)); 
            color: #2196f3; 
        }
        .stat-card-mini .icon.count { 
            background: linear-gradient(135deg, rgba(255, 152, 0, 0.15), rgba(255, 152, 0, 0.25)); 
            color: #ff9800; 
        }
        .stat-card-mini .value { 
            font-size: 1.75rem; 
            font-weight: 700; 
            color: #1a3a16; 
            line-height: 1.2;
        }
        .stat-card-mini .label { 
            font-size: 0.9rem; 
            color: #555; 
            font-weight: 500;
        }
        
        .data-section {
            background: white;
            border-radius: 16px;
            padding: 1.75rem;
            margin-bottom: 1.5rem;
            box-shadow: 0 4px 20px rgba(0,0,0,0.08);
            border: 1px solid rgba(0,0,0,0.05);
        }
        .data-section h3 {
            color: #1a3a16;
            font-weight: 700;
            margin-bottom: 1.25rem;
            padding-bottom: 1rem;
            border-bottom: 2px solid #eef5ed;
            display: flex;
            align-items: center;
            gap: 0.75rem;
            font-size: 1.25rem;
        }
        .data-section h3 i { 
            color: #2d5a27;
            font-size: 1.1rem;
        }
        .data-section h3 .badge {
            background: linear-gradient(135deg, #2d5a27, #3d7a35);
            color: white;
            font-size: 0.85rem;
            padding: 0.35em 0.75em;
        }
        
        .data-table { width: 100%; border-collapse: collapse; }
        .data-table th {
            background: linear-gradient(135deg, #f8faf8, #eef5ed);
            color: #1a3a16;
            font-weight: 700;
            padding: 1rem;
            text-align: left;
            border-bottom: 2px solid #2d5a27;
            font-size: 0.9rem;
            text-transform: uppercase;
            letter-spacing: 0.5px;
        }
        .data-table td {
            padding: 0.85rem 1rem;
            border-bottom: 1px solid #f0f0f0;
            color: #333;
            font-weight: 500;
        }
        .data-table tr:hover { background: linear-gradient(90deg, #fafff9, #f5f9f4); }
        .data-table tr:last-child td { border-bottom: none; }
        
        .badge-tipo {
            padding: 0.4em 0.85em;
            border-radius: 25px;
            font-size: 0.75rem;
            font-weight: 700;
            text-transform: uppercase;
            letter-spacing: 0.5px;
        }
        .badge-volumen { background: rgba(45, 90, 39, 0.15); color: #2d5a27; }
        .badge-biomasa { background: rgba(76, 175, 80, 0.15); color: #388e3c; }
        .badge-carbono { background: rgba(33, 150, 243, 0.15); color: #1976d2; }
        
        .action-buttons { 
            display: flex; 
            gap: 1rem; 
            flex-wrap: wrap; 
            position: relative; 
            z-index: 100; 
        }
        .btn-action {
            padding: 0.75rem 1.5rem;
            border-radius: 12px;
            font-weight: 600;
            display: inline-flex;
            align-items: center;
            gap: 0.5rem;
            transition: all 0.3s ease;
            text-decoration: none !important;
            font-size: 0.95rem;
            box-shadow: 0 4px 15px rgba(0,0,0,0.25);
            border: none;
            cursor: pointer;
            position: relative;
            z-index: 100;
            pointer-events: auto !important;
        }
        .btn-action:hover { 
            transform: translateY(-2px); 
            box-shadow: 0 8px 25px rgba(0,0,0,0.3); 
        }
        .btn-pdf { 
            background: linear-gradient(135deg, #e74c3c 0%, #c0392b 100%) !important; 
            color: white !important; 
        }
        .btn-pdf:hover { 
            background: linear-gradient(135deg, #c0392b 0%, #a93226 100%) !important; 
            color: white !important; 
        }
        .btn-back { 
            background: linear-gradient(135deg, #667eea 0%, #764ba2 100%) !important; 
            color: white !important; 
        }
        .btn-back:hover { 
            background: linear-gradient(135deg, #5a6fd6 0%, #6a4190 100%) !important; 
            color: white !important; 
        }
        .btn-logout-detail {
            background: linear-gradient(135deg, #ef4444 0%, #dc2626 100%) !important;
            color: white !important;
        }
        .btn-logout-detail:hover {
            background: linear-gradient(135deg, #dc2626 0%, #b91c1c 100%) !important;
            color: white !important;
        }
        .btn-add { 
            background: linear-gradient(135deg, #2d5a27 0%, #1a3a16 100%); 
            color: white; 
        }
        .btn-add:hover { background: linear-gradient(135deg, #1a3a16 0%, #0f2610 100%); color: white; }
        
        .empty-state {
            text-align: center;
            padding: 3rem;
            color: #666;
            background: #f8f9fa;
            border-radius: 12px;
        }
        .empty-state i { font-size: 3rem; margin-bottom: 1rem; color: #2d5a27; opacity: 0.6; }
        .empty-state p { margin-bottom: 0; font-weight: 500; }
    </style>
@endpush

@section('content')
<div class="container-fluid py-4">
    {{-- Header de la Parcela --}}
    <div class="parcela-hero">
        <div class="d-flex justify-content-between align-items-start flex-wrap gap-3">
            <div>
                <h1><i class="fas fa-map-marked-alt me-2"></i>{{ $parcela->nom_parcela }}</h1>
                <p class="meta mb-0">
                    <i class="fas fa-location-dot me-1"></i> {{ $parcela->ubicacion }}
                    <span class="mx-2">|</span>
                    <i class="fas fa-ruler-combined me-1"></i> {{ $parcela->extension }} ha
                    <span class="mx-2">|</span>
                    <i class="fas fa-user me-1"></i> {{ $parcela->productor->persona->nom ?? 'N/A' }} {{ $parcela->productor->persona->ap ?? '' }}
                </p>
            </div>
            <div class="action-buttons">
                <a href="{{ route('tecnico.dashboard') }}" class="btn-action btn-back">
                    <i class="fas fa-arrow-left"></i> Volver
                </a>
                <a href="{{ route('tecnico.parcela.pdf', $parcela->id_parcela) }}" class="btn-action btn-pdf">
                    <i class="fas fa-file-pdf"></i> Exportar PDF
                </a>
            </div>
        </div>
    </div>

    {{-- Estadísticas Resumen --}}
    <div class="row g-4 mb-4">
        <div class="col-md-3 col-sm-6">
            <div class="stat-card-mini">
                <div class="icon volumen"><i class="fas fa-cubes"></i></div>
                <div>
                    <div class="value">{{ number_format($totalVolumenTrozas + $totalVolumenArboles, 4) }}</div>
                    <div class="label">Volumen Total (m³)</div>
                </div>
            </div>
        </div>
        <div class="col-md-3 col-sm-6">
            <div class="stat-card-mini">
                <div class="icon biomasa"><i class="fas fa-leaf"></i></div>
                <div>
                    <div class="value">{{ number_format($totalBiomasaTrozas + $totalBiomasaArboles, 4) }}</div>
                    <div class="label">Biomasa Total (ton)</div>
                </div>
            </div>
        </div>
        <div class="col-md-3 col-sm-6">
            <div class="stat-card-mini">
                <div class="icon carbono"><i class="fas fa-cloud"></i></div>
                <div>
                    <div class="value">{{ number_format($totalCarbonoTrozas + $totalCarbonoArboles, 4) }}</div>
                    <div class="label">Carbono Total (ton)</div>
                </div>
            </div>
        </div>
        <div class="col-md-3 col-sm-6">
            <div class="stat-card-mini">
                <div class="icon count"><i class="fas fa-list-ol"></i></div>
                <div>
                    <div class="value">
                        {{ $parcela->trozas->count() + $parcela->arboles->count() }}
                    </div>
                    <div class="label">Total Registros</div>
                </div>
            </div>
        </div>
    </div>

    <div class="row">
        {{-- Columna Izquierda: Trozas --}}
        <div class="col-lg-6">
            <div class="data-section">
                <h3>
                    <i class="fas fa-cut"></i> Trozas Registradas
                    <span class="badge bg-secondary ms-2">{{ $parcela->trozas->count() }}</span>
                </h3>
                
                @if($parcela->trozas->count() > 0)
                    <div class="table-responsive">
                        <table class="data-table">
                            <thead>
                                <tr>
                                    <th>#</th>
                                    <th>Especie</th>
                                    <th>Long.</th>
                                    <th>Diám.</th>
                                    <th>Densidad</th>
                                </tr>
                            </thead>
                            <tbody>
                                @foreach($parcela->trozas as $troza)
                                <tr>
                                    <td><strong>{{ $troza->id_troza }}</strong></td>
                                    <td>{{ $troza->especie->nom_cientifico ?? 'N/A' }}</td>
                                    <td>{{ $troza->longitud }}m</td>
                                    <td>{{ $troza->diametro }}m</td>
                                    <td>{{ $troza->densidad }} ton/m³</td>
                                </tr>
                                @endforeach
                            </tbody>
                        </table>
                    </div>
                @else
                    <div class="empty-state">
                        <i class="fas fa-cut"></i>
                        <p>No hay trozas registradas en esta parcela</p>
                    </div>
                @endif
            </div>

            {{-- Estimaciones de Trozas --}}
            <div class="data-section">
                <h3>
                    <i class="fas fa-calculator"></i> Estimaciones de Trozas
                </h3>
                
                @php
                    $estimacionesTrozas = $parcela->trozas->flatMap->estimaciones;
                @endphp
                
                @if($estimacionesTrozas->count() > 0)
                    <div class="table-responsive">
                        <table class="data-table">
                            <thead>
                                <tr>
                                    <th>Troza</th>
                                    <th>Fórmula</th>
                                    <th>Volumen</th>
                                    <th>Biomasa</th>
                                    <th>Carbono</th>
                                </tr>
                            </thead>
                            <tbody>
                                @foreach($estimacionesTrozas as $est)
                                <tr>
                                    <td>#{{ $est->id_troza }}</td>
                                    <td>{{ $est->formula->nom_formula ?? 'N/A' }}</td>
                                    <td><span class="badge-tipo badge-volumen">{{ number_format($est->calculo, 4) }} m³</span></td>
                                    <td><span class="badge-tipo badge-biomasa">{{ number_format($est->biomasa, 4) }} ton</span></td>
                                    <td><span class="badge-tipo badge-carbono">{{ number_format($est->carbono, 4) }} ton</span></td>
                                </tr>
                                @endforeach
                            </tbody>
                            <tfoot>
                                <tr style="background: #f8faf8; font-weight: 600;">
                                    <td colspan="2">TOTALES</td>
                                    <td>{{ number_format($totalVolumenTrozas, 4) }} m³</td>
                                    <td>{{ number_format($totalBiomasaTrozas, 4) }} ton</td>
                                    <td>{{ number_format($totalCarbonoTrozas, 4) }} ton</td>
                                </tr>
                            </tfoot>
                        </table>
                    </div>
                @else
                    <div class="empty-state">
                        <i class="fas fa-calculator"></i>
                        <p>No hay estimaciones de trozas aún</p>
                    </div>
                @endif
            </div>
        </div>

        {{-- Columna Derecha: Árboles --}}
        <div class="col-lg-6">
            <div class="data-section">
                <h3>
                    <i class="fas fa-tree"></i> Árboles Registrados
                    <span class="badge bg-success ms-2">{{ $parcela->arboles->count() }}</span>
                </h3>
                
                @if($parcela->arboles->count() > 0)
                    <div class="table-responsive">
                        <table class="data-table">
                            <thead>
                                <tr>
                                    <th>#</th>
                                    <th>Especie</th>
                                    <th>Altura</th>
                                    <th>DAP</th>
                                </tr>
                            </thead>
                            <tbody>
                                @foreach($parcela->arboles as $arbol)
                                <tr>
                                    <td><strong>{{ $arbol->id_arbol }}</strong></td>
                                    <td>{{ $arbol->especie->nom_cientifico ?? 'N/A' }}</td>
                                    <td>{{ $arbol->altura_total }}m</td>
                                    <td>{{ $arbol->diametro_pecho }}m</td>
                                </tr>
                                @endforeach
                            </tbody>
                        </table>
                    </div>
                @else
                    <div class="empty-state">
                        <i class="fas fa-tree"></i>
                        <p>No hay árboles registrados en esta parcela</p>
                    </div>
                @endif
            </div>

            {{-- Estimaciones de Árboles --}}
            <div class="data-section">
                <h3>
                    <i class="fas fa-chart-line"></i> Estimaciones de Árboles
                </h3>
                
                @php
                    $estimacionesArboles = $parcela->arboles->flatMap->estimaciones1;
                @endphp
                
                @if($estimacionesArboles->count() > 0)
                    <div class="table-responsive">
                        <table class="data-table">
                            <thead>
                                <tr>
                                    <th>Árbol</th>
                                    <th>Tipo</th>
                                    <th>Cálculo</th>
                                    <th>Biomasa</th>
                                    <th>Carbono</th>
                                </tr>
                            </thead>
                            <tbody>
                                @foreach($estimacionesArboles as $est)
                                <tr>
                                    <td>#{{ $est->id_arbol }}</td>
                                    <td>
                                        @if($est->tipoEstimacion->desc_estimacion == 'Volumen Maderable')
                                            <span class="badge-tipo badge-volumen">{{ $est->tipoEstimacion->desc_estimacion }}</span>
                                        @elseif($est->tipoEstimacion->desc_estimacion == 'Biomasa')
                                            <span class="badge-tipo badge-biomasa">{{ $est->tipoEstimacion->desc_estimacion }}</span>
                                        @else
                                            <span class="badge-tipo badge-carbono">{{ $est->tipoEstimacion->desc_estimacion }}</span>
                                        @endif
                                    </td>
                                    <td>{{ number_format($est->calculo, 4) }}</td>
                                    <td>{{ number_format($est->biomasa, 4) }} ton</td>
                                    <td>{{ number_format($est->carbono, 4) }} ton</td>
                                </tr>
                                @endforeach
                            </tbody>
                            <tfoot>
                                <tr style="background: #f8faf8; font-weight: 600;">
                                    <td colspan="2">TOTALES</td>
                                    <td>{{ number_format($totalVolumenArboles, 4) }}</td>
                                    <td>{{ number_format($totalBiomasaArboles, 4) }} ton</td>
                                    <td>{{ number_format($totalCarbonoArboles, 4) }} ton</td>
                                </tr>
                            </tfoot>
                        </table>
                    </div>
                @else
                    <div class="empty-state">
                        <i class="fas fa-chart-line"></i>
                        <p>No hay estimaciones de árboles aún</p>
                    </div>
                @endif
            </div>
        </div>
    </div>

    {{-- Información General de la Parcela --}}
    <div class="row">
        <div class="col-12">
            <div class="data-section">
                <h3><i class="fas fa-info-circle"></i> Información General</h3>
                <div class="row">
                    <div class="col-md-4">
                        <p><strong>Dirección:</strong> {{ $parcela->direccion ?? 'No especificada' }}</p>
                        <p><strong>Código Postal:</strong> {{ $parcela->CP ?? 'N/A' }}</p>
                    </div>
                    <div class="col-md-4">
                        <p><strong>Extensión:</strong> {{ $parcela->extension }} hectáreas</p>
                        <p><strong>Ubicación:</strong> {{ $parcela->ubicacion }}</p>
                    </div>
                    <div class="col-md-4">
                        <p><strong>Técnico Responsable:</strong> {{ $tecnico->persona->nom ?? 'N/A' }} {{ $tecnico->persona->ap ?? '' }}</p>
                        <p><strong>Clave Técnico:</strong> {{ $tecnico->clave_tecnico ?? 'N/A' }}</p>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>
@endsection

@push('scripts')
<script>
    document.addEventListener('DOMContentLoaded', function() {
        var tooltipTriggerList = [].slice.call(document.querySelectorAll('[data-bs-toggle="tooltip"]'));
        tooltipTriggerList.map(function(el) { return new bootstrap.Tooltip(el); });
    });
</script>
@endpush
