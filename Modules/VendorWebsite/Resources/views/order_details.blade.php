@extends('vendorwebsite::layouts.master')

@section('content')
<style>
.review-section {
    min-width: 120px;
    text-align: right;
}

.rating-display i {
    font-size: 14px;
}

.rating-list li {
    cursor: pointer;
    transition: all 0.2s ease;
}

.rating-list li:hover {
    transform: scale(1.1);
}

.rating-list li.selected .icon-fill {
    display: inline !important;
}

.rating-list li.selected .icon-normal {
    display: none !important;
}

.rating-list li .icon-fill {
    display: none;
}

.rating-list li .icon-normal {
    display: inline;
}
</style>
    <x-breadcrumb title="Order Details" />
    <div class="order-details-section section-spacing-inner-pages">
        <div class="container">
            <div class="row gy-4">
                <div class="col-md-8">
                    <div class="d-flex align-items-center justify-content-between mb-3">
                        <div>
                            <i class="ph ph-caret-left align-middle icon-colo font-size-20"></i>
                            <a href="{{ route('myorder') }}"
                                class="btn btn-link text-body font-size-16">{{ __('vendorwebsite.back') }}</a>
                        </div>

                        <div class="d-flex align-items-center justify-content-between gap-3 mb-3">

                            @if ($order->payment_status == 'paid' && $order->delivery_status == 'delivered')
                                <a href="{{ route('invoice.download', $order->id) }}"
                                    class="btn btn-primary">{{ __('vendorwebsite.download_invoice') }}</a>
                            @endif



                        </div>

                    </div>

                    <div class="mt-5">
                        <div class="order-content d-flex align-items-center justify-content-between gap-2 flex-wrap">
                            <h6 class="mb-0">{{ __('vendorwebsite.order_id') }}</h6>
                            {{-- <a href="#" class="btn btn-link font-size-16">#{{ $order->id }}</a> --}}
                            <p class="font-size-20 text-primary">#{{ $order->id }}</p>
                        </div>
                    </div>

                    <div class="mt-5">
                        <h5>{{ __('vendorwebsite.order_details') }}</h5>
                        <div class="order-content">
                            <div class="row">
                                <div class="col-lg-4 col-md-6">
                                    <h6 class="mb-1">{{ __('vendorwebsite.date_and_time') }}</h6>
                                    <span
                                        class="font-size-14">{{ $order->created_at ? formatVendorDateOrTime($order->created_at, 'date') : '-' }}</span>
                                </div>
                                <div class="col-lg-4 col-md-6 mt-3 mt-md-0">
                                    <h6 class="mb-1">{{ __('vendorwebsite.payment') }}</h6>
                                    @php

                                        $orderPaymentStatus = $order->payment_status;
                                        if ($orderPaymentStatus) {
                                            $summaryStatus = ucfirst($orderPaymentStatus);
                                            $summaryClass =
                                                strtolower($orderPaymentStatus) === 'paid'
                                                    ? 'text-success'
                                                    : (strtolower($orderPaymentStatus) === 'pending'
                                                        ? 'text-warning'
                                                        : 'text-danger');
                                        } elseif ($statuses->isEmpty()) {
                                            $summaryStatus = 'N/A';
                                            $summaryClass = 'text-danger';
                                        } elseif ($statuses->count() === 1) {
                                            $summaryStatus = ucfirst($statuses->first());
                                            $summaryClass =
                                                strtolower($statuses->first()) === 'paid'
                                                    ? 'text-success'
                                                    : (strtolower($statuses->first()) === 'pending'
                                                        ? 'text-warning'
                                                        : 'text-danger');
                                        } else {
                                            $summaryStatus = 'Partially Paid';
                                            $summaryClass = 'text-warning';
                                        }
                                    @endphp
                                    <span class="font-size-14 {{ $summaryClass }}">{{ $summaryStatus }}</span>
                                </div>
                                <div class="col-lg-4 col-md-12 mt-3 mt-lg-0">
                                    <h6 class="mb-1">{{ __('vendorwebsite.delivery_status') }}</h6>
                                    @php
                                        $status = strtolower($order->delivery_status);
                                        $statusColor = match ($status) {
                                            'pending' => 'text-warning',
                                            'confirmed' => 'text-primary',
                                            'order_placed' => 'text-primary',
                                            'cancelled' => 'text-danger',
                                            'complete', 'completed', 'delivered' => 'text-success',
                                            'processing' => 'text-info',
                                            default => 'text-body',
                                        };
                                    @endphp
                                    <span
                                        class="font-size-14 {{ $statusColor }}">{{ ucfirst(str_replace('_', ' ', $order->delivery_status)) }}</span>
                                </div>
                            </div>
                        </div>
                    </div>

                    <div class="mt-5">


                        @foreach ($order->orderItems as $orderItem)
                            @php
                                $product = $orderItem->product_variation->product;
                                $image =
                                    $product && $product->media && $product->media->count() > 0
                                        ? $product->media->first()->getFullUrl()
                                        : asset('dummy-images/dummy.png');
                                $paymentStatus = strtolower($product->payment_status ?? $order->payment_status);
                                $paymentStatusClass =
                                    $paymentStatus === 'paid'
                                        ? 'text-success'
                                        : ($paymentStatus === 'pending'
                                            ? 'text-warning'
                                            : 'text-danger');
                            @endphp
                            <div class="order-content order-product-info mb-3">
                                <div class="d-flex align-items-center justify-content-between column-gap-4 row-gap-3 flex-sm-nowrap flex-wrap">
                                <div class="d-flex align-items-center column-gap-4 row-gap-3 flex-sm-nowrap flex-wrap">
                                    <div class="order-product-images">
                                        <img src="{{ $image }}" class="avatar avatar-70 object-cover"
                                            alt="{{ $product->name ?? 'Product Image' }}">
                                    </div>
                                    <div>
                                        <h5>{{ $product->name ?? 'Product Name' }}</h5>
                                        <div class="d-flex align-items-center column-gap-5 row-gap-2 flex-wrap">
                                            <div>
                                                <span class="font-size-14">{{ __('vendorwebsite.price') }}</span>
                                                <span
                                                    class="text-primary fw-medium">{{ \Currency::vendorCurrencyFormate($orderItem->unit_price ?? 0) }}</span>
                                            </div>
                                            <div>
                                                <span class="font-size-14">{{ __('vendorwebsite.quantity') }}</span>
                                                <span class="heading-color fw-medium">{{ $orderItem->qty ?? 1 }}</span>
                                            </div>
                                        </div>
                                    </div>
                                    </div>

                                    {{-- Review Button --}}
                                    @php
                                        $existingReview = $orderItem->review;
                                        $canReview = ($order->delivery_status == 'delivered' && $order->payment_status == 'paid');

                                        // Debug info - remove this after testing
                                        // $debugInfo = "Delivery: {$order->delivery_status}, Payment: {$order->payment_status}, Can Review: " . ($canReview ? 'Yes' : 'No') . ", Has Review: " . ($existingReview ? 'Yes' : 'No');
                                    @endphp


                                    @if($canReview)
                                        <div class="review-section">
                                            @if($existingReview)
                                                <div class="d-flex align-items-center gap-2">
                                                    <div class="rating-display">
                                                        @for($i = 1; $i <= 5; $i++)
                                                            <i class="ph {{ $i <= $existingReview->rating ? 'ph-fill ph-star text-warning' : 'ph ph-star text-muted' }}"></i>
                                                        @endfor
                                                    </div>
                                                    <span class="text-success font-size-14">{{ __('vendorwebsite.reviewed') }}</span>
                                                </div>
                                            @else
                                                <button type="button" class="btn btn-outline-primary btn-sm"
                                                        data-bs-toggle="modal"
                                                        data-bs-target="#review-product-modal"
                                                        data-product-id="{{ $product->id }}"
                                                        data-product-variation-id="{{ $orderItem->product_variation_id }}"
                                                        data-product-name="{{ $product->name }}">
                                                    <i class="ph ph-star"></i> {{ __('vendorwebsite.rate_us') }}
                                                </button>
                                            @endif
                                        </div>
                                    @endif
                                </div>
                            </div>
                        @endforeach
                    </div>

                    {{-- Reviews Section --}}
                    <div class="mt-5">
                        @if($existingReview)
                        <h5>{{ __('vendorwebsite.product_reviews') }}</h5>
                        @endif
                        @foreach ($order->orderItems as $orderItem)
                            @php
                                $product = $orderItem->product_variation->product;
                                $existingReview = $orderItem->review;
                            @endphp

                            @if($existingReview)
                                <div class="card mb-3">
                                    <div class="card-body">
                                        <div class="d-flex justify-content-between align-items-start mb-3">
                                            <div>
                                                <h6 class="mb-1">{{ $product->name }}</h6>
                                                <small class="text-muted"> {{ $existingReview->created_at->format('d/m/Y h:i A') }}</small>
                                                <div class="rating-display mb-2">
                                                    @for($i = 1; $i <= 5; $i++)
                                                        <i class="ph {{ $i <= $existingReview->rating ? 'ph-fill ph-star text-warning' : 'ph ph-star text-muted' }}"></i>
                                                    @endfor

                                                    <span class="ms-2 text-muted">{{ $existingReview->rating }}/5</span>
                                                </div>

                                            </div>
                                            <div class="btn-group btn-group-sm">
                                                <button type="button" class="btn btn-outline-primary btn-sm edit-review-btn"
                                                        data-bs-toggle="modal"
                                                        data-bs-target="#review-product-modal"
                                                        data-product-id="{{ $product->id }}"
                                                        data-product-variation-id="{{ $orderItem->product_variation_id }}"
                                                        data-product-name="{{ $product->name }}"
                                                        data-review-id="{{ $existingReview->id }}"
                                                        data-rating="{{ $existingReview->rating }}"
                                                        data-message="{{ $existingReview->review_msg }}">
                                                    <i class="ph ph-pencil"></i>
                                                </button>
                                                <button type="button" class="btn btn-outline-danger btn-sm delete-review-btn"
                                                        data-review-id="{{ $existingReview->id }}"
                                                        data-product-name="{{ $product->name }}">
                                                    <i class="ph ph-trash"></i>
                                                </button>
                                            </div>
                                        </div>
                                        @if($existingReview->review_msg)
                                            <p class="mb-0 text-muted">{{ $existingReview->review_msg }}</p>
                                        @endif
                                        {{-- <small class="text-muted">{{ $product->name }} - {{ __('vendorwebsite.reviewed_on') }}: {{ $existingReview->created_at->format('d/m/Y h:i A') }}</small> --}}
                                    </div>
                                </div>
                            @endif
                        @endforeach
                    </div>

                    <div class="mt-5">
                        <h5>{{ __('vendorwebsite.shipping_details') }}</h5>
                        <div class="order-content">
                            <h6>{{ $address->first_name ?? '' }} {{ $address->last_name ?? '' }}</h6>
                            <p class="mb-2">
                                {{ $address->address_line_1 ?? '' }}
                                @if (!empty($address->address_line_2))
                                    , {{ $address->address_line_2 }}
                                @endif
                                {{ $address->city_data->name ?? '' }}, {{ $address->state_data->name ?? '' }},
                                {{ $address->country_data->name ?? '' }} - {{ $address->postal_code ?? '' }}
                            </p>
                            <div><span>Contact Number:</span> <a href="#"
                                    class="heading-color btn btn-link border-0 ms-lg-3 ms-2 font-size-16">{{ $address->contact_number ?? ($order->user->mobile ?? '') }}</a>
                            </div>
                            <!-- @if (isset($booking) && $booking->start_date_time)
    <div><span>Checkout Time:</span> <span class="heading-color fw-medium">{{ \Carbon\Carbon::parse($booking->start_date_time)->format('d/m/Y h:i A') }}</span></div>
    @endif -->
                        </div>
                    </div>

                </div>
                <div class="col-md-4 payment-section">
                    <div class="payment-container">
                        <h6>{{ __('vendorwebsite.payment_details') }}</h6>
                        <!-- Payment Summary -->
                        @php
                            $bpSubtotal = $order->orderItems->sum(function ($bp) {
                                return ($bp->unit_price ?? 0) * ($bp->qty ?? 1);
                            });
                            $bpDiscount = $order->orderItems->sum('discount_value');
                            $tax_data = json_decode($order->orderGroup->taxes) ?? [];

                        @endphp
                        <div class="payment-summary">
                            <div class="d-flex justify-content-between align-items-center mb-1 price-item">
                                <span class="font-size-14">{{ __('vendorwebsite.subtotal') }}</span>
                                <span class="heading-color">{{ \Currency::vendorCurrencyFormate($bpSubtotal) }}</span>
                            </div>
                            @if ($bpDiscount > 0)
                                <div class="d-flex justify-content-between align-items-center mb-1 price-item">
                                    <span class="font-size-14">{{ __('vendorwebsite.discount') }}</span>
                                    <span class="text-success">- {{ \Currency::vendorCurrencyFormate($bpDiscount) }}</span>
                                </div>
                            @endif


                            @if (isset($order->orderGroup) && $order->orderGroup->total_tax_amount > 0)
                                <div class="tax-summary">


                                    <div class="d-flex justify-content-between align-items-center gap-3 mb-1 price-item"
                                        type="button" data-bs-toggle="collapse" data-bs-target="#collapseExample"
                                        aria-expanded="false" aria-controls="collapseExample">
                                        <span
                                            class="d-flex align-items-center justify-content-between flex-grow-1 gap-3 font-size-14">
                                            <span>{{ __('vendorwebsite.tax') }}</span>
                                            <i class="ph ph-caret-down ms-2 toggle-icon"></i>
                                        </span>
                                        <span
                                            class="text-danger fw-semibold">{{ \Currency::vendorCurrencyFormate($order->orderGroup->total_tax_amount ?? 0) }}</span>
                                    </div>

                                    <div class="collapse" id="collapseExample">
                                        <div class="card card-body">
                                            @foreach ($tax_data as $tax)
                                                <div class="d-flex justify-content-between align-items-center mb-2 px-3">
                                                    <span class="font-size-14">
                                                        {{ $tax->tax_name }}
                                                        @if ($tax->tax_type == 'percent')
                                                            ({{ $tax->tax_value }}%)
                                                        @else
                                                            ({{ \Currency::vendorCurrencyFormate($tax->tax_value) }})
                                                        @endif
                                                    </span>
                                                    <span class="heading-color">
                                                        {{ \Currency::vendorCurrencyFormate($tax->tax_amount) }}
                                                    </span>
                                                </div>
                                            @endforeach
                                        </div>
                                    </div>

                                </div>
                            @endif

                            @if ($order->shipping_cost > 0)
                                <div class="d-flex justify-content-between align-items-center mb-1 price-item">
                                    <span class="font-size-14">{{ __('vendorwebsite.delivery_charges') }}</span>
                                    <span
                                        class="heading-color">{{ \Currency::vendorCurrencyFormate($order->shipping_cost ?? 0) }}</span>
                                </div>
                            @endif
                            <hr class="line-divider">
                            <div class="d-flex justify-content-between align-items-center">
                                <span>{{ __('vendorwebsite.total') }}</span>
                                <span
                                    class="total-value fw-semibold text-primary">{{ \Currency::vendorCurrencyFormate($order->total_admin_earnings ?? 0) }}</span>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <!-- Review Modal -->
    <div class="modal fade rating-modal" id="review-product-modal" tabindex="-1" aria-modal="true" role="dialog">
        <div class="modal-dialog modal-dialog-centered modal-md">
            <div class="modal-content bg-gray-900 rounded">
                <div class="modal-body modal-body-inner rate-us-modal">
                    <form id="reviewForm">
                        <input type="hidden" id="currentReviewId" name="review_id" value="">
                        <input type="hidden" id="currentProductId" name="product_id" value="">
                        <input type="hidden" id="currentProductVariationId" name="product_variation_id" value="">

                        <div class="rate-box">
                            <h5 class="font-size-21-3 mb-0 text-center" id="modal-title">{{ __('vendorwebsite.rate_our_product_now') }}</h5>
                            <p class="mb-0 mt-2 font-size-14 text-center">
                                {{ __('vendorwebsite.your_honest_feedback_helps_us_improve_and_serve_you_better') }}</p>

                            <div class="mt-3 text-center">
                                <span id="product-name-display" class="text-primary font-weight-bold"></span>
                            </div>

                            <div class="mt-5 pt-2">
                                <div class="form-group mb-4">
                                    <label for="" class="form-label">{{ __('vendorwebsite.your_rating') }}</label>
                                    <div class="bg-gray-800 form-control">
                                        <ul class="list-inline m-0 p-0 d-flex align-items-center justify-content-start gap-1 rating-list">
                                            @for ($i = 1; $i <= 5; $i++)
                                                <li data-value="{{ $i }}" class="star">
                                                    <span class="text-warning icon">
                                                        <i class="ph-fill ph-star icon-fill"></i>
                                                        <i class="ph ph-star icon-normal"></i>
                                                    </span>
                                                </li>
                                            @endfor
                                        </ul>
                                    </div>
                                </div>
                                <div class="form-group">
                                    <label for="" class="form-label">{{ __('vendorwebsite.enter_your_feedback') }}</label>
                                    <textarea class="form-control bg-gray-800"
                                        placeholder="{{ __('vendorwebsite.Share_your_experience!_Your_feedback_helps_others_make_informed_decisions_about_their_healthcare') }}"
                                        rows="3" id="reviewTextarea"></textarea>
                                </div>
                                <div class="mt-5 pt-3 d-flex align-items-center justify-content-center row-gap-3 column-gap-4 flex-wrap">
                                    <button type="button" class="btn btn-secondary"
                                        data-bs-dismiss="modal">{{ __('vendorwebsite.cancel') }}</button>
                                    <button type="submit" class="btn btn-primary" id="submit-btn">{{ __('vendorwebsite.submit') }}</button>
                                </div>
                            </div>
                        </div>
                    </form>
                </div>
            </div>
        </div>
    </div>

    @push('scripts')
    <script>
        $(document).ready(function() {
            let selectedRating = 0;
            let currentProductId = null;
            let currentProductVariationId = null;

            // Star rating functionality
            $('.rating-list li').on('click', function() {
                selectedRating = parseInt($(this).data('value'));
                updateStarDisplay();
            });

            $('.rating-list li').on('mouseenter', function() {
                const hoverRating = parseInt($(this).data('value'));
                updateStarDisplay(hoverRating);
            });

            $('.rating-list').on('mouseleave', function() {
                updateStarDisplay(selectedRating);
            });

            function updateStarDisplay(rating = selectedRating) {
                $('.rating-list li').each(function(index) {
                    const starIndex = index + 1;
                    if (starIndex <= rating) {
                        $(this).addClass('selected');
                        $(this).find('.icon-fill').show();
                        $(this).find('.icon-normal').hide();
                    } else {
                        $(this).removeClass('selected');
                        $(this).find('.icon-fill').hide();
                        $(this).find('.icon-normal').show();
                    }
                });
            }

            // Handle review button click (both add and edit)
            $('[data-bs-target="#review-product-modal"]').on('click', function() {
                currentProductId = $(this).data('product-id');
                currentProductVariationId = $(this).data('product-variation-id');
                const productName = $(this).data('product-name');
                const reviewId = $(this).data('review-id');
                const existingRating = $(this).data('rating');
                const existingMessage = $(this).data('message');

                // Set hidden fields
                $('#currentProductId').val(currentProductId);
                $('#currentProductVariationId').val(currentProductVariationId);
                $('#currentReviewId').val(reviewId || '');

                $('#product-name-display').text(productName);

                // Check if it's edit mode
                if (reviewId && existingRating) {
                    $('#modal-title').text('{{ __('vendorwebsite.edit_review') }}');
                    $('#submit-btn').text('{{ __('vendorwebsite.update') }}');

                    // Set existing values
                    selectedRating = existingRating;
                    $('#reviewTextarea').val(existingMessage || '');
                    updateStarDisplay();
                } else {
                    $('#modal-title').text('{{ __('vendorwebsite.rate_our_product_now') }}');
                    $('#submit-btn').text('{{ __('vendorwebsite.submit') }}');

                    // Reset form for new review
                    selectedRating = 0;
                    $('#reviewTextarea').val('');
                    updateStarDisplay();
                }
            });

            // Handle modal close events
            $('#review-product-modal').on('hidden.bs.modal', function() {
                // Reset form when modal is closed
                selectedRating = 0;
                $('#reviewTextarea').val('');
                updateStarDisplay();
                currentProductId = null;
                currentProductVariationId = null;
            });

            // Handle form submission
            $('#reviewForm').on('submit', function(e) {
                e.preventDefault();

                if (selectedRating === 0) {
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
                    return;
                }

                const reviewData = {
                    product_id: currentProductId,
                    product_variation_id: currentProductVariationId,
                    rating: selectedRating,
                    review_msg: $('#reviewTextarea').val(),
                    _token: '{{ csrf_token() }}'
                };

                // Add review_id for edit mode
                const reviewId = $('#currentReviewId').val();
                if (reviewId) {
                    reviewData.review_id = reviewId;
                }

                // Show loading
                const submitBtn = $('#reviewForm button[type="submit"]');
                const originalText = submitBtn.text();
                submitBtn.prop('disabled', true).text('{{ __('vendorwebsite.submitting') }}...');

                // Determine URL based on mode
                const url = reviewId ? '{{ url("/api/update-review") }}' : '{{ url("/api/add-review") }}';

                // Submit review
                $.ajax({
                    url: url,
                    method: 'POST',
                    data: reviewData,
                    success: function(response) {
                        if (response.status) {
                            // Close modal immediately
                            $('#review-product-modal').modal('hide');

                            Swal.fire({
                                icon: 'success',
                                title: '{{ __('vendorwebsite.success') }}',
                                text: response.message || '{{ __('vendorwebsite.review_submitted_successfully') }}',
                                confirmButtonText: '{{ __('vendorwebsite.ok') }}',
                                customClass: {
                                    confirmButton: 'btn btn-primary'
                                },
                                buttonsStyling: false,
                            }).then(() => {
                                // Reload page after user clicks OK
                                location.reload();
                            });
                        } else {
                            Swal.fire({
                                icon: 'error',
                                title: '{{ __('vendorwebsite.error') }}',
                                text: response.message || '{{ __('vendorwebsite.failed_to_submit_review') }}',
                                confirmButtonText: '{{ __('vendorwebsite.ok') }}',
                                customClass: {
                                    confirmButton: 'btn btn-primary'
                                },
                                buttonsStyling: false,
                            });
                        }
                    },
                    error: function(xhr) {
                        let errorMessage = '{{ __('vendorwebsite.failed_to_submit_review') }}';
                        if (xhr.responseJSON && xhr.responseJSON.message) {
                            errorMessage = xhr.responseJSON.message;
                        }

                        Swal.fire({
                            icon: 'error',
                            title: '{{ __('vendorwebsite.error') }}',
                            text: errorMessage,
                            confirmButtonText: '{{ __('vendorwebsite.ok') }}',
                            customClass: {
                                confirmButton: 'btn btn-primary'
                            },
                            buttonsStyling: false,
                        });

                        // Don't close modal on error - let user try again
                    },
                    complete: function() {
                        // Reset button
                        submitBtn.prop('disabled', false).text(originalText);
                    }
                });
            });

            // Handle delete review button click
            $('.delete-review-btn').on('click', function() {
                const reviewId = $(this).data('review-id');
                const productName = $(this).data('product-name');

                Swal.fire({
                    title: '{{ __('vendorwebsite.delete_review') }}',
                    text: '{{ __('vendorwebsite.are_you_sure_you_want_to_delete_this_review') }}',
                    icon: 'warning',
                    showCancelButton: true,
                    confirmButtonColor: '#d33',
                    cancelButtonColor: '#3085d6',
                    confirmButtonText: '{{ __('vendorwebsite.yes_delete') }}',
                    cancelButtonText: '{{ __('vendorwebsite.cancel') }}',
                    customClass: {
                        confirmButton: 'btn btn-danger',
                        cancelButton: 'btn btn-secondary'
                    },
                    buttonsStyling: false,
                }).then((result) => {
                    if (result.isConfirmed) {
                        // Show loading
                        $(this).prop('disabled', true).html('<i class="ph ph-spinner ph-spin"></i> {{ __('vendorwebsite.deleting') }}...');

                        $.ajax({
                            url: '{{ url("/api/remove-review") }}',
                            method: 'POST',
                            data: {
                                review_id: reviewId,
                                _token: '{{ csrf_token() }}'
                            },
                            success: function(response) {
                                if (response.status) {
                                    Swal.fire({
                                        icon: 'success',
                                        title: '{{ __('vendorwebsite.success') }}',
                                        text: '{{ __('vendorwebsite.review_deleted_successfully') }}',
                                        confirmButtonText: '{{ __('vendorwebsite.ok') }}',
                                        customClass: {
                                            confirmButton: 'btn btn-primary'
                                        },
                                        buttonsStyling: false,
                                    }).then(() => {
                                        // Reload page to show updated reviews
                                        location.reload();
                                    });
                                }
                            },
                            error: function(xhr) {
                                let errorMessage = '{{ __('vendorwebsite.failed_to_delete_review') }}';
                                if (xhr.responseJSON && xhr.responseJSON.message) {
                                    errorMessage = xhr.responseJSON.message;
                                }

                                Swal.fire({
                                    icon: 'error',
                                    title: '{{ __('vendorwebsite.error') }}',
                                    text: errorMessage,
                                    confirmButtonText: '{{ __('vendorwebsite.ok') }}',
                                    customClass: {
                                        confirmButton: 'btn btn-primary'
                                    },
                                    buttonsStyling: false,
                                });
                            },
                            complete: function() {
                                // Reset button
                                $('.delete-review-btn').prop('disabled', false).html('<i class="ph ph-trash"></i> {{ __('vendorwebsite.delete') }}');
                            }
                        });
                    }
                });
            });
        });
    </script>
    @endpush
@endsection
