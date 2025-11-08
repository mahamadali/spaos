<div class="d-flex gap-2 align-items-center">
    {{-- <a href="{{ route('backend.coupons-view', ['id' => $data->id]) }}" class="btn btn-secondary btn-sm" data-type="ajax"
        data-bs-toggle="tooltip" title="{{ __('messages.coupon_view') }}"> <i class="fa-solid fa-table"></i></a> --}}

    @if(auth()->user()->hasRole('super admin'))
        {{-- Super Admin: Use full page form --}}
        <a href="{{ route('backend.promotions.edit', $data->id) }}" class="btn btn-primary btn-sm"
            title="{{ __('messages.edit') }}" data-bs-toggle="tooltip"> <i class="fa-solid fa-pen-clip"></i></a>
    @else
        {{-- Admin: Use offcanvas form --}}
        <button type="button" class="btn btn-primary btn-sm" data-promotion-id="{{ $data->id }}"
            title="{{ __('messages.edit') }}" data-bs-toggle="tooltip"> <i class="fa-solid fa-pen-clip"></i></button>
    @endif

    <a href="{{ route("backend.$module_name.destroy", $data->id) }}" id="delete-{{ $module_name }}-{{ $data->id }}"
        class="btn btn-danger btn-sm" data-type="ajax" data-method="DELETE" data-token="{{ csrf_token() }}"
        data-bs-toggle="tooltip" title="{{ __('messages.delete') }}"
        data-confirm="{{ __('messages.are_you_sure?', ['module' => __('promotion.singular_title'), 'name' => $data->name]) }}">
        <i class="fa-solid fa-trash"></i></a>

</div>
