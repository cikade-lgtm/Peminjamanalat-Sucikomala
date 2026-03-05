<?php
require_once '../config/config.php';
$active_page = 'kategori';
$title = 'Manajemen Kategori';

if (!isset($_SESSION['user_id'])) {
    header('Location: ../index.php');
    exit;
}

$success = $_GET['success'] ?? '';
$error = $_GET['error'] ?? '';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    if (isset($_POST['add'])) {
        $nama = $_POST['nama_kategori'];
        $stmt = $pdo->prepare("INSERT INTO kategori (nama_kategori) VALUES (?)");
        if ($stmt->execute([$nama])) {
            header('Location: kategori.php?success=Kategori berhasil ditambahkan');
            exit;
        }
    }
    elseif (isset($_POST['edit'])) {
        $id = $_POST['id_kategori'];
        $nama = $_POST['nama_kategori'];
        $stmt = $pdo->prepare("UPDATE kategori SET nama_kategori = ? WHERE id_kategori = ?");
        if ($stmt->execute([$nama, $id])) {
            header('Location: kategori.php?success=Kategori berhasil diperbarui');
            exit;
        }
    }
    elseif (isset($_POST['delete'])) {
        $id = $_POST['id_kategori'];
        $stmt = $pdo->prepare("DELETE FROM kategori WHERE id_kategori = ?");
        if ($stmt->execute([$id])) {
            header('Location: kategori.php?success=Kategori berhasil dihapus');
            exit;
        }
    }
}

$categories = $pdo->query("SELECT * FROM kategori ORDER BY id_kategori DESC")->fetchAll();

include '../includes/header.php';
?>

<div class="d-flex justify-content-between align-items-center mb-4">
    <h2 class="fw-bold mb-0">Manajemen Kategori</h2>
    <button class="btn btn-primary" data-bs-toggle="modal" data-bs-target="#addModal">
        <i class="fas fa-plus me-2"></i> Tambah Kategori
    </button>
</div>

<?php if ($success): ?>
    <div class="alert alert-success alert-dismissible fade show border-0 shadow-sm mb-4">
        <i class="fas fa-check-circle me-2"></i> <?php echo $success; ?>
        <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
    </div>
<?php
endif; ?>

<div class="row">
    <div class="col-md-12">
        <div class="card border-0 shadow-sm rounded-4">
            <div class="card-body p-0">
                <div class="table-responsive">
                    <table class="table table-hover align-middle mb-0">
                        <thead class="bg-light">
                            <tr>
                                <th class="ps-4">ID</th>
                                <th>Nama Kategori</th>
                                <th class="text-end pe-4">Aksi</th>
                            </tr>
                        </thead>
                        <tbody>
                            <?php foreach ($categories as $cat): ?>
                            <tr>
                                <td class="ps-4"><?php echo $cat['id_kategori']; ?></td>
                                <td><span class="fw-bold"><?php echo $cat['nama_kategori']; ?></span></td>
                                <td class="text-end pe-4">
                                    <button class="btn btn-sm btn-light border" data-bs-toggle="modal" data-bs-target="#editModal<?php echo $cat['id_kategori']; ?>">
                                        <i class="fas fa-edit text-primary"></i>
                                    </button>
                                    <button class="btn btn-sm btn-light border" data-bs-toggle="modal" data-bs-target="#deleteModal<?php echo $cat['id_kategori']; ?>">
                                        <i class="fas fa-trash text-danger"></i>
                                    </button>
                                </td>
                            </tr>

                            <!-- Edit Modal -->
                            <div class="modal fade" id="editModal<?php echo $cat['id_kategori']; ?>" tabindex="-1">
                                <div class="modal-dialog">
                                    <div class="modal-content border-0 shadow">
                                        <form method="POST">
                                            <div class="modal-header">
                                                <h5 class="modal-title fw-bold">Edit Kategori</h5>
                                                <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
                                            </div>
                                            <div class="modal-body">
                                                <input type="hidden" name="id_kategori" value="<?php echo $cat['id_kategori']; ?>">
                                                <div class="mb-3">
                                                    <label class="form-label">Nama Kategori</label>
                                                    <input type="text" name="nama_kategori" class="form-control" value="<?php echo $cat['nama_kategori']; ?>" required>
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
                            <div class="modal fade" id="deleteModal<?php echo $cat['id_kategori']; ?>" tabindex="-1">
                                <div class="modal-dialog">
                                    <div class="modal-content border-0 shadow">
                                        <form method="POST">
                                            <div class="modal-header">
                                                <h5 class="modal-title fw-bold">Hapus Kategori</h5>
                                                <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
                                            </div>
                                            <div class="modal-body">
                                                <input type="hidden" name="id_kategori" value="<?php echo $cat['id_kategori']; ?>">
                                                <p>Apakah Anda yakin ingin menghapus kategori <strong><?php echo $cat['nama_kategori']; ?></strong>? Tindakan ini mungkin mempengaruhi data alat yang terkait.</p>
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
endforeach; ?>
                        </tbody>
                    </table>
                </div>
            </div>
        </div>
    </div>
</div>

<!-- Add Modal -->
<div class="modal fade" id="addModal" tabindex="-1">
    <div class="modal-dialog">
        <div class="modal-content border-0 shadow">
            <form method="POST">
                <div class="modal-header">
                    <h5 class="modal-title fw-bold">Tambah Kategori Baru</h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
                </div>
                <div class="modal-body">
                    <div class="mb-3">
                        <label class="form-label">Nama Kategori</label>
                        <input type="text" name="nama_kategori" class="form-control" placeholder="Contoh: Perkabelan" required>
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
