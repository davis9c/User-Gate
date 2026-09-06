<?php

namespace App\Libraries;

use CodeIgniter\HTTP\ResponseInterface;

class ApiResponse
{
    public function success(
        ResponseInterface $response,
        $data = null,
        string $message = 'Success',
        int $statusCode = 200,
        array $meta = []
    ): ResponseInterface {
        $output = [
            'status'  => true,
            'message' => $message,
            'data'    => $data,
        ];

        if (!empty($meta)) {
            $output['meta'] = $meta;
        }

        return $response
            ->setStatusCode($statusCode)
            ->setJSON($output);
    }

    public function error(
        ResponseInterface $response,
        string $message,
        int $statusCode = 400,
        array $errors = []
    ): ResponseInterface {
        $output = [
            'status'  => false,
            'message' => $message,
        ];

        if (!empty($errors)) {
            $output['errors'] = $errors;
        }

        return $response
            ->setStatusCode($statusCode)
            ->setJSON($output);
    }
}
