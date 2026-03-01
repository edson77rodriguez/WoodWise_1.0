<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <meta name="csrf-token" content="{{ csrf_token() }}">
    <title>{{ $parcela->nom_parcela }} - WoodWise</title>
    
    <!-- Fonts -->
    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@300;400;500;600;700;800&display=swap" rel="stylesheet">
    
    <!-- Bootstrap 5 -->
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    
    <!-- Font Awesome -->
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
    
    <!-- Custom Styles -->
    <link rel="stylesheet" href="{{ asset('css/WW/productor-dashboard-v2.css') }}">
    
    <style>
        /* ===== PARCELA DETAIL - PREMIUM STYLES ===== */
        
        /* Hero Section */
        .detail-hero {
            background: linear-gradient(135deg, var(--forest-900) 0%, var(--forest-700) 40%, var(--forest-600) 100%);
            border-radius: var(--radius-2xl);
            padding: 2.5rem 3rem;
            margin-bottom: 2rem;
            color: white;
            position: relative;
            overflow: hidden;
            box-shadow: var(--shadow-2xl), var(--shadow-glow-forest);
        }
        
        .detail-hero::before {
            content: '';
            position: absolute;
            top: -50%;
            right: -15%;
            width: 50%;
            height: 200%;
            background: radial-gradient(circle, rgba(212, 168, 83, 0.1) 0%, transparent 60%);
            pointer-events: none;
        }
        
        .detail-hero::after {
            content: '';
            position: absolute;
            bottom: -30%;
            left: -10%;
            width: 40%;
            height: 150%;
            background: radial-gradient(circle, rgba(47, 180, 112, 0.08) 0%, transparent 50%);
            pointer-events: none;
        }
        
        .breadcrumb-nav {
            display: flex;
            align-items: center;
            gap: 0.6rem;
            margin-bottom: 1.5rem;
            font-size: 0.9rem;
            position: relative;
            z-index: 2;
        }
        
        .breadcrumb-nav a {
            color: rgba(255,255,255,0.65);
            text-decoration: none;
            transition: var(--transition);
        }
        
        .breadcrumb-nav a:hover {
            color: var(--gold-400);
        }
        
        .breadcrumb-nav span {
            color: rgba(255,255,255,0.4);
        }
        
        .breadcrumb-nav .current {
            color: var(--gold-400);
            font-weight: 600;
        }
        
        .detail-hero-content {
            display: flex;
            justify-content: space-between;
            align-items: flex-start;
            gap: 2rem;
            position: relative;
            z-index: 2;
        }
        
        .parcela-icon-lg {
            width: 85px;
            height: 85px;
            background: linear-gradient(135deg, rgba(255,255,255,0.15), rgba(255,255,255,0.05));
            border-radius: var(--radius-xl);
            display: flex;
            align-items: center;
            justify-content: center;
            font-size: 2.2rem;
            backdrop-filter: blur(10px);
            border: 2px solid rgba(255,255,255,0.2);
            flex-shrink: 0;
            color: var(--gold-400);
        }
        
        .parcela-main-info h1 {
            font-size: 2.2rem;
            font-weight: 800;
            margin-bottom: 0.4rem;
            letter-spacing: -0.02em;
            background: linear-gradient(135deg, #ffffff, var(--gold-300));
            -webkit-background-clip: text;
            -webkit-text-fill-color: transparent;
            background-clip: text;
        }
        
        .parcela-meta {
            display: flex;
            flex-wrap: wrap;
            gap: 1.5rem;
            margin-top: 1rem;
        }
        
        .meta-item {
            display: flex;
            align-items: center;
            gap: 0.5rem;
            font-size: 0.9rem;
            color: rgba(255,255,255,0.85);
            background: rgba(255,255,255,0.08);
            padding: 0.5rem 1rem;
            border-radius: var(--radius-full);
            border: 1px solid rgba(255,255,255,0.12);
        }
        
        .meta-item i {
            color: var(--forest-300);
        }
        
        .hero-actions-detail {
            display: flex;
            gap: 0.75rem;
            flex-shrink: 0;
        }
        
        .hero-btn {
            display: inline-flex;
            align-items: center;
            gap: 0.5rem;
            padding: 0.9rem 1.5rem;
            border-radius: var(--radius-full);
            font-weight: 600;
            font-size: 0.9rem;
            text-decoration: none;
            transition: var(--transition);
            cursor: pointer;
            border: none;
        }
        
        .hero-btn-primary {
            background: var(--white);
            color: var(--forest-700);
            box-shadow: 0 4px 20px rgba(255,255,255,0.25);
        }
        
        .hero-btn-primary:hover {
            transform: translateY(-3px);
            box-shadow: 0 8px 30px rgba(255,255,255,0.35);
            color: var(--forest-700);
        }
        
        /* Stats Grid */
        .detail-stats {
            display: grid;
            grid-template-columns: repeat(5, 1fr);
            gap: 1.25rem;
            margin-bottom: 2rem;
        }
        
        @media (max-width: 1200px) {
            .detail-stats { grid-template-columns: repeat(3, 1fr); }
        }
        
        @media (max-width: 768px) {
            .detail-stats { grid-template-columns: repeat(2, 1fr); }
        }
        
        .detail-stat-card {
            background: var(--white);
            border-radius: var(--radius-xl);
            padding: 1.75rem 1.5rem;
            box-shadow: var(--shadow-md);
            text-align: center;
            transition: var(--transition);
            border: 1px solid var(--gray-100);
            position: relative;
            overflow: hidden;
        }
        
        .detail-stat-card::before {
            content: '';
            position: absolute;
            top: 0;
            left: 0;
            right: 0;
            height: 4px;
        }
        
        .detail-stat-card:nth-child(1)::before { background: linear-gradient(90deg, var(--forest-600), var(--forest-400)); }
        .detail-stat-card:nth-child(2)::before { background: linear-gradient(90deg, #6366f1, #818cf8); }
        .detail-stat-card:nth-child(3)::before { background: linear-gradient(90deg, var(--gold-500), var(--gold-400)); }
        .detail-stat-card:nth-child(4)::before { background: linear-gradient(90deg, #10b981, #34d399); }
        .detail-stat-card:nth-child(5)::before { background: linear-gradient(90deg, #8b5cf6, #a78bfa); }
        
        .detail-stat-card:hover {
            transform: translateY(-6px);
            box-shadow: var(--shadow-xl);
        }
        
        .detail-stat-icon {
            width: 56px;
            height: 56px;
            margin: 0 auto 1rem;
            border-radius: var(--radius-lg);
            display: flex;
            align-items: center;
            justify-content: center;
            font-size: 1.4rem;
        }
        
        .detail-stat-card:nth-child(1) .detail-stat-icon { background: var(--forest-50); color: var(--forest-600); }
        .detail-stat-card:nth-child(2) .detail-stat-icon { background: #eef2ff; color: #6366f1; }
        .detail-stat-card:nth-child(3) .detail-stat-icon { background: var(--gold-100); color: var(--gold-600); }
        .detail-stat-card:nth-child(4) .detail-stat-icon { background: #d1fae5; color: #059669; }
        .detail-stat-card:nth-child(5) .detail-stat-icon { background: #ede9fe; color: #7c3aed; }
        
        .detail-stat-value {
            font-size: 2rem;
            font-weight: 800;
            color: var(--gray-900);
            margin-bottom: 0.3rem;
            letter-spacing: -0.02em;
        }
        
        .detail-stat-label {
            font-size: 0.8rem;
            color: var(--gray-500);
            text-transform: uppercase;
            letter-spacing: 0.05em;
            font-weight: 600;
        }
        
        /* Content Grid */
        .detail-content {
            display: grid;
            grid-template-columns: 1fr 380px;
            gap: 1.5rem;
        }
        
        @media (max-width: 1024px) {
            .detail-content { grid-template-columns: 1fr; }
        }
        
        /* Data Cards */
        .data-card {
            background: var(--white);
            border-radius: var(--radius-xl);
            box-shadow: var(--shadow-md);
            overflow: hidden;
            border: 1px solid var(--gray-100);
        }
        
        .data-card-header {
            padding: 1.25rem 1.5rem;
            border-bottom: 1px solid var(--gray-200);
            display: flex;
            justify-content: space-between;
            align-items: center;
            background: linear-gradient(135deg, var(--gray-50), var(--white));
        }
        
        .data-card-title {
            font-size: 1.1rem;
            font-weight: 700;
            color: var(--gray-900);
            display: flex;
            align-items: center;
            gap: 0.6rem;
        }
        
        .data-card-title i {
            color: var(--forest-500);
            width: 32px;
            height: 32px;
            background: var(--forest-50);
            border-radius: var(--radius-md);
            display: flex;
            align-items: center;
            justify-content: center;
            font-size: 0.9rem;
        }
        
        .data-card-count {
            background: var(--gray-100);
            color: var(--gray-600);
            padding: 0.3rem 0.8rem;
            border-radius: var(--radius-full);
            font-size: 0.8rem;
            font-weight: 600;
        }
        
        .data-card-body {
            padding: 0;
        }
        
        /* Data Table */
        .data-table {
            width: 100%;
            border-collapse: collapse;
        }
        
        .data-table th,
        .data-table td {
            padding: 1rem 1.5rem;
            text-align: left;
            border-bottom: 1px solid var(--gray-100);
        }
        
        .data-table th {
            background: var(--gray-50);
            font-size: 0.75rem;
            font-weight: 700;
            color: var(--gray-500);
            text-transform: uppercase;
            letter-spacing: 0.05em;
        }
        
        .data-table tr:last-child td {
            border-bottom: none;
        }
        
        .data-table tr:hover td {
            background: var(--forest-50);
        }
        
        .data-table .id-badge {
            font-weight: 700;
            color: var(--forest-700);
        }
        
        .data-table .especie-badge {
            display: inline-flex;
            align-items: center;
            gap: 0.4rem;
            padding: 0.4rem 0.85rem;
            background: linear-gradient(135deg, var(--forest-100), var(--forest-50));
            color: var(--forest-700);
            border-radius: var(--radius-full);
            font-size: 0.8rem;
            font-weight: 600;
            border: 1px solid var(--forest-200);
        }
        
        .data-table .value-cell {
            font-weight: 600;
            color: var(--gray-800);
        }
        
        /* Sidebar Info */
        .info-sidebar {
            display: flex;
            flex-direction: column;
            gap: 1.5rem;
        }
        
        .info-card {
            background: var(--white);
            border-radius: var(--radius-xl);
            box-shadow: var(--shadow-md);
            overflow: hidden;
            border: 1px solid var(--gray-100);
        }
        
        .info-card-header {
            padding: 1rem 1.25rem;
            border-bottom: 1px solid var(--gray-200);
            font-weight: 700;
            color: var(--gray-900);
            display: flex;
            align-items: center;
            gap: 0.6rem;
            background: linear-gradient(135deg, var(--gray-50), var(--white));
        }
        
        .info-card-header i {
            color: var(--forest-500);
            width: 28px;
            height: 28px;
            background: var(--forest-50);
            border-radius: var(--radius-sm);
            display: flex;
            align-items: center;
            justify-content: center;
            font-size: 0.8rem;
        }
        
        .info-card-body {
            padding: 1.25rem;
        }
        
        .info-row {
            display: flex;
            justify-content: space-between;
            padding: 0.9rem 0;
            border-bottom: 1px solid var(--gray-100);
        }
        
        .info-row:last-child {
            border-bottom: none;
        }
        
        .info-label {
            color: var(--gray-500);
            font-size: 0.9rem;
        }
        
        .info-value {
            font-weight: 700;
            color: var(--gray-900);
            text-align: right;
        }
        
        /* Turno Timeline */
        .turno-timeline {
            padding: 0;
            margin: 0;
            list-style: none;
        }
        
        .turno-item {
            position: relative;
            padding: 1.1rem 1.25rem 1.1rem 3rem;
            border-bottom: 1px solid var(--gray-100);
            transition: var(--transition);
        }
        
        .turno-item:hover {
            background: var(--forest-50);
        }
        
        .turno-item:last-child {
            border-bottom: none;
        }
        
        .turno-item::before {
            content: '';
            position: absolute;
            left: 1.25rem;
            top: 1.5rem;
            width: 12px;
            height: 12px;
            background: linear-gradient(135deg, var(--gold-500), var(--gold-400));
            border-radius: 50%;
            box-shadow: 0 0 0 4px var(--gold-100);
        }
        
        .turno-item::after {
            content: '';
            position: absolute;
            left: 1.45rem;
            top: 2.2rem;
            width: 2px;
            height: calc(100% - 0.8rem);
            background: var(--gray-200);
        }
        
        .turno-item:last-child::after {
            display: none;
        }
        
        .turno-codigo {
            font-weight: 700;
            color: var(--gray-900);
            margin-bottom: 0.25rem;
            font-size: 0.95rem;
        }
        
        .turno-fechas {
            font-size: 0.85rem;
            color: var(--gray-500);
        }
        
        /* Empty State */
        .empty-small {
            text-align: center;
            padding: 2.5rem;
            color: var(--gray-500);
        }
        
        .empty-small i {
            font-size: 2.5rem;
            color: var(--gray-300);
            margin-bottom: 1rem;
            display: block;
        }
        
        /* Back Link */
        .back-link {
            display: inline-flex;
            align-items: center;
            gap: 0.6rem;
            color: var(--gray-600);
            text-decoration: none;
            font-weight: 600;
            margin-bottom: 1.5rem;
            padding: 0.6rem 1.2rem;
            border-radius: var(--radius-full);
            transition: var(--transition);
            background: var(--white);
            box-shadow: var(--shadow-sm);
            border: 1px solid var(--gray-200);
        }
        
        .back-link:hover {
            color: var(--forest-600);
            border-color: var(--forest-200);
            background: var(--forest-50);
            transform: translateX(-4px);
        }
        
        /* Animations */
        .detail-stat-card {
            animation: fadeInUp 0.5s ease both;
        }
        .detail-stat-card:nth-child(1) { animation-delay: 0.05s; }
        .detail-stat-card:nth-child(2) { animation-delay: 0.1s; }
        .detail-stat-card:nth-child(3) { animation-delay: 0.15s; }
        .detail-stat-card:nth-child(4) { animation-delay: 0.2s; }
        .detail-stat-card:nth-child(5) { animation-delay: 0.25s; }
        
        .data-card, .info-card {
            animation: fadeInUp 0.6s ease both;
            animation-delay: 0.3s;
        }
        
        @keyframes fadeInUp {
            from { opacity: 0; transform: translateY(20px); }
            to { opacity: 1; transform: translateY(0); }
        }
    </style>
</head>
<body>
    <div class="producer-dashboard">
        <!-- Back Link -->
        <a href="{{ route('productor.dashboard') }}" class="back-link">
            <i class="fas fa-arrow-left"></i>
            Volver al Dashboard
        </a>
        
        <!-- Detail Hero -->
        <div class="detail-hero">
            <nav class="breadcrumb-nav">
                <a href="{{ route('productor.dashboard') }}">Dashboard</a>
                <span>/</span>
                <a href="{{ route('productor.dashboard') }}">Mis Parcelas</a>
                <span>/</span>
                <span class="current">{{ $parcela->nom_parcela }}</span>
            </nav>
            
            <div class="detail-hero-content">
                <div style="display: flex; gap: 1.5rem; align-items: flex-start;">
                    <div class="parcela-icon-lg">
                        <i class="fas fa-mountain-sun"></i>
                    </div>
                    <div class="parcela-main-info">
                        <h1>{{ $parcela->nom_parcela }}</h1>
                        <p style="opacity: 0.8; margin: 0;">{{ $parcela->ubicacion ?? 'Sin ubicación definida' }}</p>
                        
                        <div class="parcela-meta">
                            <div class="meta-item">
                                <i class="fas fa-ruler-combined"></i>
                                {{ number_format($parcela->extension, 2) }} hectáreas
                            </div>
                            @if($parcela->direccion)
                            <div class="meta-item">
                                <i class="fas fa-map-marker-alt"></i>
                                {{ $parcela->direccion }}
                            </div>
                            @endif
                            @if($parcela->CP)
                            <div class="meta-item">
                                <i class="fas fa-envelope"></i>
                                C.P. {{ $parcela->CP }}
                            </div>
                            @endif
                        </div>
                    </div>
                </div>
                
                <div class="hero-actions-detail">
                    <a href="{{ route('productor.parcela.pdf', $parcela->id_parcela) }}" class="hero-btn hero-btn-primary">
                        <i class="fas fa-file-pdf"></i>
                        Exportar PDF
                    </a>
                </div>
            </div>
        </div>
        
        <!-- Stats Row -->
        <div class="detail-stats">
            <div class="detail-stat-card">
                <div class="detail-stat-icon">
                    <i class="fas fa-tree"></i>
                </div>
                <div class="detail-stat-value">{{ number_format($stats['arboles']) }}</div>
                <div class="detail-stat-label">Árboles</div>
            </div>
            
            <div class="detail-stat-card">
                <div class="detail-stat-icon">
                    <i class="fas fa-circle-notch"></i>
                </div>
                <div class="detail-stat-value">{{ number_format($stats['trozas']) }}</div>
                <div class="detail-stat-label">Trozas</div>
            </div>
            
            <div class="detail-stat-card">
                <div class="detail-stat-icon">
                    <i class="fas fa-cube"></i>
                </div>
                <div class="detail-stat-value">{{ number_format($stats['volumen'], 2) }}</div>
                <div class="detail-stat-label">Volumen (m³)</div>
            </div>
            
            <div class="detail-stat-card">
                <div class="detail-stat-icon">
                    <i class="fas fa-weight-hanging"></i>
                </div>
                <div class="detail-stat-value">{{ number_format($stats['biomasa'], 2) }}</div>
                <div class="detail-stat-label">Biomasa (ton)</div>
            </div>
            
            <div class="detail-stat-card">
                <div class="detail-stat-icon">
                    <i class="fas fa-leaf"></i>
                </div>
                <div class="detail-stat-value">{{ number_format($stats['carbono'], 2) }}</div>
                <div class="detail-stat-label">Carbono (ton)</div>
            </div>
        </div>
        
        <!-- Content Grid -->
        <div class="detail-content">
            <!-- Main Content: Árboles y Trozas -->
            <div class="main-content">
                <!-- Árboles Table -->
                <div class="data-card" style="margin-bottom: 1.5rem;">
                    <div class="data-card-header">
                        <h3 class="data-card-title">
                            <i class="fas fa-tree"></i>
                            Árboles Registrados
                        </h3>
                        <span class="chart-badge">{{ count($arboles) }} registros</span>
                    </div>
                    <div class="data-card-body">
                        @if(count($arboles) > 0)
                        <table class="data-table">
                            <thead>
                                <tr>
                                    <th>ID</th>
                                    <th>Especie</th>
                                    <th>Altura (m)</th>
                                    <th>DAP (cm)</th>
                                    <th>Estimaciones</th>
                                </tr>
                            </thead>
                            <tbody>
                                @foreach($arboles as $arbol)
                                <tr>
                                    <td><strong>#{{ $arbol->id_arbol }}</strong></td>
                                    <td>
                                        <span class="especie-badge">
                                            <i class="fas fa-seedling"></i>
                                            {{ $arbol->especie->nom_comun ?? 'N/A' }}
                                        </span>
                                    </td>
                                    <td>{{ number_format($arbol->altura_total ?? 0, 2) }}</td>
                                    <td>{{ number_format($arbol->diametro_pecho ?? 0, 2) }}</td>
                                    <td>{{ $arbol->estimaciones->count() }}</td>
                                </tr>
                                @endforeach
                            </tbody>
                        </table>
                        @else
                        <div class="empty-small">
                            <i class="fas fa-tree"></i>
                            <p>No hay árboles registrados en esta parcela</p>
                        </div>
                        @endif
                    </div>
                </div>
                
                <!-- Trozas Table -->
                <div class="data-card">
                    <div class="data-card-header">
                        <h3 class="data-card-title">
                            <i class="fas fa-circle-notch"></i>
                            Trozas Registradas
                        </h3>
                        <span class="chart-badge">{{ count($trozas) }} registros</span>
                    </div>
                    <div class="data-card-body">
                        @if(count($trozas) > 0)
                        <table class="data-table">
                            <thead>
                                <tr>
                                    <th>ID</th>
                                    <th>Especie</th>
                                    <th>Longitud (m)</th>
                                    <th>Diám. 1 (cm)</th>
                                    <th>Diám. 2 (cm)</th>
                                </tr>
                            </thead>
                            <tbody>
                                @foreach($trozas as $troza)
                                <tr>
                                    <td><strong>#{{ $troza->id_troza }}</strong></td>
                                    <td>
                                        <span class="especie-badge">
                                            <i class="fas fa-seedling"></i>
                                            {{ $troza->especie->nom_comun ?? 'N/A' }}
                                        </span>
                                    </td>
                                    <td>{{ number_format($troza->longitud ?? 0, 2) }}</td>
                                    <td>{{ number_format($troza->diametro ?? 0, 2) }}</td>
                                    <td>{{ number_format($troza->diametro_otro_extremo ?? 0, 2) }}</td>
                                </tr>
                                @endforeach
                            </tbody>
                        </table>
                        @else
                        <div class="empty-small">
                            <i class="fas fa-circle-notch"></i>
                            <p>No hay trozas registradas en esta parcela</p>
                        </div>
                        @endif
                    </div>
                </div>
            </div>
            
            <!-- Sidebar -->
            <div class="info-sidebar">
                <!-- Información General -->
                <div class="info-card">
                    <div class="info-card-header">
                        <i class="fas fa-info-circle"></i>
                        Información General
                    </div>
                    <div class="info-card-body">
                        <div class="info-row">
                            <span class="info-label">Extensión</span>
                            <span class="info-value">{{ number_format($parcela->extension, 2) }} ha</span>
                        </div>
                        <div class="info-row">
                            <span class="info-label">Ubicación</span>
                            <span class="info-value">{{ $parcela->ubicacion ?? 'N/A' }}</span>
                        </div>
                        <div class="info-row">
                            <span class="info-label">Dirección</span>
                            <span class="info-value">{{ $parcela->direccion ?? 'N/A' }}</span>
                        </div>
                        <div class="info-row">
                            <span class="info-label">Código Postal</span>
                            <span class="info-value">{{ $parcela->CP ?? 'N/A' }}</span>
                        </div>
                        <div class="info-row">
                            <span class="info-label">Especies</span>
                            <span class="info-value">{{ $stats['especies'] }}</span>
                        </div>
                    </div>
                </div>
                
                <!-- Turnos de Corta -->
                <div class="info-card">
                    <div class="info-card-header">
                        <i class="fas fa-calendar-alt"></i>
                        Turnos de Corta
                    </div>
                    @if(count($turnos) > 0)
                    <ul class="turno-timeline">
                        @foreach($turnos as $turno)
                        <li class="turno-item">
                            <div class="turno-codigo">{{ $turno->codigo_corta }}</div>
                            <div class="turno-fechas">
                                <i class="far fa-calendar"></i>
                                {{ \Carbon\Carbon::parse($turno->fecha_corta)->format('d/m/Y') }}
                                @if($turno->fecha_fin)
                                - {{ \Carbon\Carbon::parse($turno->fecha_fin)->format('d/m/Y') }}
                                @endif
                            </div>
                        </li>
                        @endforeach
                    </ul>
                    @else
                    <div class="info-card-body">
                        <div class="empty-small">
                            <i class="fas fa-calendar-times"></i>
                            <p>Sin turnos de corta</p>
                        </div>
                    </div>
                    @endif
                </div>
                
                <!-- Resumen Estimaciones -->
                <div class="info-card">
                    <div class="info-card-header">
                        <i class="fas fa-chart-pie"></i>
                        Resumen de Estimaciones
                    </div>
                    <div class="info-card-body">
                        <div class="info-row">
                            <span class="info-label">Volumen Total</span>
                            <span class="info-value">{{ number_format($stats['volumen'], 2) }} m³</span>
                        </div>
                        <div class="info-row">
                            <span class="info-label">Biomasa Total</span>
                            <span class="info-value">{{ number_format($stats['biomasa'], 2) }} ton</span>
                        </div>
                        <div class="info-row">
                            <span class="info-label">Carbono Capturado</span>
                            <span class="info-value">{{ number_format($stats['carbono'], 2) }} ton</span>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
    
    <!-- Bootstrap JS -->
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
</body>
</html>
