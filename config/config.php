<?php
// ============================================================
// KONFIGURASI DATABASE
// ============================================================

// Deteksi Environment
$is_vercel = getenv('VERCEL') || (isset($_SERVER['HTTP_HOST']) && $_SERVER['HTTP_HOST'] !== 'localhost' && $_SERVER['HTTP_HOST'] !== '127.0.0.1');

// Database Settings
$host = getenv('DB_HOST') ?: ($is_vercel ? 'localhost' : '127.0.0.1');
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

if ($is_vercel && getenv('DB_HOST')) {
    $options[PDO::MYSQL_ATTR_SSL_VERIFY_SERVER_CERT] = false;
}

try {
    $pdo = new PDO($dsn, $user, $pass, $options);
}
catch (\PDOException $e) {
    die("Koneksi Database Gagal: " . $e->getMessage());
}

// ============================================================
// DATABASE-BASED SESSION HANDLER (Solusi untuk Vercel/Cloud)
// ============================================================
class DatabaseSessionHandler implements SessionHandlerInterface
{
    private $pdo;

    public function __construct($pdo)
    {
        $this->pdo = $pdo;
    }

    public function open($savePath, $sessionName): bool
    {
        return true;
    }
    public function close(): bool
    {
        return true;
    }

    public function read($id): string|false
    {
        $stmt = $this->pdo->prepare("SELECT data FROM sessions WHERE id = ?");
        $stmt->execute([$id]);
        $row = $stmt->fetch();
        return $row ? (string)$row['data'] : '';
    }

    public function write($id, $data): bool
    {
        $stmt = $this->pdo->prepare("REPLACE INTO sessions (id, data, last_accessed) VALUES (?, ?, CURRENT_TIMESTAMP)");
        return $stmt->execute([$id, $data]);
    }

    public function destroy($id): bool
    {
        $stmt = $this->pdo->prepare("DELETE FROM sessions WHERE id = ?");
        return $stmt->execute([$id]);
    }

    public function gc($maxlifetime): int|false
    {
        $stmt = $this->pdo->prepare("DELETE FROM sessions WHERE last_accessed < DATE_SUB(NOW(), INTERVAL ? SECOND)");
        $stmt->execute([$maxlifetime]);
        return true;
    }
}

// Gunakan Database Session di Vercel
if ($is_vercel) {
    $handler = new DatabaseSessionHandler($pdo);
    session_set_save_handler($handler, true);

    // Cookie Config
    ini_set('session.cookie_httponly', '1');
    ini_set('session.cookie_secure', '1');
    ini_set('session.cookie_samesite', 'Lax');
}

if (session_status() === PHP_SESSION_NONE) {
    session_start();
}

// ============================================================
// SITE CONFIG & REDIRECT
// ============================================================
define('SITE_NAME', 'Sistem Peminjaman Alat');

$http_host = isset($_SERVER['HTTP_HOST']) ? $_SERVER['HTTP_HOST'] : 'localhost';
if ($is_vercel) {
    define('BASE_URL', 'https://' . $http_host);
}
else {
    define('BASE_URL', 'http://' . $http_host . '/sucikomalaukk2');
}

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

// Proteksi Halaman Privat
if (!$is_logged_in && !in_array($current_page, $public_pages)) {
    safe_redirect('index.php');
}

// Redirect otomatis ke dashboard jika sudah login (DIAKTIFKAN LAGI)
if ($is_logged_in && $current_page === 'index.php') {
    safe_redirect('dashboard.php');
}
?>
