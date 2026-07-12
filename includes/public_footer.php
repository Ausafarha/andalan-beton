<?php // includes/public_footer.php
$cp = getCompanyProfile();
?>
<footer class="footer">
  <div class="container">
    <div class="grid footer-grid" style="margin-bottom:40px;">
      <div class="footer-brand">
        <div style="display:flex;align-items:center;gap:10px;margin-bottom:12px;">
          <div style="width:36px;height:36px;background:linear-gradient(135deg,var(--brand-500),var(--brand-700));border-radius:8px;display:flex;align-items:center;justify-content:center;font-weight:800;color:white;font-size:14px;">AB</div>
          <div style="font-size:15px;font-weight:800;color:white;"><?= htmlspecialchars($cp['company_name'] ?? APP_NAME) ?></div>
        </div>
        <p style="text-align:center; margin:0 auto; max-width:300px; line-height:1.7;"><?= htmlspecialchars($cp['description'] ?? 'Industri Readymix dan Precast.') ?></p>
      </div>
      <div>
        <div class="footer-title">Menu</div>
        <ul class="footer-links">
          <li><a href="<?=APP_URL?>/">Beranda</a></li>
          <li><a href="<?=APP_URL?>/profil.php">Profil Perusahaan</a></li>
          <li><a href="<?=APP_URL?>/produk.php">Produk Material</a></li>
          <li><a href="<?=APP_URL?>/galeri.php">Galeri</a></li>
          <li><a href="<?=APP_URL?>/kontak.php">Kontak</a></li>
          <li><a href="<?=APP_URL?>/pesan.php">Pemesanan Online</a></li>
        </ul>
      </div>
      <div>
        <div class="footer-title">Produk</div>
        <ul class="footer-links">
          <?php
          // Hanya tampilkan kategori Beton dan Precast
          $cats = Database::fetchAll("SELECT name, slug FROM material_categories WHERE slug IN ('beton', 'precast') ORDER BY name");
          foreach($cats as $c): ?>
          <li><a href="<?=APP_URL?>/produk.php?kategori=<?=$c['slug']?>"><?=htmlspecialchars($c['name'])?></a></li>
          <?php endforeach; ?>
          <!-- Opsional tambahkan link "Semua Produk" -->
          <li><a href="<?=APP_URL?>/produk.php">Semua Produk</a></li>
        </ul>
      </div>
      <div>
        <div class="footer-title">Kontak</div>
        <ul class="footer-links">
          <?php if($cp['address']):?>
          <li style="display:flex;gap:8px;align-items:flex-start;margin-bottom:10px;"><i class="fas fa-map-marker-alt" style="margin-top:3px;color:var(--brand-400);"></i> <span><?=htmlspecialchars($cp['address'])?>, <?=htmlspecialchars($cp['city']??'')?></span></li>
          <?php endif;?>
          <?php if($cp['phone']):?>
          <li><i class="fas fa-phone" style="width:16px;color:var(--brand-400);"></i> <a href="tel:<?=$cp['phone']?>"><?=htmlspecialchars($cp['phone'])?></a></li>
          <?php endif;?>
          <?php if($cp['whatsapp']):?>
          <li><i class="fab fa-whatsapp" style="width:16px;color:var(--brand-400);"></i> <a href="https://wa.me/<?=preg_replace('/[^0-9]/','',$cp['whatsapp'])?>"><?=htmlspecialchars($cp['whatsapp'])?></a></li>
          <?php endif;?>
          <?php if($cp['email']):?>
          <li><i class="fas fa-envelope" style="width:16px;color:var(--brand-400);"></i> <a href="mailto:<?=$cp['email']?>"><?=htmlspecialchars($cp['email'])?></a></li>
          <?php endif;?>
        </ul>
      </div>
    </div>
    <div class="footer-bottom">
      <span>&copy; <?=date('Y')?> <?=htmlspecialchars($cp['company_name']??APP_NAME)?>. Hak cipta dilindungi.</span>
    </div>
  </div>
</footer>
<!-- PWA Service Worker -->
<script>
if ('serviceWorker' in navigator) {
    window.addEventListener('load', function() {
        navigator.serviceWorker.register('<?= APP_URL ?>/service-worker.js')
            .then(function(registration) {
                console.log('Service Worker registered successfully');
            })
            .catch(function(err) {
                console.log('Service Worker registration failed: ', err);
            });
    });
}
</script>
</body>
</html>