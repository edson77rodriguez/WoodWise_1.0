<!doctype html>
<html lang="es">
<head>
<meta charset="utf-8"><meta name="viewport" content="width=device-width,initial-scale=1">
<meta name="csrf-token" content="{{ csrf_token() }}">
<title>SIGMAD · E2 + YOLO + SAM automático</title>
<style>
:root{font-family:system-ui,Arial,sans-serif;color:#173a2a;background:#f5f8f6}
body{margin:0}header{padding:22px 5%;background:#103d2c;color:white}
main{max-width:1050px;margin:24px auto;padding:0 16px}.panel{background:white;border:1px solid #dcebe2;border-radius:14px;padding:24px;margin-bottom:15px}
button,select{padding:11px;border-radius:8px;font-size:14px}button{border:0;background:#176b4d;color:white;cursor:pointer}button:disabled{opacity:.55;cursor:wait}
select{border:1px solid #bad0c3;width:100%}small,.muted{color:#68786d}a{color:#176b4d}#status{white-space:pre-wrap;padding:14px 0}
li{padding:5px 0}progress{width:100%;height:20px} .notice{background:#fff5db;border:1px solid #efdbad;border-radius:8px;padding:12px}
</style>
</head>
<body>
<header><h2>SIGMAD V0.9U · YOLO-Seg + Mask R-CNN E2 + SAM automático</h2></header>
<main>
<p><a href="{{ route('analysis.index') }}">Volver a análisis UAV</a></p>
<section class="panel">
<h3>Ejecutar análisis</h3>
<p class="muted">Un solo FastAPI y una sola URL. La inferencia se ejecuta fuera de la petición HTTP. SAM se aplica automáticamente a las copas elegibles.</p>
<p class="notice">Versión experimental, aún no validada con campo. No inicia otro trabajo del mismo mosaico mientras exista uno V0.9U activo.</p>
<label for="mosaic">Ortomosaico</label>
<select id="mosaic">
@forelse($mosaics as $mosaic)
<option value="{{ $mosaic->uuid }}">{{ $mosaic->original_name }} — {{ $mosaic->uuid }}</option>
@empty
<option value="">No hay mosaicos disponibles</option>
@endforelse
</select>
<p><button id="start" @if($mosaics->isEmpty()) disabled @endif>Ejecutar V0.9U (SAM automático)</button></p>
<progress id="bar" value="0" max="100"></progress>
<div id="status" role="status" aria-live="polite">Seleccione el ortomosaico e inicie cuando el FastAPI unificado esté activo.</div>
<div id="files"></div>
</section>
<section class="panel">
<h3>Trabajos V0.9U anteriores</h3>
<ul>
@forelse($jobs as $job)
<li><b>{{ $job->mosaic?->original_name }}</b> — {{ $job->status }} ({{ $job->progress }} %)
<button type="button" class="resume" data-url="{{ route('analysis.v09u.status',['analysisUuid'=>$job->uuid]) }}">Consultar</button>
@if($job->status==='completed') <a href="{{ route('analysis.map',['analysisUuid'=>$job->uuid]) }}">Abrir mapa</a> @endif
<small>{{ $job->uuid }}</small></li>
@empty
<li>No hay análisis V0.9U registrados en Laravel.</li>
@endforelse
</ul>
</section>
</main>
<script>
const csrf=document.querySelector('meta[name="csrf-token"]').content;
const run=document.getElementById('start'),stat=document.getElementById('status'),bar=document.getElementById('bar'),files=document.getElementById('files');
let timer=null;
async function req(url,opt={}) {
  const r=await fetch(url,{credentials:'same-origin',headers:{'Accept':'application/json','X-CSRF-TOKEN':csrf},...opt});
  const data=await r.json().catch(()=>({}));
  if(!r.ok)throw Error(data.message||('HTTP '+r.status));
  return data;
}
function show(data){
  bar.value=Number(data.progress||0);
  stat.textContent=`Estado: ${data.status} · Progreso: ${data.progress||0}% · Ventanas ${data.processed_tiles||0}/${data.total_tiles||0}\nUUID: ${data.analysis_uuid||''}`+(data.error_message?'\n'+data.error_message:'');
  files.replaceChildren();
  if(data.status==='completed'){
    if(data.map_url){let a=document.createElement('a');a.href=data.map_url;a.textContent='Abrir mapa de copas';files.append(a,document.createElement('br'));}
    for(const f of data.artifacts||[]){let a=document.createElement('a');a.href=f.download_url;a.textContent=f.filename;files.append(a,document.createElement('br'));}
  }
}
async function follow(url){
  clearTimeout(timer);
  try{
    const d=await req(url);
    show(d);
    if(d.status==='processing'||d.status==='queued')timer=setTimeout(()=>follow(url),7000);
    else run.disabled=false;
  }catch(e){stat.textContent='Consulta temporalmente no disponible: '+e.message+'\nPuede volver a consultar el mismo trabajo; no lo reinicie.';run.disabled=false;}
}
run.addEventListener('click',async()=>{
  if(!confirm('¿Iniciar un trabajo nuevo V0.9U?'))return;
  let id=document.getElementById('mosaic').value;if(!id)return;
  run.disabled=true;stat.textContent='Enviando solicitud…';
  try{
    const d=await req('/analisis-uav/mosaicos/'+encodeURIComponent(id)+'/v09u',{method:'POST'});
    await follow(d.status_url);
  }catch(e){stat.textContent='No se confirmó el inicio: '+e.message+'\nRevise trabajos recientes antes de reintentar.';run.disabled=false;}
});
document.querySelectorAll('.resume').forEach(b=>b.addEventListener('click',()=>follow(b.dataset.url)));
</script>
</body></html>
