<?php

namespace App\Controllers;

class Home extends BaseController
{
    public function index()
    {
        return view('home/index', [
            'title' => 'UserGate - User Management & API Access',
        ]);
    }

    public function apiDocumentation()
    {
        return view('home/api_documentation', [
            'title' => 'UserGate REST API Documentation',
        ]);
    }
}
