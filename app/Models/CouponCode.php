<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use App\Exceptions\CouponCodeUnavailableException;

class CouponCode extends Model
{
    const TYPE_FIXED = 'reduction';
    const TYPE_PERCENT = 'discount';

    protected $fillable = ['no', 'type', 'total', 'amount', 'offer', 'start_time', 'end_time'];

    protected $appends = ['description'];
    public function getDescriptionAttribute()
    {
        $str = '';
        if ($this->type === 'reduction') {
            $str = '减'.str_replace('.00', '', $this->offer);
        } else if ($this->type === 'discount') {
            $str = '优惠'.str_replace('.00', '', $this->offer)."%";
        }

        return '满'.$this->amount.$str;
    }

    public static function findAvalableNo()
    {
        do {
            $no = generateStr(12);
        } while (static::query()->where('no', $no)->exists());
        return $no;
    }

    public function checkAvailable($orderAmount = null)
    {
        // if (!$this->enabled) {
        //     throw new CouponCodeUnavailableException('优惠券不存在');
        // }
        if ($this->total - intval($this->used) <= 0) {
            throw new CouponCodeUnavailableException('该优惠券已被兑完');
        }

        if ($this->start_time && $this->start_time->gt(Carbon::now())) {
            throw new CouponCodeUnavailableException('该优惠券现在还不能使用');
        }

        if ($this->end_time && $this->end_time->lt(Carbon::now())) {
            throw new CouponCodeUnavailableException('该优惠券已过期');
        }

        if (!is_null($orderAmount) && $orderAmount < $this->amount) {
            throw new CouponCodeUnavailableException('订单金额不满足该优惠券最低金额');
        }
    }

    public function getAdjustedPrice($orderAmount)
    {
        // 固定金额
        if ($this->type === self::TYPE_FIXED) {
            // 为了保证系统健壮性，我们需要订单金额最少为 0.01 元
            return max(0.01, $orderAmount - $this->value);
        }

        return number_format($orderAmount * (100 - $this->value) / 100, 2, '.', '');
    }

    public function changeUsed($increase = true)
    {
        // 传入 true 代表新增用量，否则是减少用量
        if ($increase) {
            // 与检查 SKU 库存类似，这里需要检查当前用量是否已经超过总量
            return $this->newQuery()->where('id', $this->id)->where('used', '<', $this->total)->increment('used');
        } else {
            return $this->decrement('used');
        }
    }
}
