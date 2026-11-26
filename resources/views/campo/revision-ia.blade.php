@extends('layouts.uav')
@section('title', 'SIGMAD · Revisión y corrección IA')
@push('styles')
<link rel="stylesheet" href="{{ asset('sigmad-campo-assets/vendor/ol.css') }}">
<link rel="stylesheet" href="{{ asset('sigmad-revision-assets/revision-ia.css') }}?v=080">
@endpush
@section('content')
<main id="review-app" class="rv-wrap">
  <div class="rv-header">
    <div>
      <p class="rv-eyebrow">SIGMAD / LABORATORIO FORESTAL</p>
      <h1>Revisión y corrección IA</h1>
      <p class="rv-muted">Editor georreferenciado de copas · predicciones originales protegidas</p>
    </div>
    <div class="rv-header-right"><span class="rv-pill">V0.8.0 · Piloto</span><a href="{{ url('/campo') }}" class="rv-link">Volver a Campo</a></div>
  </div>

  <div class="rv-topbar">
    <label class="rv-field rv-grow"><span>Misión a revisar</span>
      <select id="rv-mission" aria-label="Seleccionar misión"><option value="">Elige una misión…</option></select>
    </label>
    <button type="button" id="rv-load" class="rv-primary">Abrir misión</button>
    <div class="rv-meta"><span id="rv-version">Sin revisión</span><small id="rv-dirty">Sin cambios</small></div>
  </div>
  <div id="rv-alert" class="rv-alert" role="status" aria-live="polite">Elige una misión y pulsa «Abrir misión».</div>

  <div class="rv-layout">
    <section class="rv-canvas-card" aria-label="Editor cartográfico">
      <div class="rv-tools">
        <div class="rv-tool-row">
          <button id="rv-accept" type="button" disabled>✓ Aceptar</button>
          <button id="rv-edit" type="button" disabled>✎ Modificar vértices</button>
          <button id="rv-split" type="button" disabled>⑂ Dividir</button>
          <button id="rv-add" type="button" disabled>＋ Agregar copa</button>
          <button id="rv-reject" type="button" disabled>✕ Rechazar</button>
          <button id="rv-restore" type="button" disabled>↺ Restaurar</button>
          <button id="rv-undo" type="button" disabled>Deshacer</button>
        </div>
        <div class="rv-tool-row rv-tool-secondary">
          <label><input id="rv-show-original" type="checkbox" checked> Mostrar IA original</label>
          <label><input id="rv-show-manual" type="checkbox" checked> Mostrar marcas MAN</label>
          <button id="rv-finish-draw" type="button" hidden>Terminar dibujo</button>
          <button id="rv-cancel-draw" type="button" hidden>Cancelar dibujo</button>
          <span id="rv-mode">Modo: seleccionar</span>
        </div>
      </div>
      <div id="rv-map" class="rv-map" aria-label="Ortomosaico, predicciones y copas corregidas"></div>
      <div class="rv-map-footer">
        <span><i class="rv-dot original"></i> IA original</span>
        <span><i class="rv-dot pending"></i> Pendiente</span>
        <span><i class="rv-dot accepted"></i> Validada</span>
        <span><i class="rv-dot new"></i> Añadida / dividida</span>
        <span><i class="rv-dot rejected"></i> Rechazada</span>
      </div>
    </section>

    <aside class="rv-side">
      <section class="rv-panel">
        <div class="rv-panel-title"><h2>Detalle de copa</h2><span id="rv-selected">Ninguna seleccionada</span></div>
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
          <button id="rv-apply-attrs" type="button" class="rv-primary" disabled>Guardar atributos de esta copa</button>
        </div>
        <p class="rv-hint">Las marcas MAN son indicios precampo. No equivalen automáticamente a falsos negativos. Un árbol oculto no debe recibir una máscara inventada.</p>
      </section>

      <section class="rv-panel">
        <div class="rv-panel-title"><h2>Control de calidad</h2></div>
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
        <div class="rv-panel-title"><h2>Historial</h2><button type="button" id="rv-history-refresh" disabled>Actualizar</button></div>
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
<script src="{{ asset('sigmad-revision-assets/revision-ia.js') }}?v=080" defer></script>
@endsection
