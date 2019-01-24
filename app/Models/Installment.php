<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Installment extends Model
{
     const STATUS_PENDING = 'pending';
     const STATUS_REPAYING = 'repaying';
     const STATUS_FINISHED = 'finished';

     public static $statusMap = [
         self::STATUS_PENDING => '未执行',
         self::STATUS_REPAYING => '还款中',
         self::STATUS_FINISHED => '已完成'
     ];

     protected $fillable = ['no', 'total_amount', 'count', 'fee_rate', 'fine_rate', 'status'];

     public static function boot()
     {
         parent::boot();

         static::creating(function($model) {
            if (!$model->no) {
                $model->no = static::findAvailableNo();

                if(!$model->no) {
                    return false;
                }
            }
         });

     }
     public static function findAvailableNo()
    {
        do {
            $no = generateNo();
        } while (static::query()->where('no', $no)->exists());
        return $no;
    }

    public function user()
    {
        return $this->belongsTo(User::class);
    }

    public function order()
    {
        return $this->belongsTo(Order::class);
    }

    public function items()
    {
        return $this->hasMany(InstallmentItem::class);
    }

    public function refreshRefundStatus()
    {
        $allSuccess = true;
        // 重新加载 items，保证与数据库中数据同步
        $this->load(['items']);
        \Log::debug('result', 333);
        foreach ($this->items as $item) {
            \Log::debug('result', $item->paid_at);
            \Log::debug('install', $item->refund_status);
            \Log::debug('status', $item->paid_at && $item->refund_status !== InstallmentItem::REFUND_STATUS_SUCCESS);
            if ($item->paid_at && $item->refund_status !== InstallmentItem::REFUND_STATUS_SUCCESS) {
                $allSuccess = false;
                break;
            }
        }
        if ($allSuccess) {
            $this->order->update([
                'refund_status' => Order::REFUND_STATUS_SUCCESS,
            ]);
        }
    }

}
