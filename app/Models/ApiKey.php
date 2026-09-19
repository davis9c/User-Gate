<?php

namespace App\Models;

use CodeIgniter\Model;

class ApiKey extends Model
{
    protected $table            = 'api_keys';
    protected $primaryKey       = 'id';
    protected $useAutoIncrement = false;
    protected $returnType       = 'array';

    protected $allowedFields = [
        'id',
        'application_id',
        'name',
        'key_hash',
        'key_prefix',
        'status',
        'last_used_at',
    ];

    protected $useTimestamps = true;
}
