<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <meta name="csrf-token" content="{{ csrf_token() }}">
    <title>Ortomosaicos UAV | UAV Forest AI</title>

    <style>
        :root {
            --bg: #f3f6f4;
            --surface: #ffffff;
            --surface-soft: #f7faf8;
            --surface-dark: #102a21;
            --text: #17251f;
            --muted: #66766e;
            --muted-2: #8a9891;
            --border: #dce6e0;
            --border-strong: #c9d8d0;
            --forest: #176b4d;
            --forest-dark: #0f4e38;
            --forest-soft: #e8f4ee;
            --forest-softer: #f3f9f6;
            --blue: #245f92;
            --blue-soft: #edf5fb;
            --amber: #9b6412;
            --amber-soft: #fff6e5;
            --red: #b42318;
            --red-soft: #fff0ee;
            --purple: #6d4ab0;
            --purple-soft: #f2edfb;
            --shadow-sm: 0 6px 18px rgba(20, 50, 35, .055);
            --shadow-md: 0 16px 38px rgba(20, 50, 35, .085);
            --radius: 16px;
            --radius-sm: 11px;
        }

        * { box-sizing: border-box; }
        html { scroll-behavior: smooth; }
        body {
            margin: 0;
            font-family: Inter, ui-sans-serif, system-ui, -apple-system, BlinkMacSystemFont, "Segoe UI", sans-serif;
            background: var(--bg);
            color: var(--text);
        }
        button, input, select { font: inherit; }
        a { color: inherit; }
        [hidden] { display: none !important; }

        /* App shell */
        .appbar {
            background: var(--surface-dark);
            color: #fff;
            border-bottom: 1px solid rgba(255,255,255,.08);
        }
        .appbar-inner {
            max-width: 1540px;
            margin: 0 auto;
            padding: 15px 28px;
            display: flex;
            align-items: center;
            justify-content: space-between;
            gap: 22px;
        }
        .brand { display: flex; align-items: center; gap: 12px; min-width: 0; }
        .brand-mark {
            width: 42px; height: 42px; border-radius: 12px;
            display: grid; place-items: center;
            background: rgba(255,255,255,.09);
            border: 1px solid rgba(255,255,255,.12);
            flex: 0 0 auto;
        }
        .brand-mark svg { width: 25px; height: 25px; }
        .brand-name { font-size: 15px; font-weight: 780; letter-spacing: .01em; }
        .brand-sub { margin-top: 2px; font-size: 11px; color: rgba(255,255,255,.64); }
        .appbar-module {
            display: inline-flex; align-items: center; gap: 8px;
            padding: 7px 11px; border-radius: 999px;
            font-size: 11px; font-weight: 700;
            color: rgba(255,255,255,.8);
            border: 1px solid rgba(255,255,255,.1);
            background: rgba(255,255,255,.05);
        }
        .appbar-module::before {
            content: ""; width: 7px; height: 7px; border-radius: 999px; background: #73d69e;
        }

        .page { max-width: 1540px; margin: 0 auto; padding: 28px 28px 64px; }
        .breadcrumb {
            display: flex; align-items: center; gap: 7px; flex-wrap: wrap;
            margin-bottom: 16px; color: var(--muted); font-size: 11px; font-weight: 650;
        }
        .breadcrumb strong { color: var(--forest-dark); }
        .page-header {
            display: flex; justify-content: space-between; align-items: flex-start; gap: 24px;
            margin-bottom: 22px;
        }
        .eyebrow {
            margin-bottom: 7px; color: var(--forest); font-size: 10px;
            font-weight: 800; letter-spacing: .1em; text-transform: uppercase;
        }
        .page-title { margin: 0; font-size: clamp(28px, 3vw, 38px); letter-spacing: -.035em; line-height: 1.08; }
        .page-subtitle { max-width: 880px; margin: 10px 0 0; color: var(--muted); line-height: 1.62; font-size: 14px; }
        .header-actions { display: flex; gap: 10px; flex-wrap: wrap; }

        /* Buttons */
        .btn {
            appearance: none; border: 0; border-radius: 10px; padding: 10px 14px;
            display: inline-flex; align-items: center; justify-content: center; gap: 8px;
            font-size: 12px; font-weight: 750; text-decoration: none; cursor: pointer;
            transition: transform .16s ease, background .16s ease, border-color .16s ease, box-shadow .16s ease;
            white-space: nowrap;
        }
        .btn:hover { transform: translateY(-1px); }
        .btn:focus-visible { outline: 3px solid rgba(23,107,77,.18); outline-offset: 2px; }
        .btn:disabled { cursor: not-allowed; opacity: .58; transform: none; }
        .btn-primary { color: #fff; background: var(--forest); box-shadow: 0 6px 14px rgba(23,107,77,.18); }
        .btn-primary:hover { background: var(--forest-dark); }
        .btn-secondary { color: var(--forest-dark); background: var(--forest-soft); }
        .btn-secondary:hover { background: #dceee5; }
        .btn-outline { color: var(--text); background: #fff; border: 1px solid var(--border); }
        .btn-outline:hover { border-color: var(--border-strong); background: var(--surface-soft); }
        .btn-ghost { color: var(--muted); background: transparent; border: 1px solid transparent; }
        .btn-ghost:hover { background: var(--surface-soft); color: var(--text); }
        .btn-small { padding: 8px 10px; font-size: 11px; }
        .btn svg { width: 15px; height: 15px; flex: 0 0 auto; }

        /* Alerts */
        .alert {
            margin-bottom: 18px; padding: 13px 15px; border-radius: 11px;
            font-size: 12px; line-height: 1.55; border: 1px solid;
        }
        .alert-success { color: #155b3b; background: #eaf7ef; border-color: #c6e4d2; }
        .alert-error { color: var(--red); background: var(--red-soft); border-color: #efc9c5; }

        /* Summary strip */
        .summary-strip {
            display: grid; grid-template-columns: repeat(5, minmax(0,1fr));
            background: #fff; border: 1px solid var(--border); border-radius: var(--radius);
            box-shadow: var(--shadow-sm); margin-bottom: 22px; overflow: hidden;
        }
        .summary-item { padding: 16px 18px; position: relative; min-width: 0; }
        .summary-item + .summary-item { border-left: 1px solid var(--border); }
        .summary-label { color: var(--muted); font-size: 10px; font-weight: 720; text-transform: uppercase; letter-spacing: .055em; }
        .summary-value { margin-top: 3px; font-size: 24px; font-weight: 790; letter-spacing: -.03em; }
        .summary-detail { margin-top: 2px; color: var(--muted-2); font-size: 10px; }
        .summary-item.success .summary-value { color: var(--forest); }
        .summary-item.warning .summary-value { color: var(--amber); }
        .summary-item.danger .summary-value { color: var(--red); }
        .summary-item.info .summary-value { color: var(--blue); }

        /* Cards */
        .card { background: var(--surface); border: 1px solid var(--border); border-radius: var(--radius); box-shadow: var(--shadow-sm); }
        .section-card { margin-bottom: 22px; overflow: hidden; }
        .section-header {
            padding: 18px 20px; display: flex; align-items: flex-start; justify-content: space-between; gap: 18px;
            border-bottom: 1px solid var(--border); background: linear-gradient(180deg, #fff, #fcfdfc);
        }
        .section-title { margin: 0; font-size: 16px; letter-spacing: -.015em; }
        .section-desc { margin-top: 5px; color: var(--muted); font-size: 11px; line-height: 1.5; }
        .section-body { padding: 20px; }
        .section-kicker { font-size: 9px; color: var(--forest); font-weight: 800; text-transform: uppercase; letter-spacing: .09em; margin-bottom: 5px; }

        /* Upload */
        .upload-layout { display: grid; grid-template-columns: minmax(230px,.55fr) minmax(420px,1.45fr); gap: 18px; align-items: stretch; }
        .field label { display: block; margin-bottom: 7px; font-size: 11px; font-weight: 750; color: #34483f; }
        select, .search-input {
            width: 100%; padding: 11px 12px; background: #fff; border: 1px solid var(--border-strong);
            border-radius: 10px; color: var(--text); font-size: 12px;
        }
        select:focus, .search-input:focus { outline: none; border-color: var(--forest); box-shadow: 0 0 0 3px rgba(23,107,77,.09); }
        .project-box { height: 100%; padding: 16px; border: 1px solid var(--border); border-radius: 13px; background: var(--surface-soft); }
        .project-box .field { margin-bottom: 12px; }
        .project-note { color: var(--muted); font-size: 10px; line-height: 1.5; }
        .dropzone {
            min-height: 170px; padding: 22px; border: 1.5px dashed #b9cbc2; border-radius: 14px;
            background: linear-gradient(180deg, #fbfdfc, #f7faf8); display: flex; align-items: center; justify-content: center;
            text-align: center; cursor: pointer; transition: border-color .16s ease, background .16s ease, box-shadow .16s ease;
        }
        .dropzone:hover, .dropzone.dragging { border-color: var(--forest); background: var(--forest-softer); box-shadow: inset 0 0 0 1px rgba(23,107,77,.04); }
        .dropzone input { display: none; }
        .drop-icon {
            width: 46px; height: 46px; margin: 0 auto 10px; display: grid; place-items: center;
            border-radius: 13px; background: var(--forest-soft); color: var(--forest);
        }
        .drop-icon svg { width: 24px; height: 24px; }
        .drop-title { font-size: 13px; font-weight: 760; }
        .drop-sub { margin-top: 4px; color: var(--muted); font-size: 11px; }
        .drop-types { margin-top: 9px; color: var(--muted-2); font-size: 9px; font-weight: 750; letter-spacing: .05em; text-transform: uppercase; }
        .selected-file {
            margin-top: 12px; padding: 11px 12px; display: none; align-items: center; justify-content: space-between; gap: 12px;
            border: 1px solid var(--border); border-radius: 10px; background: #fff;
        }
        .selected-file.visible { display: flex; }
        .selected-file-name { min-width: 0; font-size: 11px; font-weight: 730; overflow: hidden; text-overflow: ellipsis; white-space: nowrap; }
        .selected-file-size { flex: 0 0 auto; color: var(--muted); font-size: 10px; }
        .upload-actions { margin-top: 14px; display: flex; align-items: center; gap: 12px; flex-wrap: wrap; }
        .upload-privacy { color: var(--muted); font-size: 10px; line-height: 1.45; }

        /* Progress */
        .progress-shell { margin-top: 16px; padding: 14px; border-radius: 12px; background: var(--surface-soft); border: 1px solid var(--border); }
        .progress-head { display: flex; justify-content: space-between; gap: 15px; margin-bottom: 8px; }
        .progress-label { font-size: 11px; font-weight: 750; }
        .progress-percent { font-size: 11px; font-weight: 800; color: var(--forest); }
        progress { width: 100%; height: 9px; border: 0; border-radius: 999px; overflow: hidden; appearance: none; }
        progress::-webkit-progress-bar { background: #e3ebe7; border-radius: 999px; }
        progress::-webkit-progress-value { background: var(--forest); border-radius: 999px; }
        progress::-moz-progress-bar { background: var(--forest); }
        .progress-meta-line { margin-top: 7px; display: flex; justify-content: space-between; gap: 10px; color: var(--muted); font-size: 9px; }
        .progress-stages { margin-top: 12px; display: flex; gap: 7px; flex-wrap: wrap; }
        .stage-chip { padding: 5px 8px; border-radius: 999px; background: #fff; border: 1px solid var(--border); color: var(--muted); font-size: 9px; font-weight: 700; }
        .stage-chip.active { color: var(--forest-dark); background: var(--forest-soft); border-color: #cce4d7; }
        .stage-chip.done { color: #155b3b; background: #eaf7ef; border-color: #c6e4d2; }
        .upload-status { margin-top: 8px; font-size: 10px; font-weight: 700; }

        /* Explorer */
        .explorer-toolbar { padding: 15px 20px; display: grid; grid-template-columns: minmax(250px,1fr) auto; gap: 14px; align-items: center; border-bottom: 1px solid var(--border); }
        .search-wrap { position: relative; max-width: 480px; }
        .search-wrap svg { position: absolute; left: 11px; top: 50%; transform: translateY(-50%); width: 15px; height: 15px; color: var(--muted-2); pointer-events: none; }
        .search-input { padding-left: 34px; }
        .filters { display: flex; gap: 7px; align-items: center; flex-wrap: wrap; justify-content: flex-end; }
        .filter-chip {
            border: 1px solid var(--border); background: #fff; color: var(--muted); padding: 7px 10px;
            border-radius: 999px; font-size: 10px; font-weight: 740; cursor: pointer;
        }
        .filter-chip:hover { border-color: var(--border-strong); color: var(--text); }
        .filter-chip.active { background: var(--forest-soft); color: var(--forest-dark); border-color: #cce4d7; }
        .results-count { padding: 0 20px 11px; color: var(--muted); font-size: 10px; }
        .mosaic-list { padding: 0 20px 20px; display: grid; gap: 12px; }
        .mosaic-row {
            border: 1px solid var(--border); border-radius: 14px; background: #fff; overflow: hidden;
            transition: border-color .16s ease, box-shadow .16s ease, transform .16s ease;
        }
        .mosaic-row:hover { border-color: #cbd9d1; box-shadow: 0 9px 24px rgba(20,50,35,.055); transform: translateY(-1px); }
        .mosaic-main {
            padding: 14px; display: grid;
            grid-template-columns: 152px minmax(220px,1.5fr) minmax(200px,1fr) 160px 170px auto;
            gap: 16px; align-items: center;
        }
        .preview-box {
            width: 152px; height: 92px; border-radius: 10px; border: 1px solid var(--border); overflow: hidden;
            background: linear-gradient(135deg,#edf3ef,#f8faf9); display: flex; align-items: center; justify-content: center; position: relative;
        }
        .preview-box img { width: 100%; height: 100%; object-fit: cover; display: block; }
        .preview-empty { padding: 10px; color: var(--muted-2); font-size: 9px; line-height: 1.4; text-align: center; }
        .preview-link { display: block; text-decoration: none; }
        .preview-link:hover .preview-box { border-color: var(--forest); }
        .preview-badge { position: absolute; left: 7px; bottom: 7px; padding: 3px 6px; border-radius: 6px; background: rgba(10,35,26,.77); color: #fff; font-size: 8px; backdrop-filter: blur(4px); }
        .mosaic-title { font-size: 13px; font-weight: 770; overflow-wrap: anywhere; }
        .mosaic-project { margin-top: 5px; color: var(--muted); font-size: 10px; }
        .mosaic-filemeta { margin-top: 8px; display: flex; gap: 7px; flex-wrap: wrap; }
        .micro-tag { padding: 4px 6px; border-radius: 6px; background: var(--surface-soft); border: 1px solid var(--border); color: var(--muted); font-size: 8px; font-weight: 700; }
        .specs { display: grid; grid-template-columns: 1fr 1fr; gap: 8px 12px; }
        .spec-label { color: var(--muted-2); font-size: 8px; text-transform: uppercase; letter-spacing: .05em; }
        .spec-value { margin-top: 2px; font-size: 10px; font-weight: 730; overflow-wrap: anywhere; }
        .status-stack { display: grid; gap: 7px; justify-items: start; }
        .badge { display: inline-flex; align-items: center; gap: 6px; padding: 5px 8px; border-radius: 999px; font-size: 9px; font-weight: 780; white-space: nowrap; }
        .badge::before { content: ""; width: 6px; height: 6px; border-radius: 999px; background: currentColor; }
        .badge-ready { color: #15603d; background: #eaf7ef; }
        .badge-uploading, .badge-inspecting, .badge-processing, .badge-uploaded { color: var(--amber); background: var(--amber-soft); }
        .badge-failed { color: var(--red); background: var(--red-soft); }
        .badge-default { color: var(--blue); background: var(--blue-soft); }
        .status-next { color: var(--muted); font-size: 9px; line-height: 1.4; }
        .qa-block { min-width: 0; }
        .qa-head { display: flex; justify-content: space-between; gap: 8px; align-items: baseline; }
        .qa-title { font-size: 9px; font-weight: 760; color: var(--muted); text-transform: uppercase; letter-spacing: .05em; }
        .qa-score { font-size: 10px; font-weight: 800; }
        .qa-bar { height: 6px; margin-top: 7px; background: #edf2ef; border-radius: 999px; overflow: hidden; }
        .qa-fill { height: 100%; border-radius: 999px; background: var(--forest); }
        .qa-note { margin-top: 5px; color: var(--muted-2); font-size: 8px; line-height: 1.35; }
        .row-actions { display: flex; gap: 7px; align-items: center; justify-content: flex-end; flex-wrap: wrap; }
        .action-menu { position: relative; }
        .menu-toggle { width: 34px; height: 34px; padding: 0; font-size: 17px; }
        .menu-panel {
            position: absolute; right: 0; top: calc(100% + 7px); z-index: 50; min-width: 210px;
            padding: 6px; background: #fff; border: 1px solid var(--border); border-radius: 11px; box-shadow: var(--shadow-md); display: none;
        }
        .action-menu.open .menu-panel { display: grid; }
        .menu-item { width: 100%; border: 0; background: transparent; color: var(--text); padding: 9px 10px; border-radius: 8px; text-align: left; text-decoration: none; font-size: 10px; font-weight: 650; cursor: pointer; }
        .menu-item:hover { background: var(--surface-soft); }
        .menu-panel form { margin: 0; }
        .menu-panel .menu-item { display: block; }

        /* Details drawer inside row */
        .technical-panel { display: none; border-top: 1px solid var(--border); background: #fbfdfc; }
        .technical-panel.open { display: block; }
        .technical-inner { padding: 18px; }
        .technical-header { display: flex; justify-content: space-between; gap: 16px; align-items: flex-start; margin-bottom: 14px; }
        .technical-title { font-size: 13px; font-weight: 780; }
        .technical-sub { margin-top: 3px; color: var(--muted); font-size: 9px; }
        .technical-grid { display: grid; grid-template-columns: repeat(4,minmax(0,1fr)); gap: 10px; }
        .technical-card { padding: 12px; background: #fff; border: 1px solid var(--border); border-radius: 10px; min-width: 0; }
        .technical-card-title { margin-bottom: 9px; color: var(--forest-dark); font-size: 9px; font-weight: 800; text-transform: uppercase; letter-spacing: .05em; }
        .detail-line { display: flex; justify-content: space-between; gap: 10px; padding: 5px 0; border-bottom: 1px dashed #edf1ef; font-size: 9px; }
        .detail-line:last-child { border-bottom: 0; }
        .detail-line span:first-child { color: var(--muted); }
        .detail-line strong { text-align: right; overflow-wrap: anywhere; }
        .mono { font-family: ui-monospace, SFMono-Regular, Menlo, Monaco, Consolas, monospace; }
        .hash-line { margin-top: 10px; padding: 10px; border-radius: 9px; background: #f6f8f7; border: 1px solid var(--border); display: flex; align-items: center; justify-content: space-between; gap: 10px; }
        .hash-value { min-width: 0; overflow: hidden; text-overflow: ellipsis; white-space: nowrap; font-size: 8px; color: var(--muted); }

        /* Empty */
        .empty-state { padding: 48px 20px 52px; text-align: center; color: var(--muted); }
        .empty-icon { width: 48px; height: 48px; margin: 0 auto 12px; display: grid; place-items: center; border-radius: 14px; background: var(--forest-soft); color: var(--forest); }
        .empty-icon svg { width: 24px; height: 24px; }
        .empty-title { color: var(--text); font-size: 14px; font-weight: 780; margin-bottom: 5px; }
        .no-results { display: none; padding: 34px 20px 42px; text-align: center; color: var(--muted); font-size: 11px; }

        /* Integrity */
        .integrity-grid { display: grid; grid-template-columns: repeat(3,minmax(0,1fr)); gap: 12px; }
        .integrity-step { position: relative; padding: 16px; border: 1px solid var(--border); border-radius: 12px; background: var(--surface-soft); }
        .integrity-step:not(:last-child)::after { content: "→"; position: absolute; right: -17px; top: 50%; transform: translateY(-50%); z-index: 2; color: var(--muted-2); font-weight: 800; }
        .integrity-index { color: var(--forest); font-size: 9px; font-weight: 800; letter-spacing: .06em; }
        .integrity-title { margin-top: 4px; font-size: 12px; font-weight: 780; }
        .integrity-copy { margin-top: 5px; color: var(--muted); font-size: 9px; line-height: 1.5; }
        .method-note { margin-top: 13px; padding: 11px 12px; border-radius: 10px; background: var(--blue-soft); color: #315d7c; border: 1px solid #d9e8f3; font-size: 9px; line-height: 1.55; }

        @media (max-width: 1250px) {
            .summary-strip { grid-template-columns: repeat(3,1fr); }
            .summary-item:nth-child(4) { border-left: 0; border-top: 1px solid var(--border); }
            .summary-item:nth-child(5) { border-top: 1px solid var(--border); }
            .mosaic-main { grid-template-columns: 140px minmax(220px,1.4fr) minmax(180px,1fr) 150px auto; }
            .qa-block { display: none; }
            .technical-grid { grid-template-columns: repeat(2,1fr); }
        }
        @media (max-width: 980px) {
            .page, .appbar-inner { padding-left: 18px; padding-right: 18px; }
            .page-header { flex-direction: column; }
            .upload-layout { grid-template-columns: 1fr; }
            .explorer-toolbar { grid-template-columns: 1fr; }
            .filters { justify-content: flex-start; }
            .mosaic-main { grid-template-columns: 128px 1fr auto; }
            .specs, .status-stack { grid-column: 2; }
            .row-actions { grid-column: 3; grid-row: 1 / span 3; align-self: center; }
            .integrity-grid { grid-template-columns: 1fr; }
            .integrity-step:not(:last-child)::after { content: "↓"; right: auto; left: 50%; top: auto; bottom: -17px; transform: translateX(-50%); }
        }
        @media (max-width: 680px) {
            .appbar-module { display: none; }
            .page { padding: 22px 13px 44px; }
            .appbar-inner { padding: 12px 13px; }
            .summary-strip { grid-template-columns: 1fr 1fr; }
            .summary-item { border-top: 1px solid var(--border); }
            .summary-item:nth-child(-n+2) { border-top: 0; }
            .summary-item:nth-child(odd) { border-left: 0; }
            .mosaic-main { grid-template-columns: 1fr; }
            .preview-box { width: 100%; height: 160px; }
            .specs, .status-stack, .row-actions { grid-column: auto; grid-row: auto; }
            .row-actions { justify-content: flex-start; }
            .technical-grid { grid-template-columns: 1fr; }
            .section-header { flex-direction: column; }
        }
    </style>
</head>
<body>

@php
    $totalMosaics = $mosaics->count();

    $readyMosaics = $mosaics->where('status', 'ready')->count();

    $preparingMosaics = $mosaics->whereIn('status', [
        'uploading', 'uploaded', 'inspecting', 'processing'
    ])->count();

    $failedMosaics = $mosaics->where('status', 'failed')->count();

    $previewMosaics = $mosaics->filter(
        fn ($mosaic) => !empty($mosaic->preview_object_key)
    )->count();

    $tilingPreviewMosaics = $mosaics->filter(
        fn ($mosaic) =>
            !empty(data_get($mosaic->metadata, 'tiling_preview.footprints_object_key'))
            && !empty(data_get($mosaic->metadata, 'tiling_preview.coverage_object_key'))
    )->count();

    $qaCompleteMosaics = $mosaics->filter(function ($mosaic) {
        $hasPreview = !empty($mosaic->preview_object_key);
        $hasTiling =
            !empty(data_get($mosaic->metadata, 'tiling_preview.footprints_object_key'))
            && !empty(data_get($mosaic->metadata, 'tiling_preview.coverage_object_key'));
        $uncovered = data_get($mosaic->metadata, 'tiling_preview.coverage.uncovered_valid_pixels');

        return $mosaic->status === 'ready'
            && !empty($mosaic->crs)
            && $mosaic->gsd_cm !== null
            && $hasPreview
            && $hasTiling
            && $uncovered !== null
            && (int) $uncovered === 0;
    })->count();
@endphp

<header class="appbar">
    <div class="appbar-inner">
        <div class="brand">
            <div class="brand-mark" aria-hidden="true">
                <svg viewBox="0 0 32 32" fill="none">
                    <path d="M16 4 7.5 15h5.2L8.8 21h5.3v6h3.8v-6h5.3l-3.9-6h5.2L16 4Z" stroke="currentColor" stroke-width="1.8" stroke-linejoin="round"/>
                    <path d="M4 25.5h24" stroke="currentColor" stroke-width="1.6" stroke-linecap="round" opacity=".6"/>
                </svg>
            </div>
            <div>
                <div class="brand-name">UAV Forest AI</div>
                <div class="brand-sub">Inteligencia geoespacial para análisis forestal</div>
            </div>
        </div>
        <div class="appbar-module">Módulo de datos UAV</div>
    </div>
</header>

<main class="page">
    <div class="breadcrumb">
        <span>Datos UAV</span><span>/</span><strong>Ortomosaicos</strong>
    </div>

    <div class="page-header">
        <div>
            <div class="eyebrow">Insumos geoespaciales</div>
            <h1 class="page-title">Ortomosaicos UAV</h1>
            <p class="page-subtitle">
                Registra ortomosaicos GeoTIFF, verifica sus propiedades espaciales y prepara los productos QA/QC necesarios antes de incorporarlos al análisis con inteligencia artificial.
            </p>
        </div>
        <div class="header-actions">
            @if(\Illuminate\Support\Facades\Route::has('analysis.index'))
                <a href="{{ route('analysis.index') }}" class="btn btn-outline">
                    <svg viewBox="0 0 24 24" fill="none" aria-hidden="true">
                        <path d="M4 5h16v14H4zM8 9h8M8 13h5" stroke="currentColor" stroke-width="1.7" stroke-linecap="round" stroke-linejoin="round"/>
                    </svg>
                    Centro de análisis IA
                </a>
            @endif
        </div>
    </div>

    @if(session('success'))
        <div class="alert alert-success">{{ session('success') }}</div>
    @endif

    @if(session('error'))
        <div class="alert alert-error">{{ session('error') }}</div>
    @endif

    @if($errors->any())
        <div class="alert alert-error">
            <strong>No se pudo completar la operación.</strong><br>
            {{ $errors->first() }}
        </div>
    @endif

    <section class="summary-strip" aria-label="Resumen de ortomosaicos">
        <div class="summary-item">
            <div class="summary-label">Total</div>
            <div class="summary-value">{{ $totalMosaics }}</div>
            <div class="summary-detail">Ortomosaicos registrados</div>
        </div>
        <div class="summary-item success">
            <div class="summary-label">Listos</div>
            <div class="summary-value">{{ $readyMosaics }}</div>
            <div class="summary-detail">Disponibles para análisis</div>
        </div>
        <div class="summary-item warning">
            <div class="summary-label">En preparación</div>
            <div class="summary-value">{{ $preparingMosaics }}</div>
            <div class="summary-detail">Carga o inspección en curso</div>
        </div>
        <div class="summary-item danger">
            <div class="summary-label">Incidencias</div>
            <div class="summary-value">{{ $failedMosaics }}</div>
            <div class="summary-detail">Requieren atención</div>
        </div>
        <div class="summary-item info">
            <div class="summary-label">QA/QC técnico</div>
            <div class="summary-value">{{ $qaCompleteMosaics }}</div>
            <div class="summary-detail">Evidencias técnicas completas</div>
        </div>
    </section>

    <section class="card section-card" id="newMosaicSection">
        <div class="section-header">
            <div>
                <div class="section-kicker">Ingestión de datos</div>
                <h2 class="section-title">Nuevo ortomosaico</h2>
                <div class="section-desc">Asocia el GeoTIFF a un proyecto y transfiérelo al almacenamiento seguro del sistema.</div>
            </div>
        </div>

        <div class="section-body">
            @if($projects->isEmpty())
                <div class="alert alert-error">
                    No existe ningún proyecto activo. Primero debes crear o activar un proyecto antes de registrar un ortomosaico.
                </div>
            @else
                <div class="upload-layout">
                    <div class="project-box">
                        <div class="field">
                            <label for="project">Proyecto de destino</label>
                            <select id="project">
                                @foreach($projects as $project)
                                    <option value="{{ $project->id }}">{{ $project->name }}</option>
                                @endforeach
                            </select>
                        </div>
                        <div class="project-note">
                            El ortomosaico quedará asociado al proyecto seleccionado y podrá utilizarse posteriormente en el flujo de análisis IA.
                        </div>
                    </div>

                    <div>
                        <label class="dropzone" id="dropzone" for="mosaicFile">
                            <input type="file" id="mosaicFile" accept=".tif,.tiff,image/tiff">
                            <div>
                                <div class="drop-icon">
                                    <svg viewBox="0 0 24 24" fill="none" aria-hidden="true">
                                        <path d="M12 16V4m0 0L8 8m4-4 4 4M5 13v5a2 2 0 0 0 2 2h10a2 2 0 0 0 2-2v-5" stroke="currentColor" stroke-width="1.8" stroke-linecap="round" stroke-linejoin="round"/>
                                    </svg>
                                </div>
                                <div class="drop-title">Arrastra un GeoTIFF o selecciona un archivo</div>
                                <div class="drop-sub">El archivo original será conservado como insumo geoespacial del proyecto.</div>
                                <div class="drop-types">GeoTIFF · .TIF · .TIFF</div>
                            </div>
                        </label>

                        <div id="selectedFileCard" class="selected-file">
                            <div id="selectedFileName" class="selected-file-name">—</div>
                            <div id="selectedFileSize" class="selected-file-size">—</div>
                        </div>

                        <div class="upload-actions">
                            <button id="uploadButton" class="btn btn-primary" type="button">
                                <svg viewBox="0 0 24 24" fill="none" aria-hidden="true">
                                    <path d="M12 16V4m0 0L8 8m4-4 4 4M5 13v5a2 2 0 0 0 2 2h10a2 2 0 0 0 2-2v-5" stroke="currentColor" stroke-width="1.8" stroke-linecap="round" stroke-linejoin="round"/>
                                </svg>
                                Subir ortomosaico
                            </button>
                            <span class="upload-privacy">Transferencia directa al almacenamiento seguro; Laravel registra y verifica la carga.</span>
                        </div>

                        <div id="progressWrapper" class="progress-shell" hidden>
                            <div class="progress-head">
                                <span id="progressLabel" class="progress-label">Preparando...</span>
                                <span id="progressPercent" class="progress-percent">0 %</span>
                            </div>
                            <progress id="uploadProgress" value="0" max="100"></progress>
                            <div class="progress-meta-line">
                                <span id="progressBytes">0 B / 0 B</span>
                                <span id="progressState">Transferencia</span>
                            </div>
                            <div class="progress-stages">
                                <span id="stagePrepare" class="stage-chip active">Preparación</span>
                                <span id="stageTransfer" class="stage-chip">Transferencia</span>
                                <span id="stageVerify" class="stage-chip">Verificación</span>
                            </div>
                            <div id="uploadStatus" class="upload-status"></div>
                        </div>
                    </div>
                </div>
            @endif
        </div>
    </section>

    <section class="card section-card" id="mosaicExplorer">
        <div class="section-header">
            <div>
                <div class="section-kicker">Biblioteca geoespacial</div>
                <h2 class="section-title">Ortomosaicos registrados</h2>
                <div class="section-desc">Consulta disponibilidad, propiedades del raster y evidencias de preparación técnica antes de la inferencia.</div>
            </div>
            <span class="section-desc">{{ $totalMosaics }} registro{{ $totalMosaics === 1 ? '' : 's' }}</span>
        </div>

        @if($mosaics->isEmpty())
            <div class="empty-state">
                <div class="empty-icon">
                    <svg viewBox="0 0 24 24" fill="none" aria-hidden="true">
                        <path d="m4 8 8-4 8 4-8 4-8-4Zm0 4 8 4 8-4M4 16l8 4 8-4" stroke="currentColor" stroke-width="1.7" stroke-linecap="round" stroke-linejoin="round"/>
                    </svg>
                </div>
                <div class="empty-title">Todavía no hay ortomosaicos</div>
                <div>Selecciona un proyecto y carga tu primer GeoTIFF para iniciar la preparación del insumo.</div>
            </div>
        @else
            <div class="explorer-toolbar">
                <div class="search-wrap">
                    <svg viewBox="0 0 24 24" fill="none" aria-hidden="true">
                        <circle cx="11" cy="11" r="6.5" stroke="currentColor" stroke-width="1.7"/><path d="m16 16 4 4" stroke="currentColor" stroke-width="1.7" stroke-linecap="round"/>
                    </svg>
                    <input id="mosaicSearch" class="search-input" type="search" placeholder="Buscar por archivo o proyecto..." autocomplete="off">
                </div>
                <div class="filters" aria-label="Filtros de estado">
                    <button type="button" class="filter-chip active" data-filter="all">Todos</button>
                    <button type="button" class="filter-chip" data-filter="ready">Listos</button>
                    <button type="button" class="filter-chip" data-filter="preparing">En preparación</button>
                    <button type="button" class="filter-chip" data-filter="failed">Incidencias</button>
                </div>
            </div>
            <div id="resultsCount" class="results-count">Mostrando {{ $totalMosaics }} de {{ $totalMosaics }} ortomosaicos</div>

            <div class="mosaic-list" id="mosaicList">
                @foreach($mosaics as $mosaic)
                    @php
                        $badgeClass = match($mosaic->status) {
                            'ready' => 'badge-ready',
                            'uploaded' => 'badge-uploaded',
                            'uploading', 'inspecting', 'processing' => 'badge-processing',
                            'failed' => 'badge-failed',
                            default => 'badge-default',
                        };

                        $statusText = match($mosaic->status) {
                            'uploading' => 'Subiendo',
                            'uploaded' => 'Cargado',
                            'inspecting' => 'Inspeccionando',
                            'ready' => 'Listo para análisis',
                            'processing' => 'Procesando QA/QC',
                            'failed' => 'Incidencia',
                            default => ucfirst($mosaic->status),
                        };

                        $statusGroup = match($mosaic->status) {
                            'ready' => 'ready',
                            'failed' => 'failed',
                            'uploading', 'uploaded', 'inspecting', 'processing' => 'preparing',
                            default => 'preparing',
                        };

                        $nextActionText = match($mosaic->status) {
                            'uploaded' => 'Requiere inspección técnica',
                            'inspecting' => 'Inspección en curso',
                            'processing' => 'Productos técnicos en preparación',
                            'failed' => 'Revisa la incidencia y vuelve a inspeccionar',
                            'ready' => 'Disponible para el pipeline de IA',
                            default => 'Estado técnico registrado',
                        };

                        $hasPreview = !empty($mosaic->preview_object_key);
                        $previewMetadata = data_get($mosaic->metadata, 'preview', []);
                        $tilingPreviewMetadata = data_get($mosaic->metadata, 'tiling_preview', []);
                        $tilingFootprintsObjectKey = data_get($mosaic->metadata, 'tiling_preview.footprints_object_key');
                        $tilingCoverageObjectKey = data_get($mosaic->metadata, 'tiling_preview.coverage_object_key');
                        $hasTilingPreview = !empty($tilingFootprintsObjectKey) && !empty($tilingCoverageObjectKey);
                        $uncoveredValidPixels = data_get($tilingPreviewMetadata, 'coverage.uncovered_valid_pixels');
                        $meanValidFraction = data_get($tilingPreviewMetadata, 'summary.valid_fraction.mean');

                        $qaChecks = [
                            $mosaic->status === 'ready',
                            !empty($mosaic->crs),
                            $mosaic->gsd_cm !== null,
                            $hasPreview,
                            $hasTilingPreview,
                            $uncoveredValidPixels !== null && (int) $uncoveredValidPixels === 0,
                        ];
                        $qaPassed = collect($qaChecks)->filter()->count();
                        $qaTotal = count($qaChecks);
                        $qaPercent = (int) round(($qaPassed / $qaTotal) * 100);
                    @endphp

                    <article
                        class="mosaic-row"
                        data-mosaic-row
                        data-status="{{ $statusGroup }}"
                        data-search="{{ strtolower(($mosaic->original_name ?? '') . ' ' . ($mosaic->project?->name ?? '')) }}"
                    >
                        <div class="mosaic-main">
                            <div>
                                @if($hasPreview && \Illuminate\Support\Facades\Route::has('mosaics.preview'))
                                    <a href="{{ route('mosaics.preview', $mosaic->uuid) }}" target="_blank" rel="noopener" class="preview-link" title="Abrir vista previa">
                                        <div class="preview-box">
                                            <img src="{{ route('mosaics.preview', $mosaic->uuid) }}" alt="Vista previa de {{ $mosaic->original_name }}" loading="lazy">
                                            <span class="preview-badge">Vista previa</span>
                                        </div>
                                    </a>
                                @else
                                    <div class="preview-box">
                                        <div class="preview-empty">
                                            Sin vista previa
                                            @if($mosaic->status === 'ready')
                                                <br>
                                                Disponible desde acciones
                                            @endif
                                        </div>
                                    </div>
                                @endif
                            </div>

                            <div>
                                <div class="mosaic-title">{{ $mosaic->original_name }}</div>
                                <div class="mosaic-project">{{ $mosaic->project?->name ?? 'Sin proyecto' }}</div>
                                <div class="mosaic-filemeta">
                                    <span class="micro-tag">{{ strtoupper($mosaic->extension ?? 'tif') }}</span>
                                    @if($mosaic->dtype)
                                        <span class="micro-tag">{{ $mosaic->dtype }}</span>
                                    @endif
                                    @if($mosaic->bands)
                                        <span class="micro-tag">{{ $mosaic->bands }} banda{{ $mosaic->bands == 1 ? '' : 's' }}</span>
                                    @endif
                                    @if($mosaic->size_bytes)
                                        <span class="micro-tag">{{ number_format($mosaic->size_bytes / 1024 / 1024, 2) }} MiB</span>
                                    @endif
                                </div>
                            </div>

                            <div class="specs">
                                <div>
                                    <div class="spec-label">GSD</div>
                                    <div class="spec-value">{{ $mosaic->gsd_cm !== null ? number_format($mosaic->gsd_cm, 3) . ' cm/px' : '—' }}</div>
                                </div>
                                <div>
                                    <div class="spec-label">CRS</div>
                                    <div class="spec-value mono">{{ $mosaic->crs ?? '—' }}</div>
                                </div>
                                <div>
                                    <div class="spec-label">Resolución</div>
                                    <div class="spec-value">
                                        @if($mosaic->width && $mosaic->height)
                                            {{ number_format($mosaic->width) }} × {{ number_format($mosaic->height) }} px
                                        @else
                                            —
                                        @endif
                                    </div>
                                </div>
                                <div>
                                    <div class="spec-label">Cuadrícula</div>
                                    <div class="spec-value">
                                        @if($hasTilingPreview)
                                            {{ data_get($tilingPreviewMetadata, 'grid.columns') ?? '—' }} × {{ data_get($tilingPreviewMetadata, 'grid.rows') ?? '—' }}
                                        @else
                                            —
                                        @endif
                                    </div>
                                </div>
                            </div>

                            <div class="status-stack">
                                <span class="badge {{ $badgeClass }}">{{ $statusText }}</span>
                                <div class="status-next">{{ $nextActionText }}</div>
                            </div>

                            <div class="qa-block">
                                <div class="qa-head">
                                    <span class="qa-title">QA/QC técnico</span>
                                    <span class="qa-score">{{ $qaPassed }}/{{ $qaTotal }}</span>
                                </div>
                                <div class="qa-bar"><div class="qa-fill" style="width: {{ $qaPercent }}%"></div></div>
                                <div class="qa-note">Disponibilidad de metadatos y productos técnicos; no representa validación científica.</div>
                            </div>

                            <div class="row-actions">
                                @if(in_array($mosaic->status, ['uploaded', 'failed']))
                                    <form method="POST" action="{{ route('mosaics.inspect', $mosaic->uuid) }}">
                                        @csrf
                                        <button type="submit" class="btn btn-primary btn-small">Inspeccionar</button>
                                    </form>
                                @elseif($mosaic->status === 'ready')
                                    @if(\Illuminate\Support\Facades\Route::has('analysis.index'))
                                        <a href="{{ route('analysis.index', ['mosaic' => $mosaic->uuid]) }}" class="btn btn-primary btn-small">Analizar con IA</a>
                                    @endif
                                @else
                                    <span class="status-next">En proceso...</span>
                                @endif

                                <div class="action-menu">
                                    <button type="button" class="btn btn-outline btn-small menu-toggle" data-menu-toggle aria-label="Más acciones">•••</button>
                                    <div class="menu-panel">
                                        @if($mosaic->status === 'ready')
                                            @if(!$hasPreview && \Illuminate\Support\Facades\Route::has('mosaics.preview.generate'))
                                                <form method="POST" action="{{ route('mosaics.preview.generate', $mosaic->uuid) }}">
                                                    @csrf
                                                    <button type="submit" class="menu-item">Generar vista previa</button>
                                                </form>
                                            @elseif($hasPreview && \Illuminate\Support\Facades\Route::has('mosaics.preview'))
                                                <a href="{{ route('mosaics.preview', $mosaic->uuid) }}" target="_blank" rel="noopener" class="menu-item">Abrir vista previa</a>
                                            @endif

                                            @if(!$hasTilingPreview && \Illuminate\Support\Facades\Route::has('mosaics.tiling.generate'))
                                                <form method="POST" action="{{ route('mosaics.tiling.generate', $mosaic->uuid) }}">
                                                    @csrf
                                                    <button type="submit" class="menu-item">Generar QA/QC de tiling</button>
                                                </form>
                                            @elseif($hasTilingPreview && \Illuminate\Support\Facades\Route::has('mosaics.tiling.preview'))
                                                <a href="{{ route('mosaics.tiling.preview', ['mosaic' => $mosaic->uuid, 'type' => 'footprints']) }}" target="_blank" rel="noopener" class="menu-item">Ver cuadrícula de inferencia</a>
                                                <a href="{{ route('mosaics.tiling.preview', ['mosaic' => $mosaic->uuid, 'type' => 'coverage']) }}" target="_blank" rel="noopener" class="menu-item">Ver mapa de cobertura</a>
                                            @endif
                                        @endif
                                        <button type="button" class="menu-item" data-details-toggle="details-{{ $mosaic->uuid }}">Detalles técnicos</button>
                                    </div>
                                </div>
                            </div>
                        </div>

                        <div id="details-{{ $mosaic->uuid }}" class="technical-panel">
                            <div class="technical-inner">
                                <div class="technical-header">
                                    <div>
                                        <div class="technical-title">Inspección geoespacial</div>
                                        <div class="technical-sub">Metadatos del raster y evidencias QA/QC disponibles para este insumo.</div>
                                    </div>
                                    <button type="button" class="btn btn-ghost btn-small" data-close-details="details-{{ $mosaic->uuid }}">Cerrar</button>
                                </div>

                                <div class="technical-grid">
                                    <div class="technical-card">
                                        <div class="technical-card-title">Raster</div>
                                        <div class="detail-line"><span>Resolución</span><strong>{{ $mosaic->width && $mosaic->height ? number_format($mosaic->width) . ' × ' . number_format($mosaic->height) . ' px' : '—' }}</strong></div>
                                        <div class="detail-line"><span>Bandas</span><strong>{{ $mosaic->bands ?? '—' }}</strong></div>
                                        <div class="detail-line"><span>Tipo de dato</span><strong>{{ $mosaic->dtype ?? '—' }}</strong></div>
                                        <div class="detail-line"><span>Compresión</span><strong>{{ data_get($mosaic->metadata, 'compression') ?? '—' }}</strong></div>
                                        <div class="detail-line"><span>Alpha</span><strong>{{ data_get($mosaic->metadata, 'has_alpha') ? 'Sí' : 'No' }}</strong></div>
                                    </div>

                                    <div class="technical-card">
                                        <div class="technical-card-title">Referencia espacial</div>
                                        <div class="detail-line"><span>CRS</span><strong class="mono">{{ $mosaic->crs ?? '—' }}</strong></div>
                                        <div class="detail-line"><span>GSD</span><strong>{{ $mosaic->gsd_cm !== null ? number_format($mosaic->gsd_cm, 3) . ' cm/px' : '—' }}</strong></div>
                                        <div class="detail-line"><span>Píxel X</span><strong>{{ $mosaic->pixel_size_x ?? '—' }} m</strong></div>
                                        <div class="detail-line"><span>Píxel Y</span><strong>{{ $mosaic->pixel_size_y ?? '—' }} m</strong></div>
                                        <div class="detail-line"><span>Proyectado</span><strong>{{ data_get($mosaic->metadata, 'is_projected') ? 'Sí' : 'No' }}</strong></div>
                                    </div>

                                    <div class="technical-card">
                                        <div class="technical-card-title">QA/QC de tiling</div>
                                        <div class="detail-line"><span>Cuadrícula</span><strong>{{ $hasTilingPreview ? (data_get($tilingPreviewMetadata, 'grid.columns') ?? '—') . ' × ' . (data_get($tilingPreviewMetadata, 'grid.rows') ?? '—') : '—' }}</strong></div>
                                        <div class="detail-line"><span>Total tiles</span><strong>{{ data_get($tilingPreviewMetadata, 'summary.total_tiles') ?? '—' }}</strong></div>
                                        <div class="detail-line"><span>Tile</span><strong>{{ data_get($tilingPreviewMetadata, 'configuration.tile_size') ?? '—' }} px</strong></div>
                                        <div class="detail-line"><span>Overlap solicitado</span><strong>{{ data_get($tilingPreviewMetadata, 'configuration.requested_overlap') ?? '—' }} px</strong></div>
                                        <div class="detail-line"><span>Overlap real X</span><strong>{{ data_get($tilingPreviewMetadata, 'actual_overlap.x_min_px') ?? '—' }}–{{ data_get($tilingPreviewMetadata, 'actual_overlap.x_max_px') ?? '—' }} px</strong></div>
                                        <div class="detail-line"><span>Overlap real Y</span><strong>{{ data_get($tilingPreviewMetadata, 'actual_overlap.y_min_px') ?? '—' }}–{{ data_get($tilingPreviewMetadata, 'actual_overlap.y_max_px') ?? '—' }} px</strong></div>
                                    </div>

                                    <div class="technical-card">
                                        <div class="technical-card-title">Cobertura e inspección</div>
                                        <div class="detail-line"><span>Validez media</span><strong>{{ $meanValidFraction !== null ? number_format($meanValidFraction * 100, 1) . ' %' : '—' }}</strong></div>
                                        <div class="detail-line"><span>Cobertura mínima</span><strong>{{ data_get($tilingPreviewMetadata, 'coverage.min_valid') ?? '—' }}×</strong></div>
                                        <div class="detail-line"><span>Cobertura máxima</span><strong>{{ data_get($tilingPreviewMetadata, 'coverage.max_valid') ?? '—' }}×</strong></div>
                                        <div class="detail-line"><span>Píxeles válidos sin cubrir</span><strong>{{ $uncoveredValidPixels ?? '—' }}</strong></div>
                                        <div class="detail-line"><span>Vista previa</span><strong>{{ $hasPreview ? 'Disponible' : 'No disponible' }}</strong></div>
                                        <div class="detail-line"><span>Resolución preview</span><strong>{{ $hasPreview ? ((data_get($previewMetadata, 'width') ?? '—') . ' × ' . (data_get($previewMetadata, 'height') ?? '—') . ' px') : '—' }}</strong></div>
                                        <div class="detail-line"><span>Calidad JPEG</span><strong>{{ $hasPreview ? (data_get($previewMetadata, 'jpeg_quality') ?? '—') : '—' }}</strong></div>
                                        <div class="detail-line"><span>Inspeccionado</span><strong>{{ $mosaic->inspected_at ? $mosaic->inspected_at->format('d/m/Y H:i') : '—' }}</strong></div>
                                    </div>
                                </div>

                                @if($mosaic->checksum_sha256)
                                    <div class="hash-line">
                                        <div class="hash-value mono" title="{{ $mosaic->checksum_sha256 }}">SHA-256 · {{ $mosaic->checksum_sha256 }}</div>
                                        <button type="button" class="btn btn-outline btn-small" data-copy="{{ $mosaic->checksum_sha256 }}">Copiar hash</button>
                                    </div>
                                @endif
                            </div>
                        </div>
                    </article>
                @endforeach
            </div>

            <div id="noResults" class="no-results">No hay ortomosaicos que coincidan con la búsqueda o el filtro seleccionado.</div>
        @endif
    </section>

    <section class="card section-card">
        <div class="section-header">
            <div>
                <div class="section-kicker">Trazabilidad del insumo</div>
                <h2 class="section-title">Integridad geoespacial</h2>
                <div class="section-desc">Separación explícita entre fuente científica, productos de control y etapa de inferencia.</div>
            </div>
        </div>
        <div class="section-body">
            <div class="integrity-grid">
                <div class="integrity-step">
                    <div class="integrity-index">01 · FUENTE</div>
                    <div class="integrity-title">GeoTIFF original</div>
                    <div class="integrity-copy">Raster georreferenciado conservado sin modificaciones como insumo principal, junto con CRS, GSD, transformación espacial y huella SHA-256.</div>
                </div>
                <div class="integrity-step">
                    <div class="integrity-index">02 · QA/QC</div>
                    <div class="integrity-title">Productos de inspección</div>
                    <div class="integrity-copy">Vista JPG, footprints y mapa de cobertura permiten revisar visualmente el raster y comprobar la distribución de las ventanas de inferencia.</div>
                </div>
                <div class="integrity-step">
                    <div class="integrity-index">03 · ANÁLISIS</div>
                    <div class="integrity-title">Inferencia IA</div>
                    <div class="integrity-copy">YOLO-Seg y Mask R-CNN operan posteriormente sobre el flujo definido para el análisis, sin convertir los productos QA/QC en evidencia de exactitud.</div>
                </div>
            </div>
            <div class="method-note">
                <strong>Alcance del QA/QC:</strong> estas comprobaciones describen preparación técnica y cobertura del insumo. No constituyen validación científica del ortomosaico ni confianza de los modelos de IA. La correspondencia de los objetos detectados con árboles reales requiere contraste posterior con verdad de campo.
            </div>
        </div>
    </section>
</main>

<script>
    const csrf = document.querySelector('meta[name="csrf-token"]')?.content;
    const button = document.getElementById('uploadButton');
    const fileInput = document.getElementById('mosaicFile');
    const project = document.getElementById('project');
    const progress = document.getElementById('uploadProgress');
    const progressWrapper = document.getElementById('progressWrapper');
    const progressLabel = document.getElementById('progressLabel');
    const progressPercent = document.getElementById('progressPercent');
    const progressBytes = document.getElementById('progressBytes');
    const progressState = document.getElementById('progressState');
    const statusBox = document.getElementById('uploadStatus');
    const dropzone = document.getElementById('dropzone');
    const selectedFileCard = document.getElementById('selectedFileCard');
    const selectedFileName = document.getElementById('selectedFileName');
    const selectedFileSize = document.getElementById('selectedFileSize');
    const stagePrepare = document.getElementById('stagePrepare');
    const stageTransfer = document.getElementById('stageTransfer');
    const stageVerify = document.getElementById('stageVerify');

    function setSelectedFile(file) {
        if (!file) {
            selectedFileCard?.classList.remove('visible');
            if (selectedFileName) selectedFileName.textContent = '—';
            if (selectedFileSize) selectedFileSize.textContent = '—';
            return;
        }
        selectedFileCard?.classList.add('visible');
        if (selectedFileName) selectedFileName.textContent = file.name;
        if (selectedFileSize) selectedFileSize.textContent = formatBytes(file.size);
    }

    fileInput?.addEventListener('change', () => setSelectedFile(fileInput.files?.[0]));

    if (dropzone && fileInput) {
        ['dragenter', 'dragover'].forEach(eventName => {
            dropzone.addEventListener(eventName, event => {
                event.preventDefault();
                dropzone.classList.add('dragging');
            });
        });
        ['dragleave', 'drop'].forEach(eventName => {
            dropzone.addEventListener(eventName, event => {
                event.preventDefault();
                dropzone.classList.remove('dragging');
            });
        });
        dropzone.addEventListener('drop', event => {
            const files = event.dataTransfer?.files;
            if (!files?.length) return;
            const transfer = new DataTransfer();
            transfer.items.add(files[0]);
            fileInput.files = transfer.files;
            setSelectedFile(files[0]);
        });
    }

    button?.addEventListener('click', async () => {
        if (!fileInput?.files?.length) {
            showUploadMessage('Selecciona un archivo .tif o .tiff.', 'error');
            return;
        }
        if (!project?.value) {
            showUploadMessage('Selecciona un proyecto.', 'error');
            return;
        }

        const file = fileInput.files[0];
        const extension = file.name.split('.').pop().toLowerCase();
        if (!['tif', 'tiff'].includes(extension)) {
            showUploadMessage('El archivo debe ser .tif o .tiff.', 'error');
            return;
        }

        try {
            button.disabled = true;
            if (progressWrapper) progressWrapper.hidden = false;
            setStage('prepare');
            updateProgress(0, 'Preparando transferencia...', 0, file.size, 'Preparación');

            const presignResponse = await fetch('{{ route('mosaics.presign') }}', {
                method: 'POST',
                headers: {
                    'Content-Type': 'application/json',
                    'X-CSRF-TOKEN': csrf,
                    'Accept': 'application/json'
                },
                body: JSON.stringify({
                    project_id: project.value,
                    filename: file.name,
                    size_bytes: file.size,
                    mime_type: file.type || 'image/tiff'
                })
            });

            const presign = await presignResponse.json();
            if (!presignResponse.ok) {
                throw new Error(
                    presign.message
                    || (presign.errors ? Object.values(presign.errors).flat().join(' ') : null)
                    || 'No se pudo preparar la subida.'
                );
            }

            setStage('transfer');
            updateProgress(0, 'Transfiriendo ortomosaico...', 0, file.size, 'Transferencia');
            await uploadToR2(file, presign.upload_url, presign.headers);

            setStage('verify');
            updateProgress(100, 'Verificando archivo almacenado...', file.size, file.size, 'Verificación');

            const completeResponse = await fetch('{{ route('mosaics.complete') }}', {
                method: 'POST',
                headers: {
                    'Content-Type': 'application/json',
                    'X-CSRF-TOKEN': csrf,
                    'Accept': 'application/json'
                },
                body: JSON.stringify({ mosaic_uuid: presign.mosaic_uuid })
            });

            const complete = await completeResponse.json();
            if (!completeResponse.ok) {
                throw new Error(complete.message || 'No se pudo confirmar la subida.');
            }

            setStage('done');
            updateProgress(100, 'Ortomosaico almacenado correctamente.', file.size, file.size, 'Completado');
            if (statusBox) statusBox.style.color = '#177245';

            setTimeout(() => window.location.reload(), 900);
        } catch (error) {
            console.error(error);
            showUploadMessage(error.message, 'error');
        } finally {
            button.disabled = false;
        }
    });

    function uploadToR2(file, url, signedHeaders) {
        return new Promise((resolve, reject) => {
            const xhr = new XMLHttpRequest();
            xhr.open('PUT', url, true);

            if (signedHeaders) {
                Object.entries(signedHeaders).forEach(([key, value]) => {
                    if (key.toLowerCase() !== 'host') xhr.setRequestHeader(key, value);
                });
            }

            xhr.upload.onprogress = event => {
                if (!event.lengthComputable) return;
                const percent = Math.round((event.loaded / event.total) * 100);
                updateProgress(percent, 'Transfiriendo ortomosaico...', event.loaded, event.total, 'Transferencia');
            };

            xhr.onload = () => {
                if (xhr.status >= 200 && xhr.status < 300) resolve();
                else reject(new Error('El almacenamiento respondió HTTP ' + xhr.status));
            };

            xhr.onerror = () => reject(new Error('Error de red o CORS durante la subida.'));
            xhr.onabort = () => reject(new Error('La subida fue cancelada.'));
            xhr.send(file);
        });
    }

    function updateProgress(percent, text, loaded = 0, total = 0, state = '') {
        if (progress) progress.value = percent;
        if (progressPercent) progressPercent.textContent = percent + ' %';
        if (progressLabel) progressLabel.textContent = text;
        if (progressBytes) progressBytes.textContent = formatBytes(loaded) + ' / ' + formatBytes(total);
        if (progressState) progressState.textContent = state;
        if (statusBox) {
            statusBox.textContent = text;
            statusBox.style.color = '#176b4d';
        }
    }

    function setStage(stage) {
        [stagePrepare, stageTransfer, stageVerify].forEach(chip => chip?.classList.remove('active', 'done'));
        if (stage === 'prepare') stagePrepare?.classList.add('active');
        if (stage === 'transfer') {
            stagePrepare?.classList.add('done');
            stageTransfer?.classList.add('active');
        }
        if (stage === 'verify') {
            stagePrepare?.classList.add('done');
            stageTransfer?.classList.add('done');
            stageVerify?.classList.add('active');
        }
        if (stage === 'done') {
            stagePrepare?.classList.add('done');
            stageTransfer?.classList.add('done');
            stageVerify?.classList.add('done');
        }
    }

    function showUploadMessage(message, type = 'info') {
        if (progressWrapper) progressWrapper.hidden = false;
        if (statusBox) {
            statusBox.textContent = message;
            statusBox.style.color = type === 'error' ? '#b42318' : '#176b4d';
        }
    }

    function formatBytes(bytes) {
        if (!bytes) return '0 B';
        const units = ['B', 'KiB', 'MiB', 'GiB', 'TiB'];
        const index = Math.min(Math.floor(Math.log(bytes) / Math.log(1024)), units.length - 1);
        const value = bytes / Math.pow(1024, index);
        return value.toFixed(index === 0 ? 0 : 2) + ' ' + units[index];
    }

    /* Explorer filters */
    const searchInput = document.getElementById('mosaicSearch');
    const filterButtons = document.querySelectorAll('[data-filter]');
    const rows = Array.from(document.querySelectorAll('[data-mosaic-row]'));
    const resultsCount = document.getElementById('resultsCount');
    const noResults = document.getElementById('noResults');
    let currentFilter = 'all';

    function applyExplorerFilters() {
        const query = (searchInput?.value || '').trim().toLowerCase();
        let visible = 0;
        rows.forEach(row => {
            const matchesQuery = !query || (row.dataset.search || '').includes(query);
            const matchesStatus = currentFilter === 'all' || row.dataset.status === currentFilter;
            const show = matchesQuery && matchesStatus;
            row.hidden = !show;
            if (show) visible++;
        });
        if (resultsCount) resultsCount.textContent = `Mostrando ${visible} de ${rows.length} ortomosaicos`;
        if (noResults) noResults.style.display = visible === 0 ? 'block' : 'none';
    }

    searchInput?.addEventListener('input', applyExplorerFilters);
    filterButtons.forEach(buttonFilter => {
        buttonFilter.addEventListener('click', () => {
            currentFilter = buttonFilter.dataset.filter;
            filterButtons.forEach(item => item.classList.remove('active'));
            buttonFilter.classList.add('active');
            applyExplorerFilters();
        });
    });

    /* Action menus */
    document.querySelectorAll('[data-menu-toggle]').forEach(toggle => {
        toggle.addEventListener('click', event => {
            event.stopPropagation();
            const menu = toggle.closest('.action-menu');
            document.querySelectorAll('.action-menu.open').forEach(openMenu => {
                if (openMenu !== menu) openMenu.classList.remove('open');
            });
            menu?.classList.toggle('open');
        });
    });
    document.addEventListener('click', () => {
        document.querySelectorAll('.action-menu.open').forEach(menu => menu.classList.remove('open'));
    });
    document.querySelectorAll('.menu-panel').forEach(panel => panel.addEventListener('click', event => event.stopPropagation()));

    /* Technical panels */
    document.querySelectorAll('[data-details-toggle]').forEach(buttonDetails => {
        buttonDetails.addEventListener('click', () => {
            const panel = document.getElementById(buttonDetails.dataset.detailsToggle);
            panel?.classList.toggle('open');
            buttonDetails.closest('.action-menu')?.classList.remove('open');
            if (panel?.classList.contains('open')) panel.scrollIntoView({ behavior: 'smooth', block: 'nearest' });
        });
    });
    document.querySelectorAll('[data-close-details]').forEach(closeButton => {
        closeButton.addEventListener('click', () => {
            document.getElementById(closeButton.dataset.closeDetails)?.classList.remove('open');
        });
    });

    /* Copy checksum */
    document.querySelectorAll('[data-copy]').forEach(copyButton => {
        copyButton.addEventListener('click', async () => {
            try {
                await navigator.clipboard.writeText(copyButton.dataset.copy);
                const original = copyButton.textContent;
                copyButton.textContent = 'Copiado';
                setTimeout(() => copyButton.textContent = original, 1200);
            } catch (error) {
                console.error(error);
            }
        });
    });
</script>
</body>
</html>
