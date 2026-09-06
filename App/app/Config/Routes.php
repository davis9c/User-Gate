<?php

use CodeIgniter\Router\RouteCollection;

/**
 * @var RouteCollection $routes
 */

// ============================================================
// Public
// ============================================================

$routes->get('/', 'Home::index');
$routes->get('api-documentation', 'Home::apiDocumentation');

$routes->get('setup', 'Setup::index');
$routes->post('setup/install', 'Setup::install');

$routes->get('login', 'Login::index');
$routes->post('login/authenticate', 'Login::authenticate');
$routes->get('logout', 'Login::logout');


// ============================================================
// Dashboard
// ============================================================

$routes->get(
    'dashboard',
    'Dashboard::index',
    ['filter' => 'auth']
);


// ============================================================
// Dashboard - Users
// ============================================================

$routes->group(
    'dashboard/users',
    ['filter' => 'auth'],
    static function ($routes) {

        // List
        $routes->get(
            '/',
            'Users::index'
        );

        // Create
        $routes->get(
            'create',
            'Users::new'
        );

        $routes->post(
            'create',
            'Users::create'
        );

        // Edit
        $routes->get(
            'edit/(:segment)',
            'Users::edit/$1'
        );

        $routes->post(
            'update/(:segment)',
            'Users::update/$1'
        );

        // Reset password
        $routes->get(
            'reset-password/(:segment)',
            'Users::resetPassword/$1'
        );

        $routes->post(
            'reset-password/(:segment)',
            'Users::updatePassword/$1'
        );
    }
);


// ============================================================
// Profile
// ============================================================

$routes->group(
    'profile',
    ['filter' => 'auth'],
    static function ($routes) {

        $routes->get(
            '/',
            'Profile::index'
        );

        $routes->post(
            'update',
            'Profile::update'
        );

        $routes->get(
            'password',
            'Profile::password'
        );

        $routes->post(
            'password',
            'Profile::updatePassword'
        );
    }
);


// ============================================================
// Dashboard - Applications
// ============================================================

$routes->group(
    'dashboard/applications',
    ['filter' => 'auth'],
    static function ($routes) {

        $routes->get(
            '/',
            'Applications::index'
        );

        $routes->get(
            'create',
            'Applications::new'
        );

        $routes->post(
            'create',
            'Applications::create'
        );

        $routes->get(
            'edit/(:segment)',
            'Applications::edit/$1'
        );

        $routes->post(
            'update/(:segment)',
            'Applications::update/$1'
        );

        // API Keys
        $routes->get(
            '(:segment)/api-keys',
            'ApiKeys::index/$1'
        );

        $routes->get(
            '(:segment)/api-keys/create',
            'ApiKeys::create/$1'
        );

        $routes->post(
            '(:segment)/api-keys/create',
            'ApiKeys::store/$1'
        );
    }
);


// ============================================================
// Dashboard - API Keys
// ============================================================

$routes->group(
    'dashboard/api-keys',
    ['filter' => 'auth'],
    static function ($routes) {

        $routes->post(
            'toggle/(:segment)',
            'ApiKeys::toggle/$1'
        );

        $routes->get(
            '(:segment)/permissions',
            'ApiPermissions::index/$1'
        );

        $routes->post(
            '(:segment)/permissions',
            'ApiPermissions::update/$1'
        );
    }
);


// ============================================================
// API v1
// ============================================================

$routes->group(
    'api/v1',
    ['filter' => 'apiKey'],
    static function ($routes) {

        $routes->post('auth/login', 'Api\\Auth::login', ['filter' => 'loginThrottle']);
        $routes->post('auth/refresh', 'Api\\Auth::refresh');

        $routes->group('auth', ['filter' => 'accessToken'], static function ($routes) {
            $routes->get('me', 'Api\\Auth::me');
            $routes->post('logout', 'Api\\Auth::logout');
        });

        // Users - Read
        $routes->get(
            'users',
            'Api\Users::index',
            ['filter' => 'apiPermission:user.read']
        );

        $routes->get(
            'users/(:segment)',
            'Api\Users::show/$1',
            ['filter' => 'apiPermission:user.read']
        );

        // Users - Create
        $routes->post(
            'users',
            'Api\Users::create',
            ['filter' => 'apiPermission:user.create']
        );

        // Users - Update
        $routes->put(
            'users/(:segment)',
            'Api\Users::update/$1',
            ['filter' => 'apiPermission:user.update']
        );

        // Users - Delete
        $routes->delete(
            'users/(:segment)',
            'Api\Users::delete/$1',
            ['filter' => 'apiPermission:user.delete']
        );
    }
);
