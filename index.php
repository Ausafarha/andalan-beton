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
.public-main { padding-top: 70px; }
</style>

<!-- HERO -->
<section class="hero">
  <div class="hero-content" style="grid-template-columns: 1fr !important; text-align:center; max-width:900px; margin:0 auto; padding:60px 20px;">
    <div data-animate>
      <div class="hero-badge" style="background: rgba(34,197,94,0.15); border: 1px solid rgb(137, 255, 154); color: #09dc2d;">
        <i class="fas fa-award"></i>
        Terpercaya Sejak <?= $cp['established_year'] ?? 2010 ?>
      </div>

      <img src="<?= ASSETS_URL ?>img/logo-hero.png" alt="<?= htmlspecialchars($cp['company_name'] ?? APP_NAME) ?>" 
           style="max-width:100%;height:auto;max-height:200px;display:block;margin:0 auto 20px;">

      <p style="font-size:20px;font-weight:600;color:rgba(255,255,255,0.85);max-width:700px;margin:0 auto 16px;">
        <?= htmlspecialchars($cp['tagline'] ?? 'Industri Readymix dan Precast') ?>
      </p>

      <div style="max-width:750px;margin:0 auto 30px;padding:20px 28px;background:rgba(255,255,255,0.06);border-radius:12px;border-left:4px solid #22c55e;text-align:left;">
        <p style="color:rgba(255,255,255,0.85);font-size:15px;line-height:1.9;margin:0;">
          <strong style="color:#fff;">PT. Mitra Andalan Beton Pantura</strong> merupakan perusahaan yang bergerak di bidang konstruksi terutama supplier <strong style="color:#7cfc00;">Beton Ready Mix</strong> dan <strong style="color:#7cfc00;">Precast</strong> dengan brand produk kami yakni <strong style="color:#7cfc00">Andalan Beton</strong>.
          <br><br>
          PT. Mitra Andalan Beton berkomitmen memberikan pelayanan serta produk terbaik sesuai <strong style="color:#7cfc00;">Standar Nasional Indonesia</strong>. Kami telah berkontribusi pada beberapa pekerjaan konstruksi baik kabupaten/kota, provinsi maupun Nasional.
        </p>
      </div>

      <div class="hero-actions" style="justify-content:center;">
        <a href="<?= APP_URL ?>/pesan.php" class="btn btn-primary btn-lg" style="background:#16a34a;border-color:#16a34a;">
          <i class="fas fa-shopping-cart"></i> Pesan Sekarang
        </a>
        <a href="<?= APP_URL ?>/produk.php" class="btn btn-lg" style="background:rgba(255,255,255,0.1);color:white;border:1.5px solid rgba(255,255,255,0.3);">
          <i class="fas fa-boxes"></i> Lihat Produk
        </a>
      </div>

      <div class="hero-stats" style="justify-content:center;">
        <div>
          <div class="hero-stat-value" style="color:#4ade80;" data-counter data-target="<?= $totalMat ?>"><?= $totalMat ?>+</div>
          <div class="hero-stat-label">Jenis Material</div>
        </div>
        <div>
          <div class="hero-stat-value" style="color:#4ade80;"><span data-counter data-target="<?= $totalProyek ?>"><?= $totalProyek ?></span>+</div>
          <div class="hero-stat-label">Proyek Selesai</div>
        </div>
        <div>
          <div class="hero-stat-value" style="color:#4ade80;" data-counter data-target="<?= $experience ?>"><?= $experience ?>+</div>
          <div class="hero-stat-label">Tahun Pengalaman</div>
        </div>
      </div>
    </div>
  </div>
</section>
<!-- FEATURES -->
<section class="section">
  <div class="container">
    <div style="text-align:center;margin-bottom:48px;" data-animate>
      <div class="section-tag" style="color: var(--brand-600);">Keunggulan Kami</div>
      <h2 class="section-title" style="color: var(--text-primary);">MENGAPA MEMILIH <?= htmlspecialchars($cp['company_name'] ?? APP_NAME) ?>?</h2>
      <p class="section-subtitle" style="margin:14px auto 0; color: var(--text-secondary);">Kami berkomitmen memberikan layanan terbaik dengan kualitas material yang terstandarisasi SNI.</p>
    </div>
    <div class="grid grid-3" style="gap:24px;">
      <?php foreach([
        ['fas fa-certificate','#3b82f6','rgba(59,130,246,.12)','Kualitas Terjamin','Semua material memenuhi standar SNI dan telah teruji kualitasnya melalui laboratorium pengujian internal kami.'],
        ['fas fa-truck-fast','#22c55e','rgba(34,197,94,.12)','Pengiriman Cepat','Armada kendaraan lengkap memastikan material sampai ke lokasi proyek tepat waktu dan dalam kondisi sempurna.'],
        ['fas fa-tags','#f59e0b','rgba(245,158,11,.12)','Harga Kompetitif','Harga terbaik di kelasnya dengan fleksibilitas pembayaran dan diskon menarik untuk pembelian partai besar.'],
        
        ['fas fa-boxes','#ef4444','rgba(239,68,68,.12)','Stok Lengkap','Gudang berkapasitas besar dengan beragam jenis material bangunan memastikan kebutuhan proyek Anda selalu terpenuhi.'],
        
      ] as [$icon,$color,$bg,$title,$desc]): ?>
      <div class="feature-card" data-animate>
        <div class="feature-icon" style="background:<?=$bg?>;color:<?=$color?>;">
          <i class="<?=$icon?>"></i>
        </div>
        <div class="feature-title"><?=$title?></div>
        <div class="feature-desc"><?=$desc?></div>
      </div>
      <?php endforeach; ?>
    </div>
  </div>
</section>

<!-- FEATURED PRODUCTS -->
<section class="section section-dark">
  <div class="container">
    <div style="display:flex;align-items:flex-end;justify-content:space-between;margin-bottom:40px;flex-wrap:wrap;gap:16px;" data-animate>
      <div>
        <div class="section-tag" style="color: var(--brand-600);">Produk Unggulan</div>
<h2 class="section-title" style="color: var(--text-primary);">Material Pilihan Terbaik</h2>
<p class="section-subtitle" style="color: var(--text-secondary);">Produk unggulan kami yang paling banyak diminati pelanggan.</p>
      </div>
      <a href="<?= APP_URL ?>/produk.php" class="btn btn-outline" style="color: var(--brand-600); border-color: var(--brand-600);">Lihat Semua Produk <i class="fas fa-arrow-right"></i></a>
    </div>
    <?php if (empty($featuredMats)): ?>
      <div class="empty-state"><div class="empty-state-icon"><i class="fas fa-boxes"></i></div><div class="empty-state-title">Belum ada produk unggulan</div></div>
    <?php else: ?>
    <div class="grid grid-3" style="gap:24px;">
      <?php foreach ($featuredMats as $mat): ?>
      <div class="product-card" data-animate>
        <?php if ($mat['image']): ?>
          <img src="<?= uploadUrl($mat['image']) ?>" alt="<?= htmlspecialchars($mat['name']) ?>" class="product-card-img">
        <?php else: ?>
          <div class="product-card-img-placeholder">🧱</div>
        <?php endif; ?>
        <div class="product-card-body">
          <div class="product-card-category"><?= htmlspecialchars($mat['category_name'] ?? 'Material') ?></div>
          <div class="product-card-name"><?= htmlspecialchars($mat['name']) ?></div>
          <div class="product-card-desc"><?= htmlspecialchars($mat['description'] ?? '') ?></div>
          <div class="product-card-price"><?= formatRupiah($mat['price']) ?> <span class="product-card-unit">/ <?= $mat['unit'] ?></span></div>
          <a href="<?= APP_URL ?>/pesan.php?material=<?= $mat['id'] ?>" class="btn btn-primary w-100" style="margin-top:14px;">
            <i class="fas fa-shopping-cart"></i> Pesan Sekarang
          </a>
        </div>
      </div>
      <?php endforeach; ?>
    </div>
    <?php endif; ?>
  </div>
</section>

<!-- STATS -->
<section class="section" style="background:linear-gradient(135deg,#0f172a,#1e3a8a);">
  <div class="container">
    <div class="grid grid-4" style="gap:0;">
      <?php foreach([
        ['fas fa-building',''.($totalMat??0).'+','Jenis Material'],
        ['fas fa-users',($cp['total_employees']??50).'+','Karyawan Profesional'],
        ['fas fa-hard-hat',$totalProyek,'Proyek Selesai'],
        ['fas fa-trophy',$experience.'+','Tahun Pengalaman'],
      ] as [$icon,$val,$lbl]): ?>
      <div style="text-align:center;padding:40px 20px;" data-animate>
        <div style="font-size:32px;color:var(--brand-400);margin-bottom:14px;"><i class="<?=$icon?>"></i></div>
        <div style="font-size:42px;font-weight:800;color:white;" ><span data-counter><?=$val?></span>+</div>
        <div style="font-size:14px;color:rgba(255,255,255,0.5);margin-top:6px;"><?=$lbl?></div>
      </div>
      <?php endforeach; ?>
    </div>
  </div>
</section>

<!-- CTA -->
<section class="section">
  <div class="container" style="text-align:center;">
    <div style="max-width:600px;margin:0 auto;" data-animate>
      <div class="section-tag" style="color: var(--brand-600);">Mulai Sekarang</div>
      <h2 class="section-title" style="color: var(--text-primary);">Siap Memulai Proyek Anda?</h2>
      <p class="section-subtitle" style="margin:14px auto 32px; color: var(--text-secondary);">Hubungi kami sekarang untuk konsultasi gratis dan penawaran terbaik. Tim kami siap membantu kebutuhan material proyek Anda.</p>
      <div style="display:flex;gap:12px;justify-content:center;flex-wrap:wrap;">
        <a href="<?= APP_URL ?>/pesan.php" class="btn btn-primary btn-lg"><i class="fas fa-shopping-cart"></i> Pesan Online</a>
        <?php if ($cp['whatsapp']): ?>
        <a href="https://wa.me/<?= preg_replace('/[^0-9]/','', $cp['whatsapp']) ?>" target="_blank" class="btn btn-lg" style="background:#25d366;color:white;border-color:#25d366;">
          <i class="fab fa-whatsapp"></i> WhatsApp Kami
        </a>
        <?php endif; ?>
        <a href="<?= APP_URL ?>/kontak.php" class="btn btn-secondary btn-lg"><i class="fas fa-phone"></i> Hubungi Kami</a>
      </div>
    </div>
  </div>
</section>

<?php include __DIR__ . '/includes/public_footer.php'; ?>