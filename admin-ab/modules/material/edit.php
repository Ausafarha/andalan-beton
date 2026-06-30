<?php
require_once __DIR__ . '/../../../config/database.php';
require_once __DIR__ . '/../../../config/app.php';
initSession();
requireLogin();

$id  = getInt('id');
$mat = Database::fetchOne("SELECT * FROM materials WHERE id = ?", [$id]);
if (!$mat) { setFlash('error', 'Material tidak ditemukan.'); redirect(APP_URL . '/admin-ab/modules/material/index.php'); }

$pageTitle  = 'Edit Material';
$errors     = [];
$categories = Database::fetchAll("SELECT * FROM material_categories ORDER BY name");

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    if (!verifyCsrf()) { $errors[] = 'Token keamanan tidak valid.'; }
    else {
$data = [
    'category_id' => postInt('category_id') ?: null,
    'code'        => post('code'),
    'name'        => post('name'),
    'description' => post('description'),
    'unit'        => post('unit'),
    'price'       => sanitizeFloat($_POST['price'] ?? 0),
    'min_stock'   => postInt('min_stock', 10),
    'is_active'   => (isset($_POST['is_active']) && $_POST['is_active'] == 1) ? 'true' : 'false',
    'is_featured' => (isset($_POST['is_featured']) && $_POST['is_featured'] == 1) ? 'true' : 'false',
];

        if (empty($data['code']))  $errors[] = 'Kode material wajib diisi.';
        if (empty($data['name']))  $errors[] = 'Nama material wajib diisi.';
        if (empty($data['unit']))  $errors[] = 'Satuan wajib diisi.';
        if ($data['price'] <= 0)   $errors[] = 'Harga harus lebih dari 0.';

        $exists = Database::fetchColumn("SELECT id FROM materials WHERE code = ? AND id != ?", [$data['code'], $id]);
        if ($exists) $errors[] = 'Kode material sudah digunakan.';

        if (empty($errors)) {
            if (!empty($_FILES['image']['name'])) {
    require_once __DIR__ . '/../../../config/cloudinary.php';
    $upload = uploadToCloudinary($_FILES['image'], 'products');
    if ($upload['success']) {
        $data['image'] = $upload['url'];
    } else {
        $errors[] = $upload['message'];
    }
}
            if (empty($errors)) {
                Database::update('materials', $data, 'id = ?', [$id]);
                logActivity('update', 'materials', "Mengubah material: {$data['name']}");
                setFlash('success', "Material \"{$data['name']}\" berhasil diperbarui.");
                redirect(APP_URL . '/admin-ab/modules/material/index.php');
            }
        }

        $mat = array_merge($mat, $data);
    }
}

include __DIR__ . '/../../partials/head.php';
?>
<div class="admin-wrapper">
<?php include __DIR__ . '/../../partials/sidebar.php'; ?>
<div class="main-content">
<?php include __DIR__ . '/../../partials/topbar.php'; ?>
<div class="page-body">

<div class="flex-between mb-20">
  <div>
    <a href="<?= APP_URL ?>/admin-ab/modules/material/index.php" class="btn btn-ghost btn-sm" style="margin-bottom:8px;"><i class="fas fa-arrow-left"></i> Kembali</a>
    <h2 style="font-size:20px;font-weight:800;">Edit Material</h2>
  </div>
</div>

<?php foreach ($errors as $e): ?>
  <div class="alert alert-danger"><i class="fas fa-exclamation-circle"></i> <?= htmlspecialchars($e) ?></div>
<?php endforeach; ?>

<form method="POST" enctype="multipart/form-data">
  <?= csrfField() ?>
  <div class="grid" style="grid-template-columns: 2fr 1fr; gap: 20px;">
    <div>
      <div class="card mb-20">
        <div class="card-header"><div class="card-title">Informasi Material</div></div>
        <div class="card-body">
          <div class="grid grid-2">
            <div class="form-group">
              <label class="form-label">Kode Material <span>*</span></label>
              <input type="text" name="code" class="form-control" value="<?= htmlspecialchars($mat['code']) ?>" required>
            </div>
            <div class="form-group">
              <label class="form-label">Kategori</label>
              <select name="category_id" class="form-control">
                <option value="">-- Pilih Kategori --</option>
                <?php foreach ($categories as $cat): ?>
                  <option value="<?= $cat['id'] ?>" <?= $mat['category_id']==$cat['id']?'selected':'' ?>><?= htmlspecialchars($cat['name']) ?></option>
                <?php endforeach; ?>
              </select>
            </div>
          </div>
          <div class="form-group">
            <label class="form-label">Nama Material <span>*</span></label>
            <input type="text" name="name" class="form-control" value="<?= htmlspecialchars($mat['name']) ?>" required>
          </div>
          <div class="form-group">
            <label class="form-label">Deskripsi</label>
            <textarea name="description" class="form-control" rows="4"><?= htmlspecialchars($mat['description'] ?? '') ?></textarea>
          </div>
          <div class="grid grid-2">
            <div class="form-group">
              <label class="form-label">Satuan <span>*</span></label>
              <select name="unit" class="form-control" required>
                <?php foreach (['m³','m²','m','kg','ton','sak','buah','unit','batang','lembar','roll','liter'] as $u): ?>
                  <option value="<?= $u ?>" <?= $mat['unit']===$u?'selected':'' ?>><?= $u ?></option>
                <?php endforeach; ?>
              </select>
            </div>
            <div class="form-group">
              <label class="form-label">Harga Jual <span>*</span></label>
              <div class="input-group">
                <span class="input-group-icon" style="font-size:12px;font-weight:600;">Rp</span>
                <input type="number" name="price" class="form-control" value="<?= $mat['price'] ?>" min="0" step="1000" required>
              </div>
            </div>
          </div>
          <div class="form-group">
            <label class="form-label">Minimal Stok</label>
            <input type="number" name="min_stock" class="form-control" value="<?= $mat['min_stock'] ?>" min="0">
          </div>
        </div>
      </div>
    </div>
    <div>
      <div class="card mb-20">
        <div class="card-header"><div class="card-title">Foto Material</div></div>
        <div class="card-body">
          <?php if ($mat['image']): ?>
            <img id="img-preview" src="<?= uploadUrl($mat['image']) ?>" class="upload-preview" style="display:block; margin-bottom:12px;">
          <?php else: ?>
            <img id="img-preview" style="display:none;" class="upload-preview">
          <?php endif; ?>
          <div class="upload-area" onclick="document.getElementById('image-input').click()">
            <i class="fas fa-cloud-upload-alt" style="font-size:22px;color:var(--text-muted);"></i>
            <div style="font-size:13px;margin-top:6px;"><?= $mat['image'] ? 'Ganti foto' : 'Upload foto' ?></div>
          </div>
          <input type="file" id="image-input" name="image" accept="image/*" style="display:none;" data-preview-target="img-preview">
        </div>
      </div>
      <div class="card">
        <div class="card-header"><div class="card-title">Pengaturan</div></div>
        <div class="card-body">
          <label style="display:flex;align-items:center;gap:10px;cursor:pointer;margin-bottom:12px;padding:12px;background:var(--bg-muted);border-radius:8px;">
            <input type="checkbox" name="is_active" value="1" <?= $mat['is_active']?'checked':'' ?> style="width:18px;height:18px;">
            <div><div style="font-size:13.5px;font-weight:600;">Material Aktif</div></div>
          </label>
          <label style="display:flex;align-items:center;gap:10px;cursor:pointer;padding:12px;background:var(--bg-muted);border-radius:8px;">
            <input type="checkbox" name="is_featured" value="1" <?= $mat['is_featured']?'checked':'' ?> style="width:18px;height:18px;">
            <div><div style="font-size:13.5px;font-weight:600;">Unggulan</div></div>
          </label>
        </div>
      </div>
    </div>
  </div>
  <div style="display:flex;gap:10px;margin-top:8px;">
    <button type="submit" class="btn btn-primary btn-lg"><i class="fas fa-save"></i> Simpan Perubahan</button>
    <a href="<?= APP_URL ?>/admin-ab/modules/material/index.php" class="btn btn-secondary btn-lg">Batal</a>
  </div>
</form>
</div>
<?php include __DIR__ . '/../../partials/footer.php'; ?>
