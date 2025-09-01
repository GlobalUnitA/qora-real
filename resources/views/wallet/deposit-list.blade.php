@extends('layouts.master')

@section('content')
<div class="container py-5">
    <h2 class="mb-3 text-center">제테크 월렛 입금내역</h2>
    <hr>
    <div class="table-responsive">
        <table class="table table-striped table-bordered">
            <thead class="mb-2">
                <tr>
                    <th>일자</th>
                    <th>자산</th>
                    <th>수량</th>
                    <th>수수료</th>
                </tr>
            </thead>
            <tbody id="loadMoreContainer"> 
                @foreach($list as $key => $value)       
                <tr>
                    <td>{{ $value->created_at->format('Y-m-d') }}</td>
                    <td>{{ $value->wallet->coin->code }}</td>
                    <td>{{ $value->amount }}</td>
                    <td>{{ $value->fee }}</td>
                </tr>
                @endforeach
            </tbody>
        </table>
        @if($has_more)
        <form method="POST" action="{{ route('wallet.deposit.list.loadMore') }}" id="loadMoreForm">
            @csrf
            <input type="hidden" name="offset" value="10">
            <input type="hidden" name="limit" value="10">
            <button type="submit" class="btn btn-outline-primary w-100 py-2 my-4 fs-4">더보기</button>
        </form>
        @endif
    </div>
</div>
@endsection

@push('script')
@verbatim
<template id="loadMoreTemplate">
    <tr>
        <td>{{created_at}}</td>
        <td>{{coin_code}}</td>
        <td>{{status_text}}</td>
        <td>{{amount}}</td>
    </tr>
</template>
@endverbatim
@endpush