/* SIGMAD SAM INTERACTIVE V0.10 · Mismo FastAPI/ngrok del Colab V0.9U.
 * No sobreescribe IA original; las propuestas no se aplican sin aprobación humana.
 * Enviar JPG/WebP de teselas WEBP maestras NO equivale al GeoTIFF RGB original.
 */
(() => {
  'use strict';
  const host=window.SIGMAD_SAM_HOST;
  const root=document.getElementById('review-app');
  if(!host||!root){console.warn('SAM V0.10: falta el adaptador V0.9.2 o el editor.');return;}
  const $=id=>document.getElementById(id);
  const pane=document.createElement('section');pane.className='rv-sam-panel';
  pane.innerHTML=`
    <div class="rv-sam-head"><div><strong>✦ SAM 2.1 · Asistente IA</strong><small>Una propuesta supervisada. La IA original no cambia.</small></div><span>COLAB · GPU</span></div>
    <div class="rv-sam-actions"><button id="sam-activate" type="button" class="rv-sam-main">Iniciar puntos SAM</button>
    <button id="sam-positive" type="button" disabled>🟢 Incluir árbol</button><button id="sam-negative" type="button" disabled>🔴 Excluir vecino</button>
    <button id="sam-undo-point" type="button" disabled>↶ Quitar punto</button><button id="sam-clear" type="button" disabled>Limpiar</button></div>
    <div class="rv-sam-actions"><button id="sam-generate" type="button" class="rv-sam-main" disabled>✦ Generar con SAM</button>
    <button id="sam-apply" type="button" class="rv-sam-apply" disabled>✓ Aplicar propuesta</button>
    <button id="sam-cancel" type="button" disabled>Cancelar SAM</button></div>
    <div id="sam-options" class="rv-sam-options" hidden></div>
    <p id="sam-status" class="rv-sam-status" role="status">Selecciona una copa turquesa, después activa los puntos SAM.</p>
    <small class="rv-sam-disclaimer">SAM no identifica especies. Su puntuación NO es confianza frente al campo. El recorte usa teselas WebP HD.</small>`;
  const dest=root.querySelector('.rv-brush-workbench') || root.querySelector('.rv-tools');
  if(!dest){console.error('SAM V0.10: no existe panel de herramientas');return;}
  dest.insertAdjacentElement('afterend',pane);
  let mode=null,active=false,boundMap=null,cues=[],cursorLayer=null,cursorSource=null;
  let response=null,chosen=0,cropInfo=null,requesting=false,previewLayer=null,previewSource=null,selectionIdentity=null,sessionMission=null;
  const ui=(message,warn=false)=>{$('sam-status').textContent=message;$('sam-status').dataset.warn=warn?'yes':'no';};
  const color=(label)=>label===1?'#19bf70':'#e64b59';
  function drawButtons(){
    const ctx=host.snapshot();const ready=!!ctx.map&&!!ctx.selected_id&&ctx.hd_ready;
    $('sam-activate').disabled=active||!ready||requesting;
    $('sam-positive').disabled=!active||requesting;
    $('sam-negative').disabled=!active||requesting;
    $('sam-undo-point').disabled=!active||!cues.length||requesting;
    $('sam-clear').disabled=!active||!cues.length||requesting;
    $('sam-generate').disabled=!active||requesting||!cues.some(c=>c.label===1);
    $('sam-apply').disabled=!active||requesting||!response?.candidates?.length;
    $('sam-cancel').disabled=!active||requesting;
    $('sam-positive').dataset.active=mode===1?'yes':'no';
    $('sam-negative').dataset.active=mode===0?'yes':'no';
    $('sam-generate').textContent=requesting?'Analizando en Colab…':'✦ Generar con SAM';
  }
  function updatePrompts(){
    if(!cursorSource)return;
    cursorSource.clear();
    for (const p of cues){
      const f=new ol.Feature(new ol.geom.Point(p.utm));
      f.setStyle(new ol.style.Style({image:new ol.style.Circle({radius:7,
        fill:new ol.style.Fill({color:color(p.label)}),
        stroke:new ol.style.Stroke({color:'#ffffff',width:2})})}));
      cursorSource.addFeature(f);
    }
    drawButtons();
  }
  function clearPreview(){
    response=null;chosen=0;cropInfo=null;
    if(previewSource)previewSource.clear();
    $('sam-options').replaceChildren();$('sam-options').hidden=true;
    drawButtons();
  }
  function addPrompt(event){
    if(!active||requesting||!boundMap)return;
    if(cues.length>=20)return ui('Límite de 20 puntos por inferencia. Genera una propuesta o elimina alguno.',true);
    if(host.snapshot().selected_id!==selectionIdentity || host.snapshot().mission_uuid!==sessionMission){stop();return;}
    const utm=event.coordinate;
    if(!Array.isArray(utm)||utm.length!==2||!utm.every(Number.isFinite))return;
    cues.push({utm:[utm[0],utm[1]],label:mode});
    clearPreview();updatePrompts();
    ui(`${cues.filter(c=>c.label===1).length} puntos verdes · ${cues.filter(c=>c.label===0).length} rojos. Pulsa «Generar con SAM» cuando estés listo.`);
  }
  function stop(silent=false){
    if(requesting)return;
    if(boundMap){boundMap.un('singleclick',addPrompt);
      if(cursorLayer)boundMap.removeLayer(cursorLayer);
      if(previewLayer)boundMap.removeLayer(previewLayer);
    }
    boundMap=null;cursorLayer=null;cursorSource=null;previewLayer=null;previewSource=null;
    active=false;mode=null;cues=[];selectionIdentity=null;sessionMission=null;clearPreview();
    host.endPoints();drawButtons();
    if(!silent)ui('SAM cerrado. Tus copas originales y las revisiones guardadas siguen intactas.');
  }
  function start(){
    const ctx=host.snapshot();
    if(!ctx.map||!ctx.selected_id||!ctx.hd_ready)return ui('Selecciona una copa y confirma que el HD esté cargado.',true);
    try{host.beginPoints();}catch(e){return ui(e.message,true)}
    active=true;mode=1;boundMap=ctx.map;selectionIdentity=ctx.selected_id;sessionMission=ctx.mission_uuid;cues=[];
    cursorSource=new ol.source.Vector();previewSource=new ol.source.Vector();
    previewLayer=new ol.layer.Vector({source:previewSource,zIndex:28,style:new ol.style.Style({
      stroke:new ol.style.Stroke({color:'#ffe66e',width:3}),
      fill:new ol.style.Fill({color:'rgba(255,213,54,.28)'})})});
    cursorLayer=new ol.layer.Vector({source:cursorSource,zIndex:30});
    boundMap.addLayer(previewLayer);boundMap.addLayer(cursorLayer);boundMap.on('singleclick',addPrompt);
    ui('Haz clic dentro del árbol para agregar un punto verde. Cambia a rojo para excluir copas vecinas.');drawButtons();
  }
  function rasterSource(){
    if(!boundMap)return null;
    const layer=boundMap.getLayers().getArray().find(l=>l instanceof ol.layer.Tile &&
      typeof l.getSource()?.getTileGrid==='function' && l.getSource()?.getTileGrid());
    return layer?.getSource()||null;
  }
  async function getBitmap(url){
    const r=await fetch(url,{credentials:'same-origin',cache:'force-cache'});
    if(!r.ok)throw new Error('No se pudo recuperar la tesela HD: HTTP '+r.status);
    const blob=await r.blob();
    return await createImageBitmap(blob);
  }
  async function makeCrop(){
    const ctx=host.snapshot();
    if(!ctx.map||!ctx.selected_geometry||!ctx.hd_ready)throw new Error('Selecciona una copa y espera a que cargue el mapa HD.');
    const source=rasterSource();if(!source)throw new Error('No se encontró el origen de teselas del ortomosaico HD.');
    const grid=source.getTileGrid(),r=grid.getResolutions();
    if(!r.length)throw new Error('Resolución nativa HD no disponible.');
    const z=r.length-1,gsd=Number(r[z]);
    if(!Number.isFinite(gsd)||gsd<=0)throw new Error('GSD no válido.');
    const ext=ctx.selected_geometry.getExtent(),w=ext[2]-ext[0],h=ext[3]-ext[1];
    if(Math.max(w,h)>gsd*960)throw new Error('La copa es demasiado grande para un recorte SAM de 1024 px sin perder resolución.');
    const cx=(ext[0]+ext[2])/2,cy=(ext[1]+ext[3])/2,span=1024*gsd;
    const bounds=[cx-span/2,cy-span/2,cx+span/2,cy+span/2];
    // Convertir marcas UTM a píxeles del recorte raster NO del zoom digital del mapa.
    const points=cues.map(p=>({x:(p.utm[0]-bounds[0])/gsd,y:(bounds[3]-p.utm[1])/gsd,label:p.label}));
    if(points.some(p=>p.x<0||p.x>=1024||p.y<0||p.y>=1024))
      throw new Error('Alguna marca está fuera del recorte de 1024 px. Limpiar y marcar cerca de la copa.');
    const canvas=document.createElement('canvas');canvas.width=1024;canvas.height=1024;
    const g=canvas.getContext('2d',{alpha:false});g.fillStyle='white';g.fillRect(0,0,1024,1024);
    const full=grid.getExtent();
    if(!full)throw new Error('Extensión del ortomosaico HD no disponible.');
    const intersection=[Math.max(bounds[0],full[0]),Math.max(bounds[1],full[1]),
      Math.min(bounds[2],full[2]),Math.min(bounds[3],full[3])];
    if(intersection[2]<=intersection[0]||intersection[3]<=intersection[1])
      throw new Error('El recorte quedó fuera del ortomosaico.');
    // Evitar solicitar una tesela adicional cuando el recorte coincide exactamente con el borde Este/Sur.
    intersection[2]-=gsd*1e-4;intersection[1]+=gsd*1e-4;
    const tiles=grid.getTileRangeForExtentAndZ(intersection,z);
    if(!tiles)throw new Error('No se encontraron teselas HD dentro del recorte.');
    const tasks=[];
    for(let y=tiles.minY;y<=tiles.maxY;y++)for(let x=tiles.minX;x<=tiles.maxX;x++){
      const tileCoord=[z,x,y],url=source.getTileUrlFunction()(tileCoord,1,ctx.map.getView().getProjection());
      if(!url)continue;
      const rect=grid.getTileCoordExtent(tileCoord);
      tasks.push({url,rect});
    }
    if(!tasks.length||tasks.length>40)throw new Error('Teselas HD insuficientes o recorte fuera de rango.');
    // Una foto limpia de la capa HD, SIN máscaras coloreadas, líneas, etiquetas ni controles.
    // Máximo 6 descargas simultáneas para no saturar Hostinger.
    for(let i=0;i<tasks.length;i+=6){
      await Promise.all(tasks.slice(i,i+6).map(async t=>{
        const bmp=await getBitmap(t.url);
        try{g.drawImage(bmp,(t.rect[0]-bounds[0])/gsd,(bounds[3]-t.rect[3])/gsd,
            (t.rect[2]-t.rect[0])/gsd,(t.rect[3]-t.rect[1])/gsd);} finally{bmp.close()}
      }));
    }
    const blob=await new Promise((resolve,reject)=>canvas.toBlob(b=>b?resolve(b):reject(new Error('No se pudo exportar el recorte.')),'image/webp',.94));
    if(blob.size>4_900_000)throw new Error('El recorte comprimido supera 4.9 MB.');
    return {blob,points,bounds,gsd,crs:ctx.crs,mission_uuid:ctx.mission_uuid,analysis_uuid:ctx.analysis_uuid,selected_id:ctx.selected_id};
  }
  function pixelToUtm(p,bounds,gsd){return [bounds[0]+p[0]*gsd,bounds[3]-p[1]*gsd]}
  function candidateGeom(candidate, info){
    if(!candidate||candidate.geometry?.type!=='MultiPolygon')throw new Error('Propuesta SAM sin multipolígonos.');
    const coords=candidate.geometry.coordinates.map(poly=>poly.map(ring=>ring.map(point=>pixelToUtm(point,info.bounds,info.gsd))));
    return new ol.geom.MultiPolygon(coords);
  }
  function previewCandidate(index){
    if(!response||!cropInfo||!previewSource)return;
    chosen=index;previewSource.clear();
    const candidate=response.candidates[index];
    const f=new ol.Feature(candidateGeom(candidate,cropInfo));
    previewSource.addFeature(f);
    for(const b of $('sam-options').querySelectorAll('button'))b.dataset.active=String(b.dataset.index)===String(index)?'yes':'no';
    ui(`Propuesta ${index+1} sobre el mapa (amarillo). ${candidate.touches_crop_edge?'ALERTA: la máscara toca el borde del recorte.':'Revísala y confirma para aplicarla.'}`,
      candidate.touches_crop_edge);
  }
  function listCandidates(){
    const panel=$('sam-options');panel.replaceChildren();panel.hidden=false;
    response.candidates.forEach((candidate,i)=>{
      const b=document.createElement('button');b.type='button';b.dataset.index=String(i);
      const score=Number(candidate.predicted_iou_uncalibrated);
      b.textContent=`Propuesta ${i+1} · score SAM ${Number.isFinite(score)?score.toFixed(3):'sin dato'} (NO calibrado)`;
      b.addEventListener('click',()=>previewCandidate(i));panel.append(b);
    });
    previewCandidate(0);drawButtons();
  }
  async function generate(){
    if(!active||requesting||!cues.some(c=>c.label===1))return;
    requesting=true;drawButtons();ui('Recortando ortomosaico HD sin superposiciones…');
    try{
      const info=await makeCrop();
      if(host.snapshot().selected_id!==selectionIdentity || host.snapshot().mission_uuid!==sessionMission || host.snapshot().map!==boundMap)throw new Error('Cambió la sesión de edición. Cancela y vuelve a iniciar.');
      const form=new FormData();form.append('image',info.blob,'sam_hd_crop.webp');
      form.append('points',JSON.stringify(info.points));
      form.append('crop_bounds',JSON.stringify(info.bounds));
      form.append('crs',info.crs);form.append('analysis_uuid',info.analysis_uuid);
      ui('SAM está segmentando en el Colab unificado…');
      const resp=await fetch(`/campo/revision-ia/${encodeURIComponent(info.mission_uuid)}/sam-interactive`,{
        method:'POST',credentials:'same-origin',headers:{'X-CSRF-TOKEN':window.SIGMAD_REVIEW_BOOT.csrf,'Accept':'application/json'},body:form});
      const data=await resp.json().catch(()=>({}));
      if(!resp.ok)throw new Error(data.message||data.errors&&Object.values(data.errors)[0]?.[0]||`HTTP ${resp.status}`);
      if(!data.candidates?.length)throw new Error('SAM no propuso ninguna máscara. Agrega más puntos y prueba de nuevo.');
      if(info.selected_id!==host.snapshot().selected_id || host.snapshot().mission_uuid!==sessionMission || host.snapshot().map!==boundMap)throw new Error('La copa o la misión cambió durante la inferencia. Se descartan las propuestas.');
      response=data;cropInfo=info;listCandidates();
    }catch(err){ui('No se completó SAM: '+err.message,true);clearPreview();}
    finally{requesting=false;drawButtons()}
  }
  function apply(){
    if(!active||requesting||!cropInfo||!response?.candidates?.length)return;
    const candidate=response.candidates[chosen];
    if(candidate.touches_crop_edge && !confirm('SAM tocó el borde del recorte; podría estar incompleta. ¿Aun así quieres aplicar la propuesta?'))return;
    if(!confirm('¿Aplicar esta propuesta SAM a la copia EDITABLE de la copa? Podrás deshacer y todavía NO se guardará en la base de datos.'))return;
    try{
      if(host.snapshot().selected_id!==cropInfo.selected_id || host.snapshot().mission_uuid!==sessionMission || host.snapshot().map!==boundMap)throw new Error('La copa o la misión seleccionada cambió.');
      host.applyCandidate(candidateGeom(candidate,cropInfo),{
        model_id:response.model_id,score:candidate.predicted_iou_uncalibrated,
        image_sha256:response.image_sha256,created_at_utc:response.created_at_utc,
        source:response.source,points:cropInfo.points,
        bounds:cropInfo.bounds,gsd:cropInfo.gsd,rank:chosen+1});
      stop();
      ui('Propuesta aplicada a la copia editable. Corrige con los pinceles y pulsa «Guardar revisión en SIGMAD».');
    }catch(err){ui('No se aplicó SAM: '+err.message,true)}
  }
  $('sam-activate').addEventListener('click',start);
  $('sam-positive').addEventListener('click',()=>{mode=1;drawButtons();ui('Modo verde: haz clic dentro de la copa que deseas incluir.');});
  $('sam-negative').addEventListener('click',()=>{mode=0;drawButtons();ui('Modo rojo: haz clic sobre la vegetación vecina que deseas excluir.');});
  $('sam-undo-point').addEventListener('click',()=>{cues.pop();updatePrompts();clearPreview()});
  $('sam-clear').addEventListener('click',()=>{cues=[];updatePrompts();clearPreview();ui('Puntos eliminados. La copa sigue intacta.');});
  $('sam-cancel').addEventListener('click',()=>stop());
  $('sam-generate').addEventListener('click',()=>void generate());
  $('sam-apply').addEventListener('click',apply);
  // Solo estado visual. No consulta red ni ejecuta GPU.
  setInterval(()=>{
    if(active && (!host.snapshot().map||host.snapshot().map!==boundMap || host.snapshot().selected_id!==selectionIdentity || host.snapshot().mission_uuid!==sessionMission))stop(true);
    drawButtons();
  },800);
  drawButtons();
})();
