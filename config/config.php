<?php
// Configuration for Database
$host = 'localhost';
$db   = 'inventory_ukk';
$user = 'root';
$pass = '';
$charset = 'utf8mb4';

$dsn = "mysql:host=$host;dbname=$db;charset=$charset";
$options = [
    PDO::ATTR_ERRMODE            => PDO::ERRMODE_EXCEPTION,
    PDO::ATTR_DEFAULT_FETCH_MODE => PDO::FETCH_ASSOC,
    PDO::ATTR_EMULATE_PREPARES   => false,
];

try {
     $pdo = new PDO($dsn, $user, $pass, $options);
} catch (\PDOException $e) {
     throw new \PDOException($e->getMessage(), (int)$e->getCode());
}

// Site Configuration
define('SITE_NAME', 'Sistem Peminjaman Alat');
define('BASE_URL', 'http://localhost/sucikomalaukk2/');

// Start Session
if (session_status() === PHP_SESSION_NONE) {
    session_start();
}
?>
