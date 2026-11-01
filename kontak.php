<?php
require_once __DIR__ . '/config/database.php';
require_once __DIR__ . '/config/app.php';
initSession();

$currentSlug = 'kontak';
$pageTitle   = 'Kontak';$cp          = getCompanyProfile();

include __DIR__ . '/includes/public_head.php';

$heroTitle = [ 'pre'       => 'KAMI SIAP MEMBANTU ', 'highlight' => 'ANDA', ];$heroBody = '
      <p style="color:rgba(255,255,255,0.85);font-size:15px;line-height:1.8;max-width:600px;margin:0;">
        Hubungi kami untuk konsultasi, pemesanan, penawaran, atau informasi lebih lanjut.
      </p>';
?>
<style>
  #kontak-page .hero { justify-content: center !important; }
  #kontak-page .hero-content {
    margin: 0 auto !important;
    max-width: 700px !important;
    width: 100%;
    padding-left: 24px !important;
    padding-right: 24px !important;
    text-align: center !important;
  }
  #kontak-page .hero-content h1,
  #kontak-page .hero-content p {
    margin-left: auto !important;
    margin-right: auto !important;
    text-align: center !important;
  }
  #kontak-page .hero h1 { font-size: clamp(24px, 4vw, 36px) !important; }

[data-theme="dark"] #kontak-page .kontak-info-bar {
  background: #062A1C !important;
}

  .contact-grid {
    display: grid;
    grid-template-columns: 1fr;
    gap: 24px;
    align-items: start;
  }
  .social-buttons-grid {
    display: grid;
    grid-template-columns: repeat(2, 1fr);
    gap: 10px;
  }
  @media (min-width: 768px) {
    .contact-grid {
      grid-template-columns: 1fr 1fr;
      gap: 32px;
    }
    .social-buttons-grid {
      grid-template-columns: repeat(auto-fit, minmax(100px, 1fr));
    }
  }

  .section-kontak-info {
    position: relative;
    background-color: #ffffff;
  }

  .section-kontak-info::before {
    content: '';
    position: absolute;
    inset: 0;
    background-image: url('<?= ASSETS_URL ?>img/menu.png');
    background-size: cover;
    background-position: center top;
    background-repeat: repeat-y;
    opacity: 0.35;
    z-index: 0;
    pointer-events: none;
  }

  .kontak-card-box {
  box-shadow: 0 8px 24px rgba(0, 0, 0, 0.8);
}

  .section-kontak-info > .container {
    position: relative;
    z-index: 1;
  }

  .kontak-social-btn,
.kontak-cta-btn {
  transition: transform 0.25s ease, box-shadow 0.25s ease;
}
.kontak-social-btn:hover {
  transform: translateY(-3px);
}
.kontak-cta-btn:hover {
  transform: translateY(-3px);
  box-shadow: 0 10px 24px rgba(0,0,0,0.7);
}

  html[data-theme="dark"] .section-kontak-info {
    background-color: rgb(6, 28, 1) !important;
  }

  html[data-theme="dark"] .kontak-card-box {
    background: #0f2408 !important;
    border-color: rgba(255, 255, 255, 0.15) !important;
  }

  html[data-theme="dark"] .kontak-card-title {
    color: #ffffff !important;
  }

  @media (max-width: 768px) {
  .section-kontak-info .feature-card {
    padding: 0 !important;
  }
  .section-kontak-info .feature-icon {
    width: 100% !important;
    height: auto !important;
    justify-content: center !important;
  }
}
</style>

<div id="kontak-page">
<?php include __DIR__ . '/includes/hero.php'; ?>

<!-- INFO KONTAK -->
<div style="position:relative; margin-top:-70px; z-index:2;">

  <section class="kontak-info-bar" style="background:#388E3C; padding:28px 24px;">
    <h2 style="text-align:center; color:#fff; font-size:24px; font-weight:800; text-transform:uppercase; margin:0;">Informasi Kontak</h2>
  </section>

  <section class="section-kontak-info" style="padding:40px 24px;">
    <div class="container">
      <div class="grid grid-4" style="gap:20px;">
        <?php foreach([
        ['fas fa-map-marker-alt','Alamat',($cp['address']??'-').', '.($cp['city']??'').', '.($cp['province']??'').' '.($cp['postal_code']??'')],
          ['fas fa-phone','Telepon',($cp['phone']??'-').($cp['whatsapp'] ? "n".$cp['whatsapp'] : '')],
          ['fas fa-envelope','Email',$cp['email']??'-'],
          ['fas fa-clock','Jam Operasional',"Senin - Sabtun08:00 - 17:00"],
        ] as [$icon,$label,$val]): ?>
        <div data-animate class="feature-card" style="box-shadow: 0 8px 20px rgba(0,0,0,0.6);">
          <div class="feature-icon">
            <i class="<?=$icon?>"></i>
          </div>
          <div class="feature-card-body text-center">
            <div class="feature-title" style="text-transform:uppercase;"><?=$label?></div>
            <div class="feature-desc" style="white-space:pre-line;"><?= htmlspecialchars($val) ?></div>
          </div>
        </div>
        <?php endforeach; ?>
      </div>
    </div>
  </section>

  <section class="section section-kontak-info">
    <div class="container">
      <div class="contact-grid">

        <div>
          <div class="kontak-card-box" style="background:#fff; border-radius:var(--radius-xl); overflow:hidden; border:1px solid var(--border);">
            <div class="kontak-card-title" style="padding:16px 20px; font-weight:800; font-size:15px;">Lokasi Kami</div>
            <?php if (!empty($cp['maps_embed'])): ?>
              <iframe src="<?= htmlspecialchars($cp['maps_embed']) ?>" width="100%" height="350" style="border:0; display:block;" allowfullscreen="" loading="lazy"></iframe>
            <?php else: ?>
              <div style="background:#0f172a; height:350px; display:flex; align-items:center; justify-content:center; color:rgba(255,255,255,0.6); font-weight:700;">maps</div>
            <?php endif; ?>
          </div>
        </div>

        <div>
          <div class="kontak-card-box" style="background:#fff; border-radius:var(--radius-xl); border:1px solid var(--border); padding:20px; margin-bottom:16px;">
            <div class="kontak-card-title" style="font-size:15px; font-weight:800; margin-bottom:14px;">Media Sosial Kami</div>
            <div class="social-buttons-grid">
              <?php if($cp['social_facebook'] ?? false): ?>
              <a href="<?=$cp['social_facebook']?>" target="_blank" class="kontak-social-btn" style="padding:12px;background:#1877f2;color:white;border-radius:var(--radius-md);text-align:center;font-size:13.5px;font-weight:600;text-decoration:none;display:flex;align-items:center;justify-content:center;gap:8px;">
                <i class="fab fa-facebook-f"></i> Facebook
              </a>
              <?php endif; ?>
              <?php if($cp['social_instagram'] ?? false): ?>
              <a href="<?=$cp['social_instagram']?>" target="_blank" class="kontak-social-btn" style="padding:12px;background:linear-gradient(45deg,#f09433,#e6683c,#dc2743,#cc2366,#bc1888);color:white;border-radius:var(--radius-md);text-align:center;font-size:13.5px;font-weight:600;text-decoration:none;display:flex;align-items:center;justify-content:center;gap:8px;">
                <i class="fab fa-instagram"></i> Instagram
              </a>
              <?php endif; ?>
              <?php if($cp['social_youtube'] ?? false): ?>
              <a href="<?=$cp['social_youtube']?>" target="_blank" class="kontak-social-btn" style="padding:12px;background:#ff0000;color:white;border-radius:var(--radius-md);text-align:center;font-size:13.5px;font-weight:600;text-decoration:none;display:flex;align-items:center;justify-content:center;gap:8px;">
                <i class="fab fa-youtube"></i> YouTube
              </a>
              <?php endif; ?>
              <?php if($cp['social_tiktok'] ?? false): ?>
              <a href="<?=$cp['social_tiktok']?>" target="_blank" class="kontak-social-btn" style="padding:12px;background:#000000;color:white;border-radius:var(--radius-md);text-align:center;font-size:13.5px;font-weight:600;text-decoration:none;display:flex;align-items:center;justify-content:center;gap:8px;">
                <i class="fab fa-tiktok"></i> TikTok
              </a>
              <?php endif; ?>
            </div>
          </div>

         <div style="display:flex; flex-direction:column; gap:12px;">
          <?php if($cp['whatsapp']): ?>
          <a href="https://wa.me/<?=preg_replace('/[^0-9]/','',$cp['whatsapp'])?>" target="_blank" class="kontak-cta-btn" style="background:#16a34a; color:#fff; text-align:center; padding:14px; border-radius:10px; font-weight:700; text-decoration:none; box-shadow: 0 6px 16px rgba(0,0,0,0.6);">
            <i class="fab fa-whatsapp"></i> Check WhatsApp Sekarang
          </a>
          <?php endif; ?>
          <a href="<?=APP_URL?>/pesan.php" class="kontak-cta-btn" style="background:#16a34a; color:#fff; text-align:center; padding:14px; border-radius:10px; font-weight:700; text-decoration:none; box-shadow: 0 6px 16px rgba(0,0,0,0.6);">
            <i class="fas fa-shopping-cart"></i> Ayo Pesan Online Sekarang
          </a>
        </div>
        </div>
      </div>
    </div>
  </section>

</div>
</div>

<?php include __DIR__ . '/includes/public_footer.php'; ?>