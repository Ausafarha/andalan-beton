const CACHE_NAME = 'andalan-beton-v2.0.2';

// File aset statis yang aman di-cache
const urlsToCache = [
  '/assets/css/main.css',
  '/assets/js/main.js',
  '/assets/css/layouts/admin.css',
  '/assets/css/layouts/admin-responsive.css',
  '/assets/img/icon-192.png',
  '/assets/img/icon-512.png'
];

// Install Service Worker
self.addEventListener('install', event => {
  event.waitUntil(
    caches.open(CACHE_NAME)
      .then(cache => {
        return cache.addAll(urlsToCache);
      })
      .then(() => self.skipWaiting())
  );
});

// Activate - Hapus cache versi lama secara otomatis
self.addEventListener('activate', event => {
  event.waitUntil(
    caches.keys().then(cacheNames => {
      return Promise.all(
        cacheNames
          .filter(name => name !== CACHE_NAME)
          .map(name => caches.delete(name))
      );
    }).then(() => self.clients.claim())
  );
});

// Fetch Handling
self.addEventListener('fetch', event => {
  const request = event.request;
  
  // 1. Abaikan Non-GET request (POST, PUT, DELETE)
  if (request.method !== 'GET') return;

  // 2. Strategi Network First untuk Dokumen / Halaman PHP / PWA Route
  if (request.mode === 'navigate' || request.headers.get('accept')?.includes('text/html')) {
    event.respondWith(
      fetch(request)
        .catch(() => caches.match('/index.php'))
    );
    return;
  }

  // 3. Strategi Cache First untuk Aset Statis (CSS, JS, Gambar)
  event.respondWith(
    caches.match(request).then(cachedResponse => {
      if (cachedResponse) {
        return cachedResponse;
      }
      return fetch(request).then(networkResponse => {
        if (networkResponse && networkResponse.status === 200 && networkResponse.type === 'basic') {
          const responseToCache = networkResponse.clone();
          caches.open(CACHE_NAME).then(cache => {
            cache.put(request, responseToCache);
          });
        }
        return networkResponse;
      });
    })
  );
});