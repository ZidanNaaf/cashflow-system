<?php

namespace App\Controllers\Api;

use App\Controllers\BaseController;
use App\Models\CategoryModel;

class Categories extends BaseController
{
    public function index()
    {
        $type       = $this->request->getGet('type') ?: null;
        $activeOnly = session()->get('user_role') !== 'superadmin' || $this->request->getGet('all') !== '1';

        return $this->response->setJSON([
            'data' => (new CategoryModel())->options($type, $activeOnly),
        ]);
    }

    public function create()
    {
        $input  = $this->request->getJSON(true) ?: $this->request->getPost();
        $errors = $this->validatePayload($input);

        if ($errors !== []) {
            return $this->response->setStatusCode(422)->setJSON(['errors' => $errors]);
        }

        $id = (new CategoryModel())->insert([
            'type'      => $input['type'],
            'name'      => trim((string) $input['name']),
            'is_active' => (int) ($input['is_active'] ?? 1),
        ], true);

        return $this->response->setJSON(['message' => 'Kategori berhasil ditambahkan.', 'id' => $id]);
    }

    public function update($id)
    {
        $model = new CategoryModel();
        if (! $model->find($id)) {
            return $this->response->setStatusCode(404)->setJSON(['message' => 'Kategori tidak ditemukan.']);
        }

        $input  = $this->request->getJSON(true) ?: $this->request->getPost();
        $errors = $this->validatePayload($input, (int) $id);

        if ($errors !== []) {
            return $this->response->setStatusCode(422)->setJSON(['errors' => $errors]);
        }

        $model->update($id, [
            'type'      => $input['type'],
            'name'      => trim((string) $input['name']),
            'is_active' => (int) ($input['is_active'] ?? 1),
        ]);

        return $this->response->setJSON(['message' => 'Kategori berhasil diperbarui.']);
    }

    public function delete($id)
    {
        $model = new CategoryModel();
        if (! $model->find($id)) {
            return $this->response->setStatusCode(404)->setJSON(['message' => 'Kategori tidak ditemukan.']);
        }

        $model->delete($id);

        return $this->response->setJSON(['message' => 'Kategori berhasil dihapus.']);
    }

    private function validatePayload(array $input, int $ignoreId = 0): array
    {
        $errors = [];
        $type   = $input['type'] ?? '';
        $name   = trim((string) ($input['name'] ?? ''));

        if (! in_array($type, ['income', 'expense'], true)) {
            $errors['type'] = 'Tipe kategori tidak valid.';
        }

        if ($name === '') {
            $errors['name'] = 'Nama kategori wajib diisi.';
        }

        if ($type !== '' && $name !== '') {
            $query = (new CategoryModel())->where('type', $type)->where('name', $name);
            if ($ignoreId > 0) {
                $query->where('id !=', $ignoreId);
            }

            if ($query->first()) {
                $errors['name'] = 'Kategori dengan tipe ini sudah ada.';
            }
        }

        return $errors;
    }
}
