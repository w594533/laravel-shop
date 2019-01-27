<?php

namespace App\Console\Commands\Elasticsearch;

use Illuminate\Console\Command;
use App\Models\Product;

class SyncProducts extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'es:sync-products';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = '同步商品数据到Elasticsearch';

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
        $es = app('es');

        Product::query()
                ->with(['skus', 'properties'])
                ->chunkById(100, function($products) use ($es) {
                    $this->info(sprintf('正在同步 %s 至 %s 范围的数据', $products->first()->id, $products->last()->id));
                    foreach($products as $product) {
                        $data = $product->toESArray();
                        $req['body'][] = [
                            'index' => [
                                '_index' => 'products',
                                '_type' => '_doc',
                                '_id' => $data['id']
                            ]
                        ];

                        $req['body'][] = $data;
                    }

                    try {
                        //使用bulk方法批量处理
                        $es->bulk($req);
                    } catch (\Exception $e) {
                        $this->error($e->message);
                    }
                    

                });
        $this->info('同步完成');
    }
}
