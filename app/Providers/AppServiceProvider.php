<?php

namespace App\Providers;

use Illuminate\Support\ServiceProvider;
use Monolog\Logger;
use Yansongda\Pay\Pay;

class AppServiceProvider extends ServiceProvider
{
    /**
     * Bootstrap any application services.
     *
     * @return void
     */
    public function boot()
    {
        \App\Models\Order::observe(\App\Observers\OrderObserver::class);

        //打印sql执行记录
        if (config('app.debug')) {
            \DB::listen(function ($query) {
                $tmp = str_replace('?', '"'.'%s'.'"', $query->sql);
                $tmp = vsprintf($tmp, $query->bindings);
                $tmp = str_replace("\\", "", $tmp);
                \Log::info($tmp."\n\n\t");
            });
        }
    }

    /**
     * Register any application services.
     *
     * @return void
     */
    public function register()
    {
        $this->app->singleton('alipay', function () {
            $config = config('pay.alipay');
            if (app()->environment() !== 'production') {
                $conig['mode'] = 'dev';
                $config['log']['level'] = Logger::DEBUG;
            } else {
                $config['log']['level'] = Logger::WARNING;
            }

            return Pay::alipay($config);
        });

        $this->app->singleton('wechat_pay', function () {
            $config = config('pay.wechat');
            if (app()->environment() !== 'production') {
                $conig['mode'] = 'dev';
                $config['log']['level'] = Logger::DEBUG;
            } else {
                $config['log']['level'] = Logger::WARNING;
            }
            return Pay::wechat($config);
        });
    }
}
