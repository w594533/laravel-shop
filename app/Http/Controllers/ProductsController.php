<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\Product;
use App\Exceptions\InvalidRequestException;

class ProductsController extends Controller
{
    public function index(Request $request)
    {
        $builder = Product::query()->where('on_sale', true);

        //search搜索
        if ($search = $request->input('search', '')) {
            $like = '%'.$search.'%';
            $builder->where(function ($query) use ($like) {
                $query->where('title', 'like', $like)
                    ->orWhere('description', 'like', $like)
                    ->orWhereHas('skus', function ($query) use ($like) {
                        $query->where('title', 'like', $like)
                            ->orWhere('description', 'like', $like);
                    });
            });
        }


        //order排序
        if ($order = $request->input('order', '')) {
            if (preg_match('/^(.+)_(asc|desc)$/', $order, $m)) {
                $builder->orderBy($m[1], $m[2]);
            }
        }
        $products = $builder->paginate(16);

        return view(
            'products.index',
            [
          'products' => $products,
          'filters' => [
            'search' => $search,
            'order' => $order
            ]
          ]
        );
    }

    public function show(Product $product)
    {
        if (!$product->on_sale) {
            throw new InvalidRequestException('商品已下架');
        }
        return view('products.show', ['product' => $product]);
    }
}
