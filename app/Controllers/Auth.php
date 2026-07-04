<?php

namespace App\Controllers;

use App\Libraries\RememberMe;
use App\Models\SettingModel;
use App\Models\UserModel;

class Auth extends BaseController
{
    public function login()
    {
        if (session()->get('user_id')) {
            return redirect()->to('/dashboard');
        }

        if ((new RememberMe())->loginFromCookie($this->request, $this->response)) {
            return redirect()->to('/dashboard');
        }

        $setting = (new SettingModel())->first() ?? [];

        return view('auth/login', [
            'setting' => $this->settingPayload($setting),
        ]);
    }

    public function attempt()
    {
        $email    = trim((string) $this->request->getPost('email'));
        $password = (string) $this->request->getPost('password');
        $key      = 'login-' . sha1($this->request->getIPAddress() . '|' . strtolower($email));

        if (! service('throttler')->check($key, 5, 60)) {
            return redirect()->back()->withInput()->with('error', 'Terlalu banyak percobaan login. Coba lagi beberapa saat.');
        }

        $user     = (new UserModel())->where('email', $email)->first();

        if (! $user || ! password_verify($password, $user['password_hash']) || ! (bool) $user['is_active']) {
            return redirect()->back()->withInput()->with('error', 'Email atau password tidak valid.');
        }

        session()->regenerate();
        session()->set([
            'user_id'   => (int) $user['id'],
            'user_name' => $user['name'],
            'user_role' => $user['role'],
        ]);

        $redirect = redirect()->to('/dashboard');
        if ($this->request->getPost('remember')) {
            (new RememberMe())->rememberUser($user, $redirect);
        }

        return $redirect;
    }

    public function logout()
    {
        $userId = session()->get('user_id') ? (int) session()->get('user_id') : null;
        session()->destroy();

        $redirect = redirect()->to('/login')->with('success', 'Kamu sudah logout.');
        (new RememberMe())->forget($redirect, $userId, $this->request);

        return $redirect;
    }

    private function settingPayload(array $setting): array
    {
        $logoPath = $setting['logo_path'] ?? null;

        return [
            'app_name'  => $setting['app_name'] ?? 'Cashflow',
            'currency'  => $setting['currency'] ?? 'Rp',
            'logo_path' => $logoPath,
            'logo_url'  => $logoPath ? base_url($logoPath) : null,
        ];
    }
}
