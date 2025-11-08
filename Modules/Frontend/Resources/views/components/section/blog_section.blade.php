 {{-- Blog Section Start --}}
 @if ($blogs !== null && $blogs->isNotEmpty())    
   
 <section class="section-spacing-top blog-section">
  <div class="container">
      <div class="row align-items-center mb-5">
          <div class="col-sm-7">
              <div class="section-title-wrap mb-0">
                <span class="subtitle">{{__('messages.our_blogs')}}</span>
                <h3 class="section-title">{{__('messages.daily_tips_to_remember')}}</h3>
              </div>
          </div>
          <div class="col-sm-5 mt-sm-0 mt-3">
            <div class="d-flex justify-content-sm-end">
              <a href="{{ route('blogs') }}" class="btn btn-secondary">{{__('messages.explore')}}</a>
            </div>
          </div>          
      </div>
      <div class="row">
          <div class="col-12">
            <div class="slick-general" data-items="3" data-items-desktop="3" data-items-laptop="3" data-items-tab="2" data-items-mobile-sm="2" data-items-mobile="1" data-speed="1000" data-autoplay="true" data-center="false" data-infinite="true" data-navigation="false" data-pagination="false" data-spacing="12">
              @foreach($blogs as $blog)

              <div class="slick-item">
                <x-frontend::card.card_blog  :blog="$blog" />
              </div>
              @endforeach

            </div>
          </div>
      </div>
  </div>
</section>

@endif




