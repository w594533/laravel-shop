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
