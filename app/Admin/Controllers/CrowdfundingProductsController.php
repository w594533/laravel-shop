<?php

namespace App\Admin\Controllers;

use App\Models\Product;
use App\Http\Controllers\Controller;
use Encore\Admin\Controllers\HasResourceActions;
use Encore\Admin\Form;
use Encore\Admin\Grid;
use Encore\Admin\Layout\Content;
use Encore\Admin\Show;
use App\Models\CrowdfundingProduct;
use App\Models\Category;

class CrowdfundingProductsController extends CommonProductsController
{
    public function getProductType()
    {
        return Product::TYPE_CROWDFUNDING;
    }

    /**
     * Make a grid builder.
     *
     * @return Grid
     */
    public function customGrid(Grid $grid)
    {
        
        $grid->id('ID')->sortable();
        $grid->title('商品名称');
        $grid->on_sale('是否上架')->display(function($vlaue) {
            return $vlaue ? '是':'否';
        });
        $grid->price('价格');
        $grid->column('crowdfunding.target_amount', '目标金额');
        $grid->column('crowdfunding.end_at', '结束时间');
        $grid->column('crowdfunding.total_amount', '已经众筹金额');
        $grid->column('crowdfunding.status', '状态')->display(function($value) {
            return CrowdfundingProduct::$statusMap[$value];
        });
        return $grid;
    }

    public function customForm(Form $form) {
        // 添加众筹相关字段
        $form->text('crowdfunding.target_amount', '众筹目标金额')->rules('required|numeric|min:0.01');
        $form->datetime('crowdfunding.end_at', '众筹结束时间')->rules('required|date');
        return $form;
    }

    
}
