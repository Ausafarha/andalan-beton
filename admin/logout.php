<?php
require_once __DIR__ . '/../config/database.php';
require_once __DIR__ . '/../config/app.php';
initSession();

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

header('Location: ' . APP_URL . '/admin/login.php');
exit;
