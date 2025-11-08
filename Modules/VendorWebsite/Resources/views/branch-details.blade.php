@extends('vendorwebsite::layouts.master')

@section('content')

    <x-breadcrumb title="Branch Detail" />

    <div class="section-spacing-inner-pages">
        <div class="container">
            <div class="row gy-4">
                <div class="col-xl-9 col-lg-8">
                    <div class="d-flex align-items-center gap-4 mb-3 pb-1">
                        <h4 class="mb-0">{{ $branch->name }}</h4>
                        @if ($branch->branch_for)
                            <span
                                class="badge bg-purple text-body border rounded-pill text-uppercase">{{ $branch->branch_for }}</span>
                        @endif
                    </div>
                    <div class="d-flex align-items-center gap-2">
                        <i class="ph-fill ph-star text-warning"></i>
                        <span>
                            <span class="fw-medium heading-color">{{ number_format($branch->averageRating ?? 0, 1) }}</span>
                            <span>(Based on {{ $branch->totalReviews ?? '0' }} reviews)</span>
                        </span>
                    </div>

                    <ul class="nav nav-pills row-gap-2 column-gap-3 branch-tab-content mt-5 m-0" role="tablist">
                        @if ($branch->description && trim($branch->description) !== '')
                            <li class="nav-item" role="presentation">
                                <a class="nav-link active" data-bs-toggle="tab" href="#about-us-branch" aria-selected="true"
                                    role="tab">
                                    <span>About Us</span></a>
                            </li>
                        @endif
                        <li class="nav-item" role="presentation">
                            <a class="nav-link" data-bs-toggle="tab" href="#service-branch" aria-selected="false"
                                role="tab"
                                {{ !$branch->description || trim($branch->description) === '' ? '' : 'tabindex="-1"' }}><span>Services</span></a>
                        </li>
                        <li class="nav-item" role="presentation">
                            <a class="nav-link" data-bs-toggle="tab" href="#review-branch" aria-selected="false"
                                role="tab" tabindex="-1"><span>Reviews</span></a>
                        </li>
                        <li class="nav-item" role="presentation">
                            <a class="nav-link" data-bs-toggle="tab" href="#staff-branch" aria-selected="false"
                                role="tab" tabindex="-1"><span>Staff</span></a>
                        </li>
                        <li class="nav-item" role="presentation">
                            <a class="nav-link" data-bs-toggle="tab" href="#gallery-branch" aria-selected="false"
                                role="tab" tabindex="-1"><span>Gallery</span></a>
                        </li>
                    </ul>
                    <div class="tab-content mt-5">
                        @if ($branch->description && trim($branch->description) !== '')
                            <div class="tab-pane p-0 fade" id="about-us-branch" role="tabpanel">
                                <div class="about-us-section">
                                    <h4>About Us</h4>
                                    <p>{{ $branch->description }}</p>
                                </div>
                            </div>
                        @endif
                        <div class="tab-pane p-0 fade" id="service-branch" role="tabpanel">
                            @if ($services && $services->count() > 0)
                                @foreach ($services as $service)
                                    <div class="col">
                                        <x-service_card :service="$service" />
                                    </div>
                                @endforeach
                                <div class="d-flex align-items-center justify-content-center mt-5 gap-3">
                                    @if ($services->previousPageUrl() != null)
                                        <a href="{{ $services->previousPageUrl() }}#service-branch"
                                            class="btn btn-secondary">
                                            Previous Page
                                        </a>
                                    @endif
                                    @if ($services->hasMorePages())
                                        <a href="{{ $services->nextPageUrl() }}#service-branch"
                                            class="btn btn-secondary">Load More</a>
                                    @endif

                                </div>
                            @else
                                <div class="col-12 text-center py-5">
                                    <p class="text-muted">{{ __('vendorwebsite.no_services_available') }}</p>
                                </div>
                            @endif
                        </div>
                        <div class="tab-pane p-0 fade" id="review-branch" role="tabpanel">
                            @if ($branch->branchRatings && $branch->branchRatings->count() > 0)
                                <x-branchreview_section :reviews="$branch->branchRatings" />
                                <div class="d-flex align-items-center justify-content-center mt-5 gap-3">
                                    @if ($branch->branchRatings->previousPageUrl())
                                        <a href="{{ $branch->branchRatings->previousPageUrl() }}#review-branch"
                                            class="btn btn-secondary">
                                            Previous Page
                                        </a>
                                    @endif

                                    @if ($branch->branchRatings->hasMorePages())
                                        <a href="{{ $branch->branchRatings->nextPageUrl() }}#review-branch"
                                            class="btn btn-secondary">
                                            Load More
                                        </a>
                                    @endif
                                </div>
                            @else
                                <div class="text-center py-5">
                                    <div class="mb-3">
                                        <i class="ph ph-star text-muted" style="font-size: 3rem;"></i>
                                    </div>
                                    <h5 class="text-muted">{{ __('vendorwebsite.no_reviews_found') }}</h5>

                                </div>
                            @endif
                        </div>
                        <div class="tab-pane p-0 fade" id="staff-branch" role="tabpanel">
                            <div class="row row-cols-1 row-cols-md-2 row-cols-lg-3 row-cols-xl-4 gy-4">
                                @php
                                    $activeStaff = $branch->branchEmployee
                                        ? $branch->branchEmployee->filter(function ($employee) {
                                            return $employee->employee &&
                                                $employee->employee->status == 1 &&
                                                $employee->employee->is_banned == 0;
                                        })
                                        : collect();
                                    $activeStaff = $activeStaff->sortByDesc(function ($branchEmployee) {
                                        $employee = $branchEmployee->employee;
                                        if ($employee && $employee->rating && $employee->rating->count() > 0) {
                                            return $employee->rating->avg('rating');
                                        }
                                        return 0;
                                    });
                                @endphp

                                @if ($activeStaff && $activeStaff->count() > 0)
                                    @foreach ($activeStaff as $employee)
                                        @php
                                            $averageRating = $employee->employee->rating
                                                ? $employee->employee->rating->avg('rating')
                                                : 0;
                                        @endphp
                                        <div class="col">
                                            <div class="text-center branch-staff-card">
                                                <div class="avatar-wrapper">
                                                    <img src="{{ $employee->employee->profile_image ?? asset('img/vendorwebsite/user.png') }}"
                                                        alt="staff card" class="branch-staff-img">
                                                </div>
                                                <div class="staff-info">
                                                    <h5>{{ $employee->employee->full_name }}</h5>
                                                    @if ($employee->employee->profile && $employee->employee->profile->expert)
                                                        <span
                                                            class="font-size-14">{{ $employee->employee->profile->expert }}</span>
                                                    @endif
                                                </div>
                                                <div class="staff-ratting-info">
                                                    <span class="badge bg-white text-secondary font-size-14"><i
                                                            class="ph-fill ph-star text-warning"></i>
                                                        {{ number_format($averageRating, 1) }}</span>
                                                </div>
                                            </div>
                                        </div>
                                    @endforeach
                                @else
                                    <div class="col-12 text-center py-5">
                                        <p class="text-muted">{{ __('vendorwebsite.no_staff_available') }}</p>
                                    </div>
                                @endif
                            </div>
                        </div>
                        <div class="tab-pane p-0 fade" id="gallery-branch" role="tabpanel">
                            @if ($branch->gallerys && $branch->gallerys->count() > 0)
                                <x-branchgallery_section :gallery="$branch->gallerys" />
                            @else
                                <div class="text-center py-5">
                                    <div class="mb-3">
                                        <i class="ph ph-image text-muted" style="font-size: 3rem;"></i>
                                    </div>
                                    <h5 class="text-muted">{{ __('vendorwebsite.no_gallery_images_found') }}</h5>

                                </div>
                            @endif
                        </div>
                    </div>

                </div>
                <div class="col-xl-3 col-lg-4 sticky">
                    <div class="branch-details-box rounded">
                        <div>
                            {{-- <img src="{{ $branch->feature_image ?? asset('img/vendorwebsite/branch-image.jpg') }}"
                                alt="branch-detail" class="w-100 branch-details-img rounded-top position-relative"> --}}
                            <img src="{{ $branch->feature_image ?? asset('img/vendorwebsite/branch-image.jpg') }}"
                                alt="branch-detail" class="w-100 branch-details-img rounded-top position-relative"
                                style="object-fit: contain; height: 200px;">

                            <div class="d-flex position-absolute gap-3 align-content-center branch-meta">
                                @php
                                    $timezone = setting('default_time_zone') ?? 'UTC';
                                    $today = \Carbon\Carbon::now($timezone)->format('l');
                                    $now = \Carbon\Carbon::now($timezone);
                                    $hours = \Modules\BussinessHour\Models\BussinessHour::where(
                                        'branch_id',
                                        $branch->id,
                                    )
                                        ->whereRaw('LOWER(day) = ?', [strtolower($today)])
                                        ->first();
                                    $isOpen = false;
                                    if ($hours && $hours->is_holiday != 1 && $hours->start_time && $hours->end_time) {
                                        $start = \Carbon\Carbon::parse($hours->start_time, $timezone);
                                        $end = \Carbon\Carbon::parse($hours->end_time, $timezone);
                                        $isOpen = $now->between($start, $end);
                                        if ($isOpen && !empty($hours->breaks)) {
                                            $breaks = is_array($hours->breaks)
                                                ? $hours->breaks
                                                : json_decode($hours->breaks, true);
                                            foreach ($breaks as $break) {
                                                if (!empty($break['start']) && !empty($break['end'])) {
                                                    $breakStart = \Carbon\Carbon::parse($break['start'], $timezone);
                                                    $breakEnd = \Carbon\Carbon::parse($break['end'], $timezone);
                                                    if ($now->between($breakStart, $breakEnd)) {
                                                        $isOpen = false;
                                                        break;
                                                    }
                                                }
                                            }
                                        }
                                    }
                                @endphp
                                <span class="badge {{ $isOpen ? 'bg-success' : 'bg-danger' }} rounded-pill">
                                    {{ $isOpen ? __('vendorwebsite.open') : __('vendorwebsite.closed') }}
                                </span>

                            </div>
                        </div>
                        <div class="branch-details-content">
                            <div class="d-flex align-items-center gap-3 pb-3 mb-3 border-bottom">
                                <i class="ph ph-map-pin"></i>
                                <span class="font-size-14 heading-color">
                                    {{ $branch->address->address_line_1 ?? '' }}
                                    @if ($branch->address->city_data)
                                        , {{ $branch->address->city_data->name }}
                                    @endif
                                    @if ($branch->address->state_data)
                                        , {{ $branch->address->state_data->name }}
                                    @endif
                                    @if ($branch->address->country_data)
                                        , {{ $branch->address->country_data->name }}
                                    @endif
                                </span>
                            </div>
                            <div class="d-flex align-items-center gap-3 pb-3 mb-3 border-bottom">
                                <i class="ph ph-phone"></i>
                                <span class="font-size-14 heading-color">{{ $branch->contact_number ?? '' }}</span>
                            </div>
                            <div class="d-flex align-items-center gap-3 pb-3 mb-3 border-bottom">
                                <i class="ph ph-envelope-simple"></i>
                                <span class="font-size-14 heading-color">{{ $branch->contact_email ?? '' }}</span>
                            </div>
                            <div class="mb-5">
                                @if ($branch->businessHours && $branch->businessHours->count() > 0)
                                    @foreach ($branch->businessHours as $hour)
                                        <div class="d-flex align-items-center gap-2 mb-1">
                                            <i class="ph ph-clock fs-5"></i>
                                            <span class="font-size-14 heading-color text-capitalize">
                                                {{ strtolower($hour->day) }}:
                                                @if ($hour->is_holiday)
                                                    <strong>Holiday</strong>
                                                @else
                                                    {{ date('h:i A', strtotime($hour->start_time)) }} -
                                                    {{ date('h:i A', strtotime($hour->end_time)) }}
                                                @endif
                                            </span>
                                        </div>
                                        @if (!$hour->is_holiday && $hour->breaks)
                                            @php
                                                $breaks = is_string($hour->breaks)
                                                    ? json_decode($hour->breaks, true)
                                                    : $hour->breaks;
                                            @endphp

                                            @if (is_array($breaks) && count($breaks) > 0)
                                                @foreach ($breaks as $break)
                                                    <div class="d-flex align-items-center gap-2 mb-1 ms-4">
                                                        <i class="ph ph-hourglass fs-5"></i>
                                                        <span class="font-size-13 text-muted">
                                                            Break: {{ date('h:i A', strtotime($break['start_break'])) }} -
                                                            {{ date('h:i A', strtotime($break['end_break'])) }}
                                                        </span>
                                                    </div>
                                                @endforeach
                                            @endif
                                        @endif
                                    @endforeach
                                @else
                                    <div class="d-flex align-items-center gap-2 mb-1">
                                        <i class="ph ph-clock fs-5"></i>
                                        <span
                                            class="font-size-14 heading-color">{{ __('vendorwebsite.business_hours_not_available') }}</span>
                                    </div>
                                @endif
                            </div>
                            <!-- <button class="btn btn-secondary w-100">Share</button> -->
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>

@endsection
<script>
    document.addEventListener('DOMContentLoaded', function() {
        const hash = window.location.hash;
        console.log('URL Hash:', hash);

        let defaultTab =
            '{{ $branch->description && trim($branch->description) !== '' ? '#about-us-branch' : '#service-branch' }}';

        const targetHash = hash && document.querySelector(hash) ? hash : defaultTab;

        const targetNav = document.querySelector(`.nav-link[href="${targetHash}"]`);
        const targetPane = document.querySelector(targetHash);

        if (targetNav && targetPane) {
            // Remove all active classes first
            document.querySelectorAll('.nav-link').forEach(link => link.classList.remove('active'));
            document.querySelectorAll('.tab-pane').forEach(pane => pane.classList.remove('show', 'active'));

            // Activate target tab
            const bootstrapTab = new bootstrap.Tab(targetNav);
            bootstrapTab.show();

            console.log('Activated tab:', targetHash);
        } else {
            console.warn('Tab not found:', targetHash);
        }
    });
</script>

<div class="onclick-page-redirect bg-orange p-3 d-none" id="service-action-bar">
    <div class="container">
        <div class="d-flex justify-content-end align-items-center">
            <form id="service-selection-form" action="{{ route('choose-expert') }}" method="POST"
                style="display:inline;">
                @csrf
                <input type="hidden" id="selected-services" name="selected_services">
                <button type="submit" class="btn btn-secondary px-5" id="next-button"
                    disabled>{{ __('vendorwebsite.next') }}</button>
            </form>
        </div>
    </div>
    @push('styles')
        <style>
            .service-card.selected {
                border: 2px solid #0d6efd;
                background-color: rgba(13, 110, 253, .05);
            }
        </style>
    @endpush
</div>

@push('scripts')
    <script>
        document.addEventListener('DOMContentLoaded', function() {
            const nextButton = document.getElementById('next-button');
            const hiddenInput = document.getElementById('selected-services');
            const actionBar = document.getElementById('service-action-bar');
            const form = document.getElementById('service-selection-form');
            let autoSubmitted = false;

            function updateSelection(event) {
                const changedCheckbox = event ? event.target : null;
                if (changedCheckbox) {
                    const card = changedCheckbox.closest('.service-card');
                    if (card) card.classList.toggle('selected', changedCheckbox.checked);
                }
                const checkboxes = document.querySelectorAll('.service-checkbox:checked');
                const selectedIds = Array.from(checkboxes).map(cb => cb.value).filter(Boolean);
                if (hiddenInput) hiddenInput.value = selectedIds.join(',');
                if (actionBar) {
                    if (selectedIds.length > 0) {
                        actionBar.classList.remove('d-none');
                        if (nextButton) nextButton.disabled = false;
                        // Auto-submit to proceed into booking flow
                        if (!autoSubmitted && form) {
                            autoSubmitted = true;
                            form.submit();
                        }
                    } else {
                        if (nextButton) nextButton.disabled = true;
                        actionBar.classList.add('d-none');
                        autoSubmitted = false;
                    }
                }
            }

            document.addEventListener('change', function(event) {
                if (event.target && event.target.matches('.service-checkbox')) {
                    updateSelection(event);
                }
            });

            // Allow clicking the entire card to toggle
            document.addEventListener('click', function(event) {
                const serviceCard = event.target.closest('.service-card');
                if (serviceCard && !event.target.matches(
                        '.service-checkbox, .addon-checkbox, .service-card-addons-collapse, .service-card-addons-collapse *'
                    )) {
                    const checkbox = serviceCard.querySelector('.service-checkbox');
                    if (checkbox) {
                        checkbox.checked = !checkbox.checked;
                        updateSelection({
                            target: checkbox
                        });
                    }
                }
            });

            updateSelection();
        });
    </script>
@endpush
