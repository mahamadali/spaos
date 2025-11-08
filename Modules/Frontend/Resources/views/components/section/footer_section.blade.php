    <!-- Footer Section -->
    <footer>
        <div class="footer-box" style="background-image: url('{{ asset('/img/frontend/pattern.png') }}');  background-repeat: no-repeat; background-size: cover;">
            <div class="container">
                <div class="row gy-4">
                    <!-- Logo and Description -->
                    <div class="col-xl-3 col-md-4">
                        <a href="{{ route('index') }}" class="navbar-brand d-flex align-items-center">
                            <span class="logo-normal">
                                <img src="{{ setting('logo') ? asset(setting('logo')) : asset('/img/frontend/frezka-logo.png') }}" alt="logo" class="img-fluid" loading="lazy">
                            </span>
                        </a>
                        <p class="font-size-16 text-body mt-4 mb-0">{{__('messages.An_easy_and_manageable_software')}}</p>
                    </div>
                    @php
                        use App\Models\WebsiteFeature;
                        use App\Models\WebsiteSetting;
                        use Modules\Page\Models\Page;
                        $features = WebsiteFeature::all();
                        $links = WebsiteSetting::first();
                        $pages = Page::where('status',1)->get();
                    @endphp
                    <!-- Quick Links -->
                    <div class="col-xl-4 col-md-4">
                        <h6 class="font-size-18 fw-medium mb-4">{{__('messages.Quick_Links')}}</h6>
                        <div class="row gy-4">
                            <div class="col-lg-6">
                                <ul class="list-unstyled footer-menu mb-0">
                                    @if(isset($links) && $links->status == 1)
                                        <li><a href="{{ route('about_us') }}" class="nav-link">{{ __('frontend.about_us') }}</a>
                                        </li>
                                    @endif

                                    @if (isset($pages))
                                        @foreach($pages as $page)
                                            <li><a href="{{ route('page_slugs', $page->slug) }}" class="nav-link">{{ $page->name ?? '' }}</a></li>
                                        @endforeach
                                    @endif
                                </ul>
                            </div>
                            <div class="col-lg-6">
                                <ul class="list-unstyled footer-menu mb-0">
                                    <li><a href="{{ route('blogs') }}" class="nav-link">{{__('messages.blogs')}}</a>
                                    </li>
                                    <li><a href="{{ route('pricing') }}" class="nav-link">{{__('messages.pricing')}}</a></li>
                                    <li><a href="{{ route('faqs') }}" class="nav-link">{{__('messages.FAQs')}}</a></li>
                                </ul>
                            </div>
                        </div>
                    </div>

                    <!-- Features -->
                @if ($features !== null  && $features->isNotEmpty())
                    <div class="col-xl-3 col-md-4">
                        <h6 class="font-size-18 fw-medium mb-4">{{__('messages.features')}}</h6>
                        <ul class="list-unstyled footer-menu mb-0">
                            @if (isset($features) && $features->isNotEmpty())
                            @foreach($features->take(3) as $feature)  <!-- Show only the first 3 features -->
                                <li>
                                    <a href="{{ route('feature') }}" class="nav-link">{{ $feature->title ?? '' }}</a>
                                </li>
                            @endforeach

                            @if($features->count() > 3)  <!-- Show "See all Features" only if more than 3 items exist -->
                                <li>
                                    <a href="{{ route('feature') }}" class="nav-link">{{__('messages.see_all_Features')}}</a>
                                </li>
                            @endif
                        @endif

                        </ul>
                    </div>
                @endif
                    <!-- Stay Connected -->
                    @if (isset($links) )
                        @if($links->facebook_link !== null || $links->instagram_link !== null || $links->twitter_link !== null || $links->youtube_link !== null)
                            <div class="col-xl-2 col-md-12 nav-social-menu-link">
                                <h6 class="font-size-18 fw-medium mb-4">{{__('messages.stay_connected')}}</h6>
                                <ul class="list-unstyled footer-menu mb-0">
                                    @if($links->facebook_link)
                                        <li>
                                            <a href="{{ $links->facebook_link }}" target="_blank" class="d-flex align-items-center social-link gap-2">
                                                <i class="ph ph-facebook-logo"></i> {{ __('frontend.facebook') }}
                                            </a>
                                        </li>
                                    @endif
                                    @if($links->instagram_link)
                                        <li>
                                            <a href="{{ $links->instagram_link }}" target="_blank" class="d-flex align-items-center social-link gap-2">
                                                <i class="ph ph-instagram-logo"></i>{{ __('frontend.instagram') }}
                                            </a>
                                        </li>
                                    @endif
                                    @if($links->twitter_link)
                                        <li>
                                            <a href="{{ $links->twitter_link }}" target="_blank" class="d-flex align-items-center social-link gap-2">
                                                <i class="ph ph-twitter-logo"></i>{{ __('frontend.twitter') }}
                                            </a>
                                        </li>
                                    @endif
                                    @if($links->youtube_link)
                                        <li>
                                            <a href="{{ $links->youtube_link }}" target="_blank" class="d-flex align-items-center social-link gap-2">
                                                <i class="ph ph-youtube-logo"></i>{{ __('frontend.youtube') }}
                                            </a>
                                        </li>
                                    @endif
                                </ul>
                            </div>
                        @endif

                    @endif

                </div>
            </div>
        </div>
        <!-- Bottom Footer -->
        <div class="footer-bottom bg-secondary text-white text-center py-3">
            <p class="mb-0 font-size-14 fe-medium">&copy;


               {{ now()->year }}  {{ __('frontend.all_rights_reserved') }}


            </p>
        </div>
    </footer>
