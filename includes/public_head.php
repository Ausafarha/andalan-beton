<?php
// includes/public_head.php
$cp = getCompanyProfile();
$pageTitle    = ($pageTitle ?? '') . ($pageTitle ? ' — ' : '') . htmlspecialchars($cp['company_name'] ?? APP_NAME);
$metaDesc     = $metaDesc ?? htmlspecialchars($cp['meta_description'] ?? '');
$currentSlug  = $currentSlug ?? '';
?>
<!DOCTYPE html>
<html lang="id" data-theme="light">
<head>
  <meta charset="UTF-8">
  <meta name="viewport" content="width=device-width, initial-scale=1.0">
  
  <!-- Meta SEO Dinamis -->
  <meta name="description" content="<?= !empty($metaDesc) ? $metaDesc : 'PT. MITRA ANDALAN BETON PANTURA menyediakan solusi beton ready mix dan precast berkualitas tinggi berstandar SNI untuk kebutuhan proyek konstruksi Anda.' ?>">
  <meta name="keywords" content="PT Mitra Andalan Beton Pantura, Andalan Beton, ready mix, precast, supplier beton, konstruksi pantura, andalanbeton.com">
  <title><?= $pageTitle ?></title>

  <!-- Schema Markup / Structured Data JSON-LD (Biar Google ngenalin nama PT & Brand Lu) -->
  <script type="application/ld+json">
  {
    "@context": "https://schema.org",
    "@type": "LocalBusiness",
    "name": "PT. MITRA ANDALAN BETON PANTURA",
    "alternateName": "Andalan Beton",
    "image": "<?= APP_URL ?>/assets/img/logo-hero.png",
    "url": "https://andalanbeton.com",
    "telephone": "<?= htmlspecialchars($cp['whatsapp'] ?? '') ?>",
    "priceRange": "$$",
    "address": {
      "@type": "PostalAddress",
      "streetAddress": "Jalur Pantura",
      "addressLocality": "Pantura",
      "addressRegion": "Jawa Tengah",
      "addressCountry": "ID"
    }
  }
  </script>

  <!-- PWA Manifest -->
  <link rel="manifest" href="<?= APP_URL ?>/manifest.json">
  <meta name="theme-color" content="#1e3a8a">
  <link rel="apple-touch-icon" href="<?= APP_URL ?>/assets/img/icon-192.png">
  <meta name="apple-mobile-web-app-capable" content="yes">
  <meta name="apple-mobile-web-app-status-bar-style" content="black-translucent">
  
  <link rel="icon" href="<?= ASSETS_URL ?>img/favicon.ico" type="image/x-icon">
  <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.5.0/css/all.min.css">
  <link rel="stylesheet" href="<?= ASSETS_URL ?>css/main.css?v=<?= APP_VERSION ?>">
  <link rel="stylesheet" href="<?= ASSETS_URL ?>css/layouts/public.css?v=<?= APP_VERSION ?>">
  <link rel="stylesheet" href="<?= ASSETS_URL ?>css/layouts/footer.css?v=<?= APP_VERSION ?>">
  <link rel="stylesheet" href="<?= ASSETS_URL ?>css/layouts/responsive.css?v=<?= APP_VERSION ?>">
  <link rel="stylesheet" href="<?= ASSETS_URL ?>css/pages/hero.css?v=<?= APP_VERSION ?>">
  <link rel="stylesheet" href="<?= ASSETS_URL ?>css/pages/product-card.css?v=<?= APP_VERSION ?>">
  <link rel="stylesheet" href="<?= ASSETS_URL ?>css/pages/feature.css?v=<?= APP_VERSION ?>">
  <link rel="stylesheet" href="<?= ASSETS_URL ?>css/pages/gallery.css?v=<?= APP_VERSION ?>">
  <script>(function(){const t=localStorage.getItem('andalan_theme')||'light';document.documentElement.setAttribute('data-theme',t);})();</script>
  <script src="<?= ASSETS_URL ?>js/main.js?v=<?= APP_VERSION ?>"></script>

  <!-- Pendaftaran Service Worker & Paksa Update Cache -->
  <script>
    if ('serviceWorker' in navigator) {
      window.addEventListener('load', function() {
        navigator.serviceWorker.register('<?= APP_URL ?>/sw.js').then(function(reg) {
          reg.update();
        });
      });
    }
  </script>
</head>
<body>
<div class="toast-container"></div>

<!-- Public Header -->
<header class="public-header" id="pub-header" style="display:flex; align-items:center; justify-content:space-between; width:100%; box-sizing:border-box; gap:12px; padding: 10px 16px;">
  
  <a href="<?= APP_URL ?>/" style="display:flex; align-items:center; gap:12px; text-decoration:none; flex:1; min-width:0;">
  <?php if (!empty($cp['logo'])): ?>
    <img src="<?= uploadUrl($cp['logo']) ?>" alt="Logo" style="height:40px; width:40px; object-fit:contain; border-radius:10px; flex-shrink:0;">
  <?php else: ?>
    <div style="width:40px; height:40px; background:linear-gradient(135deg,var(--brand-500),var(--brand-700)); border-radius:10px; display:flex; align-items:center; justify-content:center; font-weight:800; color:white; font-size:14px; flex-shrink:0;">AB</div>
  <?php endif; ?>
  <div style="min-width:0; display:flex; flex-direction:column;">
    <div style="font-size:14px; font-weight:800; color:var(--text-primary); line-height:1.3; white-space:normal; word-break:break-word;"><?=$cp['company_name'] ?? APP_NAME?></div>
    <div style="font-size:10px; color:var(--text-muted); margin-top:2px; white-space:normal; word-break:break-word;"><?= htmlspecialchars($cp['tagline'] ?? 'Industri Readymix dan Precast') ?></div>
  </div>
</a>

  <nav class="public-nav" id="pub-nav">
    <a href="<?= APP_URL ?>/index.php" class="<?= $currentSlug===''?'active':'' ?>">Beranda</a>
    <a href="<?= APP_URL ?>/profil.php" class="<?= $currentSlug==='profil'?'active':'' ?>">Profil</a>
    <a href="<?= APP_URL ?>/produk.php" class="<?= $currentSlug==='produk'?'active':'' ?>">Produk</a>
    <a href="<?= APP_URL ?>/galeri.php" class="<?= $currentSlug==='galeri'?'active':'' ?>">Galeri</a>
    <a href="<?= APP_URL ?>/kontak.php" class="<?= $currentSlug==='kontak'?'active':'' ?>">Kontak</a>
    <a href="<?= APP_URL ?>/pesan.php" class="btn btn-primary btn-sm" style="margin-left:8px;"><i class="fas fa-shopping-cart"></i> Pesan Sekarang</a>
    <button id="theme-toggle" class="topbar-btn" style="margin-left:4px;" title="Ganti Tema"><i class="fas fa-moon" id="theme-icon"></i></button>
  </nav>

  <div style="display:flex; gap:8px; align-items:center; flex-shrink:0;">
    <button class="topbar-btn nav-mobile-toggle" style="flex-shrink:0; padding:8px 12px;" onclick="document.getElementById('pub-nav').classList.toggle('open');">
        <i class="fas fa-bars"></i>
    </button>
  </div>
</header>