<?php

namespace App\Services;

use App\Models\AuditLog;

class AuditService
{
    public function record(string $event, ?string $userId = null, ?string $applicationId = null, array $metadata = []): void
    {
        // Never accept credentials in metadata. Event names and identifiers are sufficient for auditing.
        unset($metadata['password'], $metadata['access_token'], $metadata['refresh_token'], $metadata['api_key']);

        (new AuditLog())->insert([
            'event' => $event,
            'user_id' => $userId,
            'application_id' => $applicationId,
            'ip_address' => service('request')->getIPAddress(),
            'metadata' => $metadata === [] ? null : json_encode($metadata, JSON_THROW_ON_ERROR),
            'created_at' => date('Y-m-d H:i:s'),
        ]);
    }
}
