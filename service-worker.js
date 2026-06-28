const CACHE_NAME = 'andalan-beton-v1.0.0';

// File yang di-cache (statis)
const urlsToCache = [
  '/',
  '/profil.php',
  '/produk.php',
  '/galeri.php',
  '/kontak.php',
  '/pesan.php',
  '/assets/css/main.css',
  '/assets/js/main.js'
];

// Install Service Worker
self.addEventListener('install', event => {
  event.waitUntil(
    caches.open(CACHE_NAME)
      .then(cache => cache.addAll(urlsToCache))
      .then(() => self.skipWaiting())
  );
});

// Activate - hapus cache lama
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

// Fetch - ambil dari cache dulu, baru network
self.addEventListener('fetch', event => {
  event.respondWith(
    caches.match(event.request)
      .then(response => {
        // Kalo ada di cache, return dari cache
        if (response) {
          return response;
        }
        // Kalo gak ada, fetch dari network
        return fetch(event.request).then(response => {
          // Simpan ke cache buat next visit
          if (!response || response.status !== 200 || response.type !== 'basic') {
            return response;
          }
          const responseToCache = response.clone();
          caches.open(CACHE_NAME)
            .then(cache => {
              cache.put(event.request, responseToCache);
            });
          return response;
        });
      })
  );
});