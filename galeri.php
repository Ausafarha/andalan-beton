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
// KODE BARU (Urutkan berdasarkan ID terbaru):
$gallery = Database::fetchAll("SELECT * FROM gallery WHERE is_active=true ORDER BY id DESC");
$cats    = Database::fetchAll("SELECT DISTINCT category FROM gallery WHERE is_active=true ORDER BY category");

include __DIR__ . '/includes/public_head.php';
?>


<div style="padding-top:70px;">

<!-- HEADER -->
<div style="background: linear-gradient(135deg, #0f172a 0%, #20bc95 50%, #0f172a 100%); padding:60px 0;">
  <div class="container" style="text-align:center;">
    <div class="section-tag" style="color:#000000;">Portofolio</div>
    <h1 style="font-size:clamp(28px,5vw,44px);font-weight:800;color:white;margin-top:10px;">Galeri Proyek & Fasilitas</h1>
    <p style="color:#ffffff;font-size:15px;margin-top:12px;">Dokumentasi proyek dan PT MITRA ANDALAN BETON PANTURA</p>
  </div>
</div>

<section class="section">
  <div class="container">

    <!-- Category Filter -->
    <div style="display:flex;gap:8px;justify-content:center;flex-wrap:wrap;margin-bottom:36px;">
      <a href="<?= APP_URL ?>/galeri.php" style="padding:8px 20px;border-radius:var(--radius-full);font-size:13.5px;font-weight:600;text-decoration:none;border:1.5px solid var(--border);background:<?= !$cat?'var(--brand-600)':'var(--bg-surface)'?>;color:<?= !$cat?'white':'var(--text-secondary)'?>;transition:all .18s;">Semua</a>
      <?php foreach ($cats as $c):
        $cLabel = ucfirst($c['category']);
        $isA    = $cat === $c['category'];
      ?>
      <a href="?kategori=<?= urlencode($c['category']) ?>" style="padding:8px 20px;border-radius:var(--radius-full);font-size:13.5px;font-weight:600;text-decoration:none;border:1.5px solid var(--border);background:<?= $isA?'var(--brand-600)':'var(--bg-surface)'?>;color:<?= $isA?'white':'var(--text-secondary)'?>;transition:all .18s;"><?= $cLabel ?></a>
      <?php endforeach; ?>
    </div>

    <?php
    // Filter
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