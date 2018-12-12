<?php

namespace App\Admin\Controllers;

use App\Models\Order;
use App\Http\Controllers\Controller;
use Encore\Admin\Controllers\HasResourceActions;
use Encore\Admin\Form;
use Encore\Admin\Grid;
use Encore\Admin\Layout\Content;
use Encore\Admin\Show;

class OrdersController extends Controller
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
            ->header('Index')
            ->description('description')
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
        $grid = new Grid(new Order);

        // $grid->id('Id');
        $grid->no('订单流水号');
        $grid->column('user.name', '买家');
        // $grid->address('Address');
        $grid->total_amount('总金额')->sortable();
        // $grid->remark('Remark');
        $grid->paid_at('支付时间')->sortable();
        // $grid->payment_method('Payment method');
        // $grid->payment_no('Payment no');
        $grid->refund_status('退款状态')->display(function ($value) {
            return Order::$refundStatusMap[$value];
        });
        // $grid->refund_no('Refund no');
        // $grid->closed('Closed');
        // $grid->reviewed('Reviewed');
        $grid->ship_status('物流状态')->display(function ($value) {
            return Order::$shipStatusMap[$value];
        });
        // $grid->ship_data('Ship data');
        // $grid->extra('Extra');
        // $grid->created_at('Created at');
        // $grid->updated_at('Updated at');

        $grid->disableCreateButton();
        $grid->actions(function ($actions) {
            $actions->disableDelete();
            // $actions->disableView();
            $actions->disableEdit();
        });

        $grid->tools(function ($tools) {
            $tools->batch(function ($batch) {
                $batch->disableDelete();
            });
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
        $show = new Show(Order::findOrFail($id));
        $show->no('订单流水号');

        $show->address('收货地址')->as(function ($value) {
            return implode(" ", $value);
        });
        $show->total_amount('总金额');

        $show->paid_at('支付时间');
        $show->payment_method('支付方式');
        $show->payment_no('支付流水号');
        $show->refund_status('退款状态')->as(function ($value) {
            return Order::$refundStatusMap[$value];
        });
        $show->refund_no('退款流水号');
        // $show->closed('Closed');
        // $show->reviewed('Reviewed');
        $show->ship_status('物流状态')->as(function ($value) {
            return Order::$shipStatusMap[$value];
        });
        $show->ship_data('物流信息')->as(function ($value) {
            return $value ? implode(" ", $value):'';
        });
        $show->remark('备注');
        // $show->extra('Extra');
        // $show->created_at('Created at');
        // $show->updated_at('Updated at');
        $show->user('买家', function ($user) {
            // $user->setResource('/admin/users');
            $user->name('姓名');
            $user->panel()
                ->tools(function ($tools) {
                    $tools->disableEdit();
                    $tools->disableList();
                    $tools->disableDelete();
                });
        });
        $show->panel()
            ->tools(function ($tools) {
                $tools->disableEdit();
                // $tools->disableList();
                $tools->disableDelete();
            });
        return $show;
    }

    /**
     * Make a form builder.
     *
     * @return Form
     */
    protected function form()
    {
        $form = new Form(new Order);

        $form->text('no', 'No');
        $form->number('user_id', 'User id');
        $form->textarea('address', 'Address');
        $form->decimal('total_amount', 'Total amount')->default(0.00);
        $form->textarea('remark', 'Remark');
        $form->datetime('paid_at', 'Paid at')->default(date('Y-m-d H:i:s'));
        $form->text('payment_method', 'Payment method');
        $form->text('payment_no', 'Payment no');
        $form->text('refund_status', 'Refund status')->default('pending');
        $form->text('refund_no', 'Refund no');
        $form->switch('closed', 'Closed');
        $form->switch('reviewed', 'Reviewed');
        $form->text('ship_status', 'Ship status')->default('pending');
        $form->textarea('ship_data', 'Ship data');
        $form->textarea('extra', 'Extra');

        return $form;
    }
}
