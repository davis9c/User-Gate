<?php

namespace App\Database\Migrations;

use CodeIgniter\Database\Migration;

class CreateUserCredentialsTable extends Migration
{
    public function up()
    {
        $this->forge->addField([
            'id' => [
                'type'       => 'INT',
                'constraint' => 11,
                'unsigned'   => true,
                'auto_increment' => true,
            ],
            'user_id' => [
                'type'       => 'CHAR',
                'constraint' => 36,
            ],
            'password_hash' => [
                'type'       => 'VARCHAR',
                'constraint' => 255,
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
        $this->forge->addUniqueKey('user_id');

        $this->forge->createTable('user_credentials');
    }

    public function down()
    {
        $this->forge->dropTable('user_credentials', true);
    }
}
