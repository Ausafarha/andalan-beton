<?php
require_once __DIR__ . '/config/database.php';
require_once __DIR__ . '/config/app.php';
initSession();

$currentSlug = 'galeri';
$pageTitle   = 'Galeri';

$cat     = get('kategori');
$params  = ['1=1'];
$pArr    = [];

if ($cat) { $params[] = "g.category = ?"; $pArr[] = $cat; }

$where   = implode(' AND ', $params);
$gallery = Database::fetchAll("SELECT * FROM gallery WHERE is_active=true ORDER BY id DESC");

// Kategori + jumlah foto per kategori
$cats = Database::fetchAll("SELECT category, COUNT(*) AS total FROM gallery WHERE is_active=true GROUP BY category ORDER BY category");
$allCount = array_sum(array_column($cats, 'total'));

// Ikon per kategori (kategori lain otomatis pakai fa-folder)
$catIcons = [
  'proyek' => 'fa-hard-hat',
  'umum'   => 'fa-images',
];

include __DIR__ . '/includes/public_head.php';

$heroTitle = [
  'pre'       => 'GALERI PROYEK & ',
  'highlight' => 'FASILITAS',
  'break'     => true,
];
$heroTagline = '';
$heroBody = '
      <p style="color:rgba(255,255,255,0.85);font-size:15px;line-height:1.8;max-width:600px;margin:0;">
        Dokumentasi proyek dan fasilitas PT Mitra Andalan Beton Pantura.
      </p>';
?>
<style>
  
  #galeri-page .hero { justify-content: center !important; }
  #galeri-page .hero-content {
    margin: 0 auto !important;
    max-width: 700px !important;
    width: 100%;
    padding-left: 24px !important;
    padding-right: 24px !important;
    text-align: center !important;
  }
  #galeri-page .hero-content h1,
  #galeri-page .hero-content p {
    margin-left: auto !important;
    margin-right: auto !important;
    text-align: center !important;
  }
  #galeri-page .hero h1 { font-size: clamp(24px, 4vw, 36px) !important; }

  /* ===== SECTION GALERI ===== */
  .section-galeri-list {
    position: relative;
    background-image: url('<?= ASSETS_URL ?>img/background.png');
    background-size: cover;
    background-position: center top;
    background-repeat: repeat-y;
    background-color: #ffffff;
    overflow: hidden;
  }

  .section-galeri-list::before {
    content: '';
    position: absolute;
    inset: 0;
    background: rgba(133, 133, 132, 0);
    z-index: 0;
    pointer-events: none;
  }

  .section-galeri-list > .container {
    position: relative;
    z-index: 1;
  }

  .gallery-item {
    box-shadow: 0 8px 20px rgba(0, 0, 0, 0.6);
  }

  [data-theme="dark"] .section-galeri-list {
    background-color: #061C01;
  }

  [data-theme="dark"] .section-galeri-list::before {
    background: rgba(6, 28, 1, 0.6);
  }
</style>

<div id="galeri-page">
<?php include __DIR__ . '/includes/hero.php'; ?>

<!-- KOTAK PUTIH BACKGROUND UTAMA -->
<div style="background: #ffffff; padding: 30px 0; position: relative; z-index: 2;">

  <!-- Category Filter -->
  <div class="galeri-kategori-wrap" style="background: transparent;">
    <div class="container">
      <div class="galeri-kategori-pill">

        <!-- Semua -->
        <a href="<?= APP_URL ?>/galeri.php"
           class="galeri-filter-btn <?= !$cat ? 'active' : '' ?>"
           <?= !$cat ? 'aria-current="page"' : '' ?>>
          <span class="gf-icon"><i class="fas fa-th-large"></i></span>
          <span class="gf-text">
            <strong>Semua</strong>
            <small><?= $allCount ?> Foto</small>
          </span>
          <i class="fas fa-arrow-right gf-arrow"></i>
        </a>

        <?php foreach ($cats as $c):
          $cLabel = ucfirst($c['category']);
          $isA    = $cat === $c['category'];
          $icon   = $catIcons[strtolower($c['category'])] ?? 'fa-folder';
        ?>
        <a href="?kategori=<?= urlencode($c['category']) ?>"
           class="galeri-filter-btn <?= $isA ? 'active' : '' ?>"
           <?= $isA ? 'aria-current="page"' : '' ?>>
          <span class="gf-icon"><i class="fas <?= $icon ?>"></i></span>
          <span class="gf-text">
            <strong><?= htmlspecialchars($cLabel) ?></strong>
            <small><?= (int)$c['total'] ?> Foto</small>
          </span>
          <i class="fas fa-arrow-right gf-arrow"></i>
        </a>
        <?php endforeach; ?>

      </div>
    </div>
  </div>

  <section class="section section-galeri-list" style="background: transparent; background-image: none;">
    <div class="container">
      <?php
      $filtered = $cat ? array_filter($gallery, fn($g) => $g['category'] === $cat) : $gallery;
      $filtered = array_values($filtered);
      ?>

      <?php if (empty($filtered)): ?>
        <div class="empty-state"><div class="empty-state-icon"><i class="fas fa-images"></i></div><div class="empty-state-title">Belum ada foto galeri</div></div>
      <?php else: ?>
        <div class="grid grid-3" style="gap:24px;">
          <?php foreach ($filtered as $img): ?>
          <div class="gallery-item" data-animate
            onclick="openLightbox(
              '<?= uploadUrl($img['image']) ?>',
              '<?= htmlspecialchars(addslashes($img['title'] ?? ''), ENT_QUOTES) ?>',
              '<?= htmlspecialchars(addslashes($img['description'] ?? ''), ENT_QUOTES) ?>',
              '<?= ucfirst($img['category'] ?? 'Umum') ?>'
            )">
            <img src="<?= uploadUrl($img['image']) ?>" alt="<?= htmlspecialchars($img['title']??'') ?>" class="gallery-item-img">
            <div class="gallery-item-body">
              <div class="gallery-item-title"><?= htmlspecialchars($img['title'] ?? 'Foto') ?></div>
              <?php if ($img['description']): ?>
              <div class="gallery-item-desc"><?= htmlspecialchars($img['description']) ?></div>
              <?php else: ?>
              <div class="gallery-item-desc" style="opacity:0.3;min-height:1.2em;">&nbsp;</div>
              <?php endif; ?>
              <span class="gallery-item-category"><?= ucfirst($img['category']) ?></span>
            </div>
          </div>
          <?php endforeach; ?>
        </div>
      <?php endif; ?>
    </div>
  </section>

</div>

</div>

<!-- LIGHTBOX POPUP -->
<div id="lightbox" class="lightbox-overlay" onclick="closeLightbox(event)">
  <button class="lightbox-close" onclick="closeLightbox(event)"><i class="fas fa-times"></i></button>
  <div class="lightbox-container" onclick="event.stopPropagation()">
    <div class="lightbox-image-wrap">
      <img id="lb-img" src="" alt="Gallery Image">
    </div>
    <div class="lightbox-info">
      <span class="category" id="lb-category">Proyek</span>
      <h2 id="lb-title">Judul Foto</h2>
      <p class="desc" id="lb-desc">Deskripsi foto galeri.</p>
      <div class="meta" id="lb-meta"></div>
    </div>
  </div>
</div>
</div>

<?php include __DIR__ . '/includes/public_footer.php'; ?>

<script>
function openLightbox(src, title, desc, category) {
  document.getElementById('lb-img').src = src;
  document.getElementById('lb-title').textContent = title || 'Foto';
  document.getElementById('lb-desc').textContent = desc || 'Tidak ada deskripsi.';
  document.getElementById('lb-category').textContent = category || 'Umum';
  const lb = document.getElementById('lightbox');
  lb.classList.add('show');
  document.body.style.overflow = 'hidden';
}

function closeLightbox(e) {
  if (e && e.target !== e.currentTarget && !e.target.closest('.lightbox-close')) return;
  const lb = document.getElementById('lightbox');
  lb.classList.remove('show');
  document.body.style.overflow = '';
}

document.addEventListener('keydown', function(e) {
  if (e.key === 'Escape') {
    const lb = document.getElementById('lightbox');
    if (lb.classList.contains('show')) {
      lb.classList.remove('show');
      document.body.style.overflow = '';
    }
  }
});
</script>