<?php

namespace App\Models;

use CodeIgniter\Model;

class Application extends Model
{
    protected $table            = 'applications';
    protected $primaryKey       = 'id';
    protected $useAutoIncrement = false;
    protected $returnType       = 'array';

    protected $allowedFields = [
        'id',
        'name',
        'code',
        'description',
        'status',
    ];

    protected $useTimestamps = true;
}
