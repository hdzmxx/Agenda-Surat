<?php

namespace App\Database\Migrations;

use CodeIgniter\Database\Migration;

class CreateDaftarHadir extends Migration
{
    public function up()
    {
        $this->db->query("CREATE TABLE IF NOT EXISTS daftar_hadir (
            id INT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
            judul VARCHAR(200) NOT NULL,
            tema VARCHAR(250) NOT NULL DEFAULT '',
            tanggal DATE NOT NULL,
            kota VARCHAR(100) NOT NULL,
            jabatan VARCHAR(100) NOT NULL,
            nama VARCHAR(150) NOT NULL,
            nip VARCHAR(30) NOT NULL DEFAULT '',
            data LONGTEXT NOT NULL,
            created_by INT UNSIGNED NULL,
            created_at DATETIME NULL,
            updated_at DATETIME NULL,
            KEY idx_daftar_hadir_tanggal (tanggal)
        ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci");
    }

    public function down()
    {
        $this->db->query('DROP TABLE IF EXISTS daftar_hadir');
    }
}
