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
$gallery = Database::fetchAll("SELECT * FROM gallery WHERE is_active=true ORDER BY sort_order, id DESC");
$cats    = Database::fetchAll("SELECT DISTINCT category FROM gallery WHERE is_active=true ORDER BY category");

include __DIR__ . '/includes/public_head.php';
?>

<div style="padding-top:70px;">

<div style="background:#16a34a;padding:60px 0;">
  <div class="container" style="text-align:center;">
    <div class="section-tag" style="color:var(--brand-300);">Portofolio</div>
    <h1 style="font-size:clamp(28px,5vw,44px);font-weight:800;color:white;margin-top:10px;">Galeri Proyek & Fasilitas</h1>
    <p style="color:rgba(255,255,255,.6);font-size:15px;margin-top:12px;">Dokumentasi proyek dan fasilitas PT Andalan Beton</p>
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
      <div class="grid grid-3" style="gap:20px;">
        <?php foreach ($filtered as $img): ?>
        <div style="border-radius:var(--radius-lg);overflow:hidden;box-shadow:var(--shadow-sm);border:1px solid var(--border);transition:all .18s;cursor:pointer;" class="gallery-item" data-animate
          onclick="openLightbox('<?= uploadUrl($img['image']) ?>','<?= htmlspecialchars(addslashes($img['title']??'')) ?>','<?= htmlspecialchars(addslashes($img['description']??'')) ?>')">
          <img src="<?= uploadUrl($img['image']) ?>" alt="<?= htmlspecialchars($img['title']??'') ?>"
  style="width:100%;aspect-ratio:4/3;object-fit:cover;transition:transform .3s;"
  onmouseover="this.style.transform='scale(1.05)'" onmouseout="this.style.transform='scale(1)'">
          <div style="padding:14px 16px;background:var(--bg-surface);">
            <div style="font-weight:600;font-size:14px;margin-bottom:4px;"><?= htmlspecialchars($img['title'] ?? 'Foto') ?></div>
            <?php if ($img['description']): ?>
            <div style="font-size:12.5px;color:var(--text-muted);"><?= htmlspecialchars($img['description']) ?></div>
            <?php endif; ?>
            <span style="font-size:11px;background:var(--brand-50);color:var(--brand-700);padding:2px 8px;border-radius:20px;margin-top:6px;display:inline-block;font-weight:600;"><?= ucfirst($img['category']) ?></span>
          </div>
        </div>
        <?php endforeach; ?>
      </div>
    <?php endif; ?>
  </div>
</section>

</div>

<!-- Lightbox Modal -->
<div id="lightbox" style="display:none;position:fixed;inset:0;z-index:9999;background:rgba(0,0,0,.92);align-items:center;justify-content:center;padding:20px;" onclick="closeLightbox()">
  <button onclick="closeLightbox()" style="position:absolute;top:20px;right:20px;background:rgba(255,255,255,.1);border:none;color:white;width:42px;height:42px;border-radius:50%;font-size:18px;cursor:pointer;"><i class="fas fa-times"></i></button>
  <div style="max-width:900px;width:100%;text-align:center;" onclick="event.stopPropagation()">
    <img id="lb-img" src="" style="max-height:70vh;max-width:100%;border-radius:var(--radius-lg);box-shadow:0 20px 60px rgba(0,0,0,.5);">
    <div id="lb-title" style="color:white;font-size:18px;font-weight:700;margin-top:16px;"></div>
    <div id="lb-desc"  style="color:rgba(255,255,255,.6);font-size:14px;margin-top:6px;"></div>
  </div>
</div>

<?php include __DIR__ . '/includes/public_footer.php'; ?>
<script>
function openLightbox(src, title, desc) {
  document.getElementById('lb-img').src = src;
  document.getElementById('lb-title').textContent = title;
  document.getElementById('lb-desc').textContent = desc;
  const lb = document.getElementById('lightbox');
  lb.style.display = 'flex';
  document.body.style.overflow = 'hidden';
}
function closeLightbox() {
  document.getElementById('lightbox').style.display = 'none';
  document.body.style.overflow = '';
}
document.addEventListener('keydown', e => { if (e.key === 'Escape') closeLightbox(); });
</script>
