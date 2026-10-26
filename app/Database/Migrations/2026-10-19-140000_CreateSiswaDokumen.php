<?php

namespace App\Database\Migrations;

use CodeIgniter\Database\Migration;

/**
 * Tabel siswa (data siswa) dan dokumen (surat yang dibuat lewat menu Buat Surat).
 * IF NOT EXISTS: aman dijalankan di database lama yang tabelnya sudah ada.
 */
class CreateSiswaDokumen extends Migration
{
    public function up()
    {
        $this->db->query("CREATE TABLE IF NOT EXISTS siswa (
            id INT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
            nama VARCHAR(150) NOT NULL,
            nisn VARCHAR(20) NULL,
            nipd VARCHAR(20) NULL COMMENT 'nomor induk siswa',
            kelas VARCHAR(20) NULL,
            jk CHAR(1) NULL COMMENT 'L atau P',
            tempat_lahir VARCHAR(100) NULL,
            tgl_lahir DATE NULL,
            alamat VARCHAR(255) NULL,
            created_at DATETIME NULL,
            updated_at DATETIME NULL,
            KEY idx_nisn (nisn),
            KEY idx_kelas (kelas, nama)
        ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci");

        $this->db->query("CREATE TABLE IF NOT EXISTS dokumen (
            id INT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
            jenis VARCHAR(30) NOT NULL COMMENT 'undangan, narasumber, sk_siswa, tugas, dst.',
            surat_id INT UNSIGNED NULL COMMENT 'baris agenda surat keluar',
            data LONGTEXT NOT NULL COMMENT 'isi surat (JSON)',
            created_by INT UNSIGNED NULL,
            created_at DATETIME NULL,
            updated_at DATETIME NULL,
            KEY idx_jenis (jenis),
            KEY fk_dok_surat (surat_id),
            CONSTRAINT fk_dok_surat FOREIGN KEY (surat_id) REFERENCES surat (id) ON DELETE SET NULL
        ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci");
    }

    public function down()
    {
        $this->db->query('DROP TABLE IF EXISTS dokumen');
        $this->db->query('DROP TABLE IF EXISTS siswa');
    }
}
