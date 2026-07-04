<?php

namespace App\Controllers\Api;

use App\Controllers\BaseController;

class Reports extends BaseController
{
    public function monthly()
    {
        $months = [];
        $start  = new \DateTimeImmutable('first day of this month');

        for ($i = 5; $i >= 0; $i--) {
            $date = $start->modify("-{$i} months");
            $key  = $date->format('Y-m');

            $months[$key] = [
                'label'   => $date->format('M Y'),
                'income'  => 0.0,
                'expense' => 0.0,
            ];
        }

        $rows = db_connect()->query(
            "SELECT strftime('%Y-%m', transaction_date) AS month_key, type, SUM(amount) AS total
            FROM transactions
            WHERE transaction_date >= ?
            GROUP BY month_key, type",
            [array_key_first($months) . '-01']
        )->getResultArray();

        foreach ($rows as $row) {
            if (isset($months[$row['month_key']]) && in_array($row['type'], ['income', 'expense'], true)) {
                $months[$row['month_key']][$row['type']] = (float) $row['total'];
            }
        }

        return $this->response->setJSON([
            'labels'  => array_column($months, 'label'),
            'income'  => array_column($months, 'income'),
            'expense' => array_column($months, 'expense'),
        ]);
    }
}
