@if(isset($plan) && $plan->status == 1)
<div class="col-md-4">
    <div class="pricing-card p-5 rounded" data-pay-option="{{ $plan->type ?? 'Monthly' }}">
        <div class="d-flex align-items-center flx-wrap gap-3 justify-content-between mb-4 pricing-title">
            <i class="ph ph-airplane-in-flight"></i>
        </div>

   <div class="p-0">
       <div class="d-flex align-items-center">
       <div class="d-flex align-items-center">
        <h2 class="text-primary fw-medium m-0">{{ \Currency::formatSuperadmin($plan->price ?? 0) }}</h2>
        <span class="mx-1">/</span>
       </div>
        <span>{{ $plan->duration.' ' . __('frontend.' . strtolower($plan->type)) ?? '-' }}</span>
        @if($plan->has_discount == 1 && $plan->discounted_price != null)
            <span class="mx-1">
                {{ \Currency::formatSuperadmin($plan->discount_value ?? 0) }}
                @if($plan->discount_type === 'percentage')
                    %
                @endif
             OFF</span>
        @endif
       </div>

       <h5 class="fe-medium title mb-2 mt-3">{{ $plan->name ?? '-'}}</h5>
       <p class="m-0 font-size-14 descrption">{{ $plan->description ?? '-'}}</p>

            @if( !isset($plan['currentPlanId']) || isset($plan['currentPlanId']) )
                @if ( $plan['currentPlanId'] == null || $plan['currentPlanId'] !== $plan->id)
                    <a href="{{ route('backend.pricing-plan', ['id' => $plan->id]) }}" class="btn btn-secondary  w-100 my-5">{{ __('frontend.get_started') }}</a>
                @endif
            @endif

            @if( isset($plan['currentPlanId']) &&  $plan['currentPlanId'] !=null  && $plan['currentPlanId'] == $plan->id )
                <a href="{{ route('backend.pricing-plan', ['id' => $plan->id]) }}" class="btn btn-secondary  w-100 my-5">{{ __('frontend.renew') }}</a>
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
</div>
@endif
