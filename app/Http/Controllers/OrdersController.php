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
}
