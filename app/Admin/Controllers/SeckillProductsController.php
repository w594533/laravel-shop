<?php
namespace App\Admin\Controllers;

use App\Models\Product;
use App\Models\ProductSku;
use Encore\Admin\Form;
use Encore\Admin\Grid;

class SeckillProductsController extends CommonProductsController
{
    public function getProductType()
    {
        return Product::TYPE_SECKILL;
    }

    public function customGrid(Grid $grid)
    {
        $grid->id('ID')->sortable();
        $grid->title('商品名称');
        $grid->on_sale('已上架')->display(function ($value) {
            return $value ? '是' : '否';
        });
        $grid->price('价格');
        $grid->column('seckill.start_at', '开始时间');
        $grid->column('seckill.end_at', '结束时间');
        $grid->sold_count('销量');
    }

    public function customForm(Form $form)
    {
        // 秒杀相关字段
        $form->datetime('seckill.start_at', '秒杀开始时间')->rules('required|date');
        $form->datetime('seckill.end_at', '秒杀结束时间')->rules('required|date');

        $form->saved(function(Form $form) {
            $product = $form->model();

            //商品重新加载秒杀字段
            $product->load(['seckill']);

            //获取当前时间和秒杀时间的差值
            $diff = $product->seckill->end_at->getTimestamp() - time();

            //遍历商品sku
            $product->skus->each(function(ProductSku $sku) use($diff, $product) {
                // 如果秒杀商品是上架并且尚未到结束时间
                if ($product->on_sale && $diff > 0) {
                    \Redis::setex('seckill_sku_'.$sku->id, $diff, $sku->stock);
                } else {
                    // 否则将该 SKU 的库存值从 Redis 中删除
                    \Redis::del('seckill_sku_'.$sku->id);
                }
            });
        });
    }
}