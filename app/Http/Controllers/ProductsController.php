<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\Product;
use App\Models\OrderItem;
use App\Models\Category;
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

        //分类
        $category = null;
        $categories = [];
        if($category_id = $request->input('category_id', '')) {
            $category = Category::find($category_id);
            if ($category) {
                // 如果这是一个父类目
                if ($category->is_directory) {
                    // 则筛选出该父类目下所有子类目的商品
                    $builder->whereHas('category', function ($query) use ($category) {
                        $query->where('path', 'like', $category->path.$category->id.'-%');
                    });

                    $categories = $category->childrens;
                } else {
                    // 如果这不是一个父类目，则直接筛选此类目下的商品
                    $builder->where('category_id', $category->id);
                    
                }
            }
        } else {
            $categories = Category::where('level', 0)->get();
        }

        //order排序
        if ($order = $request->input('order', '')) {
            if (preg_match('/^(.+)_(asc|desc)$/', $order, $m)) {
                $builder->orderBy($m[1], $m[2]);
            }
        }

        
        $products = $builder->paginate(5);

        return view(
            'products.index',
            [
          'products' => $products,
          'category' => $category,
          'categories' => $categories,
          'filters' => [
            'search' => $search,
            'order' => $order,
            'category_id' => $category_id,
            ]
          ]
        );
    }

    public function show(Product $product, Request $request)
    {
        if (!$product->on_sale) {
            throw new InvalidRequestException('商品已下架');
        }

        //判断是否已经收藏
        $favor = false;
        if ($user = $request->user()) {
            $favor = boolval($user->favoriteProducts()->find($product->id));
        }

        //获取已经评价
        $reviews = OrderItem::query()
                        ->with(['order.user', 'productSku'])
                        ->where('product_id', $product->id)
                        ->whereNotNull('reviewed_at')
                        ->orderBy('reviewed_at', 'DESC')
                        ->limit(10)
                        ->get();

        // dd($reviews);
        return view('products.show', ['product' => $product, 'favor' => $favor, 'reviews' => $reviews]);
    }
}
