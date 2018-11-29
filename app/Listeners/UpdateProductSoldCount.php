<?php

namespace App\Listeners;

use App\Events\OrderPaid;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Contracts\Queue\ShouldQueue;
use App\Models\OrderItem;

//  implements ShouldQueue 代表此监听器是异步执行的
class UpdateProductSoldCount implements ShouldQueue
{
    /**
     * Create the event listener.
     *
     * @return void
     */
    public function __construct()
    {
        //
    }

    /**
     * Handle the event.
     *
     * @param  OrderPaid  $event
     * @return void
     */
    public function handle(OrderPaid $event)
    {
        $order = $event->getOrder();
        // 预加载商品数据
        $order->load('items.product');

        foreach ($order->items as $order_item) {
            $product = $order_item->product;

            $sold_count = OrderItem::query()
                          ->where('product_id', $product->id)
                          ->whereHas('order', function ($query) {
                              return $query->whereNotNull('paid_at');
                          })->sum('amount');

            $product->update(['sold_count' => $sold_count]);
        }
    }
}
