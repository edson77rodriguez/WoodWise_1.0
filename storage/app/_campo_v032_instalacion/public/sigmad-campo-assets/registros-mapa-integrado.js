/* SIGMAD Campo V0.3.2 | Mapa científico administrativo (online).
 * Sin matching automático ni alteraciones sobre observaciones o predicciones.
 * Copas: SOLO las asignadas a la misión escogida, no todo el vuelo.
 */
(() => {
  'use strict';
  const root=document.getElementById('cr-record-map');
  const boot=window.SIGMAD_FIELD_RECORD_MAP;
  if(!root||!boot)return;
  const $=id=>document.getElementById(id);
  const status=$('cr-map-status'), count=$('cr-map-count'), detail=$('cr-selected-record');
  const select=$('cr-mission-select');
  const missionButton=$('cr-fit-mission'), gpsButton=$('cr-fit-gps');
  const checkImage=$('cr-layer-image'), checkAi=$('cr-layer-ai');
  const checkGps=$('cr-layer-gps'), checkAccuracy=$('cr-layer-accuracy');
  const note=$('cr-map-note');
  if(!window.ol||!window.proj4){
    if(status)status.textContent='Faltan las librerías locales de OpenLayers/proj4.';
    return;
  }
  const ol=window.ol;
  const color={validado:'#15845a',en_revision:'#266caa',observado:'#d48b28',pendiente:'#617b71'};
  const markerStyle={};
  let map=null, mission=null, missionExtent=null, currentProjection='EPSG:3857';
  let geoRows=[], allFeatures=[], fieldSource=null, crownSource=null, accuracySource=null;
  let base=null, preview=null, hd=null, crownLayer=null, gpsLayer=null, accuracyLayer=null;
  let missionLoad=0;
  const setStatus=(message,error=false)=>{if(status){status.textContent=message;status.dataset.error=error?'1':'0';}};
  const finite=n=>Number.isFinite(Number(n));
  const signedCoord=(lon,lat)=>lon!==null&&lon!==undefined&&lat!==null&&lat!==undefined&&
    lon!==''&&lat!==''&&finite(lon)&&finite(lat)&&Number(lat)>=-90&&Number(lat)<=90&&Number(lon)>=-180&&Number(lon)<=180;
  const fetchJSON=async url=>{
    const resp=await fetch(url,{credentials:'same-origin',headers:{Accept:'application/json'},cache:'no-store'});
    if(!resp.ok)throw new Error('HTTP '+resp.status);
    return resp.json();
  };
  const clearDetail=()=>{if(detail){detail.hidden=true;detail.replaceChildren();}};
  const addText=(parent,tag,value,className)=>{
    const node=document.createElement(tag);node.textContent=String(value);
    if(className)node.className=className;parent.append(node);return node;
  };
  const pointStyle=feature=>{
    const key=feature.get('status')||'pendiente';
    if(!markerStyle[key])markerStyle[key]=new ol.style.Style({image:new ol.style.Circle({
      radius:9,fill:new ol.style.Fill({color:color[key]||color.pendiente}),
      stroke:new ol.style.Stroke({color:'#fff',width:2.6})
    })});
    return markerStyle[key];
  };
  const crownStyle=new ol.style.Style({
    stroke:new ol.style.Stroke({color:'#00a68c',width:2.3}),
    fill:new ol.style.Fill({color:'rgba(0,166,140,.11)'})
  });
  const accuracyStyle=new ol.style.Style({
    stroke:new ol.style.Stroke({color:'rgba(34,114,205,.7)',width:1.3,lineDash:[4,4]}),
    fill:new ol.style.Fill({color:'rgba(35,117,216,.08)'})
  });

  function projection(crs){
    const match=/^EPSG:(326|327)(\d{2})$/.exec(crs||'');
    if(!match)return false;
    const zone=Number(match[2]);
    if(zone<1||zone>60)return false;
    proj4.defs(crs,`+proj=utm +zone=${zone} ${match[1]==='327'?'+south ':''}+datum=WGS84 +units=m +no_defs +type=crs`);
    ol.proj.proj4.register(proj4);
    return true;
  }
  const layersVisibility=()=>{
    if(preview)preview.setVisible(!!checkImage?.checked);
    if(hd)hd.setVisible(!!checkImage?.checked);
    if(crownLayer)crownLayer.setVisible(!!checkAi?.checked);
    if(gpsLayer)gpsLayer.setVisible(!!checkGps?.checked);
    if(accuracyLayer)accuracyLayer.setVisible(!!checkGps?.checked&&!!checkAccuracy?.checked);
  };
  const coordFromRow=row=>ol.proj.transform([Number(row.lon),Number(row.lat)],'EPSG:4326',currentProjection);
  function drawFieldRecords(){
    fieldSource.clear();accuracySource.clear();
    const records=[];
    for(const item of geoRows){
      if(!signedCoord(item.lon,item.lat))continue;
      let point;
      try{point=coordFromRow(item);}catch(_){continue;}
      if(!point.every(Number.isFinite))continue;
      const f=new ol.Feature({geometry:new ol.geom.Point(point)});
      f.setProperties({kind:'campo',id:item.id,label:item.label,status:item.status,grupo:item.grupo,
        url:item.url,accuracy_m:item.accuracy_m,lon:Number(item.lon),lat:Number(item.lat)});
      records.push(f);
      const precision=Number(item.accuracy_m);
      if(item.accuracy_m!==null&&item.accuracy_m!==undefined&&Number.isFinite(precision)&&precision>0&&precision<=1000){
        // UTM: metros aproximadamente. EPSG:3857 requiere compensar distorsión Mercator.
        const radius=currentProjection==='EPSG:3857'?precision/Math.max(0.01,Math.cos(Number(item.lat)*Math.PI/180)):precision;
        const c=new ol.Feature({geometry:new ol.geom.Circle(point,radius)});c.set('kind','precision');accuracySource.addFeature(c);
      }
    }
    fieldSource.addFeatures(records);allFeatures=records;
    if(count)count.textContent=`${records.length} puntos de campo · ${crownSource.getFeatures().length} copas IA asignadas`;
  }
  function createMap(crs='EPSG:3857',bounds=null){
    if(map)map.setTarget(null);
    currentProjection=crs;
    clearDetail();
    fieldSource=new ol.source.Vector({wrapX:false});crownSource=new ol.source.Vector({wrapX:false});
    accuracySource=new ol.source.Vector({wrapX:false});
    base=new ol.layer.Tile({source:new ol.source.OSM({wrapX:false})});
    preview=null;hd=null;
    crownLayer=new ol.layer.Vector({source:crownSource,style:crownStyle});
    gpsLayer=new ol.layer.Vector({source:fieldSource,style:pointStyle});
    accuracyLayer=new ol.layer.Vector({source:accuracySource,style:accuracyStyle});
    map=new ol.Map({target:root,layers:[base,accuracyLayer,crownLayer,gpsLayer],
      view:new ol.View({projection:crs,center:bounds?ol.extent.getCenter(bounds):ol.proj.fromLonLat([0,0]),zoom:bounds?9:2})});
    map.on('singleclick',evt=>{
      const hit=map.forEachFeatureAtPixel(evt.pixel,f=>{
        const kind=f.get('kind');return kind==='campo'||kind==='copa'?f:null;
      },{hitTolerance:8});
      if(!hit||!detail)return;
      detail.replaceChildren();
      if(hit.get('kind')==='campo'){
        addText(detail,'div','Medición de campo','cr-map-details-kind');
        addText(detail,'strong',hit.get('label')||'Árbol sin ID');
        addText(detail,'div',`Grupo ${hit.get('grupo')} · Revisión: ${hit.get('status')||'pendiente'}`);
        const a=hit.get('accuracy_m');
        if(a!==null&&a!==undefined&&finite(a))addText(detail,'div',`Precisión GNSS reportada: ±${Number(a).toFixed(1)} m`);
        const link=document.createElement('a');link.textContent='Abrir ficha de campo';link.href=hit.get('url');detail.append(link);
      }else{
        addText(detail,'div','Propuesta de segmentación IA · misión','cr-map-details-kind');
        addText(detail,'strong',hit.get('operational_id')||'Copa sin ID');
        addText(detail,'div','Modelo de origen: '+(hit.get('primary_model')||'No informado'));
        if(hit.get('requires_review'))addText(detail,'div','Marcada para revisión por el análisis.');
        addText(detail,'div','La coincidencia con un fuste no está validada; no implica TP, FP ni FN.');
      }
      detail.hidden=false;
    });
    drawFieldRecords();layersVisibility();
    if(bounds)map.getView().fit(bounds,{padding:[42,42,42,42],maxZoom:18,duration:0});
    else fitGPS(false);
    map.updateSize();
  }
  function fitGPS(showMessage=true){
    if(!map||!fieldSource)return;
    const features=fieldSource.getFeatures();
    if(!features.length){if(showMessage)setStatus('No hay GPS en los registros filtrados.',true);return;}
    if(features.length===1){map.getView().setCenter(features[0].getGeometry().getCoordinates());
      map.getView().setZoom(currentProjection==='EPSG:3857'?18:19);
    }else map.getView().fit(fieldSource.getExtent(),{padding:[60,60,60,60],maxZoom:18,duration:0});
    if(showMessage)setStatus(`Vista centrada en ${features.length} observaciones GPS. No supone coincidencia con copas IA.`);
  }
  function fitMission(){
    if(!map||!missionExtent){setStatus('Selecciona una misión para enfocar su ortomosaico.',true);return;}
    map.getView().fit(missionExtent,{padding:[40,40,40,40],maxZoom:21,duration:0});
    setStatus('Vista centrada en la extensión de la misión seleccionada.');
  }
  function addMissionImage(data){
    const img=new ol.layer.Image({source:new ol.source.ImageStatic({
      url:data.preview_url,projection:data.crs,imageExtent:data.bounds
    })});
    map.getLayers().insertAt(1,img);preview=img;layersVisibility();
  }
  async function addMissionHd(data,sequence){
    if(!data.tile_manifest_url)return;
    try{
      const cat=await fetchJSON(data.tile_manifest_url);
      if(sequence!==missionLoad)return;
      if(cat.schema!=='sigmad-campo-utm-tiles-v1'||cat.mission_uuid!==data.uuid||
         cat.crs!==data.crs||cat.analysis_uuid!==data.analysis_uuid||
         cat.tile_size!==256||!Array.isArray(cat.bounds)||!Array.isArray(cat.origin)||
         !Array.isArray(cat.resolutions)||!cat.resolutions.length||!Array.isArray(cat.tiles)||
         !cat.tile_url_template)return;
      const allowed=new Set(cat.tiles);
      const grid=new ol.tilegrid.TileGrid({extent:cat.bounds,origin:cat.origin,resolutions:cat.resolutions,tileSize:cat.tile_size});
      const tileSource=new ol.source.TileImage({projection:data.crs,tileGrid:grid,wrapX:false,
        tileUrlFunction:coord=>{
          if(!coord)return undefined;
          const id=`${coord[0]}/${coord[1]}/${coord[2]}`;
          return allowed.has(id)?cat.tile_url_template.replace('__Z__',coord[0]).replace('__X__',coord[1]).replace('__Y__',coord[2]):undefined;
        }
      });
      hd=new ol.layer.Tile({source:tileSource});
      // Encima de la vista previa, debajo de las capas vectoriales.
      map.getLayers().insertAt(preview?2:1,hd);layersVisibility();
      setStatus(`Misión cargada · ${crownSource.getFeatures().length} copas asignadas · HD de ${cat.tiles.length} teselas disponible online.`);
    }catch(err){
      if(sequence!==missionLoad)return;
      if(!String(err.message).includes('404'))setStatus(`Misión cargada; mapa HD no disponible (${err.message}). Se utiliza la vista previa.`);
    }
  }
  async function selectMission(uuid){
    const seq=++missionLoad;
    mission=null;missionExtent=null;
    if(!uuid){createMap();setStatus('Elige una misión para superponer el ortomosaico y las copas IA.');return;}
    const row=(window.__SIGMAD_MISSIONS||[]).find(m=>m.uuid===uuid);
    if(!row){setStatus('Misión no disponible en el catálogo actual.',true);return;}
    setStatus('Cargando misión y geometrías de copas…');
    try{
      const data=await fetchJSON(row.data_url);
      if(seq!==missionLoad)return;
      if(data.uuid!==uuid||!projection(data.crs)||!Array.isArray(data.bounds)||data.bounds.length!==4||
         !Array.isArray(data.geojson?.features))throw new Error('Datos o proyección de la misión incompatibles.');
      if(data.geojson.features.length>500)throw new Error('Misión demasiado grande para esta vista (máximo 500 copas).');
      mission=data;missionExtent=data.bounds.map(Number);
      createMap(data.crs,missionExtent);
      if(data.preview_url)addMissionImage(data);
      const features=new ol.format.GeoJSON().readFeatures(data.geojson,{
        dataProjection:'EPSG:4326',featureProjection:data.crs
      });
      for(const f of features)f.set('kind','copa');
      crownSource.addFeatures(features);layersVisibility();drawFieldRecords();
      fitMission();
      const outside=fieldSource.getFeatures().filter(f=>!ol.extent.containsCoordinate(missionExtent,f.getGeometry().getCoordinates())).length;
      setStatus(`${features.length} copas asignadas · ${fieldSource.getFeatures().length} GPS · ${outside} puntos fuera del área de la misión.`);
      if(outside>0&&note)note.textContent='Hay observaciones fuera de los límites de la misión (p. ej., pruebas realizadas en otro lugar). No son falsos negativos automáticamente.';
      else if(note)note.textContent='GPS del dispositivo ≠ posición exacta del fuste. Las copas IA son propuestas separadas; no se realiza matching automático.';
      addMissionHd(data,seq);
    }catch(e){
      if(seq!==missionLoad)return;
      createMap();setStatus('No se pudo abrir la misión: '+e.message,true);
    }
  }
  async function initialize(){
    createMap();
    for(const element of [checkImage,checkAi,checkGps,checkAccuracy])element?.addEventListener('change',layersVisibility);
    missionButton?.addEventListener('click',fitMission);
    gpsButton?.addEventListener('click',()=>fitGPS(true));
    select?.addEventListener('change',e=>selectMission(e.target.value));
    try{
      const [fieldResult,catalogResult]=await Promise.allSettled([fetchJSON(boot.url),fetchJSON(boot.missions_url)]);
      if(fieldResult.status==='fulfilled'){
        geoRows=Array.isArray(fieldResult.value.points)?fieldResult.value.points:[];
      }else{
        geoRows=[];
        setStatus('No fue posible cargar posiciones GPS: '+fieldResult.reason.message,true);
      }
      if(catalogResult.status==='fulfilled'){
        window.__SIGMAD_MISSIONS=Array.isArray(catalogResult.value.missions)?catalogResult.value.missions:[];
      }else{
        window.__SIGMAD_MISSIONS=[];
        setStatus('No fue posible cargar las misiones: '+catalogResult.reason.message+'; conserva la vista GPS.',true);
      }
      const currentParcela=String(boot.parcela_id||'');
      const avail=window.__SIGMAD_MISSIONS.filter(m=>!currentParcela||String(m.parcela_id)===currentParcela);
      for(const m of avail){
        const opt=new Option(`${m.title} · ${m.selected_count} copas · Parcela ${m.parcela_id}`,m.uuid);
        select?.add(opt);
      }
      createMap();
      const errors=[fieldResult,catalogResult].filter(r=>r.status==='rejected').length;
      if(!errors)setStatus(`${geoRows.length} registros georreferenciados. Selecciona una misión para ver copas UAV.`);
      else setStatus(`Datos cargados parcialmente: ${geoRows.length} GPS, ${avail.length} misiones. Comprueba las rutas de SIGMAD.`,true);
      if(fieldResult.status==='fulfilled'&&geoRows.length>=Number(fieldResult.value.sample_limit||500)){
        if(note)note.textContent='Se muestran como máximo los 500 registros GPS más recientes de los filtros; restringe parcela o fechas para completar la revisión.';
      }
      if(avail.length===1&&select){select.value=avail[0].uuid;await selectMission(avail[0].uuid);}
    }catch(err){
      setStatus('Error al cargar datos del panel: '+err.message,true);
    }
  }
  initialize();
})();
