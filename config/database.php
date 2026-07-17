<?php
session_start();
ob_start();

// ==========================================
// تنظیمات دیتابیس - موقع آپلود تغییر بده
// ==========================================
define('DB_HOST', 'localhost');
define('DB_NAME', 'shop_db');
define('DB_USER', 'root');
define('DB_PASS', '');
define('DB_CHARSET', 'utf8mb4');

// اتصال به دیتابیس
try {
    $pdo = new PDO(
        "mysql:host=" . DB_HOST . ";dbname=" . DB_NAME . ";charset=" . DB_CHARSET,
        DB_USER,
        DB_PASS,
        [
            PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION,
            PDO::ATTR_DEFAULT_FETCH_MODE => PDO::FETCH_ASSOC,
            PDO::ATTR_EMULATE_PREPARES => false,
            PDO::MYSQL_ATTR_INIT_COMMAND => "SET NAMES utf8mb4"
        ]
    );
} catch(PDOException $e) {
    die("خطا در اتصال به دیتابیس");
}

// تنظیمات منطقه زمانی
date_default_timezone_set('Asia/Tehran');

// ==========================================
// توابع پایه
// ==========================================

function getSetting($key, $default = '') {
    global $pdo;
    try {
        $stmt = $pdo->prepare("SELECT setting_value FROM settings WHERE setting_key = ? LIMIT 1");
        $stmt->execute([$key]);
        $result = $stmt->fetch();
        return $result ? $result['setting_value'] : $default;
    } catch(Exception $e) {
        return $default;
    }
}

function getAllSettings() {
    global $pdo;
    $stmt = $pdo->query("SELECT setting_key, setting_value, setting_group FROM settings");
    $settings = [];
    while($row = $stmt->fetch()) {
        $settings[$row['setting_key']] = $row['setting_value'];
    }
    return $settings;
}

function updateSetting($key, $value) {
    global $pdo;
    $stmt = $pdo->prepare("INSERT INTO settings (setting_key, setting_value) VALUES (?, ?) ON DUPLICATE KEY UPDATE setting_value = ?");
    return $stmt->execute([$key, $value, $value]);
}

function isLoggedIn() {
    return isset($_SESSION['user_id']);
}

function isAdmin() {
    return isset($_SESSION['user_role']) && $_SESSION['user_role'] === 'admin';
}

function requireLogin() {
    if (!isLoggedIn()) {
        $_SESSION['redirect_after_login'] = $_SERVER['REQUEST_URI'];
        header('Location: /login');
        exit;
    }
}

function requireAdmin() {
    if (!isAdmin()) {
        header('Location: /');
        exit;
    }
}

function redirect($url) {
    header("Location: $url");
    exit;
}

function sanitize($data) {
    return htmlspecialchars(trim($data), ENT_QUOTES, 'UTF-8');
}

function generateSlug($string) {
    $string = preg_replace('/[^a-zA-Z0-9\x{0600}-\x{06FF}\s-]/u', '', $string);
    $string = trim($string);
    $string = preg_replace('/[\s-]+/', '-', $string);
    return mb_strtolower($string);
}

function formatPrice($price) {
    return number_format($price) . ' ' . getSetting('currency', 'تومان');
}

function getDiscountedPrice($price, $discountPercent) {
    if ($discountPercent > 0) {
        return $price - ($price * $discountPercent / 100);
    }
    return $price;
}

function uploadImage($file, $folder = 'products') {
    $uploadDir = __DIR__ . '/../uploads/' . $folder . '/';
    if (!file_exists($uploadDir)) {
        mkdir($uploadDir, 0755, true);
    }
    
    $ext = strtolower(pathinfo($file['name'], PATHINFO_EXTENSION));
    $allowed = ['jpg', 'jpeg', 'png', 'webp', 'gif'];
    
    if (!in_array($ext, $allowed)) {
        return ['success' => false, 'message' => 'فرمت فایل مجاز نیست'];
    }
    
    if ($file['size'] > 5000000) {
        return ['success' => false, 'message' => 'حجم فایل نباید بیشتر از ۵ مگابایت باشد'];
    }
    
    $newName = uniqid() . '_' . time() . '.' . $ext;
    $destination = $uploadDir . $newName;
    
    if (move_uploaded_file($file['tmp_name'], $destination)) {
        return ['success' => true, 'path' => '/uploads/' . $folder . '/' . $newName];
    }
    
    return ['success' => false, 'message' => 'خطا در آپلود فایل'];
}

function deleteImage($path) {
    $fullPath = __DIR__ . '/..' . $path;
    if (file_exists($fullPath)) {
        unlink($fullPath);
    }
}

function getCartCount() {
    if (isLoggedIn()) {
        global $pdo;
        $stmt = $pdo->prepare("SELECT SUM(quantity) as total FROM cart_items WHERE user_id = ?");
        $stmt->execute([$_SESSION['user_id']]);
        $result = $stmt->fetch();
        return $result['total'] ?? 0;
    }
    return 0;
}

function getCartTotal() {
    if (isLoggedIn()) {
        global $pdo;
        $stmt = $pdo->prepare("
            SELECT SUM((p.price - (p.price * p.discount_percent / 100)) * ci.quantity) as total 
            FROM cart_items ci 
            JOIN products p ON ci.product_id = p.id 
            WHERE ci.user_id = ?
        ");
        $stmt->execute([$_SESSION['user_id']]);
        $result = $stmt->fetch();
        return $result['total'] ?? 0;
    }
    return 0;
}

function getWishlistCount() {
    if (isLoggedIn()) {
        global $pdo;
        $stmt = $pdo->prepare("SELECT COUNT(*) as total FROM wishlist WHERE user_id = ?");
        $stmt->execute([$_SESSION['user_id']]);
        $result = $stmt->fetch();
        return $result['total'] ?? 0;
    }
    return 0;
}

function generateOrderNumber() {
    return 'ORD-' . date('Ymd') . '-' . strtoupper(substr(uniqid(), -6));
}

function logAdminAction($action, $description = '') {
    global $pdo;
    if (isAdmin()) {
        $stmt = $pdo->prepare("INSERT INTO admin_logs (user_id, action, description, ip_address) VALUES (?, ?, ?, ?)");
        $stmt->execute([$_SESSION['user_id'], $action, $description, $_SERVER['REMOTE_ADDR']]);
    }
}

// دریافت تنظیمات
$settings = getAllSettings();

// ثابت‌ها
define('SITE_NAME', $settings['site_name'] ?? 'فروشگاه من');
define('SITE_URL', (isset($_SERVER['HTTPS']) && $_SERVER['HTTPS'] === 'on' ? "https" : "http") . "://$_SERVER[HTTP_HOST]");
define('UPLOAD_DIR', __DIR__ . '/../uploads/');
define('UPLOAD_URL', SITE_URL . '/uploads/');
define('ASSETS_URL', SITE_URL . '/assets/');
define('CART_COUNT', isLoggedIn() ? getCartCount() : 0);
define('WISHLIST_COUNT', isLoggedIn() ? getWishlistCount() : 0);
?>