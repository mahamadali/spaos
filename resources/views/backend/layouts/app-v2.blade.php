<!doctype html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}">

<head>
    <meta charset="utf-8" />
    <title> @yield('title') | {{ env('app.name') }}</title>
    <meta name="viewport" content="width=device-width, initial-scale=1.0, maximum-scale=1.0, user-scalable=no">
    <meta name="keyword" content="{{ setting('meta_keyword') }}">
    <meta name="description" content="{{ setting('meta_description') }}">
    <meta content="" name="author" />
    <meta name="setting_options" content="{{ setting('customization_json') }}">
    <meta name="csrf-token" content="{{ csrf_token() }}">
    <!-- App favicon -->
    <link rel="shortcut icon" href="{{ asset(setting('favicon') ?? 'images/logo/mini_logo.png') }}">
    <link rel="icon" type="image/x-icon" href="{{ asset(setting('favicon') ?? 'images/logo/mini_logo.png') }}">
    
    <!-- CSRF Token -->
    <meta name="csrf-token" content="{{ csrf_token() }}">
    <meta name="app_name" content="{{ app_name() }}">
    <meta name="data_table_limit" content="{{ setting('data_table_limit') ?? 10 }}">


    <meta name="auth_user_roles" content="{{ auth()->user()->roles->pluck('name') }}">
    <meta name="baseUrl" content="{{ url('/') }}" />

    @include('backend.layouts.v2.head-css')
    @include('backend.layouts.v2.vendor-styles')
    <script>
        const APP_BACKEND_URL = '{{ route("backend.dashboard") }}';
    </script>

    @yield('styles')
</head>

@section('body')
    <body data-sidebar="dark">
@show
    <!-- Begin page -->
    <div id="layout-wrapper">
        @include('backend.layouts.v2.topbar')
        @include('backend.layouts.v2.sidebar')
        <!-- ============================================================== -->
        <!-- Start right Content here -->
        <!-- ============================================================== -->
        <div class="main-content">
            <div class="page-content">
                <div class="container-fluid mb-5">
                    @yield('content')
                </div>
                <!-- container-fluid -->
            </div>
            <!-- End Page-content -->
            @include('backend.layouts.v2.footer')
        </div>
        <!-- end main content-->
    </div>
    <!-- END layout-wrapper -->

    <!-- Right Sidebar -->
    @include('backend.layouts.v2.right-sidebar')
    <!-- /Right-bar -->

    <!-- JAVASCRIPT -->
    @include('backend.layouts.v2.vendor-scripts')

    @yield('scripts')

</body>

</html>
