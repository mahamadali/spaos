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
    <meta name="base-url" content="{{ url('/') }}">
    <link rel="icon" type="image/png" href="{{ asset('img/logo/favicon.png') }}">
    <link rel="apple-touch-icon" sizes="n76x76" href="{{ asset('img/logo/favicon.png') }}">
    <meta http-equiv="X-UA-Compatible" content="IE=edge">

    <title> {{ config('app.name', 'Laravel') }}</title>

    <meta name="description" content="{{ $description ?? '' }}">
    <meta name="keywords" content="{{ $keywords ?? '' }}">
    <meta name="author" content="{{ $author ?? '' }}">
    <meta name="google" content="notranslate">
    <link rel="shortcut icon" href="{{ getVendorSetting('favicon') ?? asset('img/logo/mini_logo.png') }}">
    <link rel="icon" type="image/ico"
        href="{{ getVendorSetting('favicon') ?? asset('img/logo/mini_logo.png') }}" />

    <!-- Fonts -->
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link
        href="https://fonts.googleapis.com/css2?family=Kalam:wght@300;400;700&family=Lexend+Deca:wght@100..900&display=swap"
        rel="stylesheet">

    <link rel="stylesheet" href="{{ asset('modules/vendorwebsite/style.css') }}">

    <link rel="stylesheet" href="{{ asset('iconly/css/style.css') }}">

    <!-- <link rel="stylesheet" href="{{ asset('iconly/css/style.css') }}"> -->
    <link rel="stylesheet" href="{{ asset('phosphor-icons/regular/style.css') }}">
    <link rel="stylesheet" href="{{ asset('phosphor-icons/fill/style.css') }}">

    <link rel="stylesheet" href="{{ asset('modules/vendorwebsite/style.css') }}">
    @include('vendorwebsite::components.partials.head.plugins')

</head>

<body class="">


    @yield('content')

    <script src="{{ asset('modules/vendorwebsite/script.js') }}"></script>
    @stack('scripts')


</body>

</html>
