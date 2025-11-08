{{-- CTA Section Start --}}
    <section class="get-started-section">
        <div class="container-fluid">
            <div class="bg-primary rounded-2 get-started-section-inner py-5 py-lg-0" style="background-image: url({{ asset('/img/frontend/plus_pattern.png') }})">
                <div class="container">
                    <div class="row align-items-center">
                        <!-- Left Section -->
                        <div class="col-lg-7">
                            <div class="section-title-wrap mb-0">
                                <span class="subtitle text-white">{{ __('frontend.get_started') }}</span>
                                <h3 class="section-title text-white"> {{setting('app_name') }} {{__('messages.use_for_multiple_business')}}</h3>
                                <p class="title-description text-white"> {{setting('app_name') }} {{__('messages.offers_tailored_tools_for_appointment')}}</p>
                            </div>
                        </div>
                        <!-- Right Section -->
                        <div class="col-lg-5 mt-lg-0 mt-4">
                            <ul class="list-inline p-0 m-0 business-list d-flex flex-wrap flex-lg-column gap-4">
                                @foreach($features as $feature)
                                <li>
                                    <div class="d-inline-flex align-items-center business-list-card section-background rounded-pill gap-4">
                                        <div class="icon flex-shrink-0">
                                            <img src="{{ asset($feature['image'] ?? default_feature_image()) }}" alt="image" loading="lazy">
                                        </div>
                                        <h6 class="m-0 font-size-18">{{ $feature['title'] }}</h6>
                                    </div>
                                </li>
                                @endforeach
                            </ul>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </section>
{{-- CTA Section End --}}
