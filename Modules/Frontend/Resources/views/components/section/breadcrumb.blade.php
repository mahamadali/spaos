<div class="breadcrumb-card">
    <div class="container">
        <div class="d-flex justify-content-center align-items-center">
            <nav class="breadcrumb-container" aria-label="breadcrumb">
                <ol class="breadcrumb m-0">
                    <li class="breadcrumb-item" aria-current="page"><a
                            href="{{ route('index') }}">{{ __('messages.home') }}</a></li>
                    <li class="breadcrumb-item active">{{ $data ?? '' }}</li>
                </ol>
            </nav>
        </div>
    </div>
</div>
