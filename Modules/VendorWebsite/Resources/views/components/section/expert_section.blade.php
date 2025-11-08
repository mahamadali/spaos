@props(['experts', 'expert_ids'])
<div class="export-section section-spacing-bottom">
    <div class="container">
        <div class="section-title text-center">
            <span
                class="decorator-title decorator-font text-primary text-uppercase text-decoration-underline">{{ __('vendorwebsite.top_experts') }}</span>
            <h4 class="title">{{ __('vendorwebsite.meet_our_experts') }}</h4>
        </div>
        <div class="row row-cols-2 row-cols-sm-2 row-cols-lg-3 row-cols-xl-5 gy-4" id="experts-section-list">




            @foreach ($experts->whereIn('id', $expert_ids)->take(5) as $expert)
                <div class="col">
                    <div class="export-card text-center">
                        <div class="export-image position-relative">
                            <img src="{{ $expert->profile_image ?? asset('img/vendorwebsite/default-expert.png') }}"
                                alt="{{ $expert->full_name }}" class="avatar-128 rounded-circle object-fix-cover">
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
                                <span
                                    class="fw-semibold heading-color font-size-14">{{ number_format($rating, 1) }}</span>
                            </div>
                            <h5 class="mb-1"><a class="title-export"
                                    href="{{ route('expert-detail', $expert->id) }}">{{ $expert->full_name }}</a></h5>
                            <p class="font-size-14 mb-0">
                                {{ $expert->speciality ?? ($expert->profile->expert ?? 'Makeup specialist') }}</p>
                        </div>
                    </div>
                </div>
            @endforeach
            @if ($experts->count() == 0)
                <div class="col-12 text-center py-5">
                    <p class="text-body">No experts avaible</p>
                </div>
            @endif

            <div class="col-12 text-center py-5">
                <p class="text-body"></p>
            </div>

            <div id="shimmer-loader" class="d-flex gap-3 flex-wrap p-4 shimmer-loader d-none w-100">
                @for ($i = 0; $i < 5; $i++)
                    @include('vendorwebsite::components.card.shimmer_employee_card')
                @endfor
            </div>
        </div>



        {{-- Experts will be dynamically injected here --}}

        <div class="text-center export-button">
            <a href="{{ route('expert') }}" class="btn btn-secondary">{{ __('vendorwebsite.view_all') }}</a>
        </div>
    </div>
</div>

<script>
    document.addEventListener('DOMContentLoaded', function() {
        // Section 7 enabled and selected experts from backend
        window.section7Enabled = true;
        window.section7Experts = @json($experts->values());



        function starIcons(r) {
            const filled = Math.max(0, Math.min(5, Math.round(Number(r) || 0)))
            let html = ''
            for (let i = 1; i <= 5; i++) {
                html += i <= filled ? '<i class="ph-fill ph-star text-warning"></i>' :
                    '<i class="ph ph-star text-body"></i>'
            }
            return html
        }

        function renderExperts(experts) {
            const expertsList = document.getElementById('experts-section-list');
            if (!experts || experts.length === 0) {
                expertsList.innerHTML =
                    '<div class="col-12 text-center py-5"><p class="text-body">No experts selected</p></div>';
                return;
            }
            let expertsHtml = '';
            experts.slice(0, 5).forEach(expert => {



                let rating = (typeof expert.rating !== 'undefined' && expert.rating !== null) ?
                    expert.rating :
                    (typeof expert.avg_rating !== 'undefined' && expert.avg_rating !== null) ?
                    expert.avg_rating :
                    '4.2';
                expertsHtml += `
                <div class="col">
                    <div class="export-card text-center">
                        <div class="export-image position-relative">
                            <img class="avatar-128 rounded-circle object-fit-cover" src="${expert.profile_image || expert.image_path || '{{ asset('img/vendorwebsite/default-expert.png') }}'}" alt="${expert.full_name || expert.name}">
                        </div>
                        <div class="export-info mt-3">
                            <div class="d-flex align-items-center justify-content-center gap-lg-3 gap-2 mb-1">
                                <div class="d-inline-flex align-items-center gap-1">${starIcons(rating)}</div>
                                    <span class="fw-semibold heading-color font-size-14">
                                    ${Number(rating).toFixed(1)}
                                </span>
                            </div>
                            <h5 class="mb-1"><a class="title-export" href="{{ route('expert-detail', '') }}/${expert.id}">${expert.full_name || expert.name}</a></h5>
                            <p class="font-size-14 mb-0">${expert.speciality || (expert.profile ? expert.profile.expert : '')}</p>
                        </div>
                    </div>
                </div>
            `;
            });
            expertsList.innerHTML = expertsHtml;
        }

        // Function to load experts based on selected branch
        function loadExpertsForSection(branchId) {
            const expertsList = document.getElementById('experts-section-list');
            const shimmerLoader = document.getElementById('shimmer-loader');




            // Show shimmer and clear experts
            shimmerLoader.classList.remove('d-none');

            // Remove old expert cards (but keep shimmer)
            [...expertsList.querySelectorAll('.expert-card')].forEach(card => card.remove());

            if (!branchId) {
                if (window.section7Enabled && window.section7Experts?.length > 0) {
                    setTimeout(() => {
                        shimmerLoader.classList.add('d-none');
                        renderExperts(window.section7Experts);
                    }, 500); // Optional loading delay
                } else {
                    shimmerLoader.classList.add('d-none');
                    expertsList.innerHTML +=
                        '<div class="col-12 text-center py-5"><p class="text-body">No experts selected</p></div>';
                }
                return;
            }

            // Fetch experts for selected branch
            fetch(`{{ route('experts.by-branch') }}?branch_id=${branchId}`)
                .then(response => response.json())
                .then(data => {

                    expertsList.innerHTML = '';


                    shimmerLoader.classList.add('d-none');
                    if (data.success && data.experts?.length > 0) {
                        renderExperts(data.experts);
                    } else {
                        expertsList.innerHTML +=
                            '<div class="col-12 text-center py-5"><p class="text-body">No experts available for this branch</p></div>';
                        document.querySelector('.export-button').classList.add('d-none');
                    }
                })
                .catch(error => {
                    shimmerLoader.classList.add('d-none');
                    expertsList.innerHTML +=
                        '<div class="col-12 text-center py-5"><p class="text-danger">Error loading experts</p></div>';
                    document.querySelector('.export-button').classList.add('d-none');
                });
        }


        // Listen for branch selection changes
        document.addEventListener('branchSelected', function(event) {
            const branchId = event.detail.branchId;
            loadExpertsForSection(branchId);
        });

        // Also check if there's already a selected branch in session
        const selectedBranchId = '{{ session('selected_branch_id') }}';
        if (selectedBranchId) {
            loadExpertsForSection(selectedBranchId);
        } else {
            // Always show section 7 experts if no branch is selected
            if (window.section7Enabled && window.section7Experts && window.section7Experts.length > 0) {
                renderExperts(window.section7Experts);
            } else {
                document.getElementById('experts-section-list').innerHTML =
                    '<div class="col-12 text-center py-5"><p class="text-body">No experts selected</p></div>';
            }
        }
    });
</script>
