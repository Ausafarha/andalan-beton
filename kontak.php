<?php
require_once __DIR__ . '/config/database.php';
require_once __DIR__ . '/config/app.php';
initSession();

$currentSlug = 'kontak';
$pageTitle   = 'Kontak';
$cp          = getCompanyProfile();

include __DIR__ . '/includes/public_head.php';
?>

<div style="padding-top:70px;">

<div style="background:linear-gradient(135deg,#0f172a,#1e3a8a);padding:60px 0;">
  <div class="container" style="text-align:center;">
    <div class="section-tag" style="color:var(--brand-300);">Hubungi Kami</div>
    <h1 style="font-size:clamp(28px,5vw,44px);font-weight:800;color:white;margin-top:10px;">Kami Siap Membantu Anda</h1>
    <p style="color:rgba(255,255,255,.6);font-size:15px;margin-top:12px;">Hubungi kami untuk konsultasi, penawaran, atau informasi lebih lanjut</p>
  </div>
</div>

<section class="section">
  <div class="container">
    <div class="grid" style="grid-template-columns:1fr 1.4fr;gap:48px;align-items:start;">

      <!-- Contact Info -->
      <div>
        <h2 style="font-size:26px;font-weight:800;margin-bottom:24px;">Informasi Kontak</h2>

        <?php foreach([
          ['fas fa-map-marker-alt','#ef4444','Alamat',($cp['address']??'-').', '.($cp['city']??'').', '.($cp['province']??'').' '.($cp['postal_code']??'')],
          ['fas fa-phone','#3b82f6','Telepon',$cp['phone']??'-'],
          ['fab fa-whatsapp','#25d366','WhatsApp',$cp['whatsapp']??'-'],
          ['fas fa-envelope','#f59e0b','Email',$cp['email']??'-'],
          ['fas fa-clock','#8b5cf6','Jam Operasional','Senin - Sabtu: 08:00 - 17:00 WIB'],
        ] as [$icon,$color,$label,$val]): ?>
        <div style="display:flex;gap:16px;margin-bottom:24px;padding:18px;background:var(--bg-muted);border-radius:var(--radius-lg);border:1px solid var(--border);">
          <div style="width:44px;height:44px;border-radius:var(--radius-md);background:<?=$color?>22;color:<?=$color?>;display:flex;align-items:center;justify-content:center;font-size:18px;flex-shrink:0;">
            <i class="<?=$icon?>"></i>
          </div>
          <div>
            <div style="font-size:12px;font-weight:600;color:var(--text-muted);text-transform:uppercase;letter-spacing:.06em;"><?=$label?></div>
            <div style="font-size:14.5px;font-weight:600;margin-top:3px;color:var(--text-primary);"><?=htmlspecialchars($val)?></div>
          </div>
        </div>
        <?php endforeach; ?>

        <!-- Quick Actions -->
        <div style="display:flex;flex-direction:column;gap:10px;margin-top:8px;">
          <?php if($cp['whatsapp']):?>
          <a href="https://wa.me/<?=preg_replace('/[^0-9]/','',$cp['whatsapp'])?>" target="_blank" class="btn btn-lg w-100" style="background:#25d366;color:white;border-color:#25d366;justify-content:center;">
            <i class="fab fa-whatsapp"></i> Chat WhatsApp Sekarang
          </a>
          <?php endif; ?>
          <a href="<?=APP_URL?>/pesan.php" class="btn btn-primary btn-lg w-100" style="justify-content:center;">
            <i class="fas fa-shopping-cart"></i> Buat Pesanan Online
          </a>
        </div>
      </div>

     <!-- Map & Social -->
<div>
    <!-- Google Maps -->
    <?php if(!empty($cp['maps_embed'])): ?>
    <div style="border-radius:var(--radius-xl);overflow:hidden;border:1px solid var(--border);margin-bottom:24px;box-shadow:var(--shadow-md);">
        <iframe src="<?=htmlspecialchars($cp['maps_embed'])?>" width="100%" height="350" style="border:0;" allowfullscreen="" loading="lazy"></iframe>
    </div>
    <?php else: ?>
    <div style="border-radius:var(--radius-xl);background:var(--bg-muted);border:1px solid var(--border);height:350px;display:flex;flex-direction:column;align-items:center;justify-content:center;margin-bottom:24px;color:var(--text-muted);">
        <i class="fas fa-map" style="font-size:48px;margin-bottom:12px;"></i>
        <div style="font-size:14px;">Lokasi: <?= htmlspecialchars(($cp['city'] ?? '') . ' ' . ($cp['province'] ?? '')) ?></div>
    </div>
    <?php endif; ?>
    
    <!-- Social Media -->
    <div style="padding:20px;background:var(--bg-surface);border:1px solid var(--border);border-radius:var(--radius-lg);">
        <div style="font-size:14px;font-weight:700;margin-bottom:14px;">Ikuti Kami di Media Sosial</div>
        <div style="display:flex;gap:12px;">
            <?php if($cp['social_facebook'] ?? false): ?>
            <a href="<?=$cp['social_facebook']?>" target="_blank" style="flex:1;padding:12px;background:#1877f2;color:white;border-radius:var(--radius-md);text-align:center;font-size:13.5px;font-weight:600;text-decoration:none;display:flex;align-items:center;justify-content:center;gap:8px;">
                <i class="fab fa-facebook-f"></i> Facebook
            </a>
            <?php endif; ?>
            <?php if($cp['social_instagram'] ?? false): ?>
            <a href="<?=$cp['social_instagram']?>" target="_blank" style="flex:1;padding:12px;background:linear-gradient(45deg,#f09433,#e6683c,#dc2743,#cc2366,#bc1888);color:white;border-radius:var(--radius-md);text-align:center;font-size:13.5px;font-weight:600;text-decoration:none;display:flex;align-items:center;justify-content:center;gap:8px;">
                <i class="fab fa-instagram"></i> Instagram
            </a>
            <?php endif; ?>
            <?php if($cp['social_youtube'] ?? false): ?>
            <a href="<?=$cp['social_youtube']?>" target="_blank" style="flex:1;padding:12px;background:#ff0000;color:white;border-radius:var(--radius-md);text-align:center;font-size:13.5px;font-weight:600;text-decoration:none;display:flex;align-items:center;justify-content:center;gap:8px;">
                <i class="fab fa-youtube"></i> YouTube
            </a>
            <?php endif; ?>
        </div>
    </div>
</div>
    </div>
  </div>
</section>

</div>

<?php include __DIR__ . '/includes/public_footer.php'; ?>
