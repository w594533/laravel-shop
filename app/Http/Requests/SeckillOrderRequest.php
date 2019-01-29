<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;
use App\Models\Product;
use App\Models\ProductSku;
use App\Models\Order;

class SeckillOrderRequest extends FormRequest
{
    /**
     * Determine if the user is authorized to make this request.
     *
     * @return bool
     */
    public function authorize()
    {
        return true;
    }

    /**
     * Get the validation rules that apply to the request.
     *
     * @return array
     */
    public function rules()
    {
        return [
            'address_id' => [
                'required',
                Rule::exists('user_addresses', 'id')->where('user_id', $this->user()->id)
            ],
            'sku_id' => function($attribute, $value, $fail) {
                if (!$sku = ProductSku::find($value)) {
                    return $fail('商品不存在');
                }

                $product = $sku->product;
                if (!$product->on_sale) {
                    return $fail('商品已下架');
                }

                if($product->type !== Product::TYPE_SECKILL) {
                    return $fail('该商品不支持秒杀');
                }

                if ($sku->stock < 1) {
                    return $fail('该商品已售完');
                }

                $seckill_product = $product->seckill;

                if($seckill_product->is_before_start) {
                    return $fail('秒杀未开始');
                }

                if($seckill_product->is_after_end) {
                    return $fail('秒杀活动已结束');
                }

                if ($order = Order::query()
                                ->where('user_id', $this->user()->id)
                                ->whereHas('items', function($query) use ($value) {
                                    return $query->where('product_sku_id', $value);
                                })
                                ->where(function($query) {
                                    return $query->whereNotNull('paid_at')
                                                ->orWhere('closed', false);
                                })->first()) {
                    
                    if ($order->paid_at) {
                        return $fail('你已经抢购了该商品');
                    }

                    return $fail('你已经下单了该商品，请到订单页面支付');
                }

                
            }
        ];
    }
}
