      {{-- Banner Start --}}
      <section 
    class="banner-main"
    style="background-image: url({{ 
        isset($homepages) && ($bannerImg1 = $homepages->firstWhere('key', 'banner_image1')) && !empty($bannerImg1->value)
            ? asset($bannerImg1->value)
            : asset('/img/frontend/banner-bg.jpg') 
    }}); background-repeat: no-repeat; background-size: cover;">

          <div class="container-fluid">
            <div class="row flex-lg-row flex-row-reverse align-items-center">
                <div class="col-xl-5 col-lg-6">
                  
                    <h2 class="text-secondary banner-title">
                        {!! isset($homepages) && ($bannerTitle = $homepages->firstWhere('key', 'banner_title')) && !empty($bannerTitle->value)
                            ? htmlspecialchars_decode($bannerTitle->value)
                            : ' ' !!}                           
                    </h2>
                    <p class="mt-4 mb-0">
                        {!! isset($homepages) && ($bannerSubTitle = $homepages->firstWhere('key', 'banner_subtitle')) && !empty($bannerSubTitle->value)
                            ? htmlspecialchars_decode($bannerSubTitle->value)
                            : ' ' !!}
                    
                    </p>
                    <div class="demo-action">
                        <h6 class="m-0">
                        {!! isset($homepages) && ($bannerBadge = $homepages->firstWhere('key', 'banner_badge_text')) && !empty($bannerBadge->value)
                        ? htmlspecialchars_decode($bannerBadge->value)
                        : ' ' !!}
                            <a  class="text-decoration-underline text-primary" 
                            href="{{ route('pricing') }}">
                            {{__('messages.explore')}}</a>
                            </h6>
                    </div>
                </div>
                <div class="col-xl-7 col-lg-6 mt-lg-0 mt-5">
                    <div class="d-flex justify-content-lg-end justify-content-center">
                        <img src="{{ isset($homepages) && ($bannerImg2 = $homepages->firstWhere('key', 'banner_image2')) && !empty($bannerImg2->value)
                                ? asset($bannerImg2->value)
                                : asset('/img/frontend/hero_image.png') }}" alt="dashbord-img" class="dashbord-img w-100"/>
                    </div>
                </div>
            </div>
          </div>
      </section>
      {{-- Banner End --}}