@extends('frontend::layouts.master')
@section('title')
{{ __('frontend.all_feature') }}
@endsection
@section('content')
<x-frontend::section.title_section />
<x-frontend::section.feature_section />
<x-frontend::section.get_started_section />


@endsection
