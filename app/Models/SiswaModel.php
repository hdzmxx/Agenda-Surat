<?php

namespace App\Models;

use CodeIgniter\Model;

class SiswaModel extends Model
{
    protected $table         = 'siswa';
    protected $primaryKey    = 'id';
    protected $returnType    = 'array';
    protected $useTimestamps = true;
    protected $allowedFields = ['nama', 'nisn', 'nipd', 'kelas', 'jk', 'tempat_lahir', 'tgl_lahir', 'alamat'];
}
