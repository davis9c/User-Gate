<?php

namespace App\Models;

use CodeIgniter\Model;

class AuditLog extends Model
{
    protected $table = 'audit_logs';
    protected $returnType = 'array';
    protected $useTimestamps = false;
    protected $allowedFields = ['event', 'user_id', 'application_id', 'ip_address', 'metadata', 'created_at'];
}
