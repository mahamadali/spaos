@extends('backend.layouts.app')
@section('title')
    {{ __('messages.pricing') }}
@endsection
@section('content')
    <section class="section-spacing-bottom">
        <div class="card">
            <div class="card-body">
                <a href="{{ route('backend.upgrade-plan') }}" class="d-flex align-items-center gap-2 mb-5 text-body"
                    href="#">
                    <i class="ph ph-caret-left"></i>
                    <span class="font-size-14 fw-semibold">{{ __('frontend.back') }}
                    </span>
                </a>
                <div class="row">
                    <!-- Left Section -->
                    <div class="col-lg-4">
                        <form class="subscription">

                            @foreach ($data['plan'] as $plan)
                                @if (isset($plan) && $plan->status == 1)
                                    @if ($plan->price > 0)
                                        <div class="subs-plan">
                                            <label for="plan-{{ $plan->id }}"
                                                class="pricing-tab border-0 rounded-3 d-flex justify-content-between">
                                                <div>
                                                    <div class="d-flex align-items-center gap-1">
                                                        <h5 class="text-primary mb-0">
                                                            {{ \Currency::formatSuperadmin($plan->price ?? 0) }}</h5>
                                                        <span class="text-muted">
                                                            / {{ $plan->duration ?? '-' }}
                                                            @if ($plan->type == 'Monthly')
                                                                <span class="text-muted"> {{ __('frontend.Month') }}
                                                                </span>
                                                            @elseif($plan->type == 'Weekly')
                                                                <span class="text-muted"> {{ __('frontend.week') }}
                                                                </span>
                                                            @elseif($plan->type == 'Yearly')
                                                                <span class="text-muted"> {{ __('frontend.year') }}
                                                                </span>
                                                            @endif
                                                            <span>
                                                    </div>
                                                    <h6 class="plan-name mt-2">{{ $plan->name }}</h6>
                                                </div>

                                                <!-- Dynamically check if the current plan matches the selected plan -->
                                                <input class="form-check-input ms-auto" type="radio" name="plan"
                                                    id="plan-{{ $plan->id }}"
                                                    onchange="fetchPaymentDetails({{ $plan->id }})"
                                                    value="{{ $plan->id }}"
                                                    {{ $data['selected_plan'] == $plan->id ? 'checked' : '' }}>
                                            </label>
                                        </div>
                                    @endif
                                @endif
                            @endforeach


                        </form>
                    </div>


                    <!-- Right Section -->
                    <div class="col-lg-8 mt-lg-0 mt-4">
                        <form action="{{ route('backend.payment-process') }}" method="POST" id="payment-form">
                            @csrf
                            <!-- Payment Method -->
                            <div class="payment-card rounded-2 mb-4">
                                <h5 class="mb-3 font-size-18">{{ __('frontend.choose_payment_method') }}
                                </h5>
                                <div class="form-group">
                                    <input type="hidden" id="selected-plan-id" name="plan_id"
                                        value="{{ $data['selected_plan'] }}">
                                    <input type="hidden" id="selected-price-id" name="total_price_amount"
                                        value="{{ $data['total_amount'] }}">


                                    <select id="payment-method" name="payment_method" class="form-select select2"
                                        value="">
                                        <option value="" selected disabled>{{ __('frontend.select_payment_method') }}
                                        </option>
                                        @php
                                            $payment_methods = [
                                                'str_payment_method' => 'stripe',
                                                'razor_payment_method' => 'razorpay',
                                                'paystack_payment_method' => 'paystack',
                                                'paypal_payment_method' => 'paypal',
                                                'flutterwave_payment_method' => 'flutterwave',
                                                'cinet_payment_method' => 'cinet',
                                                'sadad_payment_method' => 'sadad',
                                                'airtelmoney_payment_method' => 'airtel',
                                                'phonepay_payment_method' => 'phonepe',
                                                'midtrans_payment_method' => 'midtrans',
                                            ];
                                        @endphp
                                        @foreach ($payment_methods as $setting => $method)
                                            @if (paymentsetting($setting) == 1)
                                                <option value="{{ $method }}">{{ __('frontend.' . $method) }}
                                                </option>
                                            @endif
                                        @endforeach
                                    </select>
                                    <div class="invalid-feedback" id="payment-method-error"
                                        style="display:none; color: red; ">
                                        {{ __('messages.please_select_payment_method') }}
                                    </div>
                                </div>

                            </div>

                            <div id="loader" style="display: none;">
                                <!-- You can use a spinner or any loading indicator -->
                                <div class="spinner-border text-primary" role="status">
                                    <span class="visually-hidden">{{ __('messages.loading') }}</span>
                                </div>
                            </div>




                            <!-- Available Coupons -->
                            <div class="bg-body available-coupon-card rounded-2 my-4 p-3 {{ isset($data['promotions']) && $data['promotions']->isNotEmpty() ? '' : 'd-none' }}"
                                id="promotional_section">
                                <div class="d-flex justify-content-between align-items-center gap-5">
                                    <h5 class="mb-2 font-size-18">{{ __('frontend.available_coupons') }}
                                    </h5>
                                    @if (count($data['promotions']) > 2)
                                        <a href="javascript:void(0);" id="viewAllCoupons" data-bs-toggle="modal"
                                            data-bs-target="#all-coupons">{{ __('messages.view_all') }}</a>
                                    @endif
                                </div>
                                <div class="row flex-nowrap coupan-list" id="promotional_id">

                                    @foreach ($data['promotions'] as $key => $promotion)
                                        @if ($key < 2)
                                            <div class="col-md-6 mb-2">
                                                <label for="radio1"
                                                    class="form-check-label rounded coupons-card active d-flex justify-content-between  gap-3 p-3">
                                                    <div class="d-flex align-items-center justify-content-between gap-3">
                                                        <input class="form-check-input coupon-radio" type="radio"
                                                            name="coupon_id" id="coupon_id_{{ $promotion['id'] }}"
                                                            value="{{ $promotion['id'] }}">
                                                        <div>
                                                            @if ($promotion['coupon']['discount_type'] == 'percent')
                                                                <p class="mb-2 font-size-14 fw-medium">
                                                                    {{ __('frontend.get_extra') }}
                                                                    {{ $promotion['coupon']['discount_percentage'] }}% off
                                                                </p>
                                                            @else
                                                                <p class="mb-2 font-size-14 fw-medium">
                                                                    {{ __('frontend.get_extra') }}
                                                                    {{ \Currency::formatSuperadmin($promotion['coupon']['discount_amount']) }}
                                                                    off </p>
                                                            @endif
                                                            <div class="d-flex align-items-center gap-2">
                                                                <p class="mb-0 font-size-14 fw-medium">
                                                                    {{ __('frontend.use_code') }}:</p>
                                                                <h6 class="mb-0">
                                                                    {{ $promotion['coupon']['coupon_code'] }}</h6>
                                                            </div>
                                                        </div>

                                                    </div>
                                                    <span
                                                        class="font-size-14 coupons-status">{{ __('frontend.apply') }}</span>
                                                </label>
                                            </div>
                                        @endif
                                    @endforeach

                                </div>
                            </div>


                            <div class="my-5">
                                <!-- Payment Details -->
                                <div class="payment-details-card border-0 rounded-2">
                                    <h5 class="payment-details-heading pb-1 mb-3 font-size-18">
                                        {{ __('frontend.payment_details') }}</h5>
                                    <div>
                                        <!-- Original Price -->
                                        <div class="d-flex justify-content-between mb-2">
                                            <span>{{ __('frontend.price') }}</span>
                                            <h6 class="font-size-18 mb-0" id="price">
                                                {{ \Currency::formatSuperadmin($data['plan_details']['price'] ?? 0) }}
                                            </h6>
                                        </div>

                                        <!-- Plan Discount -->
                                        @if (isset($data['plan_details']['has_discount']) && $data['plan_details']['has_discount'])
                                            <div class="d-flex justify-content-between mb-2" id="plan_discount_section">
                                                <div class="d-flex align-items-center gap-2">
                                                    <span>{{ __('messages.plan_discount') }}</span>
                                                    <span class="text-success" id="discount_type_label">
                                                        ({{ $data['plan_details']['discount_type'] == 'percentage'
                                                            ? $data['plan_details']['discount_value'] . '%'
                                                            : \Currency::formatSuperadmin($data['plan_details']['discount_value']) }})
                                                    </span>
                                                </div>
                                                <h6 class="font-size-18 text-success mb-0" id="plan_discount_amount">
                                                    -
                                                    {{ \Currency::formatSuperadmin($data['plan_details']['price'] - $data['plan_details']['discounted_price']) }}
                                                </h6>
                                            </div>
                                        @endif



                                        <!-- Coupon Discount -->
                                        <div class="d-flex justify-content-between mb-2 d-none" id="discount_section">
                                            <div class="d-flex align-items-center gap-2">
                                                <span>{{ __('frontend.coupon') }}</span>
                                                <span class="text-success" id="cupon_code"></span>
                                            </div>
                                            <h6 class="font-size-18 text-success mb-0" id="discount_amount"></h6>
                                        </div>

                                        <!-- Subtotal after all discounts -->
                                        <div class="d-flex justify-content-between mb-2" id="subtotal_section">
                                            <span>{{ __('frontend.subtotal') }}</span>
                                            <h6 class="font-size-18 mb-0" id="subtotal_price"></h6>
                                        </div>

                                        <!-- Tax Section -->
                                        @if ($data['total_tax'] > 0)
                                            <div class="d-flex justify-content-between align-items-center mb-2 tax-box"
                                                href="#collapseTaxes" data-bs-toggle="collapse" aria-expanded="true">
                                                <span class="h6 fw-normal text-body mb-0">{{ __('frontend.tax') }}</span>
                                                <div class="d-flex align-items-center gap-2">
                                                    <i class="ph ph-caret-up"></i>
                                                    <span class="h6 font-size-18 text-danger mb-0" id="total_tax">
                                                        + {{ \Currency::formatSuperadmin($data['total_tax']) }}
                                                    </span>
                                                </div>
                                            </div>

                                            <div id="collapseTaxes" class="collapse show">
                                                <!-- Tax details will be populated here -->
                                            </div>
                                        @endif

                                        <!-- Total Amount -->
                                        <h6 class="d-flex align-items-center justify-content-between mb-0">
                                            <span>{{ __('frontend.total_payment') }}</span>
                                            <span class="text-primary fs-5" id="total_amount">
                                                {{ \Currency::formatSuperadmin($data['total_amount']) }}
                                            </span>
                                        </h6>
                                    </div>
                                </div>
                            </div>


                            <div
                                class="d-flex justify-content-end align-items-center flex-wrap row-gap-2 column-gap-4 pt-lg-4 pt-0">
                                <div class="d-flex align-items-center justify-content-end gap-2 font-size-14">
                                    <i class="ph ph-shield-check h5 mb-0 text-success"></i>
                                    <span>{{ __('frontend.checkout_in_second') }}
                                    </span>
                                </div>
                                <button class="btn btn-secondary" type="submit">{{ __('frontend.proceed_payment') }}
                                </button>
                            </div>

                        </form>
                    </div>
                </div>
            </div>
    </section>



    <!-- id="all-coupons" -->
    <div class="all-coupons-modal modal fade" id="all-coupons">
        <div class="modal-dialog modal-dialog-centered modal-md">
            <div class="modal-content section-background">
                <div class="modal-body modal-body-inner">
                    <div class="close-modal-btn" data-bs-dismiss="modal">
                        <i class="ph ph-x"></i>
                    </div>

                    @if (!empty($data['promotions']))
                        <div class="" id="promotional_id">

                            <h4 class="font-size-18 mb-3">{{ __('frontend.available_coupons') }}</h4>
                            <form>
                                <ul class="list-inline m-0 coupons-inner">

                                    @foreach ($data['promotions'] as $key => $promotion)
                                        <li>
                                            <label for="radio2"
                                                class="form-check-label rounded coupons-card d-flex justify-content-between gap-3 p-3">
                                                <div class="d-flex align-items-center justify-content-between gap-3">
                                                    <input class="form-check-input coupon-radio" type="radio"
                                                        name="coupon_id" id="coupon_id_{{ $promotion['id'] }}"
                                                        value="{{ $promotion['id'] }}">
                                                    <div>
                                                        @if ($promotion['coupon']['discount_type'] == 'percent')
                                                            <p class="mb-2 font-size-14 fw-medium">
                                                                {{ __('frontend.get_extra') }}
                                                                {{ $promotion['coupon']['discount_percentage'] }}%
                                                                {{ __('frontend.off') }}</p>
                                                        @else
                                                            <p class="mb-2 font-size-14 fw-medium">
                                                                {{ __('frontend.get_extra') }}
                                                                {{ \Currency::formatSuperadmin($promotion['coupon']['discount_amount']) }}
                                                                {{ __('frontend.off') }}</p>
                                                        @endif
                                                        <div class="d-flex align-items-center gap-2">
                                                            <p class="mb-0 font-size-14 fw-medium">
                                                                {{ __('frontend.use_code') }}:</p>
                                                            <h6 class="mb-0"> {{ $promotion['coupon']['coupon_code'] }}
                                                            </h6>
                                                        </div>
                                                    </div>
                                                </div>
                                                <span
                                                    class="font-size-14 coupons-status">{{ __('frontend.apply') }}</span>
                                            </label>
                                        </li>
                                    @endforeach

                                </ul>

                            </form>
                        </div>
                    @endif
                </div>
            </div>
        </div>
    </div>

    <!-- id="successfully-modal" -->
    <div class="modal fade" id="successfully-modal">
        <div class="modal-dialog modal-dialog-centered modal-md">
            <div class="modal-content section-background">
                <div class="modal-body modal-body-inner">
                    <div class="mb-5 pb-3 d-flex justify-content-center">
                        <img src="{{ asset('/img/frontend/modal-success.png') }}" alt="modal-success">
                    </div>
                    <div class="text-center">
                        <h5>{{ __('frontend.thank_you') }}</h5>
                        <p class="mb-0">{{ __('frontend.successfully_purchased_plan') }}</p>
                    </div>
                    <div class="mt-5 pt-2 d-flex justify-content-center">
                        <button type="button" class="btn btn-secondary" data-bs-toggle="modal"
                            data-bs-target="#cancellation-confirmed">Start Exploring</button>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <!-- id="cancellation-confirmed" -->
    <div class="modal fade" id="cancellation-confirmed">
        <div class="modal-dialog modal-dialog-centered modal-md">
            <div class="modal-content section-background">
                <div class="modal-body modal-body-inner">
                    <div class="mb-5 pb-3 d-flex justify-content-center">
                        <img src="{{ asset('/img/frontend/modal-success.png') }}" alt="modal-success">
                    </div>
                    <div class="text-center">
                        <h5>{{ __('frontend.cancellation_confirmed') }}</h5>
                        <p class="mb-0">{{ __('frontend.cancel_payment_confirmation') }}</p>
                    </div>
                    <div class="mt-5 pt-2 d-flex gap-3 flex-wrap justify-content-center">
                        <button type="button" class="btn btn-primary"
                            data-bs-dismiss="modal">{{ __('frontend.pay') }}</button>
                        <button type="button" class="btn btn-secondary">{{ __('frontend.continue_to_pay') }}</button>
                    </div>
                </div>
            </div>
        </div>
    </div>
    </div>


@endsection
@push('after-scripts')
    <script src="https://checkout.razorpay.com/v1/checkout.js"></script>

    <link rel="stylesheet" href="{{ asset('phosphor-icons/regular/style.css') }}">
    <link rel="stylesheet" href="{{ asset('phosphor-icons/fill/style.css') }}">
    <script>
        function fetchPaymentDetails(planId) {
            // Show the loader before the request
            let paymentDetailsUrl = "{{ route('payment-details') }}";
            $('#loader').show();

            $.ajax({
                url: paymentDetailsUrl,
                method: 'GET',
                data: {
                    id: planId
                },
                success: function(response) {

                    var promotionalHtml = '';
                    var modalHtml = '';
                    var taxData = '';

                    if (response.promotions && response.promotions.length > 0) {
                        $('#promotional_section').removeClass('d-none');
                        $.each(response.promotions, function(index, promotion) {
                            if (index < 2) {
                                promotionalHtml += `
                            <div class="col-md-6">
                                <label class="form-check-label rounded coupons-card d-flex justify-content-between gap-3 p-3">
                                    <div class="d-flex align-items-center justify-content-between gap-3">
                                        <input class="form-check-input  coupon-radio" type="radio" name="coupon_id"  id="coupon_id_${promotion.coupon.id}" value="${promotion.coupon.id}">
                                        <div>
                                            <p class="mb-2 font-size-14">Get Extra ${promotion.coupon.discount_type === 'percent' ? `${promotion.coupon.discount_percentage}%` : `${formatCurrencyvalue(promotion.coupon.discount_amount)}`} off</p>
                                            <div class="d-flex align-items-center gap-2">
                                                <p class="mb-0 font-size-14">Use Code:</p>
                                                <h6 class="mb-0">${promotion.coupon.coupon_code}</h6>
                                            </div>
                                        </div>
                                    </div>
                                           <span class="font-size-14 coupons-status">{{ __('frontend.apply') }}</span>

                                </label>
                            </div>`;
                            }

                            modalHtml += `
                        <li>
                            <label class="form-check-label rounded coupons-card d-flex justify-content-between gap-3 p-3">
                                <div class="d-flex align-items-center justify-content-between gap-3">
                                    <input class="form-check-input" type="radio" name="promotion" value="${promotion.coupon.id}">
                                    <div>
                                            <p class="mb-2 font-size-14">Get extra ${promotion.coupon.discount_type === 'percent' ? `${promotion.coupon.discount_percentage}%` : `${formatCurrencyvalue(promotion.coupon.discount_percentage)}`} off</p>
                                        <div class="d-flex align-items-center gap-2">
                                            <p class="mb-0 font-size-14">use code:</p>
                                            <h6 class="mb-0">${promotion.coupon.coupon_code}</h6>
                                        </div>
                                    </div>
                                </div>

                            </label>
                        </li>`;
                        });
                    } else {
                        $('#promotional_section').addClass('d-none');
                    }

                    if (response.tax_details && response.tax_details.length > 0) {
                        var taxHtml = `
                    <div class="applied-taxes-card p-3 mb-5 rounded">
                        <h6 class="mb-3">{{ __('messages.applied_taxes') }}</h6>
                        <div>
                `;
                        $.each(response.tax_details, function(index, tax) {
                            taxHtml += `
                        <div class="d-flex justify-content-between align-items-center mb-2">
                            <span class="font-size-14">${tax.title} (${tax.type === 'Percentage' ? tax.value + '%' : formatCurrencyvalue(tax.value)})</span>
                            <h6 class="mb-0">${formatCurrencyvalue(tax.amount)}</h6>
                        </div>
                    `;
                        });

                        taxHtml += `</div></div>`;
                        $('#collapseTaxes').html(taxHtml).collapse('show');
                    } else {
                        $('#collapseTaxes').html(
                            '<p class="text-muted">{{ __('messages.no_taxes_applied') }}</p>').collapse(
                            'show');
                    }


                    $('#discount_section').addClass('d-none');
                    $('#subtotal_section').addClass('d-none');


                    $('#promotional_id').html(promotionalHtml);
                    $('#all-coupons .coupons-inner').html(modalHtml);
                    $('#price').text(formatCurrencyvalue(response.plan_details.price));

                    // Handle plan discount if exists
                    if (response.plan_details.has_discount) {
                        $('#plan_discount_section').removeClass('d-none');
                        const discountLabel = response.plan_details.discount_type === 'percentage' ?
                            `${response.plan_details.discount_value}%` :
                            formatCurrencyvalue(response.plan_details.discount_value);

                        $('#discount_type_label').text(`(${discountLabel})`);
                        $('#plan_discount_amount').text(
                            '- ' + formatCurrencyvalue(
                                response.plan_details.price - response.plan_details.discounted_price
                            )
                        );
                    } else {
                        $('#plan_discount_section').addClass('d-none');
                    }

                    $('#total_tax').text(formatCurrencyvalue(response.total_tax));
                    $('#total_amount').text(formatCurrencyvalue(response.total_amount));
                    $('#selected-plan-id').val(planId);
                    $('#selected-price-id').val(response.total_amount);

                    $('#loader').hide();
                },
                error: function(xhr, status, error) {
                    console.error('An error occurred:', error);


                    $('#loader').hide();
                }
            });
        }

        // Function to remove currency symbol
        function stripCurrencySymbol(value) {
            if (!value) return 0;
            // Remove currency symbol, commas, and any non-numeric characters except decimal point
            const numericValue = value.toString().replace(/[^0-9.-]/g, '');
            return parseFloat(numericValue) || 0;
        }

        function formatCurrencyvalue(value) {
            // Ensure value is a number
            value = parseFloat(value) || 0;

            if (window.formatSuperadmin !== undefined) {
                return window.formatSuperadmin(value);
            }
            return value.toFixed(2); // Fallback to 2 decimal places if formatSuperadmin is not available
        }

        $(document).ready(function() {
            // Use event delegation for dynamically added elements
            $(document).on('change', '.coupon-radio', function() {
                if ($(this).is(":checked")) {
                    const couponId = $(this).val();
                    const planId = $('#selected-plan-id').val();

                    // Get the correct base price
                    const hasPlanDiscount = $('#plan_discount_section').length > 0;
                    let basePrice;

                    if (hasPlanDiscount) {
                        // If plan has discount, use the discounted price
                        const originalPrice = stripCurrencySymbol($('#price').text()) || 0;
                        const planDiscountAmount = stripCurrencySymbol($('#plan_discount_amount').text()
                            .replace('-', '')) || 0;
                        basePrice = originalPrice - planDiscountAmount;
                    } else {
                        // If no plan discount, use original price
                        basePrice = stripCurrencySymbol($('#price').text()) || 0;
                    }

                    // Ensure basePrice is a valid number
                    basePrice = parseFloat(basePrice) || 0;

                    $('#loader').show();

                    $.ajax({
                        url: "{{ route('calculate_discount') }}",
                        method: "POST",
                        headers: {
                            "X-CSRF-TOKEN": "{{ csrf_token() }}"
                        },
                        data: {
                            coupon_id: couponId,
                            plan_id: planId,
                            base_price: basePrice,
                            has_plan_discount: hasPlanDiscount
                        },
                        success: function(response) {
                            // Show discount sections
                            $('#discount_section').removeClass('d-none');
                            $('#subtotal_section').removeClass('d-none');

                            // Calculate subtotal first
                            const couponDiscount = parseFloat(response.discount_amount) || 0;
                            const subtotalAfterDiscounts = Math.max(0, basePrice -
                                couponDiscount);

                            // Update subtotal display
                            $('#subtotal_price').text(formatCurrencyvalue(
                                subtotalAfterDiscounts));
                            $('#discount_amount').text('- ' + formatCurrencyvalue(
                                couponDiscount));
                            $('#cupon_code').text('(' + response.coupon_code + ')');

                            // Calculate taxes based on subtotal
                            let totalTaxAmount = 0;
                            if (response.tax_details && response.tax_details.length > 0) {
                                const updatedTaxDetails = response.tax_details.map(tax => {
                                    let taxAmount;
                                    if (tax.type === 'Percentage') {
                                        // Calculate percentage tax based on subtotal
                                        taxAmount = (subtotalAfterDiscounts *
                                            parseFloat(tax.value)) / 100;
                                    } else {
                                        // Fixed tax remains constant
                                        taxAmount = parseFloat(tax.value);
                                    }
                                    totalTaxAmount += taxAmount;
                                    return {
                                        ...tax,
                                        amount: taxAmount
                                    };
                                });

                                // Update tax details display
                                updateTaxDetails(updatedTaxDetails);
                                $('#total_tax').text('+ ' + formatCurrencyvalue(
                                    totalTaxAmount));
                            }

                            // Calculate final total (subtotal + recalculated taxes)
                            const finalTotal = subtotalAfterDiscounts + totalTaxAmount;
                            $('#total_amount').text(formatCurrencyvalue(finalTotal));
                            $('#selected-price-id').val(finalTotal);

                            updateCouponStatus(couponId);
                            $('#loader').hide();
                        },
                        error: function(xhr, status, error) {
                            console.error("Error:", error);
                            $('#loader').hide();
                            $(this).prop('checked', false);
                            $('#discount_section').addClass('d-none');
                            $('#subtotal_section').addClass('d-none');
                        }
                    });
                }
            });
        });

        function updateTaxDetails(taxDetails) {
            if (taxDetails && taxDetails.length > 0) {
                let taxHtml = `
            <div class="applied-taxes-card p-3 mb-5 rounded">
                <h6 class="mb-3">{{ __('messages.applied_taxes') }}</h6>
                <div>
                    ${taxDetails.map(tax => `
                                    <div class="d-flex justify-content-between align-items-center mb-2">
                                        <span class="font-size-14">${tax.title} (${
                                            tax.type === 'Percentage' ?
                                            tax.value + '%' :
                                            formatCurrencyvalue(tax.value)
                                        })</span>
                                        <h6 class="mb-0">${formatCurrencyvalue(tax.amount)}</h6>
                                    </div>
                                `).join('')}
                </div>
            </div>`;

                $('#collapseTaxes').html(taxHtml).collapse('show');
            } else {
                $('#collapseTaxes').html('<p class="text-muted">{{ __('messages.no_taxes_applied') }}</p>').collapse(
                    'hide');
            }
        }

        function updateCouponStatus(selectedCouponId) {
            $('.coupon-radio').each(function() {
                const card = $(this).closest('.coupons-card');
                if ($(this).val() == selectedCouponId) {
                    card.find('.coupons-status').text('Applied');
                    $(this).prop('checked', true);
                } else {
                    card.find('.coupons-status').text('Apply');
                    $(this).prop('checked', false);
                }
            });
        }

        document.getElementById('payment-form').addEventListener('submit', function(event) {
            const paymentMethod = document.getElementById('payment-method').value;

            if (!paymentMethod) {
                event.preventDefault(); // Prevent form submission
                document.getElementById('payment-method').classList.add('is-invalid');
                document.getElementById('payment-method-error').style.display = 'block';
            }
        });


        document.getElementById('payment-method').addEventListener('change', function() {
            document.getElementById('payment-method').classList.remove('is-invalid');
            document.getElementById('payment-method-error').style.display = 'none';
        });

        $(document).ready(function() {
            $('#payment-form').on('submit', function(e) {

                if (document.getElementById('payment-method').value !== 'razorpay') {
                    return true;
                }

                e.preventDefault();

                $.ajax({
                    url: $(this).attr('action'),
                    type: "POST",
                    data: $(this).serialize(),
                    headers: {
                        'X-CSRF-TOKEN': $('meta[name="csrf-token"]').attr('content')
                    },
                    success: function(response) {
                        var options = {
                            "key": response.key,
                            "amount": response.amount,
                            "currency": response.currency,
                            "name": response.name,
                            "description": response.description,
                            "order_id": response.order_id,
                            "handler": function(paymentResponse) {
                                const successUrl = new URL(response.success_url);
                                successUrl.searchParams.append('gateway', 'razorpay');
                                successUrl.searchParams.append('razorpay_payment_id',
                                    paymentResponse.razorpay_payment_id);
                                successUrl.searchParams.append('plan_id', response
                                    .plan_id);

                                window.location.href = successUrl.toString();
                            },
                            "prefill": {
                                "name": response.prefill.name ?? '-',
                                "email": response.prefill.email,
                                "contact": response.prefill.contact ?? '-',
                            },
                            "theme": {
                                "color": "#F37254"
                            }
                        };

                        var rzp1 = new Razorpay(options);
                        rzp1.open();
                    },
                    error: function(xhr) {
                        if (xhr.status === 401) {
                            window.location.href = xhr.responseJSON.redirect_url;
                        } else {
                            alert('{{ __('messages.something_went_wrong') }}');
                        }
                    }
                });
            });
        });
    </script>
@endpush
