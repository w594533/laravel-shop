<?php

namespace App\Http\Middleware;

use Closure;

class EmailVerify
{
    /**
     * Handle an incoming request.
     *
     * @param  \Illuminate\Http\Request  $request
     * @param  \Closure  $next
     * @return mixed
     */
    public function handle($request, Closure $next)
    {
        if(!\Auth::user()->is_email_verified) {
          //如果是json请求，按json格式返回
          if ($request->expectsJson()) {
            return response()->json(['message' => '请先验证邮箱'], 400);
          }
          return redirect(route('email_verify_notice'));
        }
        return $next($request);
    }
}
