@php
    if (!isset($taxes)) {
        $taxes = \Modules\Tax\Models\Tax::where('status', 1)
            ->where('created_by', session('current_vendor_id'))
            ->where(function ($query) {
                $query->where('module_type', 'services')->orWhereNull('module_type');
            })
            ->get();
    }
    $serviceSubtotal = $booking->booking_service->sum('service_price');
    $productSubtotal = isset($booking->products) ? collect($booking->products)->sum('discounted_price') : 0;
    $packageSubtotal = isset($booking->packages) ? collect($booking->packages)->sum('package_price') : 0;
    $subtotal = $serviceSubtotal + $productSubtotal * $booking->products->sum('product_qty') + $packageSubtotal;
    $totalTax = 0;
    if (isset($taxes) && $taxes->count() > 0 && $subtotal > 0) {
        foreach ($taxes as $taxItem) {
            if ($taxItem->type == 'fixed') {
                $taxAmount = $taxItem->value;
            } else {
                $taxAmount = ($subtotal * $taxItem->value) / 100;
            }
            $totalTax += $taxAmount;
        }
    }
    if (!isset($employeeReview)) {
        $employeeReview = null;
    }
    $latestTransaction = $booking->payment;

    $discountAmount = $latestTransaction->discount_amount ?? 0;

    $couponPercent = $latestTransaction->discount_percentage ?? 0;
    $status = strtolower($booking->status);
    $statusColor = match ($status) {
        'pending' => 'text-warning',
        'confirmed' => 'text-primary',
        'cancelled' => 'text-danger',
        'complete', 'completed' => 'text-success',
        default => 'text-secondary',
    };
@endphp

<div class="row mt-5">
    <div class="col-lg-8">
        <div class="mb-5">
            <div class="d-flex align-items-center justify-content-between gap-3 flex-wrap">
                <a href="{{ route('bookings') }}" class="text-body fw-medium">
                    <span class="d-flex align-items-center gap-1">
                        <i class="ph ph-caret-left"></i>
                        <span>{{ __('vendorwebsite.back') }}</span>
                    </span>
                </a>
                @php
                    $rawPaymentStatus = $booking->payment->payment_status ?? null;
                    $paymentStatus =
                        $rawPaymentStatus === 1 || $rawPaymentStatus === '1' || strtolower($rawPaymentStatus) === 'paid'
                            ? 'Paid'
                            : 'Unpaid';
                @endphp
                @if ($paymentStatus == 'Paid' && $booking->status == 'completed')
                    <a href="{{ route('booking.invoice.download', $booking->id) }}"
                        class="btn btn-primary">{{ __('vendorwebsite.download_invoice') }}</a>
                @endif
            </div>
        </div>
        <div class="d-flex align-items-center justify-content-between gap-3 flex-wrap">
            @if (empty($employeeReview) && $paymentStatus == 'Paid' && $booking->status == 'completed')
                <h5 class="mb-0 mt-0">{{ __('vendorwebsite.you_havent_rated_yet') }}</h5>
                <button class="fw-semibold letter-spacing-2-percent btn btn-link" data-bs-toggle="modal"
                    data-bs-target="#review-service">{{ __('vendorwebsite.rate_now') }}</button>
            @endif
        </div>

        <div class="mt-5">
            <div class="booking-details-box booking-details-box-20">
                <div class="d-flex align-items-center justify-content-between gap-3 flex-wrap">
                    <h5 class="flex-grow-1 mb-0">
                        {{ __('vendorwebsite.booking_id') }}
                    </h5>
                    <span class="flex-shrink-0 text-primary">#{{ $booking->id ?? '-' }}</span>
                </div>
            </div>
        </div>

        <div class="mt-5">
            <h5 class="mb-2 mt-0">{{ __('vendorwebsite.salon_information') }}</h5>
            <div class="booking-details-box booking-details-box-30">
                <div class="row">
                    <div class="col-lg-4 col-md-6">
                        <h6 class="mb-1 fw-normal font-size-14">{{ __('vendorwebsite.salon_name') }}</h6>
                        <span class="font-size-14">{{ $booking->branch->name ?? '-' }}</span>
                    </div>
                    @if (isset($booking->branch->address))
                        <div class="col-lg-4 col-md-6 mt-3 mt-md-0">
                            <h6 class="mb-1 fw-normal font-size-14">{{ __('vendorwebsite.address') }}</h6>
                            <span class="font-size-14">
                                {{ $booking->branch->address->address_line_1 ?? '-' }}
                                @if ($booking->branch->address->address_line_2)
                                    , {{ $booking->branch->address->address_line_2 }}
                                @endif
                                @if ($booking->branch->address->city_data)
                                    , {{ $booking->branch->address->city_data->name }}
                                @endif
                                @if ($booking->branch->address->state_data)
                                    , {{ $booking->branch->address->state_data->name }}
                                @endif
                                @if ($booking->branch->address->country_data)
                                    , {{ $booking->branch->address->country_data->name }}
                                @endif
                                @if ($booking->branch->address->postal_code)
                                    - {{ $booking->branch->address->postal_code }}
                                @endif
                            </span>
                        </div>
                    @endif
                    <div class="col-lg-4 col-md-12 mt-3 mt-lg-0">
                        <h6 class="mb-1 fw-normal font-size-14">{{ __('vendorwebsite.phone') }}</h6>
                        <span class="font-size-14">{{ $booking->branch->contact_number ?? '-' }}</span>
                    </div>
                </div>
            </div>
        </div>

        <div class="mt-5">
            <h5 class="mb-2 mt-0">{{ __('vendorwebsite.booking_information') }}</h5>
            <div class="booking-details-box booking-details-box-30">
                <div class="row">
                    <div class="col-lg-3 col-md-6">
                        <h6 class="mb-1 fw-normal font-size-14">{{ __('vendorwebsite.date_and_time') }}</h6>
                        <span
                            class="font-size-14">{{ formatVendorDateOrTime($booking->start_date_time, 'date') }}</span>
                        <span
                            class="font-size-14">{{ formatVendorDateOrTime($booking->start_date_time, 'time') }}</span>
                    </div>

                    <div class="col-lg-3 col-md-6 mt-3 mt-lg-0">
                        <h6 class="mb-1 fw-normal font-size-14">{{ __('vendorwebsite.specialist') }}</h6>
                        <span class="font-size-14">
                            {{ $booking->booking_service->first()->employee->full_name ?? '-' }}
                        </span>
                    </div>
                    <div class="col-lg-3 col-md-6 mt-3 mt-lg-0">
                        <h6 class="mb-1 fw-normal font-size-14">{{ __('vendorwebsite.booking_status') }}</h6>
                        <span
                            class="font-size-14 {{ $statusColor }}">{{ ucfirst(str_replace('_', ' ', $booking->status)) }}</span>
                    </div>
                    <div class="col-lg-3 col-md-6 mt-3 mt-lg-0">
                        <h6 class="mb-1 fw-normal font-size-14">{{ __('vendorwebsite.payment_status') }}</h6>
                        @php
                            $rawPaymentStatus = $booking->payment->payment_status ?? null;
                            $paymentStatus =
                                $rawPaymentStatus === 1 ||
                                $rawPaymentStatus === '1' ||
                                strtolower($rawPaymentStatus) === 'paid'
                                    ? 'Paid'
                                    : 'Unpaid';
                            $paymentColor = $paymentStatus === 'Paid' ? 'text-success' : 'text-danger';
                        @endphp
                        <span class="font-size-14 {{ $paymentColor }}">{{ $paymentStatus }}</span>
                    </div>
                </div>
            </div>
        </div>

        <div class="mt-5">
            <h5 class="mb-2 mt-0">{{ __('vendorwebsite.services') }}</h5>
            <div class="booking-details-box booking-details-box-30">
                <ul class="list-inline m-0 p-0">
                    @foreach ($booking->booking_service as $service)
                        <li class="mb-2 pb-1 border-bottom">
                            <span class="d-flex align-items-center justify-content-between gap-3">
                                <span class="d-flex align-items-center gap-2 flex-grow-1">
                                    @if ($service->service && $service->service->feature_image)
                                        <img src="{{ asset($service->service->feature_image) }}"
                                            alt="{{ $service->service->name ?? 'Service' }}" class="rounded"
                                            style="width: 40px; height: 40px; object-fit: cover;">
                                    @endif
                                    <span class="font-size-14">{{ $service->service->name ?? '-' }}</span>
                                </span>
                                <span
                                    class="flex-shrink-0 font-size-14 heading-color">{{ \Currency::vendorCurrencyFormate($service->service_price ?? 0) }}</span>
                            </span>
                        </li>
                    @endforeach
                </ul>
            </div>
        </div>

        @if (!empty($booking->packages) && count($booking->packages) > 0)
            <div class="mt-5">
                <h5 class="mb-2 mt-0">{{ __('vendorwebsite.package_details') }}</h5>
                <div class="booking-details-box booking-details-box-30">
                    @foreach ($booking->packages as $package)
                        <div
                            class="d-flex align-items-center justify-content-between gap-2 flex-wrap mb-3 pb-3 border-bottom">
                            <span class="flex-grow-1 font-size-14">{{ $package['name'] ?? '-' }}:</span>
                            <span
                                class="flex-shrink-0 font-size-14 heading-color">{{ \Currency::vendorCurrencyFormate($package['package_price'] ?? 0) }}</span>
                        </div>
                        <h6 class="mb-3 font-size-14">{{ __('vendorwebsite.your_booked_services') }}</h6>
                        <ul class="list-inline m-0 p-0">
                            @foreach ($package['services'] ?? [] as $pkgService)
                                <li>
                                    <span
                                        class="d-flex align-items-sm-baseline justify-content-between gap-2 font-size-14 flex-sm-row flex-column">
                                        <span
                                            class="d-flex align-items-center flex-wrap row-gap-1 column-gap-3 flex-grow-1">
                                            <i class="ph ph-arrow-right"></i>
                                            <span>{{ $pkgService['service_name'] ?? '-' }} - <span
                                                    class="heading-color">{{ $pkgService['duration'] ?? '-' }}
                                                    mins</span></span>
                                            @if (isset($pkgService['remaining']))
                                                <span class="text-primary">(remaining -
                                                    {{ $pkgService['remaining'] }})</span>
                                            @endif
                                        </span>
                                        <span class="flex-shrink-0 d-flex align-items-center gap-2">
                                            <span>Qty:</span>
                                            <span class="heading-color">{{ $pkgService['qty'] ?? '1' }}</span>
                                        </span>
                                    </span>
                                </li>
                            @endforeach
                        </ul>
                    @endforeach
                </div>
            </div>
        @endif

        <div class="mt-5">
            <h5 class="mb-2 mt-0">{{ __('vendorwebsite.payment_mode') }}</h5>
            <div class="payments-container bg-gray-800 rounded mt-3">
                <div class="d-flex align-items-center gap-2">
                    @php
                        // Use transaction_type from latest bookingTransaction as payment method
                        $paymentMethod = '-';
                        $paymentIcon = 'stripe.svg';
                        if ($booking->payment) {
                            $latestTransaction = $booking->payment;
                            $paymentMethod = $latestTransaction->transaction_type ?? '-';
                        }
                        switch (strtolower($paymentMethod)) {
                            case 'stripe':
                                $paymentIcon = 'stripe.svg';
                                break;
                            case 'cash':
                                $paymentIcon = 'cash.svg';
                                break;
                            case 'razorpay':
                                $paymentIcon = 'razorpay.svg';
                                break;
                            case 'paystack':
                                $paymentIcon = 'paystack.svg';
                                break;
                            case 'paypal':
                                $paymentIcon = 'paypal.svg';
                                break;
                            case 'flutterwave':
                                $paymentIcon = 'flutterwave.svg';
                                break;
                            default:
                                $paymentIcon = 'stripe.svg';
                                break;
                        }
                    @endphp
                    <img src="{{ asset('img/vendorwebsite/' . $paymentIcon) }}" alt="{{ $paymentMethod }}"
                        class="flex-shrink-0 avatar avatar-18">
                    <span
                        class="flex-shrink-0 font-size-14 fw-medium heading-color">{{ ucfirst($paymentMethod) }}</span>
                </div>
            </div>
        </div>

        {{-- Review Card Section (after Payment Mode) --}}
        <div id="review-section">
            @if (!empty($employeeReview))
                <div class="mt-5">
                    <div class="d-flex align-items- center justify-content-between gap-3 mb-2">
                        <h5 class="mb-0">{{ __('vendorwebsite.rate_our_services') }}</h5>
                        <div class="d-flex align-items-center gap-lg-4 gap-2 flex-shrink-0">
                            <button class="btn btn-link text-success border-0 fs-5 edit-review-btn"
                                data-bs-toggle="modal" data-bs-target="#review-service"><i
                                    class="ph ph-pencil-simple-line align-middle"></i></button>
                            <button class="btn btn-link border-0 fs-5 delete-review-btn"><i
                                    class="ph ph-trash align-middle"></i></button>
                        </div>
                    </div>
                    <div class="row gy-5">
                        <div class="col-12">
                            <div class="review-card">
                                <div class="review-card-user-info">
                                    <div class="d-flex align-items-sm-center flex-sm-row flex-column gap-3">
                                        <div class="flex-shrink-0">
                                            <img src="{{ auth()->user()->profile_image ?? asset('img/vendorwebsite/rating-user.png') }}"
                                                alt="review-card-user-image" class="review-card-user-image">
                                        </div>
                                        <div class="flex-grow-1">
                                            <span class="d-flex align-items-baseline justify-content-between gap-2">
                                                <span
                                                    class="d-inline-flex align-items-center gap-1 rouneded bg-white rounded py-1 px-2 lh-base border-radius">
                                                    <span class="text-warning">
                                                        <i class="ph-fill ph-star"></i>
                                                    </span>
                                                    <span class="fw-medium font-size-14 text-secondary"
                                                        id="review-rating-display">{{ $employeeReview->rating ?? '-' }}</span>
                                                </span>
                                                <div class="flex-shrink-0 font-size-14 fw-medium"
                                                    id="review-date-display">


                                                    {{ $employeeReview->created_at ? formatVendorDateOrTime($employeeReview->created_at, 'date') : '-' }}
                                                </div>
                                            </span>
                                            <h6 class="mt-1 mb-0 rating-card-user-title" id="review-user-display">
                                                {{ auth()->user()->full_name ?? '-' }}</h6>
                                        </div>
                                    </div>
                                </div>
                                <div class="review-card-content mt-4">
                                    <p class="m-0" id="review-text-display">
                                        {{ $employeeReview->review_msg ?? '' }}
                                    </p>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            @else
                <!-- <div class="mt-5 text-center">
                <button class="fw-semibold letter-spacing-2-percent btn btn-link" id="rate-now-btn" data-bs-toggle="modal" data-bs-target="#review-service">Rate Now</button>
            </div> -->
            @endif
        </div>
    </div>
    <div class="col-lg-4 mt-lg-0 mt-5">
        <h5 class="mb-2">{{ __('vendorwebsite.payment_details') }}</h5>
        <div class="payment-section">
            <div class="payment-summary">
                {{-- Services --}}
                @foreach ($booking->booking_service as $service)
                    <div class="d-flex justify-content-between align-items-center mb-1 price-item">
                        <span class="font-size-14">{{ $service->service->name ?? '-' }}</span>
                        <span class="font-size-14 fw-medium heading-color">
                            {{ \Currency::vendorCurrencyFormate($service->service_price ?? 0) }}
                        </span>
                    </div>
                @endforeach
                {{-- Products --}}
                @if ($booking->products && count($booking->products))
                    @foreach ($booking->products as $product)
                        <div class="d-flex justify-content-between align-items-center mb-1 price-item">
                            <span class="font-size-14">{{ $product->product->name ?? '-' }}
                                (x{{ $product->product_qty ?? 1 }})
                            </span>
                            <span class="font-size-14 fw-medium heading-color">
                                {{ \Currency::vendorCurrencyFormate(($product->discounted_price ?? $product->product_price * $product->product_qty) * $product->product_qty) }}
                            </span>
                        </div>
                    @endforeach
                @endif
                {{-- Packages --}}
                @if ($booking->packages && count($booking->packages))
                    @foreach ($booking->packages as $package)
                        <div class="d-flex justify-content-between align-items-center mb-1 price-item">
                            <span class="font-size-14">{{ $package['name'] ?? '-' }}</span>
                            <span class="font-size-14 fw-medium heading-color">
                                {{ \Currency::vendorCurrencyFormate($package['package_price'] ?? 0) }}
                            </span>
                        </div>
                    @endforeach
                @endif

                @if ($discountAmount > 0)
                    <div class="d-flex justify-content-between align-items-center mb-1 price-item">
                        <span class="font-size-14">Discount @if ($couponPercent > 0)
                                ({{ $couponPercent }}%)
                            @endif
                        </span>
                        <span class="font-size-14 fw-medium text-success">
                            -{{ \Currency::vendorCurrencyFormate($discountAmount) }}
                        </span>
                    </div>
                @endif
                <hr class="line-divider" />
                {{-- Subtotal --}}
                <div class="d-flex justify-content-between align-items-center mb-1 price-item">
                    <span class="font-size-14">{{ __('vendorwebsite.subtotal') }}</span>
                    <span class="font-size-14 fw-medium heading-color">
                        {{ \Currency::vendorCurrencyFormate($subtotal - $discountAmount) }}
                    </span>
                </div>
                {{-- Discount (from booking_transaction) --}}

                {{-- Tax (visible row + collapsible breakdown) --}}
                @php
                    // Build a normalized tax breakdown regardless of source (payment record vs active vendor taxes)
                    $taxSum = 0;
                    $taxBreakdown = [];

                    $paymentTaxes = $booking->payment->tax_percentage ?? null;

                    if (!empty($paymentTaxes) && is_array($paymentTaxes) && $subtotal > 0) {
                        foreach ($paymentTaxes as $taxItem) {
                            if (($taxItem['type'] ?? '') === 'fixed') {
                                $taxAmount = $taxItem['tax_amount'] ?? ($taxItem['amount'] ?? 0);
                                $percent = null;
                            } else {
                                $percent = (float) ($taxItem['percent'] ?? 0);
                                $taxAmount = (($subtotal - $discountAmount) * $percent) / 100;
                            }
                            $taxSum += $taxAmount;
                            $taxBreakdown[] = [
                                'title' => $taxItem['title'] ?? $taxItem['name'] ?? __('vendorwebsite.tax'),
                                'type' => $taxItem['type'] ?? 'percent',
                                'percent' => $percent,
                                'amount' => $taxAmount,
                            ];
                        }
                    } else {
                        // Fallback for admin-created bookings where tax wasn't persisted on payment
                        $vendorTaxes = \Modules\Tax\Models\Tax::where(function ($query) {
                                $query->where('module_type', 'services')->orWhereNull('module_type');
                            })
                            ->where('status', 1)
                            ->where('created_by', session('current_vendor_id'))
                            ->get();

                        if ($vendorTaxes && $vendorTaxes->count() > 0 && $subtotal > 0) {
                            foreach ($vendorTaxes as $taxItem) {
                                if ($taxItem->type === 'fixed') {
                                    $taxAmount = (float) $taxItem->value;
                                    $percent = null;
                                } else {
                                    $percent = (float) $taxItem->value;
                                    $taxAmount = (($subtotal - $discountAmount) * $percent) / 100;
                                }
                                $taxSum += $taxAmount;
                                $taxBreakdown[] = [
                                    'title' => $taxItem->title,
                                    'type' => $taxItem->type,
                                    'percent' => $percent,
                                    'amount' => $taxAmount,
                                ];
                            }
                        }
                    }
                @endphp



                @if ($taxSum > 0)
                    <div class="d-flex justify-content-between align-items-center mb-1 price-item">
                        <span class="font-size-14">{{ __('vendorwebsite.tax') }}</span>
                        <div class="d-flex justify-content-between align-items-center mb-1 price-item text-decoration-none taxDetails"
                            data-bs-toggle="collapse" href="#taxDetailsBreakdown" role="button"
                            aria-expanded="false" aria-controls="taxDetailsBreakdown">
                            <i class="ph ph-caret-down rotate-icon tax1"></i>
                            <span class="font-size-14 fw-medium text-danger">
                                {{ \Currency::vendorCurrencyFormate($taxSum) }}
                            </span>
                        </div>
                    </div>
                    <div class="collapse mt-2 mb-2" id="taxDetailsBreakdown">
                        <div class="text-calculate card py-2 px-3" id="tax-breakdown">
                            @foreach ($taxBreakdown as $index => $taxItem)
                                <div class="d-flex justify-content-between align-items-center {{ $index < count($taxBreakdown) - 1 ? 'mb-1' : '' }}">
                                    <span class="font-size-12">{{ $taxItem['title'] }}
                                        {{ ($taxItem['type'] === 'fixed' || is_null($taxItem['percent'])) ? '' : '(' . $taxItem['percent'] . '%)' }}</span>
                                    <span class="font-size-12 text-danger fw-medium">
                                        {{ \Currency::vendorCurrencyFormate($taxItem['amount']) }}
                                    </span>
                                </div>
                            @endforeach
                        </div>
                    </div>
                @endif

                <hr class="line-divider" />
                @php
                    $tipAmount = $booking->payment->tip_amount ?? 0;
                @endphp

                @if ($tipAmount > 0)
                    <div class="d-flex justify-content-between align-items-center mb-1 price-item">
                        <span class="font-size-14">{{ __('vendorwebsite.tip_amount') }}</span>
                        <span class="font-size-14 fw-medium heading-color">
                            {{ \Currency::vendorCurrencyFormate($tipAmount) }}
                        </span>
                    </div>
                @endif
                <hr class="line-divider" />
                {{-- Total --}}
                <div class="d-flex justify-content-between align-items-center">
                    <span>{{ __('vendorwebsite.total') }}</span>
                    <span class="total-value fw-semibold text-primary">
                        @php
                            $finalTotal = $subtotal + $taxSum - $discountAmount + $tipAmount;
                        @endphp
                        {{ \Currency::vendorCurrencyFormate($finalTotal) }}
                    </span>
                </div>
            </div>
        </div>
    </div>
</div>

<!-- Review Modal -->
<div class="modal fade rating-modal" id="review-service" tabindex="-1" aria-modal="true" role="dialog">
    <div class="modal-dialog modal-dialog-centered modal-md">
        <div class="modal-content bg-gray-900 rounded">
            <div class="modal-body modal-body-inner rate-us-modal">
                <form id="reviewForm">
                    <div class="rate-box">
                        <h5 class="font-size-21-3 mb-0 text-center">{{ __('vendorwebsite.rate_our_service_now') }}
                        </h5>
                        <p class="mb-0 mt-2 font-size-14 text-center">
                            {{ __('vendorwebsite.your_honest_feedback_helps_us_improve_and_serve_you_better') }}</p>
                        <div class="mt-5 pt-2">
                            <div class="form-group mb-4">
                                <label for=""
                                    class="form-label">{{ __('vendorwebsite.your_rating') }}</label>
                                {{-- <div class="bg-gray-800 form-control">
                                    <ul
                                        class="list-inline m-0 p-0 d-flex align-items-center justify-content-start gap-1 rating-list">
                                        @for ($i = 1; $i <= 5; $i++)
                                            <li data-value="{{ $i }}"
                                                class="star{{ $employeeReview && $employeeReview->rating >= $i ? ' selected' : '' }}">
                                                <span class="text-warning icon">
                                                    <i class="ph-fill ph-star icon-fill"></i>
                                                    <i class="ph ph-star icon-normal"></i>
                                                </span>
                                            </li>
                                        @endfor
                                    </ul>
                                </div> --}}

                                <div class="bg-gray-800 form-control">
                                    <div id="starRating"
                                        class="list-inline m-0 p-0 d-flex align-items-center justify-content-start gap-1 rating-list"
                                        style="font-size:2rem; text-align:center;">
                                        @for ($i = 1; $i <= 5; $i++)
                                            <span data-value="{{ $i }}" class="star"
                                                style="cursor: pointer; color: #ddd;">
                                                &#9733;
                                            </span>
                                        @endfor
                                    </div>
                                </div>

                            </div>
                            <div class="form-group">
                                <label for=""
                                    class="form-label">{{ __('vendorwebsite.enter_your_feedback') }}</label>
                                <textarea class="form-control bg-gray-800"
                                    placeholder="{{ __('vendorwebsite.Share_your_experience!_Your_feedback_helps_others_make_informed_decisions_about_their_healthcare') }}"
                                    rows="3" id="reviewTextarea">{{ $employeeReview->review_msg ?? '' }}</textarea>
                            </div>
                            <div
                                class="mt-5 pt-3 d-flex align-items-center justify-content-center row-gap-3 column-gap-4 flex-wrap">
                                <button type="button" class="btn btn-secondary"
                                    data-bs-dismiss="modal">{{ __('vendorwebsite.cancel') }}</button>
                                <button type="submit"
                                    class="btn btn-primary">{{ __('vendorwebsite.submit') }}</button>
                            </div>
                        </div>
                    </div>
                </form>
            </div>
        </div>
    </div>
</div>

{{-- Interactive Star Rating and Review AJAX Logic --}}
{{-- <script>
    document.addEventListener('DOMContentLoaded', function() {
        // Star selection logic
        const stars = document.querySelectorAll('.rating-list li');
        let selectedRating = {{ isset($employeeReview) && $employeeReview ? $employeeReview->rating : 0 }};

        function updateStars(rating) {
            stars.forEach((star, idx) => {
                if (idx < rating) {
                    star.classList.add('selected');
                } else {
                    star.classList.remove('selected');
                }
            });
        }
        stars.forEach((star, idx) => {
            star.addEventListener('mouseenter', () => updateStars(idx + 1));
            star.addEventListener('mouseleave', () => updateStars(selectedRating));
            star.addEventListener('click', () => {
                selectedRating = idx + 1;
                updateStars(selectedRating);
            });
        });
        updateStars(selectedRating);

        // Review form submit (AJAX)
        const reviewForm = document.getElementById('reviewForm');
        reviewForm.addEventListener('submit', function(e) {
            e.preventDefault();

            // Frontend validation
            const review = document.getElementById('reviewTextarea').value;
            const bookingId = '{{ $booking->id }}';

            if (selectedRating === 0) {

                if (typeof toastr !== 'undefined') {
                    toastr.error('{{ __('vendorwebsite.please_select_a_rating') }}');
                } else {
                    Swal.fire({
                        icon: 'error',
                        title: '{{ __('vendorwebsite.error') }}',
                        text: '{{ __('vendorwebsite.please_select_a_rating') }}',
                        confirmButtonText: '{{ __('vendorwebsite.ok') }}',
                        customClass: {
                            confirmButton: 'btn btn-primary'
                        },
                        buttonsStyling: false,
                    });
                }
                return;
            }

            fetch("{{ route('review.submit') }}", {
                    method: 'POST',
                    headers: {
                        'Content-Type': 'application/json',
                        'X-CSRF-TOKEN': '{{ csrf_token() }}'
                    },
                    body: JSON.stringify({
                        rating: selectedRating,
                        review,
                        booking_id: bookingId
                    })
                })
                .then(res => res.json())
                .then(data => {
                    if (data.success) {

                        if (typeof toastr !== 'undefined') {
                            toastr.success(
                                '{{ __('vendorwebsite.review_submitted_successfully') }}');

                            setTimeout(() => {
                                location.reload();
                            }, 1500);
                        } else {
                            sessionStorage.setItem('reviewSuccess', '1');
                            location.reload();
                        }
                    } else {
                        if (typeof toastr !== 'undefined') {
                            toastr.error(data.error ||
                                '{{ __('vendorwebsite.something_went_wrong_please_try_again') }}'
                            );
                        } else {
                            Swal.fire({
                                icon: 'error',
                                title: '{{ __('vendorwebsite.failed_to_submit_review') }}',
                                text: data.error ||
                                    '{{ __('vendorwebsite.something_went_wrong_please_try_again') }}',
                                confirmButtonText: '{{ __('vendorwebsite.ok') }}',
                                customClass: {
                                    confirmButton: 'btn btn-primary'
                                },
                                buttonsStyling: false,
                            });
                        }
                    }
                })
                .catch(err => {
                    if (typeof toastr !== 'undefined') {
                        toastr.error(
                            '{{ __('vendorwebsite.a_network_or_server_error_occured') }}');
                    } else {
                        Swal.fire({
                            icon: 'error',
                            title: '{{ __('vendorwebsite.failed_to_submit_review') }}',
                            text: '{{ __('vendorwebsite.a_network_or_server_error_occured') }}',
                            confirmButtonText: '{{ __('vendorwebsite.ok') }}',
                            customClass: {
                                confirmButton: 'btn btn-primary'
                            },
                            buttonsStyling: false,
                        });
                    }
                });
        });

        // Show SweetAlert2 success after reload if review was just submitted
        if (sessionStorage.getItem('reviewSuccess') === '1') {
            Swal.fire({
                icon: 'success',
                title: 'Thank you!',
                text: 'Your review has been submitted successfully.',
                confirmButtonText: '{{ __('vendorwebsite.ok') }}',
                buttonsStyling: false, // ✅ disable default styles
                customClass: {
                    confirmButton: 'btn btn-primary' // ✅ your primary button
                }
            });
            sessionStorage.removeItem('reviewSuccess');
        }
        // Edit review button
        const editBtn = document.querySelector('.edit-review-btn');
        if (editBtn) {
            editBtn.addEventListener('click', function() {
                // Pre-fill modal with existing review
                document.getElementById('reviewTextarea').value = document.getElementById(
                    'review-text-display').innerText;
                selectedRating = parseInt(document.getElementById('review-rating-display').innerText) ||
                    0;
                updateStars(selectedRating);
            });
        }
        // Delete review button
        const deleteBtn = document.querySelector('.delete-review-btn');
        if (deleteBtn) {
            deleteBtn.addEventListener('click', function() {
                Swal.fire({
                    title: '{{ __('vendorwebsite.are_you_sure_you_want_to_delete_your_review') }}',
                    icon: 'warning',
                    showCancelButton: true,
                    cancelButtonText: 'Cancel',
                    confirmButtonText: 'Yes, delete it!',
                    reverseButtons: true,
                    customClass: {
                        cancelButton: 'btn btn-secondary',
                        confirmButton: 'btn btn-primary',
                    },
                }).then((result) => {
                    if (result.isConfirmed) {
                        fetch("{{ route('review.delete', ['vendor_slug' => request()->route('vendor_slug')]) }}", {
                                method: 'POST',
                                headers: {
                                    'Content-Type': 'application/json',
                                    'X-CSRF-TOKEN': '{{ csrf_token() }}'
                                },
                                body: JSON.stringify({
                                    booking_id: '{{ $booking->id }}'
                                })
                            })
                            .then(res => res.json())
                            .then(data => {
                                if (data.success) {
                                    location.reload();
                                } else {
                                    Swal.fire({
                                        icon: 'error',
                                        title: '{{ __('vendorwebsite.failed_to_delete_review') }}',
                                        text: data.error ||
                                            '{{ __('vendorwebsite.something_went_wrong_please_try_again') }}',
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
                                    title: '{{ __('vendorwebsite.failed_to_delete_review') }}',
                                    text: '{{ __('vendorwebsite.a_network_or_server_error_occured') }}',
                                    confirmButtonText: '{{ __('vendorwebsite.ok') }}',
                                    customClass: {
                                        confirmButton: 'btn btn-primary'
                                    },
                                    buttonsStyling: false,
                                });
                            });
                    }
                });
            });
        }
    });
</script> --}}

<script>
    document.addEventListener('DOMContentLoaded', function() {
        // Star selection logic
        const stars = document.querySelectorAll('.rating-list .star');
        let selectedRating = {{ isset($employeeReview) && $employeeReview ? $employeeReview->rating : 0 }};
        const starColorSelected = '#ffcc00'; // Yellow color for selected stars
        const starColorDefault = '#ddd'; // Light gray for unselected stars

        // Function to update star colors based on rating
        function updateStars(rating) {
            stars.forEach((star, idx) => {
                if (idx < rating) {
                    star.style.color = starColorSelected; // Set color to yellow for selected stars
                } else {
                    star.style.color = starColorDefault; // Set color to light gray for unselected stars
                }
            });
        }

        // Add event listeners to each star
        stars.forEach((star, idx) => {
            star.addEventListener('mouseenter', () => updateStars(idx + 1)); // Highlight on hover
            star.addEventListener('mouseleave', () => updateStars(
                selectedRating)); // Reset to previous selection on mouse leave
            star.addEventListener('click', () => {
                selectedRating = idx + 1;
                updateStars(selectedRating); // Update stars based on click
            });
        });

        // Initialize stars with the current selected rating
        updateStars(selectedRating);

        // Review form submit (AJAX)
        const reviewForm = document.getElementById('reviewForm');
        reviewForm.addEventListener('submit', function(e) {
            e.preventDefault();

            // Frontend validation
            const review = document.getElementById('reviewTextarea').value;
            const bookingId = '{{ $booking->id }}';

            if (selectedRating === 0) {
                if (typeof toastr !== 'undefined') {
                    toastr.error('{{ __('vendorwebsite.please_select_a_rating') }}');
                } else {
                    Swal.fire({
                        icon: 'error',
                        title: '{{ __('vendorwebsite.error') }}',
                        text: '{{ __('vendorwebsite.please_select_a_rating') }}',
                        confirmButtonText: '{{ __('vendorwebsite.ok') }}',
                        customClass: {
                            confirmButton: 'btn btn-primary'
                        },
                        buttonsStyling: false,
                    });
                }
                return;
            }

            fetch("{{ route('review.submit') }}", {
                    method: 'POST',
                    headers: {
                        'Content-Type': 'application/json',
                        'X-CSRF-TOKEN': '{{ csrf_token() }}'
                    },
                    body: JSON.stringify({
                        rating: selectedRating,
                        review,
                        booking_id: bookingId
                    })
                })
                .then(res => res.json())
                .then(data => {
                    if (data.success) {
                        if (typeof toastr !== 'undefined') {
                            toastr.success(
                                '{{ __('vendorwebsite.review_submitted_successfully') }}');
                            setTimeout(() => {
                                location.reload();
                            }, 1500);
                        } else {
                            sessionStorage.setItem('reviewSuccess', '1');
                            location.reload();
                        }
                    } else {
                        if (typeof toastr !== 'undefined') {
                            toastr.error(data.error ||
                                '{{ __('vendorwebsite.something_went_wrong_please_try_again') }}'
                            );
                        } else {
                            Swal.fire({
                                icon: 'error',
                                title: '{{ __('vendorwebsite.failed_to_submit_review') }}',
                                text: data.error ||
                                    '{{ __('vendorwebsite.something_went_wrong_please_try_again') }}',
                                confirmButtonText: '{{ __('vendorwebsite.ok') }}',
                                customClass: {
                                    confirmButton: 'btn btn-primary'
                                },
                                buttonsStyling: false,
                            });
                        }
                    }
                })
                .catch(err => {
                    if (typeof toastr !== 'undefined') {
                        toastr.error(
                            '{{ __('vendorwebsite.a_network_or_server_error_occured') }}');
                    } else {
                        Swal.fire({
                            icon: 'error',
                            title: '{{ __('vendorwebsite.failed_to_submit_review') }}',
                            text: '{{ __('vendorwebsite.a_network_or_server_error_occured') }}',
                            confirmButtonText: '{{ __('vendorwebsite.ok') }}',
                            customClass: {
                                confirmButton: 'btn btn-primary'
                            },
                            buttonsStyling: false,
                        });
                    }
                });
        });

        // Show SweetAlert2 success after reload if review was just submitted
        if (sessionStorage.getItem('reviewSuccess') === '1') {
            Swal.fire({
                icon: 'success',
                title: 'Thank you!',
                text: 'Your review has been submitted successfully.',
                confirmButtonText: '{{ __('vendorwebsite.ok') }}',
                buttonsStyling: false, // ✅ disable default styles
                customClass: {
                    confirmButton: 'btn btn-primary' // ✅ your primary button
                }
            });
            sessionStorage.removeItem('reviewSuccess');
        }

        // Edit review button
        const editBtn = document.querySelector('.edit-review-btn');
        if (editBtn) {
            editBtn.addEventListener('click', function() {
                // Pre-fill modal with existing review
                document.getElementById('reviewTextarea').value = document.getElementById(
                    'review-text-display').innerText;
                selectedRating = parseInt(document.getElementById('review-rating-display').innerText) ||
                    0;
                updateStars(selectedRating);
            });
        }

        // Delete review button
        const deleteBtn = document.querySelector('.delete-review-btn');
        if (deleteBtn) {
            deleteBtn.addEventListener('click', function() {
                Swal.fire({
                    title: '{{ __('vendorwebsite.are_you_sure_you_want_to_delete_your_review') }}',
                    icon: 'warning',
                    showCancelButton: true,
                    cancelButtonText: 'Cancel',
                    confirmButtonText: 'Yes, delete it!',
                    reverseButtons: true,
                    customClass: {
                        cancelButton: 'btn btn-secondary',
                        confirmButton: 'btn btn-primary',
                    },
                }).then((result) => {
                    if (result.isConfirmed) {
                        fetch("{{ route('review.delete', ['vendor_slug' => request()->route('vendor_slug')]) }}", {
                                method: 'POST',
                                headers: {
                                    'Content-Type': 'application/json',
                                    'X-CSRF-TOKEN': '{{ csrf_token() }}'
                                },
                                body: JSON.stringify({
                                    booking_id: '{{ $booking->id }}'
                                })
                            })
                            .then(res => res.json())
                            .then(data => {
                                if (data.success) {
                                    location.reload();
                                } else {
                                    Swal.fire({
                                        icon: 'error',
                                        title: '{{ __('vendorwebsite.failed_to_delete_review') }}',
                                        text: data.error ||
                                            '{{ __('vendorwebsite.something_went_wrong_please_try_again') }}',
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
                                    title: '{{ __('vendorwebsite.failed_to_delete_review') }}',
                                    text: '{{ __('vendorwebsite.a_network_or_server_error_occured') }}',
                                    confirmButtonText: '{{ __('vendorwebsite.ok') }}',
                                    customClass: {
                                        confirmButton: 'btn btn-primary'
                                    },
                                    buttonsStyling: false,
                                });
                            });
                    }
                });
            });
        }

        // Reset stars when modal is closed or cancel is clicked
        const modal = document.getElementById('review-service');
        const cancelButton = document.querySelector('.btn-secondary'); // Cancel button

        function resetStars() {
            updateStars(0); // Reset to no selection
        }

        // Reset stars when the modal is closed
        modal.addEventListener('hidden.bs.modal', resetStars);

        // Reset stars when cancel button is clicked
        if (cancelButton) {
            cancelButton.addEventListener('click', resetStars);
        }
    });
</script>
