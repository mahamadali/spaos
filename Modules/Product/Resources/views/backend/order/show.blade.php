@extends('backend.layouts.app')

@section('title')
    {{ $module_title }}
@endsection

@section('content')
    <style type="text/css" media="print">
        @page :footer {
            display: none !important;
        }

        @page :header {
            display: none !important;
        }

        @page {
            size: landscape;
            margin: 0;
        }

        /* @page { margin: 0; } */

        .pr-hide {
            display: none;
        }

        button {
            display: none !important;
        }

        * {
            -webkit-print-color-adjust: none !important;
            /* Chrome, Safari 6 – 15.3, Edge */
            color-adjust: none !important;
            /* Firefox 48 – 96 */
            print-color-adjust: none !important;
            /* Firefox 97+, Safari 15.4+ */
        }
    </style>

    <div class="row pr-hide">
        <div class="col-12">
            <div class="card ">
                <div class="card-header border-bottom-0">
                    <div class="row pr-hide">
                        <div class="col-auto col-lg-4 mb-4">
                            <div class="input-group">
                                <select class="form-select select2" name="payment_status"
                                    data-minimum-results-for-search="Infinity" id="update_payment_status">
                                    <option value="" disabled>
                                        {{ __('messages.payment_status') }}
                                    </option>
                                    <option value="paid" {{ $order->payment_status == 'paid' ? 'selected' : '' }}>
                                        {{ __('messages.paid') }}
                                    </option>
                                    <option value="unpaid" {{ $order->payment_status == 'unpaid' ? 'selected' : '' }}>
                                        {{ __('messages.unpaid') }}
                                    </option>
                                </select>
                            </div>
                        </div>
                        <div class="col-auto col-lg-4 mb-4">
                            <div class="input-group">
                                <select name="delivery_status" class="form-control select2" name="delivery_status"
                                    data-ajax--url="{{ route('backend.get_search_data', ['type' => 'constant', 'sub_type' => 'ORDER_STATUS']) }}"
                                    data-ajax--cache="true">
                                    <option value="" disabled> {{ __('messages.delivery_status') }}</option>
                                    @if (isset($order->delivery_status))
                                        <option value="{{ $order->delivery_status }}" selected>
                                            {{ Str::title(Str::replace('_', ' ', $order->delivery_status)) }}
                                        </option>
                                    @endif
                                </select>
                            </div>
                        </div>

                        <div class="col-auto col-lg-4 mb-4 text-center text-lg-end">
                            @if(isset($order) && ($order->payment_status ?? null) === 'paid')
                                <a class="btn btn-primary"
                                    href="{{ route('backend.orders.downloadInvoice', ['id' => request()->id]) }}">
                                    <i class="fa-solid fa-download"></i>
                                    Download Invoice
                                </a>
                            @endif
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <div class="row g-4">
        <!--Main Invoice-->
        <div class="col-xl-9 order-2 order-md-2 order-lg-2 order-xl-1">
            <div class="card mb-4" id="section-1">
                <div class="card-body">
                    <!--Order Detail-->
                    <div class="row justify-content-between align-items-center g-3 mb-4">
                        <div class="col-auto flex-grow-1">
                            @php
                                $logoUrl = null;
                                $logoPath = null;

                                // Try vendor logo first
                                $vendorLogo = getVendorSetting('logo');
                                if ($vendorLogo && !empty(trim($vendorLogo))) {
                                    // Handle both relative and absolute paths
                                    if (file_exists(public_path($vendorLogo))) {
                                        $logoPath = public_path($vendorLogo);
                                    } elseif (file_exists($vendorLogo)) {
                                        $logoPath = $vendorLogo;
                                    }
                                }

                                // Try global setting if vendor logo not found
                                if (!$logoPath || !file_exists($logoPath)) {
                                    $globalLogo = setting('logo');
                                    if ($globalLogo && !empty(trim($globalLogo))) {
                                        // Handle URL paths (like http://127.0.0.1:8000/storage/...)
                                        if (filter_var($globalLogo, FILTER_VALIDATE_URL)) {
                                            // Extract path from URL
                                            $urlPath = parse_url($globalLogo, PHP_URL_PATH);
                                            if ($urlPath) {
                                                $cleanPath = ltrim($urlPath, '/');
                                                $logoPath = public_path($cleanPath);
                                            }
                                        } else {
                                            // Handle relative paths
                                            if (file_exists(public_path($globalLogo))) {
                                                $logoPath = public_path($globalLogo);
                                            } elseif (file_exists($globalLogo)) {
                                                $logoPath = $globalLogo;
                                            }
                                        }
                                    }
                                }

                                // Use default logo if others not found
                                if (!$logoPath || !file_exists($logoPath)) {
                                    $logoPath = public_path('img/logo/logo.png');
                                }

                                // For web view, use asset URL for better performance
                                if (file_exists($logoPath)) {
                                    // Check if we have a valid logo setting
                                    $vendorLogo = getVendorSetting('logo');
                                    $globalLogo = setting('logo');

                                    if ($vendorLogo && !empty(trim($vendorLogo))) {
                                        $logoUrl = asset($vendorLogo);
                                    } elseif ($globalLogo && !empty(trim($globalLogo))) {
                                        $logoUrl = $globalLogo; // This is already a full URL
                                    } else {
                                        $logoUrl = asset('img/logo/logo.png');
                                    }
                                } else {
                                    $logoUrl = asset('img/logo/logo.png');
                                }
                            @endphp

                            @if($logoUrl)
                                <img src="{{ $logoUrl }}" alt="logo" class="img-fluid" width="200" style="max-height: 80px;">
                            @else
                                <div class="text-center">
                                    <h4 class="text-muted">{{ config('app.name', 'Company Logo') }}</h4>
                                </div>
                            @endif
                        </div>
                        <div class="col-auto text-end">
                            <h5 class="mb-0">{{ __('messages.invoice') }}
                                <span class="text-accent">{{ setting('inv_prefix') }}{{ $order->orderGroup->order_code }}
                                </span>
                            </h5>
                            <span class="text-muted">{{ __('messages.order_date') }}:
                                {{ formatDateOrTime(strtotime($order->created_at), 'date') }}
                            </span>
                            <br>
                            <span class="text-muted">{{ __('messages.delivery_date') }}:
                                {{ formatDateOrTime(strtotime($order->updated_at), 'date') }}
                            </span>
                            @if ($order->location_id != null)
                                <div>
                                    <span class="text-muted">
                                        <i class="las la-map-marker"></i> {{ optional($order->location)->name }}
                                    </span>
                                </div>
                            @endif
                        </div>
                    </div>
                    <div class="row d-flex justify-content-md-between justify-content-center g-3">
                        <div class="col-md-3">
                            <!--Customer Detail-->
                            <div class="welcome-message">
                                <h5 class="mb-2"> {{ __('messages.customer_info') }}</h5>
                                <p class="mb-0"> {{ __('messages.name') }}:
                                    <strong>{{ optional($order->user)->full_name }}</strong>
                                </p>
                                <p class="mb-0"> {{ __('messages.email') }}:
                                    <strong>{{ optional($order->user)->email }}</strong>
                                </p>
                                <p class="mb-0"> {{ __('messages.phone') }}:
                                    <strong>{{ optional($order->user)->mobile }}</strong>
                                </p>
                            </div>
                            <div class="col-auto mt-3">
                                <h6 class="d-inline-block"> {{ __('messages.payment_method') }}:
                                    <span>{{ ucwords(str_replace('_', ' ', $order->orderGroup->payment_method)) }}</span>
                                </h6>
                            </div>
                            <h6 class="col-auto d-inline-block"> {{ __('messages.logistic') }}:
                                <span>{{ $order->logistic_name }}</span>
                            </h6>
                            <h6 class="col-auto d-inline-block"> {{ __('messages.order_status') }}:
                                <span>{{ Str::title(Str::replace('_', ' ', $order->delivery_status)) }}</span>
                            </h6>
                        </div>
                        <div class="col">
                            <div class="shipping-address d-flex justify-content-md-end gap-3 mb-3">
                                <div class="border-end w-25">
                                    <h5 class="mb-2">{{ __('messages.shipping_address') }}</h5>

                                    @php
                                        $shippingAddress = $order->orderGroup->shippingAddress;
                                    @endphp
                                    <p class="mb-0 text-wrap">
                                        {{ optional($shippingAddress)->address_line_1 }},
                                        {{ optional(optional($shippingAddress)->city_data)->name }},
                                        {{ optional(optional($shippingAddress)->state_data)->name }},
                                        {{ optional(optional($shippingAddress)->country_data)->name }}
                                    </p>
                                </div>
                                @if (!$order->orderGroup->is_pos_order)
                                    <div class="w-25">
                                        <h5 class="mb-2">{{ __('messages.billing_address') }}</h5>
                                        @php
                                            $billingAddress = $order->orderGroup->billingAddress;
                                        @endphp
                                        <p class="mb-0 text-wrap">
                                            {{ optional($billingAddress)->address_line_1 }},
                                            {{ optional(optional($billingAddress)->city_data)->name }},
                                            {{ optional(optional($billingAddress)->state_data)->name }},
                                            {{ optional(optional($billingAddress)->country_data)->name }}
                                        </p>
                                    </div>
                                @endif
                            </div>

                        </div>
                    </div>
                </div>

                <!--order details-->
                <table class="table table-bordered border-top" data-use-parent-width="true">
                    <thead>
                        <tr>
                            <th class="text-center"> {{ __('messages.s/l') }}</th>
                            <th> {{ __('messages.products') }}</th>
                            <th class="text-end"> {{ __('messages.product_price') }}</th>
                            <th class="text-end"> {{ __('messages.discount') }}</th>
                            <th class="text-end"> {{ __('messages.unit_price') }}</th>
                            <th class="text-end"> {{ __('messages.quantity') }}</th>
                            <th class="text-end"> {{ __('messages.total_price') }}</th>
                        </tr>
                    </thead>

                    <tbody>
                        @foreach ($order->orderItems as $key => $item)
                            @php
                                $product = $item->product_variation->product;

                            @endphp
                            <tr>
                                <td class="text-center">{{ $key + 1 }}</td>
                                <td>
                                    <div class="d-flex align-items-center">
                                        <div> <img src="{{ $product->feature_image }}" alt="{{ $product->name }}"
                                                class="avatar avatar-50 rounded-pill">
                                        </div>
                                        <div class="ms-2">
                                            <h6 class="fs-lg mb-0" style="max-width: 280px; white-space: normal;">
                                                {{ $product->name }}
                                            </h6>
                                            <div class="text-muted">
                                                @foreach (generateVariationOptions($item->product_variation->combinations) as $variation)
                                                    <span class="fs-xs">
                                                        {{ $variation['name'] }}:
                                                        @foreach ($variation['values'] as $value)
                                                            {{ $value['name'] }}
                                                        @endforeach
                                                        @if (!$loop->last)
                                                            ,
                                                        @endif
                                                    </span>
                                                @endforeach
                                            </div>
                                        </div>
                                    </div>
                                </td>

                                <td class="text-end">
                                    <span class="fw-bold">{{ \Currency::format($item->product_variation->price ?? 0) }}
                                    </span>
                                </td>



                                @if (isset($item->product_variation) && $item->product_variation->price - $item->unit_price > 0)
                                    @if ($product->discount_type == 'percent')
                                        <td class="text-end">
                                            <span class="fw-bold">{{ $product->discount_value }}%
                                            </span>
                                        </td>
                                    @else
                                        <td class="text-end">
                                            <span class="fw-bold">{{ \Currency::format($product->discount_value) }}
                                            </span>
                                        </td>
                                    @endif
                                @else
                                    <td class="text-end">
                                        <span class="fw-bold">-
                                        </span>
                                    </td>
                                @endif




                                <td class="text-end">
                                    <span class="fw-bold">{{ \Currency::format($item->unit_price) }}
                                    </span>
                                </td>
                                <td class="fw-bold text-end">{{ $item->qty }}</td>

                                <td class=" text-end">
                                    @if ($item->refundRequest && $item->refundRequest->refund_status == 'refunded')
                                        <span
                                            class="badge bg-info-subtle rounded-pill text-capitalize">{{ $item->refundRequest->refund_status }}</span>
                                    @endif
                                    <span class="text-accent fw-bold">{{ \Currency::format($item->total_price) }}</span>
                                </td>
                            </tr>
                        @endforeach
                    </tbody>
                    <tfoot class="text-end">
                        <tr>
                            <td colspan="6">
                                <h5 class="d-inline-block text-end"> {{ __('messages.sub_total') }}: </h5>
                            </td>
                            <td width="10%">
                                <strong>{{ \Currency::format($order->orderGroup->sub_total_amount) }}</strong>
                            </td>
                        </tr>
                        @if (isset($order->orderGroup->total_tax_amount) && $order->orderGroup->total_tax_amount > 0)
                            @php
                                $taxdata = json_decode($order->orderGroup->taxes) ?? [];
                            @endphp
                            <tr>
                                <td colspan="6">
                                    <h5 class="d-inline-block text-end"> {{ __('messages.tax') }}: </h5>
                                    <div>
                                        @foreach ($taxdata as $tax)
                                            {{ $tax->tax_name }}
                                            ({{ $tax->tax_type == 'percent' ? $tax->tax_value . '%' : \Currency::format($tax->tax_value) }})
                                            @if (!$loop->last)
                                                ,
                                            @endif
                                        @endforeach
                                    </div>

                                </td>
                                <td width="10%">
                                    <strong>{{ \Currency::format($order->orderGroup->total_tax_amount) }}</strong>
                                </td>
                            </tr>
                        @endif





                        @if ($order->orderGroup->total_tips_amount > 0)
                            <tr>
                                <td colspan="6">
                                    <h5 class="d-inline-block text-end"> {{ __('messages.tips') }}: </h5>
                                </td>
                                <td width="10%" class="text-end">
                                    <strong>{{ \Currency::format($order->orderGroup->total_tips_amount) }}</strong>
                                </td>
                            </tr>
                        @endif
                        @php
                            $taxes = is_array($order->orderGroup->tax)
                                ? $order->orderGroup->tax
                                : json_decode($order->orderGroup->tax, true);
                        @endphp
                        @if (!empty($taxes))
                            @foreach ($taxes as $tax)
                                <tr>
                                    <td colspan="6">
                                        <h5 class="d-inline-block text-end">
                                            {{ $tax['title'] }}
                                            {{ $tax['type'] == 'percent' ? '(' . $tax['value'] . '%)' : '(' . \Currency::format($tax['value']) . ')' }}:
                                        </h5>
                                    </td>
                                    <td width="10%" class="text-end">
                                        @if ($tax['type'] == 'percent')
                                            <strong>{{ \Currency::format(($order->orderGroup->sub_total_amount * $tax['value']) / 100) }}</strong>
                                        @else
                                            <strong>{{ \Currency::format($tax['value']) }}</strong>
                                        @endif
                                    </td>
                                </tr>
                            @endforeach
                        @endif
                        <tr>
                            <td colspan="6">
                                <h5 class="d-inline-block text-end"> {{ __('messages.delivery_charge') }}: </h5>
                            </td>
                            <td width="10%" class="text-end">
                                <strong>{{ \Currency::format($order->orderGroup->total_shipping_cost) }}</strong>
                            </td>
                        </tr>
                        @if ($order->orderGroup->total_coupon_discount_amount > 0)
                            <tr>
                                <td colspan="6">
                                    <h5 class="d-inline-block text-end"> {{ __('messages.coupon_discount') }}: </h5>
                                </td>
                                <td width="10%" class="text-end">
                                    <strong>{{ \Currency::format($order->orderGroup->total_coupon_discount_amount) }}</strong>
                                </td>
                            </tr>
                        @endif
                        <tr>
                            <td colspan="6">
                                <h5 class="d-inline-block text-end"> {{ __('messages.grand_total') }}: </h5>
                            </td>
                            <td width="10%" class="text-end"><strong
                                    class="text-accent">{{ \Currency::format($order->orderGroup->grand_total_amount) }}</strong>
                            </td>
                        </tr>
                    </tfoot>
                </table>

                <!--Note-->
                <div class="card-body">
                    <div class="card-footer border-top-0 px-4 py-4 rounded bg-light-subtle border border-2">
                        <p class="mb-0">{{ setting('spacial_note') }}</p>
                    </div>
                </div>
            </div>
        </div>

        <!--Order Status-->
        <div class="col-xl-3 order-1 order-md-1 order-lg-1 order-xl-2 pr-hide">
            <div class="sticky-sidebar">
                <div class="card mb-4">
                    <div class="card-header">
                        <h5 class="card-title">{{ __('messages.order_status') }}</h5>
                    </div>
                    <div class="card-body">
                        <ul class="alternate-list list-unstyled">

                            @forelse ($order->orderUpdates as $orderUpdate)
                                <li>
                                    <a class="{{ $loop->first ? 'active' : '' }}">
                                        {{ $orderUpdate->note }} <br>{{ __('messages.by') }}
                                        <span class="text-capitalize">{{ optional($orderUpdate->user)->name }}</span>
                                        {{ __('messages.at') }}
                                        {{ date('d M, Y', strtotime($orderUpdate->created_at)) }}.</a>
                                </li>
                            @empty
                                <li>
                                    <a class="active">{{ __('messages.no_logs_found') }}</a>
                                </li>
                            @endforelse
                        </ul>
                    </div>
                </div>
            </div>
        </div>
    </div>
@endsection

@push('after-scripts')
    <script>
        function invoicePrint() {
            window.print()
        }

        function updateStatusAjax(__this, url) {
            $.ajax({
                url: url,
                type: 'POST',
                dataType: 'json',
                data: {
                    order_id: {{ $order->id }},
                    status: __this.val(),
                    _token: '{{ csrf_token() }}'
                },
                success: function(res) {
                    if (res.status) {
                        window.successSnackbar(res.message)
                        setTimeout(() => {
                            location.reload()
                        }, 100);
                    }
                }
            });
        }
        $('[name="payment_status"]').on('change', function() {
            if ($(this).val() !== '') {
                updateStatusAjax($(this), "{{ route('backend.orders.update_payment_status') }}")
            }
        })

        $('[name="delivery_status"]').on('change', function() {
            if ($(this).val() !== '') {
                updateStatusAjax($(this), "{{ route('backend.orders.update_delivery_status') }}")
            }
        })
    </script>
@endpush
