<?php
require_once '../config/config.php';
$active_page = 'pengembalian';
$title = 'Manajemen Pengembalian';

if (!isset($_SESSION['user_id'])) {
    header('Location: ../index.php');
    exit;
}

$success = $_GET['success'] ?? '';
$error = $_GET['error'] ?? '';

// Handle Pengembalian (Return)
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['return'])) {
    $id_peminjaman = $_POST['id_peminjaman'];
    $tgl_kembali = date('Y-m-d');

    // Fetch deadline for late fee calculation
    $stmt = $pdo->prepare("SELECT tgl_kembali_seharusnya FROM peminjaman WHERE id_peminjaman = ?");
    $stmt->execute([$id_peminjaman]);
    $p = $stmt->fetch();

    // Use MySQL function for late fee
    $fineStmt = $pdo->prepare("SELECT hitung_denda(?, ?) as denda");
    $fineStmt->execute([$p['tgl_kembali_seharusnya'], $tgl_kembali]);
    $denda = $fineStmt->fetch()['denda'];

    try {
        $pdo->beginTransaction();

        $stmt = $pdo->prepare("INSERT INTO pengembalian (id_peminjaman, tgl_kembali, denda) VALUES (?, ?, ?)");
        $stmt->execute([$id_peminjaman, $tgl_kembali, $denda]);

        $pdo->commit();
        header('Location: pengembalian.php?success=Alat berhasil dikembalikan. Denda: Rp ' . number_format($denda, 0, ',', '.'));
        exit;
    }
    catch (PDOException $e) {
        $pdo->rollBack();
        $error = "Gagal memproses pengembalian: " . $e->getMessage();
    }
}

// Fetch Approved Borrowings that haven't been returned
$query = "SELECT p.*, u.nama_lengkap as peminjam, a.nama_alat, dp.jumlah 
          FROM peminjaman p 
          JOIN users u ON p.id_user = u.id_user 
          JOIN detail_peminjaman dp ON p.id_peminjaman = dp.id_peminjaman
          JOIN alat a ON dp.id_alat = a.id_alat
          WHERE p.status = 'approved'";

if ($_SESSION['role_id'] == 3) {
    $query .= " AND p.id_user = " . $_SESSION['user_id'];
}

$active_peminjaman = $pdo->query($query)->fetchAll();

// Fetch Returns
$returned = $pdo->query("SELECT pg.*, p.tgl_pinjam, p.tgl_kembali_seharusnya, u.nama_lengkap as peminjam, a.nama_alat 
                          FROM pengembalian pg
                          JOIN peminjaman p ON pg.id_peminjaman = p.id_peminjaman
                          JOIN users u ON p.id_user = u.id_user
                          JOIN detail_peminjaman dp ON p.id_peminjaman = dp.id_peminjaman
                          JOIN alat a ON dp.id_alat = a.id_alat
                          ORDER BY pg.tgl_kembali DESC")->fetchAll();

include '../includes/header.php';
?>

<div class="mb-4">
    <h2 class="fw-bold mb-0">Manajemen Pengembalian</h2>
</div>

<?php if ($success): ?>
    <div class="alert alert-success alert-dismissible fade show border-0 shadow-sm mb-4">
        <i class="fas fa-check-circle me-2"></i> <?php echo $success; ?>
        <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
    </div>
<?php
endif; ?>

<div class="row">
    <div class="col-md-12 mb-4">
        <div class="card border-0 shadow-sm rounded-4">
            <div class="card-header bg-white border-0 py-3">
                <h5 class="mb-0 fw-bold">Menunggu Pengembalian</h5>
            </div>
            <div class="card-body p-0">
                <div class="table-responsive">
                    <table class="table table-hover align-middle mb-0">
                        <thead class="bg-light">
                            <tr>
                                <th class="ps-4">No</th>
                                <th>Peminjam</th>
                                <th>Alat</th>
                                <th>Jumlah</th>
                                <th>Batas Kembali</th>
                                <th class="text-end pe-4">Aksi</th>
                            </tr>
                        </thead>
                        <tbody>
                            <?php if (empty($active_peminjaman)): ?>
                                <tr><td colspan="6" class="text-center py-4 text-muted">Tidak ada peminjaman aktif.</td></tr>
                            <?php
else:
    $no = 1;
    foreach ($active_peminjaman as $p):
        $is_late = (strtotime(date('Y-m-d')) > strtotime($p['tgl_kembali_seharusnya']));
?>
                                <tr>
                                    <td class="ps-4"><?php echo $no++; ?></td>
                                    <td><div class="fw-bold"><?php echo $p['peminjam']; ?></div></td>
                                    <td><?php echo $p['nama_alat']; ?></td>
                                    <td><span class="badge bg-secondary"><?php echo $p['jumlah']; ?></span></td>
                                    <td>
                                        <span class="<?php echo $is_late ? 'text-danger fw-bold' : ''; ?>">
                                            <?php echo date('d/m/Y', strtotime($p['tgl_kembali_seharusnya'])); ?>
                                            <?php if ($is_late): ?> <i class="fas fa-exclamation-triangle"></i> <?php
        endif; ?>
                                        </span>
                                    </td>
                                    <td class="text-end pe-4">
                                        <?php if ($_SESSION['role_id'] != 3 || $p['id_user'] == $_SESSION['user_id']): ?>
                                        <form method="POST" class="d-inline">
                                            <input type="hidden" name="id_peminjaman" value="<?php echo $p['id_peminjaman']; ?>">
                                            <button type="submit" name="return" class="btn btn-sm btn-primary shadow-sm">
                                                Kembalikan <i class="fas fa-undo ms-1"></i>
                                            </button>
                                        </form>
                                        <?php
        endif; ?>
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
    </div>

    <div class="col-md-12">
        <div class="card border-0 shadow-sm rounded-4">
            <div class="card-header bg-white border-0 py-3">
                <h5 class="mb-0 fw-bold">Riwayat Pengembalian</h5>
            </div>
            <div class="card-body p-0">
                <div class="table-responsive">
                    <table class="table table-hover align-middle mb-0">
                        <thead class="bg-light">
                            <tr>
                                <th class="ps-4">Peminjam</th>
                                <th>Alat</th>
                                <th>Tgl Kembali</th>
                                <th>Denda</th>
                            </tr>
                        </thead>
                        <tbody>
                            <?php foreach ($returned as $r): ?>
                            <tr>
                                <td class="ps-4"><?php echo $r['peminjam']; ?></td>
                                <td><?php echo $r['nama_alat']; ?></td>
                                <td><?php echo date('d/m/Y', strtotime($r['tgl_kembali'])); ?></td>
                                <td>
                                    <?php if ($r['denda'] > 0): ?>
                                        <span class="text-danger fw-bold">Rp <?php echo number_format($r['denda'], 0, ',', '.'); ?></span>
                                    <?php
    else: ?>
                                        <span class="text-success">Nihil</span>
                                    <?php
    endif; ?>
                                </td>
                            </tr>
                            <?php
endforeach; ?>
                        </tbody>
                    </table>
                </div>
            </div>
        </div>
    </div>
</div>

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
