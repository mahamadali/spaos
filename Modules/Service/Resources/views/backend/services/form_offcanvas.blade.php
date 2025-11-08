<!-- Service Form Offcanvas -->
<form action="{{ isset($service) ? route('backend.services.update', $service->id) : route('backend.services.store') }}"
    method="POST" enctype="multipart/form-data" id="service-form" novalidate>
    @csrf
    @if (isset($service))
        @method('PUT')
    @endif
    <div class="ajax-errors"></div>
    <div class="offcanvas offcanvas-end" tabindex="-1" id="form-offcanvas" aria-labelledby="form-offcanvasLabel">
        <div class="offcanvas-header">
            <h5 class="offcanvas-title" id="form-offcanvasLabel">
                {{ isset($service) ? __('messages.edit') . ' ' . __('service.singular_title') : __('messages.new') . ' ' . __('service.singular_title') }}
            </h5>
            <button type="button" class="btn-close" data-bs-dismiss="offcanvas" aria-label="Close"></button>
        </div>

        <div class="offcanvas-body">
            <!-- Feature Image Upload -->
            <div class="form-group">
                <div class="text-center">
                    <img src="{{ old('feature_image', $service->feature_image ?? default_feature_image()) }}"
                        alt="feature-image" class="img-fluid mb-2 avatar-140 avatar-rounded"
                        id="feature-image-preview" />
                    <div class="d-flex align-items-center justify-content-center gap-2">
                        <input type="file" class="form-control d-none" id="feature_image" name="feature_image"
                            accept=".jpeg, .jpg, .png, .gif" onchange="previewImage(event)" />
                        <label class="btn btn-info" for="feature_image">{{ __('messages.upload') }}</label>
                        <button type="button" class="btn btn-danger" onclick="removeImage()" id="remove-image-btn"
                            style="display:none;">{{ __('messages.remove') }}</button>
                    </div>
                    <input type="hidden" name="remove_feature_image" id="remove_feature_image" value="0" />
                </div>
            </div>
            <!-- Name -->
            <div class="form-group col-md-12">
                <label for="name" class="form-label">{{ __('service.lbl_name') }} <span
                        class="text-danger ">*</span></label>
                <input type="text" class="form-control" name="name" id="name"
                    value="{{ old('name', $service->name ?? '') }}" placeholder="{{ __('service.enter_name') }}"
                    required>
            </div>
            <!-- Duration (min) -->
            <div class="form-group col-md-12">
                <label for="duration_min" class="form-label">{{ __('service.lbl_duration_min') }} <span
                        class="text-danger">*</span></label>
                <input type="text" class="form-control" name="duration_min" id="duration_min"
                    value="{{ old('duration_min', $service->duration_min ?? '') }}"
                    placeholder="{{ __('service.service_duration') }}" inputmode="numeric" pattern="[0-9]*" required>
            </div>
            <!-- Default Price -->
            <div class="form-group col-md-12">
                <label for="default_price" class="form-label">{{ __('service.lbl_default_price') }}
                    ({{ config('app.currency_symbol', '$') }}) <span class="text-danger">*</span></label>
                <input type="text" class="form-control" name="default_price" id="default_price"
                    value="{{ old('default_price', $service->default_price ?? '') }}"
                    placeholder="{{ __('service.enter_price') }}" inputmode="decimal" pattern="^[0-9]*\.?[0-9]*$"
                    required>
            </div>
            <!-- Category -->
            <div class="form-group">
                <label for="category_id" class="form-label">{{ __('service.lbl_category') }} <span
                        class="text-danger">*</span></label>
                <select class="form-control select2" name="category_id" id="category_id" style="width:100%"
                    data-placeholder="{{ __('service.select_category') }}" required onchange="changeCategory(this)">
                    <option value="">{{ __('service.select_category') }}</option>
                    @foreach ($categories as $category)
                        <option value="{{ $category->id }}"
                            {{ old('category_id', $service->category_id ?? '') == $category->id ? 'selected' : '' }}>
                            {{ $category->name }}
                        </option>
                    @endforeach
                </select>
            </div>
            <!-- Sub Category -->
            <div class="form-group" id="sub-category-group">
                <label for="sub_category_id" class="form-label">{{ __('service.lbl_sub_category') }}</label>
                <select class="form-control select2" name="sub_category_id" id="sub_category_id" style="width:100%"
                    data-placeholder="{{ __('service.select_subcategory') }}">
                    <option value="">{{ __('service.select_subcategory') }}</option>
                </select>
            </div>
            <!-- Custom Fields -->
            @if (!empty($customefield))
                @foreach ($customefield as $field)
                    <div class="form-group">
                        <label for="custom_{{ $field->id }}"
                            class="form-label">{{ $field->label }}{{ $field->required ? ' *' : '' }}</label>
                        @if ($field->type === 'text')
                            <input type="text" class="form-control" name="custom_fields[{{ $field->id }}]"
                                id="custom_{{ $field->id }}"
                                value="{{ old('custom_fields.' . $field->id, $service->custom_fields[$field->id] ?? '') }}"
                                {{ $field->required ? 'required' : '' }}>
                        @elseif($field->type === 'select')
                            <select class="form-control" name="custom_fields[{{ $field->id }}]"
                                id="custom_{{ $field->id }}" {{ $field->required ? 'required' : '' }}>
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
                            <textarea class="form-control" name="custom_fields[{{ $field->id }}]" id="custom_{{ $field->id }}"
                                {{ $field->required ? 'required' : '' }}>{{ old('custom_fields.' . $field->id, $service->custom_fields[$field->id] ?? '') }}</textarea>
                        @endif
                    </div>
                @endforeach
            @endif
            <!-- Description -->
            <div class="form-group col-md-12">
                <label for="description" class="form-label">{{ __('service.lbl_description') }}</label>
                <textarea class="form-control" name="description" id="description" placeholder="{{ __('service.description') }}"
                    maxlength="250">{{ old('description', $service->description ?? '') }}</textarea>
                <small id="description-counter" class="text-muted">0/250</small>
            </div>
            <!-- Status -->
            <div class="form-group">
                <div class="d-flex justify-content-between align-items-center">
                    <label for="status" class="form-label mb-0">{{ __('service.lbl_status') }}</label>
                    <div class="form-check form-switch">
                        <input class="form-check-input" type="checkbox" name="status" id="status"
                            value="1" {{ old('status', $service->status ?? 1) ? 'checked' : '' }}>
                    </div>
                </div>
            </div>
        </div>
        <div class="offcanvas-footer p-3 border-top">
            <div class="d-flex justify-content-end gap-2">
                <button type="button" class="btn btn-outline-primary" data-bs-dismiss="offcanvas">
                    {{ __('messages.cancel') }}
                </button>
                <button type="submit" class="btn btn-primary d-inline-flex align-items-center gap-2"
                    id="submit-btn">
                    <span class="spinner-border spinner-border-sm d-none" role="status" aria-hidden="true"></span>
                    <span id="submit-text">{{ __('messages.save') }}</span>
                </button>
            </div>
        </div>
    </div>
</form>

<script>
    // Image preview functionality
    function previewImage(event) {
        const fileInput = event.target;
        const previewImgEl = document.getElementById('feature-image-preview');
        const removeBtn = document.getElementById('remove-image-btn');

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
            const removeFlag = document.getElementById('remove_feature_image');
            if (removeFlag) {
                removeFlag.value = '0';
            }
        };
        reader.onerror = function() {
            alert('Error reading the image file.');
            fileInput.value = '';
        };
        reader.readAsDataURL(file);
    }

    // Make function globally accessible
    window.previewImage = previewImage;

    function removeImage() {
        document.getElementById('feature-image-preview').src = '{{ default_feature_image() }}';
        document.getElementById('feature_image').value = '';
        document.getElementById('remove-image-btn').style.display = 'none';
        // Set flag to explicitly remove image on form submission
        if (document.getElementById('remove_feature_image')) {
            document.getElementById('remove_feature_image').value = '1';
        }
    }

    // Category change handler
    function changeCategory(element) {
        const categoryId = element.value;

        const subCategoryGroup = document.getElementById('sub-category-group');
        const subCategorySelect = $('#sub_category_id');

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
                    }
                    // Update Select2
                    subCategorySelect.trigger('change');
                    // Always show the sub category group
                    subCategoryGroup.style.display = 'block';
                })
                .catch(error => {
                    // Still show the sub category group even if there's an error
                    subCategoryGroup.style.display = 'block';
                });
        } else {
            // Show all subcategories when no category is selected
            fetch(`{{ route('backend.services.get_subcategories') }}`)
                .then(response => response.json())
                .then(data => {
                    subCategorySelect.empty();
                    subCategorySelect.append('<option value="">{{ __('service.select_subcategory') }}</option>');
                    if (data.length > 0) {
                        data.forEach(subcategory => {
                            subCategorySelect.append(
                                `<option value="${subcategory.id}">${subcategory.name}</option>`);
                        });
                    }
                    // Update Select2
                    subCategorySelect.trigger('change');
                    subCategoryGroup.style.display = 'block';
                })
                .catch(error => {
                    subCategoryGroup.style.display = 'block';
                });
        }
    }

    // Helper function to safely destroy Select2 if it exists
    function safeDestroySelect2(selector) {
        const $el = $(selector);
        if ($el.length && $el.data('select2')) {
            try {
                $el.select2('destroy');
            } catch (e) {
                // Ignore errors if Select2 is not properly initialized
            }
        }
    }

    // Initialize Select2 for category dropdowns
    function initializeServiceSelect2() {
        const $categoryId = $('#category_id');
        const $subCategoryId = $('#sub_category_id');

        // Safely destroy existing Select2 instances first to avoid conflicts
        safeDestroySelect2('#category_id');
        safeDestroySelect2('#sub_category_id');

        // Remove any duplicate Select2 containers first
        $categoryId.siblings('.select2-container').remove();
        $subCategoryId.siblings('.select2-container').remove();

        // Initialize Select2 for category dropdown
        // Always destroy and reinitialize to ensure proper dropdownParent when offcanvas is shown
        if ($categoryId.length) {
            try {
                // Force destroy if it exists
                if ($categoryId.hasClass('select2-hidden-accessible') || $categoryId.data('select2')) {
                    try {
                        $categoryId.select2('destroy');
                    } catch (e) {
                        // If destroy fails, remove data manually
                        $categoryId.removeData('select2');
                        $categoryId.removeClass('select2-hidden-accessible');
                    }
                    // Remove any leftover containers
                    $categoryId.siblings('.select2-container').remove();
                }

                // Reinitialize with correct dropdownParent
                $categoryId.select2({
                    placeholder: "{{ __('service.select_category') }}",
                    allowClear: false,
                    width: '100%',
                    minimumResultsForSearch: Infinity,
                    dropdownParent: $('#form-offcanvas')
                }).on('select2:open', function() {
                    // Remove any duplicate containers after opening
                    $categoryId.siblings('.select2-container').not(':first').remove();
                });

                // Ensure original select is hidden
                $categoryId.addClass('select2-hidden-accessible');
                // Remove any duplicate containers after initialization
                setTimeout(function() {
                    $categoryId.siblings('.select2-container').not(':first').remove();
                }, 100);
            } catch (e) {
                // Error handling
            }
        }

        // Initialize Select2 for subcategory dropdown
        // Always destroy and reinitialize to ensure proper dropdownParent when offcanvas is shown
        if ($subCategoryId.length) {
            try {
                // Force destroy if it exists
                if ($subCategoryId.hasClass('select2-hidden-accessible') || $subCategoryId.data('select2')) {
                    try {
                        $subCategoryId.select2('destroy');
                    } catch (e) {
                        // If destroy fails, remove data manually
                        $subCategoryId.removeData('select2');
                        $subCategoryId.removeClass('select2-hidden-accessible');
                    }
                    // Remove any leftover containers
                    $subCategoryId.siblings('.select2-container').remove();
                }

                // Reinitialize with correct dropdownParent
                $subCategoryId.select2({
                    placeholder: "{{ __('service.select_subcategory') }}",
                    allowClear: false,
                    width: '100%',
                    minimumResultsForSearch: Infinity,
                    dropdownParent: $('#form-offcanvas')
                }).on('select2:open', function() {
                    // Remove any duplicate containers after opening
                    $subCategoryId.siblings('.select2-container').not(':first').remove();
                });

                // Ensure original select is hidden
                $subCategoryId.addClass('select2-hidden-accessible');
                // Remove any duplicate containers after initialization
                setTimeout(function() {
                    $subCategoryId.siblings('.select2-container').not(':first').remove();
                }, 100);
            } catch (e) {
                // Error handling
            }
        }
    }

    // Make function globally accessible
    window.initializeServiceSelect2 = initializeServiceSelect2;

    // Initialize jQuery Validation
    $(document).ready(function() {
        // Don't initialize Select2 here - wait until offcanvas is shown
        // Select2 will be initialized when offcanvas opens via shown.bs.offcanvas event

        // Ensure image preview works - add event listener as fallback
        const featureImageInput = document.getElementById('feature_image');
        if (featureImageInput) {
            // Remove any existing listeners to avoid duplicates
            featureImageInput.removeEventListener('change', previewImage);
            // Add event listener as fallback (in addition to inline onchange)
            featureImageInput.addEventListener('change', function(e) {
                previewImage(e);
            });
        }

        // Add custom validation method for price (numeric only)
        $.validator.addMethod("customPrice", function(value, element) {
            // Allow empty (handled by required rule)
            if (!value) return true;
            // Check if value contains only numbers and optional decimal point
            return /^[0-9]+(\.[0-9]+)?$/.test(value.trim());
        }, "Only numbers are allowed");

        const $serviceForm = $('#service-form');

        function initAddFormValidator() {
            if (!$.fn.validate || !$serviceForm.length) return;
            if ($serviceForm.data('validator')) return; // already initialized
            $serviceForm.validate({
                errorElement: 'div',
                errorClass: 'validation-error text-danger mt-1',
                onkeyup: false,
                onfocusout: function(element) {
                    if (element.name === 'default_price' || element.name === 'duration_min') {
                        $(element).valid();
                    }
                },
                highlight: function(element) {},
                unhighlight: function(element) {
                    $(element).removeClass('is-invalid');
                    $(element).siblings('.validation-error').remove();
                    $(element).siblings('.validation-error.mt-1').remove();
                    $(element).next('.validation-error').remove();
                    $(element).next('.validation-error.mt-1').remove();
                    $(element).nextAll('.validation-error').remove();
                    $(element).nextAll('.validation-error.mt-1').remove();
                },
                errorPlacement: function(error, element) {
                    element.siblings('.validation-error.mt-1').not('#description-counter')
                        .remove();
                    element.nextAll('.validation-error.mt-1').not('#description-counter').remove();
                    error.removeClass('text-muted text-secondary').addClass(
                        'validation-error text-danger mt-1 d-block');
                    error.css('color', '#dc3545');
                    // For Select2 fields (like Category), place error under the visible container
                    if (element.hasClass('select2-hidden-accessible')) {
                        const $container = element.next('.select2');
                        if ($container.length) {
                            error.insertAfter($container);
                            return;
                        }
                    }
                    error.insertAfter(element);
                },
                invalidHandler: function(form, validator) {
                    validator.focusInvalid();
                },
                rules: {
                    name: {
                        required: true
                    },
                    duration_min: {
                        required: true,
                        digits: true
                    },
                    default_price: {
                        required: true,
                        number: true,
                        customPrice: true
                    },
                    category_id: {
                        required: true
                    }
                },
                messages: {
                    name: {
                        required: "Name is a required field"
                    },
                    duration_min: {
                        required: "Service Duration ( Mins ) is a required field",
                        digits: "Only numbers are allowed"
                    },
                    default_price: {
                        required: "Price is a required field",
                        number: "Only numbers are allowed",
                        customPrice: "Only numbers are allowed"
                    },
                    category_id: {
                        required: "Category is a required field"
                    }
                },
                submitHandler: function(form) {
                    // handled below (fetch); allow submit to continue
                    return true;
                }
            });
        }

        // Initialize on first load
        initAddFormValidator();

        // Also initialize when offcanvas is shown (for dynamic replacement)
        const offcanvasEl = document.getElementById('form-offcanvas');
        if (offcanvasEl) {
            offcanvasEl.addEventListener('shown.bs.offcanvas', function() {
                // Re-init if validator got lost due to DOM replacement
                if ($serviceForm.length && (!$serviceForm.data('validator'))) {
                    initAddFormValidator();
                }
            });
        }

        // Guard: block submit if invalid (ensures JS validation runs)
        $(document).on('click', '#submit-btn', function(e) {
            if (!$.fn.validate) return; // if plugin missing, skip
            if ($serviceForm.length) {
                initAddFormValidator();
                if (!$serviceForm.valid()) {
                    e.preventDefault();
                    e.stopPropagation();
                    return false;
                }
            }
        });

        // Prevent non-numeric characters from being typed in duration field - exactly like contact number field
        $('#duration_min').on('keydown', function(e) {
            // Allow: backspace, delete, tab, escape, enter, and arrow keys
            if ([8, 9, 27, 13, 46, 37, 38, 39, 40].indexOf(e.keyCode) !== -1 ||
                // Allow Ctrl+A, Ctrl+C, Ctrl+V, Ctrl+X
                (e.keyCode === 65 && e.ctrlKey === true) ||
                (e.keyCode === 67 && e.ctrlKey === true) ||
                (e.keyCode === 86 && e.ctrlKey === true) ||
                (e.keyCode === 88 && e.ctrlKey === true) ||
                // Allow home, end
                (e.keyCode >= 35 && e.keyCode <= 39)) {
                return;
            }

            // Allow numbers (0-9) from main keyboard or numpad
            if ((e.keyCode >= 48 && e.keyCode <= 57) || (e.keyCode >= 96 && e.keyCode <= 105)) {
                return;
            }

            // Block all other keys (including letters and special characters)
            e.preventDefault();
        });

        // Enforce numeric-only input on the fly - immediately remove any characters
        $('#duration_min').on('input', function() {
            this.value = this.value.replace(/[^0-9]/g, '');
        });

        // Prevent non-numeric characters from being typed in price field - exactly like contact number field
        $('#default_price').on('keydown', function(e) {
            // Allow: backspace, delete, tab, escape, enter, and arrow keys
            if ([8, 9, 27, 13, 46, 37, 38, 39, 40].indexOf(e.keyCode) !== -1 ||
                // Allow Ctrl+A, Ctrl+C, Ctrl+V, Ctrl+X
                (e.keyCode === 65 && e.ctrlKey === true) ||
                (e.keyCode === 67 && e.ctrlKey === true) ||
                (e.keyCode === 86 && e.ctrlKey === true) ||
                (e.keyCode === 88 && e.ctrlKey === true) ||
                // Allow home, end
                (e.keyCode >= 35 && e.keyCode <= 39)) {
                return;
            }

            // Allow decimal point (period) - only if not already present
            if ((e.keyCode === 190 || e.keyCode === 110) && this.value.indexOf('.') === -1) {
                return;
            }

            // Allow numbers (0-9) from main keyboard or numpad
            if ((e.keyCode >= 48 && e.keyCode <= 57) || (e.keyCode >= 96 && e.keyCode <= 105)) {
                return;
            }

            // Block all other keys (including letters and special characters)
            e.preventDefault();
        });

        $('#default_price').on('paste', function(e) {
            // Handle paste event to filter non-numeric characters
            const $field = $(this);
            const paste = (e.originalEvent || e).clipboardData.getData('text');
            const cleanedPaste = paste.replace(/[^0-9.]/g, '');

            // Check if paste contains valid numeric characters
            if (paste !== cleanedPaste) {
                e.preventDefault();
                const currentValue = this.value;
                const parts = cleanedPaste.split('.');
                let finalValue = parts.length > 1 ? parts[0] + '.' + parts.slice(1).join('') :
                    cleanedPaste;

                // Combine with current value at cursor position
                const start = this.selectionStart;
                const end = this.selectionEnd;
                const newValue = currentValue.substring(0, start) + finalValue + currentValue
                    .substring(end);

                // Ensure only one decimal point in final value
                const newParts = newValue.split('.');
                if (newParts.length > 2) {
                    finalValue = newParts[0] + '.' + newParts.slice(1).join('');
                } else {
                    finalValue = newValue;
                }

                this.value = finalValue;

                // Validate after paste
                setTimeout(() => {
                    $field.valid();
                }, 10);
            }
        });

        // Enforce numeric-only input on the fly - immediately remove any characters
        $('#default_price').on('input', function() {
            // Remove all non-numeric characters except decimal point
            this.value = this.value.replace(/[^0-9.]/g, '');
            // Ensure only one decimal point
            const parts = this.value.split('.');
            if (parts.length > 2) {
                this.value = parts[0] + '.' + parts.slice(1).join('');
            }
        });

        // Validate on blur to show error immediately
        $('#default_price').on('blur', function() {
            const $field = $(this);
            if ($field.val()) {
                $field.valid();
            }
        });
    });

    // Clear errors instantly when user types in form fields
    function clearFieldErrors(fieldId) {
        const $field = $('#' + fieldId);
        const $form = $('#service-form');
        const validator = $form.validate();

        if ($field.length && validator) {
            // Remove all error classes
            $field.removeClass('is-invalid');

            // For Select2 fields, clear from Select2 container first
            if ($field.hasClass('select2-hidden-accessible')) {
                const $sel2 = $field.next('.select2');
                if ($sel2.length) {
                    $sel2.removeClass('is-invalid');
                    // Remove all validation error messages after Select2
                    $sel2.next('.validation-error').remove();
                    $sel2.next('.validation-error.mt-1').remove();
                    $sel2.nextAll('.validation-error').remove();
                    $sel2.nextAll('.validation-error.mt-1').remove();
                    // Also check inside Select2 container
                    $sel2.find('.validation-error').remove();
                }
                // Also check for Select2 container
                $field.next('.select2-container').removeClass('is-invalid');
                $field.next('.select2-container').find('.validation-error').remove();
            }

            // Remove all error messages around the field (siblings and next elements)
            $field.siblings('.validation-error').remove();
            $field.siblings('.validation-error.mt-1').remove();
            $field.next('.validation-error').remove();
            $field.next('.validation-error.mt-1').remove();
            $field.nextAll('.validation-error').remove();
            $field.nextAll('.validation-error.mt-1').remove();

            // Clear from validator - this is the most important part
            validator.hideErrors($field);

            // Also manually remove error messages from the form (catch-all)
            $form.find('#' + fieldId).next('.validation-error').remove();
            $form.find('#' + fieldId).next('.validation-error.mt-1').remove();
            $form.find('#' + fieldId).nextAll('.validation-error').remove();
            $form.find('#' + fieldId).nextAll('.validation-error.mt-1').remove();
        }
    }

    // Use event delegation to handle dynamically loaded forms
    $(document).ready(function() {
        // Clear errors for name field - clear immediately when typing
        $(document).on('input keyup', '#name', function() {
            if ($(this).val() && $(this).val().trim() !== '') {
                clearFieldErrors('name');
            }
        });

        // Clear errors for duration field
        $(document).on('input keyup', '#duration_min', function() {
            if ($(this).val() && $(this).val().trim() !== '') {
                clearFieldErrors('duration_min');
            }
        });

        // Clear errors for price field
        $(document).on('input keyup', '#default_price', function() {
            if ($(this).val() && $(this).val().trim() !== '') {
                clearFieldErrors('default_price');
            }
        });

        // Clear errors for category field - clear immediately when selected
        $(document).on('change select2:select', '#category_id', function() {
            if ($(this).val()) {
                clearFieldErrors('category_id');
            }
        });
    });

    // Clear errors for fields that already have values when offcanvas is shown
    // This will be handled in the existing shown.bs.offcanvas listener below

    // Initialize subcategory on page load
    document.addEventListener('DOMContentLoaded', function() {
        const categorySelect = $('#category_id');
        const subCategoryGroup = document.getElementById('sub-category-group');
        const subCategorySelect = $('#sub_category_id');

        // Always show the sub category group
        if (subCategoryGroup) {
            subCategoryGroup.style.display = 'block';
        }

        // Helper to populate subcategories
        function populateAllSubcategories() {
            fetch(`{{ route('backend.services.get_subcategories') }}`)
                .then(response => response.json())
                .then(data => {
                    subCategorySelect.empty();
                    subCategorySelect.append(
                        '<option value="">{{ __('service.select_subcategory') }}</option>');
                    if (data.length > 0) {
                        data.forEach(subcategory => {
                            subCategorySelect.append(
                                `<option value="${subcategory.id}">${subcategory.name}</option>`
                            );
                        });
                    }
                    // Update Select2
                    subCategorySelect.trigger('change');
                });
        }

        if (categorySelect.length && categorySelect.val()) {
            changeCategory(categorySelect[0]);
        } else {
            // Load all subcategories if no category is selected
            populateAllSubcategories();
        }

        // Re-initialize Select2 when offcanvas is shown (backup initialization)
        const offcanvasEl = document.getElementById('form-offcanvas');
        if (offcanvasEl) {
            offcanvasEl.addEventListener('shown.bs.offcanvas', function() {
                // Clear all validation errors when form opens
                const $form = $('#service-form');
                if ($form.length && $form.data('validator')) {
                    const validator = $form.validate();
                    if (validator) {
                        validator.resetForm();
                    }
                }
                // Clear all error messages and classes
                $form.find('.is-invalid').removeClass('is-invalid');
                $form.find('.validation-error').remove();
                $form.find('.validation-error.mt-1').remove();
                // Clear Select2 error styling
                $form.find('.select2').removeClass('is-invalid');
                $form.find('.select2-container').removeClass('is-invalid');

                // Clear errors for fields that already have values
                setTimeout(function() {
                    if ($('#name').val() && $('#name').val().trim() !== '') {
                        clearFieldErrors('name');
                    }
                    if ($('#duration_min').val() && $('#duration_min').val().trim() !==
                        '') {
                        clearFieldErrors('duration_min');
                    }
                    if ($('#default_price').val() && $('#default_price').val().trim() !==
                        '') {
                        clearFieldErrors('default_price');
                    }
                    if ($('#category_id').val()) {
                        clearFieldErrors('category_id');
                    }
                }, 100);

                setTimeout(function() {
                    // Use global function if available, otherwise use local
                    if (typeof window.initializeServiceSelect2 === 'function') {
                        window.initializeServiceSelect2();
                    } else if (typeof initializeServiceSelect2 === 'function') {
                        initializeServiceSelect2();
                    }

                    // Re-initialize image preview handler to ensure it works in edit mode
                    const featureImageInput = document.getElementById('feature_image');
                    if (featureImageInput && typeof previewImage === 'function') {
                        // Ensure event listener is attached (will work alongside inline onchange)
                        // Remove old listener first to avoid duplicates
                        $(featureImageInput).off('change', previewImage);
                        // Add event listener
                        $(featureImageInput).on('change', function(e) {
                            previewImage(e);
                        });
                    }

                    // Re-initialize numeric input handlers for duration and price fields
                    const durationField = document.getElementById('duration_min');
                    const priceField = document.getElementById('default_price');

                    if (durationField) {
                        // Remove old handlers and re-attach to ensure they work
                        $(durationField).off('keydown input').on('keydown', function(e) {
                            // Allow: backspace, delete, tab, escape, enter, and arrow keys
                            if ([8, 9, 27, 13, 46, 37, 38, 39, 40].indexOf(e
                                    .keyCode) !== -1 ||
                                (e.keyCode === 65 && e.ctrlKey === true) ||
                                (e.keyCode === 67 && e.ctrlKey === true) ||
                                (e.keyCode === 86 && e.ctrlKey === true) ||
                                (e.keyCode === 88 && e.ctrlKey === true) ||
                                (e.keyCode >= 35 && e.keyCode <= 39)) {
                                return;
                            }
                            if ((e.keyCode >= 48 && e.keyCode <= 57) || (e
                                    .keyCode >= 96 && e.keyCode <= 105)) {
                                return;
                            }
                            e.preventDefault();
                        }).on('input', function() {
                            this.value = this.value.replace(/[^0-9]/g, '');
                        });
                    }

                    if (priceField) {
                        // Remove old handlers and re-attach to ensure they work
                        $(priceField).off('keydown input paste').on('keydown', function(e) {
                            // Allow: backspace, delete, tab, escape, enter, and arrow keys
                            if ([8, 9, 27, 13, 46, 37, 38, 39, 40].indexOf(e
                                    .keyCode) !== -1 ||
                                (e.keyCode === 65 && e.ctrlKey === true) ||
                                (e.keyCode === 67 && e.ctrlKey === true) ||
                                (e.keyCode === 86 && e.ctrlKey === true) ||
                                (e.keyCode === 88 && e.ctrlKey === true) ||
                                (e.keyCode >= 35 && e.keyCode <= 39)) {
                                return;
                            }
                            if ((e.keyCode === 190 || e.keyCode === 110) && this
                                .value.indexOf('.') === -1) {
                                return;
                            }
                            if ((e.keyCode >= 48 && e.keyCode <= 57) || (e
                                    .keyCode >= 96 && e.keyCode <= 105)) {
                                return;
                            }
                            e.preventDefault();
                        }).on('input', function() {
                            this.value = this.value.replace(/[^0-9.]/g, '');
                            const parts = this.value.split('.');
                            if (parts.length > 2) {
                                this.value = parts[0] + '.' + parts.slice(1).join(
                                    '');
                            }
                        }).on('paste', function(e) {
                            e.preventDefault();
                            const paste = (e.originalEvent || e).clipboardData
                                .getData('text');
                            const cleanedPaste = paste.replace(/[^0-9.]/g, '');
                            const parts = cleanedPaste.split('.');
                            let finalValue = parts.length > 1 ? parts[0] + '.' +
                                parts.slice(1).join('') : cleanedPaste;
                            const newParts = finalValue.split('.');
                            if (newParts.length > 2) {
                                finalValue = newParts[0] + '.' + newParts.slice(1)
                                    .join('');
                            }
                            const start = this.selectionStart;
                            const end = this.selectionEnd;
                            this.value = this.value.substring(0, start) +
                                finalValue + this.value.substring(end);
                        });
                    }
                }, 200);
            });
        }
    });
</script>
