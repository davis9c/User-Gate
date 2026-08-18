<?php

namespace App\Models;

use CodeIgniter\Model;

class AuthToken extends Model
{
    protected $table = 'auth_tokens';
    protected $primaryKey = 'id';
    protected $useAutoIncrement = false;
    protected $returnType = 'array';
    protected $useTimestamps = true;

    protected $allowedFields = [
        'id', 'user_id', 'application_id', 'api_key_id', 'access_token_hash',
        'refresh_token_hash', 'access_expires_at', 'refresh_expires_at',
        'revoked_at', 'last_used_at', 'ip_address', 'user_agent',
    ];
}
