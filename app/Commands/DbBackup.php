<?php

namespace App\Commands;

use App\Libraries\DbBackup as Pencadangan;
use CodeIgniter\CLI\BaseCommand;
use CodeIgniter\CLI\CLI;

/**
 * Jalankan: php spark db:backup
 * Cocok untuk Windows Task Scheduler atau cron agar cadangan dibuat tepat waktu.
 */
class DbBackup extends BaseCommand
{
    protected $group       = 'Database';
    protected $name        = 'db:backup';
    protected $description = 'Membuat cadangan database ke folder writable/backups.';
    protected $usage       = 'db:backup';

    public function run(array $params)
    {
        try {
            $nama = Pencadangan::buat('otomatis');
            Pencadangan::bersihkan();
            CLI::write('Cadangan dibuat: ' . $nama, 'green');
        } catch (\Throwable $e) {
            CLI::error('Gagal: ' . $e->getMessage());

            return EXIT_ERROR;
        }

        return EXIT_SUCCESS;
    }
}
