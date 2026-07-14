<?php
require_once __DIR__ . '/../config/database.php';
require_once __DIR__ . '/../config/app.php';
initSession();
requireLogin();



// Cegah caching halaman admin (biar gak bisa back)
header("Cache-Control: no-store, no-cache, must-revalidate, max-age=0");
header("Cache-Control: post-check=0, pre-check=0", false);
header("Pragma: no-cache");
header("Expires: Thu, 01 Jan 1970 00:00:00 GMT");

$pageTitle    = 'Dashboard';
$pageSubtitle = 'Ringkasan operasional ' . APP_NAME;

// ── Stats ──────────────────────────────────────────────────
$totalMaterials    = (int)Database::fetchColumn("SELECT COUNT(*) FROM materials WHERE is_active = true");
$totalStock        = (int)Database::fetchColumn("SELECT COALESCE(SUM(current_stock),0) FROM material_stock WHERE is_active = true");
$totalStockIn      = (int)Database::fetchColumn("SELECT COALESCE(SUM(quantity),0) FROM stock_in");
$totalStockOut     = (int)Database::fetchColumn("SELECT COALESCE(SUM(quantity),0) FROM stock_out");
$totalOrders       = (int)Database::fetchColumn("SELECT COUNT(*) FROM orders");
$pendingOrders     = (int)Database::fetchColumn("SELECT COUNT(*) FROM orders WHERE status = 'pending'");
$processingOrders  = (int)Database::fetchColumn("SELECT COUNT(*) FROM orders WHERE status = 'processing'");
$totalRevenue      = (float)Database::fetchColumn("SELECT COALESCE(SUM(total_amount),0) FROM orders WHERE status = 'completed'");
$lowStockCount     = (int)Database::fetchColumn("SELECT COUNT(*) FROM material_stock WHERE stock_status = 'low_stock' AND is_active = true");
$outOfStockCount   = (int)Database::fetchColumn("SELECT COUNT(*) FROM material_stock WHERE stock_status = 'out_of_stock' AND is_active = true");

// ── PENJUALAN / REVENUE ────────────────────────────────────
$thisMonthRevenue = (float)Database::fetchColumn(
    "SELECT COALESCE(SUM(total_amount),0) FROM orders 
     WHERE status = 'completed' AND TO_CHAR(created_at,'YYYY-MM') = TO_CHAR(NOW(),'YYYY-MM')"
);
$lastMonthRevenue = (float)Database::fetchColumn(
    "SELECT COALESCE(SUM(total_amount),0) FROM orders 
     WHERE status = 'completed' AND TO_CHAR(created_at,'YYYY-MM') = TO_CHAR(NOW() - INTERVAL '1 month','YYYY-MM')"
);
$revenueGrowth = $lastMonthRevenue > 0 ? round((($thisMonthRevenue - $lastMonthRevenue) / $lastMonthRevenue) * 100, 1) : 0;


$topRevenueMaterials = Database::fetchAll("
    SELECT m.name, m.unit, COALESCE(SUM(oi.quantity * oi.price_per_unit),0) as total_revenue
    FROM order_items oi
    JOIN materials m ON oi.material_id = m.id
    JOIN orders o ON oi.order_id = o.id
    WHERE o.status = 'completed'
    GROUP BY m.id, m.name, m.unit
    ORDER BY total_revenue DESC
    LIMIT 5
");

// ── UPGRADE: OPTIMASI GRAFIK BULANAN DENGAN 1 KUERI (GROUP BY) ──
$chartMonths = [];
$chartIn     = [];
$chartOut    = [];
$chartOrders = [];
$chartRevenue = [];

// 1. Buat array penampung 12 bulan terakhir secara statis di PHP agar cepat
for ($i = 11; $i >= 0; $i--) {
    $ym = date('Y-m', strtotime("-{$i} months"));
    $lbl = date('M Y', strtotime("-{$i} months"));
    $chartMonths[] = $lbl;
    $chartData[$ym] = ['in' => 0, 'out' => 0, 'orders' => 0, 'revenue' => 0];
}

// Ambil batas tanggal awal bulan ke-11 yang lalu agar filter data di SQL presisi 12 bulan penuh
$startDate = date('Y-m-01', strtotime("-11 months"));

// 2. Tarik data Stock In sekaligus
$rawIn = Database::fetchAll("SELECT TO_CHAR(received_date,'YYYY-MM') as ym, SUM(quantity) as total FROM stock_in WHERE received_date >= ? GROUP BY ym", [$startDate]);
foreach($rawIn as $r) { if(isset($chartData[$r['ym']])) $chartData[$r['ym']]['in'] = (int)$r['total']; }

// 3. Tarik data Stock Out sekaligus
$rawOut = Database::fetchAll("SELECT TO_CHAR(out_date,'YYYY-MM') as ym, SUM(quantity) as total FROM stock_out WHERE out_date >= ? GROUP BY ym", [$startDate]);
foreach($rawOut as $r) { if(isset($chartData[$r['ym']])) $chartData[$r['ym']]['out'] = (int)$r['total']; }

// 4. Tarik data Orders & Revenue sekaligus (Menggunakan filter tanggal string yang aman)
$rawOrders = Database::fetchAll("SELECT TO_CHAR(created_at,'YYYY-MM') as ym, COUNT(*) as total_orders, SUM(CASE WHEN status='completed' THEN total_amount ELSE 0 END) as total_rev FROM orders WHERE created_at >= ? GROUP BY ym", [$startDate]);
foreach($rawOrders as $r) {
    if(isset($chartData[$r['ym']])) {
        $chartData[$r['ym']]['orders'] = (int)$r['total_orders'];
        $chartData[$r['ym']]['revenue'] = round((float)$r['total_rev'] / 1000000, 1); // Dalam satuan Juta
    }
}

// 5. Pindahkan data ke array Chart.js
foreach($chartData as $ym => $v) {
    $chartIn[] = $v['in'];
    $chartOut[] = $v['out'];
    $chartOrders[] = $v['orders'];
    $chartRevenue[] = $v['revenue'];
}
// ── END OF UPGRADE ──

// ── Weekly chart (last 7 days) ─────────────────────────────
$weekDays   = [];
$weekIn     = [];
$weekOut    = [];
for ($i = 6; $i >= 0; $i--) {
    $date = date('Y-m-d', strtotime("-{$i} days"));
    $lbl  = date('D d/m', strtotime("-{$i} days"));
    $weekDays[] = $lbl;
    $weekIn[]   = (int)Database::fetchColumn("SELECT COALESCE(SUM(quantity),0) FROM stock_in WHERE received_date=?", [$date]);
    $weekOut[]  = (int)Database::fetchColumn("SELECT COALESCE(SUM(quantity),0) FROM stock_out WHERE out_date=?", [$date]);
}

// ── Top materials ──────────────────────────────────────────
$topMaterials = Database::fetchAll("
    SELECT m.name, m.unit, COALESCE(SUM(so.quantity),0) as total_out, ms.current_stock
    FROM materials m
    LEFT JOIN stock_out so ON so.material_id = m.id
    LEFT JOIN material_stock ms ON ms.id = m.id
    WHERE m.is_active = true
    GROUP BY m.id, m.name, m.unit, ms.current_stock
    ORDER BY total_out DESC
    LIMIT 5
");

// ── Recent activities ──────────────────────────────────────
$recentActivities = Database::fetchAll("
    SELECT al.*, u.name as user_name
    FROM activity_logs al
    LEFT JOIN users u ON al.user_id = u.id
    ORDER BY al.created_at DESC
    LIMIT 8
");

// ── Recent orders ──────────────────────────────────────────
$recentOrders = Database::fetchAll("
    SELECT * FROM orders ORDER BY created_at DESC LIMIT 5
");

// ── Low stock materials ────────────────────────────────────
$lowStockMaterials = Database::fetchAll("
    SELECT * FROM material_stock
    WHERE stock_status IN ('low_stock','out_of_stock') AND is_active = true
    ORDER BY current_stock ASC
    LIMIT 6
");

include __DIR__ . '/partials/head.php';
?>

<div class="admin-wrapper">
<?php include __DIR__ . '/partials/sidebar.php'; ?>

<div class="main-content">
<?php include __DIR__ . '/partials/topbar.php'; ?>

<div class="page-body">

<?php if (isLoggedIn()): ?>
    <?php include __DIR__ . '/modules/dashboard/install-prompt.php'; ?>
<?php endif; ?>

<!-- ── Stats Grid ────────────────────────────────────────── -->
<div class="grid grid-4 mb-20">
  <div class="stat-card" style="--stat-color: var(--brand-500); cursor: pointer;" onclick="window.location.href='<?= APP_URL ?>/admin-ab/modules/material/index.php'">
    <div class="stat-icon" style="background: var(--info-bg); color: var(--brand-600);">
      <i class="fas fa-boxes"></i>
    </div>
    <div class="stat-content">
      <div class="stat-label">Total Material</div>
      <div class="stat-value" data-counter><?= $totalMaterials ?></div>
      <div class="stat-change up"><i class="fas fa-arrow-up"></i> Jenis material aktif</div>
    </div>
  </div>

  <div class="stat-card" style="--stat-color: var(--success); cursor: pointer;" onclick="window.location.href='<?= APP_URL ?>/admin-ab/modules/material/index.php?status=low'">
    <div class="stat-icon" style="background: var(--success-bg); color: var(--success);">
      <i class="fas fa-warehouse"></i>
    </div>
    <div class="stat-content">
      <div class="stat-label">Total Stok</div>
      <div class="stat-value" data-counter><?= $totalStock ?></div>
      <div class="stat-change <?= $lowStockCount > 0 ? 'down' : 'up' ?>">
        <?php if ($lowStockCount > 0): ?>
          <i class="fas fa-exclamation-triangle"></i> <?= $lowStockCount ?> stok menipis
        <?php else: ?>
          <i class="fas fa-check"></i> Semua stok normal
        <?php endif; ?>
      </div>
    </div>
  </div>

  <div class="stat-card" style="--stat-color: var(--warning); cursor: pointer;" onclick="window.location.href='<?= APP_URL ?>/admin-ab/modules/orders/index.php'">
    <div class="stat-icon" style="background: var(--warning-bg); color: var(--warning);">
      <i class="fas fa-clipboard-list"></i>
    </div>
    <div class="stat-content">
      <div class="stat-label">Total Pesanan</div>
      <div class="stat-value" data-counter><?= $totalOrders ?></div>
      <div class="stat-change <?= $pendingOrders > 0 ? 'down' : 'up' ?>">
        <?php if ($pendingOrders > 0): ?>
          <i class="fas fa-clock"></i> <?= $pendingOrders ?> menunggu konfirmasi
        <?php else: ?>
          <i class="fas fa-check"></i> Semua terlayani
        <?php endif; ?>
      </div>
    </div>
  </div>

  <div class="stat-card" style="--stat-color: #8b5cf6; cursor: pointer;" onclick="window.location.href='<?= APP_URL ?>/admin-ab/modules/reports/index.php?type=orders'">
    <div class="stat-icon" style="background: rgba(241, 35, 35, 0.1); color: #8b5cf6;">
      <i class="fas fa-chart-line"></i>
    </div>
    <div class="stat-content">
      <div class="stat-label">Total Penjualan</div>
      <div class="stat-value" style="font-size:18px;" data-counter data-format="rupiah"><?= number_format($totalRevenue, 0, ',', '.') ?></div>
      <div class="stat-change <?= $revenueGrowth >= 0 ? 'up' : 'down' ?>">
        <?php if ($revenueGrowth >= 0): ?>
          <i class="fas fa-arrow-up"></i> <?= abs($revenueGrowth) ?>% dari bulan lalu
        <?php else: ?>
          <i class="fas fa-arrow-down"></i> <?= abs($revenueGrowth) ?>% dari bulan lalu
        <?php endif; ?>
      </div>
    </div>
  </div>
</div>

<!-- ── Quick Stats Row 2 ──────────────────────────────────── -->
<div class="grid grid-4 mb-20">
  <div class="stat-card" style="--stat-color: #06b6d4; cursor: pointer;" onclick="window.location.href='<?= APP_URL ?>/admin-ab/modules/stock_in/index.php'">
    <div class="stat-icon" style="background: rgba(6,182,212,0.1); color: #06b6d4; width:38px; height:38px; font-size:16px;">
      <i class="fas fa-arrow-down"></i>
    </div>
    <div class="stat-content">
      <div class="stat-label">Barang Masuk</div>
      <div class="stat-value" style="font-size:20px;" data-counter><?= $totalStockIn ?></div>
    </div>
  </div>

  <div class="stat-card" style="--stat-color: #f97316; cursor: pointer;" onclick="window.location.href='<?= APP_URL ?>/admin-ab/modules/stock_out/index.php'">
    <div class="stat-icon" style="background: rgba(249,115,22,0.1); color: #f97316; width:38px; height:38px; font-size:16px;">
      <i class="fas fa-arrow-up"></i>
    </div>
    <div class="stat-content">
      <div class="stat-label">Barang Keluar</div>
      <div class="stat-value" style="font-size:20px;" data-counter><?= $totalStockOut ?></div>
    </div>
  </div>

  <div class="stat-card" style="--stat-color: var(--warning); cursor: pointer;" onclick="window.location.href='<?= APP_URL ?>/admin-ab/modules/orders/index.php?status=pending'">
    <div class="stat-icon" style="background: var(--warning-bg); color: var(--warning); width:38px; height:38px; font-size:16px;">
      <i class="fas fa-clock"></i>
    </div>
    <div class="stat-content">
      <div class="stat-label">Menunggu</div>
      <div class="stat-value" style="font-size:20px;" data-counter><?= $pendingOrders ?></div>
    </div>
  </div>

  <div class="stat-card" style="--stat-color: var(--info); cursor: pointer;" onclick="window.location.href='<?= APP_URL ?>/admin-ab/modules/orders/index.php?status=processing'">
    <div class="stat-icon" style="background: var(--info-bg); color: var(--info); width:38px; height:38px; font-size:16px;">
      <i class="fas fa-spinner"></i>
    </div>
    <div class="stat-content">
      <div class="stat-label">Diproses</div>
      <div class="stat-value" style="font-size:20px;" data-counter><?= $processingOrders ?></div>
    </div>
  </div>
</div>

<!-- ── Charts Row ─────────────────────────────────────────── -->
<div class="grid" style="grid-template-columns: 2fr 1fr; gap: 20px; margin-bottom: 20px;">
  <!-- Monthly Chart -->
  <div class="card">
    <div class="card-header">
      <div>
        <div class="card-title">Grafik Bulanan</div>
        <div class="card-subtitle">Barang masuk & keluar + Pendapatan (Juta Rp)</div>
      </div>
      <select id="chart-type" class="form-control" style="width:130px; font-size:12px; padding:6px 10px;">
        <option value="monthly">Bulanan</option>
        <option value="weekly">Mingguan</option>
      </select>
    </div>
    <div class="card-body">
      <div class="chart-container" style="height: 260px;">
        <canvas id="mainChart"></canvas>
      </div>
    </div>
  </div>

  <!-- Order Status Pie -->
  <div class="card">
    <div class="card-header">
      <div>
        <div class="card-title">Status Pesanan</div>
        <div class="card-subtitle">Distribusi status</div>
      </div>
    </div>
    <div class="card-body">
      <div class="chart-container" style="height: 200px;">
        <canvas id="orderChart"></canvas>
      </div>
      <?php
      $osPending    = (int)Database::fetchColumn("SELECT COUNT(*) FROM orders WHERE status='pending'");
      $osProcessing = (int)Database::fetchColumn("SELECT COUNT(*) FROM orders WHERE status='processing'");
      $osCompleted  = (int)Database::fetchColumn("SELECT COUNT(*) FROM orders WHERE status='completed'");
      $osRejected   = (int)Database::fetchColumn("SELECT COUNT(*) FROM orders WHERE status='rejected'");
      ?>
      <div style="display:flex; flex-direction:column; gap:8px; margin-top:16px;">
        <?php foreach([
          ['Menunggu',  $osPending,    '#f59e0b'],
          ['Diproses',  $osProcessing, '#3b82f6'],
          ['Selesai',   $osCompleted,  '#22c55e'],
          ['Ditolak',   $osRejected,   '#ef4444'],
        ] as [$lbl, $cnt, $clr]): ?>
        <div style="display:flex; align-items:center; gap:8px; font-size:12.5px;">
          <span style="width:10px;height:10px;border-radius:50%;background:<?= $clr ?>;flex-shrink:0;"></span>
          <span style="flex:1;color:var(--text-secondary);"><?= $lbl ?></span>
          <strong><?= $cnt ?></strong>
        </div>
        <?php endforeach; ?>
      </div>
    </div>
  </div>
</div>

<!-- ── Bottom Row ─────────────────────────────────────────── -->
<div class="grid" style="grid-template-columns: 1fr 1fr; gap: 20px; margin-bottom: 20px;">
  <!-- Recent Orders -->
  <div class="card">
    <div class="card-header">
      <div>
        <div class="card-title">Pesanan Terbaru</div>
        <div class="card-subtitle">5 pesanan terakhir</div>
      </div>
      <a href="<?= APP_URL ?>/admin-ab/modules/orders/index.php" class="btn btn-ghost btn-sm">
        Lihat Semua <i class="fas fa-arrow-right"></i>
      </a>
    </div>
    <div class="card-body" style="padding: 0;">
      <?php if (empty($recentOrders)): ?>
        <div class="empty-state" style="padding:40px;">
          <div class="empty-state-icon"><i class="fas fa-inbox"></i></div>
          <div class="empty-state-title">Belum ada pesanan</div>
        </div>
      <?php else: ?>
        <div class="table-responsive">
          <table class="table">
            <thead><tr><th>Nomor</th><th>Pelanggan</th><th>Total</th><th>Status</th></tr></thead>
            <tbody>
              <?php foreach ($recentOrders as $ord): ?>
              <tr>
                <td><a href="<?= APP_URL ?>/admin-ab/modules/orders/view.php?id=<?= $ord['id'] ?>" class="text-mono" style="font-size:12px;"><?= $ord['order_number'] ?></a></td>
                <td class="truncate" style="max-width:120px;"><?= htmlspecialchars($ord['customer_name']) ?></td>
                <td style="font-size:12.5px;font-weight:600;"><?= formatRupiah($ord['total_amount']) ?></td>
                <td><?= orderStatusLabel($ord['status']) ?></td>
              </tr>
              <?php endforeach; ?>
            </tbody>
          </table>
        </div>
      <?php endif; ?>
    </div>
  </div>

  <!-- Top Materials -->
  <div class="card">
    <div class="card-header">
      <div>
        <div class="card-title">Material Terlaris</div>
        <div class="card-subtitle">Berdasarkan total barang keluar</div>
      </div>
    </div>
    <div class="card-body">
      <?php if (empty($topMaterials)): ?>
        <div class="empty-state" style="padding:20px;">
          <div class="empty-state-title">Belum ada data</div>
        </div>
      <?php else: ?>
        <?php
        $maxOut = max(array_column($topMaterials, 'total_out'));
        if ($maxOut == 0) $maxOut = 1;
        foreach ($topMaterials as $i => $mat):
          $pct = ($mat['total_out'] / $maxOut) * 100;
        ?>
        <div style="margin-bottom: 16px;">
          <div style="display:flex; justify-content:space-between; align-items:baseline; margin-bottom:5px;">
            <span style="font-size:13px; font-weight:600;"><?= htmlspecialchars($mat['name']) ?></span>
            <span style="font-size:12px; color:var(--text-muted);"><?= formatNumber($mat['total_out']) ?> <?= $mat['unit'] ?></span>
          </div>
          <div class="stock-bar">
            <div class="stock-bar-fill" style="width:<?= $pct ?>%; background: <?= ['#3b82f6','#22c55e','#f59e0b','#ef4444','#8b5cf6'][$i] ?>;"></div>
          </div>
        </div>
        <?php endforeach; ?>
      <?php endif; ?>
    </div>
  </div>
</div>

<!-- ── Bottom Row 2 (Omzet) ───────────────────────────────── -->
<div class="grid" style="grid-template-columns: 1fr 1fr; gap: 20px; margin-bottom: 20px;">
  <!-- Top Products by Revenue -->
  <div class="card">
    <div class="card-header">
      <div>
        <div class="card-title">Produk Terlaris (Omzet)</div>
        <div class="card-subtitle">Berdasarkan total penjualan</div>
      </div>
    </div>
    <div class="card-body">
      <?php if (empty($topRevenueMaterials)): ?>
        <div class="empty-state" style="padding:20px;">
          <div class="empty-state-title">Belum ada data</div>
        </div>
      <?php else: ?>
        <?php
        $maxRev = max(array_column($topRevenueMaterials, 'total_revenue'));
        if ($maxRev == 0) $maxRev = 1;
        foreach ($topRevenueMaterials as $i => $mat):
          $pct = ($mat['total_revenue'] / $maxRev) * 100;
        ?>
        <div style="margin-bottom: 16px;">
          <div style="display:flex; justify-content:space-between; margin-bottom:5px;">
            <span style="font-size:13px; font-weight:600;"><?= htmlspecialchars($mat['name']) ?></span>
            <span style="font-size:12px; color:var(--text-muted);"><?= formatRupiah($mat['total_revenue']) ?></span>
          </div>
          <div class="stock-bar">
            <div class="stock-bar-fill" style="width:<?= $pct ?>%; background: <?= ['#8b5cf6','#3b82f6','#22c55e','#f59e0b','#ef4444'][$i] ?>;"></div>
          </div>
        </div>
        <?php endforeach; ?>
      <?php endif; ?>
    </div>
  </div>

  <!-- Low Stock Alerts -->
  <div class="card">
    <div class="card-header">
      <div>
        <div class="card-title" style="color: var(--warning);">
          <i class="fas fa-exclamation-triangle"></i> Notifikasi Stok
        </div>
        <div class="card-subtitle">Material yang perlu diperhatikan</div>
      </div>
      <a href="<?= APP_URL ?>/admin-ab/modules/material/index.php" class="btn btn-ghost btn-sm">
        Kelola <i class="fas fa-arrow-right"></i>
      </a>
    </div>
    <div class="card-body" style="padding: 0;">
      <?php if (empty($lowStockMaterials)): ?>
        <div class="empty-state" style="padding:40px;">
          <div class="empty-state-icon" style="color: var(--success);"><i class="fas fa-check-circle"></i></div>
          <div class="empty-state-title">Semua stok normal</div>
          <div class="empty-state-desc">Tidak ada material yang perlu diperhatikan saat ini.</div>
        </div>
      <?php else: ?>
        <div class="table-responsive">
          <table class="table">
            <thead><tr><th>Material</th><th>Stok</th><th>Min</th><th>Status</th></tr></thead>
            <tbody>
              <?php foreach ($lowStockMaterials as $mat): ?>
              <tr>
                <td style="font-weight:600;"><?= htmlspecialchars($mat['name']) ?></td>
                <td style="color:<?= $mat['stock_status']=='out_of_stock'?'var(--danger)':'var(--warning)' ?>"><?= formatNumber($mat['current_stock']) ?> <?= $mat['unit'] ?></td>
                <td><?= $mat['min_stock'] ?></td>
                <td><?= stockStatusLabel($mat['stock_status']) ?></td>
              </tr>
              <?php endforeach; ?>
            </tbody>
          </table>
        </div>
      <?php endif; ?>
    </div>
  </div>
</div>

<!-- ── Activity Log Row ───────────────────────────────────── -->


</div><!-- /.page-body -->
</div><!-- /.main-content -->
</div><!-- /.admin-wrapper -->

<!-- Chart.js -->
<script src="https://cdnjs.cloudflare.com/ajax/libs/Chart.js/4.4.1/chart.umd.min.js"></script>
<script src="<?= ASSETS_URL ?>js/main.js"></script>

<script>
const monthlyData = {
  labels: <?= json_encode($chartMonths) ?>,
  in: <?= json_encode($chartIn) ?>,
  out: <?= json_encode($chartOut) ?>,
  revenue: <?= json_encode($chartRevenue) ?>,
};

const weeklyData = {
  labels: <?= json_encode($weekDays) ?>,
  in: <?= json_encode($weekIn) ?>,
  out: <?= json_encode($weekOut) ?>,
  revenue: [],
};

function getChartColors() {
  const isDark = document.documentElement.getAttribute('data-theme') === 'dark';
  return {
    grid: isDark ? 'rgba(255,255,255,0.06)' : 'rgba(0,0,0,0.06)',
    text: isDark ? '#6b7280' : '#9ca3af',
  };
}

let mainChart;
function buildMainChart(type = 'monthly') {
  const ctx = document.getElementById('mainChart').getContext('2d');
  const data = type === 'monthly' ? monthlyData : weeklyData;
  const cc = getChartColors();

  if (mainChart) mainChart.destroy();

  mainChart = new Chart(ctx, {
    type: 'bar',
    data: {
      labels: data.labels,
      datasets: [
        {
          label: 'Barang Masuk',
          data: data.in,
          backgroundColor: 'rgba(59,130,246,0.7)',
          borderColor: '#3b82f6',
          borderWidth: 0,
          borderRadius: 5,
          yAxisID: 'y',
        },
        {
          label: 'Barang Keluar',
          data: data.out,
          backgroundColor: 'rgba(249,115,22,0.7)',
          borderColor: '#f97316',
          borderWidth: 0,
          borderRadius: 5,
          yAxisID: 'y',
        },
        {
          label: 'Pendapatan (Juta Rp)',
          data: data.revenue,
          type: 'line',
          borderColor: '#8b5cf6',
          backgroundColor: '#8b5cf6',
          borderWidth: 3,          // <-- WAJIB > 0 supaya garis muncul
          pointRadius: 4,
          pointBackgroundColor: '#8b5cf6',
          tension: 0.3,
          fill: false,
          yAxisID: 'y1',           // <-- sumbu terpisah
        }
      ],
    },
    options: {
      responsive: true,
      maintainAspectRatio: false,
      plugins: {
        legend: { labels: { color: cc.text, font: { size: 12 } } },
        tooltip: { mode: 'index', intersect: false },
      },
      scales: {
        x: {
          ticks: { color: cc.text },
          grid: { color: cc.grid }
        },
        y: {
          type: 'linear',
          position: 'left',
          ticks: { color: cc.text },
          grid: { color: cc.grid },
          beginAtZero: true,
          title: { display: true, text: 'Jumlah Barang', color: cc.text }
        },
        y1: {
          type: 'linear',
          position: 'right',
          ticks: { color: cc.text },
          grid: { drawOnChartArea: false }, // biar grid tidak dobel
          beginAtZero: true,
          suggestedMax: Math.max(...data.revenue) * 1.5 + 1 || 10,
          title: { display: true, text: 'Pendapatan (Juta Rp)', color: cc.text }
        }
      },
    },
  });
}

// Order pie chart
new Chart(document.getElementById('orderChart'), {
  type: 'doughnut',
  data: {
    labels: ['Menunggu', 'Diproses', 'Selesai', 'Ditolak'],
    datasets: [{
      data: [<?= $osPending ?>, <?= $osProcessing ?>, <?= $osCompleted ?>, <?= $osRejected ?>],
      backgroundColor: ['#f59e0b', '#3b82f6', '#22c55e', '#ef4444'],
      borderWidth: 0,
    }],
  },
  options: {
    responsive: true,
    maintainAspectRatio: false,
    plugins: { legend: { display: false }, tooltip: { callbacks: { label: ctx => ` ${ctx.label}: ${ctx.raw}` } } },
    cutout: '70%',
  },
});

document.getElementById('chart-type').addEventListener('change', function() { buildMainChart(this.value); });
buildMainChart('monthly');
document.getElementById('theme-toggle')?.addEventListener('click', () => { setTimeout(() => buildMainChart(document.getElementById('chart-type').value), 100); });
</script>
</body>
</html>