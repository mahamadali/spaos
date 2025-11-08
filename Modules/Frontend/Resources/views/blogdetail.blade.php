@extends('frontend::layouts.master')
@section('title')
{{ __('messages.blog_detail') }}
@endsection
@section('content')
<x-frontend::section.breadcrumb :data="$data" />

    <section class="section-spacing-top">
        <div class="container">
            <div class="section-title-wrap mb-3">
                <h4 class="section-title">{{ $blog->title ?? '-' }}</h4>
            </div>
            <div class="d-flex align-items-center justify-content-between flex-wrap gap-2">
                <ul class="list-inline m-0 p-0 d-flex align-items-center row-gap-1 column-gap-3 flex-wrap category-list">
                    <li>
                        <span>{{ __('frontend.by') }}  :
                            {{ $blog->user ? $blog->user->first_name . ' ' . $blog->user->last_name : default_user_name() }}
                        </span>
                    </li>

                </ul>
                <span
                    class="date flex-shrink-0">{{ \Carbon\Carbon::parse($blog->created_at)->translatedFormat('F d, Y') ?? '-' }}</span>
            </div>
            <div class="mt-5">
                <img src="{{ asset($blog->image ?? product_feature_image()) }}"
                    class="w-100 rounded object-cover blog-detail-img" alt="blog-details">
            </div>

            <div class="blog-content mt-5">
                {!! html_entity_decode($blog->description) ?? '-' !!}

            </div>


            <div class="blog-pagination">
                <div class="row justify-content-between">
                    <div class="col-lg-4 col-md-6 blog-pagination-btn">
                        @if ($previousBlog)
                            <a href="{{ route('blog_detail', ['id' => $previousBlog->id]) }}" class="link">
                                <h6 class="text-body">{{__('messages.previous_post')}}</h6>
                                <span class="mb-0 h6 blog-pagination-link line-count-2">{{ $previousBlog->title }}</span>
                            </a>
                        @endif
                    </div>
                    <div class="col-lg-4 col-md-6 mt-md-0 mt-4 blog-pagination-btn blog-pagination-right active">
                        @if ($nextBlog)
                            <a href="{{ route('blog_detail', ['id' => $nextBlog->id]) }}" class="link">
                                <h6 class="text-body">{{__('messages.next_post')}}</h6>
                                <span class="h6 mb-0 blog-pagination-link line-count-2">{{ $nextBlog->title }}</span>
                            </a>
                        @endif
                    </div>
                </div>
            </div>

            <!-- Author Section -->
            <div class="author-card p-4">
                <div class="d-flex flex-md-nowrap flex-wrap gap-4">
                    <div class="flex-shrink-0">
                        <img src="{{ asset($blog->user->profile_image ?? default_user_avatar()) }}" alt="Author"
                            class="img-fluid rounded-circle author-img">
                    </div>
                    <div>
                        <div class="author-card-title mb-3">
                            <span class="text-heading">{{__('messages.written_by')}}</span>
                            <span
                                class="text-dark fw-bold">{{ $blog->user ? $blog->user->first_name . ' ' . $blog->user->last_name : default_user_name() }}</span>
                        </div>

                        <a href="{{ route('blogs') }}"
                            class="btn btn-secondary">{{__('messages.see_all_article')}}</a>
                    </div>
                </div>
            </div>

            @if ($relatedBlogs->isNotEmpty())
            <div class="related-blogs mt-5">
                <h4>{{__('messages.related_blogs')}}</h4>
                <div class="row">
                    @foreach($relatedBlogs as $blog)
                        <div class="col-md-4">
                            <div class="blog-card">
                                <x-frontend::card.card_blog  :blog="$blog" />
                            </div>
                        </div>
                    @endforeach
                </div>
            </div>
            @endif


        </div>
    </section>

    <x-frontend::section.get_started_section />
@endsection
