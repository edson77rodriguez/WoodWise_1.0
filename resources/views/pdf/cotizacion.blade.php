<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="utf-8">
    <meta http-equiv="Content-Type" content="text/html; charset=utf-8"/>
    <title>Cotización Comercial - {{ $cotizacion['parcela']['nom_parcela'] ?? 'SIGMAD' }}</title>
    <style>
        @page { margin: 16mm 12mm 18mm 12mm; }
        * { box-sizing: border-box; }
        body {
            font-family: 'DejaVu Sans', Arial, sans-serif;
            font-size: 9.2pt;
            color: #24313f;
            margin: 0;
            background: #ffffff;
        }
        .watermark {
            position: fixed;
            top: 45%; left: 50%;
            transform: translate(-50%, -50%) rotate(-32deg);
            font-size: 72pt;
            color: rgba(17, 94, 45, 0.04);
            font-weight: bold;
            z-index: -1;
            letter-spacing: 8px;
        }
        .header {
            width: 100%;
            border-bottom: 3px solid #115e2d;
            padding-bottom: 12px;
            margin-bottom: 16px;
        }
        .header table { width: 100%; border-collapse: collapse; }
        .logo-box {
            width: 74px; height: 74px;
            background: #115e2d;
            border-radius: 14px;
            text-align: center;
            line-height: 74px;
            overflow: hidden;
        }
        .logo-box img { width: 54px; height: 54px; margin-top: 10px; }
        .brand-title { font-size: 24pt; font-weight: bold; color: #115e2d; margin: 0; }
        .brand-subtitle { font-size: 9pt; color: #6b7280; margin-top: 2px; }
        .meta { text-align: right; }
        .doc-badge {
            display: inline-block;
            background: #115e2d;
            color: #fff;
            padding: 8px 14px;
            border-radius: 8px;
            font-weight: bold;
            font-size: 10pt;
            margin-bottom: 6px;
        }
        .doc-date { font-size: 8pt; color: #6b7280; }
        .hero {
            background: linear-gradient(135deg, #0f5132 0%, #115e2d 55%, #1f7a3f 100%);
            color: #fff;
            padding: 18px 20px;
            border-radius: 12px;
            margin-bottom: 16px;
        }
        .hero h1 { margin: 0 0 4px 0; font-size: 17pt; }
        .hero p { margin: 0; font-size: 10pt; opacity: .95; line-height: 1.45; }
        .cards { width: 100%; border-collapse: separate; border-spacing: 8px; margin-bottom: 16px; }
        .card {
            width: 25%;
            padding: 12px;
            border-radius: 10px;
            background: #f8fafc;
            border-left: 4px solid #115e2d;
            vertical-align: top;
            text-align: center;
        }
        .card .label { font-size: 7pt; text-transform: uppercase; letter-spacing: .7px; color: #64748b; }
        .card .value { font-size: 18pt; font-weight: bold; color: #0f172a; line-height: 1.1; margin-top: 4px; }
        .card .hint { font-size: 8pt; color: #64748b; margin-top: 2px; }
        .section { margin-bottom: 16px; page-break-inside: avoid; }
        .section-title {
            background: #115e2d;
            color: #fff;
            padding: 9px 12px;
            border-radius: 8px 8px 0 0;
            font-weight: bold;
            font-size: 10pt;
        }
        .section-body {
            border: 1px solid #dbe4dd;
            border-top: none;
            padding: 12px;
            background: #fbfdfb;
            border-radius: 0 0 8px 8px;
        }
        .info-table, .data-table { width: 100%; border-collapse: collapse; }
        .info-table td {
            padding: 8px 10px;
            border-bottom: 1px solid #edf2f7;
            vertical-align: top;
        }
        .info-label { width: 28%; font-weight: bold; color: #115e2d; background: #f2f7f3; }
        .data-table th, .data-table td { border: 1px solid #dbe4dd; padding: 7px 6px; }
        .data-table th { background: #115e2d; color: #fff; font-size: 8pt; }
        .data-table tr:nth-child(even) td { background: #f8faf8; }
        .number { text-align: right; font-family: 'DejaVu Sans Mono', monospace; }
        .center { text-align: center; }
        .total-row td {
            background: #e8f5e9 !important;
            font-weight: bold;
            color: #0f5132;
        }
        .summary-box {
            background: #115e2d;
            color: #fff;
            border-radius: 12px;
            padding: 16px;
            margin-top: 14px;
        }
        .summary-grid { width: 100%; border-collapse: collapse; }
        .summary-grid td { width: 25%; text-align: center; padding: 8px 10px; }
        .summary-value { font-size: 16pt; font-weight: bold; }
        .summary-label { font-size: 8pt; opacity: .9; margin-top: 3px; }
        .note {
            font-size: 8pt; color: #475569; background: #f8fafc;
            border: 1px dashed #cbd5e1; border-radius: 8px; padding: 10px; margin-top: 12px;
            line-height: 1.45;
        }
        .small-title {
            font-size: 9pt;
            font-weight: bold;
            color: #115e2d;
            margin: 0 0 6px 0;
        }
        .bullet-list {
            margin: 6px 0 0 18px;
            padding: 0;
        }
        .bullet-list li {
            margin: 0 0 4px 0;
            line-height: 1.45;
        }
        .footer {
            position: fixed;
            bottom: 0; left: 0; right: 0;
            background: #115e2d;
            color: #fff;
            padding: 8px 16px;
            font-size: 7pt;
            text-align: center;
        }
    </style>
</head>
<body>
    <div class="watermark">SIGMAD</div>

    <div class="header">
        <table>
            <tr>
                <td style="width: 90px;">
                    <div class="logo-box">
                        @if(!empty($logo))
                            <img src="{{ $logo }}" alt="SIGMAD">
                        @else
                            <span style="color:#fff;font-size:28pt;">🌲</span>
                        @endif
                    </div>
                </td>
                <td>
                    <div class="brand-title">SIGMAD</div>
                    <div class="brand-subtitle">Sistema Inteligente de Gestión Maderable</div>
                </td>
                <td class="meta">
                    <div class="doc-badge">COTIZACIÓN COMERCIAL</div><br>
                    <div class="doc-date">Generado: {{ $fecha }}</div>
                </td>
            </tr>
        </table>
    </div>

    <div class="hero">
        <h1>Cotización formal de mercado forestal</h1>
        <p>
            {{ $cotizacion['parcela']['nom_parcela'] ?? 'Parcela sin nombre' }}
            | CP {{ $cotizacion['parcela']['CP'] ?? 'N/A' }}
            | Estado aplicado: {{ $cotizacion['estado_mercado'] ?? 'N/A' }}
        </p>
    </div>

    <table class="cards">
        <tr>
            <td class="card">
                <div class="label">Total trozas</div>
                <div class="value">{{ $cotizacion['gran_total_trozas'] ?? 0 }}</div>
                <div class="hint">Registros considerados</div>
            </td>
            <td class="card" style="border-left-color:#1d4ed8;">
                <div class="label">Volumen total</div>
                <div class="value">{{ number_format($cotizacion['gran_total_volumen_m3'] ?? 0, 4) }}</div>
                <div class="hint">m³ estimados</div>
            </td>
            <td class="card" style="border-left-color:#b45309;">
                <div class="label">Estado mercado</div>
                <div class="value" style="font-size:12pt;">{{ $cotizacion['estado_mercado'] ?? 'N/A' }}</div>
                <div class="hint">Precio regional aplicado</div>
            </td>
            <td class="card" style="border-left-color:#7c3aed;">
                <div class="label">Valor estimado</div>
                <div class="value">${{ number_format($cotizacion['gran_total_estimado_mxn'] ?? 0, 2) }}</div>
                <div class="hint">MXN</div>
            </td>
        </tr>
    </table>

    <div class="section">
        <div class="section-title">Datos de la parcela</div>
        <div class="section-body">
            <table class="info-table">
                <tr>
                    <td class="info-label">Parcela</td>
                    <td>{{ $cotizacion['parcela']['nom_parcela'] ?? 'N/A' }}</td>
                    <td class="info-label">Código Postal</td>
                    <td>{{ $cotizacion['parcela']['CP'] ?? 'N/A' }}</td>
                </tr>
                <tr>
                    <td class="info-label">Estado aplicado</td>
                    <td>{{ $cotizacion['estado_mercado'] ?? 'N/A' }}</td>
                    <td class="info-label">Fecha</td>
                    <td>{{ $fecha }}</td>
                </tr>
            </table>
        </div>
    </div>

    <div class="section">
        <div class="section-title">Desglose por especie</div>
        <div class="section-body" style="padding:0;">
            <table class="data-table">
                <thead>
                    <tr>
                        <th style="width: 22%;">Especie</th>
                        <th class="center" style="width: 10%;">Cant.</th>
                        <th class="center" style="width: 16%;">Volumen (m³)</th>
                        <th class="center" style="width: 16%;">Precio unitario</th>
                        <th class="center" style="width: 18%;">Estado precio</th>
                        <th class="center" style="width: 18%;">Subtotal</th>
                    </tr>
                </thead>
                <tbody>
                    @foreach(($cotizacion['detalles_por_especie'] ?? []) as $detalle)
                        <tr>
                            <td>{{ $detalle['especie'] ?? 'N/A' }}</td>
                            <td class="center">{{ $detalle['cantidad'] ?? 0 }}</td>
                            <td class="number">{{ number_format($detalle['volumen_m3'] ?? 0, 4) }}</td>
                            <td class="number">${{ number_format($detalle['precio_unitario'] ?? 0, 2) }}</td>
                            <td class="center">{{ $detalle['estado'] ?? 'N/A' }}</td>
                            <td class="number">${{ number_format($detalle['subtotal'] ?? 0, 2) }}</td>
                        </tr>
                    @endforeach
                    <tr class="total-row">
                        <td colspan="2"><strong>TOTAL GENERAL</strong></td>
                        <td class="number"><strong>{{ number_format($cotizacion['gran_total_volumen_m3'] ?? 0, 4) }}</strong></td>
                        <td class="center">—</td>
                        <td class="center">—</td>
                        <td class="number"><strong>${{ number_format($cotizacion['gran_total_estimado_mxn'] ?? 0, 2) }}</strong></td>
                    </tr>
                </tbody>
            </table>
        </div>
    </div>

    <div class="summary-box">
        <table class="summary-grid">
            <tr>
                <td>
                    <div class="summary-value">{{ $cotizacion['gran_total_trozas'] ?? 0 }}</div>
                    <div class="summary-label">Trozas cotizadas</div>
                </td>
                <td>
                    <div class="summary-value">{{ number_format($cotizacion['gran_total_volumen_m3'] ?? 0, 4) }}</div>
                    <div class="summary-label">m³ totales</div>
                </td>
                <td>
                    <div class="summary-value">{{ $cotizacion['estado_mercado'] ?? 'N/A' }}</div>
                    <div class="summary-label">Mercado aplicado</div>
                </td>
                <td>
                    <div class="summary-value">${{ number_format($cotizacion['gran_total_estimado_mxn'] ?? 0, 2) }}</div>
                    <div class="summary-label">Valor comercial MXN</div>
                </td>
            </tr>
        </table>
    </div>

    <div class="note">
        <div class="small-title">Lectura rápida</div>
        Este documento estima el valor comercial a partir de la parcela, su código postal y el precio regional más cercano disponible para cada especie.
        Si cambian los precios de mercado o el inventario, el valor puede variar.

        <div class="small-title" style="margin-top:10px;">Qué incluye este cálculo</div>
        <ul class="bullet-list">
            <li>Solo se consideran las trozas registradas en la parcela seleccionada.</li>
            <li>El precio aplicado depende del estado o región detectada por código postal.</li>
            <li>El valor mostrado es orientativo y sirve como base comercial o técnica.</li>
        </ul>
    </div>

    <div class="footer">
        SIGMAD - Sistema Inteligente de Gestión Maderable | Documento comercial formal
    </div>
</body>
</html>