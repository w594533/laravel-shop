@extends('layouts.app')
@section('title', '商品列表')

@section('content')
<div class="row">
  <div class="">
    <div class="panel panel-default">
      <div class="panel-body">
        <!-- 筛选组件开始 -->
        <div class="row">
          <form action="{{ route('products.index') }}" class="form-inline search-form">
            <div class="filter">
              <div class="category-filter-bread">
                <a href="{{ route('products.index') }}">全部</a> &gt;
                @if ($category)
                    @foreach($category->ancestors as $ancestor)
                      <a href="{{ route('products.index', ['category_id' => $ancestor->id]) }}">{{$ancestor->name}}</a> &gt;
                    @endforeach
              <span>{{$category->name}}</span>
                @endif
              </div>
              @if ($categories)
              <div class="category-filter-child">
                  <div class="filter-item clearfix">
                    <div class="pull-left">
                      <span>分类：<span>
                    </div>
                    <div class="pull-right item">
                      <ul class="clearfix">
                         @foreach ($categories as $ancestor)
                      <li><a href="{{ route('products.index', ['category_id' => $ancestor->id]) }}">{{ $ancestor->name }}</a></li>
                         @endforeach
                      <input type="hidden" name="category_id" value={{$filters['category_id']}}/> 
                      </ul>
                    </div>
                  </div>
                </div>
              @endif
              
            </div>

            <input type="text" class="form-control input-sm" name="search" placeholder="搜索" value="{{ $filters['search'] }}">
            <button class="btn btn-primary btn-sm">搜索</button>
            <select name="order" class="form-control input-sm pull-right form-filter-order">
              <option value="">排序方式</option>
              <option value="price_asc" {{$filters['order'] === 'price_asc' ? 'selected': ''}}>价格从低到高</option>
              <option value="price_desc" {{$filters['order'] === 'price_desc' ? 'selected': ''}}>价格从高到低</option>
              <option value="sold_count_desc" {{$filters['order'] === 'sold_count_desc' ? 'selected': ''}}>销量从高到低</option>
              <option value="sold_count_asc" {{$filters['order'] === 'sold_count_asc' ? 'selected': ''}}>销量从低到高</option>
              <option value="rating_desc" {{$filters['order'] === 'rating_desc' ? 'selected': ''}}>评价从高到低</option>
              <option value="rating_asc" {{$filters['order'] === 'rating_asc' ? 'selected': ''}}>评价从低到高</option>
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