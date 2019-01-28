<?php

use Illuminate\Http\Request;

/*
|--------------------------------------------------------------------------
| API Routes
|--------------------------------------------------------------------------
|
| Here is where you can register API routes for your application. These
| routes are loaded by the RouteServiceProvider within a group which
| is assigned the "api" middleware group. Enjoy building your API!
|
*/

// Route::middleware('auth:api')->get('/user', function (Request $request) {
//     return $request->user();
// });

$api = app('Dingo\Api\Routing\Router');

$api->version('v1',[
    'namespace' => 'App\Http\Controllers\Api',
    'middleware' => ['serializer:array', 'bindings', 'throttle:60,1']
], function($api){
    $api->get('version', function(){
        return response('this is version1');
    });

    $api->post('verfycode', 'VerfyCodeController@store')->name('api.verfycode.store');

    $api->post('register', 'UserController@store')->name('api.user.store');
    $api->post('login', 'UserController@login')->name('api.user.login');

    $api->get('products', 'ProductsController@index')->name('api.products.index');
    $api->get('products/{product}', 'ProductsController@show')->name('api.products.show');
    $api->group(['middleware' => 'auth:api'], function($api){
        $api->resource('user_addresses', 'UserAddressController', ['only' => ['store', 'update', 'destroy']]);
        $api->delete('logout', 'UserController@logout')->name('api.user.logout');

        $api->post('products/{product}/favor', 'ProductsController@favor')->name('api.products.favor');

        $api->post('carts', 'CartsController@store')->name('api.carts.store');
        $api->delete('carts', 'CartsController@remove')->name('api.carts.remove');

        $api->post('orders', 'OrdersController@store')->name('api.orders.store');

        $api->get('test', function() {
            return response('this is test');
        });
    });
});

$api->version('v2', function($api){
    $api->get('version', function(){
        return response('this is version2');
    });
});
