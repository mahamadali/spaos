@extends('backend.layouts.app')

@section('title')
    {{ isset($category) ? __('messages.edit') . ' ' . __('category.singular_title') : __('messages.new') . ' ' . __('category.singular_title') }}
@endsection

@push('after-styles')
    <link rel="stylesheet" href="{{ mix('modules/category/style.css') }}">
@endpush

@section('content')
    <div class="card">
        <div class="card-body">
            <div class="d-flex align-items-center justify-content-between gap-3 flex-wrap mb-3">
                <h4 id="form-offcanvasLabel" class="mb-0">
                    {{ isset($category) ? __('messages.edit') . ' ' . __('category.singular_title') : __('messages.new') . ' ' . __('category.singular_title') }}
                </h4>
                <a href="{{ route('backend.categories.index') }}" class="btn btn-primary">{{ __('messages.back') }}</a>
            </div>

            <form id="category-form" enctype="multipart/form-data" method="POST"
                action="{{ isset($category) ? route('backend.categories.update', $category->id) : route('backend.categories.store') }}"
                novalidate>
                @csrf
                @if (isset($category))
                    @method('PUT')
                @endif
                <input type="hidden" name="id" value="{{ isset($category) ? $category->id : null }}">

                <div class="row">
                    <div class="col-12">
                        <div class="form-group">
                            <div class="col-md-12 text-center upload-image-box">
                                <img src="{{ isset($category) ? $category->feature_image : default_feature_image() }}"
                                    name="feature_image" alt="feature-image" class="img-fluid mb-2 avatar-140 rounded"
                                    id="image-preview" />
                                <div id="validation-message" class="text-danger mb-2 d-none"></div>
                                <div class="d-flex align-items-center justify-content-center gap-2">
                                    <input type="file" class="form-control d-none" id="feature_image"
                                        name="feature_image" accept=".jpeg, .jpg, .png, .gif" />
                                    <input type="hidden" name="feature_image_removed" value="0">
                                    <label class="btn btn-sm btn-primary"
                                        for="feature_image">{{ __('messages.upload') }}</label>
                                    <input type="button" class="btn btn-sm btn-secondary" name="remove"
                                        value="{{ __('messages.remove') }}" id="remove-image" onclick="removeImage()"
                                        style="display: {{ isset($category) && $category->feature_image ? 'block' : 'none' }};" />
                                </div>
                            </div>
                        </div>

                        <div class="form-group">
                            <label class="form-label" for="name">{{ __('category.lbl_name') }} <span
                                    class="text-danger">*</span></label>
                            <input type="text" name="name" id="name"
                                class="form-control @error('name') is-invalid @enderror"
                                placeholder="{{ __('category.placeholder_name') }}"
                                value="{{ old('name', isset($category) ? $category->name : '') }}" required>
                            @error('name')
                                <span class="text-danger name-error mt-2" style="display: block;">{{ $message }}</span>
                            @enderror
                        </div>

                        @if (isset($customefield) && count($customefield) > 0)
                            @foreach ($customefield as $field)
                                <div class="form-group">
                                    <label class="form-label" for="custom_field_{{ $field->id }}">
                                        {{ $field->label }}
                                        @if ($field->required)
                                            <span class="text-danger">*</span>
                                        @endif
                                    </label>

                                    @switch($field->type)
                                        @case('text')
                                            <input type="text" name="custom_fields_data[{{ $field->id }}]"
                                                id="custom_field_{{ $field->id }}"
                                                class="form-control @error('custom_fields_data.' . $field->id) is-invalid @enderror"
                                                value="{{ old('custom_fields_data.' . $field->id, isset($category) && isset($category->custom_field_data[$field->id]) ? $category->custom_field_data[$field->id] : '') }}"
                                                @if ($field->required) required @endif>
                                        @break

                                        @case('textarea')
                                            <textarea name="custom_fields_data[{{ $field->id }}]" id="custom_field_{{ $field->id }}"
                                                class="form-control @error('custom_fields_data.' . $field->id) is-invalid @enderror" rows="3"
                                                @if ($field->required) required @endif>{{ old('custom_fields_data.' . $field->id, isset($category) && isset($category->custom_field_data[$field->id]) ? $category->custom_field_data[$field->id] : '') }}</textarea>
                                        @break

                                        @case('select')
                                            <select name="custom_fields_data[{{ $field->id }}]"
                                                id="custom_field_{{ $field->id }}"
                                                class="form-control select2 @error('custom_fields_data.' . $field->id) is-invalid @enderror"
                                                @if ($field->required) required @endif>
                                                <option value="">{{ __('messages.select') }}</option>
                                                @if (isset($field->value) && is_array($field->value))
                                                    @foreach ($field->value as $option)
                                                        <option value="{{ $option }}"
                                                            {{ old('custom_fields_data.' . $field->id, isset($category) && isset($category->custom_field_data[$field->id]) ? $category->custom_field_data[$field->id] : '') == $option ? 'selected' : '' }}>
                                                            {{ $option }}
                                                        </option>
                                                    @endforeach
                                                @endif
                                            </select>
                                        @break

                                        @case('checkbox')
                                            <div class="form-check">
                                                <input type="checkbox" name="custom_fields_data[{{ $field->id }}]"
                                                    id="custom_field_{{ $field->id }}" class="form-check-input" value="1"
                                                    {{ old('custom_fields_data.' . $field->id, isset($category) && isset($category->custom_field_data[$field->id]) ? $category->custom_field_data[$field->id] : '') == '1' ? 'checked' : '' }}>
                                                <label class="form-check-label" for="custom_field_{{ $field->id }}">
                                                    {{ $field->label }}
                                                </label>
                                            </div>
                                        @break

                                        @case('radio')
                                            @if (isset($field->value) && is_array($field->value))
                                                @foreach ($field->value as $option)
                                                    <div class="form-check">
                                                        <input type="radio" name="custom_fields_data[{{ $field->id }}]"
                                                            id="custom_field_{{ $field->id }}_{{ $loop->index }}"
                                                            class="form-check-input" value="{{ $option }}"
                                                            {{ old('custom_fields_data.' . $field->id, isset($category) && isset($category->custom_field_data[$field->id]) ? $category->custom_field_data[$field->id] : '') == $option ? 'checked' : '' }}>
                                                        <label class="form-check-label"
                                                            for="custom_field_{{ $field->id }}_{{ $loop->index }}">
                                                            {{ $option }}
                                                        </label>
                                                    </div>
                                                @endforeach
                                            @endif
                                        @break

                                        @default
                                            <input type="text" name="custom_fields_data[{{ $field->id }}]"
                                                id="custom_field_{{ $field->id }}"
                                                class="form-control @error('custom_fields_data.' . $field->id) is-invalid @enderror"
                                                value="{{ old('custom_fields_data.' . $field->id, isset($category) && isset($category->custom_field_data[$field->id]) ? $category->custom_field_data[$field->id] : '') }}"
                                                @if ($field->required) required @endif>
                                    @endswitch

                                    @error('custom_fields_data.' . $field->id)
                                        <span class="text-danger custom-field-error mt-2"
                                            style="display: block;">{{ $message }}</span>
                                    @enderror
                                </div>
                            @endforeach
                        @endif

                        <div class="form-group">
                            <div class="d-flex justify-content-between align-items-center form-control">
                                <label class="form-label mb-0" for="status">{{ __('category.lbl_status') }}</label>
                                <div class="form-check form-switch">
                                    <input class="form-check-input" value="1" name="status" id="status"
                                        type="checkbox"
                                        {{ old('status', isset($category) ? $category->status : 1) == 1 ? 'checked' : '' }} />
                                </div>
                            </div>
                        </div>

                        <div class="form-group mt-4">
                            <button type="submit" class="btn btn-primary" id="submit-btn">
                                <i class="fas fa-save"></i>
                                {{ isset($category) ? __('messages.update') : __('messages.submit') }}
                            </button>

                        </div>
                    </div>
                </div>
            </form>
        </div>
    </div>
@endsection

@push('after-scripts')
    <script src="{{ mix('modules/category/script.js') }}"></script>
    <script>
        document.addEventListener('DOMContentLoaded', function() {
            // File upload preview
            const fileInput = document.getElementById('feature_image');
            const imagePreview = document.getElementById('image-preview');
            const removeButton = document.getElementById('remove-image');
            const validationMessage = document.getElementById('validation-message');

            fileInput.addEventListener('change', function(e) {
                const file = e.target.files[0];
                const featureImageRemoved = document.querySelector('input[name="feature_image_removed"]');

                if (file) {
                    // Reset the removed flag when a new image is selected
                    if (featureImageRemoved) {
                        featureImageRemoved.value = '0';
                    }

                    // Validate file type
                    const allowedTypes = ['image/jpeg', 'image/jpg', 'image/png', 'image/gif'];
                    if (!allowedTypes.includes(file.type)) {
                        validationMessage.textContent = '{{ __('messages.please_select_valid_image') }}';
                        validationMessage.classList.remove('d-none');
                        fileInput.value = '';
                        return;
                    }

                    // Validate file size (5MB limit)
                    const maxSize = 5 * 1024 * 1024; // 5MB
                    if (file.size > maxSize) {
                        validationMessage.textContent =
                            '{{ __('messages.image_size_should_be_less_than_5mb') }}';
                        validationMessage.classList.remove('d-none');
                        fileInput.value = '';
                        return;
                    }

                    validationMessage.classList.add('d-none');

                    const reader = new FileReader();
                    reader.onload = function(e) {
                        imagePreview.src = e.target.result;
                        removeButton.style.display = 'block';
                    };
                    reader.readAsDataURL(file);
                }
            });

            // Form submission
            const form = document.getElementById('category-form');
            const submitBtn = document.getElementById('submit-btn');


        });

        function removeImage() {
            const imagePreview = document.getElementById('image-preview');
            const fileInput = document.getElementById('feature_image');
            const removeButton = document.getElementById('remove-image');
            const validationMessage = document.getElementById('validation-message');
            const featureImageRemoved = document.querySelector('input[name="feature_image_removed"]');

            imagePreview.src = '{{ default_feature_image() }}';
            fileInput.value = '';
            removeButton.style.display = 'none';
            validationMessage.classList.add('d-none');

            if (featureImageRemoved) {
                featureImageRemoved.value = '1';
            }
        }
    </script>
@endpush
