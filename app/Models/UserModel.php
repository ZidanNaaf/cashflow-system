<?php

namespace App\Models;

use CodeIgniter\Model;

class UserModel extends Model
{
    protected $table         = 'users';
    protected $primaryKey    = 'id';
    protected $allowedFields = ['name', 'email', 'password_hash', 'role', 'is_active', 'remember_token', 'remember_expires_at'];
    protected $useTimestamps = true;
}
