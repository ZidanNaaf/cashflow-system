<?php

namespace App\Database\Seeds;

use CodeIgniter\Database\Seeder;

class InitialSeeder extends Seeder
{
    public function run()
    {
        $now = date('Y-m-d H:i:s');

        if ($this->db->table('users')->countAllResults() === 0) {
            $this->db->table('users')->insert([
                'name'          => 'Super Admin',
                'email'         => 'superadmin@cashflow.local',
                'password_hash' => password_hash('superadmin123', PASSWORD_DEFAULT),
                'role'          => 'superadmin',
                'is_active'     => 1,
                'created_at'    => $now,
                'updated_at'    => $now,
            ]);
        }

        if ($this->db->table('settings')->countAllResults() === 0) {
            $this->db->table('settings')->insert([
                'app_name'   => 'Cashflow',
                'currency'   => 'Rp',
                'created_at' => $now,
                'updated_at' => $now,
            ]);
        }
    }
}
