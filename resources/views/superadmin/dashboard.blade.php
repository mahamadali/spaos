@extends('backend.layouts.app', ['isBanner' => false])

@section('title')
    {{ 'Dashboard' }}
@endsection

@section('content')
    <div class="row ">
        <div class="col-md-12 mb-3">
            <div class="d-flex justify-content-between align-items-md-center gap-3 flex-md-row flex-column mb-4">
                <h3 class="mb-0">{{ __('dashboard.lbl_performance') }}</h3>
                <div class="d-flex  align-items-center">
                  <form action="{{ route('backend.home') }}" class="d-flex align-items-center gap-2">
                    <div class="form-group my-0">
                      <input type="text" name="date_range" value="{{ $date_range }}" class="form-control dashboard-date-range"
                        placeholder="24 may 2023 to 25 June 2023" readonly="readonly">
                    </div>
                    <button type="submit" name="action" value="filter" class="btn btn-primary" data-bs-toggle="tooltip"
                      data-bs-title="{{ __('messages.submit_date_filter') }}">{{ __('dashboard.lbl_submit') }}</button>
          
                  </form>
                </div>
              </div>
            
             <div class="row row-gap-5" >
                <div class="col-md-6 col-lg-3 mt-lg-0 mt-3">
                    <a href="{{ route('backend.users.index')}}">
                    <div class="dashboard-cards revenue p-5 bg-primary-subtle rounded">
                            <div class="d-flex align-items-center justify-content-between title">
                                <h2 class="text-primary fw-semibold mb-0">{{ $total_subscribers }}</h2>
                                <div class="dashboard-icon" data-bs-toggle="tooltip" data-bs-title="{{ __('dashboard.lbl_tot_subscriber') }} Count">
                                    <svg width="20" height="20" viewBox="0 0 20 20" fill="none" xmlns="http://www.w3.org/2000/svg">
                                        <g clip-path="url(#clip0_338_8880)">
                                        <path d="M18.0117 8.64688C18.2973 10.3442 18.036 12.0883 17.2657 13.6275C16.4953 15.1666 15.2558 16.4211 13.7259 17.2099C12.1961 17.9986 10.4553 18.2807 8.75465 18.0156C7.05404 17.7504 5.4817 16.9516 4.26465 15.7346C3.04761 14.5175 2.24884 12.9452 1.98366 11.2446C1.71849 9.54397 2.00064 7.80309 2.78937 6.27329C3.5781 4.74348 4.83261 3.50393 6.37175 2.73358C7.9109 1.96324 9.65504 1.70197 11.3524 1.98751C11.5146 2.0162 11.6589 2.10779 11.754 2.24236C11.8491 2.37693 11.8871 2.54359 11.8599 2.70609C11.8328 2.86859 11.7425 3.01378 11.6088 3.11008C11.4751 3.20638 11.3088 3.246 11.1461 3.22032C10.1603 3.05448 9.15014 3.10546 8.18601 3.3697C7.22188 3.63394 6.32691 4.10511 5.56338 4.7504C4.79986 5.39569 4.18612 6.19961 3.76488 7.10622C3.34364 8.01282 3.12502 9.00032 3.12423 10C3.12275 11.683 3.7413 13.3075 4.86173 14.5633C5.31612 13.9042 5.89332 13.339 6.56173 12.8984C6.61879 12.8606 6.6867 12.8426 6.75501 12.8471C6.82333 12.8516 6.88827 12.8784 6.93985 12.9234C7.78889 13.6581 8.87411 14.0625 9.99688 14.0625C11.1197 14.0625 12.2049 13.6581 13.0539 12.9234C13.1055 12.8784 13.1704 12.8516 13.2388 12.8471C13.3071 12.8426 13.375 12.8606 13.432 12.8984C14.1011 13.339 14.6791 13.9042 15.1344 14.5633C16.2557 13.3078 16.8751 11.6833 16.8742 10C16.8743 9.61574 16.8424 9.23213 16.7789 8.85313C16.7645 8.77183 16.7664 8.68849 16.7844 8.60791C16.8024 8.52734 16.8362 8.45113 16.8839 8.3837C16.9315 8.31626 16.992 8.25893 17.062 8.21502C17.1319 8.17111 17.2098 8.14149 17.2912 8.12786C17.3727 8.11424 17.456 8.11688 17.5364 8.13565C17.6168 8.15441 17.6927 8.18892 17.7597 8.23717C17.8267 8.28543 17.8834 8.34649 17.9267 8.41681C17.97 8.48713 17.9989 8.56533 18.0117 8.64688ZM6.56173 9.37501C6.56173 10.0549 6.76333 10.7195 7.14105 11.2848C7.51877 11.8501 8.05563 12.2907 8.68375 12.5508C9.31187 12.811 10.003 12.8791 10.6699 12.7465C11.3367 12.6138 11.9492 12.2864 12.4299 11.8057C12.9106 11.3249 13.238 10.7124 13.3707 10.0456C13.5033 9.37882 13.4352 8.68766 13.1751 8.05953C12.9149 7.43141 12.4743 6.89455 11.909 6.51683C11.3437 6.13911 10.6791 5.93751 9.99923 5.93751C9.08755 5.93751 8.2132 6.29967 7.56855 6.94433C6.92389 7.58899 6.56173 8.46333 6.56173 9.37501ZM18.5664 2.68282C18.5084 2.62471 18.4394 2.57861 18.3636 2.54716C18.2877 2.51571 18.2064 2.49952 18.1242 2.49952C18.0421 2.49952 17.9608 2.51571 17.8849 2.54716C17.809 2.57861 17.7401 2.62471 17.682 2.68282L15.6242 4.74142L14.8164 3.93282C14.7583 3.87475 14.6894 3.82869 14.6135 3.79726C14.5377 3.76584 14.4563 3.74966 14.3742 3.74966C14.2921 3.74966 14.2108 3.76584 14.1349 3.79726C14.059 3.82869 13.9901 3.87475 13.932 3.93282C13.874 3.99089 13.8279 4.05983 13.7965 4.1357C13.7651 4.21157 13.7489 4.29289 13.7489 4.37501C13.7489 4.45713 13.7651 4.53845 13.7965 4.61432C13.8279 4.69019 13.874 4.75913 13.932 4.8172L15.182 6.0672C15.2401 6.12531 15.309 6.17141 15.3849 6.20286C15.4608 6.23431 15.5421 6.2505 15.6242 6.2505C15.7064 6.2505 15.7877 6.23431 15.8636 6.20286C15.9394 6.17141 16.0084 6.12531 16.0664 6.0672L18.5664 3.5672C18.6245 3.50915 18.6706 3.44022 18.7021 3.36435C18.7335 3.28847 18.7497 3.20714 18.7497 3.12501C18.7497 3.04287 18.7335 2.96154 18.7021 2.88567C18.6706 2.8098 18.6245 2.74087 18.5664 2.68282Z" fill="currentColor"/>
                                        </g>
                                        <defs>
                                        <clipPath>
                                        <rect width="20" height="20" fill="white"/>
                                        </clipPath>
                                        </defs>
                                    </svg>

                                </div>
                            </div>
                            <h5 class="mb-0">{{ __('dashboard.lbl_tot_subscriber') }}</h5>
                    </div>
                </a>
                </div>

                 <div class="col-md-6 col-lg-3 mt-lg-0 mt-3">
                    <a href="{{ route('backend.subscriptions.all_subscription')}}">

                    <div class="p-5 dashboard-cards appointments bg-primary-subtle rounded">
                        <div class="d-flex align-items-center justify-content-between title">
                            <h2 class="text-primary fw-semibold mb-0">{{ $total_subscriptions_data }}</h2>
                            <div class="dashboard-icon">
                                <i class="fa-solid fa-users" data-bs-toggle="tooltip"
                                    data-bs-title="{{ __('dashboard.lbl_total_subscription') }} Count"></i>
                            </div>
                        </div>
                        <h5 class="mb-0">{{ __('dashboard.lbl_total_subscription') }}</h5>
                    </div>
                </a>

                </div>

                

                <div class="col-md-6 col-lg-3 mt-lg-0 mt-3">
                    <a href="{{ route('backend.subscriptions.index')}}">

                    <div class="p-5 dashboard-cards appointments bg-primary-subtle rounded">
                        <div class="d-flex align-items-center justify-content-between title">
                            <h2 class="text-primary fw-semibold mb-0">{{ $total_active_subscriptions }}</h2>
                            <div class="dashboard-icon">
                                <i class="fa-solid fa-users" data-bs-toggle="tooltip"
                                    data-bs-title="{{ __('dashboard.lbl_tot_active_subscription') }} Count"></i>
                            </div>
                        </div>
                        <h5 class="mb-0">{{ __('dashboard.lbl_tot_active_subscription') }}</h5>
                    </div>
                </a>
                </div>

                <div class="col-md-6 col-lg-3 mt-lg-0 mt-3">
                    <a  href="{{ route('backend.subscriptions.pending') }}">
                    <div class="p-5 dashboard-cards appointments bg-primary-subtle rounded">
                        <div class="d-flex align-items-center justify-content-between title">
                            <h2 class="text-primary fw-semibold mb-0">{{ $total_pending_subscriptions_data }}</h2>
                            <div class="dashboard-icon">
                                <i class="fa-solid fa-users" data-bs-toggle="tooltip"
                                    data-bs-title="{{ __('dashboard.lbl_total_pending_subscription') }} Count"></i>
                            </div>
                        </div>
                        <h5 class="mb-0">{{ __('dashboard.lbl_total_pending_subscription') }}</h5>
                    </div>
                </a>
                </div>

                

                <div class="col-md-6 col-lg-3 mt-lg-0 mt-3">
                    <div class="card dashboard-cards revenue p-5 bg-primary-subtle rounded">
                        <div class="d-flex align-items-center justify-content-between title">
                            <h2 class="text-primary fw-semibold mb-0">{{ (int) $retention_rate }}%</h2>
                            <div class="dashboard-icon"  data-bs-toggle="tooltip" data-bs-title="Retention Rate Percentage">
                                <svg width="20" height="20" viewBox="0 0 20 20" fill="none" xmlns="http://www.w3.org/2000/svg">
                                    <g clip-path="url(#clip0_338_8883)">
                                    <path d="M6.56274 9.37485C6.56274 8.69497 6.76435 8.03037 7.14207 7.46508C7.51978 6.89978 8.05665 6.45919 8.68477 6.19901C9.31289 5.93884 10.0041 5.87076 10.6709 6.0034C11.3377 6.13604 11.9502 6.46343 12.4309 6.94417C12.9117 7.42491 13.2391 8.03742 13.3717 8.70423C13.5043 9.37103 13.4363 10.0622 13.1761 10.6903C12.9159 11.3184 12.4753 11.8553 11.91 12.233C11.3447 12.6107 10.6801 12.8123 10.0002 12.8123C9.08856 12.8123 8.21422 12.4502 7.56956 11.8055C6.92491 11.1609 6.56274 10.2865 6.56274 9.37485ZM16.419 13.842C16.2798 13.7612 16.1152 13.7357 15.958 13.7706C15.8008 13.8055 15.6625 13.8982 15.5706 14.0303C15.4359 14.2158 15.2919 14.3944 15.1393 14.5655C14.6846 13.9055 14.1069 13.3394 13.4377 12.8983C13.3807 12.8605 13.3128 12.8424 13.2445 12.8469C13.1761 12.8514 13.1112 12.8783 13.0596 12.9233C12.2106 13.658 11.1254 14.0623 10.0026 14.0623C8.87981 14.0623 7.79459 13.658 6.94556 12.9233C6.8936 12.8772 6.82776 12.8497 6.75844 12.8451C6.68911 12.8406 6.62025 12.8593 6.56274 12.8983C5.89349 13.3386 5.31548 13.9039 4.8604 14.5631C3.88435 13.4671 3.28622 12.0865 3.15415 10.6248H4.35728C4.45844 10.6264 4.55854 10.6041 4.64939 10.5595C4.74023 10.515 4.81924 10.4495 4.87993 10.3686C4.968 10.2482 5.0101 10.1003 4.99864 9.95157C4.98718 9.80285 4.9229 9.66313 4.81743 9.55766L2.94243 7.68266C2.88439 7.62455 2.81545 7.57845 2.73958 7.547C2.66371 7.51555 2.58238 7.49936 2.50024 7.49936C2.41811 7.49936 2.33678 7.51555 2.26091 7.547C2.18503 7.57845 2.1161 7.62455 2.05806 7.68266L0.183056 9.55766C0.0775837 9.66313 0.013311 9.80285 0.0018489 9.95157C-0.00961317 10.1003 0.0324913 10.2482 0.120556 10.3686C0.181249 10.4495 0.260254 10.515 0.351099 10.5595C0.441945 10.6041 0.542048 10.6264 0.643212 10.6248H1.89868C2.02651 12.2865 2.66221 13.8686 3.7196 15.1568C4.77699 16.4449 6.20491 17.3768 7.80979 17.826C9.41466 18.2751 11.1188 18.22 12.6913 17.6679C14.2638 17.1158 15.6284 16.0935 16.6002 14.7397C16.6504 14.6699 16.6856 14.5905 16.7037 14.5065C16.7217 14.4225 16.7223 14.3356 16.7053 14.2514C16.6883 14.1671 16.6541 14.0873 16.6048 14.0169C16.5555 13.9464 16.4923 13.8869 16.419 13.842ZM19.9526 9.76079C19.9053 9.64657 19.8252 9.54895 19.7225 9.48025C19.6197 9.41155 19.4989 9.37487 19.3752 9.37485H18.1018C17.974 7.71321 17.3383 6.13106 16.2809 4.84292C15.2235 3.55477 13.7956 2.62294 12.1907 2.17374C10.5858 1.72455 8.88164 1.77974 7.30919 2.33181C5.73673 2.88389 4.37208 3.90615 3.40024 5.26C3.30348 5.39468 3.26418 5.56229 3.29099 5.72594C3.31781 5.8896 3.40853 6.0359 3.54321 6.13266C3.67789 6.22942 3.8455 6.26872 4.00915 6.24191C4.1728 6.2151 4.31911 6.12437 4.41587 5.98969C5.23351 4.85129 6.37956 3.98998 7.70045 3.52121C9.02134 3.05243 10.454 2.99857 11.8063 3.36686C13.1587 3.73514 14.3661 4.50797 15.267 5.58177C16.1678 6.65558 16.7188 7.97905 16.8463 9.37485H15.6252C15.5016 9.37475 15.3806 9.41135 15.2778 9.48002C15.1749 9.54869 15.0947 9.64634 15.0474 9.7606C15 9.87487 14.9876 10.0006 15.0118 10.1219C15.0359 10.2432 15.0955 10.3546 15.1831 10.442L17.0581 12.317C17.1161 12.3751 17.185 12.4212 17.2609 12.4527C17.3368 12.4842 17.4181 12.5003 17.5002 12.5003C17.5824 12.5003 17.6637 12.4842 17.7396 12.4527C17.8155 12.4212 17.8844 12.3751 17.9424 12.317L19.8174 10.442C19.9048 10.3546 19.9643 10.2432 19.9883 10.1219C20.0124 10.0006 20 9.87498 19.9526 9.76079Z" fill="currentColor"/>
                                    </g>
                                    <defs>
                                    <clipPath>
                                    <rect width="20" height="20" fill="white"/>
                                    </clipPath>
                                    </defs>
                                </svg>
                            </div>
                        </div>
                        <h5 class="mb-0">{{__('messages.retention_rate')}}</h5>
                    </div>
                </div>

            

                <div class="col-md-6 col-lg-3 mt-md-0 mt-3">
                    <a href="{{ route('backend.subscription.plans.index')}}">
                    <div class="dashboard-cards services p-5 bg-primary-subtle rounded">
                        <div class="d-flex align-items-center justify-content-between title">
                            <h2 class="text-primary fw-semibold mb-0">{{ $total_plans??0 }}</h2>
                            <div class="dashboard-icon" data-bs-toggle="tooltip" data-bs-title="{{ __('dashboard.lbl_total_plans') }}">
                                <i class="fa-solid fa-table"></i>
                                
                            </div>
                        </div>
                        <h5 class="mb-0">{{ __('dashboard.lbl_tot_plan') }}</h5>
                    </div>
                </a>
                </div>
                <!-- Add this after your existing cards -->
                <div class="col-md-6 col-lg-3 mt-lg-0 mt-3">
                        <div class="dashboard-cards warning p-5 bg-primary-subtle rounded">
                            <div class="d-flex align-items-center justify-content-between title">
                                <h2 class="text-primary fw-semibold mb-0">{{ $expiringSoon }}</h2>
                                <div class="dashboard-icon" data-bs-toggle="tooltip" data-bs-title="{{ __('dashboard.expiring_soon') }}">
                                <i class="fa-solid fa-hourglass-start"></i>
                                </div>
                            </div>
                            <h5 class="mb-0">{{ __('dashboard.expiring_soon') }}</h5>
                        </div>
                    </a>
                </div>
                <div class="col-md-6 col-lg-3 mt-md-0 mt-3">
                    <a href="{{ route('backend.subscriptions.all_subscription')}}">
                    <div class="dashboard-cards services p-5 bg-primary-subtle rounded">
                        <div class="d-flex align-items-center justify-content-between title">
                            <h2 class="text-primary fw-semibold mb-0">{{ $total_revenue }}</h2>
                            <div class="dashboard-icon" data-bs-toggle="tooltip" data-bs-title="{{ __('dashboard.lbl_tot_revenue') }}">
                                <svg width="20" height="20" viewBox="0 0 20 20" fill="none" xmlns="http://www.w3.org/2000/svg">
                                    <g clip-path="url(#clip0_338_8877)">
                                    <path d="M12.5 11.875C12.5 12.2065 12.3683 12.5245 12.1339 12.7589C11.8995 12.9933 11.5815 13.125 11.25 13.125H10.625V10.625H11.25C11.5815 10.625 11.8995 10.7567 12.1339 10.9911C12.3683 11.2255 12.5 11.5435 12.5 11.875ZM18.125 10C18.125 11.607 17.6485 13.1779 16.7557 14.514C15.8629 15.8502 14.594 16.8916 13.1093 17.5065C11.6247 18.1215 9.99099 18.2824 8.4149 17.9689C6.8388 17.6554 5.39106 16.8815 4.25476 15.7452C3.11846 14.6089 2.34463 13.1612 2.03112 11.5851C1.71762 10.009 1.87852 8.37535 2.49348 6.8907C3.10844 5.40605 4.14985 4.1371 5.486 3.24431C6.82214 2.35152 8.39303 1.875 10 1.875C12.1542 1.87727 14.2195 2.73403 15.7427 4.25727C17.266 5.78051 18.1227 7.84581 18.125 10ZM13.75 11.875C13.75 11.212 13.4866 10.5761 13.0178 10.1072C12.5489 9.63839 11.913 9.375 11.25 9.375H10.625V6.875H10.9375C11.269 6.875 11.587 7.0067 11.8214 7.24112C12.0558 7.47554 12.1875 7.79348 12.1875 8.125C12.1875 8.29076 12.2534 8.44973 12.3706 8.56694C12.4878 8.68415 12.6467 8.75 12.8125 8.75C12.9783 8.75 13.1372 8.68415 13.2544 8.56694C13.3717 8.44973 13.4375 8.29076 13.4375 8.125C13.4375 7.46196 13.1741 6.82607 12.7053 6.35723C12.2364 5.88839 11.6005 5.625 10.9375 5.625H10.625V5C10.625 4.83424 10.5592 4.67527 10.4419 4.55806C10.3247 4.44085 10.1658 4.375 10 4.375C9.83424 4.375 9.67527 4.44085 9.55806 4.55806C9.44085 4.67527 9.375 4.83424 9.375 5V5.625H9.0625C8.39946 5.625 7.76358 5.88839 7.29474 6.35723C6.8259 6.82607 6.5625 7.46196 6.5625 8.125C6.5625 8.78804 6.8259 9.42393 7.29474 9.89277C7.76358 10.3616 8.39946 10.625 9.0625 10.625H9.375V13.125H8.75C8.41848 13.125 8.10054 12.9933 7.86612 12.7589C7.6317 12.5245 7.5 12.2065 7.5 11.875C7.5 11.7092 7.43416 11.5503 7.31695 11.4331C7.19974 11.3158 7.04076 11.25 6.875 11.25C6.70924 11.25 6.55027 11.3158 6.43306 11.4331C6.31585 11.5503 6.25 11.7092 6.25 11.875C6.25 12.538 6.5134 13.1739 6.98224 13.6428C7.45108 14.1116 8.08696 14.375 8.75 14.375H9.375V15C9.375 15.1658 9.44085 15.3247 9.55806 15.4419C9.67527 15.5592 9.83424 15.625 10 15.625C10.1658 15.625 10.3247 15.5592 10.4419 15.4419C10.5592 15.3247 10.625 15.1658 10.625 15V14.375H11.25C11.913 14.375 12.5489 14.1116 13.0178 13.6428C13.4866 13.1739 13.75 12.538 13.75 11.875ZM7.8125 8.125C7.8125 8.45652 7.9442 8.77446 8.17862 9.00888C8.41304 9.2433 8.73098 9.375 9.0625 9.375H9.375V6.875H9.0625C8.73098 6.875 8.41304 7.0067 8.17862 7.24112C7.9442 7.47554 7.8125 7.79348 7.8125 8.125Z" fill="currentColor"/>
                                    </g>
                                    <defs>
                                    <clipPath>
                                    <rect width="20" height="20" fill="white"/>
                                    </clipPath>
                                    </defs>
                                </svg>
                            </div>
                        </div>
                        <h5 class="mb-0">{{ __('dashboard.lbl_tot_revenue') }}</h5>
                    </div>
                  </a>
                </div>
            </div>
        </div>
       
        <div class="col-lg-12 mt-3">
            <div class="card card-block card-stretch card-height">
                <div class="card-body">
                    <div class=" d-flex justify-content-between flex-wrap">
                        <h4 class="card-title">{{__('dashboard.lbl_tot_revenue')}}</h4>
                        <div class="dropdown">
                            <button class="btn btn-light dropdown-toggle total_revenue" type="button" id="dropdownTotalRevenue" data-bs-toggle="dropdown" aria-expanded="false">
                                {{ __('dashboard.year') }}
                            </button>
                            <ul class="dropdown-menu dropdown-menu-soft-primary sub-dropdown" aria-labelledby="dropdownTotalRevenue">
                                <li><a class="revenue-dropdown-item dropdown-item" data-type="Year">{{ __('dashboard.year') }}</a></li>
                                <li><a class="revenue-dropdown-item dropdown-item" data-type="Month">{{ __('dashboard.month') }}</a></li>
                                <li><a class="revenue-dropdown-item dropdown-item" data-type="Week">{{ __('dashboard.week') }}</a></li>
                            </ul>
                        </div>
                    </div>
                     <div id="total_revenue"></div>
                </div>
            </div>
        </div>
     
        <!-- users -->
         <div class="card card-block card-stretch card-height">
            <div class="card-body">
                <div class="d-flex align-items-center justify-content-between gap-3 flex-wrap mb-3">
                    <h5 class="mb-0">{{ __('dashboard.latest_subscribers') }}</h5>
                    <a class="btn btn-primary" href="{{route('backend.users.index')}}">{{ __('dashboard.view_all') }}</a>
                </div>
                <div class="table-responsive border rounded">
                    <table class="table m-0">
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
@endsection



@push('after-scripts')


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
