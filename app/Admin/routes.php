<?php

use Illuminate\Routing\Router;

Admin::registerAuthRoutes();

Route::group([
    'prefix'        => config('admin.route.prefix'),
    'namespace'     => config('admin.route.namespace'),
    'middleware'    => config('admin.route.middleware'),
], function (Router $router) {
    $router->get('/', 'HomeController@index');

    $router->resource('users', 'UserController');

    $router->resource('products', 'ProductsController');
    // $router->get('products/create', 'ProductsController@create');
    // $router->post('products', 'ProductsController@store');
});
