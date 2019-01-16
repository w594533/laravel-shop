@extends('layouts.app')
@section('title', '商品列表')

@section('content')
<div class="row">
  <div class="">
    <div class="panel panel-default">
      <div class="panel-body">
        <!-- 筛选组件开始 -->
        <div class="row">
          <div class="filter">
            <div class="category-filter-bread">
              <a href="#">全部</a>&gt;
              <a href="#">耳机</a>&gt;
              <span>蓝牙耳机</span>
            </div>
            <div class="category-filter-child">
              <div class="filter-item clearfix">
                <div class="pull-left">
                  <span>分类：<span>
                </div>
                <div class="pull-right item">
                  <ul class="clearfix">
                      <li><a href="#">耳机</a></li>
                      <li><a href="#">蓝牙耳机</a></li>
                      <li><a href="#">华为耳机</a></li>
                  </ul>
                </div>
              </div>
              
            </div>
          </div>
          <form action="{{ route('products.index') }}" class="form-inline search-form">
            <input type="text" class="form-control input-sm" name="search" placeholder="搜索" value="{{ $filters['search'] }}">
            <button class="btn btn-primary btn-sm">搜索</button>
            <select name="order" class="form-control input-sm pull-right form-filter-order">
              <option value="">排序方式</option>
              <option value="price_asc">价格从低到高</option>
              <option value="price_desc">价格从高到低</option>
              <option value="sold_count_desc">销量从高到低</option>
              <option value="sold_count_asc">销量从低到高</option>
              <option value="rating_desc">评价从高到低</option>
              <option value="rating_asc">评价从低到高</option>
            </select>
          </form>
        </div>
        <div class="product-list clearfix">
          @include('products._list', ['products' => $products])
        </div>
        <div class="pull-right">
          {{ $products->appends($filters)->render() }}
        </div>
      </div>

    </div>
  </div>
</div>
@endsection

@section('scripts')
<script>
  $(document).ready(function() {
    $('.search-form input[name=search]').val(filters.search);
    $('.search-form select[name=order]').val("{{ $filters['order'] }}");
  })

  $(".form-filter-order").change(function() {
    $(".search-form").submit();
  });
</script>
@endsection