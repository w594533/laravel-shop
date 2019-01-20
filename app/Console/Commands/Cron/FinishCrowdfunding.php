<?php

namespace App\Console\Commands\Cron;

use Illuminate\Console\Command;
use App\Models\CrowdfundingProduct;
use App\Models\Order;
use App\Services\OrderService;
use Carbon\Carbon;
use App\Jobs\RefundCrowdfundingOrders;

class FinishCrowdfunding extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'cron:finish-crowdfunding';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = '结束众筹';

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
        // dd(CrowdfundingProduct::query()
        // ->with(['product'])
        // ->where('status', CrowdfundingProduct::STATUS_FUNDING)
        // ->where('end_at', '<=', Carbon::now())
        // ->get());
        CrowdfundingProduct::query()
            ->with(['product'])
            ->where('status', CrowdfundingProduct::STATUS_FUNDING)
            ->where('end_at', '<=', Carbon::now())
            ->get()
            ->each(function (CrowdfundingProduct $crowdfunding) {
                // dd($crowdfunding);
                if ($crowdfunding->target_amount > $crowdfunding->total_amount) {
                    $this->crowdfundingFail($crowdfunding);
                } else {
                    $this->crowdfundingSuccess($crowdfunding);
                }
            });
    }

    protected function crowdfundingSuccess(CrowdfundingProduct $crowdfunding)
    {
        $crowdfunding->update([
            'status' => CrowdfundingProduct::STATUS_SUCCESS
        ]);
    }

    protected function crowdfundingFail(CrowdfundingProduct $crowdfunding)
    {
        // 将众筹状态改为众筹失败
        $crowdfunding->update([
            'status' => CrowdfundingProduct::STATUS_FAIL,
        ]);

        dispatch(new RefundCrowdfundingOrders($crowdfunding));
    }
}
