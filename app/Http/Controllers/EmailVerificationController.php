<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Notifications\EmailVerificationNotification;
use App\Exceptions\InvalidRequestException;
use App\Models\User;
use Auth;
use Carbon\Carbon;

class EmailVerificationController extends Controller
{
    public function send()
    {
      $user = Auth::user();
      if ($user->is_email_verified) {
        throw new InvalidRequestException('你已经验证过邮箱了');
      }

      $user->notify(new EmailVerificationNotification());
      return view('pages.success', ['message' => '邮件发送成功']);
    }

    public function verify($token)
    {
      $info = decrypt($token);

      //判断是否已经验证过
      if(Auth::user()->is_email_verified) {
        throw new InvalidRequestException('你已经验证过邮箱了');
      }
      //判断是否过期
      if (Carbon::now()->gt($info['expired_at'])) {
        throw new InvalidRequestException('验证链接已经过期');
      }

      if (!$user = User::where('email', $info['email'])->first()) {
        throw new InvalidRequestException('用户不存在');
      }

      $user->update(['is_email_verified' => true]);

      return view('pages.success', ['message' => '验证成功']);
    }
}
