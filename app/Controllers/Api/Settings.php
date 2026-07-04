<?php

namespace App\Controllers\Api;

use App\Controllers\BaseController;
use App\Models\SettingModel;
use Throwable;

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
        if (! (bool) ini_get('file_uploads')) {
            return $this->uploadError('Upload file sedang nonaktif di konfigurasi PHP.', 'Aktifkan file_uploads di php.ini/server panel.');
        }

        $file = $this->request->getFile('logo');

        if (! $file) {
            return $this->uploadError('File logo wajib dipilih.');
        }

        if (! $file->isValid()) {
            return $this->uploadError($this->uploadFailureMessage($file->getError()), $file->getErrorString());
        }

        if ($file->getSize() > 2 * 1024 * 1024) {
            return $this->uploadError('Ukuran logo maksimal 2 MB.', 'Ukuran file terdeteksi: ' . number_format($file->getSize() / 1024 / 1024, 2) . ' MB.');
        }

        $allowedMimes = ['image/png', 'image/jpeg', 'image/webp', 'image/gif'];
        if (! in_array($file->getMimeType(), $allowedMimes, true)) {
            return $this->uploadError('Logo harus berupa PNG, JPG, WEBP, atau GIF.', 'MIME terdeteksi: ' . ($file->getMimeType() ?: 'tidak diketahui') . '.');
        }

        if (@getimagesize($file->getTempName()) === false) {
            return $this->uploadError('File logo tidak valid.', 'File tidak terbaca sebagai gambar valid.');
        }

        $directory = FCPATH . 'uploads/logos';
        if (! is_dir($directory)) {
            if (! @mkdir($directory, 0755, true) && ! is_dir($directory)) {
                return $this->uploadError('Folder upload logo gagal dibuat.', 'Pastikan folder public/uploads bisa ditulis oleh web server.');
            }
        }

        if (! is_writable($directory)) {
            return $this->uploadError('Folder upload logo tidak bisa ditulis.', 'Jalankan chmod/chown untuk public/uploads/logos agar bisa ditulis oleh web server.');
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

        try {
            $file->move($directory, $name);
        } catch (Throwable $exception) {
            return $this->uploadError('Logo gagal disimpan ke server.', $exception->getMessage(), 500);
        }

        if (! is_file($directory . DIRECTORY_SEPARATOR . $name)) {
            return $this->uploadError('Logo gagal disimpan ke server.', 'File tujuan tidak ditemukan setelah proses upload.', 500);
        }

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

    private function uploadError(string $message, ?string $detail = null, int $status = 422)
    {
        return $this->response->setStatusCode($status)->setJSON([
            'message' => $message,
            'detail'  => $detail,
        ]);
    }

    private function uploadFailureMessage(int $error): string
    {
        return match ($error) {
            UPLOAD_ERR_INI_SIZE, UPLOAD_ERR_FORM_SIZE => 'Ukuran logo melebihi batas upload server.',
            UPLOAD_ERR_PARTIAL => 'Logo hanya terupload sebagian. Coba upload ulang.',
            UPLOAD_ERR_NO_FILE => 'File logo wajib dipilih.',
            UPLOAD_ERR_NO_TMP_DIR => 'Folder temporary upload PHP tidak tersedia.',
            UPLOAD_ERR_CANT_WRITE => 'Server gagal menulis file upload.',
            UPLOAD_ERR_EXTENSION => 'Upload dihentikan oleh extension PHP.',
            default => 'Logo gagal diupload.',
        };
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
