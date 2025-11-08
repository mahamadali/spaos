@if ($data->orderGroup->type == 'booking')
    <span class="badge bg-danger-subtle text-danger rounded-pill text-capitalize">

        {{ __('messages.booking') }}

    </span>
@else
    <span class="badge bg-primary-subtle rounded-pill text-capitalize">
        {{ __('messages.offline') }}

    </span>
@endif
