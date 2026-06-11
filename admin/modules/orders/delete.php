<?php
require_once __DIR__.'/../../../config/database.php';
require_once __DIR__.'/../../../config/app.php';
initSession(); requireLogin();
$id=getInt('id'); $token=get('token');
if (!hash_equals(csrfToken(),$token)){setFlash('error','Token tidak valid.');redirect(APP_URL.'/admin/modules/orders/index.php');}
$ord=Database::fetchOne("SELECT * FROM orders WHERE id=?",[$id]);
if (!$ord){setFlash('error','Pesanan tidak ditemukan.');redirect(APP_URL.'/admin/modules/orders/index.php');}
Database::delete('order_items','order_id=?',[$id]);
Database::delete('orders','id=?',[$id]);
logActivity('delete','orders',"Menghapus pesanan {$ord['order_number']}");
setFlash('success','Pesanan berhasil dihapus.');
redirect(APP_URL.'/admin/modules/orders/index.php');
