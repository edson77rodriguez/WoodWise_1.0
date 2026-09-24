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

    <title>UAV Forest AI · Centro de análisis IA</title>


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



        /* ======================================================================
         * UAV FOREST AI — SCIENTIFIC WORKSPACE REDESIGN
         * Visual-only layer. Keeps the existing backend and scientific logic.
         * ====================================================================== */

        :root {
            --ink: #102019;
            --ink-soft: #244137;
            --canvas: #eef3f0;
            --panel: rgba(255,255,255,.94);
            --panel-strong: #ffffff;
            --line: #d9e4de;
            --line-strong: #c7d6ce;
            --forest-950: #082d21;
            --forest-900: #0d3d2d;
            --forest-800: #11523b;
            --forest-700: #176b4d;
            --forest-100: #e8f4ee;
            --forest-50: #f3f8f5;
            --violet: #7651b8;
            --violet-soft: #f2eef9;
            --amber: #a76712;
            --amber-soft: #fff6e7;
            --blue: #2a6fc4;
            --blue-soft: #edf5fd;
            --radius-sm: 10px;
            --radius-md: 14px;
            --radius-lg: 20px;
            --radius-xl: 26px;
            --shadow-soft: 0 8px 28px rgba(12, 45, 32, .055);
            --shadow-float: 0 18px 45px rgba(8, 45, 33, .12);
        }

        html { scroll-behavior: smooth; }

        body {
            background:
                radial-gradient(circle at 10% -10%, rgba(23,107,77,.08), transparent 30%),
                radial-gradient(circle at 92% 0%, rgba(118,81,184,.045), transparent 24%),
                var(--canvas);
            color: var(--ink);
        }

        .topbar {
            position: sticky;
            top: 0;
            z-index: 50;
            padding: 0;
            background: rgba(8,45,33,.96);
            backdrop-filter: blur(14px);
            border-bottom: 1px solid rgba(255,255,255,.08);
            box-shadow: 0 8px 30px rgba(6,28,20,.16);
        }

        .topbar-inner {
            min-height: 72px;
            padding: 0 30px;
        }

        .brand-logo {
            width: 42px;
            height: 42px;
            border-radius: 12px;
            background: rgba(255,255,255,.08);
            border: 1px solid rgba(255,255,255,.12);
            box-shadow: inset 0 1px 0 rgba(255,255,255,.08);
        }

        .brand-logo svg { width: 25px; height: 25px; display:block; }
        .brand-name { font-size: 17px; letter-spacing: -.01em; }
        .brand-subtitle { font-size: 11px; color: rgba(255,255,255,.66); opacity: 1; }

        .system-status {
            display: flex;
            align-items: center;
            gap: 10px;
        }

        .system-status-copy {
            display: flex;
            flex-direction: column;
            align-items: flex-end;
            line-height: 1.2;
        }

        .system-status-label { font-size: 11px; font-weight: 700; }
        .system-status-detail { margin-top: 3px; color: rgba(255,255,255,.58); font-size: 9px; }

        .api-status {
            padding: 8px 11px;
            background: rgba(255,255,255,.07);
            border: 1px solid rgba(255,255,255,.09);
        }

        main { max-width: 1500px; padding: 34px 32px 56px; }

        .page-hero {
            position: relative;
            overflow: hidden;
            margin-bottom: 22px;
            padding: 28px 30px;
            border: 1px solid rgba(23,107,77,.14);
            border-radius: var(--radius-xl);
            background:
                linear-gradient(115deg, rgba(255,255,255,.98), rgba(247,251,249,.95)),
                white;
            box-shadow: var(--shadow-soft);
        }

        .page-hero::after {
            content: '';
            position: absolute;
            right: -90px;
            top: -120px;
            width: 290px;
            height: 290px;
            border-radius: 50%;
            border: 46px solid rgba(23,107,77,.045);
            pointer-events: none;
        }

        .hero-kicker {
            display: inline-flex;
            align-items: center;
            gap: 7px;
            margin-bottom: 10px;
            color: var(--forest-700);
            font-size: 10px;
            font-weight: 800;
            letter-spacing: .12em;
            text-transform: uppercase;
        }

        .hero-kicker-dot { width: 7px; height: 7px; border-radius: 50%; background: var(--forest-700); }
        .page-hero h1 { margin: 0; font-size: clamp(26px, 3vw, 38px); letter-spacing: -.035em; }
        .page-hero p { max-width: 980px; margin: 10px 0 0; color: #5c6f66; font-size: 14px; line-height: 1.65; }

        .scope-note {
            display: flex;
            align-items: flex-start;
            gap: 11px;
            margin-top: 18px;
            max-width: 1000px;
            padding: 12px 14px;
            border: 1px solid #d7e5de;
            border-radius: 12px;
            background: #f5faf7;
            color: #486258;
            font-size: 11px;
            line-height: 1.55;
        }

        .scope-note-icon {
            flex: 0 0 auto;
            width: 23px;
            height: 23px;
            display: grid;
            place-items: center;
            border-radius: 7px;
            background: var(--forest-100);
            color: var(--forest-700);
            font-weight: 800;
        }

        .mode-switcher {
            display: grid;
            grid-template-columns: repeat(2,minmax(0,1fr));
            gap: 10px;
            margin-bottom: 24px;
            padding: 7px;
            border: 1px solid var(--line);
            border-radius: 16px;
            background: rgba(255,255,255,.76);
            box-shadow: 0 4px 16px rgba(18,49,36,.035);
        }

        .mode-tab {
            appearance: none;
            border: 1px solid transparent;
            border-radius: 11px;
            padding: 13px 16px;
            background: transparent;
            color: #5d6d65;
            cursor: pointer;
            text-align: left;
            transition: .18s ease;
        }

        .mode-tab strong { display: block; color: inherit; font-size: 13px; }
        .mode-tab span { display: block; margin-top: 3px; font-size: 10px; opacity: .78; }
        .mode-tab:hover { background: #f6f9f7; color: var(--forest-800); }
        .mode-tab.is-active {
            background: var(--forest-950);
            color: white;
            box-shadow: 0 8px 18px rgba(8,45,33,.14);
        }

        .analysis-mode-panel { display: none; }
        .analysis-mode-panel.is-active { display: block; animation: workspaceIn .22s ease; }
        @keyframes workspaceIn { from { opacity:0; transform: translateY(4px);} to {opacity:1; transform:none;} }

        .card {
            border: 1px solid var(--line);
            border-radius: var(--radius-lg);
            box-shadow: var(--shadow-soft);
        }

        .analysis-grid { grid-template-columns: 365px minmax(0,1fr); gap: 20px; }
        .settings { top: 92px; padding: 22px; }

        .section-eyebrow,
        .workspace-eyebrow {
            margin-bottom: 7px;
            color: var(--forest-700);
            font-size: 9px;
            font-weight: 800;
            letter-spacing: .11em;
            text-transform: uppercase;
        }

        .section-title { font-size: 16px; letter-spacing: -.015em; }
        .section-description { margin: 6px 0 20px; font-size: 11px; line-height: 1.6; }

        .upload {
            padding: 18px;
            border-width: 1px;
            border-style: dashed;
            background: #f7faf8;
        }
        .upload:hover { transform: translateY(-1px); box-shadow: 0 8px 18px rgba(20,70,45,.05); }
        .upload-icon { display:none; }

        .upload-symbol {
            width: 40px;
            height: 40px;
            margin: 0 auto 10px;
            display: grid;
            place-items:center;
            border-radius: 12px;
            background: var(--forest-100);
            color: var(--forest-700);
        }
        .upload-symbol svg { width: 21px; height: 21px; }
        .upload-name { margin-top: 0; }

        .model-choice-label { margin-bottom: 9px; }
        .model-select-native { position:absolute !important; width:1px !important; height:1px !important; opacity:0 !important; pointer-events:none !important; }

        .model-cards { display: grid; gap: 8px; }
        .model-card {
            width: 100%;
            padding: 12px 13px;
            border: 1px solid var(--line);
            border-radius: 12px;
            background: #fff;
            cursor: pointer;
            text-align: left;
            transition: .18s ease;
        }
        .model-card:hover { border-color: #b9cec3; background: #fbfdfc; }
        .model-card.is-active {
            border-color: rgba(23,107,77,.46);
            background: var(--forest-50);
            box-shadow: 0 0 0 3px rgba(23,107,77,.06);
        }
        .model-card-top { display:flex; align-items:center; justify-content:space-between; gap:10px; }
        .model-card-name { font-size: 12px; font-weight: 800; color: var(--ink); }
        .model-card-code { padding: 3px 6px; border-radius:6px; background:#f0f4f2; color:#65766d; font-size:8px; font-weight:800; }
        .model-card-desc { margin-top: 4px; color:#6f7e76; font-size:9px; line-height:1.45; }
        .model-card-meta { margin-top:8px; display:flex; gap:6px; flex-wrap:wrap; }
        .mini-chip { padding:4px 7px; border-radius:999px; background:#f0f5f2; color:#587066; font-size:8px; font-weight:700; }

        .protocol-chip {
            display:inline-flex;
            align-items:center;
            gap:5px;
            margin-top:7px;
            color: var(--forest-700);
            font-size:9px;
            font-weight:700;
        }

        .compare-options {
            padding: 14px;
            border-color: #ded5ec;
            background: #faf8fd;
        }

        .button {
            min-height: 44px;
            border-radius: 11px;
            background: var(--forest-950);
            box-shadow: 0 8px 18px rgba(8,45,33,.13);
        }
        .button:hover { background: var(--forest-800); box-shadow: 0 11px 22px rgba(8,45,33,.16); }

        .science-note {
            padding: 13px 14px;
            border: 1px solid #d8e5df;
            background: #f5faf7;
            color: #4c6459;
        }

        .science-note-title { display:flex; align-items:center; gap:7px; margin-bottom:6px; color:var(--forest-800); font-weight:800; }

        .workspace { border-radius: var(--radius-lg); }
        .workspace-header { min-height: 72px; padding: 16px 18px; background: #fbfdfc; }
        .workspace-title { font-size: 14px; }
        .workspace-description { font-size: 10px; }

        .viewer {
            min-height: 560px;
            background:
                linear-gradient(rgba(255,255,255,.018) 1px, transparent 1px),
                linear-gradient(90deg, rgba(255,255,255,.018) 1px, transparent 1px),
                #101815;
            background-size: 32px 32px;
        }

        .viewer-placeholder { color:rgba(255,255,255,.58); }
        .placeholder-icon { display:none; }
        .viewer-empty-symbol {
            width:62px; height:62px; margin:0 auto 16px; display:grid; place-items:center;
            border:1px solid rgba(255,255,255,.12); border-radius:18px; background:rgba(255,255,255,.045);
        }
        .viewer-empty-symbol svg { width:29px; height:29px; opacity:.75; }

        .viewer-controls { gap: 7px; }
        .viewer-controls label {
            gap:6px; padding:6px 8px; border:1px solid var(--line); border-radius:8px; background:white; font-size:9px;
        }

        .legend { align-items:center; padding: 10px 18px; font-size: 10px; }
        .legend-title { margin-right:4px; color:#819087; font-size:9px; font-weight:800; text-transform:uppercase; letter-spacing:.08em; }

        .metrics { grid-template-columns: repeat(5,minmax(0,1fr)); gap: 8px; padding: 14px 18px; }
        .metric { padding: 11px 12px; border-radius: 10px; box-shadow:none; }
        .metric-name { font-size: 9px; }
        .metric-value { font-size: 18px; }

        .detail-card { padding: 20px; }
        table { font-size: 11px; }
        th { background:#fafcfb; font-size:9px; }

        .wall-execution,
        .wall-analysis { margin-top: 0; }

        .wall-execution { padding: 0; overflow:hidden; }
        .wall-execution-header { margin:0; padding:22px 24px 18px; border-bottom:1px solid var(--line); background:#fbfdfc; }
        .wall-execution-title,
        .wall-analysis-title { font-size:18px; letter-spacing:-.02em; }
        .wall-execution-subtitle,
        .wall-analysis-subtitle { font-size:11px; }

        .orthomosaic-workspace { padding: 22px 24px 24px; }
        .wall-selector { margin-bottom: 16px; }
        .wall-mosaic-info { gap:8px; }
        .wall-mosaic-item { padding:12px; border-radius:10px; background:#fafcfb; }
        .wall-mosaic-label { font-size:8px; }
        .wall-mosaic-value { font-size:12px; }

        .protocol-panel {
            margin: 18px 0;
            overflow:hidden;
            border:1px solid #d4e3db;
            border-radius:14px;
            background:#f7fbf8;
        }
        .protocol-panel-head {
            display:flex; align-items:flex-start; justify-content:space-between; gap:15px;
            padding:14px 16px; border-bottom:1px solid #dfebe5;
        }
        .protocol-panel-title { font-size:12px; font-weight:800; }
        .protocol-panel-copy { margin-top:3px; color:#6b7d74; font-size:9px; }
        .lock-badge { display:inline-flex; align-items:center; gap:5px; padding:5px 8px; border-radius:999px; background:#e6f3ec; color:#2f684f; font-size:8px; font-weight:800; white-space:nowrap; }
        .protocol-grid { display:grid; grid-template-columns:repeat(4,minmax(0,1fr)); }
        .protocol-item { padding:12px 14px; border-right:1px solid #e3ebe7; border-bottom:1px solid #e3ebe7; }
        .protocol-item:nth-child(4n) { border-right:0; }
        .protocol-key { color:#77877f; font-size:8px; text-transform:uppercase; letter-spacing:.05em; }
        .protocol-value { margin-top:4px; color:#203b30; font-size:11px; font-weight:800; }

        .run-zone {
            display:flex; align-items:center; justify-content:space-between; gap:18px; margin-top:18px;
            padding:15px 16px; border:1px solid var(--line); border-radius:14px; background:#fff;
        }
        .run-zone-copy strong { display:block; font-size:11px; }
        .run-zone-copy span { display:block; margin-top:4px; color:#75847c; font-size:9px; line-height:1.45; }
        .run-zone form { flex:0 0 330px; }

        .wall-analysis-header { padding:22px 24px; background:#fbfdfc; }

        .pipeline-trace { padding: 22px 24px 10px; }
        .trace-header { display:flex; align-items:end; justify-content:space-between; gap:15px; margin-bottom:15px; }
        .trace-title { font-size:13px; font-weight:800; }
        .trace-copy { margin-top:4px; color:#718078; font-size:9px; }
        .trace-flow { display:grid; grid-template-columns:1fr 34px 1fr 34px 1fr 34px 1fr; align-items:center; }
        .trace-node { min-height:104px; padding:15px; border:1px solid var(--line); border-radius:13px; background:#fff; }
        .trace-node.final { border-color:#bdd8ca; background:#f4faf6; }
        .trace-value { font-size:27px; font-weight:800; letter-spacing:-.04em; color:var(--ink); }
        .trace-node.final .trace-value { color:var(--forest-700); }
        .trace-label { margin-top:4px; font-size:10px; font-weight:800; }
        .trace-desc { margin-top:5px; color:#78877f; font-size:8px; line-height:1.4; }
        .trace-arrow { text-align:center; color:#9eaaa4; font-size:17px; }

        .evidence-dashboard { padding: 10px 24px 22px; }
        .evidence-grid { display:grid; grid-template-columns:repeat(3,minmax(0,1fr)); gap:10px; }
        .evidence-card { padding:15px; border:1px solid var(--line); border-radius:13px; background:#fff; }
        .evidence-card-label { color:#77867f; font-size:8px; font-weight:800; letter-spacing:.05em; text-transform:uppercase; }
        .evidence-card-value { margin-top:7px; font-size:24px; font-weight:800; letter-spacing:-.035em; }
        .evidence-card-meta { margin-top:4px; color:#73827a; font-size:9px; line-height:1.45; }
        .evidence-card.primary .evidence-card-value { color:var(--mask); }
        .evidence-card.secondary .evidence-card-value { color:var(--yolo); }
        .evidence-card.bilateral .evidence-card-value { color:var(--violet); }
        .evidence-card.review .evidence-card-value { color:var(--amber); }

        .interpretation-card {
            margin: 0 24px 18px;
            padding: 15px 16px;
            border:1px solid #ead6a6;
            border-radius:13px;
            background:var(--amber-soft);
            color:#7c571d;
            font-size:10px;
            line-height:1.6;
        }
        .interpretation-card strong { color:#65440f; }
        .interpretation-eq { display:inline-flex; align-items:center; margin:0 5px; padding:2px 6px; border-radius:5px; background:rgba(255,255,255,.68); font-weight:800; }

        .wall-method { margin:0 24px 14px; background:#f5faf7; border-color:#d8e8df; font-size:10px; }
        .wall-warning { display:none; }

        .artifact-actions { padding:4px 24px 18px; border-top:1px solid var(--line); padding-top:18px; }
        .artifact-button { border-radius:10px; }
        .artifact-button-primary { background:var(--forest-950); }
        .artifact-button-primary:hover { background:var(--forest-800); }

        .wall-analysis-meta { padding:0 24px 22px; font-size:9px; }

        .technical-divider { display:flex; align-items:center; gap:10px; width:100%; margin:5px 0 2px; color:#87948e; font-size:8px; font-weight:800; letter-spacing:.08em; text-transform:uppercase; }
        .technical-divider::after { content:''; flex:1; height:1px; background:var(--line); }

        .status-pill { display:inline-flex; align-items:center; gap:6px; padding:6px 9px; border-radius:999px; background:#eaf6ef; color:#286647; font-size:9px; font-weight:800; }
        .status-pill::before { content:''; width:6px; height:6px; border-radius:50%; background:#2eb875; box-shadow:0 0 0 3px rgba(46,184,117,.12); }

        @media(max-width:1100px) {
            .protocol-grid { grid-template-columns:repeat(2,minmax(0,1fr)); }
            .protocol-item:nth-child(4n) { border-right:1px solid #e3ebe7; }
            .protocol-item:nth-child(2n) { border-right:0; }
            .trace-flow { grid-template-columns:1fr; gap:7px; }
            .trace-arrow { transform:rotate(90deg); }
        }

        @media(max-width:800px) {
            .topbar-inner { padding:14px 18px; }
            .system-status-copy { display:none; }
            .page-hero { padding:22px 20px; }
            .mode-switcher { grid-template-columns:1fr; }
            .analysis-grid { grid-template-columns:1fr; }
            .settings { position:static; }
            .evidence-grid { grid-template-columns:repeat(2,minmax(0,1fr)); }
            .run-zone { flex-direction:column; align-items:stretch; }
            .run-zone form { flex:auto; }
        }

        @media(max-width:560px) {
            main { padding:20px 14px 40px; }
            .page-hero h1 { font-size:27px; }
            .protocol-grid, .evidence-grid { grid-template-columns:1fr; }
            .protocol-item, .protocol-item:nth-child(2n), .protocol-item:nth-child(4n) { border-right:0; }
            .wall-mosaic-info { grid-template-columns:1fr; }
            .metrics { grid-template-columns:repeat(2,minmax(0,1fr)); }
        }

    </style>

</head>


<body>


<header class="topbar">

    <div class="topbar-inner">

        <div class="brand">

            <div class="brand-logo" aria-hidden="true">
                <svg viewBox="0 0 32 32" fill="none" xmlns="http://www.w3.org/2000/svg">
                    <path d="M16 4L8.5 13H12L6.5 20H13V27H19V20H25.5L20 13H23.5L16 4Z" stroke="white" stroke-width="1.8" stroke-linejoin="round"/>
                    <path d="M5 25.5H27" stroke="white" stroke-width="1.8" stroke-linecap="round" opacity=".65"/>
                </svg>
            </div>

            <div>
                <div class="brand-name">UAV Forest AI</div>
                <div class="brand-subtitle">Inteligencia geoespacial para análisis forestal</div>
            </div>

        </div>

        <div class="system-status">
            <div class="system-status-copy">
                <div class="system-status-label">Motor de inferencia</div>
                <div class="system-status-detail">Servicio científico de IA</div>
            </div>

            <div class="api-status">
                @if(($apiStatus['status'] ?? 'offline') === 'ok')
                    <span class="api-dot online"></span>
                    Sistema disponible
                    @if(!empty($apiStatus['version']))
                        · v{{ $apiStatus['version'] }}
                    @endif
                @else
                    <span class="api-dot offline"></span>
                    Sistema no disponible
                @endif
            </div>
        </div>

    </div>

</header>


<main>


@php

    /*
    |--------------------------------------------------------------------------
    | Variables defensivas para la vista
    |--------------------------------------------------------------------------
    | El controlador debe suministrar estas variables. Este bloque evita
    | errores de renderizado si todavía no existe selección explícita.
    */

    $availableMosaics =
        $availableMosaics ?? collect();

    $selectedMosaicUuid =
        $selectedMosaicUuid
        ?? request()->query('mosaic');

    $selectedMosaic =
        $selectedMosaic ?? null;

    if (
        !$selectedMosaic
        &&
        $selectedMosaicUuid
    ) {

        $selectedMosaic =
            $availableMosaics->firstWhere(
                'uuid',
                $selectedMosaicUuid
            );
    }

    if (
        !$selectedMosaic
        &&
        $availableMosaics->isNotEmpty()
    ) {

        $selectedMosaic =
            $availableMosaics->first();

        $selectedMosaicUuid =
            $selectedMosaic->uuid;
    }

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


    $initialWorkspaceTab =
        (!$result && $wallToWallAnalysis)
            ? 'wall'
            : 'lab';

@endphp


<section class="page-hero">

    <div class="hero-kicker">
        <span class="hero-kicker-dot"></span>
        Laboratorio de visión artificial forestal
    </div>

    <h1>Centro de análisis IA</h1>

    <p>
        Segmenta candidatos de copa en imágenes UAV, compara la evidencia espacial
        producida por YOLO-Seg y Mask R-CNN y ejecuta el pipeline wall-to-wall
        sobre ortomosaicos completos, manteniendo trazabilidad metodológica de los resultados.
    </p>

    <div class="scope-note">
        <div class="scope-note-icon">i</div>
        <div>
            <strong>Alcance científico.</strong>
            Los resultados mostrados en esta interfaz representan candidatos u objetos operativos
            de copa generados por modelos de IA. No constituyen árboles confirmados ni validación
            respecto a verdad de campo.
        </div>
    </div>

</section>

<nav class="mode-switcher" aria-label="Modo de análisis">
    <button
        type="button"
        class="mode-tab {{ $initialWorkspaceTab === 'lab' ? 'is-active' : '' }}"
        data-workspace-tab="lab"
        aria-selected="{{ $initialWorkspaceTab === 'lab' ? 'true' : 'false' }}"
    >
        <strong>Laboratorio de inferencia</strong>
        <span>Imagen individual · comparación experimental · inspección visual</span>
    </button>

    <button
        type="button"
        class="mode-tab {{ $initialWorkspaceTab === 'wall' ? 'is-active' : '' }}"
        data-workspace-tab="wall"
        aria-selected="{{ $initialWorkspaceTab === 'wall' ? 'true' : 'false' }}"
    >
        <strong>Análisis de ortomosaico</strong>
        <span>Pipeline wall-to-wall · consolidación · resultados GIS persistentes</span>
    </button>
</nav>

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



<div id="workspace-lab" class="analysis-mode-panel {{ $initialWorkspaceTab === 'lab' ? 'is-active' : '' }}">

<div class="analysis-grid">


    {{-- ==========================================================
         CONFIGURACIÓN
         ========================================================== --}}

    <aside class="card settings">


        <div class="section-eyebrow">Experimento controlado</div>

        <h2 class="section-title">Configurar inferencia</h2>

        <p class="section-description">
            Selecciona una imagen UAV y el modelo que deseas inspeccionar.
            Los scores y parámetros mostrados deben interpretarse dentro del protocolo experimental.
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


                    <div class="upload-symbol" aria-hidden="true">
                        <svg viewBox="0 0 24 24" fill="none" xmlns="http://www.w3.org/2000/svg">
                            <path d="M12 16V4M12 4L8 8M12 4L16 8" stroke="currentColor" stroke-width="1.8" stroke-linecap="round" stroke-linejoin="round"/>
                            <path d="M5 14V18C5 19.1046 5.89543 20 7 20H17C18.1046 20 19 19.1046 19 18V14" stroke="currentColor" stroke-width="1.8" stroke-linecap="round"/>
                        </svg>
                    </div>

                    <div id="uploadName" class="upload-name">Seleccionar imagen UAV</div>


                    <div class="upload-small">

                        JPG · PNG · WEBP
                        <br>
                        máximo 10 MB

                    </div>

                </label>

            </div>


            {{-- Modelo --}}

            <div class="field">

                <label class="model-choice-label">Modelo / estrategia</label>

                <select id="modelSelect" name="model" required class="model-select-native">
                    <option value="yolo" {{ old('model', $analysisMode) === 'yolo' ? 'selected' : '' }}>YOLO-Seg E1.1</option>
                    <option value="maskrcnn" {{ old('model', $analysisMode) === 'maskrcnn' ? 'selected' : '' }}>Mask R-CNN M1.0</option>
                    <option value="both" {{ old('model', $analysisMode) === 'both' ? 'selected' : '' }}>Comparar ambos</option>
                </select>

                <div class="model-cards" id="modelCards">
                    <button type="button" class="model-card" data-model-value="yolo">
                        <div class="model-card-top">
                            <span class="model-card-name">YOLO-Seg</span>
                            <span class="model-card-code">E1.1</span>
                        </div>
                        <div class="model-card-desc">Segmentación rápida de candidatos de copa. Modelo corroborador y fallback dentro del pipeline wall-to-wall.</div>
                        <div class="model-card-meta">
                            <span class="mini-chip">Score base 0.25</span>
                            <span class="mini-chip">Segmentación</span>
                        </div>
                    </button>

                    <button type="button" class="model-card" data-model-value="maskrcnn">
                        <div class="model-card-top">
                            <span class="model-card-name">Mask R-CNN</span>
                            <span class="model-card-code">M1.0</span>
                        </div>
                        <div class="model-card-desc">Segmentación de instancias utilizada como geometría primaria provisional cuando existe candidato asociado.</div>
                        <div class="model-card-meta">
                            <span class="mini-chip">Score base 0.40</span>
                            <span class="mini-chip">Geometría primaria</span>
                        </div>
                    </button>

                    <button type="button" class="model-card" data-model-value="both">
                        <div class="model-card-top">
                            <span class="model-card-name">Comparación intermodelo</span>
                            <span class="model-card-code">YOLO + MRCNN</span>
                        </div>
                        <div class="model-card-desc">Ejecuta ambos modelos sobre la misma imagen para inspeccionar concordancia espacial y evidencia unilateral.</div>
                        <div class="model-card-meta">
                            <span class="mini-chip">IoU de máscaras</span>
                            <span class="mini-chip">QA experimental</span>
                        </div>
                    </button>
                </div>

                <div id="modelHelp" class="helper"></div>

            </div>


            {{-- Single threshold --}}

            <div
                id="singleThresholdGroup"
                class="field"
            >

                <label>
                    Score mínimo del modelo
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
                        Score mínimo YOLO
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
                        Score mínimo Mask R-CNN
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
            <div class="science-note-title">
                <span>ⓘ</span>
                Interpretación metodológica
            </div>
            El <strong>score del modelo</strong> es una salida interna de confianza y no debe
            interpretarse como probabilidad de que una detección sea correcta.
            <br><br>
            En comparación intermodelo, el <strong>IoU describe acuerdo espacial entre máscaras</strong>;
            no mide exactitud frente a verdad de campo.
        </div>

    </aside>



    {{-- ==========================================================
         WORKSPACE
         ========================================================== --}}

    <section>


        <div class="card workspace">


            <div class="workspace-header">


                <div>

                    <div class="workspace-eyebrow">Inspección visual</div>
                    <div class="workspace-title">Visor de inferencia</div>
                    <div class="workspace-description">Imagen UAV + geometrías propuestas por los modelos</div>

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

                        <div class="viewer-empty-symbol" aria-hidden="true">
                            <svg viewBox="0 0 24 24" fill="none" xmlns="http://www.w3.org/2000/svg">
                                <path d="M4 17L8.5 12L11 14.5L15 9.5L20 15" stroke="currentColor" stroke-width="1.5" stroke-linecap="round" stroke-linejoin="round"/>
                                <rect x="3" y="4" width="18" height="16" rx="3" stroke="currentColor" stroke-width="1.5"/>
                            </svg>
                        </div>
                        <strong style="display:block;color:#fff;margin-bottom:6px;">Área de inspección</strong>
                        Selecciona una imagen UAV para previsualizarla y ejecutar una inferencia.

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

                        <span class="legend-title">Leyenda</span>

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
                                        Score YOLO
                                    </th>

                                    <th>
                                        Score Mask
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
                                Score mínimo YOLO
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
                                Score mínimo Mask R-CNN
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
                                    Score del modelo
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


</div>

<div id="workspace-wall" class="analysis-mode-panel {{ $initialWorkspaceTab === 'wall' ? 'is-active' : '' }}">

{{-- ==========================================================
     EJECUCIÓN WALL-TO-WALL V0.7E
     ========================================================== --}}

<section class="card wall-execution">

    <div class="wall-execution-header">

        <div>

            <div class="section-eyebrow">Procesamiento espacial integral</div>
            <h2 class="wall-execution-title">Análisis de ortomosaico</h2>
            <div class="wall-execution-subtitle">
                Ejecuta el pipeline V0.7E sobre un ortomosaico completo con el protocolo científico V0.6G.
                La configuración metodológica permanece protegida durante la ejecución.
            </div>

        </div>


        @if(
            ($apiStatus['status'] ?? 'offline')
            === 'ok'
        )

            <div class="wall-api-badge online-state">
                ● Motor IA disponible
            </div>

        @else

            <div class="wall-api-badge offline-state">
                ● Motor IA no disponible
            </div>

        @endif

    </div>

    <div class="orthomosaic-workspace">

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

                        @if($mosaic->project)

                            · {{ $mosaic->project->name }}

                        @endif

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


            <div class="protocol-panel">

                <div class="protocol-panel-head">
                    <div>
                        <div class="protocol-panel-title">Protocolo científico activo</div>
                        <div class="protocol-panel-copy">Parámetros fijados por la metodología de análisis; no se reciben desde el navegador.</div>
                    </div>
                    <div class="lock-badge">🔒 Configuración protegida</div>
                </div>

                <div class="protocol-grid">
                    <div class="protocol-item"><div class="protocol-key">Score YOLO-Seg</div><div class="protocol-value">0.25</div></div>
                    <div class="protocol-item"><div class="protocol-key">Score Mask R-CNN</div><div class="protocol-value">0.40</div></div>
                    <div class="protocol-item"><div class="protocol-key">Threshold máscara</div><div class="protocol-value">0.50</div></div>
                    <div class="protocol-item"><div class="protocol-key">Entrada modelo</div><div class="protocol-value">1024 px</div></div>
                    <div class="protocol-item"><div class="protocol-key">Normalización</div><div class="protocol-value">2144 → 1024 px</div></div>
                    <div class="protocol-item"><div class="protocol-key">Solape de salida</div><div class="protocol-value">256 px</div></div>
                    <div class="protocol-item"><div class="protocol-key">IoU intramodelo</div><div class="protocol-value">0.50</div></div>
                    <div class="protocol-item"><div class="protocol-key">IoU intermodelo</div><div class="protocol-value">0.50</div></div>
                </div>

            </div>

            <div class="run-zone">
                <div class="run-zone-copy">
                    <strong>Ejecutar pipeline wall-to-wall</strong>
                    <span>Procesa el ortomosaico mediante ventanas de inferencia, deduplicación y consolidación intermodelo.</span>
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

            </div>

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
            disponibles para análisis.

        </div>

    @endif

    </div>

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


        $geojsonArtifact =
            $wallToWallAnalysis
                ->artifacts
                ->firstWhere(
                    'type',
                    'geojson'
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


            <div class="status-pill">

                {{ strtoupper($wallToWallAnalysis->status) }}

            </div>

        </div>


        <div class="pipeline-trace">

            <div class="trace-header">
                <div>
                    <div class="trace-title">Trazabilidad de consolidación</div>
                    <div class="trace-copy">Secuencia registrada desde las predicciones crudas hasta los objetos operativos finales.</div>
                </div>
            </div>

            <div class="trace-flow">
                <div class="trace-node">
                    <div class="trace-value">{{ $wallSummary['raw_predictions'] ?? 0 }}</div>
                    <div class="trace-label">Predicciones RAW</div>
                    <div class="trace-desc">Salidas acumuladas antes de la deduplicación intramodelo.</div>
                </div>
                <div class="trace-arrow">→</div>
                <div class="trace-node">
                    <div class="trace-value">{{ $wallSummary['unique_predictions'] ?? 0 }}</div>
                    <div class="trace-label">Candidatos únicos</div>
                    <div class="trace-desc">YOLO {{ $wallSummary['unique_yolo'] ?? 0 }} · Mask R-CNN {{ $wallSummary['unique_maskrcnn'] ?? 0 }}</div>
                </div>
                <div class="trace-arrow">→</div>
                <div class="trace-node">
                    <div class="trace-value">{{ $wallSummary['catalog_groups'] ?? 0 }}</div>
                    <div class="trace-label">Grupos de evidencia</div>
                    <div class="trace-desc">Catálogo construido para organizar asociaciones entre modelos.</div>
                </div>
                <div class="trace-arrow">→</div>
                <div class="trace-node final">
                    <div class="trace-value">{{ $wallSummary['primary_objects'] ?? 0 }}</div>
                    <div class="trace-label">Objetos operativos</div>
                    <div class="trace-desc">Geometrías finales disponibles para QA/QC y análisis posterior.</div>
                </div>
            </div>

        </div>

        <div class="evidence-dashboard">
            <div class="evidence-grid">
                <div class="evidence-card primary">
                    <div class="evidence-card-label">Geometría primaria</div>
                    <div class="evidence-card-value">{{ $wallSummary['primary_maskrcnn'] ?? 0 }}</div>
                    <div class="evidence-card-meta">Objetos cuya geometría operativa procede de Mask R-CNN.</div>
                </div>

                <div class="evidence-card secondary">
                    <div class="evidence-card-label">Fallback YOLO</div>
                    <div class="evidence-card-value">{{ $wallSummary['primary_yolo_fallback'] ?? 0 }}</div>
                    <div class="evidence-card-meta">Objetos conservados cuando Mask R-CNN no aporta candidato asociado.</div>
                </div>

                <div class="evidence-card bilateral">
                    <div class="evidence-card-label">Evidencia bilateral</div>
                    <div class="evidence-card-value">{{ $wallSummary['bilateral_clean'] ?? 0 }}</div>
                    <div class="evidence-card-meta">Objetos con evidencia espacial compatible proveniente de ambos modelos.</div>
                </div>

                <div class="evidence-card">
                    <div class="evidence-card-label">Mask R-CNN-only</div>
                    <div class="evidence-card-value">{{ $wallSummary['mrcnn_only'] ?? 0 }}</div>
                    <div class="evidence-card-meta">Evidencia unilateral registrada únicamente por Mask R-CNN.</div>
                </div>

                <div class="evidence-card">
                    <div class="evidence-card-label">YOLO-only</div>
                    <div class="evidence-card-value">{{ $wallSummary['yolo_only'] ?? 0 }}</div>
                    <div class="evidence-card-meta">Evidencia unilateral registrada únicamente por YOLO-Seg.</div>
                </div>

                <div class="evidence-card review">
                    <div class="evidence-card-label">Requieren revisión</div>
                    <div class="evidence-card-value">{{ $wallSummary['requires_review'] ?? 0 }}</div>
                    <div class="evidence-card-meta">Incluye {{ $structuralConflictObjects }} objetos con conflicto estructural registrado.</div>
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


        <div class="interpretation-card">
            <strong>Alcance de interpretación:</strong>
            los <strong>{{ $wallSummary['primary_objects'] ?? 0 }} objetos operativos</strong>
            son resultados del pipeline y <span class="interpretation-eq">≠ árboles confirmados</span>.
            De la misma forma, <strong>{{ $wallSummary['requires_review'] ?? 0 }} objetos marcados para revisión</strong>
            <span class="interpretation-eq">≠ errores confirmados</span>.
            La clasificación definitiva requiere contraste con verdad de campo.
        </div>

        <div class="artifact-actions">
            <div class="technical-divider">Resultados y productos del análisis</div>


            @if($geojsonArtifact)

                <a
                    class="
                        artifact-button
                        artifact-button-primary
                    "
                    href="{{
                        route(
                            'analysis.map',
                            [
                                'analysisUuid' =>
                                    $wallToWallAnalysis->uuid,
                            ]
                        )
                    }}"
                >

                    Abrir mapa de resultados

                </a>

            @endif


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

                    Exportar GeoPackage

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

                    Descargar manifest técnico

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

                Ver detalles técnicos

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


</div>

</main>


<script>

    /*
    |--------------------------------------------------------------------------
    | Workspace navigation
    |--------------------------------------------------------------------------
    */

    const workspaceTabs = document.querySelectorAll('[data-workspace-tab]');
    const workspacePanels = {
        lab: document.getElementById('workspace-lab'),
        wall: document.getElementById('workspace-wall'),
    };

    function activateWorkspaceTab(tabName) {
        workspaceTabs.forEach(tab => {
            const active = tab.dataset.workspaceTab === tabName;
            tab.classList.toggle('is-active', active);
            tab.setAttribute('aria-selected', active ? 'true' : 'false');
        });

        Object.entries(workspacePanels).forEach(([name, panel]) => {
            panel?.classList.toggle('is-active', name === tabName);
        });

        try {
            window.sessionStorage.setItem('uav-analysis-workspace', tabName);
        } catch (error) {
            // sessionStorage no es indispensable para el funcionamiento.
        }
    }

    workspaceTabs.forEach(tab => {
        tab.addEventListener('click', () => activateWorkspaceTab(tab.dataset.workspaceTab));
    });

    try {
        const storedWorkspace = window.sessionStorage.getItem('uav-analysis-workspace');
        if (storedWorkspace && workspacePanels[storedWorkspace]) {
            activateWorkspaceTab(storedWorkspace);
        }
    } catch (error) {
        // Mantener el estado inicial definido por Blade.
    }

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
                'YOLO-Seg E1.1 · score mínimo de referencia = 0.25'
        },

        maskrcnn: {

            threshold: 0.40,

            help:
                'Mask R-CNN M1.0 · score mínimo de referencia = 0.40'
        },

        both: {

            help:
                'Ejecuta ambos modelos sobre la misma imagen para inspeccionar acuerdo espacial entre máscaras.'
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


    const modelCards = document.querySelectorAll('[data-model-value]');

    function syncModelCards() {
        const current = modelSelect?.value;
        modelCards.forEach(card => {
            card.classList.toggle('is-active', card.dataset.modelValue === current);
        });
    }

    modelCards.forEach(card => {
        card.addEventListener('click', () => {
            if (!modelSelect) return;
            modelSelect.value = card.dataset.modelValue;
            modelSelect.dispatchEvent(new Event('change', { bubbles: true }));
            syncModelCards();
        });
    });

    modelSelect?.addEventListener('change', syncModelCards);
    syncModelCards();


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