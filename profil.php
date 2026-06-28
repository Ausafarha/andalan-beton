<?php
require_once __DIR__ . '/config/database.php';
require_once __DIR__ . '/config/app.php';
initSession();

$currentSlug = 'profil';
$pageTitle   = 'Profil Perusahaan';
$cp          = getCompanyProfile();
$experience  = date('Y') - ($cp['established_year'] ?? 2010);

include __DIR__ . '/includes/public_head.php';
?>

<div style="padding-top:70px;">

<!-- Page Header -->
<div style="background:linear-gradient(135deg,#0f172a,#1e3a8a);padding:60px 0;">
  <div class="container" style="text-align:center;">
    <div class="section-tag" style="color:var(--brand-300);">Tentang Kami</div>
    <h1 style="font-size:clamp(30px,5vw,48px);font-weight:800;color:white;margin-top:10px;"><?= htmlspecialchars($cp['company_name'] ?? APP_NAME) ?></h1>
    <p style="color:rgba(255,255,255,.6);font-size:16px;margin-top:12px;"><?= htmlspecialchars($cp['tagline'] ?? '') ?></p>
  </div>
</div>

<!-- About Section -->
<section class="section">
  <div class="container">
    <div class="grid" style="grid-template-columns:1fr 1fr;gap:60px;align-items:center;">
      <div data-animate>
        <div class="section-tag">Siapa Kami</div>
          <h2 class="section-title">Industri Readymix dan Precast</h2>
        <p style="color:var(--text-secondary);font-size:15px;line-height:1.8;margin-top:16px;"><?= nl2br(htmlspecialchars($cp['description'] ?? '')) ?></p>
        <div style="display:grid;grid-template-columns:1fr 1fr;gap:20px;margin-top:32px;">
          <?php foreach([
            [$cp['established_year']??2010,'Tahun Berdiri'],
            [($cp['total_employees']??50).'+','Karyawan'],
            [$experience.'+','Tahun Pengalaman'],
            ['1250+','Proyek Selesai'],
          ] as [$val,$lbl]): ?>
          <div style="padding:20px;background:var(--bg-muted);border-radius:var(--radius-lg);text-align:center;">
            <div style="font-size:28px;font-weight:800;color:var(--brand-600);" data-counter><?=$val?></div>
            <div style="font-size:13px;color:var(--text-muted);margin-top:4px;"><?=$lbl?></div>
          </div>
          <?php endforeach; ?>
        </div>
      </div>
      <div data-animate>
        <div style="background:linear-gradient(135deg,var(--brand-600),var(--brand-800));border-radius:var(--radius-xl);padding:40px;color:white;">
          <i class="fas fa-building" style="font-size:48px;opacity:.3;margin-bottom:20px;display:block;"></i>
          <h3 style="font-size:22px;font-weight:800;margin-bottom:12px;"><?= htmlspecialchars($cp['company_name'] ?? APP_NAME) ?></h3>
          <?php if ($cp['address']): ?>
          <div style="display:flex;gap:10px;margin-bottom:10px;opacity:.8;font-size:14px;"><i class="fas fa-map-marker-alt" style="margin-top:3px;flex-shrink:0;"></i><?= htmlspecialchars($cp['address'].", ".($cp['city']??'')) ?></div>
          <?php endif; ?>
          <?php if ($cp['phone']): ?>
          <div style="display:flex;gap:10px;margin-bottom:10px;opacity:.8;font-size:14px;"><i class="fas fa-phone" style="flex-shrink:0;"></i><?= htmlspecialchars($cp['phone']) ?></div>
          <?php endif; ?>
          <?php if ($cp['email']): ?>
          <div style="display:flex;gap:10px;margin-bottom:10px;opacity:.8;font-size:14px;"><i class="fas fa-envelope" style="flex-shrink:0;"></i><?= htmlspecialchars($cp['email']) ?></div>
          <?php endif; ?>
        </div>
      </div>
    </div>
  </div>
</section>

<!-- Vision & Mission -->
<section class="section section-dark">
  <div class="container">
    <div style="text-align:center;margin-bottom:48px;" data-animate>
      <div class="section-tag">Arah Perusahaan</div>
      <h2 class="section-title">Visi & Misi</h2>
    </div>
    <div class="grid grid-2" style="gap:32px;">
      <div class="feature-card" data-animate>
        <div class="feature-icon" style="background:rgba(59,130,246,.12);color:var(--brand-600);font-size:28px;width:60px;height:60px;">
          <i class="fas fa-eye"></i>
        </div>
        <h3 style="font-size:20px;font-weight:800;margin:16px 0 12px;">Visi</h3>
        <p style="color:var(--text-secondary);line-height:1.8;font-size:14.5px;"><?= nl2br(htmlspecialchars($cp['vision'] ?? 'Menjadi perusahaan supplier material bangunan terdepan dan terpercaya di Indonesia.')) ?></p>
      </div>
      <div class="feature-card" data-animate>
        <div class="feature-icon" style="background:rgba(34,197,94,.12);color:#16a34a;font-size:28px;width:60px;height:60px;">
          <i class="fas fa-bullseye"></i>
        </div>
        <h3 style="font-size:20px;font-weight:800;margin:16px 0 12px;">Misi</h3>
        <div style="color:var(--text-secondary);line-height:1.8;font-size:14.5px;"><?= nl2br(htmlspecialchars($cp['mission'] ?? '')) ?></div>
      </div>
    </div>
  </div>
</section>

<!-- Values -->
<section class="section">
  <div class="container">
    <div style="text-align:center;margin-bottom:48px;" data-animate>
      <div class="section-tag">Nilai Perusahaan</div>
      <h2 class="section-title">Nilai yang Kami Pegang</h2>
    </div>
    <div class="grid grid-4" style="gap:20px;">
      <?php foreach([
        ['fas fa-star','#f59e0b','rgba(245,158,11,.12)','Kualitas','Standar mutu tertinggi dalam setiap produk dan layanan'],
        ['fas fa-handshake','#3b82f6','rgba(59,130,246,.12)','Integritas','Kejujuran dan transparansi dalam setiap transaksi bisnis'],
        ['fas fa-lightbulb','#8b5cf6','rgba(139,92,246,.12)','Inovasi','Terus berkembang mengikuti perkembangan industri konstruksi'],
        ['fas fa-heart','#ef4444','rgba(239,68,68,.12)','Kepedulian','Mengutamakan kepuasan dan kepercayaan pelanggan'],
      ] as [$icon,$color,$bg,$title,$desc]): ?>
      <div style="text-align:center;padding:28px 20px;background:var(--bg-surface);border:1px solid var(--border);border-radius:var(--radius-lg);transition:all .18s;" data-animate>
        <div style="width:56px;height:56px;border-radius:var(--radius-md);background:<?=$bg?>;color:<?=$color?>;display:flex;align-items:center;justify-content:center;font-size:22px;margin:0 auto 16px;">
          <i class="<?=$icon?>"></i>
        </div>
        <div style="font-size:15px;font-weight:700;margin-bottom:8px;"><?=$title?></div>
        <div style="font-size:13px;color:var(--text-muted);line-height:1.6;"><?=$desc?></div>
      </div>
      <?php endforeach; ?>
    </div>
  </div>
</section>
<!-- Social Media -->
<section class="section">
  <div class="container">
    <div style="text-align:center;margin-bottom:48px;" data-animate>
      <div class="section-tag">Ikuti Kami</div>
      <h2 class="section-title">Media Sosial</h2>
      <p class="section-subtitle" style="margin:14px auto 0;">Pantau aktivitas dan update terbaru dari PT Mitra Andalan Beton Pantura</p>
    </div>
    <div style="display:flex;justify-content:center;gap:20px;flex-wrap:wrap;" data-animate>
      <?php if (!empty($cp['social_facebook'])): ?>
      <a href="<?= $cp['social_facebook'] ?>" target="_blank" 
         style="display:flex;align-items:center;gap:12px;padding:14px 28px;background:#1877f2;color:white;border-radius:12px;text-decoration:none;font-size:15px;font-weight:600;transition:all 0.3s ease;box-shadow:0 4px 12px rgba(24,119,242,0.3);"
         onmouseover="this.style.transform='translateY(-3px)';this.style.boxShadow='0 8px 24px rgba(24,119,242,0.5)'"
         onmouseout="this.style.transform='translateY(0)';this.style.boxShadow='0 4px 12px rgba(24,119,242,0.3)'">
        <i class="fab fa-facebook-f" style="font-size:22px;"></i> Facebook
      </a>
      <?php endif; ?>

      <?php if (!empty($cp['social_instagram'])): ?>
      <a href="<?= $cp['social_instagram'] ?>" target="_blank" 
         style="display:flex;align-items:center;gap:12px;padding:14px 28px;background:linear-gradient(45deg,#f09433,#e6683c,#dc2743,#cc2366,#bc1888);color:white;border-radius:12px;text-decoration:none;font-size:15px;font-weight:600;transition:all 0.3s ease;box-shadow:0 4px 12px rgba(225,48,108,0.3);"
         onmouseover="this.style.transform='translateY(-3px)';this.style.boxShadow='0 8px 24px rgba(225,48,108,0.5)'"
         onmouseout="this.style.transform='translateY(0)';this.style.boxShadow='0 4px 12px rgba(225,48,108,0.3)'">
        <i class="fab fa-instagram" style="font-size:22px;"></i> Instagram
      </a>
      <?php endif; ?>

      <?php if (!empty($cp['social_youtube'])): ?>
      <a href="<?= $cp['social_youtube'] ?>" target="_blank" 
         style="display:flex;align-items:center;gap:12px;padding:14px 28px;background:#ff0000;color:white;border-radius:12px;text-decoration:none;font-size:15px;font-weight:600;transition:all 0.3s ease;box-shadow:0 4px 12px rgba(255,0,0,0.3);"
         onmouseover="this.style.transform='translateY(-3px)';this.style.boxShadow='0 8px 24px rgba(255,0,0,0.5)'"
         onmouseout="this.style.transform='translateY(0)';this.style.boxShadow='0 4px 12px rgba(255,0,0,0.3)'">
        <i class="fab fa-youtube" style="font-size:22px;"></i> YouTube
      </a>
      <?php endif; ?>

      <?php if (empty($cp['social_facebook']) && empty($cp['social_instagram']) && empty($cp['social_youtube'])): ?>
      <div style="text-align:center;padding:40px;color:var(--text-muted);">
        <i class="fas fa-share-alt" style="font-size:48px;opacity:0.3;display:block;margin-bottom:12px;"></i>
        <p>Belum ada link media sosial yang dikonfigurasi.</p>
      </div>
      <?php endif; ?>
    </div>
  </div>
</section>
</div>

<?php include __DIR__ . '/includes/public_footer.php'; ?>
