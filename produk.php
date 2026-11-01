<?php
require_once __DIR__ . '/config/database.php';
require_once __DIR__ . '/config/app.php';
initSession();

$currentSlug = 'produk';
$pageTitle   = 'Produk Material';

$search   = get('search');
$catSlug  = get('kategori');
$page     = max(1, getInt('page', 1));

$categories = Database::fetchAll("SELECT * FROM material_categories WHERE slug IN ('beton', 'precast') ORDER BY name");

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

// Hitung total item di keranjang
function getCartTotalItems() {
    if (!isset($_SESSION['cart']) || !is_array($_SESSION['cart'])) return 0;
    return array_sum($_SESSION['cart']);
}
$cartTotal = getCartTotalItems();
?>

<style>
.section-produk-list {
  position: relative;
  background-image: url('<?= ASSETS_URL ?>img/background.png');
  background-size: cover;
  background-position: center top;
  background-repeat: repeat-y;
  background-color: #ffffff;
  overflow: hidden;
}

.section-produk-list::before {
  content: '';
  position: absolute;
  inset: 0;
  background: rgba(133, 133, 132, 0);
  z-index: 0;
  pointer-events: none;
}

.section-produk-list > .container {
  position: relative;
  z-index: 1;
}
[data-theme="dark"] .section-produk-list::before {
  background: rgba(6, 28, 1, 0.6);
}
</style>

<div id="produk-page">

<!-- HERO -->
<section class="hero">
  <div class="hero-slides" id="hero-slides-produk">
    <div class="hero-slide active" style="background-image:url('<?= ASSETS_URL ?>img/mgng1.jpeg');"></div>
  </div>

  <div class="hero-content">
    <div data-animate>
      <h1>
        <span style="color:#fff;">MATERIAL BANGUNAN</span><br>
        <span style="color:#16a34a;">BERKUALITAS</span>
      </h1>
      <p style="color:rgba(255,255,255,.75);font-size:15px;line-height:1.7;max-width:480px;margin:0 auto 28px;">
        Menyediakan berbagai material bangunan terbaik dengan kualitas terjamin untuk mendukung keberhasilan proyek Anda.
      </p>
      <form method="GET" class="hero-search-form">
        <input type="hidden" name="kategori" value="<?= htmlspecialchars($catSlug) ?>">
        <input type="text" name="search" placeholder="Cari material" value="<?= htmlspecialchars($search) ?>">
        <button type="submit"><i class="fas fa-search"></i></button>
      </form>
    </div>
  </div>
</section>

<!-- KOTAK PUTIH BACKGROUND UTAMA -->
<div style="background: #ffffff; padding: 30px 0; position: relative; z-index: 2;">

  <!-- KATEGORI TABS -->
  <?php
  $allCount = (int)Database::fetchColumn("SELECT COUNT(*) FROM materials WHERE is_active = true");
  $catIcons = [
    'beton'   => 'fa-truck-moving',
    'precast' => 'fa-cubes',
  ];
  ?>
  <div class="produk-kategori-wrap" style="background: transparent;">
    <div class="container">
      <div class="produk-kategori-pill">
        
        <a href="<?= APP_URL ?>/produk.php<?= $search ? '?search=' . urlencode($search) : '' ?>"
           class="produk-filter-btn <?= !$catSlug ? 'active' : '' ?>"
           <?= !$catSlug ? 'aria-current="page"' : '' ?>>
          <span class="pf-icon"><i class="fas fa-th-large"></i></span>
          <span class="pf-text">
            <strong>Semua Produk</strong>
            <small><?= $allCount ?> Produk</small>
          </span>
          <i class="fas fa-arrow-right pf-arrow"></i>
        </a>

        <?php foreach ($categories as $cat):
          $catCount = Database::fetchColumn(
            "SELECT COUNT(*) FROM materials m JOIN material_categories mc ON m.category_id = mc.id WHERE mc.slug = ? AND m.is_active = true",
            [$cat['slug']]
          );
          $isActive = $catSlug === $cat['slug'];
          $icon     = $catIcons[$cat['slug']] ?? 'fa-box';
        ?>
        <a href="?kategori=<?= urlencode($cat['slug']) ?><?= $search ? '&search=' . urlencode($search) : '' ?>"
           class="produk-filter-btn <?= $isActive ? 'active' : '' ?>"
           <?= $isActive ? 'aria-current="page"' : '' ?>>
          <span class="pf-icon"><i class="fas <?= $icon ?>"></i></span>
          <span class="pf-text">
            <strong><?= htmlspecialchars($cat['name']) ?></strong>
            <small><?= $catCount ?> Produk</small>
          </span>
          <i class="fas fa-arrow-right pf-arrow"></i>
        </a>
        <?php endforeach; ?>
      </div>
    </div>
  </div>

  <!-- SECTION PRODUK -->
  <section class="section section-produk-list" style="background: transparent; background-image: none;">
    <div class="container">
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
        <div class="grid grid-4" style="gap:20px;">
          <?php foreach ($materials as $mat): ?>
            <div data-animate class="produk-item">
              <?php if ($mat['image']): ?>
                <img src="<?= uploadUrl($mat['image']) ?>" alt="<?= htmlspecialchars($mat['name']) ?>"
                    class="produk-img"
                    onclick="openProductModal(<?= htmlspecialchars(json_encode($mat)) ?>)">
              <?php else: ?>
                <div class="produk-img produk-img-empty"
                    onclick="openProductModal(<?= htmlspecialchars(json_encode($mat)) ?>)">Foto Produk</div>
              <?php endif; ?>
              <div class="produk-body">
                <span class="product-category-badge"><?= htmlspecialchars($mat['category_name'] ?? 'Material') ?></span>
                <div class="produk-name"><?= htmlspecialchars($mat['name']) ?></div>
                <div class="produk-price"><?= formatRupiah($mat['price']) ?> <span class="produk-unit">/ <?= $mat['unit'] ?></span></div>
                <div class="produk-actions">
                  <div class="produk-qty">
                    <button type="button" onclick="let i=this.nextElementSibling; i.value=Math.max(1,(parseInt(i.value)||1)-1);">−</button>
                    <input type="number" id="qty-<?= $mat['id'] ?>" value="1" min="1">
                    <button type="button" onclick="let i=this.previousElementSibling; i.value=(parseInt(i.value)||1)+1;">+</button>
                  </div>
                  <button type="button" onclick="addToCart(<?= $mat['id'] ?>)" class="produk-order-btn" style="border:none;cursor:pointer;">
                    <i class="fas fa-shopping-cart"></i> + Keranjang
                  </button>
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
                <div style="display:flex;gap:8px;margin-top:12px;">
                    <a href="#" id="modalOrderBtn" class="btn btn-primary" style="flex:1;justify-content:center;">
                        <i class="fas fa-shopping-cart"></i> Pesan
                    </a>
                </div>
            </div>
        </div>
    </div>
</div>

</div>

<?php include __DIR__ . '/includes/public_footer.php'; ?>

<script>
function updateCartBadge(count) {
    const links = document.querySelectorAll('a');
    let targetLink = null;
    for (let link of links) {
        if (link.textContent.trim() === 'Pesan Sekarang' || link.textContent.includes('Pesan Sekarang')) {
            targetLink = link;
            break;
        }
        if (link.getAttribute('href') && link.getAttribute('href').includes('pesan.php')) {
            if (link.textContent.includes('Pesan') || link.textContent.includes('Pesan Sekarang')) {
                targetLink = link;
                break;
            }
        }
    }
    if (!targetLink) {
        targetLink = document.querySelector('a[href*="pesan.php"]');
    }
    if (targetLink) {
        let oldBadge = targetLink.querySelector('.cart-badge-header');
        if (oldBadge) oldBadge.remove();
        if (count > 0) {
            const badge = document.createElement('span');
            badge.className = 'cart-badge-header';
            badge.textContent = count;
            targetLink.appendChild(badge);
        }
    }
}

document.addEventListener('DOMContentLoaded', function() {
    updateCartBadge(<?= $cartTotal ?>);
});
function addToCart(materialId) {
    const qtyInput = document.getElementById('qty-' + materialId);
    const qty = qtyInput ? parseInt(qtyInput.value) || 1 : 1;

    let formData = new URLSearchParams();
    formData.append('material', materialId);
    formData.append('qty', qty);

    fetch('<?= APP_URL ?>/pesan.php?action=add_cart', {
        method: 'POST',
        headers: {
            'Content-Type': 'application/x-www-form-urlencoded',
            'X-Requested-With': 'XMLHttpRequest'
        },
        body: formData.toString()
    })
    .then(response => response.json())
    .then(data => {
        if (data.success) {
            // Update badge jumlah keranjang jika ada
            updateCartBadge(data.cart_count);
            
            // Berikan notifikasi kecil / alert sukses (bisa diganti toast jika mau)
            alert('✅ ' + data.message);
        } else {
            alert('❌ Gagal: ' + (data.message || 'Terjadi kesalahan'));
        }
    })
    .catch(error => {
        console.error('Error:', error);
        alert('Terjadi kesalahan koneksi.');
    });
}

function openProductModal(product) {
    let imageUrl = '';
    if (product.image) {
        if (product.image.startsWith('http://') || product.image.startsWith('https://')) {
            imageUrl = product.image;
        } else {
            imageUrl = '<?= uploadUrl('') ?>' + product.image;
        }
    }
    document.getElementById('modalImage').src = imageUrl || '<?= APP_URL ?>/assets/img/placeholder.png';
    document.getElementById('modalName').textContent = product.name || 'Produk';
    document.getElementById('modalCategory').textContent = product.category_name || 'Material';
    document.getElementById('modalPrice').textContent = 'Rp ' + Number(product.price || 0).toLocaleString('id-ID');
    document.getElementById('modalUnit').textContent = '/ ' + (product.unit || 'unit');
    document.getElementById('modalDesc').textContent = product.description || 'Deskripsi produk tidak tersedia.';
    document.getElementById('modalOrderBtn').href = '<?= APP_URL ?>/pesan.php?material=' + product.id;
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