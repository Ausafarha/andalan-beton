<?php
// admin/partials/sidebar.php
$currentPage = basename($_SERVER['PHP_SELF'], '.php');
$currentDir  = basename(dirname($_SERVER['PHP_SELF']));

// ===== FUNGSI isActive YANG LEBIH AKURAT =====
function isActive($targetDir, $targetPages = []) {
    global $currentDir, $currentPage;
    
    // Jika folder saat ini sama dengan target
    if ($currentDir === $targetDir) {
        // Halaman index atau kosong -> aktif
        if ($currentPage === 'index' || $currentPage === '') {
            return 'active';
        }
        // Halaman ada di daftar targetPages -> aktif
        if (in_array($currentPage, $targetPages)) {
            return 'active';
        }
        // Halaman create/edit/view/delete dari folder yang sama -> aktif
        if (in_array($currentPage, ['create', 'edit', 'view', 'delete'])) {
            return 'active';
        }
    }
    
    return '';
}

$user = currentUser();

// Pending orders count
try {
    $pendingOrders = (int)Database::fetchColumn("SELECT COUNT(*) FROM orders WHERE status = 'pending'");
} catch(Exception $e) { $pendingOrders = 0; }

// Low stock count
try {
    $lowStock = (int)Database::fetchColumn("SELECT COUNT(*) FROM material_stock WHERE stock_status IN ('low_stock','out_of_stock') AND is_active = true");
} catch(Exception $e) { $lowStock = 0; }
?>
<aside class="sidebar" id="sidebar">
  <div class="sidebar-logo">
    <div class="sidebar-logo-icon">AB</div>
    <div class="sidebar-logo-text">
      <div class="sidebar-logo-name">Andalan Beton</div>
      <div class="sidebar-logo-sub">Admin Panel</div>
    </div>
  </div>

  <nav class="sidebar-nav">
    <div class="nav-section-title">Utama</div>

    <a href="<?= APP_URL ?>/admin-ab/dashboard.php" class="nav-item <?= $currentPage === 'dashboard' ? 'active' : '' ?>">
      <i class="fas fa-chart-line"></i>
      <span>Dashboard</span>
    </a>

    <div class="nav-section-title">Inventori</div>

    <a href="<?= APP_URL ?>/admin-ab/modules/material/index.php" class="nav-item <?= isActive('material', ['create', 'edit']) ?>">
      <i class="fas fa-boxes"></i>
      <span>Material</span>
      <?php if ($lowStock > 0): ?>
        <span class="nav-badge danger" data-tooltip="Stok Menipis"><?= $lowStock ?></span>
      <?php endif; ?>
    </a>

    <a href="<?= APP_URL ?>/admin-ab/modules/stock_in/index.php" class="nav-item <?= isActive('stock_in', ['create', 'edit']) ?>">
      <i class="fas fa-arrow-down"></i>
      <span>Barang Masuk</span>
    </a>

    <a href="<?= APP_URL ?>/admin-ab/modules/stock_out/index.php" class="nav-item <?= isActive('stock_out', ['create', 'edit']) ?>">
      <i class="fas fa-arrow-up"></i>
      <span>Barang Keluar</span>
    </a>

    <div class="nav-section-title">Transaksi</div>

    <a href="<?= APP_URL ?>/admin-ab/modules/orders/index.php" class="nav-item <?= isActive('orders', ['view', 'edit']) ?>">
      <i class="fas fa-clipboard-list"></i>
      <span>Pesanan</span>
      <?php if ($pendingOrders > 0): ?>
        <span class="nav-badge"><?= $pendingOrders ?></span>
      <?php endif; ?>
    </a>

    <div class="nav-section-title">Lainnya</div>

    <a href="<?= APP_URL ?>/admin-ab/modules/reports/index.php" class="nav-item <?= isActive('reports') ?>">
      <i class="fas fa-file-alt"></i>
      <span>Laporan</span>
    </a>

    <a href="<?= APP_URL ?>/admin-ab/modules/gallery/index.php" class="nav-item <?= isActive('gallery', ['create', 'edit']) ?>">
      <i class="fas fa-images"></i>
      <span>Galeri</span>
    </a>

    <a href="<?= APP_URL ?>/admin-ab/modules/settings/index.php" class="nav-item <?= isActive('settings') ?>">
      <i class="fas fa-cog"></i>
      <span>Pengaturan</span>
    </a>
  </nav>

  <div class="sidebar-footer">
    <a href="<?= APP_URL ?>/" target="_blank" class="nav-item" style="margin-bottom:6px;">
      <i class="fas fa-external-link-alt"></i>
      <span>Lihat Website</span>
    </a>
    <a href="<?= APP_URL ?>/admin-ab/logout.php" class="nav-item" style="color:#f87171;">
      <i class="fas fa-sign-out-alt"></i>
      <span>Keluar</span>
    </a>
  </div>
</aside>
<div class="sidebar-overlay" id="sidebar-overlay"></div>
<script>
document.addEventListener("DOMContentLoaded", function() {
    // Cari semua link menu di sidebar yang mengarah ke admin panel internal
    const links = document.querySelectorAll(".sidebar-nav .nav-item");
    const mainContent = document.querySelector(".main-content");

    if (!mainContent) return;

    links.forEach(link => {
        // Abaikan tombol keluar/logout
        if (link.getAttribute("href").includes("logout.php")) return;

        link.addEventListener("click", function(e) {
            const url = this.getAttribute("href");
            
            // Cek jika link valid dan bukan tab baru
            if (url && !link.getAttribute("target")) {
                e.preventDefault();

                // 1. Ubah visual menu aktif di sidebar secara instan
                links.forEach(l => l.classList.remove("active"));
                this.classList.add("active");

                // 2. Beri efek loading halus pada konten utama
                mainContent.style.opacity = "0.5";
                mainContent.style.transition = "opacity 0.2s ease";

                // 3. Ambil data halaman baru di latar belakang menggunakan Fetch API
                fetch(url)
                    .then(response => response.text())
                    .then(html => {
                        // Parsir HTML yang didapat
                        const parser = new DOMParser();
                        const doc = parser.parseFromString(html, 'text/html');
                        const newContent = doc.querySelector(".main-content");

                        if (newContent) {
                            // Ganti isi konten utama tanpa reload page penuh
                            mainContent.innerHTML = newContent.innerHTML;
                            mainContent.style.opacity = "1";
                            
                            // Perbarui URL di address bar browser agar bisa di-back/forward
                            window.history.pushState({path: url}, '', url);
                            
                            // Re-eksekusi jika ada script inline baru di halaman yang dimuat
                            newContent.querySelectorAll("script").forEach(oldScript => {
                                const newScript = document.createElement("script");
                                Array.from(oldScript.attributes).forEach(attr => newScript.setAttribute(attr.name, attr.value));
                                newScript.appendChild(document.createTextNode(oldScript.innerHTML));
                                oldScript.parentNode.replaceChild(newScript, oldScript);
                            });
                        } else {
                            // Jika struktur gagal, fallback ke reload biasa
                            window.location.href = url;
                        }
                    })
                    .catch(err => {
                        window.location.href = url;
                    });
            }
        });
    });

// UPGRADE: Deteksi tombol Back/Forward browser dan matikan BFCache
window.addEventListener('popstate', function() {
    window.location.reload();
});

window.addEventListener('pageshow', function(event) {
    // Jika halaman dimuat dari cache history browser (tombol back)
    if (event.persisted || (window.performance && window.performance.navigation.type === 2)) {
        window.location.reload();
    }
});
});
</script>