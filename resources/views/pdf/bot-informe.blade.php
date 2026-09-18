<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <title>Informe SIGMAD</title>
    <style>
        @page { margin: 16mm 12mm 18mm 12mm; }
        * { box-sizing: border-box; }
        body {
            font-family: 'DejaVu Sans', Arial, sans-serif;
            font-size: 9.5pt;
            color: #24313f;
            margin: 0;
            background: #ffffff;
        }

        .watermark {
            position: fixed;
            top: 45%;
            left: 50%;
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
            width: 74px;
            height: 74px;
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
        .card .value { font-size: 17pt; font-weight: bold; color: #0f172a; line-height: 1.1; margin-top: 4px; }
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
            font-size: 8pt;
            color: #475569;
            background: #f8fafc;
            border: 1px dashed #cbd5e1;
            border-radius: 8px;
            padding: 10px;
            margin-top: 12px;
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

        table {
            width: 100%;
            border-collapse: collapse;
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
                    <div class="doc-badge">INFORME BOT</div><br>
                    <div class="doc-date">Generado: {{ $fecha }}</div>
                </td>
            </tr>
        </table>
    </div>

    <div class="hero">
        <h1>Resumen operativo del usuario</h1>
        <p>Este informe concentra las parcelas asignadas, las especies disponibles y una guía rápida para capturar datos de manera correcta.</p>
    </div>

    <table class="cards">
        <tr>
            <td class="card">
                <div class="label">Rol</div>
                <div class="value">{{ $rol }}</div>
                <div class="hint">Acceso activo</div>
            </td>
            <td class="card" style="border-left-color:#1d4ed8;">
                <div class="label">Parcelas</div>
                <div class="value">{{ $parcelas->count() }}</div>
                <div class="hint">Asignadas al usuario</div>
            </td>
            <td class="card" style="border-left-color:#b45309;">
                <div class="label">Especies</div>
                <div class="value">{{ $especies->count() }}</div>
                <div class="hint">Registradas en sistema</div>
            </td>
            <td class="card" style="border-left-color:#7c3aed;">
                <div class="label">Estado</div>
                <div class="value" style="font-size:12pt;">Disponible</div>
                <div class="hint">Listo para trabajar</div>
            </td>
        </tr>
    </table>

    <div class="section">
        <div class="section-title">Perfil</div>
        <div class="section-body">
            <table class="info-table">
                <tr>
                    <td class="info-label">Usuario</td>
                    <td>{{ trim(($persona->nom ?? '') . ' ' . ($persona->ap ?? '') . ' ' . ($persona->am ?? '')) }}</td>
                    <td class="info-label">Teléfono</td>
                    <td>{{ $persona->telefono }}</td>
                </tr>
                <tr>
                    <td class="info-label">Rol</td>
                    <td>{{ $rol }}</td>
                    <td class="info-label">Acceso</td>
                    <td>Activo</td>
                </tr>
            </table>
        </div>
    </div>

    <div class="section">
        <div class="section-title">Parcelas asignadas</div>
        <div class="section-body" style="padding:0;">
        @if($parcelas->isEmpty())
            <div style="padding:12px;" class="muted">No hay parcelas asignadas.</div>
        @else
            <table class="data-table">
                <thead>
                    <tr>
                        <th>ID</th>
                        <th>Parcela</th>
                        <th>Ubicación</th>
                    </tr>
                </thead>
                <tbody>
                    @foreach($parcelas as $parcela)
                        <tr>
                            <td>{{ $parcela->id_parcela }}</td>
                            <td>{{ $parcela->nom_parcela }}</td>
                            <td>{{ $parcela->ubicacion ?? 'Sin dato' }}</td>
                        </tr>
                    @endforeach
                </tbody>
            </table>
        @endif
        </div>
    </div>

    <div class="section">
        <div class="section-title">Especies disponibles</div>
        <div class="section-body" style="padding:0;">
        @if($especies->isEmpty())
            <div style="padding:12px;" class="muted">No hay especies registradas.</div>
        @else
            <table class="data-table">
                <thead>
                    <tr>
                        <th>Nombre común</th>
                        <th>Nombre científico</th>
                    </tr>
                </thead>
                <tbody>
                    @foreach($especies as $especie)
                        <tr>
                            <td>{{ $especie->nom_comun }}</td>
                            <td>{{ $especie->nom_cientifico ?? 'Sin dato' }}</td>
                        </tr>
                    @endforeach
                </tbody>
            </table>
        @endif
        </div>
    </div>

    <div class="section">
        <div class="section-title">Guía de captura</div>
        <div class="section-body">
            <div class="small-title">Árboles</div>
            <ul class="bullet-list">
                <li>DAP: diámetro a la altura del pecho, medido a 1.30 m del suelo.</li>
                <li>Altura total: altura completa del árbol desde la base hasta la punta.</li>
            </ul>

            <div class="small-title" style="margin-top:10px;">Trozas</div>
            <ul class="bullet-list">
                <li>Diámetro superior: extremo delgado de la troza.</li>
                <li>Longitud: largo total de la troza.</li>
                <li>Densidad: valor usado para estimaciones.</li>
                <li>Opcional: diámetro inferior y diámetro medio.</li>
            </ul>

            <div class="note">Use esta información como referencia de captura para reducir errores y mantener consistencia en el registro.</div>
        </div>
    </div>

    <div class="note">
        <div class="small-title">Observación</div>
        Este informe resume la información disponible en SIGMAD para apoyo operativo en campo. Si el usuario no reconoce una parcela o especie, se recomienda validar la asignación antes de registrar datos.
    </div>

    <div class="footer">
        SIGMAD - Sistema Inteligente de Gestión Maderable | Informe operativo formal
    </div>
</body>
</html>
