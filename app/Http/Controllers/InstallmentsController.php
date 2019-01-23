<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Exceptions\InvalidRequestException;
use App\Models\Installment;
use App\Models\InstallmentItem;
use Carbon\Carbon;
use App\Events\OrderPaid;

class InstallmentsController extends Controller
{

    public function index()
    {
        $installments = Installment::query()
                            ->where('user_id', \Auth::Id())
                            ->paginate(10);
        return view('installments.index', [
            'installments' => $installments
        ]);                            
    }

    public function show(Installment $installment)
    {
        $this->authorize('own', $installment);
        
        $items = $installment->items()->orderBy('sequence')->get();

        //取出下一次还款
        $nextItem = $items->where('paid_at', null)->first();

        return view('installments.show', [
            'installment' => $installment,
            'items' => $items,
            'nextItem' => $nextItem
        ]);
    }

    public function payByAlipay(Installment $installment)
    {
        $this->authorize('own', $installment);

        $order = $installment->order;
        if ($order->closed) {
            throw new InvalidRequestException('对应的订单已关闭');
        }

        if ($installment->status === Installment::STATUS_FINISHED) {
            throw new InvalidRequestException('分期付款已完成');
        }

        //获取当前分期
        $item = InstallmentItem::query()
                    ->whereNull('paid_at')
                    ->where('installment_id', $installment->id)
                    ->orderBy('sequence')
                    ->first();

        if (!$item) {
            throw new InvalidRequestException('没有需要支付的分期');
        }

        //调用支付宝支付
        return app('alipay')->web([
            'out_trade_no' => $installment->no.'_'.$item->sequence,
            'total_amount' => '1',//$item->total
            'subject' => '支付 Laravel Shop 的分期订单：'.$installment->no,
            'notify_url' => ngrok_url('installments.alipay.notify'),
            'return_url' => route('installments.alipay.return')
        ]);
    }
    // 支付宝前端回调
    public function alipayReturn()
    {
        try {
            $data = app('alipay')->verify();
            \Log::debug('alipay return', $data->all());
        } catch (\Exception $e) {
            return view('pages.error', ['msg' => '数据不正确']);
        }

        list($no, $sequence) = explode('_', $data->out_trade_no);
        $installment = Installment::where('no', $no)->first();
        return redirect()->route('installments.show', ['installment' => $installment->id])->with('success', '支付成功');
    }

    //支付宝回调
    public function alipayNotify()
    {
        $alipay = app('alipay');
        try {
            $data = $alipay->verify(); // 是的，验签就这么简单！
            \Log::debug('alipay notify', $data->all());

            // 请自行对 trade_status 进行判断及其它逻辑进行判断，在支付宝的业务通知中，只有交易通知状态为 TRADE_SUCCESS 或 TRADE_FINISHED 时，支付宝才会认定为买家付款成功。
            // 1、商户需要验证该通知数据中的out_trade_no是否为商户系统中创建的订单号；
            // 2、判断total_amount是否确实为该订单的实际金额（即商户订单创建时的金额）；
            // 3、校验通知中的seller_id（或者seller_email) 是否为out_trade_no这笔单据的对应的操作方（有的时候，一个商户可能有多个seller_id/seller_email）；
            // 4、验证app_id是否为该商户本身。
            // 5、其它业务逻辑情况
            if (!in_array($data->trade_status, ['TRADE_SUCCESS', 'TRADE_FINISHED'])) {
                return 'fail';
            }

            // 正常来说不太可能出现支付了一笔不存在的订单，这个判断只是加强系统健壮性。
            // 拉起支付时使用的支付订单号是由分期流水号 + 还款计划编号组成的
            // 因此可以通过支付订单号来还原出这笔还款是哪个分期付款的哪个还款计划
            list($no, $sequence) = explode('_', $data->out_trade_no);

            if (!$installment = Installment::where('no', $no)->first()) {
                return 'fail';
            }

            // 如果这笔订单的状态已经是已支付
            if (!$item = $installment->items()->where('sequence', $sequence)->first()) {
                return 'fail';
            }
            if ($item->paid_at) {
                return $alipay->success();
            }

            \DB::transaction(function() use ($data, $installment, $item) {
                $item->update([
                    'paid_at' => Carbon::now(), // 支付时间
                    'payment_method' => 'alipay', // 支付方式
                    'payment_no' => $data->trade_no, // 支付宝订单号
                ]);
                
                //如果是第一次分期付款
                if ($item->sequence === 1) {
                    $installment->update(['status' => Installment::STATUS_REPAYING]);//还款中

                    $installment->order->update([
                        'paid_at' => Carbon::now(), // 支付时间
                        'payment_method' => 'installment', // 支付方式
                        'payment_no' => $installment->no, // 支付宝订单号
                    ]);

                    event(new OrderPaid($installment->order));
                }

                if($item->sequence === $installment->count) {
                    $installment->update(['status' => Installment::STATUS_FINISHED]);
                }
            });
            return $alipay->success();
        } catch (Exception $e) {
            \Log::debug('alipay notify $e', $e);
            return 'fail';
        }
    }
}
