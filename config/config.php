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
if ($http_host === 'localhost' || $http_host === '127.0.0.1') {
    // Lokal XAMPP
    define('BASE_URL', 'http://localhost/sucikomalaukk2');
}
else {
    // Vercel
    $protocol = (isset($_SERVER['HTTP_X_FORWARDED_PROTO']) && $_SERVER['HTTP_X_FORWARDED_PROTO'] === 'https') ? "https" : (isset($_SERVER['HTTPS']) && $_SERVER['HTTPS'] === 'on' ? "https" : "http");
    define('BASE_URL', $protocol . '://' . $http_host);
}

// Start Session
if (session_status() === PHP_SESSION_NONE) {
    session_start();
}

// Redirection logic for login/dashboard (Centralized)
$current_page = basename($_SERVER['PHP_SELF']);
$public_pages = ['index.php', 'fix_admin.php'];

// Jika pengguna BELUM LOGIN dan mencoba mengakses halaman privat
if (!isset($_SESSION['user_id']) && !in_array($current_page, $public_pages)) {
    header('Location: ' . BASE_URL . '/index.php');
    exit;
}

// Jika pengguna SUDAH LOGIN dan mencoba ke login page
if (isset($_SESSION['user_id']) && $current_page === 'index.php') {
    header('Location: ' . BASE_URL . '/dashboard.php');
    exit;
}

// Redirect if already logged in (Gunakan relative path)
// Jika pengguna sudah login dan mencoba mengakses index.php, redirect ke dashboard.php
if (isset($_SESSION['user_id']) && basename($_SERVER['PHP_SELF']) === 'index.php') {
    header('Location: ./dashboard.php');
    exit;
}
?>
