/* SIGMAD Campo V0.2.1: mapa de misión + GPS + teselas HD privadas offline.
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
  const $ = id => document.getElementById(id);
  function feedback(msg, warn=false) {const el=$('map-feedback');if(el){el.textContent=msg;el.dataset.warn=warn?'yes':'no';}}
  function hdFeedback(msg, warn=false) {const el=$('map-hd-status');if(el){el.textContent=msg;el.dataset.warn=warn?'yes':'no';}}
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
  function getMissionList(){return (ctx?.missions||[]).filter(m=>String(m.id_parcela)===String($('parcela')?.value||''));}
  function updateOptions(){
    const input=$('campo-mission');if(!input)return;
    const old=input.value;input.replaceChildren(new Option('Elegir misión…',''));
    for(const m of getMissionList()) input.add(new Option(`${m.title} · ${m.selected_count} copas`,m.uuid));
    if([...input.options].some(o=>o.value===old))input.value=old;
  }
  function setMapVisible(){
    if(aiLayer)aiLayer.setVisible(group==='A');
    if(manualLayer)manualLayer.setVisible(group==='H');
    // Grupo B debe permanecer ciego a la IA; los parches HD de esta misión
    // fueron generados alrededor de copas seleccionadas, por lo que también
    // se ocultan en B para no revelar indirectamente los candidatos.
    if(hdLayer)hdLayer.setVisible(group!=='B');
    if(group!=='A')activeCandidate=null;
    if(group!=='H')activeManualTarget=null;
    const b=$('map-choice');if(b){
      b.textContent=group==='A'?(activeCandidate?`Copa elegida: ${activeCandidate}`:'Toca una copa para vincularla')
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
    stopGPS();if(rasterUrl){URL.revokeObjectURL(rasterUrl);rasterUrl=null;}
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
       cat.tile_size!==256||!cat.tile_set_id)return false;
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
        getTile(tileKey(entry,cat,id)).then(row=>{
          // No solicitamos la red en el render; sólo usamos las teselas descargadas.
          if(!row?.blob){img.src=EMPTY_TILE;return;}
          const url=URL.createObjectURL(row.blob);
          img.addEventListener('load',()=>URL.revokeObjectURL(url),{once:true});
          img.addEventListener('error',()=>URL.revokeObjectURL(url),{once:true});
          img.src=url;
        }).catch(()=>{img.src=EMPTY_TILE;});
      },
    });
    hdLayer=new ol.layer.Tile({source:hdSource,opacity:1});
    layers.push(hdLayer);
    return true;
  }
  function showMission(entry){
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
    const manualCollection=entry.manual_targets;
    if(manualCollection?.crs===entry.crs && Array.isArray(manualCollection.features)) {
      try {
        const manualFeatures=new ol.format.GeoJSON().readFeatures(manualCollection,{dataProjection:entry.crs,featureProjection:entry.crs});
        manualSource.addFeatures(manualFeatures.filter(f=>Boolean(f.get('manual_target_uuid'))));
      } catch(err) { feedback('Marcas manuales no compatibles: '+err.message,true); }
    }
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
      const f=map.forEachFeatureAtPixel(evt.pixel,x=>x.get('operational_id')?x:null,{hitTolerance:8});
      if(!f)return;
      const id=String(f.get('operational_id'));
      activeCandidate=id;$('candidate_id').value=id;$('localizado').value='si';
      $('tree-form').scrollIntoView({behavior:'smooth',block:'start'});
      setMapVisible();geoSource.changed();
      feedback(`Copa ${id} elegida. Confirma el individuo físicamente antes de registrar.`);
    });
    map.on('pointerdrag',()=>{gpsFollow=false;});
    setMapVisible();startGPS();
    hdFeedback(hd?(entry.hd_complete?'Mapa HD descargado: listo para modo avión.':'Mapa HD disponible parcialmente; descarga las teselas pendientes.')
                 :'Para mejorar los detalles, prepara las teselas HD de esta misión.');
    feedback(`${entry.title} · ${features.length} copas IA · ${manualSource.getFeatures().length} candidatos manuales · ${hd?'capa HD':'vista previa'} · datos locales.`);
  }
  async function selectMission(uuid){
    if(!uuid){destroyMap();activeMission=null;feedback('Selecciona una misión y descárgala antes de salir.');return;}
    const entry=await savedMission(uuid).catch(()=>null);
    if(entry && entry.user_id===ctx.user_id){showMission(entry);return;}
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
      // Conservar teselas HD descargadas de la misma versión de misión.
      entry.tile_manifest=old?.user_id===ctx.user_id?old.tile_manifest:null;
      entry.hd_complete=old?.user_id===ctx.user_id?Boolean(old.hd_complete):false;
      entry.preview_blob=old?.user_id===ctx.user_id?old.preview_blob:null;
      if(!entry.preview_blob && entry.preview_url){
        try{
          feedback('Descargando fondo cartográfico preliminar…');
          const img=await fetch(entry.preview_url,{credentials:'same-origin'});
          if(!img.ok)throw new Error(`HTTP ${img.status}`);
          const blob=await img.blob();
          if(blob.size>20*1024*1024||!blob.type.startsWith('image/'))throw new Error('Vista previa no apta.');
          entry.preview_blob=blob;
        }catch(e){feedback('Se descargaron las copas, pero NO el fondo preliminar: '+e.message,true);}
      }
      await storeMission(entry);showMission(entry);
      feedback(entry.preview_blob?'Misión guardada. Para ver copas nítidas, pulsa «Descargar mapa HD».':'Misión guardada SIN imagen base. Revisa el fondo antes de salir.',!entry.preview_blob);
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
      // La descarga es reanudable: solo descarga teselas que no están en IndexedDB.
      let cursor=0,complete=0,fresh=0,failed=[];
      const worker=async()=>{
        while(cursor<cat.tiles.length){
          const id=cat.tiles[cursor++];
          try{
            const key=tileKey(entry,cat,id);
            if(!(await getTile(key))){
              const response=await fetch(tileUrl(cat,id),{credentials:'same-origin',cache:'no-store'});
              if(!response.ok)throw new Error('HTTP '+response.status);
              const blob=await response.blob();
              if(!blob.size||blob.size>500_000||!blob.type.startsWith('image/webp'))throw new Error('Tesela inválida');
              await putTile(key,blob);fresh++;
            }
          }catch(err){failed.push(`${id}: ${err.message}`);}
          complete++;
          if(complete%10===0||complete===cat.tiles.length)
            hdFeedback(`Mapa HD: ${complete}/${cat.tiles.length} teselas comprobadas · ${fresh} descargadas · ${failed.length} fallidas.`);
        }
      };
      await Promise.all(Array.from({length:Math.min(4,cat.tiles.length)},()=>worker()));
      entry.hd_complete=failed.length===0;
      await storeMission(entry);
      showMission(entry);
      if(failed.length)hdFeedback(`Mapa HD incompleto: ${failed.length} teselas fallaron. Repite «Descargar mapa HD» con conexión; no salgas aún.`,true);
      else hdFeedback(`Mapa HD LISTO: ${cat.tiles.length} teselas y ${(cat.tile_bytes/1048576).toFixed(1)} MB · apto para prueba en modo avión.`);
    }catch(e){hdFeedback('No se pudo preparar mapa HD: '+e.message,true);}
    finally{btn.disabled=false;}
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
