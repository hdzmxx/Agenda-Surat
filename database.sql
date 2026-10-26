-- Agenda Surat Masuk dan Keluar (CodeIgniter 4). Impor lewat phpMyAdmin atau: mysql -u root < database.sql
CREATE DATABASE IF NOT EXISTS xagenda CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci;
USE xagenda;

CREATE TABLE IF NOT EXISTS guru (
  id INT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
  nama VARCHAR(150) NOT NULL,
  nip VARCHAR(30) NULL,
  pangkat_gol VARCHAR(100) NULL,
  jabatan VARCHAR(100) NULL,
  urut INT NULL DEFAULT 999,
  created_at DATETIME NULL,
  updated_at DATETIME NULL,
  KEY idx_guru_nama (nama),
  KEY idx_guru_nip (nip)
) ENGINE=InnoDB;

CREATE TABLE IF NOT EXISTS users (
  id INT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
  nama VARCHAR(100) NOT NULL,
  username VARCHAR(50) NOT NULL UNIQUE,
  password VARCHAR(255) NOT NULL,
  role ENUM('admin','staf','guru') NOT NULL DEFAULT 'staf',
  guru_id INT UNSIGNED NULL UNIQUE,
  profile_photo VARCHAR(255) NULL,
  created_at DATETIME NULL,
  updated_at DATETIME NULL,
  CONSTRAINT fk_users_guru FOREIGN KEY (guru_id) REFERENCES guru(id) ON DELETE SET NULL ON UPDATE CASCADE
) ENGINE=InnoDB;

CREATE TABLE IF NOT EXISTS surat (
  id INT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
  arah ENUM('masuk','keluar') NOT NULL,
  tahun SMALLINT UNSIGNED NOT NULL,
  no_agenda INT UNSIGNED NOT NULL,
  tgl_agenda DATE NOT NULL COMMENT 'masuk: tanggal diterima; keluar: tanggal surat',
  tgl_surat DATE NULL COMMENT 'khusus masuk: tanggal tertera pada surat',
  nomor_surat VARCHAR(100) NOT NULL,
  pihak VARCHAR(150) NOT NULL COMMENT 'masuk: asal surat; keluar: tujuan surat',
  perihal VARCHAR(255) NOT NULL,
  disposisi TEXT NULL COMMENT 'khusus masuk: nama guru penerima disposisi',
  jenis VARCHAR(50) NULL COMMENT 'khusus keluar',
  penandatangan VARCHAR(100) NULL COMMENT 'khusus keluar',
  sifat ENUM('Biasa','Penting','Segera','Rahasia') NOT NULL DEFAULT 'Biasa',
  lampiran VARCHAR(100) NULL,
  status VARCHAR(30) NOT NULL,
  isi TEXT NULL,
  keterangan TEXT NULL,
  berkas VARCHAR(255) NULL COMMENT 'nama file di writable/uploads/surat',
  berkas_asli VARCHAR(255) NULL,
  created_by INT UNSIGNED NULL,
  created_at DATETIME NULL,
  updated_at DATETIME NULL,
  UNIQUE KEY uq_agenda (arah, tahun, no_agenda),
  KEY idx_tgl (arah, tgl_agenda),
  KEY idx_status (arah, status),
  CONSTRAINT fk_surat_user FOREIGN KEY (created_by) REFERENCES users(id) ON DELETE SET NULL
) ENGINE=InnoDB;

CREATE TABLE IF NOT EXISTS surat_disposisi (
  id INT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
  surat_id INT UNSIGNED NOT NULL,
  user_id INT UNSIGNED NOT NULL,
  catatan TEXT NULL,
  created_by INT UNSIGNED NULL,
  created_at DATETIME NULL,
  updated_at DATETIME NULL,
  UNIQUE KEY uq_surat_disposisi (surat_id, user_id),
  KEY idx_disposisi_user (user_id),
  CONSTRAINT fk_disposisi_surat FOREIGN KEY (surat_id) REFERENCES surat(id) ON DELETE CASCADE,
  CONSTRAINT fk_disposisi_user FOREIGN KEY (user_id) REFERENCES users(id) ON DELETE CASCADE,
  CONSTRAINT fk_disposisi_creator FOREIGN KEY (created_by) REFERENCES users(id) ON DELETE SET NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- Akun awal: username admin, password "password" (segera ganti setelah login)
INSERT INTO users (nama, username, password, role, created_at, updated_at) VALUES
('Administrator', 'admin', '$2y$10$92IXUNpkjO0rOQ5byMi.Ye4oKoEa3Ro9llC/.og/at2.uheWG/igi', 'admin', NOW(), NOW());
