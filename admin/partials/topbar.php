<?php
// admin/partials/topbar.php
$user = currentUser();
$initials = strtoupper(substr($user['name'], 0, 1));
?>
<header class="topbar">
  <button class="hamburger" id="hamburger" aria-label="Toggle Menu">
    <i class="fas fa-bars"></i>
  </button>

  <div class="topbar-title">
    <h1><?= $pageTitle ?? 'Dashboard' ?></h1>
    <?php if (!empty($pageSubtitle)): ?>
      <p><?= $pageSubtitle ?></p>
    <?php endif; ?>
  </div>

 <div class="topbar-actions">
  <!-- Theme Toggle - sembunyikan teks di HP -->
  <button class="topbar-btn" id="theme-toggle" data-tooltip="Ganti Tema">
    <i class="fas fa-moon" id="theme-icon"></i>
  </button>

  <!-- Notifications -->
  <a href="<?= APP_URL ?>/admin/modules/orders/index.php" class="topbar-btn" data-tooltip="Pesanan Baru">
    <i class="fas fa-bell"></i>
    <?php
    try {
        $pendingCount = (int)Database::fetchColumn("SELECT COUNT(*) FROM orders WHERE status = 'pending'");
        if ($pendingCount > 0) echo '<span class="badge-dot"></span>';
    } catch(Exception $e) {}
    ?>
  </a>

  <!-- User Menu - di HP hanya icon, di desktop ada teks -->
  <div class="topbar-user" id="user-menu-btn">
    <div class="user-avatar"><?= $initials ?></div>
    <div class="user-info">
      <div class="user-name"><?= htmlspecialchars($user['name']) ?></div>
      <div class="user-role"><?= ucfirst($user['role']) ?></div>
    </div>
    <i class="fas fa-chevron-down" style="font-size:11px; color:var(--text-muted); margin-left:4px;"></i>
  </div>
</div>
</header>

<!-- User Dropdown Menu (TAMBAHKAN INI) -->
<div class="user-dropdown" id="user-dropdown">
  <a href="<?= APP_URL ?>/admin/modules/settings/index.php">
    <i class="fas fa-user-cog"></i> Pengaturan
  </a>
  <a href="<?= APP_URL ?>/admin/logout.php">
    <i class="fas fa-sign-out-alt"></i> Logout
  </a>
</div>

<!-- Flash Message as Toast -->
<?php
$flash = getFlash();
if ($flash):
?>
<script>
document.addEventListener('DOMContentLoaded', function() {
  const type = '<?= $flash['type'] ?>';
  const msg  = <?= json_encode($flash['message']) ?>;
  const titles = { success: 'Berhasil', error: 'Gagal', warning: 'Peringatan', info: 'Info' };
  Toast.show(type, titles[type] || 'Info', msg);
});
</script>
<?php endif; ?>

<!-- Confirm Delete Modal -->
<div class="modal-overlay" id="confirm-modal">
  <div class="modal" style="max-width:420px;">
    <div class="modal-header">
      <h3 class="modal-title"><i class="fas fa-exclamation-triangle" style="color:var(--danger);margin-right:8px;"></i>Konfirmasi Hapus</h3>
      <button class="modal-close" onclick="Modal.close('confirm-modal')"><i class="fas fa-times"></i></button>
    </div>
    <div class="modal-body">
      <p id="confirm-msg" style="color:var(--text-secondary);font-size:14px;line-height:1.6;">Yakin ingin menghapus data ini?</p>
    </div>
    <div class="modal-footer">
      <button class="btn btn-secondary" onclick="Modal.close('confirm-modal')">Batal</button>
      <button class="btn btn-danger" id="confirm-ok"><i class="fas fa-trash"></i> Hapus</button>
    </div>
  </div>
</div>

<script>
// Hamburger toggle untuk MOBILE dan DESKTOP
document.getElementById('hamburger')?.addEventListener('click', () => {
  const sidebar = document.getElementById('sidebar');
  const overlay = document.getElementById('sidebar-overlay');
  
  // Cek apakah layar mobile (<=768px)
  if (window.innerWidth <= 768) {
    // Mobile: buka/tutup sidebar dari kiri
    sidebar?.classList.toggle('open');
    if (overlay) overlay.style.display = sidebar?.classList.contains('open') ? 'block' : 'none';
  } else {
    // Desktop: collapse sidebar (jadi icon saja)
    sidebar?.classList.toggle('collapsed');
  }
});

// Overlay untuk mobile
document.getElementById('sidebar-overlay')?.addEventListener('click', () => {
  document.getElementById('sidebar')?.classList.remove('open');
  document.getElementById('sidebar-overlay').style.display = 'none';
});

// User Dropdown toggle (tambahan kalau mau)
const userBtn = document.getElementById('user-menu-btn');
const userDropdown = document.getElementById('user-dropdown');

if (userBtn && userDropdown) {
  userBtn.addEventListener('click', (e) => {
    e.stopPropagation();
    userDropdown.classList.toggle('show');
  });
  
  document.addEventListener('click', () => {
    userDropdown.classList.remove('show');
  });
}

// Resize handler: jika resize dari mobile ke desktop, hapus class open
window.addEventListener('resize', () => {
  if (window.innerWidth > 768) {
    document.getElementById('sidebar')?.classList.remove('open');
    document.getElementById('sidebar-overlay').style.display = 'none';
  }
});
</script>