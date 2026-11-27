/* SIGMAD Smart Annotation Studio · V0.9.0
 * Copas originales inmutables, revisión de polígonos aislada, historial en servidor.
 * Pinceles manuales georreferenciados sin GPU. SAM remoto aún NO integrado. */
(() => {
  'use strict';
  const boot = window.SIGMAD_REVIEW_BOOT;
  if (!boot || !document.getElementById('rv-app') && !document.getElementById('review-app')) return;
  const $ = id => document.getElementById(id);
  const format = () => new ol.format.GeoJSON();
  let mission = null, map = null, originalSource = null, reviewSource = null, manualSource = null;
  let originalLayer = null, reviewLayer = null, manualLayer = null;
  let fullMapExtent = null, mapQuality = 'waiting', currentMosaicGsd = null;
  let selection = null, modifier = null, drawer = null;
  let selected = null, selectedManual = null, mode = 'select';
  let splitSession = null, undo = [], redo = [], dirty = false, busy = false, blockSave = false;
  let brushMode = null, brushOverlay = null, brushActiveStroke = null, brushWorking = false, hdTilesReady = false;

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
      field_tree_id: p.field_tree_id || '', note: p.note || '',
      edit_method: p.edit_method || 'original',
      brush_strokes: Number(p.brush_strokes) || 0,
      brush_added_m2: Number(p.brush_added_m2) || 0,
      brush_removed_m2: Number(p.brush_removed_m2) || 0
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
    redo = [];
    $('rv-undo').disabled = false;
    $('rv-redo').disabled = true;
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
        boundary_confidence:'ND', manual_target_uuid:null, field_tree_id:'', note:'',
        edit_method:'original',brush_strokes:0,brush_added_m2:0,brush_removed_m2:0
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
      $('rv-origin').value='';$('rv-tree').value='';$('rv-note').value='';$('rv-manual').value='';
      $('rv-brush-metrics').textContent='Selecciona una copa para editar su máscara.';return;
    }
    $('rv-status').value=p.review_status;
    $('rv-species').value=p.species;
    $('rv-visibility').value=p.visibility;
    $('rv-confidence').value=p.boundary_confidence;
    $('rv-origin').value=p.source_operational_id || '';
    $('rv-manual').value=p.manual_target_uuid || '';
    $('rv-tree').value=p.field_tree_id;
    $('rv-note').value=p.note;
    $('rv-brush-metrics').textContent = `Trazos: ${p.brush_strokes} · Agregado: ${p.brush_added_m2.toFixed(3)} m² · Quitado: ${p.brush_removed_m2.toFixed(3)} m² (aprox.)`;
  }
  function updateButtons() {
    const loaded = !!mission;
    for (const id of ['rv-add','rv-export','rv-history-refresh']) $(id).disabled = !loaded || busy;
    $('rv-save').disabled = !loaded || busy || brushWorking || blockSave;
    $('rv-load').disabled = busy || brushWorking;
    for (const id of ['rv-accept','rv-edit','rv-split','rv-reject','rv-restore']) $(id).disabled = !selected || busy || mode !== 'select';
    $('rv-undo').disabled = !undo.length || busy || brushWorking || mode !== 'select';
    $('rv-redo').disabled = !redo.length || busy || brushWorking || mode !== 'select';
    const canBrush=loaded && !!selected && hdTilesReady && !busy && !brushWorking &&
      !['rejected','superseded'].includes(selected.get('review_status'));
    $('rv-brush-add').disabled=!canBrush || (mode!=='select' && mode!=='brush');
    $('rv-brush-erase').disabled=!canBrush || (mode!=='select' && mode!=='brush');
    $('rv-brush-exit').disabled=mode!=='brush';
    $('rv-brush-size').disabled=!loaded || !hdTilesReady;
    $('rv-fit-all').disabled = !loaded || !map || busy;
    $('rv-focus').disabled = !selected || !map || busy;
    $('rv-jump-man').disabled = !loaded || !map || busy;
    if (!selected && modifier) modifier.setActive(false);
  }
  function updateCounts() {
    if (!reviewSource) return;
    const features = reviewSource.getFeatures();
    const count = s => features.filter(f => f.get('review_status') === s).length;
    $('rv-kpi-total').textContent = features.length;
    $('rv-top-total').textContent = features.length;
    $('rv-top-validated').textContent = count('accepted')+count('edited');
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
    redo.push(JSON.stringify(reviewed()));
    if(redo.length>16)redo.shift();
    const state = JSON.parse(undo.pop());
    loadReview(state);flagDirty();updateButtons();
    alertMsg('Se recuperó el estado anterior (todavía sin guardar en SIGMAD).');
  }
  function doRedo() {
    if (!redo.length || !mission || mode!=='select') return;
    undo.push(JSON.stringify(reviewed()));
    if(undo.length>16) undo.shift();
    loadReview(JSON.parse(redo.pop()));flagDirty();updateButtons();
    alertMsg('Se recuperó el cambio rehecho; todavía falta guardarlo en SIGMAD.');
  }

  function editMethod(previous, next) {
    if(previous==='mixed' || (previous==='brush' && next==='vertices') ||
       (previous==='vertices' && next==='brush'))return 'mixed';
    if(previous==='manual_draw' && next==='brush')return 'mixed';
    return next;
  }

  function brushInfo(text, warn=false) {
    $('rv-brush-status').textContent=text;
    $('rv-brush-status').dataset.warn=warn?'yes':'no';
  }
  function resizeBrushOverlay() {
    if(!brushOverlay)return;
    const r=brushOverlay.getBoundingClientRect(),scale=Math.min(window.devicePixelRatio||1,2);
    if(!r.width||!r.height)return;
    const w=Math.ceil(r.width*scale),h=Math.ceil(r.height*scale);
    if(brushOverlay.width!==w||brushOverlay.height!==h){brushOverlay.width=w;brushOverlay.height=h;}
  }
  function renderBrushCursor(pos=null) {
    if(!brushOverlay)return;
    const c=brushOverlay.getContext('2d');
    c.setTransform(1,0,0,1,0,0);c.clearRect(0,0,brushOverlay.width,brushOverlay.height);
    if(mode!=='brush')return;
    const r=brushOverlay.getBoundingClientRect();if(!r.width||!r.height)return;
    c.setTransform(brushOverlay.width/r.width,0,0,brushOverlay.height/r.height,0,0);
    const stroke=brushActiveStroke;
    if(stroke && stroke.pixels.length) {
      c.beginPath();c.moveTo(...stroke.pixels[0]);
      for(let i=1;i<stroke.pixels.length;i++)c.lineTo(...stroke.pixels[i]);
      c.lineWidth=Number($('rv-brush-size').value)*2;
      c.lineCap='round';c.lineJoin='round';
      c.strokeStyle=brushMode==='add'?'rgba(34,216,143,.62)':'rgba(239,88,103,.72)';
      c.stroke();
      if(stroke.pixels.length===1){c.beginPath();c.arc(...stroke.pixels[0],Number($('rv-brush-size').value),0,Math.PI*2);c.fillStyle=c.strokeStyle;c.fill();}
    }
    const point=pos||stroke?.pixels.at(-1);
    if(point){
      c.beginPath();c.arc(point[0],point[1],Number($('rv-brush-size').value),0,Math.PI*2);
      c.lineWidth=2;c.strokeStyle=brushMode==='add'?'#26e09d':'#ff6675';c.stroke();
      c.beginPath();c.arc(point[0],point[1],2,0,Math.PI*2);c.fillStyle='white';c.fill();
    }
  }
  function turnBrushOff(silent=false) {
    if(brushWorking)return;
    brushMode=null;brushActiveStroke=null;
    if(mode==='brush')mode='select';
    if(brushOverlay){brushOverlay.style.pointerEvents='none';brushOverlay.style.cursor='default';renderBrushCursor();}
    if(selection)selection.setActive(true);
    $('rv-brush-add').dataset.active='false';$('rv-brush-erase').dataset.active='false';
    $('rv-mode').textContent='Modo: seleccionar';
    if(!silent)brushInfo('Navegación activa. Selecciona otra copa o guarda la revisión.');
    updateButtons();
  }
  function toggleBrush(which) {
    if(brushWorking)return;
    if(mode==='brush' && brushMode===which){turnBrushOff();return;}
    if(!selected||!mission||busy||brushWorking||!hdTilesReady||
       (mode!=='select' && mode!=='brush'))return;
    if(['rejected','superseded'].includes(selected.get('review_status')))
      return alertMsg('No se puede pintar una máscara rechazada o reemplazada. Restáurala antes.',true);
    if(!Number.isFinite(currentMosaicGsd)||currentMosaicGsd<=0)
      return alertMsg('Falta GSD del mapa HD; evita correcciones de precisión sin escala nativa.',true);
    brushMode=which;mode='brush';
    if(modifier)modifier.setActive(false);
    if(selection)selection.setActive(false);
    brushOverlay.style.pointerEvents='auto';brushOverlay.style.cursor='crosshair';
    $('rv-brush-add').dataset.active=which==='add'?'true':'false';
    $('rv-brush-erase').dataset.active=which==='erase'?'true':'false';
    $('rv-mode').textContent=which==='add'?'Modo: pincel agregar':'Modo: pincel borrar';
    brushInfo(which==='add'?'PINTAR: arrastra sobre la copa faltante. Suelta para aplicar.':'BORRAR: arrastra sobre la vegetación sobrante. Suelta para aplicar.');
    updateButtons();
  }
  function brushXY(e) {
    const r=brushOverlay.getBoundingClientRect();
    return [e.clientX-r.left,e.clientY-r.top];
  }
  function pushBrushPixel(e) {
    if(!brushActiveStroke || !map)return;
    const px=brushXY(e),last=brushActiveStroke.pixels.at(-1);
    if(last && Math.hypot(last[0]-px[0],last[1]-px[1])<1.2)return;
    const world=map.getCoordinateFromPixel(px);
    if(!world || !world.every(Number.isFinite))return;
    brushActiveStroke.pixels.push(px);brushActiveStroke.world.push(world);
    renderBrushCursor(px);
  }
  function rasterBrush(geometry,worldPoints,radiusM,add,gsd) {
    const bounds=geometry.getExtent().slice();
    for(const p of worldPoints){bounds[0]=Math.min(bounds[0],p[0]-radiusM);bounds[1]=Math.min(bounds[1],p[1]-radiusM);
      bounds[2]=Math.max(bounds[2],p[0]+radiusM);bounds[3]=Math.max(bounds[3],p[1]+radiusM);}
    const minX=Math.floor((bounds[0]-gsd*3)/gsd)*gsd;
    const maxY=Math.ceil((bounds[3]+gsd*3)/gsd)*gsd;
    const w=Math.ceil((bounds[2]+gsd*3-minX)/gsd);
    const h=Math.ceil((maxY-(bounds[1]-gsd*3))/gsd);
    if(w<3||h<3||Math.max(w,h)>4096||w*h>9000000)
      throw new Error(`La copa ocupa ${w}×${h} píxeles. Para evitar pérdida de precisión, usa Modificar vértices o una máscara más pequeña.`);
    const cv=document.createElement('canvas');cv.width=w;cv.height=h;
    const ctx=cv.getContext('2d',{willReadFrequently:true});
    if(!ctx)throw new Error('El navegador no proporciona un lienzo de edición 2D.');
    const pc=p=>[(p[0]-minX)/gsd,(maxY-p[1])/gsd];
    const polys=geometry.getType()==='Polygon'?[geometry.getCoordinates()]:
      geometry.getType()==='MultiPolygon'?geometry.getCoordinates():[];
    if(!polys.length)throw new Error('Solo pueden corregirse polígonos o multipolígonos.');
    ctx.fillStyle='#ffffff';
    for(const poly of polys){
      ctx.beginPath();
      for(const ring of poly){if(ring.length<4)continue;
        const xy=pc(ring[0]);ctx.moveTo(...xy);
        for(let i=1;i<ring.length;i++)ctx.lineTo(...pc(ring[i]));
        ctx.closePath();
      }
      ctx.fill('evenodd');
    }
    const beforeRGBA=ctx.getImageData(0,0,w,h).data;
    const before=new Uint8Array(w*h);
    for(let i=0;i<before.length;i++)before[i]=(beforeRGBA[i*4+3]>127)?1:0;
    // Composición directa: agregar/blanquear o quitar/vaciar. No requiere GPU.
    ctx.save();ctx.globalCompositeOperation=add?'source-over':'destination-out';
    ctx.strokeStyle='#ffffff';ctx.fillStyle='#ffffff';
    ctx.lineWidth=radiusM*2/gsd;ctx.lineCap='round';ctx.lineJoin='round';
    ctx.beginPath();
    const first=pc(worldPoints[0]);ctx.moveTo(...first);
    for(let i=1;i<worldPoints.length;i++)ctx.lineTo(...pc(worldPoints[i]));
    ctx.stroke();
    if(worldPoints.length===1){ctx.beginPath();ctx.arc(...first,radiusM/gsd,0,Math.PI*2);ctx.fill();}
    ctx.restore();
    const afterRGBA=ctx.getImageData(0,0,w,h).data;
    const after=new Uint8Array(w*h);
    let countAdd=0,countRemove=0;
    for(let i=0;i<after.length;i++){
      after[i]=afterRGBA[4*i+3]>127?1:0;
      if(after[i]&&!before[i])countAdd++;
      else if(!after[i]&&before[i])countRemove++;
    }
    if(!countAdd&&!countRemove)return null;
    const {polygons}=window.SigmadMaskEngine.polygonize(after,w,h,{tolerance:0.35});
    if(!polygons.length)throw new Error('El pincel borró toda la máscara. Utiliza Rechazar si no representa una copa real.');
    const geoms=window.SigmadMaskEngine.geoMultiPolygon(polygons,minX,maxY,gsd);
    const vertices=geoms.reduce((sum,poly)=>sum+poly.reduce((n,ring)=>n+ring.length,0),0);
    if(vertices>28000)throw new Error(`La máscara resultó demasiado compleja (${vertices} vértices). Reduce el área de trabajo.`);
    return {geom:geoms.length===1?new ol.geom.Polygon(geoms[0]):new ol.geom.MultiPolygon(geoms),
      countAdd,countRemove,gsd};
  }
  async function commitBrush(stroke) {
    if(!stroke||!selected||!map||brushWorking)return;
    const target=selected;
    const res=map.getView().getResolution(),gsd=currentMosaicGsd;
    if(!Number.isFinite(res)||res>gsd*4){
      brushInfo('Acércate más: necesitas una escala próxima al GSD nativo para editar con precisión.',true);
      return;
    }
    brushWorking=true;updateButtons();
    brushInfo('Convirtiendo trazos en un contorno georreferenciado…');
    await new Promise(resolve=>requestAnimationFrame(resolve));
    try{
      const radiusM=Number($('rv-brush-size').value)*res;
      const result=rasterBrush(target.getGeometry(),stroke.world,radiusM,stroke.kind==='add',gsd);
      if(!result){brushInfo('No hubo cambios en la máscara.');return;}
      backupUndo();
      target.setGeometry(result.geom);
      if(['pending','accepted','edited'].includes(target.get('review_status')))target.set('review_status','edited');
      target.set('edit_method',editMethod(target.get('edit_method'),'brush'));
      target.set('brush_strokes',Math.min(100000,(Number(target.get('brush_strokes'))||0)+1));
      target.set('brush_added_m2',(Number(target.get('brush_added_m2'))||0)+result.countAdd*gsd*gsd);
      target.set('brush_removed_m2',(Number(target.get('brush_removed_m2'))||0)+result.countRemove*gsd*gsd);
      reviewSource.changed();flagDirty();populateAttributes();updateCounts();
      brushInfo(`Cambio aplicado: +${result.countAdd} px / −${result.countRemove} px. Pulsa «Navegar» antes de guardar.`);
      alertMsg('Pincel aplicado a la copia V2; la máscara IA original permanece intacta.');
    }catch(e){brushInfo('No se aplicó el trazo: '+e.message,true);alertMsg('No se modificó la máscara: '+e.message,true);}
    finally{brushWorking=false;updateButtons();}
  }
  function bindBrushOverlay() {
    brushOverlay=$('rv-brush-overlay');if(!brushOverlay)return;
    resizeBrushOverlay();
    brushOverlay.addEventListener('pointerdown',e=>{
      if(mode!=='brush'||brushWorking||!selected||e.button!==0)return;
      e.preventDefault();e.stopPropagation();
      brushOverlay.setPointerCapture(e.pointerId);
      brushActiveStroke={kind:brushMode,pixels:[],world:[]};pushBrushPixel(e);
    });
    brushOverlay.addEventListener('pointermove',e=>{
      if(mode!=='brush')return;
      if(brushActiveStroke)pushBrushPixel(e);
      else renderBrushCursor(brushXY(e));
    });
    brushOverlay.addEventListener('pointerup',e=>{
      if(!brushActiveStroke)return;
      e.preventDefault();e.stopPropagation();pushBrushPixel(e);
      const stroke=brushActiveStroke;brushActiveStroke=null;renderBrushCursor();
      void commitBrush(stroke);
    });
    brushOverlay.addEventListener('pointercancel',()=>{brushActiveStroke=null;renderBrushCursor();brushInfo('Trazo cancelado; no se modificó la máscara.');});
    brushOverlay.addEventListener('pointerleave',()=>{if(!brushActiveStroke)renderBrushCursor();});
    window.addEventListener('resize',resizeBrushOverlay);
  }

  function setMapQuality(state, text) {
    mapQuality = state;
    const element = $('rv-map-quality');
    element.dataset.quality = state;
    element.textContent = text;
  }
  function focusExtent(extent, padding=52) {
    if (!map || !Array.isArray(extent) || extent.length!==4) return;
    map.getView().fit(extent, {size:map.getSize(),padding:[padding,padding,padding,padding],
      maxZoom: Math.max(0, (map.getView().getResolutions()?.length||17)-2),duration:360});
  }
  function focusFeature(feature) {
    if (!map || !feature) return;
    const geom=feature.getGeometry();
    if (!geom) return;
    const center=ol.extent.getCenter(geom.getExtent());
    const size=map.getSize()||[800,600];
    const view=map.getView();
    if (geom.getType()==='Point') {
      view.animate({center,zoom:Math.min(view.getMaxZoom(),Math.max(view.getZoom()||0,view.getMaxZoom()-2)),duration:350});
      return;
    }
    const ext=geom.getExtent();
    const width=Math.max(ext[2]-ext[0],4), height=Math.max(ext[3]-ext[1],4);
    const pad=Math.max(width,height)*2.2;
    const expanded=[center[0]-pad,center[1]-pad,center[0]+pad,center[1]+pad];
    view.fit(expanded,{size,padding:[50,50,50,50],duration:350});
  }
  function updateMapReadout() {
    if (!map) return;
    const resolution=map.getView().getResolution();
    const gsd = Number.isFinite(currentMosaicGsd) ? ` · GSD ${Math.round(currentMosaicGsd*1000)/10} cm/px` : '';
    $('rv-map-scale').textContent=Number.isFinite(resolution)
      ? `Vista ${resolution.toFixed(resolution<1?3:1)} m/px${gsd}` : 'Escala: —';
  }
  function isValidMaster(manifest,data) {
    return manifest && manifest.schema==='sigmad-master-utm-v1' &&
      manifest.analysis_uuid===data.analysis_uuid && manifest.crs===data.crs &&
      Array.isArray(manifest.extent) && manifest.extent.length===4 &&
      Array.isArray(manifest.origin) && manifest.origin.length===2 &&
      Array.isArray(manifest.resolutions) && manifest.resolutions.length>0 &&
      manifest.tile_size===256 && typeof manifest.tile_url_template==='string';
  }
  async function mapFor(data) {
    if(mode==='brush')turnBrushOff(true);
    if (map) {map.setTarget(null);map=null;}
    hdTilesReady=false;fullMapExtent = null; currentMosaicGsd = null;
    setMapQuality('loading','● Buscando ortomosaico HD maestro…');
    const m=/^EPSG:(326|327)(\d{2})$/.exec(data.crs);
    if (!m) throw new Error('Este editor requiere CRS UTM EPSG:326xx o EPSG:327xx.');
    proj4.defs(data.crs,`+proj=utm +zone=${Number(m[2])} ${m[1]==='327'?'+south ':''}+datum=WGS84 +units=m +no_defs +type=crs`);
    ol.proj.proj4.register(proj4);
    originalSource=new ol.source.Vector({features:format().readFeatures(data.original,{
      dataProjection:'EPSG:4326',featureProjection:data.crs}),wrapX:false});
    reviewSource=new ol.source.Vector({wrapX:false});
    manualSource=new ol.source.Vector({wrapX:false});
    // Las marcas MAN llegan como puntos UTM. Nunca se tratan como lon/lat.
    manualSource.addFeatures(format().readFeatures({type:'FeatureCollection',
      features:data.manual_targets?.features||[]},{dataProjection:data.crs,featureProjection:data.crs}));
    const layers=[];
    const missionExtent=data.bounds;
    fullMapExtent=missionExtent;
    // Vista previa: respaldo orientativo. No sirve para ajustes precisos a máximo zoom.
    if (data.preview_url && Array.isArray(data.bounds)) {
      layers.push(new ol.layer.Image({source:new ol.source.ImageStatic({
        url:data.preview_url,projection:data.crs,imageExtent:data.bounds,
      }),zIndex:0}));
    }
    let manifest=null, hdError=null;
    if (data.master_hd_manifest_url) {
      try {
        const response=await fetch(data.master_hd_manifest_url,{credentials:'same-origin',cache:'no-store',
          headers:{Accept:'application/json'}});
        if (response.ok) {
          const candidate=await response.json();
          if (!isValidMaster(candidate,data)) throw new Error('CRS o análisis no coincide con las máscaras de esta misión.');
          manifest=candidate;
        } else if (response.status!==404) hdError=`HTTP ${response.status}`;
      } catch(e){ hdError=e.message; }
    } else hdError='Sin identificador de análisis';
    let viewOptions={projection:data.crs,center:ol.extent.getCenter(fullMapExtent),zoom:0};
    if (manifest) {
      fullMapExtent=manifest.extent;
      currentMosaicGsd=Number(manifest.gsd_m);
      if(!Number.isFinite(currentMosaicGsd)||currentMosaicGsd<=0) currentMosaicGsd=null;
      const grid=new ol.tilegrid.TileGrid({extent:manifest.extent,origin:manifest.origin,
        resolutions:manifest.resolutions,tileSize:256});
      const source=new ol.source.TileImage({projection:data.crs,tileGrid:grid,interpolate:true,
        tileUrlFunction:coord=> {
          if(!coord) return undefined;
          return manifest.tile_url_template.replace('__Z__',String(coord[0]))
            .replace('__X__',String(coord[1])).replace('__Y__',String(coord[2]));
        }});
      let errors=0, rendered=0;
      source.on('tileloadend',()=>{
        if(!rendered++) setMapQuality('hd',`● Ortomosaico maestro HD · ${manifest.tile_count||'varias'} teselas`);
      });
      source.on('tileloaderror',()=>{
        errors++;
        setMapQuality('warning',`● HD parcial: ${errors} teselas sin cargar · revisa la conexión`);
      });
      layers.push(new ol.layer.Tile({source,zIndex:1}));
      viewOptions={projection:data.crs,center:ol.extent.getCenter(fullMapExtent),zoom:0,
        resolutions:manifest.resolutions};
      setMapQuality('hd',`● HD maestro conectado · GSD ${Math.round((currentMosaicGsd||0)*1000)/10} cm`);
    } else {
      setMapQuality('preview',data.preview_url?'● Vista preliminar · resolución insuficiente para edición fina':'● Sin ortomosaico HD');
      if(hdError) console.info('SIGMAD laboratorio: HD maestro no disponible:',hdError);
    }
    originalLayer=new ol.layer.Vector({source:originalSource,style:f=>polygonStyle(f,true),zIndex:10});
    reviewLayer=new ol.layer.Vector({source:reviewSource,style:f=>polygonStyle(f),zIndex:11});
    manualLayer=new ol.layer.Vector({source:manualSource,style:manualStyle,zIndex:12});
    layers.push(originalLayer,reviewLayer,manualLayer);
    map=new ol.Map({target:'rv-map',layers,view:new ol.View(viewOptions),
      controls:ol.control.defaults.defaults({rotate:false})});
    map.updateSize();resizeBrushOverlay();
    map.on('change:size',resizeBrushOverlay);
    focusExtent(fullMapExtent,28);
    configureSelection();
    map.getView().on('change:resolution',updateMapReadout);
    map.on('pointermove',event=>{
      if(event.dragging)return;
      const coords=event.coordinate;
      $('rv-map-position').textContent=`UTM ${coords[0].toFixed(1)} E · ${coords[1].toFixed(1)} N`;
    });
    map.on('singleclick',evt=>{
      if (mode!=='select' || selected) return;
      const mark=map.forEachFeatureAtPixel(evt.pixel,f=>f.get('manual_target_uuid')?f:null,
        {layerFilter:layer=>layer===manualLayer,hitTolerance:10});
      if (mark) {
        selectedManual=mark.get('manual_target_uuid');
        $('rv-jump-man').value=selectedManual;
        alertMsg(`${mark.get('manual_code')||'MAN'}: marca de revisión seleccionada. Pulsa «Agregar copa» solo si su contorno es visible.`);
      }
    });
    $('rv-show-original').onchange=()=>originalLayer.setVisible($('rv-show-original').checked);
    $('rv-show-manual').onchange=()=>manualLayer.setVisible($('rv-show-manual').checked);
    originalLayer.setVisible($('rv-show-original').checked);
    manualLayer.setVisible($('rv-show-manual').checked);
    updateMapReadout();
    return !!manifest;
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
      const hdAvailable=await mapFor(data);
      const collection=data.review || originalToDraft(data.original);
      loadReview(collection);
      $('rv-version').textContent=`Revisión V${data.revision_no}`;
      $('rv-manual').replaceChildren(new Option('Sin vínculo MAN',''));
      $('rv-jump-man').replaceChildren(new Option('Ir a una marca MAN…',''));
      $('rv-top-manual').textContent=(data.manual_targets?.features||[]).length;
      for (const f of data.manual_targets?.features||[]) {
        const p=f.properties||{};
        $('rv-manual').add(new Option(`${p.manual_code} · ${p.reason||'pendiente'}`,p.manual_target_uuid));
        $('rv-jump-man').add(new Option(`${p.manual_code} · ${p.reason||'por verificar'}`,p.manual_target_uuid));
      }
      flagDirty(false);
      await loadHistory();
      if (blockSave) alertMsg('La misión original cambió desde la última revisión. Puedes exportar, pero no guardar hasta reconciliar.',true);
      else alertMsg(`${data.title} · ${data.original.features.length} máscaras IA · ${data.manual_targets?.features.length||0} marcas MAN · ${hdAvailable?'ortomosaico maestro HD':'solo vista preliminar'} · Revisión V${data.revision_no}.`,!hdAvailable);
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
    $('rv-redo').addEventListener('click',doRedo);
    $('rv-brush-add').addEventListener('click',()=>toggleBrush('add'));
    $('rv-brush-erase').addEventListener('click',()=>toggleBrush('erase'));
    $('rv-brush-exit').addEventListener('click',()=>turnBrushOff());
    $('rv-brush-size').addEventListener('input',()=>{
      $('rv-brush-size-value').textContent=$('rv-brush-size').value+' px';renderBrushCursor();
    });
    bindBrushOverlay();
    window.addEventListener('keydown',e=>{
      const t=e.target?.tagName?.toLowerCase();if(['input','textarea','select'].includes(t)||e.target?.isContentEditable)return;
      if(e.key==='Escape'&&mode==='brush'){turnBrushOff();e.preventDefault();return;}
      if((e.ctrlKey||e.metaKey)&&mode==='select'&&e.key.toLowerCase()==='z'){
        e.preventDefault();if(e.shiftKey)doRedo();else doUndo();
      }else if((e.ctrlKey||e.metaKey)&&mode==='select'&&e.key.toLowerCase()==='y'){e.preventDefault();doRedo();}
    });
    $('rv-apply-attrs').addEventListener('click',applyAttributes);
    $('rv-save').addEventListener('click',()=>void save());
    $('rv-export').addEventListener('click',exportGeojson);
    $('rv-history-refresh').addEventListener('click',()=>void loadHistory().catch(e=>alertMsg(e.message,true)));
    $('rv-fit-all').addEventListener('click',()=>focusExtent(fullMapExtent,30));
    $('rv-focus').addEventListener('click',()=>focusFeature(selected));
    $('rv-jump-man').addEventListener('change',e=>{
      if(!manualSource||!e.target.value)return;
      const target=manualSource.getFeatures().find(f=>f.get('manual_target_uuid')===e.target.value);
      if(!target)return;
      selectedManual=e.target.value;
      focusFeature(target);
      alertMsg(`${target.get('manual_code')||'MAN'} localizada. Verifica visualmente si hay copa segmentable antes de dibujar.`);
    });
    window.addEventListener('beforeunload', e=>{if(dirty){e.preventDefault();e.returnValue='';}});
    const preferred=[...$('rv-mission').options].find(o=>o.textContent?.toLowerCase().includes('mision completa'));
    if(preferred) $('rv-mission').value=preferred.value;
    if (!window.ol || !window.proj4 || !window.SigmadMaskEngine)
      alertMsg('Faltan OpenLayers, proj4 o el motor local de pinceles. Comprueba sus rutas.',true);
  }
  bind();
})();
