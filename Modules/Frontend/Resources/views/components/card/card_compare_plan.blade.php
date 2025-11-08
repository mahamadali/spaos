<section class="pricing-comparison section-spacing-top">
    <div class="container">
        <div class="section-title-wrap center">
            <p class="subtitle">{{ __('frontend.compare_plan') }}</p>
            <h2 class="section-title">{{ __('frontend.comparison_plan') }}</h2>
            <p class="title-description">{{__('messages.choose_a_plan_that_best_fits_your_salon')}}</p>
        </div>

        <div class="table-responsive pricing-table">
            <table class="table mb-0" id="monthyplan" >
                <thead>
                    <tr>
                        <th class="col-3 pt-0 p-3 align-top"></th>
                        @foreach($data['plan'] as $plan)
                        @if(isset($plan) && $plan->status == 1 && $plan->type == 'Monthly')
                            <th class="col-3 pt-0 p-3 align-top">
                                <div class="section-background rounded p-lg-4 p-3">
                                    <div class="d-flex align-items-center mb-3">
                                        <h4 class="text-primary mb-0">{{ \Currency::formatSuperadmin($plan->price ?? 0) }} </h4>
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
                                    <div class="d-flex mb-2 align-items-center gap-3">
                                        <h6 class="m-0 font-size-18">{{ $plan->name ?? '-'}}</h6>

                                        @if($plan->has_discount == 1 && $plan->discounted_price != null)
                                            <span class="mx-1">
                                                {{ \Currency::formatSuperadmin($plan->discount_value ?? 0) }}
                                                @if($plan->discount_type === 'percentage')
                                                    %
                                                @endif
                                            OFF</span>
                                        @endif

                                    </div>

                                    <p class="font-size-14 mb-5">{{ $plan->description ?? ''}}</p>

                                    @if( !isset($plan['currentPlanId']) || isset($plan['currentPlanId']) )
                                        @if ( $plan['currentPlanId'] == null || $plan['currentPlanId'] !== $plan->id)
                                            <a href="{{ route('pricing_plan') }}" class="btn btn-secondary">{{ __('frontend.get_started') }}</a>

                                        @else
                                            <a href="{{ route('pricing_plan', ['id' => $plan->id]) }}" class="btn btn-secondary">{{ __('frontend.renew') }}</a>
                                        @endif
                                    </div>
                                </th>
                            @endif
                        @endif
                        @endforeach
                    </tr>
                </thead>
<tbody>
    @foreach($menus as $menu)
        <tr>
            <td>{{ __($menu->title) ?? '-' }}</td>

            @foreach($data['plan'] as $plan)
                @if(isset($plan) && $plan->status == 1 && $plan->type == 'Monthly')
                    @php
                        $permissionIds = $plan->permission_ids
                            ? array_map(function($item) {
                                return trim(preg_replace('/[\"\[\]]/', '', $item));
                            }, explode(',', $plan->permission_ids))
                            : [];

                        $hasPermission = empty(array_diff($menu->permission, $permissionIds));
                    @endphp

                    <td class="text-center">
                        @if($hasPermission)
                            <i class="ph-fill ph-check-circle text-success"></i>
                        @else
                            <i class="ph-fill ph-x-circle text-danger"></i>
                        @endif
                    </td>
                @endif
            @endforeach
        </tr>
    @endforeach
</tbody>


                    <tr>
                        <td class="col-3">{{ __('frontend.limit') }}</td>
                        @foreach($data['plan'] as $plan)
                            @if(isset($plan) && $plan->status == 1 && $plan->type == 'Monthly')
                                <th class="col-3"></th>
                            @endif
                        @endforeach
                    </tr>
                    <tbody class="bg-white limit-table">
                        @foreach($limits as $limit)
                            <tr>
                                <th class="ps-3 pt-3">{{ $limit }}</th>
                                @foreach($data['plan'] as $plan)
                                    @if(isset($plan) && $plan->status == 1 && $plan->type == 'Monthly')
                                        @if($limit == "Appointments" && isset($plan->max_appointment) && $plan->max_appointment > 0)
                                            <td class="text-center">{{ $plan->max_appointment }}</td>
                                        @elseif($limit == "Branches" && isset($plan->max_branch) && $plan->max_branch > 0)
                                            <td class="text-center">{{ $plan->max_branch }}</td>
                                        @elseif($limit == "Services" && isset($plan->max_service) && $plan->max_service > 0)
                                            <td class="text-center">{{ $plan->max_service }}</td>
                                        @elseif($limit == "Staff" && isset($plan->max_staff) && $plan->max_staff > 0)
                                            <td class="text-center">{{ $plan->max_staff }}</td>
                                        @elseif($limit == "Customer" && isset($plan->max_customer) && $plan->max_customer > 0)
                                            <td class="text-center">{{ $plan->max_customer }}</td>
                                        @else
                                            <td class="text-center">
                                                <i class="ph-fill ph-x-circle text-danger"></i>
                                            </td>
                                        @endif
                                    @endif
                                @endforeach
                            </tr>
                        @endforeach
                    </tbody>
                </tbody>
            </table>


            <table class="table mb-0 d-none" id="yearlyplan">
                <thead>
                    <tr>
                        <th class="col-3 pt-0 p-3 align-top"></th>
                        @foreach($data['plan'] as $plan)
                        @if(isset($plan) && $plan->status == 1 && $plan->type == 'Yearly')
                            <th class="col-3 pt-0 p-3 align-top">
                                <div class="section-background rounded p-lg-4 p-3">
                                    <div class="d-flex align-items-center mb-3">
                                        <h4 class="text-primary mb-0">{{ \Currency::formatSuperadmin($plan->price ?? 0) }} </h4>
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
                                    <div class="d-flex mb-2 align-items-center gap-3">
                                        <h6 class="m-0 font-size-18">{{ $plan->name ?? '-'}}</h6>

                                    </div>

                                    <p class="font-size-14 mb-5">{{ $plan->description ?? ''}}</p>

                                    @if( !isset($plan['currentPlanId']) || isset($plan['currentPlanId']) )
                                        @if ( $plan['currentPlanId'] == null || $plan['currentPlanId'] !== $plan->id)
                                            <a href="{{ route('pricing_plan') }}" class="btn btn-secondary">{{ __('frontend.get_started') }}</a>

                                        @else
                                            <a href="{{ route('pricing_plan', ['id' => $plan->id]) }}" class="btn btn-secondary">{{ __('frontend.renew') }}</a>
                                        @endif
                                    </div>
                                </th>
                            @endif
                        @endif
                        @endforeach
                    </tr>
                </thead>
             <tbody>
    @foreach($menus as $menu)
        <tr>
            <td>{{ __($menu->title) ?? '-' }}</td>

            @foreach($data['plan'] as $plan)
                @if(isset($plan) && $plan->status == 1 && $plan->type == 'Yearly')
                    @php
                        $permissionIds = $plan->permission_ids
                            ? array_map(function($item) {
                                return trim(preg_replace('/[\"\[\]]/', '', $item));
                            }, explode(',', $plan->permission_ids))
                            : [];

                        $hasPermission = empty(array_diff($menu->permission, $permissionIds));
                    @endphp

                    <td class="text-center">
                        @if($hasPermission)
                            <i class="ph-fill ph-check-circle text-success"></i>
                        @else
                            <i class="ph-fill ph-x-circle text-danger"></i>
                        @endif
                    </td>
                @endif
            @endforeach
        </tr>
    @endforeach
</tbody>



                    <tr>
                        <td class="col-3">{{ __('frontend.limit') }}</td>
                        @foreach($data['plan'] as $plan)
                            @if(isset($plan) && $plan->status == 1 && $plan->type == 'Yearly')
                                <th class="col-3"></th>
                            @endif
                        @endforeach
                    </tr>
                    <tbody class="bg-white limit-table">
                        @foreach($limits as $limit)
                            <tr>
                                <th class="ps-3 pt-3">{{ $limit }}</th>
                                @foreach($data['plan'] as $plan)
                                    @if(isset($plan) && $plan->status == 1 && $plan->type == 'Yearly')
                                        @if($limit == "Appointments" && isset($plan->max_appointment) && $plan->max_appointment > 0)
                                            <td class="text-center">{{ $plan->max_appointment }}</td>
                                        @elseif($limit == "Branches" && isset($plan->max_branch) && $plan->max_branch > 0)
                                            <td class="text-center">{{ $plan->max_branch }}</td>
                                        @elseif($limit == "Services" && isset($plan->max_service) && $plan->max_service > 0)
                                            <td class="text-center">{{ $plan->max_service }}</td>
                                        @elseif($limit == "Staff" && isset($plan->max_staff) && $plan->max_staff > 0)
                                            <td class="text-center">{{ $plan->max_staff }}</td>
                                        @elseif($limit == "Customer" && isset($plan->max_customer) && $plan->max_customer > 0)
                                            <td class="text-center">{{ $plan->max_customer }}</td>
                                        @else
                                            <td class="text-center">
                                                <i class="ph-fill ph-x-circle text-danger"></i>
                                            </td>
                                        @endif
                                    @endif
                                @endforeach
                            </tr>
                        @endforeach
                    </tbody>
                </tbody>
            </table>
        </div>
    </div>

    <script>
    document.addEventListener("DOMContentLoaded", function () {
        const monthlyTab = document.getElementById("pills-monthly-tab");
        const yearlyTab = document.getElementById("pills-yearly-tab");

        const monthlyTable = document.getElementById("monthyplan");
        const yearlyTable = document.getElementById("yearlyplan");

        function toggleTables(activeTable, inactiveTable) {
            activeTable.classList.remove("d-none");
            inactiveTable.classList.add("d-none");
        }

        if (monthlyTab && yearlyTab && monthlyTable && yearlyTable) {
            monthlyTab.addEventListener("click", function () {
                toggleTables(monthlyTable, yearlyTable);
            });

            yearlyTab.addEventListener("click", function () {
                toggleTables(yearlyTable, monthlyTable);
            });
        }
    });
</script>

</section>
