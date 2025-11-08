  <section class="section-spacing-bottom">
 
    <div class="container-fluid">
        <div class="section-title-wrap center">
            <span class="subtitle">{{setting('app_name') }} {{ __('messages.features') }}  </span>
            <h3 class="section-title">{{ __('messages.features_subtitle')  }}</h3>
        </div>
        <div class="row">
            <!-- salon-export -->
            <div class="col-xxl-3 col-xl-5 col-md-6 order-xxl-1 order-md-1">
                <div class="booking-schedling-card position-relative" style="background-image: url('{{ asset('/img/frontend/image-salon.jpg') }}')">
                    <div class="main-wrap d-flex justify-content-between flex-column h-100 w-100">
                        <h5 class="mb-0 w-50">{{__('messages.booking_and_scheduling')}}</h5>
                        <div class="booking-schedling-service p-4 rounded-3" style="background-image: url('{{ asset('/img/frontend/box-pattern-small.png') }}'); background-repet: no-repet;" >
                            <div class="d-flex justify-content-between align-items-center">
                                <span class="font-size-14 bg-primary-subtle rounded-5 py-1 px-3">{{__('messages.pretty_nail')}}</span>
                                <h4 class="text-body m-0"><i class="ph ph-calendar-check"></i></h4>
                            </div>
                            <h5 class="name-employ mt-2 font-size-18 mb-0">{{__('messages.Emma_Pristine')}}</h5>
                          
                        </div>
                    </div>
                </div>
            </div>
            <div class="col-xxl-9 col-xl-12 order-xxl-2 order-md-3 my-5 mb-md-0 my-xxl-0">
                <div class="subscription-box px-4 py-5 w-100 h-100 rounded-3" style="background-image: url('{{ asset('/img/frontend/yellow-pattern.png') }}'); background-repet: no-repet;">
                    <div class="row h-100">
                        <div class="col-md-6">
                            <div class="d-flex flex-column justify-content-between h-100">
                                <div class="content-box">
                                    <h4>{{ __('messages.features_des_title')  }}</h4>
                                    <p>{!! nl2br(__('messages.features_des_content')) !!}</p>

                                </div>
                                <div class="mt-3">
                                    <a class="text-decoration-underline text-secondary" href="{{ route('pricing') }}">{{__('messages.Choose_a_plan_Grow_with_ease')}}</a>
                                </div>
                            </div>
                        </div>
                        <div class="col-md-6 mt-5 mt-md-0 align-self-center">
                            <!-- pricing card -->
                            <div class="px-sm-5 px-0 position-relative about-subscription-plan-wrapper">
                                <div class="unlock-benefits">
                                    <div class="d-inline-flex align-items-center gap-2">
                                        <div class="flex-shrink-0">
                                            <svg width="24" height="24" viewBox="0 0 24 24" fill="none" xmlns="http://www.w3.org/2000/svg">
                                                <g>
                                                    <path d="M23.9261 6.06067C23.7523 5.71323 23.3294 5.57383 22.9826 5.74619L20.1702 7.15244C19.8227 7.32616 19.682 7.74845 19.8557 8.09589C20.0292 8.44192 20.4493 8.58409 20.7991 8.41038L23.6116 7.00413C23.959 6.83041 24.0998 6.40811 23.9261 6.06067Z" fill="currentColor"/>
                                                    <path d="M23.6116 14.1836L20.7991 12.7774C20.4517 12.6043 20.0301 12.7437 19.8557 13.0918C19.682 13.4393 19.8227 13.8616 20.1702 14.0353L22.9826 15.4416C23.3334 15.6158 23.7531 15.472 23.9261 15.1271C24.0998 14.7796 23.959 14.3573 23.6116 14.1836Z" fill="currentColor"/>
                                                    <path d="M16.1717 11.3436H5.62495V7.73428C5.62495 5.79586 7.20216 4.2187 9.14053 4.2187C11.0789 4.2187 12.6561 5.79591 12.6561 7.73428V9.14053C12.6561 9.52917 12.9706 9.84366 13.3592 9.84366H16.1717C16.5603 9.84366 16.8748 9.52917 16.8748 9.14053V7.73428C16.8748 3.46959 13.4052 0 9.14053 0C4.87584 0 1.40625 3.46959 1.40625 7.73428V11.4731C0.589594 11.7644 0 12.5375 0 13.4529V21.8903C0 23.0535 0.946219 23.9997 2.10933 23.9997H16.1717C17.3348 23.9997 18.281 23.0535 18.281 21.8903V13.4529C18.281 12.2898 17.3348 11.3436 16.1717 11.3436ZM9.84366 18.2453V20.4841C9.84366 20.8727 9.52917 21.1872 9.14053 21.1872C8.75189 21.1872 8.43741 20.8727 8.43741 20.4841V18.2453C7.62075 17.954 7.03116 17.1809 7.03116 16.2654C7.03116 15.1023 7.97738 14.1561 9.14053 14.1561C10.3037 14.1561 11.2499 15.1023 11.2499 16.2654C11.2499 17.1809 10.6603 17.954 9.84366 18.2453Z" fill="currentColor"/>
                                                    <path d="M23.2968 9.89062H20.4844C20.0957 9.89062 19.7812 10.2051 19.7812 10.5938C19.7812 10.9824 20.0957 11.2969 20.4844 11.2969H23.2968C23.6855 11.2969 24 10.9824 24 10.5938C24 10.2051 23.6855 9.89062 23.2968 9.89062Z" fill="currentColor"/>
                                                </g>
                                                <defs>
                                                    <clipPath>
                                                        <rect width="24" height="24" fill="white"/>
                                                    </clipPath>
                                                </defs>
                                            </svg>
                                        </div>
                                        <span class="font-size-14 fw-medium">{{__('messages.Unlock_benefits')}}</span>
                                    </div>
                                </div>
                                <x-frontend::card.card_price_plan :plan="$plan" />
                       
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        
        </div>
        <div class="feature-container">
            <div class="row gy-4 row-cols-1 row-cols-sm-1 row-cols-md-2 row-cols-lg-3 row-cols-xl-4">
                
                @foreach($features as $feature)
                <div class="col">
                    <x-frontend::card.card_feature :feature="$feature" />
                </div>
                @endforeach
            </div>
            <div class="d-flex justify-content-center mt-5 pt-xl-5">
                    <a href="{{ route('feature') }}" class="btn btn-secondary text-white">{{__('messages.see_all_features')}}</a>
            </div>
        </div>
    </div>
</section>
