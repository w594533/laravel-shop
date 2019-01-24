<?php

namespace App\Jobs;

use Illuminate\Bus\Queueable;
use Illuminate\Queue\SerializesModels;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;
use App\Models\Installment;
use App\Models\InstallmentItem;
use App\Models\Order;
use App\Exceptions\InternalException;

class RefundInstallmentOrder implements ShouldQueue
{
    use Dispatchable, InteractsWithQueue, Queueable, SerializesModels;

    protected $order;
    /**
     * Create a new job instance.
     *
     * @return void
     */
    public function __construct(Order $order)
    {
        $this->order = $order;
    }

    /**
     * Execute the job.
     *
     * @return void
     */
    public function handle()
    {
        $order = $this->order;
        if (!$order->paid_at 
        || $order->closed 
        || $order->refund_status !== Order::REFUND_STATUS_PROCESSING
        || $order->payment_method !== 'installment') {
            return;
        }

        $installment = Installment::query()
                            ->where('order_id', $order->id)
                            ->first();

        if (!$installment) {
            return;
        }
        
        if(!in_array($installment->status, [
            Installment::STATUS_REPAYING,
            Installment::STATUS_FINISHED
        ])) {
            return;
        }

        foreach($installment->items as $item) {
            // 如果还款计划未支付，或者退款状态为退款成功或退款中，则跳过
            if (!$item->paid_at || in_array($item->refund_status, [
                InstallmentItem::REFUND_STATUS_SUCCESS,
                InstallmentItem::REFUND_STATUS_PROCESSING,
            ])) {
                continue;
            }

            try{
                //具体退款逻辑
                $this->refundInstallmentItem($item);
            } catch(\Exception $e) {
                \Log::warning('分期退款失败：'.$e->getMessage(), [
                    'installment_item_id' => $item->id,
                ]);
                // 假如某个还款计划退款报错了，则暂时跳过，继续处理下一个还款计划的退款
                continue;
            }

            $installment->refreshRefundStatus();
        }
    }

    protected function refundInstallmentItem(InstallmentItem $item) {
        $refund_no = $this->order->refund_no."_".$item->sequence;
        if ($item->payment_method === 'wechat_pay') {
            app('wechat_pay')->refund([
                'transaction_id' => $item->payment_no, // 这里我们使用微信订单号来退款
                'total_fee'      => $item->total * 100, //原订单金额，单位分
                'refund_fee'     => $item->base * 100, // 要退款的订单金额，单位分，分期付款的退款只退本金
                'out_refund_no'  => $refund_no, // 退款订单号
                // 微信支付的退款结果并不是实时返回的，而是通过退款回调来通知，因此这里需要配上退款回调接口地址
                'notify_url'     => ngrok_url('installments.wechat.refund_notify') // todo,
            ]);
            // 将还款计划退款状态改成退款中
            $item->update([
                'refund_status' => InstallmentItem::REFUND_STATUS_PROCESSING,
            ]);
        } else if ($item->payment_method === 'alipay') {
            $result = app('alipay')->refund([
                'trade_no' => $item->payment_no, // 之前的订单流水号
                'refund_amount' => 1, //$order->total_amount, // 退款金额，单位元
                'out_request_no' => $refund_no, // 退款订单号
            ]);
                \Log::debug('result', $result);
            // 根据支付宝的文档，如果返回值里有 sub_code 字段说明退款失败
            \Log::debug('result sub code', $result->sub_code);
            if ($result->sub_code) {
                \Log::debug('result sub code', 1111);
                $item->update([
                    'refund_status' => InstallmentItem::REFUND_STATUS_FAILED,
                ]);
            } else {
                \Log::debug('result sub code', 2222);
                // 将订单的退款状态标记为退款成功并保存退款订单号
                $item->update([
                    'refund_status' => InstallmentItem::REFUND_STATUS_SUCCESS,
                ]);
            }
        } else {
            throw new InternalException('无效的支付方式');
        }
    }


    
}
