<?php

namespace App\Admin\Controllers;

use App\Models\Product;
use App\Models\Category;
use App\Http\Controllers\Controller;
use Encore\Admin\Controllers\HasResourceActions;
use Encore\Admin\Form;
use Encore\Admin\Grid;
use Encore\Admin\Layout\Content;
use Encore\Admin\Show;

class ProductsController extends CommonProductsController
{
    public function getProductType()
    {
        return Product::TYPE_NORMAL;
    }

    /**
     * Make a grid builder.
     *
     * @return Grid
     */
    public function customGrid(Grid $grid)
    {

        $grid->id('Id');
        $grid->title('名称');
        $grid->with(['category']);
        $grid->image('图片')->display(function ($value) {
            return '<image src="'.$this->image_url.'" width="50"/>';
        });
        $grid->on_sale('是否上架')->display(function ($value) {
            return $value ? '是':'否';
        });
        $grid->rating('评分');
        $grid->sold_count('销量');
        $grid->review_count('评论数');
        $grid->price('最低价格');
        $grid->column('category.name', '类目');
        $grid->created_at('创建时间');
        return $grid;
    }

    public function customForm(Form $form) {

    }

    
}
