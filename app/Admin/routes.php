<?php

use Illuminate\Routing\Router;

Admin::registerAuthRoutes();

Route::group([
    'prefix' => config('admin.route.prefix'),
    'namespace' => config('admin.route.namespace'),
    'middleware' => config('admin.route.middleware'),
], function (Router $router) {
    $router->get('/', 'HomeController@index');

    $router->resource('users', 'UserController');

    $router->resource('products', 'ProductsController');

    $router->post('orders/{order}/ship', 'OrdersController@ship')->name('admin.orders.ship');
    $router->post('orders/{order}/refund', 'OrdersController@refund')->name('admin.orders.refund');
    $router->resource('orders', 'OrdersController', ['only' => ['index', 'show']]);
    $router->resource('coupon_codes', 'CouponCodesController');
});
