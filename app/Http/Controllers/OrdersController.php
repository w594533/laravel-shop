<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Exceptions\InvalidRequestException;
use App\Exceptions\CouponCodeUnavailableException;
use App\Services\OrderService;
use App\Http\Requests\OrderRequest;
use App\Models\Order;
use App\Models\OrderItem;
use App\Models\ProductSku;
use App\Models\Product;
use App\Models\UserAddress;
use Carbon\Carbon;
use App\Jobs\ColseOrder;
use App\Http\Requests\OrderReviewRequest;
use App\Http\Requests\ApplyRefundRequest;
use App\Http\Requests\CrowdFundingOrderRequest;
use App\Events\OrderReviewed;
use App\Models\CouponCode;

class OrdersController extends Controller
{
    protected $orderService;
    public function __construct(OrderService $orderService)
    {
        $this->orderService = $orderService;
    }
    public function index(Request $request)
    {
        return view('orders.index', ['orders'=> $this->orderService->index()]);
    }

    public function show(Order $order)
    {
        $this->authorize('own', $order);
        return view('orders.view', ['order' => $order->load(['items.product', 'items.productSku'])]);
    }

    public function store(OrderRequest $request)
    {
        // 如果用户提交了优惠码
        $coupon = null;
        if ($code = $request->input('coupon_code')) {
            $coupon = CouponCode::where('no', $code)->first();
            if (!$coupon) {
                throw new CouponCodeUnavailableException('优惠券不存在');
            }
        }
        $user_address = UserAddress::find($request->address_id);
        $order = $this->orderService->store($user_address, $request->items, $request->remark, $coupon);
        return $order;
    }

    public function crowdfunding(CrowdFundingOrderRequest $request)
    {
        $user = $request->user();
        $user_address = UserAddress::find($request->input('address_id'));
        $sku = ProductSku::find($request->input('sku_id'));
        $amount = $request->input('amount');
        $order = $this->orderService->crowdfunding($user, $user_address, $sku, $amount);
        return $order;
    }

    public function received(Order $order)
    {
        $this->authorize('own', $order);
        $order->update(['ship_status' => Order::SHIP_STATUS_RECEIVED]);
        return $order;
    }

    public function review(Order $order)
    {
        $this->authorize('own', $order);
        if (!$order->paid_at) {
            throw new InvalidRequestException('订单未支付');
        }
        return view('orders.review', ['order'=>$order]);
    }

    public function sendReview(Order $order, OrderReviewRequest $request)
    {
        if (!$order->paid_at) {
            throw new InvalidRequestException('订单未支付');
        }

        if ($order->reviewed) {
            throw new InvalidRequestException("订单已评价");
        }

        $reviews = $request->reviews;
        \DB::transaction(function () use ($reviews, $order) {
            foreach ($reviews as $key => $review) {
                OrderItem::where('id', $review['id'])->update([
                  'rating' => intval($review['rating']),
                  'review' => $review['review'],
                  'reviewed_at' => Carbon::now()
                ]);
            }
            $order->update(['reviewed' => true]);
            event(new OrderReviewed($order));
        });

        return redirect()->back();
    }

    public function sendRefund(Order $order, ApplyRefundRequest $request)
    {
        if($order->type !== Product::TYPE_NORMAL) {
            throw new InvalidRequestException('订单不支持退款');
        }
        
        if (!$order->paid_at) {
            throw new InvalidRequestException('订单未支付');
        }

        if (!$order->canRefund()) {
            throw new InvalidRequestException('退款状态错误');
        }

        $extra = $order->extra ?: []; 
        $extra['refund_reason'] = $request->reason;
        
        $order->update(['extra'=>$extra, 'refund_status' => Order::REFUND_STATUS_APPLIED]);
        return $order;
    }
}
