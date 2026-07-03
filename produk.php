<?php
require_once __DIR__ . '/config/database.php';
require_once __DIR__ . '/config/app.php';
initSession();

$currentSlug = 'produk';
$pageTitle   = 'Produk Material';

$search   = get('search');
$catSlug  = get('kategori');
$page     = max(1, getInt('page', 1));

$categories = Database::fetchAll("SELECT * FROM material_categories ORDER BY name");

$params = [];
$where  = ["m.is_active = true"];

if ($search) {
    $where[]  = "(m.name ILIKE ? OR m.description ILIKE ?)";
    $params[] = "%$search%";
    $params[] = "%$search%";
}

if ($catSlug) {
    $where[]  = "mc.slug = ?";
    $params[] = $catSlug;
}

$whereStr = implode(' AND ', $where);

$totalCount = (int)Database::fetchColumn("
    SELECT COUNT(*) FROM materials m
    LEFT JOIN material_categories mc ON m.category_id = mc.id
    WHERE $whereStr
", $params);

$perPage = 12;
$pages   = (int)ceil($totalCount / $perPage);
$offset  = ($page - 1) * $perPage;

$materials = Database::fetchAll("
    SELECT m.*, mc.name AS category_name, mc.slug AS category_slug
    FROM materials m
    LEFT JOIN material_categories mc ON m.category_id = mc.id
    WHERE $whereStr
    ORDER BY m.is_featured DESC, m.name ASC
    LIMIT $perPage OFFSET $offset
", $params);

include __DIR__ . '/includes/public_head.php';
?>

<style>
/* Fix gambar product card - FULL & RAPI */
.product-card-img {
    width: 100%;
    aspect-ratio: 4/3;
    object-fit: contain;
    background: #ffffff;
    padding: 12px;
    cursor: pointer;
    transition: transform 0.3s ease;
}
.product-card-img:hover {
    transform: scale(1.03);
}

/* Modal Popup */
.product-modal-overlay {
    position: fixed;
    inset: 0;
    background: rgba(0,0,0,0.75);
    z-index: 9999;
    display: none;
    align-items: center;
    justify-content: center;
    padding: 20px;
    backdrop-filter: blur(4px);
}
.product-modal-overlay.show {
    display: flex;
}
.product-modal-content {
    background: var(--bg-surface);
    border-radius: var(--radius-xl);
    max-width: 750px;
    width: 100%;
    max-height: 90vh;
    overflow-y: auto;
    box-shadow: var(--shadow-xl);
    position: relative;
    animation: modalFade 0.3s ease;
}
@keyframes modalFade {
    from { opacity: 0; transform: scale(0.95); }
    to { opacity: 1; transform: scale(1); }
}
.product-modal-close {
    position: absolute;
    top: 12px;
    right: 16px;
    background: rgba(0,0,0,0.05);
    border: none;
    font-size: 22px;
    cursor: pointer;
    width: 40px;
    height: 40px;
    border-radius: 50%;
    display: flex;
    align-items: center;
    justify-content: center;
    transition: 0.2s;
    z-index: 10;
    color: var(--text-primary);
}
.product-modal-close:hover {
    background: rgba(0,0,0,0.1);
}
.product-modal-body {
    display: grid;
    grid-template-columns: 1fr 1fr;
    gap: 24px;
    padding: 24px;
}
.product-modal-image {
    display: flex;
    align-items: center;
    justify-content: center;
    background: var(--bg-muted);
    border-radius: var(--radius-lg);
    min-height: 300px;
    padding: 16px;
}
.product-modal-image img {
    max-width: 100%;
    max-height: 350px;
    object-fit: contain;
}
.product-modal-info {
    display: flex;
    flex-direction: column;
    gap: 10px;
}
.product-modal-info h2 {
    font-size: 22px;
    font-weight: 800;
    margin: 0;
    color: var(--text-primary);
}
.product-modal-info .category {
    display: inline-block;
    background: var(--brand-50);
    color: var(--brand-600);
    padding: 4px 12px;
    border-radius: 20px;
    font-size: 12px;
    font-weight: 600;
    width: fit-content;
}
.product-modal-info .price {
    font-size: 24px;
    font-weight: 800;
    color: var(--brand-600);
    margin: 4px 0;
}
.product-modal-info .unit {
    font-size: 14px;
    font-weight: 400;
    color: var(--text-muted);
}
.product-modal-info .desc {
    color: var(--text-secondary);
    line-height: 1.7;
    font-size: 14px;
    margin: 4px 0;
}
.product-modal-info .btn {
    margin-top: 8px;
}
@media (max-width: 640px) {
    .product-modal-body {
        grid-template-columns: 1fr;
        padding: 16px;
    }
    .product-modal-image {
        min-height: 200px;
    }
    .product-modal-info h2 {
        font-size: 18px;
    }
}
</style>

<div style="padding-top:70px;">

<!-- Page Header -->
<div style="background: linear-gradient(135deg, #0f172a 0%, #20bc95 50%, #0f172a 100%); padding:60px 0;">
  <div class="container" style="text-align:center;">
    <div class="section-tag" style="color:var(--brand-300);">Katalog Produk</div>
    <h1 style="font-size:clamp(28px,5vw,44px);font-weight:800;color:white;margin-top:10px;">Material Bangunan Berkualitas</h1>
    <p style="color:rgba(255,255,255,.6);font-size:15px;margin-top:12px;">Temukan material bangunan terbaik untuk proyek Anda</p>
    <!-- Search -->
    <form method="GET" style="max-width:500px;margin:24px auto 0;display:flex;gap:10px;">
      <input type="hidden" name="kategori" value="<?= htmlspecialchars($catSlug) ?>">
      <div style="flex:1;position:relative;">
        <i class="fas fa-search" style="position:absolute;left:14px;top:50%;transform:translateY(-50%);color:rgba(255,255,255,.4);"></i>
        <input type="text" name="search" class="form-control" placeholder="Cari material..." value="<?= htmlspecialchars($search) ?>"
          style="background:rgba(255,255,255,.1);border:1.5px solid rgba(255,255,255,.2);color:white;padding-left:42px;">
      </div>
      <button type="submit" class="btn btn-primary"><i class="fas fa-search"></i></button>
    </form>
  </div>
</div>

<section class="section">
  <div class="container">
    <div class="grid" style="grid-template-columns:220px 1fr;gap:32px;align-items:start;">

      <!-- Sidebar Filter -->
      <div>
        <div class="card" style="position:sticky;top:90px;">
          <div class="card-header"><div class="card-title">Kategori</div></div>
          <div class="card-body" style="padding:12px;">
            <a href="<?= APP_URL ?>/produk.php<?= $search?"?search=".urlencode($search):'' ?>"
               style="display:flex;justify-content:space-between;align-items:center;padding:10px 12px;border-radius:8px;font-size:13.5px;font-weight:600;color:<?= !$catSlug?'var(--brand-600)':'var(--text-secondary)'?>;background:<?= !$catSlug?'var(--brand-50)':'transparent'?>;text-decoration:none;margin-bottom:4px;transition:all .18s;">
              <span><i class="fas fa-th" style="width:18px;margin-right:8px;"></i>Semua Produk</span>
              <span style="font-size:12px;background:var(--bg-muted);padding:2px 8px;border-radius:20px;"><?= $totalCount ?></span>
            </a>
            <?php foreach ($categories as $cat):
              $catCount = Database::fetchColumn("SELECT COUNT(*) FROM materials m JOIN material_categories mc ON m.category_id=mc.id WHERE mc.slug=? AND m.is_active=true", [$cat['slug']]);
              $isActive = $catSlug === $cat['slug'];
            ?>
            <a href="?kategori=<?= urlencode($cat['slug']) ?><?= $search?"&search=".urlencode($search):'' ?>"
               style="display:flex;justify-content:space-between;align-items:center;padding:10px 12px;border-radius:8px;font-size:13.5px;font-weight:<?=$isActive?'600':'500'?>;color:<?=$isActive?'var(--brand-600)':'var(--text-secondary)'?>;background:<?=$isActive?'var(--brand-50)':'transparent'?>;text-decoration:none;margin-bottom:4px;transition:all .18s;">
              <span><i class="fas fa-chevron-right" style="width:18px;margin-right:6px;font-size:10px;"></i><?= htmlspecialchars($cat['name']) ?></span>
              <span style="font-size:12px;background:var(--bg-muted);padding:2px 8px;border-radius:20px;"><?= $catCount ?></span>
            </a>
            <?php endforeach; ?>
          </div>
        </div>
      </div>

      <!-- Product Grid -->
      <div>
        <div style="display:flex;align-items:center;justify-content:space-between;margin-bottom:20px;flex-wrap:wrap;gap:10px;">
          <div style="font-size:14px;color:var(--text-muted);">
            Menampilkan <strong><?= count($materials) ?></strong> dari <strong><?= $totalCount ?></strong> produk
            <?php if ($catSlug): ?> dalam <strong><?= htmlspecialchars($catSlug) ?></strong><?php endif; ?>
            <?php if ($search): ?> untuk "<strong><?= htmlspecialchars($search) ?></strong>"<?php endif; ?>
          </div>
          <?php if ($search || $catSlug): ?>
          <a href="<?= APP_URL ?>/produk.php" class="btn btn-secondary btn-sm"><i class="fas fa-times"></i> Reset Filter</a>
          <?php endif; ?>
        </div>

        <?php if (empty($materials)): ?>
          <div class="empty-state card" style="padding:60px;">
            <div class="empty-state-icon"><i class="fas fa-search"></i></div>
            <div class="empty-state-title">Produk tidak ditemukan</div>
            <div class="empty-state-desc">Coba ubah kata kunci pencarian atau pilih kategori lain.</div>
            <a href="<?= APP_URL ?>/produk.php" class="btn btn-primary" style="margin-top:16px;">Lihat Semua Produk</a>
          </div>
        <?php else: ?>
          <div class="grid grid-3" style="gap:20px;">
            <?php foreach ($materials as $mat): ?>
            <div class="product-card" data-animate>
              <?php if ($mat['image']): ?>
                <img src="<?= uploadUrl($mat['image']) ?>" 
                     alt="<?= htmlspecialchars($mat['name']) ?>" 
                     class="product-card-img"
                     onclick="openProductModal(<?= htmlspecialchars(json_encode($mat)) ?>)">
              <?php else: ?>
                <div class="product-card-img-placeholder" onclick="openProductModal(<?= htmlspecialchars(json_encode($mat)) ?>)">🧱</div>
              <?php endif; ?>
              <div class="product-card-body">
                <div class="product-card-category"><?= htmlspecialchars($mat['category_name'] ?? 'Material') ?></div>
                <div class="product-card-name"><?= htmlspecialchars($mat['name']) ?></div>
                <?php if ($mat['description']): ?>
                <div class="product-card-desc"><?= htmlspecialchars($mat['description']) ?></div>
                <?php endif; ?>
                <div class="product-card-price">
                  <?= formatRupiah($mat['price']) ?>
                  <span class="product-card-unit">/ <?= $mat['unit'] ?></span>
                </div>
                <?php if ($mat['is_featured']): ?>
                <span class="badge badge-info" style="margin-top:8px;"><i class="fas fa-star"></i> Unggulan</span>
                <?php endif; ?>
                <div style="display:flex;gap:8px;margin-top:14px;">
                    <a href="<?= APP_URL ?>/pesan.php?material=<?= $mat['id'] ?>" class="btn btn-primary" style="flex:1;justify-content:center;padding:8px 4px;font-size:13px;">
                        <i class="fas fa-shopping-cart"></i> Pesan
                    </a>
                    <a href="<?= APP_URL ?>/pesan.php?material=<?= $mat['id'] ?>&qty=1&added=1" class="btn btn-secondary" style="flex:1;justify-content:center;padding:8px 4px;font-size:13px;border-style:dashed;">
                        <i class="fas fa-cart-plus"></i> Keranjang
                    </a>
                </div>
              </div>
            </div>
            <?php endforeach; ?>
          </div>

          <!-- Pagination -->
          <?php if ($pages > 1): ?>
          <div style="display:flex;justify-content:center;margin-top:32px;">
            <div class="pagination">
              <?php if ($page > 1): ?>
                <a href="?page=<?=$page-1?>&kategori=<?=urlencode($catSlug)?>&search=<?=urlencode($search)?>" class="page-btn"><i class="fas fa-chevron-left"></i></a>
              <?php endif; ?>
              <?php for ($p=1;$p<=$pages;$p++): ?>
                <a href="?page=<?=$p?>&kategori=<?=urlencode($catSlug)?>&search=<?=urlencode($search)?>" class="page-btn <?=$p==$page?'active':''?>"><?=$p?></a>
              <?php endfor; ?>
              <?php if ($page < $pages): ?>
                <a href="?page=<?=$page+1?>&kategori=<?=urlencode($catSlug)?>&search=<?=urlencode($search)?>" class="page-btn"><i class="fas fa-chevron-right"></i></a>
              <?php endif; ?>
            </div>
          </div>
          <?php endif; ?>
        <?php endif; ?>
      </div>
    </div>
  </div>
</section>

</div>

<!-- PRODUCT POPUP MODAL -->
<div id="productModal" class="product-modal-overlay" onclick="closeProductModal()">
    <div class="product-modal-content" onclick="event.stopPropagation()">
        <button class="product-modal-close" onclick="closeProductModal()"><i class="fas fa-times"></i></button>
        <div class="product-modal-body">
            <div class="product-modal-image">
                <img id="modalImage" src="" alt="Product Image">
            </div>
            <div class="product-modal-info">
                <span class="category" id="modalCategory">Material</span>
                <h2 id="modalName">Nama Produk</h2>
                <div class="price" id="modalPrice">Rp 0</div>
                <div class="unit" id="modalUnit">/ unit</div>
                <p class="desc" id="modalDesc">Deskripsi produk</p>
                <div style="display:flex;gap:8px;margin-top:8px;">
                    <a href="#" id="modalOrderBtn" class="btn btn-primary" style="flex:1;justify-content:center;">
                        <i class="fas fa-shopping-cart"></i> Pesan
                    </a>
                    <a href="#" id="modalCartBtn" class="btn btn-secondary" style="flex:1;justify-content:center;border-style:dashed;">
                        <i class="fas fa-cart-plus"></i> Keranjang
                    </a>
                </div>
            </div>
        </div>
    </div>
</div>

<?php include __DIR__ . '/includes/public_footer.php'; ?>

<script>
function openProductModal(product) {
    let imageUrl = '';
    if (product.image) {
        if (product.image.startsWith('http://') || product.image.startsWith('https://')) {
            imageUrl = product.image;
        } else {
            imageUrl = '<?= uploadUrl('') ?>' + product.image;
        }
    }
    document.getElementById('modalImage').src = imageUrl;
    document.getElementById('modalName').textContent = product.name || 'Produk';
    document.getElementById('modalCategory').textContent = product.category_name || 'Material';
    document.getElementById('modalPrice').textContent = 'Rp ' + Number(product.price || 0).toLocaleString('id-ID');
    document.getElementById('modalUnit').textContent = '/ ' + (product.unit || 'unit');
    document.getElementById('modalDesc').textContent = product.description || 'Deskripsi produk tidak tersedia.';
    document.getElementById('modalOrderBtn').href = '<?= APP_URL ?>/pesan.php?material=' + product.id;
    document.getElementById('modalCartBtn').href = '<?= APP_URL ?>/pesan.php?material=' + product.id + '&qty=1&added=1';
    document.getElementById('productModal').classList.add('show');
    document.body.style.overflow = 'hidden';
}

function closeProductModal() {
    document.getElementById('productModal').classList.remove('show');
    document.body.style.overflow = '';
}

document.addEventListener('keydown', function(e) {
    if (e.key === 'Escape') closeProductModal();
});
</script>