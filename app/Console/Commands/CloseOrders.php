<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use App\Models\Order;
use Carbon\Carbon;

class CloseOrders extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'close:orders';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = '关闭未支付的订单';

    /**
     * Create a new command instance.
     *
     * @return void
     */
    public function __construct()
    {
        parent::__construct();
    }

    /**
     * Execute the console command.
     *
     * @return mixed
     */
    public function handle()
    {
        //获取某个时间前未支付的订单
        $orders = Order::query()->whereNull('paid_at')
                                      ->where('closed', false)
                                      ->where('created_at', '<=', Carbon::now()->subMinutes(config('app.order_ttl')))
                                      ->get();
        \DB::transaction(function () use ($orders) {
            foreach ($orders as $order) {
                // 将订单的 closed 字段标记为 true，即关闭订单
                $order->update(['closed' => true]);
                foreach ($order->items as $item) {
                    //归还库存
                    $item->productSku->addStock($item->amount);
                }
            }
        });
    }
}
