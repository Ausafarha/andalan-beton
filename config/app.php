<?php
// ============================================================
// config/app.php
// Konfigurasi Aplikasi Global
// ============================================================

define('APP_NAME', 'PT Andalan Beton');
define('APP_VERSION', '1.0.0');
define('APP_URL', (isset($_SERVER['HTTPS']) && $_SERVER['HTTPS'] === 'on' ? 'https' : 'http') . '://' . ($_SERVER['HTTP_HOST'] ?? 'localhost:8000'));
define('APP_ROOT', dirname(__DIR__));
define('UPLOAD_PATH', APP_ROOT . '/uploads/');
define('UPLOAD_URL', APP_URL . '/uploads/');
define('ASSETS_URL', APP_URL . '/assets/');

// Session configuration
define('SESSION_NAME', 'andalan_beton_sess');
define('SESSION_LIFETIME', 7200); // 2 jam

// Pagination
define('PER_PAGE', 15);

// Upload constraints
define('MAX_FILE_SIZE', 5 * 1024 * 1024); // 5MB
define('ALLOWED_IMAGE_TYPES', ['image/jpeg', 'image/jpg', 'image/png', 'image/webp']);
define('ALLOWED_IMAGE_EXTS', ['jpg', 'jpeg', 'png', 'webp']);

// ============================================================
// Session Bootstrap
// ============================================================
function initSession(): void {
    if (session_status() === PHP_SESSION_NONE) {
        session_name(SESSION_NAME);
        session_set_cookie_params([
            'lifetime' => SESSION_LIFETIME,
            'path'     => '/',
            'secure'   => false, // set true in production with HTTPS
            'httponly' => true,
            'samesite' => 'Lax',
        ]);
        session_start();
    }
}

// ============================================================
// CSRF Token
// ============================================================
function csrfToken(): string {
    if (empty($_SESSION['csrf_token'])) {
        $_SESSION['csrf_token'] = bin2hex(random_bytes(32));
    }
    return $_SESSION['csrf_token'];
}

function csrfField(): string {
    return '<input type="hidden" name="csrf_token" value="' . htmlspecialchars(csrfToken()) . '">';
}

function verifyCsrf(): bool {
    $token = $_POST['csrf_token'] ?? $_SERVER['HTTP_X_CSRF_TOKEN'] ?? '';
    return hash_equals($_SESSION['csrf_token'] ?? '', $token);
}

// ============================================================
// Auth Helpers
// ============================================================
function isLoggedIn(): bool {
    return !empty($_SESSION['admin_id']) && !empty($_SESSION['admin_role']);
}

function requireLogin(): void {
    if (!isLoggedIn()) {
        header('Location: ' . APP_URL . '/admin-ab/login.php');
        exit;
    }
    
    // Cek aktivitas terakhir (30 menit timeout)
    $inactive = 1800;
    if (isset($_SESSION['last_activity']) && (time() - $_SESSION['last_activity'] > $inactive)) {
        session_unset();
        session_destroy();
        header('Location: ' . APP_URL . '/admin-ab/login.php?msg=session_expired');
        exit;
    }
    $_SESSION['last_activity'] = time();
}

function currentUser(): array {
    return [
        'id'       => $_SESSION['admin_id'] ?? null,
        'name'     => $_SESSION['admin_name'] ?? '',
        'username' => $_SESSION['admin_username'] ?? '',
        'role'     => $_SESSION['admin_role'] ?? '',
    ];
}

// ============================================================
// Input Sanitization
// ============================================================
function sanitize(mixed $val): string {
    if (is_array($val)) return '';
    return htmlspecialchars(trim((string)$val), ENT_QUOTES, 'UTF-8');
}

function sanitizeInt(mixed $val, int $default = 0): int {
    return filter_var($val, FILTER_VALIDATE_INT) !== false ? (int)$val : $default;
}

function sanitizeFloat(mixed $val, float $default = 0.0): float {
    return filter_var($val, FILTER_VALIDATE_FLOAT) !== false ? (float)$val : $default;
}

function post(string $key, mixed $default = ''): string {
    return sanitize($_POST[$key] ?? $default);
}

function get(string $key, mixed $default = ''): string {
    return sanitize($_GET[$key] ?? $default);
}

function postInt(string $key, int $default = 0): int {
    return sanitizeInt($_POST[$key] ?? $default);
}

function getInt(string $key, int $default = 0): int {
    return sanitizeInt($_GET[$key] ?? $default);
}

// ============================================================
// Response Helpers
// ============================================================
function jsonResponse(array $data, int $code = 200): void {
    http_response_code($code);
    header('Content-Type: application/json');
    echo json_encode($data);
    exit;
}

function redirect(string $url): void {
    header('Location: ' . $url);
    exit;
}

function setFlash(string $type, string $message): void {
    $_SESSION['flash'] = ['type' => $type, 'message' => $message];
}

function getFlash(): array|null {
    if (!empty($_SESSION['flash'])) {
        $flash = $_SESSION['flash'];
        unset($_SESSION['flash']);
        return $flash;
    }
    return null;
}

// ============================================================
// Format Helpers
// ============================================================
function formatRupiah(float|int $amount): string {
    return 'Rp ' . number_format($amount, 0, ',', '.');
}

function formatNumber(float|int $num): string {
    return number_format($num, 0, ',', '.');
}

function formatDate(string|null $date, string $format = 'd M Y'): string {
    if (!$date) return '-';
    return date($format, strtotime($date));
}

function formatDateTime(string|null $dt): string {
    if (!$dt) return '-';
    return date('d M Y H:i', strtotime($dt));
}

function timeAgo(string $datetime): string {
    $now  = new DateTime();
    $ago  = new DateTime($datetime);
    $diff = $now->diff($ago);
    if ($diff->d === 0 && $diff->h === 0 && $diff->i < 1) return 'Baru saja';
    if ($diff->d === 0 && $diff->h === 0) return $diff->i . ' menit lalu';
    if ($diff->d === 0) return $diff->h . ' jam lalu';
    if ($diff->d < 7) return $diff->d . ' hari lalu';
    return formatDate($datetime);
}

function orderStatusLabel(string $status): string {
    return match($status) {
        'pending'    => '<span class="badge badge-warning">Menunggu</span>',
        'processing' => '<span class="badge badge-info">Diproses</span>',
        'completed'  => '<span class="badge badge-success">Selesai</span>',
        'rejected'   => '<span class="badge badge-danger">Ditolak</span>',
        default      => '<span class="badge badge-secondary">' . $status . '</span>',
    };
}

function stockStatusLabel(string $status): string {
    return match($status) {
        'available'    => '<span class="badge badge-success">Tersedia</span>',
        'low_stock'    => '<span class="badge badge-warning">Stok Menipis</span>',
        'out_of_stock' => '<span class="badge badge-danger">Habis</span>',
        default        => '<span class="badge">-</span>',
    };
}

// ============================================================
// File Upload Helper
// ============================================================
function uploadImage(array $file, string $folder = 'products'): array {
    if ($file['error'] !== UPLOAD_ERR_OK) {
        return ['success' => false, 'message' => 'Upload gagal. Error code: ' . $file['error']];
    }
    if ($file['size'] > MAX_FILE_SIZE) {
        return ['success' => false, 'message' => 'Ukuran file maksimal 5MB.'];
    }
    $finfo = finfo_open(FILEINFO_MIME_TYPE);
    $mime  = finfo_file($finfo, $file['tmp_name']);
    //finfo_close($finfo);
    if (!in_array($mime, ALLOWED_IMAGE_TYPES)) {
        return ['success' => false, 'message' => 'Format file harus JPG, PNG, atau WebP.'];
    }
    $ext      = pathinfo($file['name'], PATHINFO_EXTENSION);
    $filename = uniqid('img_', true) . '.' . strtolower($ext);
    $dir      = UPLOAD_PATH . $folder . '/';
    if (!is_dir($dir)) mkdir($dir, 0755, true);
    $dest = $dir . $filename;
    if (!move_uploaded_file($file['tmp_name'], $dest)) {
        return ['success' => false, 'message' => 'Gagal menyimpan file.'];
    }
    return ['success' => true, 'filename' => $folder . '/' . $filename];
}

function deleteFile(string $path): void {
    $full = UPLOAD_PATH . $path;
    if (file_exists($full)) @unlink($full);
}

function assetUrl(string $path): string {
    return ASSETS_URL . $path;
}

function uploadUrl(string $path = ''): string {
    // Kalau udah URL lengkap (Cloudinary), langsung return
    if (str_starts_with($path, 'http://') || str_starts_with($path, 'https://')) {
        return $path;
    }
    return $path ? UPLOAD_URL . $path : UPLOAD_URL;
}

// ============================================================
// Activity Logger
// ============================================================
function logActivity(string $action, string $module, string $description): void {
    try {
        $user = currentUser();
        Database::query(
            "INSERT INTO activity_logs (user_id, action, module, description, ip_address) VALUES (?, ?, ?, ?, ?)",
            [$user['id'], $action, $module, $description, $_SERVER['REMOTE_ADDR'] ?? '127.0.0.1']
        );
    } catch (Exception $e) {
        error_log('Activity log error: ' . $e->getMessage());
    }
}

// ============================================================
// Pagination Helper
// ============================================================
function paginate(string $sql, array $params, int $page, int $perPage = PER_PAGE): array {
    $countSql = "SELECT COUNT(*) FROM ({$sql}) AS subq";
    $total    = (int)Database::fetchColumn($countSql, $params);
    $pages    = (int)ceil($total / $perPage);
    $offset   = ($page - 1) * $perPage;
    $items    = Database::fetchAll($sql . " LIMIT {$perPage} OFFSET {$offset}", $params);
    return [
        'items'   => $items,
        'total'   => $total,
        'page'    => $page,
        'pages'   => $pages,
        'perPage' => $perPage,
    ];
}

// ============================================================
// Generate Order Number
// ============================================================
function generateOrderNumber(): string {
    $prefix = 'ORD-' . date('Y') . '-';
    $last   = Database::fetchColumn(
        "SELECT order_number FROM orders WHERE order_number LIKE ? ORDER BY id DESC LIMIT 1",
        [$prefix . '%']
    );
    if ($last) {
        $num = (int)substr($last, strrpos($last, '-') + 1) + 1;
    } else {
        $num = 1;
    }
    return $prefix . str_pad($num, 4, '0', STR_PAD_LEFT);
}


function booleanPost(string $key): bool {
    return isset($_POST[$key]) && $_POST[$key] == 1;
}
// ============================================================
// Company Profile Helper
// ============================================================
function getCompanyProfile(): array {
    static $profile = null;
    if ($profile === null) {
        $profile = Database::fetchOne("SELECT * FROM company_profile LIMIT 1") ?: [];
    }
    return $profile;
}
