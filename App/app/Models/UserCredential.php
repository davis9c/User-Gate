<?php

namespace App\Models;

use CodeIgniter\Model;

class UserCredential extends Model
{
    protected $table            = 'user_credentials';
    protected $primaryKey       = 'id';
    protected $returnType       = 'array';

    protected $allowedFields = [
        'user_id',
        'password_hash',
    ];

    protected $useTimestamps = true;
    
}
