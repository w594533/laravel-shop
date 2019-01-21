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


}
