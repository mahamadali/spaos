@extends('frontend::layouts.master')
@section('title')
{{ __('messages.blog') }}
@endsection

@section('content')
<x-frontend::section.breadcrumb :data="$data['bread_crumb']" />

<section class="section-spacing-top">
    <div class="container">
        @if(!empty($data['blogs']) && count($data['blogs']) > 0)
            <div class="row gy-5 row-cols-1 row-cols-sm-2 row-cols-md-2 row-cols-lg-3">
                @foreach($data['blogs'] as $blog)
                    <div class="col">
                        <x-frontend::card.card_blog  :blog="$blog" />
                    </div>
                @endforeach
            </div>
        @else
            <div class="text-center py-5">
                <p class="no-data-found">{{__('messages.There_are_no_blog_posts_at_this_time_New_content_is_coming_soon.')}}</p>
            </div>
        @endif
    </div>
</section>
<x-frontend::section.get_started_section />
@endsection
