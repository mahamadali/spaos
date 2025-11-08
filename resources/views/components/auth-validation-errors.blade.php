@props(['errors'])

@if ($errors->any())
    <div {{ $attributes }}>
        {{-- <div class="text-danger fw-bold">
            {{ __('messages.something_went_wrong') }}
        </div> --}}

        <ul class="mt-3 text-danger">
            @foreach ($errors->all() as $error)
                <li>{{ $error }}</li>
            @endforeach
        </ul>
    </div>
@endif
