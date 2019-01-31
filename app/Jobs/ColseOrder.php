<?php

namespace App\Jobs;

use Illuminate\Bus\Queueable;
use Illuminate\Queue\SerializesModels;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;
use App\Models\Order;

class ColseOrder implements ShouldQueue
{
    use Dispatchable, InteractsWithQueue, Queueable, SerializesModels;
    protected $order;
    /**
     * Create a new job instance.
     *
     * @return void
     */
    public function __construct(Order $order, $delay)
    {
        \Log::debug('order_ttl_close', $order->toArray());
        \Log::debug('order_ttl_delay', [$delay]);
        $this->order = $order;
        $this->delay = $delay;
    }

    /**
     * Execute the job.
     *
     * @return void
     */
    public function handle()
    {
        if ($this->order->paid_at) {
            return;
        }

        \DB::transaction(function () {
            \Log::debug('this close', [1]);
            $this->order->update(['closed' => true]);
            foreach ($this->order->items as $item) {
                $item->productSku->addStock($item->amount);
            }

            if ($this->order->couponCode) {
                $this->order->couponCode->changeUsed(false);
            }
        });
    }
}
