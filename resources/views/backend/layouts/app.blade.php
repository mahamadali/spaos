<!doctype html>
<html class="no-js " lang="{{ app()->getLocale() }}" dir="{{ language_direction() }}">
   <head>
      <meta charset="utf-8">
      <meta http-equiv="X-UA-Compatible" content="IE=Edge">
      <meta content="width=device-width, initial-scale=1, maximum-scale=1, user-scalable=no" name="viewport">
      <meta name="keyword" content="{{ setting('meta_keyword') }}">
      <meta name="description" content="{{ setting('meta_description') }}">
      <meta name="setting_options" content="{{ setting('customization_json') }}">
      <!-- CSRF Token -->
      <meta name="csrf-token" content="{{ csrf_token() }}">
      <meta name="app_name" content="{{ app_name() }}">
      <meta name="data_table_limit" content="{{ setting('data_table_limit') ?? 10 }}">
      <meta name="auth_user_roles" content="{{ auth()->user()->roles->pluck('name') }}">
      <meta name="baseUrl" content="{{ url('/') }}" />
      <title>@yield('title')</title>
      <link rel="icon" href="{{ asset(setting('logo')) }}" type="image/x-icon">
      <!-- Favicon-->
      <link rel="apple-touch-icon" sizes="76x76" href="{{ asset(setting('favicon')) }}">
      <!-- Shortcut Icon -->
      <link rel="shortcut icon" href="{{ asset(setting('favicon') ?? 'images/logo/mini_logo.png') }}">
      <link rel="icon" type="image/x-icon" href="{{ asset(setting('favicon') ?? 'images/logo/mini_logo.png') }}">
      @stack('before-styles')
      <link rel="stylesheet" href="{{ asset('assets/plugins/bootstrap/css/bootstrap.min.css') }}">
      <link rel="stylesheet" href="{{ asset('assets/plugins/jvectormap/jquery-jvectormap-2.0.3.min.css') }}" />
      <link rel="stylesheet" href="{{ asset('assets/plugins/bootstrap-material-datetimepicker/css/bootstrap-material-datetimepicker.css') }}" />
      <link rel="stylesheet" href="{{ asset('assets/plugins/morrisjs/morris.min.css') }}" />
      <link rel="stylesheet" href="{{ asset('assets/plugins/bootstrap-select/css/bootstrap-select.css') }}" />    
      <!-- Custom Css -->
      <link rel="stylesheet" href="{{ asset('assets/css/main.css') }}">
      <link rel="stylesheet" href="{{ asset('assets/css/color_skins.css') }}">
      <link rel="stylesheet" href="{{ asset('assets/plugins/jquery-datatable/dataTables.bootstrap4.min.css') }}">

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

      @stack('after-styles')
      @stack('head')
   </head>
   <body class="theme-cyan">
      <!-- Page Loader -->
      <div class="page-loader-wrapper">
         <div class="loader">
            <div class="m-t-30"><img class="zmdi-hc-spin" src="{{ asset(Vendorsetting('mini_logo') ?? '/images/logo/mini_logo.png') }}" width="48"
               height="48" alt="{{ app_name() }}"></div>
            <p>Please wait...</p>
         </div>
      </div>
      <!-- Overlay For Sidebars -->
      <div class="overlay"></div>
      @include('backend.includes.constants')
      <!-- Top Bar -->
      @include('backend.includes.v2.nav')
      <!-- Left Sidebar -->
      @include('backend.includes.v2.sidebar')
      <!-- Right Sidebar -->
      {{-- @ include('backend.includes.v2.right-sidebar-settings') --}}
      <!-- Chat-launcher -->
      {{-- 
      <div class="chat-launcher"></div>
      --}}
      <div class="chat-wrapper">
         <div class="card">
            <div class="header">
               <ul class="list-unstyled team-info margin-0">
                  <li class="m-r-15">
                     <h2>Design Team</h2>
                  </li>
                  <li>
                     <img src="{{ asset('assets/images/xs/avatar2.jpg') }}" alt="Avatar">
                  </li>
                  <li>
                     <img src="{{ asset('assets/images/xs/avatar3.jpg') }}" alt="Avatar">
                  </li>
                  <li>
                     <img src="{{ asset('assets/images/xs/avatar4.jpg') }}" alt="Avatar">
                  </li>
                  <li>
                     <img src="{{ asset('assets/images/xs/avatar6.jpg') }}" alt="Avatar">
                  </li>
                  <li>
                     <a href="javascript:void(0);" title="Add Member"><i class="zmdi zmdi-plus-circle"></i></a>
                  </li>
               </ul>
            </div>
            <div class="body">
               <div class="chat-widget">
                  <ul class="chat-scroll-list clearfix">
                     <li class="left float-left">
                        <img src="{{ asset('assets/images/xs/avatar3.jpg') }}" class="rounded-circle"
                           alt="">
                        <div class="chat-info">
                           <a class="name" href="#">Alexander</a>
                           <span class="datetime">6:12</span>
                           <span class="message">Hello, John </span>
                        </div>
                     </li>
                     <li class="right">
                        <div class="chat-info"><span class="datetime">6:15</span> <span class="message">Hi,
                           Alexander<br> How are you!</span> 
                        </div>
                     </li>
                     <li class="right">
                        <div class="chat-info"><span class="datetime">6:16</span> <span class="message">There are
                           many variations of passages of Lorem Ipsum available</span> 
                        </div>
                     </li>
                     <li class="left float-left">
                        <img src="{{ asset('assets/images/xs/avatar2.jpg') }}"
                           class="rounded-circle" alt="">
                        <div class="chat-info"> <a class="name" href="#">Elizabeth</a> <span
                           class="datetime">6:25</span> <span class="message">Hi, Alexander,<br> John <br>
                           What are you doing?</span> 
                        </div>
                     </li>
                     <li class="left float-left">
                        <img src="{{ asset('assets/images/xs/avatar1.jpg') }}"
                           class="rounded-circle" alt="">
                        <div class="chat-info"> <a class="name" href="#">Michael</a> <span
                           class="datetime">6:28</span> <span class="message">I would love to join the
                           team.</span> 
                        </div>
                     </li>
                     <li class="right">
                        <div class="chat-info"><span class="datetime">7:02</span> <span class="message">Hello,
                           <br>Michael</span> 
                        </div>
                     </li>
                  </ul>
               </div>
               <div class="input-group p-t-15">
                  <input type="text" class="form-control" placeholder="Enter text here...">
                  <span class="input-group-addon">
                  <i class="zmdi zmdi-mail-send"></i>
                  </span>
               </div>
            </div>
         </div>
      </div>
      <section class="content home">
         @yield('content')
      </section>
      @stack('before-scripts')

       <!-- Jquery Core Js -->
      <script src="{{ asset('assets/bundles/libscripts.bundle.js') }}"></script> <!-- Lib Scripts Plugin Js ( jquery.v3.2.1, Bootstrap4 js) -->
      <script src="{{ asset('assets/bundles/vendorscripts.bundle.js') }}"></script> <!-- slimscroll, waves Scripts Plugin Js -->
      <script src="{{ asset('assets/bundles/morrisscripts.bundle.js') }}"></script><!-- Morris Plugin Js -->
      <script src="{{ asset('assets/bundles/jvectormap.bundle.js') }}"></script> <!-- JVectorMap Plugin Js -->
      <script src="{{ asset('assets/bundles/knob.bundle.js') }}"></script> <!-- Jquery Knob Plugin Js -->
      <script src="{{ asset('assets/bundles/sparkline.bundle.js') }}"></script> <!-- Sparkline Plugin Js -->
      <script src="{{ asset('assets/bundles/mainscripts.bundle.js') }}"></script>
      <script src="{{ asset('assets/js/common/header-notifications.js') }}"></script>
    
    <script>
        const initDatatable = ({ url, finalColumns, advanceFilter, drawCallback = undefined, orderColumn }) => {
    const data_table_limit = $('meta[name="data_table_limit"]').attr('content')

    window.renderedDataTable = $('#datatable').DataTable({
      processing: true,
      serverSide: true,
      autoWidth: false,
      responsive: true,
      fixedHeader: true,
      lengthMenu: [
        [5, 10, 15, 20, 25, 100, -1],
        [5, 10, 15, 20, 25, 100, 'All']
      ],
      order: orderColumn,
      pageLength: data_table_limit,
      language: {
        processing: window.translations.processing,
        search: window.translations.search,
        lengthMenu: window.translations.lengthMenu,
        info: window.translations.info,
        infoEmpty: window.translations.infoEmpty,
        infoFiltered: window.translations.infoFiltered,
        loadingRecords: window.translations.loadingRecords,
        zeroRecords: window.translations.zeroRecords,
        paginate: {
          first: window.translations.paginate.first,
          last: window.translations.paginate.last,
          next: window.translations.paginate.next,
          previous: window.translations.paginate.previous
        }
      },
      dom: '<"row align-items-center"><"table-responsive my-3 mt-3 mb-2 pb-1" rt><"row align-items-center data_table_widgets" <"col-md-6" <"d-flex align-items-center flex-wrap gap-3" l i>><"col-md-6" p>><"clear">',
      ajax: {
        type: 'GET',
        url: url,
        data: function (d) {
          d.search = {
            value: $('.dt-search').val()
          }
          d.filter = {
            column_status: $('#column_status').val()
          }
          if (typeof advanceFilter == 'function' && advanceFilter() !== undefined) {
            d.filter = { ...d.filter, ...advanceFilter() }
          }
        }
      },

      drawCallback: function () {
        if (drawCallback !== undefined && typeof drawCallback == 'function') {
          drawCallback()
        }
      },
      columns: finalColumns
    })
  }
  window.initDatatable = initDatatable
  
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

        function formatCurrency(number, noOfDecimal, decimalSeparator, thousandSeparator, currencyPosition, currencySymbol) {
    // Convert the number to a string with the desired decimal places
    let formattedNumber = parseFloat(number).toFixed(noOfDecimal);


    // Split the number into integer and decimal parts
    let [integerPart, decimalPart] = formattedNumber.split('.')

    // Add thousand separators to the integer part
    integerPart = integerPart.replace(/\B(?=(\d{3})+(?!\d))/g, thousandSeparator)

    // Set decimalPart to an empty string if it is undefined
    decimalPart = decimalPart || ''

    // Construct the final formatted currency string
    let currencyString = ''

    if (currencyPosition === 'left' || currencyPosition === 'left_with_space') {
      currencyString += currencySymbol
      if (currencyPosition === 'left_with_space') {
        currencyString += ' '
      }
      currencyString += integerPart
      // Add decimal part and decimal separator if applicable
      if (noOfDecimal > 0) {
        currencyString += decimalSeparator + decimalPart
      }
    }

    if (currencyPosition === 'right' || currencyPosition === 'right_with_space') {
      // Add decimal part and decimal separator if applicable
      if (noOfDecimal > 0) {
        currencyString += integerPart + decimalSeparator + decimalPart
      }
      if (currencyPosition === 'right_with_space') {
        currencyString += ' '
      }
      currencyString += currencySymbol
    }

    return currencyString
  }

  // Enhanced currency formatting with word wrapping for long values
  function formatCurrencyWithWrapping(number, noOfDecimal, decimalSeparator, thousandSeparator, currencyPosition, currencySymbol) {
    const formatted = formatCurrency(number, noOfDecimal, decimalSeparator, thousandSeparator, currencyPosition, currencySymbol);

    // If the formatted string has more than 50 decimal places, break at 50 decimals
    const parts = formatted.split(decimalSeparator);
    if (parts.length === 2 && parts[1].length > 50) {
      const integerPart = parts[0];
      const decimalPart = parts[1];

      // Split decimal part into chunks of 50 characters
      let result = integerPart + decimalSeparator;
      for (let i = 0; i < decimalPart.length; i += 50) {
        if (i > 0) {
          result += '\n' + ' '.repeat(integerPart.length + decimalSeparator.length);
        }
        result += decimalPart.substring(i, i + 50);
      }

      return result;
    }

    return formatted;
  }

  window.formatCurrency = formatCurrency
  window.formatCurrencyWithWrapping = formatCurrencyWithWrapping
    </script>

      <!-- Jquery DataTable Plugin Js --> 
    <script src="{{ asset('assets/bundles/datatablescripts.bundle.js') }}"></script>
    <script src="{{ asset('assets/plugins/jquery-datatable/buttons/dataTables.buttons.min.js') }}"></script>
    <script src="{{ asset('assets/plugins/jquery-datatable/buttons/buttons.bootstrap4.min.js') }}"></script>
    <script src="{{ asset('assets/plugins/jquery-datatable/buttons/buttons.colVis.min.js') }}"></script>
    <script src="{{ asset('assets/plugins/jquery-datatable/buttons/buttons.flash.min.js') }}"></script>
    <script src="{{ asset('assets/plugins/jquery-datatable/buttons/buttons.html5.min.js') }}"></script>
    <script src="{{ asset('assets/plugins/jquery-datatable/buttons/buttons.print.min.js') }}"></script>
      
      @stack('after-scripts')
   </body>
</html>