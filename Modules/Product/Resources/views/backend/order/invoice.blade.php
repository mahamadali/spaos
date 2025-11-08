<style type="text/css">
    @import url('https://fonts.googleapis.com/css?family=Open+Sans:400,700');

    * {
        box-sizing: border-box;
        margin: 0;
        padding: 0;
    }

    body,
    table {
        font-family: 'Open Sans', sans-serif;
    }

    table {
        width: 100%;
        border-collapse: collapse;
    }

    th,
    td {
        padding: 10px;
        text-align: left;
        vertical-align: top;
    }

    h5,
    h6 {
        margin-bottom: 10px;
    }

    .text-end {
        text-align: right !important;
    }

    .text-muted {
        color: #6c757d;
    }

    .card {
        border: 1px solid #ddd;
        border-radius: 8px;
        padding: 20px;
        margin-bottom: 20px;
    }

    .card-header,
    .card-footer {
        background-color: #f8f9fa;
        padding: 15px;
    }

    /* Responsive adjustments */
    .visibleMobile {
        display: none;
    }

    .hiddenMobile {
        display: block;
    }

    @media (max-width: 768px) {
        .text-end {
            text-align: flex-end !important;
        }

        .visibleMobile {
            display: block;
        }

        .hiddenMobile {
            display: none;
        }
    }

    .invoice-table {
        width: 100%;
        border-collapse: collapse;
        margin-top: 20px;
    }

    .invoice-table th,
    .invoice-table td {
        border: 1px solid #f1f1f1;
        padding: 16px;
        font-size: 14px;
    }

    .invoice-table th {
        background-color: #f2f2f2;
    }
</style>


{{-- header start --}}
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

                        // For PDF generation, create base64 data URL
                        if (file_exists($logoPath)) {
                            try {
                                $imageData = base64_encode(file_get_contents($logoPath));
                                $imageMimeType = mime_content_type($logoPath);
                                $logoUrl = 'data:' . $imageMimeType . ';base64,' . $imageData;
                            } catch (Exception $e) {
                                $logoUrl = null;
                            }
                        }
                    @endphp

                    @if($logoUrl)
                        <img src="{{ $logoUrl }}" alt="Company Logo" style="max-width: 150px; height: auto;">
                    @else
                        <h3 style="margin: 0; color: #666;">{{ config('app.name', 'Company Logo') }}</h3>
                    @endif
                </div>

                <div class="col-auto text-end">
                    <h5 class="mb-0">{{ __('messages.invoice') }}
                        <span class="text-accent">{{ setting('inv_prefix') }}{{ $order->orderGroup->order_code }}
                        </span>
                    </h5>
                    <span class="text-muted">{{ __('messages.order_date') }}:
                        {{ formatDateOrTime(strtotime($order->created_at),'date') }}
                    </span>
                    <br>
                    <span class="text-muted">{{ __('messages.delivery_date') }}:
                        {{ formatDateOrTime(strtotime($order->updated_at),'date') }}
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
                    <br>
                    <div class="col-auto mt-3">
                        <h6 class="d-inline-block"> {{ __('messages.payment_method') }}:
                            <span>{{ ucwords(str_replace('_', ' ', $order->orderGroup->payment_method)) }}</span>
                        </h6>
                    </div>
                    <h6 class="col-auto d-inline-block"> {{ __('messages.logistic') }}:
                        <span>{{ $order->logistic_name }}</span>
                    </h6>
                    <h6 class="col-auto d-inline-block"> {{ __('messages.status') }}:
                        <span>{{ Str::title(Str::replace('_', ' ', $order->delivery_status)) }}</span>
                    </h6>
                </div>
                <div class="col-auto text-end">
                    <div class="shipping-address d-flex justify-content-md-end gap-3 mb-3">
                        <div class="border-end w-25">
                            <h5 class="mb-2">{{ __('messages.shipping_address') }}</h5>

                            @php
                                $shippingAddress = $order->orderGroup->shippingAddress;
                            @endphp
                            <p class="mb-0 text-wrap">
                                {{ optional($shippingAddress)->address_line_1 }},
                                {{ optional($shippingAddress->city_data)->name }},
                                {{ optional($shippingAddress->state_data)->name }},
                                {{ optional($shippingAddress->country_data)->name }}
                            </p>
                        </div>
                        @if (!$order->orderGroup->is_pos_order)
                            <div class="w-25">
                                <h5 class="mb-2"> {{ __('messages.billing_address') }}</h5>
                                @php
                                    $billingAddress = $order->orderGroup->billingAddress;
                                @endphp
                                <p class="mb-0 text-wrap">
                                    {{ optional($billingAddress)->address_line_1 }},
                                    {{ optional($billingAddress->city_data)->name }},
                                    {{ optional($billingAddress->state_data)->name }},
                                    {{ optional($billingAddress->country_data)->name }}
                                </p>
                            </div>
                        @endif
                    </div>

                </div>
            </div>
        </div>

        <!--order details-->
        <table class="invoice-table" data-use-parent-width="true">
            <thead>
                <tr>
                    <th class="text-center"> {{ __('messages.s/l') }}</th>
                    <th> {{ __('messages.products') }}</th>
                    <th class="text-end"> {{ __('messages.unit_price') }}</th>
                    <th class="text-end"> {{ __('messages.quantity') }}</th>
                    <th class="text-end"> {{ __('messages.product_price') }}</th>
                    <th class="text-end"> {{ __('messages.discount') }}</th>
                    <th class="text-end"> {{ __('messages.total_price') }}</th>
                </tr>
            </thead>

            <tbody>
                @foreach ($order->orderItems as $key => $item)
                    @php
                        $product = $item->product_variation->product;
                    @endphp
                    <tr>
                        <td class="text-center"width="7%">{{ $key + 1 }}</td>
                        <td>
                            <div class="d-flex align-items-center">

                                <div class="ms-2">
                                    <h4 style="max-width: 250px; white-space: normal;">
                                        {{ $product->name }}
                                    </h4>
                                    <div class="text-muted">
                                        @foreach (generateVariationOptions($item->product_variation->combinations) as $variation)
                                            <span class="fs-6">
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
                            <span class="fw-bold">{{ \Currency::format($item->unit_price) }}
                            </span>
                        </td>

                        <td class="fw-bold text-end">{{ $item->qty }}</td>

                        <td class="text-end">
                            <span class="fw-bold">{{ \Currency::format($item->product_variation->price ?? 0) }}
                            </span>
                        </td>

                        @if(isset($item->product_variation) && $item->product_variation->price - $item->unit_price > 0)

                        @if($product->discount_type =='percent')

                         <td class="text-end">
                            <span class="fw-bold">{{ $product->discount_value }}%
                            </span>
                        </td>


                        @else

                        <td class="text-end">
                            <span class="fw-bold">{{ \Currency::format($product->discount_value ) }}
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
                            @if ($item->refundRequest && $item->refundRequest->refund_status == 'refunded')
                                <span
                                    class="badge bg-info-subtle rounded-pill text-capitalize">{{ $item->refundRequest->refund_status }}</span>
                            @endif
                            <span class="text-accent fw-bold">{{ \Currency::format($item->total_price) }}
                            </span>

                        </td>

                    </tr>
                @endforeach
            </tbody>
            <tfoot class="text-end">
                <tr>
                    <td colspan="6">
                        <h5 class="d-inline-block text-end"> {{ __('messages.sub_total') }} </h5>
                    </td>
                    <td width="10%">
                        <strong>{{ \Currency::format($order->orderGroup->sub_total_amount) }}</strong>
                    </td>
                </tr>
                @if ($order->orderGroup->total_tips_amount > 0)
                    <tr>
                        <td colspan="6">
                            <h5 class="d-inline-block text-end"> {{ __('messages.tips') }}</h5>
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
                            <td  colspan="6">
                                <h5 class=" text-end">
                                    {{ $tax['title'] }}
                                    {{ $tax['type'] == 'percent' ? '(' . $tax['value'] . '%)' : '(' . \Currency::format($tax['value']) . ')' }}:
                                </h5>
                            </td>
                            <td colspan="1" class="text-end">
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
                        <h5 class="d-inline-block text-end"> {{ __('messages.delivery_charge') }} </h5>
                    </td>
                    <td width="10%" class="text-end">
                        <strong>{{ \Currency::format($order->orderGroup->total_shipping_cost) }}</strong>
                    </td>
                </tr>
                @if ($order->orderGroup->total_coupon_discount_amount > 0)
                    <tr>
                        <td colspan="6">
                            <h5 class="d-inline-block text-end"> {{ __('messages.coupon_discount') }} </h5>
                        </td>
                        <td width="10%" class="text-end">
                            <strong>{{ \Currency::format($order->orderGroup->total_coupon_discount_amount) }}</strong>
                        </td>
                    </tr>
                @endif
                <tr>
                    <td colspan="6">
                        <h5 class="d-inline-block text-end"> {{ __('messages.grand_total') }} </h5>
                    </td>
                    <td width="10%" class="text-end"><strong
                            class="text-accent">{{ \Currency::format($order->orderGroup->grand_total_amount) }}</strong>
                    </td>
                </tr>
            </tfoot>
        </table>

        <!--Note-->
        <div class="card-body">
            <div class="card-footer border-top-0 px-4 py-4 rounded bg-soft-gray border border-2">
                <p class="mb-0">{{ setting('spacial_note') }}</p>
            </div>
        </div>
    </div>
</div>
{{-- footer end --}}
