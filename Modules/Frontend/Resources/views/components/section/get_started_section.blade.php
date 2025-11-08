
    <!-- Get Strated Section -->
    <section class="section-spacing container border-bottom">
      <div class="">
        <div class="section-title-wrap center">
            <span class="subtitle">{{ __('frontend.get_started') }}</span>
            <h3 class="section-title">{{__('messages.ready_to_transform_your_salon_experience')}}</h3>
            <p class="title-description">{{__('messages.start_today_with_a_free_trial')}}</p>
        </div>
        @if(!auth()->user())

          <div class="d-flex flex-wrap justify-content-center gap-3">
             
                  <a href="{{ route('pricing_plan', ['id' => 1]) }}" class="btn  btn-primary text-capitalize border-0">
                      <span class="font-size-14 fw-medium">{{__('messages.Start_your_free_trial')}}</span>
                  </a>
          </div>
          @endif

      </div>
  </section>