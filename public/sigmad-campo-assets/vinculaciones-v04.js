/* SIGMAD Campo V0.4.0 — selección explícita copa↔fuste.
 * No infiere identidades por proximidad y no altera datos de adquisición.
 */
(() => {
  'use strict';
  const root = document.getElementById('v04-map');
  if (!root) return;
  const $ = id => document.getElementById(id);
  const msg = $('v04-map-feedback');
  const count = $('v04-map-counter');
  const crownSelect = $('v04-crown-select');
  const recordSelect = $('v04-observation-select');
  const decision = $('v04-decision-select');
  const evidence = $('v04-evidence-select');
  const justification = $('v04-justification');
  const contextWarning = $('v04-context-warning');
  const imageVisible = $('v04-image-visible');
  const crownsVisible = $('v04-crowns-visible');
  const gpsVisible = $('v04-gps-visible');
  const precisionVisible = $('v04-precision-visible');
  if (!window.ol || !window.proj4) {
    msg.textContent = 'Faltan los recursos locales ol.js y proj4.js. Los selectores del formulario siguen disponibles.';
    return;
  }
  const ol = window.ol;
  let map, mission, crownLayer, gpsLayer, precisionLayer, preview, hd;
  let crowns, points, gpsById, bounds;
  const styles = {
    crown: new ol.style.Style({stroke:new ol.style.Stroke({color:'#00a68c',width:2.6}),fill:new ol.style.Fill({color:'rgba(0,166,140,.13)'})}),
    selectedCrown: new ol.style.Style({stroke:new ol.style.Stroke({color:'#f4a527',width:4.5}),fill:new ol.style.Fill({color:'rgba(255,180,41,.25)'})}),
    gps: new ol.style.Style({image:new ol.style.Circle({radius:8,fill:new ol.style.Fill({color:'#2464a1'}),stroke:new ol.style.Stroke({color:'#fff',width:2.5})})}),
    selectedGps: new ol.style.Style({image:new ol.style.Circle({radius:11,fill:new ol.style.Fill({color:'#e78d26'}),stroke:new ol.style.Stroke({color:'#fff',width:3})})}),
    precision: new ol.style.Style({stroke:new ol.style.Stroke({color:'rgba(33,101,174,.8)',width:1.3,lineDash:[4,5]}),fill:new ol.style.Fill({color:'rgba(33,101,174,.08)'})})
  };
  const finite = v => v !== '' && v !== null && v !== undefined && Number.isFinite(Number(v));
  const fetchJSON = async url => {
    const r = await fetch(url,{credentials:'same-origin',headers:{Accept:'application/json'},cache:'no-store'});
    if(!r.ok) throw new Error('HTTP '+r.status);
    return r.json();
  };
  const setMsg = text => { if(msg) msg.textContent=text; };
  const fitBounds = () => map && bounds && map.getView().fit(bounds,{padding:[40,40,40,40],maxZoom:19,duration:0});
  const setVisible = () => {
    preview?.setVisible(!!imageVisible.checked);
    hd?.setVisible(!!imageVisible.checked);
    crownLayer?.setVisible(!!crownsVisible.checked);
    gpsLayer?.setVisible(!!gpsVisible.checked);
    precisionLayer?.setVisible(!!gpsVisible.checked && !!precisionVisible.checked);
  };
  const chosenOption = () => [...crownSelect.options].find(o=>o.value===crownSelect.value);
  function selectCrown(id, fromMap=false) {
    if(![...crownSelect.options].some(o=>o.value===id))return;
    crownSelect.value=id;
    const option=chosenOption();
    recordSelect.value=option?.dataset.record||'';
    decision.value=option?.dataset.decision||'';
    evidence.value=option?.dataset.evidence||'revision_mapa';
    justification.value=option?.dataset.note||'';
    const checked=document.querySelector('input[name="verified_context"]');
    if(checked) checked.checked=false; // decisión nueva requiere reconocimiento explícito
    onDecisionChange();
    crownLayer?.changed();gpsLayer?.changed();
    document.querySelectorAll('.v04-crown-row').forEach(row=>row.classList.toggle('v04-active',row.dataset.crown===id));
    warnContext();
    if(fromMap) $('v04-form')?.scrollIntoView({behavior:'smooth',block:'start'});
  }
  function selectRecord(id, fromMap=false) {
    if(![...recordSelect.options].some(o=>o.value===String(id)))return;
    if(decision.value==='sin_correspondencia')decision.value='dudosa';
    recordSelect.disabled=false;
    recordSelect.value=String(id);
    gpsLayer?.changed();
    warnContext();
    if(fromMap)$('v04-form')?.scrollIntoView({behavior:'smooth',block:'start'});
  }
  function warnContext() {
    if(!contextWarning)return;
    contextWarning.hidden=true;
    if(!map || !bounds || !recordSelect.value || !gpsById)return;
    const f=gpsById.get(String(recordSelect.value));
    if(!f){
      contextWarning.textContent='La ficha seleccionada no tiene GPS utilizable en la parcela. Verifica su identidad con croquis, vecinos y evidencia independiente.';
      contextWarning.hidden=false;
      return;
    }
    const inside=ol.extent.containsCoordinate(bounds,f.getGeometry().getCoordinates());
    if(!inside){
      contextWarning.textContent='ATENCIÓN: El GPS de esta ficha está fuera de los límites del ortomosaico. No confirmes la correspondencia sin evidencia independiente que explique la discrepancia.';
      contextWarning.hidden=false;
    }
  }
  function onDecisionChange(){
    const noMatch=decision.value==='sin_correspondencia';
    if(noMatch){recordSelect.value='';recordSelect.disabled=true;}
    else recordSelect.disabled=false;
    gpsLayer?.changed();warnContext();
  }
  crownSelect.addEventListener('change',()=>{if(crownSelect.value)selectCrown(crownSelect.value);else crownLayer?.changed();});
  recordSelect.addEventListener('change',()=>{gpsLayer?.changed();warnContext();});
  decision.addEventListener('change',onDecisionChange);
  document.querySelectorAll('.v04-crown-row').forEach(row=>row.addEventListener('click',()=>{
    selectCrown(row.dataset.crown,true);
    const feature=crowns?.getFeatures().find(f=>f.get('operational_id')===row.dataset.crown);
    if(feature) map.getView().fit(feature.getGeometry().getExtent(),{padding:[65,65,65,65],maxZoom:21,duration:160});
  }));
  for(const id of ['v04-image-visible','v04-crowns-visible','v04-gps-visible','v04-precision-visible'])$(id)?.addEventListener('change',setVisible);
  $('v04-fit-mission')?.addEventListener('click',fitBounds);
  $('v04-fit-gps')?.addEventListener('click',()=>{
    if(!gpsById || gpsById.size===0){setMsg('No hay posiciones GPS utilizables en esta misión.');return;}
    const feats=[...gpsById.values()];
    if(feats.length===1){map.getView().setCenter(feats[0].getGeometry().getCoordinates());map.getView().setZoom(19);}
    else map.getView().fit(gpsLayer.getSource().getExtent(),{padding:[48,48,48,48],maxZoom:19,duration:0});
    setMsg('Vista centrada en GPS de campo; la distancia no prueba identidad.');
  });
  document.querySelector('.v04-form')?.addEventListener('submit', e => {
    if(decision.value==='sin_correspondencia')recordSelect.disabled=false;
    if(!crownSelect.value){e.preventDefault();crownSelect.focus();return;}
  });
  // OpenLayers y proj4 reutilizados de SIGMAD 0.3.2; el ortomosaico es UTM.
  function setupProjection(crs) {
    const match=/^EPSG:(326|327)(\d{2})$/.exec(crs||'');
    if(!match || +match[2]<1 || +match[2]>60)throw new Error('CRS UTM no compatible');
    proj4.defs(crs,`+proj=utm +zone=${+match[2]} ${match[1]==='327'?'+south ':''}+datum=WGS84 +units=m +no_defs +type=crs`);
    ol.proj.proj4.register(proj4);
  }
  async function addHD(data) {
    if(!data.tile_manifest_url)return;
    try {
      const manifest=await fetchJSON(data.tile_manifest_url);
      if(manifest.schema!=='sigmad-campo-utm-tiles-v1'||manifest.mission_uuid!==data.uuid||
        manifest.crs!==data.crs||manifest.analysis_uuid!==data.analysis_uuid||
        !Array.isArray(manifest.tiles)||!Array.isArray(manifest.origin)||
        !Array.isArray(manifest.bounds)||!Array.isArray(manifest.resolutions)||
        manifest.tile_size!==256||!manifest.tile_url_template)return;
      const keys=new Set(manifest.tiles);
      hd=new ol.layer.Tile({source:new ol.source.TileImage({projection:data.crs,wrapX:false,
        tileGrid:new ol.tilegrid.TileGrid({extent:manifest.bounds,origin:manifest.origin,resolutions:manifest.resolutions,tileSize:256}),
        tileUrlFunction:coord=>{
          if(!coord)return undefined;
          return keys.has(`${coord[0]}/${coord[1]}/${coord[2]}`)?
            manifest.tile_url_template.replace('__Z__',coord[0]).replace('__X__',coord[1]).replace('__Y__',coord[2]):undefined;
        }
      })});
      map.getLayers().insertAt(preview?2:1,hd);setVisible();
    }catch(_){setMsg('Copas y puntos cargados. Las teselas HD no están disponibles; se utiliza vista previa.');}
  }
  async function init() {
    try {
      const [data, gps]=await Promise.all([fetchJSON(root.dataset.missionUrl),fetchJSON(root.dataset.pointsUrl)]);
      if(!Array.isArray(data.bounds)||data.bounds.length!==4||!Array.isArray(data.geojson?.features))throw new Error('Formato de misión inválido');
      setupProjection(data.crs);mission=data;bounds=data.bounds.map(Number);
      if(!bounds.every(Number.isFinite))throw new Error('Bounds inválidos');
      const geoFormat=new ol.format.GeoJSON();
      const crownFeatures=geoFormat.readFeatures(data.geojson,{dataProjection:'EPSG:4326',featureProjection:data.crs});
      crowns=new ol.source.Vector({features:crownFeatures,wrapX:false});
      crownFeatures.forEach(f=>f.set('kind','crown'));
      crownLayer=new ol.layer.Vector({source:crowns,style:f=>f.get('operational_id')===crownSelect.value?styles.selectedCrown:styles.crown});
      const pointFeatures=[], accuracyFeatures=[];gpsById=new Map();
      for(const p of (Array.isArray(gps.points)?gps.points:[])){
        if(!finite(p.lon)||!finite(p.lat)||Number(p.lon)<-180||Number(p.lon)>180||Number(p.lat)<-90||Number(p.lat)>90)continue;
        const coord=ol.proj.transform([Number(p.lon),Number(p.lat)],'EPSG:4326',data.crs);
        if(!coord.every(Number.isFinite))continue;
        const f=new ol.Feature({geometry:new ol.geom.Point(coord)});
        f.setProperties({kind:'gps',record_id:String(p.id),label:p.label,grupo:p.grupo});
        gpsById.set(String(p.id),f);pointFeatures.push(f);
        const radius=Number(p.accuracy_m);
        if(p.accuracy_m!==null&&finite(radius)&&radius>0&&radius<=1000)
          accuracyFeatures.push(new ol.Feature({geometry:new ol.geom.Circle(coord,radius)}));
      }
      gpsLayer=new ol.layer.Vector({source:new ol.source.Vector({features:pointFeatures,wrapX:false}),
        style:f=>f.get('record_id')===recordSelect.value?styles.selectedGps:styles.gps});
      precisionLayer=new ol.layer.Vector({source:new ol.source.Vector({features:accuracyFeatures,wrapX:false}),style:styles.precision});
      const base=new ol.layer.Tile({source:new ol.source.OSM({wrapX:false})});
      map=new ol.Map({target:root,layers:[base,precisionLayer,crownLayer,gpsLayer],
        view:new ol.View({projection:data.crs,center:ol.extent.getCenter(bounds),zoom:9})});
      if(data.preview_url){
        preview=new ol.layer.Image({source:new ol.source.ImageStatic({url:data.preview_url,projection:data.crs,imageExtent:bounds})});
        map.getLayers().insertAt(1,preview);
      }
      map.on('singleclick',evt=>{
        const f=map.forEachFeatureAtPixel(evt.pixel,feat=>{
          if(feat.get('kind')==='gps'||feat.get('kind')==='crown')return feat;
        },{hitTolerance:8});
        if(!f)return;
        if(f.get('kind')==='crown'){selectCrown(f.get('operational_id'),true);setMsg('Copa '+f.get('operational_id')+' seleccionada. Elige un árbol y describe la evidencia.');}
        else {selectRecord(f.get('record_id'),true);setMsg('Registro de campo #'+f.get('record_id')+' seleccionado. Revisa la ubicación y la ficha antes de confirmar.');}
      });
      fitBounds();setVisible();
      if(count)count.textContent=`${crownFeatures.length} copas IA · ${pointFeatures.length} GPS de campo`;
      setMsg('Mapa listo: seleccione primero una copa IA, después una ficha de campo.');
      await addHD(data);
      const initial=root.dataset.initialCrown||crownSelect.value;
      if(initial)selectCrown(initial);
      else warnContext();
      map.updateSize();
    }catch(err){setMsg('No se pudo cargar el mapa de validación: '+err.message+'. El formulario sigue disponible.');}
  }
  init();
})();
