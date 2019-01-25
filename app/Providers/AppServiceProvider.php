<?php

namespace App\Providers;

use Illuminate\Support\ServiceProvider;
use Elasticsearch\ClientBuilder as ESClientBuilder;
use Monolog\Logger;
use Yansongda\Pay\Pay;
use Carbon\Carbon;

class AppServiceProvider extends ServiceProvider
{
    /**
     * Bootstrap any application services.
     *
     * @return void
     */
    public function boot()
    {
        Carbon::setLocale('zh');
        \App\Models\Order::observe(\App\Observers\OrderObserver::class);

        // 当 Laravel 渲染 products.index 和 products.show 模板时，就会使用 CategoryTreeComposer 这个来注入类目树变量
        // 同时 Laravel 还支持通配符，例如 products.* 即代表当渲染 products 目录下的模板时都执行这个 ViewComposer
        \View::composer(['products.index', 'products.show'], \App\Http\ViewComposers\CategoryTreeComposer::class);
        
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
            $config['notify_url'] = ngrok_url('payment.alipay.notify');
            if (app()->environment() !== 'production') {
                $config['mode'] = 'dev';
                $config['log']['level'] = Logger::DEBUG;
                $config['notify_url'] = ngrok_url('payment.alipay.notify');
            } else {
                $config['log']['level'] = Logger::WARNING;
            }

            return Pay::alipay($config);
        });

        $this->app->singleton('wechat_pay', function () {
            $config = config('pay.wechat');
            $config['notify_url'] = ngrok_url('payment.wechat.notify');
            if (app()->environment() !== 'production') {
                $config['mode'] = 'dev';
                $config['log']['level'] = Logger::DEBUG;
            } else {
                $config['log']['level'] = Logger::WARNING;
            }
            return Pay::wechat($config);
        });

        // 注册一个名为 es 的单例
        $this->app->singleton('es', function () {
            // 从配置文件读取 Elasticsearch 服务器列表
            $builder = ESClientBuilder::create()->setHosts(config('database.elasticsearch.hosts'));
            // 如果是开发环境
            if (app()->environment() === 'local') {
                // 配置日志，Elasticsearch 的请求和返回数据将打印到日志文件中，方便我们调试
                $builder->setLogger(app('log')->driver());
            }

            return $builder->build();
        });
    }
}
