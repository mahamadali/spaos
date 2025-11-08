@forelse ($data->cities as $city)
    <span class="badge bg-secondary rounded-pill">{{ $city->name }}</span>
@empty
    <span class="badge bg-secondary rounded-pill">{{ __('messages.N/A') }}</span>
@endforelse
