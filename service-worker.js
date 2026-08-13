const CACHE_NAME = 'andalan-beton-v2.0.1';

// File yang di-cache (statis)
const urlsToCache = [
  '/',
  '/profil.php',
  '/produk.php',
  '/galeri.php',
  '/kontak.php',
  '/pesan.php',
  '/assets/css/main.css',
  '/assets/js/main.js',
  // ADMIN
  '/admin-ab/dashboard.php',
  '/admin-ab/login.php',
  '/admin-ab/modules/material/index.php',
  '/admin-ab/modules/stock_in/index.php',
  '/admin-ab/modules/stock_out/index.php',
  '/admin-ab/modules/orders/index.php',
  '/admin-ab/modules/reports/index.php',
  '/admin-ab/modules/gallery/index.php',
  '/admin-ab/modules/settings/index.php',
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
        console.log('Caching assets...');
        return cache.addAll(urlsToCache);
      })
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

// ── FETCH + REDIRECT (SUDAH DIPERBAIKI) ──
self.addEventListener('fetch', event => {
  const url = event.request.url;
  
  // Redirect root ke INDEX.PHP (bukan login admin!)
  if (url.includes('/?pwa=true') || 
      url === 'https://andalanbeton.com/' || 
      url === 'https://andalanbeton.com/?pwa=true' ||
      url === 'http://localhost:8000/' || 
      url === 'http://localhost:8000/?pwa=true') {
    event.respondWith(
      fetch('/index.php')  // ← SEKARANG KE BERANDA
    );
    return;
  }
  
  event.respondWith(
    caches.match(event.request)
      .then(response => {
        if (response) {
          return response;
        }
        return fetch(event.request).then(response => {
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