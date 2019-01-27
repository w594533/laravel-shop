<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\Product;
use App\Models\OrderItem;
use App\Models\Category;
use App\Exceptions\InvalidRequestException;
use Illuminate\Pagination\LengthAwarePaginator;

class ProductsController extends Controller
{
    public function index(Request $request)
    {
        $page = $request->input('page', 1);
        $perPage = 16;

        //构建查询
        $params = [
            'index' => 'products',
            'type' => '_doc',
            'body' => [
                'from' => ($page - 1) * $perPage,
                'size' => $perPage,
                'query' => [
                    'bool' => [
                        'filter' => [
                            ['term' => ['on_sale' => true]]
                        ]
                    ]
                ]
            ]
        ];

        //order排序
        if ($order = $request->input('order', '')) {
            if (preg_match('/^(.+)_(asc|desc)$/', $order, $m)) {
                if (in_array($m[1], ['price', 'sold_count', 'rating'])) {
                    // 根据传入的排序值来构造排序参数
                    $params['body']['sort'] = [[$m[1] => $m[2]]];
                }
            }
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
                    $params['body']['query']['bool']['filter'] = [
                        'prefix' => ['category_path'=>$category->path.$category->id.'-']
                    ];
                } else {
                    // 如果这不是一个父类目，则直接筛选此类目下的商品
                    $params['body']['query']['bool']['filter'][] = ['term' => ['category_id' => $category_id]];                    
                }
            }
        } else {
            $categories = Category::where('level', 0)->get();
        }

        //search搜索
        if ($search = $request->input('search', '')) {
            $keywords = array_filter(explode(" ", $search));

            $params['body']['query']['bool']['must'] = [];
            foreach($keywords as $keyword) {
                $params['body']['query']['bool']['must'][] = [
                    'multi_match' => [
                        'query' => $keyword,
                        'fields' => [
                            'title^3',
                            'long_title^2',
                            'category^2',
                            'description',
                            'skus_title',
                            'skus_description',
                            'properties_value'
                        ]
                    ]
                ];
            }
        }

        if($search || isset($category)) {
            $params['body']['aggs'] = [
                'properties' => [
                    'nested' => [
                        'path' => 'properties'
                    ],
                    'aggs' => [
                        'properties' => [
                            'terms' => [
                                'field' => 'properties.name'
                            ],
                            'aggs' => [
                                'value' => [
                                    'terms' => [
                                        'field' => 'properties.value'
                                    ],
                                ],
                            ],
                        ],
                    ],
                ],
            ];
        }

        //从用户请求参数获取filters
        $propertyFilters = [];
        if($filterString = $request->input('filters')) {
            $filterArray = explode('|', $filterString);

            foreach($filterArray as $filter) {
                list($name, $value) = explode(":", $filter);

                $propertyFilters[$name] = $value;
                //添加到filter类型中
                $params['body']['query']['bool']['filter'][] = [
                    'nested' => [
                        //指明nested字段
                        'path' => 'properties',
                        'query' => [
                            ['term' => ['properties.search_value' => $filter]],
                        ]
                    ]
                ];
            }
        }

        $result = app('es')->search($params);

        //通过collect函数将返回结果转为集合，通过pluck返回id数组
        $productIds = collect($result['hits']['hits'])->pluck('_id')->all();

        $products = Product::query()
                        ->whereIn('id', $productIds)
                        ->orderByRaw(sprintf("FIND_IN_SET(id, '%s')", join(",", $productIds)))
                        ->get();
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
