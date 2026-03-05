<?php
require_once '../config/config.php';
$active_page = 'katalog';
$title = 'Katalog Alat';

if (!isset($_SESSION['user_id'])) {
    header('Location: ../index.php');
    exit;
}

$tools = $pdo->query("SELECT a.*, k.nama_kategori FROM alat a LEFT JOIN kategori k ON a.id_kategori = k.id_kategori WHERE a.stok > 0 ORDER BY a.nama_alat ASC")->fetchAll();

include '../includes/header.php';
?>

<div class="mb-4">
    <h2 class="fw-bold mb-0">Katalog Alat</h2>
    <p class="text-muted">Cari dan pilih alat yang ingin Anda pinjam.</p>
</div>

<div class="row">
    <?php if (empty($tools)): ?>
        <div class="col-12 text-center py-5">
            <i class="fas fa-search fa-3x text-muted mb-3"></i>
            <p class="text-muted">Maaf, saat ini tidak ada alat yang tersedia untuk dipinjam.</p>
        </div>
    <?php
else:
    foreach ($tools as $t): ?>
        <div class="col-md-4 col-lg-3 mb-4">
            <div class="card h-100 border-0 shadow-sm rounded-4 overflow-hidden transition-all hover-up">
                <div class="bg-primary bg-opacity-10 py-5 text-center">
                    <i class="fas fa-toolbox fa-4x text-primary opacity-50"></i>
                </div>
                <div class="card-body">
                    <span class="badge bg-info bg-opacity-10 text-info mb-2"><?php echo $t['nama_kategori'] ?? 'Alat'; ?></span>
                    <h5 class="fw-bold mb-1"><?php echo $t['nama_alat']; ?></h5>
                    <p class="small text-muted mb-3 text-truncate"><?php echo $t['spesifikasi']; ?></p>
                    <div class="d-flex justify-content-between align-items-center">
                        <span class="small fw-bold <?php echo($t['stok'] < 3) ? 'text-danger' : 'text-success'; ?>">
                            Stok: <?php echo $t['stok']; ?>
                        </span>
                        <a href="peminjaman.php" class="btn btn-sm btn-primary rounded-pill px-3">Pinjam</a>
                    </div>
                </div>
            </div>
        </div>
    <?php
    endforeach;
endif; ?>
</div>

<style>
.hover-up:hover {
    transform: translateY(-5px);
    box-shadow: 0 10px 20px rgba(0,0,0,0.1) !important;
}
</style>

</div> 
</div> 
</div> 
<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
</body>
</html>
