<?php

namespace App\Transformers;

use App\Models\ProductSku;
use League\Fractal\TransformerAbstract;

class ProductSkuTransformer extends TransformerAbstract
{
    public function transform(ProductSku $product_sku)
    {
        $ori = $product_sku->toArray();
        $append = ["test" => '123'];
        return array_merge($ori, $append);
    }
}