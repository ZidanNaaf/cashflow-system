<?php

namespace App\Libraries;

use App\Models\UserModel;
use CodeIgniter\Cookie\Cookie;
use CodeIgniter\HTTP\RequestInterface;
use CodeIgniter\HTTP\ResponseInterface;

class RememberMe
{
    private const COOKIE_NAME = 'cashflow_remember';
    private const LIFETIME = 60 * 60 * 24 * 30;

    public function loginFromCookie(RequestInterface $request, ResponseInterface $response): bool
    {
        $cookie = (string) ($request->getCookie(self::COOKIE_NAME) ?? '');
        if ($cookie === '' || ! str_contains($cookie, ':')) {
            return false;
        }

        [$userId, $token] = explode(':', $cookie, 2);
        if (! ctype_digit($userId) || $token === '') {
            return false;
        }

        $model = new UserModel();
        $user  = $model->find((int) $userId);

        if (! $user || ! (bool) $user['is_active'] || empty($user['remember_token']) || empty($user['remember_expires_at'])) {
            $this->clearCookie($response);
            return false;
        }

        if (strtotime($user['remember_expires_at']) < time()) {
            $model->update($user['id'], ['remember_token' => null, 'remember_expires_at' => null]);
            $this->clearCookie($response);
            return false;
        }

        if (! hash_equals($user['remember_token'], hash('sha256', $token))) {
            $this->clearCookie($response);
            return false;
        }

        session()->regenerate();
        session()->set([
            'user_id'   => (int) $user['id'],
            'user_name' => $user['name'],
            'user_role' => $user['role'],
        ]);

        $this->rememberUser($user, $response);

        return true;
    }

    public function rememberUser(array $user, ResponseInterface $response): void
    {
        $token   = bin2hex(random_bytes(32));
        $expires = time() + self::LIFETIME;

        (new UserModel())->update($user['id'], [
            'remember_token'      => hash('sha256', $token),
            'remember_expires_at' => date('Y-m-d H:i:s', $expires),
        ]);

        $response->setCookie([
            'name'     => self::COOKIE_NAME,
            'value'    => $user['id'] . ':' . $token,
            'expire'   => self::LIFETIME,
            'path'     => '/',
            'httponly' => true,
            'samesite' => 'Lax',
        ]);
    }

    public function forget(ResponseInterface $response, ?int $userId = null, ?RequestInterface $request = null): void
    {
        if ($userId === null && $request !== null) {
            $userId = $this->userIdFromCookie($request);
        }

        if ($userId !== null) {
            (new UserModel())->update($userId, [
                'remember_token'      => null,
                'remember_expires_at' => null,
            ]);
        }

        $this->clearCookie($response);
    }

    private function userIdFromCookie(RequestInterface $request): ?int
    {
        $cookie = (string) ($request->getCookie(self::COOKIE_NAME) ?? '');
        if ($cookie === '' || ! str_contains($cookie, ':')) {
            return null;
        }

        [$userId] = explode(':', $cookie, 2);

        return ctype_digit($userId) ? (int) $userId : null;
    }

    private function clearCookie(ResponseInterface $response): void
    {
        $response->setCookie(new Cookie(self::COOKIE_NAME, '', [
            'expires'  => time() - 3600,
            'path'     => '/',
            'httponly' => true,
            'samesite' => 'Lax',
        ]));
    }
}
