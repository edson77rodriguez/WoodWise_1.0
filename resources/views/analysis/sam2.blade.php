<!DOCTYPE html>
<html lang="es">
<head>
  <meta charset="UTF-8">
  <meta name="viewport" content="width=device-width, initial-scale=1">
  <meta name="csrf-token" content="{{ csrf_token() }}">
  <title>SIGMAD / Crown Refinement Lab</title>
  <link href="https://cdn.jsdelivr.net/npm/ol@10.10.0/ol.css" rel="stylesheet">
  <style>
  :root{color-scheme:dark;--bg:#08120f;--panel:#10221c;--panel2:#122920;--line:#28483a;--text:#e9faf0;--muted:#9ab7a7;--green:#31e6a0;--blue:#48b9ff;--amber:#ffc768;--red:#ff7777}
  *{box-sizing:border-box} body{margin:0;font:14px/1.45 Inter,system-ui,-apple-system,'Segoe UI',sans-serif;background:var(--bg);color:var(--text)}
  button,input{font:inherit} button{cursor:pointer} a{color:inherit} .top{display:flex;align-items:center;justify-content:space-between;gap:20px;padding:15px 24px;background:#0a1914;border-bottom:1px solid var(--line)}
  .brand{display:flex;gap:13px;align-items:center}.mark{width:40px;height:40px;display:grid;place-items:center;border-radius:12px;color:#031b11;background:linear-gradient(140deg,#40e3a1,#9ff0ce);font-weight:900;letter-spacing:-.05em}
  .kicker{font-size:10px;letter-spacing:.16em;color:#64e4ad;font-weight:800}.title{font-weight:770;font-size:17px}.subtitle{font-size:11px;color:var(--muted)} .outline{background:transparent;color:var(--text);border:1px solid var(--line);padding:10px 14px;border-radius:10px;text-decoration:none}
  .main{padding:18px 22px;max-width:1850px;margin:auto} .metrics{display:grid;grid-template-columns:repeat(4,minmax(0,1fr));gap:12px;margin-bottom:14px}.stat{background:linear-gradient(150deg,#163027,#101f1a);border:1px solid var(--line);border-radius:14px;padding:15px 18px}.stat b{font-size:24px;display:block;font-variant-numeric:tabular-nums}.stat span{font-size:11px;color:var(--muted)}
  .workspace{display:grid;grid-template-columns:280px minmax(0,1fr) 300px;gap:12px;min-height:640px;height:calc(100vh - 205px)} .panel{background:#0d1e18;border:1px solid var(--line);border-radius:14px;overflow:hidden;display:flex;flex-direction:column;min-height:0}.panel header{font-size:12px;font-weight:800;letter-spacing:.03em;padding:17px 16px;border-bottom:1px solid var(--line)}.panelcontent{padding:16px;overflow:auto;min-height:0}.subhead{font-size:11px;letter-spacing:.08em;color:var(--muted);font-weight:700;text-transform:uppercase;margin-bottom:11px}
  .choice{display:flex;align-items:center;justify-content:space-between;gap:12px;background:#132a21;border:1px solid #26513e;padding:12px;border-radius:11px;margin-bottom:9px}.choice label{display:flex;align-items:center;gap:9px;cursor:pointer}.choice input{accent-color:var(--green);width:17px;height:17px}.choice small{display:block;color:var(--muted);font-size:10px}.dot{display:inline-block;width:9px;height:9px;border-radius:50%;margin-right:5px}.legend{color:var(--muted);font-size:11px;margin-top:14px}.mapwrap{position:relative;border-radius:14px;overflow:hidden;border:1px solid var(--line);background:#122018}.map{width:100%;height:100%}.hud{position:absolute;z-index:2;left:16px;top:15px;background:#0b1b16ed;padding:12px 14px;border:1px solid #41604c;border-radius:12px;pointer-events:none;max-width:300px;backdrop-filter:blur(12px)}.hud b{font-size:13px}.hud small{display:block;color:#a8c7b6;margin-top:2px}.btns{position:absolute;z-index:2;bottom:16px;left:16px;display:flex;gap:7px}.btns button{background:#092319f2;border:1px solid #427a5c;color:var(--text);padding:9px 13px;border-radius:10px;font-size:11px;font-weight:700}
  input[type=text],input[type=search]{width:100%;padding:11px 12px;background:#081811;color:var(--text);border:1px solid var(--line);border-radius:10px;outline:none}input:focus{border-color:var(--green)}.item{width:100%;text-align:left;background:#13281f;color:var(--text);border:1px solid transparent;border-radius:10px;padding:12px;margin-top:7px}.item:hover,.item.active{border-color:#4bbf8a;background:#1a3a2c}.item b{display:block;font-size:12px}.item span{font-size:10px;color:var(--muted)}.item .status{color:var(--blue)}.button{border:0;border-radius:10px;background:var(--green);color:#032018;font-weight:800;padding:11px 13px;width:100%}.note{padding:12px;border-radius:10px;background:#2d2615;border:1px solid #806b39;color:#f5d9a7;font-size:11px}.info{background:#112920;border:1px solid #28483a;color:var(--muted);padding:12px;border-radius:10px;font-size:11px;margin:12px 0}.kv{display:flex;justify-content:space-between;border-bottom:1px solid #234032;padding:9px 0;gap:8px;font-size:12px}.kv span:first-child{color:var(--muted)}.kv strong{text-align:right;word-break:break-word}.badge{display:inline-block;border-radius:999px;background:#274b35;padding:5px 8px;color:#b9ffda;font-size:10px}.msg{padding:9px 12px;background:#163f2e;border-radius:10px;margin-bottom:12px}.err{color:#ffb6b6;font-size:12px}.foot{padding:12px 24px;font-size:11px;color:var(--muted)}.hidden{display:none!important}
  @media(max-width:1180px){.workspace{grid-template-columns:230px minmax(0,1fr)}.right{grid-column:1/-1;max-height:250px}.workspace{height:auto;min-height:650px}.mapwrap{height:580px}}@media(max-width:720px){.main{padding:10px}.metrics{grid-template-columns:repeat(2,1fr)}.workspace{grid-template-columns:1fr}.mapwrap{height:62vh;min-height:410px;grid-row:1}.top{padding:12px}.top .subtitle{display:none}.panel{max-height:320px}.stat b{font-size:21px}.hud{max-width:235px} .foot{padding:12px}}
  </style>
</head>
<body>
  <div class="top">
    <div class="brand"><div class="mark">S</div><div><div class="kicker">SIGMAD · LABORATORIO GEOESPACIAL</div><div class="title">Crown Refinement / SAM 2.1</div><div class="subtitle">Comparación no destructiva · análisis {{ substr($job->uuid,0,8) }} · {{ $mosaic->original_name }}</div></div></div>
    <a class="outline" href="{{ route('analysis.map',['analysisUuid'=>$job->uuid]) }}">← Visor V0.7E</a>
  </div>
  <div class="main">
    @if(session('sam2_success'))<div class="msg">{{ session('sam2_success') }}</div>@endif
    @if($errors->any())<div class="msg err">{{ $errors->first() }}</div>@endif
    <div class="metrics">
      <div class="stat"><b>{{ (int)data_get($job->summary,'primary_objects',0) }}</b><span>Copas operativas V0.7E</span></div>
      <div class="stat"><b>{{ $sam ? (int)data_get($sam->metadata,'sam2_proposals',0) : '—' }}</b><span>Propuestas SAM 2 registradas</span></div>
      <div class="stat"><b>{{ $sam ? (int)data_get($sam->metadata,'processed',0) : '—' }}</b><span>Objetos evaluados por SAM</span></div>
      <div class="stat"><b>{{ $sam ? (int)data_get($sam->metadata,'skipped',0) : '—' }}</b><span>Objetos omitidos en proceso SAM</span></div>
    </div>
    <div class="workspace">
      <section class="panel">
        <header>01 / Capas y explorador</header>
        <div class="panelcontent">
          <div class="subhead">Geometrías visibles</div>
          <div class="choice"><label><input type="checkbox" id="baseToggle" checked><span><span class="dot" style="background:var(--green)"></span>V0.7E original<small>Mask R-CNN / YOLO-Seg</small></span></label></div>
          <div class="choice"><label><input type="checkbox" id="samToggle" {{ $sam ? 'checked' : 'disabled' }}><span><span class="dot" style="background:var(--blue)"></span>SAM 2.1<small>Contorno refinado provisional</small></span></label></div>
          <div class="choice"><label><input type="checkbox" id="rasterToggle" checked><span>Ortomosaico RGB<small>COG / preview R2</small></span></label></div>
          <div class="subhead" style="margin-top:18px">Buscar candidato</div>
          <input type="search" id="search" placeholder="Ej. operational ID…" aria-label="Buscar por identificador">
          <div id="list" style="margin-top:12px"><small>Iniciando mapa…</small></div>
          <div class="legend">Verde = original &nbsp; · &nbsp; Azul = SAM 2.1<br>Esta interfaz no valida automáticamente las copas.</div>
        </div>
      </section>
      <div class="mapwrap">
        <div id="map" class="map" aria-label="Mapa interactivo SIGMAD"></div>
        <div class="hud"><b>Comparación espacial de copas</b><small id="mapStatus">Cargando geometrías…</small></div>
        <div class="btns"><button type="button" id="fit">Ver mosaico</button><button type="button" id="focus">Enfocar copas</button></div>
      </div>
      <section class="panel right">
        <header>02 / Auditoría geométrica</header>
        <div class="panelcontent">
          <div id="inspector" class="info">Selecciona una copa en el mapa o en la lista para inspeccionar su refinamiento.</div>
          @if(!$sam)
            <div class="note"><strong>Capa SAM 2 aún no registrada.</strong><div style="margin-top:5px">Ejecuta el notebook de Colab, publica la capa en R2 y pega aquí su clave de objeto.</div></div>
            <form method="POST" action="{{ route('analysis.sam2.register',['analysisUuid'=>$job->uuid]) }}" style="margin-top:14px">@csrf
              <label for="key" style="display:block;font-size:11px;color:var(--muted);margin-bottom:8px">R2 object key de SAM 2</label>
              <input type="text" required id="key" name="sam2_geojson_key" placeholder="projects/.../sam2/runs/.../sam2_refinements.geojson" value="{{ old('sam2_geojson_key') }}">
              <button class="button" style="margin-top:10px" type="submit">Registrar capa en SIGMAD</button>
            </form>
          @else
            <div class="info"><span class="badge">Capa de propuesta registrada</span><p><b>Modelo:</b> {{ data_get($sam->metadata,'model','SAM 2') }}</p><p><b>Ejecución:</b> {{ data_get($sam->metadata,'run','—') }}</p>Sin sustitución de geometrías ni confirmación de campo.</div>
            <form method="POST" action="{{ route('analysis.sam2.register',['analysisUuid'=>$job->uuid]) }}">@csrf
              <div class="subhead">Cambiar a otra ejecución</div><input type="text" required name="sam2_geojson_key" placeholder="Clave R2 de otra ejecución">
              <button type="submit" class="button" style="margin-top:10px">Registrar otra ejecución</button>
            </form>
          @endif
          <p style="font-size:11px;color:var(--muted);margin-top:16px">Los 376 IDs originales continúan vigentes. Las propuestas SAM son una capa independiente y no alimentan automáticamente el inventario ni los modelos volumétricos.</p>
        </div>
      </section>
    </div>
  </div>
  <div class="foot">SIGMAD · Método V0.7E + SAM2-R1 · Geometrías experimentales sin validación generalizada · EPSG {{ $mosaic->crs }}</div>
  <script src="https://cdn.jsdelivr.net/npm/proj4@2.15.0/dist/proj4.js"></script>
  <script src="https://cdn.jsdelivr.net/npm/geotiff@3.1.0-beta.0/dist-browser/geotiff.js"></script>
  <script src="https://cdn.jsdelivr.net/npm/ol@10.10.0/dist/ol.js"></script>
  <script>
  (() => {
    'use strict';
    const BOUNDS = @json($bounds);
    const CRS = @json($mosaic->crs);
    const BASE_URL = @json(route('analysis.map.geojson',['analysisUuid'=>$job->uuid]));
    const SAM_URL = @json(route('analysis.sam2.geojson',['analysisUuid'=>$job->uuid]));
    const SAM_PRESENT = @json($sam !== null);
    const PREVIEW_URL = @json(route('analysis.map.preview',['analysisUuid'=>$job->uuid]));
    const COG_URL = @json(route('analysis.map.cog-url',['analysisUuid'=>$job->uuid]));
    const PREVIEW_AVAILABLE = @json($previewAvailable);
    const COG_AVAILABLE = @json($cogAvailable);
    const el=id=>document.getElementById(id);
    const escapeHtml=x=>String(x??'—').replace(/[&<>"']/g,c=>({'&':'&amp;','<':'&lt;','>':'&gt;','"':'&quot;',"'":'&#39;'}[c]));
    const proj=/^EPSG:(326|327)(\d{2})$/.exec(CRS);
    if(proj){proj4.defs(CRS,`+proj=utm +zone=${Number(proj[2])} ${proj[1]==='327'?'+south ':''}+datum=WGS84 +units=m +no_defs +type=crs`);ol.proj.proj4.register(proj4);}
    const projection = ol.proj.get(CRS);
    if(!projection){el('mapStatus').textContent=`CRS no registrado: ${CRS}`;return;}
    const baseSource=new ol.source.Vector();const samSource=new ol.source.Vector();
    const baseStyle=new ol.style.Style({stroke:new ol.style.Stroke({color:'#31e6a0',width:1.75}),fill:new ol.style.Fill({color:'rgba(49,230,160,.055)'})});
    const samStyle=new ol.style.Style({stroke:new ol.style.Stroke({color:'#48b9ff',width:2.1,lineDash:[6,4]}),fill:new ol.style.Fill({color:'rgba(72,185,255,.045)'})});
    const baseLayer=new ol.layer.Vector({source:baseSource,style:baseStyle,zIndex:10});
    const samLayer=new ol.layer.Vector({source:samSource,style:samStyle,zIndex:12,visible:SAM_PRESENT});
    const previewLayer=PREVIEW_AVAILABLE?new ol.layer.Image({source:new ol.source.ImageStatic({url:PREVIEW_URL,imageExtent:BOUNDS,projection}),zIndex:0}):null;
    const layers=[...(previewLayer?[previewLayer]:[]),baseLayer,samLayer];
    const map=new ol.Map({target:'map',layers,view:new ol.View({projection,center:ol.extent.getCenter(BOUNDS),zoom:19}),controls:ol.control.defaults.defaults().extend([new ol.control.ScaleLine({units:'metric'})])});
    map.getView().fit(BOUNDS,{padding:[40,40,40,40],duration:0});
    let rasterLayer=null;
    async function cog(){
      if(!COG_AVAILABLE || !ol.source.GeoTIFF || !ol.layer.WebGLTile)return;
      try{
        const req=await fetch(COG_URL,{credentials:'same-origin'});if(!req.ok)throw new Error(`HTTP ${req.status}`);
        const obj=await req.json();if(!obj.url)throw new Error('Sin URL');
        const src=new ol.source.GeoTIFF({sources:[{url:obj.url}],projection,convertToRGB:'auto',normalize:true,interpolate:true,wrapX:false});
        rasterLayer=new ol.layer.WebGLTile({source:src,zIndex:1});map.addLayer(rasterLayer);
        src.on('change',()=>{if(src.getState()==='ready'&&previewLayer)previewLayer.setVisible(false);if(src.getState()==='error'){rasterLayer.setVisible(false);if(previewLayer)previewLayer.setVisible(true);}});
      }catch(e){console.warn('COG no disponible, preview activo:',e);}
    }
    cog();
    const byId=new Map();
    const samById=new Map();
    function readFeatures(json){return new ol.format.GeoJSON().readFeatures(json,{dataProjection:'EPSG:4326',featureProjection:projection});}
    async function load(url,src){const res=await fetch(url,{credentials:'same-origin',headers:{'Accept':'application/geo+json'}});if(!res.ok)throw new Error(`HTTP ${res.status}`);const out=readFeatures(await res.json());src.addFeatures(out);return out;}
    function objId(f){return String(f.get('operational_id')||f.getId()||'').trim();}
    const num=x=>Number.isFinite(Number(x))?Number(x).toLocaleString('es-MX',{maximumFractionDigits:3}):'—';
    let selectedId=null;
    function show(id){
      selectedId=id;const a=byId.get(id);const b=samById.get(id);if(!a&&!b)return;
      const p=a?a.getProperties():{};const q=b?b.getProperties():{};
      let html=`<div class="subhead">Candidato ${escapeHtml(id)}</div>`;
      const lines=[['Modelo original',p.primary_model],['Área V0.7E',`${num(p.area_m2)} m²`]];
      if(b)lines.push(['Área original (cálculo)',`${num(q.area_original_m2)} m²`],['Área SAM2',`${num(q.area_sam_m2)} m²`],['Cambio de área',`${num(q.area_change_pct)} %`],['IoU SAM vs original',num(q.sam2_vs_original_iou)],['Score interno SAM',num(q.sam2_internal_score)],['QA',q.sam2_status]);
      else lines.push(['SAM2','No propuesta para este ID']);
      for(const [k,v] of lines)html+=`<div class="kv"><span>${escapeHtml(k)}</span><strong>${escapeHtml(v)}</strong></div>`;
      html+='<div class="info">IoU entre modelos ≠ exactitud de campo. Ninguna máscara SAM2 sustituye automáticamente la capa original.</div>';
      el('inspector').innerHTML=html;
      document.querySelectorAll('.item').forEach(e=>e.classList.toggle('active',e.dataset.id===id));
      let f=b||a;if(f?.getGeometry())map.getView().fit(f.getGeometry().getExtent(),{padding:[115,110,115,110],maxZoom:23,duration:240});
    }
    function drawList(){
      const query=el('search').value.toLowerCase().trim();const entries=[...byId.keys()].filter(id=>id.toLowerCase().includes(query));
      el('list').innerHTML='';const fragment=document.createDocumentFragment();
      for(const id of entries.slice(0,180)){
        const b=samById.get(id),a=byId.get(id);const p=a?.getProperties()||{};
        const btn=document.createElement('button');btn.type='button';btn.className='item';btn.dataset.id=id;
        btn.innerHTML=`<b>${escapeHtml(id)}</b><span>${escapeHtml(p.primary_model||'Original')} · ${escapeHtml(num(p.area_m2))} m²</span> ${b?'<span class="status">· SAM 2 disponible</span>':''}`;
        btn.addEventListener('click',()=>show(id));fragment.appendChild(btn);
      }
      el('list').appendChild(fragment);
      if(entries.length>180){const msg=document.createElement('div');msg.className='info';msg.textContent=`Mostrando 180 de ${entries.length} resultados. Filtra por ID para continuar.`;el('list').appendChild(msg);}
      if(!entries.length)el('list').textContent='Sin coincidencias';
    }
    async function initialize(){
      try{
        const original=await load(BASE_URL,baseSource);for(const f of original){const id=objId(f);if(id)byId.set(id,f);}
        if(SAM_PRESENT){const enhanced=await load(SAM_URL,samSource);for(const f of enhanced){const id=objId(f);if(id)samById.set(id,f);}}
        el('mapStatus').textContent=`${byId.size} objetos originales · ${samById.size} propuestas SAM 2 · capa intacta`;
        drawList();
        if(original.length){const extent=baseSource.getExtent();map.getView().fit(extent,{padding:[60,60,60,60],maxZoom:21,duration:0});}
      }catch(err){el('mapStatus').textContent=`Error cargando capas: ${err.message}`;console.error(err);}
    }
    initialize();
    el('baseToggle').addEventListener('change',e=>baseLayer.setVisible(e.target.checked));
    el('samToggle').addEventListener('change',e=>samLayer.setVisible(e.target.checked));
    el('rasterToggle').addEventListener('change',e=>{if(rasterLayer)rasterLayer.setVisible(e.target.checked);if(previewLayer)previewLayer.setVisible(e.target.checked&&(!rasterLayer||rasterLayer.getSource().getState()!=='ready'));});
    el('fit').addEventListener('click',()=>map.getView().fit(BOUNDS,{padding:[40,40,40,40],duration:200}));
    el('focus').addEventListener('click',()=>{if(baseSource.getFeatures().length)map.getView().fit(baseSource.getExtent(),{padding:[50,50,50,50],duration:200});});
    el('search').addEventListener('input',drawList);
    map.on('singleclick',e=>{let id=null;map.forEachFeatureAtPixel(e.pixel,f=>{id=objId(f);return f;},{layerFilter:ly=>ly===baseLayer||ly===samLayer,hitTolerance:6});if(id)show(id);});
    window.addEventListener('resize',()=>map.updateSize());
  })();
  </script>
</body>
</html>
