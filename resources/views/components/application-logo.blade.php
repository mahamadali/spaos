@php
    // Check if auth user is super admin, then set logo accordingly
    $logoPath = auth()->check() && auth()->user()->hasRole('admin') ? Vendorsetting('logo') : setting('logo');

    if ($logoPath == null) {
        $logoPath = asset('img/logo/logo.png');
    }

@endphp

<img src="{{ asset($logoPath) }}" class="img-fluid h-4 mb-4">
