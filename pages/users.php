<?php
require_once '../config/config.php';
$active_page = 'users';
$title = 'Manajemen User';

if (!isset($_SESSION['user_id']) || $_SESSION['role_id'] != 1) {
    header('Location: ../dashboard.php');
    exit;
}

$success = $_GET['success'] ?? '';
$error = $_GET['error'] ?? '';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    if (isset($_POST['add'])) {
        $username = $_POST['username'];
        $password = password_hash($_POST['password'], PASSWORD_DEFAULT);
        $nama = $_POST['nama_lengkap'];
        $role = $_POST['id_role'];

        $stmt = $pdo->prepare("INSERT INTO users (username, password, nama_lengkap, id_role) VALUES (?, ?, ?, ?)");
        if ($stmt->execute([$username, $password, $nama, $role])) {
            header('Location: users.php?success=User berhasil ditambahkan');
            exit;
        }
    }
    elseif (isset($_POST['edit'])) {
        $id = $_POST['id_user'];
        $username = $_POST['username'];
        $nama = $_POST['nama_lengkap'];
        $role = $_POST['id_role'];

        if (!empty($_POST['password'])) {
            $password = password_hash($_POST['password'], PASSWORD_DEFAULT);
            $stmt = $pdo->prepare("UPDATE users SET username = ?, password = ?, nama_lengkap = ?, id_role = ? WHERE id_user = ?");
            $success_exec = $stmt->execute([$username, $password, $nama, $role, $id]);
        }
        else {
            $stmt = $pdo->prepare("UPDATE users SET username = ?, nama_lengkap = ?, id_role = ? WHERE id_user = ?");
            $success_exec = $stmt->execute([$username, $nama, $role, $id]);
        }

        if ($success_exec) {
            header('Location: users.php?success=User berhasil diperbarui');
            exit;
        }
    }
    elseif (isset($_POST['delete'])) {
        $id = $_POST['id_user'];
        if ($id != $_SESSION['user_id']) {
            $stmt = $pdo->prepare("DELETE FROM users WHERE id_user = ?");
            if ($stmt->execute([$id])) {
                header('Location: users.php?success=User berhasil dihapus');
                exit;
            }
        }
        else {
            $error = "Anda tidak bisa menghapus akun sendiri!";
        }
    }
}

$roles = $pdo->query("SELECT * FROM roles")->fetchAll();
$users = $pdo->query("SELECT u.*, r.nama_role FROM users u JOIN roles r ON u.id_role = r.id_role ORDER BY u.id_user DESC")->fetchAll();

include '../includes/header.php';
?>

<div class="d-flex justify-content-between align-items-center mb-4">
    <h2 class="fw-bold mb-0">Manajemen User</h2>
    <button class="btn btn-primary" data-bs-toggle="modal" data-bs-target="#addModal">
        <i class="fas fa-user-plus me-2"></i> Tambah User
    </button>
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
                        <th>Nama Lengkap</th>
                        <th>Username</th>
                        <th>Role</th>
                        <th class="text-end pe-4">Aksi</th>
                    </tr>
                </thead>
                <tbody>
                    <?php
$no = 1;
foreach ($users as $u):
    $roleBadge = ($u['id_role'] == 1) ? 'bg-danger' : (($u['id_role'] == 2) ? 'bg-primary' : 'bg-info');
?>
                    <tr>
                        <td class="ps-4"><?php echo $no++; ?></td>
                        <td><div class="fw-bold"><?php echo $u['nama_lengkap']; ?></div></td>
                        <td><?php echo $u['username']; ?></td>
                        <td><span class="badge <?php echo $roleBadge; ?> rounded-pill"><?php echo $u['nama_role']; ?></span></td>
                        <td class="text-end pe-4">
                            <button class="btn btn-sm btn-light border" data-bs-toggle="modal" data-bs-target="#editModal<?php echo $u['id_user']; ?>">
                                <i class="fas fa-edit text-primary"></i>
                            </button>
                            <?php if ($u['id_user'] != $_SESSION['user_id']): ?>
                            <button class="btn btn-sm btn-light border" data-bs-toggle="modal" data-bs-target="#deleteModal<?php echo $u['id_user']; ?>">
                                <i class="fas fa-trash text-danger"></i>
                            </button>
                            <?php
    endif; ?>
                        </td>
                    </tr>

                    <!-- Edit Modal -->
                    <div class="modal fade" id="editModal<?php echo $u['id_user']; ?>" tabindex="-1">
                        <div class="modal-dialog">
                            <div class="modal-content border-0 shadow">
                                <form method="POST">
                                    <div class="modal-header">
                                        <h5 class="modal-title fw-bold">Edit User</h5>
                                        <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
                                    </div>
                                    <div class="modal-body">
                                        <input type="hidden" name="id_user" value="<?php echo $u['id_user']; ?>">
                                        <div class="mb-3">
                                            <label class="form-label">Nama Lengkap</label>
                                            <input type="text" name="nama_lengkap" class="form-control" value="<?php echo $u['nama_lengkap']; ?>" required>
                                        </div>
                                        <div class="mb-3">
                                            <label class="form-label">Username</label>
                                            <input type="text" name="username" class="form-control" value="<?php echo $u['username']; ?>" required>
                                        </div>
                                        <div class="mb-3">
                                            <label class="form-label">Password <small class="text-muted">(Kosongkan jika tidak diubah)</small></label>
                                            <input type="password" name="password" class="form-control">
                                        </div>
                                        <div class="mb-3">
                                            <label class="form-label">Role</label>
                                            <select name="id_role" class="form-select" required>
                                                <?php foreach ($roles as $r): ?>
                                                    <option value="<?php echo $r['id_role']; ?>" <?php echo($r['id_role'] == $u['id_role']) ? 'selected' : ''; ?>>
                                                        <?php echo $r['nama_role']; ?>
                                                    </option>
                                                <?php
    endforeach; ?>
                                            </select>
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
                    <div class="modal fade" id="deleteModal<?php echo $u['id_user']; ?>" tabindex="-1">
                        <div class="modal-dialog">
                            <div class="modal-content border-0 shadow">
                                <form method="POST">
                                    <div class="modal-header">
                                        <h5 class="modal-title fw-bold">Hapus User</h5>
                                        <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
                                    </div>
                                    <div class="modal-body">
                                        <input type="hidden" name="id_user" value="<?php echo $u['id_user']; ?>">
                                        <p>Apakah Anda yakin ingin menghapus user <strong><?php echo $u['nama_lengkap']; ?></strong>?</p>
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

<!-- Add Modal -->
<div class="modal fade" id="addModal" tabindex="-1">
    <div class="modal-dialog">
        <div class="modal-content border-0 shadow">
            <form method="POST">
                <div class="modal-header">
                    <h5 class="modal-title fw-bold">Tambah User Baru</h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
                </div>
                <div class="modal-body">
                    <div class="mb-3">
                        <label class="form-label">Nama Lengkap</label>
                        <input type="text" name="nama_lengkap" class="form-control" placeholder="Nama Lengkap" required>
                    </div>
                    <div class="mb-3">
                        <label class="form-label">Username</label>
                        <input type="text" name="username" class="form-control" placeholder="Username" required>
                    </div>
                    <div class="mb-3">
                        <label class="form-label">Password</label>
                        <input type="password" name="password" class="form-control" placeholder="Password" required>
                    </div>
                    <div class="mb-3">
                        <label class="form-label">Role</label>
                        <select name="id_role" class="form-select" required>
                            <option value="">Pilih Role</option>
                            <?php foreach ($roles as $r): ?>
                                <option value="<?php echo $r['id_role']; ?>"><?php echo $r['nama_role']; ?></option>
                            <?php
endforeach; ?>
                        </select>
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
