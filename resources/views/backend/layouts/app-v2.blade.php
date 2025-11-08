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

    <link rel="icon" href="{{ asset(setting('logo')) }}" type="image/x-icon"> <!-- Favicon-->
    <link rel="apple-touch-icon" sizes="76x76" href="{{ asset(setting('favicon')) }}">

    <!-- Shortcut Icon -->
    <link rel="shortcut icon" href="{{ asset(setting('favicon') ?? 'images/logo/mini_logo.png') }}">
    <link rel="icon" type="image/x-icon" href="{{ asset(setting('favicon') ?? 'images/logo/mini_logo.png') }}">

    <link rel="stylesheet" href="{{ asset('assets/plugins/bootstrap/css/bootstrap.min.css') }}">
    <link rel="stylesheet" href="{{ asset('assets/plugins/jvectormap/jquery-jvectormap-2.0.3.min.css') }}" />
    <link rel="stylesheet" href="{{ asset('assets/plugins/morrisjs/morris.min.css') }}" />
    <!-- Custom Css -->
    <link rel="stylesheet" href="{{ asset('assets/css/main.css') }}">
    <link rel="stylesheet" href="{{ asset('assets/css/color_skins.css') }}">
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
    {{-- <div class="chat-launcher"></div> --}}
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
                                    Alexander<br> How are you!</span> </div>
                        </li>
                        <li class="right">
                            <div class="chat-info"><span class="datetime">6:16</span> <span class="message">There are
                                    many variations of passages of Lorem Ipsum available</span> </div>
                        </li>
                        <li class="left float-left"> <img src="{{ asset('assets/images/xs/avatar2.jpg') }}"
                                class="rounded-circle" alt="">
                            <div class="chat-info"> <a class="name" href="#">Elizabeth</a> <span
                                    class="datetime">6:25</span> <span class="message">Hi, Alexander,<br> John <br>
                                    What are you doing?</span> </div>
                        </li>
                        <li class="left float-left"> <img src="{{ asset('assets/images/xs/avatar1.jpg') }}"
                                class="rounded-circle" alt="">
                            <div class="chat-info"> <a class="name" href="#">Michael</a> <span
                                    class="datetime">6:28</span> <span class="message">I would love to join the
                                    team.</span> </div>
                        </li>
                        <li class="right">
                            <div class="chat-info"><span class="datetime">7:02</span> <span class="message">Hello,
                                    <br>Michael</span> </div>
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

    <!-- Jquery Core Js -->
    <script src="{{ asset('assets/bundles/libscripts.bundle.js') }}"></script> <!-- Lib Scripts Plugin Js ( jquery.v3.2.1, Bootstrap4 js) -->
    <script src="{{ asset('assets/bundles/vendorscripts.bundle.js') }}"></script> <!-- slimscroll, waves Scripts Plugin Js -->

    <script src="{{ asset('assets/bundles/morrisscripts.bundle.js') }}"></script><!-- Morris Plugin Js -->
    <script src="{{ asset('assets/bundles/jvectormap.bundle.js') }}"></script> <!-- JVectorMap Plugin Js -->
    <script src="{{ asset('assets/bundles/knob.bundle.js') }}"></script> <!-- Jquery Knob Plugin Js -->
    <script src="{{ asset('assets/bundles/sparkline.bundle.js') }}"></script> <!-- Sparkline Plugin Js -->

    <script src="{{ asset('assets/bundles/mainscripts.bundle.js') }}"></script>
    <script src="{{ asset('assets/js/common/header-notifications.js') }}"></script>
</body>

</html>
