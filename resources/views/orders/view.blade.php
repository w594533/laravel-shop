@extends('layouts.app')
@section('title', '订单详情')

@section('content')
<div class="row">
  <div class="panel-body">
    <div class="panel panel-default">
      <div class="panel-body">
        <table class="table">
          <thead>
            <tr>
              <td width="40%">商品信息</td>
              <td width="20%" class=" text-center">单价</td>
              <td width="20%" class=" text-center">数量</td>
              <td width="20%" class=" text-right">小计</td>
            </tr>
          </thead>
          <tbody>
            @foreach ($order->items as $index => $order_item)
            <tr>
              <td class="product-info">
                <div class="media">
                  <div class="media-left">
                    <a href="{{ route('orders.show', ['order' => $order->id]) }}">
                      <img width="64" class="media-object" src="{{ $order_item->productSku->product->image_url }}" alt="{{ $order_item->productSku->title }}">
                    </a>
                  </div>
                  <div class="media-body">
                    <h4 class="media-heading">{{ $order_item->productSku->product->title }}</h4>
                    {{ $order_item->productSku->title }}
                  </div>
                </div>
              </td>
              <td class="sku-price text-center">{{ $order_item->price }}</td>
              <td class="sku-total-amount text-center">{{ $order_item->amount }}</td>
              <td class=" text-right">{{ $order_item->price * $order_item->amount }}</td>
            </tr>
            @endforeach
          </tbody>
        </table>
        <div class="order-bottom clearfix">
          <div class="order-info pull-left">
            <ul>
              <li class="clearfix">
                <span class="pull-left">收货地址：</span>
                <span class="pull-right">{{ join(' ', $order->address) }}</span>
              </li>
              <li class="clearfix">
                <span class="pull-left">备注：</span>
                <span class="pull-right">{{ $order->remark }}</span>
              </li>
              <li class="clearfix">
                <span class="pull-left">订单编号:</span>
                <span class="pull-right">{{ $order->no }}</span>
              </li>
              @if($order->paid_at)
                <li class="clearfix">
                  <span class="pull-left">物流状态:</span>
                  <span class="pull-right">{{ \App\Models\Order::$shipStatusMap[$order->ship_status] }}</span>
                </li>
                <li class="clearfix">
                  <span class="pull-left">物流信息:</span>
                  <span class="pull-right">{{ $order->ship_data ? implode(" ", $order->ship_data) : '' }}</span>
                </li>
                @endif
                 <!-- 订单已支付，且退款状态不是未退款时展示退款信息 -->
                @if($order->paid_at && $order->refund_status !== \App\Models\Order::REFUND_STATUS_PENDING)
                <li class="line">
                  <div class="pull-left">退款状态：</div>
                  <div class="pull-right">{{ \App\Models\Order::$refundStatusMap[$order->refund_status] }}</div>
                </li>
                <li class="line">
                  <div class="pull-left">退款理由：</div>
                  <div class="pull-right">{{ $order->extra['refund_reason'] }}</div>
                </li>
                @endif
            </ul>
          </div>
          <div class="order-summary pull-right">
            <ul>
              
              <li class="clearfix">
                <span class="pull-left"><strong>订单总价：</strong></span>
                <span class="pull-right"><strong>{{ $order->total_amount }}</strong></span>
              </li>
              @if ($order->couponCode)
              <li class="clearfix">
                <span class="pull-left">优惠：</span>
                <span class="pull-right">{{ $order->couponCode->description }}</span>
              </li>
              @endif
              <li class="clearfix">
                <span class="pull-left">订单状态：</span>
                <span class="pull-right">{!! $order->showStatus() !!}</span>
              </li>

              <!-- 支付按钮开始 -->
              @if(!$order->paid_at && !$order->closed)
                <li class="payment-buttons clearfix">
                  <a class="btn btn-primary btn-sm pull-right" href="{{ route('payment.alipay', ['order' => $order->id]) }}">支付宝支付</a>
                  <button class="btn btn-sm btn-success pull-right" id='btn-wechat'>微信支付</button>
                  <!-- 分期支付按钮开始 -->
                  <!-- 仅当订单总金额大等于分期最低金额时才展示分期按钮 -->
                  @if ($order->total_amount >= config('app.min_installment_amount'))
                    <button class="btn btn-sm btn-danger" id='btn-installment'>分期付款</button>
                  @endif
                  <!-- 分期支付按钮结束 -->
                </li>
                @endif
                <!-- 支付按钮结束 -->
                @if($order->paid_at && $order->ship_status === \App\Models\Order::SHIP_STATUS_DELIVERED)
                  <li class="payment-buttons clearfix">
                    <button class="btn btn-sm btn-success pull-right" id='btn-received'>确认收货</button>
                  </li>
                @endif
                @if(isset($order->extra['refund_disagree_reason']))
                  <li class="clearfix">
                    <span class="pull-left">拒绝退款理由：</span>
                    <div class="value pull-right">{{ $order->extra['refund_disagree_reason'] }}</div>
                  </li>
                @endif
                <!-- 订单已支付，且退款状态是未退款时展示申请退款按钮 -->
                @if($order->type === \App\Models\Product::TYPE_NORMAL && $order->paid_at && $order->canRefund())
                <li class="clearfix refund-button">
                  <button class="btn btn-sm btn-danger pull-right" id="btn-apply-refund">申请退款</button>
                </li>
                @endif
            </ul>
          </div>
        </div>
      </div>
    </div>
  </div>
</div>

<!-- 分期弹框开始 -->
<div class="modal fade" id="installment-modal">
  <div class="modal-dialog">
    <div class="modal-content">
      <div class="modal-header">
        <h5 class="modal-title">选择分期期数</h5>
        <button type="button" class="close" data-dismiss="modal" aria-label="Close">
          <span aria-hidden="true">&times;</span>
        </button>
      </div>
      <div class="modal-body">
        <table class="table table-bordered table-striped text-center">
          <thead>
          <tr>
            <th class="text-center">期数</th>
            <th class="text-center">费率</th>
            <th></th>
          </tr>
          </thead>
          <tbody>
          @foreach(config('app.installment_fee_rate') as $count => $rate)
            <tr>
              <td>{{ $count }}期</td>
              <td>{{ $rate }}%</td>
              <td>
                <button class="btn btn-sm btn-primary btn-select-installment" data-count="{{ $count }}">选择</button>
              </td>
            </tr>
          @endforeach
          </tbody>
        </table>
      </div>
      <div class="modal-footer">
        <button type="button" class="btn btn-default" data-dismiss="modal">取消</button>
      </div>
    </div>
  </div>
</div>
<!-- 分期弹框结束 -->
@endsection
@section('scripts')
<script>
  $(document).ready(function() {
    // 分期付款按钮点击事件
    $('#btn-installment').click(function () {
      // 展示分期弹框
      $('#installment-modal').modal();
    });

    // 选择分期期数按钮点击事件
    $('.btn-select-installment').click(function () {
      // 调用创建分期付款接口
      axios.post('{{ route('payment.installment', ['order' => $order->id]) }}', { count: $(this).data('count') })
        .then(function (response) {
          console.log(response.data);
          // todo 跳转到分期付款页面
          // location.href="{{ route('installments.show', ['installment' => "+response.data.id+"]) }}";
        })
    });
    
    // 微信支付按钮事件
    $('#btn-wechat').click(function() {
      var pay_route = "{{ route('payment.wechat', ['order' => $order->id]) }}";
      swal({
          // content 参数可以是一个 DOM 元素，这里我们用 jQuery 动态生成一个 img 标签，并通过 [0] 的方式获取到 DOM 元素
          content: $("<img src='" + pay_route + "' />")[0],
          // buttons 参数可以设置按钮显示的文案
          buttons: ['关闭', '已完成付款'],
        })
        .then(function(result) {
          // 如果用户点击了 已完成付款 按钮，则重新加载页面
          if (result) {
            location.reload();
          }
        })
    });

    $('#btn-received').click(function() {
      var received_route = "{{ route('orders.received', [$order->id]) }}";
      // 弹出确认框
      swal({
          title: "确认已经收到商品？",
          icon: "warning",
          buttons: true,
          dangerMode: true,
          buttons: ['取消', '确认收到'],
        })
        .then(function(ret) {
          // 如果点击取消按钮则不做任何操作
          if (!ret) {
            return;
          }
          // ajax 提交确认操作
          axios.post(received_route)
            .then(function() {
              // 刷新页面
              location.reload();
            })
        });
    });

    // 退款按钮点击事件
    $('#btn-apply-refund').click(function () {
      swal({
        text: '请输入退款理由',
        content: "input",
      }).then(function (input) {
        // 当用户点击 swal 弹出框上的按钮时触发这个函数
        if(!input) {
          swal('退款理由不可空', '', 'error');
          return;
        }
        // 请求退款接口
        axios.post('{{ route('orders.refund.store', [$order->id]) }}', {reason: input})
          .then(function () {
            swal('申请退款成功', '', 'success').then(function () {
              // 用户点击弹框上按钮时重新加载页面
              location.reload();
            });
          });
      });
    });
  });
</script>
@endsection