@extends('frontend::layouts.master')
@section('title')
{{ __('messages.contact_us') }}

@endsection

@section('content')
<x-frontend::section.breadcrumb :data="$data['bread_crumb']" />
<x-frontend::section.contact_us_section :contact_us="$data['contact_us']" :superadmin_email="$data['superadmin_email']" />
<x-frontend::section.get_started_section />
@endsection
