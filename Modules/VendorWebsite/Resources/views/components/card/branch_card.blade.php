@props(['branch'])

@php
    $timezone = setting('default_time_zone') ?? 'UTC';
    $today = \Carbon\Carbon::now($timezone)->format('l');
    $now = \Carbon\Carbon::now($timezone);
    $hours = \Modules\BussinessHour\Models\BussinessHour::where('branch_id', $branch->id)
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
    }
    $selectedBranchId = session('selected_branch_id');
    $isSelected = $selectedBranchId == $branch->id;
@endphp

<div class="branch-card rounded position-relative overflow-hidden branch-select-badge-card"
    data-branch-id="{{ $branch->id }}">
    <span class="font-size-14 text-uppercase position-absolute top-0 start-0  cursor-pointer z-2"
        data-branch-id="{{ $branch->id }}">
        @if ($isSelected)
            <i class="ph-fill ph-check-circle fs-4 text-primary"></i>
        @endif
    </span>
    <div class=" position-relative">
        @php
            $image = $branch->feature_image ?? asset('img/vendorwebsite/branch-image.jpg');
        @endphp
        <span
            class="badge {{ $isOpen ? 'bg-success' : 'bg-danger' }} text-white font-size-14 text-uppercase position-absolute top-0 end-0">
            {{ $isOpen ? __('vendorwebsite.open') : __('vendorwebsite.closed') }}
        </span>
        {{-- <img src="{{ $image }}" class="card-img-top" alt="{{ $branch->name }}"> --}}
        {{-- <img src="{{ $image }}" class="card-img-top" alt="{{ $branch->name }}" style="object-fit: cover; height: 200px;"> --}}
        <img src="{{ $image }}" class="card-img-top" alt="{{ $branch->name }}" style="object-fit: contain; height: 200px;">

    </div>

    <a href="{{ route('branch-detail', $branch->id) }}"
        class="branch-info-box text-decoration-none text-reset d-block z-1">
        <span class="d-flex flex-wrap align-items-center gap-1 mb-2">
            <h5 class="mb-0 fw-medium line-count-1">{{ $branch->name }}</h5>
            @if ($branch->branch_for)
                <span class="badge bg-purple text-body border rounded-pill text-uppercase">
                    {{ $branch->branch_for }}
                </span>
            @endif
        </span>
        @if ($branch->address)
            <span class="d-flex gap-2">
                <i class="ph ph-map-pin align-middle"></i>
                <span class="font-size-14">
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
            </span>
        @endif
    </a>
</div>
