<?php

namespace App\Filters;

use CodeIgniter\Filters\FilterInterface;
use CodeIgniter\HTTP\RequestInterface;
use CodeIgniter\HTTP\ResponseInterface;

class RoleFilter implements FilterInterface
{
    public function before(RequestInterface $request, $arguments = null)
    {
        $allowed = $arguments ?? [];
        $role    = session()->get('user_role');

        if (! in_array($role, $allowed, true)) {
            if ($request->isAJAX() || str_starts_with($request->getUri()->getPath(), 'api/')) {
                return service('response')->setStatusCode(403)->setJSON(['message' => 'Akses ditolak.']);
            }

            return redirect()->to('/dashboard')->with('error', 'Akses ditolak.');
        }
    }

    public function after(RequestInterface $request, ResponseInterface $response, $arguments = null)
    {
    }
}
