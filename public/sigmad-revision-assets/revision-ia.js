/* SIGMAD Laboratorio de revisión IA · V0.8.0
 * Copas originales inmutables, revisión de polígonos aislada, historial en servidor.
 * Sin IA asistida ni corrección automática; todo acto es humano y auditable. */
(() => {
  'use strict';
  const boot = window.SIGMAD_REVIEW_BOOT;
  if (!boot || !document.getElementById('rv-app') && !document.getElementById('review-app')) return;
  const $ = id => document.getElementById(id);
  const format = () => new ol.format.GeoJSON();
  let mission = null, map = null, originalSource = null, reviewSource = null, manualSource = null;
  let originalLayer = null, reviewLayer = null, manualLayer = null;
  let selection = null, modifier = null, drawer = null;
  let selected = null, selectedManual = null, mode = 'select';
  let splitSession = null, undo = [], dirty = false, busy = false, blockSave = false;

  const labels = {pending:'Pendiente',accepted:'Validada',edited:'Corregida',added:'Nueva',split:'Separación',superseded:'Fusionada reemplazada',rejected:'Descartada'};
  const colors = {pending:'#48c2c0',accepted:'#2dd39f',edited:'#6eb6f7',added:'#a592f2',split:'#7d8eff',superseded:'#efb65f',rejected:'#e7727b'};
  const url = (which, uuid = mission?.uuid, version = null) => {
    let tpl = boot[which + '_url'];
    return tpl.replace('__UUID__', encodeURIComponent(uuid)).replace('__VERSION__', String(version ?? ''));
  };
  function alertMsg(msg, warn = false) {
    $('rv-alert').textContent = msg;
    $('rv-alert').dataset.warn = warn ? 'yes' : 'no';
  }
  function flagDirty(value = true) {
    dirty = value;
    $('rv-dirty').textContent = value ? 'Cambios sin guardar' : 'Todo guardado';
    $('rv-dirty').style.color = value ? '#bd7430' : '';
  }
  function props(feature) {
    const p = feature.getProperties();
    const source = p.source_operational_id || null;
    return {
      annotation_uuid: String(p.annotation_uuid || ''),
      source_operational_id: source,
      review_status: p.review_status || 'pending',
      species: p.species || 'ND', visibility: p.visibility || 'ND',
      boundary_confidence: p.boundary_confidence || 'ND',
      manual_target_uuid: p.manual_target_uuid || null,
      field_tree_id: p.field_tree_id || '', note: p.note || ''
    };
  }
  function reviewed() {
    if (!reviewSource || !mission) return {type:'FeatureCollection',features:[]};
    const data = format().writeFeaturesObject(reviewSource.getFeatures(), {
      dataProjection: 'EPSG:4326', featureProjection: mission.crs,
      rightHanded: true, decimals: 9
    });
    data.features.forEach((f, i) => { f.properties = props(reviewSource.getFeatures()[i]); });
    return data;
  }
  function backupUndo() {
    if (!mission || !reviewSource) return;
    undo.push(JSON.stringify(reviewed()));
    if (undo.length > 16) undo.shift();
    $('rv-undo').disabled = false;
  }
  function loadReview(collection) {
    if (!mission || !reviewSource) return;
    unselect();
    const features = format().readFeatures(collection, {
      dataProjection:'EPSG:4326', featureProjection:mission.crs
    });
    reviewSource.clear();
    for (const f of features) {
      if (!f.get('annotation_uuid')) f.set('annotation_uuid', crypto.randomUUID());
    }
    reviewSource.addFeatures(features);
    updateCounts();
  }
  function originalToDraft(original) {
    return {type:'FeatureCollection',features:(original.features || []).map(f => {
      const id = String(f.properties?.operational_id || f.id || '');
      return {type:'Feature',geometry:f.geometry,properties:{
        annotation_uuid:'original:' + id, source_operational_id:id,
        review_status:'pending', species:'PL', visibility:'ND',
        boundary_confidence:'ND', manual_target_uuid:null, field_tree_id:'', note:''
      }};
    })};
  }
  function polygonStyle(f, ghost = false) {
    const status = f.get('review_status') || 'pending';
    const rejected = status === 'rejected' || status === 'superseded';
    const color = ghost ? '#ffb84e' : (colors[status] || colors.pending);
    return new ol.style.Style({
      stroke:new ol.style.Stroke({color,width:ghost?2:status==='accepted'?2.5:2,
        lineDash:ghost?[6,5]:(rejected?[3,7]:undefined)}),
      fill:new ol.style.Fill({color:ghost?'rgba(255,184,78,.045)':rejected?'rgba(231,114,123,.03)':'rgba(76,202,177,.12)'})
    });
  }
  function manualStyle(f) {
    return new ol.style.Style({
      image:new ol.style.Circle({radius:7,fill:new ol.style.Fill({color:'#b95fe1'}),stroke:new ol.style.Stroke({color:'#fff',width:2})}),
      stroke:new ol.style.Stroke({color:'#b95fe1',width:2,lineDash:[4,4]}),
      fill:new ol.style.Fill({color:'rgba(185,95,225,.10)'}),
      text:new ol.style.Text({text:String(f.get('manual_code') || 'MAN'),font:'bold 10px sans-serif',
        offsetY:f.getGeometry()?.getType()==='Point'?-17:0,
        padding:[2,4,2,4],backgroundFill:new ol.style.Fill({color:'rgba(68,32,86,.85)'}),
        fill:new ol.style.Fill({color:'#fff'})})
    });
  }
  function configureSelection() {
    selection = new ol.interaction.Select({layers:[reviewLayer],hitTolerance:7,
      style:f => polygonStyle(f)});
    map.addInteraction(selection);
    modifier = new ol.interaction.Modify({features:selection.getFeatures()});
    map.addInteraction(modifier);modifier.setActive(false);
    selection.on('select', () => {
      selected = selection.getFeatures().item(0) || null;
      selectedManual = null;
      populateAttributes();
      updateButtons();
    });
    modifier.on('modifystart', backupUndo);
    modifier.on('modifyend', () => {
      if (selected && ['pending','accepted','edited'].includes(selected.get('review_status'))) {
        selected.set('review_status','edited');
      }
      reviewSource.changed(); flagDirty(); populateAttributes(); updateCounts();
      alertMsg('Vértices modificados. Pulsa «Guardar revisión» para conservarlos en el servidor.');
    });
  }
  function unselect() {
    selected = null;
    if (selection) selection.getFeatures().clear();
    populateAttributes();updateButtons();
  }
  function populateAttributes() {
    const p = selected ? props(selected) : null;
    $('rv-selected').textContent = p ? (p.source_operational_id || 'Nueva copa') : 'Ninguna seleccionada';
    for (const id of ['rv-status','rv-species','rv-visibility','rv-confidence','rv-origin','rv-manual','rv-tree','rv-note']) {
      $(id).disabled = !p;
    }
    $('rv-apply-attrs').disabled = !p;
    if (!p) {
      $('rv-origin').value='';$('rv-tree').value='';$('rv-note').value='';$('rv-manual').value='';return;
    }
    $('rv-status').value=p.review_status;
    $('rv-species').value=p.species;
    $('rv-visibility').value=p.visibility;
    $('rv-confidence').value=p.boundary_confidence;
    $('rv-origin').value=p.source_operational_id || '';
    $('rv-manual').value=p.manual_target_uuid || '';
    $('rv-tree').value=p.field_tree_id;
    $('rv-note').value=p.note;
  }
  function updateButtons() {
    const loaded = !!mission;
    for (const id of ['rv-add','rv-export','rv-history-refresh']) $(id).disabled = !loaded || busy;
    $('rv-save').disabled = !loaded || busy || blockSave;
    for (const id of ['rv-accept','rv-edit','rv-split','rv-reject','rv-restore']) $(id).disabled = !selected || busy || mode !== 'select';
    $('rv-undo').disabled = !undo.length || busy || mode !== 'select';
    if (!selected && modifier) modifier.setActive(false);
  }
  function updateCounts() {
    if (!reviewSource) return;
    const features = reviewSource.getFeatures();
    const count = s => features.filter(f => f.get('review_status') === s).length;
    $('rv-kpi-total').textContent = features.length;
    $('rv-kpi-pending').textContent = count('pending');
    $('rv-kpi-rejected').textContent = count('rejected')+count('superseded');
    $('rv-kpi-new').textContent = count('added')+count('split');
  }
  function editStatus(status) {
    if (!selected || mode!=='select') return;
    backupUndo();selected.set('review_status', status);
    if (status==='accepted' && selected.get('boundary_confidence')==='ND') selected.set('boundary_confidence','media');
    reviewSource.changed();populateAttributes();flagDirty();updateCounts();
  }
  function applyAttributes() {
    if (!selected) return;
    const p = props(selected);
    const source = p.source_operational_id;
    const status = $('rv-status').value;
    if (status === 'split' && !source) return alertMsg('Una copa dividida necesita una máscara IA de origen.',true);
    if (status === 'superseded' && !source) return alertMsg('Solo una máscara IA original puede marcarse como fusionada.',true);
    backupUndo();
    selected.setProperties({
      review_status:status, species:$('rv-species').value,
      visibility:$('rv-visibility').value,
      boundary_confidence:$('rv-confidence').value,
      manual_target_uuid:$('rv-manual').value||null,
      field_tree_id:$('rv-tree').value.trim(), note:$('rv-note').value.trim()
    });
    reviewSource.changed();flagDirty();updateCounts();
    alertMsg('Atributos actualizados en pantalla. Falta guardar la revisión en SIGMAD.');
  }
  function stopDrawer() {
    if (drawer && map) map.removeInteraction(drawer);
    drawer = null;
    mode = 'select';
    if (selection) selection.setActive(true);
    if (modifier) modifier.setActive(false);
    $('rv-finish-draw').hidden = true;
    $('rv-cancel-draw').hidden = true;
    $('rv-mode').textContent = 'Modo: seleccionar';
    splitSession = null;
    updateButtons();
  }
  function startDrawer(kind) {
    if (!mission || !map) return;
    if (mode!=='select') return;
    if (kind==='split' && (!selected || !selected.get('source_operational_id'))) {
      return alertMsg('Selecciona primero una máscara IA para dividirla.',true);
    }
    if (kind==='split') {
      backupUndo();
      splitSession = { feature:selected, previousStatus:selected.get('review_status'),
        source:selected.get('source_operational_id'), added:[] };
      selected.set('review_status','superseded');
      reviewSource.changed();flagDirty();
    }
    mode=kind;
    if (modifier) modifier.setActive(false);
    if (selection) selection.setActive(false);
    drawer = new ol.interaction.Draw({source:reviewSource,type:'Polygon'});
    map.addInteraction(drawer);
    drawer.on('drawstart',()=>{backupUndo();});
    drawer.on('drawend',e=>{
      const f=e.feature;
      const p={annotation_uuid:crypto.randomUUID(),
        source_operational_id:kind==='split'?splitSession.source:null,
        review_status:kind==='split'?'split':'added',
        species:'PL', visibility:'ND', boundary_confidence:'ND',
        manual_target_uuid:selectedManual || null,field_tree_id:'',note:''};
      f.setProperties(p);
      if (kind==='split') splitSession.added.push(p.annotation_uuid);
      flagDirty();
      // El Draw incorpora el objeto a su source inmediatamente después de drawend.
      queueMicrotask(()=>{updateCounts();reviewSource.changed();});
      alertMsg(kind==='split'?'Copa de separación dibujada. Dibuja al menos una más y termina la división.':'Nueva copa dibujada. Pulsa «Terminar dibujo» y revisa sus atributos.');
    });
    $('rv-mode').textContent=kind==='split'?'Modo: separar en 2 o más copas':'Modo: dibujar nueva copa';
    $('rv-finish-draw').hidden=false;
    $('rv-cancel-draw').hidden=false;
    updateButtons();
    alertMsg(kind==='split'?'Traza dos o más polígonos para reemplazar la detección fusionada.':'Haz clic para dibujar la copa. Doble clic para terminar cada polígono.');
  }
  function finishDrawer(cancel=false) {
    if (!drawer) return;
    if (mode==='split') {
      const count = splitSession.added.length;
      if (!cancel && count < 2) return alertMsg('Faltan copas: para dividir deben existir al menos dos nuevos polígonos. Puedes cancelar.',true);
      if (cancel) {
        const sourceSet = new Set(splitSession.added);
        for (const f of reviewSource.getFeatures().slice()) {
          if (sourceSet.has(f.get('annotation_uuid'))) reviewSource.removeFeature(f);
        }
        splitSession.feature.set('review_status',splitSession.previousStatus);
      }
    }
    if (cancel && mode==='add') {
      // Cancelar salida de dibujo: conserva los polígonos ya completados para no perder trabajo.
      alertMsg('Dibujo detenido. Los polígonos completos se conservan; usa Deshacer si quieres retirarlos.');
    }
    stopDrawer();updateCounts();flagDirty();
    if (!cancel) alertMsg('Dibujo finalizado. Selecciona las copas nuevas para revisar sus atributos.');
  }
  function doUndo() {
    if (!undo.length || !mission || mode!=='select') return;
    const state = JSON.parse(undo.pop());
    loadReview(state);flagDirty();updateButtons();
    alertMsg('Se recuperó el estado anterior (todavía sin guardar en SIGMAD).');
  }
  function mapFor(data) {
    if (map) {map.setTarget(null);map=null;}
    const m=/^EPSG:(326|327)(\d{2})$/.exec(data.crs);
    if (!m) throw new Error('Este editor requiere CRS UTM EPSG:326xx o EPSG:327xx.');
    proj4.defs(data.crs,`+proj=utm +zone=${Number(m[2])} ${m[1]==='327'?'+south ':''}+datum=WGS84 +units=m +no_defs +type=crs`);
    ol.proj.proj4.register(proj4);
    originalSource=new ol.source.Vector({features:format().readFeatures(data.original,{
      dataProjection:'EPSG:4326',featureProjection:data.crs}),wrapX:false});
    reviewSource=new ol.source.Vector({wrapX:false});
    manualSource=new ol.source.Vector({wrapX:false});
    manualSource.addFeatures(format().readFeatures({type:'FeatureCollection',
      features:data.manual_targets?.features||[]},{dataProjection:data.crs,featureProjection:data.crs}));
    const layers = [];
    if (data.preview_url && Array.isArray(data.bounds)) {
      layers.push(new ol.layer.Image({source:new ol.source.ImageStatic({
        url:data.preview_url,projection:data.crs,imageExtent:data.bounds
      })}));
    }
    originalLayer=new ol.layer.Vector({source:originalSource,style:f=>polygonStyle(f,true)});
    reviewLayer=new ol.layer.Vector({source:reviewSource,style:f=>polygonStyle(f)});
    manualLayer=new ol.layer.Vector({source:manualSource,style:manualStyle});
    layers.push(originalLayer,reviewLayer,manualLayer);
    map=new ol.Map({target:'rv-map',layers,
      view:new ol.View({projection:data.crs,center:ol.extent.getCenter(data.bounds),zoom:0}),
      controls:ol.control.defaults.defaults({rotate:false})});
    map.getView().fit(data.bounds,{size:map.getSize(),padding:[24,24,24,24]});
    configureSelection();
    map.on('singleclick',evt=>{
      if (mode!=='select' || selected) return;
      const mark=map.forEachFeatureAtPixel(evt.pixel,f=>f.get('manual_target_uuid')?f:null,
        {layerFilter:layer=>layer===manualLayer,hitTolerance:10});
      if (mark) {
        selectedManual=mark.get('manual_target_uuid');
        alertMsg(`${mark.get('manual_code')||'MAN'}: marca de revisión seleccionada. Pulsa «Agregar copa» solo si su contorno es visible.`);
      }
    });
    $('rv-show-original').onchange=()=>originalLayer.setVisible($('rv-show-original').checked);
    $('rv-show-manual').onchange=()=>manualLayer.setVisible($('rv-show-manual').checked);
  }
  async function getJSON(endpoint, options={}) {
    const response=await fetch(endpoint,{credentials:'same-origin',cache:'no-store',...options,
      headers:{Accept:'application/json',...(options.headers||{})}});
    const result=await response.json().catch(()=>({}));
    if (!response.ok) {
      const message=result.message || Object.values(result.errors||{})[0]?.[0] || `HTTP ${response.status}`;
      throw new Error(message);
    }
    return result;
  }
  async function loadMission() {
    const uuid=$('rv-mission').value;
    if (!uuid) return alertMsg('Elige una misión.',true);
    if (dirty && !confirm('Hay cambios sin guardar. ¿Abrir otra misión y descartarlos?')) return;
    busy=true;updateButtons();alertMsg('Abriendo ortomosaico y máscaras originales…');
    try {
      const data=await getJSON(url('data',uuid));
      mission=data;blockSave=!!(data.review && data.review_source_sha256!==data.source_sha256);
      selected=null;selectedManual=null;mode='select';splitSession=null;undo=[];
      mapFor(data);
      const collection=data.review || originalToDraft(data.original);
      loadReview(collection);
      $('rv-version').textContent=`Revisión V${data.revision_no}`;
      $('rv-manual').replaceChildren(new Option('Sin vínculo MAN',''));
      for (const f of data.manual_targets?.features||[]) {
        const p=f.properties||{};
        $('rv-manual').add(new Option(`${p.manual_code} · ${p.reason||'pendiente'}`,p.manual_target_uuid));
      }
      flagDirty(false);
      await loadHistory();
      if (blockSave) alertMsg('La misión original cambió desde la última revisión. Puedes exportar, pero no guardar hasta reconciliar.',true);
      else alertMsg(`Misión abierta · ${data.original.features.length} máscaras IA · ${data.manual_targets?.features.length||0} marcas MAN · Revisión V${data.revision_no}.`);
    } catch(e) {alertMsg('No se pudo abrir: '+e.message,true);}
    finally {busy=false;updateButtons();}
  }
  async function loadHistory() {
    if (!mission) return;
    const data=await getJSON(url('history'));
    const container=$('rv-history');container.replaceChildren();
    if (!data.revisions.length) {const p=document.createElement('p');p.className='rv-muted';p.textContent='Sin revisiones guardadas todavía.';container.append(p);return;}
    for (const v of data.revisions) {
      const row=document.createElement('div');row.className='rv-history-row';
      const txt=document.createElement('div');
      const b=document.createElement('b');b.textContent=`V${v.revision_no}`;
      const small=document.createElement('span');small.textContent=` · ${v.reason}`;
      txt.append(b,small);
      const button=document.createElement('button');button.type='button';button.textContent='Abrir';
      button.addEventListener('click',()=>void loadOlder(v.revision_no));
      row.append(txt,button);container.append(row);
    }
  }
  async function loadOlder(version) {
    if (dirty && !confirm('¿Reemplazar los cambios sin guardar por esta versión anterior?')) return;
    try {
      const data=await getJSON(url('revision',mission.uuid,version));
      if (data.source_sha256!==mission.source_sha256) return alertMsg('La versión anterior se creó con máscaras IA diferentes. Exporta antes de modificar.',true);
      undo=[];loadReview(data.geojson);flagDirty(true);updateButtons();
      alertMsg(`Versión V${version} cargada como BORRADOR. Si guardas, se creará V${mission.revision_no+1}, sin borrar el historial.`);
    }catch(e){alertMsg('No se pudo recuperar la revisión: '+e.message,true);}
  }
  async function save() {
    if (!mission || busy || blockSave) return;
    if (mode!=='select') return alertMsg('Termina o cancela el dibujo antes de guardar.',true);
    const reason=$('rv-reason').value.trim();
    if (reason.length<8) return alertMsg('Escribe una justificación de al menos ocho caracteres.',true);
    const data=reviewed();
    if (data.features.some(f=>f.properties.review_status==='split' && !f.properties.source_operational_id))
      return alertMsg('Hay copas divididas sin máscara original vinculada.',true);
    const splits=new Map();
    for (const f of data.features) if(f.properties.review_status==='split') {
      const id=f.properties.source_operational_id;
      splits.set(id,(splits.get(id)||0)+1);
    }
    for(const f of data.features) if (f.properties.review_status==='superseded' &&
      (splits.get(f.properties.source_operational_id)||0)<2) {
      return alertMsg(`La máscara ${f.properties.source_operational_id} está marcada fusionada, pero tiene menos de dos copas separadas.`,true);
    }
    busy=true;updateButtons();alertMsg('Guardando revisión versionada…');
    try {
      const result=await getJSON(url('save'),{method:'POST',
        headers:{'Content-Type':'application/json','X-CSRF-TOKEN':boot.csrf},
        body:JSON.stringify({expected_revision:mission.revision_no,
          source_sha256:mission.source_sha256,reason,features:data.features})});
      mission.revision_no=result.revision_no;
      $('rv-version').textContent=`Revisión V${result.revision_no}`;
      $('rv-reason').value='';flagDirty(false);undo=[];
      alertMsg(`Guardado correcto: V${result.revision_no} · ${result.saved_count} anotaciones, sin alterar IA original ni datos de campo.`);
      await loadHistory();
    }catch(e){alertMsg('No se pudo guardar: '+e.message+' · Exporta GeoJSON antes de recargar.',true);}
    finally{busy=false;updateButtons();}
  }
  function exportGeojson() {
    if (!mission) return;
    const col=reviewed();
    col.sigmad_review={schema:'sigmad-segmentation-review-v1',
      mission_uuid:mission.uuid,mission_title:mission.title,
      parent_analysis_uuid:mission.analysis_uuid,
      original_source_sha256:mission.source_sha256,
      revision_no:mission.revision_no,
      crs:'EPSG:4326',native_mosaic_crs:mission.crs,
      exported_at:new Date().toISOString(),
      workflow:'human-reviewed; not a raw AI prediction',
      approved_for_training_statuses:['accepted','edited','added','split']};
    // Solo GeoJSON espacial: YOLO y COCO requieren convertir estas máscaras al sistema pixel del raster.
    const blob=new Blob([JSON.stringify(col,null,2)],{type:'application/geo+json'});
    const objectURL=URL.createObjectURL(blob);
    const a=document.createElement('a');a.href=objectURL;
    a.download=`SIGMAD_V2_${mission.uuid.slice(0,8)}_r${mission.revision_no}.geojson`;
    document.body.append(a);a.click();a.remove();setTimeout(()=>URL.revokeObjectURL(objectURL),5000);
    alertMsg('GeoJSON descargado. Antes de entrenar, alinea sus polígonos con los píxeles del mosaico y excluye casos no revisados.');
  }
  function bind() {
    (boot.missions||[]).forEach(m=>$('rv-mission').add(new Option(`${m.title} · ${m.selected_count} IA · parcela ${m.id_parcela}`,m.uuid)));
    $('rv-load').addEventListener('click',()=>void loadMission());
    $('rv-accept').addEventListener('click',()=>editStatus('accepted'));
    $('rv-reject').addEventListener('click',()=>editStatus('rejected'));
    $('rv-restore').addEventListener('click',()=>editStatus('pending'));
    $('rv-edit').addEventListener('click',()=>{
      if(!selected || !modifier)return;
      modifier.setActive(!modifier.getActive());
      alertMsg(modifier.getActive()?'Arrastra los vértices del contorno seleccionado para corregirlo.':'Edición de vértices desactivada.');
      $('rv-edit').textContent=modifier.getActive()?'✓ Terminar edición':'✎ Modificar vértices';
    });
    $('rv-split').addEventListener('click',()=>startDrawer('split'));
    $('rv-add').addEventListener('click',()=>startDrawer('add'));
    $('rv-finish-draw').addEventListener('click',()=>finishDrawer(false));
    $('rv-cancel-draw').addEventListener('click',()=>finishDrawer(true));
    $('rv-undo').addEventListener('click',doUndo);
    $('rv-apply-attrs').addEventListener('click',applyAttributes);
    $('rv-save').addEventListener('click',()=>void save());
    $('rv-export').addEventListener('click',exportGeojson);
    $('rv-history-refresh').addEventListener('click',()=>void loadHistory().catch(e=>alertMsg(e.message,true)));
    window.addEventListener('beforeunload', e=>{if(dirty){e.preventDefault();e.returnValue='';}});
    if (!window.ol || !window.proj4) alertMsg('Faltan las librerías OpenLayers/proj4. Comprueba sus rutas.',true);
  }
  bind();
})();
