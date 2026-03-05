<?php
require_once '../config/config.php';
$active_page = 'pinjaman_saya';
$title = 'Pinjaman Saya';

if (!isset($_SESSION['user_id'])) {
    header('Location: ../index.php');
    exit;
}

$id_user = $_SESSION['user_id'];
$query = "SELECT p.*, a.nama_alat, dp.jumlah, pg.tgl_kembali
          FROM peminjaman p 
          JOIN detail_peminjaman dp ON p.id_peminjaman = dp.id_peminjaman
          JOIN alat a ON dp.id_alat = a.id_alat
          LEFT JOIN pengembalian pg ON p.id_peminjaman = pg.id_peminjaman
          WHERE p.id_user = ?
          ORDER BY p.tgl_pinjam DESC";

$stmt = $pdo->prepare($query);
$stmt->execute([$id_user]);
$my_borrows = $stmt->fetchAll();

include '../includes/header.php';
?>

<div class="mb-4">
    <h2 class="fw-bold mb-0">Riwayat Pinjaman Saya</h2>
    <p class="text-muted">Pantau status alat yang Anda pinjam di sini.</p>
</div>

<div class="card border-0 shadow-sm rounded-4">
    <div class="card-body p-0">
        <div class="table-responsive">
            <table class="table table-hover align-middle mb-0">
                <thead class="bg-light">
                    <tr>
                        <th class="ps-4">Alat</th>
                        <th>Tgl Pinjam</th>
                        <th>Batas Kembali</th>
                        <th>Tgl Kembali</th>
                        <th>Status</th>
                    </tr>
                </thead>
                <tbody>
                    <?php if (empty($my_borrows)): ?>
                        <tr><td colspan="5" class="text-center py-5 text-muted">Anda belum pernah meminjam alat.</td></tr>
                    <?php
else:
    foreach ($my_borrows as $b):
        $statusClass = [
            'pending' => 'bg-warning',
            'approved' => 'bg-success',
            'rejected' => 'bg-danger',
            'returned' => 'bg-info'
        ][$b['status']];
?>
                        <tr>
                            <td class="ps-4">
                                <div class="fw-bold"><?php echo $b['nama_alat']; ?></div>
                                <small class="text-muted">Jumlah: <?php echo $b['jumlah']; ?></small>
                            </td>
                            <td><?php echo date('d M Y', strtotime($b['tgl_pinjam'])); ?></td>
                            <td><?php echo date('d M Y', strtotime($b['tgl_kembali_seharusnya'])); ?></td>
                            <td><?php echo $b['tgl_kembali'] ? date('d M Y', strtotime($b['tgl_kembali'])) : '-'; ?></td>
                            <td><span class="badge <?php echo $statusClass; ?> rounded-pill"><?php echo ucfirst($b['status']); ?></span></td>
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
</body>
</html>
