<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta http-equiv="Content-Type" content="text/html; charset=utf-8"/>
    <title>Reporte General - {{ $productor->persona->nom ?? 'Productor' }}</title>
    <style>
        /* === PREMIUM PDF STYLES - LANDSCAPE === */
        @page {
            margin: 0;
            size: landscape;
        }
        
        * {
            margin: 0;
            padding: 0;
            box-sizing: border-box;
        }
        
        body {
            font-family: 'Segoe UI', 'DejaVu Sans', Arial, sans-serif;
            font-size: 9px;
            color: #1a1a1a;
            line-height: 1.4;
            background: #ffffff;
        }
        
        .page-wrapper {
            padding: 20px 25px;
        }
        
        /* === HEADER === */
        .header {
            background: linear-gradient(135deg, #0d2818 0%, #1a5a3a 50%, #22915a 100%);
            padding: 20px 25px;
            margin: -20px -25px 20px -25px;
            position: relative;
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
        
        .logo {
            width: 50px;
            height: auto;
        }
        
        .brand-name {
            font-size: 20px;
            font-weight: bold;
            color: #ffffff;
            letter-spacing: 2px;
            padding-left: 10px;
        }
        
        .brand-subtitle {
            font-size: 8px;
            color: rgba(255,255,255,0.7);
            letter-spacing: 1px;
            text-transform: uppercase;
            padding-left: 10px;
        }
        
        .header-center {
            text-align: center;
        }
        
        .report-title {
            font-size: 16px;
            font-weight: bold;
            color: #ffffff;
            letter-spacing: 1px;
        }
        
        .report-subtitle {
            font-size: 9px;
            color: #d4a853;
            margin-top: 3px;
        }
        
        .header-right {
            text-align: right;
        }
        
        .productor-name {
            font-size: 11px;
            font-weight: bold;
            color: #ffffff;
        }
        
        .report-date {
            font-size: 8px;
            color: rgba(255,255,255,0.7);
            margin-top: 3px;
        }
        
        /* === STATS OVERVIEW === */
        .stats-overview {
            background: linear-gradient(135deg, #f0f9f4 0%, #e6f4ec 100%);
            border: 2px solid #22915a;
            border-radius: 10px;
            padding: 18px;
            margin-bottom: 18px;
        }
        
        .stats-grid {
            width: 100%;
            border-collapse: separate;
            border-spacing: 12px 0;
        }
        
        .overview-stat {
            background: #ffffff;
            border-radius: 8px;
            padding: 12px;
            text-align: center;
            border: 1px solid #c8e6d0;
        }
        
        .overview-stat-icon {
            font-size: 18px;
            margin-bottom: 5px;
        }
        
        .overview-stat-value {
            font-size: 20px;
            font-weight: bold;
            color: #0d2818;
            line-height: 1.1;
        }
        
        .overview-stat-label {
            font-size: 7px;
            color: #6b8f76;
            text-transform: uppercase;
            letter-spacing: 0.5px;
            margin-top: 3px;
        }
        
        /* === PARCELAS TABLE === */
        .section-header {
            background: linear-gradient(135deg, #1a5a3a, #22915a);
            color: #ffffff;
            padding: 10px 15px;
            border-radius: 8px 8px 0 0;
        }
        
        .section-title {
            font-size: 11px;
            font-weight: bold;
            letter-spacing: 0.5px;
        }
        
        .section-body {
            background: #ffffff;
            border: 1px solid #e4e4e7;
            border-top: none;
            border-radius: 0 0 8px 8px;
            padding: 12px;
        }
        
        .data-table {
            width: 100%;
            border-collapse: collapse;
            font-size: 8px;
        }
        
        .data-table thead th {
            background: linear-gradient(135deg, #f8fafc, #f1f5f9);
            color: #374151;
            font-weight: 700;
            text-transform: uppercase;
            font-size: 7px;
            letter-spacing: 0.5px;
            padding: 10px 8px;
            text-align: left;
            border-bottom: 2px solid #e4e4e7;
        }
        
        .data-table thead th.text-right {
            text-align: right;
        }
        
        .data-table tbody tr {
            border-bottom: 1px solid #f1f5f9;
        }
        
        .data-table tbody tr:nth-child(even) {
            background: #fafafa;
        }
        
        .data-table tbody tr:hover {
            background: #f0f9f4;
        }
        
        .data-table tbody td {
            padding: 10px 8px;
            vertical-align: middle;
        }
        
        .row-number {
            background: linear-gradient(135deg, #1a5a3a, #22915a);
            color: #ffffff;
            width: 20px;
            height: 20px;
            border-radius: 4px;
            display: inline-flex;
            align-items: center;
            justify-content: center;
            font-weight: bold;
            font-size: 7px;
        }
        
        .parcela-name {
            font-weight: 700;
            color: #0d2818;
            font-size: 9px;
        }
        
        .parcela-location {
            color: #6b7280;
            font-size: 7px;
            margin-top: 2px;
        }
        
        .badge {
            display: inline-block;
            padding: 3px 8px;
            border-radius: 10px;
            font-weight: 600;
            font-size: 7px;
        }
        
        .badge-trees {
            background: #e6f4ec;
            color: #1a5a3a;
            border: 1px solid #c8e6d0;
        }
        
        .badge-logs {
            background: #eef2ff;
            color: #4f46e5;
            border: 1px solid #c7d2fe;
        }
        
        .value-highlight {
            font-weight: bold;
            color: #0d2818;
        }
        
        .unit {
            color: #9ca3af;
            font-size: 7px;
        }
        
        .status-active {
            background: #fef3c7;
            color: #b45309;
            padding: 3px 8px;
            border-radius: 10px;
            font-weight: 600;
            font-size: 7px;
            border: 1px solid #fcd34d;
        }
        
        .status-none {
            color: #9ca3af;
            font-style: italic;
        }
        
        .text-right {
            text-align: right;
        }
        
        .text-center {
            text-align: center;
        }
        
        /* === SUMMARY ROW === */
        .summary-row {
            background: linear-gradient(135deg, #1a5a3a, #22915a) !important;
            color: #ffffff !important;
        }
        
        .summary-row td {
            padding: 12px 8px !important;
            font-weight: bold;
            color: #ffffff;
        }
        
        .summary-row .unit {
            color: rgba(255,255,255,0.7);
        }
        
        /* === FOOTER === */
        .footer {
            position: fixed;
            bottom: 0;
            left: 0;
            right: 0;
            background: #f8fafc;
            border-top: 1px solid #e4e4e7;
            padding: 10px 25px;
            font-size: 7px;
            color: #71717a;
        }
        
        .footer-table {
            width: 100%;
        }
        
        .footer-brand {
            color: #1a5a3a;
            font-weight: bold;
        }
        
        /* === RESUMEN LATERAL === */
        .side-summary {
            width: 100%;
            margin-top: 15px;
        }
        
        .summary-card {
            background: #ffffff;
            border: 1px solid #e4e4e7;
            border-radius: 8px;
            padding: 12px;
            text-align: center;
        }
        
        .summary-card-title {
            font-size: 7px;
            color: #6b7280;
            text-transform: uppercase;
            letter-spacing: 0.5px;
            margin-bottom: 6px;
        }
        
        .summary-card-value {
            font-size: 16px;
            font-weight: bold;
            color: #0d2818;
        }
        
        .summary-card-unit {
            font-size: 8px;
            color: #22915a;
            font-weight: 600;
        }
        
        /* === CHART PLACEHOLDER === */
        .chart-section {
            margin-top: 15px;
        }
        
        .progress-bar-container {
            background: #f1f5f9;
            border-radius: 8px;
            height: 12px;
            overflow: hidden;
            margin-bottom: 8px;
        }
        
        .progress-bar {
            height: 100%;
            border-radius: 8px;
        }
        
        .progress-label {
            display: flex;
            justify-content: space-between;
            font-size: 7px;
            color: #6b7280;
            margin-bottom: 3px;
        }
    </style>
</head>
<body>
    <div class="page-wrapper">
        <!-- HEADER -->
        <div class="header">
            <table class="header-table">
                <tr>
                    <td style="width: 200px;">
                        <table>
                            <tr>
                                <td style="width: 55px;">
                                    @if($logo)
                                        <img src="{{ $logo }}" class="logo" alt="WoodWise">
                                    @else
                                        <div style="width:50px;height:50px;background:rgba(255,255,255,0.15);border-radius:8px;display:flex;align-items:center;justify-content:center;color:#d4a853;font-weight:bold;font-size:12px;">WW</div>
                                    @endif
                                </td>
                                <td>
                                    <div class="brand-name">WOODWISE</div>
                                    <div class="brand-subtitle">Gestión Forestal</div>
                                </td>
                            </tr>
                        </table>
                    </td>
                    <td class="header-center">
                        <div class="report-title">REPORTE GENERAL DE PRODUCCIÓN</div>
                        <div class="report-subtitle">Resumen Consolidado de Parcelas</div>
                    </td>
                    <td class="header-right" style="width: 180px;">
                        <div class="productor-name">{{ $productor->persona->nom ?? 'N/A' }} {{ $productor->persona->ap ?? '' }}</div>
                        <div class="report-date">Generado: {{ $fecha }}</div>
                    </td>
                </tr>
            </table>
        </div>
        
        <!-- STATS OVERVIEW -->
        <div class="stats-overview">
            <table class="stats-grid">
                <tr>
                    <td style="width: 14.28%">
                        <div class="overview-stat">
                            <div class="overview-stat-icon"><svg width="16" height="16" viewBox="0 0 24 24" fill="currentColor"><path d="M20.5 3l-.16.03L15 5.1 9 3 3.36 4.9c-.21.07-.36.25-.36.48V20.5c0 .28.22.5.5.5l.16-.03L9 18.9l6 2.1 5.64-1.9c.21-.07.36-.25.36-.48V3.5c0-.28-.22-.5-.5-.5zM15 19l-6-2.11V5l6 2.11V19z"/></svg></div>
                            <div class="overview-stat-value">{{ $stats['total_parcelas'] }}</div>
                            <div class="overview-stat-label">Parcelas</div>
                        </div>
                    </td>
                    <td style="width: 14.28%">
                        <div class="overview-stat">
                            <div class="overview-stat-icon"><svg width="16" height="16" viewBox="0 0 24 24" fill="currentColor"><path d="M3 5v14h18V5H3zm16 12H5V7h14v10zM9 9H7v2h2V9zm0 4H7v2h2v-2zm4-4h-2v2h2V9zm0 4h-2v2h2v-2zm4-4h-2v2h2V9zm0 4h-2v2h2v-2z"/></svg></div>
                            <div class="overview-stat-value">{{ number_format($stats['total_extension'], 1) }}</div>
                            <div class="overview-stat-label">Hectáreas</div>
                        </div>
                    </td>
                    <td style="width: 14.28%">
                        <div class="overview-stat">
                            <div class="overview-stat-icon"><svg width="16" height="16" viewBox="0 0 24 24" fill="currentColor"><path d="M12 2L4 12h3v8h10v-8h3L12 2z"/></svg></div>
                            <div class="overview-stat-value">{{ $stats['total_arboles'] }}</div>
                            <div class="overview-stat-label">Árboles</div>
                        </div>
                    </td>
                    <td style="width: 14.28%">
                        <div class="overview-stat">
                            <div class="overview-stat-icon"><svg width="16" height="16" viewBox="0 0 24 24" fill="currentColor"><circle cx="12" cy="12" r="10" stroke="currentColor" stroke-width="2" fill="none"/><circle cx="12" cy="12" r="4"/></svg></div>
                            <div class="overview-stat-value">{{ $stats['total_trozas'] }}</div>
                            <div class="overview-stat-label">Trozas</div>
                        </div>
                    </td>
                    <td style="width: 14.28%">
                        <div class="overview-stat">
                            <div class="overview-stat-icon"><svg width="16" height="16" viewBox="0 0 24 24" fill="currentColor"><path d="M21 16V8L12 2 3 8v8l9 6 9-6zM12 4.15l6 4V15l-6 4-6-4V8.15l6-4z"/></svg></div>
                            <div class="overview-stat-value">{{ number_format($stats['total_volumen'], 1) }}</div>
                            <div class="overview-stat-label">Volumen (m³)</div>
                        </div>
                    </td>
                    <td style="width: 14.28%">
                        <div class="overview-stat">
                            <div class="overview-stat-icon"><svg width="16" height="16" viewBox="0 0 24 24" fill="currentColor"><path d="M12 3L1 9l11 6 9-4.91V17h2V9L12 3z"/></svg></div>
                            <div class="overview-stat-value">{{ number_format($stats['total_biomasa'], 1) }}</div>
                            <div class="overview-stat-label">Biomasa (ton)</div>
                        </div>
                    </td>
                    <td style="width: 14.28%">
                        <div class="overview-stat">
                            <div class="overview-stat-icon"><svg width="16" height="16" viewBox="0 0 24 24" fill="currentColor"><path d="M17 8C8 10 5.9 16.17 3.82 21.34l1.89.66.95-2.7c1.41-.75 3.34-1.3 5.34-1.3 2.5 0 4.5 1 6.5 3l1.5-1.5c-2-2.5-4.5-3.5-7-3.5-1.5 0-3 .3-4.3.8L12 8c2.29 0 4.33.96 5.78 2.5L20 8l-3-3.5V8z"/></svg></div>
                            <div class="overview-stat-value">{{ number_format($stats['total_carbono'], 1) }}</div>
                            <div class="overview-stat-label">Carbono (ton)</div>
                        </div>
                    </td>
                </tr>
            </table>
        </div>
        
        <!-- PARCELAS TABLE -->
        <div class="section-header">
            <span class="section-title">Detalle de Parcelas Registradas</span>
        </div>
        <div class="section-body">
            <table class="data-table">
                <thead>
                    <tr>
                        <th style="width: 35px;">No.</th>
                        <th style="width: 180px;">Parcela</th>
                        <th class="text-center" style="width: 70px;">Extensión</th>
                        <th class="text-center" style="width: 60px;">Árboles</th>
                        <th class="text-center" style="width: 55px;">Trozas</th>
                        <th class="text-right" style="width: 75px;">Volumen</th>
                        <th class="text-right" style="width: 70px;">Biomasa</th>
                        <th class="text-right" style="width: 65px;">Carbono</th>
                        <th class="text-center" style="width: 90px;">Técnico</th>
                        <th class="text-center" style="width: 70px;">Turno Activo</th>
                    </tr>
                </thead>
                <tbody>
                    @foreach($parcelas as $index => $parcela)
                    @php
                        $volParcela = 0;
                        $bioParcela = 0;
                        $carParcela = 0;
                        
                        foreach($parcela->trozas as $t) {
                            foreach($t->estimaciones as $est) {
                                $tipo = $est->tipoEstimacion->desc_estimacion ?? '';
                                if (stripos($tipo, 'volumen') !== false) $volParcela += $est->calculo ?? 0;
                                elseif (stripos($tipo, 'biomasa') !== false) $bioParcela += $est->calculo ?? 0;
                                elseif (stripos($tipo, 'carbono') !== false) $carParcela += $est->calculo ?? 0;
                            }
                        }
                        
                        foreach($parcela->arboles as $a) {
                            foreach($a->estimaciones as $est) {
                                $tipo = $est->tipoEstimacion->desc_estimacion ?? '';
                                if (stripos($tipo, 'volumen') !== false) $volParcela += $est->calculo ?? 0;
                                elseif (stripos($tipo, 'biomasa') !== false) $bioParcela += $est->calculo ?? 0;
                                elseif (stripos($tipo, 'carbono') !== false) $carParcela += $est->calculo ?? 0;
                            }
                        }
                        
                        $turnoActivo = $parcela->turnosCorta->whereNull('fecha_fin')->first();
                    @endphp
                    <tr>
                        <td><span class="row-number">{{ $index + 1 }}</span></td>
                        <td>
                            <div class="parcela-name">{{ $parcela->nom_parcela }}</div>
                            <div class="parcela-location">📍 {{ Str::limit($parcela->ubicacion, 30) }}</div>
                        </td>
                        <td class="text-center">
                            <span class="value-highlight">{{ number_format($parcela->extension ?? 0, 2) }}</span>
                            <span class="unit">ha</span>
                        </td>
                        <td class="text-center">
                            <span class="badge badge-trees">{{ $parcela->arboles_count }}</span>
                        </td>
                        <td class="text-center">
                            <span class="badge badge-logs">{{ $parcela->trozas_count }}</span>
                        </td>
                        <td class="text-right">
                            <span class="value-highlight">{{ number_format($volParcela, 2) }}</span>
                            <span class="unit">m³</span>
                        </td>
                        <td class="text-right">
                            <span class="value-highlight">{{ number_format($bioParcela, 2) }}</span>
                            <span class="unit">ton</span>
                        </td>
                        <td class="text-right">
                            <span class="value-highlight">{{ number_format($carParcela, 2) }}</span>
                            <span class="unit">ton</span>
                        </td>
                        <td class="text-center">
                            {{ $parcela->tecnicos->first()->persona->nom ?? '-' }}
                        </td>
                        <td class="text-center">
                            @if($turnoActivo)
                                <span class="status-active">{{ $turnoActivo->codigo_corta }}</span>
                            @else
                                <span class="status-none">-</span>
                            @endif
                        </td>
                    </tr>
                    @endforeach
                    
                    <!-- SUMMARY ROW -->
                    <tr class="summary-row">
                        <td colspan="2" style="font-size: 9px;">TOTALES CONSOLIDADOS</td>
                        <td class="text-center">
                            {{ number_format($stats['total_extension'], 2) }}
                            <span class="unit">ha</span>
                        </td>
                        <td class="text-center">{{ $stats['total_arboles'] }}</td>
                        <td class="text-center">{{ $stats['total_trozas'] }}</td>
                        <td class="text-right">
                            {{ number_format($stats['total_volumen'], 2) }}
                            <span class="unit">m³</span>
                        </td>
                        <td class="text-right">
                            {{ number_format($stats['total_biomasa'], 2) }}
                            <span class="unit">ton</span>
                        </td>
                        <td class="text-right">
                            {{ number_format($stats['total_carbono'], 2) }}
                            <span class="unit">ton</span>
                        </td>
                        <td colspan="2"></td>
                    </tr>
                </tbody>
            </table>
        </div>
        
        <!-- DISTRIBUTION VISUALIZATION -->
        @if($parcelas->count() > 0 && $stats['total_extension'] > 0)
        <div class="chart-section">
            <table style="width: 100%;">
                <tr>
                    <td style="width: 50%; padding-right: 10px; vertical-align: top;">
                        <div style="background: #ffffff; border: 1px solid #e4e4e7; border-radius: 8px; padding: 12px;">
                            <div style="font-size: 9px; font-weight: bold; color: #374151; margin-bottom: 10px;">Distribución por Extensión</div>
                            @foreach($parcelas->take(5) as $p)
                            @php
                                $percentage = $stats['total_extension'] > 0 ? ($p->extension / $stats['total_extension']) * 100 : 0;
                            @endphp
                            <div class="progress-label">
                                <span>{{ Str::limit($p->nom_parcela, 20) }}</span>
                                <span>{{ number_format($percentage, 1) }}%</span>
                            </div>
                            <div class="progress-bar-container">
                                <div class="progress-bar" style="width: {{ $percentage }}%; background: linear-gradient(90deg, #1a5a3a, #22915a);"></div>
                            </div>
                            @endforeach
                        </div>
                    </td>
                    <td style="width: 50%; padding-left: 10px; vertical-align: top;">
                        <div style="background: #ffffff; border: 1px solid #e4e4e7; border-radius: 8px; padding: 12px;">
                            <div style="font-size: 9px; font-weight: bold; color: #374151; margin-bottom: 10px;">Distribución por Volumen</div>
                            @foreach($parcelas->take(5) as $p)
                            @php
                                $volP = 0;
                                foreach($p->trozas as $t) {
                                    foreach($t->estimaciones as $est) {
                                        if (stripos($est->tipoEstimacion->desc_estimacion ?? '', 'volumen') !== false) {
                                            $volP += $est->calculo ?? 0;
                                        }
                                    }
                                }
                                foreach($p->arboles as $a) {
                                    foreach($a->estimaciones as $est) {
                                        if (stripos($est->tipoEstimacion->desc_estimacion ?? '', 'volumen') !== false) {
                                            $volP += $est->calculo ?? 0;
                                        }
                                    }
                                }
                                $percentageVol = $stats['total_volumen'] > 0 ? ($volP / $stats['total_volumen']) * 100 : 0;
                            @endphp
                            <div class="progress-label">
                                <span>{{ Str::limit($p->nom_parcela, 20) }}</span>
                                <span>{{ number_format($percentageVol, 1) }}%</span>
                            </div>
                            <div class="progress-bar-container">
                                <div class="progress-bar" style="width: {{ $percentageVol }}%; background: linear-gradient(90deg, #d4a853, #e8c068);"></div>
                            </div>
                            @endforeach
                        </div>
                    </td>
                </tr>
            </table>
        </div>
        @endif
    </div>
    
    <!-- FOOTER -->
    <div class="footer">
        <table class="footer-table">
            <tr>
                <td style="width: 33%;">
                    <span class="footer-brand">WoodWise</span> - Sistema de Gestión Forestal Sostenible
                </td>
                <td style="width: 34%; text-align: center;">
                    Documento generado automáticamente · Datos confidenciales
                </td>
                <td style="width: 33%; text-align: right;">
                    {{ $fecha }}
                </td>
            </tr>
        </table>
    </div>
</body>
</html>
