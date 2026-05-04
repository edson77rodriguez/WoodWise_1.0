@extends('layouts.app')

@push('styles')
    <link href="{{ asset('css/WW/tecnico-dashboard.css') }}?v={{ filemtime(public_path('css/WW/tecnico-dashboard.css')) }}" rel="stylesheet">
    <style>
        /* ===== PARCELA DETALLE - PREMIUM ELEGANTE ===== */
        
        .parcela-hero {
            background: linear-gradient(135deg, #1a3a16 0%, #2d5a27 50%, #3d7a35 100%);
            border-radius: 24px;
            padding: 2.5rem;
            color: white;
            margin-bottom: 2.5rem;
            position: relative;
            overflow: visible;
            box-shadow: 
                0 30px 60px rgba(26, 58, 22, 0.25),
                0 0 0 1px rgba(255, 255, 255, 0.1),
                inset 0 1px 1px rgba(255, 255, 255, 0.15);
            backdrop-filter: blur(20px);
        }

        .parcela-hero::before {
            content: '';
            position: absolute;
            top: 0;
            left: 0;
            right: 0;
            bottom: 0;
            background: url('/images/forest-background.jpg') center/cover;
            opacity: 0.08;
            z-index: 0;
            border-radius: 24px;
            pointer-events: none;
        }

        .parcela-hero::after {
            content: '';
            position: absolute;
            bottom: 0;
            left: 0;
            right: 0;
            height: 3px;
            background: linear-gradient(90deg, rgba(76, 175, 80, 0.8), rgba(129, 199, 132, 0.8), rgba(76, 175, 80, 0.8));
            z-index: 1;
            border-radius: 0 0 24px 24px;
        }

        .parcela-hero > .d-flex {
            position: relative;
            z-index: 10;
            flex-direction: column;
            gap: 1.5rem;
        }

        .parcela-hero > .d-flex > div:first-child {
            flex: 1;
        }

        .parcela-hero h1 { 
            font-size: 2.8rem; 
            font-weight: 800; 
            margin-bottom: 0.8rem; 
            text-shadow: 0 4px 12px rgba(0, 0, 0, 0.3);
            position: relative;
            z-index: 5;
            color: #ffffff;
            letter-spacing: -0.5px;
            line-height: 1.2;
        }

        .parcela-hero .meta { 
            font-size: 1rem;
            font-weight: 600;
            position: relative;
            z-index: 5;
            color: rgba(255, 255, 255, 0.92);
            text-shadow: 0 2px 8px rgba(0, 0, 0, 0.2);
            line-height: 1.5;
            margin: 0;
        }
        
        /* Stats Cards - Compact & Elegant */
        .stat-card-mini {
            background: linear-gradient(180deg, rgba(255, 255, 255, 0.98), rgba(248, 252, 249, 0.97));
            border-radius: 18px;
            padding: 1.25rem 1.5rem;
            box-shadow: 
                0 8px 24px rgba(26, 58, 22, 0.1),
                inset 0 1px 1px rgba(255, 255, 255, 0.8);
            display: flex;
            align-items: center;
            gap: 1.25rem;
            transition: all 0.3s cubic-bezier(0.2, 0.9, 0.2, 1);
            border: 1px solid rgba(76, 175, 80, 0.15);
            min-height: auto;
            height: 100%;
        }

        .stat-card-mini:hover { 
            transform: translateY(-6px);
            box-shadow: 
                0 16px 36px rgba(26, 58, 22, 0.15),
                inset 0 1px 1px rgba(255, 255, 255, 0.8);
            border-color: rgba(76, 175, 80, 0.3);
        }

        .stat-card-mini .icon {
            width: 56px; 
            height: 56px; 
            border-radius: 14px;
            display: flex; 
            align-items: center; 
            justify-content: center;
            font-size: 1.6rem;
            flex-shrink: 0;
            box-shadow: 0 6px 12px rgba(0, 0, 0, 0.12);
        }

        .stat-card-mini .icon.volumen { 
            background: linear-gradient(135deg, #2d5a27, #1a3a16); 
            color: #76c776;
        }

        .stat-card-mini .icon.biomasa { 
            background: linear-gradient(135deg, #4caf50, #388e3c); 
            color: #ffffff;
        }

        .stat-card-mini .icon.carbono { 
            background: linear-gradient(135deg, #2196f3, #1565c0); 
            color: #ffffff;
        }

        .stat-card-mini .icon.count { 
            background: linear-gradient(135deg, #ff9800, #f57c00); 
            color: #ffffff;
        }

        .stat-card-mini .value { 
            font-size: 1.55rem; 
            font-weight: 800; 
            color: #1a3a16;
            line-height: 1.1;
            letter-spacing: -0.5px;
        }

        .stat-card-mini .label { 
            font-size: 0.8rem; 
            color: #64748b;
            font-weight: 600;
        }

        /* Stats Grid - Full Width */
        .stats-grid {
            display: grid;
            grid-template-columns: repeat(4, 1fr);
            gap: 1.25rem;
            width: 100%;
            margin-bottom: 2rem;
        }

        .stats-grid .stat-card-mini {
            flex-direction: column;
            padding: 1.5rem 1.25rem;
            text-align: center;
            align-items: center;
            justify-content: center;
            height: auto;
        }

        .stats-grid .stat-content {
            width: 100%;
        }

        .stats-grid .icon {
            margin-bottom: 0.75rem;
            margin-right: 0 !important;
        }
        
        /* Data Sections */
        .data-section {
            background: rgba(255, 255, 255, 0.97);
            backdrop-filter: blur(20px);
            border-radius: 20px;
            padding: 2rem;
            margin-bottom: 2rem;
            box-shadow: 
                0 10px 30px rgba(26, 58, 22, 0.1),
                inset 0 1px 1px rgba(255, 255, 255, 0.8);
            border: 1px solid rgba(76, 175, 80, 0.15);
            transition: all 0.3s ease;
        }

        .data-section:hover {
            border-color: rgba(76, 175, 80, 0.25);
            box-shadow: 
                0 15px 40px rgba(26, 58, 22, 0.15),
                inset 0 1px 1px rgba(255, 255, 255, 0.8);
        }

        .data-section h3 {
            color: #1a3a16;
            font-weight: 800;
            margin-bottom: 1.5rem;
            padding-bottom: 1.25rem;
            border-bottom: 2px solid rgba(76, 175, 80, 0.15);
            display: flex;
            align-items: center;
            gap: 1rem;
            font-size: 1.35rem;
            letter-spacing: -0.3px;
        }

        .data-section h3 i { 
            color: #2d5a27;
            font-size: 1.3rem;
        }

        .data-section h3 .badge {
            background: linear-gradient(135deg, #2d5a27, #1a3a16);
            color: white;
            font-size: 0.8rem;
            padding: 0.4em 0.85em;
            border-radius: 12px;
            font-weight: 700;
            margin-left: auto;
        }
        
        /* Tables */
        .data-table { 
            width: 100%; 
            border-collapse: collapse;
            table-layout: fixed;
        }

        .data-table th {
            background: linear-gradient(135deg, #f8faf8, #eef5ed);
            color: #1a3a16;
            font-weight: 800;
            padding: 1.25rem;
            text-align: left;
            border-bottom: 2px solid #2d5a27;
            font-size: 0.85rem;
            text-transform: uppercase;
            letter-spacing: 0.8px;
        }

        .data-table td {
            padding: 1rem 1.25rem;
            border-bottom: 1px solid rgba(76, 175, 80, 0.08);
            color: #333;
            font-weight: 500;
            word-break: break-word;
        }

        .data-table tbody tr {
            transition: all 0.2s ease;
        }

        .data-table tbody tr:hover { 
            background: linear-gradient(90deg, rgba(76, 175, 80, 0.04), rgba(76, 175, 80, 0.02));
        }

        .data-table tbody tr:last-child td { 
            border-bottom: 1px solid rgba(76, 175, 80, 0.15);
        }

        .data-table tfoot tr {
            background: linear-gradient(90deg, rgba(76, 175, 80, 0.08), rgba(76, 175, 80, 0.04));
            font-weight: 700;
            border-top: 2px solid rgba(76, 175, 80, 0.2);
        }

        .data-table tfoot td {
            padding: 1.25rem;
            color: #1a3a16;
            border-bottom: none;
        }
        
        /* Badges */
        .badge-tipo {
            padding: 0.5em 1em;
            border-radius: 25px;
            font-size: 0.8rem;
            font-weight: 800;
            text-transform: uppercase;
            letter-spacing: 0.5px;
            display: inline-block;
        }

        .badge-volumen { 
            background: linear-gradient(135deg, rgba(45, 90, 39, 0.15), rgba(45, 90, 39, 0.08)); 
            color: #2d5a27;
            border: 1px solid rgba(45, 90, 39, 0.25);
        }

        .badge-biomasa { 
            background: linear-gradient(135deg, rgba(76, 175, 80, 0.15), rgba(76, 175, 80, 0.08)); 
            color: #388e3c;
            border: 1px solid rgba(76, 175, 80, 0.25);
        }

        .badge-carbono { 
            background: linear-gradient(135deg, rgba(33, 150, 243, 0.15), rgba(33, 150, 243, 0.08)); 
            color: #1976d2;
            border: 1px solid rgba(33, 150, 243, 0.25);
        }
        
        /* Action Buttons */
        .action-buttons { 
            display: flex; 
            gap: 0.85rem;
            flex-wrap: wrap;
            position: relative; 
            z-index: 100;
            width: 100%;
        }

        .btn-action {
            padding: 1rem 1.75rem;
            border-radius: 14px;
            font-weight: 800;
            display: inline-flex;
            align-items: center;
            justify-content: center;
            gap: 0.7rem;
            transition: all 0.3s cubic-bezier(0.2, 0.9, 0.2, 1);
            text-decoration: none;
            font-size: 0.95rem;
            box-shadow: 0 10px 25px rgba(0, 0, 0, 0.25);
            border: none;
            cursor: pointer;
            position: relative;
            z-index: 100;
            pointer-events: auto;
            text-transform: uppercase;
            letter-spacing: 0.6px;
            flex: 1;
            min-width: 150px;
        }

        .btn-action:hover { 
            transform: translateY(-4px);
            box-shadow: 0 15px 40px rgba(0, 0, 0, 0.35);
        }

        .btn-action:active {
            transform: translateY(-1px);
        }

        .btn-back { 
            background: linear-gradient(135deg, #10b981, #059669);
            color: white;
            border: 1px solid rgba(255, 255, 255, 0.2);
        }

        .btn-back:hover { 
            background: linear-gradient(135deg, #059669, #047857);
            color: white;
            border-color: rgba(255, 255, 255, 0.3);
        }

        .btn-pdf { 
            background: linear-gradient(135deg, #3b82f6, #2563eb);
            color: white;
            border: 1px solid rgba(255, 255, 255, 0.2);
        }

        .btn-pdf:hover { 
            background: linear-gradient(135deg, #2563eb, #1d4ed8);
            color: white;
            border-color: rgba(255, 255, 255, 0.3);
        }

        /* Empty State */
        .empty-state {
            text-align: center;
            padding: 3rem;
            color: #666;
            background: linear-gradient(135deg, rgba(76, 175, 80, 0.05), rgba(76, 175, 80, 0.02));
            border-radius: 16px;
            border: 1px dashed rgba(76, 175, 80, 0.2);
        }

        .empty-state i { 
            font-size: 3.5rem; 
            margin-bottom: 1rem; 
            color: #2d5a27; 
            opacity: 0.4;
        }

        .empty-state p { 
            margin-bottom: 0; 
            font-weight: 600;
            font-size: 1.05rem;
        }

        /* Stats Grid - Full Width */
        .stats-grid {
            display: grid;
            grid-template-columns: repeat(4, 1fr);
            gap: 1.25rem;
            width: 100%;
        }

        .stats-grid .stat-card-mini {
            flex-direction: column;
            padding: 1.5rem 1.25rem;
            text-align: center;
            height: auto;
            align-items: center;
            justify-content: center;
        }

        .stats-grid .stat-content {
            width: 100%;
        }

        .stats-grid .icon {
            margin-bottom: 0.75rem;
        }

        /* ===== RESPONSIVE MOBILE-FIRST ===== */
        @media (max-width: 1024px) {
            .stats-grid {
                grid-template-columns: repeat(2, 1fr);
                gap: 1rem;
            }

            .stats-grid .stat-card-mini {
                padding: 1.3rem 1.15rem;
            }

            .parcela-hero {
                padding: 2rem;
            }

            .parcela-hero h1 {
                font-size: 2.2rem;
            }

            .parcela-hero .meta {
                font-size: 0.95rem;
            }

            .btn-action {
                padding: 0.9rem 1.5rem;
                font-size: 0.9rem;
            }

            .data-section {
                padding: 1.5rem;
            }

            .data-section h3 {
                font-size: 1.2rem;
            }

            .stat-card-mini .icon {
                width: 52px;
                height: 52px;
                font-size: 1.5rem;
            }

            .stat-card-mini .value {
                font-size: 1.45rem;
            }

            .stat-card-mini .label {
                font-size: 0.78rem;
            }
        }

        @media (max-width: 768px) {
            .stats-grid {
                grid-template-columns: 1fr;
                gap: 0.8rem;
                margin-bottom: 1.5rem;
            }

            .stats-grid .stat-card-mini {
                flex-direction: row;
                padding: 1.1rem 1rem;
                text-align: left;
                justify-content: flex-start;
            }

            .stats-grid .icon {
                margin-bottom: 0;
                margin-right: 0.8rem;
                flex-shrink: 0;
            }

            .stats-grid .stat-content {
                text-align: left;
            }

            .parcela-hero {
                padding: 1.5rem;
                margin-bottom: 2rem;
                border-radius: 18px;
            }

            .parcela-hero h1 {
                font-size: 1.65rem;
                margin-bottom: 0.6rem;
            }

            .parcela-hero .meta {
                font-size: 0.85rem;
            }

            .action-buttons {
                width: 100%;
                gap: 0.7rem;
            }

            .action-buttons .btn-action {
                flex: 1;
                padding: 0.85rem 1rem;
                font-size: 0.8rem;
                letter-spacing: 0.4px;
                min-width: auto;
            }

            .btn-action i {
                font-size: 0.9rem;
            }

            .data-section {
                padding: 1.25rem;
                margin-bottom: 1.5rem;
                border-radius: 16px;
            }

            .data-section h3 {
                font-size: 1.1rem;
                margin-bottom: 1rem;
            }

            /* Tablas responsive - convertir a tarjetas en móvil */
            .table-responsive {
                border-radius: 12px;
                overflow: visible;
            }

            .data-table {
                display: block;
                table-layout: auto;
            }

            .data-table thead {
                display: none;
            }

            .data-table tbody,
            .data-table tfoot {
                display: block;
                width: 100%;
            }

            .data-table tbody tr {
                display: flex;
                flex-direction: column;
                margin-bottom: 0.85rem;
                border: 1px solid rgba(76, 175, 80, 0.2);
                border-radius: 12px;
                background: linear-gradient(135deg, rgba(255, 255, 255, 0.99), rgba(248, 252, 249, 0.98));
                overflow: hidden;
                box-shadow: 0 4px 12px rgba(0, 0, 0, 0.05);
            }

            .data-table tbody tr:hover {
                background: linear-gradient(135deg, rgba(255, 255, 255, 0.99), rgba(248, 252, 249, 0.98));
                border-color: rgba(76, 175, 80, 0.35);
                box-shadow: 0 6px 16px rgba(0, 0, 0, 0.08);
                transform: translateY(-2px);
            }

            .data-table td {
                display: flex;
                flex-direction: column;
                padding: 0.65rem 0.9rem;
                border-bottom: 1px solid rgba(76, 175, 80, 0.08);
                align-items: stretch;
                gap: 0.35rem;
            }

            .data-table td:last-child {
                border-bottom: none;
            }

            .data-table td::before {
                content: attr(data-label);
                font-weight: 800;
                color: #2d5a27;
                font-size: 0.75rem;
                text-transform: uppercase;
                letter-spacing: 0.5px;
                line-height: 1.2;
            }

            .data-table tbody td {
                word-break: break-word;
                color: #333;
                font-size: 0.9rem;
            }

            /* Footers (totales) */
            .data-table tfoot {
                margin-top: 0.5rem;
            }

            .data-table tfoot tr {
                display: flex;
                flex-direction: column;
                background: linear-gradient(135deg, rgba(76, 175, 80, 0.12), rgba(76, 175, 80, 0.06));
                border: 1px solid rgba(76, 175, 80, 0.25);
                border-radius: 12px;
                margin-bottom: 0.5rem;
                overflow: hidden;
            }

            .data-table tfoot tr:last-child {
                margin-bottom: 0;
            }

            .data-table tfoot td {
                display: flex;
                flex-direction: column;
                padding: 0.65rem 0.9rem;
                border-bottom: 1px solid rgba(76, 175, 80, 0.1);
                gap: 0.35rem;
            }

            .data-table tfoot td:last-child {
                border-bottom: none;
            }

            .data-table tfoot td[data-label=""] {
                display: none;
            }

            .data-table tfoot td::before {
                content: attr(data-label);
                font-weight: 800;
                color: #2d5a27;
                font-size: 0.75rem;
                text-transform: uppercase;
                letter-spacing: 0.5px;
            }

            .badge-tipo {
                padding: 0.4em 0.75em;
                font-size: 0.75rem;
            }
        }

        @media (max-width: 576px) {
            .container-fluid {
                padding: 0.75rem;
            }

            .stats-grid {
                grid-template-columns: 1fr;
                gap: 0.65rem;
            }

            .stats-grid .stat-card-mini {
                padding: 0.9rem 0.95rem;
                flex-direction: row;
            }

            .stats-grid .icon {
                width: 44px;
                height: 44px;
                font-size: 1.05rem;
                margin-right: 0.75rem;
                margin-bottom: 0;
            }

            .stats-grid .value {
                font-size: 1.15rem;
            }

            .stats-grid .label {
                font-size: 0.7rem;
            }

            .data-section {
                padding: 0.9rem;
                margin-bottom: 1rem;
                border-radius: 14px;
            }

            .data-section h3 {
                font-size: 0.95rem;
                margin-bottom: 0.75rem;
                gap: 0.4rem;
                padding-bottom: 0.75rem;
            }

            .data-section h3 i {
                font-size: 1rem;
            }

            .data-section h3 .badge {
                margin-left: auto;
                padding: 0.3em 0.6em;
                font-size: 0.65rem;
            }

            /* Tablas ultra-compactas en mobile pequeño */
            .data-table {
                font-size: 0.85rem;
            }

            .data-table tbody tr {
                margin-bottom: 0.75rem;
                border-radius: 10px;
            }

            .data-table td {
                padding: 0.55rem 0.8rem;
                gap: 0.25rem;
            }

            .data-table td::before {
                font-size: 0.7rem;
                min-height: 16px;
            }

            .data-table tbody td {
                font-size: 0.85rem;
                color: #333;
            }

            .data-table tfoot tr {
                border-radius: 10px;
                margin-bottom: 0.4rem;
            }

            .data-table tfoot td {
                padding: 0.55rem 0.8rem;
                gap: 0.25rem;
            }

            .data-table tfoot td[data-label=""] {
                display: none;
            }

            .data-table tfoot td::before {
                font-size: 0.7rem;
                min-height: 16px;
            }

            .data-table tfoot td {
                font-size: 0.85rem;
                color: #2d5a27;
                font-weight: 700;
            }

            .badge-tipo {
                padding: 0.35em 0.6em;
                font-size: 0.7rem;
                white-space: nowrap;
            }

            .row {
                --bs-gutter-x: 0.5rem;
            }
        }

        @media (max-width: 400px) {
            .stats-grid {
                grid-template-columns: 1fr;
                gap: 0.55rem;
            }

            .stats-grid .stat-card-mini {
                padding: 0.8rem 0.85rem;
            }

            .stats-grid .icon {
                width: 40px;
                height: 40px;
                font-size: 0.95rem;
                margin-right: 0.6rem;
            }

            .stats-grid .value {
                font-size: 1.05rem;
            }

            .stats-grid .label {
                font-size: 0.65rem;
            }

            .data-section {
                padding: 0.8rem;
                margin-bottom: 0.85rem;
            }

            .data-section h3 {
                font-size: 0.9rem;
                margin-bottom: 0.6rem;
            }

            .data-table td {
                padding: 0.5rem 0.7rem;
            }

            .data-table td::before {
                font-size: 0.65rem;
            }

            .data-table tfoot td[data-label=""] {
                display: none;
            }

            .badge-tipo {
                padding: 0.3em 0.5em;
                font-size: 0.65rem;
            }
        }
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
                <a href="{{ route('tecnico.dashboard') }}" class="btn-action btn-back" title="Volver al dashboard">
                    <i class="fas fa-arrow-left"></i>
                    <span>VOLVER</span>
                </a>
                <a href="{{ route('tecnico.parcela.pdf', $parcela->id_parcela) }}" class="btn-action btn-pdf" title="Exportar PDF">
                    <i class="fas fa-file-pdf"></i>
                    <span>EXPORTAR</span>
                </a>
            </div>
        </div>
    </div>

    {{-- Estadísticas Resumen --}}
    <div class="stats-grid mb-4">
        <div class="stat-card-mini">
            <div class="icon volumen"><i class="fas fa-cubes"></i></div>
            <div class="stat-content">
                <div class="value">{{ number_format($totalVolumenTrozas + $totalVolumenArboles, 4) }}</div>
                <div class="label">Volumen Total (m³)</div>
            </div>
        </div>
        <div class="stat-card-mini">
            <div class="icon biomasa"><i class="fas fa-leaf"></i></div>
            <div class="stat-content">
                <div class="value">{{ number_format($totalBiomasaTrozas + $totalBiomasaArboles, 4) }}</div>
                <div class="label">Biomasa Total (ton)</div>
            </div>
        </div>
        <div class="stat-card-mini">
            <div class="icon carbono"><i class="fas fa-cloud"></i></div>
            <div class="stat-content">
                <div class="value">{{ number_format($totalCarbonoTrozas + $totalCarbonoArboles, 4) }}</div>
                <div class="label">Carbono Total (ton)</div>
            </div>
        </div>
        <div class="stat-card-mini">
            <div class="icon count"><i class="fas fa-list-ol"></i></div>
            <div class="stat-content">
                <div class="value">{{ $parcela->trozas->count() + $parcela->arboles->count() }}</div>
                <div class="label">Total Registros</div>
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
                                    <th>Long. (m)</th>
                                    <th>Diám. (m)</th>
                                    <th>Densidad (ton/m³)</th>
                                </tr>
                            </thead>
                            <tbody>
                                @foreach($parcela->trozas as $troza)
                                <tr>
                                    <td data-label="#"><strong>{{ $troza->id_troza }}</strong></td>
                                    <td data-label="Especie">{{ $troza->especie->nom_cientifico ?? 'N/A' }}</td>
                                    <td data-label="Long. (m)">{{ $troza->longitud }}m</td>
                                    <td data-label="Diám. (m)">{{ $troza->diametro }}m</td>
                                    <td data-label="Densidad">{{ $troza->densidad }} ton/m³</td>
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
                                    <td data-label="Troza">#{{ $est->id_troza }}</td>
                                    <td data-label="Fórmula">{{ $est->formula->nom_formula ?? 'N/A' }}</td>
                                    <td data-label="Volumen"><span class="badge-tipo badge-volumen">{{ number_format($est->calculo, 4) }} m³</span></td>
                                    <td data-label="Biomasa"><span class="badge-tipo badge-biomasa">{{ number_format($est->biomasa, 4) }} ton</span></td>
                                    <td data-label="Carbono"><span class="badge-tipo badge-carbono">{{ number_format($est->carbono, 4) }} ton</span></td>
                                </tr>
                                @endforeach
                            </tbody>
                            <tfoot>
                                <tr>
                                    <td data-label="Total">VOLUMEN TOTAL (m³)</td>
                                    <td data-label="">{{ number_format($totalVolumenTrozas, 4) }}</td>
                                    <td data-label="">-</td>
                                    <td data-label="">-</td>
                                    <td data-label="">-</td>
                                </tr>
                                <tr>
                                    <td data-label="Total">BIOMASA TOTAL (ton)</td>
                                    <td data-label="">{{ number_format($totalBiomasaTrozas, 4) }}</td>
                                    <td data-label="">-</td>
                                    <td data-label="">-</td>
                                    <td data-label="">-</td>
                                </tr>
                                <tr>
                                    <td data-label="Total">CARBONO TOTAL (ton)</td>
                                    <td data-label="">{{ number_format($totalCarbonoTrozas, 4) }}</td>
                                    <td data-label="">-</td>
                                    <td data-label="">-</td>
                                    <td data-label="">-</td>
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
                                    <td data-label="#"><strong>{{ $arbol->id_arbol }}</strong></td>
                                    <td data-label="Especie">{{ $arbol->especie->nom_cientifico ?? 'N/A' }}</td>
                                    <td data-label="Altura">{{ $arbol->altura_total }}m</td>
                                    <td data-label="DAP">{{ $arbol->diametro_pecho }}m</td>
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
                                    <th>Vol. Maderable Aprox. (m³)</th>
                                    <th>Biomasa (ton)</th>
                                    <th>Carbono (ton)</th>
                                </tr>
                            </thead>
                            <tbody>
                                @foreach($estimacionesArboles as $est)
                                <tr>
                                    <td data-label="Árbol">#{{ $est->id_arbol }}</td>
                                    <td data-label="Tipo">
                                        @if($est->tipoEstimacion->desc_estimacion == 'Volumen Maderable')
                                            <span class="badge-tipo badge-volumen">{{ $est->tipoEstimacion->desc_estimacion }}</span>
                                        @elseif($est->tipoEstimacion->desc_estimacion == 'Biomasa')
                                            <span class="badge-tipo badge-biomasa">{{ $est->tipoEstimacion->desc_estimacion }}</span>
                                        @else
                                            <span class="badge-tipo badge-carbono">{{ $est->tipoEstimacion->desc_estimacion }}</span>
                                        @endif
                                    </td>
                                    <td data-label="Vol. Maderable Aprox. (m³)">{{ number_format($est->calculo, 4) }} m³</td>
                                    <td data-label="Biomasa (ton)">{{ number_format($est->biomasa, 4) }}</td>
                                    <td data-label="Carbono (ton)">{{ number_format($est->carbono, 4) }}</td>
                                </tr>
                                @endforeach
                            </tbody>
                            <tfoot>
                                <tr>
                                    <td data-label="Total">VOLUMEN MADERABLE APROXIMADO (m³)</td>
                                    <td data-label="">{{ number_format($totalVolumenArboles, 4) }}</td>
                                    <td data-label="">-</td>
                                    <td data-label="">-</td>
                                    <td data-label="">-</td>
                                </tr>
                                <tr>
                                    <td data-label="Total">BIOMASA TOTAL (ton)</td>
                                    <td data-label="">{{ number_format($totalBiomasaArboles, 4) }}</td>
                                    <td data-label="">-</td>
                                    <td data-label="">-</td>
                                    <td data-label="">-</td>
                                </tr>
                                <tr>
                                    <td data-label="Total">CARBONO TOTAL (ton)</td>
                                    <td data-label="">{{ number_format($totalCarbonoArboles, 4) }}</td>
                                    <td data-label="">-</td>
                                    <td data-label="">-</td>
                                    <td data-label="">-</td>
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
