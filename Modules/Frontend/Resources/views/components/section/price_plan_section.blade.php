{{-- Pricing Plan Start --}}
@if ($data['plan'] !== null && $data['plan']->isNotEmpty())    

<section class="section-spacing">
    <div class="section-title-wrap center">
        <span class="subtitle">{{__('messages.pricing_plan')}}</span>
        <h3 class="section-title">{{__('messages.flexible_pricing_plans_for_every_business_size')}}</h3>
    </div>

    
    @php
    $monthly_plans = $data['plan']->where('type', 'Monthly');
    $weekly_plans = $data['plan']->where('type', 'Weekly')->where('price', '>', 0);
    $yearly_plans = $data['plan']->where('type', 'Yearly');

    $hasPlans = $monthly_plans->isNotEmpty() || $weekly_plans->isNotEmpty() || $yearly_plans->isNotEmpty();

    // Determine the first available tab
    $firstTab = null;
    if ($monthly_plans->isNotEmpty()) {
        $firstTab = 'pills-monthly-tab';
    } elseif ($weekly_plans->isNotEmpty()) {
        $firstTab = 'pills-weekly-tab';
    } elseif ($yearly_plans->isNotEmpty()) {
        $firstTab = 'pills-yearly-tab';
    }
@endphp

@if($hasPlans)
    <div class="container">
        <div class="row">
            <div class="col-md-12">
                {{-- Tab Header Start --}}
                <div class="d-flex justify-content-center">
                    <ul class="pricing-nav nav nav-pills justify-content-center" id="pricing-tab" role="tablist">
                        @if($weekly_plans->isNotEmpty())
                            <li class="nav-item">
                                <button class="nav-link {{ $firstTab == 'pills-weekly-tab' ? 'active' : '' }}" id="pills-weekly-tab"
                                    data-bs-toggle="pill" data-bs-target="#pills-weekly" type="button"
                                    role="tab">{{('messages.pay_weekly')}}</button>
                            </li>
                        @endif

                        @if($monthly_plans->isNotEmpty())
                            <li class="nav-item">
                                <button class="nav-link {{ $firstTab == 'pills-monthly-tab' ? 'active' : '' }}" id="pills-monthly-tab"
                                    data-bs-toggle="pill" data-bs-target="#pills-monthly" type="button"
                                    role="tab">{{ __('frontend.pay_monthly') }}
                                </button>
                            </li>
                        @endif

                        @if($yearly_plans->isNotEmpty())
                            <li class="nav-item">
                                <button class="nav-link {{ $firstTab == 'pills-yearly-tab' ? 'active' : '' }}" id="pills-yearly-tab"
                                    data-bs-toggle="pill" data-bs-target="#pills-yearly" type="button"
                                    role="tab">{{ __('frontend.pay_yearly') }}</button>
                            </li>
                        @endif
                    </ul>
                </div>
                {{-- Tab Header End --}}

                <div class="tab-content mt-5" id="pills-tabContent">
                    @if($monthly_plans->isNotEmpty())
                        <div class="tab-pane fade {{ $firstTab == 'pills-monthly-tab' ? 'show active' : '' }}" id="pills-monthly"
                            role="tabpanel">
                            <div class="row gy-4">
                                @foreach($monthly_plans->take(3) as $plan)
                                <div class="col-md-4">
                                    <x-frontend::card.card_price_plan :plan="$plan" />
                                </div> 
                                @endforeach
                            </div>
                        </div>
                    @endif

                    @if($weekly_plans->isNotEmpty())
                        <div class="tab-pane fade {{ $firstTab == 'pills-weekly-tab' ? 'show active' : '' }}" id="pills-weekly"
                            role="tabpanel">
                            <div class="row gy-4">
                                @foreach($weekly_plans->take(3) as $plan)
                                <div class="col-md-4">
                                    <x-frontend::card.card_price_plan :plan="$plan" />
                                </div> 
                                @endforeach
                            </div>
                        </div>
                    @endif

                    @if($yearly_plans->isNotEmpty())
                        <div class="tab-pane fade {{ $firstTab == 'pills-yearly-tab' ? 'show active' : '' }}" id="pills-yearly"
                            role="tabpanel">
                            <div class="row gy-4">
                                @foreach($yearly_plans->take(3) as $plan)
                                <div class="col-md-4">
                                    <x-frontend::card.card_price_plan :plan="$plan" />
                                </div> 
                                @endforeach
                            </div>
                        </div>
                    @endif
                </div>

                <div class="text-center mt-5">
                    <a href="{{ route('pricing') }}" class="btn btn-secondary">{{__('messages.explore')}}</a>
                </div>
            </div>
        </div>
    </div>
@endif

</section>
@endif
{{-- Pricing Plan End --}}
