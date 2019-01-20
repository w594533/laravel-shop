<?php

namespace App\Jobs;

use Illuminate\Bus\Queueable;
use Illuminate\Queue\SerializesModels;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;
use App\Models\CrowdfundingProduct;
use App\Services\OrderService;
use App\Models\Product;
use App\Models\Order;

class RefundCrowdfundingOrders implements ShouldQueue
{
    use Dispatchable, InteractsWithQueue, Queueable, SerializesModels;

    protected $crowdfunding;
    /**
     * Create a new job instance.
     *
     * @return void
     */
    public function __construct(CrowdfundingProduct $crowdfunding)
    {
        $this->crowdfunding = $crowdfunding;
    }

    /**
     * Execute the job.
     *
     * @return void
     */
    public function handle()
    {
        if ($this->crowdfunding->status !== CrowdfundingProduct::STATUS_FAIL) {
            return;
        }

        $orderService = app(OrderService::class);
        //查询所有参与此众筹的订单，然后退款
        Order::query()
            // 订单类型为众筹商品订单
            ->where('type', Product::TYPE_CROWDFUNDING)
            ->whereNull('refund_no')
            ->whereNotNull('paid_at')
            ->whereHas('items', function($query) {
                return $query->where('product_id', $this->crowdfunding->product_id);
            })
            ->get()
            ->each(function(Order $order) use ($orderService) {
                //todo 执行退款
                $orderService->refundOrder($order);
            });
    }
}
