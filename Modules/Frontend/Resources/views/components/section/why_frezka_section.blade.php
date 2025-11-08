 {{-- why frezka Start --}}
 @php
     $videoType = isset($homepages) ? $homepages->firstWhere('key', 'video_type') : null;
     $videoUrl = isset($homepages) && $homepages->firstWhere('key', 'video_url') ? $homepages->firstWhere('key', 'video_url')->value : null;
     $videoImg = isset($homepages) ? $homepages->firstWhere('key', 'video_img') : null;
@endphp

@if($videoUrl !== null)

 <section class="section-spacing bg-secondary">
  <div class="container">
      <div class="row">
        <div class="col-lg-3 col-md-2"></div>
        <div class="col-lg-6 col-md-8">
          <div class="section-title-wrap center">
            <span class="subtitle">
              {{  isset($homepages) && ($Title = $homepages->firstWhere('key', 'about_title')) && !empty($Title->value)
              ? $Title->value
              : '' }}
              </span>
            <h3 class="section-title text-white">
              {{  isset($homepages) && ($SubTitle = $homepages->firstWhere('key', 'about_subtitle')) && !empty($SubTitle->value)
                ? $SubTitle->value : '' }}
                </h3>
            <p class="title-description text-white">{{  isset($homepages) && ($Description = $homepages->firstWhere('key', 'about_description')) && !empty($Description->value)
                  ? $Description->value : '' }}
            </p>
          </div>
        </div>
        <div class="col-lg-3 col-md-2"></div>
      </div>
          <div class="row">
                <div class="col-12">
                 

                  @if ($videoUrl && $videoType)
                      <div class="video-wrapper">
                          @if ($videoType->value == 'youtube')
                              {{-- Extract video ID for YouTube --}}
                              @php
                                  $youtubeUrl = $videoUrl;
                                  $youtubeId = null;
                                  if (str_contains($youtubeUrl, 'youtu.be')) {
                                      $youtubeId = last(explode('/', parse_url($youtubeUrl, PHP_URL_PATH)));
                                  } elseif (str_contains($youtubeUrl, 'youtube.com')) {
                                      parse_str(parse_url($youtubeUrl, PHP_URL_QUERY), $queryParams);
                                      $youtubeId = $queryParams['v'] ?? null;
                                  }
                              @endphp

                              @if ($youtubeId)
                                  <iframe width="100%" height="auto" src="https://www.youtube.com/embed/{{ $youtubeId }}" 
                                      title="YouTube video player" frameborder="0" allow="accelerometer; autoplay; clipboard-write; encrypted-media; gyroscope; picture-in-picture" allowfullscreen>
                                  </iframe>
                              @else
                                  <p>{{__('messages.invalid_YouTube_URL.')}}</p>
                              @endif
                                
                          @elseif ($videoType->value == 'mp4')
                              {{-- Display MP4 Video --}}
                              <video  controls poster="{{ asset($videoImg ? $videoImg->value : 'default_image.jpg') }}" width="100%" height="auto">
                                  <source src="{{ asset($videoUrl) }}" type="video/mp4">
                                  {{__('messages.your_browser_does_not_support_the_video_tag.')}}
                              </video>
                          @else
                              <p>{{__('messages.unsupported_video_type.')}}</p>
                          @endif
                      </div>
                  @else
                      <p>{{__('messages.your_video_type_or_URL_is_missing.')}}</p>
                  @endif
              </div>
          </div>
      </div>
  </div>
</section>
    
@endif
{{-- why frezka End --}}