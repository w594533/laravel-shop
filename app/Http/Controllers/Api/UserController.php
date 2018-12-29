<?php

namespace App\Http\Controllers\Api;

use Illuminate\Http\Request;
use App\Http\Controllers\Api\Controller;
use App\Http\Requests\Api\RegisterRequest;
use App\Http\Requests\Api\LoginRequest;
use App\Models\User;
use Cache;

class UserController extends Controller
{
    public function store(RegisterRequest $request)
    {
        //获取验证码的key
        $this->checkKey($request->key, $request->code, $request->phone);

        $user = User::create([
            'name' => $request->name,
            'phone' => $request->phone,
            'password' => bcrypt($request->password)
        ]);

        Cache::forget($request->key);

        return $this->response->created();
    }

    public function login(LoginRequest $request)
    {
        //获取验证码的key
        $this->checkKey($request->key, $request->code, $request->phone);
        $user = User::findByPhone($request->phone);
        $token = $user->createToken('Api')->accessToken;
        Cache::forget($request->key);

        return $this->response->array(
            [
                'token' => $token
            ]
        );

    }

    public function logout()
    {
        \Auth::user()->token()->revoke();
        return $this->response->noContent();
    }


    private function checkKey($key, $code, $phone)
    {
        //获取验证码的key
        if (!Cache::has($key)) {
            return $this->response->error('无效的验证码', 422);
        }

        $data = Cache::get($key);
        if (!$key) {
            return $this->response->error('验证码已失效', 422);
        }

        //判断验证码是否正确
        if (!hash_equals($data['code'], $code)) {
            return $this->response->errorUnauthorized('验证码错误');
        }

        //判断手机号
        if($data['phone'] !== $phone) {
            return $this->response->error('数据异常', 422);
        }
    }
}
