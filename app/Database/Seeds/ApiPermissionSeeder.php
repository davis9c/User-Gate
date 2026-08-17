<?php

namespace App\Database\Seeds;

use CodeIgniter\Database\Seeder;

class ApiPermissionSeeder extends Seeder
{
    public function run()
    {
        $data = [
            [
                'name'        => 'Read User',
                'code'        => 'user.read',
                'description' => 'Membaca data user dari UserGateway.',
            ],
            [
                'name'        => 'Create User',
                'code'        => 'user.create',
                'description' => 'Membuat user baru melalui UserGateway.',
            ],
            [
                'name'        => 'Update User',
                'code'        => 'user.update',
                'description' => 'Mengubah data user melalui UserGateway.',
            ],
            [
                'name'        => 'Delete User',
                'code'        => 'user.delete',
                'description' => 'Menghapus user melalui UserGateway.',
            ],
        ];

        $this->db->table('api_permissions')->insertBatch($data);
    }
}
