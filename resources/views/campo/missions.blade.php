@extends('layouts.uav')
@section('title', 'SIGMAD Campo · Planificador cartográfico')

@push('styles')
<link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/ol@10.10.0/ol.css">
@verbatim
<style>
/* SIGMAD Mission Planner V0.4.3 — edición auditada y filtros de copas */
main.page {max-width:none !important;width:100%;padding:0 !important;margin:0 !important;}
.cmh {--pine:#0b6045;--deep:#102d24;--ink:#18352a;--muted:#718579;--line:#dfeae2;--mint:#eaf6ed;--gold:#efb247;--card:#fff;min-height:calc(100vh - 66px);padding:24px clamp(15px,2.7vw,44px) 40px;background:radial-gradient(circle at 93% 1%,#d7efe2 0,transparent 37%),linear-gradient(145deg,#f7faf7,#eff6f1 67%,#eef4ef);color:var(--ink);font-family:Inter,ui-sans-serif,system-ui,-apple-system,'Segoe UI',sans-serif;}
.cmh *{box-sizing:border-box;}.cmh button,.cmh input,.cmh select{font:inherit;}.cmh button{cursor:pointer;}.cmh button:disabled{opacity:.45;cursor:not-allowed}.cmh a{color:inherit}.cmh [hidden]{display:none !important;}
.cmh-head{display:flex;justify-content:space-between;align-items:center;gap:18px;margin:0 0 20px;}.cmh-eyebrow{display:flex;align-items:center;gap:9px;color:#16825a;font-size:10px;font-weight:850;letter-spacing:.17em;text-transform:uppercase}.cmh-eyebrow:before{content:"";width:8px;height:8px;border-radius:50%;background:#18bd83;box-shadow:0 0 0 4px #dcf3e8}.cmh-title{font-size:clamp(25px,2.7vw,37px);line-height:1.12;letter-spacing:-.035em;margin:8px 0}.cmh-subtitle{margin:0;color:#65796d;font-size:13px;line-height:1.55;max-width:850px}.cmh-back{flex:0 0 auto;display:inline-flex;gap:9px;align-items:center;text-decoration:none;border:1px solid #d4e5d9;background:#fff;padding:10px 14px;border-radius:11px;font-size:12px;font-weight:750;box-shadow:0 4px 16px #143b2310}.cmh-back:hover{background:#edf8f1}
.cmh-alert{padding:12px 15px;border-radius:12px;margin:0 0 14px;border:1px solid #b3dfc4;background:#e6f8ed;color:#135438;font-size:12px}.cmh-alert.error{border-color:#f0c6c6;background:#fff0ed;color:#922c2c}
.cmh-form{display:grid;grid-template-columns:minmax(310px,364px) minmax(0,1fr);gap:17px;align-items:stretch}.cmh-panel{background:#fff;border:1px solid #deebe2;box-shadow:0 13px 40px rgba(16,62,38,.065);border-radius:19px;min-width:0;overflow:hidden;}.cmh-sidebar{display:flex;flex-direction:column;}.cmh-panel-head{padding:19px 20px 15px;border-bottom:1px solid var(--line);background:linear-gradient(110deg,#fff,#f7fbf8);}.cmh-step{font-size:9px;color:#14805a;font-weight:850;letter-spacing:.13em;text-transform:uppercase}.cmh-panel-title{margin:4px 0;font-weight:850;letter-spacing:-.025em;font-size:19px}.cmh-panel-sub{color:#758a7d;font-size:11px;margin:5px 0 0;line-height:1.5}.cmh-fields{padding:18px 19px 13px;display:grid;gap:13px}.cmh-field{display:block;font-size:11px;color:#3f5c4d;font-weight:800}.cmh-field input,.cmh-field select{display:block;margin-top:6px;width:100%;height:43px;min-width:0;padding:0 12px;background:#fafdfa;border:1px solid #d6e5db;border-radius:10px;color:#163f2d;font-size:12px;font-weight:550;outline:none}.cmh-field input:focus,.cmh-field select:focus,.cmh-search input:focus{border-color:#17a775;box-shadow:0 0 0 3px #17a77518}.cmh-field select{cursor:pointer}.cmh-divider{height:1px;background:var(--line)}
.cmh-selection{padding:14px 19px 12px;}.cmh-selection-head{display:flex;align-items:center;justify-content:space-between;gap:12px}.cmh-selection-title{font-weight:800;font-size:12px}.cmh-count{display:flex;align-items:baseline;gap:5px;color:#087b54;font-weight:850}.cmh-count strong{font-size:29px;letter-spacing:-.04em;line-height:1}.cmh-count span{font-size:11px;color:#789083}.cmh-meter{height:6px;background:#e7f2e9;border-radius:20px;overflow:hidden;margin:10px 0 8px}.cmh-meter>span{display:block;width:0;height:100%;background:linear-gradient(90deg,#14a572,#0b6e51);border-radius:20px;transition:width .2s}.cmh-hint{font-size:10px;color:#84958b;line-height:1.5;margin:0}.cmh-search{margin:13px 0 9px;position:relative}.cmh-search input{width:100%;height:39px;border:1px solid #d8e5db;border-radius:10px;background:#f9fcfa;padding:0 12px 0 33px;outline:none;font-size:11px}.cmh-search svg{position:absolute;left:11px;top:12px;width:15px;height:15px;stroke:#89a397}.cmh-section-caption{font-size:10px;font-weight:800;color:#567465;display:flex;justify-content:space-between;align-items:center;margin:10px 0 6px}.cmh-candidate-list{max-height:245px;min-height:105px;overflow-y:auto;display:grid;align-content:start;gap:4px;scrollbar-color:#c7dacd transparent;scrollbar-width:thin}.cmh-item{display:flex;align-items:center;gap:8px;border:1px solid transparent;background:#f7faf8;border-radius:10px;padding:6px 8px;min-width:0}.cmh-item:hover{background:#eff8f2}.cmh-item.selected{border-color:#efcf92;background:#fff9ec}.cmh-dot{width:9px;height:9px;border-radius:50%;background:#17a877;flex:0 0 9px}.cmh-dot.yolo{background:#9762cc}.cmh-item.selected .cmh-dot{background:#e2a335}.cmh-item-body{flex:1;min-width:0}.cmh-item-id{display:block;font-size:11px;font-weight:800;color:#2a4838;overflow:hidden;text-overflow:ellipsis;white-space:nowrap}.cmh-item-meta{font-size:9px;color:#8a9e90;display:block;margin-top:2px}.cmh-mini-btn{border:1px solid #d8e8dc;background:white;border-radius:7px;padding:6px 8px;font-size:9px;font-weight:850;color:#0b7450;white-space:nowrap}.cmh-mini-btn:hover{background:#e3f5e9}.cmh-item.selected .cmh-mini-btn{color:#955a11;border-color:#f1d9aa}
.cmh-selected{display:flex;gap:5px;flex-wrap:wrap;max-height:91px;overflow:auto;margin:8px 0}.cmh-chip{display:flex;gap:5px;align-items:center;padding:5px 7px;border-radius:8px;border:1px solid #f0ddb6;background:#fff8e9;color:#835815;font-size:10px;font-weight:750}.cmh-chip button{border:0;background:none;color:#8d6321;font-size:15px;line-height:12px;padding:0}.cmh-empty-list{padding:15px;color:#8a9b90;font-size:11px;border:1px dashed #d8e6db;border-radius:9px;text-align:center;background:#fbfdfa}
.cmh-actions{padding:14px 19px 18px;border-top:1px solid var(--line);margin-top:auto;background:#fcfefd}.cmh-actions-row{display:grid;grid-template-columns:1fr 2.2fr;gap:8px}.cmh-btn{border:1px solid #d4e4d8;border-radius:10px;min-height:43px;padding:9px 13px;background:#fff;color:#295d44;font-size:11px;font-weight:800;white-space:normal}.cmh-btn:hover{background:#eaf6ee}.cmh-btn.primary{background:#0b7755;border-color:#0b7755;color:white;box-shadow:0 6px 15px #0b77552a}.cmh-btn.primary:hover{background:#096547}.cmh-save-caption{font-size:10px;color:#829588;line-height:1.4;margin:10px 0 0}
.cmh-mapbox{position:relative;min-height:742px;overflow:hidden;background:#0e241c;isolation:isolate}.cmh-map{width:100%;height:100%;min-height:742px;background:repeating-linear-gradient(45deg,#172d22,#172d22 13px,#192f25 13px,#192f25 26px)}.cmh-map .ol-zoom{left:auto;right:15px;top:78px;background:#143e30aa;border:1px solid #ffffff26;border-radius:10px;padding:4px}.cmh-map .ol-zoom button{border-radius:7px;background:#194b39;color:#fff;font-size:16px}.cmh-map .ol-scale-line{left:15px;bottom:46px;background:#113b2eaf;border-radius:8px;border:1px solid #ffffff2a}.cmh-map .ol-attribution{bottom:45px}
.cmh-map-top{position:absolute;top:15px;left:16px;right:16px;z-index:20;display:flex;justify-content:space-between;align-items:flex-start;gap:12px;pointer-events:none}.cmh-map-titles{pointer-events:none;background:rgba(7,36,26,.93);border:1px solid #ffffff26;box-shadow:0 7px 25px #0005;border-radius:13px;padding:12px 15px;color:white;max-width:min(440px,62%);backdrop-filter:blur(9px)}.cmh-map-kicker{font-size:9px;color:#7adbae;letter-spacing:.11em;font-weight:850;text-transform:uppercase}.cmh-map-name{font-size:16px;font-weight:850;letter-spacing:-.025em;margin:5px 0 3px;white-space:nowrap;overflow:hidden;text-overflow:ellipsis}.cmh-map-meta{font-size:10px;color:#b4d6c4;line-height:1.4}.cmh-map-tools{display:flex;gap:6px;flex-wrap:wrap;justify-content:flex-end;pointer-events:auto;max-width:55%}.cmh-map-tools button{min-width:38px;min-height:37px;background:#103729ee;border:1px solid #ffffff33;color:#f0fff4;border-radius:10px;padding:7px 11px;font-size:11px;font-weight:800;box-shadow:0 7px 20px #0003;backdrop-filter:blur(8px)}.cmh-map-tools button:hover{background:#1a6449}.cmh-map-legend{position:absolute;z-index:17;bottom:50px;left:16px;display:flex;gap:8px;flex-wrap:wrap}.cmh-legend-pill{background:#103b2ced;border:1px solid #ffffff32;border-radius:9px;padding:8px 10px;display:flex;align-items:center;gap:7px;font-size:10px;font-weight:800;color:#d8f8e7;box-shadow:0 4px 20px #0002}.cmh-legend-pill i{width:10px;height:10px;border-radius:50%;display:inline-block;background:#27d69a}.cmh-legend-pill i.gold{background:#ffbf53}.cmh-map-footer{position:absolute;bottom:0;left:0;right:0;z-index:20;height:36px;display:flex;align-items:center;justify-content:space-between;gap:10px;background:#082e23;color:#c0ddcd;padding:0 13px;font-size:10px;border-top:1px solid #ffffff29}.cmh-map-status{background:#0d3225;border:1px solid #ffffff30;color:#d2f5e2;border-radius:10px;padding:10px 12px;position:absolute;right:15px;bottom:99px;z-index:24;max-width:min(390px,calc(100% - 30px));box-shadow:0 8px 28px #0005;font-size:11px;line-height:1.45}.cmh-map-status[data-tone="warning"]{color:#ffe6b0;border-color:#e7b75199}.cmh-map-status[data-tone="error"]{color:#ffceca;border-color:#df6f6888}
.cmh-overlay{position:absolute;inset:0 0 36px;z-index:14;display:flex;justify-content:center;align-items:center;pointer-events:none;background:radial-gradient(circle,#173e2c77,#0b251e88);}.cmh-overlay-card{max-width:360px;text-align:center;background:#0c2b20ed;color:#effaf2;border:1px solid #ffffff2a;box-shadow:0 18px 50px #0005;border-radius:19px;padding:26px 27px;}.cmh-overlay-icon{width:54px;height:54px;background:#17563e;border:1px solid #ffffff2b;display:grid;place-items:center;font-size:26px;border-radius:17px;margin:0 auto 13px}.cmh-overlay-title{font-size:17px;font-weight:850;margin:0 0 8px}.cmh-overlay-desc{font-size:11px;color:#b1d0bd;line-height:1.6;margin:0}.cmh-overlay.error .cmh-overlay-card{border-color:#d98d87}
.cmh-tooltip{position:absolute;z-index:23;pointer-events:none;max-width:240px;min-width:155px;background:#0c3024ed;color:#fff;border:1px solid #ffffff36;border-radius:9px;padding:8px 10px;font-size:10px;box-shadow:0 7px 19px #0004}.cmh-tooltip b{display:block;font-size:12px;margin-bottom:3px}.cmh-tooltip span{color:#bce2ce;line-height:1.5}.cmh-info-tag{display:inline-flex;align-items:center;gap:7px;background:#e1f4e8;color:#117e51;border-radius:8px;padding:6px 8px;font-size:10px;font-weight:800;}
.cmh-bottom{margin-top:18px;background:white;border:1px solid var(--line);border-radius:17px;padding:18px 20px;box-shadow:0 10px 25px #143b2310}.cmh-bottom-header{display:flex;align-items:center;justify-content:space-between;gap:10px}.cmh-bottom h2{font-size:17px;margin:0;letter-spacing:-.02em}.cmh-bottom-sub{font-size:11px;color:#7b8f80;margin-top:5px}.cmh-recent-grid{display:grid;grid-template-columns:repeat(3,minmax(0,1fr));gap:10px;margin-top:14px}.cmh-mission-card{border:1px solid #e0ebe3;border-radius:12px;background:#fafdfa;padding:12px}.cmh-mission-card strong{font-size:12px;display:block;white-space:nowrap;overflow:hidden;text-overflow:ellipsis}.cmh-mission-card small{display:block;color:#718677;line-height:1.6;font-size:10px;margin:6px 0}.cmh-mission-card a{font-size:11px;color:#087a52;font-weight:800;text-decoration:none}.cmh-mission-card a:hover{text-decoration:underline}
/* Edición segura: conserva composición con revisión auditada y controles visibles */
.cmh-edit-notice{margin:0 0 15px;padding:13px 17px;border:1px solid #b4d9c3;background:#e6f5eb;border-radius:13px;color:#285a40;display:flex;gap:12px;align-items:center;justify-content:space-between;flex-wrap:wrap;font-size:12px;line-height:1.5}
.cmh-edit-notice strong{font-weight:850;color:#0b6645}.cmh-edit-notice a{padding:8px 12px;background:#fff;color:#076b4c;border:1px solid #b8d7c3;border-radius:9px;text-decoration:none;font-weight:800}
.cmh-filterbar{display:flex;gap:6px;margin:8px 0 7px}.cmh-filterbar button{flex:1 1 0;border:1px solid #d7e5db;border-radius:9px;background:#fbfdfa;color:#476c58;font-size:10px;font-weight:800;min-height:34px;padding:6px 8px}.cmh-filterbar button[aria-pressed="true"]{background:#0a7553;border-color:#0a7553;color:#fff}
.cmh-reason{border-top:1px solid var(--line);padding:12px 19px 13px;background:#f8fcf9}.cmh-reason label{display:block;font-size:11px;font-weight:800;color:#365d47}.cmh-reason textarea{margin-top:7px;width:100%;min-height:67px;resize:vertical;border:1px solid #d1e3d6;border-radius:10px;padding:9px 10px;background:#fff;color:#284b36;font:inherit;font-size:11px}.cmh-reason small{display:block;margin-top:6px;color:#718a7a;font-size:10px;line-height:1.45}.cmh-edit-diff{font-size:11px;font-weight:800;color:#0b7952;padding:5px 0 0}
.cmh-mission-actions{display:flex;align-items:center;gap:12px;flex-wrap:wrap;margin-top:10px}.cmh-mission-actions a{font-size:11px;color:#087a52;font-weight:800;text-decoration:none}.cmh-mission-actions a:hover{text-decoration:underline}.cmh-mission-actions a.cmh-edit-link{background:#e1f3e8;color:#076345;padding:7px 11px;border-radius:8px;border:1px solid #badaca}.cmh-mission-card.editing{border-color:#46a77e;background:#f0faf3}
.cmh-mapbox:fullscreen{border-radius:0;min-height:100vh}.cmh-mapbox:fullscreen .cmh-map{height:100vh;min-height:100vh}
@media(max-width:1250px){.cmh-form{grid-template-columns:minmax(280px,330px) minmax(0,1fr)}.cmh-mapbox,.cmh-map{min-height:690px}.cmh-map-titles{max-width:50%}}
@media(max-width:960px){.cmh{padding:18px 14px 30px}.cmh-form{grid-template-columns:1fr}.cmh-sidebar{display:grid;grid-template-columns:1fr 1fr;}.cmh-sidebar>.cmh-panel-head{grid-column:1/-1}.cmh-sidebar>.cmh-fields{border-right:1px solid var(--line)}.cmh-sidebar>.cmh-selection{padding-top:17px}.cmh-sidebar>.cmh-actions{grid-column:1/-1}.cmh-mapbox,.cmh-map{min-height:610px}.cmh-recent-grid{grid-template-columns:repeat(2,minmax(0,1fr))}}
@media(max-width:600px){.cmh-head{align-items:flex-start;flex-direction:column}.cmh-title{font-size:26px}.cmh-back{padding:8px 12px}.cmh-sidebar{display:flex}.cmh-sidebar>.cmh-fields{border-right:0}.cmh-mapbox,.cmh-map{min-height:560px}.cmh-map-titles{max-width:70%;padding:10px}.cmh-map-name{font-size:12px}.cmh-map-meta{font-size:9px}.cmh-map-tools{max-width:45%;gap:4px}.cmh-map-tools button{padding:7px 8px;min-height:32px;min-width:31px;font-size:9px}.cmh-map-top{top:9px;left:9px;right:9px}.cmh-map-legend{left:9px;bottom:48px;gap:4px}.cmh-legend-pill{padding:6px 7px;font-size:9px}.cmh-map-footer{font-size:9px}.cmh-map-status{bottom:94px;right:9px;font-size:10px}.cmh-recent-grid{grid-template-columns:1fr}}
</style>
@endverbatim
@endpush

@section('content')
<div class="cmh" id="cmh-root">
    <header class="cmh-head">
        <div>
            <div class="cmh-eyebrow">SIGMAD Campo / Planificación espacial</div>
            <h1 class="cmh-title">{{ $editMission ? 'Editar misión de campo' : 'Preparar misión de campo' }}</h1>
            <p class="cmh-subtitle">{{ $editMission ? 'Corrige la lista de copas, agrega nuevas o retira las no utilizadas. Cada cambio queda registrado.' : 'Selecciona copas sobre el ortomosaico en alta resolución, revisa su ubicación y construye una misión trazable. Las predicciones de IA no se modifican.' }}</p>
        </div>
        <a class="cmh-back" href="{{ route('campo.index') }}">← Volver a SIGMAD Campo</a>
    </header>

    @if(session('status')) <div class="cmh-alert" role="status">{{ session('status') }}</div> @endif
    @if($errors->any()) <div class="cmh-alert error" role="alert">{{ implode(' · ', $errors->all()) }}</div> @endif
    @if($editMission)
        <div class="cmh-edit-notice"><span><strong>Modo edición:</strong> {{ $editMission->title }} · {{ $editMission->selected_count }} copas guardadas. Solo se permite cambiar el nombre y los árboles, no la parcela ni el análisis. Las copas ya utilizadas como evidencia de campo están protegidas.</span><a href="{{ route('campo.missions.create') }}">+ Crear misión nueva</a></div>
    @endif

    <form id="mission-form" method="POST" action="{{ $editMission ? route('campo.missions.update', ['mission' => $editMission->uuid]) : route('campo.missions.store') }}" class="cmh-form">
        @csrf
        @if($editMission)
            @method('PUT')
            <input type="hidden" name="revision_token" value="{{ $editToken }}">
        @endif
        <aside class="cmh-panel cmh-sidebar" aria-label="Configuración de misión">
            <div class="cmh-panel-head"><span class="cmh-step">Paso 01 · Configuración</span><h2 class="cmh-panel-title">Datos de la misión</h2><p class="cmh-panel-sub">Selecciona la parcela y el análisis de referencia.</p></div>
            <div class="cmh-fields">
                <label class="cmh-field">Nombre de la misión
                    <input name="title" id="mission-title" required minlength="3" maxlength="120" value="{{ old('title', $editMission?->title ?? 'Levantamiento piloto') }}" placeholder="Ej. Brigada 01 · Parcela norte">
                </label>
                <label class="cmh-field">Parcela asignada
                    <select name="id_parcela" id="mission-parcel" required @disabled((bool) $editMission)><option value="">Selecciona una parcela…</option>
                        @foreach($parcelas as $p)
                            <option value="{{ $p->id_parcela }}" @selected((string)old('id_parcela', $editMission?->id_parcela) === (string)$p->id_parcela)>{{ $p->nom_parcela }}</option>
                        @endforeach
                    </select>
                </label>
                <label class="cmh-field">Análisis UAV completado
                    <select name="analysis_uuid" id="mission-analysis" required @disabled((bool) $editMission)><option value="">Selecciona un análisis…</option>
                        @foreach($jobs as $j)
                            <option value="{{ $j['uuid'] }}" @selected(old('analysis_uuid', $editMission?->analysisJob?->uuid) === $j['uuid'])>{{ $j['label'] }}</option>
                        @endforeach
                    </select>
                </label>
            </div>
            <div class="cmh-divider"></div>
            <div class="cmh-selection">
                <div class="cmh-section-caption">Paso 02 · Selección espacial <span class="cmh-info-tag">Máx. 200</span></div>
                <div class="cmh-selection-head"><span class="cmh-selection-title">Copas elegidas</span><div class="cmh-count"><strong id="selected-count">0</strong><span>/ 200</span></div></div>
                <div class="cmh-meter"><span id="selected-progress"></span></div>
                <p class="cmh-hint">Haz clic sobre los polígonos del mapa o utiliza el buscador por ID operativo.</p>
                <div class="cmh-search">
                    <svg viewBox="0 0 24 24" fill="none" stroke-width="1.8"><circle cx="10.5" cy="10.5" r="7.3"/><path d="m16 16 5 5"/></svg>
                    <input type="search" id="candidate-search" placeholder="Buscar copa, ej. OBJ_084" autocomplete="off" aria-label="Buscar por ID operativo">
                </div>
                <div class="cmh-filterbar" role="group" aria-label="Filtrar copas de la misión">
                    <button type="button" id="filter-selected" aria-pressed="{{ $editMission ? 'true' : 'false' }}">● Solo seleccionadas</button>
                    <button type="button" id="filter-all" aria-pressed="{{ $editMission ? 'false' : 'true' }}">◯ Mostrar todas</button>
                </div>
                <p class="cmh-hint">Este filtro solo oculta o muestra copas. Quita o agrega árboles con los botones y guarda para confirmar.</p>
                <div class="cmh-section-caption">Catálogo de copas <span id="candidate-count">0 disponibles</span></div>
                <div id="candidate-list" class="cmh-candidate-list"><div class="cmh-empty-list">Selecciona un análisis para cargar las copas.</div></div>
                <div class="cmh-section-caption">Selección de la misión <span id="selected-caption">0 elegidas</span></div>
                <div id="selected-list" class="cmh-selected"><div class="cmh-empty-list" style="width:100%">Sin copas seleccionadas.</div></div>
                <div id="selected-inputs" hidden></div>
            </div>
            @if($editMission)
            <div class="cmh-reason">
                <label for="change-reason">Motivo de la corrección (obligatorio)
                    <textarea id="change-reason" name="change_reason" minlength="8" maxlength="500" required placeholder="Ej.: corregí 2 copas superpuestas y agregué 3 copas accesibles para la brigada">{{ old('change_reason') }}</textarea>
                </label>
                <small>No se pierden las predicciones originales. Si modificas las copas, vuelve a generar el paquete HD y solicita a los equipos descargar la misión actualizada.</small>
                <div class="cmh-edit-diff" id="edit-diff" aria-live="polite">Sin cambios pendientes</div>
            </div>
            @endif
            <div class="cmh-actions">
                <div class="cmh-actions-row">
                    <button type="button" id="clear-mission" class="cmh-btn">Quitar todas</button>
                    <button type="submit" id="save-mission" class="cmh-btn primary" disabled>{{ $editMission ? 'Guardar cambios' : 'Guardar misión' }}</button>
                </div>
                <p class="cmh-save-caption">Los técnicos asociados a esta parcela podrán descargar la misión. La asignación individual por brigada aún no está implementada.</p>
            </div>
        </aside>

        <section id="mission-map-shell" class="cmh-panel cmh-mapbox" aria-label="Visor geoespacial de selección de copas">
            <div id="mission-map" class="cmh-map" role="application" aria-label="Mapa interactivo de copas"></div>
            <div class="cmh-map-top">
                <div class="cmh-map-titles">
                    <div class="cmh-map-kicker">Ortomosaico · Visor científico</div>
                    <div id="map-analysis-name" class="cmh-map-name">Selecciona un análisis UAV</div>
                    <div id="map-analysis-meta" class="cmh-map-meta">GeoJSON + COG HD · Proyección UTM</div>
                </div>
                <div class="cmh-map-tools" aria-label="Herramientas de navegación del mapa">
                    <button id="map-fit" type="button" title="Ajustar al ortomosaico" aria-label="Ajustar extensión">⊞ <span class="cmh-tool-label">Ajustar</span></button>
                    <button id="map-fit-selected" type="button" title="Acercar a copas seleccionadas" aria-label="Enfocar selección">◎</button>
                    <button id="map-toggle-crowns" type="button" title="Mostrar u ocultar contornos" aria-pressed="true">◉</button>
                    <button id="map-fullscreen" type="button" title="Pantalla completa" aria-label="Pantalla completa">⛶</button>
                </div>
            </div>
            <div id="map-empty" class="cmh-overlay">
                <div class="cmh-overlay-card">
                    <div class="cmh-overlay-icon">🌲</div>
                    <h2 class="cmh-overlay-title">Tu bosque, listo para explorar</h2>
                    <p class="cmh-overlay-desc">Selecciona un análisis UAV en el panel izquierdo para visualizar el ortomosaico y elegir las copas de la misión.</p>
                </div>
            </div>
            <div id="map-tooltip" class="cmh-tooltip" hidden><b id="map-tooltip-title">—</b><span id="map-tooltip-meta">—</span></div>
            <div class="cmh-map-legend"><span class="cmh-legend-pill"><i></i> Disponible</span><span class="cmh-legend-pill"><i class="gold"></i> Seleccionada</span></div>
            <div id="map-status" class="cmh-map-status" role="status" aria-live="polite">Selecciona un análisis para comenzar.</div>
            <div class="cmh-map-footer"><span id="map-crs">CRS —</span><span id="map-coords">X — · Y —</span><span id="map-quality">Base: pendiente</span></div>
        </section>
    </form>

    <section class="cmh-bottom" aria-label="Misiones recientes">
        <div class="cmh-bottom-header"><div><h2>Misiones recientes</h2><div class="cmh-bottom-sub">Historial de misiones guardadas y exportaciones reproducibles.</div></div><span class="cmh-info-tag">Datos originales intactos</span></div>
        <div class="cmh-recent-grid">
            @forelse($missions as $m)
                <div class="cmh-mission-card {{ $editMission && $editMission->uuid === $m->uuid ? 'editing' : '' }}">
                    <strong>{{ $m->title }}</strong>
                    <small>Parcela {{ $m->id_parcela }} · {{ $m->selected_count }} copas · {{ $m->status }} · {{ $m->created_at?->format('Y-m-d') }}</small>
                    <div class="cmh-mission-actions">
                        @if($m->status === 'active')
                            <a class="cmh-edit-link" href="{{ route('campo.missions.create', ['edit' => $m->uuid]) }}">✎ Editar misión</a>
                        @endif
                        <a href="{{ route('campo.mission.export', ['mission' => $m->uuid]) }}">Descargar JSON HD</a>
                    </div>
                </div>
            @empty
                <p class="cmh-empty-list">Todavía no hay misiones creadas.</p>
            @endforelse
        </div>
    </section>
</div>

{{-- Solo dependencias de visualización. No se cargan misiones.js, misiones.css ni campo-mapa.js. --}}
<script src="https://cdn.jsdelivr.net/npm/proj4@2.15.0/dist/proj4.js"></script>
<script src="https://cdn.jsdelivr.net/npm/geotiff@3.1.0-beta.0/dist-browser/geotiff.js"></script>
<script src="https://cdn.jsdelivr.net/npm/ol@10.10.0/dist/ol.js"></script>
<script>
window.SIGMAD_MISSIONS_BOOT = @json($jobs);
window.SIGMAD_MISSIONS_OLD_IDS = @json(old('selected_ids', $editIds));
window.SIGMAD_MISSIONS_EDIT_UUID = @json($editMission?->uuid);
window.SIGMAD_MISSIONS_EDIT_IDS = @json($editIds);
</script>
@verbatim
<script>
(() => {
    'use strict';
    const $ = id => document.getElementById(id);
    const jobs = Array.isArray(window.SIGMAD_MISSIONS_BOOT) ? window.SIGMAD_MISSIONS_BOOT : [];
    const select = $('mission-analysis');
    const isEditing=Boolean(window.SIGMAD_MISSIONS_EDIT_UUID);
    const originalIds=new Set((window.SIGMAD_MISSIONS_EDIT_IDS||[]).map(String));
    let selectedOnly=isEditing;
    const selected = new Set();
    const registry = new Map();
    let map=null, vectorSource=null, vectorLayer=null, previewLayer=null, cogLayer=null;
    let currentJob=null, loadSeq=0, abortGeo=null, abortCog=null, hoveredId=null;
    let polygonsVisible=true, cogSource=null, previewError=false, cogReady=false, lastQuality='pendiente';
    let allIds=[];

    function status(message, tone='neutral') { const el=$('map-status');el.textContent=message;el.dataset.tone=tone; }
    function quality(message) { lastQuality=message; $('map-quality').textContent=`Base: ${message}`; }
    function errorOverlay(title,desc) {
        const overlay=$('map-empty');overlay.hidden=false;overlay.classList.add('error');
        overlay.querySelector('.cmh-overlay-title').textContent=title;
        overlay.querySelector('.cmh-overlay-desc').textContent=desc;
    }
    function hideOverlay(){ $('map-empty').hidden=true;$('map-empty').classList.remove('error'); }
    function ensureLibraries() {
        if(!window.ol || !window.proj4) {
            errorOverlay('Librerías cartográficas no disponibles','No se cargaron OpenLayers o Proj4. Comprueba la conexión a cdn.jsdelivr.net y vuelve a cargar la página.');
            status('Error: no se cargaron OpenLayers o Proj4.', 'error');return false;
        }
        return true;
    }
    function normalizeModel(value){const s=String(value||'').toLowerCase();return s.includes('yolo')?'YOLO':s.includes('mask')?'Mask R-CNN':'IA';}
    function area(feature) {try {return feature.getGeometry().getArea();}catch {return NaN;}}
    function selectedExtents() {
        const ext=ol.extent.createEmpty();for(const id of selected){const f=registry.get(id);if(f)ol.extent.extend(ext,f.getGeometry().getExtent());}
        return ol.extent.isEmpty(ext) ? null : ext;
    }
    function fitExtent(ext, maxZoom=21){if(!map||!ext)return;map.updateSize();map.getView().fit(ext,{padding:[72,62,72,62],duration:320,maxZoom,minResolution:0.025});}
    function focusId(id) {const f=registry.get(id);if(!f||!map)return;const e=f.getGeometry().getExtent();const pad=Math.max(8,Math.max(e[2]-e[0],e[3]-e[1])*.7);fitExtent(ol.extent.buffer(e,pad),22);}
    function updateMapStyle(){vectorSource?.changed();}
    let styleNormal=null, stylePicked=null, styleHover=null;
    function buildStyles(){
        styleNormal=new ol.style.Style({stroke:new ol.style.Stroke({color:'#38f2b2',width:1.65}),fill:new ol.style.Fill({color:'rgba(21,219,140,.10)'})});
        stylePicked=new ol.style.Style({stroke:new ol.style.Stroke({color:'#ffc35c',width:3.25}),fill:new ol.style.Fill({color:'rgba(255,181,58,.34)'})});
        styleHover=new ol.style.Style({stroke:new ol.style.Stroke({color:'#f8fff3',width:2.6}),fill:new ol.style.Fill({color:'rgba(48,217,158,.22)'})});
    }
    function styleFeature(f){const id=String(f.get('operational_id')??'');if(selectedOnly&&!selected.has(id))return null;return selected.has(id)?stylePicked:id===hoveredId?styleHover:styleNormal;}
    function renderSelected(){
        $('selected-count').textContent=String(selected.size);
        $('selected-caption').textContent=`${selected.size} elegidas`;
        $('selected-progress').style.width=`${Math.min(selected.size/200*100,100)}%`;
        $('save-mission').disabled=selected.size===0 || !currentJob;
        $('save-mission').textContent=isEditing?`Guardar cambios · ${selected.size} copas`:`Guardar misión · ${selected.size} copas`;
        if(isEditing){
            const add=[...selected].filter(id=>!originalIds.has(id)).length;
            const remove=[...originalIds].filter(id=>!selected.has(id)).length;
            $('edit-diff').textContent=`Cambios pendientes: +${add} copa${add===1?'':'s'} · −${remove} copa${remove===1?'':'s'}`;
        }
        const out=$('selected-list'), inputs=$('selected-inputs');out.replaceChildren();inputs.replaceChildren();
        if(!selected.size){const n=document.createElement('div');n.className='cmh-empty-list';n.style.width='100%';n.textContent='Sin copas seleccionadas.';out.append(n);}
        for(const id of selected){
            const ch=document.createElement('div');ch.className='cmh-chip';
            const a=document.createElement('span');a.textContent=id;
            const btn=document.createElement('button');btn.type='button';btn.textContent='×';btn.title=`Quitar ${id}`;btn.setAttribute('aria-label',`Quitar ${id}`);
            btn.addEventListener('click',()=>toggleSelection(id));ch.append(a,btn);out.append(ch);
            const inp=document.createElement('input');inp.type='hidden';inp.name='selected_ids[]';inp.value=id;inputs.append(inp);
        }
        updateMapStyle();
    }
    function toggleSelection(id){
        if(!registry.has(id))return;
        if(selected.has(id)){selected.delete(id)}else{if(selected.size>=200){status('La misión permite como máximo 200 copas.', 'warning');return;}selected.add(id)}
        renderSelected();renderCatalog();
        status(`${selected.size} copa${selected.size===1?'':'s'} seleccionada${selected.size===1?'':'s'}. Los polígonos originales no se modifican.`);
    }
    function renderCatalog(){
        const list=$('candidate-list');list.replaceChildren();const query=$('candidate-search').value.trim().toLowerCase();
        const visibleIds=selectedOnly?allIds.filter(id=>selected.has(id)):allIds;
        const matches=query?visibleIds.filter(id=>id.toLowerCase().includes(query)):visibleIds;
        const ids=matches.slice(0,60);
        const total=matches.length;
        $('candidate-count').textContent=`${total} ${selectedOnly?'elegida':'disponible'}${total===1?'':'s'}`;
        if(!ids.length){const n=document.createElement('div');n.className='cmh-empty-list';n.textContent=selectedOnly?'No hay copas visibles. Pulsa «Mostrar todas» para volver a agregar.':query?'No se encontraron copas.':'Selecciona un análisis para cargar las copas.';list.append(n);return;}
        const frag=document.createDocumentFragment();
        for(const id of ids){
            const f=registry.get(id);const picked=selected.has(id);
            const row=document.createElement('div');row.className=`cmh-item${picked?' selected':''}`;
            const dot=document.createElement('i');dot.className=`cmh-dot${normalizeModel(f.get('primary_model'))==='YOLO'?' yolo':''}`;
            const body=document.createElement('div');body.className='cmh-item-body';
            const name=document.createElement('span');name.className='cmh-item-id';name.textContent=id;
            const meta=document.createElement('span');meta.className='cmh-item-meta';const a=area(f);meta.textContent=`${normalizeModel(f.get('primary_model'))} · ${Number.isFinite(a)?a.toFixed(1)+' m² de máscara':'área no disponible'}`;
            body.append(name,meta);
            const focus=document.createElement('button');focus.type='button';focus.className='cmh-mini-btn';focus.textContent='Ver';focus.title=`Enfocar ${id}`;focus.addEventListener('click',()=>focusId(id));
            const toggle=document.createElement('button');toggle.type='button';toggle.className='cmh-mini-btn';toggle.textContent=picked?'Quitar':'Elegir';toggle.addEventListener('click',()=>toggleSelection(id));
            row.append(dot,body,focus,toggle);frag.append(row);
        }
        list.append(frag);
        if(total>60){const n=document.createElement('div');n.className='cmh-empty-list';n.textContent=`Se muestran 60 de ${total} copas. Escribe un ID para buscar las demás.`;list.append(n)}
    }
    function dropMap(){
        if(abortGeo){abortGeo.abort();abortGeo=null}if(abortCog){abortCog.abort();abortCog=null}
        if(map){map.setTarget(null);map=null}
        vectorSource=null;vectorLayer=null;previewLayer=null;cogLayer=null;cogSource=null;previewError=false;cogReady=false;hoveredId=null;
    }
    function loadProjection(crs){
        const m=/^EPSG:(326|327)(\d{2})$/.exec(String(crs||''));if(!m)return false;
        const zone=Number(m[2]);if(zone<1||zone>60)return false;
        proj4.defs(crs,`+proj=utm +zone=${zone} ${m[1]==='327'?'+south ':''}+datum=WGS84 +units=m +no_defs +type=crs`);
        ol.proj.proj4.register(proj4);return true;
    }
    async function initCog(job,seq){
        if(!job.cog_url){quality(job.preview_url?'vista previa':'sin raster');return;}
        if(typeof GeoTIFF==='undefined' || !ol.source.GeoTIFF || !ol.layer.WebGLTile){
            quality('vista previa');status('El motor COG no está disponible; se mantiene la vista previa.','warning');return;
        }
        abortCog=new AbortController();
        try {
            const res=await fetch(job.cog_url,{credentials:'same-origin',headers:{Accept:'application/json'},cache:'no-store',signal:abortCog.signal});
            if(!res.ok)throw Error(`COG HTTP ${res.status}`);
            const info=await res.json();if(seq!==loadSeq)return;
            if(info.status!=='ok'||info.available!==true||!info.url)throw Error('Respuesta COG no válida');
            cogSource=new ol.source.GeoTIFF({sources:[{url:info.url}],projection:job.crs,convertToRGB:'auto',normalize:true,interpolate:true,wrapX:false,transition:120});
            cogLayer=new ol.layer.WebGLTile({source:cogSource,visible:true,zIndex:2,preload:0});map.addLayer(cogLayer);quality('cargando HD…');
            cogSource.on('change',()=>{
                if(seq!==loadSeq||!cogSource)return;
                if(cogSource.getState()==='ready'){
                    cogReady=true;previewLayer?.setVisible(false);quality('COG HD activo');
                    status(`${registry.size} copas disponibles · ortomosaico de alta resolución activo.`);
                } else if(cogSource.getState()==='error'){
                    cogReady=false;cogLayer?.setVisible(false);previewLayer?.setVisible(true);
                    quality(job.preview_url?'vista previa (COG falló)':'COG no disponible');
                    status('No se pudo leer el COG. Se mantiene el mapa y la vista previa.','warning');
                }
            });
        } catch(e) {
            if(seq!==loadSeq||e.name==='AbortError')return;
            quality(job.preview_url?'vista previa (COG falló)':'COG no disponible');
            status(`COG no disponible: ${e.message}. La selección de polígonos sigue funcionando.`, 'warning');
        }
    }
    function setMapEvents(){
        map.on('singleclick',evt=>{
            if(window.SIGMAD_MISSIONS_MANUAL_DRAWING || Date.now()<(window.SIGMAD_MISSIONS_MANUAL_DRAWING_UNTIL||0))return;
            if(!polygonsVisible)return;
            const f=map.forEachFeatureAtPixel(evt.pixel,x=>x,{hitTolerance:5,layerFilter:layer=>layer===vectorLayer});
            if(!f)return;const id=String(f.get('operational_id')??'');if(id)toggleSelection(id);
        });
        map.on('pointermove',evt=>{
            if(!map||evt.dragging)return;
            const xy=evt.coordinate;$('map-coords').textContent=`X ${xy[0].toFixed(1)} · Y ${xy[1].toFixed(1)}`;
            const feature=polygonsVisible?map.forEachFeatureAtPixel(evt.pixel,x=>x,{hitTolerance:3,layerFilter:l=>l===vectorLayer}):null;
            const id=feature?String(feature.get('operational_id')??''):null;
            if(hoveredId!==id){hoveredId=id;updateMapStyle();}
            map.getTargetElement().style.cursor=id?'pointer':'';
            const tip=$('map-tooltip');if(!id){tip.hidden=true;return;}
            $('map-tooltip-title').textContent=id;
            const a=area(feature);$('map-tooltip-meta').textContent=`${selected.has(id)?'Seleccionada':'Disponible'} · ${normalizeModel(feature.get('primary_model'))}${Number.isFinite(a)?' · '+a.toFixed(1)+' m² de máscara':''}`;
            tip.hidden=false;
            const shell=$('mission-map-shell').getBoundingClientRect();const px=evt.originalEvent.clientX-shell.left,py=evt.originalEvent.clientY-shell.top;
            tip.style.left=Math.min(Math.max(px+15,10),shell.width-245)+'px';tip.style.top=Math.max(py-60,8)+'px';
        });
    }
    async function loadAnalysis(){
        const seq=++loadSeq;const previousOld=Array.isArray(window.SIGMAD_MISSIONS_OLD_IDS)?window.SIGMAD_MISSIONS_OLD_IDS.map(String):[];
        dropMap();selected.clear();registry.clear();allIds=[];currentJob=null;renderSelected();renderCatalog();
        const job=jobs.find(j=>String(j.uuid)===select.value);
        if(!job){$('map-analysis-name').textContent='Selecciona un análisis UAV';$('map-analysis-meta').textContent='GeoJSON + COG HD · Proyección UTM';$('map-crs').textContent='CRS —';quality('pendiente');$('map-empty').hidden=false;status('Selecciona un análisis para comenzar.');return;}
        if(!ensureLibraries())return;
        const b=job.bounds;
        if(!Array.isArray(b)||b.length!==4||!b.every(x=>Number.isFinite(Number(x)))||!(b[0]<b[2]&&b[1]<b[3])||!loadProjection(job.crs)){
            errorOverlay('Georreferenciación inválida','Este análisis no contiene una extensión UTM válida. Comprueba los bounds y el CRS.');status('Extensión o proyección de análisis inválida.','error');return;
        }
        currentJob=job;
        $('map-analysis-name').textContent=job.label||'Análisis UAV';
        $('map-analysis-meta').textContent='Copas de IA · Fuente original inalterada';
        $('map-crs').textContent=`CRS ${job.crs}`;
        $('map-empty').hidden=false; $('map-empty').querySelector('.cmh-overlay-title').textContent='Preparando ortomosaico';$('map-empty').querySelector('.cmh-overlay-desc').textContent='Cargando geometrías de copas desde el análisis UAV…';
        status('Cargando polígonos originales…');quality('cargando');
        vectorSource=new ol.source.Vector({wrapX:false});
        buildStyles();vectorLayer=new ol.layer.Vector({source:vectorSource,style:styleFeature,zIndex:15});
        const layers=[];
        if(job.preview_url){
            const src=new ol.source.ImageStatic({url:job.preview_url,imageExtent:b.map(Number),projection:job.crs,interpolate:true});
            previewLayer=new ol.layer.Image({source:src,zIndex:0});layers.push(previewLayer);
            src.on('imageloaderror',()=>{previewError=true;quality('vista previa no disponible');status('No se pudo cargar la vista previa; se mantienen los polígonos.', 'warning');});
        }
        layers.push(vectorLayer);
        map=new ol.Map({target:'mission-map',layers,view:new ol.View({projection:job.crs,center:ol.extent.getCenter(b.map(Number)),zoom:5,minZoom:0,maxZoom:26}),controls:ol.control.defaults.defaults({rotate:false,attribution:false}).extend([new ol.control.ScaleLine({units:'metric',minWidth:95})])});
        setMapEvents();map.updateSize();fitExtent(b.map(Number),19);
        window.dispatchEvent(new Event('sigmad:missions-map-ready'));
        hideOverlay();
        abortGeo=new AbortController();
        try {
            const response=await fetch(job.geojson_url,{credentials:'same-origin',headers:{Accept:'application/geo+json'},signal:abortGeo.signal});
            if(!response.ok)throw Error(`GeoJSON HTTP ${response.status}`);
            const geo=await response.json();if(seq!==loadSeq)return;
            if(geo.type!=='FeatureCollection'||!Array.isArray(geo.features))throw Error('No es un FeatureCollection');
            const feats=new ol.format.GeoJSON().readFeatures(geo,{dataProjection:'EPSG:4326',featureProjection:job.crs});
            const duplicateIds=new Set();const encountered=new Set();const validFeatures=[];
            for(const f of feats){
                const id=String(f.get('operational_id')??'');if(!id)continue;
                if(encountered.has(id)){duplicateIds.add(id);continue;}
                if(!['Polygon','MultiPolygon'].includes(f.getGeometry()?.getType()))continue;
                encountered.add(id);registry.set(id,f);validFeatures.push(f);
            }
            vectorSource.addFeatures(validFeatures);
            allIds=[...registry.keys()].sort((a,b)=>a.localeCompare(b,'es',{numeric:true}));
            if(previousOld.length){for(const id of previousOld){if(registry.has(id)&&selected.size<200)selected.add(id);}window.SIGMAD_MISSIONS_OLD_IDS=[];}
            renderSelected();renderCatalog();
            if(duplicateIds.size){status(`Atención: ${duplicateIds.size} identificadores duplicados en el GeoJSON; no guardes la misión sin revisar el análisis.`, 'warning');}
            else status(`${registry.size} copas cargadas. Toca una copa del mapa o usa el buscador.`);
            quality(job.preview_url?'vista previa':'sin raster');
        } catch(e){if(seq!==loadSeq||e.name==='AbortError')return;status(`Error al cargar copas: ${e.message}.`, 'error');errorOverlay('No se cargaron las copas','Comprueba el acceso al GeoJSON del análisis y revisa la consola de red.');$('save-mission').disabled=true;}
        if(seq!==loadSeq)return;
        await initCog(job,seq);
    }
    function applySelectionFilter(only){
        selectedOnly=only;
        $('filter-selected').setAttribute('aria-pressed',String(only));
        $('filter-all').setAttribute('aria-pressed',String(!only));
        hoveredId=null;
        $('map-tooltip').hidden=true;
        updateMapStyle();renderCatalog();
        status(only?'Mostrando únicamente las copas elegidas. Pulsa «Mostrar todas» para agregar otras.':'Mostrando todas las copas disponibles para esta misión.');
    }
    $('filter-selected').addEventListener('click',()=>applySelectionFilter(true));
    $('filter-all').addEventListener('click',()=>applySelectionFilter(false));
    $('candidate-search').addEventListener('input',renderCatalog);
    $('candidate-search').addEventListener('keydown',e=>{if(e.key==='Enter')e.preventDefault();});
    select.addEventListener('change',loadAnalysis);
    $('clear-mission').addEventListener('click',()=>{
        if(selected.size && !window.confirm('¿Quitar todas las copas de la selección actual? No se guardará nada hasta pulsar Guardar cambios.'))return;
        selected.clear();renderSelected();renderCatalog();status('Selección vaciada. El cambio todavía no está guardado.');
    });
    $('map-fit').addEventListener('click',()=>{if(currentJob)fitExtent(currentJob.bounds.map(Number),19);});
    $('map-fit-selected').addEventListener('click',()=>{if(selected.size)fitExtent(selectedExtents(),21);else status('Selecciona una o más copas para enfocarlas.','warning');});
    $('map-toggle-crowns').addEventListener('click',()=>{polygonsVisible=!polygonsVisible;if(vectorLayer)vectorLayer.setVisible(polygonsVisible);$('map-toggle-crowns').setAttribute('aria-pressed',String(polygonsVisible));$('map-toggle-crowns').title=polygonsVisible?'Ocultar contornos':'Mostrar contornos';});
    $('map-fullscreen').addEventListener('click',async()=>{const box=$('mission-map-shell');try{if(document.fullscreenElement)await document.exitFullscreen();else await box.requestFullscreen();}catch(e){status('El navegador no permitió pantalla completa.','warning');}});
    document.addEventListener('fullscreenchange',()=>{if(map)setTimeout(()=>map.updateSize(),100);});
    window.addEventListener('resize',()=>map?.updateSize());
    $('mission-form').addEventListener('submit',e=>{
        if(!currentJob||!registry.size||!selected.size||selected.size>200){e.preventDefault();status('Antes de guardar, elige un análisis y al menos una copa (máximo 200).','error');return;}
        for(const id of selected){if(!registry.has(id)){e.preventDefault();status('Existe un ID no disponible en el análisis actual.','error');return;}}
        $('save-mission').disabled=true;$('save-mission').textContent='Guardando misión…';
    });
    window.SIGMAD_MISSION_MAP_HOOK={getMap:()=>map,getJob:()=>currentJob};
    if(!ensureLibraries()){return;}
    if(select.value)loadAnalysis();else {renderSelected();renderCatalog();}
})();
</script>
@endverbatim
@if($editMission)
<script>
window.SIGMAD_MISSIONS_MANUAL_API = @json([
    'index' => route('campo.missions.manual.index', ['mission' => $editMission->uuid]),
    'store' => route('campo.missions.manual.store', ['mission' => $editMission->uuid]),
]);
</script>
<script src="{{ asset('sigmad-campo-assets/misiones-manuales-v05.js') }}?v=0500"></script>
@endif
@endsection
