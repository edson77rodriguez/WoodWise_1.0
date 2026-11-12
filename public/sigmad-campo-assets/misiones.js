(() => {
  'use strict';
  const jobs = window.SIGMAD_MISSIONS_BOOT || [];
  const $ = id => document.getElementById(id);
  const sel = $('mission-analysis'), status = $('mission-status');
  const mapBox = $('mission-map');
  if (!window.ol || !window.proj4) { status.textContent = 'Faltan las librerías locales de OpenLayers/proj4. Revisa la instalación de vendor.'; return; }
  let map, source, imageLayer;
  let candidates = new Map(), selected = new Set(), token = 0;
  const style = feature => {
    const id = String(feature.get('operational_id') || '');
    const active = selected.has(id);
    return new ol.style.Style({
      stroke: new ol.style.Stroke({color: active?'#ffbf45':'#48e5af',width: active?3.1:1.7}),
      fill: new ol.style.Fill({color: active?'rgba(255,191,69,.24)':'rgba(72,229,175,.065)'})
    });
  };
  function renderSelected() {
    const a = [...selected];
    $('selected-count').textContent = String(a.length);
    $('selected-list').replaceChildren(); $('selected-inputs').replaceChildren();
    for (const id of a) {
      const span=document.createElement('span'); span.textContent=id; $('selected-list').append(span);
      const input=document.createElement('input');input.type='hidden';input.name='selected_ids[]';input.value=id;$('selected-inputs').append(input);
    }
    source?.changed();
  }
  async function load() {
    const run=++token;
    selected.clear();renderSelected();candidates.clear();
    if (map) {map.setTarget(null);map=null;source=null;imageLayer=null;}
    const job=jobs.find(x=>x.uuid===sel.value);if (!job) {status.textContent='Selecciona un análisis.';return;}
    if (!job.bounds || !/^EPSG:(326|327)\d{2}$/.test(job.crs)) {status.textContent='El análisis no tiene proyección UTM válida.';return;}
    status.textContent='Cargando copas del análisis…';
    const match=/^EPSG:(326|327)(\d{2})$/.exec(job.crs);
    proj4.defs(job.crs,`+proj=utm +zone=${Number(match[2])} ${match[1]==='327'?'+south ':''}+datum=WGS84 +units=m +no_defs +type=crs`);
    ol.proj.proj4.register(proj4);
    source = new ol.source.Vector({wrapX:false});
    const layers=[];
    if(job.preview_url){imageLayer=new ol.layer.Image({source:new ol.source.ImageStatic({url:job.preview_url,imageExtent:job.bounds,projection:job.crs})});layers.push(imageLayer);}
    layers.push(new ol.layer.Vector({source,style}));
    map=new ol.Map({target:mapBox,layers,view:new ol.View({projection:job.crs,center:ol.extent.getCenter(job.bounds),zoom:2}),controls:ol.control.defaults.defaults({rotate:false})});
    map.getView().fit(job.bounds,{size:map.getSize(),padding:[35,35,35,35],maxZoom:19});
    map.on('singleclick',evt=>{
      const f=map.forEachFeatureAtPixel(evt.pixel, x=>x,{hitTolerance:6});
      if(!f)return;
      const id=String(f.get('operational_id')||'');if(!id)return;
      if(selected.has(id))selected.delete(id);
      else {if(selected.size>=200){status.textContent='Máximo 200 copas por misión.';return;}selected.add(id);}
      renderSelected();
    });
    try{
      const res=await fetch(job.geojson_url,{credentials:'same-origin',headers:{Accept:'application/geo+json'}});
      if(!res.ok)throw new Error(`HTTP ${res.status}`);
      const json=await res.json();if(run!==token)return;
      const fs=new ol.format.GeoJSON().readFeatures(json,{dataProjection:'EPSG:4326',featureProjection:job.crs});
      const unique=new Set();
      for(const f of fs){const id=String(f.get('operational_id')||'');if(!id||unique.has(id))continue; unique.add(id);source.addFeature(f);}
      status.textContent=`${source.getFeatures().length} copas disponibles. Tócalas para incluirlas.`;
    }catch(e){if(run===token)status.textContent='No se pudo cargar el GeoJSON: '+e.message;}
  }
  sel.addEventListener('change',load);
  $('clear-mission').addEventListener('click',()=>{selected.clear();renderSelected();});
  $('mission-form').addEventListener('submit',e=>{if(!selected.size){e.preventDefault();alert('Selecciona al menos una copa en el mapa.');}});
})();
