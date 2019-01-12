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
    use HasResourceActions;

    /**
     * Index interface.
     *
     * @param Content $content
     * @return Content
     */
    public function index(Content $content)
    {
        return $content
            ->header('优惠券')
            ->description('')
            ->body($this->grid());
    }

    /**
     * Show interface.
     *
     * @param mixed $id
     * @param Content $content
     * @return Content
     */
    public function show($id, Content $content)
    {
        return $content
            ->header('Detail')
            ->description('description')
            ->body($this->detail($id));
    }

    /**
     * Edit interface.
     *
     * @param mixed $id
     * @param Content $content
     * @return Content
     */
    public function edit($id, Content $content)
    {
        return $content
            ->header('Edit')
            ->description('description')
            ->body($this->form()->edit($id));
    }

    /**
     * Create interface.
     *
     * @param Content $content
     * @return Content
     */
    public function create(Content $content)
    {
        return $content
            ->header('Create')
            ->description('description')
            ->body($this->form());
    }

    /**
     * Make a grid builder.
     *
     * @return Grid
     */
    protected function grid()
    {
        $grid = new Grid(new CouponCode);

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
        $grid->start_time('开始使用时间');
        $grid->end_time('结束使用时间');
        // $grid->created_at('Created at');
        // $grid->updated_at('Updated at');

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

        $form->text('no', '优惠码');
        $form->radio('type', '类型')->options(['reduction'=>'满减', 'discount'=>'折扣'])->rules('required');
        $form->decimal('amount', '金额')->default(0.00)->rules('required|numeric|min:0');
        $form->decimal('offer', '优惠')->default(0.00)->rules(function($form) {
            dd($form->model());
            if ($form->model()->type === 'discount') {
                 return 'required|numeric|betweent:1,99';
            } else {
                 return 'required|numeric|min:0.01';
            }
        });
        $form->number('total', '总数量')->default(1);
        $form->datetime('start_time', '优惠开始时间')->default(date('Y-m-d H:i:s'));
        $form->datetime('end_time', '优惠结束时间')->default(date('Y-m-d H:i:s'));

        $form->saving(function (Form $form) {
            if (!$form->no) {
                $form->no = CouponCode::findAvalableNo();
            }
        });
        return $form;
    }
}
