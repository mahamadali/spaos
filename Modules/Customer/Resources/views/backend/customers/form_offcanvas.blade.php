<!-- Customer Form Offcanvas -->
<div class="offcanvas offcanvas-end d-flex flex-column" tabindex="-1" id="form-offcanvas" aria-labelledby="form-offcanvasLabel" style="height: 100vh;">
    <div class="offcanvas-header">
        <h5 class="offcanvas-title" id="form-offcanvasLabel">
            {{ isset($customer) ? __('messages.edit') . ' ' . __('customer.singular_title') : __('messages.new') . ' ' . __('customer.singular_title') }}
        </h5>
        <button type="button" class="btn-close" data-bs-dismiss="offcanvas" aria-label="Close"></button>
    </div>

    <form action="{{ isset($customer) ? route('backend.customers.update', $customer->id) : route('backend.customers.store') }}" method="POST" enctype="multipart/form-data" id="customer-form" novalidate class="d-flex flex-column flex-grow-1" style="min-height: 0;">
        @csrf
        @if(isset($customer))
            @method('PUT')
        @endif
        <div class="ajax-errors"></div>
        <div class="offcanvas-body flex-grow-1" style="overflow-y: auto; min-height: 0;">
            <!-- Profile Image Upload -->
            <div class="form-group">
                <div class="text-center upload-image-box">
                    <img src="{{ old('profile_image', $customer->profile_image ?? default_user_avatar()) }}" alt="profile-image" class="img-fluid avatar avatar-120 avatar-rounded mb-2" id="profile-image-preview" />
                    <div id="validation-message" class="text-danger mb-2" style="display: none;"></div>
                    <div class="d-flex align-items-center justify-content-center gap-2">
                        <input type="file" class="form-control d-none" id="profile_image" name="profile_image" accept=".jpeg, .jpg, .png, .gif" onchange="previewImage(event)" />
                        <label class="btn btn-sm btn-primary mb-3" for="profile_image">{{ __('messages.upload') }}</label>
                        <button type="button" class="btn btn-sm btn-secondary mb-3" onclick="removeImage()" id="remove-image-btn" style="display:none;">{{ __('messages.remove') }}</button>
                    </div>
                    <span class="text-danger" id="profile_image_error"></span>
                </div>
            </div>

            <!-- First Name -->
            <div class="form-group col-md-12">
                <label for="first_name">{{ __('customer.lbl_first_name') }} <span class="text-danger">*</span></label>
                <input type="text" class="form-control" name="first_name" id="first_name" value="{{ old('first_name', $customer->first_name ?? '') }}" placeholder="{{ __('customer.first_name') }}" required>
                <span class="text-danger" id="first_name_error"></span>
            </div>

            <!-- Last Name -->
            <div class="form-group col-md-12">
                <label for="last_name">{{ __('customer.lbl_last_name') }} <span class="text-danger">*</span></label>
                <input type="text" class="form-control" name="last_name" id="last_name" value="{{ old('last_name', $customer->last_name ?? '') }}" placeholder="{{ __('customer.last_name') }}" required>
                <span class="text-danger" id="last_name_error"></span>
            </div>

            <!-- Email -->
            <div class="form-group col-md-12">
                <label for="email">{{ __('customer.lbl_Email') }} <span class="text-danger">*</span></label>
                <input type="email" class="form-control" name="email" id="email" value="{{ old('email', $customer->email ?? '') }}" placeholder="{{ __('customer.email_address') }}" required>
                <span class="text-danger" id="email_error"></span>
            </div>

            <!-- Mobile -->
            <div class="form-group col-md-12">
                <label for="mobile">{{ __('customer.lbl_phone_number') }} <span class="text-danger">*</span></label>
                <div><input type="tel" class="form-control" name="mobile" id="mobile" value="{{ old('mobile', $customer->mobile ?? '') }}" placeholder="{{ __('messages.placeholder_phone') }}" required></div>
                <span class="text-danger" id="mobile_error"></span>
            </div>

            <!-- Password (only for create) -->
            <div class="form-group col-md-12" id="password-field" style="{{ isset($customer) ? 'display: none;' : '' }}">
                <label for="password">{{ __('employee.lbl_password') }} <span class="text-danger">*</span></label>
                <input type="password" class="form-control" name="password" id="password" placeholder="{{ __('customer.password') }}" {{ !isset($customer) ? 'required' : '' }}>
                <span class="text-danger" id="password_error"></span>
            </div>

            <!-- Confirm Password (only for create) -->
            <div class="form-group col-md-12" id="confirm-password-field" style="{{ isset($customer) ? 'display: none;' : '' }}">
                <label for="confirm_password">{{ __('employee.lbl_confirm_password') }} <span class="text-danger">*</span></label>
                <input type="password" class="form-control" name="confirm_password" id="confirm_password" placeholder="{{ __('customer.confirm_password') }}" {{ !isset($customer) ? 'required' : '' }}>
                <span class="text-danger" id="confirm_password_error"></span>
            </div>

            <!-- Gender -->
            <div class="form-group col-md-12">
                <label class="w-100">{{ __('employee.lbl_gender') }}</label>
                <div class="form-check form-check-inline">
                  <input class="form-check-input" type="radio" name="gender" id="gender_male" value="male" checked>
                  <label class="form-check-label" for="gender_male">{{ __('Male') }}</label>
                </div>
                <div class="form-check form-check-inline">
                  <input class="form-check-input" type="radio" name="gender" id="gender_female" value="female">
                  <label class="form-check-label" for="gender_female">{{ __('Female') }}</label>
                </div>
                {{-- <div class="form-check form-check-inline">
                  <input class="form-check-input" type="radio" name="gender" id="gender_other" value="other">
                  <label class="form-check-label" for="gender_other">{{ __('other') }}</label>
                </div> --}}
                <span class="text-danger" id="gender_error"></span>
              </div>

            <!-- Custom Fields -->
            @if(!empty($customefield))
                @foreach($customefield as $field)
                    <div class="form-group">
                        <label for="custom_{{ $field->id }}">{{ $field->label }}{{ $field->required ? ' *' : '' }}</label>
                        @if($field->type === 'text')
                            <input type="text" class="form-control" name="custom_fields[{{ $field->id }}]" id="custom_{{ $field->id }}" value="{{ old('custom_fields.'.$field->id, $customer->custom_fields[$field->id] ?? '') }}" {{ $field->required ? 'required' : '' }}>
                        @elseif($field->type === 'select')
                            <select class="form-control" name="custom_fields[{{ $field->id }}]" id="custom_{{ $field->id }}" {{ $field->required ? 'required' : '' }}>
                                <option value="">{{ __('messages.select') }}</option>
                                @if($field->value)
                                    @foreach(json_decode($field->value) as $option)
                                        <option value="{{ $option }}" {{ old('custom_fields.'.$field->id, $customer->custom_fields[$field->id] ?? '') == $option ? 'selected' : '' }}>{{ $option }}</option>
                                    @endforeach
                                @endif
                            </select>
                        @elseif($field->type === 'textarea')
                            <textarea class="form-control" name="custom_fields[{{ $field->id }}]" id="custom_{{ $field->id }}" {{ $field->required ? 'required' : '' }}>{{ old('custom_fields.'.$field->id, $customer->custom_fields[$field->id] ?? '') }}</textarea>
                        @endif
                        <span class="text-danger" id="custom_{{ $field->id }}_error"></span>
                    </div>
                @endforeach
            @endif

            <!-- Status -->
            <div class="form-group">
                <div class="d-flex justify-content-between align-items-center">
                    <label for="status">{{ __('customer.lbl_status') }}</label>
                    <div class="form-check form-switch">
                        <input type="hidden" name="status" value="0">
                        <input class="form-check-input " type="checkbox" id="customer-status" name="status" value="1" {{ old('status', $customer->status ?? false) ? 'checked' : '' }}>
                    </div>
                </div>
            </div>
        </div>
        
        <div class="offcanvas-footer p-3 border-top" style="flex-shrink: 0; margin-top: auto;">
            <div class="d-flex justify-content-end gap-2">
                <button type="button" class="btn btn-outline-primary" data-bs-dismiss="offcanvas">
                    {{ __('messages.cancel') }}
                </button>
                <button type="submit" class="btn btn-primary d-inline-flex align-items-center gap-2" name="submit" id="saveBtn">
                    <span class="spinner-border spinner-border-sm d-none" role="status" aria-hidden="true"></span>
                    <span id="save-text"><i class="fa-solid fa-floppy-disk"></i> {{ __('messages.save') }}</span>
                </button>
            </div>
        </div>
    </form>
</div>

<script>
// Image preview functionality
function previewImage(event) {
    const file = event.target.files[0];
    const maxSizeInMB = 2;
    const maxSizeInBytes = maxSizeInMB * 1024 * 1024;
    const validationMessage = document.getElementById('validation-message');
    const removeBtn = document.getElementById('remove-image-btn');

    if (file) {
        if (file.size > maxSizeInBytes) {
            validationMessage.textContent = `File size exceeds ${maxSizeInMB} MB. Please upload a smaller file.`;
            validationMessage.style.display = 'block';
            event.target.value = '';
            return;
        }

        const reader = new FileReader();
        reader.onload = function() {
            document.getElementById('profile-image-preview').src = reader.result;
            removeBtn.style.display = 'inline-block';
            validationMessage.style.display = 'none';
        };
        reader.readAsDataURL(file);
    } else {
        validationMessage.style.display = 'none';
    }
}

function removeImage() {
    document.getElementById('profile-image-preview').src = '{{ default_user_avatar() }}';
    document.getElementById('profile_image').value = '';
    document.getElementById('remove-image-btn').style.display = 'none';
    document.getElementById('validation-message').style.display = 'none';
}

// Form submission handler
document.getElementById('customer-form').addEventListener('submit', function(e) {
    e.preventDefault();

    const submitBtn = document.getElementById('saveBtn');
    const formData = new FormData(this);

    // Clear previous errors
    clearErrors();

    // Validate required fields
    if (!validateForm()) {
        return;
    }


    // Show loading state
    const spinner = submitBtn.querySelector('.spinner-border');
    const saveText = document.getElementById('save-text');
    submitBtn.disabled = true;
    if (spinner) spinner.classList.remove('d-none');
    if (saveText) {
        saveText.innerHTML = '<i class="fa-solid fa-floppy-disk"></i> {{ __("messages.saving") }}';
    }

    // Determine URL based on whether it's edit or create
    const url = this.action;
    const method = this.querySelector('input[name="_method"]') ? this.querySelector('input[name="_method"]').value : 'POST';

    // Handle profile image - for edit mode, send placeholder if no new file
    const profileImageInput = document.getElementById('profile_image');
    const isEditMode = this.querySelector('input[name="_method"]');

    if (profileImageInput.files.length === 0 || profileImageInput.files[0].size === 0) {
        if (isEditMode) {
            // For edit mode, send a placeholder to prevent clearing existing image
            formData.append('profile_image', 'keep_existing');
        } else {
            // For create mode, remove the field
            formData.delete('profile_image');
        }
    }

    // Add custom fields data as JSON string
    const customFieldsData = {};
    document.querySelectorAll('input[name^="custom_fields"], select[name^="custom_fields"], textarea[name^="custom_fields"]').forEach(field => {
        const name = field.name.match(/custom_fields\[(\d+)\]/)[1];
        customFieldsData[name] = field.value;
    });
    formData.append('custom_fields_data', JSON.stringify(customFieldsData));

    fetch(url, {
        method: 'POST', // Always use POST, Laravel will handle PUT via _method field
        body: formData,
        headers: {
            'X-Requested-With': 'XMLHttpRequest',
            'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').getAttribute('content')
        }
    })
    .then(response => response.json())
    .then(data => {
        if (data.status) {
            window.successSnackbar(data.message);
            // Reload datatable if it exists
            if (typeof window.renderedDataTable !== 'undefined') {
                window.renderedDataTable.ajax.reload(null, false);
            }
            // Hide offcanvas
            const offcanvas = bootstrap.Offcanvas.getInstance(document.getElementById('form-offcanvas'));
            offcanvas.hide();
            // Reset form
            this.reset();
            removeImage();
        } else {
            window.errorSnackbar(data.message);
            if (data.all_message) {
                displayErrors(data.all_message);
            }
        }
    })
    .catch(error => {
        console.error('Error:', error);
        window.errorSnackbar('An error occurred. Please try again.');
    })
    .finally(() => {
            const spinner = submitBtn.querySelector('.spinner-border');
        const saveText = document.getElementById('save-text');
        submitBtn.disabled = false;
        if (spinner) spinner.classList.add('d-none');
        if (saveText) {
            saveText.innerHTML = '<i class="fa-solid fa-floppy-disk"></i> {{ __("messages.save") }}';
        }
    });
});

function clearErrors() {
    document.querySelectorAll('.text-danger[id$="_error"]').forEach(error => {
        error.textContent = '';
    });
    document.getElementById('validation-message').style.display = 'none';
}

function validateForm() {
    let isValid = true;
    const numberRegex = /^\d+$/;
    const specialCharsRegex = /[!@#$%^&*(),.?":{}|<>\-_;'\/+=\[\]\\]/;
    const EMAIL_REGX = /^[a-zA-Z0-9_.+-]+@[a-zA-Z0-9-]+\.[a-zA-Z0-9-.]+$/;

    // Validate first name
    const firstName = document.getElementById('first_name');
    if (!firstName.value.trim()) {
        document.getElementById('first_name_error').textContent = 'First Name is a required field';
        isValid = false;
    } else if (specialCharsRegex.test(firstName.value) || numberRegex.test(firstName.value)) {
        document.getElementById('first_name_error').textContent = 'Only strings are allowed';
        isValid = false;
    }

    // Validate last name
    const lastName = document.getElementById('last_name');
    if (!lastName.value.trim()) {
        document.getElementById('last_name_error').textContent = 'Last Name is a required field';
        isValid = false;
    } else if (specialCharsRegex.test(lastName.value) || numberRegex.test(lastName.value)) {
        document.getElementById('last_name_error').textContent = 'Only strings are allowed';
        isValid = false;
    }

    // Validate email
    const email = document.getElementById('email');
    if (!email.value.trim()) {
        document.getElementById('email_error').textContent = 'Email is a required field';
        isValid = false;
    } else if (!EMAIL_REGX.test(email.value)) {
        document.getElementById('email_error').textContent = 'Must be a valid email';
        isValid = false;
    }

    // Validate mobile
    const mobile = document.getElementById('mobile');
    if (!mobile.value.trim()) {
        document.getElementById('mobile_error').textContent = 'Phone Number is a required field';
        isValid = false;
    } else if (!/^(\+?\d+)?(\s?\d+)*$/.test(mobile.value)) {
        document.getElementById('mobile_error').textContent = 'Phone Number must contain only digits';
        isValid = false;
    }

    // Validate password fields only for create mode
    const passwordField = document.getElementById('password');
    const confirmPasswordField = document.getElementById('confirm_password');
    const passwordFieldDiv = document.getElementById('password-field');

    if (passwordFieldDiv && passwordFieldDiv.style.display !== 'none') {
        // This is create mode, validate passwords
        if (!passwordField.value.trim()) {
            document.getElementById('password_error').textContent = 'Password is required';
            isValid = false;
        } else if (passwordField.value.length < 8) {
            document.getElementById('password_error').textContent = 'Password must be at least 8 characters long';
            isValid = false;
        }

        if (!confirmPasswordField.value.trim()) {
            document.getElementById('confirm_password_error').textContent = 'Confirm password is required';
            isValid = false;
        } else if (passwordField.value !== confirmPasswordField.value) {
            document.getElementById('confirm_password_error').textContent = 'Passwords must match';
            isValid = false;
        }
    }

    // Validate custom fields
    document.querySelectorAll('input[name^="custom_fields"], select[name^="custom_fields"], textarea[name^="custom_fields"]').forEach(field => {
        const fieldId = field.name.match(/custom_fields\[(\d+)\]/)[1];
        const errorElement = document.getElementById(`custom_${fieldId}_error`);
        const isRequired = field.hasAttribute('required');

        if (isRequired && !field.value.trim()) {
            if (errorElement) {
                errorElement.textContent = 'This field is required';
            }
            isValid = false;
        }
    });

    return isValid;
}

function displayErrors(errors) {
    Object.keys(errors).forEach(field => {
        const errorElement = document.getElementById(field + '_error');
        if (errorElement) {
            errorElement.textContent = errors[field][0];
        }
    });
}

// Load edit form when edit button is clicked
window.loadEditForm = function(customerId) {
    const editUrl = `{{ url('app/customers') }}/${customerId}/edit`;
    fetch(editUrl)
        .then(response => response.json())
        .then(data => {
            if (data.status) {
                const customer = data.data;
                // Hide password fields for edit mode
                document.getElementById('password-field').style.display = 'none';
                document.getElementById('confirm-password-field').style.display = 'none';
                document.getElementById('password').removeAttribute('required');
                document.getElementById('confirm_password').removeAttribute('required');

                // Populate form fields
                document.getElementById('first_name').value = customer.first_name || '';
                document.getElementById('last_name').value = customer.last_name || '';
                document.getElementById('email').value = customer.email || '';

                // Populate mobile with intl-tel-input
                const mobileInput = document.getElementById('mobile');
                mobileInput.value = customer.mobile || '';
                // Set the number in intl-tel-input if it's initialized
                if (window.intlTelInputGlobals) {
                    const iti = window.intlTelInputGlobals.getInstance(mobileInput);
                    if (iti) {
                        iti.setNumber(customer.mobile || '');
                    }
                }

                // Handle gender radio buttons
                const gender = customer.gender || 'male';
                document.getElementById('gender_male').checked = gender === 'male';
                document.getElementById('gender_female').checked = gender === 'female';
                // document.getElementById('gender_other').checked = gender === 'other';

                const statusInput = document.getElementById('customer-status');
                const statusVal = customer.status;
                if (statusVal == 1) {
                    statusInput.checked = true;
                } else {
                    statusInput.checked = false;
                    statusInput.removeAttribute('checked');
                }


                // Set profile image
                if (customer.profile_image) {
                    document.getElementById('profile-image-preview').src = customer.profile_image;
                    document.getElementById('remove-image-btn').style.display = 'inline-block';
                }

                // Populate custom fields
                if (customer.custom_field_data) {
                    Object.keys(customer.custom_field_data).forEach(fieldId => {
                        const field = document.querySelector(`[name="custom_fields[${fieldId}]"]`);
                        if (field) {
                            field.value = customer.custom_field_data[fieldId];
                        }
                    });
                }

                // Update form action and method
                document.getElementById('customer-form').action = `{{ url('app/customers') }}/${customerId}`;
                // Add or update the _method field for PUT request
                let methodField = document.querySelector('input[name="_method"]');
                if (methodField) {
                    methodField.value = 'PUT';
                } else {
                    const methodInput = document.createElement('input');
                    methodInput.type = 'hidden';
                    methodInput.name = '_method';
                    methodInput.value = 'PUT';
                    document.getElementById('customer-form').appendChild(methodInput);
                }

                // Show offcanvas
                const offcanvas = new bootstrap.Offcanvas(document.getElementById('form-offcanvas'));
                offcanvas.show();
            } else {
                window.errorSnackbar(data.message);
            }
        })
        .catch(error => {
            console.error('Error loading customer data:', error);
            window.errorSnackbar('Error loading customer data');
        });
};

// Initialize form on page load
document.addEventListener('DOMContentLoaded', function() {
    // Add real-time validation - clear errors when user starts typing
    const formFields = ['first_name', 'last_name', 'email', 'mobile', 'password', 'confirm_password'];
    formFields.forEach(fieldId => {
        const field = document.getElementById(fieldId);
        if (field) {
            field.addEventListener('input', function() {
                const errorElement = document.getElementById(fieldId + '_error');
                if (errorElement) {
                    errorElement.textContent = '';
                }
            });
        }
    });

    // Add real-time validation for custom fields
    document.querySelectorAll('input[name^="custom_fields"], select[name^="custom_fields"], textarea[name^="custom_fields"]').forEach(field => {
        field.addEventListener('input', function() {
            const fieldId = field.name.match(/custom_fields\[(\d+)\]/)[1];
            const errorElement = document.getElementById(`custom_${fieldId}_error`);
            if (errorElement) {
                errorElement.textContent = '';
            }
        });
    });


    // Reset form when offcanvas is hidden
    document.getElementById('form-offcanvas').addEventListener('hidden.bs.offcanvas', function() {
        document.getElementById('customer-form').reset();
        removeImage();
        clearErrors();
        // Reset form action to create
        document.getElementById('customer-form').action = '{{ url("app/customers") }}';
        // Remove _method field for POST request (create mode)
        let methodField = document.querySelector('input[name="_method"]');
        if (methodField) {
            methodField.remove();
        }
        // Show password fields for create mode
        document.getElementById('password-field').style.display = 'block';
        document.getElementById('confirm-password-field').style.display = 'block';
        document.getElementById('password').setAttribute('required', 'required');
        document.getElementById('confirm_password').setAttribute('required', 'required');
        // Reset gender to male (default)
        document.getElementById('gender_male').checked = true;
        document.getElementById('gender_female').checked = false;
        // document.getElementById('gender_other').checked = false;
    });

    // Initialize intl-tel-input for mobile field (same as branch form)
    if (window.intlTelInput) {
        const mobileInput = document.getElementById('mobile');
        if (mobileInput) {
            const iti = intlTelInput(mobileInput, {
                initialCountry: 'in',
                preferredCountries: ['in', 'us', 'gb', 'au', 'ca'],
                separateDialCode: true,
                utilsScript: "/js/utils.js",
                autoHideDialCode: false,
                autoPlaceholder: 'aggressive',
                formatOnDisplay: true,
                nationalMode: false,
                geoIpLookup: function(callback) {
                    // Default to India if geolocation fails
                    callback('in');
                }
            });

            // Handle form submission to get the full international number
            const form = mobileInput.closest('form');
            if (form) {
                form.addEventListener('submit', function(e) {
                    if (iti.isValidNumber()) {
                        const fullNumber = iti.getNumber();
                        mobileInput.value = fullNumber;
                    }
                });
            }
        }
    }

    // Handle edit button clicks using data attribute
    document.addEventListener('click', function(e) {
        if (e.target.closest('button[data-customer-id]')) {
            const customerId = e.target.closest('button[data-customer-id]').getAttribute('data-customer-id');
            loadEditForm(customerId);
        }
    });
});
</script>
