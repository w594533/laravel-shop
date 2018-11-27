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
                    <a href="{{ route('orders.show', ['id' => $order]) }}">
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
            </ul>
          </div>
          <div class="order-summary pull-right">
            <ul>
              <li class="clearfix">
                <span class="pull-left"><strong>订单总价：</strong></span>
                <span class="pull-right"><strong>{{ $order->total_amount }}</strong></span>
              </li>
              <li class="clearfix">
                <span class="pull-left">订单状态：</span>
                <span class="pull-right">{!! $order->showStatus() !!}</span>
              </li>
            </ul>
          </div>
        </div>
      </div>
    </div>
  </div>
</div>
@endsection