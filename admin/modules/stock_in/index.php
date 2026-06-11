<?php
require_once __DIR__ . '/../../../config/database.php';
require_once __DIR__ . '/../../../config/app.php';
initSession();
requireLogin();

$pageTitle    = 'Barang Masuk';
$pageSubtitle = 'Riwayat penerimaan material';

$search  = get('search');
$matId   = getInt('material_id');
$dateFrom= get('date_from');
$dateTo  = get('date_to');
$page    = max(1, getInt('page', 1));

$params = [];
$where  = ['1=1'];

if ($search) { $where[] = "(m.name ILIKE ? OR si.supplier_name ILIKE ? OR si.invoice_number ILIKE ?)"; $params[] = "%$search%"; $params[] = "%$search%"; $params[] = "%$search%"; }
if ($matId)  { $where[] = "si.material_id = ?"; $params[] = $matId; }
if ($dateFrom){ $where[] = "si.received_date >= ?"; $params[] = $dateFrom; }
if ($dateTo)  { $where[] = "si.received_date <= ?"; $params[] = $dateTo; }

$whereStr = implode(' AND ', $where);
$sql = "SELECT si.*, m.name AS material_name, m.unit, u.name AS received_by_name
        FROM stock_in si
        JOIN materials m ON si.material_id = m.id
        LEFT JOIN users u ON si.received_by = u.id
        WHERE $whereStr
        ORDER BY si.received_date DESC, si.id DESC";

$paginated  = paginate($sql, $params, $page);
$materials  = Database::fetchAll("SELECT id, name, unit FROM materials WHERE is_active=true ORDER BY name");
$totalIn    = Database::fetchColumn("SELECT COALESCE(SUM(si.quantity),0) FROM stock_in si JOIN materials m ON si.material_id=m.id WHERE $whereStr", $params);

include __DIR__ . '/../../partials/head.php';
?>
<div class="admin-wrapper">
<?php include __DIR__ . '/../../partials/sidebar.php'; ?>
<div class="main-content">
<?php include __DIR__ . '/../../partials/topbar.php'; ?>
<div class="page-body">

<div class="flex-between mb-20">
  <div class="section-header" style="margin-bottom:0;">
    <h2>Barang Masuk</h2>
    <p>Total <?= formatNumber($totalIn) ?> unit diterima</p>
  </div>
  <a href="<?= APP_URL ?>/admin/modules/stock_in/create.php" class="btn btn-primary">
    <i class="fas fa-plus"></i> Catat Barang Masuk
  </a>
</div>

<div class="card mb-20">
  <div class="card-body" style="padding:16px 20px;">
    <form method="GET" class="filter-bar">
      <div class="search-box">
        <i class="fas fa-search search-icon"></i>
        <input type="text" name="search" class="form-control" placeholder="Cari material, supplier, invoice..." value="<?= htmlspecialchars($search) ?>">
      </div>
      <select name="material_id" class="form-control" style="width:180px;">
        <option value="">Semua Material</option>
        <?php foreach ($materials as $m): ?>
          <option value="<?= $m['id'] ?>" <?= $matId==$m['id']?'selected':'' ?>><?= htmlspecialchars($m['name']) ?></option>
        <?php endforeach; ?>
      </select>
      <input type="date" name="date_from" class="form-control" value="<?= $dateFrom ?>" style="width:150px;" title="Dari Tanggal">
      <input type="date" name="date_to"   class="form-control" value="<?= $dateTo ?>"   style="width:150px;" title="Sampai Tanggal">
      <button type="submit" class="btn btn-primary"><i class="fas fa-filter"></i></button>
      <?php if ($search || $matId || $dateFrom || $dateTo): ?>
        <a href="?" class="btn btn-secondary"><i class="fas fa-times"></i></a>
      <?php endif; ?>
    </form>
  </div>
</div>

<div class="card">
  <div class="card-body" style="padding:0;">
    <?php if (empty($paginated['items'])): ?>
      <div class="empty-state">
        <div class="empty-state-icon"><i class="fas fa-arrow-down"></i></div>
        <div class="empty-state-title">Belum ada data barang masuk</div>
        <a href="<?= APP_URL ?>/admin/modules/stock_in/create.php" class="btn btn-primary" style="margin-top:16px;"><i class="fas fa-plus"></i> Catat Sekarang</a>
      </div>
    <?php else: ?>
      <div class="table-responsive">
        <table class="table">
          <thead>
            <tr><th>#</th><th>Tanggal</th><th>Material</th><th>Jumlah</th><th>Harga/Unit</th><th>Supplier</th><th>No. Invoice</th><th>Diterima Oleh</th><th>Aksi</th></tr>
          </thead>
          <tbody>
            <?php foreach ($paginated['items'] as $i => $row): ?>
            <tr>
              <td style="color:var(--text-muted);font-size:12px;"><?= ($page-1)*PER_PAGE + $i + 1 ?></td>
              <td style="font-size:13px;"><?= formatDate($row['received_date']) ?></td>
              <td>
                <div style="font-weight:600;font-size:13.5px;"><?= htmlspecialchars($row['material_name']) ?></div>
              </td>
              <td style="font-weight:700;color:var(--brand-600);"><?= formatNumber($row['quantity']) ?> <span style="font-size:12px;font-weight:400;color:var(--text-muted);"><?= $row['unit'] ?></span></td>
              <td style="font-size:13px;"><?= $row['price_per_unit'] ? formatRupiah($row['price_per_unit']) : '-' ?></td>
              <td style="font-size:13px;"><?= htmlspecialchars($row['supplier_name'] ?? '-') ?></td>
              <td style="font-size:12px;" class="text-mono"><?= htmlspecialchars($row['invoice_number'] ?? '-') ?></td>
              <td style="font-size:13px;"><?= htmlspecialchars($row['received_by_name'] ?? '-') ?></td>
              <td>
                <div class="actions">
                  <a href="<?= APP_URL ?>/admin/modules/stock_in/edit.php?id=<?= $row['id'] ?>" class="btn btn-sm btn-secondary"><i class="fas fa-edit"></i></a>
                  <button onclick="confirmDelete('<?= APP_URL ?>/admin/modules/stock_in/delete.php?id=<?= $row['id'] ?>&token=<?= csrfToken() ?>','penerimaan ini')" class="btn btn-sm btn-danger"><i class="fas fa-trash"></i></button>
                </div>
              </td>
            </tr>
            <?php endforeach; ?>
          </tbody>
        </table>
      </div>
      <?php if ($paginated['pages'] > 1): ?>
      <div class="card-footer" style="justify-content:space-between;">
        <span style="font-size:13px;color:var(--text-muted);">Halaman <?= $page ?> dari <?= $paginated['pages'] ?></span>
        <div class="pagination">
          <?php for ($p = 1; $p <= $paginated['pages']; $p++): ?>
            <a href="?page=<?= $p ?>&search=<?= urlencode($search) ?>&material_id=<?= $matId ?>&date_from=<?= $dateFrom ?>&date_to=<?= $dateTo ?>" class="page-btn <?= $p==$page?'active':'' ?>"><?= $p ?></a>
          <?php endfor; ?>
        </div>
      </div>
      <?php endif; ?>
    <?php endif; ?>
  </div>
</div>
</div>
<?php include __DIR__ . '/../../partials/footer.php'; ?>
