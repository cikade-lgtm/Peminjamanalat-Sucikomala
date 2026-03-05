<?php
require_once '../config/config.php';
$active_page = 'peminjaman';
$title = 'Manajemen Peminjaman';

if (!isset($_SESSION['user_id'])) {
    header('Location: ../index.php');
    exit;
}

$success = $_GET['success'] ?? '';
$error = $_GET['error'] ?? '';

// Handle Peminjaman (Borrowing)
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    if (isset($_POST['add'])) {
        $id_user = $_SESSION['user_id'];
        $tgl_pinjam = $_POST['tgl_pinjam'];
        $tgl_kembali = $_POST['tgl_kembali_seharusnya'];
        $id_alat = $_POST['id_alat'];
        $jumlah = $_POST['jumlah'];

        try {
            $stmt = $pdo->prepare("CALL sp_peminjaman(?, ?, ?, ?, ?)");
            $stmt->execute([$id_user, $tgl_pinjam, $tgl_kembali, $id_alat, $jumlah]);
            header('Location: peminjaman.php?success=Permohonan peminjaman berhasil diajukan');
            exit;
        }
        catch (PDOException $e) {
            $error = "Gagal memproses peminjaman: " . $e->getMessage();
        }
    }
    elseif (isset($_POST['approve'])) {
        $id = $_POST['id_peminjaman'];
        $id_petugas = $_SESSION['user_id'];

        $stmt = $pdo->prepare("UPDATE peminjaman SET status = 'approved', id_petugas = ? WHERE id_peminjaman = ?");
        if ($stmt->execute([$id_petugas, $id])) {
            header('Location: peminjaman.php?success=Peminjaman disetujui');
            exit;
        }
    }
    elseif (isset($_POST['reject'])) {
        $id = $_POST['id_peminjaman'];
        $id_petugas = $_SESSION['user_id'];

        $stmt = $pdo->prepare("UPDATE peminjaman SET status = 'rejected', id_petugas = ? WHERE id_peminjaman = ?");
        if ($stmt->execute([$id_petugas, $id])) {
            header('Location: peminjaman.php?success=Peminjaman ditolak');
            exit;
        }
    }
}

// Fetch Tools for selection
$tools = $pdo->query("SELECT * FROM alat WHERE stok > 0 ORDER BY nama_alat ASC")->fetchAll();

// Fetch Borrowings
$query = "SELECT p.*, u.nama_lengkap as peminjam, pt.nama_lengkap as petugas, a.nama_alat, dp.jumlah 
          FROM peminjaman p 
          JOIN users u ON p.id_user = u.id_user 
          LEFT JOIN users pt ON p.id_petugas = pt.id_user 
          JOIN detail_peminjaman dp ON p.id_peminjaman = dp.id_peminjaman
          JOIN alat a ON dp.id_alat = a.id_alat";

if ($_SESSION['role_id'] == 3) {
    $query .= " WHERE p.id_user = " . $_SESSION['user_id'];
}
$query .= " ORDER BY p.tgl_pinjam DESC";

$peminjaman = $pdo->query($query)->fetchAll();

include '../includes/header.php';
?>

<div class="d-flex justify-content-between align-items-center mb-4">
    <h2 class="fw-bold mb-0">Daftar Peminjaman</h2>
    <?php if ($_SESSION['role_id'] == 3): ?>
    <button class="btn btn-primary" data-bs-toggle="modal" data-bs-target="#addModal">
        <i class="fas fa-plus me-2"></i> Pinjam Alat
    </button>
    <?php
endif; ?>
</div>

<?php if ($success): ?>
    <div class="alert alert-success alert-dismissible fade show border-0 shadow-sm mb-4">
        <i class="fas fa-check-circle me-2"></i> <?php echo $success; ?>
        <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
    </div>
<?php
endif; ?>

<?php if ($error): ?>
    <div class="alert alert-danger alert-dismissible fade show border-0 shadow-sm mb-4">
        <i class="fas fa-exclamation-circle me-2"></i> <?php echo $error; ?>
        <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
    </div>
<?php
endif; ?>

<div class="card border-0 shadow-sm rounded-4">
    <div class="card-body p-0">
        <div class="table-responsive">
            <table class="table table-hover align-middle mb-0">
                <thead class="bg-light">
                    <tr>
                        <th class="ps-4">No</th>
                        <th>Peminjam</th>
                        <th>Alat</th>
                        <th>Jumlah</th>
                        <th>Tgl Pinjam</th>
                        <th>Tgl Kembali</th>
                        <th>Status</th>
                        <th class="text-end pe-4">Aksi</th>
                    </tr>
                </thead>
                <tbody>
                    <?php if (empty($peminjaman)): ?>
                        <tr><td colspan="8" class="text-center py-4 text-muted">Belum ada data peminjaman.</td></tr>
                    <?php
else:
    $no = 1;
    foreach ($peminjaman as $p):
        $statusClass = [
            'pending' => 'bg-warning',
            'approved' => 'bg-success',
            'rejected' => 'bg-danger',
            'returned' => 'bg-info'
        ][$p['status']];
?>
                        <tr>
                            <td class="ps-4"><?php echo $no++; ?></td>
                            <td><div class="fw-bold"><?php echo $p['peminjam']; ?></div></td>
                            <td><?php echo $p['nama_alat']; ?></td>
                            <td><span class="badge bg-secondary"><?php echo $p['jumlah']; ?></span></td>
                            <td><small><?php echo date('d/m/Y', strtotime($p['tgl_pinjam'])); ?></small></td>
                            <td><small><?php echo date('d/m/Y', strtotime($p['tgl_kembali_seharusnya'])); ?></small></td>
                            <td><span class="badge <?php echo $statusClass; ?> rounded-pill"><?php echo ucfirst($p['status']); ?></span></td>
                            <td class="text-end pe-4">
                                <?php if ($p['status'] == 'pending' && ($_SESSION['role_id'] == 1 || $_SESSION['role_id'] == 2)): ?>
                                    <form method="POST" class="d-inline">
                                        <input type="hidden" name="id_peminjaman" value="<?php echo $p['id_peminjaman']; ?>">
                                        <button type="submit" name="approve" class="btn btn-sm btn-success shadow-sm" title="Setujui"><i class="fas fa-check"></i></button>
                                        <button type="submit" name="reject" class="btn btn-sm btn-danger shadow-sm" title="Tolak"><i class="fas fa-times"></i></button>
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

<!-- Add Modal -->
<div class="modal fade" id="addModal" tabindex="-1">
    <div class="modal-dialog">
        <div class="modal-content border-0 shadow">
            <form method="POST">
                <div class="modal-header">
                    <h5 class="modal-title fw-bold">Ajukan Peminjaman</h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
                </div>
                <div class="modal-body">
                    <div class="mb-3">
                        <label class="form-label">Pilih Alat</label>
                        <select name="id_alat" class="form-select" required>
                            <option value="">Pilih Alat</option>
                            <?php foreach ($tools as $t): ?>
                                <option value="<?php echo $t['id_alat']; ?>"><?php echo $t['nama_alat']; ?> (Stok: <?php echo $t['stok']; ?>)</option>
                            <?php
endforeach; ?>
                        </select>
                    </div>
                    <div class="mb-3">
                        <label class="form-label">Jumlah</label>
                        <input type="number" name="jumlah" class="form-control" value="1" min="1" required>
                    </div>
                    <div class="mb-3">
                        <label class="form-label">Tanggal Pinjam</label>
                        <input type="date" name="tgl_pinjam" class="form-control" value="<?php echo date('Y-m-d'); ?>" required>
                    </div>
                    <div class="mb-3">
                        <label class="form-label">Tanggal Harus Kembali</label>
                        <input type="date" name="tgl_kembali_seharusnya" class="form-control" required>
                    </div>
                </div>
                <div class="modal-footer border-0">
                    <button type="button" class="btn btn-light" data-bs-dismiss="modal">Batal</button>
                    <button type="submit" name="add" class="btn btn-primary">Ajukan Pinjaman</button>
                </div>
            </form>
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
