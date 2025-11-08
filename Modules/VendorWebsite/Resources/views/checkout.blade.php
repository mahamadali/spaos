@extends('vendorwebsite::layouts.master')
@section('title')
    {{ __('vendorwebsite.checkout') }}
@endsection

@section('content')

    {{-- Debug: Show logged in user name --}}
    {{-- {{ auth()->user()->name }} --}}

    <x-breadcrumb title="Checkout" />
    <div class="cart-page section-spacing-inner-pages">
        <div class="container">
            <div class="row gy-4">
                <div class="col-md-7 col-lg-9">
                    <div class="cart-table">
                        <div class="table-responsive">
                            <div id="empty-cart-message" style="display:none;">
                                <div class="empty-cart text-center py-5">
                                    <img src="{{ asset('img/vendorwebsite/empty-cart.jpg') }}" alt="Empty Cart"
                                        class="img-fluid mb-3 avatar-150"
                                        onerror="this.onerror=null;this.src='https://cdn.jsdelivr.net/gh/edent/SuperTinyIcons/images/svg/shopping-cart.svg';">
                                    <h5>{{ __('vendorwebsite.your_cart_is_empty') }}</h5>
                                    <p class="text-body">
                                        {{ __('vendorwebsite.add_items_to_your_cart_to_proceed_with_checkout') }}</p>
                                    <a href="{{ route('shop') }}"
                                        class="btn btn-primary mt-3">{{ __('vendorwebsite.continue_shopping') }}</a>
                                </div>
                            </div>
                            <table id="checkout-table" class="table table-borderless custom-table-bg rounded">
                                <thead>
                                    <tr>
                                        <th>{{ __('vendorwebsite.product') }}</th>
                                        <th>{{ __('vendorwebsite.price') }}</th>
                                        <th>{{ __('vendorwebsite.discount') }} (%)</th>

                                        <th>{{ __('vendorwebsite.quantity') }}</th>
                                        <th>{{ __('vendorwebsite.subtotal') }}</th>
                                        <th></th>
                                    </tr>
                                </thead>
                            </table>
                        </div>
                    </div>

                    <div id="checkout-sections">

                        <div class="address-block">
                            <div class="d-flex flex-wrap align-items-center justify-content-between gap-3 mb-3">
                                <h5 class="mb-0">{{ __('vendorwebsite.delivery_address') }}</h5>
                                <button type="button" class="btn btn-primary" data-bs-toggle="modal"
                                    data-bs-target="#modalAddAddress" onclick="prefillUserName()">
                                    {{ __('vendorwebsite.add_new_address') }}
                                </button>
                            </div>

                            @if ($addresses->isNotEmpty())
                                @php
                                    $validAddresses = $addresses->filter(function ($address) {
                                        return $address->address_line_1 ||
                                            $address->city_data ||
                                            $address->state_data ||
                                            $address->country_data ||
                                            $address->postal_code;
                                    });

                                    $primaryAddress =
                                        $validAddresses->firstWhere('is_primary', 1) ?? $validAddresses->first();
                                    $otherAddresses = $validAddresses->where('id', '!=', $primaryAddress->id);
                                @endphp

                                @if ($validAddresses->isNotEmpty())

                                    <div class="bg-gray-800 p-4 rounded d-flex flex-wrap align-items-center justify-content-between gap-3 mb-3 address-card active"
                                        data-id="{{ $primaryAddress->id }}"
                                        onclick="selectAddress({{ $primaryAddress->id }}, this)">
                                        <div class="d-flex align-items-center gap-3 w-100">
                                            <div class="form-check">
                                                <input class="form-check-input address-radio" type="radio" name="address"
                                                    value="{{ $primaryAddress->id }}" checked>
                                            </div>
                                            <div class="flex-grow-1">
                                                <div class="d-flex justify-content-between align-items-start">
                                                    <div>
                                                        <p class="mb-1 fw-medium">
                                                            <span class="user-name">
                                                                {{ $primaryAddress->first_name }}
                                                                {{ $primaryAddress->last_name }}
                                                            </span>

                                                            @if ($primaryAddress->is_primary)
                                                                <span
                                                                    class="badge bg-primary ms-2">{{ __('vendorwebsite.primary') }}</span>
                                                            @endif
                                                        </p>
                                                        <p class="mb-1 small text-body address-line">
                                                            {{ $primaryAddress->address_line_1 }}
                                                            @if ($primaryAddress->address_line_2)
                                                                , {{ $primaryAddress->address_line_2 }}
                                                            @endif
                                                        </p>
                                                        <p class="mb-0 small text-body address-line">
                                                            {{ optional($primaryAddress->city_data)->name }},
                                                            {{ optional($primaryAddress->state_data)->name }},
                                                            {{ optional($primaryAddress->country_data)->name }} -
                                                            {{ $primaryAddress->postal_code }}
                                                        </p>
                                                        <p class="mb-0 small text-body contact-number mt-1">
                                                            <i class="ph ph-phone"></i><span
                                                                class="user-contact-number">{{ $primaryAddress->contact_number }}</span>
                                                        </p>
                                                        <p class="mb-0 small text-body  mt-1 d-none">
                                                            <i class="ph ph-envelope"></i><span
                                                                class="user-email">{{ $primaryAddress->email }}</span>
                                                        </p>
                                                    </div>
                                                    <button class="btn btn-link text-success p-0 edit-address-btn"
                                                        data-bs-target="#modalAddAddress" data-bs-toggle="modal"
                                                        onclick="event.stopPropagation(); editAddress({{ $primaryAddress->id }})">
                                                        <i class="ph ph-pencil-simple"></i>
                                                    </button>
                                                </div>
                                            </div>
                                        </div>
                                    </div>

                                    @if ($otherAddresses->isNotEmpty())
                                        <div class="text-center mb-3">
                                            <a href="#otherAddresses" class="btn btn-outline-primary btn-sm"
                                                data-bs-toggle="collapse" role="button" aria-expanded="false"
                                                aria-controls="otherAddresses">

                                                {{ __('vendorwebsite.view_other_addresses') }}
                                            </a>
                                        </div>


                                        <div class="collapse" id="otherAddresses">
                                            @foreach ($otherAddresses as $address)
                                                <div class="bg-gray-800 p-4 rounded d-flex flex-wrap align-items-center justify-content-between gap-3 mb-3 address-card cursor-pointer"
                                                    data-id="{{ $address->id }}"
                                                    onclick="selectAddress({{ $address->id }}, this)">
                                                    <div class="d-flex align-items-center gap-3 w-100">
                                                        <div class="form-check">
                                                            <input class="form-check-input address-radio" type="radio"
                                                                name="address" value="{{ $address->id }}">
                                                        </div>
                                                        <div class="flex-grow-1">
                                                            <div class="d-flex justify-content-between align-items-start">
                                                                <div>
                                                                    <p class="mb-1 fw-medium">
                                                                        <span class="user-name">
                                                                            {{ $address->first_name }}
                                                                            {{ $address->last_name }}
                                                                        </span>
                                                                    </p>
                                                                    <p class="mb-1 small text-body address-line">
                                                                        {{ $address->address_line_1 }}
                                                                        @if ($address->address_line_2)
                                                                            , {{ $address->address_line_2 }}
                                                                        @endif
                                                                    </p>
                                                                    <p class="mb-0 small text-body address-line">
                                                                        {{ optional($address->city_data)->name }},
                                                                        {{ optional($address->state_data)->name }},
                                                                        {{ optional($address->country_data)->name }} -
                                                                        {{ $address->postal_code }}
                                                                    </p>
                                                                    <p class="mb-0 small text-body contact-number mt-1">
                                                                        <i class="ph ph-phone"></i><span
                                                                            class="user-contact-number">{{ $address->contact_number }}</span>
                                                                    </p>
                                                                    <p class="mb-0 small text-body mt-1 d-none">
                                                                        <i class="ph ph-envelope"></i><span
                                                                            class="user-email">{{ $address->email }}</span>
                                                                    </p>
                                                                </div>
                                                                <button
                                                                    class="btn btn-link text-success p-0 edit-address-btn"
                                                                    data-bs-target="#modalAddAddress" data-bs-toggle="modal"
                                                                    onclick="event.stopPropagation(); editAddress({{ $address->id }})">
                                                                    <i class="ph ph-pencil-simple"></i>
                                                                </button>
                                                            </div>
                                                        </div>
                                                    </div>
                                                </div>
                                            @endforeach
                                        </div>
                                    @endif
                                @else
                                    <div class="bg-gray-800 p-4 rounded text-center">
                                        <p class="mb-0">
                                            {{ __('vendorwebsite.no_addresses_found_please_add_a_new_address_to_continue') }}
                                        </p>
                                    </div>
                                @endif
                            @else
                                <div class="bg-gray-800 p-4 rounded text-center">
                                    <p class="mb-0">
                                        {{ __('vendorwebsite.no_addresses_found_please_add_a_new_address_to_continue') }}
                                    </p>
                                </div>
                            @endif
                        </div>

                        @if ($addresses->isNotEmpty() && $validAddresses->isNotEmpty())
                            <div class="charges-block mt-4">
                                <h5 class="mb-3">{{ __('vendorwebsite.delivery_charges') }}</h5>
                                <div class="delivery-zones-container">
                                </div>
                            </div>
                        @endif



                        <!-- <div class="col-12 mt-4 mb-5">
                                                                                                                                                                            <h5>{{ __('vendorwebsite.select_payment_method') }}</h5>
                                                                                                                                                                            <div class="mb-5">
                                                                                                                                                                                <div class="dropdown payment-method-dropdown mt-3" id="payment-method-dropdown">
                                                                                                                                                                                    <button type="button"
                                                                                                                                                                                        class="border-0 rounded w-100 payments-container d-flex justify-content-between align-items-center gap-3 payments-show-list bg-gray-800"
                                                                                                                                                                                        id="selected-method-btn">
                                                                                                                                                                                        <span class="d-flex align-items-center gap-2">
                                                                                                                                                                                            <img id="selected-method-img avatar-24"
                                                                                                                                                                                                src="{{ asset('img/vendorwebsite/cash.svg') }}" alt="Cash">
                                                                                                                                                                                            <span id="selected-method-name"
                                                                                                                                                                                                class="flex-shrink-0 font-size-14 fw-medium heading-color">{{ __('vendorwebsite.cash') }}</span>
                                                                                                                                                                                        </span>
                                                                                                                                                                                        <i class="ph ph-caret-down"></i>
                                                                                                                                                                                    </button>
                                                                                                                                                                                    <div class="dropdown-menu w-100 bg-gray-800 rounded booking-payment-method mt-3 show"
                                                                                                                                                                                        id="payment-method-list">
                                                                                                                                                                                        <div class="list-group " style="border: none;">
                                                                                                                                                                                            @php $first = true; @endphp
                                                                                                                                                                                            @if (isset($paymentMethods) && is_array($paymentMethods))
    @foreach ($paymentMethods as $method => $enabled)
    @if ($enabled)
    <label
                                                                                                                                                                                                            class="list-group-item d-flex align-items-center justify-content-between gap-3 payment-method-items cursor-pointer border-0 p-0 bg-transparent">
                                                                                                                                                                                                            <span class="d-flex align-items-center gap-3">
                                                                                                                                                                                                                @if ($method == 'cash')
    <img src="{{ asset('img/vendorwebsite/cash.svg') }}"
                                                                                                                                                                                                                        alt="Cash" class="avatar-28">
                                                                                                                                                                                                                    <span>{{ __('vendorwebsite.cash') }}</span>
@elseif($method == 'wallet')
    <img src="{{ asset('img/vendorwebsite/wallet.svg') }}"
                                                                                                                                                                                                                        alt="Wallet" class="avatar-28">
                                                                                                                                                                                                                    <span>Wallet</span>
                                                                                                                                                                                                                    <span
                                                                                                                                                                                                                        class="text-success">({{ Currency::vendorCurrencyFormate($walletBalance ?? 0) }})</span>
@else
    @php
        $icon = strtolower($method);
        $displayName = $method;
        if ($method == 'stripe') {
            $displayName = 'Stripe';
        }
    @endphp
                                                                                                                                                                                                                    <img src="{{ asset('img/vendorwebsite/' . $icon . '.svg') }}"
                                                                                                                                                                                                                        alt="{{ $displayName }}" class="avatar-28">
                                                                                                                                                                                                                    <span>{{ ucfirst($displayName) }}</span>
    @endif
                                                                                                                                                                                                            </span>
                                                                                                                                                                                                            <input type="radio"
                                                                                                                                                                                                                class="form-check-input payment-radio m-0"
                                                                                                                                                                                                                name="payment_method" value="{{ $method }}"
                                                                                                                                                                                                                id="method-{{ strtolower($method) }}"
                                                                                                                                                                                                                {{ $first ? 'checked' : '' }}>
                                                                                                                                                                                                        </label>
                                                                                                                                                                                                        @php $first = false; @endphp
    @endif
    @endforeach
    @endif
                                                                                                                                                                                        </div>
                                                                                                                                                                                    </div>
                                                                                                                                                                                </div>
                                                                                                                                                                            </div>
                                                                                                                                                                        </div> -->

                        <div class="col-12 mt-4 mb-5">
                            <h5>{{ __('vendorwebsite.select_payment_method') }}</h5>
                            <div class="mb-5">
                                <div class="payment-method-collapse mt-3" id="payment-method-collapse">
                                    <!-- Collapse toggle button -->
                                    {{-- <button type="button"
                                        class="border-0 rounded w-100 payments-container d-flex justify-content-between align-items-center gap-3 payments-show-list bg-gray-800"
                                        data-bs-toggle="collapse" data-bs-target="#payment-method-list"
                                        aria-expanded="false" aria-controls="payment-method-list"
                                        id="selected-method-btn">
                                        <span class="d-flex align-items-center gap-2">
                                            <img id="selected-method-img" class="avatar-24"
                                                src="{{ asset('img/vendorwebsite/cash.svg') }}" alt="Cash">
                                            <span id="selected-method-name"
                                                class="flex-shrink-0 font-size-14 fw-medium heading-color">{{ __('vendorwebsite.cash') }}</span>
                                        </span>
                                        <i class="ph ph-caret-down"></i>
                                    </button> --}}

                                    <button type="button"
                                        class="border-0 rounded w-100 payments-container d-flex justify-content-between align-items-center gap-3 payments-show-list bg-gray-800"
                                        id="selected-method-btn">
                                        <span class="d-flex align-items-center gap-2">
                                            <img id="selected-method-img" class="avatar-24"
                                                src="{{ asset('img/vendorwebsite/cash.svg') }}" alt="Cash">
                                            <span id="selected-method-name"
                                                class="flex-shrink-0 font-size-14 fw-medium heading-color">{{ __('vendorwebsite.cash') }}</span>
                                        </span>
                                        <i class="ph ph-caret-down" id="toggle-icon"></i>
                                    </button>


                                    <!-- Collapsible content -->
                                    <div class="collapse mt-3" id="payment-method-list">
                                        <div class="list-group booking-payment-method bg-gray-800 rounded"
                                            style="border: none;">
                                            @php $first = true; @endphp
                                            @if (isset($paymentMethods) && is_array($paymentMethods))
                                                @foreach ($paymentMethods as $method => $enabled)
                                                    @if ($enabled)
                                                        <label
                                                            class="list-group-item d-flex align-items-center justify-content-between gap-3 payment-method-items cursor-pointer border-0 p-0 bg-transparent">
                                                            <span class="d-flex align-items-center gap-3">
                                                                @if ($method == 'cash')
                                                                    <img src="{{ asset('img/vendorwebsite/cash.svg') }}"
                                                                        alt="Cash" class="avatar-28">
                                                                    <span>{{ __('vendorwebsite.cash') }}</span>
                                                                @elseif($method == 'wallet')
                                                                    {{-- <img src="{{ asset('img/vendorwebsite/wallet.svg') }}"
                                                                        alt="Wallet" class="avatar-28">
                                                                    <span>Wallet</span>
                                                                    <span class="text-success">
                                                                        ({{ Currency::vendorCurrencyFormate($walletBalance ?? 0) }})
                                                                    </span> --}}
                                                                @else
                                                                    @php
                                                                        $icon = strtolower($method);
                                                                        $displayName = $method;
                                                                        if ($method == 'stripe') {
                                                                            $displayName = 'Stripe';
                                                                        }
                                                                    @endphp
                                                                    <img src="{{ asset('img/vendorwebsite/' . $icon . '.svg') }}"
                                                                        alt="{{ $displayName }}" class="avatar-28">
                                                                    <span>{{ ucfirst($displayName) }}</span>
                                                                @endif
                                                            </span>
                                                            <input type="radio"
                                                                class="form-check-input payment-radio m-0"
                                                                name="payment_method" value="{{ $method }}"
                                                                id="method-{{ strtolower($method) }}"
                                                                {{ $first ? 'checked' : '' }}>
                                                        </label>
                                                        @php $first = false; @endphp
                                                    @endif
                                                @endforeach
                                            @endif
                                        </div>
                                    </div>
                                </div>
                            </div>
                        </div>

                    </div>


                </div>
                <div class="col-md-5 col-lg-3">
                    <div class="cart-summary">
                        <h5 class="mb-3">{{ __('vendorwebsite.payment_details') }}</h5>
                        <div class="payment-details bg-gray-800 p-4 rounded">
                            <div id="checkout-summary">
                                {{-- Payment Summary (with correct total calculation) --}}
                                <div class="payment-summary mb-3">
                                    <div class="d-flex justify-content-between align-items-center">
                                        <span>{{ $product_name ?? 'Product' }}</span>
                                        <span
                                            id="product-price">{{ Currency::vendorCurrencyFormate($subtotal ?? 0) }}</span>
                                    </div>
                                    <div class="d-flex justify-content-between align-items-center mt-2">
                                        <span>{{ __('vendorwebsite.subtotal') }}</span>
                                        <span id="subtotal">{{ Currency::vendorCurrencyFormate($subtotal ?? 0) }}</span>
                                    </div>
                                    @if (!empty($discount) && $discount > 0)
                                        <div class="d-flex justify-content-between align-items-center mt-2">
                                            <span>{{ __('vendorwebsite.coupon_discount') }}
                                                ({{ $discount_percent ?? 0 }}%)</span>
                                            <span class="text-success"
                                                id="discount">-{{ Currency::vendorCurrencyFormate($discount) }}</span>
                                        </div>
                                    @endif



                                    @if (isset($taxes) && $taxes->count() > 0 && ($subtotal ?? 0) > 0)
                                        <div class="d-flex justify-content-between align-items-center mb-1 price-item">
                                            <span class="font-size-14">{{ __('vendorwebsite.tax') }}</span>
                                            <div class="d-flex justify-content-between align-items-center mb-1 price-item text-decoration-none cursor-pointer taxDetails"
                                                data-bs-toggle="collapse" href="#taxDetailsCheckout" role="button"
                                                aria-expanded="false" aria-controls="taxDetailsCheckout"
                                                style="display:block;">
                                                <i class="ph ph-caret-down rotate-icon tax2"></i>
                                                <span class="font-size-14 fw-medium text-danger" id="tax">
                                                    {{ isset($taxes) && $taxes->count() > 0 && ($subtotal ?? 0) > 0
                                                        ? Currency::vendorCurrencyFormate(
                                                            $taxes->sum(function ($tax) use ($subtotal) {
                                                                if ($tax->type == 'fixed') {
                                                                    return $tax->value;
                                                                }
                                                                if ($tax->type == 'percent') {
                                                                    return (($subtotal ?? 0) * $tax->value) / 100;
                                                                }
                                                                return 0;
                                                            }),
                                                        )
                                                        : Currency::vendorCurrencyFormate(0) }}
                                                </span>
                                            </div>
                                        </div>
                                        <div class="collapse mt-2 mb-2" id="taxDetailsCheckout">
                                            <div class="text-calculate card py-2 px-3" id="tax-details">

                                                @foreach ($taxes as $tax)
                                                    <div
                                                        class="d-flex justify-content-between align-items-center {{ !$loop->last ? 'mb-1' : '' }}">
                                                        <span
                                                            class="font-size-12">{{ $tax->title }}{{ $tax->type == 'percent' ? ' (' . $tax->value . '%)' : '' }}</span>
                                                        <span class="font-size-12 text-danger fw-medium">
                                                            {{ Currency::vendorCurrencyFormate($tax->type == 'fixed' ? $tax->value : (($subtotal ?? 0) * $tax->value) / 100) }}
                                                        </span>
                                                    </div>
                                                @endforeach

                                            </div>
                                        </div>
                                    @endif
                                    <hr>
                                    <div class="d-flex justify-content-between align-items-center fw-bold" id="total-row">
                                        <span>{{ __('vendorwebsite.total') }}</span>
                                        <span class="total-value text-primary" id="total-amount">
                                            {{ Currency::vendorCurrencyFormate(($subtotal ?? 0) - ($discount ?? 0) + (($service_tax ?? 0) + ($gst ?? 0))) }}
                                        </span>
                                    </div>


                                    <div id="wallet-payment-info" class="mt-3"></div>
                                </div>
                            </div>
                        </div>
                    </div>
                    <div class="cart-summary mt-4">
                        <h5 class="mb-3">{{ __('vendorwebsite.order_summery') }}</h5>
                        <div class="payment-details bg-gray-800 p-4 rounded">
                            <div id="order-summary-box">
                                <div class="mb-3">
                                    <div class="d-flex gap-2 mb-2">
                                        <h6 class="m-0 flex-shrink-0">{{ __('vendorwebsite.user_name') }}</h6> <span
                                            id="order-summary-username"></span>
                                    </div>
                                    <div class="d-flex gap-2 mb-2">
                                        <h6 class="m-0 flex-shrink-0">{{ __('vendorwebsite.email') }}:</h6> <span
                                            id="order-summary-email" class="text-break"></span>
                                    </div>
                                    <div class="d-flex gap-2 mb-2">
                                        <h6 class="m-0 flex-shrink-0">{{ __('vendorwebsite.contact_number') }}:</h6> <span
                                            id="order-summary-mobile" class=""></span>
                                    </div>
                                    <div class="d-flex gap-2">
                                        <h6 class="m-0 flex-shrink-0">{{ __('vendorwebsite.address') }}</h6>
                                        <span id="order-summary-address"></span>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>

    </div>

    <div class="onclick-page-redirect bg-orange p-3" id="deliver_button">
        <div class="container">
            <div class="d-flex align-items-center justify-content-end">
                @if(CheckPlanSubscriptionpermission(session('current_vendor_id'), 'view_product'))
                <button class="btn btn-secondary px-5"
                    onclick="placeOrder()">{{ __('vendorwebsite.deliver_here') }}</button>
                @else
                <span class=" text-secondary px-5">{{ __('vendorwebsite.upgrade_plan_to_deliver_here') }}</span>
                @endif
            </div>
        </div>
    </div>

    {{-- <div id="inlineAddressError" class="alert alert-danger" style="display:none;"></div> --}}

    <div class="modal fade" id="modalAddAddress" tabindex="-1">
        <div class="modal-dialog modal-dialog-centered">
            <div class="modal-content">
                <div class="modal-header pb-0 d-flex justify-content-between align-items-start">
                    <div>
                        <h5 class="modal-title" id="modalAddAddressLabel">{{ __('vendorwebsite.add_new_address') }}</h5>

                    </div>
                    <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                </div>
                <div class="modal-body">
                    <form id="addressForm" onsubmit="saveAddress(event)">
                        <input type="hidden" name="address_id" id="address_id">
                        <div class="row gy-4">
                            <div class="col-lg-12">
                                <div class="form-group">
                                    <label for="first_name"
                                        class="form-label fw-medium">{{ __('vendorwebsite.first_name') }}<span
                                            class="text-danger">*</span></label>
                                    <div class="input-group custom-input-group">
                                        <input type="text" name="first_name" class="form-control" id="first_name"
                                            placeholder="eg. Michael" required>
                                        <span class="input-group-text"><i class="ph ph-user"></i></span>
                                    </div>
                                </div>
                            </div>
                            <div class="col-lg-12">
                                <div class="form-group">
                                    <label for="last_name"
                                        class="form-label fw-medium">{{ __('vendorwebsite.last_name') }}<span
                                            class="text-danger">*</span></label>
                                    <div class="input-group custom-input-group">
                                        <input type="text" name="last_name" class="form-control" id="last_name"
                                            placeholder="eg. Thompson" required>
                                        <span class="input-group-text"><i class="ph ph-user"></i></span>
                                    </div>
                                </div>
                            </div>
                            <div class="col-lg-12">
                                <label for="contact_number"
                                    class="form-label">{{ __('vendorwebsite.contact_number') }}<span
                                        class="text-danger">*</span></label>
                                <div class="input-group custom-input-group position-relative">
                                    <input type="tel" id="mobileInput" name="contact_number"
                                        class="form-control font-size-14" required>
                                    <span class="input-group-text"><i class="ph ph-phone"></i></span>
                                </div>
                            </div>
                            <div class="col-lg-12">
                                <div class="form-group">
                                    <label for="email"
                                        class="form-label fw-medium">{{ __('vendorwebsite.email') }}<span
                                            class="text-danger"></span></label>
                                    <div class="input-group custom-input-group">
                                        <input type="email" name="email" class="form-control" id="email"
                                            placeholder="eg. Thompson">
                                        <span class="input-group-text"><i class="ph ph-envelope-simple"></i></span>
                                    </div>
                                </div>
                            </div>

                            <div class="col-lg-12">
                                <div class="form-group">
                                    <label for="country"
                                        class="form-label fw-medium">{{ __('vendorwebsite.country') }}<span
                                            class="text-danger">*</span></label>
                                    <div class="input-group custom-input-group">
                                        <select name="country" id="country" class="form-control" required>
                                            <option value="" disabled selected>
                                                {{ __('vendorwebsite.select_country') }}</option>
                                        </select>
                                        <span class="input-group-text">
                                            <svg width="16" height="17" viewBox="0 0 16 17" fill="none"
                                                xmlns="http://www.w3.org/2000/svg">
                                                <path
                                                    d="M13.5326 7.02976L8.53255 12.0298C8.46287 12.0997 8.38008 12.1552 8.28892 12.193C8.19775 12.2309 8.10001 12.2503 8.0013 12.2503C7.90259 12.2503 7.80485 12.2309 7.71369 12.193C7.62252 12.1552 7.53973 12.0997 7.47005 12.0298L2.47005 7.02976C2.32915 6.88886 2.25 6.69777 2.25 6.49851C2.25 6.29925 2.32915 6.10815 2.47005 5.96726C2.61095 5.82636 2.80204 5.74721 3.0013 5.74721C3.20056 5.74721 3.39165 5.82636 3.53255 5.96726L8.00193 10.4366L12.4713 5.96663C12.6122 5.82574 12.8033 5.74658 13.0026 5.74658C13.2018 5.74658 13.3929 5.82574 13.5338 5.96663C13.6747 6.10753 13.7539 6.29863 13.7539 6.49788C13.7539 6.69714 13.6747 6.88824 13.5338 7.02913L13.5326 7.02976Z"
                                                    fill="#A6A8A8" />
                                            </svg>
                                        </span>
                                    </div>
                                </div>
                            </div>
                            <div class="col-lg-12">
                                <div class="form-group">
                                    <label for="state"
                                        class="form-label fw-medium">{{ __('vendorwebsite.state') }}<span
                                            class="text-danger">*</span></label>
                                    <div class="input-group custom-input-group">
                                        <select name="state" id="state" class="form-control" required>
                                            <option value="" disabled selected>
                                                {{ __('vendorwebsite.select_state') }}
                                            </option>
                                        </select>
                                        <span class="input-group-text">
                                            <svg width="16" height="17" viewBox="0 0 16 17" fill="none"
                                                xmlns="http://www.w3.org/2000/svg">
                                                <path
                                                    d="M13.5326 7.02976L8.53255 12.0298C8.46287 12.0997 8.38008 12.1552 8.28892 12.193C8.19775 12.2309 8.10001 12.2503 8.0013 12.2503C7.90259 12.2503 7.80485 12.2309 7.71369 12.193C7.62252 12.1552 7.53973 12.0997 7.47005 12.0298L2.47005 7.02976C2.32915 6.88886 2.25 6.69777 2.25 6.49851C2.25 6.29925 2.32915 6.10815 2.47005 5.96726C2.61095 5.82636 2.80204 5.74721 3.0013 5.74721C3.20056 5.74721 3.39165 5.82636 3.53255 5.96726L8.00193 10.4366L12.4713 5.96663C12.6122 5.82574 12.8033 5.74658 13.0026 5.74658C13.2018 5.74658 13.3929 5.82574 13.5338 5.96663C13.6747 6.10753 13.7539 6.29863 13.7539 6.49788C13.7539 6.69714 13.6747 6.88824 13.5338 7.02913L13.5326 7.02976Z"
                                                    fill="#A6A8A8" />
                                            </svg>
                                        </span>
                                    </div>
                                </div>
                            </div>
                            <div class="col-lg-12">
                                <div class="form-group">
                                    <label for="city"
                                        class="form-label fw-medium">{{ __('vendorwebsite.city') }}<span
                                            class="text-danger">*</span></label>
                                    <div class="input-group custom-input-group">
                                        <select name="city" id="city" class="form-control" required>
                                            <option value="" disabled selected>
                                                {{ __('vendorwebsite.select_city') }}
                                            </option>
                                        </select>
                                        <span class="input-group-text">
                                            <svg width="16" height="17" viewBox="0 0 16 17" fill="none"
                                                xmlns="http://www.w3.org/2000/svg">
                                                <path
                                                    d="M13.5326 7.02976L8.53255 12.0298C8.46287 12.0997 8.38008 12.1552 8.28892 12.193C8.19775 12.2309 8.10001 12.2503 8.0013 12.2503C7.90259 12.2503 7.80485 12.2309 7.71369 12.193C7.62252 12.1552 7.53973 12.0997 7.47005 12.0298L2.47005 7.02976C2.32915 6.88886 2.25 6.69777 2.25 6.49851C2.25 6.29925 2.32915 6.10815 2.47005 5.96726C2.61095 5.82636 2.80204 5.74721 3.0013 5.74721C3.20056 5.74721 3.39165 5.82636 3.53255 5.96726L8.00193 10.4366L12.4713 5.96663C12.6122 5.82574 12.8033 5.74658 13.0026 5.74658C13.2018 5.74658 13.3929 5.82574 13.5338 5.96663C13.6747 6.10753 13.7539 6.29863 13.7539 6.49788C13.7539 6.69714 13.6747 6.88824 13.5338 7.02913L13.5326 7.02976Z"
                                                    fill="#A6A8A8" />
                                            </svg>
                                        </span>
                                    </div>
                                </div>
                            </div>
                            <div class="col-lg-12">
                                <div class="form-group">
                                    <label for="pin_code"
                                        class="form-label fw-medium">{{ __('vendorwebsite.pin_code') }}<span
                                            class="text-danger">*</span></label>
                                    <div class="input-group custom-input-group">
                                        <input type="text" name="pin_code" class="form-control" id="pin_code"
                                            placeholder="eg. 900001" pattern="^\d{6,7}$" maxlength="7" minlength="6"
                                            required oninput="this.value = this.value.replace(/[^0-9]/g, '')">
                                        <span class="input-group-text">
                                            <svg width="16" height="17" viewBox="0 0 16 17" fill="none"
                                                xmlns="http://www.w3.org/2000/svg">
                                                <g clip-path="url(#clip0_3134_40625)">
                                                    <path
                                                        d="M8 8.5C9.65685 8.5 11 7.15685 11 5.5C11 3.84315 9.65685 2.5 8 2.5C6.34315 2.5 5 3.84315 5 5.5C5 7.15685 6.34315 8.5 8 8.5Z"
                                                        stroke="#A6A8A8" stroke-width="1.5" stroke-linecap="round"
                                                        stroke-linejoin="round" />
                                                    <path d="M8 14V8.5" stroke="#A6A8A8" stroke-width="1.5"
                                                        stroke-linecap="round" stroke-linejoin="round" />
                                                    <path d="M2.5 14H13.5" stroke="#A6A8A8" stroke-width="1.5"
                                                        stroke-linecap="round" stroke-linejoin="round" />
                                                </g>
                                                <defs>
                                                    <clipPath id="clip0_3134_40625">
                                                        <rect width="16" height="16" fill="white"
                                                            transform="translate(0 0.5)" />
                                                    </clipPath>
                                                </defs>
                                            </svg>
                                        </span>
                                    </div>
                                </div>
                            </div>
                            <div class="col-lg-12">
                                <div class="form-group">
                                    <label for="address"
                                        class="form-label fw-medium">{{ __('vendorwebsite.address') }}<span
                                            class="text-danger">*</span></label>
                                    <div class="input-group custom-input-group">
                                        <input type="text" name="address" class="form-control" id="address_line_1"
                                            placeholder="eg. 123 Elm Street, Springfield" required>
                                        <span class="input-group-text">
                                            <svg width="16" height="17" viewBox="0 0 16 17" fill="none"
                                                xmlns="http://www.w3.org/2000/svg">
                                                <g clip-path="url(#clip0_3134_40630)">
                                                    <path
                                                        d="M8 6.5C8.55228 6.5 9 6.05228 9 5.5C9 4.94772 8.55228 4.5 8 4.5C7.44772 4.5 7 4.94772 7 5.5C7 6.05228 7.44772 6.5 8 6.5Z"
                                                        fill="#A6A8A8" />
                                                    <path
                                                        d="M11.5 5.5C11.5 9 8 11 8 11C8 11 4.5 9 4.5 5.5C4.5 4.57174 4.86875 3.6815 5.52513 3.02513C6.1815 2.36875 7.07174 2 8 2C8.92826 2 9.8185 2.36875 10.4749 3.02513C11.1313 3.6815 11.5 4.57174 11.5 5.5Z"
                                                        stroke="#A6A8A8" stroke-width="1.5" stroke-linecap="round"
                                                        stroke-linejoin="round" />
                                                    <path
                                                        d="M12.5 10.1963C13.7325 10.6513 14.5 11.2913 14.5 12C14.5 13.3807 11.59 14.5 8 14.5C4.41 14.5 1.5 13.3807 1.5 12C1.5 11.2913 2.2675 10.6513 3.5 10.1963"
                                                        stroke="#A6A8A8" stroke-width="1.5" stroke-linecap="round"
                                                        stroke-linejoin="round" />
                                                </g>
                                                <defs>
                                                    <clipPath id="clip0_3134_40630">
                                                        <rect width="16" height="16" fill="white"
                                                            transform="translate(0 0.5)" />
                                                    </clipPath>
                                                </defs>
                                            </svg>
                                        </span>
                                    </div>
                                </div>
                            </div>
                            <div class="col-lg-12">
                                <div class="form-check">
                                    <input type="checkbox" class="form-check-input" id="set_as_primary"
                                        name="set_as_primary">
                                    <label class="form-check-label"
                                        for="set_as_primary">{{ __('vendorwebsite.set_as_primary') }}</label>
                                </div>
                            </div>
                        </div>
                    </form>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-secondary"
                        onclick="submitAddress()">{{ __('vendorwebsite.confirm') }}</button>
                </div>
            </div>
        </div>
    </div>

    <!-- Add Razorpay JS SDK -->
    <script src="https://checkout.razorpay.com/v1/checkout.js"></script>
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/intl-tel-input/17.0.19/css/intlTelInput.css">
    <script src="https://cdnjs.cloudflare.com/ajax/libs/intl-tel-input/17.0.19/js/intlTelInput.min.js"></script>

@endsection

@push('scripts')
    <!-- Paystack JavaScript SDK -->
    <script src="https://js.paystack.co/v1/inline.js"></script>

    <!-- Flutterwave JavaScript SDK -->
    <script src="https://checkout.flutterwave.com/v3.js"></script>

    <script>
        function formatCurrencyvalue(value) {
            value = parseFloat(value);
            if (window.currencyFormat !== undefined) {
                return window.currencyFormat(value);
            }
            return value.toFixed(2);
        }

        var mobileInput = document.querySelector("#mobileInput");
        var iti = window.intlTelInput(mobileInput, {
            initialCountry: "in",
            separateDialCode: true,
            utilsScript: "https://cdnjs.cloudflare.com/ajax/libs/intl-tel-input/17.0.19/js/utils.js"
        });

        // Add digit-only validation for mobile input
        mobileInput.addEventListener('input', function(e) {
            var value = this.value;
            // Remove any non-digit characters except + (for country code)
            var cleanedValue = value.replace(/[^\d+]/g, '');

            // If the cleaned value is different from the original, update the input
            if (cleanedValue !== value) {
                this.value = cleanedValue;
            }
        });

        // Prevent paste of non-digit characters

        $('.editable-field, .editable-span').css('cursor', 'pointer');

        let checkoutTable;

        $(document).ready(function() {
            // Initialize checkout DataTable
            checkoutTable = $('#checkout-table').DataTable({
                processing: '',
                serverSide: false,
                autoWidth: false,
                responsive: true,
                ajax: "{{ route('checkout.data') }}",
                columns: [{
                        data: 'product',
                        name: 'product',
                        width: '25%',
                        render: function(data, type, row) {
                            return `
                        <div class="d-flex align-items-center gap-2">
                            <div class="bg-gray-900 avatar avatar-50 rounded">
                                ${row.product_image ?
                                    `<img src="${row.product_image}" alt="${row.product_name}" class="img-fluid avatar avatar-50">` :
                                    `<img src="{{ asset('img/vendorwebsite/product.png') }}" alt="${row.product_name}" class="img-fluid avatar avatar-50">`
                                }
                            </div>
                            <h6 class="mb-0 text-body small">${row.product_name}</h6>
                        </div>
                    `;
                        }
                    },
                    {
                        data: 'price',
                        name: 'price',
                        width: '15%',
                        render: function(data, type, row) {
                            if (row.discount_value > 0) {
                                return `
                            <div class="small">
                                  <span class="text-primary">${formatCurrencyvalue(row.discounted_price)}</span>
                                <del class="text-body">${formatCurrencyvalue(row.original_price)}</del>

                            </div>
                        `;
                            }
                            return `<div class="small">${formatCurrencyvalue(row.price)}</div>`;
                        }
                    },
                    {
                        data: 'discount_percentage',
                        name: 'discount_percentage',
                        width: '10%',
                        render: function(data, type, row) {
                            if (row.discount_value > 0) {
                                return `<div class="small text-success">${row.discount_value}%</div>`;
                            }
                            return `<div class="small">-</div>`;
                        }
                    },
                    // {
                    //     data: 'discount_amount',
                    //     name: 'discount_amount',
                    //     width: '10%',
                    //     render: function(data, type, row) {
                    //         if (row.discount_value > 0) {
                    //             const discountAmount = (row.original_price * row.discount_value /
                    //                 100).toFixed(2);
                    //             return `<div class="small text-success">-$${discountAmount}</div>`;
                    //         }
                    //         return `<div class="small">-</div>`;
                    //     }
                    // },
                    {
                        data: 'quantity',
                        name: 'quantity',
                        width: '15%',
                        render: function(data, type, row) {
                            const stockQty = row.product ? row.product.stock_qty : 0;
                            return `
                        <div class="btn-group iq-qty-btn" data-qty="btn" role="group">
                            <button type="button" class="btn btn-link border-0 iq-quantity-minus heading-color p-0" onclick="updateCartQuantity(${row.id}, 'decrease')">
                                <svg xmlns="http://www.w3.org/2000/svg" width="6" height="3" viewBox="0 0 6 3" fill="none">
                                    <path d="M5.22727 0.886364H0.136364V2.13636H5.22727V0.886364Z" fill="currentColor"></path>
                                </svg>
                            </button>
                            <input type="text" class="btn btn-link border-0 input-display" data-qty="input" pattern="^(0|[1-9][0-9]*)$" minlength="1" maxlength="2" value="${row.quantity}" title="Qty" onchange="updateCartQuantity(${row.id}, 'set', this.value)" max="${stockQty}" readonly>
                            <button type="button" class="btn btn-link border-0 iq-quantity-plus heading-color p-0" onclick="updateCartQuantity(${row.id}, 'increase')">
                                <svg xmlns="http://www.w3.org/2000/svg" width="9" height="8" viewBox="0 0 9 8" fill="none">
                                    <path d="M3.63636 7.70455H4.90909V4.59091H8.02273V3.31818H4.90909V0.204545H3.63636V3.31818H0.522727V4.59091H3.63636V7.70455Z" fill="currentColor"></path>
                                </svg>
                            </button>
                        </div>
                        <small class="text-success d-block mt-1 small">Available: ${stockQty}</small>
                    `;
                        }
                    },
                    {
                        data: 'subtotal',
                        name: 'subtotal',
                        width: '15%',
                        render: function(data, type, row) {
                            return `<div class="small">${formatCurrencyvalue(row.subtotal)}</div>`;
                        }
                    },
                    {
                        data: 'action',
                        name: 'action',
                        width: '10%',
                        orderable: false,
                        searchable: false,
                        render: function(data, type, row) {
                            return `
                        <button class="btn btn-link border-0 text-danger p-0" onclick="removeFromCartdata(${row.product_id})">
                            <i class="ph ph-trash-simple"></i>
                        </button>
                    `;
                        }
                    }
                ],
                order: [
                    [0, 'asc']
                ],
                paging: false,
                searching: false,
                lengthChange: false,
                info: false,
                language: {
                    emptyTable: `
                <tr>
                    <td colspan="7" class="text-center py-4">
                        <div class="empty-cart">
                           <img src="{{ asset('img/vendorwebsite/empty-cart.jpg') }}" alt="Empty Cart" class="img-fluid mb-3 avatar-150" onerror="this.onerror=null;this.src='https://cdn.jsdelivr.net/gh/edent/SuperTinyIcons/images/svg/shopping-cart.svg';">
                            <h5>Your cart is empty</h5>
                            <p class="text-body">{{ __('vendorwebsite.add_items_to_your_cart_to_proceed_with_checkout') }}</p>
                            <a href="{{ route('shop') }}" class="btn btn-primary mt-3">{{ __('vendorwebsite.continue_shopping') }}</a>
                        </div>
                    </td>
                </tr>
            `
                },
                drawCallback: function() {
                    updateCheckoutSummary();
                    if (this.api().data().count() === 0) {
                        $("#deliver_button").addClass("d-none");
                    } else {
                        $("#deliver_button").removeClass("d-none");
                    }
                },
                error: function(xhr, error, thrown) {
                    console.error('DataTable error:', error, thrown);
                    toastr.error('Failed to load cart data. Please refresh the page.');
                }
            });

            // Load countries on page load
            loadCountries();


            // Handle country change
            $('#country').on('change', function() {
                const countryId = $(this).val();
                if (countryId) {
                    loadStates(countryId);
                } else {
                    $('#state').html('<option value="">{{ __('vendorwebsite.select_state') }}</option>');
                    $('#city').html('<option value="">{{ __('vendorwebsite.city') }}</option>');
                }
            });

            // Handle state change
            $('#state').on('change', function() {
                const stateId = $(this).val();
                if (stateId) {
                    loadCities(stateId);
                } else {
                    $('#city').html('<option value="">{{ __('vendorwebsite.select_city') }}</option>');
                }
            });

            // Handle address selection
            $('input[name="address"]').on('change', function() {
                const addressId = $(this).val();
                // Save selected address to session for all payment methods
                fetch('/set-checkout-address', {
                    method: 'POST',
                    headers: {
                        'Content-Type': 'application/json',
                        'X-CSRF-TOKEN': '{{ csrf_token() }}'
                    },
                    body: JSON.stringify({
                        address_id: addressId
                    })
                });
                loadDeliveryZonesForAddress(addressId);
            });

            // Handle delivery zone selection
            $('input[name="delivery_zone"]').on('change', function() {
                updateCheckoutSummary();
            });

            // Always select the first available address if any
            if ($('input[name="address"]').length > 0) {
                $('input[name="address"]').first().prop('checked', true);
                loadDeliveryZonesForAddress($('input[name="address"]').first().val());

                // Set active state for the first address card
                $('.address-card').first().addClass('active');
            }

            // Handle modal show event for address modal
            $('#modalAddAddress').on('show.bs.modal', function() {
                $('#addressForm')[0].reset();
                loadCountries();
                $('#state').html('<option value="">{{ __('vendorwebsite.select_state') }}</option>');
                $('#city').html('<option value="">{{ __('vendorwebsite.select_city') }}</option>');
            });

            function toggleCheckoutSections() {
                // Check if the cart table is empty using DataTable API
                var isEmpty = false;
                if (checkoutTable && typeof checkoutTable.data === 'function') {
                    isEmpty = checkoutTable.data().count() === 0;
                } else {
                    // Fallback check
                    isEmpty = $("#checkout-table tbody tr td:contains('Your cart is empty')").length > 0;
                }
                
                if (isEmpty) {
                    $('#checkout-sections').hide();
                    $('.cart-summary').hide();
                    $('#checkout-table').hide();
                    $('#empty-cart-message').show();
                    $('.col-md-5.col-lg-3').hide(); // Hide right-side content
                    $('#deliver_button').addClass('d-none'); // Hide deliver button
                } else {
                    $('#checkout-sections').show();
                    $('.cart-summary').show();
                    $('#checkout-table').show();
                    $('#empty-cart-message').hide();
                    $('.col-md-5.col-lg-3').show(); // Show right-side content
                    $('#deliver_button').removeClass('d-none'); // Show deliver button
                }
            }
            // Call after table draw
            $('#checkout-table').on('draw.dt', toggleCheckoutSections);
            // Initial call
            toggleCheckoutSections();
        });



        var paymentDetailsTotalWithDelivery = null;

        function updateCheckoutSummary() {
            const selectedZoneId = $('input[name="delivery_zone"]:checked').val();
            $.get("{{ route('cart.summary') }}", {
                delivery_zone_id: selectedZoneId
            }, function(response) {
                if (response.status) {
                    let summaryHtml = '';
                    if (response.cart_items_count > 0) {
                        summaryHtml = `
                    <div class="payment-details-item  d-flex flex-wrap align-items-center justify-content-between mb-2  pb-2">
                        <div class="font-size-14">{{ __('vendorwebsite.subtotal') }}</div>
                        <h6 class="font-size-14 mb-0">${formatCurrencyvalue(response.subtotal)}</h6>
                    </div>
                    ${response.discount > 0 ? `
                                                                                                                                                                                                                                                            <div class="payment-details-item  d-flex flex-wrap align-items-center justify-content-between mb-2 pb-2">
                                                                                                                                                                                                                                                                <div class="font-size-14">{{ __('vendorwebsite.discount') }}</div>
                                                                                                                                                                                                                                                                <h6 class="font-size-14 mb-0 text-success">-${formatCurrencyvalue(response.discount)}</h6>
                                                                                                                                                                                                                                                            </div>
                                                                                                                                                                                                                                                        ` : ''}
                    ${response.tax > 0 ? `
                                                                                                                                                                                                                                                            <div class="d-flex justify-content-between  mb-1 price-item">
                                                                                                                                                                                                                                                                <span class="font-size-14">{{ __('vendorwebsite.tax') }}</span>
                                                                                                                                                                                                                                                                <div class="payment-details-item  d-flex flex-wrap align-items-center mb-2 pb-2 text-decoration-none cursor-pointer"
                                                                                                                                                                                                                                                                    data-bs-toggle="collapse"
                                                                                                                                                                                                                                                                    href="#taxDetailsCheckout"
                                                                                                                                                                                                                                                                    role="button"
                                                                                                                                                                                                                                                                    aria-expanded="false"
                                                                                                                                                                                                                                                                    aria-controls="taxDetailsCheckout">
                                                                                                                                                                                                                                                                    <div class="d-flex align-items-center justify-content-between font-size-14 gap-2 taxDetails">
                                                                                                                                                                                                                                                                        <i class="ph ph-caret-down rotate-icon tax2"></i>
                                                                                                                                                                                                                                                                    </div>
                                                                                                                                                                                                                                                                    <h6 class="font-size-14 mb-0 text-danger" id="tax-amount">${formatCurrencyvalue(response.tax)}</h6>
                                                                                                                                                                                                                                                                </div>
                                                                                                                                                                                                                                                            </div>
                                                                                                                                                                                                                                                            <div class="collapse mt-2 mb-2" id="taxDetailsCheckout">
                                                                                                                                                                                                                                                                <div class="text-calculate card py-2 px-3" id="tax-details">
                                                                                                                                                                                                                                                                    ${(response.tax_breakdown && response.tax_breakdown.length > 0) ? response.tax_breakdown.map(tax => `
                                    <div class="d-flex justify-content-between align-items-center mb-1">
                                        <span class="font-size-12">${tax.title}${tax.type === 'percent' ? ' (' + tax.value + '%)' : ''}</span>
                                        <span class="font-size-12 text-danger fw-medium">${formatCurrencyvalue(tax.amount)}</span>
                                    </div>
                                `).join('') : `
                                    <div class="d-flex justify-content-between align-items-center">
                                        <span class="font-size-12">{{ __('vendorwebsite.tax') }}</span>
                                        <span class="font-size-12 text-danger fw-medium">0</span>
                                    </div>
                                `}
                                                                                                                                                                                                                                                                </div>
                                                                                                                                                                                                                                                            </div>
                                                                                                                                                                                                                                                        ` : ''}
                   ${response.delivery_charge > 0 ? `
                                                                                                                                                                                                                                    <div class="payment-details-item border-bottom d-flex flex-wrap align-items-center justify-content-between mb-3 pb-3">
                                                                                                                                                                                                                                        <div class="font-size-14">{{ __('vendorwebsite.delivery_charges') }}</div>
                                                                                                                                                                                                                                        <h6 class="font-size-14 mb-0">${formatCurrencyvalue(response.delivery_charge)}</h6>
                                                                                                                                                                                                                                    </div>
                                                                                                                                                                                                                                ` : ''}
                    <div class="payment-details-item d-flex flex-wrap align-items-center justify-content-between mb-4">
                        <div class="font-size-14 fw-bold">{{ __('vendorwebsite.total_amount') }}</div>
                        <h6 class="font-size-14 mb-0 text-primary fw-bold">${formatCurrencyvalue(response.total_with_delivery)}</h6>
                    </div>
                `;
                    }

                    $('#checkout-summary').html(summaryHtml);

                    // Handle tax collapse toggle for dynamic content
                    $('[href="#taxDetailsCheckout"]').off('click').on('click', function(e) {
                        e.preventDefault();
                        const taxIcon = $(this).find('.tax2');
                        const isExpanded = $('#taxDetailsCheckout').hasClass('show');
                        if (isExpanded) {
                            taxIcon.css('transform', 'rotate(0deg)');
                        } else {
                            taxIcon.css('transform', 'rotate(180deg)');
                        }
                    });

                    paymentDetailsTotalWithDelivery = response.total_with_delivery;
                    updateOrderSummaryBox();
                }
            });
        }


        function loadCountries() {
            $.get("{{ route('frontend.address.get-countries') }}", function(data) {
                let options = '<option value="">{{ __('vendorwebsite.select_country') }}</option>';
                data.forEach(function(country) {
                    options += `<option value="${country.id}">${country.name}</option>`;
                });
                $('#country').html(options);
            }).fail(function(xhr, status, error) {
                console.error('Failed to load countries:', error);
                $('#country').html('<option value="">Failed to load countries</option>');
            });
        }

        function loadStates(countryId) {
            $.get(`{{ route('frontend.address.get-states') }}?country_id=${countryId}`, function(data) {
                let options = '<option value="">{{ __('vendorwebsite.select_state') }}</option>';
                data.forEach(function(state) {
                    options += `<option value="${state.id}">${state.name}</option>`;
                });
                $('#state').html(options);
                $('#city').html('<option value="">{{ __('vendorwebsite.select_city') }}</option>');
            }).fail(function(xhr, status, error) {
                console.error('Failed to load states:', error);
                $('#state').html('<option value="">Failed to load states</option>');
                $('#city').html('<option value="">{{ __('vendorwebsite.select_city') }}</option>');
            });
        }

        function loadCities(stateId) {
            $.get(`{{ route('frontend.address.get-cities') }}?state_id=${stateId}`, function(data) {
                let options = '<option value="">{{ __('vendorwebsite.select_city') }}</option>';
                data.forEach(function(city) {
                    options += `<option value="${city.id}">${city.name}</option>`;
                });
                $('#city').html(options);
            }).fail(function(xhr, status, error) {
                console.error('Failed to load cities:', error);
                $('#city').html('<option value="">Failed to load cities</option>');
            });
        }

        function updateCartQuantity(cartItemId, action, value = null) {
            let qty = value;
            if (!value) {
                const input = $(`input[data-qty="input"]`).filter(function() {
                    return $(this).closest('tr').find('button[onclick*="' + cartItemId + '"]').length > 0;
                });
                qty = parseInt(input.val());
                const maxQty = parseInt(input.attr('max'));

                if (action === 'increase') {
                    if (qty >= maxQty) {
                        toastr.warning(`Only ${maxQty} items available in stock`);
                        return;
                    }
                    qty++;
                } else if (action === 'decrease') {
                    qty = Math.max(1, qty - 1);
                }
            }

            $.ajax({
                url: "{{ route('cart.update') }}",
                type: 'POST',
                data: {
                    cart_item_id: cartItemId,
                    qty: qty,
                    _token: '{{ csrf_token() }}'
                },
                success: function(response) {

                    if (response.status) {

                        $('#checkout-table').DataTable().ajax.reload();
                        updateOrderSummaryBox();
                        toastr.success('Cart updated successfully');
                    } else {
                        toastr.error(response.message);
                    }
                },
                error: function() {
                    toastr.error('Failed to update quantity. Please try again.');
                }
            });
        }

        function removeFromCartdata(productId) {


            $.ajax({
                url: "{{ route('cart.remove') }}",
                type: 'POST',
                data: {
                    product_id: productId,
                    _token: '{{ csrf_token() }}'
                },
                success: function(response) {
                    if (response.status) {

                        checkoutTable.ajax.reload(null, false);
                        toastr.success('Product removed from cart successfully');
                        updateOrderSummaryBox();
                    } else {
                        checkoutTable.ajax.reload(null, false);
                        toastr.error(response.message);
                        updateOrderSummaryBox();
                    }
                },
                error: function() {
                    toastr.error('Failed to remove item from cart. Please try again.');
                }
            });
        }

        function clearAddressForm() {
            $('#addressForm')[0].reset();
            $('#address_id').val('');
            $('#state').html('<option value="">{{ __('vendorwebsite.select_state') }}</option>');
            $('#city').html('<option value="">{{ __('vendorwebsite.select_city') }}</option>');
        }

        // Utility function to load dropdown data
        function loadDropdownData(url, targetSelect, placeholder, selectedValue = null) {
            targetSelect.innerHTML = `<option value="" disabled selected>Loading...</option>`;

            fetch(url)
                .then(response => {
                    if (!response.ok) {
                        throw new Error(`HTTP error! status: ${response.status}`);
                    }
                    return response.json();
                })
                .then(data => {

                    let options =
                        `<option value="" disabled ${!selectedValue ? 'selected' : ''}>${placeholder}</option>`;
                    data.forEach(item => {
                        options +=
                            `<option value="${item.id}" ${selectedValue == item.id ? 'selected' : ''}>${item.name}</option>`;
                    });
                    targetSelect.innerHTML = options;
                })
                .catch((error) => {
                    console.error(`Failed to load ${placeholder}:`, error);
                    targetSelect.innerHTML =
                        `<option value="" disabled selected>Failed to load ${placeholder}</option>`;
                });
        }

        function editAddress(addressId) {
            // Update modal title to "Edit Address"
            $('#modalAddAddressLabel').text('Edit Address');

            // Load address data and populate form
            $.get("{{ route('frontend.address.get', ['id' => ':id']) }}".replace(':id', addressId), function(response) {
                if (response.status) {
                    $('#address_id').val(response.address.id);
                    $('#first_name').val(response.address.first_name);
                    $('#last_name').val(response.address.last_name);
                    $('#email').val(response.address.email);
                    $('#mobileInput').val(response.address.contact_number);
                    $('#address_line_1').val(response.address.address_line_1);
                    $('#address_line_2').val(response.address.address_line_2 || '');
                    $('#pin_code').val(response.address.postal_code);

                    // Load countries and set the selected country
                    loadDropdownData("{{ route('frontend.address.get-countries') }}", $('#country')[0],
                        'Select Country', response.address.country);

                    // Load states if country is selected
                    if (response.address.country) {
                        loadDropdownData("{{ route('frontend.address.get-states') }}?country_id=" + response
                            .address.country, $('#state')[0], 'Select State', response.address.state);

                        // Load cities if state is selected
                        if (response.address.state) {
                            loadDropdownData("{{ route('frontend.address.get-cities') }}?state_id=" + response
                                .address.state, $('#city')[0], 'Select City', response.address.city);
                        }
                    }

                    $('#set_as_primary').prop('checked', response.address.is_primary == 1);
                }
            });
        }

        function submitAddress() {
            const form = $('#addressForm');
            if (form[0].checkValidity()) {
                const formData = new FormData(form[0]);
                formData.append('_token', '{{ csrf_token() }}');

                $.ajax({
                    url: "{{ route('frontend.address.store') }}",
                    type: 'POST',
                    data: formData,
                    processData: false,
                    contentType: false,
                    success: function(response) {
                        if (response.status) {
                            location.reload();
                        } else {
                            toastr.error(response.message);
                        }
                    },
                    error: function() {
                        toastr.error('Failed to save address. Please try again.');
                    }
                });
            } else {
                form[0].reportValidity();
            }
        }

        function formatCurrencyvalue(value) {
            value = parseFloat(value);
            if (window.currencyFormat !== undefined) {
                return window.currencyFormat(value);
            }
            return value.toFixed(2);
        }


        function placeOrder() {
            const addressId = $('input[name="address"]:checked').val();

            const deliveryZoneId = $('input[name="delivery_zone"]:checked').val();
            const paymentMethod = $('input[name="payment_method"]:checked').val();
            console.log(paymentMethod);
            
            // Check if there are any cart items
            if (checkoutTable && typeof checkoutTable.data === 'function' && checkoutTable.data().count() === 0) {
                toastr.error('Your cart is empty. Please add items to your cart before placing an order.');
                return;
            }
            
            if (!addressId) {
                toastr.error('Please select a delivery address');
                return;
            }

            if (!deliveryZoneId) {
                toastr.error('Please select a delivery zone');
                return;
            }

            if (!paymentMethod) {
                toastr.error('Please select a payment method');
                return;
            }

            // Show loading state
            const button = $('button[onclick="placeOrder()"]');
            const originalText = button.text();
            button.prop('disabled', true).text('Processing...');

            if (paymentMethod === 'cash') {
                // Place order as before
                $.ajax({
                    url: "{{ url('api/place-order') }}",
                    type: 'POST',
                    data: {
                        shipping_address_id: addressId,
                        billing_address_id: addressId,
                        chosen_logistic_zone_id: deliveryZoneId,
                        payment_method: paymentMethod,
                        shipping_delivery_type: 'regular',
                        payment_status: 'unpaid',
                        _token: '{{ csrf_token() }}'
                    },
                    success: function(response) {
                        if (response.status) {

                            Swal.fire({
                                icon: 'success',
                                title: '{{ __('vendorwebsite.order_submitted') }}',
                                html: `
                            <h5>{{ __('vendorwebsite.thank_you_for_your_order') }}</h5>
                            <p>{{ __('vendorwebsite.your_order_has_been_successfully_booked') }}</p>
                            <div>
                                ${(response.product.id ? `<span  class="mb-2 d-flex align-items-center justify-content-center gap-2"><span class="h6 m-0 font-size-14">{{ __('vendorwebsite.order_id') }}</span>: <span class="text-primary fw-bold font-size-14">#${response.product.id}</span></span>` : '')}
                                <span class="mb-2 d-flex align-items-center justify-content-center gap-2"><span class="h6 m-0 font-size-14">{{ __('vendorwebsite.payment_method') }}</span>: <span class="h6 m-0 fw-bold font-size-14">${paymentMethod.charAt(0).toUpperCase() + paymentMethod.slice(1)}</span></span>
                                <span class="mb-2 d-flex align-items-center justify-content-center gap-2"><span class="h6 m-0 font-size-14">{{ __('vendorwebsite.total_amount') }}</span>: <span class="h6 m-0 fw-bold  font-size-14">${formatCurrencyvalue(response.product.total_admin_earnings)}</span></span>
                            </div>
                            <div class="d-flex align-items-center gap-2 flex-md-nowrap flex-wrap justify-content-center mt-4">
                            <button id="swal-close-btn" class="btn btn-primary">{{ __('vendorwebsite.close') }}</button>
                            <button id="btn-goto-orders" class="btn btn-secondary">{{ __('vendorwebsite.go_to_orders') }}</button></div>
                        `,
                                showConfirmButton: false,
                                showCancelButton: false,
                                allowOutsideClick: false,
                                didOpen: () => {
                                    document.getElementById('swal-close-btn').onclick = () => window
                                        .location.href = "{{ route('vendor.index', ['vendor_slug' => request()->route('vendor_slug')]) }}";
                                    document.getElementById('btn-goto-orders').onclick = () =>
                                        window.location.href = "{{ route('myorder') }}";
                                }
                            });
                        } else {
                            toastr.error(response.message);
                        }
                    },
                    error: function(xhr) {
                        let errorMessage = 'Failed to place order. Please try again.';
                        if (xhr.responseJSON && xhr.responseJSON.message) {
                            errorMessage = xhr.responseJSON.message;
                        }
                        toastr.error(errorMessage);
                    },
                    complete: function() {
                        // Reset button state
                        button.prop('disabled', false).text(originalText);
                    }
                });
            } else if (paymentMethod === 'wallet') {
                // Place order as before
                $.ajax({
                    url: "{{ url('api/place-order') }}",
                    type: 'POST',
                    data: {
                        shipping_address_id: addressId,
                        billing_address_id: addressId,
                        chosen_logistic_zone_id: deliveryZoneId,
                        payment_method: paymentMethod,
                        shipping_delivery_type: 'regular',
                        location_id: 0,
                        payment_status: 'paid',
                        _token: '{{ csrf_token() }}'
                    },
                    success: function(response) {
                        if (response.status) {

                            Swal.fire({
                                icon: 'success',
                                title: '{{ __('vendorwebsite.order_submitted') }}',
                                html: `
                            <h5>{{ __('vendorwebsite.thank_you_for_your_order') }}</h5>
                            <p>{{ __('vendorwebsite.your_order_has_been_successfully_booked') }}</p>
                            <div>
                                ${(response.product.id ? `<span  class="mb-2 d-flex align-items-center justify-content-center gap-2"><span class="h6 m-0 font-size-14">{{ __('vendorwebsite.order_id') }}</span>: <span class="text-primary fw-bold font-size-14">#${response.product.id}</span></span>` : '')}
                                <span class="mb-2 d-flex align-items-center justify-content-center gap-2"><span class="h6 m-0 font-size-14">{{ __('vendorwebsite.payment_method') }}</span>: <span class="h6 m-0 fw-bold font-size-14">${paymentMethod.charAt(0).toUpperCase() + paymentMethod.slice(1)}</span></span>
                                <span class="mb-2 d-flex align-items-center justify-content-center gap-2"><span class="h6 m-0 font-size-14">{{ __('vendorwebsite.total_amount') }}</span>: <span class="h6 m-0 fw-bold  font-size-14">${formatCurrencyvalue(response.product.total_admin_earnings)}</span></span>
                            </div>
                            <div class="d-flex align-items-center gap-2 flex-md-nowrap flex-wrap justify-content-center mt-4">
                            <button id="swal-close-btn" class="btn btn-primary">{{ __('vendorwebsite.close') }}</button>
                            <button id="btn-goto-orders" class="btn btn-secondary">{{ __('vendorwebsite.go_to_orders') }}</button></div>
                        `,
                                showConfirmButton: false,
                                showCancelButton: false,
                                allowOutsideClick: false,
                                didOpen: () => {
                                    document.getElementById('swal-close-btn').onclick = () => window
                                        .location.href = "{{ route('vendor.index', ['vendor_slug' => request()->route('vendor_slug')]) }}";
                                    document.getElementById('btn-goto-orders').onclick = () =>
                                        window.location.href = "{{ route('myorder') }}";
                                }
                            });
                        } else {
                            toastr.error(response.message);
                        }
                    },
                    error: function(xhr) {
                        let errorMessage = 'Failed to place order. Please try again.';
                        if (xhr.responseJSON && xhr.responseJSON.message) {
                            errorMessage = xhr.responseJSON.message;
                        }
                        toastr.error(errorMessage);
                    },
                    complete: function() {
                        // Reset button state
                        button.prop('disabled', false).text(originalText);
                    }
                });
            } else if (paymentMethod === 'stripe') {
                // Disable the button and show spinner (optional)
                button.prop('disabled', true).html(
                    '<span class="spinner-border spinner-border-sm" role="status" aria-hidden="true"></span> Processing...'
                );
                // Get the total amount from the payment summary DOM

                fetch("{{ route('payment.process') }}", {

                        method: 'POST',
                        headers: {
                            'Content-Type': 'application/json',
                            'Accept': 'application/json',
                            'X-CSRF-TOKEN': '{{ csrf_token() }}'
                        },
                        body: JSON.stringify({
                            shipping_address_id: addressId,
                            billing_address_id: addressId,
                            chosen_logistic_zone_id: deliveryZoneId,
                            payment_method: paymentMethod,
                            shipping_delivery_type: 'regular',
                            payment_status: 'unpaid',

                            _token: '{{ csrf_token() }}'
                        })
                    })
                    .then(res => res.json())
                    .then(response => {
                        if (response.success && (response.redirect || response.session_url)) {
                            window.location.href = response.redirect || response.session_url;
                        } else {
                            Swal.fire({
                                icon: 'error',
                                title: 'Stripe Error',
                                text: response.message ||
                                    '{{ __('vendorwebsite.failed_to_initiate_stripe_payment') }}',
                                confirmButtonText: '{{ __('vendorwebsite.ok') }}',
                                customClass: {
                                    confirmButton: 'btn btn-primary'
                                },
                                buttonsStyling: false,
                            });
                        }
                    })
                    .catch(err => Swal.fire({
                        icon: 'error',
                        title: 'Stripe Error',
                        text: err.message || '{{ __('vendorwebsite.failed_to_initiate_stripe_payment') }}',

                        confirmButtonText: '{{ __('vendorwebsite.ok') }}',
                        customClass: {
                            confirmButton: 'btn btn-primary'
                        },
                        buttonsStyling: false,
                    }))
                    .finally(function() {
                        button.prop('disabled', false).text(originalText);
                    });
            } else if (paymentMethod === 'razorpay') {

                button.prop('disabled', true).html(
                    '<span class="spinner-border spinner-border-sm" role="status" aria-hidden="true"></span> Processing...'
                );

                fetch("{{ route('payment.process') }}", {
                        method: 'POST',
                        headers: {
                            'Content-Type': 'application/json',
                            'Accept': 'application/json',
                            'X-CSRF-TOKEN': '{{ csrf_token() }}'
                        },
                        body: JSON.stringify({

                            shipping_address_id: addressId,
                            billing_address_id: addressId,
                            chosen_logistic_zone_id: deliveryZoneId,
                            payment_method: paymentMethod,
                            shipping_delivery_type: 'regular',
                            payment_status: 'unpaid',

                        })
                    })
                    .then(res => res.json())
                    .then(response => {


                        if (response.order_id && response.key) {


                            var options = {
                                key: response.key,
                                amount: (response.amount * 100).toFixed(2), // Always use backend value in paise
                                currency: response.currency || 'INR',
                                name: '{{ config('app.name') }}',
                                description: 'Order Payment',
                                order_id: response.order_id,
                                handler: function(paymentResponse) {

                                    if (paymentResponse.razorpay_payment_id) {
                                        // Get checkout data to send with payment
                                        const checkoutData = {
                                            razorpay_payment_id: paymentResponse.razorpay_payment_id,
                                            razorpay_order_id: options.order_id,
                                            shipping_address_id: $('input[name="address"]:checked').val(),
                                            billing_address_id: $('input[name="address"]:checked')
                                                .val(), // Same as shipping for now
                                            chosen_logistic_zone_id: $(
                                                'input[name="delivery_zone"]:checked').val(),
                                            payment_method: 'razorpay',
                                            shipping_delivery_type: 'standard', // Default value since field doesn't exist
                                            payment_status: 'completed',
                                            amount: options.amount / 100, // Convert from paise to rupees
                                            _token: '{{ csrf_token() }}'
                                        };


                                        // Call backend to store transaction
                                        $.post("{{ route('product.razorpay.success') }}", checkoutData,
                                            function(response) {
                                                console.log('Payment success response:', response);
                                                clearCheckoutData();
                                                Swal.fire({
                                                    icon: 'success',
                                                    title: '{{ __('vendorwebsite.payment_successfull') }}',
                                                    text: '{{ __('vendorwebsite.your_payment_was_successfull_thank-you') }}',
                                                    confirmButtonText: '{{ __('vendorwebsite.ok') }}',
                                                    customClass: {
                                                        confirmButton: 'btn btn-primary'
                                                    },
                                                    buttonsStyling: false,
                                                }).then(() => {
                                                    window.location.href = response.redirect ||
                                                        "{{ route('vendor.index', ['vendor_slug' => request()->route('vendor_slug')]) }}";
                                                });
                                            }).fail(function(xhr) {
                                            console.error('Payment processing failed:', xhr
                                                .responseJSON);
                                            let errorMessage =
                                                '{{ __('vendorwebsite.payment_succeeded_but_server_did_not_record_it_please_contact_support') }}';

                                            if (xhr.responseJSON && xhr.responseJSON.error) {
                                                errorMessage = xhr.responseJSON.error;
                                            }

                                            Swal.fire({
                                                icon: 'error',
                                                title: '{{ __('vendorwebsite.server_error') }}',
                                                text: errorMessage,
                                                confirmButtonText: '{{ __('vendorwebsite.ok') }}',
                                                customClass: {
                                                    confirmButton: 'btn btn-primary'
                                                },
                                                buttonsStyling: false,
                                            });
                                        });
                                    } else {
                                        Swal.fire({
                                            icon: 'error',
                                            title: '{{ __('vendorwebsite.payment_failed') }}',
                                            text: '{{ __('vendorwebsite.payment_was_not_completed_please_try_again') }}',
                                            confirmButtonText: '{{ __('vendorwebsite.ok') }}',
                                            customClass: {
                                                confirmButton: 'btn btn-primary'
                                            },
                                            buttonsStyling: false,
                                        });
                                    }
                                },
                                modal: {
                                    ondismiss: function() {
                                        Swal.fire({
                                            icon: 'info',
                                            title: '{{ __('vendorwebsite.payment_cancelled') }}',
                                            text: '{{ __('vendorwebsite.Payment_was_cancelled_Please_try_again') }}',
                                            confirmButtonText: '{{ __('vendorwebsite.ok') }}',
                                            customClass: {
                                                confirmButton: 'btn btn-primary'
                                            },
                                            buttonsStyling: false,
                                        });
                                    }
                                },
                                prefill: response.prefill || {},
                                theme: {
                                    color: '#528FF0'
                                }
                            };
                            new Razorpay(options).open();
                        } else {
                            Swal.fire({
                                icon: 'error',
                                title: 'Razorpay Error',
                                text: response.message || 'Failed to initiate Razorpay payment.',
                                confirmButtonText: '{{ __('vendorwebsite.ok') }}',
                                customClass: {
                                    confirmButton: 'btn btn-primary'
                                },
                                buttonsStyling: false,
                            });
                        }
                    })
                    .catch(err => Swal.fire({
                        icon: 'error',
                        title: 'Razorpay Error',
                        text: err.message || 'Failed to initiate Razorpay payment.',
                        confirmButtonText: '{{ __('vendorwebsite.ok') }}',
                        customClass: {
                            confirmButton: 'btn btn-primary'
                        },
                        buttonsStyling: false,
                    }))
                    .finally(function() {
                        button.prop('disabled', false).text(originalText);
                    });
            } else if (paymentMethod === 'paystack') {
                // Paystack Payment Processing
                button.prop('disabled', true).html(
                    '<span class="spinner-border spinner-border-sm" role="status" aria-hidden="true"></span> Processing...'
                );

                fetch("{{ route('payment.process') }}", {
                        method: 'POST',
                        headers: {
                            'Content-Type': 'application/json',
                            'Accept': 'application/json',
                            'X-CSRF-TOKEN': '{{ csrf_token() }}'
                        },
                        body: JSON.stringify({
                            shipping_address_id: addressId,
                            billing_address_id: addressId,
                            chosen_logistic_zone_id: deliveryZoneId,
                            payment_method: paymentMethod,
                            shipping_delivery_type: 'regular',
                            payment_status: 'unpaid',
                            _token: '{{ csrf_token() }}'
                        })
                    })
                    .then(res => res.json())
                    .then(response => {
                        console.log('Paystack process response:', response);
                        if (response.success && response.reference && response.public_key) {
                            console.log('Initializing Paystack with:', {
                                key: response.public_key,
                                email: response.email,
                                amount: response.amount * 100,
                                currency: response.currency,
                                ref: response.reference
                            });
                            // Initialize Paystack payment
                            const handler = PaystackPop.setup({
                                key: response.public_key,
                                email: response.email,
                                amount: response.amount * 100, // Paystack expects amount in kobo (cents)
                                currency: response.currency || 'NGN',
                                ref: response.reference,
                                metadata: {
                                    order_id: response.order_id,
                                    shipping_address_id: addressId,
                                    billing_address_id: addressId,
                                    chosen_logistic_zone_id: deliveryZoneId
                                },
                                callback: function(response) {
                                    // Payment successful
                                    if (response.status === 'success') {
                                        // Verify payment on server
                                        fetch("{{ route('product.paystack.success') }}", {
                                                method: 'POST',
                                                headers: {
                                                    'Content-Type': 'application/json',
                                                    'Accept': 'application/json',
                                                    'X-CSRF-TOKEN': '{{ csrf_token() }}'
                                                },
                                                body: JSON.stringify({
                                                    reference: response.reference,
                                                    transaction_id: response.trans,
                                                    _token: '{{ csrf_token() }}'
                                                })
                                            })
                                            .then(res => res.json())
                                            .then(verifyResponse => {
                                                console.log('Paystack verification response:',
                                                    verifyResponse);
                                                if (verifyResponse.status) {
                                                    // Show success message
                                                    Swal.fire({
                                                        icon: 'success',
                                                        title: '{{ __('vendorwebsite.order_submitted') }}',
                                                        html: `
                                                            <h5>{{ __('vendorwebsite.thank_you_for_your_order') }}</h5>
                                                            <p>{{ __('vendorwebsite.your_order_has_been_successfully_placed') }}</p>
                                                            <div>
                                                                ${(verifyResponse.order && verifyResponse.order.original && verifyResponse.order.original.product && verifyResponse.order.original.product.id ? `<span class="mb-2 d-flex align-items-center justify-content-center gap-2"><span class="h6 m-0 font-size-14">{{ __('vendorwebsite.order_id') }}</span>: <span class="text-primary fw-bold font-size-14">#${verifyResponse.order.original.product.id}</span></span>` : '')}
                                                                <span class="mb-2 d-flex align-items-center justify-content-center gap-2"><span class="h6 m-0 font-size-14">{{ __('vendorwebsite.payment_method') }}</span>: <span class="h6 m-0 fw-bold font-size-14">Paystack</span></span>
                                                                <span class="mb-2 d-flex align-items-center justify-content-center gap-2"><span class="h6 m-0 font-size-14">{{ __('vendorwebsite.total_amount') }}</span>: <span class="h6 m-0 fw-bold font-size-14">${formatCurrencyvalue(verifyResponse.order && verifyResponse.order.original && verifyResponse.order.original.product ? verifyResponse.order.original.product.total_admin_earnings : (response.amount / 100))}</span></span>
                                                            </div>
                                                            <div class="d-flex align-items-center gap-2 flex-md-nowrap flex-wrap justify-content-center mt-4">
                                                                <button id="swal-close-btn" class="btn btn-primary">{{ __('vendorwebsite.close') }}</button>
                                                                <button id="btn-goto-orders" class="btn btn-secondary">{{ __('vendorwebsite.go_to_orders') }}</button>
                                                            </div>
                                                        `,
                                                        showConfirmButton: false,
                                                        showCancelButton: false,
                                                        allowOutsideClick: false,
                                                        didOpen: () => {
                                                            document.getElementById(
                                                                    'swal-close-btn')
                                                                .onclick = () => window
                                                                .location.href =
                                                                "{{ route('vendor.index', ['vendor_slug' => request()->route('vendor_slug')]) }}";
                                                            document.getElementById(
                                                                    'btn-goto-orders')
                                                                .onclick = () => window
                                                                .location.href =
                                                                "{{ route('myorder') }}";
                                                        }
                                                    });
                                                } else {
                                                    Swal.fire({
                                                        icon: 'error',
                                                        title: '{{ __('vendorwebsite.payment_verification_failed') }}',
                                                        text: verifyResponse.message ||
                                                            '{{ __('vendorwebsite.payment_could_not_be_verified') }}',
                                                        confirmButtonText: '{{ __('vendorwebsite.ok') }}',
                                                        customClass: {
                                                            confirmButton: 'btn btn-primary'
                                                        },
                                                        buttonsStyling: false,
                                                    });
                                                }
                                            })
                                            .catch(err => {
                                                Swal.fire({
                                                    icon: 'error',
                                                    title: '{{ __('vendorwebsite.verification_error') }}',
                                                    text: '{{ __('vendorwebsite.payment_verification_failed') }}',
                                                    confirmButtonText: '{{ __('vendorwebsite.ok') }}',
                                                    customClass: {
                                                        confirmButton: 'btn btn-primary'
                                                    },
                                                    buttonsStyling: false,
                                                });
                                            });
                                    } else {
                                        Swal.fire({
                                            icon: 'error',
                                            title: '{{ __('vendorwebsite.payment_failed') }}',
                                            text: '{{ __('vendorwebsite.payment_was_not_completed_please_try_again') }}',
                                            confirmButtonText: '{{ __('vendorwebsite.ok') }}',
                                            customClass: {
                                                confirmButton: 'btn btn-primary'
                                            },
                                            buttonsStyling: false,
                                        });
                                    }
                                },
                                onClose: function() {
                                    // Payment cancelled
                                    Swal.fire({
                                        icon: 'info',
                                        title: '{{ __('vendorwebsite.payment_cancelled') }}',
                                        text: '{{ __('vendorwebsite.Payment_was_cancelled_Please_try_again') }}',
                                        confirmButtonText: '{{ __('vendorwebsite.ok') }}',
                                        customClass: {
                                            confirmButton: 'btn btn-primary'
                                        },
                                        buttonsStyling: false,
                                    });
                                }
                            });

                            // Open Paystack payment modal
                            handler.openIframe();
                        } else {
                            console.log('Paystack initialization failed. Response missing required fields:', response);
                            Swal.fire({
                                icon: 'error',
                                title: 'Paystack Error',
                                text: response.message ||
                                    '{{ __('vendorwebsite.failed_to_initiate_paystack_payment') }}',
                                confirmButtonText: '{{ __('vendorwebsite.ok') }}',
                                customClass: {
                                    confirmButton: 'btn btn-primary'
                                },
                                buttonsStyling: false,
                            });
                        }
                    })
                    .catch(err => {
                        console.error('Paystack process request failed:', err);
                        Swal.fire({
                            icon: 'error',
                            title: 'Paystack Error',
                            text: err.message ||
                                '{{ __('vendorwebsite.failed_to_initiate_paystack_payment') }}',
                            confirmButtonText: '{{ __('vendorwebsite.ok') }}',
                            customClass: {
                                confirmButton: 'btn btn-primary'
                            },
                            buttonsStyling: false,
                        });
                    })
                    .finally(function() {
                        button.prop('disabled', false).text(originalText);
                    });
            } else if (paymentMethod === 'flutterwave') {
                // Flutterwave Payment Processing
                button.prop('disabled', true).html(
                    '<span class="spinner-border spinner-border-sm" role="status" aria-hidden="true"></span> Processing...'
                );

                fetch("{{ route('payment.process') }}", {
                        method: 'POST',
                        headers: {
                            'Content-Type': 'application/json',
                            'Accept': 'application/json',
                            'X-CSRF-TOKEN': '{{ csrf_token() }}'
                        },
                        body: JSON.stringify({
                            shipping_address_id: addressId,
                            billing_address_id: addressId,
                            chosen_logistic_zone_id: deliveryZoneId,
                            payment_method: paymentMethod,
                            shipping_delivery_type: 'regular',
                            payment_status: 'unpaid',
                            _token: '{{ csrf_token() }}'
                        })
                    })
                    .then(res => res.json())
                    .then(response => {
                        if (response.success && response.tx_ref && response.public_key) {
                            // Initialize Flutterwave payment
                            FlutterwaveCheckout({
                                public_key: response.public_key,
                                tx_ref: response.tx_ref,
                                amount: response.amount,
                                currency: response.currency || 'NGN',
                                country: response.country || 'NG',
                                payment_options: response.payment_options || 'card',
                                customer: response.customer,
                                customizations: response.customizations,
                                meta: response.meta,
                                callback: function(data) {
                                    if (data.status === 'successful') {
                                        // Verify payment on server
                                        fetch("{{ route('product.flutterwave.success') }}", {
                                                method: 'POST',
                                                headers: {
                                                    'Content-Type': 'application/json',
                                                    'Accept': 'application/json',
                                                    'X-CSRF-TOKEN': '{{ csrf_token() }}'
                                                },
                                                body: JSON.stringify({
                                                    tx_ref: data.tx_ref,
                                                    transaction_id: data.transaction_id,
                                                    _token: '{{ csrf_token() }}'
                                                })
                                            })
                                            .then(res => res.json())
                                            .then(verifyResponse => {
                                                if (verifyResponse.status) {
                                                    // Show success message and redirect automatically
                                                    Swal.fire({
                                                        icon: 'success',
                                                        title: '{{ __('vendorwebsite.order_submitted') }}',
                                                        html: `
                                                            <h5>{{ __('vendorwebsite.thank_you_for_your_order') }}</h5>
                                                            <p>{{ __('vendorwebsite.your_order_has_been_successfully_placed') }}</p>
                                                            <div class="mt-3">
                                                                <span class="mb-2 d-flex align-items-center justify-content-center gap-2"><span class="h6 m-0 font-size-14">{{ __('vendorwebsite.payment_method') }}</span>: <span class="h6 m-0 fw-bold font-size-14">Flutterwave</span></span>
                                                            </div>
                                                            <div class="mt-3">
                                                                <p class="text-muted">Redirecting to orders page...</p>
                                                            </div>
                                                        `,
                                                        showConfirmButton: false,
                                                        showCancelButton: false,
                                                        allowOutsideClick: false,
                                                        timer: 2000
                                                    }).then(() => {
                                                        // Redirect to orders page
                                                        window.location.href = verifyResponse
                                                            .redirect;
                                                    });
                                                } else {
                                                    Swal.fire({
                                                        icon: 'error',
                                                        title: '{{ __('vendorwebsite.payment_verification_failed') }}',
                                                        text: verifyResponse.message ||
                                                            '{{ __('vendorwebsite.payment_could_not_be_verified') }}',
                                                        confirmButtonText: '{{ __('vendorwebsite.ok') }}',
                                                        customClass: {
                                                            confirmButton: 'btn btn-primary'
                                                        },
                                                        buttonsStyling: false,
                                                    });
                                                }
                                            })
                                            .catch(err => {
                                                Swal.fire({
                                                    icon: 'error',
                                                    title: '{{ __('vendorwebsite.verification_error') }}',
                                                    text: '{{ __('vendorwebsite.payment_verification_failed') }}',
                                                    confirmButtonText: '{{ __('vendorwebsite.ok') }}',
                                                    customClass: {
                                                        confirmButton: 'btn btn-primary'
                                                    },
                                                    buttonsStyling: false,
                                                });
                                            });
                                    } else {
                                        Swal.fire({
                                            icon: 'error',
                                            title: '{{ __('vendorwebsite.payment_failed') }}',
                                            text: '{{ __('vendorwebsite.payment_was_not_completed_please_try_again') }}',
                                            confirmButtonText: '{{ __('vendorwebsite.ok') }}',
                                            customClass: {
                                                confirmButton: 'btn btn-primary'
                                            },
                                            buttonsStyling: false,
                                        });
                                    }
                                },
                                onclose: function() {
                                    Swal.fire({
                                        icon: 'warning',
                                        title: '{{ __('vendorwebsite.payment_cancelled') }}',
                                        text: '{{ __('vendorwebsite.Payment_was_cancelled_Please_try_again') }}',
                                        confirmButtonText: '{{ __('vendorwebsite.ok') }}',
                                        customClass: {
                                            confirmButton: 'btn btn-primary'
                                        },
                                        buttonsStyling: false,
                                    });
                                }
                            });
                        } else {
                            Swal.fire({
                                icon: 'error',
                                title: 'Flutterwave Error',
                                text: response.message ||
                                    '{{ __('vendorwebsite.failed_to_initiate_flutterwave_payment') }}',
                                confirmButtonText: '{{ __('vendorwebsite.ok') }}',
                                customClass: {
                                    confirmButton: 'btn btn-primary'
                                },
                                buttonsStyling: false,
                            });
                        }
                    })
                    .catch(err => {
                        Swal.fire({
                            icon: 'error',
                            title: 'Flutterwave Error',
                            text: err.message ||
                                '{{ __('vendorwebsite.failed_to_initiate_flutterwave_payment') }}',
                            confirmButtonText: '{{ __('vendorwebsite.ok') }}',
                            customClass: {
                                confirmButton: 'btn btn-primary'
                            },
                            buttonsStyling: false,
                        });
                    })
                    .finally(function() {
                        button.prop('disabled', false).text(originalText);
                    });
            } else {

                button.prop('disabled', false).text(originalText);
            }
        }

        function loadDeliveryZonesForAddress(addressId) {
            $.get("{{ route('frontend.delivery-zones') }}", {
                address_id: addressId
            }, function(response) {
                if (response.status && response.zones.length > 0) {
                    let zonesHtml = '';
                    response.zones.forEach(function(zone, index) {
                        const isChecked = index === 0 ? 'checked' : '';
                        const deliveryChargeDisplay = zone.standard_delivery_charge > 0 ?
                            `
                            ${formatCurrencyvalue(zone.standard_delivery_charge)}` : 'Free';

                        zonesHtml += `
            <div class="bg-gray-800 p-4 rounded d-flex flex-wrap align-items-center justify-content-between gap-3 mb-3">
                <div class="d-flex align-items-center gap-3">
                    <div class="form-check">
                        <input class="form-check-input" type="radio" name="delivery_zone" value="${zone.id}" ${isChecked}>
                    </div>
                    <div>
                        <p class="mb-0">${zone.name}</p>
                        ${zone.logistic_name ? `<small class="text-body">${zone.logistic_name}</small>` : ''}
                    </div>
                </div>
                <div>
                <div class="d-flex align-items-center gap-lg-4 gap-2">
                    <h5 class="mb-0 text-primary">${deliveryChargeDisplay}</h5>
                </div>
                 <small class="text-body">Estimate delivery in ${zone.standard_delivery_time || '3-5'} days</small>
                </div>


            </div>
        `;
                    });

                    $('.charges-block .delivery-zones-container').html(zonesHtml);

                    // Re-attach event listeners to new radio buttons
                    $('input[name="delivery_zone"]').on('change', function() {
                        updateCheckoutSummary();
                    });

                    // Update summary with first zone
                    updateCheckoutSummary();
                    $('.onclick-page-redirect button').prop('disabled', false); // Enable button
                    $('#inlineAddressError').hide();
                } else {
                    // Show message if no zones available for this address
                    $('.charges-block .delivery-zones-container').html(`
                <div class="bg-gray-800 p-4 rounded text-center">
                    <h6 class="mb-0">{{ __('vendorwebsite.no_delivery_zones_available_for_this_address_please_select_a_different_address') }}</h6>
                </div>
            `);
                    $('.onclick-page-redirect button').prop('disabled', true); // Disable button
                    $('#inlineAddressError').text(
                        'No delivery zones available for the selected address. Please select a different address.'
                    ).show();

                    // Update checkout summary to remove delivery charges
                    updateCheckoutSummary();
                }
            }).fail(function() {
                $('.charges-block .delivery-zones-container').html(`
            <div class="bg-gray-800 p-4 rounded text-center">
                <p class="mb-0 text-danger">{{ __('vendorwebsite.failed_to_load_delivery_zones_please_try_again') }}</p>
            </div>
        `);
                $('.onclick-page-redirect button').prop('disabled', true); // Disable button
                $('#inlineAddressError').text('Failed to load delivery zones. Please try again.').show();

                // Update checkout summary to remove delivery charges
                updateCheckoutSummary();
            });
        }

        function toggleAddressForm() {

            const form = document.getElementById('inlineAddAddressForm');
            const errorDiv = document.getElementById('inlineAddressError');
            if (!form) {

                if (errorDiv) {
                    errorDiv.innerText = 'Error: Address form not found on the page.';
                    errorDiv.style.display = 'block';
                }
                return;
            }
            form.style.display = (form.style.display === 'none' || form.style.display === '') ? 'block' : 'none';

            if (errorDiv) errorDiv.style.display = 'none';
        }

        function saveInlineAddress(event) {
            event.preventDefault();

            var form = document.getElementById('inline_addressForm');
            if (!form) {

                return;
            }
            var formData = new FormData(form);

            $.ajax({
                url: "{{ route('frontend.address.store') }}",
                type: 'POST',
                data: formData,
                processData: false,
                contentType: false,
                success: function(response) {
                    if (response.status) {
                        location.reload();
                    } else {
                        toastr.error(response.message);
                    }
                },
                error: function() {
                    toastr.error('Failed to save address. Please try again.');
                }
            });
        }

        function updateOrderSummaryBox() {


            var selectedAddress = $('input[name="address"]:checked').closest('.bg-gray-800');

            $('#order-summary-username').text('');
            $('#order-summary-email').text('');
            $('#order-summary-mobile').text('');

            // Always use the logged-in user's name, email, and mobile
            var userName = selectedAddress.find('.user-name').text() || window.loggedInUserName || '';
            var userEmail = selectedAddress.find('.user-email').text() || window.loggedInUserEmail || '';
            var userMobile = selectedAddress.find('.user-contact-number').text() || window.loggedInUserMobile || '';

            $('#order-summary-username').text(userName);
            $('#order-summary-email').text(userEmail);
            $('#order-summary-mobile').text(userMobile);

            // Get address from selected address radio

            var addressLines = selectedAddress.find('p.address-line').map(function() {
                return $(this).text();
            }).get().join('<br>');
            $('#order-summary-address').html(addressLines);
        }



        // Update order summary when table is redrawn or address changes
        $(document).ready(function() {
            $('#checkout-table').on('draw.dt', function() {
                updateOrderSummaryBox();
            });
            $(document).on('change', 'input[name="address"]', function() {

                updateOrderSummaryBox();
            });
            // Initial call
            updateOrderSummaryBox();
        });

        window.loggedInUserName = "{{ (auth()->user()->first_name ?? '') . ' ' . (auth()->user()->last_name ?? '') }}";
        window.loggedInUserEmail = "{{ auth()->user()->email ?? '' }}";
        window.loggedInUserMobile = "{{ auth()->user()->mobile ?? '' }}";

        $(document).on('click', '.editable-span', function() {
            var $span = $(this);
            var currentValue = $span.text();
            var inputType = $span.attr('id') === 'order-summary-email' ? 'email' : 'text';
            var $input = $('<input type="' + inputType +
                    '" class="form-control d-inline-block editable-input" style="width: 70%; max-width: 220px;" />')
                .val(currentValue);
            $span.replaceWith($input);
            $input.focus();

            function saveInput() {
                var newValue = $input.val();
                var newSpan = $('<span></span>')
                    .attr('id', $input.attr('id'))
                    .addClass('editable-field editable-span')
                    .text(newValue);
                $input.replaceWith(newSpan);
                // Re-apply cursor style for dynamically created elements
                $('.editable-field, .editable-span').css('cursor', 'pointer');
            }

            $input.on('blur', saveInput);
            $input.on('keydown', function(e) {
                if (e.key === 'Enter') {
                    e.preventDefault();
                    saveInput();
                }
            });
        });

        // Add this function to clear cart/checkout data
        function clearCheckoutData() {
            // If you use localStorage or sessionStorage for cart, clear it here
            if (window.localStorage) {
                localStorage.removeItem('cart');
                localStorage.removeItem('checkout');
            }
            // Optionally, reload the cart table if using AJAX
            if ($('#checkout-table').length) {
                $('#checkout-table').DataTable().clear().draw();
            }
            // Optionally, clear any summary boxes
            $('#checkout-summary').html('');
            $('#order-summary-box').html('');
            // Hide table and show empty cart message
            $('#checkout-table').hide();
            $('#empty-cart-message').show();
            $('#checkout-sections').hide();
            $('.cart-summary').hide();
            $('.col-md-5.col-lg-3').hide(); // Hide right-side content
        }

        // function selectAddress(addressId, element) {
        //     // Remove active class from all address cards
        //     $('.address-card').removeClass('active');

        //     // Add active class to clicked card
        //     $(element).addClass('active');

        //     // Check the radio button for this address
        //     $(element).find('.address-radio').prop('checked', true).trigger('change');
        // }
        function selectAddress(addressId, clickedCard) {
            const block = document.querySelector('.address-block');
            const currentPrimary = block.querySelector('.address-card.active');
            const collapseSection = document.getElementById('otherAddresses');

            // 👉 Prevent re-selecting the same address
            if (currentPrimary && currentPrimary.dataset.id === String(addressId)) {
                if (collapseSection && collapseSection.classList.contains('show')) {
                    const collapseInstance = bootstrap.Collapse.getInstance(collapseSection) || new bootstrap.Collapse(
                        collapseSection);
                    collapseInstance.hide();
                }
                return;
            }

            // Remove active from all
            document.querySelectorAll('.address-card').forEach(card => card.classList.remove('active'));
            // Uncheck all radios
            document.querySelectorAll('.address-radio').forEach(radio => radio.checked = false);

            // Clone clicked
            const newPrimary = clickedCard.cloneNode(true);
            newPrimary.classList.add('active');
            newPrimary.setAttribute('onclick', `selectAddress(${addressId}, this)`);

            const newPrimaryRadio = newPrimary.querySelector('.address-radio');
            if (newPrimaryRadio) newPrimaryRadio.checked = true;

            if (currentPrimary && currentPrimary !== clickedCard) {
                // Clone current primary to move to collapse
                const oldPrimary = currentPrimary.cloneNode(true);
                oldPrimary.classList.remove('active');
                oldPrimary.setAttribute('onclick', `selectAddress(${currentPrimary.dataset.id}, this)`);
                const oldRadio = oldPrimary.querySelector('.address-radio');
                if (oldRadio) oldRadio.checked = false;

                // Append old primary to collapse
                if (collapseSection) {
                    collapseSection.prepend(oldPrimary);
                }

                // Remove the clicked card from collapse
                clickedCard.remove();

                // Replace current primary with new selected
                currentPrimary.replaceWith(newPrimary);
            }

            loadDeliveryZonesForAddress(addressId);
            updateOrderSummaryBox();

            // Show success toast
            if (typeof toastr !== 'undefined') {
                toastr.success('{{ __('vendorwebsite.delivery_address_updated') }}', '', {
                    closeButton: true,
                    progressBar: true,
                    positionClass: 'toast-top-right',
                    timeOut: 3000
                });
            }

            // Collapse section if open
            if (collapseSection && collapseSection.classList.contains('show')) {
                const collapseInstance = bootstrap.Collapse.getInstance(collapseSection) || new bootstrap.Collapse(
                    collapseSection);
                collapseInstance.hide();
            }
        }

        function prefillUserName() {
            // Reset modal title to "Add New Address"
            $('#modalAddAddressLabel').text('Add New Address');

            // Prefill first and last name fields with logged-in user's name
            var firstName = "{{ auth()->user()->first_name ?? '' }}";
            var lastName = "{{ auth()->user()->last_name ?? '' }}";
            var email = "{{ auth()->user()->email ?? '' }}";
            var contact_number = "{{ auth()->user()->mobile ?? '' }}";
            document.getElementById('first_name').value = firstName;
            document.getElementById('last_name').value = lastName;
            document.getElementById('mobileInput').value = contact_number;
            document.getElementById('email').value = email;


            // Load countries dropdown
            loadDropdownData("{{ route('frontend.address.get-countries') }}", $('#country')[0], 'Select Country');
        }

        // Payment method selection function
        function selectPaymentMethod(method, element) {
            // Remove active class from all payment method cards
            $('.payment-method-card').removeClass('active');

            // Add active class to clicked card
            $(element).addClass('active');

            // Check the radio button for this payment method
            $(element).find('.payment-radio').prop('checked', true).trigger('change');

            // Update payment summary based on selected method
            updatePaymentSummary(method);
        }

        // Update payment summary based on selected payment method
        function updatePaymentSummary(selectedMethod) {
            const subtotal = parseFloat('{{ $subtotal ?? 0 }}');
            const walletBalance = parseFloat('{{ $walletBalance ?? 0 }}');

            if (selectedMethod === 'wallet') {
                if (walletBalance >= subtotal) {
                    // Full payment from wallet
                    $('#wallet-payment-info').html(`
                        <div class="alert alert-success">
                            <i class="ph ph-check-circle"></i> Full payment will be deducted from your wallet balance.
                            <br><small>Remaining balance: ${formatCurrencyvalue(walletBalance - subtotal)}</small>
                        </div>
                    `);
                } else {
                    // Partial payment from wallet
                    const remaining = subtotal - walletBalance;
                    $('#wallet-payment-info').html(`
                        <div class="alert alert-warning">
                            <i class="ph ph-warning"></i> Partial payment from wallet (${formatCurrencyvalue(walletBalance)}).
                            <br><small>Remaining amount (${formatCurrencyvalue(remaining)}) will be charged via another payment method.</small>
                        </div>
                    `);
                }
            } else {
                // Clear wallet payment info for other payment methods
                $('#wallet-payment-info').html('');
            }
        }

        document.addEventListener('DOMContentLoaded', function() {
            // Handle tax collapse toggle for checkout
            const taxToggleCheckout = document.querySelector('[href="#taxDetailsCheckout"]');
            const taxDetailsCheckout = document.getElementById('taxDetailsCheckout');
            const taxIconCheckout = document.querySelector('.tax2');

            if (taxToggleCheckout && taxDetailsCheckout && taxIconCheckout) {
                taxToggleCheckout.addEventListener('click', function(e) {
                    e.preventDefault();
                    const isExpanded = taxDetailsCheckout.classList.contains('show');
                    if (isExpanded) {
                        taxIconCheckout.style.transform = 'rotate(0deg)';
                    } else {
                        taxIconCheckout.style.transform = 'rotate(180deg)';
                    }
                });
            }

            // Add event listeners for address form dropdowns
            const countrySelect = document.getElementById('country');
            const stateSelect = document.getElementById('state');
            const citySelect = document.getElementById('city');

            if (countrySelect) {
                countrySelect.addEventListener('change', function() {
                    const countryId = this.value;
                    // Clear state and city dropdowns
                    stateSelect.innerHTML = '<option value="" disabled selected>Select State</option>';
                    citySelect.innerHTML = '<option value="" disabled selected>Select City</option>';

                    if (countryId) {
                        loadDropdownData("{{ route('frontend.address.get-states') }}?country_id=" +
                            countryId, stateSelect, 'Select State');
                    }
                });
            }

            if (stateSelect) {
                stateSelect.addEventListener('change', function() {
                    const stateId = this.value;
                    // Clear city dropdown
                    citySelect.innerHTML = '<option value="" disabled selected>Select City</option>';

                    if (stateId) {
                        loadDropdownData("{{ route('frontend.address.get-cities') }}?state_id=" + stateId,
                            citySelect, 'Select City');
                    }
                });
            }

            // Initialize payment method selection
            $('.payment-radio').on('change', function() {
                const selectedMethod = $(this).val();
                updatePaymentSummary(selectedMethod);
            });
        });

        // Handle payment method selection
        document.addEventListener('DOMContentLoaded', function() {
            const paymentMethodDropdown = document.getElementById('payment-method-collapse');
            const selectedMethodBtn = document.getElementById('selected-method-btn');
            const selectedMethodImg = document.getElementById('selected-method-img');
            const selectedMethodName = document.getElementById('selected-method-name');
            const paymentRadios = document.querySelectorAll('.payment-radio');
            const paymentMethodList = document.getElementById('payment-method-list');

            // Close dropdown when clicking outside
            // document.addEventListener('click', function(event) {
            //     if (!paymentMethodDropdown.contains(event.target)) {
            //         paymentMethodList.classList.remove('show');
            //     }
            // });

            document.addEventListener('click', function(event) {
                console.log(paymentMethodDropdown);
                if (paymentMethodDropdown && !paymentMethodDropdown.contains(event.target)) {
                    paymentMethodList.classList.remove('show');
                    resetIcon(); // Reset the icon when closing
                }
            });

            // Toggle dropdown
            // selectedMethodBtn.addEventListener('click', function(e) {
            //     e.stopPropagation();
            //     paymentMethodList.classList.toggle('show');
            // });

            selectedMethodBtn.addEventListener('click', function(e) {
                e.stopPropagation();

                // Toggle the 'show' class for the payment method list manually
                const isExpanded = paymentMethodList.classList.contains('show');

                if (isExpanded) {
                    paymentMethodList.classList.remove('show'); // Close the dropdown
                    resetIcon(); // Reset the icon when closing
                } else {
                    paymentMethodList.classList.add('show'); // Open the dropdown
                    const icon = document.getElementById('toggle-icon');
                    icon.classList.remove('ph-caret-down');
                    icon.classList.add('ph-caret-up');
                }
            });

            // Handle radio button changes
            // paymentRadios.forEach(radio => {
            //     radio.addEventListener('change', function() {
            //         if (this.checked) {
            //             const label = this.closest('label');
            //             const img = label.querySelector('img');
            //             const name = label.querySelector('span:not(.text-success)').textContent
            //                 .trim();

            //             // Update selected method display
            //             selectedMethodImg.src = img.src;
            //             selectedMethodImg.alt = img.alt;
            //             selectedMethodName.textContent = name;

            //             // Close the dropdown
            //             paymentMethodList.classList.remove('show');
            //         }
            //     });
            // });

            paymentRadios.forEach(radio => {
                radio.addEventListener('change', function() {
                    if (this.checked) {
                        const label = this.closest('label');
                        const img = label.querySelector('img');
                        const name = label.querySelector('span:not(.text-success)').textContent
                            .trim();

                        // Update selected method display
                        selectedMethodImg.src = img.src;
                        selectedMethodImg.alt = img.alt;
                        selectedMethodName.textContent = name;

                        // Close the dropdown
                        paymentMethodList.classList.remove('show');
                        resetIcon(); // Reset the icon after selection and closing
                    }
                });
            });

            function resetIcon() {
                const icon = document.getElementById('toggle-icon');
                icon.classList.remove('ph-caret-up');
                icon.classList.add('ph-caret-down');
            }

            // Initialize with the first checked radio
            const initialChecked = document.querySelector('.payment-radio:checked');
            if (initialChecked) {
                const label = initialChecked.closest('label');
                const img = label.querySelector('img');
                const name = label.querySelector('span:not(.text-success)').textContent.trim();

                selectedMethodImg.src = img.src;
                selectedMethodImg.alt = img.alt;
                selectedMethodName.textContent = name;
            }
        });
    </script>
@endpush
