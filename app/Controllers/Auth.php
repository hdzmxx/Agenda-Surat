<?php

namespace App\Controllers;

use App\Models\UserModel;
use App\Models\GuruModel;
use CodeIgniter\Files\File;
use CodeIgniter\Exceptions\PageNotFoundException;

class Auth extends BaseController
{
    protected $helpers = ['form', 'tanggal'];

    public function login()
    {
        if (session()->get('uid')) {
            return redirect()->to(site_url('/'));
        }

        return view('auth/login');
    }

    public function attempt()
    {
        $user = (new UserModel())->where('username', trim((string) $this->request->getPost('username')))->first();
        if (! $user || ! password_verify((string) $this->request->getPost('password'), $user['password'])) {
            return redirect()->back()->withInput()->with('error', 'Username atau password salah.');
        }
        $guru = ! empty($user['guru_id']) ? (new GuruModel())->find($user['guru_id']) : null;
        $jabatan = trim((string) ($guru['jabatan'] ?? ''));
        session()->regenerate();
        session()->set([
            'uid' => $user['id'],
            'nama' => $user['nama'],
            'role' => $user['role'],
            'jabatan' => $jabatan !== '' ? $jabatan : null,
            'profile_photo' => $user['profile_photo'] ?? null,
        ]);

        return redirect()->to(site_url('/'));
    }

    public function logout()
    {
        session()->destroy();

        return redirect()->to(site_url('login'));
    }

    public function akun()
    {
        return view('auth/akun', [
            'title' => 'Akun saya',
            'user' => (new UserModel())->find(session()->get('uid')),
        ]);
    }

    public function foto()
    {
        $user = (new UserModel())->find(session()->get('uid'));
        if (! $user || empty($user['profile_photo'])
            || ! preg_match('/^profile-' . (int) session()->get('uid') . '-[a-zA-Z0-9]+\.(?:png|jpe?g)$/', $user['profile_photo'])) {
            throw PageNotFoundException::forPageNotFound();
        }
        $path = WRITEPATH . 'uploads/profile/' . $user['profile_photo'];
        if (! is_file($path)) {
            throw PageNotFoundException::forPageNotFound();
        }
        $file = new File($path);

        return $this->response
            ->setHeader('Content-Type', $file->getMimeType())
            ->setHeader('Content-Disposition', 'inline; filename="' . addslashes($user['profile_photo']) . '"')
            ->setHeader('X-Content-Type-Options', 'nosniff')
            ->setBody(file_get_contents($path));
    }

    public function simpanFoto()
    {
        $userId = (int) session()->get('uid');
        $user = (new UserModel())->find($userId);
        if (! $user) {
            return redirect()->to(site_url('login'));
        }
        if (! $this->validate([
            'foto' => 'uploaded[foto]|max_size[foto,2048]|ext_in[foto,png,jpg,jpeg]|mime_in[foto,image/png,image/jpeg]',
        ])) {
            return redirect()->back()->with('errors', $this->validator->getErrors());
        }

        $file = $this->request->getFile('foto');
        $extension = match ($file->getMimeType()) {
            'image/png' => 'png',
            'image/jpeg' => 'jpg',
            default => null,
        };
        if ($extension === null) {
            return redirect()->back()->with('error', 'Foto profil harus berupa gambar PNG atau JPG.');
        }

        $directory = WRITEPATH . 'uploads/profile';
        if (! is_dir($directory) && ! mkdir($directory, 0750, true) && ! is_dir($directory)) {
            return redirect()->back()->with('error', 'Folder penyimpanan foto profil tidak dapat dibuat.');
        }
        $name = 'profile-' . $userId . '-' . bin2hex(random_bytes(12)) . '.' . $extension;
        try {
            $file->move($directory, $name);
        } catch (\Throwable $e) {
            log_message('error', 'Gagal menyimpan foto profil pengguna {id}: {message}', ['id' => $userId, 'message' => $e->getMessage()]);

            return redirect()->back()->with('error', 'Gagal menyimpan foto profil.');
        }

        $model = new UserModel();
        if (! $model->update($userId, ['profile_photo' => $name])) {
            $this->hapusFoto($directory . DIRECTORY_SEPARATOR . $name);
            log_message('error', 'Gagal memperbarui foto profil pengguna {id}.', ['id' => $userId]);

            return redirect()->back()->with('error', 'Foto berhasil diunggah tetapi gagal ditautkan ke akun. Silakan coba lagi.');
        }
        session()->set('profile_photo', $name);
        if (! empty($user['profile_photo'])
            && preg_match('/^profile-' . $userId . '-[a-zA-Z0-9]+\.(?:png|jpe?g)$/', $user['profile_photo'])) {
            $this->hapusFoto($directory . DIRECTORY_SEPARATOR . $user['profile_photo']);
        }

        return redirect()->to(site_url('akun'))->with('ok', 'Foto profil berhasil diperbarui.');
    }

    private function hapusFoto(string $path): void
    {
        if (is_file($path) && ! unlink($path)) {
            log_message('error', 'Gagal membersihkan foto profil {path}.', ['path' => basename($path)]);
        }
    }

    public function simpanAkun()
    {
        $rules = [
            'password_lama' => 'required',
            'password_baru' => 'required|min_length[6]',
            'konfirmasi'    => 'required|matches[password_baru]',
        ];
        if (! $this->validate($rules)) {
            return redirect()->back()->with('errors', $this->validator->getErrors());
        }
        $m    = new UserModel();
        $user = $m->find(session()->get('uid'));
        if (! $user || ! password_verify((string) $this->request->getPost('password_lama'), $user['password'])) {
            return redirect()->back()->with('error', 'Password lama salah.');
        }
        $m->update($user['id'], ['password' => password_hash((string) $this->request->getPost('password_baru'), PASSWORD_DEFAULT)]);

        return redirect()->to(site_url('akun'))->with('ok', 'Password berhasil diganti.');
    }
}
