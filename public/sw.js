const CACHE_NAME = 'resultados-pwa-v1';

// Recursos de UI puramente visuales y de red (jamás lógica PHP)
const STATIC_ASSETS = [
    // Aquí puedes incluir rutas fijas como fuentes o css base
    // Pero lo dejaremos vacío inicialmente ya que interceptaremos on-the-fly
];

self.addEventListener('install', (event) => {
    self.skipWaiting();
});

self.addEventListener('activate', (event) => {
    event.waitUntil(
        caches.keys().then((keys) => {
            return Promise.all(
                keys.filter((key) => key !== CACHE_NAME).map((key) => caches.delete(key))
            );
        })
    );
    self.clients.claim();
});

self.addEventListener('fetch', (event) => {
    const url = new URL(event.request.url);

    // ESTRATEGIA: Cachear SOLO archivos estáticos de assets (imágenes, css, js)
    // ESTRICTO: NO SE CACHEA NADA QUE RECIBA HTML O PHP para evitar resultados zombis
    if (
        url.pathname.includes('/assets/') || 
        url.pathname.endsWith('.png') || 
        url.pathname.endsWith('.jpg') || 
        url.pathname.endsWith('.css') || 
        url.pathname.endsWith('.js')
    ) {
        event.respondWith(
            caches.match(event.request).then((cachedResponse) => {
                const fetchPromise = fetch(event.request).then((networkResponse) => {
                    caches.open(CACHE_NAME).then((cache) => {
                        cache.put(event.request, networkResponse.clone());
                    });
                    return networkResponse;
                }).catch(() => null); // ignorar fallas offline

                return cachedResponse || fetchPromise;
            })
        );
    } else {
        // Red Pura para todo el tráfico de resultados, tablas, y vistas.
        // Si no hay red, fallará elegantemente con el dinosaurio / error offline del navegador (seguro).
        event.respondWith(fetch(event.request));
    }
});
