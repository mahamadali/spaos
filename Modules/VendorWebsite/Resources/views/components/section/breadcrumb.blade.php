<div class="breadcrumb-section">
    <div class="gradient pink-gradient"></div>
    <div class="gradient blue-gradient"></div>
    <div class="container">
        <div class="d-flex justify-content-center align-items-center">
            <nav class="breadcrumb-container" aria-label="breadcrumb">
                <ol class="breadcrumb m-0">
                    <li class="breadcrumb-item"><a href="{{ route('vendor.index') }}">Home</a></li>
                    <li class="breadcrumb-item active" aria-current="page">
                        {{-- @php
                            $segment = request()->segment(2) ?? 'Home';
                            $title = $pageTitle ?? (isset($title) ? $title : ucfirst($segment));
                            if ($segment === 'myorder') {
                                $title = 'My Order';
                            }
                        @endphp --}}

                        @php
                            $segment = request()->segment(2) ?? 'Home';
                            $title = $pageTitle ?? (isset($title) ? $title : ucfirst($segment));

                            // If the segment is 'myorder', set the title to 'My Order'
                            if ($segment === 'myorder') {
                                $title = 'My Order';
                            }

                            // If the segment is 'faq', convert the title to uppercase
                            if ($segment === 'faq') {
                                $title = strtoupper($title);
                            }
                        @endphp

                        {{ $title }}
                    </li>
                </ol>
            </nav>
        </div>
    </div>
</div>
