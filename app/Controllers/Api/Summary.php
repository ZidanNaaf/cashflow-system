<?php

namespace App\Controllers\Api;

use App\Controllers\BaseController;
use App\Models\TransactionModel;

class Summary extends BaseController
{
    public function index()
    {
        $model      = new TransactionModel();
        $monthStart = date('Y-m-01');
        $monthEnd   = date('Y-m-t');

        $incomeMonth  = $model->sumByType('income', $monthStart, $monthEnd);
        $expenseMonth = $model->sumByType('expense', $monthStart, $monthEnd);
        $incomeTotal  = $model->sumByType('income');
        $expenseTotal = $model->sumByType('expense');

        return $this->response->setJSON([
            'balance'       => $incomeTotal - $expenseTotal,
            'income_month'  => $incomeMonth,
            'expense_month' => $expenseMonth,
        ]);
    }
}
