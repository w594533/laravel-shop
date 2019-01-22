@extends('layouts.app')
@section('title', '分期付款列表')

@section('content')
  <div class="panel panel-default">
    <div class="panel-heading clearfix">
      分期付款列表
    </div>
    <div class="panel-body">
      <table class="table table-bordered">
        <thead>
          <tr>
            <th>编号</th>
            <th>金额</th>
            <th>期数</th>
            <th>费率</th>
            <th>状态</th>
            <th>操作</th>
          </tr>
        </thead>
        <tbody>
          @foreach ($installments as $installment)
            <tr>
              <td>{{ $installment->no }}</td>
              <td>{{ $installment->count }}</td>
              <td>{{ $installment->total_amount }}</td>
              <td>{{ $installment->fee_rate }}%</td>
              <td>{{ \App\Models\Installment::$statusMap[$installment->status] }}</td>
              <td>
                <a href="{{ route('installments.show', ['installment' => $installment->id]) }}" class="btn btn-primary">查看</a>
              </td>
            </tr>
          @endforeach
        </tbody>
      </table>
      <div class="float-right">{{ $installments->render() }}</div>
    </div>
  </div>
@endsection
