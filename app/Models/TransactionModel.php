<?php

namespace App\Models;

use CodeIgniter\Model;

class TransactionModel extends Model
{
    protected $table         = 'transactions';
    protected $primaryKey    = 'id';
    protected $allowedFields = ['transaction_date', 'type', 'category', 'description', 'amount', 'created_by'];
    protected $useTimestamps = true;

    public function filtered(string $start, string $end, string $type = ''): array
    {
        $builder = $this->select('transactions.*, users.name AS created_by_name')
            ->join('users', 'users.id = transactions.created_by', 'left')
            ->where('transaction_date >=', $start)
            ->where('transaction_date <=', $end);

        if (in_array($type, ['income', 'expense'], true)) {
            $builder->where('type', $type);
        }

        return $builder
            ->orderBy('transaction_date', 'DESC')
            ->orderBy('transactions.id', 'DESC')
            ->findAll();
    }

    public function sumByType(string $type, ?string $start = null, ?string $end = null): float
    {
        $builder = $this->selectSum('amount')
            ->where('type', $type);

        if ($start !== null && $end !== null) {
            $builder->where('transaction_date >=', $start)
                ->where('transaction_date <=', $end);
        }

        $row = $builder->first();

        return (float) ($row['amount'] ?? 0);
    }
}
