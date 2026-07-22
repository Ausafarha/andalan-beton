<?php
require_once __DIR__ . '/../../../config/database.php';
require_once __DIR__ . '/../../../config/app.php';
initSession();
requireLogin();

$pageTitle    = 'Galeri';
$pageSubtitle = 'Kelola foto galeri perusahaan';

$success = getFlash();
$errors  = [];

// Handle upload & edit
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['action'])) {
    if (!verifyCsrf()) {
        $errors[] = 'Token keamanan tidak valid.';
    } else {
        if ($_POST['action'] === 'upload') {
            if (empty($_FILES['image']['name'])) {
                $errors[] = 'Pilih file gambar terlebih dahulu.';
            } else {
                require_once __DIR__ . '/../../../config/cloudinary.php';
                $upload = uploadToCloudinary($_FILES['image'], 'gallery');
                if ($upload['success']) {
                    Database::insert('gallery', [
                        'title'       => post('title'),
                        'description' => post('description'),
                        'category'    => post('category'),
                        'image'       => $upload['url'],
                        'sort_order'  => 0,
                        'is_active'   => postInt('is_active', 1)
                    ]);
                    logActivity('create', 'gallery', 'Menambah foto galeri: ' . post('title'));
                    setFlash('success', 'Foto berhasil ditambahkan.');
                    redirect(APP_URL . '/admin-ab/modules/gallery/index.php');
                } else {
                    $errors[] = $upload['message'];
                }
            }
        }
        
        if ($_POST['action'] === 'edit') {
            $id = postInt('id');
            $data = [
                'title'       => post('title'),
                'description' => post('description'),
                'category'    => post('category'),
                'is_active'   => postInt('is_active', 1)
            ];
            
            if (!empty($_FILES['image']['name'])) {
                require_once __DIR__ . '/../../../config/cloudinary.php';
                $upload = uploadToCloudinary($_FILES['image'], 'gallery');
                if ($upload['success']) {
                    $data['image'] = $upload['url'];
                } else {
                    $errors[] = $upload['message'];
                }
            }
            
            if (empty($errors)) {
                Database::update('gallery', $data, 'id = ?', [$id]);
                logActivity('update', 'gallery', 'Mengedit foto galeri ID: ' . $id);
                setFlash('success', 'Foto berhasil diupdate.');
                redirect(APP_URL . '/admin-ab/modules/gallery/index.php');
            }
        }
    }
}

// Handle delete
if (isset($_GET['delete']) && isset($_GET['token'])) {
    if (!hash_equals($_SESSION['csrf_token'] ?? '', $_GET['token'])) {
        die('Token tidak valid');
    }
    $id = (int)$_GET['delete'];
    $gallery = Database::fetchOne("SELECT image FROM gallery WHERE id = ?", [$id]);
    if ($gallery && str_contains($gallery['image'], 'cloudinary.com')) {
        require_once __DIR__ . '/../../../config/cloudinary.php';
        $parts = explode('/upload/', $gallery['image']);
        $publicId = 'andalan-beton/gallery/' . pathinfo($parts[1], PATHINFO_FILENAME);
        deleteFromCloudinary($publicId);
    }
    Database::delete('gallery', 'id = ?', [$id]);
    setFlash('success', 'Foto berhasil dihapus.');
    redirect(APP_URL . '/admin-ab/modules/gallery/index.php');
}

// Handle toggle status
if (isset($_GET['toggle']) && isset($_GET['token'])) {
    if (!hash_equals($_SESSION['csrf_token'] ?? '', $_GET['token'])) {
        die('Token tidak valid');
    }
    $id = (int)$_GET['toggle'];
    $current = Database::fetchOne("SELECT is_active FROM gallery WHERE id = ?", [$id]);
    if ($current) {
        Database::update('gallery', ['is_active' => $current['is_active'] ? false : true], 'id = ?', [$id]);
        setFlash('success', 'Status foto berhasil diubah.');
    }
    redirect(APP_URL . '/admin-ab/modules/gallery/index.php');
}

// Fetch Data - Mengurutkan berdasarkan ID terbaru
$gallery = Database::fetchAll("SELECT * FROM gallery ORDER BY id DESC");
$categories = Database::fetchAll("SELECT DISTINCT category FROM gallery ORDER BY category");

include __DIR__ . '/../../partials/head.php';
?>

<style>
.gallery-thumb {
    width: 80px;
    height: 80px;
    object-fit: cover;
    border-radius: 8px;
}
@media (max-width: 768px) {
    .gallery-thumb {
        width: 60px;
        height: 60px;
    }
    .table {
        min-width: 600px;
    }
}
</style>

<div class="admin-wrapper">
<?php include __DIR__ . '/../../partials/sidebar.php'; ?>
<div class="main-content">
<?php include __DIR__ . '/../../partials/topbar.php'; ?>
<div class="page-body">

<div class="flex-between mb-20">
    <div class="section-header">
        <h2>Galeri Foto</h2>
        <p>Kelola foto galeri proyek dan fasilitas perusahaan</p>
    </div>
    <button class="btn btn-primary" onclick="document.getElementById('upload-modal').classList.add('open')">
        <i class="fas fa-plus"></i> Tambah Foto
    </button>
</div>

<?php if ($flash = getFlash()): ?>
    <div class="alert alert-<?= $flash['type'] ?> mb-20"><?= htmlspecialchars($flash['message']) ?></div>
<?php endif; ?>

<div class="card">
    <div class="card-body" style="padding:0;">
        <?php if (empty($gallery)): ?>
            <div class="empty-state">
                <div class="empty-state-icon"><i class="fas fa-images"></i></div>
                <div class="empty-state-title">Belum ada foto galeri</div>
                <div class="empty-state-desc">Klik tombol "Tambah Foto" untuk menambahkan foto galeri.</div>
            </div>
        <?php else: ?>
            <div class="table-responsive">
                <table class="table">
                    <thead>
                        <tr>
                            <th>Foto</th>
                            <th>Judul</th>
                            <th>Kategori</th>
                            <th>Status</th>
                            <th>Aksi</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php foreach ($gallery as $item): ?>
                        <tr>
                            <td>
                                <img src="<?= uploadUrl($item['image']) ?>" class="gallery-thumb" alt="<?= htmlspecialchars($item['title'] ?? '') ?>">
                            </td>
                            <td>
                                <div style="font-weight:600;"><?= htmlspecialchars($item['title'] ?? 'Tanpa judul') ?></div>
                                <?php if ($item['description']): ?>
                                    <div style="font-size:11px; color:var(--text-muted);"><?= htmlspecialchars(substr($item['description'], 0, 50)) ?>...</div>
                                <?php endif; ?>
                            </td>
                            <td><span class="badge badge-info"><?= htmlspecialchars($item['category']) ?></span></td>
                            <td>
                                <a href="?toggle=<?= $item['id'] ?>&token=<?= csrfToken() ?>" class="badge badge-<?= $item['is_active'] ? 'success' : 'secondary' ?>" style="text-decoration:none;">
                                    <?= $item['is_active'] ? 'Aktif' : 'Nonaktif' ?>
                                </a>
                            </td>
                            <td>
<div class="actions">
    <!-- Tombol intip foto ukuran penuh di tab baru -->
    <a href="<?= uploadUrl($item['image']) ?>" target="_blank" class="btn btn-sm btn-info" title="Lihat Foto Full">
        <i class="fas fa-eye"></i>
    </a>
    
    <button onclick='editGallery(<?= json_encode($item) ?>)' class="btn btn-sm btn-secondary" title="Edit">
        <i class="fas fa-edit"></i>
    </button>
    
    <a href="?delete=<?= $item['id'] ?>&token=<?= csrfToken() ?>" class="btn btn-sm btn-danger" onclick="return confirm('Yakin hapus foto ini?')" title="Hapus">
        <i class="fas fa-trash"></i>
    </a>
</div>
                            </td>
                        </tr>
                        <?php endforeach; ?>
                    </tbody>
                </table>
            </div>
        <?php endif; ?>
    </div>
</div>

</div> <!-- /.page-body -->
</div> <!-- /.main-content -->
</div> <!-- /.admin-wrapper -->

<!-- MODAL UPLOAD -->
<div class="modal-overlay" id="upload-modal">
    <div class="modal">
        <form method="POST" enctype="multipart/form-data">
            <input type="hidden" name="action" value="upload">
            <?= csrfField() ?>
            <div class="modal-header">
                <h3 class="modal-title"><i class="fas fa-plus-circle"></i> Tambah Foto Galeri</h3>
                <button type="button" class="modal-close" onclick="document.getElementById('upload-modal').classList.remove('open')"><i class="fas fa-times"></i></button>
            </div>
            <div class="modal-body">
                <div class="form-group">
                    <label class="form-label">File Gambar <span>*</span></label>
                    <input type="file" name="image" class="form-control" accept="image/*" required>
                    <div class="form-text">Format: JPG, PNG, WebP. Maks 5MB.</div>
                </div>
                <div class="form-group">
                    <label class="form-label">Judul</label>
                    <input type="text" name="title" class="form-control" placeholder="Contoh: Proyek Perumahan Griya Asri">
                </div>
                <div class="form-group">
                    <label class="form-label">Deskripsi</label>
                    <textarea name="description" class="form-control" rows="2" placeholder="Deskripsi singkat..."></textarea>
                </div>
                <div class="form-group">
                    <label class="form-label">Kategori</label>
                    <select name="category" class="form-control">
                        <option value="proyek">Proyek</option>
                        <option value="distribusi">Distribusi</option>
                        <option value="fasilitas">Fasilitas</option>
                        <option value="armada">Armada</option>
                        <option value="umum">Umum</option>
                    </select>
                </div>
                <div class="form-group">
                    <label class="form-label">Status</label>
                    <select name="is_active" class="form-control">
                        <option value="1">Aktif</option>
                        <option value="0">Nonaktif</option>
                    </select>
                </div>
            </div>
            <div class="modal-footer">
                <button type="button" class="btn btn-secondary" onclick="document.getElementById('upload-modal').classList.remove('open')">Batal</button>
                <button type="submit" class="btn btn-primary"><i class="fas fa-save"></i> Simpan</button>
            </div>
        </form>
    </div>
</div>

<!-- MODAL EDIT -->
<div class="modal-overlay" id="edit-modal">
    <div class="modal">
        <form method="POST" enctype="multipart/form-data">
            <input type="hidden" name="action" value="edit">
            <input type="hidden" name="id" id="edit-id">
            <?= csrfField() ?>
            <div class="modal-header">
                <h3 class="modal-title"><i class="fas fa-edit"></i> Edit Foto Galeri</h3>
                <button type="button" class="modal-close" onclick="document.getElementById('edit-modal').classList.remove('open')"><i class="fas fa-times"></i></button>
            </div>
            <div class="modal-body">
                <div class="form-group">
                    <label class="form-label">Foto Baru (opsional)</label>
                    <input type="file" name="image" class="form-control" accept="image/*">
                    <div class="form-text">Kosongkan jika tidak ingin mengganti foto.</div>
                </div>
                <div class="form-group">
                    <label class="form-label">Judul</label>
                    <input type="text" name="title" id="edit-title" class="form-control">
                </div>
                <div class="form-group">
                    <label class="form-label">Deskripsi</label>
                    <textarea name="description" id="edit-desc" class="form-control" rows="2"></textarea>
                </div>
                <div class="form-group">
                    <label class="form-label">Kategori</label>
                    <select name="category" id="edit-category" class="form-control">
                        <option value="proyek">Proyek</option>
                        <option value="distribusi">Distribusi</option>
                        <option value="fasilitas">Fasilitas</option>
                        <option value="armada">Armada</option>
                        <option value="umum">Umum</option>
                    </select>
                </div>
                <div class="form-group">
                    <label class="form-label">Status</label>
                    <select name="is_active" id="edit-active" class="form-control">
                        <option value="1">Aktif</option>
                        <option value="0">Nonaktif</option>
                    </select>
                </div>
            </div>
            <div class="modal-footer">
                <button type="button" class="btn btn-secondary" onclick="document.getElementById('edit-modal').classList.remove('open')">Batal</button>
                <button type="submit" class="btn btn-primary"><i class="fas fa-save"></i> Update</button>
            </div>
        </form>
    </div>
</div>

<script>
function editGallery(item) {
    document.getElementById('edit-id').value = item.id;
    document.getElementById('edit-title').value = item.title || '';
    document.getElementById('edit-desc').value = item.description || '';
    document.getElementById('edit-category').value = item.category || 'umum';
    document.getElementById('edit-active').value = item.is_active ? 1 : 0;
    document.getElementById('edit-modal').classList.add('open');
}
</script>

<?php include __DIR__ . '/../../partials/footer.php'; ?>