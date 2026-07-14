<?php
require_once __DIR__ . '/../../../config/database.php';
require_once __DIR__ . '/../../../config/app.php';
initSession();
requireLogin();

// Tambahkan header anti-cache agar browser tidak meng-cache halaman ini
header("Cache-Control: no-store, no-cache, must-revalidate, max-age=0");
header("Cache-Control: post-check=0, pre-check=0", false);
header("Pragma: no-cache");

$pageTitle    = 'Material';
$pageSubtitle = 'Kelola data material dan produk';

$search   = get('search');
$category = getInt('category');
$status   = get('status');
$type     = get('type'); 
$page     = max(1, getInt('page', 1));

$params = [];
$where  = ['1=1'];

if ($search) {
    $where[]  = "(m.name ILIKE ? OR m.code ILIKE ?)";
    $params[] = "%$search%";
    $params[] = "%$search%";
}
if ($category) {
    $where[]  = "m.category_id = ?";
    $params[] = $category;
}
if ($status !== '') {
    if ($status === 'active')   { $where[] = "m.is_active = true"; }
    if ($status === 'inactive') { $where[] = "m.is_active = false"; }
    if ($status === 'low')      { $where[] = "ms.stock_status = 'low_stock'"; }
    if ($status === 'out')      { $where[] = "ms.stock_status = 'out_of_stock'"; }
}
if ($type !== '') {
    $where[] = "m.type = ?";
    $params[] = $type;
}
$whereStr = implode(' AND ', $where);

$sql = "
    SELECT m.id, m.code, m.name, m.unit, m.price, m.image, m.is_active, m.type,
           mc.name AS category_name,
           ms.current_stock, ms.stock_status
    FROM materials m
    LEFT JOIN material_categories mc ON m.category_id = mc.id
    LEFT JOIN material_stock ms ON ms.id = m.id
    WHERE $whereStr
    ORDER BY m.created_at DESC
";

$paginated  = paginate($sql, $params, $page);
$materials  = $paginated['items'];
$categories = Database::fetchAll("SELECT * FROM material_categories ORDER BY name");

include __DIR__ . '/../../partials/head.php';
?>

<style>
/* Pastikan tabel luar bisa di-scroll secara horizontal di HP */
.table-responsive {
  width: 100%;
  overflow-x: auto;
  -webkit-overflow-scrolling: touch;
  margin-bottom: 15px;
  border: 1px solid var(--border);
  border-radius: var(--radius-lg);
  background: var(--bg-surface);
}

/* Memastikan image di tabel desktop/mobile tidak merusak baris */
.material-img-thumb {
  width: 44px;
  height: 44px;
  object-fit: cover;
  border-radius: 8px;
  border: 1px solid var(--border);
}

.material-emoji-thumb {
  width: 44px;
  height: 44px;
  border-radius: 8px;
  background: var(--bg-muted);
  display: flex;
  align-items: center;
  justify-content: center;
  font-size: 18px;
}
</style>

<div class="admin-wrapper">
<?php include __DIR__ . '/../../partials/sidebar.php'; ?>
<div class="main-content">
<?php include __DIR__ . '/../../partials/topbar.php'; ?>
<div class="page-body">

<div class="flex-between mb-20">
  <div class="section-header" style="margin-bottom:0;">
    <h2>Daftar Material</h2>
    <p>Total <?= $paginated['total'] ?> material terdaftar</p>
  </div>
  <a href="<?= APP_URL ?>/admin-ab/modules/material/create.php" class="btn btn-primary">
    <i class="fas fa-plus"></i> Tambah Material
  </a>
</div>

<div class="card mb-20">
  <div class="card-body" style="padding: 16px 20px;">
    <form method="GET" class="filter-bar">
      <div class="search-box">
        <input type="text" name="search" class="form-control" placeholder="Cari nama atau kode material..." value="<?= htmlspecialchars($search) ?>">
        <i class="fas fa-search search-icon"></i>
      </div>
      
      <select name="category" class="form-control" style="width:180px;">
        <option value="">Semua Kategori</option>
        <?php foreach ($categories as $cat): ?>
          <option value="<?= $cat['id'] ?>" <?= $category == $cat['id'] ? 'selected' : '' ?>><?= htmlspecialchars($cat['name']) ?></option>
        <?php endforeach; ?>
      </select>
      <select name="type" class="form-control" style="width:160px;">
        <option value="">Semua Tipe</option>
        <option value="product" <?= (get('type') === 'product') ? 'selected' : '' ?>>少 Produk Jadi</option>
        <option value="raw" <?= (get('type') === 'raw') ? 'selected' : '' ?>>逃 Bahan Baku</option>
      </select>
      <select name="status" class="form-control" style="width:150px;">
        <option value="">Semua Status</option>
        <option value="active"   <?= $status==='active'  ?'selected':'' ?>>Aktif</option>
        <option value="inactive" <?= $status==='inactive'?'selected':'' ?>>Nonaktif</option>
        <option value="low"      <?= $status==='low'     ?'selected':'' ?>>Stok Menipis</option>
        <option value="out"      <?= $status==='out'     ?'selected':'' ?>>Stok Habis</option>
      </select>
      <button type="submit" class="btn btn-primary"><i class="fas fa-filter"></i> Filter</button>
      <?php if ($search || $category || $status || $type): ?>
        <a href="<?= APP_URL ?>/admin-ab/modules/material/index.php" class="btn btn-secondary"><i class="fas fa-times"></i> Reset</a>
      <?php endif; ?>
    </form>
  </div>
</div>

<div class="card">
  <div class="card-body" style="padding:0;">
    <?php if (empty($materials)): ?>
      <div class="empty-state">
        <div class="empty-state-icon"><i class="fas fa-boxes"></i></div>
        <div class="empty-state-title">Tidak ada material ditemukan</div>
        <div class="empty-state-desc">Coba ubah filter atau tambah material baru.</div>
        <a href="<?= APP_URL ?>/admin-ab/modules/material/create.php" class="btn btn-primary" style="margin-top:16px;"><i class="fas fa-plus"></i> Tambah Material</a>
      </div>
    <?php else: ?>
      <div class="table-responsive">
        <table class="table">
          <thead>
              <tr>
                  <th>Foto</th>
                  <th>Kode</th>
                  <th>Nama Material</th>
                  <th>Kategori</th>
                  <th>Harga</th>
                  <th>Stok</th>
                  <th>Status</th>
                  <th>Tipe</th>
                  <th>Aksi</th>
              </tr>
          </thead>
          <tbody>
              <?php foreach ($materials as $mat): ?>
              <tr>
                  <td>
                      <?php if ($mat['image']): ?>
                          <img src="<?= uploadUrl($mat['image']) ?>" alt="" class="material-img-thumb">
                      <?php else: ?>
                          <div class="material-emoji-thumb">ｧｱ</div>
                      <?php endif; ?>
                  </td>
                  <td><span class="text-mono" style="font-size:12px;background:var(--bg-muted);padding:3px 8px;border-radius:4px;"><?= htmlspecialchars($mat['code']) ?></span></td>
                  <td>
                      <div style="font-weight:600;font-size:13.5px;"><?= htmlspecialchars($mat['name']) ?></div>
                      <div style="font-size:11px;color:var(--text-muted);"><?= htmlspecialchars($mat['unit']) ?></div>
                  </td>
                  <td style="font-size:13px;"><?= htmlspecialchars($mat['category_name'] ?? '-') ?></td>
                  <td style="font-weight:600;font-size:13px;"><?= formatRupiah($mat['price']) ?></td>
                  <td>
                      <div style="font-size:13.5px;font-weight:700;color:<?= $mat['stock_status']==='out_of_stock'?'var(--danger)':($mat['stock_status']==='low_stock'?'var(--warning)':'var(--success)') ?>;">
                          <?= formatNumber($mat['current_stock'] ?? 0) ?>
                      </div>
                  </td>
                  <td><?= stockStatusLabel($mat['stock_status'] ?? 'available') ?></td>
                  <td>
                      <span class="badge badge-<?= ($mat['type'] ?? 'product') === 'raw' ? 'warning' : 'info' ?>">
                          <?= ($mat['type'] ?? 'product') === 'raw' ? '逃 Bahan Baku' : '少 Produk Jadi' ?>
                      </span>
                  </td>
                  <td>
                      <div class="actions">
                          <a href="<?= APP_URL ?>/admin-ab/modules/material/edit.php?id=<?= $mat['id'] ?>" class="btn btn-sm btn-secondary" data-tooltip="Edit"><i class="fas fa-edit"></i></a>
                          <button onclick="confirmDelete('<?= APP_URL ?>/admin-ab/modules/material/delete.php?id=<?= $mat['id'] ?>&token=<?= csrfToken() ?>','<?= htmlspecialchars(addslashes($mat['name'])) ?>')" class="btn btn-sm btn-danger" data-tooltip="Hapus"><i class="fas fa-trash"></i></button>
                      </div>
                  </td>
              </tr>
              <?php endforeach; ?>
          </tbody>
        </table>
      </div>

      <?php if ($paginated['pages'] > 1): ?>
      <div class="card-footer" style="justify-content:space-between; flex-wrap: wrap; gap: 10px;">
        <span style="font-size:13px;color:var(--text-muted);">Halaman <?= $page ?> dari <?= $paginated['pages'] ?> (<?= $paginated['total'] ?> data)</span>
        <div class="pagination">
          <?php for ($p = 1; $p <= $paginated['pages']; $p++): ?>
            <a href="?page=<?= $p ?>&search=<?= urlencode($search) ?>&category=<?= $category ?>&status=<?= $status ?>&type=<?= $type ?>" class="page-btn <?= $p==$page?'active':'' ?>"><?= $p ?></a>
          <?php endfor; ?>
        </div>
      </div>
      <?php endif; ?>
    <?php endif; ?>
  </div>
</div>

</div> </div> </div> <?php include __DIR__ . '/../../partials/footer.php'; ?>