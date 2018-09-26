@extends('layouts.app')
@section('title',  '收货地址 - ' . ($user_address->id?'修改':'新增'))

@section('content')
<div class="panel panel-default">
  <div class="panel-heading clearfix">
    {{ $user_address->id?'修改':'新增' }}收货地址
  </div>
  <div class="panel-body">
    @if($user_address->id)
    <form class="form-horizontal" role="form" action="{{ route('user_addresses.update', ['user_address' => $user_address->id]) }}" method="post">
      {{ method_field('PUT') }}
  @else
    <form class="form-horizontal" role="form" action="{{ route('user_addresses.store') }}" method="post">
  @endif
      {{ csrf_field() }}
      <div class="form-group">
        <label for="contact_name" class="col-sm-2 control-label">收货人姓名</label>
        <div class="col-sm-10">
          <input type="text" class="form-control" name="contact_name" id="contact_name" value="{{ old('contact_name', $user_address->contact_name) }}">
          </div>
        </div>
        <div class="form-group">
          <label for="contact_phone" class="col-sm-2 control-label">电话</label>
          <div class="col-sm-10">
            <input type="text" class="form-control" name="contact_phone" id="contact_phone" value="{{ old('contact_phone', $user_address->contact_phone) }}">
          </div>
          </div>
          <div class="form-group">
            <label for="zip" class="col-sm-2 control-label">邮编</label>
            <div class="col-sm-10">
              <input type="text" class="form-control" name="zip" id="zip" value="{{ old('zip', $user_address->zip) }}">
          </div>
            </div>
            <div class="form-group">
              <label for="contact_name" class="col-sm-2 control-label">地址</label>
              <div class="col-sm-10">
                <div id="distpicker" class="distpicker">
                  <select name="province" class="form-control"></select>
                  <select name="city" class="form-control"></select>
                  <select name="district" class="form-control"></select>
                </div>
              </div>
            </div>
            <div class="form-group">
              <label for="address" class="col-sm-2 control-label">详细地址/门牌号</label>
              <div class="col-sm-10">
                <input type="text" class="form-control" name="address" id="address" value="{{ old('address', $user_address->address) }}">
            </div>
              </div>
              <div class="form-group">
                <div class="col-sm-offset-2 col-sm-10">
                  <button type="submit" class="btn btn-default">提交</button>
                </div>
              </div>
    </form>
  </div>
</div>
@endsection

@section('scripts')
<script src="{{ mix('js/distpicker.js') }}"></script>
<script>
$("#distpicker").distpicker({
  province: "{{ $user_address->province }}",
  city: "{{ $user_address->city }}",
  district: "{{ $user_address->district }}"
});
</script>
@endsection
