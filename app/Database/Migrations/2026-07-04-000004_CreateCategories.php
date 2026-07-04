<?php

namespace App\Database\Migrations;

use CodeIgniter\Database\Migration;

class CreateCategories extends Migration
{
    public function up()
    {
        $this->forge->addField([
            'id' => [
                'type'           => 'INTEGER',
                'unsigned'       => true,
                'auto_increment' => true,
            ],
            'type' => [
                'type'       => 'VARCHAR',
                'constraint' => 20,
            ],
            'name' => [
                'type'       => 'VARCHAR',
                'constraint' => 120,
            ],
            'is_active' => [
                'type'       => 'INTEGER',
                'constraint' => 1,
                'default'    => 1,
            ],
            'created_at' => [
                'type' => 'DATETIME',
                'null' => true,
            ],
            'updated_at' => [
                'type' => 'DATETIME',
                'null' => true,
            ],
        ]);
        $this->forge->addKey('id', true);
        $this->forge->addKey(['type', 'name']);
        $this->forge->createTable('categories');

        $now = date('Y-m-d H:i:s');
        $this->db->table('categories')->insertBatch([
            ['type' => 'income', 'name' => 'Gaji', 'is_active' => 1, 'created_at' => $now, 'updated_at' => $now],
            ['type' => 'income', 'name' => 'Bonus', 'is_active' => 1, 'created_at' => $now, 'updated_at' => $now],
            ['type' => 'income', 'name' => 'Penjualan', 'is_active' => 1, 'created_at' => $now, 'updated_at' => $now],
            ['type' => 'expense', 'name' => 'Operasional', 'is_active' => 1, 'created_at' => $now, 'updated_at' => $now],
            ['type' => 'expense', 'name' => 'Belanja', 'is_active' => 1, 'created_at' => $now, 'updated_at' => $now],
            ['type' => 'expense', 'name' => 'Transportasi', 'is_active' => 1, 'created_at' => $now, 'updated_at' => $now],
        ]);
    }

    public function down()
    {
        $this->forge->dropTable('categories');
    }
}
