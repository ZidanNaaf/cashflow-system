<?php

namespace App\Models;

use CodeIgniter\Model;

class CategoryModel extends Model
{
    protected $table         = 'categories';
    protected $primaryKey    = 'id';
    protected $allowedFields = ['type', 'name', 'is_active'];
    protected $useTimestamps = true;

    public function options(?string $type = null, bool $activeOnly = true): array
    {
        $builder = $this->orderBy('type', 'ASC')->orderBy('name', 'ASC');

        if (in_array($type, ['income', 'expense'], true)) {
            $builder->where('type', $type);
        }

        if ($activeOnly) {
            $builder->where('is_active', 1);
        }

        return $builder->findAll();
    }
}
