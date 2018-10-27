<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Http\Requests\AddCartRequest;
use App\Models\CartItem;
use App\Models\ProductSku;
use Auth;

class CartController extends Controller
{
    public function index()
    {
        $user = Auth::user();
        $cart_items = $user->cartItems()->with(['productSku.product'])->get();
        return view('carts.index', ['cart_items' => $cart_items]);
    }


    public function add(AddCartRequest $request)
    {
        $amount = $request->amount;
        $skuId = $request->sku_id;
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
        return [];
    }

    public function remove(ProductSku $productSku, Request $request)
    {
        $request->user()->cartItems()->where('product_sku_id', $productSku->id)->delete();
        return [];
    }
}
