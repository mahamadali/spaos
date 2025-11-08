<div class="text-end d-flex gap-2 align-items-center">

    @if ($data->status == 'completed')
        <a href="{{ route('backend.bookings.invoice', ['id' => $data->id]) }}"
            class="btn btn-sm btn-icon btn-info" data-bs-toggle="tooltip" data-bs-placement="top"
            title="{{ __('messages.view_details') }}">
            <i class="fa-solid fa-eye"></i>
        </a>
    @endif
    @hasPermission('delete_booking')
        <a href="{{ route("backend.$module_name.destroy", $data->id) }}" id="delete-{{ $module_name }}-{{ $data->id }}"
            class="btn btn-danger btn-sm" data-type="ajax" data-method="DELETE" data-token="{{ csrf_token() }}"
            data-bs-toggle="tooltip" title="{{ __('messages.delete') }}"
            data-confirm="{{ __('messages.are_you_sure?', ['module' => __('booking.singular_title'), 'name' => $data->user->full_name ?? default_user_name()]) }}">
            <i class="fa-solid fa-trash"></i></a>
    @endhasPermission
</div>
