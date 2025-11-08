@php
    $playStoreurl = setting('customer_app_play_store') ?? '';
    $appleStoreurl = setting('customer_app_app_store') ?? '';

    $footerLinks = [
        'about' => [['label' => 'Contact Us', 'url' => route('contact')], ['label' => 'FAQs', 'url' => route('faq')]],
        'app_links' => [
            ['img' => asset('img/logo/favicon/android-btn.png'), 'url' => $playStoreurl, 'alt' => 'play store'],
            ['img' => asset('img/logo/favicon/ios-btn.png'), 'url' => $appleStoreurl, 'alt' => 'apple store'],
        ],
    ];
    $companyName = setting('app_name') ?? '';
    $footerDescription = setting('copyright_text') ?? 'Copyright';
    $footerCopyright = '© ' . date('Y') . ' ' . $footerDescription;
    $footerSetting = \Modules\FrontendSetting\Models\FrontendSetting::where('type', 'footer-setting')
        ->where('key', 'footer-setting')
        ->where('created_by', session('current_vendor_id'))
        ->first();
    $sectionValues = $footerSetting
        ? (is_array($footerSetting->value)
            ? $footerSetting->value
            : json_decode($footerSetting->value, true))
        : [];
    $quickLinks = \Modules\Page\Models\Page::where('status', 1)
        ->where('created_by', session('current_vendor_id'))
        ->get();

    $siteDescription =
        getVendorSetting('site_description') ??
        'Discover effortless beauty bookings with expert stylists, exclusive offers, and a seamless experience — all from one smart platform.';

    $serviceCategories = [];
    if (!empty($sectionValues['select_category']) && is_array($sectionValues['select_category'])) {
        $serviceCategories = \Modules\Category\Models\Category::whereIn('id', $sectionValues['select_category'])
            ->where('status', 1)
            ->where('created_by', session('current_vendor_id'))
            ->where('parent_id', null)
            ->get()
            ->map(function ($category) {
                return [
                    'label' => $category->name,
                    'url' => route('service') . '/' . $category->slug,
                ];
            })
            ->toArray();
    }
@endphp

<footer>
    <div class="footer-box">
        <div class="container">
            <div class="row gy-5">

                <!-- Logo and Description -->
                <div class="col-xl-3 col-md-4 ">
                    <a href="{{ route('vendor.index') }}" class="navbar-brand d-flex align-items-center">
                        <span class="logo-normal">
                            <img src="{{ getVendorSetting('dark_logo') ? asset(getVendorSetting('dark_logo')) : asset('img/logo/dark_logo.png') }}" alt="logo" class="img-fluid"
                                loading="lazy">
                        </span>
                    </a>
                    <p class="font-size-14 text-white mt-4 mb-0">{{ $siteDescription }}</p>
                    @if (getVendorSetting('inquriy_email') != '')
                        <p class="font-size-14 text-white mt-4 mb-0"> {{ __('vendorwebsite.email') }} :
                            {{ getVendorSetting('inquriy_email') ?? 'frezka-saas@admin.com' }} </p>
                    @endif
                    @if (getVendorSetting('helpline_number') != '')
                        <p class="font-size-14 text-white mt-1 mb-0"> {{ __('vendorwebsite.contact_number') }} :
                            {{ getVendorSetting('helpline_number') ?? '1234567890' }} </p>
                    @endif


                </div>

                <div class="col-xl-1 col-lg-2 d-lg-block d-none"></div>

                <!-- About Frezka -->
                @if (!empty($sectionValues['about']))
                    <div class="col-xl-2 col-md-4">
                        <h5 class="mb-4 text-white">
                            {{ __('vendorwebsite.about_product', ['app' => setting('app_name')]) }}
                        </h5>
                        <ul class="list-unstyled footer-menu mb-0">
                            @foreach ($footerLinks['about'] as $link)
                                <li><a href="{{ $link['url'] }}" class="nav-link">{{ $link['label'] }}</a></li>
                            @endforeach
                        </ul>
                    </div>
                @endif

                <!-- Services -->
                @if (!empty($sectionValues['category']) && !empty($serviceCategories))
                    <div class="col-xl-2 col-md-4">
                        <h5 class="text-white  mb-4">{{ __('vendorwebsite.services') }}</h5>
                        <ul class="list-unstyled footer-menu mb-0">
                            @foreach ($serviceCategories as $link)
                                <li><a href="{{ $link['url'] }}" class="nav-link">{{ $link['label'] }}</a></li>
                            @endforeach
                        </ul>
                    </div>
                @endif

                <!-- Quick Links -->
                @if (!empty($sectionValues['quicklinks']) && ($sectionValues['quicklinks']['status'] ?? 1) == 1 && $quickLinks->count())
                    <div class="col-xl-2 col-lg-4 col-md-6">
                        <h5 class="text-white  mb-4">{{ __('vendorwebsite.quick_links') }}</h5>
                        <ul class="list-unstyled footer-menu mb-0">
                            @foreach ($quickLinks as $page)
                                <li><a href="{{ route('vendor.page.show', $page->slug) }}"
                                        class="nav-link">{{ $page->name }}</a></li>
                            @endforeach
                        </ul>
                    </div>
                @endif

                <!-- Stay Connected -->
                @if (
                    !empty($sectionValues['stayconnected']) &&
                        (!empty($sectionValues['social_links']['facebook']) ||
                            !empty($sectionValues['social_links']['instagram']) ||
                            !empty($sectionValues['social_links']['twitter']) ||
                            !empty($sectionValues['social_links']['youtube'])))
                    <div class="col-xl-2 col-lg-4 col-md-6">
                        <h5 class="text-white mb-4">{{ __('vendorwebsite.stay_connected') }}</h5>
                        <ul class="list-unstyled footer-menu mb-0">
                            @if (!empty($sectionValues['social_links']['facebook']))
                                <li>
                                    <a href="{{ $sectionValues['social_links']['facebook'] }}" target="_blank"
                                        class="d-flex align-items-center gap-2">
                                        <i class="ph ph-facebook-logo"></i> {{ __('vendorwebsite.facebook') }}
                                    </a>
                                </li>
                            @endif
                            @if (!empty($sectionValues['social_links']['instagram']))
                                <li>
                                    <a href="{{ $sectionValues['social_links']['instagram'] }}" target="_blank"
                                        class="d-flex align-items-center gap-2">
                                        <i class="ph ph-instagram-logo"></i> {{ __('vendorwebsite.instagram') }}
                                    </a>
                                </li>
                            @endif
                            @if (!empty($sectionValues['social_links']['twitter']))
                                <li>
                                    <a href="{{ $sectionValues['social_links']['twitter'] }}" target="_blank"
                                        class="d-flex align-items-center gap-2">
                                        <i class="ph ph-twitter-logo"></i> {{ __('vendorwebsite.twitter') }}
                                    </a>
                                </li>
                            @endif
                            @if (!empty($sectionValues['social_links']['youtube']))
                                <li>
                                    <a href="{{ $sectionValues['social_links']['youtube'] }}" target="_blank"
                                        class="d-flex align-items-center gap-2">
                                        <i class="ph ph-youtube-logo"></i> {{ __('vendorwebsite.youtube') }}
                                    </a>
                                </li>
                            @endif
                        </ul>
                    </div>
                @endif

            </div>
        </div>
    </div>

    <style>
        .footer-bottom a {
            color: white !important;
            text-decoration: none;
        }

        .footer-bottom a:hover {
            text-decoration: underline;
        }
    </style>
    <div class="footer-bottom bg-primary text-white py-3">
        <div class="container">
            <div class="row align-items-center">
                <div class="col-md-6 text-center text-md-start mb-2 mb-md-0">
                    <p class="mb-0 font-size-14 fw-medium text-white">{!! getVendorSetting('footer_text') ?? 'All rights reserved by ' . app_name() !!}</p>
                </div>
                <div class="col-md-6 text-center text-md-end">
                    <p class="mb-0 font-size-14 fw-medium text-white">{{ $footerCopyright }}</p>
                </div>
            </div>
        </div>
    </div>
</footer>
