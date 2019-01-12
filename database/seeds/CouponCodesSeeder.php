<?php

use Illuminate\Database\Seeder;

class CouponCodesSeeder extends Seeder
{
    /**
     * Run the database seeds.
     *
     * @return void
     */
    public function run()
    {
        //创建 30 个商品
        $coupon_codes = factory(\App\Models\CouponCode::class, 100)->create();
    }
}
