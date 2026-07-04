<?php

namespace App\Controllers\Api;

use App\Controllers\BaseController;
use App\Models\CategoryModel;
use App\Models\TransactionModel;

class Transactions extends BaseController
{
    public function index()
    {
        $start = $this->request->getGet('start_date') ?: date('Y-m-01');
        $end   = $this->request->getGet('end_date') ?: date('Y-m-t');
        $type  = $this->request->getGet('type') ?: '';

        $model = new TransactionModel();
        $rows  = $model->filtered($start, $end, $type);

        return $this->response->setJSON(['data' => $rows]);
    }

    public function create()
    {
        $payload = $this->payload();
        $errors  = $this->validatePayload($payload);

        if ($errors !== []) {
            return $this->response->setStatusCode(422)->setJSON(['errors' => $errors]);
        }

        $payload['created_by'] = session()->get('user_id');
        $id = (new TransactionModel())->insert($payload, true);

        return $this->response->setJSON(['message' => 'Data berhasil ditambahkan.', 'id' => $id]);
    }

    public function update($id)
    {
        $model = new TransactionModel();
        if (! $model->find($id)) {
            return $this->response->setStatusCode(404)->setJSON(['message' => 'Data tidak ditemukan.']);
        }

        $payload = $this->payload();
        $errors  = $this->validatePayload($payload);

        if ($errors !== []) {
            return $this->response->setStatusCode(422)->setJSON(['errors' => $errors]);
        }

        $model->update($id, $payload);

        return $this->response->setJSON(['message' => 'Data berhasil diperbarui.']);
    }

    public function delete($id)
    {
        $model = new TransactionModel();
        if (! $model->find($id)) {
            return $this->response->setStatusCode(404)->setJSON(['message' => 'Data tidak ditemukan.']);
        }

        $model->delete($id);

        return $this->response->setJSON(['message' => 'Data berhasil dihapus.']);
    }

    private function payload(): array
    {
        $input = $this->request->getJSON(true) ?: $this->request->getPost();

        return [
            'transaction_date' => $input['transaction_date'] ?? '',
            'type'             => $input['type'] ?? '',
            'category'         => trim((string) ($input['category'] ?? '')),
            'description'      => trim((string) ($input['description'] ?? '')),
            'amount'           => (float) ($input['amount'] ?? 0),
        ];
    }

    private function validatePayload(array $payload): array
    {
        $errors = [];

        if (! preg_match('/^\d{4}-\d{2}-\d{2}$/', (string) $payload['transaction_date'])) {
            $errors['transaction_date'] = 'Tanggal wajib diisi.';
        }

        if (! in_array($payload['type'], ['income', 'expense'], true)) {
            $errors['type'] = 'Tipe transaksi tidak valid.';
        }

        if ($payload['category'] === '') {
            $errors['category'] = 'Kategori wajib diisi.';
        } elseif (in_array($payload['type'], ['income', 'expense'], true)) {
            $category = (new CategoryModel())
                ->where('type', $payload['type'])
                ->where('name', $payload['category'])
                ->where('is_active', 1)
                ->first();

            if (! $category) {
                $errors['category'] = 'Kategori tidak tersedia untuk tipe transaksi ini.';
            }
        }

        if ($payload['amount'] <= 0) {
            $errors['amount'] = 'Nominal harus lebih dari 0.';
        }

        return $errors;
    }
}
