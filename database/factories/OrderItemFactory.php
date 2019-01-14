<?php
use App\Models\ProductSku;
use App\Models\Order;
use Faker\Generator as Faker;

$factory->define(App\Models\OrderItem::class, function (Faker $faker) {
    $amount = random_int(1,3);
    $productSku = ProductSku::query()->where('stock', '>', $amount)->inRandomOrder()->first();
    $order = Order::query()->inRandomOrder()->first();
    return [
        'product_id' => $productSku->product->id,
        'product_sku_id' => $productSku->id,
        'amount' => $amount,
        'price' => $productSku->price,
        'rating'         => null,
        'review'         => null,
        'reviewed_at'    => null,
    ];
});
