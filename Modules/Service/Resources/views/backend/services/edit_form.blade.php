<!-- Service Edit Form -->
<form action="{{ route('backend.services.update', $service->id) }}" method="POST" enctype="multipart/form-data"
    id="service-edit-form" novalidate>
    @csrf

    <input type="hidden" id="edit_service_id" name="service_id" value="{{ $service->id }}">

    <div class="offcanvas-body">
        <!-- Feature Image Upload -->
        <div class="form-group">
            <div class="text-center">
                <img src="{{ old('feature_image', $service->feature_image ?? default_feature_image()) }}"
                    alt="feature-image" class="img-fluid mb-2 avatar-140 avatar-rounded"
                    id="edit-feature-image-preview" />
                @if ($errors->has('feature_image'))
                    <div class="text-danger mb-2">{{ $errors->first('feature_image') }}</div>
                @endif
                <div class="d-flex align-items-center justify-content-center gap-2">
                    <input type="file" class="form-control d-none" id="edit_feature_image" name="feature_image"
                        accept=".jpeg, .jpg, .png, .gif" onchange="previewEditImage(event)" />
                    <label class="btn btn-info" for="edit_feature_image">{{ __('messages.upload') }}</label>
                    <button type="button" class="btn btn-danger" onclick="removeEditImage()" id="remove-edit-image-btn"
                        style="display:none;">{{ __('messages.remove') }}</button>
                </div>
                <input type="hidden" name="remove_feature_image" id="remove_feature_image_edit" value="0" />
            </div>
        </div>

        <!-- Name -->
        <div class="form-group col-md-12">
            <label for="edit_name" class="form-label">{{ __('service.lbl_name') }} <span
                    class="text-danger">*</span></label>
            <input type="text" class="form-control" name="name" id="edit_name"
                value="{{ old('name', $service->name) }}" placeholder="{{ __('service.enter_name') }}" required>
            @error('name')
                <span class="text-danger text-error">{{ $message }}</span>
            @enderror
        </div>

        <!-- Duration (min) -->
        <div class="form-group col-md-12">
            <label for="edit_duration_min" class="form-label">{{ __('service.lbl_duration_min') }} <span
                    class="text-danger">*</span></label>
            <input type="text" class="form-control" name="duration_min" id="edit_duration_min"
                value="{{ old('duration_min', $service->duration_min) }}"
                placeholder="{{ __('service.service_duration') }}" required>
            @error('duration_min')
                <span class="text-danger text-error">{{ $message }}</span>
            @enderror
        </div>

        <!-- Default Price -->
        <div class="form-group col-md-12">
            <label for="edit_default_price" class="form-label">{{ __('service.lbl_default_price') }}
                ({{ config('app.currency_symbol', '$') }}) <span class="text-danger">*</span></label>
            <input type="text" class="form-control" name="default_price" id="edit_default_price"
                value="{{ old('default_price', $service->default_price) }}"
                placeholder="{{ __('service.enter_price') }}" required>
            @error('default_price')
                <span class="text-danger  text-error">{{ $message }}</span>
            @enderror
        </div>

        <!-- Category -->
        <div class="form-group">
            <label for="edit_category_id" class="form-label">{{ __('service.lbl_category') }} <span
                    class="text-danger">*</span></label>
            <select class="form-control select2" name="category_id" id="edit_category_id" style="width:100%"
                data-placeholder="{{ __('service.select_category') }}" required
                onchange="changeEditCategory(this.value)">
                <option value="">{{ __('service.select_category') }}</option>
                @foreach ($categories as $category)
                    <option value="{{ $category->id }}"
                        {{ old('category_id', $service->category_id) == $category->id ? 'selected' : '' }}>
                        {{ $category->name }}
                    </option>
                @endforeach
            </select>
            @error('category_id')
                <span class="text-danger  text-error">{{ $message }}</span>
            @enderror
        </div>

        <!-- Sub Category -->
        <div class="form-group" id="edit-sub-category-group"
            style="display: {{ $service->sub_category_id ? 'block' : 'none' }};">
            <label for="edit_sub_category_id" class="form-label">{{ __('service.lbl_sub_category') }}</label>
            <select class="form-control select2" name="sub_category_id" id="edit_sub_category_id" style="width:100%"
                data-placeholder="{{ __('service.select_subcategory') }}">
                <option value="">{{ __('service.select_subcategory') }}</option>
                @if ($service->sub_category_id)
                    @foreach ($subcategories as $subcategory)
                        @if ($subcategory->parent_id == $service->category_id)
                            <option value="{{ $subcategory->id }}"
                                {{ $service->sub_category_id == $subcategory->id ? 'selected' : '' }}>
                                {{ $subcategory->name }}
                            </option>
                        @endif
                    @endforeach
                @endif
            </select>
            @error('sub_category_id')
                <span class="text-danger  text-error">{{ $message }}</span>
            @enderror
        </div>

        <!-- Custom Fields -->
        @if (!empty($customefield))
            @foreach ($customefield as $field)
                <div class="form-group">
                    <label for="edit_custom_{{ $field->id }}"
                        class="form-label">{{ $field->label }}{{ $field->required ? ' *' : '' }}</label>
                    @if ($field->type === 'text')
                        <input type="text" class="form-control" name="custom_fields[{{ $field->id }}]"
                            id="edit_custom_{{ $field->id }}"
                            value="{{ old('custom_fields.' . $field->id, $service->custom_fields[$field->id] ?? '') }}"
                            {{ $field->required ? 'required' : '' }}>
                    @elseif($field->type === 'select')
                        <select class="form-control" name="custom_fields[{{ $field->id }}]"
                            id="edit_custom_{{ $field->id }}" {{ $field->required ? 'required' : '' }}>
                            <option value="">{{ __('messages.select') }}</option>
                            @if ($field->value)
                                @foreach (json_decode($field->value) as $option)
                                    <option value="{{ $option }}"
                                        {{ old('custom_fields.' . $field->id, $service->custom_fields[$field->id] ?? '') == $option ? 'selected' : '' }}>
                                        {{ $option }}</option>
                                @endforeach
                            @endif
                        </select>
                    @elseif($field->type === 'textarea')
                        <textarea class="form-control" name="custom_fields[{{ $field->id }}]" id="edit_custom_{{ $field->id }}"
                            {{ $field->required ? 'required' : '' }}>{{ old('custom_fields.' . $field->id, $service->custom_fields[$field->id] ?? '') }}</textarea>
                    @endif
                    @error('custom_fields.' . $field->id)
                        <span class="text-danger  text-error">{{ $message }}</span>
                    @enderror
                </div>
            @endforeach
        @endif

        <!-- Description -->
        <div class="form-group col-md-12">
            <label for="edit_description" class="form-label">{{ __('service.lbl_description') }}</label>
            <textarea class="form-control" name="description" id="description" placeholder="{{ __('service.description') }}"
                maxlength="250">{{ old('description', $service->description ?? '') }}</textarea>
            <small id="description-counter" class="text-muted">0/250</small>
            @error('description')
                <span class="text-danger  text-error">{{ $message }}</span>
            @enderror
        </div>

        <!-- Status -->
        <div class="form-group">
            <div class="d-flex justify-content-between align-items-center">
                <label for="edit_status" class="form-label mb-0">{{ __('service.lbl_status') }}</label>
                <div class="form-check form-switch">
                    <input class="form-check-input" type="checkbox" name="status" id="edit_status" value="1"
                        {{ old('status', $service->status) ? 'checked' : '' }}>
                </div>
            </div>
        </div>
    </div>

    <div class="offcanvas-footer">
        <div class="d-grid d-md-flex gap-3 p-3">
            <button type="submit" class="btn btn-primary d-block" id="edit-submit-btn">
                <i class="fa-solid fa-floppy-disk"></i>
                {{ __('messages.update') }}
            </button>
            <button type="button" class="btn btn-outline-primary d-block" data-bs-dismiss="offcanvas">
                <i class="fa-solid fa-angles-left"></i>
                {{ __('messages.close') }}
            </button>
        </div>
    </div>
</form>

<script>
    // Edit form image preview functionality
    function previewEditImage(event) {
        const fileInput = event.target;
        const previewImgEl = document.getElementById('edit-feature-image-preview');
        const removeBtn = document.getElementById('remove-edit-image-btn');

        if (!fileInput || !fileInput.files || fileInput.files.length === 0) {
            return;
        }

        const file = fileInput.files[0];
        if (!file) {
            return;
        }

        // Validate file type
        if (!file.type.match('image.*')) {
            alert('Please select a valid image file.');
            fileInput.value = '';
            return;
        }

        const reader = new FileReader();
        reader.onload = function(e) {
            if (previewImgEl) {
                previewImgEl.src = e.target.result;
            }
            if (removeBtn) {
                removeBtn.style.display = 'inline-block';
            }
            // Reset remove flag when new image is selected
            if (document.getElementById('remove_feature_image_edit')) {
                document.getElementById('remove_feature_image_edit').value = '0';
            }
        };
        reader.onerror = function() {
            alert('Error reading the image file.');
            fileInput.value = '';
        };
        reader.readAsDataURL(file);
    }

    // Make function globally accessible
    window.previewEditImage = previewEditImage;

    function removeEditImage() {
        document.getElementById('edit-feature-image-preview').src = '{{ default_feature_image() }}';
        document.getElementById('edit_feature_image').value = '';
        document.getElementById('remove-edit-image-btn').style.display = 'none';
        // Set flag to explicitly remove image on form submission
        if (document.getElementById('remove_feature_image_edit')) {
            document.getElementById('remove_feature_image_edit').value = '1';
        }
    }

    // Make function globally accessible
    window.removeEditImage = removeEditImage;

    // Initialize Select2 for edit form category dropdowns
    function initializeEditServiceSelect2() {
        if ($('#edit_category_id').length && !$('#edit_category_id').hasClass('select2-hidden-accessible')) {
            $('#edit_category_id').select2({
                placeholder: "{{ __('service.select_category') }}",
                allowClear: false,
                width: '100%',
                minimumResultsForSearch: Infinity
            });
        }
        if ($('#edit_sub_category_id').length && !$('#edit_sub_category_id').hasClass('select2-hidden-accessible')) {
            $('#edit_sub_category_id').select2({
                placeholder: "{{ __('service.select_subcategory') }}",
                allowClear: false,
                width: '100%',
                minimumResultsForSearch: Infinity
            });
        }
    }

    // Edit form category change handler
    function changeEditCategory(categoryId) {
        const subCategoryGroup = document.getElementById('edit-sub-category-group');
        const subCategorySelect = $('#edit_sub_category_id');

        if (categoryId) {
            // Fetch subcategories for the selected category
            fetch(`{{ route('backend.services.get_subcategories') }}?category_id=${categoryId}`)
                .then(response => response.json())
                .then(data => {
                    subCategorySelect.empty();
                    subCategorySelect.append('<option value="">{{ __('service.select_subcategory') }}</option>');

                    if (data.length > 0) {
                        data.forEach(subcategory => {
                            subCategorySelect.append(
                                `<option value="${subcategory.id}">${subcategory.name}</option>`);
                        });
                        subCategoryGroup.style.display = 'block';
                    } else {
                        subCategoryGroup.style.display = 'none';
                    }
                    // Update Select2
                    subCategorySelect.trigger('change');
                })
                .catch(error => {
                    console.error('Error fetching subcategories:', error);
                    subCategoryGroup.style.display = 'none';
                });
        } else {
            subCategoryGroup.style.display = 'none';
        }
    }

    // Initialize Select2 when edit form loads
    $(document).ready(function() {
        if ($('#edit_category_id').length) {
            initializeEditServiceSelect2();
        }
    });

    // Edit form submission handler
    document.getElementById('service-edit-form').addEventListener('submit', function(e) {
        e.preventDefault();
        const form = this;
        const submitBtn = document.getElementById('edit-submit-btn');

        // Ensure action has /{id}
        try {
            const sid = document.getElementById('edit_service_id')?.value;
            if (sid && /\/app\/services\/?$/.test(form.action)) {
                form.action = form.action.replace(/\/?$/, '/' + sid);
            }
        } catch (_) {}

        // Show loading state
        submitBtn.disabled = true;
        const originalHTML = submitBtn.innerHTML;
        submitBtn.innerHTML =
            '<span class="spinner-border spinner-border-sm" role="status" aria-hidden="true"></span> {{ __('messages.updating') }}';

        const formData = new FormData(form);
        // Ensure we send POST with method override
        if (!formData.has('_method')) {
            formData.append('_method', 'PUT');
        } else {
            formData.set('_method', 'PUT');
        }

        const csrfToken = document.querySelector('meta[name="csrf-token"]')?.getAttribute('content');

        fetch(form.action, {
                method: 'POST',
                headers: {
                    'X-Requested-With': 'XMLHttpRequest',
                    'X-CSRF-TOKEN': csrfToken
                },
                body: formData
            })
            .then(response => response.json())
            .then(data => {
                if (data.success) {
                    // Close offcanvas and refresh parent page
                    const offcanvas = new bootstrap.Offcanvas(document.getElementById('service-edit-form'));
                    offcanvas.hide();
                    window.location.reload();
                } else {
                    // Handle errors
                    if (data.errors) {
                        Object.keys(data.errors).forEach(key => {
                            const input = form.querySelector(`#edit_${key}`);
                            if (input) {
                                input.classList.add('is-invalid');
                                input.nextElementSibling.textContent = data.errors[key][0];
                            }
                        });
                    }
                    if (data.message) {
                        alert(data.message);
                    }
                }
                submitBtn.disabled = false;
                submitBtn.innerHTML = originalHTML;
            })
            .catch(error => {
                console.error('Error submitting edit form:', error);
                submitBtn.disabled = false;
                submitBtn.innerHTML = originalHTML;
                alert('An error occurred while updating the service.');
            });
    });
</script>
