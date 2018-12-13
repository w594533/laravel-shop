<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Builder;
use Carbon\Carbon;

class Order extends Model
{
    const REFUND_STATUS_PENDING = 'pending';
    const REFUND_STATUS_APPLIED = 'applied';
    const REFUND_STATUS_PROCESSING = 'processing';
    const REFUND_STATUS_SUCCESS = 'success';
    const REFUND_STATUS_FAILED = 'failed';

    const SHIP_STATUS_PENDING = 'pending';
    const SHIP_STATUS_DELIVERED = 'delivered';
    const SHIP_STATUS_RECEIVED = 'received';

    public static $refundStatusMap = [
        self::REFUND_STATUS_PENDING => '未退款',
        self::REFUND_STATUS_APPLIED => '已申请退款',
        self::REFUND_STATUS_PROCESSING => '退款中',
        self::REFUND_STATUS_SUCCESS => '退款成功',
        self::REFUND_STATUS_FAILED => '退款失败',
    ];
    public static $shipStatusMap = [
        self::SHIP_STATUS_PENDING => '未发货',
        self::SHIP_STATUS_DELIVERED => '已发货',
        self::SHIP_STATUS_RECEIVED => '已收货',
    ];

    protected $fillable = [
        'no', 'address', 'total_amount', 'remark', 'paid_at', 'payment_method', 'payment_no', 'refund_status', 'refund_no',
        'closed',
        'reviewed',
        'ship_status',
        'ship_data',
        'extra',
    ];

    protected $casts = [
        'closed' => 'boolean',
        'reviewed' => 'boolean',
        'address' => 'json',
        'ship_data' => 'json',
        'extra' => 'json'
    ];

    protected $dates = ['paid_at'];

    public function showStatus()
    {
        if ($this->paid_at) {
            if ($this->refund_status === self::REFUND_STATUS_PENDING) {
                return '已支付';
            } else {
                return self::$refundStatusMap[$this->refund_status];
            }
        } elseif ($this->closed) {
            return '已关闭';
        } else {
            $create_at = new Carbon($this->created_at);
            return '未支付<br/>订单将于' . $this->created_at->format('H:i:s') . '自动关闭';
        }
    }

    public function items()
    {
        return $this->hasMany(OrderItem::class);
    }

    public function user()
    {
        return $this->belongsTo(User::class);
    }

    public static function findAvailableNo()
    {
        $prefix = date('YmdHis');
        do {
            $no = generateNo();
        } while (static::query()->where('no', $no)->exists());
        return $no;
    }

    public static function findAvailableRefundNo()
    {
        $prefix = date('YmdHis');
        do {
            $no = generateNo();
        } while (static::query()->where('refund_no', $no)->exists());
        return $no;
    }

    public function canPay()
    {
        return !$this->paid_at && !$this->closed;
    }

    public function canRefund()
    {
        return $this->refund_status === self::REFUND_STATUS_PENDING || $this->refund_status === self::REFUND_STATUS_FAILED;
    }
}
