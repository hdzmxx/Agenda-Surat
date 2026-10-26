<?php

namespace App\Database\Migrations;

use CodeIgniter\Database\Migration;

class AddTeacherDispositions extends Migration
{
    public function up()
    {
        $db = $this->db;
        if (! $db->tableExists('guru')) {
            throw new \RuntimeException('Tabel guru harus tersedia sebelum membuat akun guru.');
        }

        $db->query("ALTER TABLE users MODIFY role ENUM('admin','staf','guru') NOT NULL DEFAULT 'staf'");
        if (! $db->fieldExists('guru_id', 'users')) {
            $db->query('ALTER TABLE users ADD guru_id INT UNSIGNED NULL UNIQUE, ADD CONSTRAINT fk_users_guru FOREIGN KEY (guru_id) REFERENCES guru(id) ON DELETE SET NULL ON UPDATE CASCADE');
        }
        if ($db->fieldExists('disposisi', 'surat')) {
            $field = $db->getFieldData('surat');
            foreach ($field as $column) {
                if ($column->name === 'disposisi' && strtolower($column->type) !== 'text') {
                    $db->query('ALTER TABLE surat MODIFY disposisi TEXT NULL');
                    break;
                }
            }
        }
        $db->query("CREATE TABLE IF NOT EXISTS surat_disposisi (
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
        ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci");

        $guruRows = $db->table('guru')->get()->getResultArray();
        foreach ($guruRows as $guru) {
            $nip = trim((string) ($guru['nip'] ?? ''));
            if ($nip === '') {
                log_message('warning', 'Akun guru tidak dibuat untuk data guru {id}: NIP belum diisi.', ['id' => $guru['id']]);
                continue;
            }
            if (strlen($nip) > 50 || ! preg_match('/^[a-zA-Z0-9]+$/', $nip)) {
                log_message('warning', 'Akun guru tidak dibuat untuk data guru {id}: NIP tidak valid.', ['id' => $guru['id']]);
                continue;
            }

            $linked = $db->table('users')->where('guru_id', $guru['id'])->get()->getRowArray();
            if ($linked) {
                continue;
            }
            $existing = $db->table('users')->where('username', $nip)->get()->getRowArray();
            if ($existing) {
                log_message('warning', 'Akun guru tidak dibuat untuk data guru {id}: username NIP sudah digunakan.', ['id' => $guru['id']]);
                continue;
            }

            $db->table('users')->insert([
                'nama'       => mb_substr(trim((string) $guru['nama']), 0, 100),
                'username'   => $nip,
                'password'   => password_hash($nip, PASSWORD_DEFAULT),
                'role'       => 'guru',
                'guru_id'    => $guru['id'],
                'created_at' => date('Y-m-d H:i:s'),
                'updated_at' => date('Y-m-d H:i:s'),
            ]);
        }
    }

    public function down()
    {
        $this->db->query('DROP TABLE IF EXISTS surat_disposisi');
        if ($this->db->fieldExists('guru_id', 'users')) {
            $this->db->query('DELETE FROM users WHERE role = "guru" AND guru_id IS NOT NULL');
            $this->db->query('ALTER TABLE users DROP FOREIGN KEY fk_users_guru, DROP INDEX guru_id, DROP COLUMN guru_id');
        }
        $this->db->query("ALTER TABLE users MODIFY role ENUM('admin','staf') NOT NULL DEFAULT 'staf'");
    }
}
