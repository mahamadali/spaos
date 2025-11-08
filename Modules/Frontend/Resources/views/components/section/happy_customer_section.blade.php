{{-- happy customer Start --}}
    <section class="section-spacing-top overflow-hidden">
      <div class="section-title-wrap center">
        <span class="subtitle">{{__('messages.happy_customer')}}</span>
        <h3 class="section-title">{{__('messages.happy_customer_from_frezka')}}</h3>
      </div>
        <div class="container-fluid px-0 ">
          <div class="row">
            <div class="col-12">
              <div class="slick-general" data-items="4" data-items-desktop="4" data-items-laptop="3" data-items-tab="2" data-items-mobile-sm="2" data-items-mobile="1" data-speed="1000" data-autoplay="true" data-center="true" data-infinite="true" data-navigation="false" data-pagination="true" data-spacing="12">
                @foreach($reviews as $review)<div class="slick-item">
                  <x-frontend::card.card_customer :review="$review"/>
                </div>
                @endforeach
              </div>
            </div>
          </div>
        </div>
    </section>
{{-- happy customer End --}}


