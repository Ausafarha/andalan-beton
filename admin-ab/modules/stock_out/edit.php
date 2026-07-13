<?php
require_once __DIR__.'/../../../config/database.php';
require_once __DIR__.'/../../../config/app.php';
initSession(); requireLogin();
$id=getInt('id'); $row=Database::fetchOne("SELECT * FROM stock_out WHERE id=?",[$id]);
if (!$row){setFlash('error','Data tidak ditemukan.');redirect(APP_URL.'/admin-ab/modules/stock_out/index.php');}
$pageTitle='Edit Barang Keluar'; $errors=[];
$materials=Database::fetchAll("SELECT id,name,unit FROM materials WHERE is_active=true ORDER BY name");
if ($_SERVER['REQUEST_METHOD']==='POST'){
    if (!verifyCsrf()){$errors[]='Token tidak valid.';}
    else {
        $data=['material_id'=>postInt('material_id'),'quantity'=>postInt('quantity'),'destination'=>post('destination')?:null,'driver_name'=>post('driver_name')?:null,'vehicle_number'=>post('vehicle_number')?:null,'notes'=>post('notes')?:null,'out_date'=>post('out_date')?:date('Y-m-d')];
        if (!$data['material_id']) $errors[]='Pilih material.';
        if ($data['quantity']<=0) $errors[]='Jumlah harus > 0.';
        if (empty($errors)){
            Database::update('stock_out',$data,'id=?',[$id]);
            logActivity('update','stock_out',"Mengubah data barang keluar ID:{$id}");
            setFlash('success','Data berhasil diperbarui.');
            redirect(APP_URL.'/admin-ab/modules/stock_out/index.php');
        }
        $row=array_merge($row,$data);
    }
}
include __DIR__.'/../../partials/head.php';
?>
<div class="admin-wrapper">
<?php include __DIR__.'/../../partials/sidebar.php'; ?>
<div class="main-content">
<?php include __DIR__.'/../../partials/topbar.php'; ?>
<div class="page-body">
<div class="mb-20"><a href="<?=APP_URL?>/admin-ab/modules/stock_out/index.php" class="btn btn-ghost btn-sm" style="margin-bottom:8px;"><i class="fas fa-arrow-left"></i> Kembali</a><h2 style="font-size:20px;font-weight:800;">Edit Barang Keluar</h2></div>
<?php foreach($errors as $e):?><div class="alert alert-danger"><?=htmlspecialchars($e)?></div><?php endforeach;?>
<div class="card" style="max-width:700px;"><div class="card-body">
<form method="POST"><?=csrfField()?>
  <div class="form-group"><label class="form-label">Material <span>*</span></label>
    <select name="material_id" class="form-control" required><option value="">-- Pilih --</option>
    <?php foreach($materials as $m):?><option value="<?=$m['id']?>" <?=$row['material_id']==$m['id']?'selected':''?>><?=htmlspecialchars($m['name'])?></option><?php endforeach;?></select></div>
  <div class="grid grid-2">
    <div class="form-group"><label class="form-label">Jumlah <span>*</span></label><input type="number" name="quantity" class="form-control" value="<?=$row['quantity']?>" min="1" required></div>
    <div class="form-group"><label class="form-label">Tanggal <span>*</span></label><input type="date" name="out_date" class="form-control" value="<?=$row['out_date']?>" required></div>
  </div>
  <div class="form-group"><label class="form-label">Tujuan</label><input type="text" name="destination" class="form-control" value="<?=htmlspecialchars($row['destination']??'')?>"></div>
  <div class="grid grid-2">
    <div class="form-group"><label class="form-label">Driver</label><input type="text" name="driver_name" class="form-control" value="<?=htmlspecialchars($row['driver_name']??'')?>"></div>
    <div class="form-group"><label class="form-label">Kendaraan(PLAT)</label><input type="text" name="vehicle_number" class="form-control" value="<?=htmlspecialchars($row['vehicle_number']??'')?>"></div>
  </div>
  <div class="form-group"><label class="form-label">Catatan</label><textarea name="notes" class="form-control" rows="3"><?=htmlspecialchars($row['notes']??'')?></textarea></div>
  <div style="display:flex;gap:10px;"><button type="submit" class="btn btn-primary"><i class="fas fa-save"></i> Simpan</button><a href="<?=APP_URL?>/admin-ab/modules/stock_out/index.php" class="btn btn-secondary">Batal</a></div>
</form></div></div>
</div>
<?php include __DIR__.'/../../partials/footer.php'; ?>
