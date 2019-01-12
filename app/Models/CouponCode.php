<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class CouponCode extends Model
{
    protected $fillable = ['no', 'type', 'total', 'amount', 'offer', 'start_time', 'end_time'];

    public static function findAvalableNo()
    {
        do {
            $no = generateStr(12);
        } while (static::query()->where('no', $no)->exists());
        return $no;
    }
}
