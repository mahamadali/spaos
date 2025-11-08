<!DOCTYPE html>
<html data-bs-theme="light" lang="{{ app()->getLocale() }}" dir="{{ language_direction() }}" class="theme-fs-sm">

<head>
    <meta charset="utf-8">
    <meta http-equiv="X-UA-Compatible" content="IE=edge">
    <meta name="viewport" content="width=device-width, initial-scale=1.0, shrink-to-fit=no">
    <link rel="icon" type="image/png" href="{{ asset(setting('logo')) }}">
    <link rel="apple-touch-icon" sizes="76x76" href="{{ asset(setting('favicon')) }}">
    <meta name="keyword" content="{{ setting('meta_keyword') }}">
    <meta name="description" content="{{ setting('meta_description') }}">
    <meta name="setting_options" content="{{ setting('customization_json') }}">
    <!-- Shortcut Icon -->
    <link rel="shortcut icon" href="{{ asset(setting('favicon') ?? 'images/logo/mini_logo.png') }}">
    <link rel="icon" type="image/x-icon" href="{{ asset(setting('favicon') ?? 'images/logo/mini_logo.png') }}">


    <!-- CSRF Token -->
    <meta name="csrf-token" content="{{ csrf_token() }}">
    <meta name="app_name" content="{{ app_name() }}">
    <meta name="data_table_limit" content="{{ setting('data_table_limit') ?? 10 }}">


    <meta name="auth_user_roles" content="{{ auth()->user()->roles->pluck('name') }}">
    <meta name="baseUrl" content="{{ url('/') }}" />


    <title>@yield('title') </title>

    <script>
        (function() {
            const theme_mode = sessionStorage.getItem('theme_mode');
            if (theme_mode) {
                document.documentElement.setAttribute('data-bs-theme', theme_mode);
            } else {
                document.documentElement.setAttribute('data-bs-theme', 'light');
            }
        })();
    </script>

    <link rel="stylesheet" href="{{ mix('css/icon.min.css') }}">
    @if ($isNoUISlider ?? '')
        <!-- NoUI Slider css -->
        <link rel="stylesheet" href="{{ asset('vendor/noUiSilder/style/nouislider.min.css') }}">
    @endif


    @stack('before-styles')
    <link rel="stylesheet" href="{{ mix('css/libs.min.css') }}">
    <link rel="stylesheet" href="{{ mix('css/backend.css') }}">
    <link rel="stylesheet" href="{{ asset('custom-css/dashboard.css') }}">



    @if (language_direction() == 'rtl')
        <link rel="stylesheet" href="{{ asset('css/rtl.css') }}">
    @endif

    <link rel="stylesheet" href="{{ asset('css/customizer.css') }}">

    <!-- intl-tel-input CSS -->
    <link rel="stylesheet" href="{{ asset('css/intlTelInput.css') }}">
    <link rel="stylesheet" href="{{ asset('css/customized-dashboard-styles.css') }}?v={{ time() }}">

    <style>
        :root {
            <?php
            $rootColors = setting('root_colors');
            if (!empty($rootColors) && is_string($rootColors)) {
                $colors = json_decode($rootColors, true);
                if (json_last_error() === JSON_ERROR_NONE && is_array($colors) && count($colors) > 0) {
                    foreach ($colors as $key => $value) {
                        echo $key . ': ' . $value . '; ';
                    }
                }
            }
            ?>
        }
    </style>
    <style>
        /* Global validation error font size */
        form .invalid-feedback,
        form .text-danger {
            font-size: 0.990rem !important; /* match products-categories */
        }
    </style>

    <!-- Scripts -->
    @php
        $currentLang = App::currentLocale();
        $langFolderPath = base_path("lang/$currentLang");
        $filePaths = \File::files($langFolderPath);
    @endphp

    @foreach ($filePaths as $filePath)
        @php
            $fileName = pathinfo($filePath, PATHINFO_FILENAME);

            $arr = require $filePath;
            $dbLang = Modules\Language\Models\Language::getAllLang()
                ->where('language', app()->getLocale())
                ->where('file', $fileName)
                ->pluck('value', 'key')
                ->toArray();
            if (count($dbLang) > 0) {
                $arr = array_merge($arr, $dbLang);
            }
        @endphp
        <script>
            window.localMessagesUpdate = {
                ...window.localMessagesUpdate,
                "{{ $fileName }}": @json($arr)
            }
        </script>
    @endforeach
    <script>
        window.auth_permissions = @json($permissions)
    </script>
    <script>
        window.auth_profile_image = @json(default_user_avatar());
    </script>
    <script>
        window.translations = {
            processing: "{{ __('messages.processing') }}",
            search: "{{ __('messages.search') }}",
            all: "{{ __('messages.all') }}",
            lengthMenu: "{{ __('messages.lengthMenu') }}",
            info: "{{ __('messages.info') }}",
            infoEmpty: "{{ __('messages.infoEmpty') }}",
            infoFiltered: "{{ __('messages.infoFiltered') }}",
            loadingRecords: "{{ __('messages.loadingRecords') }}",
            zeroRecords: "{{ __('messages.zeroRecords') }}",
            paginate: {
                first: "{{ __('messages.paginate.first') }}",
                last: "{{ __('messages.paginate.last') }}",
                next: "{{ __('messages.paginate.next') }}",
                previous: "{{ __('messages.paginate.previous') }}"
            }
        };
    </script>

    <link rel="stylesheet" href="{{ mix('css/toastr.min.css') }}">

    @stack('after-styles')

    <x-google-analytics />

    <style>
        {!! setting('custom_css_block') !!}
    </style>
</head>

<body class="{{ !empty(getCustomizationSetting('card_style')) ? getCustomizationSetting('card_style') : '' }}">
    
    <!-- Sidebar -->
    @hasPermission('menu_builder_sidebar')
        @include('backend.includes.sidebar')
    @endhasPermission
    <!-- /Sidebar -->
    <div class="main-content wrapper">
        <div
            class="position-relative pr-hide @hasPermission('menu_builder_sidebar')
{{ !isset($isBanner) ? 'iq-banner' : '' }} default
@endhasPermission">
            <!-- Header -->
            @include('backend.includes.header')
            <!-- /Header -->
            @if (!isset($isBanner))
                <!-- Header Banner Start-->
                @hasPermission('menu_builder_sidebar')
                    @include('components.partials.sub-header')
                @endhasPermission
                <!-- Header Banner End-->
            @endif
        </div>

        <div class="conatiner-fluid content-inner pb-0" id="page_layout">
            <!-- Main content block -->
            @yield('content')
            <!-- / Main content block -->
            @if (isset($export_import) && $export_import)
                <div data-render="import-export">
                    <export-modal export-url="{{ $export_url }}"
                        :module-column-prop="{{ json_encode($export_columns) }}"
                        module-name="{{ $module_name }}"></export-modal>
                    <import-modal></import-modal>
                </div>
            @endif
        </div>

        <!-- Footer block -->
        @include('backend.includes.footer')
        <!-- / Footer block -->

    </div>

    <!-- Modal -->
    <div class="modal fade" data-iq-modal="renderer" id="renderModal" tabindex="-1" aria-labelledby="renderModalLabel"
        aria-hidden="true">
        <div class="modal-dialog">
            <div class="modal-content" data-iq-modal="content">
                <div class="modal-header">
                    <h5 class="modal-title" data-iq-modal="title">Modal title</h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                </div>
                <div class="modal-body" data-iq-modal="body">
                    <p>"{{ __('messages.model_body') }}"</p>
                </div>
            </div>
        </div>
    </div>

    @if (!isset($global_booking))
        <div data-render="global-booking">
            <booking-form booking-type="GLOBAL_BOOKING"
                :booking-data="{ branch_id: {{ $selected_branch->id ?? 0 }} }"></booking-form>
        </div>
    @endif

    @stack('before-scripts')
    @if ($isNoUISlider ?? '')
        <!-- NoUI Slider Script -->
        <script src="{{ asset('vendor/noUiSilder/script/nouislider.min.js') }}"></script>
    @endif
    <script src="{{ mix('js/backend.js') }}"></script>
    <script src="{{ asset('js/iqonic-script/utility.js') }}"></script>
    <script src="{{ asset('js/app.js') }}"></script>
    <script src="{{ asset('laravel-js/modal-view.js') }}" defer></script>
    <script>
        const currencyFormat = (amount) => {
            const DEFAULT_CURRENCY = JSON.parse(@json(json_encode(Currency::getDefaultCurrency(true))))
            const noOfDecimal = DEFAULT_CURRENCY ? DEFAULT_CURRENCY.no_of_decimal : 2
            const decimalSeparator = DEFAULT_CURRENCY ? DEFAULT_CURRENCY.decimal_separator : '.'
            const thousandSeparator = DEFAULT_CURRENCY ? DEFAULT_CURRENCY.thousand_separator : ','
            const currencyPosition = DEFAULT_CURRENCY ? DEFAULT_CURRENCY.currency_position : 'left'
            const currencySymbol = DEFAULT_CURRENCY ? DEFAULT_CURRENCY.currency_symbol : '$'
            return formatCurrency(amount, noOfDecimal, decimalSeparator, thousandSeparator, currencyPosition,
                currencySymbol)
        }
        window.currencyFormat = currencyFormat
        window.defaultCurrencySymbol = @json(Currency::defaultSymbol())
    </script>
    <script>
        const formatSuperadmin = (amount) => {
            const DEFAULT_CURRENCY = JSON.parse(@json(json_encode(Currency::getSuperAdminDefaultCurrency(true))))
            const noOfDecimal = DEFAULT_CURRENCY ? DEFAULT_CURRENCY.no_of_decimal : 2
            const decimalSeparator = DEFAULT_CURRENCY ? DEFAULT_CURRENCY.decimal_separator : '.'
            const thousandSeparator = DEFAULT_CURRENCY ? DEFAULT_CURRENCY.thousand_separator : ','
            const currencyPosition = DEFAULT_CURRENCY ? DEFAULT_CURRENCY.currency_position : 'left'
            const currencySymbol = DEFAULT_CURRENCY ? DEFAULT_CURRENCY.currency_symbol : '$'
            return formatCurrency(amount, noOfDecimal, decimalSeparator, thousandSeparator, currencyPosition,
                currencySymbol)
        }
        window.formatSuperadmin = formatSuperadmin
        window.defaultCurrencySymbol = @json(Currency::defaultSymbol())
    </script>
    <script src="{{ mix('js/booking-form.min.js') }}"></script>
    <script src="{{ mix('js/import-export.min.js') }}"></script>
    @if (isset($assets) && (in_array('textarea', $assets) || in_array('editor', $assets)))
        <script src="{{ asset('vendor/tinymce/js/tinymce/tinymce.min.js') }}"></script>
        <script src="{{ asset('vendor/tinymce/js/tinymce/jquery.tinymce.min.js') }}"></script>
    @endif

    <script src="{{ mix('js/jquery.validate.min.js') }}"></script>
    <script src="{{ mix('js/toastr.min.js') }}"></script>

    <script src="{{ mix('js/select2.js') }}"></script>

    <!-- intl-tel-input JS -->
    <script src="{{ asset('js/intlTelInput.min.js') }}"></script>

    <script>
        // @foreach (['success', 'error', 'warning', 'info'] as $msg)
        //     @if (session($msg))
        //         toastr.options.toastClass = "bg-{{ $msg }}";
        //         toastr.{{ $msg }}("{{ session($msg) }}");
        //     @endif
        // @endforeach

        @if(session('error'))
            window.errorSnackbar("{{ session('error') }}");
        @endif

        @if(session('success'))
            window.successSnackbar("{{ session('success') }}");
        @endif
    </script>


    @stack('after-scripts')
    <!-- / Scripts -->
    <script>
        $('.notification_list').on('click', function() {
            notificationList();
        });

        $(document).on('click', '.notification_data', function(event) {
            event.stopPropagation();
        })

        function notificationList(type = '') {
            var url = "{{ route('notification.list') }}";
            $.ajax({
                type: 'get',
                url: url,
                data: {
                    'type': type
                },
                success: function(res) {
                    $('.notification_data').html(res.data);
                    getNotificationCounts();
                    if (res.type == "markas_read") {
                        notificationList();
                    }
                    $('.notify_count').removeClass('notification_tag').text('');
                }
            });
        }

        function setNotification(count) {
            if (Number(count) >= 100) {
                $('.notify_count').text('99+');
            }
        }

        function getNotificationCounts() {
            var url = "{{ route('notification.counts') }}";

            $.ajax({
                type: 'get',
                url: url,
                success: function(res) {
                    if (res.counts > 0) {
                        $('.notify_count').addClass('notification_tag').text(res.counts);
                        setNotification(res.counts);
                        $('.notification_list span.dots').addClass('d-none')
                        $('.notify_count').removeClass('d-none')
                    } else {
                        $('.notify_count').addClass('d-none')
                        $('.notification_list span.dots').removeClass('d-none')
                    }

                    if (res.counts <= 0 && res.unread_total_count > 0) {
                        $('.notification_list span.dots').removeClass('d-none')
                    } else {
                        $('.notification_list span.dots').addClass('d-none')
                    }
                }
            });
        }

        getNotificationCounts();
    </script>

    <script>
        {!! setting('custom_js_block') !!}
        // @if (\Session::get('error'))
        //     Swal.fire({
        //         title: 'Error',
        //         text: '{{ session()->get('error') }}',
        //         icon: "error",
        //         showClass: {
        //             popup: 'animate__animated animate__zoomIn'
        //         },
        //         hideClass: {
        //             popup: 'animate__animated animate__zoomOut'
        //         }
        //     })
        // @endif
    </script>
    <script>
        $('#price').on('input', function() {
            let value = $(this).val();
            if (value.includes('.')) {
                let parts = value.split('.');
                if (parts[1].length > 2) {
                    $(this).val(parts[0] + '.' + parts[1].substring(0, 2));
                }
            }
        });
    </script>

</body>

</html>
