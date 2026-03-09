-- ============================================================
-- DATABASE SETUP
-- ============================================================
-- LOKAL (XAMPP): Buat database 'inventory_ukk' lalu jalankan script ini.
-- AIVEN (CLOUD): Langsung jalankan script ini di dalam database 'defaultdb'.
-- ============================================================

SET FOREIGN_KEY_CHECKS = 0;

DROP TABLE IF EXISTS log_aktivitas;
DROP TABLE IF EXISTS pengembalian;
DROP TABLE IF EXISTS detail_peminjaman;
DROP TABLE IF EXISTS peminjaman;
DROP TABLE IF EXISTS alat;
DROP TABLE IF EXISTS kategori;
DROP TABLE IF EXISTS users;
DROP TABLE IF EXISTS roles;

DROP TRIGGER IF EXISTS after_approve_borrowing;
DROP TRIGGER IF EXISTS after_return_tool;
DROP FUNCTION IF EXISTS hitung_denda;
DROP PROCEDURE IF EXISTS sp_peminjaman;

SET FOREIGN_KEY_CHECKS = 1;

-- ============================================================
-- TABLES
-- ============================================================

-- Roles Table
CREATE TABLE IF NOT EXISTS roles (
    id_role INT PRIMARY KEY AUTO_INCREMENT,
    nama_role VARCHAR(50) NOT NULL
);

-- Users Table
CREATE TABLE IF NOT EXISTS users (
    id_user INT PRIMARY KEY AUTO_INCREMENT,
    username VARCHAR(50) UNIQUE NOT NULL,
    password VARCHAR(255) NOT NULL,
    nama_lengkap VARCHAR(100) NOT NULL,
    id_role INT,
    FOREIGN KEY (id_role) REFERENCES roles(id_role) ON DELETE SET NULL
);

-- Categories Table
CREATE TABLE IF NOT EXISTS kategori (
    id_kategori INT PRIMARY KEY AUTO_INCREMENT,
    nama_kategori VARCHAR(50) NOT NULL
);

-- Tools Table
CREATE TABLE IF NOT EXISTS alat (
    id_alat INT PRIMARY KEY AUTO_INCREMENT,
    nama_alat VARCHAR(100) NOT NULL,
    id_kategori INT,
    spesifikasi TEXT,
    stok INT DEFAULT 0,
    FOREIGN KEY (id_kategori) REFERENCES kategori(id_kategori) ON DELETE SET NULL
);

-- Borrowing Table
CREATE TABLE IF NOT EXISTS peminjaman (
    id_peminjaman INT PRIMARY KEY AUTO_INCREMENT,
    id_user INT,
    tgl_pinjam DATE NOT NULL,
    tgl_kembali_seharusnya DATE NOT NULL,
    status ENUM('pending', 'approved', 'rejected', 'returned') DEFAULT 'pending',
    id_petugas INT NULL,
    FOREIGN KEY (id_user) REFERENCES users(id_user),
    FOREIGN KEY (id_petugas) REFERENCES users(id_user)
);

-- Borrowing Detail Table
CREATE TABLE IF NOT EXISTS detail_peminjaman (
    id_detail INT PRIMARY KEY AUTO_INCREMENT,
    id_peminjaman INT,
    id_alat INT,
    jumlah INT NOT NULL,
    FOREIGN KEY (id_peminjaman) REFERENCES peminjaman(id_peminjaman) ON DELETE CASCADE,
    FOREIGN KEY (id_alat) REFERENCES alat(id_alat)
);

-- Return Table
CREATE TABLE IF NOT EXISTS pengembalian (
    id_pengembalian INT PRIMARY KEY AUTO_INCREMENT,
    id_peminjaman INT UNIQUE,
    tgl_kembali DATE NOT NULL,
    denda DECIMAL(10,2) DEFAULT 0,
    FOREIGN KEY (id_peminjaman) REFERENCES peminjaman(id_peminjaman)
);

-- Activity Log Table
CREATE TABLE IF NOT EXISTS log_aktivitas (
    id_log INT PRIMARY KEY AUTO_INCREMENT,
    id_user INT,
    aktivitas TEXT,
    waktu TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    FOREIGN KEY (id_user) REFERENCES users(id_user)
);

-- ============================================================
-- TRIGGERS
-- ============================================================

-- Trigger to decrease stock when a tool is borrowed (status 'approved')
DELIMITER //
CREATE TRIGGER after_approve_borrowing
AFTER UPDATE ON peminjaman
FOR EACH ROW
BEGIN
    IF OLD.status = 'pending' AND NEW.status = 'approved' THEN
        UPDATE alat a
        JOIN detail_peminjaman dp ON a.id_alat = dp.id_alat
        SET a.stok = a.stok - dp.jumlah
        WHERE dp.id_peminjaman = NEW.id_peminjaman;
    END IF;
END //
DELIMITER ;

-- Trigger to increase stock when a tool is returned
DELIMITER //
CREATE TRIGGER after_return_tool
AFTER INSERT ON pengembalian
FOR EACH ROW
BEGIN
    UPDATE alat a
    JOIN detail_peminjaman dp ON a.id_alat = dp.id_alat
    SET a.stok = a.stok + dp.jumlah
    WHERE dp.id_peminjaman = NEW.id_peminjaman;

    UPDATE peminjaman SET status = 'returned' WHERE id_peminjaman = NEW.id_peminjaman;
END //
DELIMITER ;

-- ============================================================
-- FUNCTIONS
-- ============================================================

-- Function to calculate late fee
DELIMITER //
CREATE FUNCTION hitung_denda(tgl_kembali_seharusnya DATE, tgl_aktual DATE)
RETURNS DECIMAL(10,2)
DETERMINISTIC
BEGIN
    DECLARE selisih INT;
    DECLARE total_denda DECIMAL(10,2);
    SET selisih = DATEDIFF(tgl_aktual, tgl_kembali_seharusnya);
    IF selisih > 0 THEN
        SET total_denda = selisih * 5000; -- Rp5.000 per hari keterlambatan
    ELSE
        SET total_denda = 0;
    END IF;
    RETURN total_denda;
END //
DELIMITER ;

-- ============================================================
-- STORED PROCEDURES
-- ============================================================

-- Procedure for a complete borrowing transaction
DELIMITER //
CREATE PROCEDURE sp_peminjaman(
    IN p_id_user INT,
    IN p_tgl_pinjam DATE,
    IN p_tgl_kembali_seharusnya DATE,
    IN p_id_alat INT,
    IN p_jumlah INT
)
BEGIN
    DECLARE v_id_peminjaman INT;

    START TRANSACTION;

    INSERT INTO peminjaman (id_user, tgl_pinjam, tgl_kembali_seharusnya, status)
    VALUES (p_id_user, p_tgl_pinjam, p_tgl_kembali_seharusnya, 'pending');

    SET v_id_peminjaman = LAST_INSERT_ID();

    INSERT INTO detail_peminjaman (id_peminjaman, id_alat, jumlah)
    VALUES (v_id_peminjaman, p_id_alat, p_jumlah);

    COMMIT;
END //
DELIMITER ;

-- ============================================================
-- SEED DATA
-- ============================================================
INSERT INTO roles (nama_role) VALUES ('Admin'), ('Petugas'), ('Peminjam');

-- Default Admin (password: admin123)
INSERT INTO users (username, password, nama_lengkap, id_role) VALUES
('admin', '$2y$10$92IXUNpkjO0rOQ5byMi.Ye4oKoEa3Ro9llC/.og/at2.uheWG/igi', 'Super Admin', 1);
