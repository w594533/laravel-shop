<?php

/*
|--------------------------------------------------------------------------
| Web Routes
|--------------------------------------------------------------------------
|
| Here is where you can register web routes for your application. These
| routes are loaded by the RouteServiceProvider within a group which
| contains the "web" middleware group. Now create something great!
|
*/
Route::get("/", 'ProductsController@index')->name('root');

Auth::routes();


Route::group(['middleware' => 'auth'], function () {
    Route::get('/email_verify_notice', 'PagesController@emailVerifyNotice')->name('email_verify_notice');

    Route::get('/email_verification/send', 'EmailVerificationController@send')->name('email_verification.send');
    Route::get('/email_verification/{token}', 'EmailVerificationController@verify')->name('email_verification.verify');

    Route::resource('user_addresses', 'UserAddressController');

    Route::post('/products/{product}/favorite', 'UserFavoriteProductController@addFavorite')->name('user.favorite.product');
    Route::delete('/products/{product}/favorite', 'UserFavoriteProductController@delFavorite')->name('user.delfavorite.product');
    Route::get('/products/favorites', 'UserFavoriteProductController@index')->name('user.favorite.index');

    Route::post('/cart', 'CartController@add')->name('cart.add');
    Route::get('/cart', 'CartController@index')->name('cart.index');
    Route::delete('/cart/{productSku}', 'CartController@remove')->name('cart.remove');

    Route::post("/orders", 'OrdersController@store')->name('orders.store');
    Route::get("/orders", 'OrdersController@index')->name('orders.index');

    Route::group(['middleware' => 'emailVerify'], function () {
        Route::get('/test', function () {
            return '已认证邮箱';
        });
    });
});
Route::resource('products', 'ProductsController', ['only' => ['index', 'show']]);
