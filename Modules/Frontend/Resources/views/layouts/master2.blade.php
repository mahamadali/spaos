<!DOCTYPE html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}" data-bs-theme="light">

<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <meta name="csrf-token" content="{{ csrf_token() }}">
    <meta http-equiv="X-UA-Compatible" content="IE=edge">

    <title>@yield('title')</title>  

    <meta name="description" content="{{ $description ?? '' }}">
    <meta name="keywords" content="{{ $keywords ?? '' }}">
    <meta name="author" content="{{ $author ?? '' }}">
    <meta name="baseUrl" content="{{env('APP_URL')}}" />

    <!-- Fonts -->
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link href="https://fonts.googleapis.com/css2?family=Lexend+Deca:wght@100..900&display=swap" rel="stylesheet">
    <link href="https://fonts.googleapis.com/css2?family=Fuzzy+Bubbles:wght@400;700&display=swap" rel="stylesheet">

    <link rel="stylesheet" href="{{ asset('modules/frontend/style.css') }}">
    <link rel="stylesheet" href="{{ asset('phosphor-icons/regular/style.css') }}">
    <link rel="stylesheet" href="{{ asset('phosphor-icons/fill/style.css') }}">
    <link rel="shortcut icon" href="{{ asset(setting('favicon')) }}">
    <link rel="icon" type="image/ico" href="{{ asset(setting('favicon')) }}" />

    <link rel="stylesheet" href="{{ asset('vendor/slick/slick.css') }}">
    <link rel="stylesheet" href="{{ asset('vendor/slick/slick-theme.css') }}">
    <link rel="stylesheet" href="{{ asset('vendor/datatable/datatables.min.css') }}">


</head>

<body>

    <x-frontend::section.header_section />
    @yield('content')

    <script src="{{ asset('vendor/jquery/jquery.min.js') }}"></script>
    <script src="{{ asset('vendor/slick/slick.min.js') }}"></script>
    @stack('after-scripts')


    <script src="{{ mix('modules/frontend/script.js') }}"></script>
    <script src="{{ mix('js/backend-custom.js') }}"></script>
    <script src="{{ asset('js/app.js') }}"></script>

    <script>

const currencyFormat = (amount) => {
        const DEFAULT_CURRENCY = JSON.parse(@json(json_encode(Currency::getDefaultCurrency(true))))
        const noOfDecimal = DEFAULT_CURRENCY ? DEFAULT_CURRENCY.no_of_decimal : 2
            const decimalSeparator =  DEFAULT_CURRENCY ? DEFAULT_CURRENCY.decimal_separator : '.'
            const thousandSeparator =  DEFAULT_CURRENCY ? DEFAULT_CURRENCY.thousand_separator : ','
            const currencyPosition =  DEFAULT_CURRENCY ? DEFAULT_CURRENCY.currency_position : 'left'
            const currencySymbol =  DEFAULT_CURRENCY ? DEFAULT_CURRENCY.currency_symbol : '$'
        return formatCurrency(amount, noOfDecimal, decimalSeparator, thousandSeparator, currencyPosition, currencySymbol)
      }
      window.currencyFormat = currencyFormat
      window.defaultCurrencySymbol = @json(Currency::defaultSymbol())

        </script>
     <script>
        const formatSuperadmin = (amount) => {
            const DEFAULT_CURRENCY = JSON.parse(@json(json_encode(Currency::getSuperAdminDefaultCurrency(true))))
            const noOfDecimal = DEFAULT_CURRENCY ? DEFAULT_CURRENCY.no_of_decimal : 2
            const decimalSeparator =  DEFAULT_CURRENCY ? DEFAULT_CURRENCY.decimal_separator : '.'
            const thousandSeparator =  DEFAULT_CURRENCY ? DEFAULT_CURRENCY.thousand_separator : ','
            const currencyPosition =  DEFAULT_CURRENCY ? DEFAULT_CURRENCY.currency_position : 'left'
            const currencySymbol =  DEFAULT_CURRENCY ? DEFAULT_CURRENCY.currency_symbol : '$'
            return formatCurrency(amount, noOfDecimal, decimalSeparator, thousandSeparator, currencyPosition,
                currencySymbol)
        }
        window.formatSuperadmin = formatSuperadmin
        window.defaultCurrencySymbol = @json(Currency::defaultSymbol())
    </script>

</body>
