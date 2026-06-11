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
  <meta name="description" content="<?= $metaDesc ?>">
  <title><?= $pageTitle ?></title>
  <link rel="icon" href="<?= ASSETS_URL ?>img/favicon.ico" type="image/x-icon">
  <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.5.0/css/all.min.css">
  <link rel="stylesheet" href="<?= ASSETS_URL ?>css/main.css">
  <script>(function(){const t=localStorage.getItem('andalan_theme')||'light';document.documentElement.setAttribute('data-theme',t);})();</script>
</head>
<body>
<div class="toast-container"></div>

<!-- Public Header -->
<header class="public-header" id="pub-header">
  <a href="<?= APP_URL ?>/" style="display:flex;align-items:center;gap:14px;text-decoration:none;flex-shrink:0;">
  <?php if (!empty($cp['logo'])): ?>
    <img src="<?= uploadUrl($cp['logo']) ?>" alt="Logo" style="height:42px;width:42px;object-fit:contain;border-radius:10px;">
  <?php else: ?>
    <div style="width:42px;height:42px;background:linear-gradient(135deg,var(--brand-500),var(--brand-700));border-radius:10px;display:flex;align-items:center;justify-content:center;font-weight:800;color:white;font-size:16px;">AB</div>
  <?php endif; ?>
  <div>
    <div style="font-size:16px;font-weight:800;color:var(--text-primary);line-height:1.3;"><?= htmlspecialchars($cp['company_name'] ?? APP_NAME) ?></div>
    <div style="font-size:11px;color:var(--text-muted);margin-top:2px;"><?= htmlspecialchars($cp['tagline'] ?? 'Supplier Material Bangunan Terpercaya') ?></div>
  </div>
</a>

  <nav class="public-nav" id="pub-nav">
    <a href="<?= APP_URL ?>/" class="<?= $currentSlug===''?'active':'' ?>">Beranda</a>
    <a href="<?= APP_URL ?>/profil.php" class="<?= $currentSlug==='profil'?'active':'' ?>">Profil</a>
    <a href="<?= APP_URL ?>/produk.php" class="<?= $currentSlug==='produk'?'active':'' ?>">Produk</a>
    <a href="<?= APP_URL ?>/galeri.php" class="<?= $currentSlug==='galeri'?'active':'' ?>">Galeri</a>
    <a href="<?= APP_URL ?>/kontak.php" class="<?= $currentSlug==='kontak'?'active':'' ?>">Kontak</a>
    <a href="<?= APP_URL ?>/pesan.php" class="btn btn-primary btn-sm" style="margin-left:8px;"><i class="fas fa-shopping-cart"></i> Pesan Sekarang</a>
    <button id="theme-toggle" class="topbar-btn" style="margin-left:4px;" title="Ganti Tema"><i class="fas fa-moon" id="theme-icon"></i></button>
  </nav>

  <div style="margin-left:auto;display:flex;gap:8px;align-items:center;">
<button class="topbar-btn nav-mobile-toggle" onclick="document.getElementById('pub-nav').classList.toggle('open');">
    <i class="fas fa-bars"></i>
</button>
  </div>
</header>
