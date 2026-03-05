<?php
require_once '../config/config.php';
$active_page = 'alat';
$title = 'Manajemen Alat';

// Check if user is logged in
if (!isset($_SESSION['user_id'])) {
    header('Location: ../index.php');
    exit;
}

$success = $_GET['success'] ?? '';
$error = $_GET['error'] ?? '';

// Handle CRUD
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    if (isset($_POST['add'])) {
        $nama = $_POST['nama_alat'];
        $kategori = $_POST['id_kategori'];
        $spek = $_POST['spesifikasi'];
        $stok = $_POST['stok'];

        $stmt = $pdo->prepare("INSERT INTO alat (nama_alat, id_kategori, spesifikasi, stok) VALUES (?, ?, ?, ?)");
        if ($stmt->execute([$nama, $kategori, $spek, $stok])) {
            header('Location: alat.php?success=Alat berhasil ditambahkan');
            exit;
        }
    }
    elseif (isset($_POST['edit'])) {
        $id = $_POST['id_alat'];
        $nama = $_POST['nama_alat'];
        $kategori = $_POST['id_kategori'];
        $spek = $_POST['spesifikasi'];
        $stok = $_POST['stok'];

        $stmt = $pdo->prepare("UPDATE alat SET nama_alat = ?, id_kategori = ?, spesifikasi = ?, stok = ? WHERE id_alat = ?");
        if ($stmt->execute([$nama, $kategori, $spek, $stok, $id])) {
            header('Location: alat.php?success=Alat berhasil diperbarui');
            exit;
        }
    }
    elseif (isset($_POST['delete'])) {
        $id = $_POST['id_alat'];
        $stmt = $pdo->prepare("DELETE FROM alat WHERE id_alat = ?");
        if ($stmt->execute([$id])) {
            header('Location: alat.php?success=Alat berhasil dihapus');
            exit;
        }
    }
}

// Fetch Categories for dropdown
$categories = $pdo->query("SELECT * FROM kategori ORDER BY nama_kategori ASC")->fetchAll();

// Fetch Tools with Category Name
$tools = $pdo->query("SELECT a.*, k.nama_kategori FROM alat a LEFT JOIN kategori k ON a.id_kategori = k.id_kategori ORDER BY a.id_alat DESC")->fetchAll();

// Absolute paths for includes since we are in /pages
include '../includes/header.php';
?>

<div class="d-flex justify-content-between align-items-center mb-4">
    <h2 class="fw-bold mb-0">Manajemen Alat</h2>
    <button class="btn btn-primary" data-bs-toggle="modal" data-bs-target="#addModal">
        <i class="fas fa-plus me-2"></i> Tambah Alat
    </button>
</div>

<?php if ($success): ?>
    <div class="alert alert-success alert-dismissible fade show border-0 shadow-sm mb-4">
        <i class="fas fa-check-circle me-2"></i> <?php echo $success; ?>
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
                        <th>Nama Alat</th>
                        <th>Kategori</th>
                        <th>Stok</th>
                        <th class="text-end pe-4">Aksi</th>
                    </tr>
                </thead>
                <tbody>
                    <?php if (empty($tools)): ?>
                        <tr><td colspan="5" class="text-center py-4 text-muted">Belum ada data alat.</td></tr>
                    <?php
else:
    $no = 1;
    foreach ($tools as $tool):
?>
                        <tr>
                            <td class="ps-4"><?php echo $no++; ?></td>
                            <td>
                                <div class="fw-bold"><?php echo $tool['nama_alat']; ?></div>
                                <small class="text-muted"><?php echo substr($tool['spesifikasi'], 0, 50); ?>...</small>
                            </td>
                            <td><span class="badge bg-info bg-opacity-10 text-info"><?php echo $tool['nama_kategori'] ?? 'N/A'; ?></span></td>
                            <td>
                                <span class="fw-bold <?php echo($tool['stok'] < 5) ? 'text-danger' : ''; ?>">
                                    <?php echo $tool['stok']; ?>
                                </span>
                            </td>
                            <td class="text-end pe-4">
                                <button class="btn btn-sm btn-light border" data-bs-toggle="modal" data-bs-target="#editModal<?php echo $tool['id_alat']; ?>">
                                    <i class="fas fa-edit text-primary"></i>
                                </button>
                                <button class="btn btn-sm btn-light border" data-bs-toggle="modal" data-bs-target="#deleteModal<?php echo $tool['id_alat']; ?>">
                                    <i class="fas fa-trash text-danger"></i>
                                </button>
                            </td>
                        </tr>

                        <!-- Edit Modal -->
                        <div class="modal fade" id="editModal<?php echo $tool['id_alat']; ?>" tabindex="-1">
                            <div class="modal-dialog">
                                <div class="modal-content border-0 shadow">
                                    <form method="POST">
                                        <div class="modal-header">
                                            <h5 class="modal-title fw-bold">Edit Alat</h5>
                                            <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
                                        </div>
                                        <div class="modal-body">
                                            <input type="hidden" name="id_alat" value="<?php echo $tool['id_alat']; ?>">
                                            <div class="mb-3">
                                                <label class="form-label">Nama Alat</label>
                                                <input type="text" name="nama_alat" class="form-control" value="<?php echo $tool['nama_alat']; ?>" required>
                                            </div>
                                            <div class="mb-3">
                                                <label class="form-label">Kategori</label>
                                                <select name="id_kategori" class="form-select" required>
                                                    <?php foreach ($categories as $cat): ?>
                                                        <option value="<?php echo $cat['id_kategori']; ?>" <?php echo($cat['id_kategori'] == $tool['id_kategori']) ? 'selected' : ''; ?>>
                                                            <?php echo $cat['nama_kategori']; ?>
                                                        </option>
                                                    <?php
        endforeach; ?>
                                                </select>
                                            </div>
                                            <div class="mb-3">
                                                <label class="form-label">Spesifikasi</label>
                                                <textarea name="spesifikasi" class="form-control" rows="3"><?php echo $tool['spesifikasi']; ?></textarea>
                                            </div>
                                            <div class="mb-3">
                                                <label class="form-label">Stok</label>
                                                <input type="number" name="stok" class="form-control" value="<?php echo $tool['stok']; ?>" required>
                                            </div>
                                        </div>
                                        <div class="modal-footer border-0">
                                            <button type="button" class="btn btn-light" data-bs-dismiss="modal">Batal</button>
                                            <button type="submit" name="edit" class="btn btn-primary">Simpan Perubahan</button>
                                        </div>
                                    </form>
                                </div>
                            </div>
                        </div>

                        <!-- Delete Modal -->
                        <div class="modal fade" id="deleteModal<?php echo $tool['id_alat']; ?>" tabindex="-1">
                            <div class="modal-dialog">
                                <div class="modal-content border-0 shadow">
                                    <form method="POST">
                                        <div class="modal-header">
                                            <h5 class="modal-title fw-bold">Hapus Alat</h5>
                                            <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
                                        </div>
                                        <div class="modal-body">
                                            <input type="hidden" name="id_alat" value="<?php echo $tool['id_alat']; ?>">
                                            <p>Apakah Anda yakin ingin menghapus alat <strong><?php echo $tool['nama_alat']; ?></strong>?</p>
                                        </div>
                                        <div class="modal-footer border-0">
                                            <button type="button" class="btn btn-light" data-bs-dismiss="modal">Batal</button>
                                            <button type="submit" name="delete" class="btn btn-danger">Hapus</button>
                                        </div>
                                    </form>
                                </div>
                            </div>
                        </div>
                    <?php
    endforeach;
endif;
?>
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
                    <h5 class="modal-title fw-bold">Tambah Alat Baru</h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
                </div>
                <div class="modal-body">
                    <div class="mb-3">
                        <label class="form-label">Nama Alat</label>
                        <input type="text" name="nama_alat" class="form-control" placeholder="Contoh: Bor Listrik" required>
                    </div>
                    <div class="mb-3">
                        <label class="form-label">Kategori</label>
                        <select name="id_kategori" class="form-select" required>
                            <option value="">Pilih Kategori</option>
                            <?php foreach ($categories as $cat): ?>
                                <option value="<?php echo $cat['id_kategori']; ?>"><?php echo $cat['nama_kategori']; ?></option>
                            <?php
endforeach; ?>
                        </select>
                    </div>
                    <div class="mb-3">
                        <label class="form-label">Spesifikasi</label>
                        <textarea name="spesifikasi" class="form-control" rows="3" placeholder="Detail spesifikasi alat..."></textarea>
                    </div>
                    <div class="mb-3">
                        <label class="form-label">Stok Awal</label>
                        <input type="number" name="stok" class="form-control" value="0" required>
                    </div>
                </div>
                <div class="modal-footer border-0">
                    <button type="button" class="btn btn-light" data-bs-dismiss="modal">Batal</button>
                    <button type="submit" name="add" class="btn btn-primary">Simpan</button>
                </div>
            </form>
        </div>
    </div>
</div>

<?php

// Layout footer inclusion happens via relative paths if needed, 
// here we shared the structure in header and closed it in dashboard.
// For page files, we need a common footer or just close it here.
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
