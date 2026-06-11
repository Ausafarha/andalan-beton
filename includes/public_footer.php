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
       <p style="text-align:center; margin:0 auto; max-width:260px;"><?= htmlspecialchars($cp['description'] ? substr($cp['description'],0,120).'...' : 'Supplier material bangunan terpercaya.') ?></p>
        <div style="display:flex;gap:10px;margin-top:16px;">
          <?php if(!empty($cp['social_facebook'])):?><a href="<?=$cp['social_facebook']?>" target="_blank" style="width:34px;height:34px;background:rgba(255,255,255,0.1);border-radius:50%;display:flex;align-items:center;justify-content:center;color:white;font-size:14px;"><i class="fab fa-facebook-f"></i></a><?php endif;?>
          <?php if(!empty($cp['social_instagram'])):?><a href="<?=$cp['social_instagram']?>" target="_blank" style="width:34px;height:34px;background:rgba(255,255,255,0.1);border-radius:50%;display:flex;align-items:center;justify-content:center;color:white;font-size:14px;"><i class="fab fa-instagram"></i></a><?php endif;?>
          <?php if(!empty($cp['whatsapp'])):?><a href="https://wa.me/<?=preg_replace('/[^0-9]/','',$cp['whatsapp'])?>" target="_blank" style="width:34px;height:34px;background:rgba(255,255,255,0.1);border-radius:50%;display:flex;align-items:center;justify-content:center;color:white;font-size:14px;"><i class="fab fa-whatsapp"></i></a><?php endif;?>
        </div>
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
          $cats = Database::fetchAll("SELECT name FROM material_categories LIMIT 5");
          foreach($cats as $c): ?>
          <li><a href="<?=APP_URL?>/produk.php"><?=htmlspecialchars($c['name'])?></a></li>
          <?php endforeach; ?>
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
      <a href="<?=APP_URL?>/admin/login.php" style="color:rgba(255,255,255,0.3);font-size:11px;"><i class="fas fa-lock"></i> Admin</a>
    </div>
  </div>
</footer>
<script src="<?=ASSETS_URL?>js/main.js"></script>
</body>
</html>
