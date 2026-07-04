<?php

namespace App\Controllers\Api;

use App\Controllers\BaseController;
use App\Models\SettingModel;

class Settings extends BaseController
{
    public function index()
    {
        return $this->response->setJSON(['data' => $this->settingPayload((new SettingModel())->first() ?? [])]);
    }

    public function update()
    {
        $input = $this->request->getJSON(true) ?: $this->request->getPost();
        $data  = [
            'app_name' => trim((string) ($input['app_name'] ?? 'Cashflow')),
            'currency' => trim((string) ($input['currency'] ?? 'Rp')),
        ];

        if ($data['app_name'] === '' || $data['currency'] === '') {
            return $this->response->setStatusCode(422)->setJSON(['message' => 'Nama aplikasi dan mata uang wajib diisi.']);
        }

        $model   = new SettingModel();
        $setting = $model->first();

        if ($setting) {
            $model->update($setting['id'], $data);
        } else {
            $model->insert($data);
        }

        return $this->response->setJSON([
            'message' => 'Setting berhasil disimpan.',
            'data'    => $this->settingPayload((new SettingModel())->first() ?? []),
        ]);
    }

    public function uploadLogo()
    {
        $file = $this->request->getFile('logo');

        if (! $file || ! $file->isValid()) {
            return $this->response->setStatusCode(422)->setJSON(['message' => 'File logo wajib dipilih.']);
        }

        if ($file->getSize() > 2 * 1024 * 1024) {
            return $this->response->setStatusCode(422)->setJSON(['message' => 'Ukuran logo maksimal 2 MB.']);
        }

        $allowedMimes = ['image/png', 'image/jpeg', 'image/webp', 'image/gif'];
        if (! in_array($file->getMimeType(), $allowedMimes, true)) {
            return $this->response->setStatusCode(422)->setJSON(['message' => 'Logo harus berupa PNG, JPG, WEBP, atau GIF.']);
        }

        if (@getimagesize($file->getTempName()) === false) {
            return $this->response->setStatusCode(422)->setJSON(['message' => 'File logo tidak valid.']);
        }

        $directory = FCPATH . 'uploads/logos';
        if (! is_dir($directory)) {
            mkdir($directory, 0755, true);
        }

        $model   = new SettingModel();
        $setting = $model->first();
        $extensions = [
            'image/png'  => 'png',
            'image/jpeg' => 'jpg',
            'image/webp' => 'webp',
            'image/gif'  => 'gif',
        ];
        $name = 'logo-' . bin2hex(random_bytes(8)) . '.' . $extensions[$file->getMimeType()];

        $file->move($directory, $name);

        $data = ['logo_path' => 'uploads/logos/' . $name];
        if ($setting) {
            $this->deleteLogoFile($setting['logo_path'] ?? null);
            $model->update($setting['id'], $data);
        } else {
            $model->insert([
                'app_name'  => 'Cashflow',
                'currency'  => 'Rp',
                'logo_path' => $data['logo_path'],
            ]);
        }

        return $this->response->setJSON([
            'message' => 'Logo berhasil diperbarui.',
            'data'    => $this->settingPayload($model->first() ?? []),
        ]);
    }

    public function deleteLogo()
    {
        $model   = new SettingModel();
        $setting = $model->first();

        if ($setting) {
            $this->deleteLogoFile($setting['logo_path'] ?? null);
            $model->update($setting['id'], ['logo_path' => null]);
        }

        return $this->response->setJSON([
            'message' => 'Logo berhasil dihapus.',
            'data'    => $this->settingPayload($model->first() ?? []),
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

    private function deleteLogoFile(?string $path): void
    {
        if (! $path) {
            return;
        }

        $base = realpath(FCPATH . 'uploads/logos');
        $file = realpath(FCPATH . $path);

        if ($base && $file && str_starts_with($file, $base) && is_file($file)) {
            unlink($file);
        }
    }
}
