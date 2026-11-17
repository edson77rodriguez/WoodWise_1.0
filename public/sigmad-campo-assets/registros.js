/* SIGMAD Campo V0.3 · Mapa administrativo de registros sincronizados (solo online). */
(() => {
  'use strict';
  const target = document.getElementById('cr-record-map');
  if (!target) return;
  const info = document.getElementById('cr-map-count');
  const note = document.getElementById('cr-map-note');
  const selected = document.getElementById('cr-selected-record');
  if (!window.ol || !window.SIGMAD_FIELD_RECORD_MAP?.url) {
    if (info) info.textContent = 'No se pudo iniciar el mapa';
    return;
  }
  const ol = window.ol;
  const colors = {validado:'#168554',observado:'#c48a1a',en_revision:'#2464a1',pendiente:'#73877b'};
  const src = new ol.source.Vector();
  const layer = new ol.layer.Vector({
    source:src,
    style:feature => new ol.style.Style({
      image:new ol.style.Circle({
        radius:7, fill:new ol.style.Fill({color:colors[feature.get('status')] || '#73877b'}),
        stroke:new ol.style.Stroke({color:'#ffffff',width:2})
      })
    })
  });
  const map = new ol.Map({
    target:target,
    layers:[new ol.layer.Tile({source:new ol.source.OSM()}),layer],
    view:new ol.View({center:ol.proj.fromLonLat([-99.15,19.45]),zoom:5})
  });
  fetch(window.SIGMAD_FIELD_RECORD_MAP.url, {credentials:'same-origin',headers:{Accept:'application/json'},cache:'no-store'})
    .then(async res => {
      if (!res.ok) throw new Error('HTTP '+res.status);
      return res.json();
    }).then(body => {
      const data = Array.isArray(body.points) ? body.points : [];
      const features = data.filter(p => Number.isFinite(Number(p.lon)) && Number.isFinite(Number(p.lat)))
        .map(p => {
          const f = new ol.Feature({geometry:new ol.geom.Point(ol.proj.fromLonLat([Number(p.lon),Number(p.lat)]))});
          f.setProperties({id:p.id,label:p.label,status:p.status,accuracy_m:p.accuracy_m,url:p.url,grupo:p.grupo});
          return f;
        });
      src.addFeatures(features);
      if (info) info.textContent = features.length + ' posiciones con GPS';
      if (features.length) {
        const extent = src.getExtent();
        map.getView().fit(extent,{padding:[35,35,35,35],maxZoom:17,duration:250});
        if (features.length===1) map.getView().setZoom(16);
      } else if (note) {
        note.textContent = 'No hay coordenadas GPS en los registros filtrados. Los registros sin GPS permanecen en la tabla.';
      }
      if (data.length >= (body.sample_limit || 500) && note) {
        note.textContent += ' Se muestran como máximo los 500 registros más recientes; usa filtros para acotar.';
      }
    }).catch(err => {if (info) info.textContent = 'Error al cargar posiciones'; if (note) note.textContent = err.message;});

  map.on('singleclick', evt => {
    const f = map.forEachFeatureAtPixel(evt.pixel, feature => feature,{hitTolerance:8});
    if (!f) return;
    selected.replaceChildren();
    const title=document.createElement('strong');
    title.textContent = f.get('label') + ' · Grupo ' + f.get('grupo');
    const details=document.createElement('span');
    details.textContent = ' · Revisión: '+f.get('status')+
      (f.get('accuracy_m') != null ? ' · Precisión GNSS reportada ±'+f.get('accuracy_m')+' m':'');
    const link=document.createElement('a');
    link.href=f.get('url'); link.textContent='Abrir ficha completa';
    selected.append(title,details,document.createElement('br'),link);
    selected.hidden=false;
  });
})();
