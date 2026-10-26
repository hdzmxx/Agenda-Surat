<?php

namespace App\Models;

use CodeIgniter\Model;

class DaftarHadirModel extends Model
{
    protected $table         = 'daftar_hadir';
    protected $primaryKey    = 'id';
    protected $returnType    = 'array';
    protected $useTimestamps = true;
    protected $allowedFields = ['judul', 'tema', 'tanggal', 'kota', 'jabatan', 'nama', 'nip', 'data', 'created_by'];
}
