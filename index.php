<?php
require_once 'config/config.php';



$error = '';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $username = $_POST['username'] ?? '';
    $password = $_POST['password'] ?? '';

    if (!empty($username) && !empty($password)) {
        // In UKK Context, often passwords are md5 or bcrypt. 
        // The seed data uses bcrypt-like hash.
        $stmt = $pdo->prepare("SELECT u.*, r.nama_role FROM users u JOIN roles r ON u.id_role = r.id_role WHERE u.username = ?");
        $stmt->execute([$username]);
        $user = $stmt->fetch();

        // Support both Bcrypt (preferred) and MD5 (legacy/template)
        $is_valid = false;
        if ($user) {
            if (password_verify($password, $user['password'])) {
                $is_valid = true;
            }
            elseif (md5($password) === $user['password']) {
                $is_valid = true;
            }
        }

        if ($is_valid) {
            $_SESSION['user_id'] = $user['id_user'];
            $_SESSION['username'] = $user['username'];
            $_SESSION['nama_lengkap'] = $user['nama_lengkap'];
            $_SESSION['role_id'] = $user['id_role'];
            $_SESSION['role_name'] = $user['nama_role'];

            // Log activity
            $logStmt = $pdo->prepare("INSERT INTO log_aktivitas (id_user, aktivitas) VALUES (?, ?)");
            $logStmt->execute([$user['id_user'], 'User logged in']);

            header('Location: dashboard.php');
            exit;
        }
        else {
            $error = 'Username atau password salah!';
        }
    }
    else {
        $error = 'Semua field harus diisi!';
    }
}
?>
<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Login - <?php echo SITE_NAME; ?></title>
    <!-- Bootstrap 5 CSS -->
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <!-- Google Fonts -->
    <link href="https://fonts.googleapis.com/css2?family=Outfit:wght@300;400;600;700&display=swap" rel="stylesheet">
    <!-- Font Awesome -->
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
    <link rel="stylesheet" href="<?php echo BASE_URL; ?>/assets/css/style.css">
    <style>
        :root {
            --primary-gradient: linear-gradient(135deg, #4facfe 0%, #00f2fe 100%);
            --glass-bg: rgba(255, 255, 255, 0.9);
        }
        
        body {
            font-family: 'Outfit', sans-serif;
            background: var(--primary-gradient);
            min-height: 100vh;
            display: flex;
            align-items: center;
            justify-content: center;
            padding: 20px;
        }

        .login-card {
            background: var(--glass-bg);
            backdrop-filter: blur(10px);
            border-radius: 20px;
            box-shadow: 0 15px 35px rgba(0,0,0,0.1);
            width: 100%;
            max-width: 400px;
            overflow: hidden;
            border: 1px solid rgba(255,255,255,0.2);
        }

        .login-header {
            padding: 40px 30px 20px;
            text-align: center;
        }

        .login-header i {
            font-size: 3rem;
            background: var(--primary-gradient);
            -webkit-background-clip: text;
            -webkit-text-fill-color: transparent;
            margin-bottom: 20px;
        }

        .login-body {
            padding: 20px 30px 40px;
        }

        .form-control {
            border-radius: 10px;
            padding: 12px 15px;
            border: 1px solid #e1e1e1;
            margin-bottom: 15px;
        }

        .form-control:focus {
            box-shadow: 0 0 0 0.25rem rgba(79, 172, 254, 0.25);
            border-color: #4facfe;
        }

        .btn-login {
            background: var(--primary-gradient);
            border: none;
            border-radius: 10px;
            padding: 12px;
            font-weight: 600;
            color: white;
            width: 100%;
            margin-top: 10px;
            transition: transform 0.2s;
        }

        .btn-login:hover {
            transform: translateY(-2px);
            color: white;
            opacity: 0.9;
        }

        .alert {
            border-radius: 10px;
            font-size: 0.9rem;
        }
    </style>
</head>
<body>

<div class="login-card shadow-lg">
    <div class="login-header">
        <i class="fas fa-toolbox"></i>
        <h3 class="fw-bold"><?php echo SITE_NAME; ?></h3>
        <p class="text-muted small">Silakan masuk ke akun Anda</p>
    </div>
    <div class="login-body">
        <?php if ($error): ?>
            <div class="alert alert-danger border-0 shadow-sm">
                <i class="fas fa-exclamation-circle me-2"></i> <?php echo $error; ?>
            </div>
        <?php
endif; ?>

        <form method="POST" action="">
            <div class="mb-3">
                <label class="form-label small fw-semibold">Username</label>
                <div class="input-group">
                    <span class="input-group-text bg-light border-end-0"><i class="fas fa-user text-muted"></i></span>
                    <input type="text" name="username" class="form-control border-start-0 ps-0" placeholder="Username" required>
                </div>
            </div>
            <div class="mb-3">
                <label class="form-label small fw-semibold">Password</label>
                <div class="input-group">
                    <span class="input-group-text bg-light border-end-0"><i class="fas fa-lock text-muted"></i></span>
                    <input type="password" name="password" class="form-control border-start-0 ps-0" placeholder="Password" required>
                </div>
            </div>
            <button type="submit" class="btn btn-login shadow">
                Login <i class="fas fa-sign-in-alt ms-2"></i>
            </button>
        </form>
        <div class="mt-4 text-center">
            <p class="small text-muted mb-0">&copy; <?php echo date('Y'); ?> UKK Inventaris</p>
        </div>
    </div>
</div>

<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
</body>
</html>
