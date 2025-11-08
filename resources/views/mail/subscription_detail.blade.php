<!DOCTYPE html>
<html lang="{{ app()->getLocale() }}">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>{{ __('messages.subscription_details') }}</title>
</head>
<body>
    <div class="container">
        <h2>{{ __('messages.subscription_details') }}</h2>

        <p>{{ __('messages.user') }}: {{ optional($subscriptionDetail->user)->first_name .' '. optional($subscriptionDetail->user)->last_name }}</p>
        <p>{{ __('messages.email') }}: {{ optional($subscriptionDetail->user)->email ?? '-' }}</p>
        <p>{{ __('messages.contact_no') }}: {{ optional($subscriptionDetail->user)->mobile ?? '-' }}</p>

        <table style="border:1px solid black;width:100%">
            <thead>
                <tr>
                    <th style="border:1px solid black">{{ __('messages.plan') }}</th>
                    <th style="border:1px solid black">{{ __('messages.end_date') }}</th>
                    <th style="border:1px solid black">{{ __('messages.amount') }}</th>
                    <th style="border:1px solid black">{{ __('messages.tax_amount') }}</th>
                    <th style="border:1px solid black">{{ __('messages.total_amount') }}</th>
                    <th style="border:1px solid black">{{ __('messages.duration') }}</th>
                    <th style="border:1px solid black">{{ __('messages.status') }}</th>
                </tr>
            </thead>
            <tbody>
                <tr>
                    <td style="border:1px solid black">{{ $subscriptionDetail->name ?? '-' }}</td>
                    <td style="border:1px solid black">{{ \Carbon\Carbon::parse($subscriptionDetail->end_date)->format('Y-m-d') ?? '-' }}</td>
                    <td style="border:1px solid black">{{ $subscriptionDetail->amount ?? '-' }}</td>
                    <td style="border:1px solid black">{{ $subscriptionDetail->tax_amount ?? '-' }}</td>
                    <td style="border:1px solid black">{{ $subscriptionDetail->total_amount ?? '-' }}</td>
                    <td style="border:1px solid black">{{ $subscriptionDetail->duration . ' ' . $subscriptionDetail->type ?? '-' }}</td>
                    <td style="border:1px solid black">{{ $subscriptionDetail->status ?? '-' }}</td>
                </tr>
            </tbody>
        </table>
    </div>
</body>
</html>
