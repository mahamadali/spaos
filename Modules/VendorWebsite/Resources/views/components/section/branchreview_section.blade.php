@if(isset($reviews) && $reviews->count() > 0)
<ul class="list-unstyled mb-0">
    @foreach($reviews as $review)
    <li class="branch-review-card">
        <div class="d-flex gap-3 flex-sm-row flex-column mb-2">
            <!-- Profile Image Column -->
            <div class="avatar-wrapper">
                <img src="{{ $review->user->profile_image ?? asset('img/vendorwebsite/user.png') }}" alt="review img" class="branch-review-img rounded-pill">
            </div>
            <!-- Info Column -->
            <div class="flex-grow-1">
                <div class="d-flex align-items-start justify-content-between gap-1 flex-wrap">
                    <div>
                        <span class="badge bg-gray-900 text-secondary font-size-14 mb-2">
                            <i class="ph-fill ph-star text-warning"></i> {{ $review->rating ?? '0' }}
                        </span>
                    </div>
                    <span class="fw-medium font-size-14">{{ $review->created_at ? $review->created_at->format('d/m/Y') : '-' }}</span>
                </div>
                <div class="d-flex align-items-center gap-2">
                    <h5 class="m-0">{{ $review->user->full_name ?? 'Anonymous' }}</h5>
                    <i class="ph-fill ph-seal-check text-success font-size-18"></i>
                </div>
            </div>
        </div>
        <span class="font-size-14">{{ $review->review_msg ?? 'No review message provided.' }}</span>
    </li>
    @if(!$loop->last)
    <hr class="my-4">
    @endif
    @endforeach
</ul>
@endif
