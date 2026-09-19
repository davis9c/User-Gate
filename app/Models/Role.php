<?php

namespace App\Models;

use CodeIgniter\Model;

class Role extends Model
{
    protected $table      = 'roles';
    protected $primaryKey = 'id';

    protected $returnType = 'array';

    protected $allowedFields = [
        'name',
        'code',
        'description',
        'status',
    ];

    protected $useTimestamps = true;
}
