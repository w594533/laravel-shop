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
                $sql = $query->sql;
                $bindings = $query->bindings;
                $time = $query->time;
                //写入sql
                if ($bindings) {
                    \Log::info(date("Y-m-d H:i:s") . "]" . $sql . "\r\nparmars:" . json_encode($bindings, 320) . "\r\n\r\n");
                } else {
                    \Log::info("[" . date("Y-m-d H:i:s") . "]" . $sql . "\r\n\r\n");
                }
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
            $config['return_url'] = route('payment.alipay.return');
            $config['notify_url'] = route('payment.alipay.notify');
            if (app()->environment() !== 'production') {
                $config['mode'] = 'dev';
                $config['log']['level'] = Logger::DEBUG;
                $config['notify_url'] = 'http://requestbin.leo108.com/1jaufje1';
            } else {
                $config['log']['level'] = Logger::WARNING;
            }

            return Pay::alipay($config);
        });

        $this->app->singleton('wechat_pay', function () {
            $config = config('pay.wechat');
            $config['notify_url'] = route('payment.wechat.notify');
            if (app()->environment() !== 'production') {
                $config['mode'] = 'dev';
                $config['log']['level'] = Logger::DEBUG;
            } else {
                $config['log']['level'] = Logger::WARNING;
            }
            return Pay::wechat($config);
        });
    }
}
