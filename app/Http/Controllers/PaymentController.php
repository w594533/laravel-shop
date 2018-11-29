<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Exceptions\InvalidRequestException;
use App\Models\Order;
use Carbon\Carbon;
use App\Events\OrderPaid;

class PaymentController extends Controller
{
    public function payByAlipay(Order $order, Request $request)
    {
        // 判断订单是否属于当前用户
        $this->authorize('own', $order);
        if (!$order->canPay()) {
            //已经支付
            return redirect()->route('orders.show', ['order' => $order])->with('订单状态不正确');
        }

        return app('alipay')->web([
            'out_trade_no' => $order->no,
            'total_amount' => '1',//$order->total_amount
            'subject' => '支付宝支付 - '.$order->no,
        ]);
    }

    public function payByWechat(Order $order, Request $request)
    {
        // 判断订单是否属于当前用户
        $this->authorize('own', $order);
        if (!$order->canPay()) {
            //已经支付
            return redirect()->route('orders.show', ['order' => $order])->with('订单状态不正确');
        }
        $wechatOrder = app('wechat_pay')->scan([
          'out_trade_no' => $order->no,
          'total_fee' => $order->total_amount * 100,//单位 分
          'body' => '微信支付 - '.$order->no,
          'openid' => 'onkVf1FjWS5SBIixxxxxxx'
        ]);
        // 把要转换的字符串作为 QrCode 的构造函数参数
        $qrCode = new QrCode($wechatOrder->code_url);

        // 将生成的二维码图片数据以字符串形式输出，并带上相应的响应类型
        return response($qrCode->writeString(), 200, ['Content-Type' => $qrCode->getContentType()]);
    }

    //前端回调
    public function alipayReturn()
    {
        try {
            $data = app('alipay')->verify();
            \Log::debug('alipay return', $data->all());
        } catch (\Exception $e) {
            return view('pages.error', ['msg' => '数据不正确']);
        }

        $order = Order::where('no', $data->out_trade_no)->first();
        return redirect()->route('orders.show', ['order' => $order->id])->with('success', '支付成功');
    }


    //服务端回调
    public function alipayNotify()
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
            $this->afterPaid($order);
            return $alipay->success();
        } catch (Exception $e) {
            return 'fail';
        }
    }

    public function wechatPayNotify()
    {
        $wechat_pay = app('wechat_pay');
        try {
            $data = $wechat_pay->verify(); // 是的，验签就这么简单！
            // 正常来说不太可能出现支付了一笔不存在的订单，这个判断只是加强系统健壮性。
            $order = Order::where('no', $data->out_trade_no)->first();
            if (!$order) {
                return 'fail';
            }
            // 如果这笔订单的状态已经是已支付
            if ($order->paid_at) {
                return $wechat_pay->success();
            }
            //更新支付状态
            $order->update([
              'paid_at'        => Carbon::now(), // 支付时间
              'payment_method' => 'wechat', // 支付方式
              'payment_no'     => $wechat_pay->transaction_id, // 支付宝订单号
            ]);
            $this->afterPaid($order);
            return $wechat_pay->success();
        } catch (Exception $e) {
            // $e->getMessage();
            return 'fail';
        }

        return $wechat_pay->success();// laravel 框架中请直接 `return $pay->success()`
    }

    private function afterPaid(Order $order)
    {
        event(new OrderPaid($order));
    }
}
