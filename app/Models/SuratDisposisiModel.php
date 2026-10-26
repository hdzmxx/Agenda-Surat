<?php

namespace App\Models;

use CodeIgniter\Model;

class SuratDisposisiModel extends Model
{
    protected $table         = 'surat_disposisi';
    protected $primaryKey    = 'id';
    protected $returnType    = 'array';
    protected $allowedFields = ['surat_id', 'user_id', 'catatan', 'created_by'];
    protected $useTimestamps = true;

    public function untukGuru(int $userId): array
    {
        return $this->db->table('surat_disposisi d')
            ->select('d.id AS disposisi_id, d.catatan, d.created_at AS disposisi_at, s.*')
            ->join('surat s', 's.id = d.surat_id')
            ->where('d.user_id', $userId)
            ->orderBy('d.created_at', 'DESC')
            ->get()->getResultArray();
    }

    public function untukSurat(int $suratId): array
    {
        return $this->db->table('surat_disposisi d')
            ->select('d.id, d.surat_id, d.user_id, d.catatan, d.created_by, d.created_at, u.nama')
            ->join('users u', 'u.id = d.user_id')
            ->where('d.surat_id', $suratId)
            ->orderBy('u.nama')
            ->get()->getResultArray();
    }

    public function petaNama(array $suratIds): array
    {
        if ($suratIds === []) {
            return [];
        }

        $rows = $this->db->table('surat_disposisi d')
            ->select('d.surat_id, u.nama')
            ->join('users u', 'u.id = d.user_id')
            ->whereIn('d.surat_id', $suratIds)
            ->orderBy('u.nama')
            ->get()->getResultArray();
        $map = [];
        foreach ($rows as $row) {
            $map[$row['surat_id']][] = $row['nama'];
        }

        return $map;
    }
}
