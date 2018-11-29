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

Route::get("/payment/alipay/return", 'PaymentController@alipayReturn')->name('payment.alipay.return');
Route::post("/payment/alipay/notify", 'PaymentController@alipayNotify')->name('payment.alipay.notify');
Route::post("/payment/wechat/notify", 'PaymentController@wechatPayNotify')->name('payment.wechat.notify');

Route::group(['middleware' => 'auth'], function () {
    Route::get('/email_verify_notice', 'PagesController@emailVerifyNotice')->name('email_verify_notice');

    Route::get('/email_verification/send', 'EmailVerificationController@send')->name('email_verification.send');
    Route::get('/email_verification/{token}', 'EmailVerificationController@verify')->name('email_verification.verify');



    Route::post('/products/{product}/favorite', 'UserFavoriteProductController@addFavorite')->name('user.favorite.product');
    Route::delete('/products/{product}/favorite', 'UserFavoriteProductController@delFavorite')->name('user.delfavorite.product');
    Route::get('/products/favorites', 'UserFavoriteProductController@index')->name('user.favorite.index');

    Route::post('/cart', 'CartController@add')->name('cart.add');
    Route::get('/cart', 'CartController@index')->name('cart.index');
    Route::delete('/cart/{productSku}', 'CartController@remove')->name('cart.remove');

    Route::post("/orders", 'OrdersController@store')->name('orders.store');
    Route::get("/orders", 'OrdersController@index')->name('orders.index');
    Route::get("/orders/{order}", 'OrdersController@show')->name('orders.show');

    Route::get('/payment/{order}/alipay', 'PaymentController@payByAlipay')->name('payment.alipay');
    Route::get('/payment/{order}/wechat', 'PaymentController@payByWechat')->name('payment.wechat');
    Route::group(['middleware' => 'emailVerify'], function () {
        Route::resource('user_addresses', 'UserAddressController');
        Route::get('/test', function () {
            return '已认证邮箱';
        });
    });
});
Route::resource('products', 'ProductsController', ['only' => ['index', 'show']]);
