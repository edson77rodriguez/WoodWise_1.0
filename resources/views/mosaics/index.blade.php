<!DOCTYPE html>
<html lang="es">

<head>

    <meta charset="UTF-8">

    <meta
        name="viewport"
        content="width=device-width, initial-scale=1"
    >

    <meta
        name="csrf-token"
        content="{{ csrf_token() }}"
    >

    <title>Ortomosaicos UAV | WoodWise</title>

    <style>

        :root {
            --bg: #f4f7f5;
            --surface: #ffffff;
            --surface-soft: #f8faf9;

            --primary: #176b4d;
            --primary-dark: #10533b;
            --primary-soft: #e8f4ee;

            --text: #1f2933;
            --text-soft: #637168;

            --border: #dfe7e2;

            --success: #177245;
            --success-bg: #e8f6ee;

            --warning: #a96600;
            --warning-bg: #fff5df;

            --danger: #b42318;
            --danger-bg: #fef0ee;

            --info: #1d5f91;
            --info-bg: #edf6fc;

            --shadow:
                0 8px 24px
                rgba(18, 52, 38, 0.07);
        }


        * {
            box-sizing: border-box;
        }


        body {
            margin: 0;

            font-family:
                Inter,
                system-ui,
                -apple-system,
                BlinkMacSystemFont,
                "Segoe UI",
                sans-serif;

            background: var(--bg);
            color: var(--text);
        }


        .page {
            max-width: 1500px;
            margin: auto;
            padding: 32px 24px 60px;
        }


        /* =========================================================
           HEADER
        ========================================================= */

        .page-header {
            display: flex;
            justify-content: space-between;
            align-items: flex-start;
            gap: 24px;

            margin-bottom: 28px;
        }


        .page-title {
            margin: 0;

            font-size: 30px;
            line-height: 1.2;

            color: #16382b;
        }


        .page-subtitle {
            margin: 8px 0 0;

            max-width: 780px;

            color: var(--text-soft);
            line-height: 1.55;
        }


        .header-actions {
            display: flex;
            gap: 10px;
            flex-wrap: wrap;
        }


        /* =========================================================
           BOTONES
        ========================================================= */

        .btn {
            appearance: none;

            border: 0;
            border-radius: 9px;

            padding: 10px 16px;

            display: inline-flex;
            align-items: center;
            justify-content: center;
            gap: 7px;

            font-size: 14px;
            font-weight: 650;

            text-decoration: none;

            cursor: pointer;

            transition:
                transform .15s ease,
                background .15s ease,
                opacity .15s ease;
        }


        .btn:hover {
            transform: translateY(-1px);
        }


        .btn:disabled {
            cursor: not-allowed;
            opacity: .6;
            transform: none;
        }


        .btn-primary {
            color: white;
            background: var(--primary);
        }


        .btn-primary:hover {
            background: var(--primary-dark);
        }


        .btn-secondary {
            color: var(--primary);
            background: var(--primary-soft);
        }


        .btn-secondary:hover {
            background: #dceee5;
        }


        .btn-outline {
            color: var(--text);

            background: white;

            border: 1px solid var(--border);
        }


        .btn-outline:hover {
            background: var(--surface-soft);
        }


        .btn-small {
            padding: 7px 11px;
            font-size: 13px;
        }


        /* =========================================================
           ALERTAS
        ========================================================= */

        .alert {
            border-radius: 10px;

            padding: 14px 16px;
            margin-bottom: 20px;

            font-size: 14px;
            line-height: 1.45;
        }


        .alert-success {
            background: var(--success-bg);
            color: var(--success);

            border: 1px solid #cbe9d8;
        }


        .alert-error {
            background: var(--danger-bg);
            color: var(--danger);

            border: 1px solid #f3d0cc;
        }


        /* =========================================================
           CARDS
        ========================================================= */

        .card {
            background: var(--surface);

            border: 1px solid var(--border);
            border-radius: 14px;

            box-shadow: var(--shadow);

            margin-bottom: 24px;
        }


        .card-header {
            padding: 20px 22px;

            border-bottom: 1px solid var(--border);

            display: flex;
            justify-content: space-between;
            align-items: center;
            gap: 16px;
        }


        .card-header h2 {
            margin: 0;
            font-size: 18px;
        }


        .card-body {
            padding: 22px;
        }


        /* =========================================================
           RESUMEN
        ========================================================= */

        .stats-grid {
            display: grid;

            grid-template-columns:
                repeat(4, minmax(0, 1fr));

            gap: 16px;

            margin-bottom: 24px;
        }


        .stat-card {
            background: white;

            border: 1px solid var(--border);
            border-radius: 12px;

            padding: 18px;
        }


        .stat-label {
            color: var(--text-soft);

            font-size: 13px;

            margin-bottom: 6px;
        }


        .stat-value {
            font-size: 26px;
            font-weight: 750;

            color: #193b2e;
        }


        /* =========================================================
           CARGA
        ========================================================= */

        .upload-grid {
            display: grid;

            grid-template-columns:
                minmax(230px, .7fr)
                minmax(320px, 1.3fr);

            gap: 20px;
        }


        .field label {
            display: block;

            margin-bottom: 7px;

            font-size: 13px;
            font-weight: 700;

            color: #35483f;
        }


        select,
        input[type="file"] {
            width: 100%;

            padding: 11px 12px;

            background: white;

            border: 1px solid #cad6cf;
            border-radius: 9px;

            font: inherit;
            color: var(--text);
        }


        select:focus,
        input[type="file"]:focus {
            outline: none;

            border-color: var(--primary);

            box-shadow:
                0 0 0 3px
                rgba(23, 107, 77, .12);
        }


        .file-help {
            margin-top: 7px;

            color: var(--text-soft);

            font-size: 12px;
            line-height: 1.4;
        }


        .upload-actions {
            margin-top: 18px;

            display: flex;
            align-items: center;

            gap: 16px;

            flex-wrap: wrap;
        }


        /* =========================================================
           PROGRESO
        ========================================================= */

        .progress-wrapper {
            margin-top: 20px;
        }


        .progress-meta {
            display: flex;
            justify-content: space-between;

            gap: 15px;

            font-size: 13px;

            margin-bottom: 7px;

            color: var(--text-soft);
        }


        progress {
            width: 100%;
            height: 12px;

            border: 0;
            border-radius: 999px;

            overflow: hidden;

            appearance: none;
        }


        progress::-webkit-progress-bar {
            background: #e4ebe7;
            border-radius: 999px;
        }


        progress::-webkit-progress-value {
            background: var(--primary);
            border-radius: 999px;
        }


        progress::-moz-progress-bar {
            background: var(--primary);
        }


        .upload-status {
            margin-top: 9px;

            font-size: 13px;
            font-weight: 650;
        }


        /* =========================================================
           TABLA
        ========================================================= */

        .table-wrapper {
            overflow-x: auto;
        }


        table {
            width: 100%;

            border-collapse: collapse;

            min-width: 1450px;
        }


        th {
            padding: 13px 14px;

            font-size: 12px;
            font-weight: 750;

            text-transform: uppercase;
            letter-spacing: .035em;

            color: #5d6c64;
            background: var(--surface-soft);

            text-align: left;
            white-space: nowrap;

            border-bottom: 1px solid var(--border);
        }


        td {
            padding: 15px 14px;

            vertical-align: middle;

            border-bottom: 1px solid #edf1ef;

            font-size: 13px;
        }


        tbody tr:hover {
            background: #fbfdfc;
        }


        tbody tr:last-child td {
            border-bottom: 0;
        }


        .file-name {
            max-width: 250px;

            font-size: 13px;
            font-weight: 700;

            overflow-wrap: anywhere;
        }


        .file-meta {
            display: block;

            margin-top: 4px;

            color: var(--text-soft);

            font-size: 11px;
        }


        .mono {
            font-family:
                ui-monospace,
                SFMono-Regular,
                Menlo,
                Monaco,
                Consolas,
                monospace;

            font-size: 12px;
        }


        /* =========================================================
           PREVIEW
        ========================================================= */

        .preview-box {
            width: 170px;
            height: 105px;

            background:
                linear-gradient(
                    135deg,
                    #edf3ef,
                    #f8faf9
                );

            border: 1px solid var(--border);
            border-radius: 10px;

            overflow: hidden;

            display: flex;
            align-items: center;
            justify-content: center;

            position: relative;
        }


        .preview-box img {
            width: 100%;
            height: 100%;

            object-fit: contain;

            display: block;

            background: #eef2ef;
        }


        .preview-empty {
            padding: 12px;

            color: #78857d;

            font-size: 11px;

            line-height: 1.35;

            text-align: center;
        }


        .preview-link {
            display: block;

            text-decoration: none;

            border-radius: 10px;
        }


        .preview-link:hover .preview-box {
            border-color: var(--primary);

            box-shadow:
                0 0 0 3px
                rgba(23, 107, 77, .08);
        }


        .preview-label {
            position: absolute;

            left: 7px;
            bottom: 7px;

            padding: 3px 6px;

            border-radius: 6px;

            background:
                rgba(20, 47, 37, .78);

            color: white;

            font-size: 10px;

            backdrop-filter:
                blur(4px);
        }


        /* =========================================================
           BADGES
        ========================================================= */

        .badge {
            display: inline-flex;
            align-items: center;

            gap: 6px;

            padding: 5px 9px;

            border-radius: 999px;

            font-size: 11px;
            font-weight: 750;

            white-space: nowrap;
        }


        .badge::before {
            content: "";

            width: 7px;
            height: 7px;

            border-radius: 999px;

            background: currentColor;
        }


        .badge-ready,
        .badge-uploaded {
            color: var(--success);
            background: var(--success-bg);
        }


        .badge-uploading,
        .badge-inspecting,
        .badge-processing {
            color: var(--warning);
            background: var(--warning-bg);
        }


        .badge-failed {
            color: var(--danger);
            background: var(--danger-bg);
        }


        .badge-default {
            color: var(--info);
            background: var(--info-bg);
        }


        /* =========================================================
           ACCIONES
        ========================================================= */

        .actions {
            display: flex;

            gap: 7px;

            align-items: center;

            flex-wrap: wrap;

            min-width: 150px;
        }


        .actions form {
            margin: 0;
        }


        /* =========================================================
           DETALLES
        ========================================================= */

        details {
            min-width: 190px;
        }


        summary {
            color: var(--primary);

            font-weight: 650;

            cursor: pointer;
        }


        .details-grid {
            margin-top: 10px;

            display: grid;

            grid-template-columns: 1fr;

            gap: 6px;

            color: var(--text-soft);

            font-size: 12px;
        }


        .details-grid strong {
            color: var(--text);
        }


        .hash {
            display: inline-block;

            max-width: 230px;

            overflow: hidden;

            text-overflow: ellipsis;

            white-space: nowrap;
        }


        /* =========================================================
           EMPTY
        ========================================================= */

        .empty-state {
            padding: 46px 20px;

            text-align: center;

            color: var(--text-soft);
        }


        .empty-title {
            color: var(--text);

            font-weight: 700;

            margin-bottom: 6px;
        }


        /* =========================================================
           RESPONSIVE
        ========================================================= */

        @media (max-width: 900px) {

            .stats-grid {
                grid-template-columns:
                    repeat(2, minmax(0, 1fr));
            }


            .upload-grid {
                grid-template-columns: 1fr;
            }


            .page-header {
                flex-direction: column;
            }

        }


        @media (max-width: 520px) {

            .page {
                padding: 20px 14px 40px;
            }


            .stats-grid {
                grid-template-columns:
                    1fr 1fr;
            }


            .page-title {
                font-size: 25px;
            }

        }

    </style>

</head>


<body>


@php

    $totalMosaics =
        $mosaics->count();


    $readyMosaics =
        $mosaics
            ->where(
                'status',
                'ready'
            )
            ->count();


    $pendingMosaics =
        $mosaics
            ->whereIn(
                'status',
                [
                    'uploading',
                    'uploaded',
                    'inspecting',
                    'processing'
                ]
            )
            ->count();


    $failedMosaics =
        $mosaics
            ->where(
                'status',
                'failed'
            )
            ->count();


    $previewMosaics =
        $mosaics
            ->filter(
                fn ($mosaic) =>
                    !empty(
                        $mosaic
                            ->preview_object_key
                    )
            )
            ->count();

@endphp



<div class="page">


    {{-- =========================================================
         HEADER
    ========================================================== --}}

    <div class="page-header">

        <div>

            <h1 class="page-title">
                Ortomosaicos UAV
            </h1>

            <p class="page-subtitle">

                Gestión, almacenamiento e inspección
                geoespacial de ortomosaicos para análisis
                forestal mediante inteligencia artificial.

            </p>

        </div>


        <div class="header-actions">

            @if(
                \Illuminate\Support\Facades\Route::has(
                    'analysis.index'
                )
            )

                <a
                    href="{{
                        route(
                            'analysis.index'
                        )
                    }}"
                    class="btn btn-outline"
                >

                    ← Análisis UAV

                </a>

            @endif

        </div>

    </div>



    {{-- =========================================================
         MENSAJES
    ========================================================== --}}

    @if(session('success'))

        <div class="alert alert-success">

            {{ session('success') }}

        </div>

    @endif


    @if(session('error'))

        <div class="alert alert-error">

            {{ session('error') }}

        </div>

    @endif


    @if($errors->any())

        <div class="alert alert-error">

            <strong>
                No se pudo completar la operación.
            </strong>

            <br>

            {{ $errors->first() }}

        </div>

    @endif



    {{-- =========================================================
         RESUMEN
    ========================================================== --}}

    <div class="stats-grid">

        <div class="stat-card">

            <div class="stat-label">
                Ortomosaicos
            </div>

            <div class="stat-value">
                {{ $totalMosaics }}
            </div>

        </div>


        <div class="stat-card">

            <div class="stat-label">
                Listos
            </div>

            <div class="stat-value">
                {{ $readyMosaics }}
            </div>

        </div>


        <div class="stat-card">

            <div class="stat-label">
                Con vista previa
            </div>

            <div class="stat-value">
                {{ $previewMosaics }}
            </div>

        </div>


        <div class="stat-card">

            <div class="stat-label">
                Pendientes / error
            </div>

            <div class="stat-value">

                {{
                    $pendingMosaics
                    +
                    $failedMosaics
                }}

            </div>

        </div>

    </div>



    {{-- =========================================================
         CARGA
    ========================================================== --}}

    <div class="card">

        <div class="card-header">

            <h2>
                Nuevo ortomosaico
            </h2>

        </div>


        <div class="card-body">


            @if($projects->isEmpty())

                <div class="alert alert-error">

                    No existe ningún proyecto activo.
                    Primero debes crear o activar
                    un proyecto.

                </div>


            @else


                <div class="upload-grid">

                    <div class="field">

                        <label for="project">
                            Proyecto
                        </label>

                        <select id="project">

                            @foreach(
                                $projects
                                as
                                $project
                            )

                                <option
                                    value="{{
                                        $project->id
                                    }}"
                                >

                                    {{
                                        $project->name
                                    }}

                                </option>

                            @endforeach

                        </select>

                    </div>


                    <div class="field">

                        <label for="mosaicFile">

                            Ortomosaico GeoTIFF

                        </label>

                        <input
                            type="file"
                            id="mosaicFile"
                            accept=".tif,.tiff,image/tiff"
                        >

                        <div class="file-help">

                            Formatos permitidos:
                            .tif y .tiff.

                            El archivo se transfiere
                            directamente desde el navegador
                            hacia Cloudflare R2.

                        </div>

                    </div>

                </div>


                <div class="upload-actions">

                    <button
                        id="uploadButton"
                        class="btn btn-primary"
                        type="button"
                    >

                        Subir ortomosaico

                    </button>


                    <span
                        id="selectedFileInfo"
                        class="file-help"
                    ></span>

                </div>


                <div
                    id="progressWrapper"
                    class="progress-wrapper"
                    hidden
                >

                    <div class="progress-meta">

                        <span id="progressLabel">
                            Preparando...
                        </span>

                        <span id="progressPercent">
                            0 %
                        </span>

                    </div>


                    <progress
                        id="uploadProgress"
                        value="0"
                        max="100"
                    ></progress>


                    <div
                        id="uploadStatus"
                        class="upload-status"
                    ></div>

                </div>


            @endif

        </div>

    </div>



    {{-- =========================================================
         TABLA
    ========================================================== --}}

    <div class="card">

        <div class="card-header">

            <h2>
                Ortomosaicos registrados
            </h2>


            <span class="file-help">

                {{ $totalMosaics }}

                registro{{
                    $totalMosaics === 1
                        ? ''
                        : 's'
                }}

            </span>

        </div>


        @if($mosaics->isEmpty())


            <div class="empty-state">

                <div class="empty-title">

                    Todavía no hay ortomosaicos

                </div>

                <div>

                    Selecciona un proyecto y carga
                    tu primer GeoTIFF.

                </div>

            </div>


        @else


            <div class="table-wrapper">

                <table>

                    <thead>

                        <tr>

                            <th>
                                Vista
                            </th>

                            <th>
                                Archivo
                            </th>

                            <th>
                                Proyecto
                            </th>

                            <th>
                                Tamaño
                            </th>

                            <th>
                                GSD
                            </th>

                            <th>
                                CRS
                            </th>

                            <th>
                                Resolución
                            </th>

                            <th>
                                Bandas
                            </th>

                            <th>
                                Estado
                            </th>

                            <th>
                                Información
                            </th>

                            <th>
                                Acciones
                            </th>

                        </tr>

                    </thead>


                    <tbody>


                    @foreach(
                        $mosaics
                        as
                        $mosaic
                    )


                        @php

                            $badgeClass =
                                match(
                                    $mosaic->status
                                ) {

                                    'ready' =>
                                        'badge-ready',

                                    'uploaded' =>
                                        'badge-uploaded',

                                    'uploading',
                                    'inspecting',
                                    'processing' =>
                                        'badge-processing',

                                    'failed' =>
                                        'badge-failed',

                                    default =>
                                        'badge-default',
                                };


                            $statusText =
                                match(
                                    $mosaic->status
                                ) {

                                    'uploading' =>
                                        'Subiendo',

                                    'uploaded' =>
                                        'Subido',

                                    'inspecting' =>
                                        'Inspeccionando',

                                    'ready' =>
                                        'Listo',

                                    'processing' =>
                                        'Procesando',

                                    'failed' =>
                                        'Error',

                                    default =>
                                        ucfirst(
                                            $mosaic->status
                                        ),
                                };


                            $hasPreview =
                                !empty(
                                    $mosaic
                                        ->preview_object_key
                                );


                            $previewMetadata =
                                data_get(
                                    $mosaic->metadata,
                                    'preview',
                                    []
                                );

                        @endphp



                        <tr>


                            {{-- =================================================
                                 PREVIEW
                            ================================================== --}}

                            <td>


                                @if(
                                    $hasPreview
                                    &&
                                    \Illuminate\Support\Facades\Route::has(
                                        'mosaics.preview'
                                    )
                                )


                                    <a
                                        href="{{
                                            route(
                                                'mosaics.preview',
                                                $mosaic->uuid
                                            )
                                        }}"
                                        target="_blank"
                                        class="preview-link"
                                        title="Abrir vista previa"
                                    >

                                        <div class="preview-box">

                                            <img
                                                src="{{
                                                    route(
                                                        'mosaics.preview',
                                                        $mosaic->uuid
                                                    )
                                                }}"
                                                alt="Vista previa de {{
                                                    $mosaic
                                                        ->original_name
                                                }}"
                                                loading="lazy"
                                            >

                                            <span
                                                class="preview-label"
                                            >
                                                Preview
                                            </span>

                                        </div>

                                    </a>


                                @else


                                    <div class="preview-box">

                                        <div class="preview-empty">

                                            Sin vista previa

                                            @if(
                                                $mosaic->status
                                                === 'ready'
                                            )

                                                <br>

                                                Genérala desde
                                                Acciones.

                                            @endif

                                        </div>

                                    </div>


                                @endif


                            </td>



                            {{-- =================================================
                                 ARCHIVO
                            ================================================== --}}

                            <td>

                                <div class="file-name">

                                    {{
                                        $mosaic
                                            ->original_name
                                    }}

                                </div>


                                <span class="file-meta">

                                    {{
                                        strtoupper(
                                            $mosaic->extension
                                            ?? 'tif'
                                        )
                                    }}


                                    @if($mosaic->dtype)

                                        ·
                                        {{ $mosaic->dtype }}

                                    @endif

                                </span>

                            </td>



                            {{-- =================================================
                                 PROYECTO
                            ================================================== --}}

                            <td>

                                {{
                                    $mosaic->project?->name
                                    ?? 'Sin proyecto'
                                }}

                            </td>



                            {{-- =================================================
                                 TAMAÑO
                            ================================================== --}}

                            <td>

                                @if(
                                    $mosaic
                                        ->size_bytes
                                )

                                    {{
                                        number_format(
                                            $mosaic
                                                ->size_bytes
                                            / 1024
                                            / 1024,
                                            2
                                        )
                                    }}

                                    MiB

                                @else

                                    —

                                @endif

                            </td>



                            {{-- =================================================
                                 GSD
                            ================================================== --}}

                            <td>

                                @if(
                                    $mosaic->gsd_cm
                                    !== null
                                )

                                    <strong>

                                        {{
                                            number_format(
                                                $mosaic
                                                    ->gsd_cm,
                                                3
                                            )
                                        }}

                                    </strong>

                                    cm/px

                                @else

                                    —

                                @endif

                            </td>



                            {{-- =================================================
                                 CRS
                            ================================================== --}}

                            <td>

                                @if($mosaic->crs)

                                    <span class="mono">

                                        {{
                                            $mosaic->crs
                                        }}

                                    </span>

                                @else

                                    —

                                @endif

                            </td>



                            {{-- =================================================
                                 RESOLUCIÓN
                            ================================================== --}}

                            <td>

                                @if(
                                    $mosaic->width
                                    &&
                                    $mosaic->height
                                )

                                    {{
                                        number_format(
                                            $mosaic->width
                                        )
                                    }}

                                    ×

                                    {{
                                        number_format(
                                            $mosaic->height
                                        )
                                    }}

                                    <span class="file-meta">
                                        px
                                    </span>

                                @else

                                    —

                                @endif

                            </td>



                            {{-- =================================================
                                 BANDAS
                            ================================================== --}}

                            <td>

                                {{
                                    $mosaic->bands
                                    ?? '—'
                                }}

                            </td>



                            {{-- =================================================
                                 ESTADO
                            ================================================== --}}

                            <td>

                                <span
                                    class="
                                        badge
                                        {{ $badgeClass }}
                                    "
                                >

                                    {{ $statusText }}

                                </span>

                            </td>



                            {{-- =================================================
                                 INFORMACIÓN
                            ================================================== --}}

                            <td>


                                @if(
                                    $mosaic->status
                                    === 'ready'
                                )


                                    <details>

                                        <summary>
                                            Ver detalles
                                        </summary>


                                        <div class="details-grid">


                                            <div>

                                                <strong>
                                                    Píxel X:
                                                </strong>

                                                {{
                                                    $mosaic
                                                        ->pixel_size_x
                                                    ?? '—'
                                                }}

                                                m

                                            </div>


                                            <div>

                                                <strong>
                                                    Píxel Y:
                                                </strong>

                                                {{
                                                    $mosaic
                                                        ->pixel_size_y
                                                    ?? '—'
                                                }}

                                                m

                                            </div>


                                            <div>

                                                <strong>
                                                    Alpha:
                                                </strong>

                                                {{
                                                    data_get(
                                                        $mosaic
                                                            ->metadata,
                                                        'has_alpha'
                                                    )
                                                    ? 'Sí'
                                                    : 'No'
                                                }}

                                            </div>


                                            <div>

                                                <strong>
                                                    Compresión:
                                                </strong>

                                                {{
                                                    data_get(
                                                        $mosaic
                                                            ->metadata,
                                                        'compression'
                                                    )
                                                    ?? '—'
                                                }}

                                            </div>


                                            <div>

                                                <strong>
                                                    Proyectado:
                                                </strong>

                                                {{
                                                    data_get(
                                                        $mosaic
                                                            ->metadata,
                                                        'is_projected'
                                                    )
                                                    ? 'Sí'
                                                    : 'No'
                                                }}

                                            </div>


                                            <div>

                                                <strong>
                                                    Inspeccionado:
                                                </strong>

                                                @if(
                                                    $mosaic
                                                        ->inspected_at
                                                )

                                                    {{
                                                        $mosaic
                                                            ->inspected_at
                                                            ->format(
                                                                'd/m/Y H:i'
                                                            )
                                                    }}

                                                @else

                                                    —

                                                @endif

                                            </div>



                                            @if($hasPreview)


                                                <div>

                                                    <strong>
                                                        Preview:
                                                    </strong>

                                                    Disponible

                                                </div>


                                                <div>

                                                    <strong>
                                                        Resolución preview:
                                                    </strong>

                                                    {{
                                                        data_get(
                                                            $previewMetadata,
                                                            'width'
                                                        )
                                                        ?? '—'
                                                    }}

                                                    ×

                                                    {{
                                                        data_get(
                                                            $previewMetadata,
                                                            'height'
                                                        )
                                                        ?? '—'
                                                    }}

                                                    px

                                                </div>


                                                <div>

                                                    <strong>
                                                        Tamaño preview:
                                                    </strong>

                                                    @if(
                                                        data_get(
                                                            $previewMetadata,
                                                            'size_bytes'
                                                        )
                                                    )

                                                        {{
                                                            number_format(
                                                                data_get(
                                                                    $previewMetadata,
                                                                    'size_bytes'
                                                                )
                                                                / 1024,
                                                                1
                                                            )
                                                        }}

                                                        KiB

                                                    @else

                                                        —

                                                    @endif

                                                </div>


                                                <div>

                                                    <strong>
                                                        Calidad JPEG:
                                                    </strong>

                                                    {{
                                                        data_get(
                                                            $previewMetadata,
                                                            'jpeg_quality'
                                                        )
                                                        ?? '—'
                                                    }}

                                                </div>


                                            @endif



                                            @if(
                                                $mosaic
                                                    ->checksum_sha256
                                            )


                                                <div>

                                                    <strong>
                                                        SHA-256:
                                                    </strong>


                                                    <span
                                                        class="
                                                            mono
                                                            hash
                                                        "
                                                        title="{{
                                                            $mosaic
                                                                ->checksum_sha256
                                                        }}"
                                                    >

                                                        {{
                                                            $mosaic
                                                                ->checksum_sha256
                                                        }}

                                                    </span>

                                                </div>


                                            @endif


                                        </div>

                                    </details>


                                @else


                                    <span class="file-meta">

                                        Inspección pendiente

                                    </span>


                                @endif


                            </td>



                            {{-- =================================================
                                 ACCIONES
                            ================================================== --}}

                            <td>

                                <div class="actions">


                                    {{-- Inspección --}}
                                    @if(
                                        in_array(
                                            $mosaic->status,
                                            [
                                                'uploaded',
                                                'failed'
                                            ]
                                        )
                                    )


                                        <form
                                            method="POST"
                                            action="{{
                                                route(
                                                    'mosaics.inspect',
                                                    $mosaic->uuid
                                                )
                                            }}"
                                        >

                                            @csrf


                                            <button
                                                type="submit"
                                                class="
                                                    btn
                                                    btn-secondary
                                                    btn-small
                                                "
                                            >

                                                Inspeccionar

                                            </button>

                                        </form>



                                    {{-- Ya está listo --}}
                                    @elseif(
                                        $mosaic->status
                                        === 'ready'
                                    )


                                        <span
                                            class="
                                                badge
                                                badge-ready
                                            "
                                        >

                                            Validado

                                        </span>



                                        {{-- Generar preview --}}
                                        @if(
                                            !$hasPreview
                                            &&
                                            \Illuminate\Support\Facades\Route::has(
                                                'mosaics.preview.generate'
                                            )
                                        )


                                            <form
                                                method="POST"
                                                action="{{
                                                    route(
                                                        'mosaics.preview.generate',
                                                        $mosaic->uuid
                                                    )
                                                }}"
                                            >

                                                @csrf


                                                <button
                                                    type="submit"
                                                    class="
                                                        btn
                                                        btn-secondary
                                                        btn-small
                                                    "
                                                >

                                                    Generar vista

                                                </button>

                                            </form>



                                        {{-- Abrir preview --}}
                                        @elseif(
                                            $hasPreview
                                            &&
                                            \Illuminate\Support\Facades\Route::has(
                                                'mosaics.preview'
                                            )
                                        )


                                            <a
                                                href="{{
                                                    route(
                                                        'mosaics.preview',
                                                        $mosaic->uuid
                                                    )
                                                }}"
                                                target="_blank"
                                                class="
                                                    btn
                                                    btn-outline
                                                    btn-small
                                                "
                                            >

                                                Ver mosaico

                                            </a>


                                        @endif



                                    {{-- Procesando --}}
                                    @elseif(
                                        in_array(
                                            $mosaic->status,
                                            [
                                                'uploading',
                                                'inspecting',
                                                'processing'
                                            ]
                                        )
                                    )


                                        <span class="file-meta">

                                            En proceso...

                                        </span>


                                    @endif


                                </div>

                            </td>


                        </tr>


                    @endforeach


                    </tbody>

                </table>

            </div>


        @endif

    </div>



    {{-- =========================================================
         NOTA METODOLÓGICA
    ========================================================== --}}

    <div class="card">

        <div class="card-body">

            <strong>
                Trazabilidad geoespacial
            </strong>

            <p
                style="
                    margin: 7px 0 0;
                    color: var(--text-soft);
                    line-height: 1.55;
                    font-size: 13px;
                "
            >

                El GeoTIFF original permanece almacenado
                sin modificaciones en Cloudflare R2.

                La vista JPG se utiliza exclusivamente para
                visualización y no interviene en cálculos
                dasométricos, segmentación ni mediciones.

                Los análisis científicos continúan utilizando
                el raster original junto con su CRS, GSD,
                transformación espacial y SHA-256.

            </p>

        </div>

    </div>


</div>



<script>

    const csrf =
        document
            .querySelector(
                'meta[name="csrf-token"]'
            )
            .content;


    const button =
        document.getElementById(
            'uploadButton'
        );


    const fileInput =
        document.getElementById(
            'mosaicFile'
        );


    const project =
        document.getElementById(
            'project'
        );


    const progress =
        document.getElementById(
            'uploadProgress'
        );


    const progressWrapper =
        document.getElementById(
            'progressWrapper'
        );


    const progressLabel =
        document.getElementById(
            'progressLabel'
        );


    const progressPercent =
        document.getElementById(
            'progressPercent'
        );


    const statusBox =
        document.getElementById(
            'uploadStatus'
        );


    const selectedFileInfo =
        document.getElementById(
            'selectedFileInfo'
        );



    /*
    |--------------------------------------------------------------------------
    | ARCHIVO SELECCIONADO
    |--------------------------------------------------------------------------
    */

    if (fileInput) {

        fileInput.addEventListener(
            'change',
            () => {


                if (
                    !fileInput
                        .files
                        .length
                ) {

                    if (
                        selectedFileInfo
                    ) {

                        selectedFileInfo
                            .textContent =
                            '';

                    }

                    return;
                }


                const file =
                    fileInput.files[0];


                if (
                    selectedFileInfo
                ) {

                    selectedFileInfo
                        .textContent =
                        file.name
                        + ' · '
                        + formatBytes(
                            file.size
                        );

                }

            }
        );

    }



    /*
    |--------------------------------------------------------------------------
    | SUBIDA DIRECTA
    |--------------------------------------------------------------------------
    */

    if (button) {

        button.addEventListener(
            'click',
            async () => {


                if (
                    !fileInput
                    ||
                    !fileInput
                        .files
                        .length
                ) {

                    showUploadMessage(
                        'Selecciona un archivo .tif o .tiff.',
                        'error'
                    );

                    return;
                }


                if (
                    !project
                    ||
                    !project.value
                ) {

                    showUploadMessage(
                        'Selecciona un proyecto.',
                        'error'
                    );

                    return;
                }


                const file =
                    fileInput.files[0];


                const extension =
                    file.name
                        .split('.')
                        .pop()
                        .toLowerCase();


                if (
                    ![
                        'tif',
                        'tiff'
                    ].includes(
                        extension
                    )
                ) {

                    showUploadMessage(
                        'El archivo debe ser .tif o .tiff.',
                        'error'
                    );

                    return;
                }


                try {


                    button.disabled =
                        true;


                    if (
                        progressWrapper
                    ) {

                        progressWrapper
                            .hidden =
                            false;

                    }


                    if (progress) {

                        progress.value =
                            0;

                    }


                    updateProgress(
                        0,
                        'Preparando subida...'
                    );


                    /*
                    |--------------------------------------------------------------------------
                    | 1. URL FIRMADA
                    |--------------------------------------------------------------------------
                    */

                    const presignResponse =
                        await fetch(
                            '{{ route('mosaics.presign') }}',
                            {

                                method:
                                    'POST',

                                headers: {

                                    'Content-Type':
                                        'application/json',

                                    'X-CSRF-TOKEN':
                                        csrf,

                                    'Accept':
                                        'application/json'
                                },

                                body:
                                    JSON.stringify({

                                        project_id:
                                            project.value,

                                        filename:
                                            file.name,

                                        size_bytes:
                                            file.size,

                                        mime_type:
                                            file.type
                                            || 'image/tiff'
                                    })
                            }
                        );


                    const presign =
                        await presignResponse
                            .json();


                    if (
                        !presignResponse.ok
                    ) {

                        throw new Error(

                            presign.message

                            || (

                                presign.errors

                                ? Object
                                    .values(
                                        presign.errors
                                    )
                                    .flat()
                                    .join(' ')

                                : null
                            )

                            || 'No se pudo preparar la subida.'
                        );

                    }


                    updateProgress(
                        0,
                        'Subiendo directamente a R2...'
                    );


                    /*
                    |--------------------------------------------------------------------------
                    | 2. NAVEGADOR → R2
                    |--------------------------------------------------------------------------
                    */

                    await uploadToR2(

                        file,

                        presign
                            .upload_url,

                        presign
                            .headers
                    );


                    /*
                    |--------------------------------------------------------------------------
                    | 3. CONFIRMAR CON LARAVEL
                    |--------------------------------------------------------------------------
                    */

                    updateProgress(
                        100,
                        'Verificando archivo en R2...'
                    );


                    const completeResponse =
                        await fetch(
                            '{{ route('mosaics.complete') }}',
                            {

                                method:
                                    'POST',

                                headers: {

                                    'Content-Type':
                                        'application/json',

                                    'X-CSRF-TOKEN':
                                        csrf,

                                    'Accept':
                                        'application/json'
                                },

                                body:
                                    JSON.stringify({

                                        mosaic_uuid:
                                            presign
                                                .mosaic_uuid
                                    })
                            }
                        );


                    const complete =
                        await completeResponse
                            .json();


                    if (
                        !completeResponse.ok
                    ) {

                        throw new Error(

                            complete.message

                            || 'No se pudo confirmar la subida.'
                        );

                    }


                    updateProgress(
                        100,
                        'Ortomosaico almacenado correctamente.'
                    );


                    if (statusBox) {

                        statusBox
                            .style
                            .color =
                            '#177245';

                    }


                    setTimeout(
                        () => {

                            window.location
                                .reload();

                        },
                        900
                    );


                } catch (error) {


                    console.error(
                        error
                    );


                    showUploadMessage(
                        error.message,
                        'error'
                    );


                } finally {


                    button.disabled =
                        false;

                }

            }
        );

    }



    /*
    |--------------------------------------------------------------------------
    | PUT DIRECTO A R2
    |--------------------------------------------------------------------------
    */

    function uploadToR2(
        file,
        url,
        signedHeaders
    ) {

        return new Promise(
            (resolve, reject) => {


                const xhr =
                    new XMLHttpRequest();


                xhr.open(
                    'PUT',
                    url,
                    true
                );


                if (
                    signedHeaders
                ) {

                    Object.entries(
                        signedHeaders
                    ).forEach(
                        ([key, value]) => {


                            if (
                                key
                                    .toLowerCase()
                                !== 'host'
                            ) {

                                xhr.setRequestHeader(
                                    key,
                                    value
                                );

                            }

                        }
                    );

                }


                xhr.upload.onprogress =
                    function (event) {


                        if (
                            event
                                .lengthComputable
                        ) {

                            const percent =
                                Math.round(
                                    (
                                        event.loaded
                                        /
                                        event.total
                                    )
                                    * 100
                                );


                            updateProgress(
                                percent,
                                'Subiendo ortomosaico...'
                            );

                        }

                    };


                xhr.onload =
                    function () {


                        if (
                            xhr.status >= 200
                            &&
                            xhr.status < 300
                        ) {

                            resolve();

                        } else {

                            reject(
                                new Error(
                                    'R2 respondió HTTP '
                                    + xhr.status
                                )
                            );

                        }

                    };


                xhr.onerror =
                    function () {

                        reject(
                            new Error(
                                'Error de red o CORS durante la subida.'
                            )
                        );

                    };


                xhr.onabort =
                    function () {

                        reject(
                            new Error(
                                'La subida fue cancelada.'
                            )
                        );

                    };


                xhr.send(
                    file
                );

            }
        );

    }



    /*
    |--------------------------------------------------------------------------
    | UTILIDADES
    |--------------------------------------------------------------------------
    */

    function updateProgress(
        percent,
        text
    ) {


        if (progress) {

            progress.value =
                percent;

        }


        if (
            progressPercent
        ) {

            progressPercent
                .textContent =
                percent
                + ' %';

        }


        if (
            progressLabel
        ) {

            progressLabel
                .textContent =
                text;

        }


        if (statusBox) {

            statusBox
                .textContent =
                text;


            statusBox
                .style
                .color =
                '#176b4d';

        }

    }



    function showUploadMessage(
        message,
        type = 'info'
    ) {


        if (
            progressWrapper
        ) {

            progressWrapper
                .hidden =
                false;

        }


        if (statusBox) {

            statusBox
                .textContent =
                message;


            statusBox
                .style
                .color =
                type === 'error'
                    ? '#b42318'
                    : '#176b4d';

        }

    }



    function formatBytes(
        bytes
    ) {


        if (!bytes) {

            return '0 B';

        }


        const units = [

            'B',
            'KiB',
            'MiB',
            'GiB',
            'TiB'

        ];


        const index =
            Math.min(

                Math.floor(
                    Math.log(bytes)
                    /
                    Math.log(1024)
                ),

                units.length - 1
            );


        const value =
            bytes
            /
            Math.pow(
                1024,
                index
            );


        return (
            value.toFixed(
                index === 0
                    ? 0
                    : 2
            )
            + ' '
            + units[index]
        );

    }

</script>


</body>

</html>