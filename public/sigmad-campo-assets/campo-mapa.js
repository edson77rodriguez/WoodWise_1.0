/* SIGMAD Campo V0.6.1: mapa de misión + GPS + teselas HD privadas offline.
   No altera la base de datos de observaciones ni asigna automáticamente TP/FP/FN. */
(() => {
  'use strict';
  const DB_NAME = 'sigmad-campo-misiones-v02';
  const STORE = 'missions';
  const TILE_STORE = 'hd_tiles';
  const EMPTY_TILE = 'data:image/gif;base64,R0lGODlhAQABAAD/ACwAAAAAAQABAAACADs=';
  let ctx = null, mapDb=null, map=null, rasterUrl=null, geoSource=null;
  let gpsSource=null, aiLayer=null, gpsFeature=null, accuracyFeature=null;
  let hdLayer=null, hdSource=null, activeMission=null, activeCandidate=null;
  let manualSource=null,manualLayer=null,activeManualTarget=null;
  let records=[], group='A', gpsWatch=null, gpsFollow=true, currentPosition=null;
  let hdCheckToken=0, selectionToken=0, hdDownloading=false;
  const $ = id => document.getElementById(id);
  function feedback(msg, warn=false) {const el=$('map-feedback');if(el){el.textContent=msg;el.dataset.warn=warn?'yes':'no';}}
  function hdFeedback(msg, warn=false) {const el=$('map-hd-status');if(el){el.textContent=msg;el.dataset.warn=warn?'yes':'no';}}
  function previewFeedback(msg, warn=false) {const el=$('map-preview-status');if(el){el.textContent=msg;el.dataset.warn=warn?'yes':'no';}}
  function openDb(){return new Promise((resolve,reject)=>{
    const req=indexedDB.open(DB_NAME,2);
    req.onupgradeneeded=()=>{
      const db=req.result;
      if(!db.objectStoreNames.contains(STORE))db.createObjectStore(STORE,{keyPath:'uuid'});
      if(!db.objectStoreNames.contains(TILE_STORE))db.createObjectStore(TILE_STORE,{keyPath:'key'});
    };
    req.onsuccess=()=>resolve(req.result);req.onerror=()=>reject(req.error);
    req.onblocked=()=>reject(new Error('Cierra otras pestañas de SIGMAD Campo para actualizar IndexedDB.'));
  });}
  function requestPromise(req){return new Promise((resolve,reject)=>{req.onsuccess=()=>resolve(req.result);req.onerror=()=>reject(req.error);});}
  function savedMission(uuid){return requestPromise(mapDb.transaction(STORE).objectStore(STORE).get(uuid));}
  function storeMission(entry){return requestPromise(mapDb.transaction(STORE,'readwrite').objectStore(STORE).put(entry));}
  function tileKey(entry,cat,id){return `${ctx.user_id}|${entry.uuid}|${cat.tile_set_id}|${id}`;}
  function getTile(key){return requestPromise(mapDb.transaction(TILE_STORE).objectStore(TILE_STORE).get(key));}
  function putTile(key,blob){return requestPromise(mapDb.transaction(TILE_STORE,'readwrite').objectStore(TILE_STORE).put({key,blob,bytes:blob.size}));}
  const delay=ms=>new Promise(resolve=>setTimeout(resolve,ms));
  async function normalizeRaster(blob,kind){
    if(!window.SIGMAD_RASTER?.normalize)throw new Error('Falta el verificador de imágenes V0.6. Recarga SIGMAD conectado.');
    return window.SIGMAD_RASTER.normalize(blob,kind);
  }
  function sameRaster(old,entry){
    return old && old.user_id===ctx.user_id && old.analysis_uuid===entry.analysis_uuid &&
      old.crs===entry.crs && JSON.stringify(old.bounds)===JSON.stringify(entry.bounds);
  }
  async function ensurePreview(entry){
    let checked=null;
    if(entry.preview_blob){
      checked=await normalizeRaster(entry.preview_blob,'preview');
      if(checked){
        if(checked.repaired || checked.blob!==entry.preview_blob){
          entry.preview_blob=checked.blob;
          await storeMission(entry);
        }
        previewFeedback(checked.repaired?'Fondo recuperado automáticamente; disponible sin conexión.':'Fondo preliminar verificado; disponible sin conexión.');
        return true;
      }
      // Solo retiramos la copia de imagen inválida. NUNCA tocamos registros de campo.
      entry.preview_blob=null;
      await storeMission(entry);
    }
    if(entry.preview_url && navigator.onLine && window.SIGMAD_FIELD_BOOT){
      try{
        previewFeedback('Recuperando el fondo cartográfico…');
        const response=await fetch(entry.preview_url,{credentials:'same-origin',cache:'no-store'});
        if(!response.ok)throw new Error('HTTP '+response.status);
        checked=await normalizeRaster(await response.blob(),'preview');
        if(!checked)throw new Error('Imagen incompleta o no decodificable.');
        entry.preview_blob=checked.blob;
        await storeMission(entry);
        previewFeedback(checked.repaired?'Fondo recuperado. Aviso: la respuesta del servidor aún tenía un byte extra.':'Fondo descargado y verificado; disponible sin conexión.',checked.repaired);
        return true;
      }catch(error){
        previewFeedback('No se pudo recuperar el fondo: '+error.message+' · Conéctate e intenta «Descargar misión».',true);
        return false;
      }
    }
    previewFeedback('Fondo no disponible sin conexión. Conservamos las copas y las mediciones; conecta el dispositivo para recuperarlo.',true);
    return false;
  }
  async function repairStoredTile(entry,cat,id){
    const key=tileKey(entry,cat,id);
    const row=await getTile(key);
    if(!row?.blob)return {ok:false,repaired:false};
    const checked=await normalizeRaster(row.blob,'tile');
    if(!checked)return {ok:false,repaired:false};
    if(checked.repaired || checked.blob!==row.blob){await putTile(key,checked.blob);}
    return {ok:true,repaired:checked.repaired,blob:checked.blob};
  }
  async function verifyCachedHD(entry,token){
    const cat=entry.tile_manifest;
    if(!usableCatalog(cat,entry)||hdDownloading)return;
    const total=cat.tiles.length;
    let valid=0,repaired=0;
    hdFeedback(`Verificando el mapa HD almacenado: 0/${total}…`);
    for(let i=0;i<total;i++){
      if(token!==hdCheckToken||activeMission!==entry||hdDownloading)return;
      try{
        const result=await repairStoredTile(entry,cat,cat.tiles[i]);
        if(result.ok)valid++;
        if(result.repaired)repaired++;
      }catch(err){console.warn('SIGMAD: tesela local inválida',cat.tiles[i],err.message);}
      if((i+1)%30===0){hdFeedback(`Comprobando HD local: ${i+1}/${total}…`);await delay(0);}
    }
    if(token!==hdCheckToken||activeMission!==entry||hdDownloading)return;
    entry.hd_complete=valid===total && total>0;
    await storeMission(entry);
    if(repaired&&hdSource)hdSource.refresh();
    hdFeedback(entry.hd_complete
      ? `Mapa HD verificado: ${valid}/${total} teselas; ${repaired} recuperadas. Preparado para probar sin conexión.`
      : `HD parcial: ${valid}/${total} teselas válidas. ${total-valid} requieren descarga; pulsa «Descargar mapa HD» antes de salir.`,!entry.hd_complete);
  }
  async function fetchValidTile(cat,id){
    let last=null;
    for(let attempt=0;attempt<3;attempt++){
      try{
        const response=await fetch(tileUrl(cat,id),{credentials:'same-origin',cache:'no-store'});
        if(!response.ok){
          const error=new Error('HTTP '+response.status);
          if([401,403,404,419].includes(response.status))throw Object.assign(error,{permanent:true});
          throw error;
        }
        const checked=await normalizeRaster(await response.blob(),'tile');
        if(!checked)throw new Error('WebP inválida o truncada');
        return checked;
      }catch(err){
        last=err;
        if(err.permanent||attempt===2)break;
        await delay(350*(attempt+1));
      }
    }
    throw last||new Error('No se pudo descargar la tesela');
  }
  function getMissionList(){return (ctx?.missions||[]).filter(m=>String(m.id_parcela)===String($('parcela')?.value||''));}
  function updateOptions(){
    const input=$('campo-mission');if(!input)return;
    const old=input.value;input.replaceChildren(new Option('Elegir misión…',''));
    for(const m of getMissionList()) input.add(new Option(`${m.title} · ${m.selected_count} copas`,m.uuid));
    if([...input.options].some(o=>o.value===old))input.value=old;
  }
  function setMapVisible(){
    if(aiLayer)aiLayer.setVisible(group==='A');
    // Las marcas precampo se muestran en A y H, pero solo H puede vincularlas como hallazgos.
    // B permanece ciego para preservar el censo independiente.
    if(manualLayer)manualLayer.setVisible(group==='A'||group==='H');
    // Grupo B debe permanecer ciego a la IA; los parches HD de esta misión
    // fueron generados alrededor de copas seleccionadas, por lo que también
    // se ocultan en B para no revelar indirectamente los candidatos.
    if(hdLayer)hdLayer.setVisible(group!=='B');
    if(group!=='A')activeCandidate=null;
    if(group!=='H')activeManualTarget=null;
    const b=$('map-choice');if(b){
      b.textContent=group==='A'?(activeCandidate?`Copa elegida: ${activeCandidate}`:'Toca una copa IA · etiquetas violetas son marcas precampo')
        :(group==='B'?'Censo independiente: IA, marcas manuales y parches HD ocultos':group==='H'?(activeManualTarget?`Marca manual elegida: ${activeManualTarget.slice(0,8)} · registra Tree ID en campo`:'Grupo H: toca una marca violeta o registra un hallazgo nuevo'):'Detecciones IA ocultas');
    }
  }
  function accuracyGPS(pos){
    if(!map || !gpsSource)return;
    const coord=ol.proj.fromLonLat([pos.coords.longitude,pos.coords.latitude],activeMission.crs);
    if(!gpsFeature){gpsFeature=new ol.Feature(new ol.geom.Point(coord));gpsSource.addFeature(gpsFeature);}
    else gpsFeature.getGeometry().setCoordinates(coord);
    const acc=Number(pos.coords.accuracy);
    if(Number.isFinite(acc)&&acc>=0&&acc<5000){
      if(!accuracyFeature){accuracyFeature=new ol.Feature(new ol.geom.Circle(coord,acc));gpsSource.addFeature(accuracyFeature);}
      else accuracyFeature.setGeometry(new ol.geom.Circle(coord,acc));
    }
    const label=$('map-gps');if(label)label.textContent=`GPS ±${Math.round(acc)} m${acc>15?' · precisión limitada':''}`;
    if(gpsFollow)map.getView().animate({center:coord,duration:300});
  }
  function startGPS(){
    if(gpsWatch!==null||!navigator.geolocation||!map)return;
    gpsWatch=navigator.geolocation.watchPosition(pos=>{
      currentPosition=pos;accuracyGPS(pos);
    },err=>{const el=$('map-gps');if(el)el.textContent='GPS: '+err.message;
    },{enableHighAccuracy:true,maximumAge:3000,timeout:20000});
  }
  function stopGPS(){if(gpsWatch!==null&&navigator.geolocation){navigator.geolocation.clearWatch(gpsWatch);gpsWatch=null;}}
  function destroyMap(){
    hdCheckToken++;stopGPS();if(rasterUrl){URL.revokeObjectURL(rasterUrl);rasterUrl=null;}
    if(map){map.setTarget(null);map=null;}
    gpsFeature=null;accuracyFeature=null;geoSource=null;gpsSource=null;aiLayer=null;hdLayer=null;hdSource=null;
    manualSource=null;manualLayer=null;activeManualTarget=null;
  }
  function featureStyle(f){
    const id=String(f.get('operational_id')||'');
    const measured=records.some(r=>r.grupo==='A'&&r.payload?.mission_uuid===activeMission?.uuid&&r.payload?.candidate_id===id);
    const chosen=id===activeCandidate;
    const color=chosen?'#ffe082':measured?'#63d584':'#49deca';
    return new ol.style.Style({
      stroke:new ol.style.Stroke({color,width:chosen?4:2}),
      fill:new ol.style.Fill({color:chosen?'rgba(255,224,130,.26)':measured?'rgba(99,213,132,.16)':'rgba(73,222,202,.06)'})
    });
  }
  function usableCatalog(cat, entry){
    if(!cat||cat.schema!=='sigmad-campo-utm-tiles-v1'||cat.mission_uuid!==entry.uuid||
       cat.analysis_uuid!==entry.analysis_uuid||cat.crs!==entry.crs||!Array.isArray(cat.tiles)||
       !Array.isArray(cat.resolutions)||!cat.resolutions.length||
       cat.tile_size!==256||!cat.tile_set_id||!Array.isArray(cat.bounds)||cat.bounds.length!==4||
       !Array.isArray(cat.origin)||cat.origin.length!==2||cat.tiles.length>2000)return false;
    return true;
  }
  function addHdLayer(entry,layers){
    const cat=entry.tile_manifest;
    if(!usableCatalog(cat,entry))return false;
    const available=new Set(cat.tiles);
    const grid=new ol.tilegrid.TileGrid({
      extent:cat.bounds,origin:cat.origin,resolutions:cat.resolutions,tileSize:cat.tile_size,
    });
    hdSource=new ol.source.TileImage({
      projection:entry.crs,tileGrid:grid,interpolate:true,
      tileUrlFunction:coord=>{
        if(!coord)return undefined;
        const key=`${coord[0]}/${coord[1]}/${coord[2]}`;
        return available.has(key)?'sigmad-local:'+key:undefined;
      },
      tileLoadFunction:(tile,src)=>{
        const img=tile.getImage();
        const id=src.slice('sigmad-local:'.length);
        // Lee y, cuando procede, repara el WebP local antes de entregarlo a OpenLayers.
        repairStoredTile(entry,cat,id).then(result=>{
          if(!result.ok){img.src=EMPTY_TILE;return;}
          const url=URL.createObjectURL(result.blob);
          const cleanup=()=>URL.revokeObjectURL(url);
          img.addEventListener('load',cleanup,{once:true});
          img.addEventListener('error',cleanup,{once:true});
          img.src=url;
        }).catch(()=>{img.src=EMPTY_TILE;});
      },
    });
    hdLayer=new ol.layer.Tile({source:hdSource,opacity:1});
    layers.push(hdLayer);
    return true;
  }
  // Las marcas son una capa precampo INDEPENDIENTE de las predicciones originales.
  function loadManualFeatures(entry){
    if(!manualSource)return 0;
    manualSource.clear();
    const collection=entry.manual_targets;
    if(collection?.type==='FeatureCollection' && collection.crs===entry.crs && Array.isArray(collection.features)){
      try{
        const features=new ol.format.GeoJSON().readFeatures(collection,{dataProjection:entry.crs,featureProjection:entry.crs});
        manualSource.addFeatures(features.filter(f=>Boolean(f.get('manual_target_uuid'))));
      }catch(err){feedback('No se pudieron visualizar las etiquetas: '+err.message,true);}
    }
    return manualSource.getFeatures().length;
  }
  // Sincroniza SOLO etiquetas. No reemplaza el GeoJSON IA, los registros ni el raster local.
  async function refreshManualTargets(entry){
    if(!navigator.onLine||!window.SIGMAD_FIELD_BOOT||!ctx?.mission_url_template)return false;
    const url=ctx.mission_url_template.replace('__UUID__',encodeURIComponent(entry.uuid));
    const response=await fetch(url,{credentials:'same-origin',headers:{Accept:'application/json'},cache:'no-store'});
    if(!response.ok)throw new Error('HTTP '+response.status);
    const fresh=await response.json();
    if(fresh.uuid!==entry.uuid||fresh.analysis_uuid!==entry.analysis_uuid||fresh.crs!==entry.crs){
      throw new Error('El origen de la misión cambió. Pulsa «Descargar misión» para actualizarla.');
    }
    const collection=fresh.manual_targets;
    if(collection?.type!=='FeatureCollection'||collection.crs!==entry.crs||
        !Array.isArray(collection.features)||collection.features.length>200){
      throw new Error('El servidor no entregó etiquetas de misión compatibles.');
    }
    const local=await savedMission(entry.uuid);
    if(!local||!sameRaster(local,entry))return false;
    if(JSON.stringify(local.manual_targets)===JSON.stringify(collection)){
      entry.manual_targets=collection;
      return false;
    }
    local.manual_targets=collection;
    await storeMission(local);
    entry.manual_targets=collection;
    return true;
  }
  function showMission(entry,checkHd=true){
    destroyMap();activeMission=entry;activeCandidate=null;
    if($('candidate_id'))$('candidate_id').value='';
    group=$('grupo')?.value||'A';
    if(!window.ol||!window.proj4){feedback('Faltan las librerías OpenLayers/proj4 locales.',true);return;}
    if(!entry.bounds||!/^EPSG:(326|327)\d{2}$/.test(entry.crs)){feedback('La misión no tiene CRS UTM válido.',true);return;}
    const matched=/^EPSG:(326|327)(\d{2})$/.exec(entry.crs);
    proj4.defs(entry.crs,`+proj=utm +zone=${Number(matched[2])} ${matched[1]==='327'?'+south ':''}+datum=WGS84 +units=m +no_defs +type=crs`);
    ol.proj.proj4.register(proj4);
    const features=new ol.format.GeoJSON().readFeatures(entry.geojson,{dataProjection:'EPSG:4326',featureProjection:entry.crs});
    geoSource=new ol.source.Vector({features,wrapX:false});
    gpsSource=new ol.source.Vector({wrapX:false});
    manualSource=new ol.source.Vector({wrapX:false});
    loadManualFeatures(entry);
    const layers=[];
    if(entry.preview_blob?.size){
      rasterUrl=URL.createObjectURL(entry.preview_blob);
      layers.push(new ol.layer.Image({source:new ol.source.ImageStatic({url:rasterUrl,projection:entry.crs,imageExtent:entry.bounds})}));
    }
    const hd=addHdLayer(entry,layers);
    manualLayer=new ol.layer.Vector({source:manualSource,visible:group==='H',style:f=>new ol.style.Style({
      stroke:new ol.style.Stroke({color:'#c658e3',width:2.4,lineDash:[6,4]}),
      fill:new ol.style.Fill({color:'rgba(198,88,227,.16)'}),
      image:new ol.style.Circle({radius:9,fill:new ol.style.Fill({color:'#b758d7'}),stroke:new ol.style.Stroke({color:'#fff',width:2.2})}),
      text:new ol.style.Text({text:String(f.get('manual_code')||'MAN'),font:'bold 11px sans-serif',offsetY:f.getGeometry()?.getType()==='Point'?-19:0,
        fill:new ol.style.Fill({color:'#fff'}),backgroundFill:new ol.style.Fill({color:'rgba(89,31,108,.85)'}),padding:[3,4,3,4]})
    })});
    layers.push(manualLayer);
    aiLayer=new ol.layer.Vector({source:geoSource,style:featureStyle});layers.push(aiLayer);
    layers.push(new ol.layer.Vector({source:gpsSource,style:()=>new ol.style.Style({
      stroke:new ol.style.Stroke({color:'#60a5fa',width:1.8}),
      fill:new ol.style.Fill({color:'rgba(79,157,244,.20)'}),
      image:new ol.style.Circle({radius:8,fill:new ol.style.Fill({color:'#2d91ff'}),stroke:new ol.style.Stroke({color:'#fff',width:2})})
    })}));
    const opts={projection:entry.crs,center:ol.extent.getCenter(entry.bounds),zoom:0};
    if(hd)opts.resolutions=entry.tile_manifest.resolutions;
    map=new ol.Map({target:'campo-map',layers,view:new ol.View(opts),controls:ol.control.defaults.defaults({rotate:false})});
    map.getView().fit(entry.bounds,{size:map.getSize(),padding:[24,24,24,24]});
    map.on('singleclick',evt=>{
      if(group==='H') {
        const mf=map.forEachFeatureAtPixel(evt.pixel,f=>f.get('manual_target_uuid')?f:null,{hitTolerance:9,layerFilter:l=>l===manualLayer});
        if(!mf)return;
        activeManualTarget=String(mf.get('manual_target_uuid'));
        setMapVisible();manualSource.changed();
        $('tree-form').scrollIntoView({behavior:'smooth',block:'start'});
        feedback(`${mf.get('manual_code')||'MAN'} seleccionado como objetivo manual. Verifica el árbol y registra un Tree ID propio de campo.`);
        return;
      }
      if(group!=='A')return;
      const f=map.forEachFeatureAtPixel(evt.pixel,x=>x.get('operational_id')?x:null,{hitTolerance:8,layerFilter:l=>l===aiLayer});
      if(!f){
        const mf=map.forEachFeatureAtPixel(evt.pixel,x=>x.get('manual_target_uuid')?x:null,{hitTolerance:9,layerFilter:l=>l===manualLayer});
        if(mf)feedback(`Etiqueta ${mf.get('manual_code')||'MAN'}: marca precampo, no predicción IA. Verifica en campo si corresponde a una copa IA o a un hallazgo sin detección.`,true);
        return;
      }
      const id=String(f.get('operational_id'));
      activeCandidate=id;$('candidate_id').value=id;$('localizado').value='si';
      $('tree-form').scrollIntoView({behavior:'smooth',block:'start'});
      setMapVisible();geoSource.changed();
      feedback(`Copa ${id} elegida. Confirma el individuo físicamente antes de registrar.`);
    });
    map.on('pointerdrag',()=>{gpsFollow=false;});
    setMapVisible();startGPS();
    hdFeedback(hd?'Comprobando que las teselas locales sean utilizables…':'Sin paquete HD local. Descarga el mapa HD cuando esté disponible.');
    if(hd&&checkHd)void verifyCachedHD(entry,hdCheckToken);
    feedback(`${entry.title} · ${features.length} copas IA · ${manualSource.getFeatures().length} objetivos manuales · ${entry.preview_blob?'fondo validado':'SIN fondo preliminar'} · ${hd?'HD local en verificación':'sin HD local'}.`,!entry.preview_blob&&!hd);
  }
  async function selectMission(uuid){
    const token=++selectionToken;
    if(!uuid){destroyMap();activeMission=null;feedback('Selecciona una misión y descárgala antes de salir.');return;}
    const entry=await savedMission(uuid).catch(()=>null);
    if(token!==selectionToken||$('campo-mission')?.value!==uuid)return;
    if(entry && entry.user_id===ctx.user_id){
      try{await ensurePreview(entry);}catch(e){previewFeedback('No se pudo verificar el fondo: '+e.message,true);}
      if(token!==selectionToken||$('campo-mission')?.value!==uuid)return;
      showMission(entry);
      // Actualización automática de etiquetas al abrir, sin bajar otra vez el HD.
      // Si no hay internet, usamos la última colección descargada de la misión.
      void refreshManualTargets(entry).then(changed=>{
        if(!changed||token!==selectionToken||activeMission!==entry)return;
        const n=loadManualFeatures(entry);
        setMapVisible();
        feedback(`${entry.title} · ${n} etiquetas precampo sincronizadas · copas IA intactas.`);
      }).catch(err=>{
        if(token===selectionToken&&activeMission===entry){
          console.warn('SIGMAD: actualización de etiquetas pendiente:',err.message);
        }
      });
      return;
    }
    destroyMap();activeMission=null;feedback('Misión no descargada. Con internet pulsa «Descargar misión» antes de salir.',true);
  }
  async function downloadMission(){
    const id=$('campo-mission')?.value;
    if(!id){feedback('Selecciona una misión.',true);return;}
    if(!navigator.onLine||!window.SIGMAD_FIELD_BOOT){feedback('Necesitas conexión y sesión para descargar.',true);return;}
    const btn=$('map-download');btn.disabled=true;
    try{
      const url=ctx.mission_url_template.replace('__UUID__',encodeURIComponent(id));
      feedback('Descargando geometrías de la misión…');
      const res=await fetch(url,{credentials:'same-origin',headers:{Accept:'application/json'}});
      if(!res.ok)throw new Error('No se pudo descargar la misión (HTTP '+res.status+').');
      const entry=await res.json();
      if(entry.uuid!==id||!Array.isArray(entry.geojson?.features))throw new Error('Misión inválida.');
      const old=await savedMission(id).catch(()=>null);
      entry.user_id=ctx.user_id;
      // Conservar imágenes HD SOLO si el origen raster y los límites siguen siendo idénticos.
      const preserve=sameRaster(old,entry);
      entry.tile_manifest=preserve?old.tile_manifest:null;
      entry.hd_complete=false; // Se recalcula verificando todas las teselas existentes.
      entry.preview_blob=preserve?old.preview_blob:null;
      await storeMission(entry);
      await ensurePreview(entry);
      if($('campo-mission')?.value!==id)return;
      showMission(entry);
      feedback(entry.preview_blob?'Misión actualizada, fondo verificado. Valida el HD antes de ir a campo.':'Misión guardada SIN fondo cartográfico. Comprueba la conexión.',!entry.preview_blob);
    }catch(e){feedback('Error: '+e.message,true);}
    finally{btn.disabled=false;}
  }
  function tileUrl(cat,id){
    const [z,x,y]=id.split('/');
    return cat.tile_url_template.replace('__Z__',z).replace('__X__',x).replace('__Y__',y);
  }
  async function downloadHD(){
    const entry=activeMission;
    if(!entry){hdFeedback('Primero selecciona una misión descargada.',true);return;}
    if(!navigator.onLine||!window.SIGMAD_FIELD_BOOT){hdFeedback('Conéctate a internet para preparar el mapa HD.',true);return;}
    if(!entry.tile_manifest_url){hdFeedback('La misión no contiene el enlace de teselas. Actualiza el módulo Laravel.',true);return;}
    if(hdDownloading){hdFeedback('Ya se está preparando el mapa HD.');return;}
    hdDownloading=true;hdCheckToken++;
    const btn=$('map-download-hd');btn.disabled=true;
    try{
      hdFeedback('Consultando teselas disponibles…');
      const res=await fetch(entry.tile_manifest_url,{credentials:'same-origin',headers:{Accept:'application/json'},cache:'no-store'});
      if(res.status===404)throw new Error('Mapa HD no generado/subido para esta misión. Ejecuta el generador local y sube el resultado.');
      if(!res.ok)throw new Error('Error HTTP '+res.status+' al consultar mapa HD.');
      const cat=await res.json();
      if(!usableCatalog(cat,entry)||!cat.tile_url_template)throw new Error('El manifiesto HD no coincide con esta misión.');
      if(cat.tiles.length>850||cat.tile_bytes>120*1024*1024)throw new Error('Paquete demasiado grande para este piloto; genera una región menor.');
      const estimate=await navigator.storage?.estimate?.().catch(()=>null);
      if(estimate?.quota && estimate?.usage && estimate.quota-estimate.usage < cat.tile_bytes*1.15){
        throw new Error('Espacio local insuficiente para descargar el mapa.');
      }
      await navigator.storage?.persist?.().catch(()=>false);
      entry.tile_manifest=cat;entry.hd_complete=false;
      await storeMission(entry);
      // Reanudable. Inspecciona primero cada tesela almacenada, la repara si tiene
      // el byte 0A y solo solicita a internet las ausentes/irrecuperables.
      let cursor=0,complete=0,fresh=0,repaired=0,failed=[];
      const worker=async()=>{
        while(cursor<cat.tiles.length){
          const id=cat.tiles[cursor++];
          try{
            const key=tileKey(entry,cat,id);
            const previous=await repairStoredTile(entry,cat,id);
            if(previous.repaired)repaired++;
            if(!previous.ok){
              const checked=await fetchValidTile(cat,id);
              await putTile(key,checked.blob);
              fresh++;
              if(checked.repaired)repaired++;
            }
          }catch(err){failed.push(`${id}: ${err.message}`);}
          complete++;
          if(complete%10===0||complete===cat.tiles.length)
            hdFeedback(`Verificando HD: ${complete}/${cat.tiles.length} · nuevas ${fresh} · reparadas ${repaired} · errores ${failed.length}`);
        }
      };
      // Dos conexiones simultáneas para reducir problemas de red en móviles.
      await Promise.all(Array.from({length:Math.min(2,cat.tiles.length)},()=>worker()));
      entry.hd_complete=failed.length===0;
      await storeMission(entry);
      if(activeMission===entry && $('campo-mission')?.value===entry.uuid)showMission(entry,false);
      if(failed.length)hdFeedback(`HD incompleto: ${cat.tiles.length-failed.length}/${cat.tiles.length} válidas. Primera incidencia: ${failed[0]}. Puedes reanudar la descarga.`,true);
      else hdFeedback(`Mapa HD verificado: ${cat.tiles.length} teselas válidas (${fresh} nuevas, ${repaired} recuperadas). Prueba el modo avión antes de salir.`);
    }catch(e){hdFeedback('No se pudo preparar mapa HD: '+e.message,true);}
    finally{hdDownloading=false;btn.disabled=false;}
  }
  function initialize(context){
    ctx=context;if(!$('campo-map'))return;
    openDb().then(async db=>{
      mapDb=db;updateOptions();
      $('parcela')?.addEventListener('change',()=>{updateOptions();selectMission('');});
      $('campo-mission')?.addEventListener('change',e=>selectMission(e.target.value));
      $('map-download')?.addEventListener('click',downloadMission);
      $('map-download-hd')?.addEventListener('click',downloadHD);
      $('map-follow')?.addEventListener('click',()=>{
        gpsFollow=true;if(currentPosition)accuracyGPS(currentPosition);else feedback('Todavía no hay posición GPS.');
      });
      $('map-manual')?.addEventListener('click',()=>{
        $('grupo').value='H';$('grupo').dispatchEvent(new Event('change'));
        $('tree-form').scrollIntoView({behavior:'smooth',block:'start'});
        feedback('Hallazgo H: árbol sin propuesta IA. No es parte automática del censo B.');
      });
      if(!window.ol||!window.proj4){feedback('Faltan librerías locales OpenLayers/proj4.',true);return;}
      const available=getMissionList();
      if(available.length===1){$('campo-mission').value=available[0].uuid;await selectMission(available[0].uuid);}
      else feedback('Elige una misión. Descarga la misión y el mapa HD antes de salir.');
    }).catch(e=>feedback('No se pudo abrir almacenamiento local: '+e.message,true));
  }
  function association(){return {mission_uuid:activeMission?.uuid||null,
    detection_ref:(group==='A'&&activeCandidate&&activeMission)?`${activeMission.analysis_uuid}:${activeCandidate}`:null,
    manual_target_uuid:(group==='H'&&activeManualTarget&&activeMission)?activeManualTarget:null};}
  function updateGroup(next){group=next;setMapVisible();if(geoSource)geoSource.changed();}
  function setRecords(all){records=all||[];if(geoSource)geoSource.changed();}
  function onReset(){activeCandidate=null;activeManualTarget=null;setMapVisible();if(geoSource)geoSource.changed();}
  async function onEdit(rec){
    const uuid=rec.payload?.mission_uuid;if(!uuid)return;
    const select=$('campo-mission');if(select&&[...select.options].some(o=>o.value===uuid)){
      select.value=uuid;await selectMission(uuid);
      activeCandidate=rec.grupo==='A'?(rec.payload?.candidate_id||null):null;
      activeManualTarget=rec.grupo==='H'?(rec.payload?.manual_target_uuid||null):null;
      if($('candidate_id')&&activeCandidate)$('candidate_id').value=activeCandidate;
      setMapVisible();if(geoSource)geoSource.changed();
    }
  }
  window.SIGMAD_CAMPO_MAP={initialize,association,updateGroup,setRecords,onReset,onEdit};
})();
