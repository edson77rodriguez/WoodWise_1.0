<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="utf-8">
    <meta http-equiv="Content-Type" content="text/html; charset=utf-8"/>
    <title>Reporte de Impacto Ambiental - SIGMAD</title>
    <style>
        @page { margin: 15mm 12mm 18mm 12mm; }
        * { box-sizing: border-box; }
        body {
            margin: 0;
            font-family: 'DejaVu Sans', Arial, sans-serif;
            font-size: 9.5pt;
            color: #1f2937;
            background: #ffffff;
        }
        .watermark {
            position: fixed;
            top: 44%;
            left: 50%;
            transform: translate(-50%, -50%) rotate(-30deg);
            font-size: 68pt;
            font-weight: bold;
            letter-spacing: 8px;
            color: rgba(16, 95, 46, 0.04);
            z-index: -1;
        }
        .header {
            width: 100%;
            border-bottom: 3px solid #105f2e;
            padding-bottom: 12px;
            margin-bottom: 14px;
        }
        .header table { width: 100%; border-collapse: collapse; }
        .brand-title {
            font-size: 23pt;
            line-height: 1;
            color: #105f2e;
            font-weight: bold;
            margin: 0;
        }
        .brand-subtitle {
            margin-top: 3px;
            font-size: 8.8pt;
            color: #6b7280;
        }
        .seal {
            display: inline-block;
            background: #105f2e;
            color: #fff;
            padding: 8px 14px;
            border-radius: 9px;
            font-size: 9.5pt;
            font-weight: bold;
            letter-spacing: .3px;
        }
        .doc-date {
            margin-top: 7px;
            font-size: 8.2pt;
            color: #6b7280;
        }
        .hero {
            background: linear-gradient(135deg, #0f5132 0%, #105f2e 58%, #1f7a3f 100%);
            color: #fff;
            border-radius: 14px;
            padding: 18px 20px;
            margin-bottom: 14px;
        }
        .hero h1 {
            margin: 0 0 5px 0;
            font-size: 17pt;
            line-height: 1.15;
        }
        .hero p {
            margin: 0;
            font-size: 10pt;
            opacity: .96;
        }
        .grid {
            width: 100%;
            border-collapse: separate;
            border-spacing: 8px;
            margin-bottom: 12px;
        }
        .metric {
            width: 25%;
            background: #f8fafc;
            border-radius: 10px;
            border-left: 4px solid #105f2e;
            padding: 11px 10px;
            vertical-align: top;
            text-align: center;
        }
        .metric .label {
            font-size: 7pt;
            text-transform: uppercase;
            letter-spacing: .8px;
            color: #64748b;
            margin-bottom: 4px;
        }
        .metric .value {
            font-size: 17pt;
            line-height: 1.1;
            color: #0f172a;
            font-weight: bold;
        }
        .metric .hint {
            margin-top: 3px;
            font-size: 7.6pt;
            color: #64748b;
        }
        .section { margin-bottom: 13px; page-break-inside: avoid; }
        .section-title {
            background: #105f2e;
            color: #fff;
            padding: 9px 12px;
            border-radius: 9px 9px 0 0;
            font-size: 10pt;
            font-weight: bold;
            letter-spacing: .2px;
        }
        .section-body {
            border: 1px solid #dbe4dd;
            border-top: none;
            background: #fbfdfb;
            border-radius: 0 0 9px 9px;
            padding: 12px;
        }
        .info-table, .data-table { width: 100%; border-collapse: collapse; }
        .info-table td {
            padding: 8px 10px;
            border-bottom: 1px solid #edf2f7;
            vertical-align: top;
        }
        .info-label {
            width: 26%;
            background: #f2f7f3;
            color: #105f2e;
            font-weight: bold;
        }
        .tag {
            display: inline-block;
            background: #e8f5e9;
            color: #105f2e;
            border: 1px solid #cde7d2;
            border-radius: 999px;
            padding: 3px 9px;
            font-size: 8pt;
            margin-right: 4px;
            margin-bottom: 4px;
        }
        .data-table th, .data-table td {
            border: 1px solid #dbe4dd;
            padding: 7px 6px;
            vertical-align: top;
        }
        .data-table th {
            background: #105f2e;
            color: #fff;
            font-size: 8pt;
        }
        .data-table tr:nth-child(even) td { background: #f8faf8; }
        .number { text-align: right; font-family: 'DejaVu Sans Mono', monospace; }
        .center { text-align: center; }
        .total-row td {
            background: #e8f5e9 !important;
            color: #0f5132;
            font-weight: bold;
        }
        .alert-box {
            background: #fff7ed;
            border: 1px solid #f5d0a9;
            border-radius: 10px;
            padding: 10px 12px;
        }
        .alert-box ul, .list { margin: 6px 0 0 18px; padding: 0; }
        .alert-box li, .list li { margin-bottom: 4px; }
        .rec {
            margin-bottom: 10px;
            padding: 11px 12px;
            background: #ffffff;
            border: 1px solid #dbe4dd;
            border-left: 4px solid #105f2e;
            border-radius: 10px;
        }
        .rec h3 {
            margin: 0 0 4px 0;
            font-size: 11pt;
            color: #0f172a;
        }
        .rec-meta {
            font-size: 8pt;
            color: #64748b;
            margin-bottom: 6px;
        }
        .rec p { margin: 0 0 6px 0; }
        .review-box {
            background: linear-gradient(135deg, #ecfdf5 0%, #f8fafc 100%);
            border: 1px solid #cde7d2;
            border-radius: 12px;
            padding: 12px;
            margin-top: 4px;
        }
        .review-box strong { color: #105f2e; }
        .footer {
            position: fixed;
            bottom: 0;
            left: 0;
            right: 0;
            background: #105f2e;
            color: #fff;
            font-size: 7pt;
            text-align: center;
            padding: 8px 14px;
        }
        .spacer { height: 14px; }
    </style>
</head>
<body>
    <div class="watermark">SIGMAD</div>

    <div class="header">
        <table>
            <tr>
                <td style="width: 64%;">
                    <div class="brand-title">SIGMAD</div>
                    <div class="brand-subtitle">Sistema Inteligente de Gestión Maderable</div>
                </td>
                <td style="text-align: right;">
                    <div class="seal">REPORTE DE IMPACTO AMBIENTAL</div>
                    <div class="doc-date">Generado: {{ $fecha }}</div>
                </td>
            </tr>
        </table>
    </div>

    <div class="hero">
        <h1>Lectura estratégica del impacto forestal</h1>
        <p>
            {{ $impacto['usuario'] ?? ($persona->nom ?? 'Usuario') }}
            | Alcance: {{ $impacto['filtro']['modo'] ?? 'todas' }}
        </p>
    </div>

    <table class="grid">
        <tr>
            <td class="metric">
                <div class="label">Árboles</div>
                <div class="value">{{ $impacto['totales']['arboles'] ?? 0 }}</div>
                <div class="hint">individuos evaluados</div>
            </td>
            <td class="metric" style="border-left-color:#1d4ed8;">
                <div class="label">Trozas</div>
                <div class="value">{{ $impacto['totales']['trozas'] ?? 0 }}</div>
                <div class="hint">elementos de inventario</div>
            </td>
            <td class="metric" style="border-left-color:#b45309;">
                <div class="label">Biomasa total</div>
                <div class="value">{{ number_format($impacto['totales']['biomasa_total'] ?? 0, 2) }}</div>
                <div class="hint">kg estimados</div>
            </td>
            <td class="metric" style="border-left-color:#7c3aed;">
                <div class="label">Carbono total</div>
                <div class="value">{{ number_format($impacto['totales']['carbono_total'] ?? 0, 2) }}</div>
                <div class="hint">kg retenidos</div>
            </td>
        </tr>
    </table>

    <div class="section">
        <div class="section-title">Datos generales</div>
        <div class="section-body">
            <table class="info-table">
                <tr>
                    <td class="info-label">Usuario</td>
                    <td>{{ $impacto['usuario'] ?? ($persona->nom ?? 'N/A') }}</td>
                    <td class="info-label">Alcance</td>
                    <td>{{ strtoupper($impacto['filtro']['modo'] ?? 'todas') }}</td>
                </tr>
                <tr>
                    <td class="info-label">Parcela</td>
                    <td>{{ $impacto['filtro']['id_parcela'] ?? 'Todas las parcelas' }}</td>
                    <td class="info-label">Versión</td>
                    <td>SIGMAD | Reporte formal</td>
                </tr>
            </table>
        </div>
    </div>

    <div class="section">
        <div class="section-title">Hallazgos críticos</div>
        <div class="section-body">
            @if(!empty($impacto['alertas']))
                <div class="alert-box">
                    <strong>Alertas detectadas</strong>
                    <ul>
                        @foreach($impacto['alertas'] as $a)
                            <li>{{ $a }}</li>
                        @endforeach
                    </ul>
                </div>
            @else
                <div class="review-box">
                    <strong>Sin alertas relevantes.</strong>
                    <div>El análisis no detectó condiciones críticas en el conjunto evaluado.</div>
                </div>
            @endif
        </div>
    </div>

    <div class="section">
        <div class="section-title">Recomendaciones prioritarias</div>
        <div class="section-body">
            @if(!empty($impacto['recomendaciones_detalladas']))
                @foreach($impacto['recomendaciones_detalladas'] as $r)
                    <div class="rec">
                        <h3>{{ $r['title'] ?? 'Recomendación' }}</h3>
                        <div class="rec-meta">
                            Prioridad: {{ strtoupper($r['priority'] ?? 'media') }} |
                            Métrica: {{ $r['metric_impacted'] ?? 'no especificada' }}
                        </div>
                        <p>{{ $r['recommendation'] ?? '' }}</p>
                        @if(!empty($r['actions']))
                            <strong>Acciones sugeridas</strong>
                            <ul class="list">
                                @foreach($r['actions'] as $action)
                                    <li>{{ $action }}</li>
                                @endforeach
                            </ul>
                        @endif
                    </div>
                @endforeach
            @elseif(!empty($impacto['recomendaciones']))
                <ul class="list">
                    @foreach($impacto['recomendaciones'] as $r)
                        <li>{{ $r }}</li>
                    @endforeach
                </ul>
            @else
                <div class="review-box">No hay recomendaciones específicas para este corte de análisis.</div>
            @endif
        </div>
    </div>

    @if(!empty($impacto['analisis_ia']['resumen']))
    <div class="section">
        <div class="section-title">Lectura inteligente</div>
        <div class="section-body">
            <div class="review-box">
                <strong>Resumen IA</strong>
                <div style="margin-top:6px; line-height:1.5;">{{ $impacto['analisis_ia']['resumen'] }}</div>
            </div>
        </div>
    </div>
    @endif

    <div class="spacer"></div>
    <div class="footer">SIGMAD | Sistema Inteligente de Gestión Maderable | Reporte formal de impacto ambiental</div>
</body>
</html>