<?php

namespace App\Http\Requests\Api;

use Illuminate\Foundation\Http\FormRequest;
use App\Models\ProductSku;
use Illuminate\Validation\Rule;

class OrderRequest extends FormRequest
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
                Rule::in($this->user()->addresses()->pluck("id")->toArray())
            ],
            'skus.*.amount' => 'required|integer|min:1',
            'skus.*.sku_id' => [
                'required',
                function($attribute, $value, $fail) {
                    $m = explode(".", $attribute);
                    if (!$sku = ProductSku::find($value)) {
                        return $fail('商品不存在');
                    }

                    if (!$sku->product->on_sale) {
                        return $fail('商品已下架');
                    }

                    if($sku->stock <= 0) {
                        return $fail('商品已售完');
                    }

                    $skus = $this->input('skus');
                    $item = $skus[$m[1]];
                    if ($item['amount'] && $item['amount'] > $sku->stock) {
                        return $fail('库存不足');
                    }
                }
            ]
        ];
    }

    public function messages()
    {
        return [
        'skus.*.sku_id.required' => '请选择一个商品',
        'skus.*.amount.required' => '请输入商品数量',
        'skus.*.amount.min' => '商品数量至少为1'
      ];
    }
}
