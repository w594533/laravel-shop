<?php
use App\Models\CouponCode;
use App\Models\Order;
use App\Models\User;
use Faker\Generator as Faker;

$factory->define(App\Models\Order::class, function (Faker $faker) {
    $user = User::query()->inRandomOrder()->first();
    $address = $user->addresses()->inRandomOrder()->first();
    $refund = random_int(1,10) < 1;//10%的概率已退款
    $ship = $faker->randomElement(array_keys(Order::$shipStatusMap));

    // 优惠券
    $coupon = null;
    // 30% 概率该订单使用了优惠券
    if (random_int(0, 10) < 3) {
        // 为了避免出现逻辑错误，我们只选择没有最低金额限制的优惠券
        $coupon = CouponCode::query()->where('amount', '<', 1155)->inRandomOrder()->first();
        // 增加优惠券的使用量
        $coupon->changeUsed();
    }
    return [
        'address'        => [
            'address'       => $address->full_address,
            'zip'           => $address->zip,
            'contact_name'  => $address->contact_name,
            'contact_phone' => $address->contact_phone,
        ],
        'total_amount' => 0,
        'no' => Order::findAvailableNo(),
        'user_id' => $user->id,
        'remark' => $faker->sentence(),
        'paid_at' => $faker->dateTimeBetween('-30 days'), // 30天前到现在任意时间点
        'payment_method' => $faker->randomElement(['alipay', 'wechat']),
        'payment_no' => $faker->uuid,
        'refund_status' => $refund ? Order::REFUND_STATUS_SUCCESS : Order::REFUND_STATUS_PENDING,
        'refund_no' => $refund ? Order::findAvailableRefundNo():null,
        'closed' => false,
        'reviewed' => random_int(0, 10) > 2,
        'ship_status'    => $ship,
        'ship_data'      => $ship === Order::SHIP_STATUS_PENDING ? null : [
            'express_company' => $faker->company,
            'express_no'      => $faker->uuid,
        ],
        'extra'          => $refund ? ['refund_reason' => $faker->sentence] : [],
        'coupon_code_id' => $coupon ? $coupon->id : null,
    ];
});
