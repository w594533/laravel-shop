<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\Product;
use App\Models\OrderItem;
use App\Models\Category;
use App\Exceptions\InvalidRequestException;
use Illuminate\Pagination\LengthAwarePaginator;
use App\SearchBuilders\ProductSearchBuilder;
use App\Services\ProductService;

class ProductsController extends Controller
{
    public function index(Request $request)
    {
        $page = $request->input('page', 1);
        $perPage = 16;

        // 新建查询构造器对象，设置只搜索上架商品，设置分页
        $builder = (new ProductSearchBuilder())->onSale()->paginate($perPage, $page);

        //分类
        $category = null;
        $categories = [];
        if($category_id = $request->input('category_id', '')) {
            $category = Category::find($category_id);
            if ($category) {
                // 调用查询构造器的类目筛选
                $builder->category($category);
            }
        } else {
            $categories = Category::where('level', 0)->get();
        }

        //search搜索
        if ($search = $request->input('search', '')) {
            $keywords = array_filter(explode(' ', $search));
            // 调用查询构造器的关键词筛选
            $builder->keywords($keywords);
        }

        if($search || isset($category)) {
            $builder->aggregateProperties();
        }

        //从用户请求参数获取filters
        $propertyFilters = [];
        if($filterString = $request->input('filters')) {
            $filterArray = explode('|', $filterString);

            foreach($filterArray as $filter) {
                list($name, $value) = explode(":", $filter);

                $propertyFilters[$name] = $value;
                // 调用查询构造器的属性筛选
                $builder->propertyFilter($name, $value);
            }
        }

        //order排序
        if ($order = $request->input('order', '')) {
            if (preg_match('/^(.+)_(asc|desc)$/', $order, $m)) {
                if (in_array($m[1], ['price', 'sold_count', 'rating'])) {
                    // 调用查询构造器的排序
                    $builder->orderBy($m[1], $m[2]);
                }
            }
        }

        $result = app('es')->search($builder->getParams());

        //通过collect函数将返回结果转为集合，通过pluck返回id数组
        $productIds = collect($result['hits']['hits'])->pluck('_id')->all();

        $products = Product::query()->byIds($productIds)->get();
        // 返回一个 LengthAwarePaginator 对象
        $pager = new LengthAwarePaginator($products, $result['hits']['total'], $perPage, $page, [
            'path' => route('products.index', false), // 手动构建分页的 url
        ]);

        // dd($result['aggregations']);
        $properties = [];
        if (isset($result['aggregations'])) {
            //使用collect 函数将返回值转为集合
            $properties = collect($result['aggregations']['properties']['properties']['buckets'])
                                ->map(function($bucket) {
                                    return [
                                        'key' => $bucket['key'],
                                        'values' => collect($bucket['value']['buckets'])->pluck('key')->all()
                                    ];
                                })
                                ->filter(function($property) use ($propertyFilters) {
                                    return count($property['values']) > 1 && !isset($propertyFilters[$property['key']]);
                                });
        }
        


        return view(
            'products.index',
            [
          'products' => $pager,
          'category' => $category,
          'categories' => $categories,
          'properties' => $properties,
          'propertyFilters' => $propertyFilters,
          'filters' => [
            'search' => $search,
            'order' => $order,
            'category_id' => $category_id
            ]
          ]
        );
    }

    public function show(Product $product, Request $request,ProductService $service)
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


        $similarProductIds = $service->getSimilarProductIds($product, 4);
        $similarProducts   = Product::query()->byIds($similarProductIds)->get();

        return view('products.show', [
            'product' => $product,
            'favor' => $favor,
            'reviews' => $reviews,
            'similar' => $similarProducts
        ]);
    }
}
