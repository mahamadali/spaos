@extends('frontend::layouts.master')
@section('title')
{{__('messages.features')}}
@endsection

@section('content')
<x-frontend::section.breadcrumb :data="$data['bread_crumb']" />
<section class="section-spacing">
    <div class="container">
        <div class="section-title-wrap center">
            <p class="subtitle">{{setting('app_name') }} {{__('messages.features')}}</p>
            <h3 class="section-title">{{__('messages.powerful_tools_to_enhance_salon_experiences')}}</h3>
        </div>
        <div class="row gy-4 row-cols-1 row-cols-sm-1 row-cols-md-2 row-cols-lg-3 row-cols-xl-4">
            @foreach($data['features'] as $feature)
            <div class="col">
                <x-frontend::card.card_feature :feature="$feature" />
            </div>
            @endforeach
        </div>
    </div>
</section>

<x-frontend::section.get_started_section/>

@endsection
