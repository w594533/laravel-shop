<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Exceptions\InvalidRequestException;
use App\Http\Requests\OrderRequest;
use App\Models\Order;
use App\Models\OrderItem;
use App\Models\ProductSku;
use App\Models\Product;
use App\Models\UserAddress;
use Carbon\Carbon;

class OrdersController extends Controller
{
    public function store(OrderRequest $request)
    {
        $user = $request->user();

        $order = \DB::transaction(function () use ($user, $request) {
            $user_address = UserAddress::find($request->address_id);
            $user_address->update(['last_used_at' => Carbon::now()]);
            //创建订单
            $order = new Order();
            $order->address = [
                'address'       => $user_address->full_address,
                'zip'           => $user_address->zip,
                'contact_name'  => $user_address->contact_name,
                'contact_phone' => $user_address->contact_phone,
              ];
            $order->remark = $request->remark;
            $order->total_amount = 0;
            $order->user()->associate($user);
            $order->save();

            $totalAmount = 0;
            $items = $request->items;
            foreach ($items as $key => $item) {
                $sku = ProductSku::find($item['sku_id']);
                $order_item = $order->items()->make([
                  'amount' => $item['amount'],
                  'price' => $sku->price
                ]);
                $order_item->product()->associate($sku->product_id);
                $order_item->productSku()->associate($sku);
                $order_item->save();
                $totalAmount += $sku->price * $item['amount'];
                if ($sku->decreaseStock($item['amount']) <= 0) {
                    throw new InvalidRequestException('该商品库存不足');
                }
            }

            //将商品从购物车中移除
            $skuIds = collect($items)->pluck('sku_id');
            $user->cartItems()->whereIn('product_sku_id', $skuIds)->delete();

            //更新总价
            $order->update(['total_amount' => $totalAmount]);

            return $order;
        });
        return $order;
    }
}
