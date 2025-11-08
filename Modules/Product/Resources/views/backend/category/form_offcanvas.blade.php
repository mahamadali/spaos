<form id="category-form" enctype="multipart/form-data" action="{{ route('backend.products-categories.store') }}"
    novalidate>
    @csrf
    <input type="hidden" name="id" id="category_id">
    <div class="offcanvas offcanvas-end" tabindex="-1" id="form-offcanvas" aria-labelledby="form-offcanvasLabel">
        <div class="offcanvas-header">
            <h5 class="offcanvas-title" id="form-offcanvasLabel">
                <span id="category-form-title">
                    @if (!empty($isSubCategory))
                        {{ __('messages.new') }} {{ __('category.sub_category') }}@else{{ __('messages.new') }}
                        {{ __('category.singular_title') }}
                    @endif
                </span>
            </h5>
            <button type="button" class="btn-close" data-bs-dismiss="offcanvas" aria-label="Close"></button>
        </div>

        <div class="offcanvas-body">
            <div class="row">
                <div class="col-12">
                    <div class="form-group">
                        <div class="col-md-12 text-center upload-image-box">
                            <div class="category-image-wrapper"
                                style="width: 140px; height: 140px; border-radius: 50%; overflow: hidden; margin: 0 auto 0.5rem auto; display: flex; align-items: center; justify-content: center;">
                                <img src="{{ default_feature_image() }}" alt="feature-image"
                                    class="img-fluid avatar-140 rounded" id="category-image-preview"
                                    style="margin: 0; width: 100%; height: 100%; object-fit: cover; border-radius: 0;" />
                            </div>
                            <div id="category-validation-message" class="text-danger mb-2 d-none"></div>
                            <div class="d-flex align-items-center justify-content-center gap-2">
                                <input type="file" class="form-control d-none" id="category_feature_image"
                                    name="feature_image" accept=".jpeg, .jpg, .png, .gif" />
                                <label class="btn btn-sm btn-primary"
                                    for="category_feature_image">{{ __('messages.upload') }}</label>
                                <input type="button" class="btn btn-sm btn-secondary" name="remove"
                                    value="{{ __('messages.remove') }}" id="category-remove-image"
                                    style="display: none;" />
                            </div>
                            <input type="hidden" name="remove_feature_image" id="category_remove_feature_image"
                                value="0" />
                        </div>
                    </div>

                    <div class="form-group">
                        <label class="form-label" for="category_name">{{ __('category.lbl_name11') }} <span
                                class="text-danger">*</span></label>
                        <input type="text" class="form-control" id="category_name" name="name"
                            placeholder="{{ __('messages.enter_category_name') }}" required>
                        <div class="invalid-feedback" id="category-name-error"></div>
                    </div>

                    @if (!empty($isSubCategory))
                        <div class="form-group">
                            <label for="category-parent_id" class="form-label">{{ __('category.lbl_parent_category') }}
                                <span class="text-danger">*</span></label>
                            <select id="category-parent_id" name="parent_id" class="form-control select2"
                                style="width:100%" data-placeholder="{{ __('messages.select_category') }}">
                                <option value="">{{ __('messages.select_category') }}</option>
                                @if (!empty($categories))
                                    @foreach ($categories as $cat)
                                        <option value="{{ $cat->id }}">{{ $cat->name }}</option>
                                    @endforeach
                                @endif
                            </select>
                            <div class="invalid-feedback" id="parent_id-error"></div>
                        </div>
                    @endif

                    <div class="form-group">
                        <label for="category-brand" class="form-label">{{ __('category.lbl_parent_brand') }} <span
                                class="text-danger">*</span></label>
                        <select id="category-brand" name="brand_id[]" class="form-control select2" multiple
                            style="width:100%" data-placeholder="{{ __('product.select_brand') }}"></select>
                        <div class="invalid-feedback" id="category-brand-error"></div>
                    </div>

                    <div class="form-group">
                        <div class="d-flex justify-content-between align-items-center form-control">
                            <label class="form-label mb-0"
                                for="category-status">{{ __('category.lbl_status') }}</label>
                            <div class="form-check form-switch">
                                <input type="hidden" name="status" value="0" />
                                <input class="form-check-input" value="1" name="status" id="category-status"
                                    type="checkbox" checked />
                            </div>
                        </div>
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
                    form="category-form" id="category-submit-btn">
                    <span class="spinner-border spinner-border-sm d-none" role="status" aria-hidden="true"></span>
                    <span id="category-submit-text">{{ __('messages.save') }}</span>
                </button>
            </div>
        </div>
    </div>
</form>

<style>
    /* Ensure validation error messages are red/danger color */
    #category-name-error,
    #parent_id-error,
    #category-brand-error {
        color: #dc3545 !important;
    }

    .invalid-feedback.text-danger {
        color: #dc3545 !important;
    }
</style>

<script>
    // Immediate console log - executes when script loads
    console.log('📝 Category Form Script - Script block loaded and executing');

    (function() {
        console.log('📝 Category Form Script - IIFE starting...');

        function initializeCategoryForm() {
            console.log('🔄 Category Form - Initializing...');

            const form = document.getElementById('category-form');
            const imageInput = document.getElementById('category_feature_image');
            const imagePreview = document.getElementById('category-image-preview');
            const removeImageBtn = document.getElementById('category-remove-image');
            const validationMessage = document.getElementById('category-validation-message');
            const submitBtn = document.getElementById('category-submit-btn');
            const submitText = document.getElementById('category-submit-text');
            const spinner = submitBtn.querySelector('.spinner-border');
            const brandSelect = $('#category-brand');
            const parentSelect = $('#category-parent_id');
            const isSubCategory = {!! json_encode(!empty($isSubCategory)) !!};
            let hasTriedSubmit = false; // show validation messages only after first submit attempt

            let currentImage = null;
            let isEdit = false;

            // Check initial setup
            console.log('🔍 Category Form - Initial setup check:');
            console.log('   Form found:', !!form);
            console.log('   Image preview found:', !!imagePreview);

            if (imagePreview) {
                const wrapper = imagePreview.closest('.category-image-wrapper');
                if (wrapper) {
                    console.log('✅ Category Form - Wrapper found on init');
                    const wrapperStyles = window.getComputedStyle(wrapper);
                    console.log('   Wrapper width:', wrapperStyles.width, 'height:', wrapperStyles.height);
                    console.log('   Wrapper border-radius:', wrapperStyles.borderRadius);
                    console.log('   Wrapper overflow:', wrapperStyles.overflow);
                    console.log('   Wrapper display:', wrapperStyles.display);
                } else {
                    console.error('❌ Category Form - Wrapper NOT found on init!');
                    console.log('   Image parent:', imagePreview.parentElement);
                }

                const imgStyles = window.getComputedStyle(imagePreview);
                console.log('   Image initial styles:');
                console.log('   - Width:', imgStyles.width, 'Height:', imgStyles.height);
                console.log('   - Object-fit:', imgStyles.objectFit);
                console.log('   - Border-radius:', imgStyles.borderRadius);
            }

            // Track ongoing brand fetch requests to prevent duplicates
            let brandFetchController = null;
            let brandFetchTimeout = null;
            let isBrandLoading = false;

            // Initialize Select2 for brands and load options
            function initBrandSelect(selectedIds = [], parentCategoryId = '') {
                // Cancel any pending fetch
                if (brandFetchController) {
                    brandFetchController.abort();
                    brandFetchController = null;
                }
                if (brandFetchTimeout) {
                    clearTimeout(brandFetchTimeout);
                    brandFetchTimeout = null;
                }

                // Debounce: delay execution to prevent rapid successive calls
                brandFetchTimeout = setTimeout(() => {
                    _initBrandSelect(selectedIds, parentCategoryId);
                }, 150);
            }

            function _initBrandSelect(selectedIds = [], parentCategoryId = '') {
                // Prevent multiple simultaneous loads
                if (isBrandLoading) {
                    return;
                }

                // Ensure jQuery and Select2 are available
                if (typeof window.$ === 'undefined' || typeof window.$.fn === 'undefined' || typeof window.$.fn
                    .select2 === 'undefined') {
                    // Wait for jQuery and Select2 to be available
                    setTimeout(() => _initBrandSelect(selectedIds, parentCategoryId), 100);
                    return;
                }

                // Initialize Select2 only if not already initialized
                if (!brandSelect.hasClass('select2-hidden-accessible')) {
                    brandSelect.select2({
                        width: '100%',
                        placeholder: $('#category-brand').data('placeholder') ||
                            '{{ __('product.select_brand') }}',
                        allowClear: true,
                        dropdownParent: $('#form-offcanvas').length ? $('#form-offcanvas') : $('body')
                    });
                } else {
                    // Disable Select2 while loading to prevent interaction
                    brandSelect.prop('disabled', true);
                    brandSelect.trigger('change.select2');
                }

                isBrandLoading = true;

                // Build URL; when sub-category and parent selected, filter brands by parent category
                const baseUrl = '{{ route('backend.brands.index_list') }}';
                const url = (isSubCategory && parentCategoryId) ?
                    `${baseUrl}?category_id=${encodeURIComponent(parentCategoryId)}` : baseUrl;

                // Create abort controller for this request
                brandFetchController = new AbortController();

                // Load brands
                fetch(url, {
                        signal: brandFetchController.signal
                    })
                    .then(r => {
                        if (!r.ok) throw new Error('Network response was not ok');
                        return r.json();
                    })
                    .then(list => {
                        brandFetchController = null;
                        isBrandLoading = false;

                        // Re-enable Select2
                        brandSelect.prop('disabled', false);

                        // Double-check Select2 is initialized after fetch (in case it took longer)
                        if (!brandSelect.hasClass('select2-hidden-accessible')) {
                            if (typeof window.$ !== 'undefined' && typeof window.$.fn !== 'undefined' &&
                                typeof window.$.fn.select2 !== 'undefined') {
                                brandSelect.select2({
                                    width: '100%',
                                    placeholder: $('#category-brand').data('placeholder') ||
                                        '{{ __('product.select_brand') }}',
                                    allowClear: true,
                                    dropdownParent: $('#form-offcanvas').length ? $('#form-offcanvas') :
                                        $('body')
                                });
                            } else {
                                // If Select2 still not available, retry after short delay
                                setTimeout(() => _initBrandSelect(selectedIds, parentCategoryId), 100);
                                return;
                            }
                        }

                        // Store current selection to preserve it during reload
                        const currentSelection = brandSelect.val() || [];

                        // Clear existing options
                        brandSelect.empty();

                        // Add all brand options
                        if (list && Array.isArray(list)) {
                            list.forEach(opt => {
                                const option = new Option(opt.name, opt.id, false, false);
                                brandSelect.append(option);
                            });
                        }

                        // Restore selection: use provided selectedIds if available, otherwise keep current selection
                        const valuesToSelect = (selectedIds && selectedIds.length > 0) ? selectedIds.map(id =>
                            String(id)) : currentSelection.map(id => String(id));

                        if (valuesToSelect.length > 0) {
                            // Only select values that exist in the new options
                            const availableValues = valuesToSelect.filter(val => {
                                return brandSelect.find('option[value="' + val + '"]').length > 0;
                            });
                            if (availableValues.length > 0) {
                                brandSelect.val(availableValues).trigger('change.select2');
                            } else {
                                brandSelect.val(null).trigger('change.select2');
                            }
                        } else {
                            // Just trigger update if no selection needed
                            brandSelect.trigger('change.select2');
                        }
                    })
                    .catch(error => {
                        brandFetchController = null;
                        isBrandLoading = false;
                        // Re-enable Select2 on error
                        brandSelect.prop('disabled', false);
                        brandSelect.trigger('change.select2');

                        if (error.name !== 'AbortError') {
                            console.error('Error loading brands:', error);
                        }
                    });
            }

            function initParentSelect() {
                if (!isSubCategory) return;
                // Ensure jQuery and Select2 are available
                if (typeof window.$ === 'undefined' || typeof window.$.fn === 'undefined' || typeof window.$.fn
                    .select2 === 'undefined') {
                    setTimeout(() => initParentSelect(), 100);
                    return;
                }
                if (parentSelect.length && !parentSelect.hasClass('select2-hidden-accessible')) {
                    parentSelect.select2({
                        width: '100%',
                        placeholder: $('#category-parent_id').data('placeholder') ||
                            '{{ __('messages.select_category') }}',
                        allowClear: true,
                        dropdownParent: $('#form-offcanvas').length ? $('#form-offcanvas') : $('body')
                    });
                }
            }

            // File upload handling
            imageInput.addEventListener('change', function(e) {
                const file = e.target.files[0];
                const maxSizeInMB = 2;
                const maxSizeInBytes = maxSizeInMB * 1024 * 1024;

                if (file) {
                    console.log('🖼️ Category Form - File selected:', file.name, file.size);

                    if (file.size > maxSizeInBytes) {
                        showValidationMessage(
                            `File size exceeds ${maxSizeInMB} MB. Please upload a smaller file.`);
                        imageInput.value = '';
                        return;
                    }

                    const reader = new FileReader();
                    reader.onload = function(e) {
                        console.log('🖼️ Category Form - FileReader loaded, setting image src...');
                        imagePreview.src = e.target.result;

                        // Verify wrapper exists
                        const wrapper = imagePreview.closest('.category-image-wrapper');
                        if (wrapper) {
                            console.log('✅ Category Form - Wrapper found');
                            const wrapperStyles = window.getComputedStyle(wrapper);
                            console.log('   Wrapper width:', wrapperStyles.width, 'height:',
                                wrapperStyles.height);
                            console.log('   Wrapper border-radius:', wrapperStyles.borderRadius);
                            console.log('   Wrapper overflow:', wrapperStyles.overflow);
                        } else {
                            console.error('❌ Category Form - Wrapper NOT found!');
                        }

                        // Check image styles after load
                        imagePreview.onload = function() {
                            console.log('🖼️ Category Form - Image loaded, checking styles...');
                            const imgStyles = window.getComputedStyle(imagePreview);
                            console.log('   Image width:', imgStyles.width, 'height:', imgStyles
                                .height);
                            console.log('   Image object-fit:', imgStyles.objectFit);
                            console.log('   Image border-radius:', imgStyles.borderRadius);
                            console.log('   Image actual size:', imagePreview.offsetWidth, 'x',
                                imagePreview.offsetHeight);

                            // Verify wrapper clipping
                            if (wrapper) {
                                console.log('   Wrapper actual size:', wrapper.offsetWidth, 'x',
                                    wrapper.offsetHeight);
                                console.log('   Wrapper border-radius computed:', window
                                    .getComputedStyle(wrapper).borderRadius);
                            }
                        };

                        currentImage = file;
                        removeImageBtn.style.display = 'inline-block';
                        document.getElementById('category_remove_feature_image').value = '0';
                        hideValidationMessage();
                    };
                    reader.readAsDataURL(file);
                } else {
                    console.log('🖼️ Category Form - No file selected');
                }
            });

            // Remove image
            removeImageBtn.addEventListener('click', function() {
                imagePreview.src = '{{ default_feature_image() }}';
                imageInput.value = '';
                currentImage = null;
                removeImageBtn.style.display = 'none';
                document.getElementById('category_remove_feature_image').value = '1';
                hideValidationMessage();
            });

            // jQuery Validate - inline messages like other forms
            if (window.$ && $.fn && $.fn.validate) {
                const errorMap = {
                    name: 'category-name-error',
                    'brand_id[]': 'category-brand-error',
                    parent_id: 'parent_id-error'
                };
                const categoryValidator = $('#category-form').validate({
                    ignore: [],
                    onkeyup: false,
                    onfocusout: false,
                    rules: {
                        name: {
                            required: true,
                            normalizer: function(value) {
                                return $.trim(value);
                            }
                        },
                        'brand_id[]': {
                            required: true
                        },
                        @if (!empty($isSubCategory))
                            parent_id: {
                                required: true
                            }
                        @endif
                    },
                    messages: {
                        name: {
                            required: 'Name is a required field'
                        },
                        'brand_id[]': {
                            required: 'At least one brand must be selected'
                        },
                        @if (!empty($isSubCategory))
                            parent_id: {
                                required: 'Category is a required field'
                            }
                        @endif
                    },
                    errorPlacement: function(error, element) {
                        const name = element.attr('name');
                        const id = errorMap[name];
                        if (id) {
                            $('#' + id).text(error.text()).addClass('d-block').removeClass('text-muted')
                                .addClass('text-danger');
                        } else {
                            error.insertAfter(element);
                        }
                    },
                    highlight: function(el) {
                        $(el).addClass('is-invalid');
                        if ($(el).hasClass('select2-hidden-accessible')) $(el).next(
                            '.select2-container').find('.select2-selection').addClass('is-invalid');
                    },
                    unhighlight: function(el) {
                        $(el).removeClass('is-invalid');
                        if ($(el).hasClass('select2-hidden-accessible')) $(el).next(
                            '.select2-container').find('.select2-selection').removeClass(
                            'is-invalid');
                        const name = $(el).attr('name');
                        const id = errorMap[name];
                        if (id) {
                            $('#' + id).text('').removeClass('d-block text-danger');
                        }
                    }
                });
                // revalidate on select2 change
                $('#category-brand').on('change.select2 change', function() {
                    if (hasTriedSubmit) {
                        $(this).valid();
                    }
                });
                @if (!empty($isSubCategory))
                    $('#category-parent_id').on('change.select2 change', function() {
                        if (hasTriedSubmit) {
                            $(this).valid();
                        }
                    });
                @endif
            }

            // Form submission
            let isSubmitting = false; // Prevent duplicate submissions
            form.addEventListener('submit', function(e) {
                e.preventDefault();
                if (submitBtn.disabled || isSubmitting) return;

                if (window.$ && $.fn && $.fn.validate) {
                    const $form = $('#category-form');
                    hasTriedSubmit = true;
                    if (!$form.valid()) {
                        // ensure first invalid is focused and name error shows
                        $form.validate().focusInvalid();
                        return;
                    }
                } else {
                    if (!validateForm()) return;
                }

                isSubmitting = true;
                setLoadingState(true);

                const formData = new FormData(form);
                const idField = document.getElementById('category_id');
                const id = idField ? String(idField.value).trim() : '';

                // Determine if this is an edit or create operation - check ID value directly
                const isEditMode = id !== '' && !isNaN(parseInt(id)) && parseInt(id) > 0;

                let url;
                if (isEditMode) {
                    url = '{{ route('backend.products-categories.update', ':id') }}'.replace(':id', id);
                    form.action = url;
                    formData.set('_method', 'PUT'); // Use set instead of append to replace if exists
                    console.log('🔧 Category Form - Submitting UPDATE for ID:', id);
                } else {
                    url = '{{ route('backend.products-categories.store') }}';
                    form.action = url;
                    // Remove _method if it exists to ensure it's a POST request
                    if (formData.has('_method')) {
                        formData.delete('_method');
                    }
                    console.log('🔧 Category Form - Submitting CREATE (ID is empty or invalid)');
                }

                const xhr = new XMLHttpRequest();
                xhr.open('POST', url, true);
                xhr.setRequestHeader('X-CSRF-TOKEN', document.querySelector('meta[name="csrf-token"]')
                    .getAttribute('content'));

                xhr.onload = function() {
                    if (xhr.status >= 200 && xhr.status < 300) {
                        try {
                            const data = JSON.parse(xhr.responseText);
                            if (data.status) {
                                window.successSnackbar(data.message);
                                if (typeof renderedDataTable !== 'undefined') {
                                    renderedDataTable.ajax.reload(null, false);
                                }
                                const ocEl = document.getElementById('form-offcanvas');
                                const instance = bootstrap.Offcanvas.getOrCreateInstance(ocEl);
                                instance.hide();
                                // Reset isEdit flag and form before hiding
                                isEdit = false;
                                document.getElementById('category_id').value = '';
                                resetForm();
                            } else {
                                window.errorSnackbar(data.message);
                                if (data.all_message) {
                                    displayErrors(data.all_message);
                                }
                            }
                        } catch (e) {
                            window.errorSnackbar('An error occurred while processing your request.');
                        }
                    } else {
                        window.errorSnackbar('An error occurred while processing your request.');
                    }
                    isSubmitting = false;
                    setLoadingState(false);
                };

                xhr.onerror = function() {
                    window.errorSnackbar('An error occurred while processing your request.');
                    isSubmitting = false;
                    setLoadingState(false);
                };

                xhr.send(formData);
            });

            // Validation
            function validateForm() {
                let ok = true;
                clearErrors();
                const name = document.getElementById('category_name').value.trim();
                const brandValues = $('#category-brand').val() || [];
                const parentVal = isSubCategory ? (parentSelect.val() || '') : '';
                if (!name) {
                    showFieldError('category_name', 'Name is a required field');
                    ok = false;
                }
                if (brandValues.length === 0) {
                    showFieldError('category-brand', 'Brand is a required field');
                    ok = false;
                }
                if (isSubCategory && !parentVal) {
                    showFieldError('category-parent_id', 'Category is a required field');
                    ok = false;
                }
                return ok;
            }

            function showFieldError(fieldId, message) {
                const field = document.getElementById(fieldId);
                let errorId = fieldId.replace('category_', '') + '-error';
                if (fieldId === 'category-parent_id') {
                    errorId = 'parent_id-error';
                }
                const errorDiv = document.getElementById(errorId);
                if (field && errorDiv) {
                    field.classList.add('is-invalid');
                    errorDiv.textContent = message;
                    errorDiv.classList.add('d-block', 'text-danger');
                    errorDiv.classList.remove('text-muted');
                }
            }

            function clearErrors() {
                document.querySelectorAll('.is-invalid').forEach(f => f.classList.remove('is-invalid'));
                document.querySelectorAll('.invalid-feedback').forEach(e => e.textContent = '');
            }

            function displayErrors(errors) {
                Object.keys(errors).forEach(field => {
                    let fieldId = field;
                    if (field === 'name') fieldId = 'category_name';
                    else if (field === 'brand_id') fieldId = 'category-brand';
                    else if (field === 'parent_id') fieldId = 'category-parent_id';
                    showFieldError(fieldId, errors[field][0]);
                });
            }

            function setLoadingState(loading) {
                submitBtn.disabled = loading;
                if (loading) {
                    spinner.classList.remove('d-none');
                    submitText.textContent = '{{ __('messages.saving') }}';
                } else {
                    spinner.classList.add('d-none');
                    submitText.textContent = '{{ __('messages.save') }}';
                }
            }

            function showValidationMessage(message) {
                validationMessage.textContent = message;
                validationMessage.classList.remove('d-none');
            }

            function hideValidationMessage() {
                validationMessage.classList.add('d-none');
                validationMessage.textContent = '';
            }

            function resetForm() {
                form.reset();
                imagePreview.src = '{{ default_feature_image() }}';
                imageInput.value = '';
                currentImage = null;
                removeImageBtn.style.display = 'none';
                document.getElementById('category_remove_feature_image').value = '0';
                clearErrors();
                hideValidationMessage();
                hasTriedSubmit = false;
                isEdit = false;
                document.getElementById('category_id').value = '';
                document.getElementById('category-form-title').textContent = isSubCategory ?
                    '{{ __('messages.new') }} {{ __('category.sub_category') }}' :
                    '{{ __('messages.new') }} {{ __('category.singular_title') }}';
                form.action = '{{ route('backend.products-categories.store') }}';
                // Reset brands
                initBrandSelect([]);
                // Reset parent category
                if (isSubCategory && parentSelect.length) {
                    parentSelect.val('').trigger('change');
                }
            }

            // Expose edit function
            window.editCategory = function(categoryData) {
                console.log('🖼️ Category Form - editCategory called, loading image:', categoryData
                    .feature_image);
                isEdit = true;
                document.getElementById('category_id').value = categoryData.id;
                document.getElementById('category_name').value = categoryData.name || '';
                document.getElementById('category-status').checked = categoryData.status == 1;

                if (categoryData.feature_image && categoryData.feature_image !==
                    '{{ default_feature_image() }}') {
                    imagePreview.src = categoryData.feature_image;

                    // Check wrapper on edit image load
                    const wrapper = imagePreview.closest('.category-image-wrapper');
                    imagePreview.onload = function() {
                        console.log('🖼️ Category Form - Edit image loaded');
                        if (wrapper) {
                            console.log('✅ Category Form - Wrapper found for edit image');
                            const wrapperStyles = window.getComputedStyle(wrapper);
                            console.log('   Wrapper border-radius:', wrapperStyles.borderRadius);
                            console.log('   Wrapper overflow:', wrapperStyles.overflow);
                        }
                        const imgStyles = window.getComputedStyle(imagePreview);
                        console.log('   Image styles - width:', imgStyles.width, 'height:', imgStyles
                            .height);
                        console.log('   Image object-fit:', imgStyles.objectFit);
                    };

                    currentImage = categoryData.feature_image;
                    removeImageBtn.style.display = 'inline-block';
                    document.getElementById('category_remove_feature_image').value = '0';
                } else {
                    console.log('🖼️ Category Form - Using default image for edit');
                    imagePreview.src = '{{ default_feature_image() }}';
                    currentImage = null;
                    removeImageBtn.style.display = 'none';
                    document.getElementById('category_remove_feature_image').value = '0';
                }
                document.getElementById('category-form-title').textContent = isSubCategory ?
                    '{{ __('messages.edit') }} {{ __('category.sub_category') }}' :
                    '{{ __('messages.edit') }} {{ __('category.singular_title') }}';

                const offcanvas = bootstrap.Offcanvas.getOrCreateInstance(document.getElementById(
                    'form-offcanvas'));
                offcanvas.show();

                // Wait for offcanvas to be shown before setting Select2 values
                const offcanvasEl = document.getElementById('form-offcanvas');
                const setCategoryValues = () => {
                    console.log('🔧 Setting category values, parent_id:', categoryData.parent_id,
                        'brand_id:', categoryData.brand_id);
                    // Set flag to prevent parent change event from reloading brands
                    isSettingEditValues = true;

                    // For sub-category: Set parent category first, then load brands
                    if (isSubCategory && parentSelect.length) {
                        const pid = categoryData.parent_id || '';
                        console.log('🔧 Sub-category mode, parent ID:', pid);
                        if (pid) {
                            // Ensure Select2 is initialized first
                            initParentSelect();

                            // Use setTimeout to ensure Select2 is fully initialized before setting value
                            setTimeout(() => {
                                // Check if option exists, if not add it
                                const existingOption = parentSelect.find('option[value="' + pid +
                                    '"]');
                                console.log('🔧 Existing option found:', existingOption.length > 0);
                                if (existingOption.length === 0) {
                                    // Try to find category name from data (category_name is set from mainCategory->name in controller)
                                    const categoryName = categoryData.category_name || categoryData
                                        .mainCategory?.name || categoryData.parent?.name ||
                                        'Selected category';
                                    console.log('🔧 Adding missing option:', categoryName, pid);
                                    parentSelect.append(new Option(categoryName, pid, true, true));
                                }

                                // Set parent category value WITHOUT triggering change event to avoid brand reload
                                console.log('🔧 Setting parent select value to:', String(pid));
                                parentSelect.val(String(pid));

                                // Update Select2 display without triggering change events
                                if (parentSelect.hasClass('select2-hidden-accessible')) {
                                    console.log('🔧 Select2 is initialized, updating display');
                                    // Only trigger select2 update, not regular change event
                                    parentSelect.trigger('change.select2');
                                } else {
                                    console.log(
                                        '🔧 Select2 not initialized yet, initializing now...');
                                    initParentSelect();
                                    setTimeout(() => {
                                        parentSelect.val(String(pid));
                                        if (parentSelect.hasClass(
                                                'select2-hidden-accessible')) {
                                            parentSelect.trigger('change.select2');
                                        }
                                    }, 50);
                                }

                                // After parent is set, load brands filtered by parent category
                                // Load brands once, without delay
                                const selectedBrandIds = (categoryData.brand_id || []).map(Number);
                                console.log('🔧 Loading brands with IDs:', selectedBrandIds,
                                    'for parent:', pid);
                                initBrandSelect(selectedBrandIds, pid);

                                // Reset flag after brands are loaded
                                setTimeout(() => {
                                    isSettingEditValues = false;
                                }, 100);
                            }, 200);
                        } else {
                            // No parent category, load all brands immediately
                            console.log('🔧 No parent category, loading all brands');
                            const selectedBrandIds = (categoryData.brand_id || []).map(Number);
                            initBrandSelect(selectedBrandIds, '');
                            isSettingEditValues = false;
                        }
                    } else {
                        // Not a sub-category, load brands immediately
                        console.log('🔧 Not a sub-category, loading brands immediately');
                        const selectedBrandIds = (categoryData.brand_id || []).map(Number);
                        initBrandSelect(selectedBrandIds, '');
                        isSettingEditValues = false;
                    }
                };

                if (offcanvasEl.classList.contains('show')) {
                    // Offcanvas is already shown
                    console.log('🔧 Offcanvas already shown, setting values immediately');
                    setCategoryValues();
                } else {
                    // Wait for offcanvas to be shown
                    console.log('🔧 Offcanvas not shown yet, waiting for shown event');
                    offcanvasEl.addEventListener('shown.bs.offcanvas', function handler() {
                        console.log('🔧 Offcanvas shown event fired');
                        offcanvasEl.removeEventListener('shown.bs.offcanvas', handler);
                        setCategoryValues();
                    }, {
                        once: true
                    });
                }
            }

            document.getElementById('form-offcanvas').addEventListener('hidden.bs.offcanvas', function() {
                resetForm();
            });

            // Listen for ID changes (triggered from index) and load edit data
            document.addEventListener('crud_change_id', function(e) {
                const id = Number(e.detail.form_id || 0);
                const parentIdPayload = (typeof e.detail.parent_id !== 'undefined') ? e.detail.parent_id :
                    '';
                if (id > 0) {
                    fetch('{{ route('backend.products-categories.edit', ':id') }}'.replace(':id', id))
                        .then(r => r.json())
                        .then(res => {
                            if (res.status) {
                                window.editCategory(res.data);
                            } else {
                                window.errorSnackbar(res.message || 'Error loading category data');
                            }
                        })
                        .catch(() => window.errorSnackbar('An error occurred while loading category data'));
                } else {
                    resetForm();
                    if (isSubCategory && parentSelect.length && parentIdPayload && Number(parentIdPayload) >
                        0) {
                        if (parentSelect.find('option[value="' + parentIdPayload + '"]').length === 0) {
                            parentSelect.append(new Option('Selected category', parentIdPayload, true,
                                true));
                        }
                        parentSelect.val(String(parentIdPayload)).trigger('change');
                    }
                }
            });

            // Cancel and X should always close
            document.querySelectorAll('#form-offcanvas [data-bs-dismiss="offcanvas"]').forEach(function(el) {
                el.addEventListener('click', function(ev) {
                    ev.preventDefault();
                    const ocEl = document.getElementById('form-offcanvas');
                    const instance = bootstrap.Offcanvas.getOrCreateInstance(ocEl);
                    instance.hide();
                });
            });

            // When parent category changes (only in sub-category form), reload brand list filtered by selection
            // Only reload if we're not in edit mode (to avoid clearing selected brands during edit)
            let isSettingEditValues = false; // Flag to prevent brand reload during edit setup
            if (isSubCategory && parentSelect.length) {
                parentSelect.on('change', function() {
                    const pid = $(this).val() || '';
                    // Only clear and reload brands if we're not currently in edit mode or setting edit values
                    // The editCategory function will handle setting brands when editing
                    if (!isEdit && !isSettingEditValues) {
                        // Clear current brand selection before reloading
                        brandSelect.val(null).trigger('change.select2');
                        // Use debounced initBrandSelect to prevent rapid successive reloads
                        initBrandSelect([], pid);
                    }
                });
            }

            // Initialize defaults
            initBrandSelect([], '');
            initParentSelect();
            resetForm();

            console.log('✅ Category Form - Initialization complete');
        }

        // Try immediate initialization
        console.log('📝 Category Form - Attempting immediate initialization...');
        if (document.getElementById('category-form')) {
            console.log('📝 Category Form - Form found immediately, initializing...');
            initializeCategoryForm();
        } else {
            console.log('📝 Category Form - Form not found, will try on DOMContentLoaded or offcanvas shown...');
        }

        // Also try on DOMContentLoaded
        if (document.readyState === 'loading') {
            document.addEventListener('DOMContentLoaded', function() {
                console.log('📝 Category Form - DOMContentLoaded fired');
                if (document.getElementById('category-form')) {
                    initializeCategoryForm();
                }
            });
        } else {
            console.log('📝 Category Form - DOM already loaded, trying initialization...');
            setTimeout(function() {
                if (document.getElementById('category-form')) {
                    console.log('📝 Category Form - Form found after delay, initializing...');
                    initializeCategoryForm();
                }
            }, 100);
        }

        // Listen for offcanvas shown event (for dynamic loading)
        const offcanvasEl = document.getElementById('form-offcanvas');
        if (offcanvasEl) {
            console.log('📝 Category Form - Offcanvas element found, attaching shown event...');
            offcanvasEl.addEventListener('shown.bs.offcanvas', function() {
                console.log('📝 Category Form - Offcanvas SHOWN event fired!');
                setTimeout(function() {
                    if (document.getElementById('category-form')) {
                        console.log(
                            '📝 Category Form - Form found after offcanvas shown, initializing...'
                            );
                        initializeCategoryForm();
                    }
                }, 100);
            });
        } else {
            console.warn('⚠️ Category Form - Offcanvas element NOT found for event listener');
            // Try to find it later
            setTimeout(function() {
                const oc = document.getElementById('form-offcanvas');
                if (oc) {
                    console.log('📝 Category Form - Offcanvas found after delay, attaching shown event...');
                    oc.addEventListener('shown.bs.offcanvas', function() {
                        console.log('📝 Category Form - Offcanvas SHOWN event fired (late)!');
                        setTimeout(function() {
                            if (document.getElementById('category-form')) {
                                initializeCategoryForm();
                            }
                        }, 100);
                    });
                }
            }, 500);
        }

        console.log('📝 Category Form Script - Setup complete');
    })();
</script>
