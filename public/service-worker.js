const CACHE_NAME = 'hpe-system-v1';

// Only cache essential static files
const urlsToCache = [
  '/manifest.json',
  '/offline.html'
];

self.addEventListener('install', (event) => {
  event.waitUntil(
    caches.open(CACHE_NAME)
      .then((cache) => {
        // Add files one by one to handle missing files gracefully
        return Promise.allSettled(
          urlsToCache.map(url => 
            fetch(url)
              .then(response => {
                if (response.ok) {
                  return cache.put(url, response);
                }
              })
              .catch(err => {
                console.log(`Failed to cache ${url}:`, err);
                return null;
              })
          )
        );
      })
      .then(() => {
        return self.skipWaiting();
      })
      .catch(err => {
        console.log('Service worker install error (non-critical):', err);
      })
  );
});

self.addEventListener('fetch', (event) => {
  const url = new URL(event.request.url);
  
  // NEVER intercept navigation requests or API calls
  if (event.request.mode === 'navigate' || url.pathname.startsWith('/api/')) {
    return; // Let browser handle normally
  }

  // Only handle static assets (CSS, JS, images, etc.)
  if (event.request.method === 'GET' && 
      (url.pathname.endsWith('.css') || 
       url.pathname.endsWith('.js') || 
       url.pathname.endsWith('.png') || 
       url.pathname.endsWith('.jpg') ||
       url.pathname.endsWith('.json'))) {
    
    event.respondWith(
      caches.match(event.request)
        .then((response) => {
          if (response) {
            return response;
          }
          return fetch(event.request).then((response) => {
            if (response && response.status === 200) {
              const responseToCache = response.clone();
              caches.open(CACHE_NAME).then((cache) => {
                cache.put(event.request, responseToCache).catch(() => {});
              });
            }
            return response;
          });
        })
        .catch(() => {
          return fetch(event.request);
        })
    );
  }
});

self.addEventListener('activate', (event) => {
  event.waitUntil(
    caches.keys().then((cacheNames) => {
      return Promise.all(
        cacheNames.map((cacheName) => {
          if (cacheName !== CACHE_NAME) {
            return caches.delete(cacheName);
          }
        })
      );
    })
    .then(() => {
      return self.clients.claim();
    })
  );
});
