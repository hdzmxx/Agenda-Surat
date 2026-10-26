<?php

namespace App\Controllers;

use App\Models\UserModel;
use CodeIgniter\Exceptions\PageNotFoundException;

class Users extends BaseController
{
    protected $helpers = ['form', 'tanggal'];

    private function admin(): void
    {
        if (session()->get('role') !== 'admin') {
            throw PageNotFoundException::forPageNotFound();
        }
    }

    private function user(int $id): array
    {
        $u = (new UserModel())->find($id);
        if (! $u) {
            throw PageNotFoundException::forPageNotFound();
        }

        return $u;
    }

    public function index()
    {
        $this->admin();

        $rows = (new UserModel())
            ->select('users.*, guru.jabatan')
            ->join('guru', 'guru.id = users.guru_id', 'left')
            ->orderBy('users.nama')
            ->findAll();

        return view('users/index', ['title' => 'Pengguna', 'rows' => $rows]);
    }

    public function baru()
    {
        $this->admin();

        return view('users/form', ['title' => 'Tambah pengguna', 'row' => null, 'action' => site_url('users')]);
    }

    public function ubah(int $id)
    {
        $this->admin();
        $user = $this->user($id);
        if (! empty($user['guru_id'])) {
            return redirect()->to(site_url('users'))->with('error', 'Akun guru dikelola melalui Data guru; username dan password awal mengikuti NIP.');
        }

        return view('users/form', ['title' => 'Ubah pengguna', 'row' => $user, 'action' => site_url("users/$id")]);
    }

    public function simpan()
    {
        $this->admin();
        $rules = [
            'nama'     => 'required|max_length[100]',
            'username' => 'required|alpha_numeric|max_length[50]|is_unique[users.username]',
            'password' => 'required|min_length[6]',
            'role'     => 'required|in_list[admin,staf]',
        ];
        if (! $this->validate($rules)) {
            return redirect()->back()->withInput()->with('errors', $this->validator->getErrors());
        }
        (new UserModel())->insert([
            'nama'     => trim((string) $this->request->getPost('nama')),
            'username' => $this->request->getPost('username'),
            'password' => password_hash((string) $this->request->getPost('password'), PASSWORD_DEFAULT),
            'role'     => $this->request->getPost('role'),
        ]);

        return redirect()->to(site_url('users'))->with('ok', 'Pengguna ditambahkan.');
    }

    public function perbarui(int $id)
    {
        $this->admin();
        if (! empty($this->user($id)['guru_id'])) {
            return redirect()->to(site_url('users'))->with('error', 'Akun guru dikelola melalui Data guru.');
        }
        $rules = [
            'nama'     => 'required|max_length[100]',
            'username' => "required|alpha_numeric|max_length[50]|is_unique[users.username,id,$id]",
            'password' => 'permit_empty|min_length[6]',
            'role'     => 'required|in_list[admin,staf]',
        ];
        if (! $this->validate($rules)) {
            return redirect()->back()->withInput()->with('errors', $this->validator->getErrors());
        }
        $d = [
            'nama'     => trim((string) $this->request->getPost('nama')),
            'username' => $this->request->getPost('username'),
            'role'     => $this->request->getPost('role'),
        ];
        if ((string) $this->request->getPost('password') !== '') {
            $d['password'] = password_hash((string) $this->request->getPost('password'), PASSWORD_DEFAULT);
        }
        if ($id === (int) session()->get('uid')) {
            $d['role'] = 'admin';
        }
        (new UserModel())->update($id, $d);

        return redirect()->to(site_url('users'))->with('ok', 'Pengguna diperbarui.');
    }

    public function hapus(int $id)
    {
        $this->admin();
        if ($id === (int) session()->get('uid')) {
            return redirect()->to(site_url('users'))->with('error', 'Anda tidak dapat menghapus akun sendiri.');
        }
        if (! empty($this->user($id)['guru_id'])) {
            return redirect()->to(site_url('users'))->with('error', 'Hapus data guru melalui Data guru untuk menghapus akun terkait.');
        }
        (new UserModel())->delete($id);

        return redirect()->to(site_url('users'))->with('ok', 'Pengguna dihapus.');
    }
}
