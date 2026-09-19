<?php

namespace App\Controllers\Api;

use App\Controllers\BaseController;
use App\Exceptions\UserException;
use App\Libraries\ApiPermission;
use App\Libraries\ApiResponse;
use CodeIgniter\HTTP\RequestInterface;
use CodeIgniter\HTTP\ResponseInterface;
use Psr\Log\LoggerInterface;

class ApiController extends BaseController
{
    protected ApiResponse $apiResponse;
    protected ApiPermission $apiPermission;

    public function initController(
        RequestInterface $request,
        ResponseInterface $response,
        LoggerInterface $logger
    ) {
        parent::initController(
            $request,
            $response,
            $logger
        );

        $this->apiResponse = new ApiResponse();
        $this->apiPermission = new ApiPermission();
    }
    protected function requirePermission(string $permission)
    {
        if (!$this->apiPermission->has($permission)) {
            return $this->apiResponse->error(
                $this->response,
                'API Key does not have ' . $permission . ' permission.',
                403
            );
        }

        return null;
    }
    protected function errorResponse(\Throwable $e)
    {
        if ($e instanceof UserException) {
            return $this->apiResponse->error(
                $this->response,
                $e->getMessage(),
                $e->getStatusCode()
            );
        }

        log_message(
            'error',
            '[API] ' . $e->getMessage()
        );

        return $this->apiResponse->error(
            $this->response,
            'Internal server error.',
            500
        );
    }
}
