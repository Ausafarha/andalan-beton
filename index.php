<?php
require_once __DIR__ . '/config/database.php';
require_once __DIR__ . '/config/app.php';
initSession();

$currentSlug = '';
$pageTitle   = '';

$cp           = getCompanyProfile();
$featuredMats = Database::fetchAll("
    SELECT 
        m.id,
        m.code,
        m.name,
        m.description,
        m.unit,
        m.price,
        m.image,
        m.is_active,
        m.is_featured,
        COALESCE(COALESCE(si.total_in, 0) - COALESCE(so.total_out, 0), 0) AS current_stock,
        mc.name AS category_name,
        mc.id AS category_id
    FROM materials m
    LEFT JOIN material_categories mc ON m.category_id = mc.id
    LEFT JOIN (
        SELECT material_id, SUM(quantity) AS total_in
        FROM stock_in
        GROUP BY material_id
    ) si ON m.id = si.material_id
    LEFT JOIN (
        SELECT material_id, SUM(quantity) AS total_out
        FROM stock_out
        GROUP BY material_id
    ) so ON m.id = so.material_id
    WHERE m.is_active = true AND m.is_featured = true
    ORDER BY m.name LIMIT 6
");
$categories   = Database::fetchAll("SELECT * FROM material_categories ORDER BY name");
$totalMat     = Database::fetchColumn("SELECT COUNT(*) FROM materials WHERE is_active=true");
$totalProyek  = $cp['total_projects'] ?? 50;
$experience   = date('Y') - ($cp['established_year'] ?? 2010);

include __DIR__ . '/includes/public_head.php';
?>

<style>
.public-main { padding-top: 60px; }
</style>

<div id="beranda-page">

<?php
$heroBody = '
      <p class="br-hero-desc">
        <strong>PT. Mitra Andalan Beton Pantura</strong> merupakan perusahaan yang bergerak di bidang konstruksi terutama supplier <strong class="hl">Beton Ready Mix</strong> dan <strong class="hl">Precast</strong> dengan brand produk kami yakni <strong class="hl">Andalan Beton</strong>.
      </p>
      <p class="br-hero-desc">
        PT. Mitra Andalan Beton berkomitmen memberikan pelayanan serta produk terbaik sesuai <strong class="hl">Standar Nasional Indonesia</strong>. Kami telah berkontribusi pada beberapa pekerjaan konstruksi baik kabupaten/kota, provinsi maupun Nasional.
      </p>';

$heroActions = [
  ['url' => APP_URL.'/pesan.php', 'label' => 'Pesan Sekarang', 'icon' => 'fas fa-shopping-cart', 'style' => 'background:#388E3C;border-color:#1F8606;color:#fff;', 'class' => 'hero-btn-pesan'],
  ['url' => APP_URL.'/produk.php', 'label' => 'Lihat Produk', 'icon' => 'fas fa-boxes', 'style' => 'background:rgba(255, 255, 255, 0.1);color:white;border:1.5px solid rgba(255,255,255,0.3);'],
];
include __DIR__ . '/includes/hero.php';
?>

<!-- STATS BAND -->
<section class="section-stats-wrap">
  <div class="br-stats-band" data-animate>
    <?php foreach([
      [($totalMat??0), 'Jenis Material', false],
      [($cp['total_employees']??50), 'Karyawan Profesional', false],
      [$totalProyek, 'Proyek Selesai', true],
      [$experience, 'Tahun Pengalaman', false],
    ] as [$val,$lbl,$hasPlus]): ?>
    <div class="br-stat-item">
      <div class="br-stat-value">
        <?php if($hasPlus): ?>
          <span data-counter data-target="<?=$val?>"><?=$val?></span>+
        <?php else: ?>
          <span data-counter data-target="<?=$val?>"><?=$val?></span>
        <?php endif; ?>
      </div>
      <div class="br-stat-label"><?=$lbl?></div>
    </div>
    <?php endforeach; ?>
  </div>
</section>

<!-- FEATURES -->
<section class="section section-keunggulan">
  <div class="container">
    <div class="br-section-intro" data-animate>
      <div class="section-tag br-section-tag">Keunggulan Kami</div>
      <h2 class="section-title br-section-title">MENGAPA MEMILIH <?= htmlspecialchars($cp['company_name'] ?? APP_NAME) ?>?</h2>
      <p class="section-subtitle br-section-subtitle">Kami berkomitmen memberikan layanan terbaik dengan kualitas material yang terstandarisasi SNI.</p>
    </div>
    <div class="grid grid-4 br-features-grid">
      <?php foreach([
        ['fas fa-certificate','Kualitas Terjamin','Semua material memenuhi standar SNI dan telah teruji kualitasnya melalui laboratorium pengujian internal kami.'],
        ['fas fa-truck-fast','Pengiriman Cepat','Armada kendaraan lengkap memastikan material sampai ke lokasi proyek tepat waktu dan dalam kondisi sempurna.'],
        ['fas fa-tags','Harga Kompetitif','Harga terbaik di kelasnya dengan fleksibilitas pembayaran dan diskon menarik untuk pembelian partai besar.'],
        ['fas fa-boxes','Stok Lengkap','Semua material memenuhi standar SNI dan telah teruji kualitasnya melalui laboratorium pengujian internal kami.'],
      ] as [$icon,$title,$desc]): ?>
      <div data-animate class="br-feature-card">
        <div class="br-feature-icon-wrap">
          <i class="<?=$icon?>"></i>
        </div>
        <div class="br-feature-body">
          <div class="br-feature-title"><?=$title?></div>
          <div class="br-feature-desc"><?=$desc?></div>
        </div>
      </div>
      <?php endforeach; ?>
    </div>
  </div>
</section>

<!-- TERPERCAYA -->
<section class="br-trust-bar">
  <span>Terpercaya Sejak <?= $cp['established_year'] ?? 2010 ?></span>
</section>

<div class="br-products-cta-wrap">

<!-- FEATURED PRODUCTS -->
<section class="section section-products-wrap">
  <div class="container">
    <div class="br-products-head" data-animate>
      <div>
        <div class="section-tag br-products-tag">Produk Unggulan</div>
        <h2 class="section-title br-products-title">Material Pilihan Terbaik</h2>
        <p class="section-subtitle br-products-subtitle">Produk unggulan kami yang paling banyak diminati pelanggan.</p>
      </div>
      <a href="<?= APP_URL ?>/produk.php" class="btn br-products-link">Lihat Semua Produk <i class="fas fa-arrow-right"></i></a>
    </div>
    <?php if (empty($featuredMats)): ?>
      <div class="empty-state"><div class="empty-state-icon"><i class="fas fa-boxes"></i></div><div class="empty-state-title">Belum ada produk unggulan</div></div>
    <?php else: ?>
    <div class="grid grid-3" style="gap:20px;">
      <?php foreach ($featuredMats as $mat): ?>
      <div data-animate class="br-product-card">
        <?php if ($mat['image']): ?>
          <img src="<?= uploadUrl($mat['image']) ?>" alt="<?= htmlspecialchars($mat['name']) ?>" class="br-product-img">
        <?php else: ?>
          <div class="br-product-img-placeholder">Foto Produk</div>
        <?php endif; ?>
        <div class="br-product-body">
          <span class="br-product-category"><?= htmlspecialchars($mat['category_name'] ?? 'Material') ?></span>
          <div class="br-product-name"><?= htmlspecialchars($mat['name']) ?></div>
          <div class="br-product-price"><?= formatRupiah($mat['price']) ?> <span class="br-product-unit">/ <?= $mat['unit'] ?></span></div>
          <div class="br-product-actions">
            <div class="br-qty-box">
              <button type="button" class="br-qty-btn" onclick="let i=this.nextElementSibling; i.value=Math.max(1,(parseInt(i.value)||1)-1);">−</button>
              <input type="number" value="1" min="1" class="br-qty-input">
              <button type="button" class="br-qty-btn" onclick="let i=this.previousElementSibling; i.value=(parseInt(i.value)||1)+1;">+</button>
            </div>
            <a href="<?= APP_URL ?>/pesan.php?material=<?= $mat['id'] ?>" class="br-order-btn">
              <i class="fas fa-shopping-cart"></i> Pesan
            </a>
          </div>
        </div>
      </div>
      <?php endforeach; ?>
    </div>
    <?php endif; ?>
  </div>
</section>

<!-- CTA -->
<section class="section section-cta">
  <div class="container" style="text-align:center;">
    <div class="br-cta-box" data-animate>
      <h2 class="section-title br-cta-title">Siap Memulai Proyek Anda?</h2>
      <p class="section-subtitle section-cta-desc">Hubungi kami sekarang untuk konsultasi gratis dan penawaran terbaik. Tim kami siap membantu kebutuhan material proyek Anda.</p>
      <div class="br-cta-buttons">
        <a href="<?= APP_URL ?>/pesan.php" class="br-cta-btn"><i class="fas fa-shopping-cart"></i> Pesan Online</a>
        <?php if ($cp['whatsapp']): ?>
        <a href="https://wa.me/<?= preg_replace('/[^0-9]/','', $cp['whatsapp']) ?>" target="_blank" class="br-cta-btn">
          <i class="fab fa-whatsapp"></i> WhatsApp
        </a>
        <?php endif; ?>
        <a href="<?= APP_URL ?>/kontak.php" class="br-cta-btn"><i class="fas fa-phone"></i> Hubungi Kami</a>
      </div>
    </div>
  </div>
</section>

</div>
<!-- akhir .br-products-cta-wrap -->

</div>
<!-- akhir #beranda-page -->

<?php include __DIR__ . '/includes/public_footer.php'; ?>