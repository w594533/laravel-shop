<?php

namespace App\Http\Controllers\Api;

use Illuminate\Http\Request;
use App\Http\Controllers\Api\Controller;
use App\Http\Requests\Api\AddCartRequest;
use App\Models\CartItem;
use App\Models\ProductSku;

class CartsController extends Controller
{
    public function store(AddCartRequest $request)
    {
        $user = $request->user();
        //判断是否有相同的产品在购物车
        if($cart_item = $user->cartItems()->where('product_sku_id', $request->sku_id)->first()){
            $cart_item->amount = $cart_item->amount + $request->amount;
            $cart_item->save();
        } else {
            $cart_item = new CartItem(['amount' => $request->amount]);
            $cart_item->user()->associate($user);
            $cart_item->productSku()->associate($request->sku_id);
            $cart_item->save();
        }
        return $this->response->created();
    }

    public function remove(Request $request)
    {
        //可以删除多个，也可以删除一个
        $skuIds = $request->sku_ids;
        if (!is_array($skuIds)) {
            $productSkuIds = [$skuIds];
        } else {
            $productSkuIds = $skuIds;
        }
        \Auth::user()->cartItems()->whereIn('product_sku_id', $productSkuIds)->delete();
        return $this->response->noContent();
    }
}
