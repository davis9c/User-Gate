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
            [
                'name'        => 'Read Application',
                'code'        => 'application.read',
                'description' => 'Melihat aplikasi yang terdaftar.',
            ],
            [
                'name'        => 'Create Application',
                'code'        => 'application.create',
                'description' => 'Membuat aplikasi baru.',
            ],
            [
                'name'        => 'Update Application',
                'code'        => 'application.update',
                'description' => 'Mengubah aplikasi yang terdaftar.',
            ],
            [
                'name'        => 'Read API Key',
                'code'        => 'api_key.read',
                'description' => 'Melihat API key.',
            ],
            [
                'name'        => 'Create API Key',
                'code'        => 'api_key.create',
                'description' => 'Membuat API key baru.',
            ],
            [
                'name'        => 'Update API Key',
                'code'        => 'api_key.update',
                'description' => 'Mengubah API key.',
            ],
            [
                'name'        => 'Read API Permission',
                'code'        => 'api_permission.read',
                'description' => 'Melihat permission API.',
            ],
            [
                'name'        => 'Update API Permission',
                'code'        => 'api_permission.update',
                'description' => 'Mengubah permission API.',
            ],
        ];

        foreach ($data as $permission) {
            $existing = $this->db->table('api_permissions')
                ->where('code', $permission['code'])
                ->get()
                ->getRowArray();

            if (!$existing) {
                $this->db->table('api_permissions')->insert($permission);
            }
        }

        $roleData = [
            'name'        => 'Super Administrator',
            'code'        => 'SUPER_ADMIN',
            'description' => 'Akses penuh ke UserGateway.',
            'status'      => 'ACTIVE',
        ];
        $role = $this->db->table('roles')
            ->where('code', $roleData['code'])
            ->get()
            ->getRowArray();

        if (!$role) {
            $this->db->table('roles')->insert($roleData);
            $role = $this->db->table('roles')->where('code', $roleData['code'])->get()->getRowArray();
        }

        $permissions = $this->db->table('api_permissions')->select('id')->get()->getResultArray();
        foreach ($permissions as $permission) {
            $assignment = [
                'role_id'       => $role['id'],
                'permission_id' => $permission['id'],
            ];

            if ($this->db->table('role_permissions')
                ->where('role_id', $assignment['role_id'])
                ->where('permission_id', $assignment['permission_id'])
                ->countAllResults() === 0
            ) {
                $this->db->table('role_permissions')->insert($assignment);
            }
        }
    }
}
