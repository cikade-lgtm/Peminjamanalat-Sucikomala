<?php
// Configuration for Database
// Menggunakan Environment Variables untuk Vercel (Aiven), fallback ke localhost
$host = getenv('DB_HOST') ?: 'localhost';
$db = getenv('DB_NAME') ?: 'inventory_ukk';
$user = getenv('DB_USER') ?: 'root';
$pass = getenv('DB_PASS') ?: '';
$port = getenv('DB_PORT') ?: '3306';
$charset = 'utf8mb4';

// Aiven mewajibkan koneksi SSL, kita tambahkan dsn parameter jika menggunakan port selain 3306 (opsional)
$dsn = "mysql:host=$host;port=$port;dbname=$db;charset=$charset";
$options = [
    PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION,
    PDO::ATTR_DEFAULT_FETCH_MODE => PDO::FETCH_ASSOC,
    PDO::ATTR_EMULATE_PREPARES => false,
];

try {
    $pdo = new PDO($dsn, $user, $pass, $options);
}
catch (\PDOException $e) {
    throw new \PDOException($e->getMessage(), (int)$e->getCode());
}

// Site Configuration
define('SITE_NAME', 'Sistem Peminjaman Alat');

// BASE_URL Dinamis untuk Localhost & Vercel
$http_host = isset($_SERVER['HTTP_HOST']) ? $_SERVER['HTTP_HOST'] : 'localhost';
if ($http_host === 'localhost' || $http_host === '127.0.0.1') {
    define('BASE_URL', 'http://localhost/sucikomalaukk2/');
}
else {
    // Vercel selalu menggunakan HTTPS
    $protocol = isset($_SERVER['HTTP_X_FORWARDED_PROTO']) ? $_SERVER['HTTP_X_FORWARDED_PROTO'] : (isset($_SERVER['HTTPS']) && $_SERVER['HTTPS'] === 'on' ? "https" : "http");
    define('BASE_URL', $protocol . '://' . $http_host . '/');
}

// Start Session
if (session_status() === PHP_SESSION_NONE) {
    session_start();
}
?>
