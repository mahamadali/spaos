@extends('vendorwebsite::layouts.master')
@section('title')
    {{ $expert->full_name }}
@endsection

@section('content')

    <x-breadcrumb />

    <div class="export-section-spacing section-spacing-bottom">
        <div class="container">
            <div class="expert-container">
                <div class="row gy-4">
                    <div class="col-xl-3 col-md-6 col-sm-12">
                        <div class="expert-img">
                            <img src="{{ $expert->profile_image ?? asset('img/vendorwebsite/user_image.png') }}"
                                alt="Expert Image" class="img-fluid">
                        </div>
                    </div>
                    <div class="col-xl-9 col-md-6 col-sm-12">
                        <div>
                            <div class="d-flex justify-content-between align-items-center flex-wrap gap-2 mb-4">
                                <h4 class="m-0">{{ $expert->full_name }}</h4>
                                {{-- <span class="badge bg-secondary rounded-pill py-2 px-4">{{__("vendorwebsite.nails_specialist")}}</span> --}}
                            </div>
                            {{-- <p>{{__("vendorwebsite.experienced_nail_specialist_description")}}</p> --}}
                            <div class="expert-contact-info d-flex align-items-center column-gap-5 row-gap-2 flex-wrap">
                                @if($averageRating > 0)
                                <div>
                                    <span class="font-size-14 mb-1">{{ __('vendorwebsite.rating') }}</span>
                                    <div class="d-flex align-items-center gap-1">
                                        <i class="fa-solid fa-star text-warning"></i>
                                        <h6 class="m-0">{{ $averageRating }}</h6>
                                    </div>
                                </div>
                                @endif
                                <div>
                                    <span class="font-size-14 mb-1">{{ __('vendorwebsite.phone') }}</span>
                                    <h6 class="m-0">{{ $expert->mobile ?? 'N/A' }}</h6>
                                </div>
                                @if($expert->formatted_dob && $expert->formatted_dob !== 'N/A')
                                <div>
                                    <span class="font-size-14 mb-1">{{ __('vendorwebsite.dob') }}</span>
                                    <h6 class="m-0">{{ $expert->formatted_dob }}</h6>
                                </div>
                                @endif
                                <div>
                                    <span class="font-size-14 mb-1">{{ __('vendorwebsite.email') }}</span>
                                    <a href="mailto:{{ $expert->email }}"
                                        class="m-0 h6 heading-color d-block">{{ $expert->email }}</a>
                                </div>
                                {{-- <div>
                                    <span class="font-size-14 mb-1">{{__("vendorwebsite.experience")}}</span>
                                    <h6 class="m-0">3 {{__("vendorwebsite.years")}}</h6>
                                </div> --}}
                            </div>
                            <div class="export-social-btn">
                                <div class="social-icon d-flex align-items-center flex-wrap">
                                    @php $profile = $expert->profile; @endphp
                                    @if ($profile && $profile->facebook_link)
                                        <a href="{{ $profile->facebook_link }}" class="expert-social-icon" target="_blank"
                                            rel="noopener"><i class="ph ph-facebook-logo align-middle"></i></a>
                                    @endif
                                    @if ($profile && $profile->instagram_link)
                                        <a href="{{ $profile->instagram_link }}" class="expert-social-icon" target="_blank"
                                            rel="noopener"><i class="ph ph-instagram-logo align-middle"></i></a>
                                    @endif
                                    @if ($profile && $profile->twitter_link)
                                        <a href="{{ $profile->twitter_link }}" class="expert-social-icon" target="_blank"
                                            rel="noopener"><i class="ph ph-x-logo align-middle"></i></a>
                                    @endif
                                    @if ($profile && $profile->dribbble_link)
                                        <a href="{{ $profile->dribbble_link }}" class="expert-social-icon" target="_blank"
                                            rel="noopener"><i class="ph ph-dribbble-logo align-middle"></i></a>
                                    @endif
                                </div>
                            </div>
                            <div class="export-box-content">
                                <div class="row gy-4">
                                    <div class="col-lg-4">
                                        <div
                                            class="export-box-info d-flex align-items-center column-gap-3 row-gap-2 flex-sm-nowrap flex-wrap">
                                            <i class="ph ph-calendar-check h2 text-primary"></i>
                                            <div>
                                                <h5 class="fw-semibold">{{ $totalBookings ?? 0 }}+</h5>
                                                <span
                                                    class="font-size-14 fw-semibold">{{ __('vendorwebsite.total_bookings') }}</span>
                                            </div>
                                        </div>
                                    </div>
                                    <div class="col-lg-4">
                                        <div
                                            class="export-box-info d-flex align-items-center column-gap-3 row-gap-2 flex-sm-nowrap flex-wrap">
                                            <i class="ph ph-hair-dryer h2 text-primary"></i>
                                            <div>
                                                <h5 class="fw-semibold">{{ $totalServices ?? 0 }}+</h5>
                                                <span
                                                    class="font-size-14 fw-semibold">{{ __('vendorwebsite.top_service') }}</span>
                                            </div>
                                        </div>
                                    </div>
                                    <div class="col-lg-4">
                                        <div
                                            class="export-box-info d-flex align-items-center column-gap-3 row-gap-2 flex-sm-nowrap flex-wrap">
                                            <i class="ph ph-sparkle h2 text-primary"></i>
                                            <div>
                                                <h5 class="fw-semibold">{{ $customerSatisfaction }}%</h5>
                                                <span
                                                    class="font-size-14 fw-semibold">{{ __('vendorwebsite.customer_satisfaction') }}</span>
                                            </div>
                                        </div>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
            <div class="experts-review-box">
                <div class="d-flex align-items-center justify-content-between gap-2 flex-wrap mb-4">
                    <h5 class="m-0">{{ __('vendorwebsite.reviews') }}</h5>
                    {{-- Changed condition from 6 to 5 --}}
                    @if ($totalRatings > 5)
                        <a href="{{ route('expert.reviews', $expert->id) }}"
                            class="btn btn-secondary">{{ __('vendorwebsite.view_all') }}</a>
                    @endif

                </div>
                <ul class="list-unstyled mb-0 mt-5">
                    @forelse($ratings as $rating)
                        <li class="expert-review-card">
                            <div class="d-flex column-gap-4 row-gap-2 flex-sm-row flex-column mb-4">
                                <!-- Profile Image Column -->
                                <div class="avatar-wrapper">
                                    <img src="{{ $rating->user->profile_image ?? asset('img/vendorwebsite/export-image.jpg') }}"
                                        alt="review img" class="expert-review-img rounded-pill">
                                </div>
                                <!-- Info Column -->
                                <div class="flex-grow-1">
                                    <div class="d-flex align-items-start justify-content-between gap-1 flex-wrap">
                                        <div>
                                            <div>
                                                <h5 class="mb-3">{{ $rating->user->full_name ?? 'Anonymous' }}</h5>
                                                <div class="d-flex align-items-center gap-1">
                                                    @for ($i = 1; $i <= 5; $i++)
                                                        @if ($i <= $rating->rating)
                                                            <i class="ph-fill ph-star text-warning"></i>
                                                        @else
                                                            <i class="ph ph-star text-warning"></i>
                                                        @endif
                                                    @endfor
                                                </div>
                                            </div>
                                        </div>
                                        <span
                                            class="fw-medium font-size-14">{{ formatVendorDateOrTime($rating->created_at, 'date') }}</span>
                                    </div>
                                </div>
                            </div>
                            <span>{{ $rating->review_msg ?? 'No review message provided.' }}</span>
                        </li>
                    @empty
                        <li class="expert-review-card">
                            <div class="text-center py-4">
                                <p class="mb-0 text-body">{{ __('vendorwebsite.no_reviews_available_yet') }}</p>
                            </div>
                        </li>
                    @endforelse
                </ul>
            </div>
        </div>
    </div>

@endsection

@push('scripts')
    {{-- (Removed debug script for DOB) --}}
@endpush
