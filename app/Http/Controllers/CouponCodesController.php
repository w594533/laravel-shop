<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\CouponCode;
use App\Http\Requests\CouponCodeRequest;
use App\Exceptions\CouponCodeUnavailableException;

class CouponCodesController extends Controller
{
    public function show(Request $request)
    {
        if (!$record = CouponCode::where('no', $request->code)->first()) {
            throw new CouponCodeUnavailableException('优惠券不存在111');
        }

        $record->checkAvailable();

        return $record;
    }
}
