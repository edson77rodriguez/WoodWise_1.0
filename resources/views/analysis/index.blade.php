<!DOCTYPE html>

<html lang="es">

<head>

    <meta charset="UTF-8">

    <meta
        name="viewport"
        content="width=device-width, initial-scale=1.0"
    >

    <meta
        name="csrf-token"
        content="{{ csrf_token() }}"
    >

    <title>
        UAV Forest AI
    </title>


    <style>

        :root {

            --bg: #f3f6f5;
            --surface: #ffffff;
            --surface-soft: #f8faf9;

            --text: #18211d;
            --muted: #68746e;

            --border: #dde6e1;

            --forest: #176b4d;
            --forest-dark: #105039;
            --forest-soft: #e9f4ef;

            --yolo: #2680d9;
            --yolo-soft: #eaf4fe;

            --mask: #1ba66b;
            --mask-soft: #e9f8f0;

            --match: #8a46c7;
            --match-soft: #f3eafa;

            --warning: #956415;
            --warning-soft: #fff7e5;

            --danger: #b42318;
            --danger-soft: #fff0ee;

            --shadow:
                0 12px 35px
                rgba(20,50,35,.07);

            --radius: 16px;
        }


        * {
            box-sizing: border-box;
        }


        body {

            margin: 0;

            font-family:
                Inter,
                -apple-system,
                BlinkMacSystemFont,
                "Segoe UI",
                Roboto,
                Arial,
                sans-serif;

            background: var(--bg);

            color: var(--text);
        }


        /*
        |--------------------------------------------------------------------------
        | Header
        |--------------------------------------------------------------------------
        */

        .topbar {

            background:
                linear-gradient(
                    130deg,
                    #103b2d,
                    #176b4d
                );

            color: white;

            padding: 19px 30px;

            box-shadow:
                0 5px 20px
                rgba(0,0,0,.14);
        }


        .topbar-inner {

            max-width: 1450px;

            margin: auto;

            display: flex;

            justify-content:
                space-between;

            align-items: center;

            gap: 20px;
        }


        .brand {

            display: flex;

            gap: 13px;

            align-items: center;
        }


        .brand-logo {

            width: 46px;
            height: 46px;

            border-radius: 13px;

            display: grid;

            place-items: center;

            background:
                rgba(255,255,255,.13);

            font-size: 24px;
        }


        .brand-name {

            font-size: 19px;

            font-weight: 750;
        }


        .brand-subtitle {

            margin-top: 2px;

            font-size: 12px;

            opacity: .76;
        }


        .api-status {

            display: inline-flex;

            align-items: center;

            gap: 8px;

            padding: 8px 13px;

            border-radius: 999px;

            background:
                rgba(255,255,255,.12);

            font-size: 12px;

            font-weight: 600;
        }


        .api-dot {

            width: 8px;

            height: 8px;

            border-radius: 50%;
        }


        .online {
            background: #72e3a5;
        }


        .offline {
            background: #ff8c8c;
        }


        /*
        |--------------------------------------------------------------------------
        | Main
        |--------------------------------------------------------------------------
        */

        main {

            max-width: 1450px;

            margin: auto;

            padding: 32px;
        }


        .intro {

            margin-bottom: 25px;
        }


        .intro h1 {

            margin: 0 0 7px;

            font-size: 29px;
        }


        .intro p {

            margin: 0;

            max-width: 900px;

            color: var(--muted);

            line-height: 1.55;
        }


        /*
        |--------------------------------------------------------------------------
        | Alerts
        |--------------------------------------------------------------------------
        */

        .alert {

            margin-bottom: 20px;

            padding: 15px 18px;

            border-radius: 12px;

            line-height: 1.5;

            font-size: 13px;
        }


        .alert-success {

            color: var(--forest-dark);

            background: var(--forest-soft);

            border:
                1px solid #bcdccc;
        }


        .alert-error {

            color: var(--danger);

            background:
                var(--danger-soft);

            border:
                1px solid #efc8c4;
        }


        .alert-warning {

            color: var(--warning);

            background:
                var(--warning-soft);

            border:
                1px solid #ecd69c;
        }


        /*
        |--------------------------------------------------------------------------
        | General
        |--------------------------------------------------------------------------
        */

        .card {

            background: var(--surface);

            border:
                1px solid var(--border);

            border-radius:
                var(--radius);

            box-shadow:
                var(--shadow);
        }


        .analysis-grid {

            display: grid;

            grid-template-columns:
                350px
                minmax(0,1fr);

            gap: 24px;

            align-items: start;
        }


        /*
        |--------------------------------------------------------------------------
        | Configuration
        |--------------------------------------------------------------------------
        */

        .settings {

            padding: 24px;

            position: sticky;

            top: 20px;
        }


        .section-title {

            margin: 0;

            font-size: 17px;
        }


        .section-description {

            margin: 6px 0 22px;

            color: var(--muted);

            font-size: 13px;

            line-height: 1.5;
        }


        .field {

            margin-bottom: 20px;
        }


        label {

            display: block;

            font-size: 13px;

            font-weight: 650;

            margin-bottom: 8px;
        }


        select,
        input {

            width: 100%;

            padding: 11px 12px;

            border:
                1px solid var(--border);

            border-radius: 10px;

            background: white;

            color: var(--text);

            font-size: 14px;
        }


        select:focus,
        input:focus {

            outline: none;

            border-color:
                var(--forest);

            box-shadow:
                0 0 0 3px
                rgba(23,107,77,.10);
        }


        .helper {

            margin-top: 7px;

            color: var(--muted);

            font-size: 11px;

            line-height: 1.5;
        }


        /*
        |--------------------------------------------------------------------------
        | Upload
        |--------------------------------------------------------------------------
        */

        .upload {

            display: block;

            padding: 20px;

            border:
                2px dashed #ccd9d2;

            border-radius: 12px;

            background:
                var(--surface-soft);

            text-align: center;

            cursor: pointer;

            transition: .2s;
        }


        .upload:hover {

            border-color:
                var(--forest);

            background:
                var(--forest-soft);
        }


        .upload input {

            display: none;
        }


        .upload-icon {

            font-size: 29px;
        }


        .upload-name {

            margin-top: 7px;

            font-weight: 650;

            font-size: 13px;
        }


        .upload-small {

            margin-top: 6px;

            color: var(--muted);

            font-size: 10px;
        }


        /*
        |--------------------------------------------------------------------------
        | Comparison configuration
        |--------------------------------------------------------------------------
        */

        .compare-options {

            display: none;

            padding: 15px;

            margin-bottom: 20px;

            border-radius: 12px;

            background:
                #f8f6fb;

            border:
                1px solid #e3d8ee;
        }


        .compare-options.visible {

            display: block;
        }


        .compare-title {

            font-size: 12px;

            font-weight: 700;

            margin-bottom: 15px;

            color: #67418a;
        }


        /*
        |--------------------------------------------------------------------------
        | Button
        |--------------------------------------------------------------------------
        */

        .button {

            width: 100%;

            padding: 13px 16px;

            border: none;

            border-radius: 10px;

            background:
                var(--forest);

            color: white;

            cursor: pointer;

            font-weight: 700;

            transition: .2s;
        }


        .button:hover {

            background:
                var(--forest-dark);

            transform:
                translateY(-1px);
        }


        .button:disabled {

            opacity: .65;

            cursor: wait;
        }


        /*
        |--------------------------------------------------------------------------
        | Scientific note
        |--------------------------------------------------------------------------
        */

        .science-note {

            margin-top: 20px;

            padding: 14px;

            border-radius: 11px;

            background: #eef4f8;

            color: #456171;

            font-size: 11px;

            line-height: 1.55;
        }


        /*
        |--------------------------------------------------------------------------
        | Workspace
        |--------------------------------------------------------------------------
        */

        .workspace {

            overflow: hidden;
        }


        .workspace-header {

            min-height: 67px;

            padding: 15px 20px;

            display: flex;

            align-items: center;

            justify-content:
                space-between;

            gap: 20px;

            border-bottom:
                1px solid var(--border);
        }


        .workspace-title {

            font-size: 14px;

            font-weight: 700;
        }


        .workspace-description {

            margin-top: 3px;

            color: var(--muted);

            font-size: 11px;
        }


        /*
        |--------------------------------------------------------------------------
        | Viewer
        |--------------------------------------------------------------------------
        */

        .viewer {

            position: relative;

            min-height: 540px;

            display: flex;

            align-items: center;

            justify-content: center;

            overflow: hidden;

            background: #17201c;
        }


        .viewer-placeholder {

            max-width: 420px;

            padding: 50px;

            text-align: center;

            color:
                rgba(255,255,255,.62);

            line-height: 1.6;
        }


        .placeholder-icon {

            margin-bottom: 12px;

            font-size: 50px;
        }


        .image-wrapper {

            position: relative;

            display: inline-block;

            max-width: 100%;

            line-height: 0;
        }


        .image-wrapper img {

            display: block;

            width: auto;

            height: auto;

            max-width: 100%;

            max-height: 740px;
        }


        .image-wrapper canvas {

            position: absolute;

            top: 0;
            left: 0;

            width: 100%;
            height: 100%;

            pointer-events: none;
        }


        /*
        |--------------------------------------------------------------------------
        | Viewer controls
        |--------------------------------------------------------------------------
        */

        .viewer-controls {

            display: flex;

            gap: 14px;

            flex-wrap: wrap;
        }


        .viewer-controls label {

            display: flex;

            align-items: center;

            gap: 5px;

            margin: 0;

            color: var(--muted);

            font-size: 11px;

            font-weight: 500;
        }


        .viewer-controls input {

            width: auto;
        }


        /*
        |--------------------------------------------------------------------------
        | Legend
        |--------------------------------------------------------------------------
        */

        .legend {

            display: flex;

            gap: 15px;

            flex-wrap: wrap;

            padding: 12px 20px;

            border-top:
                1px solid var(--border);

            background:
                var(--surface-soft);

            font-size: 11px;
        }


        .legend-item {

            display: flex;

            align-items: center;

            gap: 6px;
        }


        .legend-color {

            width: 11px;

            height: 11px;

            border-radius: 3px;
        }


        .legend-yolo {
            background: var(--yolo);
        }


        .legend-mask {
            background: var(--mask);
        }


        .legend-match {
            background: var(--match);
        }


        /*
        |--------------------------------------------------------------------------
        | Metrics
        |--------------------------------------------------------------------------
        */

        .metrics {

            padding: 18px;

            display: grid;

            grid-template-columns:
                repeat(5,1fr);

            gap: 12px;

            border-top:
                1px solid var(--border);

            background:
                var(--surface-soft);
        }


        .metric {

            padding: 13px;

            border:
                1px solid var(--border);

            border-radius: 11px;

            background: white;
        }


        .metric-name {

            margin-bottom: 4px;

            color: var(--muted);

            font-size: 10px;
        }


        .metric-value {

            font-size: 20px;

            font-weight: 750;
        }


        .metric-yolo {

            color: var(--yolo);
        }


        .metric-mask {

            color: var(--mask);
        }


        .metric-match {

            color: var(--match);
        }


        /*
        |--------------------------------------------------------------------------
        | Details
        |--------------------------------------------------------------------------
        */

        .detail-card {

            margin-top: 24px;

            padding: 22px;
        }


        /*
        |--------------------------------------------------------------------------
        | Tables
        |--------------------------------------------------------------------------
        */

        .table-wrapper {

            overflow-x: auto;
        }


        table {

            width: 100%;

            border-collapse: collapse;

            font-size: 12px;
        }


        th {

            padding: 11px;

            border-bottom:
                1px solid var(--border);

            text-align: left;

            color: var(--muted);

            font-size: 10px;

            text-transform: uppercase;

            letter-spacing: .04em;
        }


        td {

            padding: 12px 11px;

            border-bottom:
                1px solid var(--border);
        }


        tbody tr:hover {

            background:
                var(--surface-soft);
        }


        .badge {

            display: inline-block;

            padding: 5px 8px;

            border-radius: 8px;

            font-size: 10px;

            font-weight: 700;
        }


        .badge-yolo {

            background:
                var(--yolo-soft);

            color: var(--yolo);
        }


        .badge-mask {

            background:
                var(--mask-soft);

            color: var(--mask);
        }


        .badge-match {

            background:
                var(--match-soft);

            color: var(--match);
        }


        /*
        |--------------------------------------------------------------------------
        | Responsive
        |--------------------------------------------------------------------------
        */

        @media(max-width:1050px) {

            .analysis-grid {

                grid-template-columns: 1fr;
            }


            .settings {

                position: static;
            }
        }


        @media(max-width:800px) {

            main {

                padding: 18px;
            }


            .metrics {

                grid-template-columns:
                    repeat(2,1fr);
            }


            .topbar-inner {

                align-items:
                    flex-start;

                flex-direction:
                    column;
            }
        }


        @media(max-width:500px) {

            .metrics {

                grid-template-columns:
                    1fr;
            }
        }


        /*
        |--------------------------------------------------------------------------
        | Wall-to-wall execution
        |--------------------------------------------------------------------------
        */

        .wall-execution {

            margin-top: 28px;

            padding: 24px;
        }


        .wall-execution-header {

            display: flex;

            align-items: flex-start;

            justify-content: space-between;

            gap: 20px;

            margin-bottom: 22px;
        }


        .wall-execution-title {

            margin: 0;

            font-size: 19px;
        }


        .wall-execution-subtitle {

            margin-top: 6px;

            color: var(--muted);

            font-size: 12px;

            line-height: 1.5;
        }


        .wall-api-badge {

            display: inline-flex;

            align-items: center;

            gap: 7px;

            padding: 7px 11px;

            border-radius: 999px;

            font-size: 11px;

            font-weight: 700;

            white-space: nowrap;
        }


        .wall-api-badge.online-state {

            background:
                var(--forest-soft);

            color:
                var(--forest-dark);
        }


        .wall-api-badge.offline-state {

            background:
                var(--danger-soft);

            color:
                var(--danger);
        }


        .wall-selector {

            margin-bottom: 20px;
        }


        .wall-selector label {

            margin-bottom: 8px;
        }


        .wall-mosaic-info {

            display: grid;

            grid-template-columns:
                repeat(4, minmax(0,1fr));

            gap: 12px;

            margin-bottom: 20px;
        }


        .wall-mosaic-item {

            padding: 14px;

            border:
                1px solid var(--border);

            border-radius: 11px;

            background:
                var(--surface-soft);
        }


        .wall-mosaic-label {

            margin-bottom: 5px;

            color:
                var(--muted);

            font-size: 10px;

            text-transform: uppercase;

            letter-spacing: .03em;
        }


        .wall-mosaic-value {

            font-size: 13px;

            font-weight: 700;

            word-break: break-word;
        }


        .wall-frozen-config {

            margin-bottom: 18px;

            padding: 15px;

            border-radius: 11px;

            background:
                #eef6f2;

            border:
                1px solid #d2e7dc;

            color:
                #456156;

            font-size: 11px;

            line-height: 1.6;
        }


        .wall-run-button {

            width: 100%;
        }


        .wall-empty {

            padding: 15px 18px;

            border-radius: 12px;

            background:
                var(--warning-soft);

            border:
                1px solid #ecd69c;

            color:
                var(--warning);

            font-size: 12px;

            line-height: 1.5;
        }


        @media(max-width:900px) {

            .wall-mosaic-info {

                grid-template-columns:
                    repeat(2, minmax(0,1fr));
            }


            .wall-execution-header {

                flex-direction: column;
            }
        }


        @media(max-width:500px) {

            .wall-mosaic-info {

                grid-template-columns:
                    1fr;
            }
        }


        /*
        |--------------------------------------------------------------------------
        | Persistent wall-to-wall analysis
        |--------------------------------------------------------------------------
        */

        .wall-analysis {

            margin-top: 28px;

            overflow: hidden;
        }


        .wall-analysis-header {

            padding: 22px 24px;

            display: flex;

            align-items: flex-start;

            justify-content: space-between;

            gap: 20px;

            border-bottom:
                1px solid var(--border);
        }


        .wall-analysis-title {

            margin: 0;

            font-size: 19px;
        }


        .wall-analysis-subtitle {

            margin-top: 6px;

            color: var(--muted);

            font-size: 12px;

            line-height: 1.5;
        }


        .wall-status {

            display: inline-flex;

            align-items: center;

            gap: 7px;

            padding: 7px 11px;

            border-radius: 999px;

            background:
                var(--forest-soft);

            color:
                var(--forest-dark);

            font-size: 11px;

            font-weight: 700;

            white-space: nowrap;
        }


        .wall-summary {

            padding: 20px 24px;

            display: grid;

            grid-template-columns:
                repeat(4, minmax(0,1fr));

            gap: 12px;
        }


        .wall-stat {

            padding: 15px;

            border:
                1px solid var(--border);

            border-radius: 12px;

            background:
                var(--surface-soft);
        }


        .wall-stat-name {

            margin-bottom: 5px;

            color:
                var(--muted);

            font-size: 10px;

            text-transform: uppercase;

            letter-spacing: .03em;
        }


        .wall-stat-value {

            font-size: 23px;

            font-weight: 750;
        }


        .wall-stat-detail {

            margin-top: 4px;

            color:
                var(--muted);

            font-size: 10px;

            line-height: 1.4;
        }


        .wall-method {

            margin:
                0 24px 20px;

            padding: 16px;

            border-radius: 12px;

            background:
                #eef6f2;

            border:
                1px solid #d2e7dc;

            font-size: 12px;

            line-height: 1.6;
        }


        .wall-warning {

            margin:
                0 24px 20px;

            padding: 14px 16px;

            border-radius: 12px;

            background:
                var(--warning-soft);

            border:
                1px solid #ecd69c;

            color:
                var(--warning);

            font-size: 12px;

            line-height: 1.5;
        }


        .artifact-actions {

            padding:
                0 24px 24px;

            display: flex;

            flex-wrap: wrap;

            gap: 10px;
        }


        .artifact-button {

            display: inline-flex;

            align-items: center;

            justify-content: center;

            padding: 10px 14px;

            border-radius: 10px;

            text-decoration: none;

            font-size: 12px;

            font-weight: 700;

            transition: .2s;
        }


        .artifact-button-primary {

            background:
                var(--forest);

            color: white;
        }


        .artifact-button-primary:hover {

            background:
                var(--forest-dark);
        }


        .artifact-button-secondary {

            background: white;

            color:
                var(--forest-dark);

            border:
                1px solid var(--border);
        }


        .artifact-button-secondary:hover {

            background:
                var(--forest-soft);
        }


        .wall-analysis-meta {

            padding:
                0 24px 20px;

            color:
                var(--muted);

            font-size: 10px;

            line-height: 1.5;
        }


        @media(max-width:900px) {

            .wall-summary {

                grid-template-columns:
                    repeat(2, minmax(0,1fr));
            }


            .wall-analysis-header {

                flex-direction: column;
            }
        }


        @media(max-width:500px) {

            .wall-summary {

                grid-template-columns:
                    1fr;
            }
        }

    </style>

</head>


<body>


<header class="topbar">

    <div class="topbar-inner">

        <div class="brand">

            <div class="brand-logo">
                🌲
            </div>

            <div>

                <div class="brand-name">
                    UAV Forest AI
                </div>

                <div class="brand-subtitle">
                    Dasometría computacional y análisis UAV
                </div>

            </div>

        </div>


        <div class="api-status">

            @if(
                ($apiStatus['status'] ?? 'offline')
                === 'ok'
            )

                <span
                    class="api-dot online"
                ></span>

                API IA conectada

                @if(
                    !empty($apiStatus['version'])
                )

                    · v{{ $apiStatus['version'] }}

                @endif

            @else

                <span
                    class="api-dot offline"
                ></span>

                API IA desconectada

            @endif

        </div>

    </div>

</header>


<main>


@php

    $isCompare =
        $analysisMode === 'both';

    $singleCrowns =
        !$isCompare
            ? ($result['crowns'] ?? [])
            : [];

    $yoloCrowns =
        $isCompare
            ? ($result['yolo']['crowns'] ?? [])
            : [];

    $maskCrowns =
        $isCompare
            ? ($result['maskrcnn']['crowns'] ?? [])
            : [];

    $matches =
        $isCompare
            ? ($result['matches'] ?? [])
            : [];

    $summary =
        $isCompare
            ? ($result['summary'] ?? [])
            : [];

@endphp


<div class="intro">

    <h1>
        Análisis de Copas UAV
    </h1>

    <p>
        Ejecuta YOLO-Seg, Mask R-CNN o ambos modelos sobre una
        misma imagen UAV y analiza visualmente sus propuestas
        de segmentación y su nivel de acuerdo espacial.
    </p>

</div>


@if(session('success'))

    <div class="alert alert-success">

        {{ session('success') }}

    </div>

@endif


@if($errors->any())

    <div class="alert alert-error">

        <strong>
            Revisa los datos ingresados.
        </strong>

        @foreach(
            $errors->all()
            as $error
        )

            <div>
                {{ $error }}
            </div>

        @endforeach

    </div>

@endif


@if(session('error'))

    <div class="alert alert-error">

        <strong>
            Error durante el análisis.
        </strong>

        <br>

        {{ session('error') }}

    </div>

@endif



<div class="analysis-grid">


    {{-- ==========================================================
         CONFIGURACIÓN
         ========================================================== --}}

    <aside class="card settings">


        <h2 class="section-title">
            Configuración
        </h2>


        <p class="section-description">

            Selecciona una imagen, el modelo
            y los parámetros de inferencia.

        </p>


        <form
            id="analysisForm"
            method="POST"
            action="{{ route('analysis.analyze') }}"
            enctype="multipart/form-data"
        >

            @csrf


            {{-- Imagen --}}

            <div class="field">

                <label>
                    Imagen UAV
                </label>


                <label class="upload">

                    <input
                        id="imageInput"
                        type="file"
                        name="image"
                        accept=".jpg,.jpeg,.png,.webp"
                        required
                    >


                    <div class="upload-icon">
                        🛰️
                    </div>


                    <div
                        id="uploadName"
                        class="upload-name"
                    >
                        Seleccionar imagen
                    </div>


                    <div class="upload-small">

                        JPG · PNG · WEBP
                        <br>
                        máximo 10 MB

                    </div>

                </label>

            </div>


            {{-- Modelo --}}

            <div class="field">

                <label>
                    Modo de análisis
                </label>


                <select
                    id="modelSelect"
                    name="model"
                    required
                >

                    <option
                        value="yolo"
                        {{
                            old(
                                'model',
                                $analysisMode
                            ) === 'yolo'
                                ? 'selected'
                                : ''
                        }}
                    >
                        YOLO-Seg E1.1
                    </option>


                    <option
                        value="maskrcnn"
                        {{
                            old(
                                'model',
                                $analysisMode
                            ) === 'maskrcnn'
                                ? 'selected'
                                : ''
                        }}
                    >
                        Mask R-CNN M1.0
                    </option>


                    <option
                        value="both"
                        {{
                            old(
                                'model',
                                $analysisMode
                            ) === 'both'
                                ? 'selected'
                                : ''
                        }}
                    >
                        Comparar ambos
                    </option>

                </select>


                <div
                    id="modelHelp"
                    class="helper"
                ></div>

            </div>


            {{-- Single threshold --}}

            <div
                id="singleThresholdGroup"
                class="field"
            >

                <label>
                    Threshold de confianza
                </label>


                <input
                    id="thresholdInput"
                    type="number"
                    name="threshold"
                    min="0.01"
                    max="0.99"
                    step="0.01"
                    value="{{ old('threshold', 0.25) }}"
                >


                <div
                    id="thresholdHelp"
                    class="helper"
                ></div>

            </div>


            {{-- Comparison settings --}}

            <div
                id="compareOptions"
                class="compare-options"
            >

                <div class="compare-title">
                    Parámetros de comparación
                </div>


                <div class="field">

                    <label>
                        YOLO threshold
                    </label>

                    <input
                        type="number"
                        name="yolo_threshold"
                        min="0.01"
                        max="0.99"
                        step="0.01"
                        value="{{
                            old(
                                'yolo_threshold',
                                0.25
                            )
                        }}"
                    >

                    <div class="helper">
                        Threshold seleccionado
                        mediante VALIDATION.
                    </div>

                </div>


                <div class="field">

                    <label>
                        Mask R-CNN threshold
                    </label>

                    <input
                        type="number"
                        name="maskrcnn_threshold"
                        min="0.01"
                        max="0.99"
                        step="0.01"
                        value="{{
                            old(
                                'maskrcnn_threshold',
                                0.40
                            )
                        }}"
                    >

                    <div class="helper">
                        Threshold seleccionado
                        mediante VALIDATION.
                    </div>

                </div>


                <div class="field">

                    <label>
                        IoU mínimo de coincidencia
                    </label>

                    <input
                        type="number"
                        name="matching_iou"
                        min="0.01"
                        max="0.99"
                        step="0.01"
                        value="{{
                            old(
                                'matching_iou',
                                0.50
                            )
                        }}"
                    >

                    <div class="helper">

                        Define el acuerdo espacial mínimo
                        para emparejar una predicción YOLO
                        con una predicción Mask R-CNN.

                    </div>

                </div>

            </div>


            <button
                id="analyzeButton"
                class="button"
                type="submit"
            >

                <span id="buttonText">
                    Analizar imagen
                </span>

            </button>

        </form>


        <div class="science-note">

            <strong>
                Nota metodológica
            </strong>

            <br><br>

            El confidence es un score interno del modelo.
            No debe interpretarse directamente como
            probabilidad de que la detección sea correcta.

            <br><br>

            En el modo comparación, el IoU representa
            <strong>acuerdo espacial entre modelos</strong>,
            no exactitud respecto a la verdad de campo.

        </div>

    </aside>



    {{-- ==========================================================
         WORKSPACE
         ========================================================== --}}

    <section>


        <div class="card workspace">


            <div class="workspace-header">


                <div>

                    <div class="workspace-title">
                        Visor de inferencia
                    </div>

                    <div class="workspace-description">

                        Imagen original +
                        segmentaciones producidas por IA

                    </div>

                </div>


                @if($result)


                    <div class="viewer-controls">


                        @if($isCompare)

                            <label>

                                <input
                                    id="showYolo"
                                    type="checkbox"
                                    checked
                                >

                                YOLO

                            </label>


                            <label>

                                <input
                                    id="showMask"
                                    type="checkbox"
                                    checked
                                >

                                Mask R-CNN

                            </label>


                            <label>

                                <input
                                    id="showMatches"
                                    type="checkbox"
                                    checked
                                >

                                Coincidencias

                            </label>

                        @else

                            <label>

                                <input
                                    id="showFill"
                                    type="checkbox"
                                    checked
                                >

                                Máscara

                            </label>

                        @endif


                        <label>

                            <input
                                id="showLabels"
                                type="checkbox"
                                checked
                            >

                            Etiquetas

                        </label>


                    </div>


                @endif

            </div>



            <div class="viewer">


                @if(
                    $result
                    &&
                    $imageUrl
                )


                    <div class="image-wrapper">

                        <img
                            id="uavImage"
                            src="{{ $imageUrl }}"
                            alt="Imagen UAV analizada"
                        >

                        <canvas
                            id="maskCanvas"
                        ></canvas>

                    </div>


                @else


                    <div
                        id="viewerPlaceholder"
                        class="viewer-placeholder"
                    >

                        <div class="placeholder-icon">
                            🌳
                        </div>

                        Selecciona una imagen UAV
                        y ejecuta un análisis para
                        comenzar.

                    </div>


                    <img
                        id="previewImage"
                        style="
                            display:none;
                            max-width:100%;
                            max-height:740px;
                        "
                        alt="Vista previa"
                    >


                @endif


            </div>



            @if($result)


                @if($isCompare)


                    <div class="legend">

                        <div class="legend-item">

                            <span
                                class="
                                    legend-color
                                    legend-yolo
                                "
                            ></span>

                            YOLO-Seg

                        </div>


                        <div class="legend-item">

                            <span
                                class="
                                    legend-color
                                    legend-mask
                                "
                            ></span>

                            Mask R-CNN

                        </div>


                        <div class="legend-item">

                            <span
                                class="
                                    legend-color
                                    legend-match
                                "
                            ></span>

                            Coincidencia espacial

                        </div>

                    </div>


                    <div class="metrics">


                        <div class="metric">

                            <div class="metric-name">
                                YOLO
                            </div>

                            <div
                                class="
                                    metric-value
                                    metric-yolo
                                "
                            >
                                {{
                                    $summary[
                                        'yolo_detections'
                                    ] ?? 0
                                }}
                            </div>

                        </div>


                        <div class="metric">

                            <div class="metric-name">
                                Mask R-CNN
                            </div>

                            <div
                                class="
                                    metric-value
                                    metric-mask
                                "
                            >
                                {{
                                    $summary[
                                        'maskrcnn_detections'
                                    ] ?? 0
                                }}
                            </div>

                        </div>


                        <div class="metric">

                            <div class="metric-name">
                                Coincidencias
                            </div>

                            <div
                                class="
                                    metric-value
                                    metric-match
                                "
                            >
                                {{
                                    $summary[
                                        'matches'
                                    ] ?? 0
                                }}
                            </div>

                        </div>


                        <div class="metric">

                            <div class="metric-name">
                                Solo YOLO
                            </div>

                            <div class="metric-value">
                                {{
                                    $summary[
                                        'only_yolo'
                                    ] ?? 0
                                }}
                            </div>

                        </div>


                        <div class="metric">

                            <div class="metric-name">
                                Solo Mask R-CNN
                            </div>

                            <div class="metric-value">
                                {{
                                    $summary[
                                        'only_maskrcnn'
                                    ] ?? 0
                                }}
                            </div>

                        </div>


                    </div>


                @else


                    <div class="metrics">


                        <div class="metric">

                            <div class="metric-name">
                                Modelo
                            </div>

                            <div
                                class="metric-value"
                                style="font-size:14px;"
                            >
                                {{
                                    $result[
                                        'model'
                                    ] ?? '—'
                                }}
                            </div>

                        </div>


                        <div class="metric">

                            <div class="metric-name">
                                Threshold
                            </div>

                            <div class="metric-value">

                                {{
                                    number_format(
                                        $result[
                                            'threshold'
                                        ] ?? 0,
                                        2
                                    )
                                }}

                            </div>

                        </div>


                        <div class="metric">

                            <div class="metric-name">
                                Copas candidatas
                            </div>

                            <div class="metric-value">

                                {{
                                    $result[
                                        'detections'
                                    ] ?? 0
                                }}

                            </div>

                        </div>


                        <div class="metric">

                            <div class="metric-name">
                                Resolución
                            </div>

                            <div
                                class="metric-value"
                                style="font-size:14px;"
                            >

                                {{
                                    $result[
                                        'width'
                                    ] ?? '?'
                                }}

                                ×

                                {{
                                    $result[
                                        'height'
                                    ] ?? '?'
                                }}

                            </div>

                        </div>

                    </div>


                @endif


            @endif


        </div>



        {{-- ======================================================
             COMPARISON TABLE
             ====================================================== --}}

        @if(
            $result
            &&
            $isCompare
        )


            <div class="card detail-card">


                <h2 class="section-title">
                    Coincidencias entre modelos
                </h2>


                <p class="section-description">

                    Emparejamiento uno a uno realizado
                    mediante IoU de máscaras.

                </p>


                @if(count($matches) > 0)


                    <div class="table-wrapper">


                        <table>


                            <thead>

                                <tr>

                                    <th>
                                        Match
                                    </th>

                                    <th>
                                        YOLO
                                    </th>

                                    <th>
                                        Mask R-CNN
                                    </th>

                                    <th>
                                        IoU
                                    </th>

                                    <th>
                                        Conf. YOLO
                                    </th>

                                    <th>
                                        Conf. Mask
                                    </th>

                                </tr>

                            </thead>


                            <tbody>


                                @foreach(
                                    $matches
                                    as $match
                                )


                                    <tr>


                                        <td>

                                            <span
                                                class="
                                                    badge
                                                    badge-match
                                                "
                                            >

                                                #{{ $match['id'] ?? '?' }}

                                            </span>

                                        </td>


                                        <td>

                                            <span
                                                class="
                                                    badge
                                                    badge-yolo
                                                "
                                            >

                                                #{{
                                                    $match[
                                                        'yolo_id'
                                                    ] ?? '?'
                                                }}

                                            </span>

                                        </td>


                                        <td>

                                            <span
                                                class="
                                                    badge
                                                    badge-mask
                                                "
                                            >

                                                #{{
                                                    $match[
                                                        'maskrcnn_id'
                                                    ] ?? '?'
                                                }}

                                            </span>

                                        </td>


                                        <td>

                                            <strong>

                                                {{
                                                    number_format(
                                                        $match[
                                                            'iou'
                                                        ] ?? 0,
                                                        3
                                                    )
                                                }}

                                            </strong>

                                        </td>


                                        <td>

                                            {{
                                                number_format(
                                                    $match[
                                                        'yolo_confidence'
                                                    ] ?? 0,
                                                    3
                                                )
                                            }}

                                        </td>


                                        <td>

                                            {{
                                                number_format(
                                                    $match[
                                                        'maskrcnn_confidence'
                                                    ] ?? 0,
                                                    3
                                                )
                                            }}

                                        </td>


                                    </tr>


                                @endforeach


                            </tbody>

                        </table>

                    </div>


                @else


                    <div class="alert alert-warning">

                        No se encontraron coincidencias
                        con el IoU mínimo seleccionado.

                    </div>


                @endif


            </div>


            <div class="card detail-card">


                <h2 class="section-title">
                    Configuración experimental
                </h2>


                <p class="section-description">
                    Parámetros utilizados en esta comparación.
                </p>


                <table>

                    <tbody>

                        <tr>

                            <td>
                                YOLO threshold
                            </td>

                            <td>
                                <strong>
                                    {{
                                        number_format(
                                            $result[
                                                'configuration'
                                            ][
                                                'yolo_threshold'
                                            ] ?? 0,
                                            2
                                        )
                                    }}
                                </strong>
                            </td>

                        </tr>


                        <tr>

                            <td>
                                Mask R-CNN threshold
                            </td>

                            <td>
                                <strong>
                                    {{
                                        number_format(
                                            $result[
                                                'configuration'
                                            ][
                                                'maskrcnn_threshold'
                                            ] ?? 0,
                                            2
                                        )
                                    }}
                                </strong>
                            </td>

                        </tr>


                        <tr>

                            <td>
                                Matching IoU
                            </td>

                            <td>
                                <strong>
                                    {{
                                        number_format(
                                            $result[
                                                'configuration'
                                            ][
                                                'matching_iou'
                                            ] ?? 0,
                                            2
                                        )
                                    }}
                                </strong>
                            </td>

                        </tr>


                        <tr>

                            <td>
                                Tiempo YOLO
                            </td>

                            <td>
                                {{
                                    $result[
                                        'yolo'
                                    ][
                                        'processing_seconds'
                                    ] ?? '—'
                                }}
                                s
                            </td>

                        </tr>


                        <tr>

                            <td>
                                Tiempo Mask R-CNN
                            </td>

                            <td>
                                {{
                                    $result[
                                        'maskrcnn'
                                    ][
                                        'processing_seconds'
                                    ] ?? '—'
                                }}
                                s
                            </td>

                        </tr>

                    </tbody>

                </table>


            </div>


        @elseif(
            $result
            &&
            count($singleCrowns) > 0
        )


            <div class="card detail-card">


                <h2 class="section-title">
                    Copas candidatas
                </h2>


                <p class="section-description">

                    Instancias propuestas por
                    {{ $result['model'] ?? 'el modelo' }}.

                </p>


                <div class="table-wrapper">


                    <table>

                        <thead>

                            <tr>

                                <th>
                                    Copa
                                </th>

                                <th>
                                    Confidence
                                </th>

                            </tr>

                        </thead>


                        <tbody>

                            @foreach(
                                $singleCrowns
                                as $crown
                            )

                                <tr>

                                    <td>
                                        #{{ $crown['id'] ?? '?' }}
                                    </td>

                                    <td>

                                        {{
                                            number_format(
                                                $crown[
                                                    'confidence'
                                                ] ?? 0,
                                                3
                                            )
                                        }}

                                    </td>

                                </tr>

                            @endforeach

                        </tbody>

                    </table>

                </div>


            </div>


        @endif


    </section>


</div>


{{-- ==========================================================
     EJECUCIÓN WALL-TO-WALL V0.7E
     ========================================================== --}}

<section class="card wall-execution">

    <div class="wall-execution-header">

        <div>

            <h2 class="wall-execution-title">
                Análisis wall-to-wall
            </h2>

            <div class="wall-execution-subtitle">

                Ejecuta el pipeline V0.7E sobre un ortomosaico
                completo utilizando el método científico V0.6G.

            </div>

        </div>


        @if(
            ($apiStatus['status'] ?? 'offline')
            === 'ok'
        )

            <div class="wall-api-badge online-state">
                ● FastAPI disponible
            </div>

        @else

            <div class="wall-api-badge offline-state">
                ● FastAPI no disponible
            </div>

        @endif

    </div>


    @if($availableMosaics->isNotEmpty())

        <form
            method="GET"
            action="{{ route('analysis.index') }}"
            class="wall-selector"
        >

            <label for="wallToWallMosaic">
                Ortomosaico listo para análisis
            </label>

            <select
                id="wallToWallMosaic"
                name="mosaic"
                onchange="this.form.submit()"
            >

                @foreach(
                    $availableMosaics
                    as $mosaic
                )

                    <option
                        value="{{ $mosaic->uuid }}"
                        {{
                            $selectedMosaicUuid
                            ===
                            $mosaic->uuid
                                ? 'selected'
                                : ''
                        }}
                    >

                        {{ $mosaic->original_name }}

                        ·

                        {{ number_format($mosaic->width) }}
                        ×
                        {{ number_format($mosaic->height) }}
                        px

                        @if($mosaic->gsd_cm)

                            · GSD
                            {{
                                number_format(
                                    (float) $mosaic->gsd_cm,
                                    4
                                )
                            }}
                            cm/px

                        @endif

                    </option>

                @endforeach

            </select>

            <div class="helper">
                El cambio de ortomosaico actualiza también
                el último resultado wall-to-wall mostrado.
            </div>

        </form>


        @if($selectedMosaic)

            <div class="wall-mosaic-info">

                <div class="wall-mosaic-item">

                    <div class="wall-mosaic-label">
                        Dimensiones
                    </div>

                    <div class="wall-mosaic-value">

                        {{ number_format($selectedMosaic->width) }}
                        ×
                        {{ number_format($selectedMosaic->height) }}
                        px

                    </div>

                </div>


                <div class="wall-mosaic-item">

                    <div class="wall-mosaic-label">
                        GSD nativo
                    </div>

                    <div class="wall-mosaic-value">

                        @if($selectedMosaic->gsd_cm)

                            {{
                                number_format(
                                    (float) $selectedMosaic->gsd_cm,
                                    4
                                )
                            }}
                            cm/px

                        @else

                            —

                        @endif

                    </div>

                </div>


                <div class="wall-mosaic-item">

                    <div class="wall-mosaic-label">
                        CRS
                    </div>

                    <div class="wall-mosaic-value">
                        {{ $selectedMosaic->crs ?? '—' }}
                    </div>

                </div>


                <div class="wall-mosaic-item">

                    <div class="wall-mosaic-label">
                        Estado
                    </div>

                    <div class="wall-mosaic-value">
                        {{ strtoupper($selectedMosaic->status) }}
                    </div>

                </div>

            </div>


            <div class="wall-frozen-config">

                <strong>
                    Configuración científica congelada:
                </strong>

                YOLO-Seg 0.25
                ·
                Mask R-CNN 0.40
                ·
                máscara 0.50
                ·
                normalización 2144 → 1024 px
                ·
                solape de salida 256 px
                ·
                IoU intramodelo 0.50
                ·
                IoU intermodelo 0.50.

                Estos parámetros no se reciben desde el navegador.

            </div>


            <form
                method="POST"
                action="{{
                    route(
                        'analysis.wall-to-wall.run',
                        [
                            'mosaic' =>
                                $selectedMosaic->uuid,
                        ]
                    )
                }}"
                id="wallToWallForm"
            >

                @csrf

                <button
                    id="wallToWallSubmit"
                    class="button wall-run-button"
                    type="submit"
                    @if(
                        ($apiStatus['status'] ?? 'offline')
                        !== 'ok'
                    )
                        disabled
                    @endif
                >

                    <span id="wallToWallButtonText">

                        @if(
                            ($apiStatus['status'] ?? 'offline')
                            === 'ok'
                        )

                            Ejecutar análisis wall-to-wall

                        @else

                            FastAPI no disponible

                        @endif

                    </span>

                </button>

            </form>


            @if(
                ($apiStatus['status'] ?? 'offline')
                !== 'ok'
            )

                <div class="helper">
                    Activa el servicio FastAPI antes de ejecutar
                    el análisis del ortomosaico.
                </div>

            @endif

        @endif

    @else

        <div class="wall-empty">

            No hay ortomosaicos con estado
            <strong>ready</strong>
            asociados a tus proyectos.

        </div>

    @endif

</section>


{{-- ==========================================================
     ANÁLISIS WALL-TO-WALL PERSISTENTE
     ========================================================== --}}

@if($wallToWallAnalysis)

    @php

        $wallSummary =
            $wallToWallAnalysis->summary
            ?? [];

        $wallModels =
            $wallToWallAnalysis->models
            ?? [];

        $wallParameters =
            $wallToWallAnalysis->parameters
            ?? [];


        $gpkgArtifact =
            $wallToWallAnalysis
                ->artifacts
                ->firstWhere(
                    'type',
                    'geopackage'
                );


        $manifestArtifact =
            $wallToWallAnalysis
                ->artifacts
                ->firstWhere(
                    'type',
                    'manifest'
                );


        $structuralConflictObjects =
            (
                $wallSummary[
                    'split_merge_objects'
                ]
                ?? 0
            )
            +
            (
                $wallSummary[
                    'containment_conflict_objects'
                ]
                ?? 0
            );

    @endphp


    <section
        class="card wall-analysis"
    >


        <div class="wall-analysis-header">

            <div>

                <h2 class="wall-analysis-title">

                    Análisis wall-to-wall

                    ·

                    {{
                        $wallParameters[
                            'pipeline_version'
                        ]
                        ?? 'V0.7E'
                    }}

                </h2>


                <div class="wall-analysis-subtitle">

                    Inferencia espacial consolidada sobre
                    el ortomosaico completo.

                    <br>

                    Método científico:

                    <strong>
                        {{
                            $wallParameters[
                                'scientific_method_version'
                            ]
                            ?? 'V0.6G'
                        }}
                    </strong>

                    ·

                    Geometría primaria:

                    <strong>
                        {{
                            $wallModels[
                                'primary_geometry_model'
                            ]
                            ?? '—'
                        }}
                    </strong>

                    ·

                    Modelo secundario:

                    <strong>
                        {{
                            $wallModels[
                                'secondary_model'
                            ]
                            ?? '—'
                        }}
                    </strong>

                </div>

            </div>


            <div class="wall-status">

                ●

                {{
                    strtoupper(
                        $wallToWallAnalysis->status
                    )
                }}

            </div>

        </div>


        <div class="wall-summary">


            <div class="wall-stat">

                <div class="wall-stat-name">
                    Predicciones RAW
                </div>

                <div class="wall-stat-value">

                    {{
                        $wallSummary[
                            'raw_predictions'
                        ]
                        ?? 0
                    }}

                </div>

                <div class="wall-stat-detail">
                    Antes de deduplicación.
                </div>

            </div>


            <div class="wall-stat">

                <div class="wall-stat-name">
                    Candidatos intramodelo
                </div>

                <div class="wall-stat-value">

                    {{
                        $wallSummary[
                            'unique_predictions'
                        ]
                        ?? 0
                    }}

                </div>

                <div class="wall-stat-detail">

                    YOLO:

                    {{
                        $wallSummary[
                            'unique_yolo'
                        ]
                        ?? 0
                    }}

                    · Mask R-CNN:

                    {{
                        $wallSummary[
                            'unique_maskrcnn'
                        ]
                        ?? 0
                    }}

                </div>

            </div>


            <div class="wall-stat">

                <div class="wall-stat-name">
                    Grupos de evidencia
                </div>

                <div class="wall-stat-value">

                    {{
                        $wallSummary[
                            'catalog_groups'
                        ]
                        ?? 0
                    }}

                </div>

                <div class="wall-stat-detail">
                    Catálogo intermodelo.
                </div>

            </div>


            <div class="wall-stat">

                <div class="wall-stat-name">
                    Objetos operativos
                </div>

                <div class="wall-stat-value">

                    {{
                        $wallSummary[
                            'primary_objects'
                        ]
                        ?? 0
                    }}

                </div>

                <div class="wall-stat-detail">

                    Mask R-CNN:

                    {{
                        $wallSummary[
                            'primary_maskrcnn'
                        ]
                        ?? 0
                    }}

                    · fallback YOLO:

                    {{
                        $wallSummary[
                            'primary_yolo_fallback'
                        ]
                        ?? 0
                    }}

                </div>

            </div>


            <div class="wall-stat">

                <div class="wall-stat-name">
                    Bilaterales
                </div>

                <div
                    class="wall-stat-value"
                    style="
                        color:
                            var(--match);
                    "
                >

                    {{
                        $wallSummary[
                            'bilateral_clean'
                        ]
                        ?? 0
                    }}

                </div>

                <div class="wall-stat-detail">
                    Evidencia de ambos modelos.
                </div>

            </div>


            <div class="wall-stat">

                <div class="wall-stat-name">
                    Mask R-CNN-only
                </div>

                <div
                    class="wall-stat-value"
                    style="
                        color:
                            var(--mask);
                    "
                >

                    {{
                        $wallSummary[
                            'mrcnn_only'
                        ]
                        ?? 0
                    }}

                </div>

                <div class="wall-stat-detail">
                    Evidencia unilateral.
                </div>

            </div>


            <div class="wall-stat">

                <div class="wall-stat-name">
                    YOLO-only
                </div>

                <div
                    class="wall-stat-value"
                    style="
                        color:
                            var(--yolo);
                    "
                >

                    {{
                        $wallSummary[
                            'yolo_only'
                        ]
                        ?? 0
                    }}

                </div>

                <div class="wall-stat-detail">
                    Fallback provisional.
                </div>

            </div>


            <div class="wall-stat">

                <div class="wall-stat-name">
                    Requieren revisión
                </div>

                <div
                    class="wall-stat-value"
                    style="
                        color:
                            var(--warning);
                    "
                >

                    {{
                        $wallSummary[
                            'requires_review'
                        ]
                        ?? 0
                    }}

                </div>

                <div class="wall-stat-detail">

                    Objetos con conflicto estructural:

                    {{
                        $structuralConflictObjects
                    }}

                </div>

            </div>


        </div>


        <div class="wall-method">

            <strong>
                Regla de consolidación:
            </strong>

            Mask R-CNN se utiliza como fuente
            geométrica primaria cuando existe una
            detección asociada.

            YOLO-Seg se conserva como evidencia
            independiente y como fallback cuando
            Mask R-CNN no presenta candidato.

        </div>


        <div class="wall-warning">

            <strong>
                Interpretación científica:
            </strong>

            Los

            <strong>
                {{
                    $wallSummary[
                        'primary_objects'
                    ]
                    ?? 0
                }}
                objetos operativos
            </strong>

            no representan todavía un número
            validado de árboles.

            Los

            <strong>
                {{
                    $wallSummary[
                        'requires_review'
                    ]
                    ?? 0
                }}
            </strong>

            objetos marcados para revisión tampoco
            deben interpretarse automáticamente
            como errores.

            La clasificación definitiva requiere
            contraste con verdad de campo.

        </div>


        <div class="artifact-actions">


            @if($gpkgArtifact)

                <a
                    class="
                        artifact-button
                        artifact-button-primary
                    "
                    href="{{
                        route(
                            'analysis.artifacts.download',
                            [
                                'analysisUuid' =>
                                    $wallToWallAnalysis->uuid,

                                'artifactUuid' =>
                                    $gpkgArtifact->uuid,
                            ]
                        )
                    }}"
                >

                    Descargar GeoPackage

                </a>

            @endif


            @if($manifestArtifact)

                <a
                    class="
                        artifact-button
                        artifact-button-secondary
                    "
                    href="{{
                        route(
                            'analysis.artifacts.download',
                            [
                                'analysisUuid' =>
                                    $wallToWallAnalysis->uuid,

                                'artifactUuid' =>
                                    $manifestArtifact->uuid,
                            ]
                        )
                    }}"
                >

                    Descargar Manifest

                </a>

            @endif


            <a
                class="
                    artifact-button
                    artifact-button-secondary
                "
                href="{{
                    route(
                        'analysis.jobs.show',
                        [
                            'analysisUuid' =>
                                $wallToWallAnalysis->uuid,
                        ]
                    )
                }}"
            >

                Ver JSON técnico

            </a>


        </div>


        <div class="wall-analysis-meta">

            Analysis UUID:

            <strong>
                {{ $wallToWallAnalysis->uuid }}
            </strong>

            @if(
                $wallToWallAnalysis->completed_at
            )

                · Completado:

                {{
                    $wallToWallAnalysis
                        ->completed_at
                        ->format(
                            'd/m/Y H:i'
                        )
                }}

            @endif

            · Ventanas procesadas:

            {{
                $wallToWallAnalysis
                    ->processed_tiles
            }}

            /

            {{
                $wallToWallAnalysis
                    ->total_tiles
            }}

        </div>


    </section>

@endif


</main>


<script>

    /*
    |--------------------------------------------------------------------------
    | Form configuration
    |--------------------------------------------------------------------------
    */

    const modelSelect =
        document.getElementById(
            'modelSelect'
        );

    const thresholdInput =
        document.getElementById(
            'thresholdInput'
        );

    const thresholdHelp =
        document.getElementById(
            'thresholdHelp'
        );

    const singleThresholdGroup =
        document.getElementById(
            'singleThresholdGroup'
        );

    const compareOptions =
        document.getElementById(
            'compareOptions'
        );

    const modelHelp =
        document.getElementById(
            'modelHelp'
        );


    const modelConfiguration = {

        yolo: {

            threshold: 0.25,

            help:
                'YOLO-Seg E1.1 · threshold validado = 0.25'
        },

        maskrcnn: {

            threshold: 0.40,

            help:
                'Mask R-CNN M1.0 · threshold validado = 0.40'
        },

        both: {

            help:
                'Ejecuta ambos modelos sobre exactamente la misma imagen.'
        }

    };


    function updateModelInterface(
        changeThreshold = true
    ) {

        const model =
            modelSelect.value;


        if (model === 'both') {

            singleThresholdGroup
                .style.display = 'none';

            compareOptions
                .classList.add(
                    'visible'
                );

            modelHelp.textContent =
                modelConfiguration
                    .both
                    .help;

            return;
        }


        singleThresholdGroup
            .style.display = 'block';

        compareOptions
            .classList.remove(
                'visible'
            );


        const configuration =
            modelConfiguration[
                model
            ];


        modelHelp.textContent =
            configuration.help;


        thresholdHelp.textContent =
            configuration.help;


        if (changeThreshold) {

            thresholdInput.value =
                configuration.threshold;
        }
    }


    modelSelect
        ?.addEventListener(
            'change',
            function () {

                updateModelInterface(
                    true
                );
            }
        );


    updateModelInterface(
        false
    );


    /*
    |--------------------------------------------------------------------------
    | Preview
    |--------------------------------------------------------------------------
    */

    const imageInput =
        document.getElementById(
            'imageInput'
        );

    const uploadName =
        document.getElementById(
            'uploadName'
        );


    imageInput
        ?.addEventListener(
            'change',
            function (event) {

                const file =
                    event.target.files[0];


                if (!file) {
                    return;
                }


                uploadName.textContent =
                    file.name;


                const preview =
                    document.getElementById(
                        'previewImage'
                    );


                const placeholder =
                    document.getElementById(
                        'viewerPlaceholder'
                    );


                if (preview) {

                    preview.src =
                        URL.createObjectURL(
                            file
                        );

                    preview.style.display =
                        'block';
                }


                if (placeholder) {

                    placeholder.style.display =
                        'none';
                }
            }
        );


    /*
    |--------------------------------------------------------------------------
    | Loading
    |--------------------------------------------------------------------------
    */

    const form =
        document.getElementById(
            'analysisForm'
        );

    const button =
        document.getElementById(
            'analyzeButton'
        );

    const buttonText =
        document.getElementById(
            'buttonText'
        );


    form?.addEventListener(
        'submit',
        function () {

            button.disabled = true;

            buttonText.textContent =
                'Procesando modelos...';
        }
    );


    /*
    |--------------------------------------------------------------------------
    | Loading wall-to-wall
    |--------------------------------------------------------------------------
    */

    const wallToWallForm =
        document.getElementById(
            'wallToWallForm'
        );

    const wallToWallSubmit =
        document.getElementById(
            'wallToWallSubmit'
        );

    const wallToWallButtonText =
        document.getElementById(
            'wallToWallButtonText'
        );


    wallToWallForm
        ?.addEventListener(
            'submit',
            function () {

                if (wallToWallSubmit) {

                    wallToWallSubmit.disabled =
                        true;
                }


                if (wallToWallButtonText) {

                    wallToWallButtonText.textContent =
                        'Procesando ortomosaico...';
                }
            }
        );


@if($result)


    /*
    |--------------------------------------------------------------------------
    | Canvas configuration
    |--------------------------------------------------------------------------
    */

    const isCompare =
        @json($isCompare);


    const image =
        document.getElementById(
            'uavImage'
        );


    const canvas =
        document.getElementById(
            'maskCanvas'
        );


    const ctx =
        canvas.getContext(
            '2d'
        );


    const originalWidth =
        {{
            (int)(
                $isCompare
                    ? (
                        $result[
                            'image'
                        ][
                            'width'
                        ] ?? 1024
                    )
                    : (
                        $result[
                            'width'
                        ] ?? 1024
                    )
            )
        }};


    const originalHeight =
        {{
            (int)(
                $isCompare
                    ? (
                        $result[
                            'image'
                        ][
                            'height'
                        ] ?? 1024
                    )
                    : (
                        $result[
                            'height'
                        ] ?? 1024
                    )
            )
        }};


    /*
    |--------------------------------------------------------------------------
    | Helpers
    |--------------------------------------------------------------------------
    */

    function drawPolygon(
        polygon,
        fill,
        stroke,
        label = null,
        dashed = false
    ) {

        if (
            !polygon
            ||
            polygon.length < 3
        ) {
            return;
        }


        ctx.beginPath();


        polygon.forEach(
            (point, index) => {

                if (index === 0) {

                    ctx.moveTo(
                        point[0],
                        point[1]
                    );

                } else {

                    ctx.lineTo(
                        point[0],
                        point[1]
                    );
                }
            }
        );


        ctx.closePath();


        if (fill) {

            ctx.fillStyle = fill;

            ctx.fill();
        }


        ctx.strokeStyle = stroke;

        ctx.lineWidth = 3;


        if (dashed) {

            ctx.setLineDash(
                [10, 7]
            );

        } else {

            ctx.setLineDash([]);
        }


        ctx.stroke();

        ctx.setLineDash([]);


        if (label) {

            const point =
                polygon[0];


            ctx.font =
                'bold 17px Arial';


            const width =
                ctx.measureText(
                    label
                ).width;


            const x =
                point[0];

            const y =
                Math.max(
                    25,
                    point[1]
                );


            ctx.fillStyle =
                'rgba(10,20,15,.82)';


            ctx.fillRect(
                x - 5,
                y - 21,
                width + 10,
                27
            );


            ctx.fillStyle =
                '#ffffff';


            ctx.fillText(
                label,
                x,
                y
            );
        }
    }


    function polygonCentroid(
        polygon
    ) {

        if (
            !polygon
            ||
            polygon.length === 0
        ) {

            return [0,0];
        }


        let x = 0;

        let y = 0;


        polygon.forEach(
            point => {

                x += point[0];

                y += point[1];
            }
        );


        return [
            x / polygon.length,
            y / polygon.length
        ];
    }


    function drawMatchMarker(
        polygon,
        match
    ) {

        const [
            x,
            y
        ] = polygonCentroid(
            polygon
        );


        ctx.beginPath();

        ctx.arc(
            x,
            y,
            18,
            0,
            Math.PI * 2
        );


        ctx.fillStyle =
            'rgba(138,70,199,.92)';

        ctx.fill();


        ctx.strokeStyle =
            '#ffffff';

        ctx.lineWidth = 2;

        ctx.stroke();


        ctx.fillStyle =
            '#ffffff';

        ctx.font =
            'bold 11px Arial';

        ctx.textAlign =
            'center';

        ctx.textBaseline =
            'middle';


        ctx.fillText(
            'M' + match.id,
            x,
            y
        );


        ctx.textAlign =
            'start';

        ctx.textBaseline =
            'alphabetic';
    }


    /*
    |--------------------------------------------------------------------------
    | Comparison mode
    |--------------------------------------------------------------------------
    */

@if($isCompare)


    const yoloCrowns =
        @json(
            $yoloCrowns
        );


    const maskCrowns =
        @json(
            $maskCrowns
        );


    const matches =
        @json(
            $matches
        );


    const matchedYoloIds =
        new Set(
            matches.map(
                item =>
                    Number(
                        item.yolo_id
                    )
            )
        );


    const matchedMaskIds =
        new Set(
            matches.map(
                item =>
                    Number(
                        item.maskrcnn_id
                    )
            )
        );


    function renderComparison() {


        canvas.width =
            originalWidth;

        canvas.height =
            originalHeight;


        ctx.clearRect(
            0,
            0,
            canvas.width,
            canvas.height
        );


        const showYolo =
            document.getElementById(
                'showYolo'
            )?.checked;


        const showMask =
            document.getElementById(
                'showMask'
            )?.checked;


        const showMatches =
            document.getElementById(
                'showMatches'
            )?.checked;


        const showLabels =
            document.getElementById(
                'showLabels'
            )?.checked;


        /*
        |--------------------------------------------------------------------------
        | YOLO
        |--------------------------------------------------------------------------
        */

        if (showYolo) {

            yoloCrowns.forEach(
                crown => {

                    const matched =
                        matchedYoloIds.has(
                            Number(
                                crown.id
                            )
                        );


                    const label =
                        showLabels
                            ? (
                                'Y'
                                + crown.id
                                + ' · '
                                + Number(
                                    crown.confidence
                                ).toFixed(2)
                            )
                            : null;


                    drawPolygon(
                        crown.polygon,

                        'rgba(38,128,217,.18)',

                        matched
                            ? 'rgba(117,78,190,.95)'
                            : 'rgba(65,160,255,.95)',

                        label,

                        false
                    );
                }
            );
        }


        /*
        |--------------------------------------------------------------------------
        | Mask R-CNN
        |--------------------------------------------------------------------------
        */

        if (showMask) {

            maskCrowns.forEach(
                crown => {

                    const matched =
                        matchedMaskIds.has(
                            Number(
                                crown.id
                            )
                        );


                    const label =
                        showLabels
                            ? (
                                'M'
                                + crown.id
                                + ' · '
                                + Number(
                                    crown.confidence
                                ).toFixed(2)
                            )
                            : null;


                    drawPolygon(
                        crown.polygon,

                        'rgba(27,166,107,.13)',

                        matched
                            ? 'rgba(177,95,220,.95)'
                            : 'rgba(53,226,143,.95)',

                        label,

                        true
                    );
                }
            );
        }


        /*
        |--------------------------------------------------------------------------
        | Match markers
        |--------------------------------------------------------------------------
        */

        if (showMatches) {

            matches.forEach(
                match => {

                    const crown =
                        yoloCrowns.find(
                            item =>
                                Number(
                                    item.id
                                )
                                ===
                                Number(
                                    match.yolo_id
                                )
                        );


                    if (
                        crown
                        &&
                        crown.polygon
                    ) {

                        drawMatchMarker(
                            crown.polygon,
                            match
                        );
                    }
                }
            );
        }
    }


    [
        'showYolo',
        'showMask',
        'showMatches',
        'showLabels'

    ].forEach(
        id => {

            document
                .getElementById(id)
                ?.addEventListener(
                    'change',
                    renderComparison
                );
        }
    );


@else


    /*
    |--------------------------------------------------------------------------
    | Individual mode
    |--------------------------------------------------------------------------
    */

    const crowns =
        @json(
            $singleCrowns
        );


    const resultMode =
        @json(
            $analysisMode
        );


    function renderSingle() {


        canvas.width =
            originalWidth;

        canvas.height =
            originalHeight;


        ctx.clearRect(
            0,
            0,
            canvas.width,
            canvas.height
        );


        const showFill =
            document.getElementById(
                'showFill'
            )?.checked;


        const showLabels =
            document.getElementById(
                'showLabels'
            )?.checked;


        const isMask =
            resultMode ===
            'maskrcnn';


        crowns.forEach(
            crown => {


                const label =
                    showLabels
                        ? (
                            '#'
                            + crown.id
                            + ' · '
                            + Number(
                                crown.confidence
                            ).toFixed(2)
                        )
                        : null;


                drawPolygon(
                    crown.polygon,

                    showFill
                        ? (
                            isMask
                                ? 'rgba(27,166,107,.22)'
                                : 'rgba(38,128,217,.22)'
                        )
                        : null,

                    isMask
                        ? 'rgba(53,226,143,.95)'
                        : 'rgba(65,160,255,.95)',

                    label
                );

            }
        );

    }


    [
        'showFill',
        'showLabels'

    ].forEach(
        id => {

            document
                .getElementById(id)
                ?.addEventListener(
                    'change',
                    renderSingle
                );
        }
    );


@endif


    /*
    |--------------------------------------------------------------------------
    | Initial render
    |--------------------------------------------------------------------------
    */

    function renderCurrent() {

        if (isCompare) {

            renderComparison();

        } else {

            renderSingle();
        }
    }


    image?.addEventListener(
        'load',
        renderCurrent
    );


    if (
        image
        &&
        image.complete
    ) {

        renderCurrent();
    }


@endif

</script>


</body>

</html>