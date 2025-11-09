@extends('backend.layouts.app', ['isBanner' => false])
@section('title')
{{ 'Dashboard' }}
@endsection
@push('after-styles')
<link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/flatpickr/dist/flatpickr.min.css">
@endpush
@section('content')
<div class="block-header">
   <div class="row">
      <div class="col-lg-7 col-md-6 col-sm-12">
         <h2>{{ __('menu.dashboard') }}
            <small class="text-muted">{{ __('welcome_to') }} {{ config('app.name') }}</small>
         </h2>
      </div>
      <div class="col-lg-5 col-md-6 col-sm-12">
         <form action="{{ route('backend.home') }}" class="d-flex align-items-center float-right gap-2">
            <div class="form-group my-0">
               <input type="text" name="date_range" value="{{ $date_range }}" class="form-control dashboard-date-range"
                  placeholder="24 may 2023 to 25 June 2023" readonly="readonly">
            </div>
            <button 
               type="submit" 
               name="action" 
               value="filter" 
               class="btn btn-primary btn-icon btn-round hidden-sm-down m-l-10" 
               data-bs-toggle="tooltip"
               data-bs-title="{{ __('messages.submit_date_filter') }}">
            <i class="zmdi zmdi-forward"></i>
            </button>
         </form>
      </div>
   </div>
</div>
<div class="container-fluid">
   <div class="card widget_2">
      <ul class="row clearfix list-unstyled m-b-0">
         <li class="col-lg-3 col-md-6 col-sm-12">
            <div class="body">
               <div class="row">
                  <div class="col-7">
                     <h5 class="m-t-0">{{ __('dashboard.lbl_tot_subscriber') }}</h5>
                     <a href="{{ route('backend.users.index')}}">
                     <button
                        class="btn btn-success btn-icon btn-round hidden-sm-down">
                     <i class="zmdi zmdi-accounts-list"></i>
                     </button>
                     </a>
                  </div>
                  <div class="col-5 text-right">
                     <h2 class="">{{ $total_subscribers }}</h2>
                     <small class="info">{{ __('so_far') }}</small>
                  </div>
               </div>
            </div>
         </li>
         <li class="col-lg-3 col-md-6 col-sm-12">
            <div class="body">
               <div class="row">
                  <div class="col-7">
                     <h5 class="m-t-0">{{ __('messages.retention_rate') }}</h5>
                     <button
                        class="btn btn-success btn-icon btn-round hidden-sm-down">
                     <i class="zmdi zmdi-case-check"></i>
                     </button>
                  </div>
                  <div class="col-5 text-right">
                     <h2 class="">{{ (int) $retention_rate }}%</h2>
                     <small class="info">{{ __('so_far') }}</small>
                  </div>
               </div>
            </div>
         </li>
         <li class="col-lg-3 col-md-6 col-sm-12">
            <div class="body">
               <div class="row">
                  <div class="col-7">
                     <h5 class="m-t-0">{{ __('dashboard.lbl_tot_plan') }}</h5>
                     <a href="{{ route('backend.subscription.plans.index') }}">
                     <button
                        class="btn btn-success btn-icon btn-round hidden-sm-down">
                     <i class="zmdi zmdi-format-list-bulleted"></i>
                     </button>
                     </a>
                  </div>
                  <div class="col-5 text-right">
                     <h2 class="">{{ $total_plans }}</h2>
                     <small class="info">{{ __('so_far') }}</small>
                  </div>
               </div>
            </div>
         </li>
         <li class="col-lg-3 col-md-6 col-sm-12">
            <div class="body">
               <div class="row">
                  <div class="col-7">
                     <h5 class="m-t-0">{{ $total_revenue }}</h5>
                     <button
                        class="btn btn-success btn-icon btn-round hidden-sm-down">
                     <i class="zmdi zmdi-money"></i>
                     </button>
                  </div>
                  <div class="col-5 text-right">
                     <h2 class=""></h2>
                     <small class="info">{{ __('dashboard.lbl_tot_revenue') }}</small>
                  </div>
               </div>
            </div>
         </li>
      </ul>
   </div>
   <div class="card widget_2">
      <ul class="row clearfix list-unstyled m-b-0">
         <li class="col-lg-3 col-md-6 col-sm-12">
            <div class="body">
               <div class="row">
                  <div class="col-7">
                     <h5 class="m-t-0">{{ __('dashboard.lbl_total_subscription') }}</h5>
                     <a href="{{ route('backend.subscriptions.all_subscription')}}">
                     <button
                        class="btn btn-success btn-icon btn-round hidden-sm-down">
                     <i class="zmdi zmdi-accounts-list"></i>
                     </button>
                     </a>
                  </div>
                  <div class="col-5 text-right">
                     <h2 class="">{{ $total_subscriptions_data }}</h2>
                     <small class="info">{{ __('so_far') }}</small>
                  </div>
               </div>
            </div>
         </li>
         <li class="col-lg-3 col-md-6 col-sm-12">
            <div class="body">
               <div class="row">
                  <div class="col-7">
                     <h5 class="m-t-0">{{ __('dashboard.lbl_tot_active_subscription') }}</h5>
                     <a href="{{ route('backend.subscriptions.index')}}">
                     <button
                        class="btn btn-success btn-icon btn-round hidden-sm-down">
                     <i class="zmdi zmdi-accounts-list"></i>
                     </button>
                     </a>
                  </div>
                  <div class="col-5 text-right">
                     <h2 class="">{{ $total_active_subscriptions }}</h2>
                     <small class="info">{{ __('so_far') }}</small>
                  </div>
               </div>
            </div>
         </li>
         <li class="col-lg-3 col-md-6 col-sm-12">
            <div class="body">
               <div class="row">
                  <div class="col-7">
                     <h5 class="m-t-0">{{ __('dashboard.lbl_total_pending_subscription') }}</h5>
                     <a href="{{ route('backend.subscriptions.pending')}}">
                     <button
                        class="btn btn-success btn-icon btn-round hidden-sm-down">
                     <i class="zmdi zmdi-accounts-list"></i>
                     </button>
                     </a>
                  </div>
                  <div class="col-5 text-right">
                     <h2 class="">{{ $total_pending_subscriptions_data }}</h2>
                     <small class="info">{{ __('so_far') }}</small>
                  </div>
               </div>
            </div>
         </li>
         <li class="col-lg-3 col-md-6 col-sm-12">
            <div class="body">
               <div class="row">
                  <div class="col-7">
                     <h5 class="m-t-0">{{ __('dashboard.expiring_soon') }}</h5>
                     <a href="{{ route('backend.subscriptions.all_subscription')}}">
                     <button
                        class="btn btn-success btn-icon btn-round hidden-sm-down">
                     <i class="zmdi zmdi-accounts-list"></i>
                     </button>
                     </a>
                  </div>
                  <div class="col-5 text-right">
                     <h2 class="">{{ $expiringSoon }}</h2>
                     <small class="info">{{ __('so_far') }}</small>
                  </div>
               </div>
            </div>
         </li>
      </ul>
   </div>
   <div class="row">
      <div class="col-lg-12 mt-3">
         <div class="card">
            <div class="header d-flex justify-content-between flex-wrap">
                <h2>{{__('dashboard.lbl_tot_revenue')}}</h2>
                <ul class="header-dropdown">
                    <li class="dropdown"> <a href="javascript:void(0);" class="dropdown-toggle" data-toggle="dropdown" role="button" aria-haspopup="true" aria-expanded="false"> <i class="zmdi zmdi-more"></i> </a>
                        <ul class="dropdown-menu dropdown-menu-right slideUp float-right">
                            <li><a class="revenue-dropdown-item dropdown-item" data-type="Year">{{ __('dashboard.year') }}</a></li>
                            <li><a class="revenue-dropdown-item dropdown-item" data-type="Month">{{ __('dashboard.month') }}</a></li>
                            <li><a class="revenue-dropdown-item dropdown-item" data-type="Week">{{ __('dashboard.week') }}</a></li>
                        </ul>
                    </li>
                </ul>
            </div>
            <div class="card-body">
               <div id="total_revenue"></div>
            </div>
         </div>
      </div>
   </div>
   <div class="row clearfix">
      <!-- users -->
      <div class="col-lg-12 col-md-12 col-sm-12">
        <div class="card">
            <div class="header d-flex justify-content-between flex-wrap">
                <h2 class="mb-0">{{ __('dashboard.latest_subscribers') }}</h2>
                <a href="{{route('backend.users.index')}}">
                    <button type="button"
                        class="btn btn-primary btn-icon btn-round hidden-sm-down m-l-10">
                        <i class="zmdi zmdi-accounts-list"></i>
                    </button>
                </a>
            </div>
            <div class="card-body">
                <div class="body table-responsive pl-0 pr-0">
                <table class="table table-striped">
                    <thead>
                        <tr>
                            <th>{{ __('dashboard.vendor') }}</th>
                            <th>{{ __('dashboard.start_date') }}</th>
                            <th>{{ __('dashboard.plan') }}</th>
                            <th>{{ __('dashboard.duration') }}</th>
                            <th>{{ __('dashboard.total_amount') }}</th>
                            <th>{{ __('dashboard.status') }}</th>
                        </tr>
                    </thead>
                    <tbody>
                        @forelse($recent_subscribers as $subscriber)
                        <tr>
                            <td>
                            <div class="d-flex gap-3 align-items-center">
                                <div>{{ $subscriber['user_name'] }}</div>
                            </div>
                            </td>
                            <td>{{ formatDateOrTime($subscriber['date'],'date') }}</td>
                            <td>{{ $subscriber['plan_name'] }}</td>
                            <td>{{ $subscriber['duration'] }}</td>
                            <td>{{ $subscriber['amount'] }}</td>
                            <td>
                            <span class="text-capitalize badge bg-{{ $subscriber['status'] === 'active' ? 'success' : 'danger' }}">
                            {{ $subscriber['status'] }}
                            </span>
                            </td>
                        </tr>
                        @empty
                        <tr>
                            <td colspan="6" class="text-center">{{ __('dashboard.no_recent_subscribers') }}</td>
                        </tr>
                        @endforelse
                    </tbody>
                </table>
                </div>
            </div>
      </div>
      </div>
   </div>
</div>
@endsection
@push('after-scripts')
<script src="https://cdn.jsdelivr.net/npm/flatpickr"></script>
<!-- apexchart -->
<script src="{{ mix('js/apexcharts.min.js') }}"></script>
<script>
   revanue_chart('Year');
   
   var chart = null; // Initialize chart instance globally
   let revenueInstance;
   
   function revanue_chart(type) {
       var Base_url = "{{ url('/') }}";
       var url = Base_url + "/app/get_revenue_chart_data/" + type;
   
   // Show a loader while fetching data
   $("#revenue_loader").show();
   
   $.ajax({
       url: url,
       method: "GET",
       success: function(response) {
           $("#revenue_loader").hide();
   
           // Check if the chart container exists
           if (document.querySelectorAll('#total_revenue').length) {
               const monthlyTotals = response.data.chartData; // Fetch data from the API
               const category = response.data.category; // Fetch categories from the API
   
               const options = {
                   series: [{
                       name: "Total Revenue",
                       data: monthlyTotals, // Dynamic data from the API
                   }],
                   chart: {
                       type: 'area', // Keep your theme
                       height: 350,
                       zoom: {
                           enabled: false
                       }
                   },
                   colors: ['var(--bs-secondary)'], // Retain your color scheme
                   dataLabels: {
                       enabled: false
                   },
   
                   fill: {
                       type: "gradient",
                       gradient: {
                           shadeIntensity: 1,
                           gradientToColors: ['rgba(255, 255, 255, 0)'],
                           opacityFrom: 1,
                           opacityTo: 0,
                           stops: [0, 66],
                           inverseColors: false,
                           shade: 'light'
                       }
                   },
                   yaxis:{
                       labels: {
                           style: {
                               colors: ['var(--bs-heading-color)'],
                           },formatter: function (value) {
                               return currencyFormat(value).replace(/\.00$/, ''); 
                           }
   
                       }
                   },
                   stroke: {
                       curve: 'smooth'
                   },
                   labels: category, // Dynamic categories
                   xaxis: {
                       labels: {
                           style: {
                               colors: "var(--bs-heading-color)"
                           }
                       },
                       categories: category // Update x-axis categories dynamically
                   },
                   legend: {
                       horizontalAlign: 'left'
                   }
               };
   
               // Update or create the chart instance
               if (revenueInstance) {
                   revenueInstance.updateOptions(options); // Update the existing chart with new options
               } else {
                   revenueInstance = new ApexCharts(document.querySelector("#total_revenue"), options);
                   revenueInstance.render(); // Render the chart initially
               }
           }
       },
       error: function(xhr, status, error) {
           console.error("Error fetching revenue data:", error);
           $("#revenue_loader").hide(); // Hide the loader in case of an error
       }
   });
   }
   
            // Dropdown click event listener
               $(document).on('click', '.revenue-dropdown-item', function() {
                   const selectedText = $(this).text();
                   $('.total_revenue').text(selectedText);
                   var type = $(this).data('type');
                   revanue_chart(type); // Fetch and update the chart based on the selected type
               });
   
    document.addEventListener('DOMContentLoaded', function() {
        flatpickr('.dashboard-date-range', {
        dateFormat: "Y-m-d",
        mode: "range",
        });

    });
</script>
@endpush