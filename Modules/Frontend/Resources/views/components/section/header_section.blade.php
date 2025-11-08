<header>
    <nav class="nav navbar navbar-expand-xl navbar-light iq-navbar header-hover-menu py-xl-0">
        <div class="container-fluid navbar-inner">
            <div class="d-flex align-items-center justify-content-between gap-3 w-100 ">
                <div class="d-flex align-items-center gap-2">
                    <button data-trigger="navbar_main"
                        class="d-xl-none btn btn-primary rounded-pill p-1 pt-0 toggle-rounded-btn" type="button">
                        <svg width="20px" class="icon-20" viewBox="0 0 24 24">
                            <path fill="currentColor"
                                d="M4,11V13H16L10.5,18.5L11.92,19.92L19.84,12L11.92,4.08L10.5,5.5L16,11H4Z"></path>
                        </svg>
                    </button>

                    <a href="{{ route('index') }}" class="navbar-brand m-0">
                        <span class="logo-normal">
                            <img src="{{ setting('logo') ? asset(setting('logo')) : asset('/img/frontend/frezka-logo.png') }}"
                                alt="logo" class="img-fluid logo" loading="lazy">
                        </span>
                    </a>
                </div>

                <nav id="navbar_main"
                    class="mobile-offcanvas nav navbar navbar-expand-xl hover-nav horizontal-nav mega-menu-content py-xl-0">
                    <div class="offcanvas-header pt-3">
                        <a href="./index.html" class="navbar-brand m-0">
                            <span class="logo-normal">
                                <img src="{{ setting('logo') ? asset(setting('logo')) : asset('/img/frontend/frezka-logo.png') }}"
                                    alt="logo" class="img-fluid" loading="lazy">
                            </span>
                        </a>
                        <button class="btn-close float-end px-3"></button>
                    </div>
                    <ul class="navbar-nav iq-nav-menu list-unstyled" id="header-menu">
                        <li class="nav-item">
                            <a href="{{ route('index') }}"
                                class="nav-link {{ request()->routeIs('index') ? 'active' : '' }}">
                                <span class="item-name">{{ __('messages.why') }} {{ setting('app_name') }} </span>
                            </a>
                        </li>
                        @php
                            use App\Models\WebsiteFeature;
                            use App\Models\WebsiteSetting;

                            $features = WebsiteFeature::all();
                            $links = WebsiteSetting::first();
                        @endphp
                        @if ($features !== null && $features->isNotEmpty())
                            <li class="nav-item">
                                <a href="#features" class="nav-link {{ request()->routeIs('feature') ? 'active' : '' }}"
                                    data-bs-toggle="collapse">
                                    <span class="item-name">{{ __('messages.features') }}</span>
                                    <span class="menu-icon">
                                        <i class="ph ph-caret-down"></i>
                                    </span>
                                </a>
                                <ul id="features" class="sub-nav mega-menu-item collapse list-unstyled">
                                    <li class="nav-item text-center">
                                        <div class="row">
                                            <div class="col-md-12">
                                                <h6 class="notice-badge">
                                                    {{ __('messages.new_features_are_launching_soon_stay_tune!') }}
                                                </h6>
                                            </div>
                                        </div>
                                    </li>
                                    <li class="nav-item">
                                        <div class="row">
                                            @php
                                                $totalFeatures = $features->count();
                                                $displayFeatures = $features->take(9);
                                            @endphp

                                            @foreach ($displayFeatures->chunk(3) as $featureChunk)
                                                <div class="col-xl-4">
                                                    <ul class="shadow-none line list-unstyled">
                                                        @foreach ($featureChunk as $feature)
                                                            <li class="nav-item">
                                                                <a class="nav-link" href="{{ route('feature') }}">
                                                                    <span
                                                                        class="item-name">{{ $feature->title ?? '' }}</span>
                                                                </a>
                                                            </li>
                                                        @endforeach
                                                    </ul>
                                                </div>
                                            @endforeach

                                            @if ($totalFeatures > 9)
                                                <div class="col-12 text-center mt-3">
                                                    <a href="{{ route('feature') }}"
                                                        class="btn btn-link text-primary">{{ __('messages.See_all_features') }}</a>
                                                </div>
                                            @endif
                                        </div>
                                    </li>
                                </ul>
                            </li>
                        @endif
                        @auth
                            @if (!auth()->user()->hasRole('super admin'))
                                <li class="nav-item">
                                    <a href="{{ route('pricing') }}"
                                        class="nav-link {{ request()->routeIs('pricing') ? 'active' : '' }}">
                                        <span class="item-name">{{ __('messages.pricing') }}</span>
                                    </a>
                                </li>
                            @endif
                        @else
                            <li class="nav-item">
                                <a href="{{ route('pricing') }}"
                                    class="nav-link {{ request()->routeIs('pricing') ? 'active' : '' }}">
                                    <span class="item-name">{{ __('messages.pricing') }}</span>
                                </a>
                            </li>
                        @endauth
                        <li class="nav-item">
                            <a href="#resources"
                                class="nav-link {{ in_array(request()->route()->getName(), ['about_us', 'blogs']) ? 'active' : '' }}"
                                data-bs-toggle="collapse">
                                <span class="item-name">{{ __('messages.Resources') }}</span>
                                <span class="menu-icon">
                                    <i class="ph ph-caret-down"></i>
                                </span>
                            </a>
                            <ul id="resources" class="sub-nav collapse list-unstyled">
                                @if ($links->status == 1)
                                    <li class="nav-item">
                                        <a href="{{ route('about_us') }}" class="nav-link" href="#">
                                            <span class="item-name">{{ __('messages.about_us') }}</span>
                                        </a>
                                    </li>
                                @endif

                                <li class="nav-item">
                                    <a href="{{ route('blogs') }}" class="nav-link " href="#">
                                        {{ __('messages.blog') }}
                                    </a>
                                </li>
                            </ul>
                        </li>
                    </ul>
                </nav>

                <div class="right-panel">
                    <button class="navbar-toggler" type="button" data-bs-toggle="collapse"
                        data-bs-target="#headerActionPanel" aria-controls="headerActionPanel" aria-expanded="false"
                        aria-label="Toggle navigation">
                        <span class="navbar-toggler-btn">
                            <span class="navbar-toggler-icon"></span>
                        </span>
                    </button>
                    <div class="navbar-collapse collapse" id="headerActionPanel">
                        <div class="d-flex align-items-center justify-content-end px-xl-0 py-xl-0 py-2 px-3 gap-3">
                            @if (auth()->check() && auth()->user()->hasRole('admin'))
                                <div>
                                    <div class="dropdown dropdown-user-wrapper">
                                        <a class="nav-link dropdown-user" href="#" id="navbarDropdown"
                                            role="button" data-bs-toggle="dropdown" aria-expanded="false">
                                            <img src="{{ asset(user_avatar()) }}"
                                                class="img-fluid user-image rounded-circle"
                                                alt="{{ auth()->user()->name ?? default_user_name() }}">
                                        </a>
                                        <div class="dropdown-menu dropdown-menu-end dropdown-user-menu border"
                                            aria-labelledby="navbarDropdown">
                                            <div
                                                class="bg-light p-3 d-flex justify-content-between align-items-center gap-3 rounded mb-4">
                                                <div class="d-inline-flex align-items-center gap-3">
                                                    <div class="image flex-shrink-0">
                                                        <img src="{{ asset(user_avatar()) }}"
                                                            class="img-fluid dropdown-user-menu-image"
                                                            alt="{{ auth()->user()->name ?? default_user_name() }}">
                                                    </div>
                                                    <div class="content">
                                                        <h6 class="mb-1">
                                                            {{ Auth::user()->full_name ?? default_user_name() }}</h6>
                                                        <span
                                                            class="font-size-14 dropdown-user-menu-contnet">{{ Auth::user()->email ?? 'abc@gmail.com' }}</span>
                                                    </div>
                                                </div>
                                                <div class="link">
                                                    <a href="{{ route('backend.my-profile') }}"
                                                        class="link-body-emphasis">
                                                        <i class="ph ph-caret-right"></i>
                                                    </a>
                                                </div>
                                            </div>
                                            <ul class="d-flex flex-column gap-3 list-inline m-0 p-0">
                                                <li>
                                                    <a href="{{ route('app.home') }}"
                                                        class="link-body-emphasis font-size-14">
                                                        <span
                                                            class="d-flex align-items-center justify-content-between gap-3">
                                                            <span
                                                                class="fw-medium">{{ __('messages.dashboard') }}</span>
                                                            <i class="ph ph-caret-right"></i>
                                                        </span>
                                                    </a>
                                                </li>
                                                <li>
                                                    <a href="{{ route('edit-profile') }}"
                                                        class="link-body-emphasis font-size-14">
                                                        <span
                                                            class="d-flex align-items-center justify-content-between gap-3">
                                                            <span
                                                                class="fw-medium">{{ __('messages.profile') }}</span>
                                                            <i class="ph ph-caret-right"></i>
                                                        </span>
                                                    </a>
                                                </li>

                                                <li>
                                                    <a class="link-primary font-size-14" href="{{ route('logout') }}"
                                                        id="logout-link">
                                                        <span
                                                            class="d-flex align-items-center justify-content-between gap-3">
                                                            <span class="fw-medium">{{ __('messages.logout') }}</span>
                                                        </span>
                                                    </a>
                                                </li>
                                                <form id="logout-form" action="{{ route('logout') }}" method="POST"
                                                    style="display: none;"> @csrf </form>

                                            </ul>
                                        </div>
                                    </div>
                                </div>
                            @else
                                <div class="header-btn-wrap d-flex align-items-center gap-3">
                                    <a href="{{ route('user.register') }}"
                                        class="btn btn-secondary">{{ __('messages.register') }}</a>
                                    <a href="{{ route('user.login') }}"
                                        class="btn btn-primary">{{ __('messages.login') }}</a>
                                </div>
                            @endif
                        </div>
                    </div>
                </div>

            </div>
        </div>
    </nav>
    @push('after-scripts')
        <script>
            $(document).on("click", "#logout-link", function(e) {
                e.preventDefault(); // Prevent default anchor behavior
                $("#logout-form").trigger("submit"); // Trigger form submission
            });
        </script>
    @endpush

</header>
