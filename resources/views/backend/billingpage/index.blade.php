@extends('backend.layouts.app')

@section('title') {{ __('messages.billing') }} @endsection

@section('content')
<div class="card">
  <div class="card-body">

    <div class="row">
      <div class="col-lg-12">
        <div class="upgrade-plan d-flex flex-wrap gap-3 align-items-center justify-content-between rounded">
          @if(!empty($data['activeSubscriptions']))
          @php $activePlanDetail = json_decode($data['activeSubscriptions']->plan_details); @endphp

          <div class="d-flex justify-content-center align-items-center gap-4">
            <div>
              <i class="fa fa-plane" aria-hidden="true"></i>
            </div>
            <div>
              <div class="d-flex align-items-center gap-4">
                @if($activePlanDetail)
                <h6 class="super-plan mb-0">{{ $activePlanDetail->name }} {{ __('frontend.plan') }}</h6>

                <p class="badge {{ $data['subscriptionStatus'] == 'active' ? 'bg-success' : 'bg-danger' }} mb-0">
                  {{ __('frontend.' . strtolower($data['subscriptionStatus'])) }}
                </p>
                @endif
              </div>

              @if($activePlanDetail)
              <p class="mb-0 text-body">
                {{ __('frontend.plan_detail', [
                    'plan' => $activePlanDetail->name,
                    'expiry_date' => \Carbon\Carbon::parse($data['activeSubscriptions']->end_date)->format('d M, Y') ?? '-'
                ]) }}
              </p>
              @endif
            </div>
          </div>
          <div class="d-flex align-items-center flex-wrap gap-3">
            <a href="{{ route('backend.upgrade-plan') }}" class="btn btn-primary">{{ __('frontend.upgrade_plan') }}</a>
            @if($activePlanDetail && $activePlanDetail->name !== 'Free')
            <a class="btn btn-secondary" href="{{ route('downloadinvoice', ['id' => $data['activeSubscriptions']->id]) }}">
              {{ __('frontend.download_invoice') }}
            </a>
            @endif
          </div>

          @elseif(!empty($data['expiredSubscription']))

          @php $expiredPlanDetail = json_decode($data['expiredSubscription']->plan_details); @endphp
          <div class="d-flex justify-content-center align-items-center gap-4">
            <i class="ph ph-warning-circle text-danger"></i>
            <div>
              <h6 class="super-plan mb-0 d-inline">{{ $expiredPlanDetail->name }} {{ __('frontend.plan') }}</h6>
              <span class="badge {{ $data['subscriptionStatus'] == 'active' ? 'bg-success' : 'bg-danger' }} d-inline">
                {{ ucfirst($data['subscriptionStatus']) }}
              </span>
              <p class="mb-0 text-body">
                Your {{ $expiredPlanDetail->name }} {{ __('frontend.plan_expired') }}
                {{ \Carbon\Carbon::parse($data['expiredSubscription']->end_date)->format('d M, Y') ?? '-' }}.
              </p>
            </div>
          </div>
          <div class="d-flex gap-3">
            <a href="{{ route('backend.upgrade-plan') }}" class="btn btn-primary">{{ __('frontend.renew_plan') }}</a>
          </div>

          @elseif(!empty($data['cancelledSubscription']))

          @php $cancelPlanDetail = json_decode($data['cancelledSubscription']->plan_details); @endphp
          <div class="d-flex justify-content-center align-items-center gap-4">
            <i class="ph ph-warning-circle text-danger"></i>
            <div>
              <h6 class="super-plan mb-0 d-inline">{{ $cancelPlanDetail->name }} {{ __('frontend.plan') }}</h6>
              <span class="badge {{ $data['subscriptionStatus'] == 'active' ? 'bg-success' : 'bg-danger' }} d-inline">
                {{ ucfirst(__('order_report.cancelled')) }}
              </span>
            </div>
          </div>
          <div class="d-flex gap-3">
            <a href="{{ route('backend.upgrade-plan') }}" class="btn btn-primary">{{ __('frontend.renew_plan') }}</a>
          </div>

          @else
          <div class="d-flex align-items-center gap-3">
            <h6 class="super-plan mb-0">{{ __('frontend.not_active_subscription') }}</h6>
            <p class="mb-0 text-body">{{ __('frontend.consider_uograding') }}</p>
          </div>
          <div class="d-flex gap-3">
            <a href="{{ route('backend.upgrade-plan') }}" class="btn btn-primary">{{ __('frontend.upgrade') }}</a>
          </div>
          @endif
        </div>
      </div>
      <div class="col-lg-1 d-lg-block d-none"></div>
    </div>

  </div>
</div>

<div class="card">
  <div class="card-body">

    <div class="section-spacing-bottom px-0">

      <h5 class="main-title text-capitalize mb-2">{{ __('frontend.billing_history') }}</h5>
<div class="table-responsive">
 
    <table class="table payment-history border">
      <thead>
        <tr>
          <th class="text-white">{{ __('frontend.date') }}</th>
          <th class="text-white">{{ __('frontend.plan') }}</th>
          <th class="text-white">{{ __('frontend.duration') }}</th>
          <th class="text-white">{{ __('frontend.expiry_date') }}</th>
          <th class="text-white">{{ __('frontend.amount') }}</th>
          <th class="text-white">{{ __('frontend.discount') }}</th>
          <th class="text-white">{{ __('frontend.tax_amount') }}</th>
          <th class="text-white">{{ __('frontend.total_amount') }}</th>
          <th class="text-white">{{ __('frontend.payment_method') }}</th>
          <th class="text-white">{{ __('frontend.status') }}</th>
          <th class="text-white">{{ __('frontend.invoice') }}</th>
        </tr>
      </thead>
      <tbody class="payment-info">

      @if(!empty($data['subscriptions']) && count($data['subscriptions']) > 0)

        @foreach($data['subscriptions'] as $subscription)
          @php $planDetails = json_decode($subscription->plan_details); @endphp
          @if($planDetails && $planDetails->name !== 'Free')
            <tr>
              <td>{{ \Carbon\Carbon::parse($subscription['created_at'])->format('d/m/Y') }}</td>
              <td>{{ $planDetails->name }}</td>
              @if($planDetails->type == 'Monthly')
                <td>{{ $planDetails->duration }} {{ __('frontend.Month') }}</td>
              @elseif($planDetails->type == 'Yearly')
                <td>{{ $planDetails->duration }} {{ __('frontend.year') }}</td>
              @elseif($planDetails->type == 'Weekly')
                <td>{{ $planDetails->duration }} {{ __('frontend.week') }}</td>
              @else
                <td>{{ $planDetails->duration }} {{ __('frontend.day') }}</td>
              @endif
              <td>{{ \Carbon\Carbon::parse($subscription->end_date)->format('d/m/Y') }}</td>
              <td>{{ \Currency::formatSuperadmin($subscription->amount) }}</td>
              <td>{{ \Currency::formatSuperadmin($subscription->discount_amount) }}</td>
              <td>{{ \Currency::formatSuperadmin($subscription->tax_amount) }}</td>
              <td>{{ \Currency::formatSuperadmin($subscription->total_amount) }}</td>
              <td>{{ ucfirst($subscription->subscription_transaction->payment_type ?? 'Offline') }}</td>
              <td>
                <p class="badge {{ strtolower($subscription->status) == 'active' ? 'bg-success' : 'bg-danger' }} mb-0">
                  {{ ucfirst(__("frontend." . strtolower($subscription->status ?? '-'))) }}
                </p>
              </td>
              <td>
                <a href="{{ route('downloadinvoice', ['id' => $subscription->id]) }}" title="{{ __('frontend.download_invoice') }}">
                  <i class="fas fa-download"></i> {{ __('frontend.download') }}
                </a>
              </td>
            </tr>
          @endif
        @endforeach

        @else
    <td class="text-center text-muted py-5" colspan="11">
    {{ __('frontend.history_not_found') }}
    </td>
  @endif
      </tbody>
    </table>

</div>
    </div>

  </div>
</div>
@endsection
