<?php
require_once __DIR__.'/../../../config/database.php';
require_once __DIR__.'/../../../config/app.php';
initSession(); requireLogin();
$id=getInt('id'); $token=get('token');
if (!hash_equals(csrfToken(),$token)){setFlash('error','Token tidak valid.');redirect(APP_URL.'/admin/modules/stock_out/index.php');}
$row=Database::fetchOne("SELECT * FROM stock_out WHERE id=?",[$id]);
if (!$row){setFlash('error','Data tidak ditemukan.');redirect(APP_URL.'/admin/modules/stock_out/index.php');}
Database::delete('stock_out','id=?',[$id]);
logActivity('delete','stock_out',"Menghapus data barang keluar ID:{$id}");
setFlash('success','Data barang keluar berhasil dihapus.');
redirect(APP_URL.'/admin/modules/stock_out/index.php');
