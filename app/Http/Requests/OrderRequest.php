<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;
use App\Models\ProductSku;

class OrderRequest extends FormRequest
{
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
              Rule::exists('user_addresses', "id")->where(function ($query) {
                  $query->where('user_id', $this->user()->id);
              })
            ],
            'items' => 'required|array',
            'items.*.sku_id' => [
              'required',
              function ($attribute, $value, $fail) {
                  //判断商品是否上架
                  if (!$productSku = ProductSku::find($value)) {
                      return $fail("该商品不存在");
                  }

                  if (!$productSku->product->on_sale) {
                      return $fail('该商品已下架');
                  }

                  if ($productSku->stock == 0) {
                      return $fail('该商品已售完');
                  }

                  preg_match('/items\.(\d+)\.sku_id/', $attribute, $m);
                  $index  = $m[1];
                  $amount = $this->input['items'][$index]['amount'];
                  if ($amount > 0 && $amount > $productSku->stock) {
                      return $fail('库存不足');
                  }
              }
            ],
            'items.*.amount' => 'required|integer|min:1'
        ];
    }
}
