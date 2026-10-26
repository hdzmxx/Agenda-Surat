<?php

namespace App\Filters;

use CodeIgniter\Filters\FilterInterface;
use CodeIgniter\HTTP\RequestInterface;
use CodeIgniter\HTTP\ResponseInterface;

class AuthFilter implements FilterInterface
{
    public function before(RequestInterface $request, $arguments = null)
    {
        if (! session()->get('uid')) {
            return redirect()->to(site_url('login'));
        }

        if (session('role') === 'guru') {
            $path = trim($request->getUri()->getPath(), '/');
            $path = preg_replace('#^index\.php/?#', '', $path);
            $first = explode('/', $path)[0] ?? '';
            if (! in_array($first, ['', 'akun', 'disposisi'], true)) {
                return redirect()->to(site_url('disposisi'))->with('error', 'Akun guru hanya dapat melihat surat yang didisposisikan kepadanya.');
            }
        }
    }

    public function after(RequestInterface $request, ResponseInterface $response, $arguments = null)
    {
    }
}
