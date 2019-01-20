<?php
function route_class()
{
    return str_replace('.', '-', Route::currentRouteName());
}

/** 生成唯一编号 */
function generateNo()
{
    return date('YmdHis') . str_pad(mt_rand(1, 999999), 6, '0', STR_PAD_LEFT);
}

function generateStr($len=6)
{
    $uppers = range('A', 'Z');
    $lowers = range('a', 'z');
    $numbers = range(1, 9);
    $arr = array_merge($uppers, $lowers, $numbers);
    shuffle($arr);
    return substr(implode("", $arr),0,  $len);
}

function ngrok_url($routeName, $parameters = [])
{
    // 开发环境，并且配置了 NGROK_URL
    if(app()->environment('local') && $url = config('app.ngrok_url')) {
        // route() 函数第三个参数代表是否绝对路径
        return $url.route($routeName, $parameters, false);
    }

    return route($routeName, $parameters);
}
