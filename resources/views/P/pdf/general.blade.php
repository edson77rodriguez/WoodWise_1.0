<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <title>Reporte General - WoodWise</title>
    <style>
        @page {
            margin: 35px 30px;
            size: landscape;
        }
        
        * {
            margin: 0;
            padding: 0;
            box-sizing: border-box;
        }
        
        body {
            font-family: 'Segoe UI', Tahoma, Geneva, Verdana, sans-serif;
            font-size: 10px;
            color: #333;
            line-height: 1.5;
            background: #fff;
        }
        
        /* Header */
        .header {
            border-bottom: 3px solid #2d5a3d;
            padding-bottom: 12px;
            margin-bottom: 15px;
        }
        
        .header table {
            width: 100%;
        }
        
        .logo {
            height: 45px;
        }
        
        .header-title {
            text-align: center;
        }
        
        .header-title h1 {
            color: #2d5a3d;
            font-size: 18px;
            margin: 0;
            letter-spacing: 1px;
        }
        
        .header-title p {
            color: #666;
            font-size: 9px;
            margin-top: 2px;
        }
        
        .header-date {
            text-align: right;
            color: #888;
            font-size: 8px;
        }
        
        /* Producer Info */
        .producer-box {
            background: #e8f3eb;
            border-left: 4px solid #2d5a3d;
            padding: 12px 15px;
            margin-bottom: 15px;
        }
        
        .producer-name {
            font-size: 14px;
            font-weight: bold;
            color: #1a3a2a;
        }
        
        .producer-subtitle {
            color: #666;
            font-size: 9px;
        }
        
        /* Stats Row */
        .stats-row {
            margin-bottom: 15px;
        }
        
        .stats-row table {
            width: 100%;
            border-collapse: collapse;
        }
        
        .stat-cell {
            background: #f8f9f7;
            border: 1px solid #e0e0e0;
            padding: 10px 8px;
            text-align: center;
        }
        
        .stat-cell.highlight {
            background: #2d5a3d;
            border-color: #2d5a3d;
        }
        
        .stat-value {
            font-size: 16px;
            font-weight: bold;
            color: #2d5a3d;
        }
        
        .stat-cell.highlight .stat-value {
            color: #fff;
        }
        
        .stat-label {
            font-size: 8px;
            color: #777;
            text-transform: uppercase;
            letter-spacing: 0.5px;
        }
        
        .stat-cell.highlight .stat-label {
            color: #b8d4c0;
        }
        
        /* Section Title */
        .section-title {
            background: #2d5a3d;
            color: #fff;
            padding: 6px 10px;
            font-size: 11px;
            font-weight: bold;
            margin-bottom: 0;
        }
        
        /* Tables */
        .data-table {
            width: 100%;
            border-collapse: collapse;
            margin-bottom: 15px;
        }
        
        .data-table th {
            background: #f0f4f1;
            color: #333;
            padding: 6px 8px;
            text-align: left;
            font-size: 8px;
            text-transform: uppercase;
            letter-spacing: 0.5px;
            border-bottom: 2px solid #2d5a3d;
        }
        
        .data-table td {
            padding: 6px 8px;
            border-bottom: 1px solid #eee;
            font-size: 9px;
        }
        
        .data-table tr:nth-child(even) {
            background: #fafafa;
        }
        
        .parcela-name-cell {
            font-weight: bold;
            color: #2d5a3d;
        }
        
        .location-cell {
            color: #666;
            font-size: 8px;
        }
        
        .number-cell {
            text-align: right;
            font-family: 'Consolas', monospace;
        }
        
        .badge {
            display: inline-block;
            padding: 2px 6px;
            background: #e8f3eb;
            color: #2d5a3d;
            border-radius: 8px;
            font-size: 8px;
            font-weight: bold;
        }
        
        /* Summary */
        .summary-box {
            background: #1a3a2a;
            color: #fff;
            padding: 12px 15px;
            margin-top: 15px;
        }
        
        .summary-box h3 {
            font-size: 11px;
            margin-bottom: 8px;
            color: #b8d4c0;
        }
        
        .summary-box table {
            width: 100%;
        }
        
        .summary-box td {
            padding: 4px 0;
            font-size: 9px;
        }
        
        .summary-label {
            color: #a8bba8;
        }
        
        .summary-value {
            text-align: right;
            font-weight: bold;
            font-size: 10px;
        }
        
        /* Footer */
        .footer {
            position: fixed;
            bottom: 15px;
            left: 30px;
            right: 30px;
            border-top: 1px solid #ddd;
            padding-top: 8px;
            font-size: 7px;
            color: #888;
        }
        
        .footer table {
            width: 100%;
        }
        
        /* Empty State */
        .empty-msg {
            text-align: center;
            padding: 30px;
            color: #888;
            font-style: italic;
        }
        
        /* Two Column Layout */
        .two-col {
            width: 100%;
        }
        
        .two-col td {
            width: 50%;
            vertical-align: top;
            padding-right: 10px;
        }
        
        .two-col td:last-child {
            padding-right: 0;
            padding-left: 10px;
        }
    </style>
</head>
<body>
    <!-- Header -->
    <div class="header">
        <table>
            <tr>
                <td style="width: 70px;">
                    @if($logo)
                        <img src="{{ $logo }}" class="logo" alt="WoodWise">
                    @endif
                </td>
                <td class="header-title">
                    <h1>REPORTE GENERAL DE PARCELAS</h1>
                    <p>Sistema de Gestión Forestal WoodWise</p>
                </td>
                <td class="header-date" style="width: 110px;">
                    Generado: {{ $fecha }}
                </td>
            </tr>
        </table>
    </div>
    
    <!-- Producer Info -->
    <div class="producer-box">
        <div class="producer-name">{{ $productor->persona->nombre ?? '' }} {{ $productor->persona->ap_paterno ?? '' }} {{ $productor->persona->ap_materno ?? '' }}</div>
        <div class="producer-subtitle">Productor Forestal</div>
    </div>
    
    <!-- Stats Row -->
    <div class="stats-row">
        <table>
            <tr>
                <td class="stat-cell">
                    <div class="stat-value">{{ number_format($stats['total_parcelas']) }}</div>
                    <div class="stat-label">Parcelas</div>
                </td>
                <td class="stat-cell">
                    <div class="stat-value">{{ number_format($stats['total_extension'], 2) }}</div>
                    <div class="stat-label">Hectáreas</div>
                </td>
                <td class="stat-cell">
                    <div class="stat-value">{{ number_format($stats['total_arboles']) }}</div>
                    <div class="stat-label">Árboles</div>
                </td>
                <td class="stat-cell">
                    <div class="stat-value">{{ number_format($stats['total_trozas']) }}</div>
                    <div class="stat-label">Trozas</div>
                </td>
                <td class="stat-cell highlight">
                    <div class="stat-value">{{ number_format($stats['total_volumen'], 2) }}</div>
                    <div class="stat-label">Volumen (m³)</div>
                </td>
                <td class="stat-cell highlight">
                    <div class="stat-value">{{ number_format($stats['total_biomasa'], 2) }}</div>
                    <div class="stat-label">Biomasa (ton)</div>
                </td>
                <td class="stat-cell highlight">
                    <div class="stat-value">{{ number_format($stats['total_carbono'], 2) }}</div>
                    <div class="stat-label">Carbono (ton)</div>
                </td>
            </tr>
        </table>
    </div>
    
    <!-- Parcelas Table -->
    <h3 class="section-title">Detalle de Parcelas</h3>
    @if($parcelas->count() > 0)
    <table class="data-table">
        <thead>
            <tr>
                <th style="width: 25%;">Parcela</th>
                <th style="width: 20%;">Ubicación</th>
                <th style="width: 10%;">Extensión</th>
                <th style="width: 8%;">Árboles</th>
                <th style="width: 8%;">Trozas</th>
                <th style="width: 10%;">Volumen</th>
                <th style="width: 10%;">Biomasa</th>
                <th style="width: 9%;">Carbono</th>
            </tr>
        </thead>
        <tbody>
            @foreach($parcelas as $parcela)
            @php
                $volParcela = 0;
                $bioParcela = 0;
                $carParcela = 0;
                
                foreach($parcela->arboles as $arbol) {
                    foreach($arbol->estimaciones as $est) {
                        $tipo = $est->tipoEstimacion->desc_estimacion ?? '';
                        if(stripos($tipo, 'volumen') !== false) $volParcela += $est->calculo;
                        if(stripos($tipo, 'biomasa') !== false) $bioParcela += $est->calculo;
                        if(stripos($tipo, 'carbono') !== false) $carParcela += $est->calculo;
                    }
                }
                
                foreach($parcela->trozas as $troza) {
                    foreach($troza->estimaciones as $est) {
                        $tipo = $est->tipoEstimacion->desc_estimacion ?? '';
                        if(stripos($tipo, 'volumen') !== false) $volParcela += $est->calculo;
                    }
                }
            @endphp
            <tr>
                <td class="parcela-name-cell">{{ $parcela->nom_parcela }}</td>
                <td class="location-cell">{{ $parcela->ubicacion ?? 'N/A' }}</td>
                <td class="number-cell">{{ number_format($parcela->extension, 2) }} ha</td>
                <td class="number-cell">{{ $parcela->arboles_count }}</td>
                <td class="number-cell">{{ $parcela->trozas_count }}</td>
                <td class="number-cell">{{ number_format($volParcela, 2) }} m³</td>
                <td class="number-cell">{{ number_format($bioParcela, 2) }} ton</td>
                <td class="number-cell">{{ number_format($carParcela, 2) }} ton</td>
            </tr>
            @endforeach
        </tbody>
    </table>
    @else
    <p class="empty-msg">No hay parcelas registradas</p>
    @endif
    
    <!-- Summary -->
    <div class="summary-box">
        <table>
            <tr>
                <td style="width: 50%;">
                    <h3>Resumen de Producción</h3>
                    <table>
                        <tr>
                            <td class="summary-label">Total Parcelas</td>
                            <td class="summary-value">{{ number_format($stats['total_parcelas']) }}</td>
                        </tr>
                        <tr>
                            <td class="summary-label">Extensión Total</td>
                            <td class="summary-value">{{ number_format($stats['total_extension'], 2) }} hectáreas</td>
                        </tr>
                        <tr>
                            <td class="summary-label">Árboles Registrados</td>
                            <td class="summary-value">{{ number_format($stats['total_arboles']) }}</td>
                        </tr>
                        <tr>
                            <td class="summary-label">Trozas Registradas</td>
                            <td class="summary-value">{{ number_format($stats['total_trozas']) }}</td>
                        </tr>
                    </table>
                </td>
                <td style="width: 50%;">
                    <h3>Estimaciones Totales</h3>
                    <table>
                        <tr>
                            <td class="summary-label">Volumen Total</td>
                            <td class="summary-value">{{ number_format($stats['total_volumen'], 2) }} m³</td>
                        </tr>
                        <tr>
                            <td class="summary-label">Biomasa Total</td>
                            <td class="summary-value">{{ number_format($stats['total_biomasa'], 2) }} toneladas</td>
                        </tr>
                        <tr>
                            <td class="summary-label">Carbono Capturado</td>
                            <td class="summary-value">{{ number_format($stats['total_carbono'], 2) }} toneladas</td>
                        </tr>
                        <tr>
                            <td class="summary-label">Promedio Vol/Parcela</td>
                            <td class="summary-value">{{ $stats['total_parcelas'] > 0 ? number_format($stats['total_volumen'] / $stats['total_parcelas'], 2) : '0.00' }} m³</td>
                        </tr>
                    </table>
                </td>
            </tr>
        </table>
    </div>
    
    <!-- Footer -->
    <div class="footer">
        <table>
            <tr>
                <td>WoodWise - Sistema de Gestión Forestal</td>
                <td style="text-align: center;">Este documento es de carácter informativo</td>
                <td style="text-align: right;">Página 1</td>
            </tr>
        </table>
    </div>
</body>
</html>
