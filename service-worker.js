const CACHE_NAME = 'andalan-beton-v2.0.4';

// File aset statis publik saja
const urlsToCache = [
  '/assets/css/main.css',
  '/assets/js/main.js',
  '/assets/img/icon-192.png',
  '/assets/img/icon-512.png'
];

// Install Service Worker
self.addEventListener('install', event => {
  event.waitUntil(
    caches.open(CACHE_NAME)
      .then(cache => cache.addAll(urlsToCache))
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
  const url = new URL(request.url);

  // 1. PENTING: ABAIKAN SEMUA REQUEST DIREKTORI ADMIN!
  if (url.pathname.includes('/admin-ab/')) {
    return; // Biarkan browser berkomunikasi murni langsung dengan server PHP
  }

  // 2. Abaikan Request Non-GET (POST, PUT, DELETE)
  if (request.method !== 'GET') return;

  // 3. Strategi Network First untuk halaman publik
  if (request.mode === 'navigate' || request.headers.get('accept')?.includes('text/html')) {
    event.respondWith(
      fetch(request).catch(() => caches.match('/index.php'))
    );
    return;
  }

  // 4. Cache First untuk aset statis
  event.respondWith(
    caches.match(request).then(cachedResponse => {
      if (cachedResponse) return cachedResponse;
      return fetch(request);
    })
  );
});