<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="utf-8">
    <meta http-equiv="Content-Type" content="text/html; charset=utf-8"/>
    <title>Reporte Técnico Forestal - {{ $parcela->nom_parcela }}</title>
    <style>
        /* ============================================
           ESTILOS CORPORATIVOS WOODWISE
           Diseño Profesional para Reportes Técnicos
           ============================================ */
        
        @page {
            margin: 15mm 12mm 20mm 12mm;
        }
        
        * {
            margin: 0;
            padding: 0;
            box-sizing: border-box;
        }
        
        body {
            font-family: 'DejaVu Sans', Arial, sans-serif;
            font-size: 9pt;
            line-height: 1.5;
            color: #2c3e50;
            background: #fff;
        }

        /* ============================================
           ENCABEZADO CORPORATIVO
           ============================================ */
        .corporate-header {
            width: 100%;
            margin-bottom: 20px;
            border-bottom: 3px solid #1a5f2a;
            padding-bottom: 15px;
        }
        
        .header-table {
            width: 100%;
            border-collapse: collapse;
        }
        
        .logo-cell {
            width: 80px;
            vertical-align: middle;
        }
        
        .logo-container {
            width: 70px;
            height: 70px;
            background-color: #1a5f2a;
            border-radius: 12px;
            text-align: center;
            line-height: 70px;
        }
        
        .logo-icon {
            font-size: 32pt;
            color: white;
        }
        
        .brand-cell {
            vertical-align: middle;
            padding-left: 15px;
        }
        
        .brand-name {
            font-size: 22pt;
            font-weight: bold;
            color: #1a5f2a;
            margin: 0;
            letter-spacing: 1px;
        }
        
        .brand-tagline {
            font-size: 9pt;
            color: #7f8c8d;
            margin-top: 2px;
            font-style: italic;
        }
        
        .doc-info-cell {
            text-align: right;
            vertical-align: middle;
        }
        
        .doc-type {
            background: #1a5f2a;
            color: white;
            padding: 8px 15px;
            border-radius: 5px;
            font-size: 10pt;
            font-weight: bold;
            display: inline-block;
            margin-bottom: 5px;
        }
        
        .doc-date {
            font-size: 8pt;
            color: #7f8c8d;
        }

        /* ============================================
           TÍTULO DEL REPORTE
           ============================================ */
        .report-title-section {
            background-color: #1a5f2a;
            color: #ffffff;
            padding: 20px 25px;
            border-radius: 10px;
            margin-bottom: 20px;
        }
        
        .report-title-section::before {
            content: '';
            position: absolute;
            top: 0;
            right: 0;
            width: 150px;
            height: 100%;
            background: rgba(255,255,255,0.1);
            transform: skewX(-20deg);
        }
        
        .report-main-title {
            font-size: 16pt;
            font-weight: bold;
            margin-bottom: 5px;
            position: relative;
        }
        
        .report-subtitle {
            font-size: 12pt;
            opacity: 0.95;
            position: relative;
        }
        
        .report-meta-row {
            margin-top: 10px;
            font-size: 8pt;
            opacity: 0.85;
            position: relative;
        }

        /* ============================================
           RESUMEN EJECUTIVO
           ============================================ */
        .executive-summary {
            margin-bottom: 20px;
        }
        
        .summary-grid {
            width: 100%;
            border-collapse: separate;
            border-spacing: 8px;
        }
        
        .summary-card {
            width: 25%;
            background: #f8f9fa;
            border-radius: 8px;
            padding: 12px;
            text-align: center;
            border-left: 4px solid;
            vertical-align: top;
        }
        
        .summary-card.volumen { border-left-color: #1a5f2a; background-color: #e8f5e9; }
        .summary-card.biomasa { border-left-color: #4caf50; background-color: #e8f5e9; }
        .summary-card.carbono { border-left-color: #2196f3; background-color: #e3f2fd; }
        .summary-card.registros { border-left-color: #ff9800; background-color: #fff3e0; }
        
        .summary-value {
            font-size: 18pt;
            font-weight: bold;
            color: #1a3a16;
            line-height: 1.2;
        }
        
        .summary-label {
            font-size: 7pt;
            color: #666;
            text-transform: uppercase;
            letter-spacing: 0.5px;
            margin-top: 3px;
        }
        
        .summary-icon {
            font-size: 10pt;
            margin-bottom: 5px;
        }

        /* ============================================
           SECCIONES
           ============================================ */
        .section {
            margin-bottom: 18px;
            page-break-inside: avoid;
        }
        
        .section-header {
            background-color: #1a5f2a;
            color: #ffffff;
            padding: 10px 15px;
            font-size: 10pt;
            font-weight: bold;
            border-radius: 6px 6px 0 0;
        }
        
        .section-header-icon {
            margin-right: 8px;
            font-size: 11pt;
        }
        
        .section-content {
            border: 1px solid #e0e0e0;
            border-top: none;
            padding: 15px;
            background: #fafafa;
            border-radius: 0 0 6px 6px;
        }

        /* ============================================
           INFORMACIÓN DE PARCELA
           ============================================ */
        .info-table {
            width: 100%;
            border-collapse: collapse;
        }
        
        .info-table td {
            padding: 8px 12px;
            border-bottom: 1px solid #eee;
            vertical-align: top;
        }
        
        .info-table tr:last-child td {
            border-bottom: none;
        }
        
        .info-label {
            width: 35%;
            font-weight: bold;
            color: #1a5f2a;
            background: #f5f7f5;
        }
        
        .info-value {
            color: #333;
        }

        /* ============================================
           TABLAS DE DATOS
           ============================================ */
        .data-table {
            width: 100%;
            border-collapse: collapse;
            font-size: 8pt;
        }
        
        .data-table th {
            background-color: #1a5f2a;
            color: #ffffff;
            padding: 10px 8px;
            text-align: left;
            font-weight: bold;
            border: 1px solid #145526;
            font-size: 8pt;
        }
        
        .data-table td {
            padding: 8px;
            border: 1px solid #ddd;
            background-color: #ffffff;
        }
        
        .data-table tr:nth-child(even) td {
            background-color: #f5f5f5;
        }
        
        .data-table tr:hover td {
            background: #f5fff5;
        }
        
        .data-table .number {
            text-align: right;
            font-family: 'DejaVu Sans Mono', monospace;
        }
        
        .data-table .center {
            text-align: center;
        }
        
        .total-row td {
            background-color: #e8f5e9 !important;
            font-weight: bold;
            border-top: 2px solid #1a5f2a;
            color: #1a3a16;
        }

        /* ============================================
           BADGES / ETIQUETAS
           ============================================ */
        .badge {
            display: inline-block;
            padding: 3px 8px;
            border-radius: 12px;
            font-size: 7pt;
            font-weight: bold;
        }
        
        .badge-success { background: #c8e6c9; color: #2e7d32; }
        .badge-info { background: #bbdefb; color: #1565c0; }
        .badge-warning { background: #fff3e0; color: #ef6c00; }

        /* ============================================
           RESUMEN FINAL CONSOLIDADO
           ============================================ */
        .consolidated-summary {
            background-color: #1a5f2a;
            color: #ffffff;
            padding: 20px;
            border-radius: 10px;
            margin-top: 20px;
        }
        
        .consolidated-title {
            font-size: 12pt;
            font-weight: bold;
            margin-bottom: 15px;
            text-align: center;
            border-bottom: 1px solid rgba(255,255,255,0.3);
            padding-bottom: 10px;
        }
        
        .consolidated-grid {
            width: 100%;
            border-collapse: collapse;
        }
        
        .consolidated-grid td {
            padding: 8px 15px;
            text-align: center;
            width: 25%;
        }
        
        .consolidated-value {
            font-size: 16pt;
            font-weight: bold;
        }
        
        .consolidated-label {
            font-size: 8pt;
            opacity: 0.9;
            margin-top: 3px;
        }

        /* ============================================
           ÁREA DE FIRMAS
           ============================================ */
        .signatures-section {
            margin-top: 40px;
            page-break-inside: avoid;
        }
        
        .signatures-table {
            width: 100%;
            border-collapse: collapse;
        }
        
        .signature-cell {
            width: 45%;
            text-align: center;
            padding: 20px 30px;
            vertical-align: bottom;
        }
        
        .signature-line {
            border-top: 1px solid #333;
            padding-top: 8px;
            margin-top: 50px;
        }
        
        .signature-name {
            font-weight: bold;
            color: #1a5f2a;
            font-size: 9pt;
        }
        
        .signature-title {
            font-size: 8pt;
            color: #666;
            margin-top: 3px;
        }

        /* ============================================
           PIE DE PÁGINA
           ============================================ */
        .document-footer {
            position: fixed;
            bottom: 0;
            left: 0;
            right: 0;
            background-color: #1a5f2a;
            color: #ffffff;
            padding: 10px 20px;
            font-size: 7pt;
            text-align: center;
        }
        
        .footer-content {
            display: table;
            width: 100%;
        }
        
        .footer-left {
            display: table-cell;
            text-align: left;
            width: 33%;
        }
        
        .footer-center {
            display: table-cell;
            text-align: center;
            width: 34%;
        }
        
        .footer-right {
            display: table-cell;
            text-align: right;
            width: 33%;
        }

        /* ============================================
           MARCA DE AGUA SUTIL
           ============================================ */
        .watermark {
            position: fixed;
            top: 45%;
            left: 50%;
            transform: translate(-50%, -50%) rotate(-35deg);
            font-size: 80pt;
            color: rgba(26, 95, 42, 0.03);
            font-weight: bold;
            z-index: -1;
            white-space: nowrap;
            letter-spacing: 10px;
        }

        /* ============================================
           UTILIDADES
           ============================================ */
        .text-right { text-align: right; }
        .text-center { text-align: center; }
        .text-bold { font-weight: bold; }
        .text-green { color: #1a5f2a; }
        .text-muted { color: #999; font-size: 8pt; }
        .mb-5 { margin-bottom: 5px; }
        .mb-10 { margin-bottom: 10px; }
        .mb-20 { margin-bottom: 20px; }
        .page-break { page-break-after: always; }
    </style>
</head>
<body>
    <div class="watermark">WOODWISE</div>

    <!-- ============================================
         ENCABEZADO CORPORATIVO
         ============================================ -->
    <div class="corporate-header">
        <table class="header-table">
            <tr>
                <td class="logo-cell">
                    <div class="logo-container">
                        <span class="logo-icon">🌲</span>
                    </div>
                </td>
                <td class="brand-cell">
                    <div class="brand-name">WOODWISE</div>
                    <div class="brand-tagline">Sistema de Gestión Forestal Inteligente</div>
                </td>
                <td class="doc-info-cell">
                    <div class="doc-type">REPORTE TÉCNICO</div>
                    <div class="doc-date">
                        Generado: {{ $fecha_generacion->format('d/m/Y') }}<br>
                        Hora: {{ $fecha_generacion->format('H:i:s') }}
                    </div>
                </td>
            </tr>
        </table>
    </div>

    <!-- ============================================
         TÍTULO DEL REPORTE
         ============================================ -->
    <div class="report-title-section">
        <div class="report-main-title">INFORME TÉCNICO DE INVENTARIO FORESTAL</div>
        <div class="report-subtitle">{{ $parcela->nom_parcela }}</div>
        <div class="report-meta-row">
            <strong>Técnico Responsable:</strong> {{ $tecnico->persona->nom ?? 'N/A' }} {{ $tecnico->persona->ap ?? '' }} | 
            <strong>Clave:</strong> {{ $tecnico->clave_tecnico ?? 'N/A' }} |
            <strong>Ubicación:</strong> {{ $parcela->ubicacion }}
        </div>
    </div>

    <!-- ============================================
         RESUMEN EJECUTIVO
         ============================================ -->
    <div class="executive-summary">
        <table class="summary-grid">
            <tr>
                <td class="summary-card volumen">
                    <div class="summary-icon">📦</div>
                    <div class="summary-value">{{ number_format($totales['volumen'], 2) }}</div>
                    <div class="summary-label">Volumen Total (m³)</div>
                </td>
                <td class="summary-card biomasa">
                    <div class="summary-icon">🌿</div>
                    <div class="summary-value">{{ number_format($totales['biomasa'], 2) }}</div>
                    <div class="summary-label">Biomasa (ton)</div>
                </td>
                <td class="summary-card carbono">
                    <div class="summary-icon">💨</div>
                    <div class="summary-value">{{ number_format($totales['carbono'], 2) }}</div>
                    <div class="summary-label">Carbono (ton)</div>
                </td>
                <td class="summary-card registros">
                    <div class="summary-icon">📊</div>
                    <div class="summary-value">{{ $totales['estimaciones'] }}</div>
                    <div class="summary-label">Estimaciones</div>
                </td>
            </tr>
        </table>
    </div>

    <!-- ============================================
         INFORMACIÓN DE LA PARCELA
         ============================================ -->
    <div class="section">
        <div class="section-header">
            <span class="section-header-icon">📍</span>
            INFORMACIÓN DE LA PARCELA
        </div>
        <div class="section-content">
            <table class="info-table">
                <tr>
                    <td class="info-label">Nombre de Parcela</td>
                    <td class="info-value">{{ $parcela->nom_parcela }}</td>
                </tr>
                <tr>
                    <td class="info-label">Ubicación Geográfica</td>
                    <td class="info-value">{{ $parcela->ubicacion }}</td>
                </tr>
                <tr>
                    <td class="info-label">Dirección</td>
                    <td class="info-value">{{ $parcela->direccion ?? 'No especificada' }}</td>
                </tr>
                <tr>
                    <td class="info-label">Código Postal</td>
                    <td class="info-value">{{ $parcela->CP ?? 'N/A' }}</td>
                </tr>
                <tr>
                    <td class="info-label">Extensión</td>
                    <td class="info-value"><strong>{{ $parcela->extension }} hectáreas</strong></td>
                </tr>
                <tr>
                    <td class="info-label">Productor/Propietario</td>
                    <td class="info-value">{{ $parcela->productor->persona->nom ?? 'N/A' }} {{ $parcela->productor->persona->ap ?? '' }} {{ $parcela->productor->persona->am ?? '' }}</td>
                </tr>
            </table>
        </div>
    </div>

    <!-- ============================================
         INVENTARIO DE TROZAS
         ============================================ -->
    @if($parcela->trozas->count() > 0)
    <div class="section">
        <div class="section-header">
            <span class="section-header-icon">🪵</span>
            INVENTARIO DE TROZAS ({{ $parcela->trozas->count() }} registros)
        </div>
        <div class="section-content" style="padding: 0;">
            <table class="data-table">
                <thead>
                    <tr>
                        <th class="center" style="width: 8%;">ID</th>
                        <th style="width: 28%;">Especie</th>
                        <th class="center" style="width: 12%;">Longitud (m)</th>
                        <th class="center" style="width: 12%;">Diám. (m)</th>
                        <th class="center" style="width: 12%;">D. Medio (m)</th>
                        <th class="center" style="width: 12%;">D. Otro Ext.</th>
                        <th class="center" style="width: 12%;">Densidad</th>
                    </tr>
                </thead>
                <tbody>
                    @foreach($parcela->trozas as $troza)
                    <tr>
                        <td class="center">{{ $troza->id_troza }}</td>
                        <td><em>{{ $troza->especie->nom_cientifico ?? 'N/A' }}</em></td>
                        <td class="number">{{ number_format($troza->longitud, 2) }}</td>
                        <td class="number">{{ number_format($troza->diametro, 3) }}</td>
                        <td class="number">{{ number_format($troza->diametro_medio ?? 0, 3) }}</td>
                        <td class="number">{{ number_format($troza->diametro_otro_extremo ?? 0, 3) }}</td>
                        <td class="number">{{ number_format($troza->densidad, 2) }}</td>
                    </tr>
                    @endforeach
                </tbody>
            </table>
        </div>
    </div>

    <!-- Estimaciones de Trozas -->
    @php
        $estimacionesTrozas = $parcela->trozas->flatMap->estimaciones;
    @endphp
    @if($estimacionesTrozas->count() > 0)
    <div class="section">
        <div class="section-header">
            <span class="section-header-icon">📊</span>
            ESTIMACIONES VOLUMÉTRICAS - TROZAS ({{ $estimacionesTrozas->count() }} cálculos)
        </div>
        <div class="section-content" style="padding: 0;">
            <table class="data-table">
                <thead>
                    <tr>
                        <th class="center" style="width: 8%;">Troza</th>
                        <th style="width: 32%;">Fórmula Aplicada</th>
                        <th class="center" style="width: 18%;">Volumen (m³)</th>
                        <th class="center" style="width: 18%;">Biomasa (ton)</th>
                        <th class="center" style="width: 18%;">Carbono (ton)</th>
                    </tr>
                </thead>
                <tbody>
                    @foreach($estimacionesTrozas as $est)
                    <tr>
                        <td class="center">#{{ $est->id_troza }}</td>
                        <td>{{ $est->formula->nom_formula ?? 'N/A' }}</td>
                        <td class="number">{{ number_format($est->calculo, 4) }}</td>
                        <td class="number">{{ number_format($est->biomasa, 4) }}</td>
                        <td class="number">{{ number_format($est->carbono, 4) }}</td>
                    </tr>
                    @endforeach
                    <tr class="total-row">
                        <td colspan="2"><strong>SUBTOTAL TROZAS</strong></td>
                        <td class="number">{{ number_format($estadisticas['volumen_trozas'], 4) }}</td>
                        <td class="number">{{ number_format($estadisticas['biomasa_trozas'], 4) }}</td>
                        <td class="number">{{ number_format($estadisticas['carbono_trozas'], 4) }}</td>
                    </tr>
                </tbody>
            </table>
        </div>
    </div>
    @endif
    @endif

    <!-- ============================================
         INVENTARIO DE ÁRBOLES
         ============================================ -->
    @if($parcela->arboles->count() > 0)
    <div class="section">
        <div class="section-header">
            <span class="section-header-icon">🌳</span>
            INVENTARIO DE ÁRBOLES EN PIE ({{ $parcela->arboles->count() }} registros)
        </div>
        <div class="section-content" style="padding: 0;">
            <table class="data-table">
                <thead>
                    <tr>
                        <th class="center" style="width: 10%;">ID</th>
                        <th style="width: 45%;">Especie (Nombre Científico)</th>
                        <th class="center" style="width: 20%;">Altura Total (m)</th>
                        <th class="center" style="width: 20%;">DAP (m)</th>
                    </tr>
                </thead>
                <tbody>
                    @foreach($parcela->arboles as $arbol)
                    <tr>
                        <td class="center">{{ $arbol->id_arbol }}</td>
                        <td><em>{{ $arbol->especie->nom_cientifico ?? 'Sin identificar' }}</em></td>
                        <td class="number">{{ number_format($arbol->altura_total, 2) }}</td>
                        <td class="number">{{ number_format($arbol->diametro_pecho, 3) }}</td>
                    </tr>
                    @endforeach
                    <tr class="total-row">
                        <td colspan="2"><strong>PROMEDIOS DEL INVENTARIO</strong></td>
                        <td class="number">{{ number_format($estadisticas['altura_promedio'], 2) }} m</td>
                        <td class="number">{{ number_format($estadisticas['dap_promedio'], 3) }} m</td>
                    </tr>
                </tbody>
            </table>
        </div>
    </div>

    <!-- Estimaciones de Árboles -->
    @php
        $estimacionesArboles = $parcela->arboles->flatMap->estimaciones1;
    @endphp
    @if($estimacionesArboles->count() > 0)
    <div class="section">
        <div class="section-header">
            <span class="section-header-icon">📊</span>
            ESTIMACIONES DE BIOMASA Y CARBONO - ÁRBOLES ({{ $estimacionesArboles->count() }} cálculos)
        </div>
        <div class="section-content" style="padding: 0;">
            <table class="data-table">
                <thead>
                    <tr>
                        <th class="center" style="width: 8%;">Árbol</th>
                        <th style="width: 20%;">Tipo Est.</th>
                        <th style="width: 25%;">Fórmula</th>
                        <th class="center" style="width: 14%;">Cálculo</th>
                        <th class="center" style="width: 14%;">Biomasa</th>
                        <th class="center" style="width: 14%;">Carbono</th>
                    </tr>
                </thead>
                <tbody>
                    @foreach($estimacionesArboles as $est)
                    <tr>
                        <td class="center">#{{ $est->id_arbol }}</td>
                        <td>
                            <span class="badge badge-success">{{ $est->tipoEstimacion->desc_estimacion ?? 'N/A' }}</span>
                        </td>
                        <td style="font-size: 7pt;">{{ $est->formula->nom_formula ?? 'Automático' }}</td>
                        <td class="number">{{ number_format($est->calculo, 4) }}</td>
                        <td class="number">{{ number_format($est->biomasa, 4) }}</td>
                        <td class="number">{{ number_format($est->carbono, 4) }}</td>
                    </tr>
                    @endforeach
                    <tr class="total-row">
                        <td colspan="3"><strong>SUBTOTAL ÁRBOLES</strong></td>
                        <td class="number">{{ number_format($estadisticas['volumen_arboles'], 4) }}</td>
                        <td class="number">{{ number_format($estadisticas['biomasa_arboles'], 4) }}</td>
                        <td class="number">{{ number_format($estadisticas['carbono_arboles'], 4) }}</td>
                    </tr>
                </tbody>
            </table>
        </div>
    </div>
    @endif
    @endif

    <!-- ============================================
         RESUMEN CONSOLIDADO
         ============================================ -->
    <div class="consolidated-summary">
        <div class="consolidated-title">RESUMEN CONSOLIDADO DEL INVENTARIO</div>
        <table class="consolidated-grid">
            <tr>
                <td>
                    <div class="consolidated-value">{{ $totales['trozas'] + $totales['arboles'] }}</div>
                    <div class="consolidated-label">Total Registros</div>
                </td>
                <td>
                    <div class="consolidated-value">{{ number_format($totales['volumen'], 2) }} m³</div>
                    <div class="consolidated-label">Volumen Maderable</div>
                </td>
                <td>
                    <div class="consolidated-value">{{ number_format($totales['biomasa'], 2) }} ton</div>
                    <div class="consolidated-label">Biomasa Total</div>
                </td>
                <td>
                    <div class="consolidated-value">{{ number_format($totales['carbono'], 2) }} ton</div>
                    <div class="consolidated-label">Carbono Capturado</div>
                </td>
            </tr>
        </table>
    </div>

    <!-- ============================================
         DESGLOSE POR CATEGORÍA
         ============================================ -->
    <div class="section" style="margin-top: 20px;">
        <div class="section-header">
            <span class="section-header-icon">📈</span>
            DESGLOSE COMPARATIVO
        </div>
        <div class="section-content">
            <table class="data-table">
                <thead>
                    <tr>
                        <th style="width: 30%;">Concepto</th>
                        <th class="center" style="width: 20%;">Trozas</th>
                        <th class="center" style="width: 20%;">Árboles</th>
                        <th class="center" style="width: 25%;">TOTAL GENERAL</th>
                    </tr>
                </thead>
                <tbody>
                    <tr>
                        <td><strong>Cantidad de Registros</strong></td>
                        <td class="number">{{ $totales['trozas'] }}</td>
                        <td class="number">{{ $totales['arboles'] }}</td>
                        <td class="number text-bold">{{ $totales['trozas'] + $totales['arboles'] }}</td>
                    </tr>
                    <tr>
                        <td><strong>Volumen Maderable (m³)</strong></td>
                        <td class="number">{{ number_format($estadisticas['volumen_trozas'], 4) }}</td>
                        <td class="number">{{ number_format($estadisticas['volumen_arboles'], 4) }}</td>
                        <td class="number text-bold text-green">{{ number_format($totales['volumen'], 4) }}</td>
                    </tr>
                    <tr>
                        <td><strong>Biomasa (toneladas)</strong></td>
                        <td class="number">{{ number_format($estadisticas['biomasa_trozas'], 4) }}</td>
                        <td class="number">{{ number_format($estadisticas['biomasa_arboles'], 4) }}</td>
                        <td class="number text-bold text-green">{{ number_format($totales['biomasa'], 4) }}</td>
                    </tr>
                    <tr>
                        <td><strong>Carbono Almacenado (toneladas)</strong></td>
                        <td class="number">{{ number_format($estadisticas['carbono_trozas'], 4) }}</td>
                        <td class="number">{{ number_format($estadisticas['carbono_arboles'], 4) }}</td>
                        <td class="number text-bold text-green">{{ number_format($totales['carbono'], 4) }}</td>
                    </tr>
                </tbody>
            </table>
        </div>
    </div>

    <!-- ============================================
         ÁREA DE FIRMAS
         ============================================ -->
    <div class="signatures-section">
        <table class="signatures-table">
            <tr>
                <td class="signature-cell">
                    <div class="signature-line">
                        <div class="signature-name">{{ $tecnico->persona->nom ?? 'N/A' }} {{ $tecnico->persona->ap ?? '' }}</div>
                        <div class="signature-title">Técnico Forestal Responsable</div>
                        <div class="signature-title">Clave: {{ $tecnico->clave_tecnico ?? 'N/A' }}</div>
                    </div>
                </td>
                <td style="width: 10%;"></td>
                <td class="signature-cell">
                    <div class="signature-line">
                        <div class="signature-name">Sello Institucional</div>
                        <div class="signature-title">Validación del Documento</div>
                        <div class="signature-title">&nbsp;</div>
                    </div>
                </td>
            </tr>
        </table>
    </div>

    <!-- ============================================
         PIE DE PÁGINA CORPORATIVO
         ============================================ -->
    <div class="document-footer">
        <div class="footer-content">
            <div class="footer-left">
                <strong>WoodWise</strong> © {{ date('Y') }}
            </div>
            <div class="footer-center">
                Sistema de Gestión Forestal - Documento Oficial
            </div>
            <div class="footer-right">
                Página 1 de 1
            </div>
        </div>
    </div>

</body>
</html>
