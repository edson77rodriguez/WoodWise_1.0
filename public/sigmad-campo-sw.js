/* SIGMAD Campo V0.2.1 - Sólo recursos públicos estáticos y shell offline.
   Las misiones y observaciones científicas NO se guardan en CacheStorage. */
const CACHE = 'sigmad-campo-v0.2.1';
const ASSETS = [
  '/sigmad-campo-assets/offline.html',
  '/sigmad-campo-assets/campo.css',
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
    event.respondWith(caches.match(event.request).then(hit=>hit||fetch(event.request)));
  }
});
