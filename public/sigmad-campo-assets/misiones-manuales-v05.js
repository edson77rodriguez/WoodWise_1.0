/* SIGMAD Misiones V0.5 — objetivos manuales pre-campo. Solo administrador.
   No altera el GeoJSON IA ni la composición original de copas de la misión. */
(() => {
  'use strict';
  const api = window.SIGMAD_MISSIONS_MANUAL_API;
  const mapHook = window.SIGMAD_MISSION_MAP_HOOK;
  if (!api?.index || !api?.store || !mapHook) return;
  const $ = (id) => document.getElementById(id);
  const csrf = document.querySelector('#mission-form input[name="_token"]')?.value;
  if (!csrf) return;
  const base = document.querySelector('.cmh-sidebar');
  const toolbar = document.querySelector('.cmh-map-tools');
  if (!base || !toolbar) return;

  const style = document.createElement('style');
  style.textContent = `
    .mm05-panel{padding:15px 19px;border-top:1px solid #e0eae2;background:#fff}
    .mm05-title{font-size:13px;font-weight:850;color:#17543a;margin:0 0 5px}
    .mm05-note{font-size:11px;line-height:1.5;color:#687f70;margin:0 0 10px}
    .mm05-fields{display:grid;grid-template-columns:1fr 1fr;gap:7px}
    .mm05-fields label{font-size:10px;color:#436653;font-weight:800}
    .mm05-fields select,.mm05-fields textarea{width:100%;border:1px solid #cfdfd4;border-radius:8px;background:#fafdfa;padding:8px;font:inherit;font-size:11px;color:#254d34;margin-top:4px}
    .mm05-fields textarea{min-height:65px;resize:vertical}.mm05-wide{grid-column:1/-1}
    .mm05-row{display:flex;gap:7px;margin:9px 0;flex-wrap:wrap}
    .mm05-row button{padding:7px 9px;font-size:11px;border:1px solid #ceead9;border-radius:8px;background:#f0faf4;color:#116642;font-weight:800}
    .mm05-row .mm05-main{background:#9951bc;color:#fff;border-color:#9951bc}
    .mm05-row button:disabled{opacity:.5}.mm05-list{max-height:160px;overflow:auto;display:grid;gap:5px}
    .mm05-item{padding:8px;border:1px solid #ded8e9;border-radius:8px;background:#fcf9ff;text-align:left;font-size:11px;color:#53356a}
    .mm05-item strong{display:block}.mm05-msg{margin:7px 0;font-size:10px;color:#84609c;line-height:1.4}
  `;
  document.head.append(style);

  const pointBtn=document.createElement('button'); pointBtn.type='button';pointBtn.textContent='＋ Punto omitido';pointBtn.title='Marcar candidato manual (solo misiones existentes)';
  const polygonBtn=document.createElement('button');polygonBtn.type='button';polygonBtn.textContent='⬡ Copa aproximada';polygonBtn.title='Dibujar copa manual; doble clic para terminar';
  const cancelBtn=document.createElement('button');cancelBtn.type='button';cancelBtn.textContent='Cancelar dibujo';cancelBtn.hidden=true;
  toolbar.append(pointBtn,polygonBtn,cancelBtn);

  const panel=document.createElement('section');panel.className='mm05-panel';
  panel.innerHTML=`
    <h3 class="mm05-title">Árboles omitidos · candidatos manuales</h3>
    <p class="mm05-note">Marca visualmente sobre el ortomosaico. No es una detección IA ni una verdad de campo. Quedará disponible en grupo H; el censo B permanece ciego.</p>
    <div class="mm05-fields">
      <label>Especie probable<select id="mm05-species"><option value="ND">No determinada</option><option value="PL">Posible Pinus lawsonii</option><option value="QR">Posible Quercus rugosa</option><option value="O">Otra</option></select></label>
      <label>Motivo<select id="mm05-reason"><option value="visible_omitido">Visible y omitido por IA</option><option value="fusionada">Copa fusionada</option><option value="oculta">Oculto / suprimido</option><option value="dudosa">Dudoso</option><option value="otro">Otro</option></select></label>
      <label>Prioridad<select id="mm05-priority"><option value="normal">Normal</option><option value="alta">Alta</option><option value="baja">Baja</option></select></label>
      <label class="mm05-wide">Observaciones<textarea id="mm05-note" maxlength="1000" placeholder="Por qué debe visitar la brigada este árbol"></textarea></label>
    </div>
    <div class="mm05-row"><button type="button" id="mm05-save" class="mm05-main" disabled>Guardar marca</button><button type="button" id="mm05-cancel">Limpiar</button><button type="button" id="mm05-reload">Actualizar lista</button></div>
    <div id="mm05-msg" class="mm05-msg" role="status">Elige Punto o Copa aproximada arriba del mapa.</div>
    <div id="mm05-list" class="mm05-list"></div>`;
  base.insertBefore(panel,base.querySelector('.cmh-actions'));

  let currentMap=null,currentLayer=null,source=null,draftSource=null,draftLayer=null,draw=null;
  let pending=null, editing=null, features=new Map(), listVersion=0, drawing=false;
  const notice=(message) => { $('mm05-msg').textContent=message; };
  const selection=(feature)=>{
    const c=feature.get('manual_code')||'MAN';
    const geom=feature.getGeometry();
    const isPoint=geom?.getType()==='Point';
    return new ol.style.Style({
      stroke:new ol.style.Stroke({color:'#ce5deb',width:2.6,lineDash:isPoint?null:[7,5]}),
      fill:new ol.style.Fill({color:'rgba(196,75,219,.14)'}),
      image:new ol.style.Circle({radius:8,fill:new ol.style.Fill({color:'#c458d9'}),stroke:new ol.style.Stroke({color:'#fff',width:2.2})}),
      text:new ol.style.Text({text:c,font:'bold 11px sans-serif',offsetY:isPoint?-17:0,fill:new ol.style.Fill({color:'#fff'}),backgroundFill:new ol.style.Fill({color:'rgba(70,22,89,.82)'}),padding:[3,4,3,4]})
    });
  };
  const featureUrl=(uuid)=>api.store.replace(/\/$/,'')+'/'+encodeURIComponent(uuid);
  async function request(url,method='GET',body=null){
    const res=await fetch(url,{method,credentials:'same-origin',cache:'no-store',headers:{Accept:'application/json',...(method!=='GET'?{'Content-Type':'application/json','X-CSRF-TOKEN':csrf}:{})},...(body!==null?{body:JSON.stringify(body)}:{})});
    const data=await res.json().catch(()=>({}));
    if(!res.ok)throw new Error(data.message||Object.values(data.errors||{})[0]?.[0]||`HTTP ${res.status}`);
    return data;
  }
  function metadata(){return {probable_species:$('mm05-species').value,reason:$('mm05-reason').value,priority:$('mm05-priority').value,note:$('mm05-note').value.trim()||null};}
  function stopDrawing(){
    if (currentMap && draw) currentMap.removeInteraction(draw);
    draw=null;drawing=false;window.SIGMAD_MISSIONS_MANUAL_DRAWING=false;
    window.SIGMAD_MISSIONS_MANUAL_DRAWING_UNTIL=Date.now()+650;
    cancelBtn.hidden=true;pointBtn.disabled=false;polygonBtn.disabled=false;
  }
  function clear(){stopDrawing();pending=null;editing=null;draftSource?.clear();const retire=$('mm05-retire');if(retire)retire.hidden=true;$('mm05-save').disabled=true;$('mm05-save').textContent='Guardar marca';}
  function start(type){
    if(!currentMap){notice('Primero carga el ortomosaico de esta misión.');return;}
    clear();drawing=true;window.SIGMAD_MISSIONS_MANUAL_DRAWING=true;
    pointBtn.disabled=true;polygonBtn.disabled=true;cancelBtn.hidden=false;
    draw=new ol.interaction.Draw({source:draftSource,type,maxPoints:type==='Polygon'?64:undefined});
    currentMap.addInteraction(draw);
    draw.on('drawend',e=>{
      const geom=e.feature.getGeometry().clone();
      pending={type:geom.getType(),coordinates:geom.getCoordinates()};
      stopDrawing();setTimeout(()=>{draftSource?.clear();draftSource?.addFeature(new ol.Feature({geometry:geom}));},0);
      $('mm05-save').disabled=false;
      notice(type==='Point'?'Punto colocado. Completa y guarda la marca.':'Copa aproximada dibujada. No es una máscara validada.');
    });
    notice(type==='Point'?'Haz clic sobre el centro visible de la copa.':'Dibuja el contorno de copa; termina con doble clic.');
  }
  function showList(){
    const el=$('mm05-list');el.replaceChildren();
    const entries=[...features.values()].sort((a,b)=>(a.get('manual_code')||'').localeCompare(b.get('manual_code')||''));
    for(const f of entries){
      const uuid=f.get('manual_target_uuid'), code=f.get('manual_code');
      const row=document.createElement('button');row.type='button';row.className='mm05-item';
      const name=document.createElement('strong');name.textContent=`${code} · ${f.get('probable_species')||'ND'}`;
      const details=document.createElement('span');details.textContent=`${f.get('reason')||''} · ${f.get('priority')||'normal'}`;
      row.append(name,details);row.addEventListener('click',()=>{
        clear();editing=uuid;
        $('mm05-species').value=f.get('probable_species')||'ND';
        $('mm05-reason').value=f.get('reason')||'visible_omitido';
        $('mm05-priority').value=f.get('priority')||'normal';
        $('mm05-note').value=f.get('note')||'';
        $('mm05-save').disabled=false;$('mm05-save').textContent=`Actualizar ${code}`;
        const extent=f.getGeometry().getExtent();
        if(currentMap){const padding=Math.max(8,ol.extent.getWidth(extent),ol.extent.getHeight(extent));currentMap.getView().fit(ol.extent.buffer(extent,padding),{padding:[85,80,85,80],maxZoom:23,duration:220});}
        notice(`${code}: puedes editar atributos o retirarlo con el botón que aparece.`);
        renderRetire(uuid,code);
      });el.append(row);
    }
    if(!entries.length)notice('No hay marcas manuales todavía. Dibuja un punto o una copa.');
  }
  function renderRetire(uuid,code){
    let remove=$('mm05-retire');
    if(!remove){remove=document.createElement('button');remove.id='mm05-retire';remove.type='button';remove.textContent='Retirar marca';remove.style.cssText='margin:4px 0;padding:7px;border:1px solid #dfb6cf;border-radius:7px;color:#8f366f;background:#fff';$('mm05-msg').after(remove);}
    remove.hidden=false;
    remove.onclick=async()=>{
      const justification=prompt(`Motivo para retirar ${code} (mínimo 8 caracteres):`);
      if(justification===null)return;
      if(justification.trim().length<8){notice('Explica el retiro en al menos 8 caracteres.');return;}
      try{await request(featureUrl(uuid),'DELETE',{justification:justification.trim()});remove.hidden=true;clear();await refresh();notice(`${code} retirado del mapa; el historial conserva el evento.`);}
      catch(e){notice('No se pudo retirar: '+e.message);}
    };
  }
  async function refresh(){
    const seq=++listVersion;
    if(!currentMap)return;
    try{
      const r=await request(api.index);
      if(seq!==listVersion||!currentMap)return;
      if(r.manual_targets?.crs!==mapHook.getJob()?.crs)throw Error('El CRS de las marcas no coincide con el análisis.');
      const fs=new ol.format.GeoJSON().readFeatures(r.manual_targets,{dataProjection:r.manual_targets.crs,featureProjection:r.manual_targets.crs});
      source.clear();features.clear();
      for(const f of fs){const uuid=f.get('manual_target_uuid');if(!uuid)continue;features.set(uuid,f);source.addFeature(f);}
      showList();
      notice(`${features.size} marcas guardadas. Para que lleguen a campo, la brigada debe descargar de nuevo esta misión.`);
    }catch(e){notice('No se pudieron cargar las marcas: '+e.message);}
  }
  function attach(){
    const m=mapHook.getMap(); if(!m || m===currentMap)return;
    clear();listVersion++;features.clear();currentMap=m;
    source=new ol.source.Vector({wrapX:false});draftSource=new ol.source.Vector({wrapX:false});
    currentLayer=new ol.layer.Vector({source,zIndex:25,style:selection});
    draftLayer=new ol.layer.Vector({source:draftSource,zIndex:28,style:new ol.style.Style({stroke:new ol.style.Stroke({color:'#ffe375',width:3,lineDash:[6,4]}),fill:new ol.style.Fill({color:'rgba(255,227,117,.18)'}),image:new ol.style.Circle({radius:8,fill:new ol.style.Fill({color:'#ffe375'}),stroke:new ol.style.Stroke({color:'#552d5e',width:2})})})});
    m.addLayer(currentLayer);m.addLayer(draftLayer);
    refresh();
  }
  pointBtn.addEventListener('click',()=>start('Point'));
  polygonBtn.addEventListener('click',()=>start('Polygon'));
  cancelBtn.addEventListener('click',()=>{clear();notice('Dibujo cancelado.');});
  $('mm05-cancel').addEventListener('click',()=>{clear();const button=$('mm05-retire');if(button)button.hidden=true;notice('Selección limpiada.');});
  $('mm05-reload').addEventListener('click',refresh);
  $('mm05-save').addEventListener('click',async()=>{
    const data=metadata();
    if(!editing&&!pending){notice('Dibuja primero un punto o un polígono.');return;}
    $('mm05-save').disabled=true;
    try{
      if(editing)await request(featureUrl(editing),'PATCH',data);
      else await request(api.store,'POST',{...data,geometry:pending});
      const message=editing?'Atributos actualizados y auditados.':'Marca creada y auditada.';
      clear();const button=$('mm05-retire');if(button)button.hidden=true;
      await refresh();notice(`${message} La brigada debe volver a descargar la misión.`);
    }catch(e){$('mm05-save').disabled=false;notice('No se guardó: '+e.message);}
  });
  window.addEventListener('sigmad:missions-map-ready',attach);
  attach();
})();
