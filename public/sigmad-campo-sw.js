/* SIGMAD Campo V0.6.1: cachea SOLO los recursos estáticos del frontend.
   No elimina IndexedDB; no intercepta rutas de misiones, medidas o teselas privadas. */
const CACHE = 'sigmad-campo-v0.6.1';
const ASSETS = [
  '/sigmad-campo-assets/offline.html',
  '/sigmad-campo-assets/campo.css',
  '/sigmad-campo-assets/campo-raster-integridad.js',
  '/sigmad-campo-assets/campo.js',
  '/sigmad-campo-assets/campo-mapa.js',
  '/sigmad-campo-assets/vendor/ol.css',
  '/sigmad-campo-assets/vendor/ol.js',
  '/sigmad-campo-assets/vendor/proj4.js',
];
self.addEventListener('install', event => {
  event.waitUntil(caches.open(CACHE).then(cache=>cache.addAll(ASSETS)).then(()=>self.skipWaiting()));
});
self.addEventListener('activate', event => {
  event.waitUntil(caches.keys().then(keys=>Promise.all(keys
    .filter(key=>key.startsWith('sigmad-campo-')&&key!==CACHE)
    .map(key=>caches.delete(key)))).then(()=>self.clients.claim()));
});
self.addEventListener('fetch', event => {
  const url=new URL(event.request.url);
  if(url.origin!==self.location.origin||event.request.method!=='GET')return;
  if(event.request.mode==='navigate'&&(url.pathname==='/campo'||url.pathname==='/campo/')){
    event.respondWith(fetch(event.request).catch(()=>caches.match('/sigmad-campo-assets/offline.html')));
    return;
  }
  if(ASSETS.includes(url.pathname)){
    // El HTML añade ?v=0610 para actualizar navegadores sin SW.
    // Buscar por ruta hace que la misma versión precargada sirva también offline.
    event.respondWith(caches.open(CACHE).then(cache=>cache.match(url.pathname))
      .then(hit=>hit||fetch(event.request)));
  }
});
