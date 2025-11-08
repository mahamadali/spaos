@extends('frontend::layouts.master')
@section('title')
{{ __('messages.page') }}
@endsection
@section('content')

    <x-frontend::section.breadcrumb :data="$data['page_title']" />
    
    @if ($data['page_content'])
        <section class="section-spacing-top">
            <div class="container">
                <div class="section-title-wrap center">
                    {!! $data['page_content'] !!}
                </div>
            </div>
        </section>
    @endif
    <x-frontend::section.get_started_section />


@endsection
