<?php
require_once __DIR__ . '/../../../config/database.php';
require_once __DIR__ . '/../../../config/app.php';
initSession();
requireLogin();

$pageTitle = 'Catat Barang Masuk';
$errors    = [];
$user      = currentUser();
$materials = Database::fetchAll("SELECT id, name, unit FROM materials WHERE is_active=true ORDER BY name");
$data      = ['material_id'=>'','quantity'=>'','price_per_unit'=>'','supplier_name'=>'','invoice_number'=>'','notes'=>'','received_date'=>date('Y-m-d')];

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    if (!verifyCsrf()) { $errors[] = 'Token keamanan tidak valid.'; }
    else {
        $data = [
            'material_id'    => postInt('material_id'),
            'quantity'       => postInt('quantity'),
            'price_per_unit' => sanitizeFloat($_POST['price_per_unit'] ?? 0) ?: null,
            'supplier_name'  => post('supplier_name') ?: null,
            'invoice_number' => post('invoice_number') ?: null,
            'notes'          => post('notes') ?: null,
            'received_date'  => post('received_date') ?: date('Y-m-d'),
            'received_by'    => $user['id'],
        ];

        if (!$data['material_id']) $errors[] = 'Pilih material.';
        if ($data['quantity'] <= 0) $errors[] = 'Jumlah harus lebih dari 0.';

        if (empty($errors)) {
            Database::insert('stock_in', $data);
            $matName = Database::fetchColumn("SELECT name FROM materials WHERE id=?", [$data['material_id']]);
            logActivity('create', 'stock_in', "Mencatat barang masuk: {$matName} sebanyak {$data['quantity']}");
            setFlash('success', 'Data barang masuk berhasil dicatat.');
            redirect(APP_URL . '/admin-ab/modules/stock_in/index.php');
        }
    }
}

include __DIR__ . '/../../partials/head.php';
?>
<div class="admin-wrapper">
<?php include __DIR__ . '/../../partials/sidebar.php'; ?>
<div class="main-content">
<?php include __DIR__ . '/../../partials/topbar.php'; ?>
<div class="page-body">

<div class="mb-20">
  <a href="<?= APP_URL ?>/admin-ab/modules/stock_in/index.php" class="btn btn-ghost btn-sm" style="margin-bottom:8px;"><i class="fas fa-arrow-left"></i> Kembali</a>
  <h2 style="font-size:20px;font-weight:800;">Catat Barang Masuk</h2>
</div>

<?php foreach ($errors as $e): ?>
  <div class="alert alert-danger"><i class="fas fa-exclamation-circle"></i> <?= htmlspecialchars($e) ?></div>
<?php endforeach; ?>

<div class="card" style="max-width:700px;">
  <div class="card-header"><div class="card-title">Form Penerimaan Material</div></div>
  <div class="card-body">
    <form method="POST">
      <?= csrfField() ?>
      <div class="form-group">
        <label class="form-label">Material <span>*</span></label>
        <select name="material_id" class="form-control" required id="mat-select">
          <option value="">-- Pilih Material --</option>
          <?php foreach ($materials as $m): ?>
            <option value="<?= $m['id'] ?>" data-unit="<?= htmlspecialchars($m['unit']) ?>" <?= $data['material_id']==$m['id']?'selected':'' ?>>
              <?= htmlspecialchars($m['name']) ?> (<?= $m['unit'] ?>)
            </option>
          <?php endforeach; ?>
        </select>
      </div>
      <div class="grid grid-2">
        <div class="form-group">
          <label class="form-label">Jumlah <span>*</span></label>
          <div class="input-group">
            <input type="number" name="quantity" class="form-control" value="<?= $data['quantity'] ?>" min="1" required placeholder="0">
            <span style="position:absolute;right:12px;font-size:12px;color:var(--text-muted);" id="unit-label"></span>
          </div>
        </div>
        <div class="form-group">
          <label class="form-label">Harga per Unit</label>
          <div class="input-group">
            <span class="input-group-icon" style="font-size:12px;font-weight:600;">Rp</span>
            <input type="number" name="price_per_unit" class="form-control" value="<?= $data['price_per_unit'] ?>" min="0" step="1000" placeholder="0">
          </div>
        </div>
      </div>
      <div class="grid grid-2">
        <div class="form-group">
          <label class="form-label">Nama Supplier</label>
          <input type="text" name="supplier_name" class="form-control" value="<?= htmlspecialchars($data['supplier_name']) ?>" placeholder="Nama pemasok">
        </div>
        <div class="form-group">
          <label class="form-label">Nomor Invoice</label>
          <input type="text" name="invoice_number" class="form-control" value="<?= htmlspecialchars($data['invoice_number']) ?>" placeholder="INV-2024-001" class="text-mono">
        </div>
      </div>
      <div class="form-group">
        <label class="form-label">Tanggal Penerimaan <span>*</span></label>
        <input type="date" name="received_date" class="form-control" value="<?= $data['received_date'] ?>" required>
      </div>
      <div class="form-group">
        <label class="form-label">Catatan</label>
        <textarea name="notes" class="form-control" rows="3" placeholder="Catatan tambahan..."><?= htmlspecialchars($data['notes']) ?></textarea>
      </div>
      <div style="display:flex;gap:10px;">
        <button type="submit" class="btn btn-primary"><i class="fas fa-save"></i> Simpan</button>
        <a href="<?= APP_URL ?>/admin-ab/modules/stock_in/index.php" class="btn btn-secondary">Batal</a>
      </div>
    </form>
  </div>
</div>
</div>
<?php include __DIR__ . '/../../partials/footer.php'; ?>
<script>
document.getElementById('mat-select').addEventListener('change', function() {
  const opt = this.options[this.selectedIndex];
  document.getElementById('unit-label').textContent = opt.dataset.unit || '';
});
// Trigger on load
document.getElementById('mat-select').dispatchEvent(new Event('change'));
</script>
