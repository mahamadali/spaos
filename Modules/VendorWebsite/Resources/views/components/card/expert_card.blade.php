@props(['expert'])

<div class="export-card text-center">
    <div class="export-image position-relative">
        <img src="{{ $expert->profile_image ?? asset('img/vendorwebsite/export-image.jpg') }}"
            alt="{{ $expert->full_name }}" class="avatar-128 rounded-circle object-fit-cover">
    </div>
    <div class="export-info mt-3">
        @php
            $rating = isset($expert->avg_rating) ? (float) $expert->avg_rating : 0;
            $filled = max(0, min(5, (int) round($rating)));
        @endphp
        <div class="d-flex align-items-center justify-content-center gap-lg-3 gap-2 mb-1">
            <div class="d-inline-flex align-items-center gap-1">
                @for ($i = 1; $i <= 5; $i++)
                    @if ($i <= $filled)
                        <i class="ph-fill ph-star text-warning"></i>
                    @else
                        <i class="ph ph-star text-body"></i>
                    @endif
                @endfor
            </div>
            <span class="fw-semibold heading-color font-size-14">{{ number_format($rating, 1) }}</span>
        </div>
        <h5 class="mb-1"><a class="title-export"
                href="{{ route('expert-detail', $expert->id) }}">{{ $expert->full_name }}</a></h5>
        <p class="font-size-14 mb-0">{{ $expert->specialty ?? 'Makeup specialist' }}</p>
    </div>
</div>
