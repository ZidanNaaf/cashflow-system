<?php

use CodeIgniter\Router\RouteCollection;

/** @var RouteCollection $routes */
$routes->get('/', 'Home::index');

$routes->get('login', 'Auth::login');
$routes->post('login', 'Auth::attempt');
$routes->get('logout', 'Auth::logout');

$routes->group('', ['filter' => 'auth'], static function ($routes) {
    $routes->get('dashboard', 'App::index');

    $routes->group('api', static function ($routes) {
        $routes->get('summary', 'Api\Summary::index');
        $routes->get('reports/monthly', 'Api\Reports::monthly');
        $routes->get('categories', 'Api\Categories::index');
        $routes->get('transactions', 'Api\Transactions::index');
        $routes->post('transactions', 'Api\Transactions::create');
        $routes->put('transactions/(:num)', 'Api\Transactions::update/$1');
        $routes->delete('transactions/(:num)', 'Api\Transactions::delete/$1');

        $routes->group('', ['filter' => 'role:superadmin'], static function ($routes) {
            $routes->get('users', 'Api\Users::index');
            $routes->post('users', 'Api\Users::create');
            $routes->put('users/(:num)', 'Api\Users::update/$1');
            $routes->delete('users/(:num)', 'Api\Users::delete/$1');
            $routes->get('settings', 'Api\Settings::index');
            $routes->put('settings', 'Api\Settings::update');
            $routes->post('settings/logo', 'Api\Settings::uploadLogo');
            $routes->delete('settings/logo', 'Api\Settings::deleteLogo');
            $routes->post('categories', 'Api\Categories::create');
            $routes->put('categories/(:num)', 'Api\Categories::update/$1');
            $routes->delete('categories/(:num)', 'Api\Categories::delete/$1');
        });
    });
});
