@php
    $route = isset($data->id) ? route('backend.role.update', $data->id) : route('backend.role.store');
    $method = isset($data->id) ? 'PUT' : 'POST';
@endphp

<form action="{{ $route }}" method="POST">
    @csrf
    @if(isset($data->id))
        @method('PUT')
    @endif

    <div class="form-group">
        <label class="form-label">{{ __('messages.role_label_title') }} <span class="text-danger">*</span></label>
        <input type="text" name="title" value="{{ old('title', $data->title ?? '') }}" 
               class="form-control" id="role-title" placeholder="{{ __('messages.role_label_title') }}" required>
    </div>

    <button type="submit" class="btn btn-primary">{{ __('messages.save') }}</button>
    <button type="button" class="btn btn-danger" data-bs-dismiss="modal">{{ __('messages.close') }}</button>
</form>
