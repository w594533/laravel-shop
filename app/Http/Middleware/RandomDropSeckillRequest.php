<?php

namespace App\Http\Middleware;

use Closure;
use App\Exceptions\InvalidRequestException;

class RandomDropSeckillRequest
{
    /**
     * Handle an incoming request.
     *
     * @param  \Illuminate\Http\Request  $request
     * @param  \Closure  $next
     * @return mixed
     */
    public function handle($request, Closure $next, $percent)
    {
        if(random_int(0, 100)) {
            throw new InvalidRequestException('参与用户过多，请稍后再试');
        }
        
        return $next($request);
    }
}
