<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;
use App\Models\CouponCode;
use Carbon;

class CouponCodeRequest extends FormRequest
{
    /**
     * Determine if the user is authorized to make this request.
     *
     * @return bool
     */
    public function authorize()
    {
        return true;
    }

    /**
     * Get the validation rules that apply to the request.
     *
     * @return array
     */
    public function rules()
    {
        return [
            'code' => [
                'required',
                function($attribute, $value, $fail) {
                    if (!$coupon_code = CouponCode::where('no', $value)->first()) {
                        return $fail('无效的优惠券');
                    }

                    if ($coupon_code->total - $coupon_code->used <=0) {
                        return $fail("优惠券已使用完毕");
                    }

                    if ($coupon_code->start_time && $coupon_code->start_time->gt(Carbon::now())) {
                        return $fail('优惠未开始');
                    }

                    if ($coupon_code->end_time && $coupon_code->end_time->lt(Carbon::now())) {
                        return $ffail('优惠已结束');
                    }
                }
            ]
        ];
    }
}
