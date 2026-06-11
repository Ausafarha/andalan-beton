<?php
require_once __DIR__.'/../../../config/database.php';
require_once __DIR__.'/../../../config/app.php';
initSession(); requireLogin();
$id = getInt('id'); $token = get('token');
if (!hash_equals(csrfToken(),$token)) { setFlash('error','Token tidak valid.'); redirect(APP_URL.'/admin/modules/stock_in/index.php'); }
$row = Database::fetchOne("SELECT * FROM stock_in WHERE id=?",[$id]);
if (!$row) { setFlash('error','Data tidak ditemukan.'); redirect(APP_URL.'/admin/modules/stock_in/index.php'); }
Database::delete('stock_in','id=?',[$id]);
logActivity('delete','stock_in',"Menghapus data barang masuk ID:{$id}");
setFlash('success','Data barang masuk berhasil dihapus.');
redirect(APP_URL.'/admin/modules/stock_in/index.php');
