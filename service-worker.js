const CACHE_NAME = 'andalan-beton-v1.0.4'; // Naikkan versi cache

// Hanya cache file publik & asset statis!
// JANGAN masukkan halaman admin yang diproteksi session login ke dalam daftar ini.
const urlsToCache = [
  '/',
  '/profil.php',
  '/produk.php',
  '/galeri.php',
  '/kontak.php',
  '/pesan.php',
  '/assets/css/main.css',
  '/assets/js/main.js',
  '/admin-ab/login.php',
  '/assets/css/layouts/admin.css',
  '/assets/css/layouts/admin-responsive.css',
  '/assets/img/icon-192.png',
  '/assets/img/icon-512.png'
];

// Install Service Worker (Safe AddAll dengan error handler)
self.addEventListener('install', event => {
  event.waitUntil(
    caches.open(CACHE_NAME).then(async cache => {
      console.log('Caching assets...');
      // Mengunduh file satu per satu, jika 1 file gagal (404/redirect), file lain tidak ikut gagal
      await Promise.allSettled(
        urlsToCache.map(async url => {
          try {
            const response = await fetch(url);
            if (response.ok) {
              await cache.put(url, response);
            } else {
              console.warn(`[SW Cache Warning] Gagal memuat asset: ${url} (Status: ${response.status})`);
            }
          } catch (err) {
            console.warn(`[SW Cache Error] Tidak dapat mengunduh: ${url}`, err);
          }
        })
      );
    }).then(() => self.skipWaiting())
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

// ── FETCH + REDIRECT PWA ADMIN ──────────────────────────────
self.addEventListener('fetch', event => {
  const url = event.request.url;
  
  // Abaikan request non-GET (seperti POST upload/edit foto) agar tidak di-cache oleh SW
  if (event.request.method !== 'GET') return;

  // Redirect root ke admin login jika dari PWA
  if (url.includes('/?pwa=true') || 
      url === 'https://andalanbeton.com/' || 
      url === 'https://andalanbeton.com/?pwa=true' ||
      url === 'http://localhost:8000/' || 
      url === 'http://localhost:8000/?pwa=true') {
    event.respondWith(
      fetch('/admin-ab/login.php')
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