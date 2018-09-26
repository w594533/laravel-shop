<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Http\Requests\UserAddressRequest;
use App\Models\User;
use App\Models\UserAddress;
use Auth;

class UserAddressController extends Controller
{
    public function index()
    {
        return view('user_addresses.index', ['user_addresses' => Auth::user()->addresses]);
    }

    public function create()
    {
        return view('user_addresses.create_and_edit', ['user_address' => new UserAddress]);
    }

    public function store(UserAddressRequest $request, UserAddress $user_address)
    {
        $user_address->fill($request->all());
        $user_address->user_id = Auth::id();
        $user_address->save();
        return redirect()->route('user_addresses.index')->with('success', '添加成功');
    }

    public function edit(UserAddress $user_address)
    {
        $this->authorize('own', $user_address);
        return view('user_addresses.create_and_edit', ['user_address' => $user_address]);
    }

    public function update(UserAddressRequest $request, UserAddress $user_address)
    {
        $this->authorize('own', $user_address);
        $user_address->update($request->all());
        return redirect()->route('user_addresses.index')->with('success', '修改成功');
    }

    public function destroy(UserAddress $user_address)
    {
        $this->authorize('own', $user_address);
        $user_address->delete();
        return [];
    }
}
