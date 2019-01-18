<?php

namespace App\Admin\Controllers;

use App\Models\CouponCode;
use App\Http\Controllers\Controller;
use Encore\Admin\Controllers\HasResourceActions;
use Encore\Admin\Form;
use Encore\Admin\Grid;
use Encore\Admin\Layout\Content;
use Encore\Admin\Show;

class CouponCodesController extends Controller
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
    protected function grid()
    {
        $grid = new Grid(new CouponCode);

        $grid->model()->orderBy('id', 'desc');


        $grid->id('Id');
        $grid->no('优惠码');
        $grid->type('类型')->display(function($value){
            $str = '';
            if ($value === 'reduction') {
                $str = '减'.str_replace('.00', '', $this->offer);
            } else if ($value === 'discount') {
                $str = '优惠'.str_replace('.00', '', $this->offer)."%";
            }

            return '满'.$this->amount.$str;
        });
        $grid->total('数量');
        $grid->used('已使用');
        $grid->start_time('开始使用时间');
        $grid->end_time('结束使用时间');
        // $grid->created_at('Created at');
        // $grid->updated_at('Updated at');

        $grid->actions(function ($actions) {
            $actions->disableView();
        });
        return $grid;
    }

    /**
     * Make a show builder.
     *
     * @param mixed $id
     * @return Show
     */
    protected function detail($id)
    {
        $show = new Show(CouponCode::findOrFail($id));

        $show->id('Id');
        $show->no('No');
        $show->type('Type');
        $show->amount('Amount');
        $show->offer('Offer');
        $show->total('Total');
        $show->start_time('Start time');
        $show->end_time('End time');
        $show->created_at('Created at');
        $show->updated_at('Updated at');

        return $show;
    }

    /**
     * Make a form builder.
     *
     * @return Form
     */
    protected function form()
    {
        $form = new Form(new CouponCode);


        $form->text('no', '优惠码')->rules('nullable|unique:coupon_codes');
        $form->radio('type', '类型')->options(['reduction'=>'固定金额', 'discount'=>'折扣'])->rules('required');
        $form->decimal('amount', '最低使用金额')->default(0.00)->rules('required|numeric|min:0');
        $form->decimal('offer', '折扣')->rules(function ($form) {
            if (request()->input('type') === 'discount') {
                // 如果选择了百分比折扣类型，那么折扣范围只能是 1 ~ 99
                return 'required|numeric|between:1,99';
            } else {
                // 否则只要大等于 0.01 即可
                return 'required|numeric|min:0.01';
            }
        });
        $form->number('total', '总数量')->default(1);
        $form->datetime('start_time', '优惠开始时间');
        $form->datetime('end_time', '优惠结束时间');

        $form->saving(function (Form $form) {
            if (!$form->no) {
                $form->no = CouponCode::findAvalableNo();
            }
        });

        $form->tools(function (Form\Tools $tools) {
            // 去掉`查看`按钮
            $tools->disableView();
        });

        $form->footer(function ($footer) {
            // 去掉`查看`checkbox
            $footer->disableViewCheck();
        
        });
        
        return $form;
    }
}
