<?php
// Configuration for Database
// Menggunakan Environment Variables untuk Vercel (Aiven), fallback ke localhost

// Deteksi apakah berjalan di Vercel (produksi) atau lokal
$is_vercel = getenv('VERCEL') || getenv('VERCEL_ENV');

// Gunakan 127.0.0.1 untuk lokal (menghindari socket Unix yang menyebabkan error "No such file or directory")
$host = getenv('DB_HOST') ?: ($is_vercel ? 'localhost' : '127.0.0.1');
// Aiven menggunakan 'defaultdb' sebagai nama database default
// Lokal XAMPP menggunakan 'inventory_ukk'
$db = getenv('DB_NAME') ?: ($is_vercel ? 'defaultdb' : 'inventory_ukk');
$user = getenv('DB_USER') ?: 'root';
$pass = getenv('DB_PASS') ?: '';
$port = getenv('DB_PORT') ?: '3306';
$charset = 'utf8mb4';

$dsn = "mysql:host=$host;port=$port;dbname=$db;charset=$charset";
$options = [
    PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION,
    PDO::ATTR_DEFAULT_FETCH_MODE => PDO::FETCH_ASSOC,
    PDO::ATTR_EMULATE_PREPARES => false,
];

// SSL options untuk Aiven (hanya aktif jika di Vercel/produksi)
if ($is_vercel && getenv('DB_HOST')) {
    $options[PDO::MYSQL_ATTR_SSL_VERIFY_SERVER_CERT] = false;
}

try {
    $pdo = new PDO($dsn, $user, $pass, $options);
}
catch (\PDOException $e) {
    // Berikan pesan error yang lebih informatif
    if ($is_vercel && !getenv('DB_HOST')) {
        throw new \PDOException(
            'Database environment variables (DB_HOST, DB_NAME, DB_USER, DB_PASS) belum dikonfigurasi di Vercel. ' .
            'Silakan tambahkan di Settings > Environment Variables pada dashboard Vercel Anda.',
            (int)$e->getCode()
            );
    }
    throw new \PDOException($e->getMessage(), (int)$e->getCode());
}

// Site Configuration
define('SITE_NAME', 'Sistem Peminjaman Alat');

// BASE_URL Dinamis untuk Localhost & Vercel
$http_host = isset($_SERVER['HTTP_HOST']) ? $_SERVER['HTTP_HOST'] : 'localhost';

if ($is_vercel) {
    define('BASE_URL', 'https://' . $http_host);

    // Setting Session Wajib untuk Cloud
    session_name('UKK_USER_SESSION');
    ini_set('session.use_cookies', '1');
    ini_set('session.use_only_cookies', '1');
    ini_set('session.cookie_httponly', '1');
    ini_set('session.cookie_secure', '1');
    ini_set('session.cookie_samesite', 'Lax');

    if (is_writable('/tmp')) {
        session_save_path('/tmp');
    }
}
else {
    // Lokal XAMPP
    define('BASE_URL', 'http://' . $http_host . '/sucikomalaukk2');
}

// Start Session
if (session_status() === PHP_SESSION_NONE) {
    session_start();
}

// Fungsi Redirect yang lebih stabil di Cloud (Header + JS Fallback)
function safe_redirect($target)
{
    if (!headers_sent()) {
        header("Location: $target");
    }
    echo "<script>window.location.href='$target';</script>";
    exit;
}

// ============================================================
// LOGIKA REDIRECT ANTI-LOOP (High Compatibility)
// ============================================================
$current_page = strtolower(basename($_SERVER['SCRIPT_NAME']));
$public_pages = ['index.php', 'fix_admin.php'];
$is_logged_in = isset($_SESSION['user_id']) && !empty($_SESSION['user_id']);

// 1. PROTEKSI: Jika BELUM LOGIN dan mencoba ke halaman privat
if (!$is_logged_in && !in_array($current_page, $public_pages)) {
    if ($current_page !== 'index.php') {
        safe_redirect('index.php');
    }
}

// 2. AUTO-DASHBOARD: Jika SUDAH LOGIN dan berada di halaman login (index.php)
if ($is_logged_in && $current_page === 'index.php') {
    safe_redirect('dashboard.php');
}
?>
