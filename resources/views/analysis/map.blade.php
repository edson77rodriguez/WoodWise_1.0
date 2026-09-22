<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <meta name="csrf-token" content="{{ csrf_token() }}">

    <title>Mapa profesional · {{ $job->uuid }}</title>

    <link
        rel="stylesheet"
        href="https://cdn.jsdelivr.net/npm/ol@10.10.0/ol.css"
    >

    <style>
        :root {
            --bg: #0b1220;
            --bg-soft: #121b2e;
            --panel: rgba(15, 23, 42, 0.88);
            --panel-strong: rgba(10, 16, 30, 0.94);
            --panel-light: #f8fafc;
            --text: #e5edf8;
            --text-dark: #0f172a;
            --muted: #9cafc8;
            --line: rgba(148, 163, 184, 0.18);
            --line-strong: rgba(148, 163, 184, 0.28);
            --accent: #4f46e5;
            --accent-soft: rgba(79, 70, 229, 0.16);
            --mrcnn: #22c55e;
            --mrcnn-soft: rgba(34, 197, 94, 0.18);
            --yolo: #a855f7;
            --yolo-soft: rgba(168, 85, 247, 0.18);
            --review: #f59e0b;
            --review-soft: rgba(245, 158, 11, 0.18);
            --danger: #ef4444;
            --danger-soft: rgba(239, 68, 68, 0.16);
            --white: #ffffff;
            --shadow-xl: 0 22px 60px rgba(0, 0, 0, 0.28);
            --shadow-lg: 0 16px 40px rgba(0, 0, 0, 0.20);
            --radius-xl: 24px;
            --radius-lg: 18px;
            --radius-md: 14px;
            --radius-sm: 10px;
        }

        * {
            box-sizing: border-box;
        }

        html,
        body {
            margin: 0;
            min-height: 100%;
            font-family:
                Inter,
                ui-sans-serif,
                system-ui,
                -apple-system,
                BlinkMacSystemFont,
                "Segoe UI",
                Roboto,
                Arial,
                sans-serif;
            background:
                radial-gradient(circle at top left, rgba(79, 70, 229, 0.18), transparent 28%),
                radial-gradient(circle at top right, rgba(34, 197, 94, 0.10), transparent 25%),
                linear-gradient(180deg, #0a1020 0%, #0b1220 100%);
            color: var(--text);
        }

        a {
            color: inherit;
        }

        .shell {
            max-width: 1880px;
            margin: 0 auto;
            padding: 20px;
        }

        .hero {
            background:
                linear-gradient(135deg, rgba(79, 70, 229, 0.28), rgba(10, 16, 30, 0.85) 42%),
                linear-gradient(135deg, rgba(34, 197, 94, 0.12), transparent 65%);
            border: 1px solid var(--line);
            border-radius: 26px;
            box-shadow: var(--shadow-lg);
            overflow: hidden;
            margin-bottom: 18px;
        }

        .hero-inner {
            display: grid;
            grid-template-columns: minmax(0, 1.65fr) minmax(340px, 0.95fr);
            gap: 18px;
            padding: 22px;
        }

        .eyebrow {
            display: inline-flex;
            align-items: center;
            gap: 8px;
            padding: 7px 12px;
            border-radius: 999px;
            background: rgba(255, 255, 255, 0.08);
            border: 1px solid rgba(255, 255, 255, 0.12);
            color: #dbe6f5;
            font-size: 12px;
            font-weight: 700;
            letter-spacing: .02em;
        }

        .hero-title {
            margin: 14px 0 8px;
            font-size: clamp(28px, 4vw, 40px);
            line-height: 1.04;
            letter-spacing: -.02em;
            font-weight: 850;
        }

        .hero-text {
            margin: 0;
            max-width: 920px;
            color: #c7d4e8;
            font-size: 14px;
            line-height: 1.72;
        }

        .hero-actions {
            display: flex;
            flex-wrap: wrap;
            gap: 12px;
            margin-top: 18px;
        }

        .btn {
            display: inline-flex;
            align-items: center;
            justify-content: center;
            gap: 8px;
            border: 1px solid transparent;
            border-radius: 12px;
            padding: 11px 16px;
            font-size: 13px;
            font-weight: 700;
            text-decoration: none;
            cursor: pointer;
            transition: transform .18s ease, box-shadow .18s ease, background .18s ease, border-color .18s ease;
        }

        .btn:hover {
            transform: translateY(-1px);
        }

        .btn-primary {
            background: linear-gradient(135deg, #635bff, #4f46e5);
            color: white;
            box-shadow: 0 10px 30px rgba(79, 70, 229, 0.30);
        }

        .btn-secondary {
            background: rgba(255, 255, 255, 0.06);
            border-color: rgba(255, 255, 255, 0.12);
            color: #e5edf8;
        }

        .hero-meta {
            display: grid;
            grid-template-columns: 1fr 1fr;
            gap: 12px;
            align-content: start;
        }

        .meta-card {
            background: rgba(255, 255, 255, 0.06);
            border: 1px solid rgba(255, 255, 255, 0.10);
            border-radius: 18px;
            padding: 14px;
            min-height: 104px;
        }

        .meta-label {
            color: #a9bad3;
            font-size: 11px;
            text-transform: uppercase;
            letter-spacing: .06em;
        }

        .meta-value {
            margin-top: 7px;
            font-size: 22px;
            font-weight: 820;
            letter-spacing: -.02em;
        }

        .meta-subvalue {
            margin-top: 6px;
            color: #cbd8ea;
            font-size: 12px;
            line-height: 1.5;
        }

        .layout {
            display: grid;
            grid-template-columns: minmax(0, 1fr) 400px;
            gap: 18px;
            align-items: start;
        }

        .glass-card {
            background: var(--panel);
            backdrop-filter: blur(14px);
            border: 1px solid var(--line);
            border-radius: var(--radius-xl);
            box-shadow: var(--shadow-xl);
            overflow: hidden;
        }

        .map-card {
            position: relative;
            min-height: 780px;
        }

        .map-header {
            display: flex;
            justify-content: space-between;
            gap: 14px;
            padding: 16px 18px;
            border-bottom: 1px solid var(--line);
            background: rgba(255, 255, 255, 0.03);
        }

        .map-title-block h2 {
            margin: 0;
            font-size: 17px;
            font-weight: 800;
            letter-spacing: -.02em;
        }

        .map-title-block p {
            margin: 4px 0 0;
            font-size: 12px;
            line-height: 1.55;
            color: var(--muted);
        }

        .status-pills {
            display: flex;
            flex-wrap: wrap;
            gap: 8px;
            justify-content: flex-end;
        }

        .pill {
            display: inline-flex;
            align-items: center;
            gap: 8px;
            padding: 8px 11px;
            border-radius: 999px;
            font-size: 11px;
            font-weight: 750;
            white-space: nowrap;
            border: 1px solid transparent;
        }

        .pill-neutral {
            background: rgba(255, 255, 255, 0.06);
            border-color: rgba(255, 255, 255, 0.10);
            color: #e6eef9;
        }

        .pill-success {
            background: var(--mrcnn-soft);
            border-color: rgba(34, 197, 94, 0.30);
            color: #c6f6d5;
        }

        .pill-yolo {
            background: var(--yolo-soft);
            border-color: rgba(168, 85, 247, 0.28);
            color: #ead7ff;
        }

        .pill-warning {
            background: var(--review-soft);
            border-color: rgba(245, 158, 11, 0.34);
            color: #fde7b1;
        }

        .map-toolbar {
            display: flex;
            flex-wrap: wrap;
            justify-content: space-between;
            gap: 16px;
            padding: 14px 18px;
            border-bottom: 1px solid var(--line);
            background: rgba(255, 255, 255, 0.02);
        }

        .control-group {
            display: flex;
            flex-wrap: wrap;
            gap: 12px;
            align-items: center;
        }

        .toggle-chip {
            display: inline-flex;
            align-items: center;
            gap: 10px;
            padding: 10px 12px;
            border-radius: 14px;
            border: 1px solid var(--line);
            background: rgba(255, 255, 255, 0.04);
            color: #e6eef9;
            font-size: 12px;
            font-weight: 700;
        }

        .toggle-chip input {
            accent-color: var(--accent);
            width: 16px;
            height: 16px;
            margin: 0;
        }

        .dot {
            display: inline-block;
            width: 10px;
            height: 10px;
            border-radius: 999px;
            flex: 0 0 auto;
        }

        .dot-preview { background: #e5edf8; }
        .dot-mrcnn { background: var(--mrcnn); }
        .dot-yolo { background: var(--yolo); }
        .dot-review { background: var(--review); }

        .range-wrap {
            display: flex;
            align-items: center;
            gap: 10px;
            padding: 10px 12px;
            border-radius: 14px;
            border: 1px solid var(--line);
            background: rgba(255, 255, 255, 0.04);
            min-width: 230px;
        }

        .range-wrap label {
            font-size: 11px;
            color: var(--muted);
            font-weight: 700;
            white-space: nowrap;
        }

        .range-wrap input[type="range"] {
            width: 100%;
        }

        .range-value {
            min-width: 34px;
            text-align: right;
            font-size: 11px;
            font-weight: 800;
            color: #f8fbff;
        }

        .toolbar-buttons {
            display: flex;
            gap: 10px;
            flex-wrap: wrap;
        }

        .ghost-btn {
            display: inline-flex;
            align-items: center;
            justify-content: center;
            gap: 8px;
            padding: 10px 14px;
            border-radius: 12px;
            border: 1px solid var(--line-strong);
            background: rgba(255, 255, 255, 0.04);
            color: #eef5ff;
            font-size: 12px;
            font-weight: 700;
            cursor: pointer;
        }

        .map-stage {
            position: relative;
            padding: 14px;
        }

        #map {
            width: 100%;
            height: calc(100vh - 290px);
            min-height: 690px;
            border-radius: 20px;
            overflow: hidden;
            background:
                linear-gradient(45deg, rgba(255,255,255,0.03) 25%, transparent 25%),
                linear-gradient(-45deg, rgba(255,255,255,0.03) 25%, transparent 25%),
                linear-gradient(45deg, transparent 75%, rgba(255,255,255,0.03) 75%),
                linear-gradient(-45deg, transparent 75%, rgba(255,255,255,0.03) 75%),
                linear-gradient(180deg, #13203a 0%, #0f172a 100%);
            background-size: 30px 30px;
            background-position: 0 0, 0 15px, 15px -15px, -15px 0;
            border: 1px solid rgba(255, 255, 255, 0.06);
        }

        .floating-summary {
            position: absolute;
            top: 28px;
            left: 28px;
            z-index: 20;
            display: grid;
            grid-template-columns: repeat(4, minmax(118px, 1fr));
            gap: 10px;
            pointer-events: none;
        }

        .floating-item {
            background: rgba(10, 16, 30, 0.82);
            backdrop-filter: blur(10px);
            border: 1px solid rgba(255,255,255,0.08);
            border-radius: 16px;
            padding: 11px 12px;
            box-shadow: 0 10px 25px rgba(0,0,0,0.22);
        }

        .floating-item .label {
            color: #9db1cd;
            font-size: 10px;
            font-weight: 700;
            text-transform: uppercase;
            letter-spacing: .06em;
        }

        .floating-item .value {
            margin-top: 6px;
            font-size: 21px;
            font-weight: 850;
            color: white;
        }

        .floating-item .sub {
            margin-top: 4px;
            font-size: 10px;
            color: #bfd0e7;
        }

        .map-footer {
            display: flex;
            justify-content: space-between;
            align-items: center;
            flex-wrap: wrap;
            gap: 10px;
            padding: 12px 18px 18px;
        }

        .legend {
            display: flex;
            flex-wrap: wrap;
            gap: 12px;
        }

        .legend-item {
            display: inline-flex;
            align-items: center;
            gap: 8px;
            padding: 8px 10px;
            border-radius: 12px;
            background: rgba(255, 255, 255, 0.04);
            border: 1px solid var(--line);
            color: #d9e7f8;
            font-size: 11px;
            font-weight: 700;
        }

        .legend-swatch {
            width: 14px;
            height: 14px;
            border-radius: 4px;
            box-shadow: inset 0 0 0 2px rgba(255,255,255,0.20);
        }

        .swatch-mrcnn {
            background: rgba(34, 197, 94, 0.32);
            border: 2px solid rgba(34, 197, 94, 0.95);
        }

        .swatch-yolo {
            background: rgba(168, 85, 247, 0.28);
            border: 2px solid rgba(168, 85, 247, 0.96);
        }

        .swatch-review {
            background: rgba(245, 158, 11, 0.20);
            border: 2px dashed rgba(245, 158, 11, 0.96);
        }

        .legend-note {
            color: var(--muted);
            font-size: 11px;
            line-height: 1.5;
        }

        .side-stack {
            display: grid;
            gap: 18px;
        }

        .panel {
            background: var(--panel);
            backdrop-filter: blur(14px);
            border: 1px solid var(--line);
            border-radius: 22px;
            box-shadow: var(--shadow-xl);
            overflow: hidden;
        }

        .panel-head {
            padding: 16px 18px 12px;
            border-bottom: 1px solid var(--line);
            background: rgba(255, 255, 255, 0.03);
        }

        .panel-head h3 {
            margin: 0;
            font-size: 15px;
            font-weight: 800;
        }

        .panel-head p {
            margin: 6px 0 0;
            color: var(--muted);
            font-size: 11px;
            line-height: 1.55;
        }

        .panel-body {
            padding: 16px 18px 18px;
        }

        .kpi-grid {
            display: grid;
            grid-template-columns: 1fr 1fr;
            gap: 12px;
        }

        .kpi {
            padding: 14px;
            border-radius: 16px;
            border: 1px solid var(--line);
            background: rgba(255, 255, 255, 0.04);
        }

        .kpi-label {
            font-size: 10px;
            text-transform: uppercase;
            letter-spacing: .06em;
            color: var(--muted);
            font-weight: 800;
        }

        .kpi-value {
            margin-top: 7px;
            font-size: 22px;
            font-weight: 850;
            color: white;
        }

        .kpi-sub {
            margin-top: 5px;
            font-size: 11px;
            color: #c7d5e9;
        }

        .mini-list {
            display: grid;
            gap: 10px;
        }

        .mini-row {
            display: flex;
            justify-content: space-between;
            align-items: flex-start;
            gap: 12px;
            padding: 11px 0;
            border-bottom: 1px solid rgba(255,255,255,0.06);
        }

        .mini-row:last-child {
            border-bottom: 0;
            padding-bottom: 0;
        }

        .mini-key {
            color: var(--muted);
            font-size: 11px;
            font-weight: 700;
        }

        .mini-value {
            font-size: 12px;
            font-weight: 800;
            color: #f8fbff;
            text-align: right;
        }

        .object-empty {
            color: var(--muted);
            font-size: 12px;
            line-height: 1.65;
        }

        .selected-title {
            display: flex;
            align-items: center;
            justify-content: space-between;
            gap: 10px;
            margin-bottom: 14px;
        }

        .selected-id {
            font-size: 18px;
            font-weight: 850;
            letter-spacing: -.02em;
            color: #f8fbff;
        }

        .badge {
            display: inline-flex;
            align-items: center;
            justify-content: center;
            gap: 8px;
            padding: 7px 10px;
            border-radius: 999px;
            font-size: 11px;
            font-weight: 800;
        }

        .badge-success {
            background: var(--mrcnn-soft);
            color: #ccf4d9;
            border: 1px solid rgba(34, 197, 94, 0.25);
        }

        .badge-yolo {
            background: var(--yolo-soft);
            color: #ecd7ff;
            border: 1px solid rgba(168, 85, 247, 0.25);
        }

        .badge-warning {
            background: var(--review-soft);
            color: #fde7b1;
            border: 1px solid rgba(245, 158, 11, 0.28);
        }

        .feature-grid {
            display: grid;
            grid-template-columns: 1fr 1fr;
            gap: 10px;
            margin-bottom: 14px;
        }

        .feature-box {
            border-radius: 14px;
            padding: 12px;
            border: 1px solid var(--line);
            background: rgba(255,255,255,0.04);
        }

        .feature-box .name {
            color: var(--muted);
            font-size: 10px;
            font-weight: 800;
            text-transform: uppercase;
            letter-spacing: .06em;
        }

        .feature-box .value {
            margin-top: 6px;
            font-size: 17px;
            font-weight: 850;
            color: white;
        }

        .property-list {
            display: grid;
            gap: 10px;
        }

        .property-row {
            padding-bottom: 10px;
            border-bottom: 1px solid rgba(255,255,255,0.06);
        }

        .property-row:last-child {
            border-bottom: 0;
            padding-bottom: 0;
        }

        .property-key {
            color: var(--muted);
            font-size: 10px;
            text-transform: uppercase;
            letter-spacing: .06em;
            font-weight: 800;
        }

        .property-value {
            margin-top: 5px;
            font-size: 13px;
            line-height: 1.55;
            color: #f8fbff;
            font-weight: 700;
            overflow-wrap: anywhere;
        }

        .info-note {
            color: var(--muted);
            font-size: 11px;
            line-height: 1.7;
        }

        .warn-box {
            padding: 13px 14px;
            border-radius: 16px;
            background: rgba(245, 158, 11, 0.12);
            border: 1px solid rgba(245, 158, 11, 0.26);
            color: #fde7b1;
            font-size: 11px;
            line-height: 1.6;
        }

        .tip-box {
            padding: 13px 14px;
            border-radius: 16px;
            background: rgba(79, 70, 229, 0.12);
            border: 1px solid rgba(79, 70, 229, 0.25);
            color: #d9dcff;
            font-size: 11px;
            line-height: 1.6;
        }

        .ol-control button {
            background: rgba(15, 23, 42, 0.88) !important;
            border-radius: 10px !important;
            color: white !important;
        }

        .ol-zoom {
            top: 18px !important;
            left: auto !important;
            right: 18px !important;
            border-radius: 12px !important;
            overflow: hidden;
            border: 1px solid rgba(255,255,255,0.08);
            backdrop-filter: blur(8px);
        }

        .ol-scale-line {
            background: rgba(15, 23, 42, 0.76) !important;
            border-radius: 10px;
            padding: 4px 6px;
            bottom: 18px !important;
            left: 18px !important;
        }

        .ol-attribution {
            background: rgba(15, 23, 42, 0.76) !important;
            color: white;
            border-radius: 10px 0 0 0;
        }

        .map-tooltip {
            position: absolute;
            z-index: 50;
            transform: translate(-50%, calc(-100% - 14px));
            padding: 10px 12px;
            min-width: 190px;
            pointer-events: none;
            background: rgba(10, 16, 30, 0.94);
            border: 1px solid rgba(255, 255, 255, 0.10);
            border-radius: 14px;
            box-shadow: 0 14px 34px rgba(0,0,0,0.30);
            color: #f8fbff;
            opacity: 0;
            transition: opacity .12s ease;
        }

        .map-tooltip.active {
            opacity: 1;
        }

        .tooltip-title {
            font-size: 12px;
            font-weight: 800;
        }

        .tooltip-sub {
            margin-top: 4px;
            color: #c9d7ea;
            font-size: 11px;
            line-height: 1.55;
        }

        .tooltip-badge {
            display: inline-flex;
            align-items: center;
            gap: 6px;
            margin-top: 8px;
            padding: 5px 8px;
            border-radius: 999px;
            font-size: 10px;
            font-weight: 800;
        }

        @media (max-width: 1480px) {
            .layout {
                grid-template-columns: 1fr 360px;
            }

            .hero-inner {
                grid-template-columns: 1fr;
            }

            .hero-meta {
                grid-template-columns: repeat(2, minmax(0, 1fr));
            }
        }

        @media (max-width: 1180px) {
            .layout {
                grid-template-columns: 1fr;
            }

            #map {
                height: 70vh;
                min-height: 560px;
            }
        }

        @media (max-width: 900px) {
            .floating-summary {
                grid-template-columns: repeat(2, minmax(118px, 1fr));
                top: 26px;
                left: 26px;
                right: 26px;
            }

            .feature-grid,
            .kpi-grid,
            .hero-meta {
                grid-template-columns: 1fr 1fr;
            }
        }

        @media (max-width: 700px) {
            .shell {
                padding: 12px;
            }

            .feature-grid,
            .kpi-grid,
            .hero-meta,
            .floating-summary {
                grid-template-columns: 1fr;
            }

            .map-header,
            .map-toolbar {
                flex-direction: column;
                align-items: stretch;
            }

            #map {
                height: 68vh;
                min-height: 480px;
            }
        }
    </style>
</head>
<body>
<div class="shell">

    <section class="hero">
        <div class="hero-inner">
            <div>
                <span class="eyebrow">UAV Forest AI · Visor cartográfico profesional</span>

                <h1 class="hero-title">
                    Visualización avanzada del ortomosaico y segmentaciones de copa
                </h1>

                <p class="hero-text">
                    Explora el ortomosaico con una interfaz más profesional, diferenciando claramente las geometrías
                    consolidadas con <strong>Mask R-CNN</strong> y los <strong>fallbacks de YOLO-Seg</strong>. También puedes
                    inspeccionar únicamente los objetos que requieren revisión, ajustar opacidades y consultar el detalle
                    técnico de cada copa segmentada.
                </p>

                <div class="hero-actions">
                    <a
                        class="btn btn-primary"
                        href="{{ route('analysis.index', ['mosaic' => $mosaic->uuid]) }}"
                    >
                        ← Volver al análisis
                    </a>

                    <a
                        class="btn btn-secondary"
                        href="{{ route('analysis.jobs.show', ['analysisUuid' => $job->uuid]) }}"
                    >
                        Ver JSON técnico
                    </a>
                </div>
            </div>

            <div class="hero-meta">
                <div class="meta-card">
                    <div class="meta-label">Analysis UUID</div>
                    <div class="meta-value">{{ $job->uuid }}</div>
                    <div class="meta-subvalue">
                        Método científico: {{ $job->models['scientific_method_version'] ?? 'V0.6G' }}<br>
                        Pipeline: {{ $job->models['pipeline_version'] ?? 'V0.7E' }}
                    </div>
                </div>

                <div class="meta-card">
                    <div class="meta-label">Proyecto / mosaico</div>
                    <div class="meta-value">{{ $mosaic->project->name ?? '—' }}</div>
                    <div class="meta-subvalue">
                        {{ $mosaic->original_name ?? $mosaic->uuid }}<br>
                        CRS: {{ $mosaic->crs ?? '—' }}
                    </div>
                </div>

                <div class="meta-card">
                    <div class="meta-label">Objetos operativos</div>
                    <div class="meta-value">{{ $job->summary['primary_objects'] ?? 0 }}</div>
                    <div class="meta-subvalue">
                        Mask R-CNN: {{ $job->summary['primary_maskrcnn'] ?? 0 }} · YOLO fallback: {{ $job->summary['primary_yolo_fallback'] ?? 0 }}
                    </div>
                </div>

                <div class="meta-card">
                    <div class="meta-label">Control de calidad</div>
                    <div class="meta-value">{{ $job->summary['requires_review'] ?? 0 }}</div>
                    <div class="meta-subvalue">
                        Requieren revisión operativa · Sin revisión: {{ $job->summary['no_review'] ?? 0 }}
                    </div>
                </div>
            </div>
        </div>
    </section>

    <div class="layout">
        <section class="glass-card map-card">
            <div class="map-header">
                <div class="map-title-block">
                    <h2>Mapa de segmentaciones</h2>
                    <p>
                        Capas activables por modelo primario. El ortomosaico se visualiza como imagen estática georreferenciada,
                        mientras que las geometrías del análisis se reproyectan para interoperabilidad web.
                    </p>
                </div>

                <div class="status-pills">
                    <span class="pill pill-neutral">Estado: {{ strtoupper($job->status) }}</span>
                    <span class="pill pill-success">Mask R-CNN: {{ $job->summary['primary_maskrcnn'] ?? 0 }}</span>
                    <span class="pill pill-yolo">YOLO fallback: {{ $job->summary['primary_yolo_fallback'] ?? 0 }}</span>
                    <span class="pill pill-warning">Revisión: {{ $job->summary['requires_review'] ?? 0 }}</span>
                </div>
            </div>

            <div class="map-toolbar">
                <div class="control-group">
                    @if($previewAvailable)
                        <label class="toggle-chip">
                            <input id="toggle-preview" type="checkbox" checked>
                            <span class="dot dot-preview"></span>
                            Ortomosaico
                        </label>
                    @endif

                    <label class="toggle-chip">
                        <input id="toggle-mrcnn" type="checkbox" checked>
                        <span class="dot dot-mrcnn"></span>
                        Mask R-CNN primario
                    </label>

                    <label class="toggle-chip">
                        <input id="toggle-yolo" type="checkbox" checked>
                        <span class="dot dot-yolo"></span>
                        YOLO fallback
                    </label>

                    <label class="toggle-chip">
                        <input id="toggle-review" type="checkbox" checked>
                        <span class="dot dot-review"></span>
                        Resaltar revisión
                    </label>
                </div>

                <div class="control-group">
                    <div class="range-wrap">
                        <label for="preview-opacity">Opacidad ortomosaico</label>
                        <input id="preview-opacity" type="range" min="0" max="100" value="100">
                        <span id="preview-opacity-value" class="range-value">100%</span>
                    </div>

                    <div class="range-wrap">
                        <label for="seg-opacity">Opacidad segmentación</label>
                        <input id="seg-opacity" type="range" min="15" max="100" value="82">
                        <span id="seg-opacity-value" class="range-value">82%</span>
                    </div>
                </div>

                <div class="toolbar-buttons">
                    <button id="fit-extent" type="button" class="ghost-btn">Ajustar extensión</button>
                    <button id="clear-selection" type="button" class="ghost-btn">Limpiar selección</button>
                </div>
            </div>

            <div class="map-stage">
                <div class="floating-summary">
                    <div class="floating-item">
                        <div class="label">Objetos visibles</div>
                        <div id="visible-count" class="value">0</div>
                        <div class="sub">Según filtros activos</div>
                    </div>

                    <div class="floating-item">
                        <div class="label">Mask visibles</div>
                        <div id="visible-mrcnn-count" class="value">0</div>
                        <div class="sub">Primarios de Mask R-CNN</div>
                    </div>

                    <div class="floating-item">
                        <div class="label">YOLO visibles</div>
                        <div id="visible-yolo-count" class="value">0</div>
                        <div class="sub">Fallback conservado</div>
                    </div>

                    <div class="floating-item">
                        <div class="label">Revisión visible</div>
                        <div id="visible-review-count" class="value">0</div>
                        <div class="sub">Objetos marcados</div>
                    </div>
                </div>

                <div id="map"></div>
                <div id="map-tooltip" class="map-tooltip"></div>
            </div>

            <div class="map-footer">
                <div class="legend">
                    <span class="legend-item">
                        <span class="legend-swatch swatch-mrcnn"></span>
                        Mask R-CNN primario
                    </span>

                    <span class="legend-item">
                        <span class="legend-swatch swatch-yolo"></span>
                        YOLO-Seg fallback
                    </span>

                    <span class="legend-item">
                        <span class="legend-swatch swatch-review"></span>
                        Contorno de revisión
                    </span>
                </div>

                <div class="legend-note">
                    Haz clic en una copa para inspeccionarla. Usa los toggles superiores para comparar rápidamente la distribución
                    de Mask R-CNN frente a YOLO-Seg.
                </div>
            </div>
        </section>

        <aside class="side-stack">
            <section class="panel">
                <div class="panel-head">
                    <h3>Resumen ejecutivo</h3>
                    <p>Indicadores principales del resultado consolidado.</p>
                </div>

                <div class="panel-body">
                    <div class="kpi-grid">
                        <div class="kpi">
                            <div class="kpi-label">Objetos</div>
                            <div class="kpi-value">{{ $job->summary['primary_objects'] ?? 0 }}</div>
                            <div class="kpi-sub">Total de objetos operativos</div>
                        </div>

                        <div class="kpi">
                            <div class="kpi-label">Grupos catálogo</div>
                            <div class="kpi-value">{{ $job->summary['catalog_groups'] ?? 0 }}</div>
                            <div class="kpi-sub">Componentes intermodelo</div>
                        </div>

                        <div class="kpi">
                            <div class="kpi-label">Mask R-CNN</div>
                            <div class="kpi-value">{{ $job->summary['primary_maskrcnn'] ?? 0 }}</div>
                            <div class="kpi-sub">Geometrías primarias</div>
                        </div>

                        <div class="kpi">
                            <div class="kpi-label">YOLO fallback</div>
                            <div class="kpi-value">{{ $job->summary['primary_yolo_fallback'] ?? 0 }}</div>
                            <div class="kpi-sub">Conservadas por fallback</div>
                        </div>
                    </div>

                    <div style="height:16px"></div>

                    <div class="mini-list">
                        <div class="mini-row">
                            <div class="mini-key">Requieren revisión</div>
                            <div class="mini-value">{{ $job->summary['requires_review'] ?? 0 }}</div>
                        </div>

                        <div class="mini-row">
                            <div class="mini-key">Sin revisión operativa</div>
                            <div class="mini-value">{{ $job->summary['no_review'] ?? 0 }}</div>
                        </div>

                        <div class="mini-row">
                            <div class="mini-key">Relaciones intermodelo</div>
                            <div class="mini-value">{{ $job->summary['intermodel_relations'] ?? 0 }}</div>
                        </div>

                        <div class="mini-row">
                            <div class="mini-key">Matches primarios</div>
                            <div class="mini-value">{{ $job->summary['primary_matches'] ?? 0 }}</div>
                        </div>
                    </div>

                    @unless($previewAvailable)
                        <div style="height:14px"></div>
                        <div class="warn-box">
                            Este mosaico aún no tiene un preview web disponible. Las segmentaciones se visualizarán sobre un fondo neutro.
                        </div>
                    @endunless
                </div>
            </section>

            <section class="panel">
                <div class="panel-head">
                    <h3>Objeto seleccionado</h3>
                    <p>Haz clic sobre una segmentación para ver sus métricas y su evidencia.</p>
                </div>

                <div id="object-details" class="panel-body">
                    <div class="object-empty">
                        No hay ninguna copa seleccionada. Haz clic sobre una geometría en el mapa para abrir su ficha detallada.
                    </div>
                </div>
            </section>

            <section class="panel">
                <div class="panel-head">
                    <h3>Interpretación metodológica</h3>
                    <p>Lectura correcta de la visualización.</p>
                </div>

                <div class="panel-body">
                    <div class="tip-box">
                        <strong>Mask R-CNN</strong> representa la geometría primaria priorizada por tu criterio científico.
                        <strong>YOLO-Seg</strong> aparece solamente cuando fue preservado como fallback operativo.
                    </div>

                    <div style="height:12px"></div>

                    <div class="info-note">
                        Las áreas, diámetros equivalentes y distancias se calcularon sobre el flujo métrico original en UTM.
                        El GeoJSON mostrado aquí es una capa derivada para visualización web, útil para revisión espacial y auditoría,
                        pero no sustituye la validación final contra verdad de campo.
                    </div>
                </div>
            </section>
        </aside>
    </div>
</div>

<script src="https://cdn.jsdelivr.net/npm/proj4@2.15.0/dist/proj4.js"></script>
<script src="https://cdn.jsdelivr.net/npm/ol@10.10.0/dist/ol.js"></script>

<script>
(() => {
    'use strict';

    const mapBounds = @json($mapBounds);
    const mosaicProjection = @json($mosaic->crs);
    const previewAvailable = @json($previewAvailable);
    const geojsonUrl = @json(route('analysis.map.geojson', ['analysisUuid' => $job->uuid]));
    const previewUrl = previewAvailable
        ? @json(route('analysis.map.preview', ['analysisUuid' => $job->uuid]))
        : null;

    const summaryExpected = {
        total: Number(@json($job->summary['primary_objects'] ?? 0)),
        mrcnn: Number(@json($job->summary['primary_maskrcnn'] ?? 0)),
        yolo: Number(@json($job->summary['primary_yolo_fallback'] ?? 0)),
        review: Number(@json($job->summary['requires_review'] ?? 0)),
    };

    const els = {
        togglePreview: document.getElementById('toggle-preview'),
        toggleMrcnn: document.getElementById('toggle-mrcnn'),
        toggleYolo: document.getElementById('toggle-yolo'),
        toggleReview: document.getElementById('toggle-review'),
        previewOpacity: document.getElementById('preview-opacity'),
        previewOpacityValue: document.getElementById('preview-opacity-value'),
        segOpacity: document.getElementById('seg-opacity'),
        segOpacityValue: document.getElementById('seg-opacity-value'),
        fitExtent: document.getElementById('fit-extent'),
        clearSelection: document.getElementById('clear-selection'),
        objectDetails: document.getElementById('object-details'),
        visibleCount: document.getElementById('visible-count'),
        visibleMrcnnCount: document.getElementById('visible-mrcnn-count'),
        visibleYoloCount: document.getElementById('visible-yolo-count'),
        visibleReviewCount: document.getElementById('visible-review-count'),
        tooltip: document.getElementById('map-tooltip'),
    };

    const utmMatch = /^EPSG:(326|327)(\d{2})$/.exec(mosaicProjection);

    if (utmMatch) {
        const hemisphereCode = utmMatch[1];
        const zone = Number(utmMatch[2]);
        const south = hemisphereCode === '327';

        proj4.defs(
            mosaicProjection,
            `+proj=utm +zone=${zone} ${south ? '+south ' : ''}+datum=WGS84 +units=m +no_defs +type=crs`
        );

        ol.proj.proj4.register(proj4);
    }

    const mapProjection = ol.proj.get(mosaicProjection);

    if (!mapProjection) {
        throw new Error(`OpenLayers no pudo resolver ${mosaicProjection}.`);
    }

    let currentSegOpacity = Number(els.segOpacity?.value || 82) / 100;
    let hoveredFeature = null;

    const previewLayer = previewAvailable
        ? new ol.layer.Image({
            source: new ol.source.ImageStatic({
                url: previewUrl,
                imageExtent: mapBounds,
                projection: mapProjection,
                interpolate: true,
            }),
            opacity: 1,
            zIndex: 1,
        })
        : null;

    const vectorSource = new ol.source.Vector();

    function toBool(value) {
        return (
            value === true
            || value === 1
            || value === '1'
            || String(value).toLowerCase() === 'true'
        );
    }

    function getPrimaryModel(feature) {
        return String(feature.get('primary_model') || '').trim();
    }

    function isMrcnn(feature) {
        return getPrimaryModel(feature) === 'Mask R-CNN';
    }

    function isYolo(feature) {
        return getPrimaryModel(feature) === 'YOLO-Seg';
    }

    function requiresReview(feature) {
        return toBool(feature.get('requires_review'));
    }

    function shouldShowFeature(feature) {
        if (isMrcnn(feature) && !els.toggleMrcnn.checked) {
            return false;
        }

        if (isYolo(feature) && !els.toggleYolo.checked) {
            return false;
        }

        return true;
    }

    function createStroke(color, width, lineDash = null) {
        return new ol.style.Stroke({
            color,
            width,
            lineDash,
            lineJoin: 'round',
            lineCap: 'round',
        });
    }

    function createFill(color) {
        return new ol.style.Fill({ color });
    }

    function getFeatureStyle(feature) {
        if (!shouldShowFeature(feature)) {
            return null;
        }

        const review = requiresReview(feature);
        const showReview = els.toggleReview.checked;

        if (isMrcnn(feature)) {
            return [
                new ol.style.Style({
                    stroke: createStroke(
                        review && showReview
                            ? 'rgba(245, 158, 11, 0.98)'
                            : 'rgba(34, 197, 94, 0.98)',
                        review && showReview ? 3.0 : 2.3,
                        review && showReview ? [10, 6] : null,
                    ),
                    fill: createFill(
                        review && showReview
                            ? `rgba(245, 158, 11, ${Math.max(currentSegOpacity * 0.18, 0.10)})`
                            : `rgba(34, 197, 94, ${Math.max(currentSegOpacity * 0.28, 0.10)})`
                    ),
                }),
                ...(review && showReview ? [
                    new ol.style.Style({
                        stroke: createStroke('rgba(255, 238, 204, 0.92)', 1.2, [2, 6]),
                    }),
                ] : []),
            ];
        }

        return [
            new ol.style.Style({
                stroke: createStroke(
                    review && showReview
                        ? 'rgba(245, 158, 11, 0.98)'
                        : 'rgba(168, 85, 247, 0.98)',
                    review && showReview ? 3.0 : 2.3,
                    review && showReview ? [10, 6] : null,
                ),
                fill: createFill(
                    review && showReview
                        ? `rgba(245, 158, 11, ${Math.max(currentSegOpacity * 0.17, 0.10)})`
                        : `rgba(168, 85, 247, ${Math.max(currentSegOpacity * 0.24, 0.10)})`
                ),
            }),
            ...(review && showReview ? [
                new ol.style.Style({
                    stroke: createStroke('rgba(255, 243, 214, 0.96)', 1.2, [2, 6]),
                }),
            ] : []),
        ];
    }

    const vectorLayer = new ol.layer.Vector({
        source: vectorSource,
        style: getFeatureStyle,
        zIndex: 10,
        declutter: true,
    });

    const selectedStyle = [
        new ol.style.Style({
            stroke: createStroke('rgba(255, 255, 255, 1)', 4.4),
            fill: createFill('rgba(255, 255, 255, 0.05)'),
        }),
        new ol.style.Style({
            stroke: createStroke('rgba(239, 68, 68, 1)', 2.8),
            fill: createFill('rgba(239, 68, 68, 0.10)'),
        }),
    ];

    const map = new ol.Map({
        target: 'map',
        layers: [
            ...(previewLayer ? [previewLayer] : []),
            vectorLayer,
        ],
        view: new ol.View({
            projection: mapProjection,
            center: ol.extent.getCenter(mapBounds),
            zoom: 19,
        }),
        controls: ol.control.defaults.defaults().extend([
            new ol.control.ScaleLine({
                units: 'metric',
                bar: true,
                steps: 4,
                text: true,
                minWidth: 120,
            }),
        ]),
    });

    function fitMap() {
        map.getView().fit(mapBounds, {
            padding: [40, 40, 40, 40],
            duration: 280,
            maxZoom: 24,
        });
    }

    fitMap();

    const selected = new ol.Collection();

    const selectInteraction = new ol.interaction.Select({
        layers: [vectorLayer],
        features: selected,
        style: selectedStyle,
        hitTolerance: 6,
        filter: (feature) => shouldShowFeature(feature),
    });

    map.addInteraction(selectInteraction);

    function escapeHtml(value) {
        if (value === null || value === undefined || value === '') {
            return '—';
        }

        return String(value)
            .replaceAll('&', '&amp;')
            .replaceAll('<', '&lt;')
            .replaceAll('>', '&gt;')
            .replaceAll('"', '&quot;')
            .replaceAll("'", '&#039;');
    }

    function formatNumber(value, digits = 3) {
        const num = Number(value);

        if (!Number.isFinite(num)) {
            return '—';
        }

        return num.toLocaleString('es-MX', {
            minimumFractionDigits: 0,
            maximumFractionDigits: digits,
        });
    }

    function renderSelected(feature) {
        if (!feature) {
            els.objectDetails.innerHTML = `
                <div class="object-empty">
                    No hay ninguna copa seleccionada. Haz clic sobre una geometría en el mapa para abrir su ficha detallada.
                </div>
            `;
            return;
        }

        const review = requiresReview(feature);
        const model = getPrimaryModel(feature);
        const modelBadge = model === 'Mask R-CNN'
            ? '<span class="badge badge-success">Mask R-CNN</span>'
            : '<span class="badge badge-yolo">YOLO-Seg</span>';
        const reviewBadge = review
            ? '<span class="badge badge-warning">Requiere revisión</span>'
            : '<span class="badge badge-success">Sin revisión operativa</span>';

        els.objectDetails.innerHTML = `
            <div class="selected-title">
                <div class="selected-id">${escapeHtml(feature.get('operational_id') || 'Objeto')}</div>
                <div style="display:flex; gap:8px; flex-wrap:wrap; justify-content:flex-end;">
                    ${modelBadge}
                    ${reviewBadge}
                </div>
            </div>

            <div class="feature-grid">
                <div class="feature-box">
                    <div class="name">Área de copa</div>
                    <div class="value">${formatNumber(feature.get('area_m2'), 3)} m²</div>
                </div>

                <div class="feature-box">
                    <div class="name">Diámetro equivalente</div>
                    <div class="value">${formatNumber(feature.get('eq_crown_d_m'), 3)} m</div>
                </div>

                <div class="feature-box">
                    <div class="name">Score representativo</div>
                    <div class="value">${formatNumber(feature.get('representative_score'), 4)}</div>
                </div>

                <div class="feature-box">
                    <div class="name">Observaciones</div>
                    <div class="value">${escapeHtml(feature.get('n_observations'))}</div>
                </div>
            </div>

            <div class="property-list">
                ${propertyRow('Candidate ID', feature.get('candidate_id'))}
                ${propertyRow('Tipo de candidato', feature.get('candidate_type'))}
                ${propertyRow('Modelo primario', feature.get('primary_model'))}
                ${propertyRow('Cluster origen', feature.get('source_cluster'))}
                ${propertyRow('Estado geométrico', feature.get('geometry_status'))}
                ${propertyRow('Estado de evidencia', feature.get('evidence_status'))}
                ${propertyRow('Rol de conflicto', feature.get('conflict_role'))}
                ${propertyRow('IoU intermodelo máximo', formatNumber(feature.get('max_intermodel_iou'), 4))}
                ${propertyRow('Overlap mínimo máximo', formatNumber(feature.get('max_overlap_min'), 4))}
                ${propertyRow('Distancia mínima entre centroides', `${formatNumber(feature.get('min_centroid_distance_m'), 3)} m`)}
                ${propertyRow('CV de área', formatNumber(feature.get('area_cv_pct'), 3))}
                ${propertyRow('Pair IoU mean', formatNumber(feature.get('pair_iou_mean'), 4))}
                ${propertyRow('Pair IoU min', formatNumber(feature.get('pair_iou_min'), 4))}
                ${propertyRow('Distancia máxima centroides', `${formatNumber(feature.get('centroid_dist_max_m'), 3)} m`)}
                ${propertyRow('Observaciones de borde', feature.get('edge_observations'))}
                ${propertyRow('Máscara válida representativa', formatNumber(feature.get('representative_mask_valid'), 4))}
                ${propertyRow('Regla de decisión', feature.get('decision_rule'))}
            </div>
        `;
    }

    function propertyRow(label, value) {
        return `
            <div class="property-row">
                <div class="property-key">${escapeHtml(label)}</div>
                <div class="property-value">${escapeHtml(value)}</div>
            </div>
        `;
    }

    function updateVisibleCounters() {
        const features = vectorSource.getFeatures();
        let total = 0;
        let mrcnn = 0;
        let yolo = 0;
        let review = 0;

        for (const feature of features) {
            if (!shouldShowFeature(feature)) {
                continue;
            }

            total += 1;

            if (isMrcnn(feature)) {
                mrcnn += 1;
            }

            if (isYolo(feature)) {
                yolo += 1;
            }

            if (requiresReview(feature)) {
                review += 1;
            }
        }

        els.visibleCount.textContent = total;
        els.visibleMrcnnCount.textContent = mrcnn;
        els.visibleYoloCount.textContent = yolo;
        els.visibleReviewCount.textContent = review;
    }

    function refreshVectorLayer() {
        vectorLayer.changed();

        if (selected.getLength() > 0) {
            const chosen = selected.item(0);
            if (!shouldShowFeature(chosen)) {
                selected.clear();
                renderSelected(null);
            }
        }

        updateVisibleCounters();
    }

    function updatePreviewOpacityLabel() {
        if (els.previewOpacityValue) {
            els.previewOpacityValue.textContent = `${els.previewOpacity.value}%`;
        }
    }

    function updateSegOpacityLabel() {
        if (els.segOpacityValue) {
            els.segOpacityValue.textContent = `${els.segOpacity.value}%`;
        }
    }

    async function loadGeoJson() {
        const response = await fetch(geojsonUrl, {
            headers: {
                'Accept': 'application/geo+json, application/json',
            },
            credentials: 'same-origin',
        });

        if (!response.ok) {
            throw new Error(`No fue posible cargar el GeoJSON. HTTP ${response.status}`);
        }

        const json = await response.json();

        const features = new ol.format.GeoJSON().readFeatures(json, {
            dataProjection: 'EPSG:4326',
            featureProjection: mapProjection,
        });

        vectorSource.clear(true);
        vectorSource.addFeatures(features);

        if (features.length !== summaryExpected.total) {
            console.warn('Conteo visual distinto al summary:', {
                expected: summaryExpected.total,
                actual: features.length,
            });
        }

        updateVisibleCounters();
    }

    selectInteraction.on('select', (event) => {
        const feature = event.selected[0] || null;
        renderSelected(feature);
    });

    map.on('pointermove', (event) => {
        if (event.dragging) {
            els.tooltip.classList.remove('active');
            hoveredFeature = null;
            return;
        }

        const feature = map.forEachFeatureAtPixel(
            event.pixel,
            (candidate) => shouldShowFeature(candidate) ? candidate : null,
            { layerFilter: (layer) => layer === vectorLayer, hitTolerance: 5 }
        );

        if (!feature) {
            hoveredFeature = null;
            els.tooltip.classList.remove('active');
            return;
        }

        hoveredFeature = feature;

        const model = getPrimaryModel(feature);
        const badgeClass = model === 'Mask R-CNN' ? 'badge-success' : 'badge-yolo';

        els.tooltip.innerHTML = `
            <div class="tooltip-title">${escapeHtml(feature.get('operational_id') || 'Objeto')}</div>
            <div class="tooltip-sub">
                ${escapeHtml(model)} · Área ${formatNumber(feature.get('area_m2'), 2)} m²
            </div>
            <div class="tooltip-badge ${badgeClass}">
                ${escapeHtml(requiresReview(feature) ? 'Revisión requerida' : 'Sin revisión operativa')}
            </div>
        `;

        els.tooltip.style.left = `${event.pixel[0]}px`;
        els.tooltip.style.top = `${event.pixel[1]}px`;
        els.tooltip.classList.add('active');
    });

    map.getViewport().addEventListener('mouseleave', () => {
        hoveredFeature = null;
        els.tooltip.classList.remove('active');
    });

    if (els.togglePreview && previewLayer) {
        els.togglePreview.addEventListener('change', () => {
            previewLayer.setVisible(els.togglePreview.checked);
        });
    }

    els.toggleMrcnn.addEventListener('change', refreshVectorLayer);
    els.toggleYolo.addEventListener('change', refreshVectorLayer);
    els.toggleReview.addEventListener('change', refreshVectorLayer);

    if (els.previewOpacity && previewLayer) {
        updatePreviewOpacityLabel();
        els.previewOpacity.addEventListener('input', () => {
            const opacity = Number(els.previewOpacity.value) / 100;
            previewLayer.setOpacity(opacity);
            updatePreviewOpacityLabel();
        });
    }

    updateSegOpacityLabel();
    els.segOpacity.addEventListener('input', () => {
        currentSegOpacity = Number(els.segOpacity.value) / 100;
        updateSegOpacityLabel();
        vectorLayer.changed();
    });

    els.fitExtent.addEventListener('click', fitMap);

    els.clearSelection.addEventListener('click', () => {
        selected.clear();
        renderSelected(null);
    });

    loadGeoJson().catch((error) => {
        console.error(error);

        els.objectDetails.innerHTML = `
            <div class="warn-box">
                No fue posible cargar la capa GeoJSON del análisis. Revisa la consola del navegador y confirma que el artifact
                <strong>geojson</strong> exista y sea accesible desde Laravel.
            </div>
        `;
    });
})();
</script>
</body>
</html>
