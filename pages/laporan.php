<?php
require_once '../config/config.php';
$active_page = 'laporan';
$title = 'Laporan Peminjaman';

if (!isset($_SESSION['user_id']) || ($_SESSION['role_id'] != 1 && $_SESSION['role_id'] != 2)) {
    header('Location: ../dashboard.php');
    exit;
}

$tgl_awal = $_GET['tgl_awal'] ?? date('Y-m-01');
$tgl_akhir = $_GET['tgl_akhir'] ?? date('Y-m-d');

$query = "SELECT p.*, u.nama_lengkap as peminjam, a.nama_alat, dp.jumlah, pg.tgl_kembali, pg.denda
          FROM peminjaman p 
          JOIN users u ON p.id_user = u.id_user 
          JOIN detail_peminjaman dp ON p.id_peminjaman = dp.id_peminjaman
          JOIN alat a ON dp.id_alat = a.id_alat
          LEFT JOIN pengembalian pg ON p.id_peminjaman = pg.id_peminjaman
          WHERE p.tgl_pinjam BETWEEN ? AND ?
          ORDER BY p.tgl_pinjam DESC";

$stmt = $pdo->prepare($query);
$stmt->execute([$tgl_awal, $tgl_akhir]);
$data = $stmt->fetchAll();

include '../includes/header.php';
?>

<div class="mb-4">
    <h2 class="fw-bold mb-0">Laporan Peminjaman</h2>
    <p class="text-muted">Cetak dan filter laporan peminjaman alat.</p>
</div>

<div class="card border-0 shadow-sm rounded-4 mb-4">
    <div class="card-body p-4">
        <form method="GET" class="row align-items-end g-3">
            <div class="col-md-4">
                <label class="form-label small fw-bold">Tanggal Awal</label>
                <input type="date" name="tgl_awal" class="form-control" value="<?php echo $tgl_awal; ?>">
            </div>
            <div class="col-md-4">
                <label class="form-label small fw-bold">Tanggal Akhir</label>
                <input type="date" name="tgl_akhir" class="form-control" value="<?php echo $tgl_akhir; ?>">
            </div>
            <div class="col-md-4 d-flex gap-2">
                <button type="submit" class="btn btn-primary flex-grow-1">
                    <i class="fas fa-filter me-2"></i> Filter
                </button>
                <button type="button" onclick="window.print()" class="btn btn-outline-secondary">
                    <i class="fas fa-print"></i>
                </button>
            </div>
        </form>
    </div>
</div>

<div class="card border-0 shadow-sm rounded-4">
    <div class="card-body p-0">
        <div class="table-responsive">
            <table class="table table-hover align-middle mb-0">
                <thead class="bg-light">
                    <tr>
                        <th class="ps-4">No</th>
                        <th>Peminjam</th>
                        <th>Alat</th>
                        <th>Tgl Pinjam</th>
                        <th>Tgl Kembali</th>
                        <th>Denda</th>
                        <th>Status</th>
                    </tr>
                </thead>
                <tbody>
                    <?php if (empty($data)): ?>
                        <tr><td colspan="7" class="text-center py-4 text-muted">Tidak ada data untuk periode ini.</td></tr>
                    <?php
else:
    $no = 1;
    foreach ($data as $row):
?>
                        <tr>
                            <td class="ps-4"><?php echo $no++; ?></td>
                            <td class="fw-bold"><?php echo $row['peminjam']; ?></td>
                            <td><?php echo $row['nama_alat']; ?> (<?php echo $row['jumlah']; ?>)</td>
                            <td><?php echo date('d/m/Y', strtotime($row['tgl_pinjam'])); ?></td>
                            <td><?php echo $row['tgl_kembali'] ? date('d/m/Y', strtotime($row['tgl_kembali'])) : '-'; ?></td>
                            <td>Rp <?php echo number_format($row['denda'] ?? 0, 0, ',', '.'); ?></td>
                            <td>
                                <span class="badge bg-opacity-10 rounded-pill 
                                    <?php echo $row['status'] == 'returned' ? 'bg-success text-success' : 'bg-warning text-warning'; ?>">
                                    <?php echo ucfirst($row['status']); ?>
                                </span>
                            </td>
                        </tr>
                    <?php
    endforeach;
endif; ?>
                </tbody>
            </table>
        </div>
    </div>
</div>

<style>
@media print {
    #sidebar, .navbar-custom, .card-body form, button, .btn {
        display: none !important;
    }
    #content {
        margin-left: 0 !important;
        width: 100% !important;
    }
    .wrapper {
        display: block !important;
    }
    .card {
        border: none !important;
        box-shadow: none !important;
    }
    .main-content {
        padding: 0 !important;
    }
}
</style>

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
