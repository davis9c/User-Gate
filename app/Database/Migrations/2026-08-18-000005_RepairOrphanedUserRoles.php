<?php

namespace App\Database\Migrations;

use CodeIgniter\Database\Migration;

class RepairOrphanedUserRoles extends Migration
{
    public function up()
    {
        $role = $this->db->table('roles')
            ->where('code', 'SUPER_ADMIN')
            ->get()
            ->getRowArray();

        if (!$role) {
            $this->db->table('roles')->insert([
                'name'        => 'Super Administrator',
                'code'        => 'SUPER_ADMIN',
                'description' => 'Akses penuh ke UserGateway.',
                'status'      => 'ACTIVE',
            ]);
            $role = $this->db->table('roles')->where('code', 'SUPER_ADMIN')->get()->getRowArray();
        }

        $this->db->table('user_roles')
            ->where('role_id IS NULL', null, false)
            ->set('role_id', $role['id'])
            ->update();
    }

    public function down()
    {
        // Role assignments are intentionally not removed during rollback.
    }
}