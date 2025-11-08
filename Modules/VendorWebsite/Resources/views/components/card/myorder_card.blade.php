@php
    $paymentStatus = strtolower($order->payment_status);
    $paymentStatusClass =
        $paymentStatus === 'paid' ? 'text-success' : ($paymentStatus === 'pending' ? 'text-warning' : 'text-danger');
    $deliveryStatus = strtolower($order->delivery_status);
    // Match admin color logic
    $deliveryStatusClass = match ($deliveryStatus) {
        'order_placed' => 'text-primary',
        'pending' => 'text-warning',
        'processing' => 'text-info',
        'delivered' => 'text-success',
        'cancelled' => 'text-danger',
        default => 'text-secondary',
    };

    // Debug: Check if bookingProducts relationship exists
    if (!$order->bookingProducts || $order->bookingProducts->isEmpty()) {
        // Fallback to orderItems if bookingProducts is empty
        $order->bookingProducts = collect();
    }
@endphp

<a href="{{ route('order-detail', $order->id) }}">
    <div class="order-card rounded-3 d-flex flex-column">

        {{-- Order header (shown once) --}}
        <div class="d-flex justify-content-between align-items-start flex-wrap gap-3">
            <h6 class="mb-0 font-size-14 text-secondary">#Order{{ $order->id }}</h6>
            <div class="text-end">
                <span class="d-flex align-items-center justify-content-end row-gap-2 flex-wrap column-gap-3"><span
                        class="text-body">{{ __('vendorwebsite.payment') }}: <span
                            class="fw-medium {{ $paymentStatusClass }}">{{ ucfirst($paymentStatus) }}</span></span>
                    <span class="text-body">{{ __('vendorwebsite.delivery_status') }}: <span
                            class="fw-medium {{ $deliveryStatusClass }}">{{ ucfirst(str_replace('_', ' ', $order->delivery_status)) }}</span></span></span>
            </div>
        </div>

        {{-- Products of this order --}}
        @if ($order->bookingProducts && $order->bookingProducts->count() > 0)
            @foreach ($order->bookingProducts as $bookingProduct)
                @php
                    $product = $bookingProduct->product;
                    $image =
                        $product && $product->media && $product->media->count() > 0
                            ? $product->media->first()->getFullUrl()
                            : asset('dummy-images/dummy.png');
                @endphp

                <div
                    class="d-flex flex-column flex-md-row align-items-start align-items-md-center column-gap-3 row-gap-2">
                    <div class="order-card-img">
                        <img src="{{ $image }}" alt="{{ $product->name ?? 'Product Image' }}"
                            class="img-fluid rounded bg-gray-900">
                    </div>

                    <div class="d-flex flex-column flex-grow-1 gap-2">
                        <h5 class="mb-0">
                            <span>{{ $product->name ?? __('vendorwebsite.product_name') }}</span>
                        </h5>

                        <div class="order-card-info d-flex flex-wrap gap-3 gap-lg-5">
                            <div class="d-flex align-items-center gap-2">
                                <span class="font-size-14">{{ __('vendorwebsite.price') }}:</span>
                                <span
                                    class="fw-medium text-primary">{{ \Currency::vendorCurrencyFormate($bookingProduct->product_price ?? 0) }}</span>
                            </div>
                            <div class="d-flex align-items-center gap-2">
                                <span class="font-size-14">{{ __('vendorwebsite.quantity') }}:</span>
                                <span class="fw-medium heading-color">{{ $bookingProduct->product_qty ?? 1 }}</span>
                            </div>
                            <div>
                                <span class="font-size-14">{{ __('vendorwebsite.payment') }}:</span>
                                <span class="fw-medium {{ $paymentStatusClass }}">{{ ucfirst($paymentStatus) }}</span>
                            </div>
                            <div>
                                <span class="font-size-14">{{ __('vendorwebsite.delivery_date') }}:</span>
                                <span
                                    class="heading-color fw-medium">{{ $order->updated_at ? $order->updated_at->format('d/m/Y') : '-' }}</span>
                            </div>
                        </div>
                    </div>
                </div>

                @unless ($loop->last)
                    <hr class="my-2">
                @endunless
            @endforeach
        @else
            {{-- Fallback to orderItems if bookingProducts is empty --}}
            @foreach ($order->orderItems as $orderItem)
                @php
                    $product = $orderItem->product_variation->product;
                    $image =
                        $product && $product->media && $product->media->count() > 0
                            ? $product->media->first()->getFullUrl()
                            : asset('dummy-images/dummy.png');
                @endphp

                <div
                    class="d-flex flex-column flex-md-row align-items-start align-items-md-center column-gap-3 row-gap-2">
                    <div class="order-card-img">
                        <img src="{{ $image }}" alt="{{ $product->name ?? 'Product Image' }}"
                            class="img-fluid rounded bg-gray-900">
                    </div>

                    <div class="d-flex flex-column flex-grow-1 gap-2">
                        <h5 class="mb-0">
                            <span>{{ $product->name ?? __('vendorwebsite.product_name') }}</span>
                        </h5>

                        <div class="order-card-info d-flex flex-wrap gap-3 gap-lg-5">
                            <div class="d-flex align-items-center gap-2">
                                <span class="font-size-14">{{ __('vendorwebsite.price') }}:</span>
                                <span
                                    class="fw-medium text-primary">{{ \Currency::vendorCurrencyFormate($orderItem->unit_price ?? 0) }}</span>
                            </div>
                            <div class="d-flex align-items-center gap-2">
                                <span class="font-size-14">{{ __('vendorwebsite.quantity') }}:</span>
                                <span class="fw-medium heading-color">{{ $orderItem->qty ?? 1 }}</span>
                            </div>
                        </div>
                    </div>
                </div>

                @unless ($loop->last)
                    <hr class="my-2">
                @endunless
            @endforeach
        @endif

        {{-- Actions (shown once for the order) --}}
        <div class="order-card-action">
            @if ($order->delivery_status !== 'cancelled' && $order->delivery_status !== 'delivered')
                <button class="btn btn-secondary fw-semibold cancel-order-btn"
                    data-order-id="{{ $order->id }}" onclick="event.stopPropagation()">{{ __('vendorwebsite.cancel_order') }}</button>
            @endif
        </div>

    </div>


    <script>
        // alert('Script loaded!'); // Debug: confirm script is loaded
        $(document).on('click', '.cancel-order-btn', function() {

            const orderId = $(this).data('order-id');
            Swal.fire({
                title: '{{ __('vendorwebsite.are_you_sure') }}',
                text: '{{ __('vendorwebsite.do_you_really_want_to_cancel_this_order') }}',
                icon: 'warning',
                showCancelButton: true,
                confirmButtonText: '{{ __('vendorwebsite.yes_cancel_it') }}',
                cancelButtonText: '{{ __('vendorwebsite.no_keep_it') }}',
                customClass: {
                    confirmButton: 'btn btn-primary',
                    cancelButton: 'btn btn-secondary'
                },
            }).then((result) => {
                if (result.isConfirmed) {
                    let cancelUrl = "{{ route('orders.cancel', 'ORDER_ID_PLACEHOLDER') }}";
                    cancelUrl = cancelUrl.replace('ORDER_ID_PLACEHOLDER', orderId);
                    $.ajax({
                        url: cancelUrl,
                        type: 'POST',
                        data: {
                            _token: $('meta[name="csrf-token"]').attr('content')
                        },
                        success: function(response) {

                            if (response.success) {
                                Swal.fire({
                                    title: 'Canceled!',
                                    text: '{{ __('vendorwebsite.Your_order_has_been_canceled') }}',
                                    icon: 'success',
                                    customClass: {
                                        confirmButton: 'btn btn-primary'
                                    },
                                    buttonsStyling: false
                                });
                                $('.cancel-order-btn[data-order-id="' + orderId + '"]').prop(
                                    'disabled', true);
                                location.reload();
                            } else {
                                Swal.fire({
                                    icon: 'error',
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
                        error: function(xhr) {

                            let msg = 'Could not cancel order (AJAX error).';
                            if (xhr.status) msg += '\nStatus: ' + xhr.status;
                            if (xhr.responseText) msg += '\nResponse: ' + xhr.responseText;
                            alert(msg);
                            Swal.fire('Error', msg, 'error');
                        }
                    });
                }
            });
        });
    </script>
