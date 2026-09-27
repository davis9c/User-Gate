<?php

namespace App\Controllers;

use App\Models\ApiKey;
use App\Models\Application;
use App\Models\User;

class Dashboard extends BaseController
{
    public function index()
    {
        $userModel       = new User();
        $applicationModel = new Application();
        $apiKeyModel     = new ApiKey();

        return view('dashboard/index', [
            'title' => 'Dashboard',
            'stats' => [
                'users' => [
                    'total'    => $userModel->countAllResults(),
                    'active'   => $userModel->where('status', 'ACTIVE')->countAllResults(),
                    'inactive' => $userModel->where('status', 'INACTIVE')->countAllResults(),
                ],
                'applications' => [
                    'total'    => $applicationModel->countAllResults(),
                    'active'   => $applicationModel->where('status', 'ACTIVE')->countAllResults(),
                    'inactive' => $applicationModel->where('status', 'INACTIVE')->countAllResults(),
                ],
                'apiKeys' => [
                    'total'    => $apiKeyModel->countAllResults(),
                    'active'   => $apiKeyModel->where('status', 'ACTIVE')->countAllResults(),
                    'inactive' => $apiKeyModel->where('status', 'INACTIVE')->countAllResults(),
                ],
            ],
        ]);
    }
}
