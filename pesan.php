<?php
require_once __DIR__ . '/config/database.php';
require_once __DIR__ . '/config/app.php';
initSession();

// ===== HANDLER AJAX ADD CART =====
if (isset($_GET['action']) && $_GET['action'] === 'add_cart' && $_SERVER['REQUEST_METHOD'] === 'POST') {
    header('Content-Type: application/json');
    $materialId = (int) ($_POST['material'] ?? 0);
    $qty        = (int) ($_POST['qty'] ?? 1);
    if ($materialId <= 0 || $qty <= 0) {
        echo json_encode(['success' => false, 'message' => 'Data tidak valid']);
        exit;
    }
    $material = Database::fetchOne("SELECT id FROM materials WHERE id = ? AND is_active = true", [$materialId]);
    if (!$material) {
        echo json_encode(['success' => false, 'message' => 'Material tidak ditemukan']);
        exit;
    }
    if (!isset($_SESSION['cart']) || !is_array($_SESSION['cart'])) {
        $_SESSION['cart'] = [];
    }
    // Format baru: key = material_id, value = quantity
    if (isset($_SESSION['cart'][$materialId])) {
        $_SESSION['cart'][$materialId] += $qty;
    } else {
        $_SESSION['cart'][$materialId] = $qty;
    }
    $totalItems = array_sum($_SESSION['cart']);
    echo json_encode(['success' => true, 'cart_count' => $totalItems, 'message' => 'Berhasil ditambahkan ke keranjang']);
    exit;
}

// ===== HANDLER AJAX REMOVE CART =====
if (isset($_GET['action']) && $_GET['action'] === 'remove' && isset($_GET['id'])) {
    header('Content-Type: application/json');
    $removeId = (int) $_GET['id'];
    if ($removeId <= 0) {
        echo json_encode(['success' => false, 'message' => 'ID tidak valid']);
        exit;
    }
    if (!isset($_SESSION['cart']) || empty($_SESSION['cart'])) {
        echo json_encode(['success' => false, 'message' => 'Keranjang kosong']);
        exit;
    }
    if (isset($_SESSION['cart'][$removeId])) {
        unset($_SESSION['cart'][$removeId]);
        $totalItems = array_sum($_SESSION['cart']);
        echo json_encode(['success' => true, 'cart_count' => $totalItems, 'message' => 'Item dihapus']);
    } else {
        echo json_encode(['success' => false, 'message' => 'Item tidak ditemukan']);
    }
    exit;
}

// ===== HALAMAN UTAMA =====
$currentSlug = 'pesan';
$pageTitle   = 'Pemesanan Online';
$cp          = getCompanyProfile();

$errors    = [];
$success   = false;
$materials = Database::fetchAll("SELECT id, name, unit, price FROM materials WHERE is_active=true ORDER BY name");

$preMatId = getInt('material');

// ── SESSION CART (format baru) ──
if (!isset($_SESSION['cart']) || !is_array($_SESSION['cart'])) {
    $_SESSION['cart'] = [];
}

// Tambah item dari URL (tombol "Pesan" langsung)
if (getInt('material') && getInt('qty', 0) > 0) {
    $matId = getInt('material');
    $qty   = getInt('qty', 1);
    if (isset($_SESSION['cart'][$matId])) {
        $_SESSION['cart'][$matId] += $qty;
    } else {
        $_SESSION['cart'][$matId] = $qty;
    }
    header('Location: ' . APP_URL . '/pesan.php');
    exit;
}

// Hapus item via GET (fallback)
if (getInt('remove') > 0) {
    $removeId = getInt('remove');
    if (isset($_SESSION['cart'][$removeId])) {
        unset($_SESSION['cart'][$removeId]);
    }
    header('Location: ' . APP_URL . '/pesan.php');
    exit;
}

// Kosongkan keranjang
if (get('clear_cart')) {
    $_SESSION['cart'] = [];
    header('Location: ' . APP_URL . '/pesan.php');
    exit;
}

// ── PROSES FORM PESANAN ──────────────────────
if ($_SERVER['REQUEST_METHOD'] === 'POST' && !isset($_GET['action'])) {
    if (!verifyCsrf()) {
        $errors[] = 'Token keamanan tidak valid. Silakan muat ulang halaman.';
    } else {
        $customerName    = post('customer_name');
        $customerPhone   = post('customer_phone');
        $customerEmail   = post('customer_email');
        $deliveryAddress = post('delivery_address');
        $notes           = post('notes');

        if (empty($customerName))    $errors[] = 'Nama pemesan wajib diisi.';
        if (empty($customerPhone))   $errors[] = 'Nomor HP wajib diisi.';
        if (empty($deliveryAddress)) $errors[] = 'Alamat pengiriman wajib diisi.';

        $items    = [];
        $matIds   = $_POST['material_id']  ?? [];
        $qtys     = $_POST['quantity']     ?? [];

        if (empty($matIds) || empty(array_filter($qtys))) {
            $errors[] = 'Tambahkan minimal satu material.';
        }

        $totalAmount = 0;
        foreach ($matIds as $i => $matId) {
            $matId = (int)$matId;
            $qty   = (int)($qtys[$i] ?? 0);
            if (!$matId || $qty <= 0) continue;
            $mat = Database::fetchOne("SELECT id, price, name FROM materials WHERE id=? AND is_active=true", [$matId]);
            if ($mat) {
                $subtotal     = $mat['price'] * $qty;
                $totalAmount += $subtotal;
                $items[]      = ['material_id' => $matId, 'quantity' => $qty, 'price_per_unit' => $mat['price'], 'subtotal' => $subtotal];
            }
        }

        if (empty($items) && empty($errors)) {
            $errors[] = 'Tidak ada item valid dalam pesanan.';
        }

        if (empty($errors)) {
            Database::beginTransaction();
            try {
                $orderNumber = generateOrderNumber();
                $orderId = Database::insert('orders', [
                    'order_number'     => $orderNumber,
                    'customer_name'    => $customerName,
                    'customer_phone'   => $customerPhone,
                    'customer_email'   => $customerEmail ?: null,
                    'delivery_address' => $deliveryAddress,
                    'notes'            => $notes ?: null,
                    'status'           => 'pending',
                    'total_amount'     => $totalAmount,
                ]);
                foreach ($items as $item) {
                    $item['order_id'] = $orderId;
                    Database::insert('order_items', $item);
                }
                Database::commit();
                $success     = true;
                $successOrder= $orderNumber;
                $_SESSION['cart'] = [];
            } catch (Exception $e) {
                Database::rollback();
                $errors[] = 'Terjadi kesalahan sistem. Silakan coba lagi.';
                error_log('Order error: ' . $e->getMessage());
            }
        }
    }
}

include __DIR__ . '/includes/public_head.php';
?>

<div style="padding-top:70px;">

<div style="background: linear-gradient(135deg, #0f172a 0%, #20bc95 50%, #0f172a 100%); padding:60px 0;">
  <div class="container" style="text-align:center;">
    <div class="section-tag" style="color:var(--brand-300);">Pemesanan</div>
    <h1 style="font-size:clamp(28px,5vw,44px);font-weight:800;color:white;margin-top:10px;">Pesan Material Online</h1>
    <p style="color:rgba(255,255,255,.6);font-size:15px;margin-top:12px;">Isi formulir di bawah dan tim kami akan segera menghubungi Anda</p>
  </div>
</div>

<section class="section">
  <div class="container" style="max-width:860px;">

    <?php if ($success): ?>
    <!-- Success State (sama) -->
    <div style="text-align:center;padding:60px 40px;background:var(--bg-surface);border:1px solid var(--border);border-radius:var(--radius-xl);box-shadow:var(--shadow-lg);">
      <div style="width:80px;height:80px;border-radius:50%;background:var(--success-bg);color:var(--success);display:flex;align-items:center;justify-content:center;font-size:36px;margin:0 auto 20px;">
        <i class="fas fa-check-circle"></i>
      </div>
      <h2 style="font-size:28px;font-weight:800;margin-bottom:12px;">Pesanan Berhasil Dikirim!</h2>
      <p style="color:var(--text-secondary);font-size:15px;line-height:1.7;max-width:480px;margin:0 auto 24px;">
        Nomor pesanan Anda adalah <strong style="color:var(--brand-600);"><?= htmlspecialchars($successOrder ?? '') ?></strong>.<br>
        Tim kami akan menghubungi Anda segera untuk konfirmasi pesanan.
      </p>
      <div style="background:var(--bg-muted);border-radius:var(--radius-lg);padding:16px 24px;margin-bottom:28px;display:inline-block;">
        <div style="font-size:13px;color:var(--text-muted);">Nomor Pesanan</div>
        <div style="font-size:22px;font-weight:800;color:var(--brand-600);font-family:var(--font-mono);"><?= htmlspecialchars($successOrder ?? '') ?></div>
      </div>
      <div style="display:flex;gap:12px;justify-content:center;flex-wrap:wrap;">
        <a href="<?= APP_URL ?>/pesan.php" class="btn btn-primary btn-lg"><i class="fas fa-plus"></i> Buat Pesanan Baru</a>
        <?php if($cp['whatsapp']):?>
        <?php
        $customerName = $_POST['customer_name'] ?? $customerName ?? '';
        $customerPhone = $_POST['customer_phone'] ?? $customerPhone ?? '';
        $customerEmail = $_POST['customer_email'] ?? '';
        $deliveryAddress = $_POST['delivery_address'] ?? $deliveryAddress ?? '';
        $orderNotes = $_POST['notes'] ?? '';
        $totalAmount = $totalAmount ?? 0;

        $itemDetails = '';
        foreach ($items ?? [] as $idx => $item) {
            $mat = Database::fetchOne("SELECT name, unit FROM materials WHERE id = ?", [$item['material_id']]);
            $matName = $mat['name'] ?? 'Material';
            $matUnit = $mat['unit'] ?? 'unit';
            $itemDetails .= ($idx + 1) . ". " . $matName . " x " . $item['quantity'] . " " . $matUnit . " = Rp " . number_format($item['subtotal'], 0, ',', '.') . "%0A";
        }

        $orderLink = APP_URL . '/admin-ab/modules/orders/view.php?id=' . $orderId;

        $waMessage = "PESANAN BARU%0A";
        $waMessage .= "══════════════════%0A%0A";
        $waMessage .= "Nomor Pesanan: " . $successOrder . " %0A";
        $waMessage .= "Link: " . $orderLink . "%0A%0A";
        $waMessage .= "Tanggal: " . date('d M Y H:i') . "%0A";
        $waMessage .= "━━━━━━━━━━━━━━━━%0A";
        $waMessage .= "PELANGGAN:%0A";
        $waMessage .= "   Nama: " . $customerName . "%0A";
        $waMessage .= "   HP: " . $customerPhone . "%0A";
        if ($customerEmail) $waMessage .= "   Email: " . $customerEmail . "%0A";
        $waMessage .= "   Alamat: " . $deliveryAddress . "%0A";
        if ($orderNotes) $waMessage .= "   Catatan: " . $orderNotes . "%0A";
        $waMessage .= "━━━━━━━━━━━━━━━━%0A";
        $waMessage .= "ITEM PESANAN:%0A";
        $waMessage .= $itemDetails ?: "   (Tidak ada item)%0A";
        $waMessage .= "━━━━━━━━━━━━━━━━%0A";
        $waMessage .= "Total: Rp " . number_format($totalAmount, 0, ',', '.') . "%0A";
        $waMessage .= "══════════════════%0A%0A";
        $waMessage .= "Silakan segera diproses. Terima kasih.";
        ?>
        <a href="https://wa.me/<?=preg_replace('/[^0-9]/','',$cp['whatsapp'])?>?text=<?= $waMessage ?>" target="_blank" class="btn btn-lg" style="background:#25d366;color:white;border-color:#25d366; padding:14px 24px; font-size:15px;">
            <i class="fab fa-whatsapp"></i> Konfirmasi via WA
        </a>
        <?php endif;?>
        <a href="<?= APP_URL ?>/" class="btn btn-secondary btn-lg"><i class="fas fa-home"></i> Beranda</a>
      </div>
    </div>

    <?php else: ?>

    <?php foreach ($errors as $e): ?>
      <div class="alert alert-danger" data-auto-dismiss="6000"><i class="fas fa-exclamation-circle"></i> <?= htmlspecialchars($e) ?></div>
    <?php endforeach; ?>

    <form method="POST" id="order-form">
      <?= csrfField() ?>

      <!-- Customer Info -->
      <div class="card mb-20">
        <div class="card-header">
          <div class="card-title"><i class="fas fa-user" style="margin-right:8px;color:var(--brand-600);"></i>Data Pemesan</div>
        </div>
        <div class="card-body">
          <div class="grid grid-2" style="gap:16px;">
            <div class="form-group" style="margin-bottom:0;">
              <label class="form-label">Nama Lengkap <span>*</span></label>
              <input type="text" name="customer_name" class="form-control" value="<?= htmlspecialchars($_POST['customer_name']??'') ?>" placeholder="Nama lengkap Anda" required>
            </div>
            <div class="form-group" style="margin-bottom:0;">
              <label class="form-label">Nomor HP / WhatsApp <span>*</span></label>
              <input type="tel" name="customer_phone" class="form-control" value="<?= htmlspecialchars($_POST['customer_phone']??'') ?>" placeholder="08xxxxxxxxxx" required>
            </div>
          </div>
          <div class="form-group" style="margin-top:16px;margin-bottom:0;">
            <label class="form-label">Email (opsional)</label>
            <input type="email" name="customer_email" class="form-control" value="<?= htmlspecialchars($_POST['customer_email']??'') ?>" placeholder="email@contoh.com">
          </div>
          <div class="form-group" style="margin-top:16px;margin-bottom:0;">
            <label class="form-label">Alamat Pengiriman <span>*</span></label>
            <textarea name="delivery_address" class="form-control" rows="3" placeholder="Alamat lengkap pengiriman material..." required><?= htmlspecialchars($_POST['delivery_address']??'') ?></textarea>
          </div>
        </div>
      </div>

      <!-- ── KERANJANG PESANAN ─────────────────────────────── -->
      <div style="background:var(--bg-surface);border:1px solid var(--border);border-radius:var(--radius-lg);padding:16px 18px;margin-bottom:20px;">
        <div style="display:flex;justify-content:space-between;align-items:center;margin-bottom:14px;">
          <span style="font-size:14px;font-weight:700;color:var(--text-primary);">
            🛒 Keranjang Pesanan
          </span>
          <span id="item-count" style="font-size:12px;background:var(--brand-50);color:var(--brand-600);padding:2px 12px;border-radius:20px;font-weight:600;">0 item</span>
        </div>
        <div id="order-items" style="display:flex;flex-direction:column;gap:12px;">
          <!-- Item rows akan diisi JS -->
        </div>
        <button type="button" onclick="addItem()" class="btn btn-outline w-100" style="margin-top:8px;border-style:dashed;padding:12px;margin-bottom:20px;">
          <i class="fas fa-plus"></i> Tambah Material Lain
        </button>
        <div style="border-top:1px solid var(--border);padding-top:14px;margin-top:4px;display:flex;justify-content:space-between;align-items:center;">
          <span style="font-size:13px;color:var(--text-muted);">Estimasi Total</span>
          <span style="font-size:20px;font-weight:800;color:var(--brand-600);" id="grand-total">Rp 0</span>
        </div>
        <div style="display:flex;gap:10px;margin-top:12px;flex-wrap:wrap;border-top:1px solid var(--border);padding-top:12px;">
          <button type="button" onclick="clearCart()" class="btn btn-sm btn-danger" style="font-size:12px;">
            <i class="fas fa-trash"></i> Kosongkan Keranjang
          </button>
          <span style="font-size:12px;color:var(--text-muted);align-self:center;">
            Total item: <strong id="item-count-label">0</strong>
          </span>
        </div>
      </div>

      <!-- Notes -->
      <div class="card mb-20">
        <div class="card-header"><div class="card-title"><i class="fas fa-sticky-note" style="margin-right:8px;color:var(--brand-600);"></i>Catatan</div></div>
        <div class="card-body">
          <textarea name="notes" class="form-control" rows="3" placeholder="Catatan tambahan seperti waktu pengiriman, instruksi khusus, dll..."><?= htmlspecialchars($_POST['notes']??'') ?></textarea>
        </div>
      </div>

      <div style="background:var(--info-bg);border:1px solid var(--brand-200);border-radius:var(--radius-md);padding:14px 18px;margin-bottom:20px;font-size:13.5px;color:var(--text-primary);">
        <i class="fas fa-info-circle"></i>
        Pesanan Anda akan dikonfirmasi oleh tim kami melalui telepon atau WhatsApp dalam waktu 1x24 jam.
      </div>

      <button type="submit" class="btn btn-primary btn-lg w-100" style="justify-content:center;font-size:16px;padding:15px;">
        <i class="fas fa-paper-plane"></i> Kirim Pesanan Sekarang
      </button>
    </form>
    <?php endif; ?>

  </div>
</section>

</div>

<?php include __DIR__ . '/includes/public_footer.php'; ?>

<script>
const materialsData = <?= json_encode(array_column($materials, null, 'id')) ?>;
const preMatId = <?= $preMatId ?: 0 ?>;
let itemCount = 0;

function addItem(matId = '', qty = 1) {
  const i = itemCount++;
  const div = document.createElement('div');
  div.id = `item-${i}`;
  div.dataset.materialId = matId;
  div.style.cssText = `
    display: grid;
    grid-template-columns: 1fr 80px 100px 36px;
    gap: 8px;
    align-items: center;
    padding: 10px 12px;
    background: var(--bg-muted);
    border-radius: var(--radius-md);
    border: 1px solid var(--border);
    transition: all 0.2s ease;
  `;

  let opts = '<option value="">-- Pilih Material --</option>';
  Object.values(materialsData).forEach(m => {
    const selected = (m.id == matId) ? 'selected' : '';
    opts += `<option value="${m.id}" ${selected} data-price="${m.price}" data-unit="${m.unit}">${m.name}</option>`;
  });

  div.innerHTML = `
    <div>
      <select name="material_id[]" class="form-control mat-select" onchange="updateSubtotal(${i})" required style="font-size:13px;padding:6px 8px;height:38px;">
        ${opts}
      </select>
    </div>
    <div style="display:flex;align-items:center;gap:4px;">
      <input type="number" name="quantity[]" class="form-control qty-input" value="${qty}" min="1" onchange="updateSubtotal(${i})" style="text-align:center;width:55px;padding:6px 4px;height:38px;">
      <span class="unit-label" style="font-size:11px;color:var(--text-muted);white-space:nowrap;">unit</span>
    </div>
    <div class="subtotal" style="font-size:13px;font-weight:700;color:var(--brand-600);text-align:right;">Rp 0</div>
    <div>
      <button type="button" onclick="removeItem(${i})" class="btn btn-sm btn-danger btn-icon" style="padding:4px 6px;height:32px;width:32px;">
        <i class="fas fa-times"></i>
      </button>
    </div>
  `;

  document.getElementById('order-items').appendChild(div);
  if (matId) updateSubtotal(i);
  updateGrandTotal();
}

function removeItem(i) {
  const el = document.getElementById(`item-${i}`);
  if (!el) return;
  const matId = el.dataset.materialId;
  if (!matId) {
    el.remove();
    updateGrandTotal();
    return;
  }
  fetch('<?= APP_URL ?>/pesan.php?action=remove&id=' + matId, {
    method: 'GET',
    headers: { 'X-Requested-With': 'XMLHttpRequest' }
  })
  .then(response => response.json())
  .then(data => {
    if (data.success) {
      el.remove();
      updateGrandTotal();
      if (typeof updateCartBadge === 'function') {
        updateCartBadge(data.cart_count);
      }
    } else {
      alert('Gagal menghapus: ' + data.message);
    }
  })
  .catch(err => {
    alert('Terjadi kesalahan, coba lagi.');
  });
}

function updateSubtotal(i) {
  const el = document.getElementById(`item-${i}`);
  if (!el) return;
  const sel = el.querySelector('.mat-select');
  const qty = parseFloat(el.querySelector('.qty-input').value) || 0;
  const opt = sel.options[sel.selectedIndex];
  const price = parseFloat(opt?.dataset?.price || 0);
  const unit  = opt?.dataset?.unit || 'unit';
  el.querySelector('.unit-label').textContent = unit;
  const sub = price * qty;
  el.querySelector('.subtotal').textContent = 'Rp ' + sub.toLocaleString('id-ID');
  updateGrandTotal();
}

function updateGrandTotal() {
  let total = 0;
  let count = 0;
  document.querySelectorAll('#order-items .mat-select').forEach((sel) => {
    const row = sel.closest('[id^="item-"]');
    if (!row) return;
    const qty = parseFloat(row.querySelector('.qty-input').value) || 0;
    const price = parseFloat(sel.options[sel.selectedIndex]?.dataset?.price || 0);
    total += price * qty;
    count++;
  });
  document.getElementById('grand-total').textContent = 'Rp ' + total.toLocaleString('id-ID');
  document.getElementById('item-count').textContent = count + ' item';
  document.getElementById('item-count-label').textContent = count;
}

function clearCart() {
    if (confirm('Yakin ingin mengosongkan keranjang?')) {
        window.location.href = '<?= APP_URL ?>/pesan.php?clear_cart=1';
    }
}

document.addEventListener('DOMContentLoaded', function() {
    <?php 
    // Session cart sekarang dalam format [material_id => quantity]
    if (!empty($_SESSION['cart']) && is_array($_SESSION['cart'])): 
        foreach ($_SESSION['cart'] as $id => $qty): 
    ?>
            addItem(<?= $id ?>, <?= $qty ?>);
    <?php 
        endforeach; 
    else: 
        if ($preMatId > 0): 
    ?>
            addItem(<?= $preMatId ?>, 1);
    <?php 
        endif; 
    endif; 
    ?>
});
</script>