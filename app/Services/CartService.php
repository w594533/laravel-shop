<?php
namespace App\Services;

use Auth;
use App\Models\CartItem;

class CartService
{
    public function get()
    {
        $cart_items = Auth::user()->cartItems()->with(['productSku.product'])->get();
        return $cart_items;
    }

    public function add($skuId, $amount)
    {
        $user = Auth::user();

        //判断是否已经有相同产品在购物车
        if ($cart_item = $user->cartItems()->where('product_sku_id', $skuId)->first()) {
            $cart_item->update(['amount' => $cart_item->amount + $amount]);
        } else {
            $cart_item = new CartItem();
            $cart_item->amount = $amount;
            $cart_item->user()->associate($user);
            $cart_item->productSku()->associate($skuId);
            $cart_item->save();
        }
        return $cart_item;
    }

    public function remove($skuIds)
    {
        // 可以传单个 ID，也可以传 ID 数组
        if (!is_array($skuIds)) {
            $skuIds = [$skuIds];
        }
        Auth::user()->cartItems()->whereIn('product_sku_id', $skuIds)->delete();
    }
}
