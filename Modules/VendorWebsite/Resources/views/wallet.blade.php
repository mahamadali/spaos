@extends('vendorwebsite::layouts.master')
@section('title') {{__('vendorwebsite.wallet_balance')}} @endsection

@section('content')
    <x-breadcrumb />
    <div class="section-spacing-inner-pages">
        <div class="wallet-container">
            <div class="container">
                <x-balance_section :banks="$banks" :withdrawals="$withdrawals" />
                <x-history_section :withdrawals="$withdrawals" />
            </div>
        </div>
    </div>
@endsection
