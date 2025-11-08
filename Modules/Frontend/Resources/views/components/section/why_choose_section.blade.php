      {{-- Why Choose frezka Start --}}
  @php
      use App\Models\WebsiteFeature;
      $chooseUsFeatureList = isset($homepages) ? $homepages->firstWhere('key', 'choose_us_feature_list') : null;
  @endphp
  @if($chooseUsFeatureList && is_array($chooseUsFeatureList->value) !== null)
      <section class="section-spacing-top">
        <div class="container-fluid">
            <div class="choose-frezka" style="background-image: url('{{ asset('/img/frontend/why-pattern-bg.png') }}');">
                <div class="container">
                    <div class="row">
                        <div class="col-xl-5 col-lg-6 col-md-12 order-2 order-lg-1">
                            <div class="wrap-content-feature">
                                <div class="section-title-wrap">
                                    <span class="subtitle">
                                        {{  isset($homepages) && ($Title = $homepages->firstWhere('key', 'chooseUs_title')) && !empty($Title->value)
                                            ? $Title->value
                                            : '' }}
                                    </span>
                                    <h3 class="section-title">  
                                        {{  isset($homepages) && ($SubTitle = $homepages->firstWhere('key', 'chooseUs_subtitle')) && !empty($SubTitle->value)
                                        ? $SubTitle->value : '' }}
                                        </h3>
                                    <p class="title-description">  {{  isset($homepages) && ($Description = $homepages->firstWhere('key', 'chooseUs_description')) && !empty($Description->value)
                                        ? $Description->value : '' }}</p>
                                </div>

                               
                                @if($chooseUsFeatureList && is_array($chooseUsFeatureList->value))
                                    <ul class="list-inline p-0 m-0 d-flex flex-column gap-4">
                                        @foreach(WebsiteFeature::whereIn('id', collect($chooseUsFeatureList->value)->pluck('id'))->get() as $feature)
                                            <li>
                                                <x-frontend::card.card_feature :feature="$feature" />
                                            </li>
                                        @endforeach
                                    </ul>
                                @endif
                             </div>
                        </div>

                        <div class="col-xl-1 d-xl-block d-none order-xl-2"></div>
                        <div class="col-xl-6 col-lg-6 col-md-12 text-start order-1 order-xl-3 order-lg-2 mb-5 mb-lg-0">
                            <img src="{{  isset($homepages) && ($ChooseUsImage = $homepages->firstWhere('key', 'chooseUs_image')) && !empty($ChooseUsImage->value)
                                                ? asset($ChooseUsImage->value)
                                                : asset('/img/frontend/expert.png') }}" class="rounded-4 img-fluid" alt="choose-us">
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </section>
    @endif
    {{-- Why Choose frezka End --}}
