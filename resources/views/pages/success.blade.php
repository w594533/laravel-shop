@extends('layouts.app')

@section('title', '消息通知')

@section('content')
  <div class="panel panel-default">
    <div class="panel-heading">提示</div>
    <div class="panel-body text-center">
        <h1>{{ $message }}</h1>
        <a class="btn btn-primary" href="{{ route('root') }}">返回首页</a>
    </div>
  </div>
@endsection
