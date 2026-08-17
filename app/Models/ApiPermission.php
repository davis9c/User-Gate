<?php

namespace App\Models;

use CodeIgniter\Model;

class ApiPermission extends Model
{
    protected $table      = 'api_permissions';
    protected $primaryKey = 'id';
    protected $returnType = 'array';

    protected $allowedFields = [
        'name',
        'code',
        'description',
    ];

    protected $useTimestamps = true;
}
