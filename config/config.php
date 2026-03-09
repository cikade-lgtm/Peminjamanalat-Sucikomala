<?php
// ============================================================
// KONFIGURASI DATABASE
// ============================================================

// Deteksi Environment
$is_vercel = getenv('VERCEL') || (isset($_SERVER['HTTP_HOST']) && $_SERVER['HTTP_HOST'] !== 'localhost' && $_SERVER['HTTP_HOST'] !== '127.0.0.1');

// Database Settings
$host = getenv('DB_HOST') ?: ($is_vercel ? 'localhost' : '127.0.0.1');
$db = getenv('DB_NAME') ?: 'inventory_ukk';

$user = getenv('DB_USER') ?: 'root';
$pass = getenv('DB_PASS') ?: '';
$port = getenv('DB_PORT') ?: '3306';
$charset = 'utf8mb4';

// Aiven biasanya DB_NAME adalah 'defaultdb' jika di cloud
if ($is_vercel && !getenv('DB_NAME')) {
    $db = 'defaultdb';
}

$dsn = "mysql:host=$host;port=$port;dbname=$db;charset=$charset";
$options = [
    PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION,
    PDO::ATTR_DEFAULT_FETCH_MODE => PDO::FETCH_ASSOC,
    PDO::ATTR_EMULATE_PREPARES => false,
];

// SSL untuk Aiven
if ($is_vercel && getenv('DB_HOST')) {
    $options[PDO::MYSQL_ATTR_SSL_VERIFY_SERVER_CERT] = false;
}

try {
    $pdo = new PDO($dsn, $user, $pass, $options);
}
catch (\PDOException $e) {
    if ($is_vercel && !getenv('DB_HOST')) {
        die("Database belum dikonfigurasi di Vercel. Tambahkan Environment Variables di dashboard.");
    }
    die("Koneksi Database Gagal: " . $e->getMessage());
}

// ============================================================
// KONFIGURASI SITE & URL
// ============================================================
define('SITE_NAME', 'Sistem Peminjaman Alat');

$http_host = isset($_SERVER['HTTP_HOST']) ? $_SERVER['HTTP_HOST'] : 'localhost';
if ($is_vercel) {
    define('BASE_URL', 'https://' . $http_host);

    // Konfigurasi Session untuk Vercel (Cloud)
    ini_set('session.cookie_httponly', '1');
    ini_set('session.cookie_secure', '1');
    ini_set('session.cookie_samesite', 'Lax');
    // Folder /tmp adalah satu-satunya yang bisa ditulisi di Vercel
    if (is_writable('/tmp')) {
        session_save_path('/tmp');
    }
}
else {
    define('BASE_URL', 'http://' . $http_host . '/sucikomalaukk2');
}

if (session_status() === PHP_SESSION_NONE) {
    session_start();
}

// ============================================================
// LOGIKA REDIRECT (ANTI-LOOP)
// ============================================================
function safe_redirect($path)
{
    $url = BASE_URL . '/' . ltrim($path, '/');
    if (!headers_sent()) {
        header("Location: $url");
    }
    echo "<script>window.location.href='$url';</script>";
    exit;
}

$current_page = strtolower(basename($_SERVER['SCRIPT_NAME']));
$public_pages = ['index.php', 'fix_admin.php'];
$is_logged_in = !empty($_SESSION['user_id']);

// 1. Proteksi Halaman Privat
if (!$is_logged_in && !in_array($current_page, $public_pages)) {
    safe_redirect('index.php');
}

// 2. Redirect jika sudah login (DIKOMENTARI dulu untuk memutus loop jika session flaky)
/*
if ($is_logged_in && $current_page === 'index.php') {
    safe_redirect('dashboard.php');
}
*/
?>
