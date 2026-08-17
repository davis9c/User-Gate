<?php

namespace App\Database\Migrations;

use CodeIgniter\Database\Migration;

class CreateApiKeyPermissionsTable extends Migration
{
    public function up()
    {
        $this->forge->addField([
            'api_key_id' => [
                'type'       => 'CHAR',
                'constraint' => 36,
            ],
            'permission_id' => [
                'type'       => 'INT',
                'constraint' => 11,
                'unsigned'   => true,
            ],
        ]);

        $this->forge->addKey(
            ['api_key_id', 'permission_id'],
            true
        );

        $this->forge->addForeignKey(
            'api_key_id',
            'api_keys',
            'id',
            'CASCADE',
            'CASCADE'
        );

        $this->forge->addForeignKey(
            'permission_id',
            'api_permissions',
            'id',
            'CASCADE',
            'CASCADE'
        );

        $this->forge->createTable('api_key_permissions');
    }

    public function down()
    {
        $this->forge->dropTable('api_key_permissions');
    }
}
