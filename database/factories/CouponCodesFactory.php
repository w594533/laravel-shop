<?php

use Faker\Generator as Faker;
use App\Models\CouponCode;

$factory->define(App\Models\CouponCode::class, function (Faker $faker) {
    $no = CouponCode::findAvalableNo();
    $type = $faker->randomElement(['reduction', 'discount']);
    //满减
    $amount = $faker->numberBetween(100, 4000);
    if ($type === 'reduction') {
        $offer = $faker->numberBetween(1, $amount);
    } else {
        //折扣
        $offer = $faker->numberBetween(1, 50);
    }
    $result = [
        'no' => $no,
        'type' => $type,
        'amount' => $amount,
        'offer' => $offer,
        'total' => $faker->randomNumber()
    ];
    return $result;
});
