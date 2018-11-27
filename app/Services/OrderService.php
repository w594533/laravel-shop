<?php
namespace App\Services;

use Auth;
use App\Exceptions\InvalidRequestException;
use App\Models\Order;
use App\Models\UserAddress;
use App\Models\OrderItem;
use App\Models\ProductSku;
use App\Jobs\ColseOrder;
use Carbon\Carbon;

class OrderService
{
    public function index()
    {
        $user = Auth::user();
        return Order::query()
                      ->where('user_id', $user->id)
                      ->with(['items.product', 'items.productSku'])
                      ->orderBy('created_at', 'desc')
                      ->paginate(20);
    }
    public function store(UserAddress $user_address, $items, $remark)
    {
        $user = Auth::user();

        $order = \DB::transaction(function () use ($user, $user_address, $items, $remark) {
            $user_address->update(['last_used_at' => Carbon::now()]);
            //创建订单
            $order = new Order([
              'address' => [
                'address'       => $user_address->full_address,
                'zip'           => $user_address->zip,
                'contact_name'  => $user_address->contact_name,
                'contact_phone' => $user_address->contact_phone,
              ],
              'remark' => $remark,
              'total_amount' => 0
            ]);
            $order->user()->associate($user);
            $order->save();

            $totalAmount = 0;
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
            app(CartService::class)->remove($skuIds);

            //更新总价
            $order->update(['total_amount' => $totalAmount]);

            ColseOrder::dispatch($order, config('app.order_ttl'));

            return $order;
        });
        return $order;
    }
}
