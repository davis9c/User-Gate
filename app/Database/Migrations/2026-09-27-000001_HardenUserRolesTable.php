<?php

namespace App\Database\Migrations;

use CodeIgniter\Database\Migration;

/**
 * Memulihkan constraint pada user_roles.
 *
 * Latar belakang: AlignUserRolesTable (2026-08-18-000004) memanggil
 * addUniqueKey() dan addForeignKey(), tapi Forge tidak menjalankan alter
 * kalau tidak ada operasi kolom lain yang sama. Akibatnya migration
 * ditandai sudah jalan sementara tabel hanya punya PRIMARY dan index
 * biasa di user_id — persis bug yang dicoba perbaiki di sini.
 *
 * Karena itu DDL-nya ditulis sebagai SQL langsung, bukan lewat Forge.
 * Semuanya idempoten: dicek dulu, baru ditambahkan.
 */
class HardenUserRolesTable extends Migration
{
    public function up()
    {
        $this->repairOrphans();
        $this->removeDuplicates();

        if (! $this->hasIndex(['user_id', 'role_id'], true)) {
            $this->db->query(
                'ALTER TABLE `user_roles`'
                . ' ADD UNIQUE KEY `user_id_role_id_unique` (`user_id`, `role_id`)'
            );
        }

        foreach (['user_id' => 'users', 'role_id' => 'roles'] as $column => $table) {
            if (! $this->hasForeignKey($column)) {
                $this->db->query(
                    'ALTER TABLE `user_roles`'
                    . ' ADD CONSTRAINT `user_roles_' . $column . '_foreign`'
                    . " FOREIGN KEY (`{$column}`)"
                    . " REFERENCES `{$table}` (`id`)"
                    . ' ON DELETE CASCADE ON UPDATE CASCADE'
                );
            }
        }
    }

    public function down()
    {
        $this->dropForeignKeyIfExists('role_id');
        $this->dropForeignKeyIfExists('user_id');

        if ($this->hasIndex(['user_id', 'role_id'], true)) {
            $this->db->query(
                'ALTER TABLE `user_roles` DROP KEY `user_id_role_id_unique`'
            );
        }
    }

    /**
     * Foreign key butuh semua baris punya induk yang ada. Baris yatim
     * dibuang supaya penambahan FK tidak gagal.
     */
    private function repairOrphans(): void
    {
        $this->db->query(
            'DELETE ur FROM `user_roles` ur'
            . ' LEFT JOIN `users` u ON u.id = ur.user_id'
            . ' WHERE u.id IS NULL'
        );

        // role_id nullable, jadi baris ini bisa dipertahankan dengan NULL.
        $this->db->query(
            'UPDATE user_roles ur'
            . ' LEFT JOIN `roles` r ON r.id = ur.role_id'
            . ' SET ur.role_id = NULL'
            . ' WHERE ur.role_id IS NOT NULL AND r.id IS NULL'
        );
    }

    /**
     * UNIQUE key akan gagal kalau sudah ada baris kembar. Sisakan yang
     * paling lama.
     */
    private function removeDuplicates(): void
    {
        $this->db->query(
            'DELETE ur FROM `user_roles` ur'
            . ' JOIN `user_roles` keep'
            . '   ON keep.user_id = ur.user_id'
            . '  AND keep.role_id <=> ur.role_id'
            . '  AND keep.id < ur.id'
            . ' WHERE ur.role_id IS NOT NULL'
        );
    }

    /**
     * @param list<string> $columns
     */
    private function hasIndex(array $columns, bool $unique = false): bool
    {
        $indexes = $this->db->query('SHOW INDEX FROM `user_roles`')->getResultArray();

        $grouped = [];

        foreach ($indexes as $index) {
            if ($unique && (int) $index['Non_unique'] === 1) {
                continue;
            }

            $grouped[$index['Key_name']][(int) $index['Seq_in_index']] = $index['Column_name'];
        }

        foreach ($grouped as $indexColumns) {
            if (array_values($indexColumns) === $columns) {
                return true;
            }
        }

        return false;
    }

    private function hasForeignKey(string $column): bool
    {
        return $this->foreignKeyName($column) !== null;
    }

    private function dropForeignKeyIfExists(string $column): void
    {
        $name = $this->foreignKeyName($column);

        if ($name !== null) {
            $this->db->query('ALTER TABLE `user_roles` DROP FOREIGN KEY `' . $name . '`');
        }
    }

    private function foreignKeyName(string $column): ?string
    {
        $row = $this->db->query(
            'SELECT CONSTRAINT_NAME FROM information_schema.KEY_COLUMN_USAGE'
            . ' WHERE TABLE_SCHEMA = DATABASE()'
            . "   AND TABLE_NAME = 'user_roles'"
            . "   AND COLUMN_NAME = '{$column}'"
            . '   AND REFERENCED_TABLE_NAME IS NOT NULL'
            . ' LIMIT 1'
        )->getRowArray();

        return $row['CONSTRAINT_NAME'] ?? null;
    }
}
