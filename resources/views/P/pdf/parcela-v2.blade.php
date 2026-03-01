<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta http-equiv="Content-Type" content="text/html; charset=utf-8"/>
    <title>Reporte de Parcela - {{ $parcela->nom_parcela }}</title>
    <style>
        /* === PREMIUM PDF STYLES === */
        @page {
            margin: 0;
        }
        
        * {
            margin: 0;
            padding: 0;
            box-sizing: border-box;
        }
        
        body {
            font-family: 'Segoe UI', 'DejaVu Sans', Arial, sans-serif;
            font-size: 10px;
            color: #1a1a1a;
            line-height: 1.5;
            background: #ffffff;
        }
        
        /* Page wrapper with margins */
        .page-wrapper {
            padding: 25px 30px;
        }
        
        /* === HEADER === */
        .header {
            position: relative;
            background: linear-gradient(135deg, #0d2818 0%, #1a5a3a 50%, #22915a 100%);
            padding: 25px 30px;
            margin: -25px -30px 25px -30px;
        }
        
        .header::after {
            content: '';
            position: absolute;
            bottom: 0;
            left: 0;
            right: 0;
            height: 4px;
            background: linear-gradient(90deg, #d4a853, #e8c068, #d4a853);
        }
        
        .header-table {
            width: 100%;
        }
        
        .logo-cell {
            width: 70px;
            vertical-align: middle;
        }
        
        .logo {
            width: 55px;
            height: auto;
        }
        
        .brand-text {
            vertical-align: middle;
            padding-left: 12px;
        }
        
        .brand-name {
            font-size: 22px;
            font-weight: bold;
            color: #ffffff;
            letter-spacing: 2px;
        }
        
        .brand-subtitle {
            font-size: 9px;
            color: rgba(255,255,255,0.7);
            letter-spacing: 1px;
            text-transform: uppercase;
            margin-top: 2px;
        }
        
        .header-right {
            text-align: right;
            vertical-align: middle;
        }
        
        .report-type {
            font-size: 11px;
            color: #d4a853;
            font-weight: bold;
            letter-spacing: 2px;
            text-transform: uppercase;
        }
        
        .report-date {
            font-size: 9px;
            color: rgba(255,255,255,0.65);
            margin-top: 4px;
        }
        
        /* === PARCELA HERO === */
        .parcela-hero {
            background: linear-gradient(135deg, #f0f9f4 0%, #e6f4ec 100%);
            border: 2px solid #22915a;
            border-radius: 12px;
            padding: 20px 25px;
            margin-bottom: 20px;
            position: relative;
            overflow: hidden;
        }
        
        .parcela-hero::before {
            content: '';
            position: absolute;
            top: 0;
            left: 0;
            width: 6px;
            height: 100%;
            background: linear-gradient(180deg, #22915a, #1a5a3a);
        }
        
        .parcela-name {
            font-size: 20px;
            font-weight: bold;
            color: #0d2818;
            margin-bottom: 8px;
            padding-left: 15px;
        }
        
        .parcela-location {
            font-size: 11px;
            color: #4a7c59;
            padding-left: 15px;
            display: flex;
            align-items: center;
        }
        
        .parcela-meta {
            margin-top: 12px;
            padding-left: 15px;
        }
        
        .meta-table {
            width: 100%;
        }
        
        .meta-item {
            display: inline-block;
            background: #ffffff;
            border: 1px solid #c8e6d0;
            border-radius: 6px;
            padding: 6px 12px;
            margin-right: 8px;
            font-size: 9px;
        }
        
        .meta-label {
            color: #6b8f76;
            text-transform: uppercase;
            font-size: 7px;
            letter-spacing: 0.5px;
        }
        
        .meta-value {
            color: #1a5a3a;
            font-weight: bold;
            font-size: 11px;
        }
        
        /* === STATS CARDS === */
        .stats-row {
            width: 100%;
            margin-bottom: 20px;
        }
        
        .stats-table {
            width: 100%;
            border-collapse: separate;
            border-spacing: 10px 0;
        }
        
        .stat-card {
            background: #ffffff;
            border: 1px solid #e4e4e7;
            border-radius: 10px;
            padding: 15px;
            text-align: center;
            position: relative;
            overflow: hidden;
        }
        
        .stat-card::before {
            content: '';
            position: absolute;
            top: 0;
            left: 0;
            right: 0;
            height: 3px;
        }
        
        .stat-card.trees::before { background: linear-gradient(90deg, #22915a, #51c98a); }
        .stat-card.logs::before { background: linear-gradient(90deg, #6366f1, #818cf8); }
        .stat-card.volume::before { background: linear-gradient(90deg, #d4a853, #e8c068); }
        .stat-card.carbon::before { background: linear-gradient(90deg, #3b82f6, #60a5fa); }
        
        .stat-icon {
            width: 36px;
            height: 36px;
            border-radius: 8px;
            margin: 0 auto 8px;
            display: flex;
            align-items: center;
            justify-content: center;
            font-size: 16px;
        }
        
        .stat-card.trees .stat-icon { background: #e6f4ec; color: #22915a; }
        .stat-card.logs .stat-icon { background: #eef2ff; color: #6366f1; }
        .stat-card.volume .stat-icon { background: #fef3e3; color: #d4a853; }
        .stat-card.carbon .stat-icon { background: #eff6ff; color: #3b82f6; }
        
        .stat-value {
            font-size: 22px;
            font-weight: bold;
            color: #0d2818;
            line-height: 1.1;
        }
        
        .stat-label {
            font-size: 8px;
            color: #71717a;
            text-transform: uppercase;
            letter-spacing: 0.5px;
            margin-top: 4px;
        }
        
        /* === SECTIONS === */
        .section {
            margin-bottom: 20px;
        }
        
        .section-header {
            background: linear-gradient(135deg, #1a5a3a, #22915a);
            color: #ffffff;
            padding: 10px 15px;
            border-radius: 8px 8px 0 0;
            display: flex;
            align-items: center;
        }
        
        .section-icon {
            width: 24px;
            height: 24px;
            background: rgba(255,255,255,0.15);
            border-radius: 5px;
            display: inline-flex;
            align-items: center;
            justify-content: center;
            margin-right: 10px;
            font-size: 11px;
        }
        
        .section-title {
            font-size: 12px;
            font-weight: bold;
            letter-spacing: 0.5px;
        }
        
        .section-count {
            background: rgba(255,255,255,0.2);
            padding: 2px 8px;
            border-radius: 10px;
            font-size: 9px;
            margin-left: auto;
        }
        
        .section-body {
            background: #ffffff;
            border: 1px solid #e4e4e7;
            border-top: none;
            border-radius: 0 0 8px 8px;
            padding: 15px;
        }
        
        /* === TABLES === */
        .data-table {
            width: 100%;
            border-collapse: collapse;
            font-size: 9px;
        }
        
        .data-table thead th {
            background: #f8fafc;
            color: #4a5568;
            font-weight: 600;
            text-transform: uppercase;
            font-size: 8px;
            letter-spacing: 0.5px;
            padding: 10px 8px;
            text-align: left;
            border-bottom: 2px solid #e4e4e7;
        }
        
        .data-table tbody tr {
            border-bottom: 1px solid #f1f5f9;
        }
        
        .data-table tbody tr:nth-child(even) {
            background: #fafafa;
        }
        
        .data-table tbody tr:last-child {
            border-bottom: none;
        }
        
        .data-table tbody td {
            padding: 10px 8px;
            vertical-align: middle;
        }
        
        .row-number {
            background: linear-gradient(135deg, #1a5a3a, #22915a);
            color: #ffffff;
            width: 22px;
            height: 22px;
            border-radius: 5px;
            display: inline-flex;
            align-items: center;
            justify-content: center;
            font-weight: bold;
            font-size: 8px;
        }
        
        .species-badge {
            background: linear-gradient(135deg, #e6f4ec, #f0f9f4);
            color: #1a5a3a;
            padding: 4px 10px;
            border-radius: 12px;
            font-weight: 600;
            font-size: 8px;
            display: inline-block;
            border: 1px solid #c8e6d0;
        }
        
        .value-highlight {
            font-weight: bold;
            color: #0d2818;
        }
        
        .unit {
            color: #71717a;
            font-size: 8px;
        }
        
        /* === TURNOS DE CORTA === */
        .turno-card {
            background: linear-gradient(135deg, #fef3e3, #fffbeb);
            border: 1px solid #e8c068;
            border-radius: 8px;
            padding: 12px 15px;
            margin-bottom: 8px;
        }
        
        .turno-card:last-child {
            margin-bottom: 0;
        }
        
        .turno-code {
            font-weight: bold;
            color: #b8860b;
            font-size: 11px;
        }
        
        .turno-dates {
            color: #8b7355;
            font-size: 9px;
            margin-top: 4px;
        }
        
        /* === PRODUCTOR INFO === */
        .productor-box {
            background: #f8fafc;
            border: 1px solid #e4e4e7;
            border-radius: 8px;
            padding: 15px;
            margin-bottom: 20px;
        }
        
        .productor-label {
            font-size: 8px;
            color: #71717a;
            text-transform: uppercase;
            letter-spacing: 0.5px;
        }
        
        .productor-name {
            font-size: 14px;
            font-weight: bold;
            color: #0d2818;
            margin-top: 3px;
        }
        
        /* === FOOTER === */
        .footer {
            position: fixed;
            bottom: 0;
            left: 0;
            right: 0;
            background: #f8fafc;
            border-top: 1px solid #e4e4e7;
            padding: 12px 30px;
            font-size: 8px;
            color: #71717a;
        }
        
        .footer-table {
            width: 100%;
        }
        
        .footer-left {
            text-align: left;
        }
        
        .footer-center {
            text-align: center;
        }
        
        .footer-right {
            text-align: right;
        }
        
        .footer-brand {
            color: #1a5a3a;
            font-weight: bold;
        }
        
        /* === EMPTY STATE === */
        .empty-state {
            text-align: center;
            padding: 20px;
            color: #71717a;
            font-style: italic;
        }
        
        /* === UTILITIES === */
        .text-right { text-align: right; }
        .text-center { text-align: center; }
        .text-muted { color: #71717a; }
        .font-bold { font-weight: bold; }
        
        /* Page break */
        .page-break {
            page-break-after: always;
        }
    </style>
</head>
<body>
    <div class="page-wrapper">
        <!-- HEADER -->
        <div class="header">
            <table class="header-table">
                <tr>
                    <td class="logo-cell">
                        @if($logo)
                            <img src="{{ $logo }}" class="logo" alt="WoodWise">
                        @else
                            <div style="width:55px;height:55px;background:rgba(255,255,255,0.15);border-radius:10px;display:flex;align-items:center;justify-content:center;color:#d4a853;font-weight:bold;font-size:14px;">WW</div>
                        @endif
                    </td>
                    <td class="brand-text">
                        <div class="brand-name">WOODWISE</div>
                        <div class="brand-subtitle">Sistema de Gestión Forestal</div>
                    </td>
                    <td class="header-right">
                        <div class="report-type">Reporte de Parcela</div>
                        <div class="report-date">Generado: {{ $fecha }}</div>
                    </td>
                </tr>
            </table>
        </div>
        
        <!-- PARCELA HERO -->
        <div class="parcela-hero">
            <div class="parcela-name">{{ $parcela->nom_parcela }}</div>
            <div class="parcela-location">
                📍 {{ $parcela->ubicacion ?? 'Ubicación no especificada' }}
            </div>
            <div class="parcela-meta">
                <table class="meta-table">
                    <tr>
                        <td style="width:25%">
                            <div class="meta-item">
                                <div class="meta-label">Extensión</div>
                                <div class="meta-value">{{ number_format($parcela->extension ?? 0, 2) }} ha</div>
                            </div>
                        </td>
                        <td style="width:25%">
                            <div class="meta-item">
                                <div class="meta-label">Productor</div>
                                <div class="meta-value">{{ $productor->persona->nom ?? 'N/A' }} {{ $productor->persona->ap ?? '' }}</div>
                            </div>
                        </td>
                        <td style="width:25%">
                            <div class="meta-item">
                                <div class="meta-label">Técnico Asignado</div>
                                <div class="meta-value">{{ $parcela->tecnicos->first()->persona->nom ?? 'Sin asignar' }}</div>
                            </div>
                        </td>
                        <td style="width:25%">
                            <div class="meta-item">
                                <div class="meta-label">Turnos de Corta</div>
                                <div class="meta-value">{{ $parcela->turnosCorta->count() }}</div>
                            </div>
                        </td>
                    </tr>
                </table>
            </div>
        </div>
        
        <!-- STATS CARDS -->
        @php
            $totalVolumen = 0;
            $totalBiomasa = 0;
            $totalCarbono = 0;
            
            foreach($parcela->arboles as $arbol) {
                foreach($arbol->estimaciones as $est) {
                    $tipo = $est->tipoEstimacion->desc_estimacion ?? '';
                    if (stripos($tipo, 'volumen') !== false) $totalVolumen += $est->calculo ?? 0;
                    elseif (stripos($tipo, 'biomasa') !== false) $totalBiomasa += $est->calculo ?? 0;
                    elseif (stripos($tipo, 'carbono') !== false) $totalCarbono += $est->calculo ?? 0;
                }
            }
            
            foreach($parcela->trozas as $troza) {
                foreach($troza->estimaciones as $est) {
                    $tipo = $est->tipoEstimacion->desc_estimacion ?? '';
                    if (stripos($tipo, 'volumen') !== false) $totalVolumen += $est->calculo ?? 0;
                    elseif (stripos($tipo, 'biomasa') !== false) $totalBiomasa += $est->calculo ?? 0;
                    elseif (stripos($tipo, 'carbono') !== false) $totalCarbono += $est->calculo ?? 0;
                }
            }
        @endphp
        
        <div class="stats-row">
            <table class="stats-table">
                <tr>
                    <td style="width:25%">
                        <div class="stat-card trees">
                            <div class="stat-icon"><svg width="18" height="18" viewBox="0 0 24 24" fill="currentColor"><path d="M12 2L4 12h3v8h10v-8h3L12 2z"/></svg></div>
                            <div class="stat-value">{{ $parcela->arboles->count() }}</div>
                            <div class="stat-label">Árboles Registrados</div>
                        </div>
                    </td>
                    <td style="width:25%">
                        <div class="stat-card logs">
                            <div class="stat-icon"><svg width="18" height="18" viewBox="0 0 24 24" fill="currentColor"><circle cx="12" cy="12" r="10" stroke="currentColor" stroke-width="2" fill="none"/><circle cx="12" cy="12" r="4"/></svg></div>
                            <div class="stat-value">{{ $parcela->trozas->count() }}</div>
                            <div class="stat-label">Trozas Registradas</div>
                        </div>
                    </td>
                    <td style="width:25%">
                        <div class="stat-card volume">
                            <div class="stat-icon"><svg width="18" height="18" viewBox="0 0 24 24" fill="currentColor"><path d="M21 16V8c0-.55-.22-1.05-.59-1.41L16.17 2H8c-.55 0-1.05.22-1.41.59L2 7v10c0 .55.22 1.05.59 1.41L7 22h10c.55 0 1.05-.22 1.41-.59L21 16zM4 8l4-4h8l4 4v8l-4 4H8l-4-4V8z"/></svg></div>
                            <div class="stat-value">{{ number_format($totalVolumen, 2) }}</div>
                            <div class="stat-label">Volumen Total (m³)</div>
                        </div>
                    </td>
                    <td style="width:25%">
                        <div class="stat-card carbon">
                            <div class="stat-icon"><svg width="18" height="18" viewBox="0 0 24 24" fill="currentColor"><path d="M17 8C8 10 5.9 16.17 3.82 21.34l1.89.66.95-2.7c1.41-.75 3.34-1.3 5.34-1.3 2.5 0 4.5 1 6.5 3l1.5-1.5c-2-2.5-4.5-3.5-7-3.5-1.5 0-3 .3-4.3.8L12 8c2.29 0 4.33.96 5.78 2.5L20 8l-3-3.5V8z"/></svg></div>
                            <div class="stat-value">{{ number_format($totalCarbono, 2) }}</div>
                            <div class="stat-label">Carbono (ton)</div>
                        </div>
                    </td>
                </tr>
            </table>
        </div>
        
        <!-- ÁRBOLES SECTION -->
        <div class="section">
            <div class="section-header">
                <span class="section-icon">▲</span>
                <span class="section-title">Inventario de Árboles</span>
                <span class="section-count">{{ $parcela->arboles->count() }} registros</span>
            </div>
            <div class="section-body">
                @if($parcela->arboles->count() > 0)
                <table class="data-table">
                    <thead>
                        <tr>
                            <th style="width:50px">No.</th>
                            <th>Especie</th>
                            <th style="width:80px" class="text-right">Altura</th>
                            <th style="width:80px" class="text-right">DAP</th>
                            <th style="width:90px" class="text-right">Volumen</th>
                        </tr>
                    </thead>
                    <tbody>
                        @foreach($parcela->arboles as $index => $arbol)
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
                            <td><span class="row-number">{{ $index + 1 }}</span></td>
                            <td><span class="species-badge">{{ $arbol->especie->nom_comun ?? 'N/A' }}</span></td>
                            <td class="text-right">
                                <span class="value-highlight">{{ number_format($arbol->altura_total ?? 0, 2) }}</span>
                                <span class="unit">m</span>
                            </td>
                            <td class="text-right">
                                <span class="value-highlight">{{ number_format($arbol->diametro_pecho ?? 0, 2) }}</span>
                                <span class="unit">cm</span>
                            </td>
                            <td class="text-right">
                                <span class="value-highlight">{{ number_format($volArbol, 4) }}</span>
                                <span class="unit">m³</span>
                            </td>
                        </tr>
                        @endforeach
                    </tbody>
                </table>
                @else
                <div class="empty-state">No hay árboles registrados en esta parcela</div>
                @endif
            </div>
        </div>
        
        <!-- TROZAS SECTION -->
        <div class="section">
            <div class="section-header">
                <span class="section-icon">●</span>
                <span class="section-title">Inventario de Trozas</span>
                <span class="section-count">{{ $parcela->trozas->count() }} registros</span>
            </div>
            <div class="section-body">
                @if($parcela->trozas->count() > 0)
                <table class="data-table">
                    <thead>
                        <tr>
                            <th style="width:50px">No.</th>
                            <th>Especie</th>
                            <th style="width:70px" class="text-right">Longitud</th>
                            <th style="width:70px" class="text-right">Ø Mayor</th>
                            <th style="width:70px" class="text-right">Ø Menor</th>
                            <th style="width:80px" class="text-right">Volumen</th>
                        </tr>
                    </thead>
                    <tbody>
                        @foreach($parcela->trozas as $index => $troza)
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
                            <td><span class="row-number">{{ $index + 1 }}</span></td>
                            <td><span class="species-badge">{{ $troza->especie->nom_comun ?? 'N/A' }}</span></td>
                            <td class="text-right">
                                <span class="value-highlight">{{ number_format($troza->longitud ?? 0, 2) }}</span>
                                <span class="unit">m</span>
                            </td>
                            <td class="text-right">
                                <span class="value-highlight">{{ number_format($troza->diametro ?? 0, 2) }}</span>
                                <span class="unit">cm</span>
                            </td>
                            <td class="text-right">
                                <span class="value-highlight">{{ number_format($troza->diametro_otro_extremo ?? 0, 2) }}</span>
                                <span class="unit">cm</span>
                            </td>
                            <td class="text-right">
                                <span class="value-highlight">{{ number_format($volTroza, 4) }}</span>
                                <span class="unit">m³</span>
                            </td>
                        </tr>
                        @endforeach
                    </tbody>
                </table>
                @else
                <div class="empty-state">No hay trozas registradas en esta parcela</div>
                @endif
            </div>
        </div>
        
        <!-- TURNOS DE CORTA -->
        @if($parcela->turnosCorta && $parcela->turnosCorta->count() > 0)
        <div class="section">
            <div class="section-header">
                <span class="section-icon">■</span>
                <span class="section-title">Historial de Turnos de Corta</span>
                <span class="section-count">{{ $parcela->turnosCorta->count() }} turnos</span>
            </div>
            <div class="section-body">
                @foreach($parcela->turnosCorta as $turno)
                <div class="turno-card">
                    <div class="turno-code">{{ $turno->codigo_corta }}</div>
                    <div class="turno-dates">
                        Inicio: {{ \Carbon\Carbon::parse($turno->fecha_corta)->format('d/m/Y') }}
                        @if($turno->fecha_fin)
                            &nbsp;&nbsp;|&nbsp;&nbsp; Fin: {{ \Carbon\Carbon::parse($turno->fecha_fin)->format('d/m/Y') }}
                        @else
                            &nbsp;&nbsp;|&nbsp;&nbsp; <em style="color:#d4a853">En progreso</em>
                        @endif
                    </div>
                </div>
                @endforeach
            </div>
        </div>
        @endif
        
    </div>
    
    <!-- FOOTER -->
    <div class="footer">
        <table class="footer-table">
            <tr>
                <td class="footer-left">
                    <span class="footer-brand">WoodWise</span> - Sistema de Gestión Forestal Sostenible
                </td>
                <td class="footer-center">
                    Documento generado automáticamente
                </td>
                <td class="footer-right">
                    {{ $fecha }}
                </td>
            </tr>
        </table>
    </div>
</body>
</html>
