<!DOCTYPE html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}" data-bs-theme="light"
    dir="{{ session()->has('dir') ? session()->get('dir') : 'ltr' }}"
    data-bs-theme-color={{ getCustomizationSetting('theme_color') }}>

<head>
    <script>
        (function() {

            const savedTheme = localStorage.getItem('data-bs-theme') || 'light';
            document.documentElement.setAttribute('data-bs-theme', savedTheme);
            if (savedTheme === 'dark') {
                document.documentElement.classList.add('dark-mode-preload');
            }
        })();
    </script>
    <style>
        .darkmode-logo {
            display: none;
        }

        html[data-bs-theme="dark"] .darkmode-logo {
            display: inline-block;
        }

        html[data-bs-theme="dark"] .light-logo {
            display: none;
        }
    </style>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <meta name="csrf-token" content="{{ csrf_token() }}">
    <meta name="baseUrl" content="{{ url('/') }}" />
    <link rel="icon" type="image/png" href="{{ asset('img/logo/favicon.png') }}">
    <link rel="apple-touch-icon" sizes="76x76" href="{{ asset('img/logo/favicon.png') }}">
    <meta http-equiv="X-UA-Compatible" content="IE=edge">
    <link rel="shortcut icon" href="{{ getVendorSetting('favicon') ?? asset('img/logo/mini_logo.png') }}">
    <link rel="icon" type="image/ico" href="{{ getVendorSetting('favicon') ?? asset('img/logo/mini_logo.png') }}" />

    <title> @yield('title') </title>

    <!-- Bootstrap JS Bundle with Popper -->
    @include('vendorwebsite::components.partials.head.plugins')

    <meta name="description" content="{{ $description ?? '' }}">
    <meta name="keywords" content="{{ $keywords ?? '' }}">
    <meta name="author" content="{{ $author ?? '' }}">
    <meta name="google" content="notranslate">
    <meta name="is-authenticated" content="{{ auth()->check() ? '1' : '0' }}">
    <meta name="data_table_limit" content="{{ setting('data_table_limit') }}">

    <!-- Fonts -->
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link
        href="https://fonts.googleapis.com/css2?family=Kalam:wght@300;400;700&family=Lexend+Deca:wght@100..900&display=swap"
        rel="stylesheet">
    <link rel="stylesheet" href="{{ asset('iconly/css/style.css') }}">

    <!-- <link rel="stylesheet" href="{{ asset('iconly/css/style.css') }}"> -->
    <link rel="stylesheet" href="{{ asset('phosphor-icons/regular/style.css') }}">
    <link rel="stylesheet" href="{{ asset('phosphor-icons/fill/style.css') }}">

    <!-- SweetAlert2 -->
    <script src="https://cdn.jsdelivr.net/npm/sweetalert2@11"></script>


    <!-- Bootstrap 5 CSS -->
    <link rel="stylesheet" href="{{ asset('modules/vendorwebsite/style.css') }}">
    <script src="https://code.jquery.com/jquery-3.6.0.min.js"></script>
    <script src="{{ asset('vendor/slick/slick.min.js') }}" defer></script>

    <!-- Toastr CSS -->
    <link href="https://cdnjs.cloudflare.com/ajax/libs/toastr.js/latest/toastr.min.css" rel="stylesheet" />

</head>

<body class="">
    @include('vendorwebsite::layouts.header')

    @yield('content')

    @php
        $footerSetting = \Modules\FrontendSetting\Models\FrontendSetting::where('type', 'footer-setting')
            ->where('created_by', session('current_vendor_id'))
            ->first();
        $footerEnabled = $footerSetting && $footerSetting->status == 1;
    @endphp
    @if ($footerEnabled)
        @include('vendorwebsite::layouts.footer')
    @endif

    @include('vendorwebsite::components.partials.back-to-top')

    <!-- jQuery (required for Bootstrap and other plugins) -->


    <script src="{{ asset('modules/vendorwebsite/script.js') }}"></script>
    @include('vendorwebsite::components.partials.scripts.plugins')
    <script>
        const wishlistAddUrl = "{{ route('wishlist.add') }}";
        const wishlistRemoveUrl = "{{ route('wishlist.remove') }}";
    </script>
    @stack('scripts')
    @php
        $defaultCurrency = \Modules\Currency\Models\Currency::getVendorDefaultCurrency(true);
    @endphp

    <script>
        const currencyFormat = (amount) => {
            const DEFAULT_CURRENCY = JSON.parse(@json(json_encode(\Modules\Currency\Models\Currency::getVendorDefaultCurrency(true))))
            const noOfDecimal = DEFAULT_CURRENCY.no_of_decimal
            const decimalSeparator = DEFAULT_CURRENCY.decimal_separator
            const thousandSeparator = DEFAULT_CURRENCY.thousand_separator
            const currencyPosition = DEFAULT_CURRENCY.currency_position
            const currencySymbol = DEFAULT_CURRENCY.currency_symbol
            console.log(DEFAULT_CURRENCY);
            return formatCurrency(amount, noOfDecimal, decimalSeparator, thousandSeparator, currencyPosition,
                currencySymbol)
        }
        window.currencyFormat = currencyFormat
        window.defaultCurrencySymbol = @json(Currency::defaultSymbol())
    </script>

    <!-- Toastr JS -->
    <script src="https://cdnjs.cloudflare.com/ajax/libs/toastr.js/latest/toastr.min.js"></script>

    <!-- Laravel Session-based Toastr -->
    <script>
        @if (session('success'))
            toastr.success("{{ session('success') }}");
        @endif

        @if (session('error'))
            toastr.error("{{ session('error') }}");
        @endif

        @if ($errors->any())
            toastr.error("{{ $errors->first() }}");
        @endif
    </script>

    <script>
        window.isLoggedIn = {{ auth()->check() ? 'true' : 'false' }};
    </script>

    @include('components.login_modal')


    <!-- Global Cancel/Delete Confirmation Modal Function -->
    <script>
        // Global function for general confirmations
        function showGeneralConfirm(options = {}) {
            const defaultOptions = {
                title: '{{ __('Are you sure?') }}',
                text: '{{ __('This action cannot be undone.') }}',
                icon: 'warning',
                confirmButtonText: '{{ __('Yes, proceed') }}',
                cancelButtonText: '{{ __('Cancel') }}',
                confirmButtonColor: 'var(--bs-danger)',
                cancelButtonColor: 'var(--bs-body-color)',
                showCancelButton: true,
                reverseButtons: true,
                customClass: {
                    confirmButton: 'btn btn-danger',
                    cancelButton: 'btn btn-secondary'
                }
            };

            const finalOptions = {
                ...defaultOptions,
                ...options
            };

            return Swal.fire(finalOptions);
        }

        // Global function for delete confirmations
        function showDeleteConfirm(options = {}) {
            const defaultOptions = {
                title: '{{ __('Delete Confirmation') }}',
                text: '{{ __('Are you sure you want to delete this item? This action cannot be undone.') }}',
                icon: 'warning',
                confirmButtonText: '{{ __('Yes, delete') }}',
                cancelButtonText: '{{ __('Cancel') }}',
                confirmButtonColor: 'var(--bs-danger)',
                cancelButtonColor: 'var(--bs-body-color)',
                showCancelButton: true,
                reverseButtons: true,
                customClass: {
                    confirmButton: 'btn btn-danger',
                    cancelButton: 'btn btn-secondary'
                }
            };

            const finalOptions = {
                ...defaultOptions,
                ...options
            };

            return Swal.fire(finalOptions);
        }

        // Global function for cancel confirmations
        function showCancelConfirm(options = {}) {
            const defaultOptions = {
                title: '{{ __('Cancel Confirmation') }}',
                text: '{{ __('Are you sure you want to cancel? Any unsaved changes will be lost.') }}',
                icon: 'question',
                confirmButtonText: '{{ __('Yes, cancel') }}',
                cancelButtonText: '{{ __('Continue editing') }}',
                showCancelButton: true,
                reverseButtons: true,
                customClass: {
                    confirmButton: 'btn btn-warning',
                    cancelButton: 'btn btn-secondary'
                }
            };

            const finalOptions = {
                ...defaultOptions,
                ...options
            };

            return Swal.fire(finalOptions);
        }

        // Helper function to handle delete actions with confirmation
        function handleDeleteAction(deleteUrl, options = {}) {
            showDeleteConfirm(options).then((result) => {
                if (result.isConfirmed) {
                    // Create a form and submit it
                    const form = document.createElement('form');
                    form.method = 'POST';
                    form.action = deleteUrl;

                    // Add CSRF token
                    const csrfToken = document.querySelector('meta[name="csrf-token"]').getAttribute('content');
                    const csrfInput = document.createElement('input');
                    csrfInput.type = 'hidden';
                    csrfInput.name = '_token';
                    csrfInput.value = csrfToken;
                    form.appendChild(csrfInput);

                    // Add method override for DELETE
                    const methodInput = document.createElement('input');
                    methodInput.type = 'hidden';
                    methodInput.name = '_method';
                    methodInput.value = 'DELETE';
                    form.appendChild(methodInput);

                    document.body.appendChild(form);
                    form.submit();
                }
            });
        }

        // Helper function to handle cancel actions with confirmation
        function handleCancelAction(cancelUrl, options = {}) {
            showCancelConfirm(options).then((result) => {
                if (result.isConfirmed) {
                    window.location.href = cancelUrl;
                }
            });
        }
    </script>

    <!-- Bootstrap JS Bundle with Popper (moved to head) -->
</body>

</html>
