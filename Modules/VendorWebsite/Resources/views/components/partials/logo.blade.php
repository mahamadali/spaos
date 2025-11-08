<!-- <a href="{{route('index')}}" class="navbar-brand">
    <img src="{{ asset('img/logo/logo.png') }}" alt="Logo" class="logo img-fluid">
</a> -->


<a class="navbar-brand text-primary" href="{{ route('vendor.index') }}"> 
        <div class="logo-main">
        <div class="logo-mini d-none">
    <img src="{{ getVendorSetting('mini_logo') ? asset(getVendorSetting('mini_logo')) : asset('img/logo/mini_logo.png') }}" height="30" alt="{{ app_name() }}">
</div>
<div class="logo-normal">
    <img src="{{ getVendorSetting('logo') ? asset(getVendorSetting('logo')) : asset('img/logo/logo.png') }}" height="30" alt="{{ app_name() }}">
</div>
<div class="logo-dark">
    <img src="{{ getVendorSetting('dark_logo') ? asset(getVendorSetting('dark_logo')) : asset('img/logo/dark_logo.png') }}" height="30" alt="{{ app_name() }}">
</div>
        </div>
    </a>