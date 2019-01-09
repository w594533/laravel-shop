<?php

namespace App\Http\Requests\Api;

use Illuminate\Foundation\Http\FormRequest;
use App\Models\ProductSku;
use App\Models\Product;

class AddCartRequest extends FormRequest
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
            'amount' => 'required|integer|min:1',
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

                    if($sku->stock <= 0) {
                        return $fail('商品已售完');
                    }

                    if($this->input('amount') && $this->input('amount') > $sku->stock) {
                        return $fail('库存不足');
                    }
                    
                }
            ]
        ];
    }

    public function messages()
    {
        return [
        'sku_id.required' => '请选择一个商品',
        'amount.required' => '请输入商品数量',
        'amount.min' => '商品数量至少为1'
      ];
    }
}
