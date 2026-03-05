<nav id="sidebar">
    <div class="sidebar-header">
        <h4 class="mb-0"><i class="fas fa-toolbox me-2"></i>UKK Alat</h4>
    </div>

    <ul class="list-unstyled components">
        <li class="header text-muted px-4 py-2 mt-2">Main Menu</li>
        <li class="<?php echo($active_page == 'dashboard') ? 'active' : ''; ?>">
            <a href="<?php echo BASE_URL; ?>dashboard.php"><i class="fas fa-tachometer-alt me-2"></i> Dashboard</a>
        </li>
        
        <li class="header text-muted px-4 py-2 mt-4">Master Data</li>
        <li class="<?php echo($active_page == 'kategori') ? 'active' : ''; ?>">
            <a href="<?php echo BASE_URL; ?>pages/kategori.php"><i class="fas fa-tags me-2"></i> Kategori Alat</a>
        </li>
        <li class="<?php echo($active_page == 'alat') ? 'active' : ''; ?>">
            <a href="<?php echo BASE_URL; ?>pages/alat.php"><i class="fas fa-tools me-2"></i> Daftar Alat</a>
        </li>

        <li class="header text-muted px-4 py-2 mt-4">Transaksi</li>
        <li class="<?php echo($active_page == 'peminjaman') ? 'active' : ''; ?>">
            <a href="<?php echo BASE_URL; ?>pages/peminjaman.php"><i class="fas fa-hand-holding me-2"></i> Peminjaman</a>
        </li>
        <li class="<?php echo($active_page == 'pengembalian') ? 'active' : ''; ?>">
            <a href="<?php echo BASE_URL; ?>pages/pengembalian.php"><i class="fas fa-undo me-2"></i> Pengembalian</a>
        </li>
        
        <?php if ($_SESSION['role_id'] == 1 || $_SESSION['role_id'] == 2): ?>
        <li class="header text-muted px-4 py-2 mt-4">Laporan & Audit</li>
        <li class="<?php echo($active_page == 'laporan') ? 'active' : ''; ?>">
            <a href="<?php echo BASE_URL; ?>pages/laporan.php"><i class="fas fa-file-alt me-2"></i> Laporan</a>
        </li>
        <li class="<?php echo($active_page == 'log') ? 'active' : ''; ?>">
            <a href="<?php echo BASE_URL; ?>pages/log.php"><i class="fas fa-history me-2"></i> Log Aktivitas</a>
        </li>
        <?php
endif; ?>

        <?php if ($_SESSION['role_id'] == 1): ?>
        <li class="header text-muted px-4 py-2 mt-4">Pengaturan</li>
        <li class="<?php echo($active_page == 'users') ? 'active' : ''; ?>">
            <a href="<?php echo BASE_URL; ?>pages/users.php"><i class="fas fa-users-cog me-2"></i> Manajemen User</a>
        </li>
        <?php
endif; ?>
    </ul>

    <div class="mt-auto p-4">
        <div class="bg-white bg-opacity-10 rounded p-3 text-center">
            <small class="d-block text-white-50">Logged in as:</small>
            <span class="d-block fw-bold text-white small"><?php echo $_SESSION['username']; ?></span>
        </div>
    </div>
</nav>
