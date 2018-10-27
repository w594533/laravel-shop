@extends('layouts.app')
@section('title', '我的收藏')

@section('content')
<div class="row">
  <div class="">
    <div class="panel panel-default">
      <div class="panel-heading">我的收藏</div>
      <div class="panel-body">
        <div class="product-list clearfix">
          @include('products._list', ['products' => $products])
        </div>
        <div class="pull-right">
          {{ $products->render() }}
        </div>
      </div>

    </div>
  </div>
</div>
@endsection