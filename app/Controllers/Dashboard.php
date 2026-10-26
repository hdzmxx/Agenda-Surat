<?php

namespace App\Controllers;

use App\Models\SuratModel;

class Dashboard extends BaseController
{
    protected $helpers = ['form', 'tanggal'];

    public function index()
    {
        if (session('role') === 'guru') {
            return redirect()->to(site_url('disposisi'));
        }

        // Cadangan otomatis bila jadwal (Cadangan database) sudah jatuh tempo; kegagalan tidak mengganggu halaman.
        try {
            \App\Libraries\DbBackup::autoJikaJatuhTempo();
        } catch (\Throwable $e) {
            log_message('error', 'Cadangan otomatis gagal: ' . $e->getMessage());
        }

        $m     = new SuratModel();
        $awal  = date('Y-m-01');
        $akhir = date('Y-m-t');
        $data  = [
            'title'        => 'Dashboard',
            'total_masuk'  => $m->where('arah', 'masuk')->countAllResults(),
            'total_keluar' => $m->where('arah', 'keluar')->countAllResults(),
            'belum'        => $m->where(['arah' => 'masuk', 'status' => 'Belum diproses'])->countAllResults(),
            'bln_masuk'    => $m->where('arah', 'masuk')->where('tgl_agenda >=', $awal)->where('tgl_agenda <=', $akhir)->countAllResults(),
            'bln_keluar'   => $m->where('arah', 'keluar')->where('tgl_agenda >=', $awal)->where('tgl_agenda <=', $akhir)->countAllResults(),
        ];
        foreach (['masuk', 'keluar'] as $a) {
            $data['terbaru_' . $a] = $m->where('arah', $a)->orderBy('tgl_agenda', 'DESC')->orderBy('id', 'DESC')->findAll(5);
        }

        return view('dashboard', $data);
    }
}
