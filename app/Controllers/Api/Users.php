<?php

namespace App\Controllers\Api;

use App\Controllers\BaseController;
use App\Models\UserModel;

class Users extends BaseController
{
    public function index()
    {
        $users = (new UserModel())
            ->select('id, name, email, role, is_active, created_at')
            ->orderBy('name', 'ASC')
            ->findAll();

        return $this->response->setJSON(['data' => $users]);
    }

    public function create()
    {
        $input  = $this->request->getJSON(true) ?: $this->request->getPost();
        $errors = $this->validatePayload($input, true);

        if ($errors !== []) {
            return $this->response->setStatusCode(422)->setJSON(['errors' => $errors]);
        }

        $id = (new UserModel())->insert([
            'name'          => trim((string) $input['name']),
            'email'         => trim((string) $input['email']),
            'password_hash' => password_hash((string) $input['password'], PASSWORD_DEFAULT),
            'role'          => $input['role'],
            'is_active'     => (int) ($input['is_active'] ?? 1),
        ], true);

        return $this->response->setJSON(['message' => 'User berhasil ditambahkan.', 'id' => $id]);
    }

    public function update($id)
    {
        $model = new UserModel();
        if (! $model->find($id)) {
            return $this->response->setStatusCode(404)->setJSON(['message' => 'User tidak ditemukan.']);
        }

        $input  = $this->request->getJSON(true) ?: $this->request->getPost();
        $errors = $this->validatePayload($input, false, (int) $id);

        if ($errors !== []) {
            return $this->response->setStatusCode(422)->setJSON(['errors' => $errors]);
        }

        $data = [
            'name'      => trim((string) $input['name']),
            'email'     => trim((string) $input['email']),
            'role'      => $input['role'],
            'is_active' => (int) ($input['is_active'] ?? 1),
        ];

        if (! empty($input['password'])) {
            $data['password_hash'] = password_hash((string) $input['password'], PASSWORD_DEFAULT);
        }

        $model->update($id, $data);

        return $this->response->setJSON(['message' => 'User berhasil diperbarui.']);
    }

    public function delete($id)
    {
        if ((int) $id === (int) session()->get('user_id')) {
            return $this->response->setStatusCode(422)->setJSON(['message' => 'User aktif tidak bisa dihapus.']);
        }

        (new UserModel())->delete($id);

        return $this->response->setJSON(['message' => 'User berhasil dihapus.']);
    }

    private function validatePayload(array $input, bool $passwordRequired, int $ignoreId = 0): array
    {
        $errors = [];
        $model  = new UserModel();
        $email  = trim((string) ($input['email'] ?? ''));

        if (trim((string) ($input['name'] ?? '')) === '') {
            $errors['name'] = 'Nama wajib diisi.';
        }

        if (! filter_var($email, FILTER_VALIDATE_EMAIL)) {
            $errors['email'] = 'Email tidak valid.';
        } else {
            $query = $model->where('email', $email);
            if ($ignoreId > 0) {
                $query->where('id !=', $ignoreId);
            }
            if ($query->first()) {
                $errors['email'] = 'Email sudah digunakan.';
            }
        }

        if (! in_array(($input['role'] ?? ''), ['admin', 'superadmin'], true)) {
            $errors['role'] = 'Role tidak valid.';
        }

        if ($passwordRequired && strlen((string) ($input['password'] ?? '')) < 6) {
            $errors['password'] = 'Password minimal 6 karakter.';
        } elseif (! $passwordRequired && ! empty($input['password']) && strlen((string) $input['password']) < 6) {
            $errors['password'] = 'Password minimal 6 karakter.';
        }

        return $errors;
    }
}
