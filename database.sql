-- Database: minyak_jelantah_db
-- Import file ini di phpMyAdmin / mysql client sebelum menjalankan aplikasi

CREATE DATABASE IF NOT EXISTS minyak_jelantah_db;
USE minyak_jelantah_db;

-- =========================================
-- Tabel users (menampung 3 role: user, pengepul, admin)
-- =========================================
CREATE TABLE IF NOT EXISTS users (
    id INT AUTO_INCREMENT PRIMARY KEY,
    nama VARCHAR(100) NOT NULL,
    email VARCHAR(100) NOT NULL UNIQUE,
    password VARCHAR(255) NOT NULL,
    no_hp VARCHAR(20) DEFAULT NULL,
    alamat TEXT DEFAULT NULL,
    role ENUM('user', 'pengepul', 'admin') NOT NULL DEFAULT 'user',
    saldo DECIMAL(12,2) NOT NULL DEFAULT 0,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP
) ENGINE=InnoDB;

-- =========================================
-- Tabel harga (harga per liter yang berlaku, diatur admin)
-- =========================================
CREATE TABLE IF NOT EXISTS harga (
    id INT AUTO_INCREMENT PRIMARY KEY,
    harga_per_liter DECIMAL(10,2) NOT NULL,
    updated_by INT DEFAULT NULL,
    updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
    FOREIGN KEY (updated_by) REFERENCES users(id) ON DELETE SET NULL
) ENGINE=InnoDB;

-- Harga awal default
INSERT INTO harga (harga_per_liter) VALUES (5000.00);

-- =========================================
-- Tabel setoran (pengajuan setor minyak jelantah dari user)
-- =========================================
CREATE TABLE IF NOT EXISTS setoran (
    id INT AUTO_INCREMENT PRIMARY KEY,
    user_id INT NOT NULL,
    pengepul_id INT DEFAULT NULL,
    perkiraan_liter DECIMAL(8,2) NOT NULL,
    liter_aktual DECIMAL(8,2) DEFAULT NULL,
    alamat_jemput TEXT NOT NULL,
    catatan TEXT DEFAULT NULL,
    harga_per_liter_saat_itu DECIMAL(10,2) DEFAULT NULL,
    total_saldo DECIMAL(12,2) DEFAULT NULL,
    status ENUM('menunggu', 'diproses', 'selesai', 'dibatalkan') NOT NULL DEFAULT 'menunggu',
    tanggal_ajukan TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    tanggal_selesai TIMESTAMP NULL DEFAULT NULL,
    FOREIGN KEY (user_id) REFERENCES users(id) ON DELETE CASCADE,
    FOREIGN KEY (pengepul_id) REFERENCES users(id) ON DELETE SET NULL
) ENGINE=InnoDB;

-- =========================================
-- Akun admin default (password: admin123 - GANTI setelah login pertama!)
-- Hash di bawah adalah hasil password_hash('admin123', PASSWORD_DEFAULT)
-- =========================================
INSERT INTO users (nama, email, password, role) VALUES
('Administrator', 'admin@jelantah.local', '$2b$12$97fyRjvEB.gnM1RxGXefXeTurmix67YSl/QT8UynR.9KuW6ZdwCcm', 'admin');
