<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;
use App\Models\CrowdfundingProduct;
use App\Models\Product;
use App\Models\ProductSku;
use Illuminate\Validation\Rule;

class CrowdFundingOrderRequest extends FormRequest
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
            'sku_id' => [
                'required',
                function($attribute, $value, $fail) {
                    if (!$sku = ProductSku::find($value)) {
                        return $fail('商品不存在');
                    }

                    $product = $sku->product;
                    if (!$product->on_sale) {
                        return $fail('商品已下架');
                    }

                    if($product->type !== product::TYPE_CROWDFUNDING) {
                        return $fail('该商品不支持众筹');
                    }

                    if($product->crowdfunding->status !== CrowdfundingProduct::STATUS_FUNDING) {
                        return $fail('该商品众筹已结束');
                    }

                    if ($sku->stock === 0) {
                        return $fail('该商品已售完');
                    }

                    if ($sku->stock < $this->input('amount')) {
                        return $fail('库存不足');
                    }
                }
            ],
            'amount'     => ['required', 'integer', 'min:1'],
            'address_id' => [
                'required',
                Rule::exists('user_addresses', 'id')->where('user_id', $this->user()->id)
            ]
        ];
    }

    public function messages()
    {
        return [
            'amount.min' => '购买数量至少为1'
        ];
    }
}
