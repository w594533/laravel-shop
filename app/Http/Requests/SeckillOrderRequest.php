<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;
use App\Models\Product;
use App\Models\ProductSku;
use App\Models\Order;
use Illuminate\Auth\AuthenticationException;
use App\Exceptions\InvalidRequestException;

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
            'address.province'      => 'required',
            'address.city'          => 'required',
            'address.district'      => 'required',
            'address.address'       => 'required',
            'address.zip'           => 'required',
            'address.contact_name'  => 'required',
            'address.contact_phone' => 'required',
            'sku_id' => function($attribute, $value, $fail) {
                //从redis中读取数据
                $stock = \Redis::get('seckill_sku_'.$value);

                if(is_null($stock)) {
                    return $fail('该商品不存在');
                }

                if($stock < 1) {
                    return $fail('库存不足');
                }
                if (!$sku = ProductSku::find($value)) {
                    return $fail('商品不存在');
                }

                $sku = ProductSku::find($value);
                $seckill_product = $sku->product->seckill;
                if($seckill_product->is_before_start) {
                    return $fail('秒杀未开始');
                }

                if($seckill_product->is_after_end) {
                    return $fail('秒杀活动已结束');
                }

                if (!$user = \Auth::user()) {
                    throw new AuthenticationException('请先登录');
                }
                if (!$user->is_email_verified) {
                    throw new InvalidRequestException('请先验证邮箱');
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
