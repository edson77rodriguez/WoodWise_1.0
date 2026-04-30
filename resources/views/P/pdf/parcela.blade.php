<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <title>Reporte Parcela - {{ $parcela->nom_parcela }}</title>
    <style>
        @page {
            margin: 40px 35px;
        }
        
        * {
            margin: 0;
            padding: 0;
            box-sizing: border-box;
        }
        
        body {
            font-family: 'Segoe UI', Tahoma, Geneva, Verdana, sans-serif;
            font-size: 11px;
            color: #333;
            line-height: 1.5;
            background: #fff;
        }
        
        /* Header */
        .header {
            border-bottom: 3px solid #2d5a3d;
            padding-bottom: 15px;
            margin-bottom: 20px;
        }
        
        .header-content {
            width: 100%;
        }
        
        .header table {
            width: 100%;
        }
        
        .logo {
            height: 50px;
        }
        
        .header-title {
            text-align: center;
        }
        
        .header-title h1 {
            color: #2d5a3d;
            font-size: 20px;
            margin: 0;
            letter-spacing: 1px;
        }
        
        .header-title p {
            color: #666;
            font-size: 10px;
            margin-top: 3px;
        }
        
        .header-date {
            text-align: right;
            color: #888;
            font-size: 9px;
        }
        
        /* Parcela Info Box */
        .parcela-box {
            background: #e8f3eb;
            border-left: 4px solid #2d5a3d;
            padding: 15px;
            margin-bottom: 20px;
        }
        
        .parcela-name {
            font-size: 16px;
            font-weight: bold;
            color: #1a3a2a;
            margin-bottom: 8px;
        }
        
        .parcela-details {
            color: #555;
        }
        
        .parcela-details span {
            margin-right: 20px;
        }
        
        /* Stats Grid */
        .stats-grid {
            width: 100%;
            margin-bottom: 20px;
        }
        
        .stats-grid table {
            width: 100%;
            border-collapse: collapse;
        }
        
        .stat-box {
            background: #f9f9f9;
            border: 1px solid #e0e0e0;
            padding: 12px;
            text-align: center;
            width: 20%;
        }
        
        .stat-value {
            font-size: 18px;
            font-weight: bold;
            color: #2d5a3d;
        }
        
        .stat-label {
            font-size: 9px;
            color: #777;
            text-transform: uppercase;
            letter-spacing: 0.5px;
        }
        
        /* Section Titles */
        .section-title {
            background: #2d5a3d;
            color: #fff;
            padding: 8px 12px;
            font-size: 12px;
            font-weight: bold;
            margin-bottom: 0;
        }
        
        /* Tables */
        .data-table {
            width: 100%;
            border-collapse: collapse;
            margin-bottom: 20px;
        }
        
        .data-table th {
            background: #f0f4f1;
            color: #333;
            padding: 8px 10px;
            text-align: left;
            font-size: 9px;
            text-transform: uppercase;
            letter-spacing: 0.5px;
            border-bottom: 2px solid #2d5a3d;
        }
        
        .data-table td {
            padding: 8px 10px;
            border-bottom: 1px solid #eee;
        }
        
        .data-table tr:nth-child(even) {
            background: #fafafa;
        }
        
        .badge {
            display: inline-block;
            padding: 2px 8px;
            background: #e8f3eb;
            color: #2d5a3d;
            border-radius: 10px;
            font-size: 9px;
            font-weight: bold;
        }
        
        /* Turnos Section */
        .turnos-list {
            margin-bottom: 20px;
        }
        
        .turno-item {
            padding: 10px 12px;
            border-bottom: 1px solid #eee;
            background: #f9f9f9;
        }
        
        .turno-code {
            font-weight: bold;
            color: #2d5a3d;
        }
        
        .turno-dates {
            color: #666;
            font-size: 10px;
        }
        
        /* Footer */
        .footer {
            position: fixed;
            bottom: 20px;
            left: 35px;
            right: 35px;
            border-top: 1px solid #ddd;
            padding-top: 10px;
            font-size: 8px;
            color: #888;
        }
        
        .footer table {
            width: 100%;
        }
        
        /* Empty State */
        .empty-msg {
            text-align: center;
            padding: 20px;
            color: #888;
            font-style: italic;
        }
        
        /* Summary Box */
        .summary-box {
            background: #1a3a2a;
            color: #fff;
            padding: 15px;
            margin-top: 20px;
        }
        
        .summary-box h3 {
            font-size: 12px;
            margin-bottom: 10px;
        }
        
        .summary-box table {
            width: 100%;
        }
        
        .summary-box td {
            padding: 5px 0;
        }
        
        .summary-label {
            color: #a8bba8;
        }
        
        .summary-value {
            text-align: right;
            font-weight: bold;
        }
    </style>
</head>
<body>
    <!-- Header -->
    <div class="header">
        <table>
            <tr>
                <td style="width: 80px;">
                    @if($logo)
                        <img src="{{ $logo }}" class="logo" alt="SIGMAD">
                    @endif
                </td>
                <td class="header-title">
                    <h1>REPORTE DE PARCELA</h1>
                    <p>Sistema Inteligente de Gestión Maderable</p>
                </td>
                <td class="header-date" style="width: 120px;">
                    Generado: {{ $fecha }}
                </td>
            </tr>
        </table>
    </div>
    
    <!-- Parcela Info -->
    <div class="parcela-box">
        <div class="parcela-name">{{ $parcela->nom_parcela }}</div>
        <div class="parcela-details">
            <span><strong>Ubicación:</strong> {{ $parcela->ubicacion ?? 'N/A' }}</span>
            <span><strong>Extensión:</strong> {{ number_format($parcela->extension, 2) }} ha</span>
            @if($parcela->direccion)
            <span><strong>Dirección:</strong> {{ $parcela->direccion }}</span>
            @endif
            @if($parcela->CP)
            <span><strong>C.P.:</strong> {{ $parcela->CP }}</span>
            @endif
        </div>
        <div class="parcela-details" style="margin-top: 5px;">
            <span><strong>Productor:</strong> {{ $parcela->productor->persona->nombre ?? '' }} {{ $parcela->productor->persona->ap_paterno ?? '' }} {{ $parcela->productor->persona->ap_materno ?? '' }}</span>
        </div>
    </div>
    
    <!-- Stats -->
    @php
        $totalArboles = $parcela->arboles->count();
        $totalTrozas = $parcela->trozas->count();
        
        $totalVolumen = 0;
        $totalBiomasa = 0;
        $totalCarbono = 0;
        
        foreach($parcela->arboles as $arbol) {
            foreach($arbol->estimaciones as $est) {
                $tipo = $est->tipoEstimacion->desc_estimacion ?? '';
                if(stripos($tipo, 'volumen') !== false) $totalVolumen += $est->calculo;
                if(stripos($tipo, 'biomasa') !== false) $totalBiomasa += $est->calculo;
                if(stripos($tipo, 'carbono') !== false) $totalCarbono += $est->calculo;
            }
        }
        
        foreach($parcela->trozas as $troza) {
            foreach($troza->estimaciones as $est) {
                $tipo = $est->tipoEstimacion->desc_estimacion ?? '';
                if(stripos($tipo, 'volumen') !== false) $totalVolumen += $est->calculo;
            }
        }
    @endphp
    
    <div class="stats-grid">
        <table>
            <tr>
                <td class="stat-box">
                    <div class="stat-value">{{ number_format($totalArboles) }}</div>
                    <div class="stat-label">Árboles</div>
                </td>
                <td class="stat-box">
                    <div class="stat-value">{{ number_format($totalTrozas) }}</div>
                    <div class="stat-label">Trozas</div>
                </td>
                <td class="stat-box">
                    <div class="stat-value">{{ number_format($totalVolumen, 2) }}</div>
                    <div class="stat-label">Volumen (m³)</div>
                </td>
                <td class="stat-box">
                    <div class="stat-value">{{ number_format($totalBiomasa, 2) }}</div>
                    <div class="stat-label">Biomasa (ton)</div>
                </td>
                <td class="stat-box">
                    <div class="stat-value">{{ number_format($totalCarbono, 2) }}</div>
                    <div class="stat-label">Carbono (ton)</div>
                </td>
            </tr>
        </table>
    </div>
    
    <!-- Árboles -->
    <h3 class="section-title">Árboles Registrados</h3>
    @if($parcela->arboles->count() > 0)
    <table class="data-table">
        <thead>
            <tr>
                <th style="width: 60px;">No.</th>
                <th>Especie</th>
                <th style="width: 80px;">Altura (m)</th>
                <th style="width: 80px;">DAP (m)</th>
                <th style="width: 100px;">Volumen (m³)</th>
            </tr>
        </thead>
        <tbody>
            @foreach($parcela->arboles as $arbol)
            @php
                $volArbol = 0;
                foreach($arbol->estimaciones as $est) {
                    if(stripos($est->tipoEstimacion->desc_estimacion ?? '', 'volumen') !== false) {
                        $volArbol = $est->calculo;
                        break;
                    }
                }
            @endphp
            <tr>
                <td><strong>#{{ $arbol->id_arbol }}</strong></td>
                <td><span class="badge">{{ $arbol->especie->nom_comun ?? 'N/A' }}</span></td>
                <td>{{ number_format($arbol->altura_total ?? 0, 2) }}</td>
                <td>{{ number_format($arbol->diametro_pecho ?? 0, 2) }}</td>
                <td>{{ number_format($volArbol, 4) }}</td>
            </tr>
            @endforeach
        </tbody>
    </table>
    @else
    <p class="empty-msg">No hay árboles registrados en esta parcela</p>
    @endif
    
    <!-- Trozas -->
    <h3 class="section-title">Trozas Registradas</h3>
    @if($parcela->trozas->count() > 0)
    <table class="data-table">
        <thead>
            <tr>
                <th style="width: 60px;">No.</th>
                <th>Especie</th>
                <th style="width: 80px;">Longitud (m)</th>
                <th style="width: 80px;">D. Mayor</th>
                <th style="width: 80px;">D. Menor</th>
                <th style="width: 100px;">Volumen (m³)</th>
            </tr>
        </thead>
        <tbody>
            @foreach($parcela->trozas as $troza)
            @php
                $volTroza = 0;
                foreach($troza->estimaciones as $est) {
                    if(stripos($est->tipoEstimacion->desc_estimacion ?? '', 'volumen') !== false) {
                        $volTroza = $est->calculo;
                        break;
                    }
                }
            @endphp
            <tr>
                <td><strong>#{{ $troza->id_troza }}</strong></td>
                <td><span class="badge">{{ $troza->especie->nom_comun ?? 'N/A' }}</span></td>
                <td>{{ number_format($troza->longitud ?? 0, 2) }} m</td>
                <td>{{ number_format($troza->diametro ?? 0, 2) }} m</td>
                <td>{{ number_format($troza->diametro_otro_extremo ?? 0, 2) }} m</td>
                <td>{{ number_format($volTroza, 4) }} m³</td>
            </tr>
            @endforeach
        </tbody>
    </table>
    @else
    <p class="empty-msg">No hay trozas registradas en esta parcela</p>
    @endif
    
    <!-- Turnos de Corta -->
    @if($parcela->turnosCorta && $parcela->turnosCorta->count() > 0)
    <h3 class="section-title">Turnos de Corta</h3>
    <div class="turnos-list">
        @foreach($parcela->turnosCorta as $turno)
        <div class="turno-item">
            <span class="turno-code">{{ $turno->codigo_corta }}</span><br>
            <span class="turno-dates">
                Inicio: {{ \Carbon\Carbon::parse($turno->fecha_corta)->format('d/m/Y') }}
                @if($turno->fecha_fin)
                &nbsp;|&nbsp; Fin: {{ \Carbon\Carbon::parse($turno->fecha_fin)->format('d/m/Y') }}
                @endif
            </span>
        </div>
        @endforeach
    </div>
    @endif
    
    <!-- Summary -->
    <div class="summary-box">
        <h3>Resumen de Estimaciones</h3>
        <table>
            <tr>
                <td class="summary-label">Volumen Total Estimado</td>
                <td class="summary-value">{{ number_format($totalVolumen, 2) }} m³</td>
            </tr>
            <tr>
                <td class="summary-label">Biomasa Total Estimada</td>
                <td class="summary-value">{{ number_format($totalBiomasa, 2) }} toneladas</td>
            </tr>
            <tr>
                <td class="summary-label">Carbono Capturado Estimado</td>
                <td class="summary-value">{{ number_format($totalCarbono, 2) }} toneladas</td>
            </tr>
        </table>
    </div>
    
    <!-- Footer -->
    <div class="footer">
        <table>
            <tr>
                <td>SIGMAD - Sistema Inteligente de Gestión Maderable</td>
                <td style="text-align: center;">Este documento es de carácter informativo</td>
                <td style="text-align: right;">Página 1</td>
            </tr>
        </table>
    </div>
</body>
</html>
