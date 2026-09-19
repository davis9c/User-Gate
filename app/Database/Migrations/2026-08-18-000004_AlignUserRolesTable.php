<?php

namespace App\Database\Migrations;

use CodeIgniter\Database\Migration;

class AlignUserRolesTable extends Migration
{
    public function up()
    {
        $columns = $this->db->getFieldNames('user_roles');
        if (!in_array('role_id', $columns, true)) {
            $this->forge->addColumn('user_roles', [
                'role_id' => [
                    'type'       => 'INT',
                    'constraint' => 11,
                    'unsigned'   => true,
                    'null'       => true,
                    'after'      => 'user_id',
                ],
            ]);
        }

        $legacyRoles = $this->db->table('user_roles')
            ->select('role')
            ->where('role IS NOT NULL', null, false)
            ->where('role !=', '')
            ->distinct()
            ->get()
            ->getResultArray();
        foreach ($legacyRoles as $legacyRole) {
            $code = $legacyRole['role'];
            if ($this->db->table('roles')->where('code', $code)->countAllResults() === 0) {
                $this->db->table('roles')->insert([
                    'name'   => ucwords(strtolower(str_replace('_', ' ', $code))),
                    'code'   => $code,
                    'status' => 'ACTIVE',
                ]);
            }
        }
        if (in_array('role', $columns, true)) {
            $this->db->query(
                'UPDATE user_roles ur JOIN roles r ON r.code = ur.role '
                . "SET ur.role_id = r.id WHERE ur.role IS NOT NULL AND ur.role <> ''"
            );

            $indexes = $this->db->query('SHOW INDEX FROM user_roles')->getResultArray();
            $indexColumns = [];
            foreach ($indexes as $index) {
                $indexColumns[$index['Key_name']][$index['Seq_in_index']] = $index['Column_name'];
            }
            foreach ($indexColumns as $name => $indexColumnsByPosition) {
                if (array_values($indexColumnsByPosition) === ['user_id', 'role']) {
                    $this->forge->dropKey('user_roles', $name);
                }
            }
        }
        $this->forge->dropColumn('user_roles', 'role');
        $this->forge->addUniqueKey(['user_id', 'role_id']);
        $this->forge->addForeignKey('user_id', 'users', 'id', 'CASCADE', 'CASCADE');
        $this->forge->addForeignKey('role_id', 'roles', 'id', 'CASCADE', 'CASCADE');
    }

    public function down()
    {
        $this->forge->dropForeignKey('user_roles', 'user_roles_user_id_foreign');
        $this->forge->dropForeignKey('user_roles', 'user_roles_role_id_foreign');
        $this->forge->dropKey('user_roles', 'user_id_role_id_unique');
        $this->forge->addColumn('user_roles', [
            'role' => [
                'type'       => 'VARCHAR',
                'constraint' => 50,
                'after'      => 'user_id',
            ],
        ]);
        $this->forge->dropColumn('user_roles', 'role_id');
        $this->forge->addUniqueKey(['user_id', 'role']);
    }
}