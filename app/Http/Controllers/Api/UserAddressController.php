<?php

namespace App\Http\Controllers\Api;

use Illuminate\Http\Request;
use App\Http\Controllers\Api\Controller;
use App\Http\Requests\UserAddressRequest;
use App\Models\UserAddress;

class UserAddressController extends Controller
{
    public function store(UserAddressRequest $request)
    {
        $user= $request->user()->addresses()->create($request->all());
        return $this->response->created();
    }


    public function show(UserAddress $user_address)
    {
        dd($user_address);
    }

    public function update(UserAddressRequest $request, UserAddress $user_address)
    {
        //dd($request->url());
        dd($user_address);
        $this->authorize('own', $user_address);
        $user_address->update($request->all());
        // return redirect()->route('user_addresses.index')->with('success', '修改成功');
    }
}
