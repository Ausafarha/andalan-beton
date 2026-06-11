<!DOCTYPE html>
<html lang="id" data-theme="light">
<head>
  <meta charset="UTF-8">
  <meta name="viewport" content="width=device-width, initial-scale=1.0, maximum-scale=1.0, user-scalable=yes">
  <meta name="robots" content="noindex, nofollow">
  <title><?= ($pageTitle ?? 'Dashboard') . ' — ' . APP_NAME . ' Admin' ?></title>
  <link rel="icon" href="<?= ASSETS_URL ?>img/favicon.ico" type="image/x-icon">

  <!-- Font Awesome -->
  <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.5.0/css/all.min.css">

  <!-- Main CSS (harus pertama) -->
  <link rel="stylesheet" href="<?= ASSETS_URL ?>css/main.css">
  
  <!-- Admin Layout CSS -->
  <link rel="stylesheet" href="<?= ASSETS_URL ?>css/layouts/admin.css">
  
  <!-- Admin Responsive CSS (paling akhir biar override) -->
  <link rel="stylesheet" href="<?= ASSETS_URL ?>css/layouts/admin-responsive.css">

  <?= $extraCss ?? '' ?>
</head>
<body>
<div class="toast-container"></div>

<script>
// Apply theme early to prevent flash
(function() {
  const t = localStorage.getItem('andalan_theme') || (window.matchMedia('(prefers-color-scheme: dark)').matches ? 'dark' : 'light');
  document.documentElement.setAttribute('data-theme', t);
})();
</script>