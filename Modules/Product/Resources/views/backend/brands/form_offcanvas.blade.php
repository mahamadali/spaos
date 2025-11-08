<div class="offcanvas offcanvas-end" tabindex="-1" id="form-offcanvas" aria-labelledby="form-offcanvasLabel">
    <div class="offcanvas-header">
        <h5 class="offcanvas-title" id="form-offcanvasLabel">
            <span id="form-title">{{ __('messages.new') }} {{ __('product.brand') }}</span>
        </h5>
        <button type="button" class="btn-close text-reset" data-bs-dismiss="offcanvas" aria-label="Close"></button>
    </div>
    
    <form id="brand-form" enctype="multipart/form-data" action="{{ route('backend.brands.store') }}" novalidate class="d-flex flex-column h-100">
        @csrf
        <input type="hidden" name="id" id="brand_id">
        
        <div class="offcanvas-body flex-grow-1">
            <div class="form-group">
                <div class="col-md-12 text-center upload-image-box">
                    <div class="brand-image-wrapper" style="width: 140px; height: 140px; border-radius: 50%; overflow: hidden; margin: 0 auto 0.5rem auto; display: flex; align-items: center; justify-content: center;">
                        <img src="{{ default_feature_image() }}" alt="feature-image" class="img-fluid avatar-140 rounded" id="image-preview" style="margin: 0; width: 100%; height: 100%; object-fit: cover; border-radius: 0;" />
                    </div>
                    <div id="validation-message" class="text-danger mb-2 d-none"></div>
                    <div class="d-flex align-items-center justify-content-center gap-2">
                        <input type="file" class="form-control d-none" id="feature_image" name="feature_image" accept=".jpeg, .jpg, .png, .gif" />
                        <label class="btn btn-sm btn-primary" for="feature_image">{{ __('messages.upload') }}</label>
                        <input type="button" class="btn btn-sm btn-secondary" name="remove" value="{{ __('messages.remove') }}" id="remove-image" style="display: none;" />
                    </div>
                    <input type="hidden" name="remove_feature_image" id="remove_feature_image" value="0" />
                </div>
            </div>
            
            <div class="form-group">
                <label class="form-label" for="brand_name">{{ __('brand.name') }} <span class="text-danger">*</span></label>
                <input type="text" class="form-control" id="brand_name" name="name" placeholder="{{ __('messages.enter_brand_name') }}" required>
                <div class="invalid-feedback" id="name-error"></div>
            </div>
            
                                      <div class="form-group">
                 <label class="form-label" for="brand-status">{{ __('service.lbl_status') }}</label>
                 <div class="d-flex justify-content-between align-items-center form-control">
                     <label class="form-label mb-0" for="brand-status">{{ __('service.lbl_status') }}</label>
                     <div class="form-check form-switch">
                        <input type="hidden" name="status" value="0" />
                         <input class="form-check-input" value="1" name="status" id="brand-status" type="checkbox" checked />
                     </div>
                 </div>
             </div>
        </div>
        
        <div class="offcanvas-footer p-3 border-top">
            <div class="d-flex justify-content-end gap-2">
                <button type="button" class="btn btn-outline-primary" data-bs-dismiss="offcanvas">
                    {{ __('messages.cancel') }}
                </button>
                <button type="submit" class="btn btn-primary d-inline-flex align-items-center gap-2" id="submit-btn">
                    <span class="spinner-border spinner-border-sm d-none" role="status" aria-hidden="true"></span>
                    <span id="submit-text">{{ __('messages.save') }}</span>
                </button>
            </div>
        </div>
    </form>
</div>

<script>
document.addEventListener('DOMContentLoaded', function() {
    const form = document.getElementById('brand-form');
    const imageInput = document.getElementById('feature_image');
    const imagePreview = document.getElementById('image-preview');
    const removeImageBtn = document.getElementById('remove-image');
    const validationMessage = document.getElementById('validation-message');
    const submitBtn = document.getElementById('submit-btn');
    const submitText = document.getElementById('submit-text');
    const spinner = submitBtn.querySelector('.spinner-border');
    
         let currentImage = null;
     let isEdit = false;
     let isEditMode = false; // Flag to track if we're in edit mode
    
    // File upload handling
    imageInput.addEventListener('change', function(e) {
        const file = e.target.files[0];
        const maxSizeInMB = 2;
        const maxSizeInBytes = maxSizeInMB * 1024 * 1024;
        
        if (file) {
            if (file.size > maxSizeInBytes) {
                showValidationMessage(`File size exceeds ${maxSizeInMB} MB. Please upload a smaller file.`);
                imageInput.value = '';
                return;
            }
            
            const reader = new FileReader();
            reader.onload = function(e) {
                imagePreview.src = e.target.result;
                currentImage = file;
                removeImageBtn.style.display = 'inline-block';
                // User selected a new image; do not auto-remove existing
                document.getElementById('remove_feature_image').value = '0';
                hideValidationMessage();
            };
            reader.readAsDataURL(file);
        }
    });
    
    // Remove image
    removeImageBtn.addEventListener('click', function() {
        imagePreview.src = '{{ default_feature_image() }}';
        imageInput.value = '';
        currentImage = null;
        removeImageBtn.style.display = 'none';
        // Mark image for removal explicitly
        document.getElementById('remove_feature_image').value = '1';
        hideValidationMessage();
    });
    
    // jQuery Validate - inline messages
    if (window.$ && $.fn && $.fn.validate) {
        const brandValidator = $('#brand-form').validate({
            ignore: [],
            onkeyup: function(el){ $(el).valid(); },
            onfocusout: function(el){ $(el).valid(); },
            rules: { name: { required: true, normalizer: function(v){ return $.trim(v); } } },
            messages: { name: { required: 'Name is a required field' } },
            errorPlacement: function(error, element){
                const map = { name: 'name-error' };
                const errorId = map[element.attr('name')];
                if (errorId) { $('#' + errorId).text(error.text()).addClass('d-block text-danger'); }
                else { error.insertAfter(element); }
            },
            highlight: function(el){ $(el).addClass('is-invalid'); },
            unhighlight: function(el){ $(el).removeClass('is-invalid'); const map={ name:'name-error'}; const id=map[$(el).attr('name')]; if(id){ $('#'+id).text('').removeClass('d-block text-danger'); } }
        });
    }

    // Form submission
    form.addEventListener('submit', function(e) {
        e.preventDefault();
        
        if (submitBtn.disabled) return;
        
        // Validate form
        if (window.$ && $.fn && $.fn.validate) {
            const $form = $('#brand-form');
            if (!$form.valid()) { $form.validate().focusInvalid(); return; }
        } else if (!validateForm()) {
            return;
        }
        
                 setLoadingState(true);
         
         const formData = new FormData(form);
         formData.append('slug', formData.get('name'));
         
         const brandId = document.getElementById('brand_id').value;
         let url;
         if (isEdit) {
             // Build update URL using Laravel route helper and placeholder replacement
             url = '{{ route("backend.brands.update", ":id") }}'.replace(':id', brandId);
             console.log('Constructed update URL:', url);
             // Update form action for edit mode
             form.action = url;
         } else {
             url = '{{ route("backend.brands.store") }}';
             console.log('Using store URL:', url);
             // Update form action for create mode
             form.action = url;
         }
         // Always send POST; for edits we spoof the method with _method=PUT to avoid server 405 on PUT
         const method = 'POST';
         
         console.log('Form submission - isEdit:', isEdit, 'brandId:', brandId, 'url:', url, 'method:', method);
         console.log('Route template:', '{{ route("backend.brands.update", ":id") }}');
         console.log('Store route:', '{{ route("backend.brands.store") }}');
        
        // Add _method for PUT request
        if (isEdit) {
            formData.append('_method', 'PUT');
        }
        
                 console.log('About to make fetch request to:', url);
         console.log('Request method:', method);
         console.log('FormData contents:');
         for (let pair of formData.entries()) {
             console.log(pair[0] + ': ' + pair[1]);
         }
         
                  // Try using XMLHttpRequest instead of fetch to see if that helps
         const xhr = new XMLHttpRequest();
         xhr.open(method, url, true);
         xhr.setRequestHeader('X-CSRF-TOKEN', document.querySelector('meta[name="csrf-token"]').getAttribute('content'));
         
         xhr.onload = function() {
             console.log('XHR Response status:', xhr.status);
             console.log('XHR Response URL:', xhr.responseURL);
             
             if (xhr.status >= 200 && xhr.status < 300) {
                 try {
                     const data = JSON.parse(xhr.responseText);
                     if (data.status) {
                         window.successSnackbar(data.message);
                         if (typeof renderedDataTable !== 'undefined') {
                             renderedDataTable.ajax.reload(null, false);
                         }
                         // Close offcanvas robustly
                         (function(){
                             const ocEl = document.getElementById('form-offcanvas');
                             let instance = bootstrap.Offcanvas.getInstance(ocEl);
                             if (!instance) {
                                 instance = new bootstrap.Offcanvas(ocEl);
                             }
                             instance.hide();
                         })();
                         resetForm();
                     } else {
                         window.errorSnackbar(data.message);
                         if (data.all_message) {
                             displayErrors(data.all_message);
                         }
                     }
                 } catch (e) {
                     console.error('Error parsing JSON:', e);
                     console.log('Raw response:', xhr.responseText);
                     window.errorSnackbar('An error occurred while processing your request.');
                 }
             } else {
                 console.error('XHR Error:', xhr.status, xhr.statusText);
                 console.log('Response URL:', xhr.responseURL);
                 window.errorSnackbar('An error occurred while processing your request.');
             }
             setLoadingState(false);
         };
         
         xhr.onerror = function() {
             console.error('XHR Network error');
             window.errorSnackbar('An error occurred while processing your request.');
             setLoadingState(false);
         };
         
         xhr.send(formData);
    });
    
    // Ensure cancel button and close (X) always hide the offcanvas programmatically
    document.querySelectorAll('#form-offcanvas [data-bs-dismiss="offcanvas"]').forEach(function(el) {
        el.addEventListener('click', function(ev) {
            ev.preventDefault();
            const ocEl = document.getElementById('form-offcanvas');
            let instance = bootstrap.Offcanvas.getInstance(ocEl);
            if (!instance) {
                instance = new bootstrap.Offcanvas(ocEl);
            }
            instance.hide();
        });
    });
    
    // Form validation
    function validateForm() {
        let isValid = true;
        const name = document.getElementById('brand_name').value.trim();
        
        // Clear previous errors
        clearErrors();
        
        if (!name) {
            showFieldError('brand_name', 'Name is a required field');
            isValid = false;
        }
        
        return isValid;
    }
    
    // Error handling
    function showFieldError(fieldId, message) {
        const field = document.getElementById(fieldId);
        const errorDiv = document.getElementById(fieldId.replace('brand_', '') + '-error');
        
        if (field && errorDiv) {
            field.classList.add('is-invalid');
            errorDiv.textContent = message;
        }
    }
    
    function clearErrors() {
        document.querySelectorAll('.is-invalid').forEach(field => {
            field.classList.remove('is-invalid');
        });
        document.querySelectorAll('.invalid-feedback').forEach(error => {
            error.textContent = '';
        });
    }
    
    function displayErrors(errors) {
        Object.keys(errors).forEach(field => {
            const fieldId = field === 'name' ? 'brand_name' : field;
            showFieldError(fieldId, errors[field][0]);
        });
    }
    
    // Loading state
    function setLoadingState(loading) {
        submitBtn.disabled = loading;
        if (loading) {
            spinner.classList.remove('d-none');
            submitText.textContent = '{{ __("messages.saving") }}';
        } else {
            spinner.classList.add('d-none');
            submitText.textContent = '{{ __("messages.save") }}';
        }
    }
    
    // Validation message
    function showValidationMessage(message) {
        validationMessage.textContent = message;
        validationMessage.classList.remove('d-none');
    }
    
    function hideValidationMessage() {
        validationMessage.classList.add('d-none');
        validationMessage.textContent = '';
    }
    
         // Reset form
     function resetForm() {
         console.log('resetForm called - current isEdit:', isEdit, 'isEditMode:', isEditMode);
         form.reset();
         imagePreview.src = '{{ default_feature_image() }}';
         imageInput.value = '';
         currentImage = null;
         removeImageBtn.style.display = 'none';
         document.getElementById('remove_feature_image').value = '0';
         clearErrors();
         hideValidationMessage();
         isEdit = false;
         isEditMode = false; // Reset edit mode flag
         document.getElementById('brand_id').value = '';
         document.getElementById('form-title').textContent = '{{ __("messages.new") }} {{ __("product.brand") }}';
         
         // Reset form action to store URL
         form.action = '{{ route("backend.brands.store") }}';
         console.log('resetForm completed - isEdit:', isEdit, 'isEditMode:', isEditMode);
     }
    
         // Edit brand function (called from outside)
     window.editBrand = function(brandData) {
         console.log('window.editBrand called with data:', brandData);
         
         isEditMode = true; // Set edit mode flag
         isEdit = true;
         
         console.log('Setting form values...');
         document.getElementById('brand_id').value = brandData.id;
         document.getElementById('brand_name').value = brandData.name;
         document.getElementById('brand-status').checked = brandData.status == 1;
         
         // Set form action for edit mode
         form.action = '{{ route("backend.brands.update", ":id") }}'.replace(':id', brandData.id);
         
         console.log('Form values set - ID:', brandData.id, 'Name:', brandData.name, 'Status:', brandData.status);
         console.log('Form field values after setting - brand_id:', document.getElementById('brand_id').value, 'brand_name:', document.getElementById('brand_name').value);
         
         if (brandData.feature_image && brandData.feature_image !== '{{ default_feature_image() }}') {
             imagePreview.src = brandData.feature_image;
             currentImage = brandData.feature_image;
             removeImageBtn.style.display = 'inline-block';
             document.getElementById('remove_feature_image').value = '0';
         } else {
             imagePreview.src = '{{ default_feature_image() }}';
             currentImage = null;
             removeImageBtn.style.display = 'none';
             document.getElementById('remove_feature_image').value = '0';
         }
         
         document.getElementById('form-title').textContent = '{{ __("messages.edit") }} {{ __("product.brand") }}';
         
         // Clear any previous errors
         clearErrors();
         hideValidationMessage();
         
         console.log('Showing offcanvas...');
         // Show the offcanvas
         const offcanvas = new bootstrap.Offcanvas(document.getElementById('form-offcanvas'));
         offcanvas.show();
         
         // Double-check form values after showing offcanvas
         setTimeout(() => {
             console.log('Form values after offcanvas show - brand_id:', document.getElementById('brand_id').value, 'brand_name:', document.getElementById('brand_name').value, 'isEdit:', isEdit, 'isEditMode:', isEditMode);
         }, 100);
         
         console.log('Edit mode setup complete');
     };
    
         // Reset form when offcanvas is hidden
     document.getElementById('form-offcanvas').addEventListener('hidden.bs.offcanvas', function() {
         console.log('Offcanvas hidden - resetting form');
         resetForm();
     });
     
     // Handle "New" button click to reset form - more specific detection
     document.addEventListener('click', function(e) {
         const newButton = e.target.closest('[data-bs-target="#form-offcanvas"]');
         if (newButton && newButton.textContent.trim().toLowerCase().includes('new') && !isEditMode) {
             console.log('New button clicked - resetting form');
             // Reset form when "New" button is clicked
             resetForm();
         }
     });
     
     // Initialize form on page load
     resetForm();
});
</script>
