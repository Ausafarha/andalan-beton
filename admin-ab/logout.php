<?php
require_once __DIR__ . '/../config/database.php';
require_once __DIR__ . '/../config/app.php';
initSession();

// Cegah caching
header("Cache-Control: no-store, no-cache, must-revalidate, max-age=0");
header("Cache-Control: post-check=0, pre-check=0", false);
header("Pragma: no-cache");
header("Expires: Thu, 01 Jan 1970 00:00:00 GMT");

if (isLoggedIn()) {
    $user = currentUser();
    try {
        Database::query(
            "INSERT INTO activity_logs (user_id, action, module, description, ip_address) VALUES (?,?,?,?,?)",
            [$user['id'], 'logout', 'auth', 'User ' . $user['username'] . ' logout', $_SERVER['REMOTE_ADDR'] ?? '']
        );
    } catch(Exception $e) {}
}

$_SESSION = [];
if (ini_get('session.use_cookies')) {
    $p = session_get_cookie_params();
    setcookie(session_name(), '', time() - 42000, $p['path'], $p['domain'], $p['secure'], $p['httponly']);
}
session_destroy();

header('Location: ' . APP_URL . '/admin-ab/login.php');
exit;
