<!DOCTYPE html>
<html lang="id" data-theme="light">
<head>
  <meta charset="UTF-8">
  <meta name="viewport" content="width=device-width, initial-scale=1.0, maximum-scale=1.0, user-scalable=yes">
  <meta name="robots" content="noindex, nofollow">
  <meta http-equiv="Cache-Control" content="no-cache, no-store, must-revalidate">
  <meta http-equiv="Pragma" content="no-cache">
  <meta http-equiv="Expires" content="0">
  <title><?= ($pageTitle ?? 'Dashboard') . ' — ' . APP_NAME . ' Admin' ?></title>
  
  <!-- PWA Meta Tags -->
  <link rel="manifest" href="<?= APP_URL ?>/manifest.json">
  <meta name="theme-color" content="#20bc95">
  <link rel="apple-touch-icon" href="<?= APP_URL ?>/assets/img/icon-192.png">
  <meta name="apple-mobile-web-app-capable" content="yes">
  <meta name="apple-mobile-web-app-status-bar-style" content="black-translucent">

  <link rel="icon" href="<?= ASSETS_URL ?>img/favicon.ico" type="image/x-icon">

  <!-- Font Awesome -->
  <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.5.0/css/all.min.css">

  <!-- Main CSS (harus pertama) -->
  <link rel="stylesheet" href="<?= ASSETS_URL ?>css/main.css?v=<?= APP_VERSION ?>">
  
  <!-- Admin Layout CSS -->
  <link rel="stylesheet" href="<?= ASSETS_URL ?>css/layouts/admin.css?v=<?= APP_VERSION ?>">
  
  <!-- Admin Responsive CSS (paling akhir biar override) -->
  <link rel="stylesheet" href="<?= ASSETS_URL ?>css/layouts/admin-responsive.css?v=<?= APP_VERSION ?>">
  <link rel="stylesheet" href="<?= ASSETS_URL ?>css/base/variables.css?v=<?= APP_VERSION ?>">
<link rel="stylesheet" href="<?= ASSETS_URL ?>css/base/reset.css?v=<?= APP_VERSION ?>">
<link rel="stylesheet" href="<?= ASSETS_URL ?>css/base/typography.css?v=<?= APP_VERSION ?>">

<!-- Components CSS -->
<link rel="stylesheet" href="<?= ASSETS_URL ?>css/components/buttons.css?v=<?= APP_VERSION ?>">
<link rel="stylesheet" href="<?= ASSETS_URL ?>css/components/cards.css?v=<?= APP_VERSION ?>">
<link rel="stylesheet" href="<?= ASSETS_URL ?>css/components/forms.css?v=<?= APP_VERSION ?>">
<link rel="stylesheet" href="<?= ASSETS_URL ?>css/components/modal.css?v=<?= APP_VERSION ?>">
<link rel="stylesheet" href="<?= ASSETS_URL ?>css/components/toast.css?v=<?= APP_VERSION ?>">
<link rel="stylesheet" href="<?= ASSETS_URL ?>css/components/pagination.css?v=<?= APP_VERSION ?>">
<link rel="stylesheet" href="<?= ASSETS_URL ?>css/components/tables.css?v=<?= APP_VERSION ?>">

  <?= $extraCss ?? '' ?>
</head>
<body>
<div class="toast-container"></div>
<script src="<?= ASSETS_URL ?>js/main.js?v=<?= APP_VERSION ?>"></script>
<script>
// Apply theme early to prevent flash
(function() {
  const t = localStorage.getItem('andalan_theme') || (window.matchMedia('(prefers-color-scheme: dark)').matches ? 'dark' : 'light');
  document.documentElement.setAttribute('data-theme', t);
})();
</script>