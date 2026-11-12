/* SIGMAD Campo V0.2: mapa de misiones, GPS en vivo y selección de árboles.
   Los datos del servidor se descargan sólo tras acción explícita del usuario.
   No convierte predicciones IA en verdad de campo. */
(() => {
  'use strict';
  const DB_NAME = 'sigmad-campo-misiones-v02';
  const STORE = 'missions';
  let ctx = null, mapDb=null, map=null, mapReady=false, rasterUrl=null, geoSource=null;
  let gpsSource=null, aiLayer=null, gpsFeature=null, accuracyFeature=null;
  let activeMission=null, activeCandidate=null, records=[], group='A';
  let gpsWatch=null, gpsFollow=true, currentPosition=null;
  const $ = id => document.getElementById(id);
  function feedback(msg, warn=false) {const el=$('map-feedback');if(el){el.textContent=msg;el.dataset.warn=warn?'yes':'no';}}
  function openDb(){return new Promise((resolve,reject)=>{
    const req=indexedDB.open(DB_NAME,1);
    req.onupgradeneeded=()=>{const db=req.result;if(!db.objectStoreNames.contains(STORE))db.createObjectStore(STORE,{keyPath:'uuid'});};
    req.onsuccess=()=>resolve(req.result);req.onerror=()=>reject(req.error);
  });}
  function requestPromise(req){return new Promise((resolve,reject)=>{req.onsuccess=()=>resolve(req.result);req.onerror=()=>reject(req.error);});}
  function savedMission(uuid){return requestPromise(mapDb.transaction(STORE).objectStore(STORE).get(uuid));}
  function storeMission(entry){return requestPromise(mapDb.transaction(STORE,'readwrite').objectStore(STORE).put(entry));}
  function getMissionList(){return (ctx?.missions||[]).filter(m=>String(m.id_parcela)===String($('parcela')?.value||''));}
  function updateOptions(){
    const input=$('campo-mission');if(!input)return;
    const old=input.value;input.replaceChildren(new Option('Elegir misión…',''));
    for(const m of getMissionList()) input.add(new Option(`${m.title} · ${m.selected_count} copas`,m.uuid));
    if([...input.options].some(o=>o.value===old))input.value=old;
  }
  function setMapVisible(){if(aiLayer)aiLayer.setVisible(group==='A');if(group!=='A')activeCandidate=null;
    const b=$('map-choice');if(b){b.textContent=group==='A'?(activeCandidate?`Copa elegida: ${activeCandidate}`:'Toca una copa para vincularla'):'Censo independiente: detecciones IA ocultas';}
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
    const gpsLabel=$('map-gps');if(gpsLabel)gpsLabel.textContent=`GPS ±${Math.round(acc)} m${acc>15?' · precisión limitada':''}`;
    if(gpsFollow)map.getView().animate({center:coord,duration:300});
  }
  function startGPS(){
    if(gpsWatch!==null||!navigator.geolocation||!map)return;
    gpsWatch=navigator.geolocation.watchPosition(pos=>{
      currentPosition=pos;
      accuracyGPS(pos);
    },err=>{const el=$('map-gps');if(el)el.textContent='GPS: '+err.message;
    },{enableHighAccuracy:true,maximumAge:3000,timeout:20000});
  }
  function stopGPS(){if(gpsWatch!==null&&navigator.geolocation){navigator.geolocation.clearWatch(gpsWatch);gpsWatch=null;}}
  function destroyMap(){stopGPS();if(rasterUrl){URL.revokeObjectURL(rasterUrl);rasterUrl=null;}
    if(map){map.setTarget(null);map=null;}gpsFeature=null;accuracyFeature=null;geoSource=null;gpsSource=null;aiLayer=null;mapReady=false;
  }
  function featureStyle(f){
    const id=String(f.get('operational_id')||'');
    const measured=records.some(r=>r.grupo==='A'&&r.payload?.mission_uuid===activeMission?.uuid&&r.payload?.candidate_id===id);
    const isSelected=id===activeCandidate;
    const color=isSelected?'#ffe082':measured?'#63d584':'#49deca';
    return new ol.style.Style({
      stroke:new ol.style.Stroke({color,width:isSelected?4:2}),
      fill:new ol.style.Fill({color:isSelected?'rgba(255,224,130,.26)':measured?'rgba(99,213,132,.16)':'rgba(73,222,202,.06)'})
    });
  }
  function showMission(entry){
    destroyMap();activeMission=entry;activeCandidate=null;
    if ($('candidate_id')) $('candidate_id').value = '';
    group=$('grupo')?.value||'A';
    if(!window.ol||!window.proj4){feedback('Faltan librerías de mapa en este dispositivo. Instala OpenLayers/proj4 locales.',true);return;}
    if(!entry.bounds||!/^EPSG:(326|327)\d{2}$/.test(entry.crs)){feedback('La misión no tiene CRS UTM válido.',true);return;}
    const matched=/^EPSG:(326|327)(\d{2})$/.exec(entry.crs);
    proj4.defs(entry.crs,`+proj=utm +zone=${Number(matched[2])} ${matched[1]==='327'?'+south ':''}+datum=WGS84 +units=m +no_defs +type=crs`);
    ol.proj.proj4.register(proj4);
    const format=new ol.format.GeoJSON();
    const features=format.readFeatures(entry.geojson,{dataProjection:'EPSG:4326',featureProjection:entry.crs});
    geoSource=new ol.source.Vector({features,wrapX:false});
    gpsSource=new ol.source.Vector({wrapX:false});
    const layers=[];
    if(entry.preview_blob && entry.preview_blob.size){
      rasterUrl=URL.createObjectURL(entry.preview_blob);
      layers.push(new ol.layer.Image({source:new ol.source.ImageStatic({url:rasterUrl,projection:entry.crs,imageExtent:entry.bounds})}));
    }
    aiLayer=new ol.layer.Vector({source:geoSource,style:featureStyle});layers.push(aiLayer);
    layers.push(new ol.layer.Vector({source:gpsSource,style:f=>new ol.style.Style({
      stroke:new ol.style.Stroke({color:'#60a5fa',width:1.8}),
      fill:new ol.style.Fill({color:'rgba(79,157,244,.20)'}),
      image:new ol.style.Circle({radius:8,fill:new ol.style.Fill({color:'#2d91ff'}),stroke:new ol.style.Stroke({color:'#fff',width:2})})
    })}));
    map=new ol.Map({target:'campo-map',layers,view:new ol.View({projection:entry.crs,center:ol.extent.getCenter(entry.bounds),zoom:3}),controls:ol.control.defaults.defaults({rotate:false})});
    map.getView().fit(entry.bounds,{size:map.getSize(),padding:[24,24,24,24],maxZoom:20});
    map.on('singleclick',evt=>{
      if(group!=='A')return;
      const f=map.forEachFeatureAtPixel(evt.pixel,x=>x.get('operational_id')?x:null,{hitTolerance:8});
      if(!f)return;
      const id=String(f.get('operational_id'));
      activeCandidate=id;
      $('candidate_id').value=id;
      $('localizado').value='si';
      $('tree-form').scrollIntoView({behavior:'smooth',block:'start'});
      setMapVisible();geoSource.changed();
      feedback(`Copa ${id} elegida. Comprueba el individuo físicamente antes de registrar.`);
    });
    map.on('pointerdrag',()=>{gpsFollow=false;});
    mapReady=true;setMapVisible();startGPS();
    feedback(`${entry.title} · ${features.length} copas · ${entry.preview_blob?'ortomosaico descargado':'sin imagen de fondo'} · datos locales.`);
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
    if(!navigator.onLine || !window.SIGMAD_FIELD_BOOT){feedback('Necesitas conexión y una sesión iniciada para descargar.',true);return;}
    const btn=$('map-download');btn.disabled=true;
    try{
      const url=ctx.mission_url_template.replace('__UUID__',encodeURIComponent(id));
      feedback('Descargando geometrías de la misión…');
      const res=await fetch(url,{credentials:'same-origin',headers:{Accept:'application/json'}});
      if(!res.ok)throw new Error('No se pudo descargar misión (HTTP '+res.status+').');
      const entry=await res.json();
      if(entry.uuid!==id||!entry.geojson||!Array.isArray(entry.geojson.features))throw new Error('Misión inválida.');
      entry.user_id=ctx.user_id;entry.preview_blob=null;
      if(entry.preview_url){
        try{
          feedback('Descargando fondo cartográfico…');
          const img=await fetch(entry.preview_url,{credentials:'same-origin'});
          if(!img.ok)throw new Error(`HTTP ${img.status}`);
          const blob=await img.blob();
          if(blob.size>20*1024*1024||!blob.type.startsWith('image/'))throw new Error('Imagen no apta para uso offline.');
          entry.preview_blob=blob;
        }catch(e){feedback('Se descargaron las copas, pero NO el ortomosaico: '+e.message,true);}
      }
      await storeMission(entry);
      showMission(entry);
      if(!entry.preview_blob)feedback('Copas guardadas localmente SIN imagen de fondo. Revisa la vista previa antes de salir.',true);
      else feedback(`Misión guardada sin conexión: ${entry.geojson.features.length} copas + imagen de fondo.`);
    }catch(e){feedback('Error: '+e.message,true);}
    finally{btn.disabled=false;}
  }
  function initialize(context){
    ctx=context;
    if(!$('campo-map'))return;
    openDb().then(async db=>{
      mapDb=db;updateOptions();
      $('parcela')?.addEventListener('change',()=>{updateOptions();selectMission('');});
      $('campo-mission')?.addEventListener('change',e=>selectMission(e.target.value));
      $('map-download')?.addEventListener('click',downloadMission);
      $('map-follow')?.addEventListener('click',()=>{
        gpsFollow=true;
        if(currentPosition)accuracyGPS(currentPosition);else feedback('Todavía no hay posición GPS.');
      });
      $('map-manual')?.addEventListener('click',()=>{
        $('grupo').value='H';$('grupo').dispatchEvent(new Event('change'));
        $('tree-form').scrollIntoView({behavior:'smooth',block:'start'});
        feedback('Hallazgo H: árbol sin propuesta IA. No se incorpora al censo independiente B; escribe el Tree ID.');
      });
      if(!window.ol || !window.proj4){feedback('Faltan las librerías de mapa locales. Revisa vendor/ol.js y vendor/proj4.js.',true);return;}
      const available=getMissionList();
      if(available.length===1){$('campo-mission').value=available[0].uuid;await selectMission(available[0].uuid);}
      else feedback('Elige una misión. La descarga explícita permite usarla sin conexión.');
    }).catch(e=>feedback('No se pudo abrir el almacenamiento de misiones: '+e.message,true));
  }
  function association(){
    return {mission_uuid:activeMission?.uuid||null,
      detection_ref:(group==='A'&&activeCandidate&&activeMission)
        ? `${activeMission.analysis_uuid}:${activeCandidate}` : null};
  }
  function updateGroup(next){group=next;setMapVisible();if(geoSource)geoSource.changed();}
  function setRecords(all){records=all||[];if(geoSource)geoSource.changed();}
  function onReset(){activeCandidate=null;setMapVisible();if(geoSource)geoSource.changed();}
  async function onEdit(rec){
    const uuid=rec.payload?.mission_uuid;
    if(!uuid)return;
    const select=$('campo-mission');if(select && [...select.options].some(o=>o.value===uuid)){
      select.value=uuid;await selectMission(uuid);
      activeCandidate=rec.grupo==='A'?(rec.payload?.candidate_id||null):null;
      if ($('candidate_id') && activeCandidate) $('candidate_id').value = activeCandidate;
      setMapVisible();if(geoSource)geoSource.changed();
    }
  }
  window.SIGMAD_CAMPO_MAP={initialize,association,updateGroup,setRecords,onReset,onEdit};
})();
