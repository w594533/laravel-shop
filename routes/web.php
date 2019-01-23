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
// 后端回调不能放在 auth 中间件中
Route::get('installments/alipay/return', 'InstallmentsController@alipayReturn')->name('installments.alipay.return');
Route::post('installments/alipay/notify', 'InstallmentsController@alipayNotify')->name('installments.alipay.notify');

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
    Route::post("/orders/{order}/received", 'OrdersController@received')->name('orders.received');

    Route::get('orders/{order}/review', 'OrdersController@review')->name('orders.review.show');
    Route::post('orders/{order}/sendReview', 'OrdersController@sendReview')->name('orders.review.store');

    Route::post("orders/{order}/sendRefund", 'OrdersController@sendRefund')->name('orders.refund.store');

    Route::get('/payment/{order}/alipay', 'PaymentController@payByAlipay')->name('payment.alipay');
    Route::get('/payment/{order}/wechat', 'PaymentController@payByWechat')->name('payment.wechat');

    Route::post('/coupon_codes', 'CouponCodesController@show')->name('coupon_codes.show');

    Route::post('crowdfunding_orders', 'OrdersController@crowdfunding')->name('crowdfunding_orders.store');
    
    Route::post('payment/{order}/installment', 'PaymentController@payByInstallment')->name('payment.installment');


    Route::get('installments/{installment}/alipay', 'InstallmentsController@payByAlipay')->name('installments.alipay');
    
    Route::get('installments', 'InstallmentsController@index')->name('installments.index');
    Route::get('installments/{installment}', 'InstallmentsController@show')->name('installments.show');
    
    Route::group(['middleware' => 'emailVerify'], function () {
        Route::resource('user_addresses', 'UserAddressController');
        Route::get('/test', function () {
            return '已认证邮箱';
        });
    });
});
Route::resource('products', 'ProductsController', ['only' => ['index', 'show']]);
