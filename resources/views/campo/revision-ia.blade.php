@extends('layouts.uav')
@section('title', 'SIGMAD · Smart Annotation Studio')
@push('styles')
<link rel="stylesheet" href="{{ asset('sigmad-campo-assets/vendor/ol.css') }}">
<link rel="stylesheet" href="{{ asset('sigmad-revision-assets/revision-ia.css') }}?v=093">
<link rel="stylesheet" href="{{ asset('sigmad-revision-assets/sam-interactive.css') }}?v=010c">
@endpush
@section('content')
<main id="review-app" class="rv-wrap">
  <div class="rv-header">
    <div>
      <p class="rv-eyebrow">SIGMAD / LABORATORIO FORESTAL</p>
      <h1>Editor de copas forestales</h1>
      <p class="rv-muted">Selecciona una copa, corrige su contorno y guarda la revisión</p>
    </div>
    <div class="rv-header-right"><span class="rv-pill">V0.10 · SAM Interactive + Editor HD</span><a href="{{ url('/campo') }}" class="rv-link">Volver a Campo</a></div>
  </div>

  <div class="rv-topbar">
    <label class="rv-field rv-grow"><span>Conjunto de anotaciones a revisar</span>
      <select id="rv-mission" aria-label="Seleccionar misión"><option value="">Elige una misión…</option></select>
    </label>
    <button type="button" id="rv-load" class="rv-primary">Abrir área de trabajo</button>
    <div class="rv-meta"><span id="rv-version">Sin revisión</span><small id="rv-dirty">Sin cambios</small></div>
  </div>
  <div id="rv-alert" class="rv-alert" role="status" aria-live="polite">Selecciona un conjunto de anotaciones. El mapa HD maestro se carga por análisis, independientemente de las misiones de campo.</div>

  <div class="rv-overview" aria-label="Estado del laboratorio">
    <div class="rv-overview-intro"><span class="rv-overview-icon">✦</span><div><b>Laboratorio geoespacial de precisión</b><span>Verifica, corrige y documenta cada copa con su evidencia original.</span></div></div>
    <div class="rv-overview-stat"><strong id="rv-top-total">—</strong><span>Copas revisables</span></div>
    <div class="rv-overview-stat"><strong id="rv-top-manual">—</strong><span>Marcas de campo</span></div>
    <div class="rv-overview-stat"><strong id="rv-top-validated">—</strong><span>Validadas</span></div>
  </div>
  <div class="rv-layout">
    <section class="rv-canvas-card" aria-label="Editor cartográfico">
      <div class="rv-workspace-head">
        <div><span class="rv-section-eyebrow">MAPA INTERACTIVO</span><h2>Ortomosaico maestro HD</h2></div>
        <div class="rv-map-status" id="rv-map-quality" data-quality="waiting">● Selecciona un conjunto</div>
      </div>
      <div class="rv-tools rv-simple-tools">
        <div class="rv-workflow-step">
          <span class="rv-step-number">1</span>
          <div class="rv-step-description"><strong>Elige una copa</strong><small>Haz clic dentro de un contorno turquesa. Después selecciona cómo quieres corregirlo.</small></div>
          <span class="rv-selection-state" data-ready="no" id="rv-selection-state">Sin copa seleccionada</span>
        </div>
        <div class="rv-brush-workbench" role="group" aria-label="Editor visual de máscaras">
          <div class="rv-brush-heading"><div class="rv-step-description"><span class="rv-step-number">2</span><div><strong>Corrige con un pincel</strong><small>Trabaja sobre la copa seleccionada. Sin GPU ni alterar la IA original.</small></div></div><span class="rv-brush-chip">HD · LOCAL</span></div>
          <div class="rv-brush-controls rv-brush-primary">
            <button id="rv-brush-add" type="button" class="rv-brush-add" title="Añadir parte faltante a la copa" disabled>＋ Agregar</button>
            <button id="rv-brush-erase" type="button" class="rv-brush-erase" title="Borrar directamente una pequeña zona" disabled>− Borrar directo</button>
            <button id="rv-brush-mark" type="button" class="rv-brush-mark" title="Marcar en rojo una zona sin borrarla todavía" disabled>▧ Marcar para borrar</button>
            <button id="rv-brush-exit" type="button" disabled>↖ Terminar pincel</button>
            <label class="rv-brush-size" for="rv-brush-size">Tamaño <input id="rv-brush-size" type="range" min="3" max="45" value="12" step="1" disabled><output id="rv-brush-size-value">12 px</output></label>
          </div>
          <div class="rv-mark-actions">
            <span id="rv-mark-count" class="rv-mark-count">Sin zona marcada</span>
            <button id="rv-delete-mark" type="button" class="rv-delete-mark" disabled>Eliminar zona marcada</button>
            <button id="rv-clear-mark" type="button" disabled>Cancelar marca</button>
          </div>
          <p id="rv-brush-status" class="rv-brush-status" role="status" aria-live="polite">Selecciona una copa en el mapa para empezar.</p>
        </div>
        <div class="rv-simple-utilities">
          <div class="rv-tool-row"><button id="rv-undo" type="button" disabled>↶ Deshacer</button><button id="rv-redo" type="button" disabled>↷ Rehacer</button><span id="rv-mode">Modo: seleccionar</span></div>
        </div>
        <details class="rv-advanced">
          <summary>Herramientas adicionales <span>Dividir, dibujar copas, etiquetas y capas</span></summary>
          <div class="rv-tool-row">
            <button id="rv-accept" type="button" disabled>✓ Validar copa</button>
            <button id="rv-edit" type="button" disabled>✎ Mover vértices</button>
            <button id="rv-split" type="button" disabled>⑂ Dividir</button>
            <button id="rv-add" type="button" disabled>＋ Nueva copa</button>
            <button id="rv-reject" type="button" disabled>✕ Descartar copa completa</button>
            <button id="rv-restore" type="button" disabled>↺ Restaurar</button>
          </div>
          <div class="rv-tool-row rv-tool-secondary">
            <label><input id="rv-show-original" type="checkbox"> Mostrar predicción IA original</label>
            <label><input id="rv-show-manual" type="checkbox" checked> Mostrar marcas MAN</label>
            <button id="rv-finish-draw" type="button" hidden>Terminar dibujo</button>
            <button id="rv-cancel-draw" type="button" hidden>Cancelar dibujo</button>
          </div>
        </details>
      </div>
      <div class="rv-map-shell">
        <div id="rv-map" class="rv-map" aria-label="Ortomosaico maestro, predicciones y copas corregidas"></div>
        <canvas id="rv-brush-overlay" class="rv-brush-overlay" aria-label="Pincel geoespacial para modificar la copa seleccionada"></canvas>
        <div class="rv-floating-tools" aria-label="Navegación del mapa">
          <button id="rv-fit-all" type="button" title="Ver área completa" disabled>⛶ <span>Área completa</span></button>
          <button id="rv-focus" type="button" title="Enfocar la copa seleccionada" disabled>◎ <span>Enfocar copa</span></button>
          <button id="rv-zoom-in" type="button" title="Acercar" disabled>＋ <span>Acercar</span></button>
          <button id="rv-zoom-out" type="button" title="Alejar" disabled>− <span>Alejar</span></button>
          <button id="rv-zoom-detail" type="button" title="Ampliar hasta inspeccionar píxeles nativos" disabled>⌕ <span>Zoom de detalle</span></button>
        </div>
        <div class="rv-map-corner"><span id="rv-zoom-warning" class="rv-zoom-warning" hidden>Ampliación digital · no añade detalle</span><span id="rv-map-scale">Escala: —</span><span id="rv-map-position">UTM: —</span></div>
      </div>
      <div class="rv-map-footer">
        <span><i class="rv-dot original"></i> IA original</span>
        <span><i class="rv-dot pending"></i> Pendiente</span>
        <span><i class="rv-dot accepted"></i> Validada</span>
        <span><i class="rv-dot new"></i> Añadida / dividida</span>
        <span><i class="rv-dot rejected"></i> Rechazada</span>
      </div>
    </section>

    <aside class="rv-side">
      <section class="rv-panel rv-navigation-panel">
        <div class="rv-panel-title"><h2>Buscar objetivo</h2><span>Localización rápida</span></div>
        <label class="rv-field"><span>Marca manual a inspeccionar</span><select id="rv-jump-man" disabled><option value="">Seleccionar marca MAN…</option></select></label>
        <p class="rv-hint">Selecciona una marca para centrar el mapa en su ubicación. Una marca MAN todavía no confirma un árbol omitido.</p>
      </section>
      <section class="rv-panel">
        <div class="rv-panel-title"><h2>Copa seleccionada</h2><span id="rv-selected">Ninguna seleccionada</span></div>
        <details class="rv-attrs-details"><summary>Etiquetas, especie y atributos <span>Opcional</span></summary>
        <div class="rv-fields">
          <label class="rv-field"><span>Estado de revisión</span><select id="rv-status" disabled>
            <option value="pending">Pendiente</option><option value="accepted">IA correcta · aceptada</option>
            <option value="edited">Contorno corregido</option><option value="split">Parte de una fusión</option>
            <option value="added">Copa nueva / omitida</option><option value="superseded">Máscara fusionada (sustituida)</option>
            <option value="rejected">Predicción descartada</option>
          </select></label>
          <div class="rv-two">
            <label class="rv-field"><span>Especie probable</span><select id="rv-species" disabled>
              <option value="PL">Pinus lawsonii</option><option value="QR">Quercus rugosa</option>
              <option value="O">Otra</option><option value="ND">No determinada</option>
            </select></label>
            <label class="rv-field"><span>Visibilidad</span><select id="rv-visibility" disabled>
              <option value="ND">Sin evaluar</option><option value="F">Fácil</option>
              <option value="M">Moderada</option><option value="D">Difícil</option><option value="MD">Muy difícil</option>
            </select></label>
          </div>
          <label class="rv-field"><span>Confianza en el contorno</span><select id="rv-confidence" disabled>
            <option value="ND">Sin evaluar</option><option value="alta">Alta</option>
            <option value="media">Media</option><option value="baja">Baja</option>
          </select></label>
          <label class="rv-field"><span>Máscara IA de origen</span><input id="rv-origin" disabled placeholder="OBJ_... o sin predicción"></label>
          <label class="rv-field"><span>Vincular marca manual (opcional)</span><select id="rv-manual" disabled><option value="">Sin vínculo MAN</option></select></label>
          <label class="rv-field"><span>Tree ID de campo (opcional)</span><input id="rv-tree" maxlength="80" placeholder="Ej. P04-025-A" disabled></label>
          <label class="rv-field"><span>Observaciones / incertidumbre</span><textarea id="rv-note" rows="3" maxlength="1000" placeholder="Motivo de corrección, límites dudosos…" disabled></textarea></label>
          <button id="rv-apply-attrs" type="button" class="rv-primary" disabled>Aplicar etiquetas</button>
        </div></details>
        <p class="rv-brush-metrics" id="rv-brush-metrics">Selecciona una copa para editar su máscara.</p>
        <p class="rv-hint">Las marcas MAN son indicios precampo. No equivalen automáticamente a falsos negativos. Un árbol oculto no debe recibir una máscara inventada.</p>
      </section>

      <section class="rv-panel">
        <div class="rv-panel-title"><h2>3 · Guarda la revisión</h2></div>
        <div class="rv-kpis">
          <div><strong id="rv-kpi-total">—</strong><span>Copas V2</span></div>
          <div><strong id="rv-kpi-pending">—</strong><span>Pendientes</span></div>
          <div><strong id="rv-kpi-rejected">—</strong><span>Descartadas</span></div>
          <div><strong id="rv-kpi-new">—</strong><span>Añadidas / divididas</span></div>
        </div>
        <label class="rv-field"><span>Motivo obligatorio del guardado</span>
          <textarea id="rv-reason" rows="2" placeholder="Ej. Se separaron copas fusionadas tras revisar el ortomosaico."></textarea>
        </label>
        <button type="button" class="rv-primary rv-wide" id="rv-save" disabled>Guardar revisión en SIGMAD</button>
        <button type="button" class="rv-outlined rv-wide" id="rv-export" disabled>Exportar GeoJSON V2</button>
        <p class="rv-hint">Las revisiones se guardan en una tabla separada con número de versión. Los modelos originales, los puntos MAN y las mediciones no se modifican.</p>
      </section>

      <section class="rv-panel">
        <div class="rv-panel-title"><h2>Historial de revisiones</h2><button type="button" id="rv-history-refresh" disabled>Actualizar</button></div>
        <div id="rv-history" class="rv-history"><p class="rv-muted">Sin misión abierta.</p></div>
      </section>
    </aside>
  </div>
</main>
<script>
  window.SIGMAD_REVIEW_BOOT = {
    missions: @json($missions),
    csrf: @json(csrf_token()),
    data_url: @json(route('campo.review.data', ['mission' => '__UUID__'])),
    save_url: @json(route('campo.review.save', ['mission' => '__UUID__'])),
    history_url: @json(route('campo.review.history', ['mission' => '__UUID__'])),
    revision_url: @json(route('campo.review.revision', ['mission' => '__UUID__', 'version' => '__VERSION__']))
  };
</script>
<script src="{{ asset('sigmad-campo-assets/vendor/proj4.js') }}" defer></script>
<script src="{{ asset('sigmad-campo-assets/vendor/ol.js') }}" defer></script>
<script src="{{ asset('sigmad-revision-assets/mask-engine.js') }}?v=090" defer></script>
<script src="{{ asset('sigmad-revision-assets/revision-ia.js') }}?v=010c" defer></script>
<script src="{{ asset('sigmad-revision-assets/sam-interactive.js') }}?v=010c" defer></script>
@endsection
