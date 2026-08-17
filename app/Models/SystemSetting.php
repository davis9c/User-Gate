<?php

namespace App\Models;

use CodeIgniter\Model;

class SystemSetting extends Model
{
    protected $table            = 'system_settings';
    protected $primaryKey       = 'id';
    protected $returnType       = 'array';

    protected $allowedFields = [
        'key',
        'value',
    ];

    protected $useTimestamps = true;
}
