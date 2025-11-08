<!DOCTYPE html>
<html>
<head>
    <meta http-equiv="Content-Type" content="text/html; charset=utf-8"/>
    <title>{{ __('messages.subscriptions') }}</title>
    <style>
        body {
            font-family: DejaVu Sans, sans-serif;
            font-size: 12px;
        }
        .table {
            width: 100%;
            border-collapse: collapse;
            margin-bottom: 1rem;
        }
        .table th,
        .table td {
            padding: 8px;
            border: 1px solid #ddd;
            text-align: left;
        }
        .table th {
            background-color: #f5f5f5;
            font-weight: bold;
        }
        .table tr:nth-child(even) {
            background-color: #f9f9f9;
        }
        .status-active {
            color: #28a745;
        }
        .status-inactive,
        .status-cancel {
            color: #dc3545;
        }
        .status-pending {
            color: #ffc107;
        }
        .header {
            text-align: center;
            margin-bottom: 20px;
        }
    </style>
</head>
<body>
    <div class="header">
        <h2>{{ __('messages.subscriptions') }}</h2>
        <p>{{ __('messages.generated_on') }}: {{ now()->format('d/m/Y H:i') }}</p>
    </div>

    <table class="table">
        <thead>
            <tr>
                <th>{{ __('frontend.vendor_name') }}</th>
                <th>{{ __('frontend.plan') }}</th>
                <th>{{ __('frontend.payment_method') }}</th>
                <th>{{ __('frontend.amount') }}</th>
                <th>{{ __('frontend.duration') }}</th>
                <th>{{ __('frontend.start_date') }}</th>
                <th>{{ __('frontend.expired_date') }}</th>
                <th>{{ __('frontend.status') }}</th>
            </tr>
        </thead>
        <tbody>
            @foreach($subscriptions as $subscription)
                <tr>
                    <td>
                        @if($subscription->user)
                            {{ $subscription->user->deleted_at ? __('messages.deleted_user') : $subscription->user->first_name . ' ' . $subscription->user->last_name }}
                        @else
                            {{ __('messages.deleted_user') }}
                        @endif
                    </td>
                    <td>{{ json_decode($subscription->plan_details, true)['name'] ?? '-' }}</td>
                    <td>{{ ucfirst($subscription->gateway_type ?? '-') }}</td>
                    <td>{{ amountWithCurrencySymbol(number_format($subscription->total_amount ?? $subscription->amount, 2), defaultCurrency()) }}</td>
                    <td>
                        @php
                            $planDetails = json_decode($subscription->plan_details, true);
                            $durationSuffix = match($planDetails['type'] ?? '') {
                                'Monthly' => 'Month',
                                'Yearly' => 'Year',
                                'Weekly' => 'Week',
                                default => 'Day'
                            };
                        @endphp
                        {{ ($planDetails['duration'] ?? 0) . ' ' . $durationSuffix }}
                    </td>
                    <td>
                        @if($subscription instanceof \App\Models\Payment)
                            -
                        @else
                            {{ formatDateOrTime($subscription->start_date, 'date') }}
                        @endif
                    </td>
                    <td>
                        @if($subscription instanceof \App\Models\Payment)
                            -
                        @else
                            {{ formatDateOrTime($subscription->end_date, 'date') }}
                        @endif
                    </td>
                    <td class="status-{{ $subscription instanceof \App\Models\Payment ? 'pending' : strtolower($subscription->status ?? 'pending') }}">
                        {{ __('frontend.' . ($subscription instanceof \App\Models\Payment ? 'pending' : strtolower($subscription->status ?? 'pending'))) }}
                    </td>
                </tr>
            @endforeach
        </tbody>
    </table>
</body>
</html>