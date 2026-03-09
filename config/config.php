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
    // Vercel selalu HTTPS, paksa agar tidak terjadi loop protocol
    define('BASE_URL', 'https://' . $http_host);

    // Optimasi Session untuk Vercel (Serverless)
    if (is_writable('/tmp')) {
        session_save_path('/tmp');
    }
    // Pastikan cookie terbaca di HTTPS dan tetap aman
    session_set_cookie_params([
        'lifetime' => 86400,
        'path' => '/',
        'domain' => $http_host,
        'secure' => true,
        'httponly' => true,
        'samesite' => 'Lax'
    ]);
}
else {
    // Lokal XAMPP
    define('BASE_URL', 'http://' . $http_host . '/sucikomalaukk2');
}

// Start Session
if (session_status() === PHP_SESSION_NONE) {
    session_start();
}

// ============================================================
// LOGIKA REDIRECT TERPUSAT (Anti-Loop)
// ============================================================
$current_script = strtolower(basename($_SERVER['SCRIPT_NAME']));
$public_pages = ['index.php', 'fix_admin.php'];

// 1. Jika pengguna BELUM LOGIN dan mencoba mengakses halaman privat (dashboard, dll.)
if (!isset($_SESSION['user_id']) && !in_array($current_script, $public_pages)) {
    header('Location: ' . BASE_URL . '/index.php');
    exit;
}

// 2. Jika pengguna SUDAH LOGIN dan berada di halaman login (index.php)
if (isset($_SESSION['user_id']) && $current_script === 'index.php') {
    header('Location: ' . BASE_URL . '/dashboard.php');
    exit;
}
?>
