<?php
namespace App\Services;

use Auth;
use App\Exceptions\InvalidRequestException;
use App\Exceptions\CouponCodeUnavailableException;
use App\Models\Order;
use App\Models\UserAddress;
use App\Models\OrderItem;
use App\Models\ProductSku;
use App\Models\Product;
use App\Models\User;
use App\Jobs\ColseOrder;
use Carbon\Carbon;
use App\Models\CouponCode;
use App\Jobs\RefundCrowdfundingOrders;
use App\Jobs\RefundInstallmentOrder;

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
    public function store(UserAddress $user_address, $items, $remark, CouponCode $coupon=null)
    {
        $user = Auth::user();

        // 如果传入了优惠券，则先检查是否可用
        if ($coupon) {
            // 但此时我们还没有计算出订单总金额，因此先不校验
            $coupon->checkAvailable($user);
        }

        $order = \DB::transaction(function () use ($user, $user_address, $items, $remark, $coupon) {
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
              'total_amount' => 0,
              'type' => Product::TYPE_NORMAL
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

            if ($coupon) {
                // 总金额已经计算出来了，检查是否符合优惠券规则
                $coupon->checkAvailable($user, $totalAmount);
                // 把订单金额修改为优惠后的金额
                $totalAmount = $coupon->getAdjustedPrice($totalAmount);
                // 将订单与优惠券关联
                $order->couponCode()->associate($coupon);
                // 增加优惠券的用量，需判断返回值
                if ($coupon->changeUsed() <= 0) {
                    throw new CouponCodeUnavailableException('该优惠券已被兑完');
                }
            }

            //将商品从购物车中移除
            $skuIds = collect($items)->pluck('sku_id');
            app(CartService::class)->remove($skuIds);

            //更新总价
            $order->update(['total_amount' => $totalAmount]);

            \Log::debug('order_ttl', config('app.order_ttl'));

            ColseOrder::dispatch($order, config('app.order_ttl'));

            return $order;
        });
        return $order;
    }

    public function crowdfunding(User $user, UserAddress $user_address, ProductSku $sku, $amount)
    {
        //开启事务
        return \DB::transaction(function() use ($user, $user_address, $sku, $amount){
            $user_address->update(['last_used_at' => Carbon::now()]);
            //创建订单
            $order = new Order([
              'address' => [
                'address'       => $user_address->full_address,
                'zip'           => $user_address->zip,
                'contact_name'  => $user_address->contact_name,
                'contact_phone' => $user_address->contact_phone,
              ],
              'remark' => '',
              'total_amount' => $sku->price * $amount,
              'type' => Product::TYPE_CROWDFUNDING
            ]);
            $order->user()->associate($user);
            $order->save();

            $item = $order->items()->make([
                'amount' => $amount,
                'price' => $sku->price
            ]);

            $item->product()->associate($sku->product_id);
            $item->productSku()->associate($sku);
            $item->save();

            // 扣减对应 SKU 库存
            if ($sku->decreaseStock($amount) <= 0) {
                throw new InvalidRequestException('该商品库存不足');
            }

            // 众筹结束时间减去当前时间得到剩余秒数
            $crowdfundingTtl = $sku->product->crowdfunding->end_at->getTimestamp() - time();

            ColseOrder::dispatch($order, min(config('app.order_ttl'), $crowdfundingTtl));

            return $order;
        });
    }

    public function seckill(User $user, UserAddress $user_address, ProductSku $sku)
    {
        //开启事务
        return \DB::transaction(function() use ($user, $user_address, $sku){
            $user_address->update(['last_used_at' => Carbon::now()]);
            $amount = 1;
            //创建订单
            $order = new Order([
              'address' => [
                'address'       => $user_address->full_address,
                'zip'           => $user_address->zip,
                'contact_name'  => $user_address->contact_name,
                'contact_phone' => $user_address->contact_phone,
              ],
              'remark' => '',
              'total_amount' => $sku->price * $amount,
              'type' => Product::TYPE_SECKILL
            ]);
            $order->user()->associate($user);
            $order->save();

            $item = $order->items()->make([
                'amount' => 1,
                'price' => $sku->price
            ]);

            $item->product()->associate($sku->product_id);
            $item->productSku()->associate($sku);
            $item->save();

            // 扣减对应 SKU 库存
            if ($sku->decreaseStock(1) <= 0) {
                throw new InvalidRequestException('该商品库存不足');
            }

            \Log::debug('seckill_order_ttl', config('app.seckill_order_ttl'));

            ColseOrder::dispatch($order, config('app.seckill_order_ttl'));

            return $order;
        });
    }

    public function refundOrder(Order $order)
    {
        if ($order->payment_method === 'wechat_pay') {
            //todo
            return true;
        } else if ($order->payment_method === 'alipay') {
            $refund_no = Order::findAvailableRefundNo();
            $result = app('alipay')->refund([
                'out_trade_no' => $order->no, // 之前的订单流水号
                'refund_amount' => 1, //$order->total_amount, // 退款金额，单位元
                'out_request_no' => $refund_no, // 退款订单号
            ]);

            //根据支付宝文档，如果返回了业务返回码，说明发生了错误
            if ($result->sub_code) {
                $extra = $order->extra;
                $extra['refund_failed_code'] = $refund_no;
                $order->update([
                    'refund_status' => Order::REFUND_STATUS_FAILED,
                    'refund_no' => $refund_no,
                    'extra' => $extra
                ]);
                return false;
            } else {
                $order->update([
                    'refund_status' => Order::REFUND_STATUS_SUCCESS,
                    'refund_no' => $refund_no
                ]);
                return true;
            }
        } else if($order->payment_method === 'installment') {
            $order->update([
                'refund_no' => Order::findAvailableRefundNo(),
                'refund_status' => Order::REFUND_STATUS_PROCESSING
            ]);

            dispatch(new RefundInstallmentOrder($order));
            return true;
        } else {
            throw new InvalidRequestException('无效的支付方式');
        }
    }
}
