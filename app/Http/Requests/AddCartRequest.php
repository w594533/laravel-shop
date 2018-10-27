<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;
use App\Models\ProductSku;

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
            'sku_id' => [
              'required',
              function ($attribute, $value, $fail) {
                  if (!$sku = ProductSku::find($value)) {
                      return $fail('商品不存在');
                  }

                  if ($sku->stock === 0) {
                      return $fail('商品已售完');
                  }

                  if ($this->input('amount') > 0 && $sku->stock < $this->input('amount')) {
                      return $fail('该商品库存不足');
                  }
              },
            ],
            'amount' => 'required|integer|min:1'
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
