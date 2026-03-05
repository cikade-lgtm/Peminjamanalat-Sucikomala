<?php
require_once 'config/config.php';

echo "<h2>🔧 Sistem Repair & Diagnostic V2</h2>";

try {
    // 1. Diagnostics
    echo "<h3>1. Diagnostik Database...</h3>";
    echo "PDO Connection: OK<br>";
    echo "Database: " . $db . "<br>";

    // 2. Roles
    $roles = $pdo->query("SELECT * FROM roles")->fetchAll();
    echo "Roles found: " . count($roles) . "<br>";
    if (count($roles) == 0) {
        $pdo->exec("INSERT INTO roles (nama_role) VALUES ('Admin'), ('Petugas'), ('Peminjam')");
        echo "Default roles inserted.<br>";
    }

    // 3. User Admin Check & Reset
    $username = 'admin';
    $password_plain = 'admin123';
    $password_bcrypt = password_hash($password_plain, PASSWORD_DEFAULT);
    $password_md5 = md5($password_plain);

    echo "<h3>2. Resetting User 'admin'...</h3>";
    $stmt = $pdo->prepare("SELECT * FROM users WHERE username = ?");
    $stmt->execute([$username]);
    $user = $stmt->fetch();

    $roleId = $pdo->query("SELECT id_role FROM roles WHERE nama_role = 'Admin' LIMIT 1")->fetchColumn();
    if (!$roleId)
        $roleId = 1;

    if (!$user) {
        echo "User 'admin' tidak ada. Membuat baru...<br>";
        $stmt = $pdo->prepare("INSERT INTO users (username, password, nama_lengkap, id_role) VALUES (?, ?, 'Super Admin', ?)");
        $stmt->execute([$username, $password_bcrypt, $roleId]);
    }
    else {
        echo "User 'admin' ditemukan. Memperbarui password ke Bcrypt...<br>";
        $stmt = $pdo->prepare("UPDATE users SET password = ?, id_role = ? WHERE username = ?");
        $stmt->execute([$password_bcrypt, $roleId, $username]);
    }

    // 4. Listing and Verification
    echo "<h3>3. Verifikasi & Daftar User:</h3>";
    $users = $pdo->query("SELECT u.*, r.nama_role FROM users u LEFT JOIN roles r ON u.id_role = r.id_role")->fetchAll();

    echo "<table border='1' cellpadding='8' style='border-collapse: collapse; font-family: sans-serif;'>
            <tr style='background: #f0f0f0;'>
                <th>Username</th>
                <th>Role</th>
                <th>Hash Hint</th>
                <th>Test Password (admin123)</th>
            </tr>";

    foreach ($users as $u) {
        $is_bcrypt = password_verify($password_plain, $u['password']);
        $is_md5 = (md5($password_plain) === $u['password']);

        $status = "<span style='color:red;'>FAIL</span>";
        if ($is_bcrypt)
            $status = "<span style='color:green;'>Bcrypt OK</span>";
        elseif ($is_md5)
            $status = "<span style='color:orange;'>MD5 OK</span>";

        echo "<tr>
                <td><strong>{$u['username']}</strong></td>
                <td>{$u['nama_role']}</td>
                <td style='font-size: 0.8em; color: gray;'>" . substr($u['password'], 0, 10) . "...</td>
                <td>$status</td>
              </tr>";
    }
    echo "</table>";

    echo "<br><div style='background: #e1f5fe; padding: 20px; border-radius: 10px; border: 1px solid #01579b;'>
            <h4 style='margin-top:0;'>✅ SELESAI</h4>
            Coba login kembali dengan kredensial berikut:<br>
            Username: <code>admin</code><br>
            Password: <code>admin123</code><br>
            <br>
            <a href='index.php' style='padding: 10px 20px; background: #0288d1; color: white; text-decoration: none; border-radius: 5px; font-weight: bold;'>Buka Halaman Login</a>
          </div>";

}
catch (Exception $e) {
    echo "<div style='background: #ffebee; color: #c62828; padding: 20px; border-radius: 10px; border: 1px solid #c62828;'>
            <strong>FATAL ERROR:</strong> " . $e->getMessage() . "
          </div>";
}
?>
