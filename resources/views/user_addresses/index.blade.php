@extends('layouts.app')
@section('title', '收货地址')

@section('content')
  <div class="panel panel-default">
    <div class="panel-heading clearfix">
      收货地址列表
      <span class="pull-right"><a href="{{ route('user_addresses.create') }}" class="btn btn-primary"><span class="glyphicon glyphicon-plus" aria-hidden="true"></span> 新增</a></span>
    </div>
    <div class="panel-body">
      <table class="table table-bordered">
        <thead>
          <tr>
            <th>收货人</th>
            <th>地址</th>
            <th>电话</th>
            <th>邮编</th>
            <th>操作</th>
          </tr>
        </thead>
        <tbody>
          @foreach ($user_addresses as $user_address)
            <tr>
              <td>{{ $user_address->contact_name }}</td>
              <td width="50%">{{ $user_address->full_address }}</td>
              <td>{{ $user_address->contact_phone }}</td>
              <td>{{ $user_address->zip }}</td>
              <td>
                <a href="{{ route('user_addresses.edit', ['user_address' => $user_address->id]) }}" class="btn btn-primary">修改</a>
                <button class="btn btn-danger btn-del-address" type="button" data-id="{{ $user_address->id }}">删除</button>
              </td>
            </tr>
          @endforeach
        </tbody>
      </table>
    </div>
  </div>
@endsection
@section('scripts')
  <script>
$(document).ready(function() {
  // 删除按钮点击事件
  $('.btn-del-address').click(function() {
    // 获取按钮上 data-id 属性的值，也就是地址 ID
    var id = $(this).data('id');
    // 调用 sweetalert
    swal({
        title: "确认要删除该地址？",
        icon: "warning",
        buttons: ['取消', '确定'],
        dangerMode: true,
      })
    .then(function(willDelete) { // 用户点击按钮后会触发这个回调函数
      // 用户点击确定 willDelete 值为 true， 否则为 false
      // 用户点了取消，啥也不做
      if (!willDelete) {
        return;
      }
      // 调用删除接口，用 id 来拼接出请求的 url
      axios.delete('/user_addresses/' + id)
        .then(function () {
          // 请求成功之后重新加载页面
          location.reload();
        })
    });
  });
});
</script>
@endsection
