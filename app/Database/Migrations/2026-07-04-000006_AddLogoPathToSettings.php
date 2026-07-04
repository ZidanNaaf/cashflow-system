<?php

namespace App\Database\Migrations;

use CodeIgniter\Database\Migration;

class AddLogoPathToSettings extends Migration
{
    public function up()
    {
        $this->forge->addColumn('settings', [
            'logo_path' => [
                'type'       => 'VARCHAR',
                'constraint' => 255,
                'null'       => true,
                'after'      => 'currency',
            ],
        ]);
    }

    public function down()
    {
        $this->forge->dropColumn('settings', 'logo_path');
    }
}
