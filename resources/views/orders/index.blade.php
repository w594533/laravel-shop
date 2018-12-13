@extends('layouts.app')
@section('title', '我的订单')

@section('content')
<div class="row">
  <div class="panel-body">
    <ul class="list-group">
      @foreach ($orders as $order)
      <li class="list-group-item">
        <div class="panel panel-default">
          <div class="panel-heading">
            订单编号: {{ $order->no }}
            <div class="pull-right">{{ $order->created_at->format('Y-m-d H:i:s') }}</div>
          </div>
          <div class="panel-body">
            <table class="table">
              <thead>
                <tr>
                  <td width="30%">商品信息</td>
                  <td width="10%" class=" text-center">单价</td>
                  <td width="10%" class=" text-center">数量</td>
                  <td width="20%" class=" text-center">订单总价</td>
                  <td width="15%" class=" text-center">状态</td>
                  <td width="15%" class=" text-center">操作</td>
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
                  @if($index === 0)
                  <td rowspan="{{ count($order->items) }}" class=" text-center">{{ $order->total_amount }}</td>
                  <td rowspan="{{ count($order->items) }}" class=" text-center">{!! $order->showStatus() !!}</td>
                  <td rowspan="{{ count($order->items) }}" class=" text-center">
                    <a class="btn btn-primary btn-xs" href="{{ route('orders.show', ['id' => $order]) }}">查看订单</a>
                    @if ($order->paid_at)
                    <a class="btn btn-primary btn-xs" href="{{ route('orders.review.show', ['id' => $order]) }}">{{ $order->reviewed ? '查看评价':'评价' }}</a>
                    @endif
                  </td>
                  @endif
                </tr>
                @endforeach
              </tbody>
            </table>
          </div>
        </div>
      </li>
      @endforeach
    </ul>
  </div>
</div>
@endsection