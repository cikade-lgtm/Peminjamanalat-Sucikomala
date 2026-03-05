<?php
require_once '../config/config.php';
$active_page = 'log';
$title = 'Log Aktivitas';

if (!isset($_SESSION['user_id']) || ($_SESSION['role_id'] != 1 && $_SESSION['role_id'] != 2)) {
    header('Location: ../dashboard.php');
    exit;
}

$logs = $pdo->query("SELECT l.*, u.username, u.nama_lengkap FROM log_aktivitas l JOIN users u ON l.id_user = u.id_user ORDER BY l.waktu DESC")->fetchAll();

include '../includes/header.php';
?>

<div class="mb-4">
    <h2 class="fw-bold mb-0">Log Aktivitas Sistem</h2>
    <p class="text-muted">Riwayat aktivitas pengguna dalam sistem.</p>
</div>

<div class="card border-0 shadow-sm rounded-4">
    <div class="card-body p-0">
        <div class="table-responsive">
            <table class="table table-hover align-middle mb-0">
                <thead class="bg-light">
                    <tr>
                        <th class="ps-4">No</th>
                        <th>Waktu</th>
                        <th>User</th>
                        <th>Aktivitas</th>
                    </tr>
                </thead>
                <tbody>
                    <?php if (empty($logs)): ?>
                        <tr><td colspan="4" class="text-center py-4 text-muted">Belum ada log aktivitas.</td></tr>
                    <?php
else:
    $no = 1;
    foreach ($logs as $l):
?>
                        <tr>
                            <td class="ps-4"><?php echo $no++; ?></td>
                            <td><small class="text-muted fw-bold"><?php echo date('d M Y, H:i:s', strtotime($l['waktu'])); ?></small></td>
                            <td>
                                <div class="fw-bold"><?php echo $l['nama_lengkap']; ?></div>
                                <small class="text-muted">@<?php echo $l['username']; ?></small>
                            </td>
                            <td><?php echo $l['aktivitas']; ?></td>
                        </tr>
                    <?php
    endforeach;
endif; ?>
                </tbody>
            </table>
        </div>
    </div>
</div>

</div> 
</div> 
</div> 
<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
<script>
    document.getElementById('sidebarCollapse').addEventListener('click', function () {
        document.getElementById('sidebar').classList.toggle('active');
        document.getElementById('content').classList.toggle('active');
    });
</script>
</body>
</html>
