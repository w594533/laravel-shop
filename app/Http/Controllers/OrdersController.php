<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Exceptions\InvalidRequestException;
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
use App\Events\OrderReviewed;

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
        $user_address = UserAddress::find($request->address_id);
        $order = $this->orderService->store($user_address, $request->items, $request->remark);
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
}
