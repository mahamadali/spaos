@extends('vendorwebsite::layouts.master')

@section('content')
    <x-breadcrumb/>
    <div class="about-us-section section-spacing-inner-pages">
        <div class="container">
            <div class="row">
                <div class="col-md-12 text-center">
                    <h4 class="mb-5 pb-3">{{ $privacyTitle }}</h4>
                    <div class="mb-3">{!! $privacyContent !!}</div>
                </div>
            </div>
        </div>
    </div>
@endsection
