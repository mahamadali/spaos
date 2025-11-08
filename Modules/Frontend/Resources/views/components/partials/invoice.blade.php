<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8">
    <meta http-equiv="X-UA-Compatible" content="IE=edge">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Invoice</title>
    <style>
        @page :footer {
            display: none !important;
        }

        @page :header {
            display: none !important;
        }

        @page {
            size: A4;
        }

        h1,
        h2,
        h3,
        h4,
        h5,
        h6 {
            margin: 0;
            color: #010A0F;
            font-weight: 500;
        }

        p {
            margin: 0;
        }

        body {
            color: #6C757D;
            font-size: 16px;
            font-family: 'DejaVu Sans', sans-serif;
        }

        .header {

            padding: 25px 0 0;
        }

        .border-bottom {
            border-bottom: 1px solid #CCCCCC;
        }

        .text-black {
            color: #050505;
        }

        .c-row {
            display: flex;
            flex-wrap: wrap;
        }

        .c-align-center {
            align-items: center;
        }

        .c-justify-between {
            justify-content: space-between;
        }

        .c-col-6 {
            flex: 0 0 auto;
            width: 50%;
        }

        .c-col-7 {
            flex: 0 0 auto;
            width: 58.33333333%;
        }

        .c-col-5 {
            flex: 0 0 auto;
            width: 41.66666667%;
        }

        .p-badge {
            background-color: #32A071;
            color: #ffffff;
            padding: 6px 10px;
            border-radius: 6px;
        }

        .c-text-end {
            text-align: right;
        }

        ul {
            margin: 0;
            padding: 0;
            list-style-type: none;
        }

        li {
            display: inline;
            margin-right: 16px;
        }


        .bg-color {
            background-color: #FAF8FF;
        }
    </style>
</head>

<body>
    <div class="header">
        <div class="main-logo" style="float: left;">
            @php
                $logoPath = str_replace(config('app.url'), '', SuperAdminsetting('logo'));
                $fullLogoPath = public_path($logoPath);
            @endphp

            @if (SuperAdminsetting('logo') && file_exists($fullLogoPath))
                <img class="logo-mini img-fluid" src="{{ $fullLogoPath }}" height="30" alt="logo">
            @else
                <img class="logo-mini img-fluid" src="{{ public_path('img/logo/logo.png') }}" height="30"
                    alt="logo">
            @endif
        </div>
        <div style="float: right;">
            <span>{{ __('messages.invoice_date') }}<span class="text-black">{{ now()->format('d/m/Y') }}</span></span>
            <span style="margin-left: 12px;">{{ __('messages.invoice_ID') }}<span
                    class="text-black">#{{ $data->id }}</span></span>
        </div>
    </div>

    <div style="clear: both; padding-top: 16px;" class="border-bottom"></div>
    <div class="main-content">
        <div style="padding: 35px 0 40px;">
            <div style="float: left;">
                <p>{{ __('messages.thanks_you_have') }}</p>
            </div>
            <div class="c-text-end" style="float: right;">
                <span style="color: #050505;">{{ __('messages.payment_status:') }}</span>
                <span class="p-badge" style="margin-left: 10px;">
                    {{ strtoupper(optional($data->subscription_transaction)->payment_status ?? 'Offline') }}
                </span>

            </div>
            <div style="clear: both;"></div>
        </div>

        <div class="header-content"
            style="border-bottom: 1px solid #CCCCCC; padding-bottom: 20px; margin-bottom: 20px;">
            <div class="left-content" style="float: left; width: 70%;">
                <h3>{{ SuperAdminsetting('app_name') }} </h3>
                <div style="margin-top: 10px;">
                    <p>{{ implode(
                        ', ',
                        array_filter([
                            SuperAdminsetting('bussiness_address_line_1'),
                            SuperAdminsetting('bussiness_address_line_2'),
                            SuperAdminsetting('bussiness_address_city'),
                            SuperAdminsetting('bussiness_address_state'),
                            SuperAdminsetting('bussiness_address_country'),
                            SuperAdminsetting('bussiness_address_postal_code'),
                        ]),
                    ) ?? '-' }}
                    </p>
                </div>
            </div>
            <div class="right-content text-black" style="text-align: right; float: right; width: 30%;">
                <a href="mailto:customer@frezka.com" class="text-black">{{ SuperAdminsetting('inquriy_email') }}</a>
                <p style="margin: 10px 0 0;">{{ SuperAdminsetting('helpline_number') }}</p>
            </div>
            <div style="clear: both;"></div>
        </div>

        <div>
            <h4>{{ __('messages.customer_detail:') }}</h4>
            <div style="margin-top: 10px; padding: 20px 24px; background-color: #FAF8FF;">
                <ul>
                    <li><span
                            class="text-black">{{ __('messages.name:') }}</span>{{ $data->user->first_name . ' ' . $data->user->last_name }}
                    </li>
                    <li><span class="text-black">{{ __('messages.email:') }}</span>{{ $data->user->email }}</li>
                    <li><span class="text-black">{{ __('messages.mobile_number:') }}</span>{{ $data->user->mobile }}
                    </li>
                </ul>
            </div>
        </div>

        @php
            $planType = optional($data->plan)->type;

            if ($planType === 'Daily') {
                $displayType = 'Day';
            } elseif ($planType === 'Weekly') {
                $displayType = 'Week';
            } elseif ($planType === 'Monthly') {
                $displayType = 'Month';
            } elseif ($planType === 'Yearly') {
                $displayType = 'Year';
            } else {
                $displayType = $planType;
            }
        @endphp

        <div style="margin: 32px 0;">
            <h4>Plan Detail:</h4>
            <div style="margin-top: 10px; padding: 20px 24px; background-color: #FAF8FF;">
                <ul>
                    <li><span
                            class="text-black">{{ __('messages.Name:') }}</span>{{ optional($data->plan)->name ?? '-' }}
                    </li>
                    <li><span
                            class="text-black">{{ __('messages.Duration:') }}</span>{{ optional($data->plan)->duration . ' ' . $displayType ?? '' }}
                    </li>
                    <li><span
                            class="text-black">{{ __('messages.purchase_date:') }}</span>{{ \Carbon\Carbon::parse($data->start_date)->format('d/m/Y') ?? '-' }}
                    </li>
                    <li><span
                            class="text-black">{{ __('messages.expiry_date:') }}</span>{{ \Carbon\Carbon::parse($data->end_date)->format('d/m/Y') ?? '-' }}
                    </li>
                    <li><span
                            class="text-black">{{ __('messages.Payment_method:') }}</span>{{ ucfirst(optional($data->subscription_transaction)->payment_type) }}
                    </li>

                </ul>
            </div>
        </div>

        <table style="width: 100%; border-collapse: collapse; border: 1px solid #ccc; border-radius: 8px;">
            <thead class="bg-color text-black">
                <th style="padding:12px 30px; text-align: left;">{{ __('messages.plan_name') }}</th>
                <th style="padding:12px 30px; text-align: right;">{{ __('messages.price') }}</th>
                <th style="padding:12px 30px; text-align: right;">{{ __('messages.discount') }}</th>
                <th style="padding:12px 30px; text-align: right;">{{ __('messages.amount') }}</th>
            </thead>
            <tbody>
                <tr>
                    <td style="padding:12px 30px; text-align: left;">
                        {{ optional($data->plan)->name ?? '-' }} - {{ optional($data->plan)->duration ?? '' }}
                        {{ $displayType }}
                    </td>
                    <td style="padding:12px 30px; text-align: right;">
                        {{ \Currency::formatSuperadmin(optional($data->plan)->price ?? '-') }}
                    </td>
                    <td style="padding:12px 30px; text-align: right;">
                        @if (optional($data->plan)->has_discount)
                            @if (optional($data->plan)->discount_type === 'percentage')
                                -
                                {{ \Currency::formatSuperadmin((optional($data->plan)->price * optional($data->plan)->discount_value) / 100) }}
                                ({{ optional($data->plan)->discount_value }}%)
                            @else
                                - {{ \Currency::formatSuperadmin(optional($data->plan)->discount_value) }}
                            @endif
                        @else
                            -
                        @endif
                    </td>
                    <td style="padding:12px 30px; text-align: right;">
                        {{ \Currency::formatSuperadmin(
                            optional($data->plan)->has_discount ? optional($data->plan)->discounted_price : optional($data->plan)->price ?? 0,
                        ) }}
                    </td>
                </tr>
            </tbody>
        </table>
        <table style="width: 100%; border-collapse: collapse; margin-top: 24px;">
            <tbody class="bg-color text-black">

                @php
                    $discountData = optional($data->subscription_transaction)->discount_data
                        ? json_decode($data->subscription_transaction->discount_data)
                        : null;
                    $couponCode = isset($discountData->coupon->coupon_code)
                        ? '(' . $discountData->coupon->coupon_code . ')'
                        : '';
                @endphp

                <tr>
                    <td style="padding:12px 30px; text-align: start;"></td>
                    <td style="padding:12px 30px; text-align: end;"></td>
                    <td style="padding:12px 30px; text-align: end;"></td>
                    <td style="padding:12px 30px; text-align: start; color: #6C757D;">
                        {{ __('messages.coupon_discount') }}<span style="color: #09954D;">{{ $couponCode }}</span>
                    </td>
                    <td style="padding:12px 30px; text-align: right; color: #09954D;">
                        -{{ \Currency::formatSuperadmin($data->discount_amount) }}</td>
                </tr>
                <tr>
                    <td style="padding:12px 30px; text-align: start;"></td>
                    <td style="padding:12px 30px; text-align: end;"></td>
                    <td style="padding:12px 30px; text-align: end;"></td>
                    <td style="padding:12px 30px; text-align: start; color: #6C757D;">{{ __('messages.Subtotal') }}
                    </td>
                    <td style="padding:12px 30px; text-align: right; color: #050505;">
                        {{ \Currency::formatSuperadmin($data->amount - $data->discount_amount) }}</td>
                </tr>
                @if (isset($data->plan->taxes) && $data->plan->taxes->isNotEmpty())
                    <tr>
                        <td style="padding:12px 30px; text-align: start;"></td>
                        <td style="padding:12px 30px; text-align: end;"></td>
                        <td style="padding:12px 30px; text-align: end;"></td>
                        <td style="padding:12px 30px; text-align: start; color: #6C757D;">
                            {{ __('messages.tax') }} <br>
                            @if (isset($data->plan->taxes) && $data->plan->taxes->isNotEmpty())
                                @foreach ($data->plan->taxes as $tax)
                                    @if ($tax->type == 'Percentage')
                                        ({{ $tax->title }}: {{ $tax->value }}%)
                                        <br>
                                    @else
                                        ({{ $tax->title }}: {{ \Currency::formatSuperadmin($tax->value) }})
                                        <br>
                                    @endif
                                @endforeach
                            @else
                                {{ __('messages.no_taxes') }}
                            @endif
                        </td>
                        <td style="padding:12px 30px; text-align: right; color: #EF3E36;">
                            {{ \Currency::formatSuperadmin($data->tax_amount ?? '0.00') }}
                        </td>
                    </tr>

                @endif

                <tr>
                    <td style="padding:12px 30px; text-align: start;"></td>
                    <td style="padding:12px 30px; text-align: end;"></td>
                    <td style="padding:12px 30px; text-align: end;"></td>
                    <td style="padding:12px 30px; text-align: start; color: #050505; border-top:1px solid #ccc;">
                        {{ __('messages.total_amount:') }}</td>
                    <td style="padding:12px 30px; text-align: right; color: #050505; border-top:1px solid #ccc;">
                        {{ \Currency::formatSuperadmin($data->total_amount ?? '-') }}</td>
                </tr>
            </tbody>
        </table>

        <div class="bottom-section" style="margin-top: 12px;">
            <h4 style="margin-bottom: 10px;">{{ __('messages.terms_&_condition') }}</h4>
            <p>{{ __('messages.by_purchasing_a_subscription,') }}<a href="#" class="text-black"
                    style="text-decoration: none; color: #A82D86;">support@frezka.com</a></p>
        </div>

        <footer style="margin-top: 32px;">
            <div class="" style="display: flex; align-items: center; gap: 10px;">
                <h5>{{ __('messages.for_more_information') }}</h5>
                <a href="{{ env('APP_URL') }}" style="color: #A82D86;">{{ env('APP_URL') }}</a>
            </div>
        </footer>

</body>

</html>
