<?php
require_once 'config/config.php';
$active_page = 'dashboard';
$title = 'Dashboard';

// Check if user is logged in
if (!isset($_SESSION['user_id'])) {
    header('Location: index.php');
    exit;
}

// Fetch stats
$totalAlat = $pdo->query("SELECT COUNT(*) FROM alat")->fetchColumn();
$totalPinjam = $pdo->query("SELECT COUNT(*) FROM peminjaman WHERE status = 'pending'")->fetchColumn();
$totalDipinjam = $pdo->query("SELECT COUNT(*) FROM peminjaman WHERE status = 'approved'")->fetchColumn();
$totalUsers = $pdo->query("SELECT COUNT(*) FROM users")->fetchColumn();

include 'includes/header.php';
?>

<div class="row mb-4">
    <div class="col-12">
        <div class="welcome-card card border-0 shadow-sm">
            <div class="card-body p-4">
                <div class="row align-items-center">
                    <div class="col-md-8">
                        <h2 class="fw-bold">Selamat Datang, <?php echo $_SESSION['nama_lengkap']; ?>!</h2>
                        <p class="text-muted mb-0">Anda masuk sebagai <strong><?php echo $_SESSION['role_name']; ?></strong>. Pantau dan kelola peminjaman alat dengan mudah di sini.</p>
                    </div>
                    <div class="col-md-4 text-center d-none d-md-block">
                        <img src="https://cdni.iconscout.com/illustration/premium/thumb/dashboard-analysis-2706316-2252119.png" alt="Welcome" class="img-fluid" style="max-height: 150px;">
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>

<div class="row">
    <div class="col-md-3 mb-4">
        <div class="stat-card">
            <i class="fas fa-tools"></i>
            <div>
                <h5>Total Alat</h5>
                <h3><?php echo $totalAlat; ?></h3>
            </div>
        </div>
    </div>
    <div class="col-md-3 mb-4">
        <div class="stat-card">
            <i class="fas fa-clock text-warning" style="background: rgba(255, 193, 7, 0.1);"></i>
            <div>
                <h5>Pending</h5>
                <h3><?php echo $totalPinjam; ?></h3>
            </div>
        </div>
    </div>
    <div class="col-md-3 mb-4">
        <div class="stat-card">
            <i class="fas fa-hand-holding-heart text-success" style="background: rgba(40, 167, 69, 0.1);"></i>
            <div>
                <h5>Aktif</h5>
                <h3><?php echo $totalDipinjam; ?></h3>
            </div>
        </div>
    </div>
    <div class="col-md-3 mb-4">
        <div class="stat-card">
            <i class="fas fa-users text-info" style="background: rgba(23, 162, 184, 0.1);"></i>
            <div>
                <h5>Users</h5>
                <h3><?php echo $totalUsers; ?></h3>
            </div>
        </div>
    </div>
</div>

<div class="row mt-2">
    <div class="col-md-8">
        <div class="card border-0 shadow-sm rounded-4">
            <div class="card-header bg-white border-0 py-3">
                <h5 class="mb-0 fw-bold">Peminjaman Terbaru</h5>
            </div>
            <div class="card-body">
                <div class="table-responsive">
                    <table class="table table-hover align-middle">
                        <thead class="bg-light">
                            <tr>
                                <th>Peminjam</th>
                                <th>Tgl Pinjam</th>
                                <th>Status</th>
                                <th>Aksi</th>
                            </tr>
                        </thead>
                        <tbody>
                            <?php
$stmt = $pdo->query("SELECT p.*, u.nama_lengkap FROM peminjaman p JOIN users u ON p.id_user = u.id_user ORDER BY tgl_pinjam DESC LIMIT 5");
while ($row = $stmt->fetch()):
    $statusClass = [
        'pending' => 'bg-warning',
        'approved' => 'bg-success',
        'rejected' => 'bg-danger',
        'returned' => 'bg-info'
    ][$row['status']];
?>
                            <tr>
                                <td><?php echo $row['nama_lengkap']; ?></td>
                                <td><?php echo date('d M Y', strtotime($row['tgl_pinjam'])); ?></td>
                                <td><span class="badge <?php echo $statusClass; ?> rounded-pill"><?php echo ucfirst($row['status']); ?></span></td>
                                <td><a href="pages/peminjaman.php" class="btn btn-sm btn-light border">Detail</a></td>
                            </tr>
                            <?php
endwhile; ?>
                        </tbody>
                    </table>
                </div>
            </div>
        </div>
    </div>
    <div class="col-md-4">
        <div class="card border-0 shadow-sm rounded-4">
            <div class="card-header bg-white border-0 py-3">
                <h5 class="mb-0 fw-bold">Log Aktivitas</h5>
            </div>
            <div class="card-body p-0">
                <ul class="list-group list-group-flush">
                    <?php
$stmt = $pdo->query("SELECT l.*, u.username FROM log_aktivitas l JOIN users u ON l.id_user = u.id_user ORDER BY waktu DESC LIMIT 5");
while ($row = $stmt->fetch()):
?>
                    <li class="list-group-item py-3 px-4 border-0">
                        <div class="d-flex justify-content-between">
                            <h6 class="mb-1 fw-bold"><?php echo $row['username']; ?></h6>
                            <small class="text-muted"><?php echo date('H:i', strtotime($row['waktu'])); ?></small>
                        </div>
                        <p class="small text-muted mb-0"><?php echo $row['aktivitas']; ?></p>
                    </li>
                    <?php
endwhile; ?>
                </ul>
            </div>
        </div>
    </div>
</div>

<?php

// Close content div and include scripts/footer
?>
</div> <!-- .main-content -->
</div> <!-- #content -->
</div> <!-- .wrapper -->

<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
<script>
    document.getElementById('sidebarCollapse').addEventListener('click', function () {
        document.getElementById('sidebar').classList.toggle('active');
        document.getElementById('content').classList.toggle('active');
    });
</script>
</body>
</html>
