<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <meta name="csrf-token" content="{{ csrf_token() }}">

    <title>
        Mapa de objetos de copa · {{ $job->uuid }}
    </title>

    <link
        rel="stylesheet"
        href="https://cdn.jsdelivr.net/npm/ol@10.10.0/ol.css"
    >

    <style>
        :root {
            --bg: #eef3f0;
            --surface: #ffffff;
            --surface-soft: #f7faf8;
            --text: #18211d;
            --muted: #67736d;
            --border: #dce5e0;
            --forest: #176b4d;
            --forest-dark: #0f5039;
            --forest-soft: #eaf5ef;
            --warning: #a56812;
            --warning-soft: #fff4d8;
            --danger: #b42318;
            --shadow: 0 12px 35px rgba(20, 50, 35, .08);
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
                -apple-system,
                BlinkMacSystemFont,
                "Segoe UI",
                Roboto,
                Arial,
                sans-serif;
            background: var(--bg);
            color: var(--text);
        }

        .topbar {
            background: linear-gradient(130deg, #103b2d, #176b4d);
            color: white;
            padding: 15px 24px;
            box-shadow: 0 5px 20px rgba(0, 0, 0, .14);
        }

        .topbar-inner {
            max-width: 1700px;
            margin: 0 auto;
            display: flex;
            align-items: center;
            justify-content: space-between;
            gap: 18px;
        }

        .brand-title {
            font-weight: 760;
            font-size: 17px;
        }

        .brand-subtitle {
            margin-top: 3px;
            opacity: .78;
            font-size: 11px;
        }

        .back-button {
            display: inline-flex;
            align-items: center;
            gap: 7px;
            padding: 9px 13px;
            border: 1px solid rgba(255, 255, 255, .28);
            border-radius: 9px;
            color: white;
            text-decoration: none;
            font-size: 12px;
            font-weight: 650;
            background: rgba(255, 255, 255, .10);
        }

        main {
            max-width: 1700px;
            margin: 0 auto;
            padding: 20px;
        }

        .summary-bar {
            display: grid;
            grid-template-columns: minmax(0, 1fr) auto;
            gap: 16px;
            align-items: start;
            margin-bottom: 16px;
        }

        .summary-title {
            margin: 0;
            font-size: 23px;
        }

        .summary-text {
            margin-top: 7px;
            color: var(--muted);
            font-size: 12px;
            line-height: 1.55;
        }

        .status-chip {
            padding: 8px 12px;
            border-radius: 999px;
            background: var(--forest-soft);
            color: var(--forest-dark);
            font-size: 11px;
            font-weight: 750;
            white-space: nowrap;
        }

        .map-layout {
            display: grid;
            grid-template-columns: minmax(0, 1fr) 330px;
            gap: 16px;
            align-items: stretch;
        }

        .map-card,
        .panel-card {
            background: var(--surface);
            border: 1px solid var(--border);
            border-radius: 15px;
            box-shadow: var(--shadow);
            overflow: hidden;
        }

        .map-toolbar {
            min-height: 58px;
            padding: 11px 14px;
            display: flex;
            align-items: center;
            justify-content: space-between;
            gap: 14px;
            border-bottom: 1px solid var(--border);
            background: var(--surface-soft);
        }

        .toolbar-group {
            display: flex;
            align-items: center;
            flex-wrap: wrap;
            gap: 12px;
        }

        .toolbar-label {
            display: flex;
            align-items: center;
            gap: 6px;
            font-size: 11px;
            color: var(--muted);
        }

        .toolbar-label input {
            width: auto;
        }

        .toolbar-button {
            border: 1px solid var(--border);
            background: white;
            border-radius: 8px;
            padding: 7px 10px;
            cursor: pointer;
            font-size: 11px;
            color: var(--text);
        }

        #map {
            width: 100%;
            height: calc(100vh - 225px);
            min-height: 590px;
            background:
                linear-gradient(45deg, #e7ece9 25%, transparent 25%),
                linear-gradient(-45deg, #e7ece9 25%, transparent 25%),
                linear-gradient(45deg, transparent 75%, #e7ece9 75%),
                linear-gradient(-45deg, transparent 75%, #e7ece9 75%),
                #f4f7f5;
            background-size: 22px 22px;
            background-position: 0 0, 0 11px, 11px -11px, -11px 0;
        }

        .legend {
            display: flex;
            gap: 16px;
            flex-wrap: wrap;
            padding: 10px 14px;
            border-top: 1px solid var(--border);
            background: white;
            font-size: 11px;
            color: var(--muted);
        }

        .legend-item {
            display: inline-flex;
            align-items: center;
            gap: 6px;
        }

        .legend-swatch {
            width: 13px;
            height: 13px;
            border-radius: 3px;
        }

        .legend-ok {
            background: rgba(23, 107, 77, .28);
            border: 2px solid #176b4d;
        }

        .legend-review {
            background: rgba(196, 126, 18, .28);
            border: 2px solid #b36f0c;
        }

        .side-panel {
            display: flex;
            flex-direction: column;
            gap: 16px;
        }

        .panel-header {
            padding: 16px 17px 12px;
            border-bottom: 1px solid var(--border);
        }

        .panel-title {
            font-size: 14px;
            font-weight: 750;
        }

        .panel-subtitle {
            margin-top: 4px;
            color: var(--muted);
            font-size: 10px;
            line-height: 1.45;
        }

        .panel-content {
            padding: 16px 17px;
        }

        .metric-grid {
            display: grid;
            grid-template-columns: 1fr 1fr;
            gap: 9px;
        }

        .metric {
            padding: 10px;
            border: 1px solid var(--border);
            border-radius: 9px;
            background: var(--surface-soft);
        }

        .metric-name {
            color: var(--muted);
            font-size: 9px;
            text-transform: uppercase;
            letter-spacing: .035em;
        }

        .metric-value {
            margin-top: 4px;
            font-size: 17px;
            font-weight: 760;
        }

        .object-empty {
            color: var(--muted);
            font-size: 12px;
            line-height: 1.6;
        }

        .property-list {
            display: grid;
            gap: 8px;
        }

        .property-row {
            padding-bottom: 8px;
            border-bottom: 1px solid #edf1ef;
        }

        .property-key {
            color: var(--muted);
            font-size: 9px;
            text-transform: uppercase;
            letter-spacing: .035em;
        }

        .property-value {
            margin-top: 3px;
            font-size: 12px;
            font-weight: 620;
            overflow-wrap: anywhere;
        }

        .review-badge {
            display: inline-flex;
            align-items: center;
            padding: 4px 7px;
            border-radius: 999px;
            font-size: 10px;
            font-weight: 700;
        }

        .review-yes {
            background: var(--warning-soft);
            color: var(--warning);
        }

        .review-no {
            background: var(--forest-soft);
            color: var(--forest-dark);
        }

        .scientific-note {
            font-size: 10px;
            color: var(--muted);
            line-height: 1.55;
        }

        .warning-box {
            margin-top: 12px;
            padding: 10px 11px;
            border: 1px solid #efd5a4;
            border-radius: 9px;
            background: #fff9ec;
            color: #7d5717;
            font-size: 10px;
            line-height: 1.5;
        }

        .ol-control button {
            background: rgba(18, 77, 55, .88);
        }

        .ol-scale-line {
            background: rgba(16, 59, 45, .76);
        }

        @media (max-width: 1080px) {
            .map-layout {
                grid-template-columns: 1fr;
            }

            #map {
                height: 68vh;
                min-height: 500px;
            }
        }

        @media (max-width: 650px) {
            main {
                padding: 12px;
            }

            .summary-bar {
                grid-template-columns: 1fr;
            }

            .topbar {
                padding: 13px;
            }

            .topbar-inner {
                align-items: flex-start;
            }

            .brand-subtitle {
                display: none;
            }
        }
    </style>
</head>

<body>

<header class="topbar">
    <div class="topbar-inner">
        <div>
            <div class="brand-title">
                UAV Forest AI · Visualización espacial
            </div>

            <div class="brand-subtitle">
                Artifact web derivado de primary_objects · Método científico V0.6G
            </div>
        </div>

        <a
            class="back-button"
            href="{{
                route(
                    'analysis.index',
                    [
                        'mosaic' => $mosaic->uuid,
                    ]
                )
            }}"
        >
            ← Volver al análisis
        </a>
    </div>
</header>

<main>

    <div class="summary-bar">
        <div>
            <h1 class="summary-title">
                Mapa de objetos de copa segmentados
            </h1>

            <div class="summary-text">
                Proyecto:
                <strong>{{ $mosaic->project->name ?? '—' }}</strong>
                · Ortomosaico:
                <strong>{{ $mosaic->original_name ?? $mosaic->uuid }}</strong>
                · CRS del mosaico:
                <strong>{{ $mosaic->crs }}</strong>
                · Analysis UUID:
                <strong>{{ $job->uuid }}</strong>
            </div>
        </div>

        <div class="status-chip">
            {{ strtoupper($job->status) }}
        </div>
    </div>

    <div class="map-layout">

        <section class="map-card">

            <div class="map-toolbar">
                <div class="toolbar-group">
                    @if($previewAvailable)
                        <label class="toolbar-label">
                            <input
                                id="toggle-preview"
                                type="checkbox"
                                checked
                            >
                            Ortomosaico
                        </label>
                    @endif

                    <label class="toolbar-label">
                        <input
                            id="toggle-crowns"
                            type="checkbox"
                            checked
                        >
                        Objetos de copa
                    </label>
                </div>

                <div class="toolbar-group">
                    <button
                        id="fit-extent"
                        class="toolbar-button"
                        type="button"
                    >
                        Ajustar extensión
                    </button>
                </div>
            </div>

            <div id="map"></div>

            <div class="legend">
                <span class="legend-item">
                    <span class="legend-swatch legend-ok"></span>
                    Sin revisión operativa
                </span>

                <span class="legend-item">
                    <span class="legend-swatch legend-review"></span>
                    Requiere revisión
                </span>

                <span class="legend-item">
                    Geometrías: primary_objects
                </span>
            </div>

        </section>

        <aside class="side-panel">

            <section class="panel-card">
                <div class="panel-header">
                    <div class="panel-title">
                        Resumen del análisis
                    </div>

                    <div class="panel-subtitle">
                        Conteos operativos. No equivalen todavía a árboles validados en campo.
                    </div>
                </div>

                <div class="panel-content">
                    <div class="metric-grid">
                        <div class="metric">
                            <div class="metric-name">Objetos</div>
                            <div class="metric-value">
                                {{ $job->summary['primary_objects'] ?? '—' }}
                            </div>
                        </div>

                        <div class="metric">
                            <div class="metric-name">Revisión</div>
                            <div class="metric-value">
                                {{ $job->summary['requires_review'] ?? '—' }}
                            </div>
                        </div>

                        <div class="metric">
                            <div class="metric-name">MRCNN</div>
                            <div class="metric-value">
                                {{ $job->summary['primary_maskrcnn'] ?? '—' }}
                            </div>
                        </div>

                        <div class="metric">
                            <div class="metric-name">YOLO fallback</div>
                            <div class="metric-value">
                                {{ $job->summary['primary_yolo_fallback'] ?? '—' }}
                            </div>
                        </div>
                    </div>

                    @unless($previewAvailable)
                        <div class="warning-box">
                            Este mosaico no dispone de un preview web accesible. Las copas se mostrarán sobre fondo neutro hasta generar o registrar el preview.
                        </div>
                    @endunless
                </div>
            </section>

            <section class="panel-card">
                <div class="panel-header">
                    <div class="panel-title">
                        Objeto seleccionado
                    </div>

                    <div class="panel-subtitle">
                        Haz clic sobre una geometría para inspeccionar su evidencia y métricas.
                    </div>
                </div>

                <div
                    id="object-details"
                    class="panel-content"
                >
                    <div class="object-empty">
                        Ningún objeto seleccionado.
                    </div>
                </div>
            </section>

            <section class="panel-card">
                <div class="panel-content scientific-note">
                    <strong>Interpretación:</strong>
                    este visor es una herramienta de control de calidad espacial. La geometría del GeoJSON está reproyectada para interoperabilidad web, mientras que área, diámetro equivalente y distancias proceden del procesamiento métrico original. La inspección visual no sustituye la validación contra verdad de campo.
                </div>
            </section>

        </aside>

    </div>

</main>

<script src="https://cdn.jsdelivr.net/npm/proj4@2.15.0/dist/proj4.js"></script>
<script src="https://cdn.jsdelivr.net/npm/ol@10.10.0/dist/ol.js"></script>

<script>
(() => {
    'use strict';

    const mapBounds = @json($mapBounds);
    const mosaicProjection = @json($mosaic->crs);
    const geojsonUrl = @json(
        route(
            'analysis.map.geojson',
            [
                'analysisUuid' => $job->uuid,
            ]
        )
    );

    const previewAvailable = @json($previewAvailable);
    const previewUrl = previewAvailable
        ? @json(
            route(
                'analysis.map.preview',
                [
                    'analysisUuid' => $job->uuid,
                ]
            )
        )
        : null;


    // Registro explícito de UTM WGS84 con proj4.
    // EPSG:326xx = hemisferio norte; EPSG:327xx = hemisferio sur.
    const utmMatch = /^EPSG:(326|327)(\d{2})$/.exec(
        mosaicProjection
    );


    if (utmMatch) {
        const hemisphereCode = utmMatch[1];
        const zone = Number(
            utmMatch[2]
        );
        const south = (
            hemisphereCode
            ===
            '327'
        );

        proj4.defs(
            mosaicProjection,
            `+proj=utm +zone=${zone} ${south ? '+south ' : ''}+datum=WGS84 +units=m +no_defs +type=crs`
        );

        ol.proj.proj4.register(
            proj4
        );
    }


    const mapProjection = ol.proj.get(
        mosaicProjection
    );


    if (!mapProjection) {
        throw new Error(
            `OpenLayers no pudo resolver ${mosaicProjection}.`
        );
    }


    const layers = [];
    let previewLayer = null;


    if (
        previewAvailable
        &&
        previewUrl
    ) {
        previewLayer = new ol.layer.Image({
            source: new ol.source.ImageStatic({
                url: previewUrl,
                imageExtent: mapBounds,
                projection: mapProjection,
                interpolate: true,
            }),
            opacity: 1,
            zIndex: 1,
        });

        layers.push(
            previewLayer
        );
    }


    const vectorSource = new ol.source.Vector({
        url: geojsonUrl,
        format: new ol.format.GeoJSON({
            dataProjection: 'EPSG:4326',
            featureProjection: mapProjection,
        }),
    });


    function requiresReview(feature) {
        const value = feature.get(
            'requires_review'
        );

        return (
            value === true
            ||
            value === 1
            ||
            value === '1'
            ||
            String(value).toLowerCase() === 'true'
        );
    }


    const styleOk = new ol.style.Style({
        stroke: new ol.style.Stroke({
            color: 'rgba(23, 107, 77, 0.96)',
            width: 2,
        }),
        fill: new ol.style.Fill({
            color: 'rgba(23, 107, 77, 0.18)',
        }),
    });


    const styleReview = new ol.style.Style({
        stroke: new ol.style.Stroke({
            color: 'rgba(179, 111, 12, 0.98)',
            width: 2.2,
        }),
        fill: new ol.style.Fill({
            color: 'rgba(196, 126, 18, 0.22)',
        }),
    });


    const styleSelected = new ol.style.Style({
        stroke: new ol.style.Stroke({
            color: 'rgba(172, 43, 43, 1)',
            width: 3.4,
        }),
        fill: new ol.style.Fill({
            color: 'rgba(190, 52, 52, 0.18)',
        }),
    });


    const crownLayer = new ol.layer.Vector({
        source: vectorSource,
        style: (feature) => (
            requiresReview(feature)
                ? styleReview
                : styleOk
        ),
        zIndex: 10,
    });


    layers.push(
        crownLayer
    );


    const view = new ol.View({
        projection: mapProjection,
        center: ol.extent.getCenter(
            mapBounds
        ),
        zoom: 19,
    });


    const map = new ol.Map({
        target: 'map',
        layers,
        view,
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
        map.getView().fit(
            mapBounds,
            {
                padding: [30, 30, 30, 30],
                duration: 250,
                maxZoom: 24,
            }
        );
    }


    fitMap();


    vectorSource.on(
        'featuresloadend',
        (event) => {
            const features = event.features || [];

            if (
                features.length
                !==
                {{ (int) ($job->summary['primary_objects'] ?? 0) }}
            ) {
                console.warn(
                    'Conteo visual distinto al summary:',
                    features.length
                );
            }
        }
    );


    vectorSource.on(
        'featuresloaderror',
        () => {
            document.getElementById(
                'object-details'
            ).innerHTML = `
                <div class="warning-box">
                    No fue posible cargar el GeoJSON del análisis.
                </div>
            `;
        }
    );


    const selected = new ol.Collection();


    const selectInteraction = new ol.interaction.Select({
        layers: [
            crownLayer,
        ],
        style: styleSelected,
        hitTolerance: 5,
        features: selected,
    });


    map.addInteraction(
        selectInteraction
    );


    function escapeHtml(value) {
        if (
            value === null
            ||
            value === undefined
        ) {
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
        const number = Number(value);

        if (!Number.isFinite(number)) {
            return '—';
        }

        return number.toLocaleString(
            'es-MX',
            {
                minimumFractionDigits: 0,
                maximumFractionDigits: digits,
            }
        );
    }


    function propertyRow(label, value) {
        return `
            <div class="property-row">
                <div class="property-key">${escapeHtml(label)}</div>
                <div class="property-value">${escapeHtml(value)}</div>
            </div>
        `;
    }


    function renderFeature(feature) {
        const review = requiresReview(
            feature
        );

        const badge = review
            ? '<span class="review-badge review-yes">Requiere revisión</span>'
            : '<span class="review-badge review-no">Sin revisión operativa</span>';

        const html = `
            <div style="margin-bottom:12px;">${badge}</div>
            <div class="property-list">
                ${propertyRow('Operational ID', feature.get('operational_id'))}
                ${propertyRow('Tipo de candidato', feature.get('candidate_type'))}
                ${propertyRow('Modelo primario', feature.get('primary_model'))}
                ${propertyRow('Estado geométrico', feature.get('geometry_status'))}
                ${propertyRow('Estado de evidencia', feature.get('evidence_status'))}
                ${propertyRow('Rol de conflicto', feature.get('conflict_role'))}
                ${propertyRow('Área de copa', `${formatNumber(feature.get('area_m2'), 3)} m²`)}
                ${propertyRow('Diámetro equivalente', `${formatNumber(feature.get('eq_crown_d_m'), 3)} m`)}
                ${propertyRow('Score representativo', formatNumber(feature.get('representative_score'), 4))}
                ${propertyRow('IoU intermodelo máx.', formatNumber(feature.get('max_intermodel_iou'), 4))}
                ${propertyRow('Solape mínimo máx.', formatNumber(feature.get('max_overlap_min'), 4))}
                ${propertyRow('Distancia centroides mín.', `${formatNumber(feature.get('min_centroid_distance_m'), 3)} m`)}
                ${propertyRow('Observaciones', feature.get('n_observations'))}
                ${propertyRow('Regla de decisión', feature.get('decision_rule'))}
            </div>
        `;

        document.getElementById(
            'object-details'
        ).innerHTML = html;
    }


    selectInteraction.on(
        'select',
        (event) => {
            const feature = (
                event.selected[0]
                ||
                null
            );

            if (feature) {
                renderFeature(
                    feature
                );
            } else {
                document.getElementById(
                    'object-details'
                ).innerHTML = `
                    <div class="object-empty">
                        Ningún objeto seleccionado.
                    </div>
                `;
            }
        }
    );


    const toggleCrowns = document.getElementById(
        'toggle-crowns'
    );


    toggleCrowns.addEventListener(
        'change',
        () => {
            crownLayer.setVisible(
                toggleCrowns.checked
            );
        }
    );


    const togglePreview = document.getElementById(
        'toggle-preview'
    );


    if (
        togglePreview
        &&
        previewLayer
    ) {
        togglePreview.addEventListener(
            'change',
            () => {
                previewLayer.setVisible(
                    togglePreview.checked
                );
            }
        );
    }


    document.getElementById(
        'fit-extent'
    ).addEventListener(
        'click',
        fitMap
    );
})();
</script>

</body>
</html>
