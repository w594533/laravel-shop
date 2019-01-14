<?php

use Faker\Generator as Faker;

$factory->define(App\Models\UserAddress::class, function (Faker $faker) {

    return [
        'province' => $faker->state,
        'city' => $faker->city,
        'district' => $faker->streetName,
        'address' => $faker->address,
        'zip' => $faker->postcode,
        'contact_phone' => $faker->phoneNumber,
        'contact_name' => $faker->name,
    ];
});
