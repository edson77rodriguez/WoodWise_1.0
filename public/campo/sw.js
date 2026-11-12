/* V0.1: solo recursos estáticos propios y shell offline, nunca respuestas de la API. */
const CACHE = 'sigmad-campo-v0.1.0';
const ASSETS = ['/campo/offline.html', '/campo/campo.css', '/campo/campo.js'];
self.addEventListener('install', e => {
  e.waitUntil(caches.open(CACHE).then(cache => cache.addAll(ASSETS)).then(() => self.skipWaiting()));
});
self.addEventListener('activate', e => {
  e.waitUntil(caches.keys().then(keys => Promise.all(keys.filter(key => key.startsWith('sigmad-campo-') && key !== CACHE).map(key => caches.delete(key)))).then(() => self.clients.claim()));
});
self.addEventListener('fetch', e => {
  const url = new URL(e.request.url);
  if (url.origin !== self.location.origin || e.request.method !== 'GET') return;
  if (e.request.mode === 'navigate' && url.pathname === '/campo/') {
    e.respondWith(fetch(e.request).catch(() => caches.match('/campo/offline.html')));
    return;
  }
  if (ASSETS.includes(url.pathname)) {
    e.respondWith(caches.match(e.request).then(hit => hit || fetch(e.request)));
  }
});
