<?php

namespace App\Http\Controllers\Api;

use Illuminate\Http\Request;
use App\Http\Controllers\Api\Controller;
use App\Transformers\ProductTransformer;
use App\Transformers\ProductSkuTransformer;
use App\Models\Product;

class ProductsController extends Controller
{
    public function index(Request $request)
    {
        $query = Product::query()->where('on_sale', true);
        if ($search = $request->input('search')){
            $like = '%'.$search.'%';
            $query->where(function($query) use ($like) {
                return $query->where('title', 'like', $like)
                    ->orWhere('description', 'like', $like)
                    ->orWhereHas('skus', function($query) use($like){
                        return $query->where('title', 'like', $like)
                                ->orWhere('description', 'like', $like);
                    });
            });
        }
        if($order = $request->input('order')) {
            $order_params = explode('_', $order);
            $query->orderBy($order_params[0], $order_params[1]);
            
        }
        $products = $query->paginate(20);
        return $this->response->paginator($products, new ProductTransformer());
    }

    public function show(Product $product, Request $request)
    {
        if (!$product->on_sale) {
            return $this->response->errorBadRequest('产品已下架');
        }

        $favor = false;
        if ($request->user())
        {
            $favor = boolval($request->user()->favoriteProducts()->find($product->id));
        }
        return $this->response->item($product, new ProductTransformer())->setMeta([
            'favor' => $favor
        ]);
    }

    /**
     * 收藏或者取消收藏
     */
    public function favor(Product $product, Request $request)
    {
        $request->user()->favoriteProducts()->toggle([$product->id]);
        return $this->response->noContent();
    }

}
