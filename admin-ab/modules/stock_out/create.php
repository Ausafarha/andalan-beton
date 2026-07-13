<?php
require_once __DIR__.'/../../../config/database.php';
require_once __DIR__.'/../../../config/app.php';
initSession(); requireLogin();
$pageTitle='Catat Barang Keluar'; $errors=[];
$user=currentUser();
$materials = Database::fetchAll("SELECT id,name,unit FROM materials WHERE type = 'product' AND is_active=true ORDER BY name");
$data=['material_id'=>'','quantity'=>'','destination'=>'','driver_name'=>'','vehicle_number'=>'','notes'=>'','out_date'=>date('Y-m-d')];
if ($_SERVER['REQUEST_METHOD']==='POST') {
    if (!verifyCsrf()){$errors[]='Token tidak valid.';}
    else {
        $data=['material_id'=>postInt('material_id'),'quantity'=>postInt('quantity'),'destination'=>post('destination')?:null,'driver_name'=>post('driver_name')?:null,'vehicle_number'=>post('vehicle_number')?:null,'notes'=>post('notes')?:null,'out_date'=>post('out_date')?:date('Y-m-d'),'processed_by'=>$user['id']];
        if (!$data['material_id']) $errors[]='Pilih material.';
        if ($data['quantity']<=0)  $errors[]='Jumlah harus lebih dari 0.';
        // Check stock availability
        if ($data['material_id'] && $data['quantity']>0) {
            $stock=(int)Database::fetchColumn("SELECT current_stock FROM material_stock WHERE id=?",[$data['material_id']]);
            if ($data['quantity']>$stock) $errors[]="Stok tidak mencukupi. Stok tersedia: {$stock}";
        }
        if (empty($errors)) {
            Database::insert('stock_out',$data);
            $matName=Database::fetchColumn("SELECT name FROM materials WHERE id=?",[$data['material_id']]);
            logActivity('create','stock_out',"Mencatat barang keluar: {$matName} sebanyak {$data['quantity']}");
            setFlash('success','Data barang keluar berhasil dicatat.');
            redirect(APP_URL.'/admin-ab/modules/stock_out/index.php');
        }
    }
}
include __DIR__.'/../../partials/head.php';
?>
<div class="admin-wrapper">
<?php include __DIR__.'/../../partials/sidebar.php'; ?>
<div class="main-content">
<?php include __DIR__.'/../../partials/topbar.php'; ?>
<div class="page-body">
<div class="mb-20"><a href="<?=APP_URL?>/admin-ab/modules/stock_out/index.php" class="btn btn-ghost btn-sm" style="margin-bottom:8px;"><i class="fas fa-arrow-left"></i> Kembali</a><h2 style="font-size:20px;font-weight:800;">Catat Barang Keluar</h2></div>
<?php foreach($errors as $e):?><div class="alert alert-danger"><i class="fas fa-exclamation-circle"></i> <?=htmlspecialchars($e)?></div><?php endforeach;?>
<div class="card" style="max-width:700px;">
  <div class="card-header"><div class="card-title">Form Distribusi Material</div></div>
  <div class="card-body">
    <form method="POST">
      <?=csrfField()?>
      <div class="form-group">
        <label class="form-label">Material <span>*</span></label>
        <select name="material_id" class="form-control" required id="mat-out">
          <option value="">-- Pilih Material --</option>
          <?php foreach($materials as $m):?>
          <option value="<?=$m['id']?>" data-unit="<?=htmlspecialchars($m['unit'])?>" <?=$data['material_id']==$m['id']?'selected':''?>><?=htmlspecialchars($m['name'])?> (<?=$m['unit']?>)</option>
          <?php endforeach;?>
        </select>
      </div>
      <div id="stock-info" style="display:none;padding:10px 14px;background:var(--info-bg);border-radius:8px;font-size:13px;margin-bottom:16px;border:1px solid var(--brand-200);">
        <i class="fas fa-info-circle" style="color:var(--brand-600);"></i> Stok tersedia: <strong id="stock-val">-</strong>
      </div>
      <div class="grid grid-2">
        <div class="form-group"><label class="form-label">Jumlah <span>*</span></label><input type="number" name="quantity" class="form-control" value="<?=$data['quantity']?>" min="1" required placeholder="0"></div>
        <div class="form-group"><label class="form-label">Tanggal Keluar <span>*</span></label><input type="date" name="out_date" class="form-control" value="<?=$data['out_date']?>" required></div>
      </div>
      <div class="form-group">
    <label class="form-label">Tujuan/Proyek</label>
    <input type="text" name="destination" class="form-control" value="<?=htmlspecialchars($data['destination']??'')?>" placeholder="Nama proyek atau tujuan pengiriman">
</div>
<div class="form-group">
    <label class="form-label">Nama Pelanggan (Opsional)</label>
    <input type="text" name="customer_name" class="form-control" value="<?=htmlspecialchars($data['customer_name']??'')?>" placeholder="Nama pelanggan penerima">
</div>
      <div class="grid grid-2">
        <div class="form-group"><label class="form-label">Nama Driver</label><input type="text" name="driver_name" class="form-control" value="<?=htmlspecialchars($data['driver_name']??'')?>"></div>
        <div class="form-group"><label class="form-label">Nomor Kendaraan</label><input type="text" name="vehicle_number" class="form-control" value="<?=htmlspecialchars($data['vehicle_number']??'')?>" placeholder="B 1234 CD" class="text-mono"></div>
      </div>
      <div class="form-group"><label class="form-label">Catatan</label><textarea name="notes" class="form-control" rows="3"><?=htmlspecialchars($data['notes']??'')?></textarea></div>
      <div style="display:flex;gap:10px;"><button type="submit" class="btn btn-primary"><i class="fas fa-save"></i> Simpan</button><a href="<?=APP_URL?>/admin-ab/modules/stock_out/index.php" class="btn btn-secondary">Batal</a></div>
    </form>
  </div>
</div>
</div>
<?php include __DIR__.'/../../partials/footer.php'; ?>
<script>
const stockData = <?= json_encode(array_column(Database::fetchAll("SELECT id, current_stock, unit FROM material_stock WHERE is_active=true"), null, 'id')) ?>;
document.getElementById('mat-out').addEventListener('change', function() {
  const id = this.value;
  const info = document.getElementById('stock-info');
  if (id && stockData[id] !== undefined) {
    document.getElementById('stock-val').textContent = Number(stockData[id].current_stock).toLocaleString('id-ID') + ' ' + stockData[id].unit;
    info.style.display = 'block';
  } else { info.style.display = 'none'; }
});
</script>
