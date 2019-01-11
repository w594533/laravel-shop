<?php

namespace App\Http\Controllers\Api;

use Illuminate\Http\Request;
use App\Http\Controllers\Api\Controller;
use App\Http\Requests\Api\OrderRequest;
use App\Models\UserAddress;
use App\Models\ProductSku;
use App\Models\OrderItem;
use App\Models\Order;
use App\Models\CartItem;
use App\Jobs\ColseOrder;

class OrdersController extends Controller
{
    public function store(OrderRequest $request)
    {
        $user_address = UserAddress::find(1);

        \DB::transaction(function() use ($request, $user_address) {
            $user = $request->user();
            $order = new Order([
                'address' => [
                    'address'       => $user_address->full_address,
                    'zip'           => $user_address->zip,
                    'contact_name'  => $user_address->contact_name,
                    'contact_phone' => $user_address->contact_phone,
                ],
                'remark' => $request->remark,
                'total_amount' => 0
            ]);
            $order->user()->associate($user);
            $order->save();

            $totalAmount = 0;

            foreach($request->skus as $item) {
                $productSku = ProductSku::where('id', $item['sku_id'])
                                    ->where('stock', '>=', $item['amount'])->first();
                
                if (!$productSku) {
                    return $this->response->errorBadRequest('商品库存不足');
                }
                $totalAmount += $productSku->price * $item['amount'];
                $order_item = new OrderItem([
                    'amount' => $item['amount'],
                    'price' => $productSku->price
                ]);
                $order_item->order()->associate($order);
                $order_item->product()->associate($productSku->product);
                $order_item->productSku()->associate($productSku);
                $order_item->save();

                if ($productSku->decreaseStock($item['amount']) <= 0) {
                    return $this->response->error('该商品库存不足');
                }
            }
            $order->update(['total_amount' => $totalAmount]);

            //删除购物车
            $skuIds = collect($request->skus)->pluck('sku_id');
            $user->cartItems()->whereIn('product_sku_id', $skuIds)->delete();

            ColseOrder::dispatch($order, config('app.order_ttl'));
        });

        return $this->response->noContent();
    }
}
