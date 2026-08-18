<?php

namespace App\Database\Migrations;

use CodeIgniter\Database\Migration;

class CreateAuthTokensTable extends Migration
{
    public function up()
    {
        $this->forge->addField([
            'id' => ['type' => 'CHAR', 'constraint' => 36],
            'user_id' => ['type' => 'CHAR', 'constraint' => 36],
            'application_id' => ['type' => 'CHAR', 'constraint' => 36],
            'api_key_id' => ['type' => 'CHAR', 'constraint' => 36],
            'access_token_hash' => ['type' => 'CHAR', 'constraint' => 64],
            'refresh_token_hash' => ['type' => 'CHAR', 'constraint' => 64],
            'access_expires_at' => ['type' => 'DATETIME'],
            'refresh_expires_at' => ['type' => 'DATETIME'],
            'revoked_at' => ['type' => 'DATETIME', 'null' => true],
            'last_used_at' => ['type' => 'DATETIME', 'null' => true],
            'ip_address' => ['type' => 'VARCHAR', 'constraint' => 45, 'null' => true],
            'user_agent' => ['type' => 'VARCHAR', 'constraint' => 255, 'null' => true],
            'created_at' => ['type' => 'DATETIME', 'null' => true],
            'updated_at' => ['type' => 'DATETIME', 'null' => true],
        ]);
        $this->forge->addKey('id', true);
        $this->forge->addUniqueKey('access_token_hash');
        $this->forge->addUniqueKey('refresh_token_hash');
        $this->forge->addKey(['user_id', 'revoked_at']);
        $this->forge->addKey('application_id');
        $this->forge->createTable('auth_tokens');
    }

    public function down()
    {
        $this->forge->dropTable('auth_tokens', true);
    }
}
