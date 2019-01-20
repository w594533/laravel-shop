<?php

namespace App\Listeners;

use App\Models\Order;
use App\Models\Product;
use App\Events\OrderPaid;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Contracts\Queue\ShouldQueue;

class UpdateCrowdfundingProductProgress
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

        if($order->type !== Product::TYPE_CROWDFUNDING) {
            return;
        }

        $crowdfunding = $order->items()->first()->product()->crowdfunding();

        $result = Order::query()
                    // 查出订单类型为众筹订单
                    ->where('type', Product::TYPE_CROWDFUNDING)
                    ->whereNotNull('paid_at')
                    ->whereHas('items', function($query) {
                        return $query->where('product_id', $crowdfunding->product_id);
                    })
                    ->first([
                        \DB::raw('sum(total_amount) as total_amount'),
                        \DB::raw('count(distinct(user_id)) as user_count')
                    ]);

        $crowdfunding->update([
            'total_amount' => $data->total_amount,
            'user_count' => $data->user_count
        ]);
    }
}
