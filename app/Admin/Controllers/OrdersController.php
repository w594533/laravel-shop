<?php

namespace App\Admin\Controllers;

use App\Models\Order;
use App\Models\Product;
use App\Models\CrowdfundingProduct;
use App\Http\Controllers\Controller;
use Encore\Admin\Controllers\HasResourceActions;
use Encore\Admin\Form;
use Encore\Admin\Grid;
use Encore\Admin\Layout\Content;
use Encore\Admin\Show;
use Encore\Admin\Widgets\Form as CustomForm;
use Encore\Admin\Widgets\Box;
use Illuminate\Http\Request;
use Validator;
use App\Exceptions\InvalidRequestException;
use App\Services\OrderService;

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
            ->header('订单列表')
            // ->description('description')
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
        $content
            ->header('订单详情')
            // ->description('')
            ->body($this->detail($id));

        $order = Order::findOrFail($id);
        //发货
        if ($order->paid_at && $order->ship_status === Order::SHIP_STATUS_PENDING) {
            if (
                $order->type === Product::TYPE_NORMAL || 
                ($order->type === product::TYPE_CROWDFUNDING && $order->items[0]->product->crowdfunding->status === CrowdfundingProduct::STATUS_SUCCESS)
            ) {
                $content->body($this->shipForm($id));
            }
        }

        //处理退款
        if ($order->refund_status === Order::REFUND_STATUS_APPLIED) {
            $content->body($this->dealRefundForm($id));
        }
        return $content;
    }

    //发货
    public function ship(Order $order, Request $request)
    {
        // 判断当前订单是否已支付
        if (!$order->paid_at) {
            throw new InvalidRequestException('该订单未付款');
        }

        if (
            ($order->type === product::TYPE_CROWDFUNDING && $order->items[0]->product->crowdfunding->status !== CrowdfundingProduct::STATUS_SUCCESS)
        ) {
            throw new InvalidRequestException('众筹订单未成功，不可发货');
        }

        // 判断当前订单发货状态是否为未发货
        if ($order->ship_status !== Order::SHIP_STATUS_PENDING) {
            throw new InvalidRequestException('该订单已发货');
        }
        // Laravel 5.5 之后 validate 方法可以返回校验过的值
        $data = $this->validate($request, [
            'express_company' => ['required'],
            'express_no' => ['required'],
        ], [], [
            'express_company' => '物流公司',
            'express_no' => '物流单号',
        ]);

        $order->update(['ship_status' => Order::SHIP_STATUS_DELIVERED, 'ship_data' => $data]);
        // 返回上一页
        admin_success("success", "发货成功");
        return redirect()->back();
    }

    public function shipForm($id)
    {
        $form = new CustomForm();
        $form->disablePjax();
        $form->disableReset();
        $form->action(route('admin.orders.ship', [$id]));
        $form->text('express_company', '物流公司');
        $form->text('express_no', '物流编号');
        $box = new Box('发货设置', $form->render());
        $box->style('info');
        return $box;
    }

    //退款
    public function refund(Order $order, Request $request)
    {
        if ($order->refund_status !== Order::REFUND_STATUS_APPLIED) {
            throw new InvalidRequestException('退款状态错误');
        }
        $v = Validator::make($request->all(), [
            'agree' => 'required'
        ]);
        $v->sometimes('refund_disagree_reason', 'required|max:500', function ($request) {
            return $request->agree;
        });

        $extra = $order->extra ? : [];
        if ($request->input('agree')) {
            //开始处理退款
            $orderService = app(OrderService::class);
            $result = $orderService->refundOrder($order);
            if ($result) {
                admin_success('SUCCESS', '退款成功');
            } else {
                admin_error('ERROR', '退款失败');
            }
            return redirect()->back();
        } else {
            $refund_status = Order::REFUND_STATUS_PENDING;
            $extra['refund_disagree_reason'] = $request->input('refund_disagree_reason');
            $order->update([
                'refund_status' => $refund_status,
                'extra' => $extra
            ]);
            admin_success('success', '退款处理成功');
            return redirect()->back();
        }

    }

    public function dealRefundForm($id)
    {
        $form = new CustomForm();
        $form->disablePjax();
        $form->disableReset();
        $form->action(route('admin.orders.refund', [$id]));
        $form->radio('agree', '处理结果')->options([true => '同意', false => '拒绝'])->default(true);
        $form->text('refund_disagree_reason', '拒绝理由');
        $box = new Box('退款处理', $form->render());
        $box->style('info');
        return $box;
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
        $grid->model()->orderBy('paid_at', 'desc');
        $grid->no('订单流水号');
        $grid->column('user.name', '买家');
        $grid->total_amount('总金额')->sortable();
        $grid->paid_at('支付时间')->sortable();
        $grid->refund_status('退款状态')->display(function ($value) {
            return Order::$refundStatusMap[$value];
        });
        $grid->ship_status('物流状态')->display(function ($value) {
            return Order::$shipStatusMap[$value];
        });

        $grid->disableCreateButton();
        $grid->actions(function ($actions) {
            $actions->disableDelete();
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
        $order = Order::findOrFail($id);
        $show = new Show($order);
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
        $show->ship_status('物流状态')->as(function ($value) {
            return Order::$shipStatusMap[$value];
        });
        $show->ship_data('物流信息')->as(function ($value) {
            return $value ? implode(" ", $value) : '';
        });
        $show->remark('备注');
        $show->ship('物流', function ($show) {
            $form = new Form();
            $form->email('email')->default('qwe@aweq.com');
            $form->password('password');
            $form->text('name', '输入框');
            return $form->render();
        });
        $show->user('买家', function ($user) {
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
