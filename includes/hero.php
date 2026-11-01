<?php
// includes/hero.php — partial hero, isi $heroBgImage/$heroLogo/$heroTitle/dst sebelum include kalau mau override default

$heroBgImage   = $heroBgImage   ?? "url('" . ASSETS_URL . "img/mgng1.jpeg')";
$heroSlides    = $heroSlides    ?? [
    ASSETS_URL . 'img/mgng1.jpeg',
    ASSETS_URL . 'img/mgng2.jpeg',
    ASSETS_URL . 'img/mgng3.jpeg',
];
$heroLogo      = $heroLogo      ?? ASSETS_URL . 'img/logo-hero.png';
$heroTitle     = $heroTitle     ?? null;
$heroBreadcrumb = $heroBreadcrumb ?? '';
$heroTagline   = $heroTagline   ?? ($cp['tagline'] ?? 'Industri Readymix dan Precast');
$heroBody      = $heroBody      ?? '';
$heroActions   = $heroActions   ?? [];
?>

<style>
:root { --hero-pad-left: clamp(24px, 8vw, 120px); }
</style>

<!-- HERO -->
<section class="hero">

  <div class="hero-slides" id="hero-slides">
    <?php foreach ($heroSlides as $i => $slideUrl): ?>
      <div class="hero-slide <?= $i === 0 ? 'active' : '' ?>" style="background-image:url('<?= $slideUrl ?>');"></div>
    <?php endforeach; ?>
  </div>

  <div class="hero-content" style="grid-template-columns: 1fr !important; text-align:center;">
    <div data-animate>
      <?php if ($heroBreadcrumb): ?>
        <?= $heroBreadcrumb ?>
      <?php endif; ?>

      <?php if ($heroTitle): ?>
        <h1>
          <?= htmlspecialchars($heroTitle['pre'] ?? '') ?>
          <?php if (!empty($heroTitle['break'])): ?><br><?php endif; ?>
          <span style="display:inline-block;<?php if (!empty($heroTitle['indent'])): ?> margin-left:<?= (int)$heroTitle['indent'] ?>px;<?php endif; ?>"><?= htmlspecialchars($heroTitle['highlight'] ?? '') ?></span>
        </h1>
      <?php else: ?>
        <img src="<?= $heroLogo ?>" alt="<?= htmlspecialchars($cp['company_name'] ?? APP_NAME) ?>"
     style="max-width:100%;height:auto;max-height:100px;display:block;margin:0 auto 16px;">
      <?php endif; ?>

      <?php if ($heroTagline): ?>
      <p style="font-size:16px;font-weight:600;color:rgba(255,255,255,0.85);max-width:700px;margin:0 0 14px;">
        <?= htmlspecialchars($heroTagline) ?>
      </p>
      <?php endif; ?>

      <?= $heroBody ?>

      <?php if (!empty($heroActions)): ?>
      <div class="hero-actions" style="justify-content:center;">
        <?php foreach ($heroActions as $action): ?>
        <a href="<?= $action['url'] ?>" class="btn btn-lg <?= $action['class'] ?? '' ?>" style="<?= $action['style'] ?? '' ?>">
          <?php if (!empty($action['icon'])): ?><i class="<?= $action['icon'] ?>"></i><?php endif; ?>
          <?= htmlspecialchars($action['label']) ?>
        </a>
        <?php endforeach; ?>
      </div>
      <?php endif; ?>
    </div>
  </div>
</section>