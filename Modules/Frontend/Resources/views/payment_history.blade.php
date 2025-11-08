@extends('frontend::layouts.master')
@section('title')
{{__('messages.subscription_history')}}
@endsection
@section('content')

<div class="breadcrumb-card">
    <div class="container">
        <div class="d-flex justify-content-center align-items-center">
            <nav class="breadcrumb-container" aria-label="breadcrumb">
                <ol class="breadcrumb m-0">
                    <li class="breadcrumb-item" aria-current="page"><a href="{{ route('index') }}">{{__('messages.home')}}</a></li>
                    <li class="breadcrumb-item active">{{__('messages.membership')}}
                    </li>
                </ol>
            </nav>
        </div>
    </div>
</div>

<div class="section-spacing">
  <div class="container">

    <div class="section-spacing-bottom px-0">

      <h5 class="main-title text-capitalize mb-2">{{__('messages.subscription_history')}}</h5>
        <div class="table-responsive">
          <table class="table payment-history table-borderless">
            <thead class="table-dark">
              <tr>
                <th class="text-white">{{__('messages.date')}}</th>
                <th class="text-white">{{__('messages.plan')}}</th>
                <th class="text-white">{{__('messages.duration')}}</th>
                <th class="text-white">{{__('messages.expiry_date')}}</th>
                <th class="text-white">{{__('messages.amount')}}</th>
                <th class="text-white">{{__('messages.discount')}}</th>
                <th class="text-white">{{__('messages.tax')}}</th>
                <th class="text-white">{{__('messages.total')}}</th>
                <th class="text-white">{{__('messages.payment_method')}}</th>
                <th class="text-white">{{__('messages.status')}}</th>
                <th class="text-white">{{__('messages.invoice')}}</th>
              </tr>
            </thead>
            <tbody class="payment-info">

             
              @if($subscriptions && $subscriptions->isEmpty())
                <tr>
                    <td colspan="10" class="text-center fw-bold">

                        {{ __('frontend.subscription_history_not_found') }} <!-- You can customize this message -->
                    </td>
                </tr>
            @else
                <tbody class="payment-info">
                    @foreach($subscriptions as $subscription)
                    <tr>
                        <td class=" text-black">{{ \Carbon\Carbon::parse($subscription->created_at)->format('d/m/Y') }}</td>
                        <td class=" text-black">{{ $subscription->plan->name }}</td>
                        @if( $subscription->plan->type =='Monthly')
                        <td class=" text-black">{{ $subscription->plan->duration }} Month</td>
                        @elseif( $subscription->plan->type =='Yearly')
                        <td class=" text-black">{{ $subscription->plan->duration }} Year</td>
                        @elseif( $subscription->plan->type =='Weekly')
                        <td class=" text-black">{{ $subscription->plan->duration }} Week</td>
                        @else
                        <td class=" text-black">{{ $subscription->plan->duration }} Day</td>
                        @endif

                        <td class=" text-black">{{ \Carbon\Carbon::parse($subscription->end_date)->format('d/m/Y') }}</td>
                        <td class=" text-black">{{ \Currency::format($subscription->amount) }}</td>
                        <td class=" text-black">{{ \Currency::format($subscription->discount_amount) }}</td>
                        <td class=" text-black">{{\Currency::format($subscription->plan->tax) }}</td>
                        <td class=" text-black">{{ \Currency::format($subscription->total_amount) }}</td>
                        <td class=" text-black">{{ ucfirst($subscription->subscription_transaction->payment_type ?? '-') }}</td>
                        <td class=" text-black">{{ ucfirst($subscription->status ?? '-') }}</td>
                        <td class=""><a  href="{{route('downloadinvoice', ['id' => $subscription->id])}}">{{__('messages.download_invoice')}}</a></td>
                    </tr>
                    @endforeach
                </tbody>
                @endif
            </tbody>
          </table>
        </div>
    </div>
  </div>
</div>

@endsection
