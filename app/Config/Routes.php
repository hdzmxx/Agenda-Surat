<?php

use CodeIgniter\Router\RouteCollection;

/** @var RouteCollection $routes */
$routes->get('login', 'Auth::login');
$routes->post('login', 'Auth::attempt');
$routes->get('logout', 'Auth::logout');
$routes->get('website/logo', 'WebsiteSettings::logo');
$routes->get('website/favicon', 'WebsiteSettings::favicon');

$routes->group('', ['filter' => 'auth'], static function ($routes) {
    $routes->get('/', 'Dashboard::index');
    $routes->get('akun', 'Auth::akun');
    $routes->post('akun', 'Auth::simpanAkun');
    $routes->post('akun/foto', 'Auth::simpanFoto');
    $routes->get('akun/foto', 'Auth::foto');
    $routes->get('disposisi', 'Disposisi::index');
    $routes->get('disposisi/(:num)', 'Disposisi::lihat/$1');
    $routes->get('disposisi/(:num)/berkas', 'Disposisi::berkas/$1');
    $routes->get('kop/pengaturan', 'Kop::index');
    $routes->post('kop/pengaturan', 'Kop::upload');
    $routes->get('kop/logo', static fn () => redirect()->to(site_url('kop/pengaturan')));
    $routes->post('kop/logo', 'Kop::upload');
    $routes->get('website/pengaturan', 'WebsiteSettings::index');
    $routes->post('website/pengaturan', 'WebsiteSettings::simpan');
    $routes->get('daftar-hadir', 'DaftarHadir::index');
    $routes->get('daftar-hadir/baru', 'DaftarHadir::baru');
    $routes->post('daftar-hadir', 'DaftarHadir::simpan');
    $routes->get('daftar-hadir/(:num)/cetak', 'DaftarHadir::cetak/$1');
    $routes->get('daftar-hadir/(:num)/ubah', 'DaftarHadir::ubah/$1');
    $routes->post('daftar-hadir/(:num)', 'DaftarHadir::perbarui/$1');
    $routes->post('daftar-hadir/(:num)/manual', 'DaftarHadir::manual/$1');
    $routes->post('daftar-hadir/(:num)/hapus', 'DaftarHadir::hapus/$1');

    $routes->get('surat/(:segment)', 'Surat::index/$1');
    $routes->get('surat/(:segment)/baru', 'Surat::baru/$1');
    $routes->get('surat/(:segment)/cetak', 'Surat::cetak/$1');
    $routes->get('surat/(:segment)/nomor', 'Surat::nomor/$1');
    $routes->post('surat/(:segment)', 'Surat::simpan/$1');
    $routes->get('surat/(:segment)/(:num)', 'Surat::lihat/$1/$2');
    $routes->get('surat/(:segment)/(:num)/ubah', 'Surat::ubah/$1/$2');
    $routes->get('surat/(:segment)/(:num)/berkas', 'Surat::berkas/$1/$2');
    $routes->post('surat/(:segment)/(:num)', 'Surat::perbarui/$1/$2');
    $routes->post('surat/(:segment)/(:num)/hapus', 'Surat::hapus/$1/$2');

    $routes->get('rekap/sppd', 'Rekap::sppd');
    $routes->get('rekap/sppd/excel', 'Rekap::sppdExcel');

    $routes->get('dokumen', 'Dokumen::index');
    $routes->get('dokumen/baru/(:segment)', 'Dokumen::baru/$1');
    $routes->post('dokumen/baru/(:segment)', 'Dokumen::simpan/$1');
    $routes->get('dokumen/nomor', 'Dokumen::nomor');
    $routes->get('dokumen/(:num)/ubah', 'Dokumen::ubah/$1');
    $routes->get('dokumen/(:num)/cetak', 'Dokumen::cetak/$1');
    $routes->post('dokumen/(:num)', 'Dokumen::perbarui/$1');
    $routes->post('dokumen/(:num)/hapus', 'Dokumen::hapus/$1');

    $routes->get('data/(:segment)', 'Master::index/$1');
    $routes->get('data/(:segment)/baru', 'Master::baru/$1');
    $routes->get('data/(:segment)/template', 'Master::template/$1');
    $routes->get('data/(:segment)/import', 'Master::importForm/$1');
    $routes->post('data/(:segment)/import', 'Master::import/$1');
    $routes->post('data/(:segment)', 'Master::simpan/$1');
    $routes->get('data/(:segment)/(:num)/ubah', 'Master::ubah/$1/$2');
    $routes->post('data/(:segment)/(:num)', 'Master::perbarui/$1/$2');
    $routes->post('data/(:segment)/(:num)/hapus', 'Master::hapus/$1/$2');

    $routes->get('cadangan', 'Cadangan::index');
    $routes->post('cadangan/buat', 'Cadangan::buat');
    $routes->post('cadangan/pengaturan', 'Cadangan::pengaturan');
    $routes->get('cadangan/unduh/(:segment)', 'Cadangan::unduh/$1');
    $routes->post('cadangan/hapus/(:segment)', 'Cadangan::hapus/$1');
    $routes->get('cadangan/pulihkan/(:segment)', 'Cadangan::pulihkan/$1');
    $routes->post('cadangan/pulihkan/(:segment)', 'Cadangan::pulihkanProses/$1');
    $routes->post('cadangan/unggah', 'Cadangan::unggah');

    $routes->get('users', 'Users::index');
    $routes->get('users/baru', 'Users::baru');
    $routes->post('users', 'Users::simpan');
    $routes->get('users/(:num)/ubah', 'Users::ubah/$1');
    $routes->post('users/(:num)', 'Users::perbarui/$1');
    $routes->post('users/(:num)/hapus', 'Users::hapus/$1');
});
