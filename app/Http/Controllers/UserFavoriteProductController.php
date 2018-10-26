<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\Product;
use App\Models\User;
use App\Models\UserFavoriteProduct;
use Auth;

class UserFavoriteProductController extends Controller
{
    public function index()
    {
        $products = Auth::user()->favoriteProducts()->paginate(16);
        // dd($products);
        return view('products.favorites', ['products' => $products]);
    }


    public function addFavorite(Product $product)
    {
        $user = Auth::user();

        //判断是否已经收藏
        if ($user->favoriteProducts()->find($product->id)) {
            return [];
        }
        $user->favoriteProducts()->attach($product);
        return [];
    }

    public function delFavorite(Product $product)
    {
        $user = Auth::user();
        $user->favoriteProducts()->detach($product);
        return [];
    }
}
