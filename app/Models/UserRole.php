<?php

namespace App\Models;

use CodeIgniter\Model;

class UserRole extends Model
{
    protected $table      = 'user_roles';
    protected $primaryKey = 'id';

    protected $returnType = 'array';

    protected $allowedFields = [
        'user_id',
        'role_id',
    ];

    protected $useTimestamps = true;
}
