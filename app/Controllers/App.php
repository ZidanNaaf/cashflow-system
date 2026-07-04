<?php

namespace App\Controllers;

use App\Models\SettingModel;

class App extends BaseController
{
    public function index(): string
    {
        $setting = (new SettingModel())->first() ?? [];

        return view('app/index', [
            'user' => [
                'id'   => session()->get('user_id'),
                'name' => session()->get('user_name'),
                'role' => session()->get('user_role'),
            ],
            'setting' => $this->settingPayload($setting),
        ]);
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
