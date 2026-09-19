<?php

namespace App\Filters;

use App\Models\ApiKey;
use CodeIgniter\HTTP\RequestInterface;
use CodeIgniter\HTTP\ResponseInterface;
use CodeIgniter\Filters\FilterInterface;

class ApiKeyAuth implements FilterInterface
{
    public function before(
        RequestInterface $request,
        $arguments = null
    ) {
        $apiKey = $request->getHeaderLine('X-API-Key');

        if (!$apiKey) {
            return service('response')
                ->setStatusCode(401)
                ->setJSON([
                    'status'  => false,
                    'message' => 'API Key is required.',
                ]);
        }

        $model = new ApiKey();

        $keyHash = hash('sha256', $apiKey);

        $record = $model
            ->select('api_keys.*')
            ->join('applications', 'applications.id = api_keys.application_id')
            ->where('api_keys.key_hash', $keyHash)
            ->where('api_keys.status', 'ACTIVE')
            ->where('applications.status', 'ACTIVE')
            ->first();

        if (!$record) {
            return service('response')
                ->setStatusCode(401)
                ->setJSON([
                    'status'  => false,
                    'message' => 'Invalid or inactive API Key.',
                ]);
        }

        $model->update($record['id'], [
            'last_used_at' => date('Y-m-d H:i:s'),
        ]);

        service('request')->apiKey = $record;
    }

    public function after(
        RequestInterface $request,
        ResponseInterface $response,
        $arguments = null
    ) {}
}
