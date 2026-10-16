<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0, viewport-fit=cover">
    <meta name="csrf-token" content="{{ csrf_token() }}">

    <title>Mapa de resultados | UAV Forest AI</title>

    <link
        rel="stylesheet"
        href="https://cdn.jsdelivr.net/npm/ol@10.10.0/ol.css"
    >

    <style>
        :root {
            --bg: #0b110e;
            --surface: #111a16;
            --surface-2: #16221c;
            --surface-3: #1b2922;
            --surface-hover: #203129;
            --panel: rgba(17, 26, 22, .97);
            --panel-soft: rgba(22, 34, 28, .94);

            --text: #f2f7f4;
            --text-soft: #c3d0c8;
            --muted: #8fa299;
            --faint: #697b72;

            --border: rgba(207, 226, 215, .11);
            --border-strong: rgba(207, 226, 215, .19);

            --forest: #2f9d6a;
            --forest-strong: #49bd83;
            --forest-soft: rgba(47, 157, 106, .14);

            --mrcnn: #35c77b;
            --mrcnn-soft: rgba(53, 199, 123, .16);

            --yolo: #a56bf4;
            --yolo-soft: rgba(165, 107, 244, .16);

            --review: #f2ad3d;
            --review-soft: rgba(242, 173, 61, .15);

            --danger: #ef6a62;
            --danger-soft: rgba(239, 106, 98, .13);

            --info: #69a7f2;
            --info-soft: rgba(105, 167, 242, .13);

            --neutral-model: #9fb0a7;

            --topbar-h: 66px;
            --left-w: 304px;
            --right-w: 372px;

            --radius-xl: 18px;
            --radius-lg: 14px;
            --radius-md: 11px;
            --radius-sm: 8px;

            --shadow-panel: 0 18px 46px rgba(0, 0, 0, .24);
        }

        * {
            box-sizing: border-box;
        }

        html,
        body {
            margin: 0;
            width: 100%;
            min-height: 100%;
            background: var(--bg);
            color: var(--text);
            font-family:
                Inter,
                ui-sans-serif,
                system-ui,
                -apple-system,
                BlinkMacSystemFont,
                "Segoe UI",
                sans-serif;
        }

        body {
            overflow: hidden;
        }

        button,
        input,
        select {
            font: inherit;
        }

        button,
        a {
            -webkit-tap-highlight-color: transparent;
        }

        a {
            color: inherit;
        }

        [hidden] {
            display: none !important;
        }

        /* =========================================================
           TOPBAR
        ========================================================= */

        .topbar {
            height: var(--topbar-h);
            display: flex;
            align-items: center;
            justify-content: space-between;
            gap: 18px;
            padding: 0 18px;
            background: #0d1511;
            border-bottom: 1px solid var(--border);
            position: relative;
            z-index: 150;
        }

        .topbar-left,
        .topbar-right {
            min-width: 0;
            display: flex;
            align-items: center;
            gap: 12px;
        }

        .brand-mark {
            width: 38px;
            height: 38px;
            flex: 0 0 38px;
            border-radius: 11px;
            display: grid;
            place-items: center;
            background: linear-gradient(145deg, #1a533d, #123427);
            border: 1px solid rgba(105, 207, 154, .22);
            box-shadow: inset 0 1px 0 rgba(255,255,255,.06);
        }

        .brand-mark svg {
            width: 22px;
            height: 22px;
        }

        .brand-copy {
            flex: 0 0 auto;
        }

        .brand-name {
            font-size: 13px;
            font-weight: 800;
            letter-spacing: -.01em;
        }

        .brand-mode {
            margin-top: 2px;
            color: var(--muted);
            font-size: 10px;
        }

        .breadcrumb-divider {
            width: 1px;
            height: 30px;
            background: var(--border);
            margin: 0 2px;
        }

        .context-path {
            min-width: 0;
            display: flex;
            align-items: center;
            gap: 7px;
            color: var(--muted);
            font-size: 11px;
            white-space: nowrap;
            overflow: hidden;
        }

        .context-path strong {
            max-width: 220px;
            overflow: hidden;
            text-overflow: ellipsis;
            color: var(--text-soft);
            font-weight: 700;
        }

        .context-chevron {
            color: var(--faint);
        }

        .top-pill {
            display: inline-flex;
            align-items: center;
            gap: 7px;
            min-height: 32px;
            padding: 6px 10px;
            border-radius: 999px;
            border: 1px solid var(--border);
            background: rgba(255,255,255,.035);
            color: var(--text-soft);
            font-size: 10px;
            font-weight: 700;
            white-space: nowrap;
        }

        .status-dot {
            width: 7px;
            height: 7px;
            border-radius: 50%;
            background: var(--forest-strong);
            box-shadow: 0 0 0 3px rgba(73, 189, 131, .11);
        }

        .top-action {
            height: 34px;
            display: inline-flex;
            align-items: center;
            justify-content: center;
            gap: 7px;
            padding: 0 11px;
            border: 1px solid var(--border);
            border-radius: 9px;
            background: rgba(255,255,255,.035);
            color: var(--text-soft);
            text-decoration: none;
            font-size: 11px;
            font-weight: 750;
            cursor: pointer;
            transition: background .15s ease, border-color .15s ease;
        }

        .top-action:hover {
            background: rgba(255,255,255,.07);
            border-color: var(--border-strong);
        }

        .top-action svg {
            width: 15px;
            height: 15px;
        }

        /* =========================================================
           WORKSPACE
        ========================================================= */

        .workspace {
            height: calc(100vh - var(--topbar-h));
            height: calc(100dvh - var(--topbar-h));
            display: grid;
            grid-template-columns: var(--left-w) minmax(0, 1fr) var(--right-w);
            min-height: 0;
            background: var(--bg);
        }

        .side-panel {
            min-width: 0;
            min-height: 0;
            display: flex;
            flex-direction: column;
            background: var(--surface);
            position: relative;
            z-index: 60;
        }

        .left-panel {
            border-right: 1px solid var(--border);
        }

        .right-panel {
            border-left: 1px solid var(--border);
        }

        .panel-heading {
            flex: 0 0 auto;
            padding: 16px;
            border-bottom: 1px solid var(--border);
        }

        .panel-heading-row {
            display: flex;
            align-items: center;
            justify-content: space-between;
            gap: 10px;
        }

        .panel-kicker {
            color: var(--forest-strong);
            font-size: 9px;
            font-weight: 850;
            letter-spacing: .11em;
            text-transform: uppercase;
        }

        .panel-title {
            margin: 4px 0 0;
            font-size: 16px;
            line-height: 1.2;
            font-weight: 800;
            letter-spacing: -.015em;
        }

        .panel-description {
            margin: 7px 0 0;
            color: var(--muted);
            font-size: 10.5px;
            line-height: 1.55;
        }

        .panel-count {
            min-width: 30px;
            height: 26px;
            padding: 0 8px;
            display: inline-flex;
            align-items: center;
            justify-content: center;
            border-radius: 999px;
            background: var(--forest-soft);
            color: #bde9d1;
            border: 1px solid rgba(73, 189, 131, .18);
            font-size: 10px;
            font-weight: 850;
        }

        /* =========================================================
           EXPLORER
        ========================================================= */

        .explorer-controls {
            flex: 0 0 auto;
            padding: 13px 14px 12px;
            border-bottom: 1px solid var(--border);
            display: grid;
            gap: 10px;
        }

        .search-box {
            position: relative;
        }

        .search-box svg {
            position: absolute;
            left: 11px;
            top: 50%;
            transform: translateY(-50%);
            width: 14px;
            height: 14px;
            color: var(--muted);
            pointer-events: none;
        }

        .search-input {
            width: 100%;
            height: 38px;
            border: 1px solid var(--border);
            border-radius: 10px;
            background: #0e1713;
            color: var(--text);
            padding: 0 11px 0 34px;
            font-size: 11px;
            outline: none;
            transition: border-color .15s ease, box-shadow .15s ease;
        }

        .search-input:focus {
            border-color: rgba(73, 189, 131, .55);
            box-shadow: 0 0 0 3px rgba(73, 189, 131, .08);
        }

        .search-input::placeholder {
            color: #677a70;
        }

        .quick-filters {
            display: flex;
            flex-wrap: wrap;
            gap: 6px;
        }

        .filter-chip {
            min-height: 29px;
            padding: 5px 9px;
            border-radius: 8px;
            border: 1px solid var(--border);
            background: rgba(255,255,255,.025);
            color: var(--muted);
            font-size: 9.5px;
            font-weight: 750;
            cursor: pointer;
            transition: background .15s ease, color .15s ease, border-color .15s ease;
        }

        .filter-chip:hover {
            color: var(--text-soft);
            border-color: var(--border-strong);
        }

        .filter-chip.active {
            color: #d8f4e5;
            border-color: rgba(73, 189, 131, .28);
            background: var(--forest-soft);
        }

        .advanced-filters {
            display: grid;
            grid-template-columns: 1fr 1fr;
            gap: 7px;
        }

        .compact-select {
            width: 100%;
            height: 34px;
            padding: 0 26px 0 9px;
            border: 1px solid var(--border);
            border-radius: 9px;
            background: #0e1713;
            color: var(--text-soft);
            font-size: 9.5px;
            outline: none;
        }

        .filter-feedback {
            min-height: 15px;
            display: flex;
            align-items: center;
            justify-content: space-between;
            gap: 8px;
            color: var(--muted);
            font-size: 9.5px;
        }

        .filter-reset {
            padding: 0;
            border: 0;
            background: transparent;
            color: #9fdab9;
            font-size: 9.5px;
            font-weight: 750;
            cursor: pointer;
        }

        .object-list {
            min-height: 0;
            flex: 1 1 auto;
            overflow: auto;
            padding: 8px;
            scrollbar-width: thin;
            scrollbar-color: #31453a transparent;
        }

        .object-list-empty {
            margin: 12px;
            padding: 20px 14px;
            border: 1px dashed var(--border-strong);
            border-radius: 12px;
            color: var(--muted);
            font-size: 10.5px;
            line-height: 1.6;
            text-align: center;
        }

        .object-row {
            width: 100%;
            display: block;
            border: 1px solid transparent;
            border-radius: 11px;
            background: transparent;
            color: inherit;
            text-align: left;
            padding: 10px;
            margin: 0 0 4px;
            cursor: pointer;
            transition: background .12s ease, border-color .12s ease;
        }

        .object-row:hover {
            background: var(--surface-2);
        }

        .object-row.selected {
            background: rgba(73, 189, 131, .09);
            border-color: rgba(73, 189, 131, .24);
        }

        .object-row-top {
            display: flex;
            align-items: center;
            justify-content: space-between;
            gap: 8px;
        }

        .object-id {
            min-width: 0;
            overflow: hidden;
            text-overflow: ellipsis;
            white-space: nowrap;
            font-size: 11px;
            font-weight: 820;
            color: #f0f7f3;
        }

        .model-mark {
            flex: 0 0 auto;
            width: 8px;
            height: 8px;
            border-radius: 50%;
        }

        .model-mark.mrcnn { background: var(--mrcnn); }
        .model-mark.yolo { background: var(--yolo); }
        .model-mark.unknown { background: var(--neutral-model); }

        .object-row-meta {
            margin-top: 7px;
            display: flex;
            flex-wrap: wrap;
            align-items: center;
            gap: 5px 8px;
            color: var(--muted);
            font-size: 9.5px;
        }

        .mini-status {
            display: inline-flex;
            align-items: center;
            gap: 5px;
        }

        .mini-status::before {
            content: "";
            width: 5px;
            height: 5px;
            border-radius: 50%;
            background: var(--forest-strong);
        }

        .mini-status.review::before {
            background: var(--review);
        }

        .explorer-footer {
            flex: 0 0 auto;
            padding: 10px 14px 12px;
            border-top: 1px solid var(--border);
            color: var(--muted);
            font-size: 9.5px;
            line-height: 1.5;
        }

        /* =========================================================
           MAP PANE
        ========================================================= */

        .map-pane {
            min-width: 0;
            min-height: 0;
            position: relative;
            overflow: hidden;
            background: #101914;
        }

        #map {
            position: absolute;
            inset: 0 0 32px;
            background:
                linear-gradient(45deg, rgba(255,255,255,.018) 25%, transparent 25%),
                linear-gradient(-45deg, rgba(255,255,255,.018) 25%, transparent 25%),
                linear-gradient(45deg, transparent 75%, rgba(255,255,255,.018) 75%),
                linear-gradient(-45deg, transparent 75%, rgba(255,255,255,.018) 75%),
                #111b16;
            background-size: 28px 28px;
            background-position: 0 0, 0 14px, 14px -14px, -14px 0;
        }

        .map-toolbar {
            position: absolute;
            top: 12px;
            left: 12px;
            right: 12px;
            z-index: 40;
            display: flex;
            justify-content: space-between;
            align-items: flex-start;
            gap: 10px;
            pointer-events: none;
        }

        .toolbar-left,
        .toolbar-right {
            display: flex;
            align-items: center;
            flex-wrap: wrap;
            gap: 7px;
            pointer-events: auto;
        }

        .tool-group {
            min-height: 38px;
            display: inline-flex;
            align-items: center;
            gap: 6px;
            padding: 5px;
            border-radius: 11px;
            border: 1px solid rgba(222, 239, 229, .12);
            background: rgba(13, 21, 17, .90);
            backdrop-filter: blur(10px);
            box-shadow: 0 8px 22px rgba(0,0,0,.18);
        }

        .map-tool {
            height: 28px;
            display: inline-flex;
            align-items: center;
            justify-content: center;
            gap: 6px;
            padding: 0 8px;
            border: 0;
            border-radius: 7px;
            background: transparent;
            color: var(--text-soft);
            font-size: 9.5px;
            font-weight: 750;
            cursor: pointer;
            white-space: nowrap;
            transition: background .12s ease, color .12s ease;
        }

        .map-tool:hover {
            background: rgba(255,255,255,.07);
            color: var(--text);
        }

        .map-tool.active {
            background: var(--forest-soft);
            color: #c7f0da;
        }

        .map-tool.review-active {
            background: var(--review-soft);
            color: #ffe2ac;
        }

        .map-tool svg {
            width: 14px;
            height: 14px;
        }

        .layer-dot {
            width: 7px;
            height: 7px;
            border-radius: 50%;
        }

        .layer-dot.preview { background: #f1f4f2; }
        .layer-dot.mrcnn { background: var(--mrcnn); }
        .layer-dot.yolo { background: var(--yolo); }
        .layer-dot.review { background: var(--review); }

        .opacity-popover {
            position: relative;
        }

        .opacity-panel {
            position: absolute;
            top: calc(100% + 8px);
            right: 0;
            width: 236px;
            padding: 12px;
            border-radius: 12px;
            border: 1px solid var(--border-strong);
            background: rgba(13, 21, 17, .97);
            box-shadow: var(--shadow-panel);
            display: none;
        }

        .opacity-popover.open .opacity-panel {
            display: grid;
            gap: 12px;
        }

        .opacity-row {
            display: grid;
            gap: 6px;
        }

        .opacity-row-head {
            display: flex;
            justify-content: space-between;
            gap: 12px;
            color: var(--muted);
            font-size: 9.5px;
            font-weight: 700;
        }

        .opacity-row-head strong {
            color: var(--text-soft);
        }

        .opacity-row input[type="range"] {
            width: 100%;
            accent-color: var(--forest-strong);
        }

        .map-summary {
            position: absolute;
            left: 12px;
            bottom: 44px;
            z-index: 35;
            display: inline-flex;
            align-items: center;
            gap: 4px;
            padding: 5px;
            border-radius: 11px;
            border: 1px solid rgba(222, 239, 229, .11);
            background: rgba(13, 21, 17, .88);
            backdrop-filter: blur(9px);
            box-shadow: 0 8px 22px rgba(0,0,0,.17);
            pointer-events: none;
        }

        .summary-item {
            min-width: 68px;
            padding: 5px 8px;
            border-right: 1px solid var(--border);
        }

        .summary-item:last-child {
            border-right: 0;
        }

        .summary-label {
            color: var(--muted);
            font-size: 8px;
            text-transform: uppercase;
            letter-spacing: .06em;
            font-weight: 750;
        }

        .summary-value {
            margin-top: 2px;
            font-size: 13px;
            font-weight: 850;
        }

        .map-statusbar {
            position: absolute;
            left: 0;
            right: 0;
            bottom: 0;
            height: 32px;
            z-index: 45;
            display: flex;
            align-items: center;
            gap: 0;
            overflow-x: auto;
            border-top: 1px solid var(--border);
            background: #0d1511;
            color: var(--muted);
            scrollbar-width: none;
        }

        .map-statusbar::-webkit-scrollbar {
            display: none;
        }

        .statusbar-item {
            min-height: 31px;
            display: inline-flex;
            align-items: center;
            gap: 6px;
            padding: 0 11px;
            border-right: 1px solid var(--border);
            font-size: 9.5px;
            white-space: nowrap;
        }

        .statusbar-item strong {
            color: var(--text-soft);
            font-weight: 750;
        }

        .map-tooltip {
            position: absolute;
            z-index: 80;
            transform: translate(-50%, calc(-100% - 13px));
            pointer-events: none;
            min-width: 190px;
            max-width: 260px;
            padding: 10px 11px;
            border-radius: 11px;
            border: 1px solid rgba(222, 239, 229, .14);
            background: rgba(10, 17, 13, .96);
            box-shadow: 0 15px 35px rgba(0,0,0,.28);
            opacity: 0;
            transition: opacity .1s ease;
        }

        .map-tooltip.active {
            opacity: 1;
        }

        .tooltip-title {
            font-size: 11px;
            font-weight: 820;
        }

        .tooltip-meta {
            margin-top: 5px;
            color: var(--muted);
            font-size: 9.5px;
            line-height: 1.5;
        }

        .tooltip-status {
            margin-top: 7px;
            display: inline-flex;
            align-items: center;
            gap: 5px;
            padding: 4px 6px;
            border-radius: 6px;
            background: var(--review-soft);
            color: #fbd698;
            font-size: 8.5px;
            font-weight: 800;
        }

        .map-loading,
        .map-error {
            position: absolute;
            inset: 0 0 32px;
            z-index: 70;
            display: grid;
            place-items: center;
            background: rgba(8, 14, 11, .54);
            backdrop-filter: blur(3px);
        }

        .map-error {
            background: rgba(8, 14, 11, .72);
        }

        .loading-card,
        .error-card {
            width: min(380px, calc(100% - 40px));
            padding: 20px;
            border: 1px solid var(--border-strong);
            border-radius: 15px;
            background: rgba(17, 26, 22, .97);
            box-shadow: var(--shadow-panel);
            text-align: center;
        }

        .loading-spinner {
            width: 24px;
            height: 24px;
            margin: 0 auto 10px;
            border: 2px solid rgba(255,255,255,.10);
            border-top-color: var(--forest-strong);
            border-radius: 50%;
            animation: spin .75s linear infinite;
        }

        @keyframes spin {
            to { transform: rotate(360deg); }
        }

        .loading-title,
        .error-title {
            font-size: 12px;
            font-weight: 800;
        }

        .loading-text,
        .error-text {
            margin-top: 6px;
            color: var(--muted);
            font-size: 10px;
            line-height: 1.55;
        }

        .error-action {
            margin-top: 13px;
            min-height: 32px;
            padding: 0 12px;
            border: 1px solid rgba(73, 189, 131, .22);
            border-radius: 8px;
            background: var(--forest-soft);
            color: #c8efdc;
            font-size: 10px;
            font-weight: 800;
            cursor: pointer;
        }

        /* =========================================================
           OPENLAYERS
        ========================================================= */

        .ol-control button {
            background: rgba(13, 21, 17, .92) !important;
            color: white !important;
            border-radius: 7px !important;
        }

        .ol-control button:hover {
            background: rgba(31, 49, 40, .96) !important;
        }

        .ol-zoom {
            top: 62px !important;
            left: auto !important;
            right: 12px !important;
            border-radius: 9px !important;
            overflow: hidden;
            border: 1px solid rgba(222,239,229,.12);
            background: transparent !important;
        }

        .ol-scale-line {
            left: auto !important;
            right: 12px !important;
            bottom: 44px !important;
            border-radius: 8px;
            background: rgba(13, 21, 17, .84) !important;
            border: 1px solid rgba(222,239,229,.10);
        }

        .ol-attribution {
            right: 6px !important;
            bottom: 36px !important;
            background: rgba(13, 21, 17, .78) !important;
            color: white !important;
            border-radius: 7px !important;
        }

        /* =========================================================
           INSPECTOR
        ========================================================= */

        .inspector-tabs {
            flex: 0 0 auto;
            display: grid;
            grid-template-columns: 1fr 1fr;
            gap: 5px;
            padding: 7px;
            border-bottom: 1px solid var(--border);
            background: #0f1814;
        }

        .inspector-tab {
            min-height: 32px;
            border: 0;
            border-radius: 8px;
            background: transparent;
            color: var(--muted);
            font-size: 10px;
            font-weight: 800;
            cursor: pointer;
        }

        .inspector-tab.active {
            background: var(--surface-3);
            color: var(--text);
        }

        .inspector-body {
            min-height: 0;
            flex: 1 1 auto;
            overflow-y: auto;
            padding: 14px;
            scrollbar-width: thin;
            scrollbar-color: #31453a transparent;
        }

        .inspector-empty {
            padding: 18px 14px;
            border: 1px dashed var(--border-strong);
            border-radius: 12px;
            color: var(--muted);
            font-size: 10.5px;
            line-height: 1.65;
        }

        .selected-header {
            display: flex;
            align-items: flex-start;
            justify-content: space-between;
            gap: 10px;
            margin-bottom: 13px;
        }

        .selected-kicker {
            color: var(--muted);
            font-size: 8.5px;
            text-transform: uppercase;
            letter-spacing: .08em;
            font-weight: 800;
        }

        .selected-id {
            margin-top: 3px;
            font-size: 18px;
            line-height: 1.15;
            font-weight: 850;
            letter-spacing: -.02em;
            overflow-wrap: anywhere;
        }

        .badges {
            display: flex;
            flex-wrap: wrap;
            gap: 5px;
            margin-bottom: 13px;
        }

        .badge {
            display: inline-flex;
            align-items: center;
            gap: 6px;
            padding: 5px 7px;
            border-radius: 7px;
            border: 1px solid var(--border);
            font-size: 8.5px;
            font-weight: 800;
        }

        .badge-mrcnn {
            color: #c6f2d9;
            border-color: rgba(53,199,123,.22);
            background: var(--mrcnn-soft);
        }

        .badge-yolo {
            color: #eadbff;
            border-color: rgba(165,107,244,.22);
            background: var(--yolo-soft);
        }

        .badge-neutral {
            color: #d8e2dc;
            background: rgba(159,176,167,.10);
        }

        .badge-review {
            color: #f9dda9;
            border-color: rgba(242,173,61,.24);
            background: var(--review-soft);
        }

        .badge-ok {
            color: #c5edd6;
            border-color: rgba(73,189,131,.18);
            background: var(--forest-soft);
        }

        .inspector-actions {
            display: flex;
            flex-wrap: wrap;
            gap: 6px;
            margin-bottom: 14px;
        }

        .small-action {
            min-height: 30px;
            display: inline-flex;
            align-items: center;
            justify-content: center;
            gap: 5px;
            padding: 0 9px;
            border-radius: 8px;
            border: 1px solid var(--border);
            background: rgba(255,255,255,.025);
            color: var(--text-soft);
            font-size: 9px;
            font-weight: 750;
            cursor: pointer;
        }

        .small-action:hover {
            background: var(--surface-3);
        }

        .small-action:disabled {
            opacity: .4;
            cursor: default;
        }

        .metric-grid {
            display: grid;
            grid-template-columns: 1fr 1fr;
            gap: 7px;
            margin-bottom: 14px;
        }

        .metric-box {
            min-width: 0;
            padding: 10px;
            border-radius: 10px;
            border: 1px solid var(--border);
            background: rgba(255,255,255,.025);
        }

        .metric-label {
            color: var(--muted);
            font-size: 8px;
            font-weight: 800;
            letter-spacing: .06em;
            text-transform: uppercase;
        }

        .metric-value {
            margin-top: 5px;
            color: var(--text);
            font-size: 15px;
            font-weight: 850;
            overflow-wrap: anywhere;
        }

        .section-block {
            margin-top: 14px;
        }

        .section-block:first-child {
            margin-top: 0;
        }

        .section-label {
            display: flex;
            align-items: center;
            justify-content: space-between;
            gap: 8px;
            margin-bottom: 8px;
            color: #b8c9bf;
            font-size: 9px;
            font-weight: 850;
            text-transform: uppercase;
            letter-spacing: .09em;
        }

        .section-help {
            text-transform: none;
            letter-spacing: 0;
            color: var(--faint);
            font-size: 8px;
            font-weight: 650;
        }

        .property-list {
            border: 1px solid var(--border);
            border-radius: 11px;
            overflow: hidden;
        }

        .property-row {
            display: grid;
            grid-template-columns: minmax(0, .9fr) minmax(0, 1.1fr);
            gap: 10px;
            padding: 8px 10px;
            border-bottom: 1px solid var(--border);
            background: rgba(255,255,255,.018);
        }

        .property-row:last-child {
            border-bottom: 0;
        }

        .property-key {
            color: var(--muted);
            font-size: 9px;
            line-height: 1.4;
        }

        .property-value {
            color: var(--text-soft);
            font-size: 9.5px;
            font-weight: 720;
            line-height: 1.4;
            text-align: right;
            overflow-wrap: anywhere;
        }

        .method-note,
        .warning-note,
        .info-note {
            padding: 11px;
            border-radius: 10px;
            font-size: 9.5px;
            line-height: 1.58;
        }

        .method-note {
            color: #c8d7cf;
            border: 1px solid var(--border);
            background: rgba(255,255,255,.025);
        }

        .warning-note {
            color: #f7dca9;
            border: 1px solid rgba(242,173,61,.20);
            background: var(--review-soft);
        }

        .info-note {
            color: #c7d9f1;
            border: 1px solid rgba(105,167,242,.18);
            background: var(--info-soft);
        }

        .analysis-flow {
            display: grid;
            grid-template-columns: repeat(4, 1fr);
            gap: 5px;
            margin: 8px 0 14px;
        }

        .flow-step {
            min-width: 0;
            padding: 9px 6px;
            border: 1px solid var(--border);
            border-radius: 9px;
            background: rgba(255,255,255,.022);
            text-align: center;
            position: relative;
        }

        .flow-step:not(:last-child)::after {
            content: "";
            position: absolute;
            right: -5px;
            top: 50%;
            width: 5px;
            border-top: 1px solid var(--border-strong);
        }

        .flow-value {
            font-size: 15px;
            font-weight: 850;
        }

        .flow-label {
            margin-top: 3px;
            color: var(--muted);
            font-size: 7.5px;
            line-height: 1.25;
            text-transform: uppercase;
            letter-spacing: .05em;
        }

        .analysis-kpis {
            display: grid;
            grid-template-columns: 1fr 1fr;
            gap: 7px;
        }

        .analysis-kpi {
            padding: 10px;
            border-radius: 10px;
            border: 1px solid var(--border);
            background: rgba(255,255,255,.022);
        }

        .analysis-kpi-label {
            color: var(--muted);
            font-size: 8px;
            text-transform: uppercase;
            letter-spacing: .06em;
            font-weight: 800;
        }

        .analysis-kpi-value {
            margin-top: 4px;
            font-size: 17px;
            font-weight: 850;
        }

        /* =========================================================
           MOBILE CONTROLS
        ========================================================= */

        .mobile-panel-button {
            display: none;
        }

        .panel-close {
            display: none;
            width: 30px;
            height: 30px;
            border: 1px solid var(--border);
            border-radius: 8px;
            background: rgba(255,255,255,.03);
            color: var(--text-soft);
            cursor: pointer;
        }

        .drawer-backdrop {
            display: none;
        }

        /* =========================================================
           FULLSCREEN
        ========================================================= */

        .workspace:fullscreen {
            height: 100vh;
        }

        .workspace:fullscreen .map-pane {
            background: #0b110e;
        }

        /* =========================================================
           RESPONSIVE
        ========================================================= */

        @media (max-width: 1380px) {
            :root {
                --left-w: 276px;
                --right-w: 344px;
            }

            .context-path strong {
                max-width: 150px;
            }

            .top-pill.version-pill {
                display: none;
            }
        }

        @media (max-width: 1279px) {
            body {
                overflow: hidden;
            }

            .workspace {
                grid-template-columns: 1fr;
                position: relative;
            }

            .side-panel {
                position: absolute;
                top: 0;
                bottom: 0;
                width: min(360px, calc(100vw - 28px));
                z-index: 120;
                box-shadow: var(--shadow-panel);
                transition: transform .2s ease;
            }

            .left-panel {
                left: 0;
                transform: translateX(-105%);
            }

            .right-panel {
                right: 0;
                transform: translateX(105%);
            }

            .left-panel.open,
            .right-panel.open {
                transform: translateX(0);
            }

            .drawer-backdrop {
                position: absolute;
                inset: 0;
                z-index: 110;
                background: rgba(0,0,0,.42);
            }

            .drawer-backdrop.active {
                display: block;
            }

            .panel-close {
                display: inline-grid;
                place-items: center;
            }

            .mobile-panel-button {
                display: inline-flex;
            }

            .context-path {
                display: none;
            }

            .breadcrumb-divider {
                display: none;
            }
        }

        @media (max-width: 760px) {
            :root {
                --topbar-h: 58px;
            }

            .topbar {
                padding: 0 10px;
            }

            .brand-mark {
                width: 34px;
                height: 34px;
                flex-basis: 34px;
            }

            .brand-mode,
            .top-pill,
            .json-action {
                display: none;
            }

            .top-action {
                padding: 0 9px;
            }

            .top-action .action-text {
                display: none;
            }

            .map-toolbar {
                top: 8px;
                left: 8px;
                right: 8px;
                align-items: flex-start;
            }

            .map-tool .tool-label {
                display: none;
            }

            .map-summary {
                left: 8px;
                bottom: 40px;
                max-width: calc(100% - 16px);
                overflow-x: auto;
                pointer-events: auto;
            }

            .summary-item {
                min-width: 62px;
            }

            .ol-scale-line {
                display: none;
            }
        }

        /* =========================================================
           WEB.6 — GPS PARA TRABAJO DE CAMPO
           Componentes visuales independientes del pipeline científico.
        ========================================================= */
        .field-gps {
            position: absolute;
            top: 63px;
            right: 12px;
            z-index: 55;
            width: min(246px, calc(100% - 24px));
            display: grid;
            gap: 7px;
            pointer-events: none;
        }
        .field-gps-actions {
            display: flex;
            justify-content: flex-end;
            gap: 6px;
            pointer-events: auto;
        }
        .gps-button {
            min-width: 40px;
            min-height: 40px;
            display: inline-flex;
            align-items: center;
            justify-content: center;
            gap: 7px;
            border: 1px solid rgba(207,226,215,.18);
            border-radius: 11px;
            background: rgba(13,21,17,.94);
            box-shadow: 0 7px 24px rgba(0,0,0,.28);
            color: #e5f4eb;
            font-weight: 800;
            font-size: 11px;
            cursor: pointer;
            backdrop-filter: blur(9px);
            touch-action: manipulation;
        }
        .gps-button svg { width: 18px; height: 18px; flex: 0 0 auto; }
        .gps-button.primary { background: #1b7355; border-color: #4fc58d; }
        .gps-button.primary:hover { background: #258361; }
        .gps-button:disabled { opacity: .48; cursor: not-allowed; }
        .gps-button.is-following { background: #244d8a; border-color: #7badf3; }
        .gps-status {
            pointer-events: auto;
            padding: 9px 11px;
            border: 1px solid rgba(207,226,215,.17);
            border-radius: 11px;
            background: rgba(13,21,17,.94);
            box-shadow: 0 7px 24px rgba(0,0,0,.21);
            backdrop-filter: blur(9px);
            color: var(--text-soft);
            font-size: 10px;
            line-height: 1.5;
        }
        .gps-status-top {
            display: flex;
            align-items: center;
            justify-content: space-between;
            gap: 8px;
        }
        .gps-status-title { font-size: 11px; font-weight: 850; color: #f3fbf6; }
        .gps-status-indicator { font-size: 9px; font-weight: 800; color: #aecac0; }
        .gps-status-indicator[data-state="good"] { color: #76e4aa; }
        .gps-status-indicator[data-state="warning"] { color: #ffc974; }
        .gps-status-indicator[data-state="error"] { color: #ff9991; }
        .gps-status-text { margin-top: 4px; color: #c1d0c7; overflow-wrap: anywhere; }
        .gps-metrics { display: flex; flex-wrap: wrap; gap: 7px; margin-top: 6px; color: #a4bcb0; }
        .gps-metrics strong { color: white; }
        .gps-target {
            margin-top: 7px;
            padding-top: 7px;
            border-top: 1px solid rgba(207,226,215,.12);
            color: #cfe8db;
            font-size: 10px;
        }
        .gps-field-note { color: #8ea79a; font-size: 9px; margin-top: 5px; }
        .field-selected {
            display: none;
            position: absolute;
            bottom: 89px;
            left: 10px;
            right: 10px;
            z-index: 50;
            min-height: 51px;
            padding: 8px 10px;
            align-items: center;
            justify-content: space-between;
            gap: 9px;
            border: 1px solid rgba(191,229,210,.25);
            border-radius: 13px;
            background: rgba(13,21,17,.95);
            color: white;
            box-shadow: var(--shadow-panel);
            backdrop-filter: blur(12px);
        }
        .field-selected-meta { min-width: 0; }
        .field-selected-title { font-size: 11px; font-weight: 850; overflow: hidden; white-space: nowrap; text-overflow: ellipsis; }
        .field-selected-sub { font-size: 9px; color: #bfd0c7; margin-top: 3px; }
        .field-selected .gps-button { min-height: 34px; padding: 0 11px; white-space: nowrap; }
        .gps-mobile-close { display: none; }

        @media (max-width: 760px) {
            .workspace {
                height: calc(100dvh - var(--topbar-h) - env(safe-area-inset-top, 0px));
            }
            .topbar {
                height: calc(var(--topbar-h) + env(safe-area-inset-top, 0px));
                padding-top: env(safe-area-inset-top, 0px);
                padding-left: max(10px, env(safe-area-inset-left, 0px));
                padding-right: max(10px, env(safe-area-inset-right, 0px));
            }
            #map {
                bottom: calc(36px + env(safe-area-inset-bottom, 0px));
            }
            .map-statusbar {
                height: calc(36px + env(safe-area-inset-bottom, 0px));
                padding-bottom: env(safe-area-inset-bottom, 0px);
            }
            .map-summary { display: none; }
            .map-toolbar {
                flex-wrap: nowrap;
                gap: 6px;
                pointer-events: auto;
            }
            .toolbar-left {
                min-width: 0;
                flex: 1 1 auto;
                flex-wrap: nowrap;
                overflow-x: auto;
                scrollbar-width: none;
                overscroll-behavior-x: contain;
            }
            .toolbar-left::-webkit-scrollbar { display: none; }
            .toolbar-right { flex: 0 0 auto; flex-wrap: nowrap; }
            .toolbar-right > .tool-group:last-child { display: none; }
            .tool-group { min-height: 43px; }
            .map-tool { min-height: 33px; min-width: 33px; }
            .field-gps {
                top: 64px;
                right: 9px;
                width: min(233px, calc(100% - 18px));
            }
            .gps-button { min-width: 44px; min-height: 44px; }
            .gps-status { font-size: 10px; }
            .ol-zoom { display: none !important; }
            .map-tooltip { display: none !important; }
            .right-panel {
                top: auto;
                left: 0;
                right: 0;
                bottom: 0;
                width: 100%;
                height: min(74dvh, 650px);
                border-radius: 19px 19px 0 0;
                border-top: 1px solid rgba(207,226,215,.23);
                border-left: 0;
                transform: translateY(105%);
                padding-bottom: env(safe-area-inset-bottom, 0px);
            }
            .right-panel.open { transform: translateY(0); }
            .right-panel .panel-heading::before {
                content: '';
                display: block;
                width: 42px;
                height: 4px;
                border-radius: 8px;
                background: #647a6b;
                margin: -3px auto 10px;
            }
            .field-selected:not([hidden]) { display: flex; bottom: calc(47px + env(safe-area-inset-bottom, 0px)); }
            .gps-mobile-close { display: inline-flex; }
            .map-loading, .map-error { inset: 0 0 calc(36px + env(safe-area-inset-bottom, 0px)); }
        }
        @media (prefers-reduced-motion: reduce) {
            .side-panel { transition: none !important; }
        }

    

        /* =========================================================
           V0.8A.WEB.7 · RESPONSIVE FIELD WORKSPACE
           Ajustes visuales no destructivos. NO cambia la fuente GIS.
        ========================================================= */
        :root {
            --left-w: 284px;
            --right-w: 326px;
            --topbar-h: 64px;
            --field-control: 42px;
        }
        html, body { height: 100%; }
        body { overscroll-behavior: none; }
        button, input, select { touch-action: manipulation; }
        button:focus-visible, a:focus-visible, input:focus-visible, select:focus-visible {
            outline: 2px solid #7ad4a4;
            outline-offset: 2px;
        }
        .topbar {
            padding: 0 clamp(12px, 1.45vw, 24px);
            gap: clamp(8px, 1.2vw, 18px);
            background: #0d1812;
        }
        .topbar-left { flex: 1 1 auto; overflow: hidden; gap: 11px; }
        .topbar-right { flex: 0 0 auto; gap: 8px; }
        .brand-copy { min-width: 0; }
        .brand-name { font-size: 13px; white-space: nowrap; }
        .context-path { flex: 1 1 auto; }
        .context-path strong { max-width: clamp(100px, 13vw, 235px); }
        .top-action { min-height: 38px; height: 38px; border-radius: 10px; }
        .workspace {
            grid-template-columns: minmax(0, var(--left-w)) minmax(0, 1fr) minmax(0, var(--right-w));
            overflow: hidden;
        }
        .side-panel { background: #111d16; }
        .panel-heading { padding: 18px 17px 15px; }
        .panel-title { font-size: 17px; }
        .panel-description { font-size: 11px; line-height: 1.6; }
        .explorer-controls { padding: 14px 13px; gap: 11px; }
        .search-input { height: 43px; font-size: 12px; }
        .quick-filters { gap: 6px; }
        .filter-chip { min-height: 33px; padding: 6px 10px; font-size: 10.5px; }
        .compact-select { height: 38px; font-size: 10.5px; }
        .filter-feedback, .filter-reset { font-size: 10px; }
        .object-list { padding: 9px; overscroll-behavior: contain; }
        .object-row { padding: 12px; margin-bottom: 5px; border-radius: 12px; }
        .object-id { font-size: 12px; }
        .object-row-meta { font-size: 10px; line-height: 1.55; }
        .explorer-footer { padding: 13px 15px; font-size: 10px; }
        .inspector-body { padding: 15px; overscroll-behavior: contain; }
        .metric-box { padding: 13px; }
        .metric-value { font-size: 17px; }
        .property-row { padding: 10px 11px; }
        .property-key { font-size: 10px; }
        .property-value { font-size: 10.5px; }
        .inspector-tabs { padding: 9px; }
        .inspector-tab { min-height: 38px; font-size: 11px; }
        .small-action { min-height: 35px; font-size: 10px; }
        .section-label { font-size: 10px; }
        .section-help { font-size: 9px; }
        .method-note, .warning-note, .info-note { font-size: 10.5px; }

        /* Compact toolbars with predictable width and separated GPS. */
        .map-toolbar { top: 13px; left: 13px; right: 13px; gap: 9px; }
        .toolbar-left { flex: 1 1 auto; min-width: 0; flex-wrap: nowrap; }
        .toolbar-right { flex: 0 0 auto; gap: 7px; }
        .tool-group { min-height: 43px; gap: 3px; padding: 5px; border-radius: 12px; }
        .map-tool { min-height: 33px; height: 33px; padding: 0 8px; font-size: 10.5px; }
        .tool-label-short { display: none; }
        .opacity-panel { z-index: 110; }
        .map-statusbar { justify-content: space-between; }
        .statusbar-item { font-size: 10px; }
        .map-summary { bottom: 47px; }
        .summary-item { padding: 7px 10px; }
        .summary-value { font-size: 14px; }
        .ol-zoom { top: 72px !important; }
        .ol-zoom button { width: 32px !important; height: 32px !important; }

        /* GPS visually independent from map layers. */
        .field-gps {
            top: auto;
            bottom: 52px;
            right: 14px;
            width: min(254px, calc(100% - 28px));
            gap: 8px;
        }
        .gps-button { min-height: 43px; min-width: 43px; font-size: 11px; padding: 0 11px; }
        .gps-status { display: none; border-radius: 13px; padding: 11px 12px; font-size: 10.5px; }
        .gps-status.is-visible { display: grid; gap: 3px; }
        .gps-status-text { font-size: 10.5px; }
        .gps-metrics { font-size: 10px; }
        .gps-field-note { font-size: 9.5px; line-height: 1.5; }
        .field-selected { pointer-events: auto; }

        @media (min-width: 1600px) {
            :root { --left-w: 298px; --right-w: 354px; }
            .map-toolbar { left: 18px; right: 18px; top: 16px; }
            .panel-heading { padding: 19px; }
            .inspector-body { padding: 18px; }
        }
        @media (min-width: 1280px) and (max-width: 1599px) {
            :root { --left-w: 270px; --right-w: 312px; }
            .map-toolbar { left: 10px; right: 10px; top: 10px; }
            .tool-label { display: none; }
            .tool-label-short { display: inline; }
            .map-tool { padding: 0 7px; font-size: 10px; }
            .toolbar-left .tool-group { flex: 0 1 auto; min-width: 0; }
            .toolbar-right .tool-group { flex: 0 0 auto; }
            .toolbar-right .map-tool .tool-label-short { display: none; }
            .toolbar-right .map-tool { width: 33px; padding: 0; }
        }
        @media (min-width: 1280px) and (max-width: 1349px) {
            :root { --left-w: 259px; --right-w: 298px; }
            .toolbar-left .tool-group { gap: 1px; }
            .toolbar-left .map-tool { padding: 0 5px; }
        }

        /* Laptop compacto/tablet: priorizar el mapa sobre paneles estrechos. */
        @media (max-width: 1279px) {
            .workspace { grid-template-columns: minmax(0, 1fr); }
            .side-panel { z-index: 125; box-shadow: 0 16px 56px rgba(0,0,0,.36); }
            .left-panel, .right-panel { width: min(370px, calc(100vw - 34px)); }
            .left-panel { border-right-color: var(--border-strong); }
            .right-panel { border-left-color: var(--border-strong); }
            .drawer-backdrop.active { display: block; z-index: 115; background: rgba(2,10,6,.56); }
            .panel-close { width: 36px; height: 36px; font-size: 20px; }
            .mobile-panel-button { display: inline-flex; }
            .context-path, .breadcrumb-divider { display: none; }
            .topbar-right { margin-left: auto; }
            .field-gps { bottom: 54px; }
            .map-toolbar { left: 11px; right: 11px; }
            .tool-label { display: none; }
            .tool-label-short { display: inline; }
            .toolbar-left { overflow: hidden; }
            .toolbar-left .tool-group { flex-wrap: nowrap; }
            .toolbar-right .tool-label-short { display: none; }
        }
        @media (min-width: 761px) and (max-width: 1279px) {
            .topbar .top-pill.version-pill { display: none; }
            .topbar .top-action { min-width: 40px; }
            .map-toolbar { max-width: calc(100% - 22px); }
            .toolbar-left { flex: 0 1 auto; }
            .toolbar-right { margin-left: auto; }
            .side-panel { max-height: 100%; }
        }

        /* Teléfono: mapa amplio + barra de capas clara + ficha inferior. */
        @media (max-width: 760px) {
            :root { --topbar-h: 56px; }
            .topbar {
                gap: 7px;
                padding-left: max(9px, env(safe-area-inset-left, 0px));
                padding-right: max(9px, env(safe-area-inset-right, 0px));
            }
            .topbar-left { gap: 6px; }
            .topbar-right { gap: 6px; }
            .brand-mark { width: 32px; height: 32px; flex-basis: 32px; border-radius: 9px; }
            .brand-mark svg { width: 19px; height: 19px; }
            .brand-name { font-size: 11.5px; }
            .brand-mode { display: none; }
            .top-pill, .json-action { display: none; }
            .top-action { width: 38px; height: 38px; min-height: 38px; padding: 0; border-radius: 10px; }
            .top-action svg { width: 17px; height: 17px; }
            .top-action .action-text { display: none; }
            .topbar .top-action:last-child { background: rgba(255,255,255,.055); }

            .workspace {
                height: calc(100dvh - var(--topbar-h) - env(safe-area-inset-top, 0px));
            }
            #map { bottom: calc(36px + env(safe-area-inset-bottom, 0px)); }
            .map-toolbar { top: 8px; left: 8px; right: 8px; display: block; pointer-events: none; }
            .toolbar-left { display: flex; width: 100%; overflow: visible; pointer-events: none; }
            .toolbar-left > .tool-group {
                pointer-events: auto;
                display: flex;
                width: 100%;
                padding: 5px;
                justify-content: space-between;
                gap: 2px;
                border: 1px solid rgba(201,229,211,.24);
                border-radius: 13px;
                background: rgba(11,23,16,.96);
            }
            .toolbar-left .map-tool {
                min-width: 0;
                flex: 1 1 0;
                padding: 0 5px;
                height: 35px;
                min-height: 35px;
                border-radius: 9px;
                font-size: 10px;
            }
            .toolbar-left .tool-label-short { display: inline; }
            .toolbar-left .layer-dot { width: 6px; height: 6px; flex: 0 0 auto; }
            .toolbar-right {
                position: absolute;
                top: 55px;
                right: 0;
                display: flex;
                justify-content: flex-end;
                pointer-events: auto;
                gap: 6px;
            }
            .toolbar-right > .tool-group:last-child { display: inline-flex; }
            .toolbar-right .map-tool { height: 35px; min-height: 35px; min-width: 36px; width: 36px; }
            .toolbar-right .map-tool .tool-label, .toolbar-right .map-tool .tool-label-short { display: none; }
            .toolbar-right .tool-group { padding: 4px; min-height: 43px; border-radius: 11px; }
            .opacity-panel {
                top: calc(100% + 8px);
                right: 0;
                width: min(260px, calc(100vw - 20px));
                padding: 15px;
            }
            .ol-zoom { display: none !important; }
            .ol-scale-line { display: none !important; }
            .ol-attribution { bottom: calc(42px + env(safe-area-inset-bottom, 0px)) !important; }
            .map-summary { display: none; }
            .map-tooltip { display: none !important; }

            .field-gps {
                top: auto;
                right: max(10px, env(safe-area-inset-right, 0px));
                bottom: calc(52px + env(safe-area-inset-bottom, 0px));
                width: min(226px, calc(100% - 20px));
                display: flex;
                flex-direction: column;
                align-items: flex-end;
                gap: 6px;
            }
            body.has-field-selection .field-gps {
                bottom: calc(119px + env(safe-area-inset-bottom, 0px));
            }
            .field-gps-actions { gap: 7px; }
            .gps-button { width: 45px; min-width: 45px; height: 45px; min-height: 45px; padding: 0; border-radius: 13px; }
            .gps-button.primary #gps-toggle-text { display: none; }
            .gps-status.is-visible {
                min-width: 0;
                width: auto;
                display: flex;
                align-items: center;
                justify-content: center;
                gap: 8px;
                padding: 8px 11px;
                background: rgba(13,22,18,.96);
                border-radius: 10px;
                font-size: 10px;
            }
            .gps-status-title, .gps-status-text, .gps-field-note, .gps-target { display: none !important; }
            .gps-status-top { gap: 0; }
            .gps-metrics { margin: 0; gap: 0; }
            .gps-metrics span:nth-child(2) { display: none; }
            .gps-status-indicator { white-space: nowrap; }

            .field-selected:not([hidden]) {
                bottom: calc(44px + env(safe-area-inset-bottom, 0px));
                left: 9px; right: 9px;
                padding: 11px 12px;
                min-height: 59px;
                border-radius: 14px;
                gap: 8px;
            }
            .field-selected-title { font-size: 12px; }
            .field-selected-sub { font-size: 10px; line-height: 1.3; }
            .field-selected .gps-button { width: auto; min-width: 0; height: 38px; min-height: 38px; padding: 0 13px; font-size: 11px; }

            .map-statusbar {
                height: calc(36px + env(safe-area-inset-bottom, 0px));
                padding: 0 max(7px, env(safe-area-inset-right, 0px)) env(safe-area-inset-bottom, 0px) max(7px, env(safe-area-inset-left, 0px));
                justify-content: space-between;
                overflow: hidden;
            }
            .statusbar-item { min-height: 35px; padding: 0 7px; font-size: 10px; border-right: 0; }
            .statusbar-item:nth-child(2), .statusbar-item:nth-child(3),
            .statusbar-item:nth-child(4), .statusbar-item:nth-child(5) { display: none; }
            .map-loading, .map-error { inset: 0 0 calc(36px + env(safe-area-inset-bottom, 0px)); }

            .left-panel {
                width: min(370px, calc(100vw - 27px));
                padding-left: env(safe-area-inset-left, 0px);
                background: #111c16;
            }
            .right-panel {
                top: auto; left: 0; right: 0; bottom: 0;
                width: 100%;
                height: min(76dvh, 700px);
                max-height: calc(100% - 25px);
                padding-bottom: env(safe-area-inset-bottom, 0px);
                border-radius: 21px 21px 0 0;
                background: #111c16;
                border-top: 1px solid rgba(206,236,218,.26);
                border-left: 0;
                transform: translateY(105%);
                overscroll-behavior-y: contain;
            }
            .right-panel.open { transform: translateY(0); }
            .right-panel .panel-heading { padding: 12px 17px 13px; }
            .right-panel .panel-heading::before { margin: -2px auto 13px; width: 44px; background: #718778; }
            .right-panel .panel-description { display: none; }
            .right-panel .inspector-body { padding: 14px 15px; }
            .panel-title { font-size: 16px; }
            .panel-heading { padding: 16px; }
            .panel-description { font-size: 11px; }
            .filter-chip { min-height: 38px; }
            .object-row { min-height: 65px; }
            .drawer-backdrop.active { background: rgba(0,9,4,.58); }
        }
        @media (max-width: 370px) {
            .brand-mark { width: 30px; height: 30px; flex-basis: 30px; }
            .brand-name { font-size: 11px; }
            .toolbar-left .map-tool { gap: 4px; font-size: 9px; padding: 0 3px; }
            .toolbar-right { top: 54px; }
        }
        @media (max-width: 760px) and (max-height: 610px) {
            .right-panel { height: min(87dvh, 550px); }
            .field-gps { bottom: calc(44px + env(safe-area-inset-bottom, 0px)); }
            body.has-field-selection .field-gps { bottom: calc(115px + env(safe-area-inset-bottom, 0px)); }
            .toolbar-right { top: 54px; }
        }
        @media (prefers-reduced-motion: reduce) {
            .side-panel, .map-tool, .object-row, .gps-button { transition: none !important; }
        }

    </style>
</head>

<body>

@php
    $summary = $job->summary ?? [];
    $models = $job->models ?? [];
    $parameters = $job->parameters ?? [];

    $pipelineVersion =
        data_get($parameters, 'pipeline_version')
        ?? data_get($models, 'pipeline_version')
        ?? 'V0.7E';

    $scientificMethodVersion =
        data_get($parameters, 'scientific_method_version')
        ?? data_get($models, 'scientific_method_version')
        ?? 'V0.6G';

    $statusLabel = match($job->status) {
        'completed' => 'Completado',
        'processing' => 'Procesando',
        'queued' => 'En cola',
        'failed' => 'Incidencia',
        default => ucfirst((string) $job->status),
    };
@endphp

<header class="topbar">
    <div class="topbar-left">
        <div class="brand-mark" aria-hidden="true">
            <svg viewBox="0 0 24 24" fill="none">
                <path d="M12 2.5 6.2 8h3.1L5 12.2h4L4.8 17h5.7v4.5h3V17h5.7L15 12.2h4L14.7 8h3.1L12 2.5Z" fill="#BDE8D0"/>
                <path d="M3 18.5h18" stroke="#68C997" stroke-width="1.4" stroke-linecap="round" opacity=".9"/>
            </svg>
        </div>

        <div class="brand-copy">
            <div class="brand-name">UAV Forest AI</div>
            <div class="brand-mode">Visor GIS de resultados</div>
        </div>

        <span class="breadcrumb-divider"></span>

        <nav class="context-path" aria-label="Ruta de navegación">
            <strong>{{ $mosaic->project->name ?? 'Proyecto' }}</strong>
            <span class="context-chevron">›</span>
            <strong>{{ $mosaic->original_name ?? $mosaic->uuid }}</strong>
            <span class="context-chevron">›</span>
            <span>Análisis</span>
            <span class="context-chevron">›</span>
            <span>Mapa</span>
        </nav>
    </div>

    <div class="topbar-right">
        <span class="top-pill version-pill">
            Método {{ $scientificMethodVersion }} · Pipeline {{ $pipelineVersion }}
        </span>

        <span class="top-pill">
            <span class="status-dot"></span>
            {{ $statusLabel }}
        </span>

        <button
            id="open-explorer-mobile"
            aria-label="Abrir catálogo de objetos operativos"
            class="top-action mobile-panel-button"
            type="button"
            title="Abrir explorador de objetos"
        >
            <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.8">
                <path d="M4 6h16M4 12h16M4 18h10" stroke-linecap="round"/>
            </svg>
            <span class="action-text">Objetos</span>
        </button>

        <button
            id="open-inspector-mobile"
            aria-label="Abrir información del objeto"
            class="top-action mobile-panel-button"
            type="button"
            title="Abrir inspector"
        >
            <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.8">
                <circle cx="12" cy="12" r="9"/>
                <path d="M12 10v6M12 7h.01" stroke-linecap="round"/>
            </svg>
            <span class="action-text">Inspector</span>
        </button>

        <a
            class="top-action json-action"
            href="{{ route('analysis.jobs.show', ['analysisUuid' => $job->uuid]) }}"
        >
            <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.8">
                <path d="m8 8-4 4 4 4M16 8l4 4-4 4M14 5l-4 14" stroke-linecap="round" stroke-linejoin="round"/>
            </svg>
            JSON técnico
        </a>

        <a
            class="top-action"
            href="{{ route('analysis.index', ['mosaic' => $mosaic->uuid]) }}"
        >
            <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.8">
                <path d="M15 18 9 12l6-6" stroke-linecap="round" stroke-linejoin="round"/>
            </svg>
            <span class="action-text">Volver al análisis</span>
        </a>
    </div>
</header>

<div id="workspace" class="workspace">

    <div id="drawer-backdrop" class="drawer-backdrop"></div>

    {{-- =========================================================
         EXPLORADOR DE OBJETOS
    ========================================================== --}}

    <aside id="left-panel" class="side-panel left-panel">
        <div class="panel-heading">
            <div class="panel-heading-row">
                <div>
                    <div class="panel-kicker">Explorador</div>
                    <h2 class="panel-title">Objetos operativos</h2>
                </div>

                <div style="display:flex; align-items:center; gap:7px;">
                    <span id="explorer-count" class="panel-count">0</span>
                    <button id="close-explorer-mobile" class="panel-close" type="button">×</button>
                </div>
            </div>

            <p class="panel-description">
                Busca y filtra geometrías consolidadas sin alterar el resultado científico del análisis.
            </p>
        </div>

        <div class="explorer-controls">
            <div class="search-box">
                <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.8">
                    <circle cx="11" cy="11" r="7"/>
                    <path d="m20 20-3.4-3.4" stroke-linecap="round"/>
                </svg>
                <input
                    id="object-search"
                    class="search-input"
                    type="search"
                    placeholder="Buscar Operational ID..."
                    autocomplete="off"
                >
            </div>

            <div class="quick-filters" role="group" aria-label="Filtros rápidos">
                <button class="filter-chip active" type="button" data-filter="all">Todos</button>
                <button class="filter-chip" type="button" data-filter="mrcnn">Mask R-CNN</button>
                <button class="filter-chip" type="button" data-filter="yolo">YOLO</button>
                <button class="filter-chip" type="button" data-filter="review">Revisión</button>
                <button class="filter-chip" type="button" data-filter="no-review">Sin revisión</button>
            </div>

            <div class="advanced-filters">
                <select id="evidence-filter" class="compact-select" aria-label="Filtrar por evidencia">
                    <option value="">Toda evidencia</option>
                </select>

                <select id="conflict-filter" class="compact-select" aria-label="Filtrar por conflicto">
                    <option value="">Todo conflicto</option>
                </select>
            </div>

            <div class="filter-feedback">
                <span id="filter-feedback">Cargando objetos...</span>
                <button id="reset-filters" class="filter-reset" type="button">Restablecer</button>
            </div>
        </div>

        <div id="object-list" class="object-list">
            <div class="object-list-empty">Cargando catálogo espacial...</div>
        </div>

        <div class="explorer-footer">
            Los filtros afectan únicamente la visualización y exploración web. No modifican el GeoJSON ni el resultado persistente del pipeline.
        </div>
    </aside>

    {{-- =========================================================
         MAPA
    ========================================================== --}}

    <main class="map-pane">
        <div id="map"></div>

        <div class="map-toolbar">
            <div class="toolbar-left">
                <div class="tool-group">
                    @if(($cogAvailable ?? false) || $previewAvailable)
                        <button id="toggle-preview" class="map-tool active" type="button" aria-pressed="true">
                            <span class="layer-dot preview"></span>
                            <span class="tool-label">Ortomosaico</span><span class="tool-label-short">Base</span>
                        </button>
                    @endif

                    <button id="toggle-mrcnn" class="map-tool active" type="button" aria-pressed="true">
                        <span class="layer-dot mrcnn"></span>
                        <span class="tool-label">Mask R-CNN</span><span class="tool-label-short">Mask</span>
                    </button>

                    <button id="toggle-yolo" class="map-tool active" type="button" aria-pressed="true">
                        <span class="layer-dot yolo"></span>
                        <span class="tool-label">YOLO fallback</span><span class="tool-label-short">YOLO</span>
                    </button>

                    <button id="toggle-review-highlight" class="map-tool review-active" type="button" aria-pressed="true">
                        <span class="layer-dot review"></span>
                        <span class="tool-label">Resaltar revisión</span><span class="tool-label-short">Revisión</span>
                    </button>
                </div>
            </div>

            <div class="toolbar-right">
                <div id="opacity-popover" class="tool-group opacity-popover">
                    <button id="opacity-toggle" class="map-tool" type="button" title="Ajustar opacidad" aria-label="Ajustar opacidad de las capas" aria-expanded="false">
                        <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.8">
                            <path d="M12 3s6 6.2 6 11a6 6 0 1 1-12 0c0-4.8 6-11 6-11Z"/>
                        </svg>
                        <span class="tool-label">Opacidad</span><span class="tool-label-short">Opacidad</span>
                    </button>

                    <div class="opacity-panel">
                        @if(($cogAvailable ?? false) || $previewAvailable)
                            <div class="opacity-row">
                                <div class="opacity-row-head">
                                    <span>Ortomosaico</span>
                                    <strong id="preview-opacity-value">100%</strong>
                                </div>
                                <input id="preview-opacity" type="range" min="0" max="100" value="100">
                            </div>
                        @endif

                        <div class="opacity-row">
                            <div class="opacity-row-head">
                                <span>Segmentaciones</span>
                                <strong id="seg-opacity-value">82%</strong>
                            </div>
                            <input id="seg-opacity" type="range" min="15" max="100" value="82">
                        </div>
                    </div>
                </div>

                <div class="tool-group">
                    <button id="fit-extent" class="map-tool" type="button" title="Ajustar al ortomosaico">
                        <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.8">
                            <path d="M8 3H3v5M16 3h5v5M8 21H3v-5M16 21h5v-5" stroke-linecap="round" stroke-linejoin="round"/>
                        </svg>
                        <span class="tool-label">Extensión</span>
                    </button>

                    <button id="clear-selection" class="map-tool" type="button" title="Limpiar selección">
                        <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.8">
                            <path d="m6 6 12 12M18 6 6 18" stroke-linecap="round"/>
                        </svg>
                        <span class="tool-label">Limpiar</span>
                    </button>

                    <button id="fullscreen-map" class="map-tool" type="button" title="Pantalla completa">
                        <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.8">
                            <path d="M8 3H3v5M16 3h5v5M8 21H3v-5M16 21h5v-5" stroke-linecap="round" stroke-linejoin="round"/>
                        </svg>
                        <span class="tool-label">Pantalla completa</span>
                    </button>
                </div>
            </div>
        </div>


        {{-- WEB.6 — GPS local: el navegador solicita permiso al pulsar el botón. --}}
        <section class="field-gps" aria-label="Navegación GPS del trabajo de campo">
            <div class="field-gps-actions">
                <button id="gps-toggle" class="gps-button primary" aria-pressed="false" type="button" aria-label="Iniciar mi ubicación en tiempo real" title="Activar / detener GPS">
                    <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.8" aria-hidden="true">
                        <circle cx="12" cy="12" r="7"/><circle cx="12" cy="12" r="2"/>
                        <path d="M12 1v4M12 19v4M1 12h4M19 12h4" stroke-linecap="round"/>
                    </svg>
                    <span id="gps-toggle-text">Mi ubicación</span>
                </button>
                <button id="gps-follow" class="gps-button" type="button" title="Seguir mi posición" aria-label="Centrar y seguir GPS" aria-pressed="false" disabled>
                    <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.8" aria-hidden="true"><path d="m4 20 7-16 2 7 7 2-16 7Z" stroke-linejoin="round"/></svg>
                </button>
            </div>
            <div class="gps-status" role="status" aria-live="polite">
                <div class="gps-status-top">
                    <span class="gps-status-title">Navegación de campo</span>
                    <span id="gps-indicator" class="gps-status-indicator" data-state="idle">APAGADO</span>
                </div>
                <div id="gps-status-text" class="gps-status-text">Activa la ubicación para orientarte sobre el ortomosaico.</div>
                <div class="gps-metrics">
                    <span>Precisión: <strong id="gps-accuracy">—</strong></span>
                    <span>Actualización: <strong id="gps-age">—</strong></span>
                </div>
                <div id="gps-target" class="gps-target" hidden>Selecciona una copa para consultar la distancia aproximada.</div>
                <div class="gps-field-note">GPS orientativo, no confirma la identidad del árbol.</div>
            </div>
        </section>

        <div id="field-selected" class="field-selected" hidden>
            <div class="field-selected-meta">
                <div id="field-selected-name" class="field-selected-title">Objeto</div>
                <div id="field-selected-distance" class="field-selected-sub">Distancia pendiente de GPS</div>
            </div>
            <button type="button" id="field-show-inspector" class="gps-button">Ver ficha</button>
        </div>

        <div class="map-summary" aria-label="Resumen de objetos visibles">
            <div class="summary-item">
                <div class="summary-label">Visibles</div>
                <div id="visible-count" class="summary-value">0</div>
            </div>
            <div class="summary-item">
                <div class="summary-label">Mask</div>
                <div id="visible-mrcnn-count" class="summary-value">0</div>
            </div>
            <div class="summary-item">
                <div class="summary-label">YOLO</div>
                <div id="visible-yolo-count" class="summary-value">0</div>
            </div>
            <div class="summary-item">
                <div class="summary-label">Revisión</div>
                <div id="visible-review-count" class="summary-value">0</div>
            </div>
        </div>

        <div id="map-tooltip" class="map-tooltip"></div>

        <div id="map-loading" class="map-loading">
            <div class="loading-card">
                <div class="loading-spinner"></div>
                <div class="loading-title">Cargando resultados espaciales</div>
                <div class="loading-text">Preparando las geometrías operativas del análisis para visualización.</div>
            </div>
        </div>

        <div id="map-error" class="map-error" hidden>
            <div class="error-card">
                <div class="error-title">No fue posible cargar la capa de objetos</div>
                <div id="map-error-text" class="error-text">
                    El ortomosaico puede seguir disponible, pero la capa GeoJSON no pudo cargarse.
                </div>
                <button id="retry-geojson" class="error-action" type="button">Reintentar carga</button>
            </div>
        </div>

        <div class="map-statusbar">
            <span class="statusbar-item"><strong>CRS</strong> {{ $mosaic->crs ?? '—' }}</span>
            <span class="statusbar-item"><strong>X</strong> <span id="coord-x">—</span></span>
            <span class="statusbar-item"><strong>Y</strong> <span id="coord-y">—</span></span>
            <span class="statusbar-item"><strong>Escala aprox.</strong> <span id="map-scale">—</span></span>
            <span class="statusbar-item"><strong>Resolución</strong> <span id="map-resolution">—</span></span>
            <span class="statusbar-item"><strong>Objetos visibles</strong> <span id="status-visible-count">0</span></span>
        </div>
    </main>

    {{-- =========================================================
         INSPECTOR
    ========================================================== --}}

    <aside id="right-panel" class="side-panel right-panel">
        <div class="panel-heading">
            <div class="panel-heading-row">
                <div>
                    <div class="panel-kicker">Inspector</div>
                    <h2 class="panel-title">Lectura científica</h2>
                </div>

                <button id="close-inspector-mobile" class="panel-close" type="button">×</button>
            </div>

            <p class="panel-description">
                Examina geometría, evidencia intermodelo, estabilidad y diagnóstico operativo del objeto seleccionado.
            </p>
        </div>

        <div class="inspector-tabs">
            <button class="inspector-tab active" type="button" data-inspector-tab="object">Objeto</button>
            <button class="inspector-tab" type="button" data-inspector-tab="analysis">Análisis</button>
        </div>

        <div id="inspector-object" class="inspector-body">
            <div class="inspector-empty">
                <strong style="color:var(--text-soft);">Ningún objeto seleccionado.</strong><br><br>
                Haz clic sobre una geometría del mapa o selecciona un registro del explorador. La ficha mostrará atributos del objeto operativo; no implica confirmación contra verdad de campo.
            </div>
        </div>

        <div id="inspector-analysis" class="inspector-body" hidden>
            <div class="section-block">
                <div class="section-label">Trazabilidad del pipeline</div>

                <div class="analysis-flow">
                    <div class="flow-step">
                        <div class="flow-value">{{ data_get($summary, 'raw_predictions', 0) }}</div>
                        <div class="flow-label">Predicciones RAW</div>
                    </div>
                    <div class="flow-step">
                        <div class="flow-value">{{ data_get($summary, 'unique_predictions', 0) }}</div>
                        <div class="flow-label">Candidatos únicos</div>
                    </div>
                    <div class="flow-step">
                        <div class="flow-value">{{ data_get($summary, 'catalog_groups', 0) }}</div>
                        <div class="flow-label">Grupos evidencia</div>
                    </div>
                    <div class="flow-step">
                        <div class="flow-value">{{ data_get($summary, 'primary_objects', 0) }}</div>
                        <div class="flow-label">Objetos operativos</div>
                    </div>
                </div>
            </div>

            <div class="section-block">
                <div class="section-label">Composición operacional</div>
                <div class="analysis-kpis">
                    <div class="analysis-kpi">
                        <div class="analysis-kpi-label">Mask R-CNN primario</div>
                        <div class="analysis-kpi-value" style="color:#aeeac8;">{{ data_get($summary, 'primary_maskrcnn', 0) }}</div>
                    </div>
                    <div class="analysis-kpi">
                        <div class="analysis-kpi-label">YOLO fallback</div>
                        <div class="analysis-kpi-value" style="color:#ddc3ff;">{{ data_get($summary, 'primary_yolo_fallback', 0) }}</div>
                    </div>
                    <div class="analysis-kpi">
                        <div class="analysis-kpi-label">Requieren revisión</div>
                        <div class="analysis-kpi-value" style="color:#f7cf8c;">{{ data_get($summary, 'requires_review', 0) }}</div>
                    </div>
                    <div class="analysis-kpi">
                        <div class="analysis-kpi-label">Matches primarios</div>
                        <div class="analysis-kpi-value">{{ data_get($summary, 'primary_matches', 0) }}</div>
                    </div>
                </div>
            </div>

            <div class="section-block">
                <div class="section-label">Contexto técnico</div>
                <div class="property-list">
                    <div class="property-row">
                        <div class="property-key">Proyecto</div>
                        <div class="property-value">{{ $mosaic->project->name ?? '—' }}</div>
                    </div>
                    <div class="property-row">
                        <div class="property-key">Ortomosaico</div>
                        <div class="property-value">{{ $mosaic->original_name ?? $mosaic->uuid }}</div>
                    </div>
                    <div class="property-row">
                        <div class="property-key">CRS</div>
                        <div class="property-value">{{ $mosaic->crs ?? '—' }}</div>
                    </div>
                    <div class="property-row">
                        <div class="property-key">Método científico</div>
                        <div class="property-value">{{ $scientificMethodVersion }}</div>
                    </div>
                    <div class="property-row">
                        <div class="property-key">Pipeline</div>
                        <div class="property-value">{{ $pipelineVersion }}</div>
                    </div>
                    <div class="property-row">
                        <div class="property-key">Analysis UUID</div>
                        <div class="property-value">{{ $job->uuid }}</div>
                    </div>
                </div>
            </div>

            <div class="section-block">
                <div class="section-label">Alcance del visor</div>
                <div class="info-note">
                    Esta pantalla es una herramienta de inspección espacial y QA/QC. Los objetos mostrados son geometrías operativas producidas por el pipeline y todavía no constituyen un conteo validado de individuos arbóreos mediante verdad de campo.
                </div>
            </div>

            <div class="section-block">
                <div class="section-label">Interpretación metodológica</div>
                <div class="method-note">
                    Mask R-CNN se representa como fuente geométrica primaria provisional cuando el pipeline la conserva. YOLO-Seg aparece como evidencia secundaria o fallback de acuerdo con la regla de consolidación ya ejecutada. El visor no modifica esas decisiones.
                </div>
            </div>
        </div>
    </aside>
</div>

<script src="https://cdn.jsdelivr.net/npm/proj4@2.15.0/dist/proj4.js"></script>
<script src="https://cdn.jsdelivr.net/npm/geotiff@3.1.0-beta.0/dist-browser/geotiff.js"></script>
<script src="https://cdn.jsdelivr.net/npm/ol@10.10.0/dist/ol.js"></script>

<script>
(() => {
    'use strict';

    const mapBounds = @json($mapBounds);
    const mosaicProjection = @json($mosaic->crs);
    const previewAvailable = @json($previewAvailable);
    const cogAvailable = @json($cogAvailable ?? false);

    const geojsonUrl = @json(
        route('analysis.map.geojson', ['analysisUuid' => $job->uuid])
    );

    const previewUrl = previewAvailable
        ? @json(route('analysis.map.preview', ['analysisUuid' => $job->uuid]))
        : null;

    const cogUrlEndpoint = cogAvailable
        ? @json(route('analysis.map.cog-url', ['analysisUuid' => $job->uuid]))
        : null;

    const summaryExpected = {
        total: Number(@json(data_get($summary, 'primary_objects', 0))),
        mrcnn: Number(@json(data_get($summary, 'primary_maskrcnn', 0))),
        yolo: Number(@json(data_get($summary, 'primary_yolo_fallback', 0))),
        review: Number(@json(data_get($summary, 'requires_review', 0))),
    };

    const els = {
        workspace: document.getElementById('workspace'),
        leftPanel: document.getElementById('left-panel'),
        rightPanel: document.getElementById('right-panel'),
        drawerBackdrop: document.getElementById('drawer-backdrop'),
        openExplorerMobile: document.getElementById('open-explorer-mobile'),
        openInspectorMobile: document.getElementById('open-inspector-mobile'),
        closeExplorerMobile: document.getElementById('close-explorer-mobile'),
        closeInspectorMobile: document.getElementById('close-inspector-mobile'),

        search: document.getElementById('object-search'),
        quickFilters: Array.from(document.querySelectorAll('.filter-chip')),
        evidenceFilter: document.getElementById('evidence-filter'),
        conflictFilter: document.getElementById('conflict-filter'),
        resetFilters: document.getElementById('reset-filters'),
        filterFeedback: document.getElementById('filter-feedback'),
        objectList: document.getElementById('object-list'),
        explorerCount: document.getElementById('explorer-count'),

        togglePreview: document.getElementById('toggle-preview'),
        toggleMrcnn: document.getElementById('toggle-mrcnn'),
        toggleYolo: document.getElementById('toggle-yolo'),
        toggleReviewHighlight: document.getElementById('toggle-review-highlight'),
        opacityPopover: document.getElementById('opacity-popover'),
        opacityToggle: document.getElementById('opacity-toggle'),
        previewOpacity: document.getElementById('preview-opacity'),
        previewOpacityValue: document.getElementById('preview-opacity-value'),
        segOpacity: document.getElementById('seg-opacity'),
        segOpacityValue: document.getElementById('seg-opacity-value'),
        fitExtent: document.getElementById('fit-extent'),
        clearSelection: document.getElementById('clear-selection'),
        fullscreenMap: document.getElementById('fullscreen-map'),
        gpsToggle: document.getElementById('gps-toggle'),
        gpsToggleText: document.getElementById('gps-toggle-text'),
        gpsFollow: document.getElementById('gps-follow'),
        gpsIndicator: document.getElementById('gps-indicator'),
        gpsStatusText: document.getElementById('gps-status-text'),
        gpsAccuracy: document.getElementById('gps-accuracy'),
        gpsAge: document.getElementById('gps-age'),
        gpsTarget: document.getElementById('gps-target'),
        fieldSelected: document.getElementById('field-selected'),
        fieldSelectedName: document.getElementById('field-selected-name'),
        fieldSelectedDistance: document.getElementById('field-selected-distance'),
        fieldShowInspector: document.getElementById('field-show-inspector'),

        visibleCount: document.getElementById('visible-count'),
        visibleMrcnnCount: document.getElementById('visible-mrcnn-count'),
        visibleYoloCount: document.getElementById('visible-yolo-count'),
        visibleReviewCount: document.getElementById('visible-review-count'),
        statusVisibleCount: document.getElementById('status-visible-count'),

        coordX: document.getElementById('coord-x'),
        coordY: document.getElementById('coord-y'),
        mapScale: document.getElementById('map-scale'),
        mapResolution: document.getElementById('map-resolution'),
        tooltip: document.getElementById('map-tooltip'),

        mapLoading: document.getElementById('map-loading'),
        mapError: document.getElementById('map-error'),
        mapErrorText: document.getElementById('map-error-text'),
        retryGeojson: document.getElementById('retry-geojson'),

        inspectorTabs: Array.from(document.querySelectorAll('.inspector-tab')),
        inspectorObject: document.getElementById('inspector-object'),
        inspectorAnalysis: document.getElementById('inspector-analysis'),
    };

    let allFeatures = [];
    let filteredFeatures = [];
    let activeQuickFilter = 'all';
    let currentSegOpacity = Number(els.segOpacity?.value || 82) / 100;
    let highlightReview = true;
    let showMrcnn = true;
    let showYolo = true;
    let selectedFeature = null;
    let selectedFilteredIndex = -1;

    /* =========================================================
       PROYECCIÓN
    ========================================================= */

    const utmMatch = /^EPSG:(326|327)(\d{2})$/.exec(mosaicProjection || '');

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

    /* =========================================================
       HELPERS DE DATOS
    ========================================================= */

    function toBool(value) {
        return (
            value === true
            || value === 1
            || value === '1'
            || String(value).toLowerCase() === 'true'
        );
    }

    function cleanText(value) {
        if (value === null || value === undefined) {
            return '';
        }

        return String(value).trim();
    }

    function getPrimaryModel(feature) {
        return cleanText(feature.get('primary_model'));
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

    function getOperationalId(feature) {
        return cleanText(feature.get('operational_id')) || 'Objeto operativo';
    }

    function getEvidenceStatus(feature) {
        return cleanText(feature.get('evidence_status'));
    }

    function getConflictRole(feature) {
        return cleanText(feature.get('conflict_role'));
    }

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

    function formatMetric(value, unit = '', digits = 3) {
        const formatted = formatNumber(value, digits);
        return formatted === '—' ? '—' : `${formatted}${unit ? ` ${unit}` : ''}`;
    }

    function readableValue(value) {
        const text = cleanText(value);
        return text || '—';
    }

    function modelClass(feature) {
        if (isMrcnn(feature)) return 'mrcnn';
        if (isYolo(feature)) return 'yolo';
        return 'unknown';
    }

    function modelLabel(feature) {
        return getPrimaryModel(feature) || 'Modelo no identificado';
    }

    /* =========================================================
       FILTROS
    ========================================================= */

    function passesQuickFilter(feature) {
        switch (activeQuickFilter) {
            case 'mrcnn':
                return isMrcnn(feature);
            case 'yolo':
                return isYolo(feature);
            case 'review':
                return requiresReview(feature);
            case 'no-review':
                return !requiresReview(feature);
            default:
                return true;
        }
    }

    function passesAdvancedFilters(feature) {
        const evidence = els.evidenceFilter?.value || '';
        const conflict = els.conflictFilter?.value || '';

        if (evidence && getEvidenceStatus(feature) !== evidence) {
            return false;
        }

        if (conflict && getConflictRole(feature) !== conflict) {
            return false;
        }

        return true;
    }

    function passesSearch(feature) {
        const query = cleanText(els.search?.value).toLowerCase();

        if (!query) {
            return true;
        }

        const haystack = [
            feature.get('operational_id'),
            feature.get('candidate_id'),
            feature.get('source_cluster'),
            feature.get('candidate_type'),
        ]
            .map(value => cleanText(value).toLowerCase())
            .join(' ');

        return haystack.includes(query);
    }

    function passesLayerVisibility(feature) {
        if (isMrcnn(feature) && !showMrcnn) {
            return false;
        }

        if (isYolo(feature) && !showYolo) {
            return false;
        }

        return true;
    }

    function isFeatureVisible(feature) {
        return (
            passesLayerVisibility(feature)
            && passesQuickFilter(feature)
            && passesAdvancedFilters(feature)
            && passesSearch(feature)
        );
    }

    function populateSelect(select, values, placeholder) {
        const current = select.value;
        select.innerHTML = `<option value="">${escapeHtml(placeholder)}</option>`;

        values.forEach(value => {
            const option = document.createElement('option');
            option.value = value;
            option.textContent = value;
            select.appendChild(option);
        });

        if (values.includes(current)) {
            select.value = current;
        }
    }

    function populateAttributeFilters() {
        const evidenceValues = [...new Set(
            allFeatures
                .map(getEvidenceStatus)
                .filter(Boolean)
        )].sort((a, b) => a.localeCompare(b, 'es'));

        const conflictValues = [...new Set(
            allFeatures
                .map(getConflictRole)
                .filter(Boolean)
        )].sort((a, b) => a.localeCompare(b, 'es'));

        populateSelect(els.evidenceFilter, evidenceValues, 'Toda evidencia');
        populateSelect(els.conflictFilter, conflictValues, 'Todo conflicto');
    }

    function applyFilters() {
        filteredFeatures = allFeatures.filter(isFeatureVisible);

        if (selectedFeature && !isFeatureVisible(selectedFeature)) {
            clearSelection();
        }

        renderObjectList();
        vectorLayer.changed();
        updateVisibleCounters();
    }

    function resetFilters() {
        activeQuickFilter = 'all';

        els.quickFilters.forEach(button => {
            button.classList.toggle('active', button.dataset.filter === 'all');
        });

        if (els.search) els.search.value = '';
        if (els.evidenceFilter) els.evidenceFilter.value = '';
        if (els.conflictFilter) els.conflictFilter.value = '';

        applyFilters();
    }

    /* =========================================================
       CAPAS Y ESTILOS
    ========================================================= */

    // =========================================================
    // V0.8A.WEB.3 - RASTER COG DE ALTA RESOLUCIÓN
    // =========================================================
    // El preview funciona como fallback. Cuando el COG queda
    // listo, OpenLayers usa el raster nativo por bloques/rangos.

    let orthomosaicVisible = true;
    let currentRasterOpacity = Number(els.previewOpacity?.value || 100) / 100;

    const previewLayer = previewAvailable
        ? new ol.layer.Image({
            source: new ol.source.ImageStatic({
                url: previewUrl,
                imageExtent: mapBounds,
                projection: mapProjection,
                interpolate: true,
            }),
            opacity: currentRasterOpacity,
            visible: orthomosaicVisible,
            zIndex: 0,
        })
        : null;

    let cogLayer = null;
    let cogSource = null;
    let cogReady = false;
    let cogFailed = false;

    function syncRasterLayers() {
        if (cogLayer) {
            cogLayer.setOpacity(currentRasterOpacity);
            cogLayer.setVisible(orthomosaicVisible && !cogFailed);
        }

        if (previewLayer) {
            previewLayer.setOpacity(currentRasterOpacity);
            previewLayer.setVisible(
                orthomosaicVisible
                && (!cogReady || cogFailed)
            );
        }
    }

    async function initializeCogLayer() {
        if (!cogAvailable || !cogUrlEndpoint) {
            syncRasterLayers();
            return;
        }

        try {
            if (
                typeof GeoTIFF === 'undefined'
                || !ol?.source?.GeoTIFF
                || !ol?.layer?.WebGLTile
            ) {
                throw new Error(
                    'GeoTIFF/OpenLayers WebGLTile no está disponible en el navegador.'
                );
            }

            const response = await fetch(cogUrlEndpoint, {
                headers: {
                    'Accept': 'application/json',
                },
                credentials: 'same-origin',
                cache: 'no-store',
            });

            if (!response.ok) {
                throw new Error(
                    `No fue posible obtener URL COG (HTTP ${response.status}).`
                );
            }

            const payload = await response.json();

            if (
                payload?.status !== 'ok'
                || payload?.available !== true
                || !payload?.url
            ) {
                throw new Error('Laravel no devolvió una URL COG válida.');
            }

            cogSource = new ol.source.GeoTIFF({
                sources: [
                    {
                        url: payload.url,
                    },
                ],
                projection: mapProjection,
                convertToRGB: 'auto',
                normalize: true,
                interpolate: true,
                wrapX: false,
                transition: 120,
            });

            cogLayer = new ol.layer.WebGLTile({
                source: cogSource,
                opacity: currentRasterOpacity,
                visible: orthomosaicVisible,
                zIndex: 1,
                preload: 0,
            });

            map.addLayer(cogLayer);

            cogSource.on('change', () => {
                const state = cogSource.getState();

                if (state === 'ready') {
                    cogReady = true;
                    cogFailed = false;
                    syncRasterLayers();

                    console.info('COG web listo:', {
                        crs: payload.crs,
                        width: payload.width,
                        height: payload.height,
                        overviews: payload.overviews,
                        expires_at: payload.expires_at,
                    });
                } else if (state === 'error') {
                    cogReady = false;
                    cogFailed = true;
                    syncRasterLayers();
                    console.error(
                        'GeoTIFFSource entró en estado error. Se mantiene el preview como fallback.'
                    );
                }
            });

            syncRasterLayers();
        } catch (error) {
            cogReady = false;
            cogFailed = true;
            syncRasterLayers();

            console.error(
                'No fue posible inicializar el COG. Se usará el preview como fallback.',
                error
            );
        }
    }

    const vectorSource = new ol.source.Vector();

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

    const crownStyleCache = new Map();

    function getFeatureStyle(feature) {
        if (!isFeatureVisible(feature)) {
            return null;
        }

        const review = requiresReview(feature);
        const useReviewStyle = review && highlightReview;
        const styleKey = `${modelClass(feature)}:${useReviewStyle ? 1 : 0}:${Math.round(currentSegOpacity * 100)}`;
        if (crownStyleCache.has(styleKey)) return crownStyleCache.get(styleKey);

        let strokeColor = 'rgba(159, 176, 167, .98)';
        let fillColor = `rgba(159, 176, 167, ${Math.max(currentSegOpacity * .20, .08)})`;

        if (isMrcnn(feature)) {
            strokeColor = 'rgba(53, 199, 123, .98)';
            fillColor = `rgba(53, 199, 123, ${Math.max(currentSegOpacity * .27, .09)})`;
        } else if (isYolo(feature)) {
            strokeColor = 'rgba(165, 107, 244, .98)';
            fillColor = `rgba(165, 107, 244, ${Math.max(currentSegOpacity * .24, .09)})`;
        }

        if (useReviewStyle) {
            strokeColor = 'rgba(242, 173, 61, .98)';
            fillColor = `rgba(242, 173, 61, ${Math.max(currentSegOpacity * .17, .08)})`;
        }

        const baseStyle = new ol.style.Style({
            stroke: createStroke(
                strokeColor,
                useReviewStyle ? 3 : 2.2,
                useReviewStyle ? [9, 6] : null
            ),
            fill: createFill(fillColor),
        });

        if (!useReviewStyle) {
            crownStyleCache.set(styleKey, baseStyle);
            return baseStyle;
        }

        const reviewStyles = [
            baseStyle,
            new ol.style.Style({
                stroke: createStroke('rgba(255, 241, 214, .9)', 1, [2, 6]),
            }),
        ];
        crownStyleCache.set(styleKey, reviewStyles);
        return reviewStyles;
    }

    const vectorLayer = new ol.layer.Vector({
        source: vectorSource,
        style: getFeatureStyle,
        zIndex: 10,
        declutter: true,
    });

    const selectedStyle = [
        new ol.style.Style({
            stroke: createStroke('rgba(255,255,255,1)', 4.4),
            fill: createFill('rgba(255,255,255,.035)'),
        }),
        new ol.style.Style({
            stroke: createStroke('rgba(239,106,98,1)', 2.4),
            fill: createFill('rgba(239,106,98,.09)'),
        }),
    ];

    /* =========================================================
       MAPA
    ========================================================= */

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
                minWidth: 110,
            }),
        ]),
    });

    function fitMap() {
        const compact = window.innerWidth <= 760;
        map.getView().fit(mapBounds, {
            padding: compact ? [66, 18, 76, 18] : [74, 42, 68, 42],
            duration: 260,
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
        filter: feature => isFeatureVisible(feature),
    });

    map.addInteraction(selectInteraction);


    /* =========================================================
       WEB.6 — GEOLOCALIZACIÓN GPS LOCAL
       - Solicitud exclusivamente tras interacción del usuario.
       - Posición WGS84 -> CRS proyectado del mapa.
       - No se envían ni persisten posiciones GPS en el servidor.
       - Coordenada/precisión no constituyen verdad de campo.
    ========================================================= */
    const gpsSource = new ol.source.Vector();
    const gpsAccuracyFeature = new ol.Feature({
        geometry: new ol.geom.Circle([0, 0], 0),
        gps_kind: 'accuracy',
    });
    const gpsPositionFeature = new ol.Feature({
        geometry: new ol.geom.Point([0, 0]),
        gps_kind: 'position',
    });
    gpsSource.addFeatures([gpsAccuracyFeature, gpsPositionFeature]);

    const gpsLayer = new ol.layer.Vector({
        source: gpsSource,
        visible: false,
        zIndex: 40,
        updateWhileAnimating: false,
        updateWhileInteracting: false,
        style: feature => feature.get('gps_kind') === 'accuracy'
            ? new ol.style.Style({
                stroke: new ol.style.Stroke({ color: 'rgba(97,184,255,.75)', width: 1.4 }),
                fill: new ol.style.Fill({ color: 'rgba(69,162,247,.12)' }),
            })
            : [
                new ol.style.Style({
                    image: new ol.style.Circle({
                        radius: 11,
                        fill: new ol.style.Fill({ color: '#ffffff' }),
                        stroke: new ol.style.Stroke({ color: 'rgba(25,50,90,.30)', width: 1.3 }),
                    }),
                }),
                new ol.style.Style({
                    image: new ol.style.Circle({
                        radius: 7,
                        fill: new ol.style.Fill({ color: '#278cf2' }),
                        stroke: new ol.style.Stroke({ color: '#ffffff', width: 1.4 }),
                    }),
                }),
            ],
    });
    map.addLayer(gpsLayer);

    let gpsWatchId = null;
    let gpsActive = false;
    let gpsFollow = false;
    let gpsCoordinate = null;
    let gpsAccuracy = null;
    let gpsTimestamp = 0;
    let gpsHasFix = false;

    function gpsUpdateStatus(message, state = 'idle', label = 'GPS') {
        els.gpsStatusText.textContent = message;
        els.gpsIndicator.textContent = label;
        els.gpsIndicator.dataset.state = state;
    }

    function gpsUpdateButtons() {
        els.gpsToggleText.textContent = gpsActive ? 'Detener GPS' : 'Mi ubicación';
        document.body.classList.toggle('gps-active', gpsActive);
        els.gpsToggle.setAttribute('aria-pressed', gpsActive ? 'true' : 'false');
        els.gpsToggle.setAttribute('aria-label', gpsActive ? 'Detener seguimiento GPS' : 'Iniciar GPS');
        els.gpsFollow.disabled = !gpsHasFix;
        els.gpsFollow.classList.toggle('is-following', gpsActive && gpsFollow);
        els.gpsFollow.setAttribute('aria-pressed', (gpsActive && gpsFollow) ? 'true' : 'false');
        els.gpsFollow.title = gpsFollow ? 'Siguiendo tu posición' : 'Centrar y seguir mi posición';
    }

    function gpsDistanceToSelected() {
        if (!gpsHasFix || !gpsCoordinate || !selectedFeature) {
            els.gpsTarget.hidden = !selectedFeature;
            if (selectedFeature) {
                els.gpsTarget.textContent = 'Distancia: pendiente de ubicación GPS.';
            }
            if (els.fieldSelectedDistance) {
                els.fieldSelectedDistance.textContent = 'Activa GPS para estimar distancia';
            }
            return;
        }

        const geometry = selectedFeature.getGeometry();
        if (!geometry) return;
        const center = ol.extent.getCenter(geometry.getExtent());
        // Ambas coordenadas están en UTM, por lo que la distancia es euclidiana métrica.
        const meters = Math.hypot(center[0] - gpsCoordinate[0], center[1] - gpsCoordinate[1]);
        const label = Number.isFinite(meters) ? `${meters.toFixed(1)} m` : '—';
        els.gpsTarget.hidden = false;
        els.gpsTarget.textContent = `Hasta centro aproximado de ${getOperationalId(selectedFeature)}: ${label} (línea recta).`;
        els.fieldSelectedDistance.textContent = `${label} aprox. · No identifica el fuste`;
        const inspectorDistance = document.getElementById('gps-distance-inspector');
        if (inspectorDistance) inspectorDistance.textContent = `Distancia GPS aproximada al centro geométrico: ${label}. La incertidumbre GNSS puede superar esta distancia.`;
    }

    function gpsSyncSelected() {
        els.fieldSelected.hidden = !selectedFeature;
        document.body.classList.toggle('has-field-selection', !!selectedFeature);
        if (selectedFeature) {
            els.fieldSelectedName.textContent = getOperationalId(selectedFeature);
        }
        gpsDistanceToSelected();
    }

    function gpsStop(silent = false) {
        if (gpsWatchId !== null && navigator.geolocation) {
            navigator.geolocation.clearWatch(gpsWatchId);
        }
        gpsWatchId = null;
        gpsActive = false;
        gpsFollow = false;
        gpsHasFix = false;
        gpsCoordinate = null;
        gpsAccuracy = null;
        gpsLayer.setVisible(false);
        els.gpsAccuracy.textContent = '—';
        els.gpsAge.textContent = '—';
        if (!silent) gpsUpdateStatus('GPS detenido. Tu ubicación no se guarda en el sistema.', 'idle', 'APAGADO');
        gpsUpdateButtons();
        gpsDistanceToSelected();
    }

    function gpsCenterOnCurrent() {
        if (!gpsCoordinate) return;
        const view = map.getView();
        const options = { center: gpsCoordinate, duration: 280 };
        // Al entrar desde fuera del área, mantener una escala razonable.
        if (!gpsHasFix || (view.getZoom() ?? 0) < 18) options.zoom = 19;
        view.animate(options);
    }

    function gpsSuccess(result) {
        if (!gpsActive) return;
        const longitude = Number(result.coords.longitude);
        const latitude = Number(result.coords.latitude);
        const accuracy = Number(result.coords.accuracy);
        if (!Number.isFinite(longitude) || !Number.isFinite(latitude)) return;
        const projected = ol.proj.fromLonLat([longitude, latitude], mapProjection);
        if (!projected.every(Number.isFinite)) return;
        const firstFix = !gpsHasFix;
        gpsCoordinate = projected;
        gpsAccuracy = Number.isFinite(accuracy) && accuracy >= 0 ? accuracy : null;
        gpsTimestamp = result.timestamp || Date.now();
        gpsHasFix = true;
        gpsPositionFeature.getGeometry().setCoordinates(projected);
        gpsAccuracyFeature.getGeometry().setCenterAndRadius(projected, Math.min(gpsAccuracy ?? 0, 1000));
        gpsLayer.setVisible(true);

        const withinMosaic = ol.extent.containsCoordinate(mapBounds, projected);
        const accuracyText = gpsAccuracy !== null ? `± ${Math.round(gpsAccuracy)} m` : 'No disponible';
        els.gpsAccuracy.textContent = accuracyText;
        const lowConfidence = gpsAccuracy === null || gpsAccuracy > 15;
        gpsUpdateStatus(
            !withinMosaic
                ? 'Estás fuera de la extensión del mosaico o existe desplazamiento GPS. Comprueba el área y la precisión.'
                : lowConfidence
                    ? 'Posición obtenida con incertidumbre alta; úsala solo como orientación.'
                    : 'Posición recibida. Puedes seleccionar una copa y consultar su distancia aproximada.',
            !withinMosaic || lowConfidence ? 'warning' : 'good',
            (!withinMosaic || lowConfidence) ? 'REVISAR GPS' : 'GPS ACTIVO'
        );
        gpsUpdateButtons();
        gpsDistanceToSelected();
        if (gpsFollow && ol.extent.containsCoordinate(ol.extent.buffer(mapBounds, 60), projected)) {
            if (firstFix) {
                gpsCenterOnCurrent();
            } else {
                // No animar si el movimiento es menor a la propia incertidumbre.
                const viewCenter = map.getView().getCenter();
                const d = viewCenter ? Math.hypot(viewCenter[0]-projected[0],viewCenter[1]-projected[1]) : Infinity;
                if (d > Math.max(2, Math.min(gpsAccuracy ?? 5, 12))) gpsCenterOnCurrent();
            }
        }
    }

    function gpsError(err) {
        const message = err?.code === 1
            ? 'Permiso de ubicación denegado. Habilítalo en la configuración del navegador.'
            : err?.code === 2
                ? 'El teléfono no consigue una posición GPS. Verifica señal y servicios de ubicación.'
                : 'La posición tardó demasiado. Prueba al aire libre y vuelve a intentar.';
        gpsUpdateStatus(message, 'error', 'SIN SEÑAL');
        if (err?.code === 1) {
            gpsStop(true);
            gpsUpdateStatus(message, 'error', 'SIN PERMISO');
        }
    }

    function gpsStart() {
        if (!window.isSecureContext || !('geolocation' in navigator)) {
            gpsUpdateStatus('Se requiere HTTPS y un navegador con geolocalización.', 'error', 'NO DISPONIBLE');
            return;
        }
        gpsActive = true;
        gpsFollow = true;
        gpsUpdateStatus('Solicitando permiso y primera posición…', 'warning', 'BUSCANDO');
        gpsUpdateButtons();
        try {
            gpsWatchId = navigator.geolocation.watchPosition(gpsSuccess, gpsError, {
                enableHighAccuracy: true,
                maximumAge: 3000,
                timeout: 18000,
            });
        } catch (err) {
            gpsStop(true);
            gpsUpdateStatus('No fue posible iniciar el GPS: ' + (err.message || 'error inesperado'), 'error', 'ERROR');
        }
    }

    els.gpsToggle.addEventListener('click', () => gpsActive ? gpsStop() : gpsStart());
    els.gpsFollow.addEventListener('click', () => {
        if (!gpsHasFix) return;
        gpsFollow = true;
        gpsUpdateButtons();
        gpsCenterOnCurrent();
    });
    els.fieldShowInspector?.addEventListener('click', () => {
        if (selectedFeature && window.innerWidth <= 1279) openDrawer(els.rightPanel);
    });
    map.on('pointerdrag', () => {
        if (gpsFollow) {
            gpsFollow = false;
            gpsUpdateButtons();
        }
    });
    window.addEventListener('pagehide', () => gpsStop(true));
    window.setInterval(() => {
        if (!gpsActive || !gpsHasFix) return;
        const age = Math.max(0, Math.round((Date.now() - gpsTimestamp) / 1000));
        els.gpsAge.textContent = age < 60 ? `${age} s` : `${Math.floor(age / 60)} min`;
        if (age > 45) gpsUpdateStatus('Posición antigua: esperando una nueva lectura GPS.', 'warning', 'GPS ANTIGUO');
    }, 5000);
    gpsUpdateButtons();


    /* =========================================================
       LISTA DE OBJETOS
    ========================================================= */

    function renderObjectList() {
        els.explorerCount.textContent = filteredFeatures.length;
        els.filterFeedback.textContent = `${filteredFeatures.length} de ${allFeatures.length} objetos visibles`;

        if (!filteredFeatures.length) {
            els.objectList.innerHTML = `
                <div class="object-list-empty">
                    No hay objetos que coincidan con los filtros actuales.
                </div>
            `;
            return;
        }

        const fragment = document.createDocumentFragment();

        filteredFeatures.forEach((feature, index) => {
            const row = document.createElement('button');
            row.type = 'button';
            row.className = `object-row${selectedFeature === feature ? ' selected' : ''}`;
            row.dataset.featureIndex = String(index);

            const review = requiresReview(feature);
            const area = formatMetric(feature.get('area_m2'), 'm²', 2);
            const evidence = getEvidenceStatus(feature);

            row.innerHTML = `
                <div class="object-row-top">
                    <div class="object-id">${escapeHtml(getOperationalId(feature))}</div>
                    <span class="model-mark ${modelClass(feature)}" title="${escapeHtml(modelLabel(feature))}"></span>
                </div>
                <div class="object-row-meta">
                    <span>${escapeHtml(modelLabel(feature))}</span>
                    <span>Área ${escapeHtml(area)}</span>
                    <span class="mini-status${review ? ' review' : ''}">${review ? 'Revisión' : 'Sin revisión'}</span>
                    ${evidence ? `<span>${escapeHtml(evidence)}</span>` : ''}
                </div>
            `;

            row.addEventListener('click', () => selectFeature(feature, true));
            fragment.appendChild(row);
        });

        els.objectList.replaceChildren(fragment);
    }

    function syncSelectedRow() {
        els.objectList.querySelectorAll('.object-row').forEach((row, index) => {
            row.classList.toggle('selected', filteredFeatures[index] === selectedFeature);
        });

        if (selectedFeature) {
            selectedFilteredIndex = filteredFeatures.indexOf(selectedFeature);
        } else {
            selectedFilteredIndex = -1;
        }
    }

    /* =========================================================
       INSPECTOR
    ========================================================= */

    function propertyRow(label, value) {
        return `
            <div class="property-row">
                <div class="property-key">${escapeHtml(label)}</div>
                <div class="property-value">${escapeHtml(value)}</div>
            </div>
        `;
    }

    function activateInspectorTab(tabName) {
        els.inspectorTabs.forEach(button => {
            button.classList.toggle('active', button.dataset.inspectorTab === tabName);
        });

        els.inspectorObject.hidden = tabName !== 'object';
        els.inspectorAnalysis.hidden = tabName !== 'analysis';
    }

    function renderSelected(feature) {
        if (!feature) {
            els.inspectorObject.innerHTML = `
                <div class="inspector-empty">
                    <strong style="color:var(--text-soft);">Ningún objeto seleccionado.</strong><br><br>
                    Haz clic sobre una geometría del mapa o selecciona un registro del explorador. La ficha mostrará atributos del objeto operativo; no implica confirmación contra verdad de campo.
                </div>
            `;
            return;
        }

        const review = requiresReview(feature);
        const model = modelLabel(feature);
        const evidence = readableValue(feature.get('evidence_status'));
        const conflict = readableValue(feature.get('conflict_role'));
        const operationalId = getOperationalId(feature);
        const score = formatNumber(feature.get('representative_score'), 4);

        const modelBadge = isMrcnn(feature)
            ? '<span class="badge badge-mrcnn">Mask R-CNN · geometría primaria</span>'
            : isYolo(feature)
                ? '<span class="badge badge-yolo">YOLO-Seg · fallback</span>'
                : `<span class="badge badge-neutral">${escapeHtml(model)}</span>`;

        const reviewBadge = review
            ? '<span class="badge badge-review">Requiere revisión</span>'
            : '<span class="badge badge-ok">Sin revisión operativa</span>';

        const conflictAlert = (
            conflict !== '—'
            && !['none', 'null', 'no_conflict', 'sin conflicto'].includes(conflict.toLowerCase())
        )
            ? `
                <div class="section-block">
                    <div class="warning-note">
                        <strong>Condición estructural registrada:</strong> ${escapeHtml(conflict)}.<br>
                        Esta marca orienta la revisión QA/QC y no debe interpretarse automáticamente como error confirmado.
                    </div>
                </div>
            `
            : '';

        els.inspectorObject.innerHTML = `
            <div class="selected-header">
                <div>
                    <div class="selected-kicker">Objeto operativo</div>
                    <div class="selected-id">${escapeHtml(operationalId)}</div>
                </div>
            </div>

            <div class="badges">
                ${modelBadge}
                ${reviewBadge}
            </div>

            <div class="inspector-actions">
                <button id="previous-object" class="small-action" type="button" ${selectedFilteredIndex <= 0 ? 'disabled' : ''}>← Anterior</button>
                <button id="next-object" class="small-action" type="button" ${selectedFilteredIndex < 0 || selectedFilteredIndex >= filteredFeatures.length - 1 ? 'disabled' : ''}>Siguiente →</button>
                <button id="zoom-selected" class="small-action" type="button">Centrar</button>
                <button id="copy-object-id" class="small-action" type="button">Copiar ID</button>
            </div>

            <div class="section-block">
                <div class="section-label">Geometría</div>
                <div class="metric-grid">
                    <div class="metric-box">
                        <div class="metric-label">Área segmentada</div>
                        <div class="metric-value">${escapeHtml(formatMetric(feature.get('area_m2'), 'm²', 3))}</div>
                    </div>
                    <div class="metric-box">
                        <div class="metric-label">Diámetro equivalente</div>
                        <div class="metric-value">${escapeHtml(formatMetric(feature.get('eq_crown_d_m'), 'm', 3))}</div>
                    </div>
                </div>
            </div>

            <div class="section-block">
                <div class="section-label">Evidencia IA</div>
                <div class="property-list">
                    ${propertyRow('Modelo primario', model)}
                    ${propertyRow('Estado de evidencia', evidence)}
                    ${propertyRow('IoU intermodelo máximo', formatNumber(feature.get('max_intermodel_iou'), 4))}
                    ${propertyRow('Overlap mínimo máximo', formatNumber(feature.get('max_overlap_min'), 4))}
                    ${propertyRow('Distancia mínima centroides', formatMetric(feature.get('min_centroid_distance_m'), 'm', 3))}
                    ${propertyRow('Score representativo', score)}
                </div>
                <div class="method-note" style="margin-top:8px;">
                    El score representativo es un valor interno del modelo asociado a la observación representativa. No equivale a probabilidad de corrección ni a validación frente a campo.
                </div>
            </div>

            <div class="section-block">
                <div class="section-label">Estabilidad espacial</div>
                <div class="property-list">
                    ${propertyRow('Observaciones', readableValue(feature.get('n_observations')))}
                    ${propertyRow('CV de área', formatMetric(feature.get('area_cv_pct'), '%', 3))}
                    ${propertyRow('Pair IoU mean', formatNumber(feature.get('pair_iou_mean'), 4))}
                    ${propertyRow('Pair IoU min', formatNumber(feature.get('pair_iou_min'), 4))}
                    ${propertyRow('Distancia máxima centroides', formatMetric(feature.get('centroid_dist_max_m'), 'm', 3))}
                    ${propertyRow('Observaciones de borde', readableValue(feature.get('edge_observations')))}
                    ${propertyRow('Máscara representativa válida', formatNumber(feature.get('representative_mask_valid'), 4))}
                </div>
            </div>

            <div class="section-block">
                <div class="section-label">Diagnóstico operativo</div>
                <div class="property-list">
                    ${propertyRow('Candidate ID', readableValue(feature.get('candidate_id')))}
                    ${propertyRow('Tipo de candidato', readableValue(feature.get('candidate_type')))}
                    ${propertyRow('Cluster origen', readableValue(feature.get('source_cluster')))}
                    ${propertyRow('Estado geométrico', readableValue(feature.get('geometry_status')))}
                    ${propertyRow('Rol de conflicto', conflict)}
                    ${propertyRow('Regla de decisión', readableValue(feature.get('decision_rule')))}
                </div>
            </div>

            ${conflictAlert}

            <div class="section-block">
                <div class="info-note">
                    Esta ficha describe un objeto operativo de copa. La correspondencia con un árbol individual requiere la fase posterior de contraste con verdad de campo.
                    <div id="gps-distance-inspector" style="margin-top:7px;">La distancia GPS es solo orientativa.</div>
                </div>
            </div>
        `;

        document.getElementById('previous-object')?.addEventListener('click', () => navigateSelection(-1));
        document.getElementById('next-object')?.addEventListener('click', () => navigateSelection(1));
        document.getElementById('zoom-selected')?.addEventListener('click', () => zoomToFeature(feature));
        document.getElementById('copy-object-id')?.addEventListener('click', async event => {
            try {
                await navigator.clipboard.writeText(operationalId);
                event.currentTarget.textContent = 'ID copiado';
                setTimeout(() => {
                    event.currentTarget.textContent = 'Copiar ID';
                }, 1200);
            } catch (error) {
                console.warn('No fue posible copiar el ID.', error);
            }
        });
    }

    function navigateSelection(direction) {
        if (!filteredFeatures.length || selectedFilteredIndex < 0) {
            return;
        }

        const nextIndex = selectedFilteredIndex + direction;

        if (nextIndex < 0 || nextIndex >= filteredFeatures.length) {
            return;
        }

        selectFeature(filteredFeatures[nextIndex], true);
    }

    function zoomToFeature(feature) {
        const geometry = feature?.getGeometry();

        if (!geometry) {
            return;
        }

        const compact = window.innerWidth <= 760;
        map.getView().fit(geometry.getExtent(), {
            padding: compact ? [112, 46, 142, 46] : [96, 96, 96, 96],
            duration: 260,
            maxZoom: 23,
        });
    }

    function selectFeature(feature, zoom = false) {
        if (!feature || !isFeatureVisible(feature)) {
            return;
        }

        selected.clear();
        selected.push(feature);
        selectedFeature = feature;
        selectedFilteredIndex = filteredFeatures.indexOf(feature);

        renderSelected(feature);
        activateInspectorTab('object');
        syncSelectedRow();
        gpsSyncSelected();

        if (zoom) {
            zoomToFeature(feature);
        }

        if (window.innerWidth <= 1279) {
            closeDrawers();
        }
    }

    function clearSelection() {
        selected.clear();
        selectedFeature = null;
        selectedFilteredIndex = -1;
        renderSelected(null);
        syncSelectedRow();
        gpsSyncSelected();
    }

    /* =========================================================
       CONTADORES Y ESTADO GIS
    ========================================================= */

    function updateVisibleCounters() {
        const visible = allFeatures.filter(isFeatureVisible);

        let mrcnn = 0;
        let yolo = 0;
        let review = 0;

        visible.forEach(feature => {
            if (isMrcnn(feature)) mrcnn += 1;
            if (isYolo(feature)) yolo += 1;
            if (requiresReview(feature)) review += 1;
        });

        els.visibleCount.textContent = visible.length;
        els.visibleMrcnnCount.textContent = mrcnn;
        els.visibleYoloCount.textContent = yolo;
        els.visibleReviewCount.textContent = review;
        els.statusVisibleCount.textContent = visible.length;
    }

    function updateViewStatus() {
        const view = map.getView();
        const resolution = view.getResolution();
        const center = view.getCenter();

        if (!Number.isFinite(resolution) || !center) {
            return;
        }

        let metersPerPixel = resolution;

        try {
            metersPerPixel = ol.proj.getPointResolution(
                mapProjection,
                resolution,
                center,
                'm'
            );
        } catch (error) {
            console.warn('No fue posible calcular resolución métrica.', error);
        }

        const scaleDenominator = metersPerPixel * 96 * 39.3700787402;

        els.mapResolution.textContent = `${formatNumber(metersPerPixel, 3)} m/px`;
        els.mapScale.textContent = Number.isFinite(scaleDenominator)
            ? `1:${Math.max(1, Math.round(scaleDenominator)).toLocaleString('es-MX')}`
            : '—';
    }

    /* =========================================================
       GEOJSON
    ========================================================= */

    async function loadGeoJson() {
        els.mapLoading.hidden = false;
        els.mapError.hidden = true;

        try {
            const response = await fetch(geojsonUrl, {
                headers: {
                    'Accept': 'application/geo+json, application/json',
                },
                credentials: 'same-origin',
            });

            if (!response.ok) {
                throw new Error(`HTTP ${response.status}`);
            }

            const json = await response.json();

            const features = new ol.format.GeoJSON().readFeatures(json, {
                dataProjection: 'EPSG:4326',
                featureProjection: mapProjection,
            });

            vectorSource.clear(true);
            vectorSource.addFeatures(features);

            allFeatures = features;

            if (features.length !== summaryExpected.total) {
                console.warn('Conteo visual distinto al summary:', {
                    expected: summaryExpected.total,
                    actual: features.length,
                });
            }

            populateAttributeFilters();
            applyFilters();
            clearSelection();
        } catch (error) {
            console.error(error);

            els.mapErrorText.textContent =
                `La capa GeoJSON no pudo cargarse (${error.message}). `
                + 'El ortomosaico puede seguir visible, pero los objetos operativos no están disponibles.';

            els.mapError.hidden = false;
            els.objectList.innerHTML = `
                <div class="object-list-empty">
                    No fue posible cargar el catálogo espacial del análisis.
                </div>
            `;
        } finally {
            els.mapLoading.hidden = true;
        }
    }

    /* =========================================================
       INTERACCIÓN MAPA
    ========================================================= */

    selectInteraction.on('select', event => {
        const feature = event.selected[0] || null;

        selectedFeature = feature;
        selectedFilteredIndex = feature ? filteredFeatures.indexOf(feature) : -1;
        renderSelected(feature);
        syncSelectedRow();
        gpsSyncSelected();

        if (feature) {
            activateInspectorTab('object');
        }
    });

    const finePointer = window.matchMedia('(hover: hover) and (pointer: fine)').matches;
    if (finePointer) map.on('pointermove', event => {
        if (event.coordinate) {
            els.coordX.textContent = formatNumber(event.coordinate[0], 2);
            els.coordY.textContent = formatNumber(event.coordinate[1], 2);
        }

        if (event.dragging) {
            els.tooltip.classList.remove('active');
            return;
        }

        const feature = map.forEachFeatureAtPixel(
            event.pixel,
            candidate => isFeatureVisible(candidate) ? candidate : null,
            {
                layerFilter: layer => layer === vectorLayer,
                hitTolerance: 5,
            }
        );

        map.getTargetElement().style.cursor = feature ? 'pointer' : '';

        if (!feature) {
            els.tooltip.classList.remove('active');
            return;
        }

        const review = requiresReview(feature);

        els.tooltip.innerHTML = `
            <div class="tooltip-title">${escapeHtml(getOperationalId(feature))}</div>
            <div class="tooltip-meta">
                ${escapeHtml(modelLabel(feature))}<br>
                Área: ${escapeHtml(formatMetric(feature.get('area_m2'), 'm²', 2))}<br>
                Evidencia: ${escapeHtml(readableValue(feature.get('evidence_status')))}
            </div>
            ${review ? '<div class="tooltip-status">Requiere revisión operativa</div>' : ''}
        `;

        els.tooltip.style.left = `${event.pixel[0]}px`;
        els.tooltip.style.top = `${event.pixel[1]}px`;
        els.tooltip.classList.add('active');
    });

    map.getViewport().addEventListener('mouseleave', () => {
        els.tooltip.classList.remove('active');
        map.getTargetElement().style.cursor = '';
    });

    map.getView().on('change:resolution', updateViewStatus);
    map.getView().on('change:center', updateViewStatus);

    /* =========================================================
       TOOLBAR
    ========================================================= */

    function setToolActive(button, active, reviewStyle = false) {
        if (!button) return;

        button.classList.toggle('active', active && !reviewStyle);
        button.classList.toggle('review-active', active && reviewStyle);
        button.setAttribute('aria-pressed', active ? 'true' : 'false');
    }

    els.togglePreview?.addEventListener('click', () => {
        orthomosaicVisible = !orthomosaicVisible;
        syncRasterLayers();
        setToolActive(els.togglePreview, orthomosaicVisible);
    });

    els.toggleMrcnn.addEventListener('click', () => {
        showMrcnn = !showMrcnn;
        setToolActive(els.toggleMrcnn, showMrcnn);
        applyFilters();
    });

    els.toggleYolo.addEventListener('click', () => {
        showYolo = !showYolo;
        setToolActive(els.toggleYolo, showYolo);
        applyFilters();
    });

    els.toggleReviewHighlight.addEventListener('click', () => {
        highlightReview = !highlightReview;
        crownStyleCache.clear();
        setToolActive(els.toggleReviewHighlight, highlightReview, true);
        vectorLayer.changed();
    });

    els.opacityToggle.addEventListener('click', event => {
        event.stopPropagation();
        const opened = els.opacityPopover.classList.toggle('open');
        els.opacityToggle.setAttribute('aria-expanded', opened ? 'true' : 'false');
    });

    els.opacityPopover.addEventListener('click', event => {
        event.stopPropagation();
    });

    document.addEventListener('click', () => {
        els.opacityPopover.classList.remove('open');
        els.opacityToggle.setAttribute('aria-expanded', 'false');
    });

    els.previewOpacity?.addEventListener('input', () => {
        currentRasterOpacity = Number(els.previewOpacity.value) / 100;
        syncRasterLayers();
        els.previewOpacityValue.textContent = `${els.previewOpacity.value}%`;
    });

    els.segOpacity.addEventListener('input', () => {
        currentSegOpacity = Number(els.segOpacity.value) / 100;
        crownStyleCache.clear();
        els.segOpacityValue.textContent = `${els.segOpacity.value}%`;
        vectorLayer.changed();
    });

    els.fitExtent.addEventListener('click', fitMap);
    els.clearSelection.addEventListener('click', clearSelection);

    els.fullscreenMap.addEventListener('click', async () => {
        try {
            if (!document.fullscreenElement) {
                await els.workspace.requestFullscreen();
            } else {
                await document.exitFullscreen();
            }
        } catch (error) {
            console.warn('No fue posible activar pantalla completa.', error);
        }
    });

    document.addEventListener('fullscreenchange', () => {
        setTimeout(() => map.updateSize(), 80);
    });

    /* =========================================================
       FILTROS / BUSCADOR
    ========================================================= */

    els.quickFilters.forEach(button => {
        button.addEventListener('click', () => {
            activeQuickFilter = button.dataset.filter || 'all';

            els.quickFilters.forEach(item => {
                item.classList.toggle('active', item === button);
            });

            applyFilters();
        });
    });

    // Reducir reconstrucciones del listado al teclear en móviles.
    let searchDebounce = null;
    els.search.addEventListener('input', () => {
        if (searchDebounce !== null) clearTimeout(searchDebounce);
        searchDebounce = setTimeout(applyFilters, 130);
    });
    els.evidenceFilter.addEventListener('change', applyFilters);
    els.conflictFilter.addEventListener('change', applyFilters);
    els.resetFilters.addEventListener('click', resetFilters);

    /* =========================================================
       TABS INSPECTOR
    ========================================================= */

    els.inspectorTabs.forEach(button => {
        button.addEventListener('click', () => {
            activateInspectorTab(button.dataset.inspectorTab || 'object');
        });
    });

    /* =========================================================
       DRAWERS MÓVILES
    ========================================================= */

    function closeDrawers() {
        els.leftPanel.classList.remove('open');
        els.rightPanel.classList.remove('open');
        els.drawerBackdrop.classList.remove('active');
    }

    function openDrawer(panel) {
        closeDrawers();
        panel.classList.add('open');
        els.drawerBackdrop.classList.add('active');
        els.opacityPopover.classList.remove('open');
        els.opacityToggle.setAttribute('aria-expanded', 'false');
    }

    els.openExplorerMobile?.addEventListener('click', () => openDrawer(els.leftPanel));
    els.openInspectorMobile?.addEventListener('click', () => openDrawer(els.rightPanel));
    els.closeExplorerMobile?.addEventListener('click', closeDrawers);
    els.closeInspectorMobile?.addEventListener('click', closeDrawers);
    els.drawerBackdrop.addEventListener('click', closeDrawers);
    document.addEventListener('keydown', event => {
        if (event.key === 'Escape') {
            closeDrawers();
            els.opacityPopover.classList.remove('open');
            els.opacityToggle.setAttribute('aria-expanded', 'false');
        }
    });

    let resizeRaf = null;
    function updateMapViewport() {
        if (resizeRaf !== null) cancelAnimationFrame(resizeRaf);
        resizeRaf = requestAnimationFrame(() => {
            map.updateSize();
            resizeRaf = null;
        });
    }

    window.visualViewport?.addEventListener('resize', updateMapViewport);
    if (window.ResizeObserver) {
        const mapResizeObserver = new ResizeObserver(updateMapViewport);
        mapResizeObserver.observe(document.querySelector('.map-pane'));
    }
    window.addEventListener('resize', () => {
        updateMapViewport();

        if (window.innerWidth > 1279) {
            closeDrawers();
        }
    });

    /* =========================================================
       INICIO
    ========================================================= */

    els.retryGeojson.addEventListener('click', loadGeoJson);

    updateViewStatus();
    requestAnimationFrame(() => map.updateSize());
    initializeCogLayer();
    loadGeoJson();
})();
</script>

</body>
</html>
