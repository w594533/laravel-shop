<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Http\Requests\AddCartRequest;
use App\Models\CartItem;
use App\Models\ProductSku;
use App\Services\CartService;
use Auth;

class CartController extends Controller
{
    protected $cartServices;
    public function __construct(CartService $cartService)
    {
        $this->cartService = $cartService;
    }
    public function index()
    {
        $user = Auth::user();
        $cart_items = $this->cartService->get();
        $addresses = $user->addresses()->orderBy('last_used_at', 'desc')->get();
        return view('carts.index', ['cart_items' => $cart_items, 'addresses' => $addresses]);
    }


    public function add(AddCartRequest $request)
    {
        $this->cartService->add($request->sku_id, $request->amount);
        return [];
    }

    public function remove(ProductSku $productSku, Request $request)
    {
        $this->cartService->remove($productSku->id);
        return [];
    }
}
