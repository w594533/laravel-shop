<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Exceptions\InvalidRequestException;
use App\Models\Order;
use Carbon\Carbon;

class PaymentController extends Controller
{
    public function alipay(Order $order, Request $request)
    {
        // 判断订单是否属于当前用户
        $this->authorize('own', $order);
        if ($order->paid_at || $order->closed) {
            //已经支付
            return redirect()->route('orders.show', ['order' => $order])->with('订单状态不正确');
        }

        return app('alipay')->web([
            'out_trade_no' => $order->no,
            'total_amount' => '1',//$order->total_amount
            'subject' => '支付宝支付 - '.$order->no,
        ]);
    }

    //前端回调
    public function return()
    {
        try {
            $data = app('alipay')->verify();
        } catch (\Exception $e) {
            return view('pages.error', ['msg' => '数据不正确']);
        }

        $order = Order::where('no', $data->out_trade_no)->first();
        return redirect()->route('orders.show', ['order' => $order])->with('success', '支付成功');
    }


    //服务端回调
    public function notify()
    {
        $alipay = app('alipay');
        try {
            $data = $alipay->verify(); // 是的，验签就这么简单！

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
            $order = Order::where('no', $data->out_trade_no)->first();
            if (!$order) {
                return 'fail';
            }
            // 如果这笔订单的状态已经是已支付
            if ($order->paid_at) {
                return $alipay->success();
            }
            //更新支付状态
            $order->update([
              'paid_at'        => Carbon::now(), // 支付时间
              'payment_method' => 'alipay', // 支付方式
              'payment_no'     => $data->trade_no, // 支付宝订单号
            ]);
            return $alipay->success();
        } catch (Exception $e) {
            return 'fail';
        }
    }
}
