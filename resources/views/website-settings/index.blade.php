@extends('backend.layouts.app')

@section('title')
    {{ __('frontend.landing_page_settings') }}
@endsection


@push('after-styles')
    <link rel="stylesheet" href="{{ mix('modules/constant/style.css') }}">
@endpush
@section('content')

<div class="block-header">
        <div class="row">
            <div class="col-lg-7 col-md-6 col-sm-12">
                <h2>{{ __('frontend.landing_page_settings') }}
                    <small class="text-muted">{{ config('app.name') }}</small>
                </h2>
            </div>
        </div>
        </div>

<div class="container-fluid">
    <div class="card">
        <div class="card-body">

            <form id="website-setting-form" enctype="multipart/form-data" method="POST"
                action="{{ route('backend.website-setting.store') }}">
                @csrf
                <div class="mt-4">
                    <ul class="nav nav-tabs gap-2 mb-4" id="settingsTabs" role="tablist">
                        <li class="nav-item">
                            <a class="nav-link active" id="homepage-setting-tab" data-toggle="tab" href="#homepage-setting"
                                role="tab" aria-controls="homepage-setting"
                                aria-selected="true">{{ __('frontend.homepage_settin') }}</a>
                        </li>
                        <!-- Settings Tab -->
                        <li class="nav-item">
                            <a class="nav-link" id="settings-tab" data-toggle="tab" href="#settings" role="tab"
                                aria-controls="settings" aria-selected="false">{{ __('messages.general_setting') }}</a>
                        </li>

                        <li class="nav-item">
                            <a class="nav-link" id="feature-tab" data-toggle="tab" href="#feature" role="tab"
                                aria-controls="feature" aria-selected="false">{{ __('frontend.features') }}</a>
                        </li>
                        <!-- About Us Tab -->
                        <li class="nav-item">
                            <a class="nav-link" id="about-us-tab" data-toggle="tab" href="#about-us" role="tab"
                                aria-controls="about-us" aria-selected="false">{{ __('frontend.about_us') }}</a>
                        </li>
                        <!-- Website logo Tab -->

                        <!-- Homepage Setting Tab -->

                    </ul>

                    <input type="hidden" name="type" id="selected_type" value="settings">

                    <!-- Tab Content -->
                    <div class="tab-content">
                        <!-- Settings Tab Content -->
                        <div class="tab-pane fade " id="settings" role="tabpanel"
                            aria-labelledby="settings-tab">
                            <div class="settings-box  bg-body rounded">
                                <h4 class="mb-4">{{ __('messages.general_setting') }}</h4>
                                <div class="row">
                                    <div class="col-md-6">
                                        <div class="form-group">
                                            <label class="form-label"
                                                for="facebook_link">{{ __('frontend.facebook_link') }}</label>
                                            <input type="url" name="facebook_link" id="facebook_link"
                                                class="form-control" placeholder="{{ __('frontend.enter_facebook') }}"
                                                value="{{ isset($web_setting) ? $web_setting->facebook_link : '' }}">
                                            <span class="error text-danger"></span>
                                        </div>
                                    </div>
                                    <div class="col-md-6">
                                        <div class="form-group">
                                            <label class="form-label"
                                                for="instagram_link">{{ __('frontend.instagram_link') }}</label>
                                            <input type="url" name="instagram_link" id="instagram_link"
                                                class="form-control" placeholder="{{ __('frontend.enter_instagram') }}"
                                                value="{{ isset($web_setting) ? $web_setting->instagram_link : '' }}">
                                            <span class="error text-danger"></span>
                                        </div>
                                    </div>
                                    <div class="col-md-6">
                                        <div class="form-group">
                                            <label class="form-label"
                                                for="youtube_link">{{ __('frontend.youtube_link') }}</label>
                                            <input type="url" name="youtube_link" id="youtube_link" class="form-control"
                                                placeholder="{{ __('frontend.enter_youtube') }}"
                                                value="{{ isset($web_setting) ? $web_setting->youtube_link : '' }}">
                                            <span class="error text-danger"></span>
                                        </div>
                                    </div>
                                    <div class="col-md-6">
                                        <div class="form-group">
                                            <label class="form-label"
                                                for="twitter_link">{{ __('frontend.twitter_link') }}</label>
                                            <input type="url" name="twitter_link" id="twitter_link" class="form-control"
                                                placeholder="{{ __('frontend.enter_twitter') }}"
                                                value="{{ isset($web_setting) ? $web_setting->twitter_link : '' }}">
                                            <span class="error text-danger"></span>
                                        </div>
                                    </div>



                                </div>
                            </div>
                        </div>

                        <!-- Feature Tab Content -->
                        <div class="tab-pane fade" id="feature" role="tabpanel" aria-labelledby="feature-tab">
                            <div class="d-flex align-items-center justify-content-between gap-3 mb-4">
                                <h4 class="m-0"> {{ __('frontend.all_feature') }}</h4>
                                <button type="button" class="btn btn-primary btn-add"><i class="fas fa-plus-circle"></i>
                                    Add</button>
                            </div>
                            <div class="form-group ">
                                <div id="features-container" class="row gy-4 row-cols-md-2 row-cols-lg-3">
                                    <!-- Default input field -->
                                    @forelse ($features as $feature)
                                        <div class="col settings-box">
                                            <div class="p-4 bg-body rounded">
                                                <input type="hidden" name="website_setting_id" id="feature_id"
                                                    class="form-control" value="{{ $web_setting->id }}">
                                                <div>
                                                    <div class="form-group">
                                                        <label class="form-label"
                                                            for="feature_title">{{ __('frontend.title') }}
                                                        </label>
                                                        <input type="hidden" name="feature_id[]" id="feature_id"
                                                            class="form-control"
                                                            value="{{ isset($feature) ? $feature->id : null }}">
                                                        <input type="text" name="feature_title[]" id="feature_title"
                                                            class="form-control"
                                                            placeholder="{{ __('frontend.enter_features') }}"
                                                            value="{{ isset($feature) ? $feature->title : '' }}">
                                                        <span class="error text-danger"></span>
                                                    </div>
                                                </div>
                                                <div>
                                                    <div class="form-group">
                                                        <label class="form-label"
                                                            for="feature_description">{{ __('frontend.descriptions') }}</label>
                                                        <textarea name="feature_description[]" id="" class="form-control" cols="30" rows="3">{{ isset($feature) ? $feature->description : '' }}</textarea>
                                                        <span class="error text-danger"></span>
                                                    </div>
                                                </div>
                                                <div>
                                                    <div class="form-group">
                                                        <label class="form-label"
                                                            for="feature_image">{{ __('frontend.image') }}</label>
                                                        <div class="position-relative">
                                                            <img class="image-preview img-fluid w-100"
                                                                src="{{ asset($feature && $feature->image ? $feature->image : product_feature_image()) }}"
                                                                data-default="{{ asset(product_feature_image()) }}"
                                                                style="height:150px; object-fit: cover;">
                                                            <div
                                                                class="upload-buttons d-flex justify-content-center gap-3">
                                                                <button type="button"
                                                                    class="btn btn-sm btn-primary upload-image">{{ __('frontend.upload') }}</button>
                                                                <button type="button"
                                                                    class="btn btn-sm btn-secondary remove-image">{{ __('frontend.remove') }}</button>
                                                                <input type="file" class="file-input form-control"
                                                                    name="feature_image[]" accept="image/*"
                                                                    style="display: none;">
                                                            </div>
                                                        </div>

                                                        <span class="error text-danger"></span>
                                                    </div>

                                                </div>
                                                <div>
                                                    <div class="d-flex align-items-center justify-content-end gap-3">

                                                        <button type="button"
                                                            class="btn btn-danger btn-remove">{{ __('frontend.remove') }}</button>
                                                    </div>
                                                </div>

                                            </div>
                                        </div>
                                    @empty
                                        <div class="col settings-box">
                                            <div class="  bg-body rounded">
                                                <div class="row">
                                                    <div class="col-lg-3 col-md-6">
                                                        <div class="form-group">
                                                            <label class="form-label"
                                                                for="feature_title">{{ __('frontend.title') }}</label>
                                                            <input type="text" name="feature_title[]"
                                                                id="feature_title" class="form-control"
                                                                placeholder="{{ __('frontend.enter_features') }}"
                                                                value="{{ isset($feature) ? $feature->title : '' }}">
                                                            <span class="error text-danger"></span>
                                                        </div>
                                                    </div>

                                                    <div class="col-lg-3 col-md-6">
                                                        <div class="form-group">
                                                            <label class="form-label"
                                                                for="feature_description">{{ __('frontend.descriptions') }}</label>
                                                            <textarea name="feature_description[]" id="" class="form-control" cols="30" rows="3">{{ isset($feature) ? $feature->description : '' }}</textarea>
                                                            <span class="error text-danger"></span>
                                                        </div>
                                                    </div>
                                                    <div class="col-lg-4 col-md-6">
                                                        <div class="form-group">
                                                            <label class="form-label"
                                                                for="feature_image">{{ __('frontend.image') }}</label>
                                                            <div class="d-flex flex-column align-items-center">
                                                                <img class="image-preview img-fluid"
                                                                    data-default="{{ asset(product_feature_image()) }}"
                                                                    style="width: 250px; height: 150px; object-fit: cover;">
                                                                <div class="d-flex justify-content-center gap-3 mt-3">
                                                                    <button type="button"
                                                                        class="btn btn-primary upload-image">{{ __('frontend.upload') }}</button>
                                                                    <button type="button"
                                                                        class="btn btn-danger remove-image">{{ __('frontend.remove') }}</button>
                                                                    <input type="file" class="file-input form-control"
                                                                        name="feature_image[]" accept="image/*"
                                                                        style="display: none;">
                                                                </div>
                                                            </div>

                                                            <span class="error text-danger"></span>

                                                        </div>
                                                    </div>
                                                    <div class="form-group col-lg-2 col-md-6">
                                                        <div class="form-group">
                                                            <button type="button"
                                                                class="btn btn-success btn-add">+</button>
                                                            <button type="button"
                                                                class="btn btn-danger btn-remove">-</button>
                                                        </div>
                                                    </div>
                                                </div>
                                            </div>
                                        </div>
                                    @endforelse
                                </div>
                            </div>
                        </div>

                        <!-- About Us Tab Content -->
                        <div class="tab-pane fade" id="about-us" role="tabpanel" aria-labelledby="about-us-tab">
                            <div class="settings-box  bg-body rounded">
                                <div class="row">
                                    <div class="col-12">
                                        <div class="form-group">
                                            <label class="form-label setting-label"
                                                for="about_us">{{ __('frontend.about_us') }}</label>
                                            <textarea id="about_us_textarea" name="about_us">{{ isset($web_setting) ? $web_setting->about_us : '' }}</textarea>
                                        </div>
                                    </div>
                                    {{-- <div class="col-12">
                                        <div class="form-group">
                                            <div class="form-check form-switch">
                                                <label class="form-label">{{ __('frontend.status') }}</label>
                                                <input class="form-check-input" id="status" name="status"
                                                    type="checkbox" value="1"
                                                    {{ isset($web_setting) && $web_setting->status == 1 ? 'checked' : '-' }}>
                                            </div>
                                        </div>
                                    </div> --}}


                                </div>
                            </div>
                        </div>

                        <!-- Website Logo Tab Content -->
                        <div class="tab-pane fade" id="website-logo" role="tabpanel" aria-labelledby="website-logo-tab">
                            <div class="row">
                                <h4 class="mb-4">{{ __('frontend.website_logo') }}</h4>
                                <div class="col-12">
                                    <div class="settings-box  bg-body rounded">
                                        <div class="form-group">
                                            <label class="form-label setting-label"
                                                for="website_logo2">{{ __('frontend.frontend_logo') }}</label>

                                            <div class="d-flex flex-column">
                                                <img class="image-preview img-fluid"
                                                    src="{{ asset($web_setting && $web_setting->website_logo ? $web_setting->website_logo : product_feature_image()) }}"
                                                    data-default="{{ asset(product_feature_image()) }}"
                                                    style="width: 250px; height: 150px; object-fit: cover;">
                                                <div class="d-flex gap-3 mt-3">
                                                    <button type="button"
                                                        class="btn btn-secondary upload-image">{{ __('frontend.upload') }}</button>
                                                    <button type="button"
                                                        class="btn btn-danger remove-image">{{ __('frontend.remove') }}</button>
                                                    <input type="file" class="file-input form-control"
                                                        name="website_logo" accept="image/*" style="display: none;">
                                                </div>
                                            </div>
                                        </div>
                                    </div>
                                </div>
                            </div>
                        </div>

                        <!--Homepage Setting Tab Content -->
                        <div class="tab-pane fade show active" id="homepage-setting" role="tabpanel"
                            aria-labelledby="homepage-setting-tab">

                            <h4 class="mb-4">{{ __('frontend.homepage_settin') }}</h4>
                            <!-- Banner Section (3 Images in one line) -->
                            <div class="row mb-4">
                                <div class="col-md-12">
                                    <div class="form-group">
                                        <div class="settings-box  bg-body rounded">
                                            <h5 class="mb-3">{{ __('frontend.banner') }}</h5>
                                            <label class="form-label"
                                                for="banner_image1">{{ __('frontend.banner_images') }}</label>
                                            <div class="row gy-4">
                                                <div class="col-md-4">
                                                    <div class="form-group">
                                                        <div class="d-flex flex-column align-items-center">
                                                            <div class="image-box">
                                                                <img class="image-preview img-fluid"
                                                                    src="{{ asset($homepages && $homepages->firstWhere('key', 'banner_image1') ? $homepages->firstWhere('key', 'banner_image1')->value : product_feature_image()) }}"
                                                                    data-default="{{ asset(product_feature_image()) }}"
                                                                    style="width: 250px; height: 150px; object-fit: cover;">
                                                                <div class="d-flex justify-content-center gap-3 mt-3">
                                                                    <button type="button"
                                                                        class="btn btn-sm btn-primary upload-image">{{ __('frontend.upload') }}</button>
                                                                    <button type="button"
                                                                        class="btn btn-sm btn-danger remove-image">{{ __('frontend.remove') }}</button>
                                                                    <input type="file" class="file-input form-control"
                                                                        name="banner_image1" accept="image/*"
                                                                        style="display: none;">
                                                                </div>
                                                            </div>
                                                        </div>
                                                    </div>
                                                </div>
                                                <div class="col-md-4">
                                                    <div class="form-group">
                                                        <div class="d-flex flex-column align-items-center">
                                                            <div class="image-box">
                                                                <img class="image-preview img-fluid"
                                                                    src="{{ asset($homepages && $homepages->firstWhere('key', 'banner_image2') ? $homepages->firstWhere('key', 'banner_image2')->value : product_feature_image()) }}"
                                                                    data-default="{{ asset(product_feature_image()) }}"
                                                                    style="width: 250px; height: 150px; object-fit: cover;">
                                                                <div class="d-flex justify-content-center gap-3 mt-3">
                                                                    <button type="button"
                                                                        class="btn btn-sm btn-primary upload-image">{{ __('frontend.upload') }}</button>
                                                                    <button type="button"
                                                                        class="btn btn-sm btn-danger remove-image">{{ __('frontend.remove') }}</button>
                                                                    <input type="file" class="file-input form-control"
                                                                        name="banner_image2" accept="image/*"
                                                                        style="display: none;">
                                                                </div>
                                                            </div>
                                                        </div>
                                                    </div>
                                                </div>
                                                <div class="col-md-4">
                                                    <div class="form-group">
                                                        <div class="d-flex flex-column align-items-center">
                                                            <div class="image-box">
                                                                <img class="image-preview img-fluid"
                                                                    src="{{ asset($homepages && $homepages->firstWhere('key', 'banner_image3') ? $homepages->firstWhere('key', 'banner_image3')->value : product_feature_image()) }}"
                                                                    data-default="{{ asset(product_feature_image()) }}"
                                                                    style="width: 250px; height: 150px; object-fit: cover;">
                                                                <div class="d-flex justify-content-center gap-3 mt-3">
                                                                    <button type="button"
                                                                        class="btn btn-sm btn-primary upload-image">{{ __('frontend.upload') }}</button>
                                                                    <button type="button"
                                                                        class="btn btn-sm btn-danger remove-image">{{ __('frontend.remove') }}</button>
                                                                    <input type="file" class="file-input form-control"
                                                                        name="banner_image3" accept="image/*"
                                                                        style="display: none;">
                                                                </div>
                                                            </div>
                                                        </div>
                                                    </div>
                                                </div>
                                            </div>

                                            <!-- Banner Text Fields (Title, Subtitle, Badge Text, Link) -->
                                            <div class="col-md-12">
                                                <div class="row gy-4 mt-3">
                                                    <div class="col-md-4">
                                                        <div class="form-group">
                                                            <label class="form-label"
                                                                for="banner_title">{{ __('frontend.banner_title') }}</label>
                                                            <input type="text" name="banner_title"
                                                                class="form-control"
                                                                placeholder="{{ __('frontend.enter_banner') }}"
                                                                value="{{ isset($homepages) ? $homepages->firstWhere('key', 'banner_title')->value : '' }}">
                                                        </div>
                                                    </div>
                                                    <div class="col-md-4">
                                                        <div class="form-group">
                                                            <label class="form-label"
                                                                for="banner_subtitle">{{ __('frontend.banner_subtitle') }}</label>
                                                            <input type="text" name="banner_subtitle"
                                                                class="form-control"
                                                                placeholder="{{ __('frontend.enter_banner_subtitle') }}"
                                                                value="{{ isset($homepages) ? $homepages->firstWhere('key', 'banner_subtitle')->value : '' }}">
                                                        </div>
                                                    </div>
                                                    <div class="col-md-4">
                                                        <div class="form-group">
                                                            <label class="form-label"
                                                                for="banner_badge_text">{{ __('frontend.banner_badge_text') }}</label>
                                                            <input type="text" name="banner_badge_text"
                                                                class="form-control"
                                                                placeholder="{{ __('frontend.enter_banner_badge') }}"
                                                                value="{{ isset($homepages) ? $homepages->firstWhere('key', 'banner_badge_text')->value : '' }}">
                                                        </div>
                                                    </div>
                                                </div>
                                            </div>
                                        </div>
                                    </div>
                                </div>
                            </div>

                            <!-- About Us Section in one card -->
                            <div class="row mb-4">
                                <div class="col-md-12">
                                    <div class="settings-box  bg-body rounded">
                                        <h5 class="mb-3">{{ __('frontend.about') }}</h5>
                                        <div class="row gy-4">
                                            <div class="col-md-6">
                                                <div class="form-group">
                                                    <label class="form-label"
                                                        for="about_title">{{ __('frontend.about_title') }}</label>
                                                    <input type="text" name="about_title" class="form-control"
                                                        placeholder="{{ __('frontend.enter_about') }}"
                                                        value="{{ isset($homepages) ? $homepages->firstWhere('key', 'about_title')->value : '' }}">
                                                </div>
                                            </div>
                                            <div class="col-md-6">
                                                <div class="form-group">
                                                    <label class="form-label"
                                                        for="about_subtitle">{{ __('frontend.about_subtitle') }}</label>
                                                    <input type="text" name="about_subtitle" class="form-control"
                                                        placeholder="{{ __('frontend.enter_about_subtitle') }}"
                                                        value="{{ isset($homepages) ? $homepages->firstWhere('key', 'about_subtitle')->value : '' }}">
                                                </div>
                                            </div>
                                            <div class="col-md-12">
                                                <div class="form-group">
                                                    <label class="form-label"
                                                        for="about_description">{{ __('frontend.about_description') }}</label>
                                                    <textarea name="about_description" class="form-control" rows="3">{{ isset($homepages) ? $homepages->firstWhere('key', 'about_description')->value : '' }}</textarea>
                                                </div>
                                            </div>
                                        </div>
                                    </div>
                                </div>
                            </div>

                            <!-- Video Section -->
                            <div class="row mb-4">
                                <div class="col-md-12">
                                    <div class="settings-box  bg-body rounded">
                                        <h5 class="mb-3">{{ __('frontend.video') }}</h5>
                                        <div class="row gy-4">
                                            <div class="col-md-4">
                                                <div class="form-group">
                                                    <label class="form-label"
                                                        for="video">{{ __('frontend.video_img') }}</label>
                                                    <div class="d-flex flex-column align-items-center">
                                                        <div class="image-box">
                                                            <img class="image-preview img-fluid"
                                                                src="{{ asset($homepages && $homepages->firstWhere('key', 'video_img') ? $homepages->firstWhere('key', 'video_img')->value : product_feature_image()) }}"
                                                                data-default="{{ asset(product_feature_image()) }}"
                                                                style="width: 250px; height: 150px; object-fit: cover;">
                                                            <div class="d-flex justify-content-center gap-3 mt-3">
                                                                <button type="button"
                                                                    class="btn btn-sm btn-primary upload-image">{{ __('frontend.upload') }}</button>
                                                                <button type="button"
                                                                    class="btn btn-sm btn-danger remove-image">{{ __('frontend.remove') }}</button>
                                                                <input type="file" class="file-input form-control"
                                                                    name="video_img" accept="image/*"
                                                                    style="display: none;">
                                                            </div>
                                                        </div>
                                                    </div>

                                                </div>
                                            </div>
                                            <div class="col-md-8">
                                                <div class="form-group">
                                                    <label class="form-label"
                                                        for="video_type">{{ __('frontend.video_type') }}</label>
                                                    <select name="video_type" id="video_type"
                                                        class="form-select select2">
                                                        <option value="">{{ __('frontend.select_video_type') }}
                                                        </option>
                                                        <option value="mp4"
                                                            {{ isset($homepages) && $homepages->firstWhere('key', 'video_type')->value == 'mp4' ? 'selected' : '' }}>
                                                            MP4</option>
                                                        <option value="youtube"
                                                            {{ isset($homepages) && $homepages->firstWhere('key', 'video_type')->value == 'youtube' ? 'selected' : '' }}>
                                                            YouTube</option>
                                                    </select>
                                                </div>
                                                <div class="form-group">
                                                    <label class="form-label"
                                                        for="video_url">{{ __('frontend.video_URL') }}</label>
                                                    <input type="url" name="video_url" id="video_url"
                                                        class="form-control"
                                                        placeholder="{{ __('frontend.enter_video_URL') }}"
                                                        value="{{ isset($homepages) ? $homepages->firstWhere('key', 'video_url')->value : '' }}">
                                                    <span id="video_url_error" class="text-danger"
                                                        style="display: none;">{{ __('frontend.invalid_videourl') }}</span>
                                                </div>
                                            </div>
                                        </div>
                                    </div>
                                </div>
                            </div>

                            <!-- Choose Us Section -->
                            <div class="row mb-4">
                                <div class="col-md-12">
                                    <div class="settings-box  bg-body rounded">
                                        <h5 class="mb-3">{{ __('frontend.why_choose') }}</h5>
                                        <div class="row">
                                            <div class="col-md-4">
                                                <div class="form-group">
                                                    <label class="form-label d-block"
                                                        for="chooseUs_image">{{ __('frontend.choose_us_image') }}</label>
                                                    <div class="d-flex flex-column align-items-center">
                                                        <div class="image-box">
                                                            <img class="image-preview img-fluid"
                                                                src="{{ asset($homepages && $homepages->firstWhere('key', 'chooseUs_image') ? $homepages->firstWhere('key', 'chooseUs_image')->value : product_feature_image()) }}"
                                                                data-default="{{ asset(product_feature_image()) }}"
                                                                style="width: 250px; height: 150px; object-fit: cover;">
                                                            <div class="d-flex justify-content-center gap-3 mt-3">
                                                                <button type="button"
                                                                    class="btn btn-sm btn-primary upload-image">{{ __('frontend.upload') }}</button>
                                                                <button type="button"
                                                                    class="btn btn-sm btn-danger remove-image">{{ __('frontend.remove') }}</button>
                                                                <input type="file" class="file-input form-control"
                                                                    name="chooseUs_image" accept="image/*"
                                                                    style="display: none;">
                                                            </div>
                                                        </div>
                                                    </div>
                                                </div>
                                            </div>
                                            <div class="col-md-8">
                                                <div class="row gy-4">
                                                    <div class="col-md-6">
                                                        <div class="form-group">
                                                            <label class="form-label"
                                                                for="chooseUs_title">{{ __('frontend.choose_us_title') }}</label>
                                                            <input type="text" name="chooseUs_title"
                                                                class="form-control"
                                                                placeholder="{{ __('frontend.choose_title') }}"
                                                                value="{{ isset($homepages) ? $homepages->firstWhere('key', 'chooseUs_title')->value : '' }}">
                                                        </div>
                                                    </div>
                                                    <div class="col-md-6">
                                                        <div class="form-group">
                                                            <label class="form-label"
                                                                for="chooseUs_subtitle">{{ __('frontend.choose_us_subtitle') }}</label>
                                                            <input type="text" name="chooseUs_subtitle"
                                                                class="form-control"
                                                                placeholder="{{ __('frontend.choose_subtitle') }}"
                                                                value="{{ isset($homepages) ? $homepages->firstWhere('key', 'chooseUs_subtitle')->value : '' }}">
                                                        </div>
                                                    </div>
                                                    <div class="col-md-12">
                                                        <div class="form-group">
                                                            <label class="form-label d-block"
                                                                for="choose_us_feature_list">{{ __('frontend.choose_feature') }}</label>
                                                            <select name="choose_us_feature_list[]"
                                                                id="choose_us_feature_list" class="select2 form-control"
                                                                multiple>
                                                                <!-- Options will be populated dynamically -->
                                                            </select>
                                                        </div>
                                                    </div>
                                                </div>
                                            </div>
                                            <div class="col-md-12">
                                                <div class="form-group">
                                                    <label class="form-label"
                                                        for="chooseUs_description">{{ __('frontend.choose_us_description') }}</label>
                                                    <textarea name="chooseUs_description" class="form-control" rows="3">{{ isset($homepages) ? $homepages->firstWhere('key', 'chooseUs_description')->value : '' }}</textarea>
                                                </div>
                                            </div>
                                        </div>
                                    </div>
                                </div>
                            </div>

                        </div>
                    </div>
                </div>
                <button type="submit" class="btn btn-primary mt-5">{{ __('frontend.submit') }}</button>
            </form>
        </div>
    </div>
</div>
@endsection

@push('after-styles')
    <!-- DataTables Core and Extensions -->
    <link rel="stylesheet" href="{{ asset('vendor/datatable/datatables.min.css') }}">
@endpush

@push('after-scripts')
    <script src="{{ mix('js/jquery.validate.min.js') }}"></script>
    <script src="{{ mix('js/tinymce/tinymce.min.js') }}"></script>

    <script type="text/javascript">
        tinymce.init({
            selector: "#about_us_textarea"
        });
    </script>
    <script>
        $(document).ready(function() {
            $("#website-setting-form").validate({
                rules: {
                    website_title: {
                        required: true,
                        minlength: 2,
                        maxlength: 50,
                    },
                    'feature_description[]': {
                        required: false,
                        maxlength: 250,
                    },
                },
                messages: {
                    'feature_description[]': {
                        maxlength: "The description must not exceed 250 characters.",
                    },
                },
                errorElement: "span",
                errorClass: "error text-danger",
                highlight: function(element) {
                    $(element).addClass('is-invalid');
                },
                unhighlight: function(element) {
                    $(element).removeClass('is-invalid');
                },
                submitHandler: function(form) {

                    $(form).find('.error').remove();
                    $(form).trigger("submit");
                },
            });
        });

        $(document).ready(function() {
            $('#homepage-setting-tab').tab('show');
            $('#settingsTabs a').on('click', function(e) {
                e.preventDefault();
                $(this).tab('show');
            });
        });
        document.addEventListener("DOMContentLoaded", function() {
            const videoTypeSelect = document.getElementById("video_type");
            const videoUrlInput = document.getElementById("video_url");
            const videoUrlError = document.getElementById("video_url_error");

            function validateVideoUrl() {
                const videoType = videoTypeSelect.value;
                const videoUrl = videoUrlInput.value.trim();
                let isValid = true;

                if (!videoType || !videoUrl) {
                    videoUrlError.style.display = "none";
                    return;
                }

                if (videoType === "mp4") {

                    isValid = /.+\.mp4$/.test(videoUrl);
                } else if (videoType === "youtube") {

                    isValid = /^(https?:\/\/)?(www\.)?(youtube\.com\/|youtu\.be\/)/.test(videoUrl);
                }

                if (isValid) {
                    videoUrlError.style.display = "none";
                    videoUrlInput.classList.remove("is-invalid");
                    videoUrlInput.classList.add("is-valid");
                } else {
                    videoUrlError.style.display = "block";
                    videoUrlInput.classList.add("is-invalid");
                    videoUrlInput.classList.remove("is-valid");
                }
            }

            videoTypeSelect.addEventListener("change", validateVideoUrl);
            videoUrlInput.addEventListener("input", validateVideoUrl);
        });
        document.addEventListener('DOMContentLoaded', function() {

            document.body.addEventListener('click', function(event) {
                const target = event.target;


                $(document).on("click", ".upload-image", function() {
                    const container = $(this).closest(".form-group");
                    const fileInput = container.find(".file-input");
                    if (fileInput.length) {
                        fileInput.trigger("click");
                    }
                });


                if (target.classList.contains('remove-image')) {
                    const container = target.closest('.form-group');
                    const preview = container.querySelector(
                        '.image-preview');
                    const fileInput = container.querySelector(
                        '.file-input');
                    if (preview && fileInput) {
                        const defaultImageUrl = preview.getAttribute(
                            'data-default');
                        preview.src = defaultImageUrl;
                        fileInput.value = '';
                    }
                }
            });


            document.body.addEventListener('change', function(event) {
                const target = event.target;

                if (target.classList.contains('file-input')) {
                    const container = target.closest('.form-group');
                    const preview = container.querySelector(
                        '.image-preview');
                    const file = target.files[0];

                    if (file && preview) {
                        const reader = new FileReader();
                        reader.onload = function(e) {
                            preview.src = e.target.result;
                        };
                        reader.readAsDataURL(file);
                    }
                }
            });
        });

        document.addEventListener('DOMContentLoaded', function() {
            const selectElement = document.getElementById('choose_us_feature_list');
            var baseUrl = "{{ $baseurl }}";
            // Fetch all features from the API
            fetch(baseUrl + "/api/features") // Replace with your actual API endpoint
                .then(response => response.json())
                .then(features => {
                    // Preselected features from Blade
                    const homepages = @json($homepages->where('key', 'choose_us_feature_list')->first());
                    const selectedFeatures = homepages?.value || []; // Default to empty if no value
                    // Populate the select element with options
                    features.forEach(feature => {
                        const option = document.createElement('option');
                        option.value = feature.id;
                        option.textContent = feature.title;

                        const isSelected = selectedFeatures.some(selected => selected.id.toString() ===
                            feature.id.toString());
                        if (isSelected) {
                            option.selected = true;
                        }

                        selectElement.appendChild(option);
                    });

                    // Initialize Select2
                    $('#choose_us_feature_list').select2({
                        placeholder: "{{ __('frontend.select_features') }}",
                        allowClear: true,
                    });
                })
                .catch(error => console.error('Error fetching features:', error));
        });

        document.addEventListener("DOMContentLoaded", function() {
            document.querySelectorAll(".nav-link").forEach(tab => {
                tab.addEventListener("click", function() {
                    let typeValue = this.getAttribute("href").replace("#", "");
                    document.getElementById("selected_type").value = typeValue;
                });
            });
        });
    </script>

    <script>
        $(document).ready(function() {
            // Handle the "+" button click
            $(document).on('click', '.btn-add', function() {
                const newFeatureRow = `
                <div class="col settings-box">
                    <div class="  bg-body rounded mb-4">

                            <input type="hidden" name="website_setting_id" id="feature_id" class="form-control" value="{{ $web_setting->id }}">
                            <div>
                                <div class="form-group">
                                    <label class="form-label" for="feature_title">Title</label>
                                    <input type="hidden" name="feature_id[]" id="feature_id"
                                                                class="form-control"
                                                                value="null">
                                    <input type="text" name="feature_title[]" id="feature_title" class="form-control" placeholder="{{ __('frontend.enter_features') }}">
                                    <span class="error text-danger"></span>
                                </div>
                            </div>

                            <div>
                                <div class="form-group">
                                    <label class="form-label" for="feature_description">Descriptions</label>
                                    <textarea name="feature_description[]" id="" class="form-control" cols="30" rows="3"></textarea>
                                    <span class="error text-danger"></span>
                                </div>
                            </div>
                            <div>
                                <div class="form-group">
                                    <label class="form-label" for="feature_image">Image</label>
                                    <div class="position-relative">
                                        <img class="image-preview img-fluid w-100"
                                        src="{{ asset(product_feature_image()) }}"
                                        data-default="{{ asset(product_feature_image()) }}"
                                            style="height: 150px; object-fit: cover;">
                                        <div class="upload-buttons d-flex justify-content-center gap-3">
                                            <button type="button"
                                                class="btn btn-sm btn-primary upload-image">Upload</button>
                                            <button type="button"
                                                class="btn btn-sm btn-secondary remove-image">Remove</button>
                                            <input type="file" class="file-input form-control"
                                                name="feature_image[]" accept="image/*"
                                                style="display: none;">
                                        </div>
                                    </div>
                                    <span class="error text-danger"></span>
                                </div>
                            </div>
                            <div>
                                <div class="d-flex align-items-center justify-content-end gap-3">
                                    <button type="button" class="btn btn-danger btn-remove"> Remove </button>
                                </div>
                            </div>

                     </div></div>`;
                $('#features-container').prepend(newFeatureRow);
            });

            $(document).on('click', '.btn-remove', function() {
              let $featureContainer = $('#features-container');
              let $featureBoxes = $featureContainer.find('.settings-box');

              if ($featureBoxes.length > 1) {
                  Swal.fire({
                      title: "{{ __('messages.confirm_title') }}",
                      text: "{{ __('messages.confirm_delete_feature') }}",
                      icon: "warning",
                      showCancelButton: true,
                      confirmButtonColor: "#d33",
                      cancelButtonColor: "#3085d6",
                      confirmButtonText: "{{ __('messages.yes_delete') }}",
                      cancelButtonText: "{{ __('messages.cancel') }}",
                  }).then((result) => {
                      if (result.isConfirmed) {
                          $(this).closest('.settings-box').remove();
                          Swal.fire("{{ __('messages.deleted') }}", "{{ __('messages.feature_removed') }}", "success");
                      }
                  });
              } else {
                  Swal.fire("{{ __('messages.warning') }}", "{{ __('messages.at_least_one_feature_required') }}", "error");
              }
          });

        });
    </script>
@endpush
