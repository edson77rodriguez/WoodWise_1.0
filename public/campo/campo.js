/* SIGMAD Campo V0.1: captura dasométrica offline con IndexedDB.
   Sin dependencias externas. No interpreta una predicción IA como verdad de campo. */
(() => {
  'use strict';
  const root = document.getElementById('campo-root');
  if (!root) return;
  const DATABASE = 'sigmad-campo-v01';
  let db, context, currentGPS = null, editId = null;
  const $ = sel => root.querySelector(sel);
  const numberOrNull = value => value === '' || value == null ? null : Number(value);

  function dbOpen() {
    return new Promise((resolve, reject) => {
      const req = indexedDB.open(DATABASE, 1);
      req.onupgradeneeded = () => {
        const d = req.result;
        if (!d.objectStoreNames.contains('records')) d.createObjectStore('records', {keyPath: 'uuid'});
        if (!d.objectStoreNames.contains('settings')) d.createObjectStore('settings', {keyPath: 'key'});
      };
      req.onsuccess = () => resolve(req.result);
      req.onerror = () => reject(req.error);
    });
  }
  function store(name, mode = 'readonly') { return db.transaction(name, mode).objectStore(name); }
  function reqPromise(req) {
    return new Promise((resolve, reject) => { req.onsuccess = () => resolve(req.result); req.onerror = () => reject(req.error); });
  }
  const setting = key => reqPromise(store('settings').get(key));
  const setSetting = (key, value) => reqPromise(store('settings', 'readwrite').put({key, value}));
  const putRecord = record => reqPromise(store('records', 'readwrite').put(record));
  const allRecords = () => reqPromise(store('records').getAll());
  const myRecords = async () => (await allRecords()).filter(r => r.user_id === context.user_id).sort((a,b) => b.client_created_at.localeCompare(a.client_created_at));
  function message(str, error = false) { const n = $('#campo-msg'); n.textContent = str; n.className = 'message' + (error ? ' error' : ''); n.hidden = false; }

  function render() {
    root.innerHTML = `
      <div class="smallhead"><div><h1>SIGMAD Campo</h1><p class="intro">Inventario digital · Protocolo de validación UAV V0.8A · Piloto V0.1</p></div><span class="pill" id="connection">Comprobando conexión</span></div>
      <div id="campo-msg" class="message" hidden></div>
      <div class="cards"><div>
      <div class="card"><h2>Registrar árbol</h2><p class="intro">Captura directa sin Excel. Los valores quedan en este teléfono hasta confirmación del servidor.</p>
        <form id="tree-form">
          <div class="fieldgrid">
            <div><label for="grupo">Grupo de muestreo</label><select id="grupo" name="grupo" required><option value="A">A · Candidato de IA</option><option value="B">B · Censo independiente</option><option value="C">C · Remedición de control</option></select></div>
            <div><label for="parcela">Parcela</label><select id="parcela" name="parcela" required></select></div>
            <div><label for="candidate_id">ID del candidato IA</label><input id="candidate_id" name="candidate_id" maxlength="80" placeholder="Ej. A012"></div>
            <div><label for="tree_id">Tree ID de campo</label><input id="tree_id" name="tree_id" maxlength="80" placeholder="Ej. P01-023"></div>
            <div><label for="localizado">¿Localizado?</label><select id="localizado" name="localizado"><option value="">Seleccionar…</option><option value="si">Sí</option><option value="dudoso">Dudoso</option><option value="no">No</option></select></div>
            <div><label for="especie">Especie verificada</label><select id="especie" name="especie"><option value="">Seleccionar…</option><option value="PL">Pinus lawsonii</option><option value="QR">Quercus rugosa</option><option value="O">Otra</option><option value="ND">No determinada</option></select></div>
            <div><label for="estado">Estado</label><select id="estado" name="estado"><option value="">Seleccionar…</option><option value="vivo">Vivo</option><option value="muerto">Muerto</option><option value="ND">No determinado</option></select></div>
            <div><label for="dap_cm">DAP a 1.30 m (cm)</label><input type="number" step="0.01" min="0.01" id="dap_cm" name="dap_cm" inputmode="decimal" placeholder="Ej. 28.4"></div>
            <div><label for="altura_m">Altura total (m)</label><input type="number" step="0.01" min="0.01" id="altura_m" name="altura_m" inputmode="decimal" placeholder="Ej. 17.2"></div>
            <div><label for="conf_altura">Confianza altura</label><select id="conf_altura" name="conf_altura"><option value="">Seleccionar…</option><option value="alta">Alta</option><option value="media">Media</option><option value="baja">Baja</option></select></div>
          </div>
          <div class="section"><h2>Radios de copa desde el fuste (m)</h2><div class="fieldgrid">
            <div><label for="r_n_m">Norte</label><input id="r_n_m" name="r_n_m" type="number" step="0.01" min="0" inputmode="decimal"></div>
            <div><label for="r_s_m">Sur</label><input id="r_s_m" name="r_s_m" type="number" step="0.01" min="0" inputmode="decimal"></div>
            <div><label for="r_e_m">Este</label><input id="r_e_m" name="r_e_m" type="number" step="0.01" min="0" inputmode="decimal"></div>
            <div><label for="r_o_m">Oeste</label><input id="r_o_m" name="r_o_m" type="number" step="0.01" min="0" inputmode="decimal"></div>
            <div><label for="dominancia">Dominancia</label><select id="dominancia" name="dominancia"><option value="">Seleccionar…</option><option value="D">Dominante</option><option value="CD">Codominante</option><option value="I">Intermedio</option><option value="S">Suprimido</option></select></div>
            <div><label for="solapamiento">Solapamiento</label><select id="solapamiento" name="solapamiento"><option value="">Seleccionar…</option><option value="0">0 · Ninguno</option><option value="1">1 · Bajo</option><option value="2">2 · Moderado</option><option value="3">3 · Alto</option></select></div>
            <div><label for="visibilidad">Visibilidad</label><select id="visibilidad" name="visibilidad"><option value="">Seleccionar…</option><option value="F">F · Fácil</option><option value="M">M · Moderado</option><option value="D">D · Difícil</option><option value="MD">MD · Muy difícil</option></select></div>
          </div></div>
          <div class="section"><h2>Georreferenciación del fuste</h2><p class="intro">Ubícate junto al tronco y captura la posición del teléfono. La coordenada del dispositivo no equivale necesariamente a la del fuste.</p>
            <div class="actions"><button class="secondary" type="button" id="capture-gps">◎ Capturar GPS</button><button type="button" class="ghost" id="no-gps">Guardar sin GPS</button></div>
            <p id="gps-status" class="gps-status">Sin posición registrada. Se conservará la precisión reportada.</p>
          </div>
          <div class="section"><div class="fieldgrid"><div class="span2"><label for="motivo_sin_medicion">Motivo si faltan mediciones</label><input id="motivo_sin_medicion" name="motivo_sin_medicion" maxlength="300" placeholder="No localizado / sin instrumento / copa inaccesible…"></div><div class="span2"><label for="observaciones">Observaciones</label><textarea id="observaciones" name="observaciones" maxlength="3000" placeholder="Dificultades, método y condición del árbol…"></textarea></div></div></div>
          <div class="actions"><button type="submit" id="save-record">Guardar en este dispositivo</button><button type="button" id="reset-form" class="ghost">Nuevo registro</button></div>
        </form>
      </div></div><div>
      <div class="card"><div class="smallhead"><h2>Estado del levantamiento</h2><span id="pending-count" class="pill warn">0 pendientes</span></div><p class="intro">Los datos no se borrarán cuando se interrumpa la conexión.</p><div id="local-count" class="kpi">0</div><p class="intro">Fichas en este dispositivo</p><div class="actions"><button type="button" id="sync-records">Sincronizar pendientes</button><button class="secondary" type="button" id="export-records">Exportar respaldo JSON</button></div><p class="offline-note">Antes de salir de campo, descarga un respaldo. V0.1 no guarda fotografías, mapas ni coordenadas UTM transformadas.</p></div>
      <div class="card"><h2>Registros</h2><div id="records-list"></div></div></div></div>`;
    const select = $('#parcela');
    (context.parcelas || []).forEach(p => select.add(new Option(p.name, String(p.id))));
    if (!select.options.length) select.add(new Option('Sin parcelas asignadas', ''));
    $('#grupo').addEventListener('change', updateGroup);
    $('#tree-form').addEventListener('submit', save);
    $('#capture-gps').addEventListener('click', captureGPS);
    $('#no-gps').addEventListener('click', () => { currentGPS = {gps_metodo: 'sin_gps'}; updateGPS(); });
    $('#reset-form').addEventListener('click', resetForm);
    $('#sync-records').addEventListener('click', synchronize);
    $('#export-records').addEventListener('click', exportBackup);
    updateGroup(); updateConnection(); updateGPS(); refreshRecords();
  }

  function updateGroup() {
    const group = $('#grupo').value;
    $('#candidate_id').closest('div').hidden = group !== 'A';
    $('#localizado').closest('div').hidden = group !== 'A';
    $('#localizado').required = group === 'A';
    $('#tree_id').required = group !== 'A';
    $('#candidate_id').required = group === 'A';
  }
  function updateConnection() {
    const p = $('#connection'); if (!p) return;
    p.textContent = navigator.onLine ? 'Conexión disponible (sin comprobar)' : 'Sin conexión';
    p.className = 'pill ' + (navigator.onLine ? '' : 'warn');
    $('#sync-records').disabled = !window.SIGMAD_FIELD_BOOT || !navigator.onLine;
  }
  function updateGPS() {
    const p = $('#gps-status'); if (!p) return;
    if (!currentGPS || currentGPS.gps_metodo === 'sin_gps') {
      p.textContent = 'Sin GPS. Si registras mediciones, documenta el motivo.';
    } else {
      p.textContent = `Latitud ${currentGPS.latitud.toFixed(7)} · Longitud ${currentGPS.longitud.toFixed(7)} · Precisión reportada ±${Math.round(currentGPS.gps_precision_m)} m · ${new Date(currentGPS.gps_fecha).toLocaleString()}`;
    }
  }
  function captureGPS() {
    if (!navigator.geolocation) { message('Este navegador no permite obtener geolocalización.', true); return; }
    $('#capture-gps').disabled = true;
    navigator.geolocation.getCurrentPosition(pos => {
      currentGPS = {
        gps_metodo: 'telefono', latitud: pos.coords.latitude,
        longitud: pos.coords.longitude, gps_precision_m: pos.coords.accuracy,
        gps_fecha: new Date(pos.timestamp).toISOString()
      };
      $('#capture-gps').disabled = false; updateGPS();
      message('GPS registrado. Confirma que el dispositivo esté junto al fuste.');
    }, err => {
      $('#capture-gps').disabled = false;
      message('No se pudo obtener GPS: ' + err.message + '. Puedes guardar con motivo documentado.', true);
    }, {enableHighAccuracy: true, maximumAge: 0, timeout: 20000});
  }
  function readPayload() {
    const form = $('#tree-form'); const fd = new FormData(form);
    const p = {
      tree_id: String(fd.get('tree_id') || '').trim() || null,
      candidate_id: fd.get('grupo') === 'A' ? String(fd.get('candidate_id') || '').trim() || null : null,
      localizado: fd.get('grupo') === 'A' ? (fd.get('localizado') || null) : null,
      especie: fd.get('especie') || null, estado: fd.get('estado') || null,
      dap_cm: numberOrNull(fd.get('dap_cm')), altura_m: numberOrNull(fd.get('altura_m')),
      conf_altura: fd.get('conf_altura') || null,
      r_n_m: numberOrNull(fd.get('r_n_m')), r_s_m: numberOrNull(fd.get('r_s_m')),
      r_e_m: numberOrNull(fd.get('r_e_m')), r_o_m: numberOrNull(fd.get('r_o_m')),
      dominancia: fd.get('dominancia') || null, solapamiento: numberOrNull(fd.get('solapamiento')),
      visibilidad: fd.get('visibilidad') || null,
      latitud: currentGPS?.latitud ?? null, longitud: currentGPS?.longitud ?? null,
      gps_precision_m: currentGPS?.gps_precision_m ?? null,
      gps_fecha: currentGPS?.gps_fecha ?? null,
      gps_metodo: currentGPS?.gps_metodo || 'sin_gps',
      motivo_sin_medicion: String(fd.get('motivo_sin_medicion') || '').trim() || null,
      observaciones: String(fd.get('observaciones') || '').trim() || null,
    };
    return p;
  }
  async function save(e) {
    e.preventDefault();
    if (!$('#tree-form').reportValidity()) return;
    const group = $('#grupo').value, parcela = Number($('#parcela').value);
    if (!parcela) return message('Debes tener una parcela asignada para guardar.', true);
    const payload = readPayload();
    if (group === 'A' && !payload.candidate_id) return message('Grupo A requiere ID de candidato.', true);
    if (group !== 'A' && !payload.tree_id) return message('Grupo B/C requiere Tree ID.', true);
    if (group === 'A' && !payload.localizado) return message('Indica si localizaste al candidato.', true);
    if (!(group === 'A' && payload.localizado === 'no') && (!payload.especie || !payload.estado)) return message('Selecciona especie y estado, o registra que no se pudieron determinar.', true);
    if (group === 'B' && payload.especie !== 'PL') {
      if (!confirm('El censo principal B es de Pinus lawsonii. ¿Registrar este individuo igualmente como observación acompañante?')) return;
    }
    if (payload.dap_cm != null && (payload.dap_cm > 150 || payload.dap_cm < 7.5)) {
      if (!confirm('DAP fuera del rango operativo habitual: confirma el valor antes de guardarlo.')) return;
    }
    if (payload.altura_m != null && payload.altura_m > 50) {
      if (!confirm('Altura inusual: ¿confirmas el valor medido?')) return;
    }
    if (!payload.dap_cm || !payload.altura_m || [payload.r_n_m,payload.r_s_m,payload.r_e_m,payload.r_o_m].some(v=>v===null)) {
      if (!payload.motivo_sin_medicion && !(group === 'A' && payload.localizado === 'no')) {
        return message('Indica el motivo de los valores no medidos; nunca los sustituyas por cero.', true);
      }
    }
    if (payload.latitud == null && !payload.motivo_sin_medicion) {
      if (!confirm('No registraste GPS. El árbol quedará pendiente de georreferenciación. ¿Guardar?')) return;
    }
    const old = editId ? await reqPromise(store('records').get(editId)) : null;
    if (old?.status === 'synced') return message('No se permite editar un registro ya sincronizado en esta versión.', true);
    const rec = {
      uuid: old?.uuid || crypto.randomUUID(), user_id: context.user_id,
      id_parcela: parcela, grupo: group,
      client_created_at: old?.client_created_at || new Date().toISOString(),
      payload, status: 'pending', server_id: null,
    };
    await putRecord(rec);
    message('Guardado en este dispositivo. Pendiente de sincronización.');
    resetForm(false); await refreshRecords();
  }
  function resetForm(clearMessage = true) {
    $('#tree-form').reset(); editId = null; currentGPS = null;
    updateGroup(); updateGPS(); $('#save-record').textContent = 'Guardar en este dispositivo';
    if (clearMessage) $('#campo-msg').hidden = true;
  }
  async function refreshRecords() {
    const records = await myRecords();
    $('#local-count').textContent = records.length;
    const pending = records.filter(r => r.status !== 'synced');
    $('#pending-count').textContent = `${pending.length} pendientes`;
    const list = $('#records-list'); list.replaceChildren();
    if (!records.length) { const p = document.createElement('p'); p.className='empty'; p.textContent='Todavía no hay árboles registrados.'; list.append(p); return; }
    records.slice(0,80).forEach(rec => {
      const div = document.createElement('div'); div.className='item';
      const title = document.createElement('div'); title.className='item-title';
      title.textContent = `${rec.grupo} · ${rec.payload.tree_id || rec.payload.candidate_id || 'Sin ID'}`;
      const meta = document.createElement('div'); meta.className='item-meta';
      meta.textContent = `Parcela ${rec.id_parcela} · ${new Date(rec.client_created_at).toLocaleString()} · ${rec.status === 'synced' ? 'En servidor' : 'Pendiente'}`;
      div.append(title,meta);
      if (rec.status !== 'synced') {
        const b = document.createElement('button'); b.type='button'; b.className='secondary'; b.textContent='Editar pendiente';
        b.addEventListener('click', () => loadRecord(rec)); div.append(b);
      }
      list.append(div);
    });
  }
  function loadRecord(rec) {
    if (rec.status !== 'pending') return;
    editId = rec.uuid; $('#grupo').value=rec.grupo; $('#parcela').value=String(rec.id_parcela);
    updateGroup();
    Object.entries(rec.payload).forEach(([key,value]) => {
      const el = $('#'+CSS.escape(key)); if (el && value != null) el.value = String(value);
    });
    const {latitud,longitud,gps_precision_m,gps_fecha,gps_metodo} = rec.payload;
    currentGPS = {latitud,longitud,gps_precision_m,gps_fecha,gps_metodo}; updateGPS();
    $('#save-record').textContent = 'Actualizar registro pendiente';
    root.scrollIntoView({behavior:'smooth',block:'start'});
  }
  async function synchronize() {
    if (!window.SIGMAD_FIELD_BOOT) return message('Reabre /campo/ cuando recuperes conexión para renovar tu sesión antes de sincronizar.', true);
    const csrf = document.querySelector('meta[name="csrf-token"]')?.content;
    if (!csrf) return message('Falta el token de sesión. Actualiza la página conectado.', true);
    const records = (await myRecords()).filter(r => r.status === 'pending');
    if (!records.length) return message('No tienes registros pendientes.');
    $('#sync-records').disabled = true;
    let good=0;
    for (const rec of records) {
      try {
        const res = await fetch(context.sync_url, {
          method:'POST', credentials:'same-origin',
          headers:{'Content-Type':'application/json','Accept':'application/json','X-CSRF-TOKEN':csrf},
          body: JSON.stringify({uuid:rec.uuid,id_parcela:rec.id_parcela,grupo:rec.grupo,client_created_at:rec.client_created_at,payload:rec.payload})
        });
        if (res.status === 401 || res.status === 419) throw new Error('Sesión expirada: inicia sesión o recarga estando conectado.');
        const body = await res.json().catch(() => ({}));
        if (!res.ok || !body.ok || body.uuid !== rec.uuid) throw new Error(body.message || `Error HTTP ${res.status}`);
        // Marcar confirmado solo después de respuesta explícita del servidor.
        await putRecord({...rec,status:'synced',server_id:body.id,synced_at:new Date().toISOString()});
        good++;
      } catch (err) {
        message(`Sincronizados ${good}/${records.length}. Pendientes conservados. ${err.message}`,true);
        $('#sync-records').disabled=false; await refreshRecords(); return;
      }
    }
    $('#sync-records').disabled=false; await refreshRecords(); message(`${good} registros confirmados por SIGMAD.`);
  }
  async function exportBackup() {
    const records = await myRecords();
    const data = JSON.stringify({schema:'SIGMAD-CAMPO-0.1',exported_at:new Date().toISOString(),user_id:context.user_id,records},null,2);
    const url = URL.createObjectURL(new Blob([data],{type:'application/json'}));
    const a = document.createElement('a'); a.href=url; a.download=`SIGMAD_Campo_respaldo_${new Date().toISOString().slice(0,10)}.json`;
    a.click(); setTimeout(()=>URL.revokeObjectURL(url),5000);
    message('Respaldo local preparado. Guárdalo en un lugar seguro.');
  }
  async function init() {
    try {
      db = await dbOpen();
      if (window.SIGMAD_FIELD_BOOT) {
        context = window.SIGMAD_FIELD_BOOT;
        await setSetting('last_context', {user_id:context.user_id,user_name:context.user_name,parcelas:context.parcelas,sync_url:context.sync_url});
      } else {
        const cached = await setting('last_context'); context = cached?.value;
      }
      if (!context) {
        root.textContent='Primero debes iniciar sesión y abrir /campo/ con internet para preparar el modo sin conexión.';
        return;
      }
      render();
      window.addEventListener('online',updateConnection);
      window.addEventListener('offline',updateConnection);
      if ('serviceWorker' in navigator && window.isSecureContext) {
        navigator.serviceWorker.register('/campo/sw.js',{scope:'/campo/'}).catch(()=>{});
      }
    } catch(err) {
      root.textContent=`No fue posible abrir el almacenamiento local: ${err.message}. No continúes capturando en este dispositivo.`;
    }
  }
  init();
})();
