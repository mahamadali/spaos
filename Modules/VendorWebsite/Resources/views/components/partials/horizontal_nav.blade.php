<nav id="navbar_main" class="offcanvas mobile-offcanvas nav navbar navbar-expand-xl hover-nav horizontal-nav py-xl-0">
    <div class="container-fluid p-0">
        <div class="offcanvas-header">
            <div class="">
                <x-logo />
            </div>
            <button type="button" class="btn-close p-0" data-bs-dismiss="offcanvas" aria-label="Close"></button>
        </div>
        <!-- Select Branch -->
        @php
            $branches = get_active_branch();
            $selectedBranchId = session('selected_branch_id');

        @endphp


        @if (
            $branches->count() > 0 &&
                isset($headerMenuSettingDecoded['selectbranch']) &&
                $headerMenuSettingDecoded['selectbranch'] == 1)
            <div class="select-branch">
                <a class="branch-panel d-flex align-items-center justify-content-between gap-lg-3 gap-2"
                    data-bs-toggle="dropdown" href="#" aria-expanded="false">
                    <div>
                        <i class="ph ph-git-branch"></i>
                        @if ($selectedBranchId)
                            @php
                                $selectedBranch = $branches->where('id', $selectedBranchId)->first();
                            @endphp
                            <span class="font-size-14">
                                {{ $selectedBranch ? $selectedBranch->name : __('vendorwebsite.select_branch') }}
                            </span>
                        @else
                            <span class="font-size-14">{{ __('vendorwebsite.select_branch') }}</span>
                        @endif
                    </div>
                    <i class="ph ph-caret-down branch-icons"></i>
                </a>
                <div class="dropdown-menu dropdown-menu-start dropdown-branch-panel shadow">
                    @php $branchArray = $branches->toArray(request()); @endphp
                    @foreach (array_slice($branchArray, 0, 3) as $branch)
                        <div class="branch-panel-card d-flex flex-column flex-md-row align-items-stretch position-relative"
                            @php
$timezone = getVendorSetting('default_time_zone') ?? 'UTC'; // Change this to your local timezone if needed
                $today = \Carbon\Carbon::now($timezone)->format('l');
                $now = \Carbon\Carbon::now($timezone);

                $hours = \Modules\BussinessHour\Models\BussinessHour::where('branch_id', $branch['id'])
                    ->whereRaw('LOWER(day) = ?', [strtolower($today)])
                    ->first();
                $isOpen = false;
                if ($hours && $hours->is_holiday != 1 && $hours->start_time && $hours->end_time) {
                    $start = \Carbon\Carbon::parse($hours->start_time, $timezone);
                    $end = \Carbon\Carbon::parse($hours->end_time, $timezone);
                    $isOpen = $now->between($start, $end);
                    // Check breaks
                    if ($isOpen && !empty($hours->breaks)) {
                        $breaks = is_array($hours->breaks) ? $hours->breaks : json_decode($hours->breaks, true);
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
                } @endphp
                            <div
                            class="branch-panel-card d-flex flex-row align-items-stretch position-relative cursor-pointer"
                            data-branch-id="{{ $branch['id'] }}">
                            @if ($branch['id'] == $selectedBranchId)
                                <i class="ph-fill ph-check-circle font-size-21-3 text-primary branch-panel-active"></i>
                            @else
                                <i class="ph-fill ph-check-circle font-size-21-3 text-primary branch-panel-active"
                                    style="display: none;"></i>
                            @endif
                            <div class="position-relative cursor-pointer" onclick="selectBranch('{{ $branch['id'] }}')">
                                {{-- <span
                                    class="badge {{ $isOpen ? 'bg-success' : 'bg-danger' }} text-white">{{ $isOpen ? 'Open' : 'Closed' }}</span> --}}
                                <span
                                    class="badge {{ $isOpen ? 'bg-success' : 'bg-danger' }} text-white">
                                    {{ $isOpen ? __('vendorwebsite.open') : __('vendorwebsite.closed') }}
                                </span>

                                {{-- <img src="{{ $branch['branch_image'] }}" class="card-img rounded-start"
                                    alt="Salon Image"> --}}
                                    <img src="{{ $branch['branch_image'] ?? asset('img/default.png') }}" class="card-img rounded-start" alt="Salon Image">

                            </div>
                            <div class="panel-desc">
                                <div class="d-flex flex-wrap gap-2 gap-md-3 gap-lg-5 mb-2">
                                    <h6 class="mb-0">{{ $branch['name'] }}</h6>
                                    <div>
                                        <span
                                            class="badge bg-purple text-body border rounded-pill">{{ ucfirst($branch['branch_for']) }}</span>
                                    </div>
                                </div>
                                <ul class="list-inline m-0 p-0">
                                    <li class="mb-2 small"><i class="ph ph-map-pin"></i>{{ $branch['address_line_1'] }}

                                    </li>
                                    <li class="mb-2 small">{!! get_distance_from_location($branch['latitude'], $branch['longitude'], 'K') !!}</li>
                                    <li class="text-warning fw-semibold small">
                                        <span class="text-warning">★</span><span class="heading-color">
                                            {{ $branch['rating_star'] }} </span> <span class="text-body">(Based on
                                            {{ $branch['total_review'] }} reviews)</span>
                                    </li>
                                </ul>
                            </div>
                        </div>
                    @endforeach
                    <div class="dropdown-divider"></div>
                    <a href="{{ route('branch') }}"
                        class="dropdown-item text-primary fw-bold">{{ __('vendorwebsite.view_all_branches') }}</a>
                </div>
            </div>
        @endif
        <!-- menu -->
        <ul class="navbar-nav iq-nav-menu  list-unstyled" id="header-menu">
            <li class="nav-item">
                <a class="nav-link" href="{{ route('vendor.index') }}">
                    <span class="item-name">{{ __('vendorwebsite.home') }}</span>
                </a>
            </li>
            @if (isset($headerMenuSettingDecoded['category']) && $headerMenuSettingDecoded['category'] == 1)
                <li class="nav-item">
                    <a class="nav-link" href="{{ route('category') }}">
                        <span class="item-name">{{ __('vendorwebsite.category') }}</span>
                    </a>
                </li>
            @endif
            @if (isset($headerMenuSettingDecoded['service']) && $headerMenuSettingDecoded['service'] == 1)
                <li class="nav-item">
                    <a class="nav-link" href="{{ route('service') }}">
                        <span class="item-name">{{ __('vendorwebsite.service') }}</span>
                    </a>
                </li>
            @endif
            @if (isset($headerMenuSettingDecoded['shop']) && $headerMenuSettingDecoded['shop'] == 1)
                <li class="nav-item">
                    <a class="nav-link" href="{{ route('shop') }}">
                        <span class="item-name">{{ __('vendorwebsite.shop') }}</span>
                    </a>
                </li>
            @endif
            @if (auth()->check() && isset($headerMenuSettingDecoded['mybooking']) && $headerMenuSettingDecoded['mybooking'] == 1)
                <li class="nav-item">
                    <a class="nav-link" href="{{ route('bookings') }}">
                        <span class="item-name">{{ __('vendorwebsite.booking') }}</span>
                    </a>
                </li>
            @endif
        </ul>
    </div>
</nav>


@push('scripts')
    <script>
        document.addEventListener('DOMContentLoaded', function() {
            // Handle branch selection
            document.querySelectorAll('.branch-panel-card').forEach(card => {
                card.addEventListener('click', function() {
                    const branchId = this.getAttribute('data-branch-id');

                    // Show loading state
                    const checkIcons = document.querySelectorAll('.branch-panel-active');
                    checkIcons.forEach(icon => icon.style.display = 'none');

                    // Send AJAX request
                    fetch('{{ route('branch.select') }}', {
                            method: 'POST',
                            headers: {
                                'Content-Type': 'application/json',
                                'X-CSRF-TOKEN': '{{ csrf_token() }}',
                                'X-Requested-With': 'XMLHttpRequest'
                            },
                            body: JSON.stringify({
                                branch_id: branchId
                            })
                        })
                        .then(response => response.json())
                        .then(data => {
                            if (data.success) {
                                // Show checkmark on selected branch
                                const selectedIcon = document.querySelector(
                                    `.branch-panel-card[data-branch-id="${data.branch_id}"] .branch-panel-active`
                                );
                                if (selectedIcon) {
                                    selectedIcon.style.display = 'block';
                                }

                                // Update branch name in the header
                                const branchName = this.querySelector('h6').textContent;
                                const branchSpan = document.querySelector(
                                    '.branch-panel .font-size-14');
                                if (branchSpan) {
                                    branchSpan.textContent = branchName;
                                }

                                // Dispatch custom event for branch selection
                                const branchSelectedEvent = new CustomEvent('branchSelected', {
                                    detail: {
                                        branchId: data.branch_id,
                                        branchName: branchName
                                    }
                                });
                                document.dispatchEvent(branchSelectedEvent);
                                // Redirect to home after selection
                                window.location.href = '{{ route('vendor.index') }}';
                            }
                        })
                        .catch(error => {
                            console.error('Error:', error);
                            alert(
                                '{{ __('vendorwebsite.error_occurred_while_selecting_branch') }}'
                            );
                        });
                });
            });
        });
    </script>
@endpush
