<?php

namespace App\Transformers;

use App\Models\Product;
use App\Models\ProductSku;
use League\Fractal\TransformerAbstract;

class ProductTransformer extends TransformerAbstract
{
    protected $availableIncludes = [
        'skus'
    ];
    public function transform(Product $product)
    {
        $ori = $product->toArray();
        $append = [];
        return array_merge($ori, $append);
    }

    public function includeSkus(Product $product)
    {
        return $this->collection($product->skus, new ProductSkuTransformer);
    }
}