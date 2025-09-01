@extends('layouts.master')

@section('content')

<header class="p-4 w-100 border-top-title" style="background: url('../images/tit_bg_01.png') center right no-repeat, #1e1e1f;" >
    <h2 class="text-white mb-1 px-1">Withdrawal</h2>
    <h6 class="text-white mb-4 px-1">{{ __('asset.withdrawal') }}</h6>
    <div class="m-0 px-1">
        <a href="{{ route('asset.withdrawal.list') }}">
            <h5 class="btn btn-primary border-0 m-0" style="background: linear-gradient(to right, #00d8a0, #00b084);">{{ __('asset.withdrawal_list') }}</h5>
        </a>
    </div>
</header>
<main class="container-fluid py-5 mb-5">
    <div class="px-3 mb-5">
        <form method="POST" action="{{ route('asset.withdrawal.store') }}" id="withdrawalForm">
            @csrf
            <fieldset>
                <legend class="mb-3 fs-4 text-body">{{ __('asset.select_withdrawal_asset_guide') }}</legend>
                <div class="d-grid d-grid-col-2 mb-3">                    
                @foreach ($assets as $asset)
                    <div class="selectedAsset">
                        <input type="radio" class="btn-check" name="asset" value="{{ $asset->encrypted_id }}" id="{{ $asset->coin->code }}" autocomplete="off" data-balance="{{ $asset->balance }}">
                        <label class="btn btn-light w-100 p-4 rounded text-center fs-5 d-flex flex-column align-items-center" for="{{ $asset->coin->code }}">
                            <img src="{{ $asset->coin->image_urls[0] }}" width="40" alt="{{ $asset->coin->code }}" class="img-fluid mb-2">
                            {{ $asset->coin->name }}
                        </label>
                        <input type="hidden" class="tax_rate" value="{{ $asset->tax_rate }}">
                        <input type="hidden" class="fee_rate" value="{{ $asset->fee_rate }}">
                    </div>
                @endforeach                                  
                </div>                
            </fieldset>
            <div class="my-4">
                <label class="form-label fs-4 text-body">{{ __('asset.withdrawal_amount_guide') }}</label>
                <input type="text" name="amount" class="form-control mb-3"  placeholder="0">
                <p class="mb-5 opacity-50 fw-light fs-4 d-none" id="stock-label">{{ __('system.stock_amount') }}: <span id="stock" class="fw-bold"></span></p>
                <input type="hidden" name="tax">
                <input type="hidden" name="fee">
                <div>
                    <div class="text-center">
                        <!--p class="mb-1">
                        <span class="pe-1">{{ __('asset.fee') }}: <span id="fee">0.00</span></span>
                        <span class="divider position-relative ps-2">{{ __('asset.tax') }}: <span id="tax">0.00</span></span>
                        </p-->
                        <h4 class="pb-4 text-primary">{{ __('asset.withdrawal_actual_amount') }}: <span id="finalAmount">0.00</span></h4>
                    </div>
                    <div class="text-body mb-4">
                        <h6 class="text-body mt-4">{{ __('asset.withdrawal_notice') }}</h6>
                        <p class="mb-1">- {{ __('asset.withdrawal_min_amount') }}</p>
                        <p class="mb-1">- {{ __('asset.withdrawal_arrival_period_guide2') }}</p>
                        <p class="mb-1">- {{ __('asset.withdrawal_tax_guide') }}</p>
                    </div>
            </div>
            <button type="submit" class="btn btn-primary w-100 py-3 mb-4 fs-4">{{ __('asset.withdrawal') }}</button>                                
        </form>
    </div>
</main>
@endsection

@push('message')
<div id="msg_withdrawal_asset" data-label="{{ __('asset.select_withdrawal_asset_guide') }}"></div>
<div id="msg_withdrawal_amount" data-label="{{ __('asset.withdrawal_amount_guide') }}"></div>
@endpush

@push('script')
<script src="{{ asset('js/asset/withdrawal.js') }}"></script>
@endpush