<!doctype html>
<html lang="es">
<head>
  <meta charset="utf-8">
  <meta name="viewport" content="width=device-width,initial-scale=1">
  <meta name="csrf-token" content="{{ csrf_token() }}">
  <title>SIGMAD | Piloto E2 + SAM 2.1</title>
  <style>
  :root{color-scheme:light;font-family:Inter,system-ui,Arial,sans-serif;background:#f5f8f6;color:#20352a}
  body{margin:0}header{padding:22px 5%;background:#113f2d;color:white}main{max-width:1050px;margin:28px auto;padding:0 20px}
  h1{margin:0;font-size:23px}header p{margin:8px 0 0;color:#cbe4d8}
  .panel{background:white;border:1px solid #deece3;border-radius:15px;padding:25px;box-shadow:0 4px 18px #153b2810;margin-bottom:16px}
  label{display:block;font-size:13px;font-weight:700;margin:16px 0 6px}select,button{padding:12px;border-radius:9px;font-size:14px}
  select{width:100%;border:1px solid #bbd2c5;background:white}button{border:0;background:#176b4d;color:#fff;cursor:pointer;margin-top:15px;font-weight:650}
  button:disabled{opacity:.5;cursor:wait}.sub{font-size:13px;color:#61766c;line-height:1.6}.warn{background:#fff8e7;padding:12px;border-radius:9px;border:1px solid #f5e1a9}
  #status{margin-top:18px;white-space:pre-wrap;line-height:1.7}a{color:#176b4d;font-weight:650}.nav{color:#d2eadd}
  </style>
</head>
<body>
<header><h1>SIGMAD · Análisis experimental E2 + SAM 2.1 Small</h1><p>Versión V0.8S · Inferencia en Google Colab · No validado frente a campo</p></header>
<main>
 <p><a href="{{ url('/analisis-uav') }}">Volver al análisis UAV</a></p>
 <div class="panel">
   <h2>Nuevo análisis forestal</h2>
   <p class="sub">Mask R-CNN E2 PILOTO + YOLO-Seg E1.1 detectan las copas y SAM 2.1 Small genera límites refinados. Esta ejecución produce un nuevo registro y nuevos archivos en R2, sin modificar V0.7E.</p>
   <p class="warn sub">El servidor V0.8S de Colab debe estar activo y su URL configurada en Hostinger. El entrenamiento E2 usa validación interna; una copa segmentada todavía requiere comprobación científica.</p>
   <label for="mosaic">Selecciona un ortomosaico disponible</label>
   <select id="mosaic">
    @forelse($mosaics as $mosaic)
     <option value="{{ $mosaic->uuid }}">{{ $mosaic->original_name }} · {{ $mosaic->project->name }} · {{ $mosaic->uuid }}</option>
    @empty
     <option value="">No hay ortomosaicos listos</option>
    @endforelse
   </select>
   <button type="button" id="run" @if($mosaics->isEmpty()) disabled @endif>Ejecutar nuevo análisis V0.8S</button>
   <div id="status" role="status" aria-live="polite"></div>
 </div>
</main>
<script>
const token=document.querySelector('meta[name="csrf-token"]').content;
const run=document.getElementById('run');const stat=document.getElementById('status');
const WAIT=8000;let busy=false;
async function request(url,opt={}){
  const rsp=await fetch(url,{credentials:'same-origin',headers:{'Accept':'application/json','X-CSRF-TOKEN':token,...(opt.headers||{})},...opt});
  const data=await rsp.json().catch(()=>({}));
  if(!rsp.ok){throw new Error(data.message||('HTTP '+rsp.status));}
  return data;
}
function statusText(s){stat.textContent=s;}
async function refresh(url){
  try{
    const r=await request(url);
    if(r.status==='completed'){
      statusText('Análisis completado. Abre el mapa para revisar copas y procedencias.');
      if(r.map_url){const a=document.createElement('a');a.href=r.map_url;a.textContent='Ver mapa V0.8S';stat.appendChild(document.createTextNode(' '));stat.appendChild(a);}
      run.disabled=false;busy=false;return;
    }
    if(r.status==='failed'){statusText('Error en el análisis: '+(r.error_message||'revisa logs de Colab'));run.disabled=false;busy=false;return;}
    statusText('Procesando en Colab. El estado se actualizará automáticamente mientras permanezca abierta esta página.');
    setTimeout(()=>refresh(url),WAIT);
  }catch(err){statusText('No fue posible consultar el estado: '+err.message+'\nVerifica si Colab sigue activo.');run.disabled=false;busy=false;}
}
run.addEventListener('click',async()=>{
  if(busy)return;const id=document.getElementById('mosaic').value;if(!id)return;
  busy=true;run.disabled=true;statusText('Enviando análisis a Colab…');
  try{
    const r=await request('/analisis-uav/mosaicos/'+encodeURIComponent(id)+'/v08s',{method:'POST'});
    statusText('Solicitud aceptada. UUID: '+r.analysis_uuid);
    await refresh(r.status_url);
  }catch(err){statusText('No se pudo iniciar: '+err.message);busy=false;run.disabled=false;}
});
</script>
</body></html>
