@php

$activeSubscriptions = \Modules\Subscriptions\Models\Subscription::where('user_id', auth()->id())
->where('status', 'active')
->where('end_date', '>', now())
->orderBy('id', 'desc')
->first();
$currentPlanId = $activeSubscriptions ? $activeSubscriptions->plan_id : null;
$plan['currentPlanId'] = $currentPlanId;

@endphp
@if(isset($plan) && $plan->status == 1)

    <div class="pricing-card p-5 rounded" data-pay-option="{{ $plan->type ?? 'Monthly' }}">
        <div class="d-flex align-items-center flx-wrap gap-3 justify-content-between mb-4 pricing-title">
            <i class="ph ph-airplane-in-flight"></i>
        </div>

   <div class="p-0">
   <div class="d-flex justify-content-between align-items-center">
      <div class="d-flex align-items-center">
      
        <h4 class="text-primary m-0">{{ \Currency::formatSuperadmin($plan->price ?? 0) }}</h4>

        <span class="mx-1">/</span>
        @php
            if($plan->duration == 1 && $plan->type == 'Monthly'){
                $plantype = 'Month';
            } elseif ($plan->duration > 1 && $plan->type == 'Monthly') {
                $plantype = 'Months';
            } elseif ($plan->duration == 1 && $plan->type == 'Yearly') {
                $plantype = 'Year';
            } elseif ($plan->duration > 1 && $plan->type == 'Yearly') {
                $plantype = 'Years';
            }
        @endphp

        <span>{{ $plan->duration.' '.$plantype ?? '-' }}</span>
  
       </div>
       @if($plan->has_discount == 1 && $plan->discounted_price != null)
            <span class="mx-1">
                {{ \Currency::formatSuperadmin($plan->discount_value ?? 0) }}
                @if($plan->discount_type === 'percentage')
                    %
                @endif
             OFF</span>
        @endif
      </div>

       <h6 class="font-size-18 title mb-2 mt-3">{{ $plan->name ?? '-'}}</h6>
       <p class="m-0 font-size-14 descrption">{{ $plan->description ?? '-'}}</p>

            @if( !isset($plan['currentPlanId']) || isset($plan['currentPlanId']) )
                @if ( $plan['currentPlanId'] == null || $plan['currentPlanId'] !== $plan->id)
                    <a href="{{ route('pricing_plan', ['id' => $plan->id]) }}" class="btn btn-secondary buy-btn w-100 my-5">{{ __('frontend.get_started') }}</a>
                @endif
            @endif

            @if( isset($plan['currentPlanId']) &&  $plan['currentPlanId'] !=null  && $plan['currentPlanId'] == $plan->id )
                <a href="{{ route('pricing_plan', ['id' => $plan->id]) }}" class="btn btn-secondary buy-btn w-100 my-5">{{ __('frontend.renew') }}</a>
            @endif

             <ul class="m-0 pricing-list-desc">
                @if (isset($plan->features))
                    @foreach ($plan->features as $feature)
                        <li>{{ $feature->title ?? '-'}}</li>
                    @endforeach
                @endif
            </ul>
        </div>
    </div>
@endif
