@php
    $headerMenuSetting = \Modules\FrontendSetting\Models\FrontendSetting::where('type', 'header-menu-setting')
        ->where('key', 'header-menu-setting')
        ->where('created_by', session('current_vendor_id'))
        ->first();
    $headerMenuSettingDecoded = null;
    $isHeaderEnabled = true;

    if ($headerMenuSetting) {
        $isHeaderEnabled = $headerMenuSetting->status == 1;

        if (isset($headerMenuSetting->value)) {
            $decoded = $headerMenuSetting->value;

            while (is_string($decoded)) {
                $decoded = json_decode($decoded, true);
            }

            if (is_array($decoded)) {
                $headerMenuSettingDecoded = $decoded;
            }
        }
    }
@endphp

@if ($isHeaderEnabled)
    <!-- Header is ENABLED -->
    <header>
        @if (
            $headerMenuSettingDecoded &&
                isset($headerMenuSettingDecoded['header_offer_section']) &&
                $headerMenuSettingDecoded['header_offer_section']
        )
            <div class="top-header bg-primary text-white">
                <marquee behavior="scroll" direction="left" scrollamount="6" class="font-size-14 fw-bold">
                    {{ $headerMenuSettingDecoded['header_offer_title'] ?? '' }}
                </marquee>
            </div>
        @endif

        </div>
        <nav class="nav navbar navbar-expand-xl navbar-light iq-navbar header-hover-menu py-xl-0">
            <div class="container-fluid navbar-inner">
                <div class="d-flex align-items-center justify-content-between w-100 landing-header">
                    <div class="d-flex gap-2 gap-sm-3 align-items-center">
                        <button data-bs-toggle="offcanvas" data-bs-target="#navbar_main" aria-controls="navbar_main"
                            class="d-xl-none btn btn-primary rounded-pill toggle-rounded-btn" type="button">
                            <i class="ph ph-arrow-right"></i>
                        </button>
                        <!--Logo -->
                        <x-logo />
                        <!-- menu -->
                        <x-horizontal_nav :headerMenuSettingDecoded="$headerMenuSettingDecoded" />
                        <!-- menu end -->
                    </div>

                    <div class="right-panel">
                        <ul class="navbar-nav align-items-center d-xl-none">

                            <!-- color mode -->

                            @if (isset($headerMenuSettingDecoded) && $headerMenuSettingDecoded['enable_darknight_mode'] == 1)
                                <li class="nav-item theme-scheme-switch">
                                    <a href="javascript:void(0)" class="nav-link d-flex align-items-center change-mode">
                                        <span class="light-mode">
                                            <i class="ph ph-sun"></i>
                                        </span>
                                        <span class="dark-mode">
                                            <i class="ph ph-moon"></i>
                                        </span>
                                    </a>
                                </li>
                            @endif
                            @if (auth()->check() && auth()->user()->hasRole('user'))
                                <li class="nav-item flex-shrink-0 dropdown dropdown-user-wrapper">
                                    <a class="nav-link dropdown-user" href="#" id="navbarDropdown" role="button"
                                        data-bs-toggle="dropdown" aria-expanded="false">
                                        <img src="{{ asset('img\vendorwebsite\user.png') }}"
                                            class="img-fluid user-image rounded-circle" alt="user image">
                                    </a>
                                    <div class="dropdown-menu dropdown-menu-end dropdown-user-menu border-0"
                                        aria-labelledby="navbarDropdown">
                                        <div class="d-flex align-items-center gap-3 border-bottom mb-3 p-3">
                                            <div class="image flex-shrink-0">
                                                <img src="{{ asset('img\vendorwebsite\user.png') }}"
                                                    class="img-fluid dropdown-user-menu-image" alt="">
                                            </div>
                                            <div class="content">
                                                <h6 class="mb-1">{{ auth()->user()->first_name }}
                                                    {{ auth()->user()->last_name }}</h6>
                                                <div class="text-body small">{{ auth()->user()->email }}</div>
                                                <div class="text-body small">
                                                    {{ auth()->user()->contact_no ?? (auth()->user()->mobile ?? '') }}
                                                </div>
                                            </div>
                                        </div>
                                        <ul class="d-flex flex-column gap-3 list-inline m-0 px-3">
                                            <!-- <li>
                                                <a href="{{ route('wallet') }}" class="font-size-14">
                                                    <span
                                                        class="d-flex align-items-center justify-content-between gap-3">
                                                        <span
                                                            class="fw-medium">{{ __('vendorwebsite.wallet_balance') }}</span>
                                                        <h6 class="text-primary m-0">
                                                            {{ \Currency::format(optional(auth()->user()->wallet)->amount) }}
                                                        </h6>
                                                    </span>
                                                </a>
                                            </li> -->


                                            {{-- <li>
                                                <a href="{{ route('bank-list') }}" class="font-size-14">
                                                    <span
                                                        class="d-flex align-items-center justify-content-between gap-3">
                                                        <span
                                                            class="fw-medium">{{ __('vendorwebsite.bank_list') }}</span>
                                                    </span>
                                                </a>
                                            </li> --}}

                                            <li>
                                                <a href="{{ route('changepassword') }}`" class="font-size-14">
                                                    <span
                                                        class="d-flex align-items-center justify-content-between gap-3">
                                                        <span
                                                            class="fw-medium">{{ __('vendorwebsite.change_password') }}</span>
                                                    </span>
                                                </a>
                                            </li>
                                            <li>
                                                <a href="{{ route('bookings') }}" class="font-size-14">
                                                    <span
                                                        class="d-flex align-items-center justify-content-between gap-3">
                                                        <span
                                                            class="fw-medium">{{ __('vendorwebsite.booking') }}</span>
                                                    </span>
                                                </a>
                                            </li>
                                            <li>
                                                <a href="{{ route('profile') }}" class="font-size-14">
                                                    <span
                                                        class="d-flex align-items-center justify-content-between gap-3">
                                                        <span
                                                            class="fw-medium">{{ __('vendorwebsite.settings') }}</span>
                                                    </span>
                                                </a>
                                            </li>
                                            <li>
                                                <a href="{{ route('wishlist') }}" class="font-size-14">
                                                    <span
                                                        class="d-flex align-items-center justify-content-between gap-3">
                                                        <span
                                                            class="fw-medium">{{ __('vendorwebsite.wishlist') }}</span>
                                                    </span>
                                                </a>
                                            </li>
                                            <li>
                                                <a href="{{ route('myorder') }}" class="font-size-14">
                                                    <span
                                                        class="d-flex align-items-center justify-content-between gap-3">
                                                        <span class="fw-medium">{{ __('vendorwebsite.orders') }}</span>
                                                    </span>
                                                </a>
                                            </li>
                                            <li>
                                                <a href="{{ route('address') }}" class="font-size-14">
                                                    <span
                                                        class="d-flex align-items-center justify-content-between gap-3">
                                                        <span
                                                            class="fw-medium">{{ __('vendorwebsite.manage_address') }}</span>
                                                    </span>
                                                </a>
                                            </li>
                                        </ul>
                                        <div class="btn-logout">
                                            <a href="#"
                                                onclick="event.preventDefault();
                                                Swal.fire({
                                                   title: '{{ __('Are you sure?') }}',
                                                   text: '{{ __('You will be logged out') }}',
                                                   icon: 'warning',
                                                   showCancelButton: true,
                                                   reverseButtons: true,
                                                   confirmButtonText: '{{ __('Yes, logout') }}',
                                                   cancelButtonText: '{{ __('Cancel') }}',

                                                    customClass: {
                                                        confirmButton: 'btn btn-primary',
                                                        cancelButton: 'btn btn-secondary'
                                                    },
                                                }).then((result) => {
                                                   if (result.isConfirmed) {
                                                      document.getElementById('logout-form-2').submit();
                                                   }
                                                });"
                                                class="btn btn-secondary w-100 text-center">
                                                <i class="ph ph-sign-out px-1"></i> {{ __('vendorwebsite.logout') }}
                                            </a>
                                            <form id="logout-form-2" action="{{ route('website.logout') }}"
                                                method="POST" style="display: none;">
                                                @csrf
                                            </form>
                                        </div>
                                    </div>
                                </li>
                            @endif
                        </ul>
                        <button class="navbar-toggler" type="button" data-bs-toggle="collapse"
                            data-bs-target="#navbarSupportedContent" aria-controls="navbarSupportedContent"
                            aria-expanded="false" aria-label="Toggle navigation">
                            <i class="ph ph-list toggle-list fs-4"></i>
                            <i class="ph ph-x toggle-x fs-4"></i>
                        </button>
                        <div class="collapse navbar-collapse" id="navbarSupportedContent">
                            <ul class="navbar-nav align-items-center ms-auto mb-2 mb-xl-0">
                                <!-- search -->
                                @if (isset($headerMenuSettingDecoded['enable_search']) && $headerMenuSettingDecoded['enable_search'] == 1)
                                    <li class="nav-item dropdown iq-responsive-menu">
                                        <a href="#" class="nav-link" id="search-drop" data-bs-toggle="dropdown"
                                            aria-expanded="false">
                                            <span class="">
                                                <span class="btn-inner">
                                                    <i class="ph ph-magnifying-glass"></i>
                                                </span>
                                            </span>
                                        </a>
                                        <div class="dropdown-menu dropdown-menu-end p-0 dropdown-search"
                                            style="width: 20rem;" data-bs-popper="static">
                                            <div class="form-group input-group mb-0">
                                                <input type="text" id="global-search-input"
                                                    class="form-control border-0" placeholder="Search...">
                                                <span class="input-group-text border-0 cursor-pointer"
                                                    id="global-search-btn">
                                                    <i class="ph ph-magnifying-glass"></i>
                                                </span>
                                            </div>
                                        </div>
                                    </li>
                                @endif
                                <!-- color mode -->
                                @if (isset($headerMenuSettingDecoded['enable_darknight_mode']) &&
                                        $headerMenuSettingDecoded['enable_darknight_mode'] == 1)
                                    <li class="nav-item theme-scheme-switch d-none d-xl-block">
                                        <a href="javascript:void(0)"
                                            class="nav-link d-flex align-items-center change-mode">
                                            <span class="light-mode">
                                                <i class="ph ph-sun"></i>
                                            </span>
                                            <span class="dark-mode">
                                                <i class="ph ph-moon"></i>
                                            </span>
                                        </a>
                                    </li>
                                @endif
                                <!-- Language -->
                                @if (isset($headerMenuSettingDecoded['enable_language']) && $headerMenuSettingDecoded['enable_language'] == 1)
                                    <li class="nav-item dropdown dropdown-language-wrapper">
                                        <a class="gap-1 px-3 dropdown-toggle d-flex align-items-center"
                                            data-bs-toggle="dropdown" href="#" role="button"
                                            aria-haspopup="true" aria-expanded="false">

                                            <img src="{{ asset('images/flags/' . App::getLocale() . '.png') }}"
                                                alt="flag" class="img-fluid me-2 avatar-20"
                                                onerror="this.onerror=null; this.src='https://apps.iqonic.design/streamit-laravel/flags/globe.png';">

                                            <i class="fa-solid fa-globe me-2"></i>
                                            {{ strtoupper(App::getLocale()) }}
                                        </a>
                                        <div class="dropdown-menu dropdown-menu-end dropdown-menu-language mt-0">
                                            @foreach (config('app.available_locales') as $locale => $title)
                                                <a class="dropdown-item {{ app()->getLocale() === $locale ? 'text-primary fw-semibold' : '' }}"
                                                    href="{{ route('frontend.language.switch', $locale) }}">
                                                    <span class="d-flex align-items-center gap-3">
                                                        <img src="{{ asset('images/flags/' . $locale . '.png') }}"
                                                            alt="{{ $title }} flag"
                                                            class="img-fluid mr-2 avatar-20">
                                                        <span>{{ $title }}</span>
                                                        @if (app()->getLocale() === $locale)
                                                            <span class="active-icon">
                                                                <i class="ph-fill ph-check-fat align-middle"></i>
                                                            </span>
                                                        @endif
                                                    </span>
                                                </a>
                                            @endforeach
                                        </div>
                                    </li>
                                @endif
                                @if (auth()->check() && auth()->user()->hasRole('user'))
                                    <!-- notification -->
                                    <li class="nav-item dropdown dropdown-notification-wrapper">
                                        <a class="nav-link btn-icon" data-bs-toggle="dropdown" href="#"
                                            aria-expanded="false">
                                            <span class="btn-inner">
                                                <i class="ph ph-bell-ringing"></i>
                                            </span>
                                            <span class="notification-alert">
                                                @auth
                                                    {{ auth()->user()->unreadNotifications->count() }}
                                                @else
                                                    0
                                                @endauth
                                            </span>
                                        </a>
                                        <ul class="p-0 sub-drop dropdown-menu dropdown-notification dropdown-menu-end">
                                            <div class="m-0 shadow-none card bg-transparent notification_data">
                                                <div class="card-header border-bottom p-3">
                                                    <h5 class="mb-0">
                                                        All Notifications
                                                        (@auth{{ auth()->user()->notifications()->take(10)->count() }}
                                                    @else
                                                    0 @endauth)
                                                </h5>
                                            </div>
                                            <div
                                                class="card-body overflow-auto card-header-border p-0 card-body-list max-17 scroll-thin">
                                                <div
                                                    class="dropdown-menu-1 overflow-y-auto list-style-1 mb-0 notification-height">
                                                    @auth


                                                        @php $notifications = auth()->user()->notifications()->latest()->take(10)->get(); @endphp


                                                        @forelse($notifications as $notification)
                                                            @php
                                                                $data = $notification->data['data'] ?? [];

                                                                $route = '#';
                                                                if ($data['notification_group'] == 'shop') {
                                                                    $route = route('order-detail', [
                                                                        'order_id' => $data['id'],
                                                                    ]);
                                                                }

                                                                if ($data['notification_group'] == 'booking') {
                                                                    $route = route('bookings.detail-page', [
                                                                        'id' => $data['id'],
                                                                    ]);
                                                                }

                                                            @endphp
                                                            <div
                                                                class="dropdown-item-1 float-none p-3 list-unstyled iq-sub-card notify-list-bg {{ $notification->read_at ? '' : 'bg-gray-700' }}">
                                                                <a href="{{ $route }}" class="">

                                                                    <div class="list-item d-flex gap-lg-3 gap-2">
                                                                        <div class="mt-1">
                                                                            <button type="button"
                                                                                class="btn btn-primary-subtle btn-icon rounded-pill">
                                                                                {{ strtoupper(substr($notification->data['title'] ?? 'N', 0, 1)) }}
                                                                            </button>
                                                                        </div>
                                                                        <div class="list-style-detail flex-grow-1">
                                                                            <p class="heading-color text-start mb-1">
                                                                                {{ $notification->data['data']['type'] ?? '' }}
                                                                            </p>
                                                                            <div class="d-flex justify-content-between">
                                                                                <small
                                                                                    class="text-body">{{ $notification->created_at->format('d/m/Y') }}</small>
                                                                                <small
                                                                                    class="text-body">{{ $notification->created_at->format('h:i A') }}</small>
                                                                            </div>
                                                                        </div>
                                                                    </div>
                                                                </a>
                                                            </div>
                                                        @empty
                                                            <div class="p-3 text-center">
                                                                {{ __('vendorwebsite.no_notifications_found') }}</div>
                                                        @endforelse
                                                    @else
                                                        <div class="p-3 text-center">
                                                            {{ __('vendorwebsite.please_log_in_to_see_notifications') }}
                                                        </div>
                                                    @endauth
                                                </div>
                                            </div>
                                            <div class="card-footer py-2 border-top">
                                                <div class="d-flex align-items-center gap-3 justify-content-end">
                                                    @auth
                                                        @if (auth()->user()->unreadNotifications->count() > 0)
                                                            <form action="{{ route('notifications.markAllRead') }}"
                                                                method="POST" class="d-inline">
                                                                @csrf
                                                                <button type="submit"
                                                                    class="btn btn-link border-0 mb-0 notifyList pull-right"><span>{{ __('vendorwebsite.mark_all_as_read') }}</span></button>
                                                            </form>
                                                        @endif
                                                        <a href="{{ route('user-notifications') }}"
                                                            class="btn btn-sm btn-primary">{{ __('vendorwebsite.view_all') }}</a>
                                                    @endauth
                                                </div>
                                            </div>
                                        </div>
                                    </ul>
                                </li>

                                <!-- shopping cart -->
                                <li class="nav-item dropdown-shopping-cart-wrapper">
                                    <a href="{{ route('cart') }}" class="nav-link btn-icon">
                                        <span class="btn-inner">
                                            <i class="ph ph-bag"></i>
                                        </span>
                                        <span class="notification-alert" id="cartCount">0</span>
                                    </a>
                                </li>
                                <!-- user droupdown -->
                                <li
                                    class="nav-item flex-shrink-0 dropdown dropdown-user-wrapper d-none d-xl-block">
                                    <a class="nav-link dropdown-user" href="#" id="navbarDropdown"
                                        role="button" data-bs-toggle="dropdown" aria-expanded="false">
                                        <img src="{{ asset(user_avatar()) }}"
                                            class="img-fluid user-image rounded-circle" alt="user image">
                                    </a>
                                    <div class="dropdown-menu dropdown-menu-end dropdown-user-menu border-0"
                                        aria-labelledby="navbarDropdown">
                                        <div
                                            class="d-flex align-items-center gap-3 border-bottom mb-3 p-3 user-info-profile-redirect">
                                            <div class="image flex-shrink-0">
                                                <img src="{{ asset(user_avatar()) }}"
                                                    class="img-fluid dropdown-user-menu-image" alt="">
                                            </div>
                                            <div class="content">
                                                <h6 class="mb-1">{{ auth()->user()->first_name }}
                                                    {{ auth()->user()->last_name }}</h6>
                                                <div class="text-body small">{{ auth()->user()->email }}</div>
                                                <div class="text-body small">
                                                    {{ auth()->user()->contact_no ?? (auth()->user()->mobile ?? '') }}
                                                </div>
                                            </div>
                                        </div>
                                        <ul class="d-flex flex-column gap-3 list-inline m-0 px-3">
                                            <!-- <li>
                                        <a href="{{ route('wallet') }}" class="font-size-14">
                                            <span
                                                class="d-flex align-items-center justify-content-between gap-3">
                                                <span
                                                    class="fw-medium">{{ __('vendorwebsite.wallet_balance') }}</span>
                                                <h6 class="text-primary m-0">
                                                    {{ \Currency::format(optional(auth()->user()->wallet)->amount) }}
                                                </h6>
                                            </span>
                                        </a>
                                    </li> -->

                                            {{-- <li>
                                                <a href="{{ route('bank-list') }}" class="font-size-14">
                                                    <span
                                                        class="d-flex align-items-center justify-content-between gap-3">
                                                        <span
                                                            class="fw-medium">{{ __('vendorwebsite.bank_list') }}</span>
                                                    </span>
                                                </a>
                                            </li> --}}

                                            <li>
                                                <a href="{{ route('changepassword') }}" class="font-size-14">
                                                    <span
                                                        class="d-flex align-items-center justify-content-between gap-3">
                                                        <span
                                                            class="fw-medium">{{ __('vendorwebsite.change_password') }}</span>
                                                    </span>
                                                </a>
                                            </li>
                                            {{-- <li>
                                 <a href="{{route('profilepackagep')}}" class="font-size-14">
                                    <span class="d-flex align-items-center justify-content-between gap-3">
                                       <span class="fw-medium">{{__('vendorwebsite.packages')}}</span>
                                    </span>
                                 </a>
                              </li> --}}
                                            {{-- <li>
                                 <a href="{{ route('profile') }}" class="font-size-14">
                                    <span class="d-flex align-items-center justify-content-between gap-3">
                                       <span class="fw-medium">{{__('vendorwebsite.settings')}}</span>
                                    </span>
                                 </a>
                              </li> --}}
                                            <li>
                                                <a href="{{ route('wishlist') }}" class="font-size-14">
                                                    <span
                                                        class="d-flex align-items-center justify-content-between gap-3">
                                                        <span
                                                            class="fw-medium">{{ __('vendorwebsite.wishlist') }}</span>
                                                    </span>
                                                </a>
                                            </li>
                                            {{-- <li>
                                 <a href="{{route('bookings')}}" class="font-size-14">
                                    <span class="d-flex align-items-center justify-content-between gap-3">
                                       <span class="fw-medium">{{__('vendorwebsite.bookings')}}</span>
                                    </span>
                                 </a>
                              </li> --}}
                                            <li>
                                                <a href="{{ route('myorder') }}" class="font-size-14">
                                                    <span
                                                        class="d-flex align-items-center justify-content-between gap-3">
                                                        <span
                                                            class="fw-medium">{{ __('vendorwebsite.orders') }}</span>
                                                    </span>
                                                </a>
                                            </li>
                                            <li>
                                                <a href="{{ route('address') }}" class="font-size-14">
                                                    <span
                                                        class="d-flex align-items-center justify-content-between gap-3">
                                                        <span
                                                            class="fw-medium">{{ __('vendorwebsite.manage_address') }}</span>
                                                    </span>
                                                </a>
                                            </li>

                                        </ul>
                                        <div class="btn-logout">
                                            <a href="#"
                                                onclick="event.preventDefault();
                              Swal.fire({
                                 title: '{{ __('Are you sure?') }}',
                                 text: '{{ __('You will be logged out') }}',
                                 icon: 'warning',
                                 showCancelButton: true,
                                 reverseButtons: true,
                                 confirmButtonText: '{{ __('Yes, logout') }}',
                                 cancelButtonText: '{{ __('Cancel') }}',
                                customClass: {
                                    confirmButton: 'btn btn-primary',
                                    cancelButton: 'btn btn-secondary'
                                },
                              }).then((result) => {
                                 if (result.isConfirmed) {
                                    document.getElementById('logout-form').submit();
                                 }
                              });"
                                                class="btn btn-secondary w-100 text-center">
                                                <i class="ph ph-sign-out px-1"></i>
                                                {{ __('vendorwebsite.logout') }}
                                            </a>
                                            <form id="logout-form" action="{{ route('website.logout') }}"
                                                method="POST" style="display: none;">
                                                @csrf
                                            </form>
                                        </div>
                                    </div>
                                </li>
                            @else
                                <li>
                                    <a href="{{ route('vendor.login', ['vendor_slug' => request()->route('vendor_slug')]) }}"
                                        class="btn btn-secondary w-100">{{ __('vendorwebsite.login') }}</a>

                                </li>
                            @endif
                        </ul>
                    </div>
                </div>
            </div>
        </div>
    </nav>
</header>
@else
<!-- Header is DISABLED -->
@endif

@push('scripts')
<script>
    function updateCart() {

        $.get('{{ route('cart.count') }}', function(response) {

            if (response.status) {
                $('#cartCount').text(response.cart_count);
                $('#cartItemCount').text(response.cart_count);

                // Update cart items
                if (response.cart_items && response.cart_items.length > 0) {
                    let cartHtml = '';
                    let total = 0;

                    response.cart_items.forEach(function(item) {
                        total += item.price * item.qty;
                        cartHtml += `
                        <div class="dropdown-item-1 float-none p-3 list-unstyled iq-sub-card">
                            <div class="d-flex align-items-center gap-3">
                                <img src="${item.image}" alt="${item.name}" class="img-fluid rounded img-fluid mr-2 avatar-60 object-fit-cover">
                                <div class="flex-grow-1">
                                    <h6 class="mb-1">${item.name}</h6>
                                    <div class="d-flex justify-content-between align-items-center">
                                        <span>$${item.price} x ${item.qty}</span>
                                        <button class="btn btn-sm btn-danger" onclick="removeFromCart(${item.product_id})">
                                            <i class="ph ph-trash"></i>
                                        </button>
                                    </div>
                                </div>
                            </div>
                        </div>
                    `;
                    });

                    $('#cartItems').html(cartHtml);
                    $('#cartTotal').text(total.toFixed(2));
                } else {
                    $('#cartItems').html(
                        '<div class="p-3 text-center">{{ __('vendorwebsite.your_cart_is_empty') }}</div>');
                    $('#cartTotal').text('0.00');
                }
            }
        }).fail(function(jqXHR, textStatus, errorThrown) {
            console.error('Error updating cart:', textStatus, errorThrown);

        });
    }

    function addToCart(productId, productVariationId, qty = 1) {

        // Check if CSRF token exists
        const token = $('meta[name="csrf-token"]').attr('content');
        if (!token) {
            console.error('CSRF token not found');
            toastr.error('{{ __('vendorwebsite.security_token_missing_please_refresh_the_page') }}');
            return;
        }

        $.ajax({
            url: '{{ route('cart.add') }}',
            type: 'POST',
            data: {
                product_id: productId,
                product_variation_id: productVariationId,
                qty: qty,
                _token: token
            },
            success: function(response) {

                if (response.status) {
                    updateCart();
                    toastr.success('{{ __('vendorwebsite.product_added_to_cart_successfully') }}');
                    // Update button state
                    $(`#addToCartBtn_${productId}`).hide();
                    $(`#removeFromCartBtn_${productId}`).show();
                } else if (response.redirect) {

                    $('#loginModal').modal('show');
                } else {
                    console.error('Add to cart failed:', response.message);
                    toastr.error(response.message);
                }
            },
            error: function(jqXHR, textStatus, errorThrown) {
                console.error('Add to cart error:', {
                    status: jqXHR.status,
                    statusText: jqXHR.statusText,
                    responseText: jqXHR.responseText,
                    textStatus: textStatus,
                    errorThrown: errorThrown
                });

                if (jqXHR.status === 401) {

                    $('#loginModal').modal('show');
                } else {
                    toastr.error('{{ __('vendorwebsite.failed_to_add_item_to_cart_please_try_again') }}');
                }
            }
        });
    }

    function removeFromCart(productId, productVariationId) {

        // Check if CSRF token exists
        const token = $('meta[name="csrf-token"]').attr('content');
        if (!token) {
            console.error('CSRF token not found');
            toastr.error('{{ __('vendorwebsite.security_token_missing_please_refresh_the_page') }}');
            return;
        }

        $.ajax({
            url: '{{ route('cart.remove') }}',
            type: 'POST',
            data: {
                product_id: productId,
                product_variation_id: productVariationId,
                _token: token
            },
            success: function(response) {

                if (response.status) {
                    updateCart();
                    toastr.success('{{ __('vendorwebsite.product_removed_from_cart_successfully') }}');
                    // Update button state
                    $(`#removeFromCartBtn_${productId}`).hide();
                    $(`#addToCartBtn_${productId}`).show();
                } else {
                    console.error('Remove from cart failed:', response.message);
                    toastr.error(response.message);
                }
            },
            error: function(jqXHR, textStatus, errorThrown) {
                console.error('Remove from cart error:', {
                    status: jqXHR.status,
                    statusText: jqXHR.statusText,
                    responseText: jqXHR.responseText,
                    textStatus: textStatus,
                    errorThrown: errorThrown
                });
                toastr.error('{{ __('vendorwebsite.failed_to_remove_item_from_cart_please_try_again') }}');
            }
        });
    }

    // Update cart on page load
    $(document).ready(function() {

        updateCart();
    });

    // Global search redirect
    $(document).ready(function() {
        function doGlobalSearch() {
            var query = $('#global-search-input').val().trim();
            if (query.length > 0) {
                window.location.href = "{{ route('search') }}" + "?query=" + encodeURIComponent(query);
            }
        }
        $('#global-search-btn').on('click', doGlobalSearch);
        $('#global-search-input').on('keypress', function(e) {
            if (e.which === 13) { // Enter key
                doGlobalSearch();
            }
        });
    });

    $(document).ready(function() {
        // Open the branch selection modal when 'View All Branch' is clicked
        $('#viewAllBranchBtn').on('click', function(e) {
            e.preventDefault();
            $('#branchSelectionModal').modal('show');
        });

        // Close the modal after a branch is selected
        // Adjust '.select-branch-btn' to match your actual selector for branch selection
        $(document).on('click', '.select-branch-btn', function() {
            // ... your branch selection logic here ...
            $('#branchSelectionModal').modal('hide');
        });

        // Make user info div clickable and redirect to profile
        $('.user-info-profile-redirect').css('cursor', 'pointer').on('click', function() {
            window.location.href = '{{ route('profile') }}';
        });
    });
</script>
@endpush
