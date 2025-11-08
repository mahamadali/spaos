@extends('vendorwebsite::layouts.master')
@section('title')
    {{ $page->name ?? ' Term & Condition ' }}
@endsection

@section('content')
    <x-breadcrumb />
    <div class="about-us-section section-spacing-inner-pages">
        <div class="container">
            <div class="row">
                <div class="col-md-12 text-center">
                    <h4 class="mb-5 pb-3">{{ $page->name ?? 'Term & Condition' }}</h4>


                    <div class="mb-3">
                        {!! $page->description ??
                            '<div class="text-muted"><p>This page is currently under construction. Please check back later for updated content.</p><p>If you need immediate assistance, please contact our support team.</p></div>' !!}
                    </div>
                </div>
            </div>
        </div>
    </div>
@endsection
