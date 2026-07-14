<?php
require_once __DIR__ . '/../../../config/database.php';
require_once __DIR__ . '/../../../config/app.php';
initSession();
requireLogin();

$pageTitle    = 'Material';
$pageSubtitle = 'Kelola data material dan produk';

$search   = get('search');
$category = getInt('category');
$status   = get('status');
$type     = get('type'); // TAMBAHKAN INI
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
// TAMBAHKAN FILTER TIPE
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
/* Card view untuk HP */
@media (max-width: 768px) {
  .material-table-view {
    display: none;
  }
  .material-card-view {
    display: block;
  }
  .material-card {
    background: var(--bg-surface);
    border: 1px solid var(--border);
    border-radius: var(--radius-lg);
    margin-bottom: 16px;
    overflow: hidden;
  }
  .material-card-body {
    display: flex;
    gap: 16px;
    padding: 16px;
  }
  .material-card-image {
    width: 70px;
    height: 70px;
    border-radius: var(--radius-md);
    object-fit: cover;
    background: var(--bg-muted);
    flex-shrink: 0;
  }
  .material-card-info {
    flex: 1;
  }
  .material-card-name {
    font-size: 16px;
    font-weight: 700;
    color: var(--text-primary);
    margin-bottom: 4px;
  }
  .material-card-code {
    font-size: 11px;
    background: var(--bg-muted);
    display: inline-block;
    padding: 2px 8px;
    border-radius: 4px;
    margin-bottom: 8px;
  }
  .material-card-detail {
    display: flex;
    flex-wrap: wrap;
    gap: 12px;
    margin-top: 8px;
  }
  .material-card-detail-item {
    flex: 1;
    min-width: 80px;
  }
  .material-card-detail-label {
    font-size: 10px;
    color: var(--text-muted);
  }
  .material-card-detail-value {
    font-size: 13px;
    font-weight: 600;
  }
  .material-card-footer {
    display: flex;
    gap: 8px;
    padding: 12px 16px;
    border-top: 1px solid var(--border);
    background: var(--bg-muted);
  }
  .material-card-footer .btn {
    flex: 1;
    justify-content: center;
  }
}
@media (min-width: 769px) {
  .material-table-view {
    display: block;
  }
  .material-card-view {
    display: none;
  }
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

<!-- Filter -->
<div class="card mb-20">
  <div class="card-body" style="padding: 16px 20px;">
    <form method="GET" class="filter-bar">
    <div class="search-box">
  <input type="text" name="search" class="form-control"
         placeholder="Cari nama atau kode material..."
         value="<?= htmlspecialchars($search) ?>">

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
        <option value="product" <?= (get('type') === 'product') ? 'selected' : '' ?>>🏭 Produk Jadi</option>
        <option value="raw" <?= (get('type') === 'raw') ? 'selected' : '' ?>>📦 Bahan Baku</option>
      </select>
      <select name="status" class="form-control" style="width:150px;">
        <option value="">Semua Status</option>
        <option value="active"   <?= $status==='active'  ?'selected':'' ?>>Aktif</option>
        <option value="inactive" <?= $status==='inactive'?'selected':'' ?>>Nonaktif</option>
        <option value="low"      <?= $status==='low'     ?'selected':'' ?>>Stok Menipis</option>
        <option value="out"      <?= $status==='out'     ?'selected':'' ?>>Stok Habis</option>
      </select>
      <button type="submit" class="btn btn-primary"><i class="fas fa-filter"></i> Filter</button>
      <?php if ($search || $category || $status): ?>
        <a href="<?= APP_URL ?>/admin-ab/modules/material/index.php" class="btn btn-secondary"><i class="fas fa-times"></i> Reset</a>
      <?php endif; ?>
    </form>
  </div>
</div>

<!-- TABLE VIEW (Desktop) -->
<div class="card material-table-view">
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
                    <img src="<?= uploadUrl($mat['image']) ?>" alt="" style="width:44px;height:44px;object-fit:cover;border-radius:8px;border:1px solid var(--border);">
                <?php else: ?>
                    <div style="width:44px;height:44px;border-radius:8px;background:var(--bg-muted);display:flex;align-items:center;justify-content:center;font-size:18px;">🧱</div>
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
                <div style="font-size:10px;color:var(--text-muted);">masuk:<?= formatNumber($mat['total_in']??0) ?> keluar:<?= formatNumber($mat['total_out']??0) ?></div>
            </td>
            <td><?= stockStatusLabel($mat['stock_status'] ?? 'available') ?></td>
            <td>
                <span class="badge badge-<?= ($mat['type'] ?? 'product') === 'raw' ? 'warning' : 'info' ?>">
                    <?= ($mat['type'] ?? 'product') === 'raw' ? '📦 Bahan Baku' : '🏭 Produk Jadi' ?>
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
      <!-- Pagination -->
      <?php if ($paginated['pages'] > 1): ?>
      <div class="card-footer" style="justify-content:space-between;">
        <span style="font-size:13px;color:var(--text-muted);">Halaman <?= $page ?> dari <?= $paginated['pages'] ?> (<?= $paginated['total'] ?> data)</span>
        <div class="pagination">
          <?php for ($p = 1; $p <= $paginated['pages']; $p++): ?>
            <a href="?page=<?= $p ?>&search=<?= urlencode($search) ?>&category=<?= $category ?>&status=<?= $status ?>" class="page-btn <?= $p==$page?'active':'' ?>"><?= $p ?></a>
          <?php endfor; ?>
        </div>
      </div>
      <?php endif; ?>
    <?php endif; ?>
  </div>
</div>

<!-- CARD VIEW (Mobile/HP) -->
<div class="material-card-view">
  <?php if (empty($materials)): ?>
    <div class="empty-state">
      <div class="empty-state-icon"><i class="fas fa-boxes"></i></div>
      <div class="empty-state-title">Tidak ada material ditemukan</div>
      <div class="empty-state-desc">Coba ubah filter atau tambah material baru.</div>
    </div>
  <?php else: ?>
    <?php foreach ($materials as $mat): ?>
    <div class="material-card">
      <div class="material-card-body">
        <div>
          <?php if ($mat['image']): ?>
            <img src="<?= uploadUrl($mat['image']) ?>" class="material-card-image">
          <?php else: ?>
            <div class="material-card-image" style="display:flex;align-items:center;justify-content:center;font-size:28px;">🧱</div>
          <?php endif; ?>
        </div>
        <div class="material-card-info">
          <div class="material-card-name"><?= htmlspecialchars($mat['name']) ?></div>
          <span class="material-card-code"><?= htmlspecialchars($mat['code']) ?></span>
          <div class="material-card-detail">
            <div class="material-card-detail-item">
              <div class="material-card-detail-label">Kategori</div>
              <div class="material-card-detail-value"><?= htmlspecialchars($mat['category_name'] ?? '-') ?></div>
            </div>
            <div class="material-card-detail-item">
              <div class="material-card-detail-label">Harga</div>
              <div class="material-card-detail-value"><?= formatRupiah($mat['price']) ?> / <?= $mat['unit'] ?></div>
            </div>
            <div class="material-card-detail-item">
              <div class="material-card-detail-label">Stok</div>
              <div class="material-card-detail-value" style="color:<?= $mat['stock_status']==='out_of_stock'?'var(--danger)':($mat['stock_status']==='low_stock'?'var(--warning)':'var(--success)') ?>;">
                <?= formatNumber($mat['current_stock'] ?? 0) ?>
              </div>
            </div>
          </div>
          <div style="margin-top:8px;">
            <?= stockStatusLabel($mat['stock_status'] ?? 'available') ?>
            <?php if (!$mat['is_active']): ?>
              <span class="badge badge-secondary">Nonaktif</span>
            <?php endif; ?>
          </div>
        </div>
      </div>
      <div class="material-card-footer">
        <a href="<?= APP_URL ?>/admin-ab/modules/material/edit.php?id=<?= $mat['id'] ?>" class="btn btn-secondary btn-sm">
          <i class="fas fa-edit"></i> Edit
        </a>
        <button onclick="confirmDelete('<?= APP_URL ?>/admin-ab/modules/material/delete.php?id=<?= $mat['id'] ?>&token=<?= csrfToken() ?>','<?= htmlspecialchars(addslashes($mat['name'])) ?>')" class="btn btn-danger btn-sm">
          <i class="fas fa-trash"></i> Hapus
        </button>
      </div>
    </div>
    <?php endforeach; ?>
    
    <!-- Pagination di mobile -->
    <?php if ($paginated['pages'] > 1): ?>
    <div class="flex-between" style="margin-top:20px; flex-wrap:wrap; gap:10px; justify-content:center;">
      <div class="pagination">
        <?php for ($p = 1; $p <= $paginated['pages']; $p++): ?>
          <a href="?page=<?= $p ?>&search=<?= urlencode($search) ?>&category=<?= $category ?>&status=<?= $status ?>" class="page-btn <?= $p==$page?'active':'' ?>"><?= $p ?></a>
        <?php endfor; ?>
      </div>
    </div>
    <?php endif; ?>
  <?php endif; ?>
</div>

</div>
<?php include __DIR__ . '/../../partials/footer.php'; ?>