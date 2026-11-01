<?php
require_once __DIR__ . '/config/database.php';
require_once __DIR__ . '/config/app.php';
initSession();

$currentSlug = 'profil';
$pageTitle   = 'Profil Perusahaan';
$cp          = getCompanyProfile();
$experience  = date('Y') - ($cp['established_year'] ?? 2010);

include __DIR__ . '/includes/public_head.php';

$heroTitle = [
  'pre'       => 'PT MITRA ',
  'highlight' => 'ANDALAN BETON PANTURA',
];
$heroBody = '
      <p style="color:rgba(255,255,255,0.9);font-size:15px;line-height:1.8;max-width:600px;margin:0;">
        Mengenal lebih dekat perjalanan dan komitmen kami dalam menyediakan material konstruksi berkualitas.
      </p>';
?>
<style>
  #profil-page .hero { justify-content: center !important; }
  #profil-page .hero-content {
    margin: 0 auto !important;
    max-width: 900px !important;
    width: 100%;
    padding-left: 24px !important;
    padding-right: 24px !important;
    text-align: center !important;
  }
  #profil-page .hero-content h1,
  #profil-page .hero-content p {
    margin-left: auto !important;
    margin-right: auto !important;
    text-align: center !important;
  }
  #profil-page .hero h1 { font-size: clamp(26px, 4vw, 40px) !important; }

  [data-theme="dark"] #profil-page .section-about {
    background: #061C01 !important;
  }
  [data-theme="dark"] #profil-page .section-about-desc {
    color: rgba(255, 255, 255, 0.85) !important;
  }
  [data-theme="dark"] #profil-page .section-about .section-tag {
    color: #4CAF50 !important;
  }

  [data-theme="dark"] #profil-page .section-trusted {
    border: 1px solid rgba(255, 255, 255, 0.3) !important;
  }
  [data-theme="dark"] #profil-page .pf-vm-icon {
    background: #1c471f !important;
  }
  
  /* ===== BAR ALAMAT & JAM OPERASIONAL ===== */
  .pf-info-bar {
    display: grid;
    grid-template-columns: 1fr 1fr 1fr;
    border-radius: 20px;
    padding: 22px 28px;
  }
  .pf-info-item {
    display: flex;
    align-items: center;
    gap: 16px;
  }
  .pf-info-item + .pf-info-item {
    border-left: 1px solid #c8dccd;
    padding-left: 32px;
  }
  .pf-info-icon {
    flex: 0 0 52px;
    width: 52px;
    height: 52px;
    border-radius: 50%;
    background: #d3ebd9;
    color: #16a34a;
    display: flex;
    align-items: center;
    justify-content: center;
    font-size: 22px;
  }
  .pf-info-title {
    font-size: 15px;
    font-weight: 800;
    color: #111827;
    margin-bottom: 4px;
  }
  .pf-info-text {
    font-size: 14px;
    line-height: 1.6;
    color: #4b5563;
  }

  [data-theme="dark"] #profil-page .pf-info-item + .pf-info-item {
    border-left-color: rgba(255,255,255,0.15);
  }
  [data-theme="dark"] #profil-page .pf-info-icon {
    background: #1c471f;
  }
  [data-theme="dark"] #profil-page .pf-info-title {
    color: #ffffff;
  }
  [data-theme="dark"] #profil-page .pf-info-text {
    color: rgba(255,255,255,0.85);
  }

  @media (max-width: 640px) {
    .pf-info-bar {
      grid-template-columns: 1fr;
      gap: 16px;
      padding: 18px;
    }
    .pf-info-item + .pf-info-item {
      border-left: none;
      border-top: 1px solid #c8dccd;
      padding-left: 0;
      padding-top: 16px;
    }
  }

  .pf-vmc-wrap {
    position: relative;
    overflow: hidden;
    background-color: #ffffff;
    background-image: url('<?= ASSETS_URL ?>img/background.png');
    background-size: cover;
    background-position: center top;
    background-repeat: repeat-y;
  }
  .pf-vmc-wrap::before {
    content: '';
    position: absolute;
    inset: 0;
    background: rgba(133, 133, 132, 0);
    z-index: 0;
    pointer-events: none;
  }
  .pf-vmc-wrap > section {
    position: relative;
    z-index: 1;
  }
  [data-theme="dark"] #profil-page .pf-vmc-wrap {
    background-color: #061C01 !important;
  }
  [data-theme="dark"] #profil-page .pf-vmc-wrap::before {
    background: rgba(6, 28, 1, 0.6);
  }

  .pf-vmc-tag { color: var(--brand-600) !important; }
  .pf-vmc-title {
    color: #111827 !important;
    font-family: 'Oswald', sans-serif;
  }
  .pf-vmc-subtitle { color: #4b5563 !important; }

  .pf-vm-card {
    box-shadow: 0 8px 24px rgba(0,0,0,0.40);
    border: 1px solid #eef2f0;
  }

  [data-theme="dark"] #profil-page .pf-vmc-tag,
  [data-theme="dark"] #profil-page .pf-vmc-title,
  [data-theme="dark"] #profil-page .pf-vmc-subtitle {
    color: #ffffff !important;
  }
  [data-theme="dark"] #profil-page .pf-vm-card {
    background: #152F14 !important;
    border-color: rgba(255,255,255,0.1);
    box-shadow: 0 8px 24px rgba(0,0,0,0.4);
  }
  [data-theme="dark"] #profil-page .pf-vm-text {
    color: rgba(255,255,255,0.85) !important;
  }
</style>

<div id="profil-page">
<?php include __DIR__ . '/includes/hero.php'; ?>

<!-- About Section -->
<section class="section section-about" style="background:#ffffff;padding-bottom:16px;">
  <div class="container">
    <div class="grid" style="grid-template-columns:1fr 1fr;gap:60px;align-items:center;">
      <div data-animate>
        <img src="<?= ASSETS_URL ?>img/profile.jpeg" alt="Tim <?= htmlspecialchars($cp['company_name'] ?? APP_NAME) ?>" style="width:100%; border-radius:var(--radius-xl); display:block;">
      </div>
      <div data-animate>
        <div class="section-tag">Siapa Kami ?</div>
        <h2 class="section-title" style="display:inline-block; background:#388E3C; color:#fff; padding:8px 20px; border-radius:8px; margin-top:10px;">Industri Readymix dan Precast</h2>
        <p class="section-about-desc" style="color:var(--text-secondary);font-size:15px;line-height:1.8;margin-top:20px;font-weight:500;">
          <?= nl2br(htmlspecialchars($cp['description'] ?? '')) ?>
        </p>
      </div>
    </div>
  </div>
</section>


<!-- Alamat & Jam Operasional (Sudah Berlatar Putih) -->
<section style="padding:24px 0; background: #ffffff;">
  <div class="container">
    <div class="pf-info-bar" data-animate style="background: #ffffff !important;  padding: 35px;  rgba(0,0,0,0.08); color: #1e293b !important;">
      <div class="pf-info-item">
        <div class="pf-info-icon"><i class="fas fa-map-marker-alt"></i></div>
        <div>
          <div class="pf-info-title" style="color: #1e293b; font-weight: 700;">Alamat Kantor</div>
          <div class="pf-info-text" style="color: #475569;"><?= htmlspecialchars(($cp['address']??'-').', '.($cp['city']??'').', '.($cp['province']??'')) ?></div>
        </div>
      </div>
      <div class="pf-info-item">
        <div class="pf-info-icon"><i class="fas fa-clock"></i></div>
        <div>
          <div class="pf-info-title" style="color: #1e293b; font-weight: 700;">Jam Operasional</div>
          <div class="pf-info-text" style="color: #475569;">Senin - Sabtu<br>07.00 - 16.00 WIB</div>
        </div>
      </div>
      <div class="pf-info-item">
        <div class="pf-info-icon"><i class="fas fa-phone-alt"></i></div>
        <div>
          <div class="pf-info-title" style="color: #1e293b; font-weight: 700;">Telepon / WhatsApp</div>
          <div class="pf-info-text" style="color: #475569;">
            <a href="tel:<?= preg_replace('/[^0-9]/','', $cp['phone']??'') ?>" style="color:inherit;text-decoration:none;"><?= htmlspecialchars($cp['phone']??'-') ?></a>
          </div>
        </div>
      </div>
    </div>
  </div>
</section>

<!-- Stats Band -->
<section class="section-trusted" style="background:#0a0a0a; padding:24px;">
  <div class="container">
    <div class="grid grid-4" style="gap:20px;">
      <?php foreach([
        [$cp['established_year']??2010, 'Tahun Berdiri'],
        [$cp['total_employees']??50, 'Karyawan Profesional'],
        [$experience, 'Tahun Pengalaman'],
        [($cp['total_projects']??50).'+', 'Proyek Selesai'],
      ] as [$val, $lbl]): ?>
      <div style="text-align:center;">
        <div style="font-size:28px;font-weight:800;color:#fff;"><?= htmlspecialchars($val) ?></div>
        <div style="font-size:13px;color:rgba(255, 255, 255, 0.6);margin-top:4px;"><?=$lbl?></div>
      </div>
      <?php endforeach; ?>
    </div>
  </div>
</section>

<div class="pf-vmc-wrap">

<!-- Vision & Mission -->
<section class="section section-vision-mission">
  <div class="container">
    <div class="pf-vmc-intro" style="text-align:center;margin-bottom:48px;" data-animate>
      <div class="section-tag pf-vmc-tag">Arah Perusahaan</div>
      <h2 class="section-title pf-vmc-title">Visi & Misi</h2>
      <p class="section-subtitle pf-vmc-subtitle" style="margin:14px auto 0;">Komitmen kami untuk terus memberikan yang terbaik dalam setiap proyek</p>
    </div>
    <div class="grid grid-2" style="gap:32px;">
      <div class="pf-vm-card" style="background:#fff; border-radius:var(--radius-lg); padding:56px 36px 36px; text-align:center; position:relative;" data-animate>
        <div class="pf-vm-icon" style="width:70px;height:70px;border-radius:50%;background:#16a34a;border:4px solid #fff;color:#fff;display:flex;align-items:center;justify-content:center;font-size:28px;position:absolute;top:-35px;left:50%;transform:translateX(-50%);box-shadow:0 4px 10px rgba(0,0,0,0.15);">
          <i class="fas fa-eye"></i>
        </div>
        <p class="pf-vm-text" style="color:#374151;line-height:1.8;font-size:14.5px;font-weight:600;"><?= nl2br(htmlspecialchars($cp['vision'] ?? 'Menjadi perusahaan supplier material bangunan terdepan dan terpercaya di Indonesia.')) ?></p>
      </div>
      <div class="pf-vm-card" style="background:#fff; border-radius:var(--radius-lg); padding:56px 36px 36px; text-align:center; position:relative;" data-animate>
        <div class="pf-vm-icon" style="width:70px;height:70px;border-radius:50%;background:#16a34a;border:4px solid #fff;color:#fff;display:flex;align-items:center;justify-content:center;font-size:28px;position:absolute;top:-35px;left:50%;transform:translateX(-50%);box-shadow:0 4px 10px rgba(0,0,0,0.15);">
          <i class="fas fa-bullseye"></i>
        </div>
        <div class="pf-vm-text" style="color:#374151;line-height:1.8;font-size:14.5px;font-weight:600;text-align:left;"><?= nl2br(htmlspecialchars($cp['mission'] ?? '')) ?></div>
      </div>
    </div>
  </div>
</section>

<!-- Values -->
<section class="section section-values">
  <div class="container">
    <div style="text-align:center;margin-bottom:48px;" data-animate>
      <div class="section-tag" style="color: var(--brand-600);">Nilai Perusahaan</div>
      <h2 class="section-title" style="font-family:'Oswald', sans-serif;">Nilai yang Kami Pegang</h2>
    </div>
    <div class="grid grid-4" style="gap:20px;">
      <?php foreach([
        ['fas fa-star','Kualitas','Standar mutu tertinggi dalam setiap produk dan layanan'],
        ['fas fa-handshake','Integritas','Kejujuran dan transparansi dalam setiap transaksi bisnis'],
        ['fas fa-lightbulb','Inovasi','Terus berkembang mengikuti perkembangan industri konstruksi'],
        ['fas fa-heart','Kepedulian','Mengutamakan kepuasan dan kepercayaan pelanggan'],
      ] as [$icon,$title,$desc]): ?>
      <div data-animate style="background:#152F14; border-radius:16px; overflow:hidden;">
        <div style="padding:32px 24px 20px; display:flex; align-items:center; justify-content:center;">
          <i class="<?=$icon?>" style="font-size:34px; color:#fff;"></i>
        </div>
        <div style="background:linear-gradient(120deg, #1f4e21 0%, #16a34a 75%); padding:20px 24px 28px; text-align:center;">
          <div style="font-size:16px; font-weight:800; margin-bottom:10px; color:#fff;"><?=$title?></div>
          <div style="font-size:13px; line-height:1.7; color:rgba(255,255,255,0.9);"><?=$desc?></div>
        </div>
      </div>
      <?php endforeach; ?>
    </div>
  </div>
</section>

<!-- Social Media -->
<section class="section section-social" style="padding-top:48px; padding-bottom:48px;">
  <div class="container">
    <div class="pf-social-box" style="background:linear-gradient(120deg, #16a34a 0%, #0c3a01 100%); border-radius:var(--radius-xl); padding:40px 24px; box-shadow:0 4px 16px rgba(0,0,0,0.15);">
      <div style="text-align:center;margin-bottom:32px;" data-animate>
        <div class="section-tag" style="color:#fff;">Ikuti Kami</div>
        <h2 class="section-title" style="color:#fff;">Media Sosial</h2>
        <p class="section-subtitle" style="margin:14px auto 0; color:rgba(255,255,255,0.9);">Pantau aktivitas dan update terbaru dari PT Mitra Andalan Beton Pantura</p>
      </div>
      <div style="display:flex;justify-content:center;gap:16px;flex-wrap:wrap;" data-animate>

      <?php if (!empty($cp['social_facebook'])): ?>
      <a href="<?= $cp['social_facebook'] ?>" target="_blank"
         style="display:inline-flex;align-items:center;gap:10px;padding:12px 24px;min-width:140px;justify-content:center;background:#1877f2;color:white;border-radius:10px;text-decoration:none;font-size:14px;font-weight:600;">
        <i class="fab fa-facebook-f" style="font-size:18px;"></i> Facebook
      </a>
      <?php endif; ?>

      <?php if (!empty($cp['social_instagram'])): ?>
      <a href="<?= $cp['social_instagram'] ?>" target="_blank"
         style="display:inline-flex;align-items:center;gap:10px;padding:12px 24px;min-width:140px;justify-content:center;background:linear-gradient(45deg,#f09433,#e6683c,#dc2743,#cc2366,#bc1888);color:white;border-radius:10px;text-decoration:none;font-size:14px;font-weight:600;">
        <i class="fab fa-instagram" style="font-size:18px;"></i> Instagram
      </a>
      <?php endif; ?>

      <?php if (!empty($cp['social_youtube'])): ?>
      <a href="<?= $cp['social_youtube'] ?>" target="_blank"
         style="display:inline-flex;align-items:center;gap:10px;padding:12px 24px;min-width:140px;justify-content:center;background:#ff0000;color:white;border-radius:10px;text-decoration:none;font-size:14px;font-weight:600;">
        <i class="fab fa-youtube" style="font-size:18px;"></i> YouTube
      </a>
      <?php endif; ?>

      <?php if (!empty($cp['social_tiktok'])): ?>
      <a href="<?= $cp['social_tiktok'] ?>" target="_blank"
         style="display:inline-flex;align-items:center;gap:10px;padding:12px 24px;min-width:140px;justify-content:center;background:#000000;color:white;border-radius:10px;text-decoration:none;font-size:14px;font-weight:600;">
        <i class="fab fa-tiktok" style="font-size:18px;"></i> TikTok
      </a>
      <?php endif; ?>

      </div>
    </div>
  </div>
</section>

</div>
</div>

<?php include __DIR__ . '/includes/public_footer.php'; ?>