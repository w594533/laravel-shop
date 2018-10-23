@extends('layouts.app')
@section('title', '商品列表')

@section('content')
<div class="row">
  <div class="">
    <div class="panel panel-default">
      <div class="panel-body">
        <div class="product-list clearfix">
          @foreach($products as $product)
          <div class="col-xs-3 product-item">
            <div class="product-content">
              <div class="top">
                <div class="image">
                  <image src="{{ $product->image_url }}" />
                </div>
                <div class="price">{{ $product->price }}</div>
                <div class="title">{{ $product->title }}</div>
              </div>
              <div class="bottom">
                <div class="sold_count">销量 <span>{{ $product->sold_count }}笔</span></div>
                <div class="review_count">评价 <span>{{ $product->review_count }}</span></div>
              </div>
            </div>
          </div>
          @endforeach
        </div>
        <div class="pull-right">
          {{ $products->render() }}
        </div>
      </div>

    </div>
  </div>
</div>
@endsection