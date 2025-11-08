@extends('frontend::layouts.master')
@section('title')
{{ __('frontend.about_us') }}
@endsection
@section('content')

    <x-frontend::section.breadcrumb :data="$data['bread_crumb']" />
    @if ($data['about_us'])
    <x-frontend::section.aboutus_section :data="$data['about_us']"  />
    @endif
    <x-frontend::section.get_started_section />
@endsection
