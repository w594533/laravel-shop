<?php

namespace App\Http\Controllers\Api;

use Illuminate\Http\Request;
use App\Http\Controllers\Api\Controller;
use Validator;

class VerfyCodeController extends Controller
{
    public function store(Request $request)
    {
        $v = Validator::make($request->all(), [
            'type' => 'required',
        ]);

        $v->sometimes('phone', 'required|exists:users', function($input){
            return $input->type === 'login';
        });

        $v->sometimes('phone', 'required|unique:users', function($input){
            return $input->type === 'register';
        });
        $v->validate();

        $cache_key = 'verfycode_'.str_random(15);
        $code = str_pad(random_int(1,9999), 4, 0, STR_PAD_LEFT);
        $expired = now()->addMinute(10);

        \Cache::put($cache_key, ['phone' => $request->phone, 'code'=>$code], $expired);
        return $this->response->array([
            'key' => $cache_key,
            'code' => $code,//测试阶段返回code，接通短信后不需要返回
            'expired' => $expired->toDateTimeString()
        ])->setStatusCode(201);
    }
}
