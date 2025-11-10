@extends('backend.layouts.app')

@section('title') {{ __('profile.title') }} @endsection

@section('content')
<div class="row">
    <!-- Sidebar Panel -->
    <div class="col-md-4 col-lg-3">
        <div class="card">
            <div class="card-body">
                <div class="list-group list-group-flush">
                    <div class="mb-3">
                        <button class="btn btn-border active" onclick="showTab('information')">
                            <i class="fa-solid fa-user"></i>{{ __('messages.personal_information') }}
                        </button>
                    </div>
                    <div class="mb-3">
                        <button class="btn btn-border" onclick="showTab('password')">
                            <i class="fa-solid fa-key"></i>{{ __('messages.change_password') }}
                        </button>
                    </div>
                    {{-- @if(auth()->user()->hasRole('admin'))
                    <div class="mb-3">
                        <button class="btn btn-border" onclick="showTab('branch')">
                            <i class="fa-solid fa-code-branch"></i>{{ __('messages.branch_setting') }}
                        </button>
                    </div>
                    @endif --}}
                </div>
            </div>
        </div>
    </div>

    <!-- Main Content -->
    <div class="col-md-8 col-lg-9 navbar-expand-md">
        <div class="offcanvas offcanvas-end" id="offcanvas" data-bs-backdrop="false">
            <div class="offcanvas-header">
                <h5 class="offcanvas-title" id="offcanvasNavbarLabel">{{ __('messages.setting') }}</h5>
                <button type="button" class="btn-close" onclick="toggleOffcanvas()"></button>
            </div>
            <div class="card card-accent-primary offcanvas-body">
                <div class="card-body">
                    <!-- Information Tab -->
                    <div id="information" class="tab-content active">
                        <form id="profileForm" action="{{ route('backend.users.information') }}" method="POST" enctype="multipart/form-data" novalidate>
                            @csrf
                            <input type="hidden" name="profile_image_removed" value="0" id="profileImageRemovedFlag">
                            <div class="d-flex justify-content-between align-items-center mb-3">
                                <h5 class="mb-0">
                                    <i class="fa-solid fa-user me-2"></i>
                                    {{ __('messages.personal_information') }}
                                </h5>
                            </div>
                            <div class="row">
                                <div class="col-md-8">
                                    <div class="row">
                                        <div class="col-md-6 mb-3">
                                            <label for="first_name" class="form-label">{{ __('profile.lbl_first_name') }} <span class="text-danger">*</span></label>
                                            <input type="text" class="form-control @error('first_name') is-invalid @enderror" id="first_name" name="first_name" value="{{ old('first_name', auth()->user()->first_name) }}" placeholder="{{ __('profile.enter_first_name') }}">
                                            @error('first_name')
                                            <div class="text-danger mb-1">{{ $message }}</div>
                                            @enderror
                                            <span class="text-danger field-error mb-1 d-none"></span>

                                        </div>
                                        <div class="col-md-6 mb-3">
                                            <label for="last_name" class="form-label">{{ __('profile.lbl_last_name') }} <span class="text-danger">*</span></label>
                                            <input type="text" class="form-control @error('last_name') is-invalid @enderror" id="last_name" name="last_name" value="{{ old('last_name', auth()->user()->last_name) }}" placeholder="{{ __('profile.enter_last_name') }}">
                                            @error('last_name')
                                                <div class="text-danger mb-1">{{ $message }}</div>
                                            @enderror
                                            <span class="text-danger field-error mb-1 d-none"></span>

                                        </div>
                                        <div class="col-md-6 mb-3">
                                            <label for="email" class="form-label">{{ __('profile.lbl_email') }} <span class="text-danger">*</span></label>
                                            <input type="email" class="form-control @error('email') is-invalid @enderror" id="email" name="email" value="{{ old('email', auth()->user()->email) }}" placeholder="{{ __('profile.enter_email') }}">
                                            @error('email')
                                                <div class="text-danger mb-1">{{ $message }}</div>
                                            @enderror
                                            <span class="text-danger field-error mb-1 d-none"></span>

                                        </div>
                                        <div class="col-md-6 mb-3">
                                            <label for="mobile" class="form-label">{{ __('profile.lbl_contact_number') }} <span class="text-danger">*</span></label>
                                            <input type="tel" class="form-control @error('mobile') is-invalid @enderror" id="mobileInput" name="mobile" value="{{ old('mobile', auth()->user()->mobile) }}" placeholder="+1234567890">
                                            @error('mobile')
                                                <div class="text-danger mb-1">{{ $message }}</div>
                                            @enderror
                                            <span class="text-danger field-error mb-1 d-none"></span>

                                        </div>
                                    </div>
                                    <div class="row">
                                        <div class="col-md-6 mb-3">
                                            <label class="form-label">{{ __('profile.lbl_gender') }}</label>
                                            <div class="form-check form-check-inline">
                                                <input class="form-check-input" type="radio" name="gender" id="male" value="male" {{ old('gender', auth()->user()->gender) == 'male' ? 'checked' : '' }}>
                                                <label class="form-check-label" for="male">{{ __('messages.male') }}</label>
                                            </div>
                                            <div class="form-check form-check-inline">
                                                <input class="form-check-input" type="radio" name="gender" id="female" value="female" {{ old('gender', auth()->user()->gender) == 'female' ? 'checked' : '' }}>
                                                <label class="form-check-label" for="female">{{ __('messages.female') }}</label>
                                            </div>
                                            <div class="form-check form-check-inline">
                                                <input class="form-check-input" type="radio" name="gender" id="other" value="other" {{ old('gender', auth()->user()->gender) == 'other' ? 'checked' : '' }}>
                                                <label class="form-check-label" for="other">{{ __('messages.intersex') }}</label>
                                            </div>
                                            @error('gender')
                                                <div class="text-danger mt-1">{{ $message }}</div>
                                            @enderror
                                        </div>
                                        <div class="col-md-6 mb-3">
                                            <div class="form-check">
                                                <input class="form-check-input" type="checkbox" id="show_in_calender" name="show_in_calender" value="1" {{ old('show_in_calender', auth()->user()->show_in_calender) ? 'checked' : '' }}>
                                                <label class="form-check-label" for="show_in_calender">
                                                    {{ __('profile.lbl_show_in_calender') }}
                                                </label>
                                            </div>
                                        </div>
                                    </div>
                                    <div class="row">
                                        <div class="col-12">
                                            <div class="d-grid d-md-block setting-footer">
                                                <button type="submit" class="btn btn-primary" id="profileSubmitBtn" >
                                                    <span class="spinner-border spinner-border-sm d-none" role="status" aria-hidden="true"></span>
                                                    <span class="btn-text">{{ __('messages.submit') }}</span>
                                                </button>
                                            </div>
                                        </div>
                                    </div>
                                </div>
                                <div class="col-md-4">
                                    <div class="text-center upload-image-box">
                                        <img src="{{ auth()->user()->profile_image ? auth()->user()->profile_image : default_user_avatar() }}" class="img-fluid avatar avatar-120 avatar-rounded mb-2" alt="profile-image" id="profileImage" />
                                        <div class="d-flex align-items-center justify-content-center gap-2">
                                            <input type="file" class="form-control d-none" id="profile_image" name="profile_image" accept=".jpeg, .jpg, .png, .gif" onchange="previewImage(this)" />
                                            <label class="btn btn-sm btn-primary" for="profile_image">{{ __('messages.upload') }}</label>
                                            <button type="button" class="btn btn-sm btn-secondary" onclick="removeImage()" id="removeImageBtn" >{{ __('messages.remove') }}</button>
                                        </div>
                                        @error('profile_image')
                                            <div class="text-danger mt-1">{{ $message }}</div>
                                        @enderror
                                    </div>
                                </div>
                            </div>
                        </form>
                    </div>

                    <!-- Password Tab -->
                    <div id="password" class="tab-content" style="display: none;">
                        <form id="passwordForm" action="{{ route('backend.users.change_password') }}" method="POST" novalidate>
                            @csrf
                            <div class="d-flex justify-content-between align-items-center mb-3">
                                <h5 class="mb-0">
                                    <i class="fa-solid fa-key me-2"></i>
                                    {{ __('messages.change_password') }}
                                </h5>
                            </div>
                            <div class="row">
                                <div class="col-md-12 mb-3">
                                    <label for="old_password" class="form-label">{{ __('users.lbl_old_password') }} <span class="text-danger">*</span></label>
                                    <div class="position-relative">
                                        <input type="password" class="form-control @error('old_password') is-invalid @enderror" id="old_password" name="old_password" placeholder="{{ __('users.old_password') }}">
                                        <span class="password-toggle" id="toggle-old-password">
                                            <i class="fa-solid fa-eye-slash"></i>
                                        </span>
                                    </div>
                                    @error('old_password')
                                        <div class="text-danger mb-1">{{ $message }}</div>
                                    @enderror
                                    <span class="text-danger field-error mb-1 d-none"></span>
                                </div>
                                <div class="col-md-12 mb-3">
                                    <label for="new_password" class="form-label">{{ __('users.lbl_new_password') }} <span class="text-danger">*</span></label>
                                    <div class="position-relative">
                                        <input type="password" class="form-control @error('new_password') is-invalid @enderror" id="new_password" name="new_password" placeholder="{{ __('users.new_password') }}">
                                        <span class="password-toggle" id="toggle-new-password">
                                            <i class="fa-solid fa-eye-slash"></i>
                                        </span>
                                    </div>
                                    @error('new_password')
                                        <div class="text-danger mb-1">{{ $message }}</div>
                                    @enderror
                                    <span class="text-danger field-error mb-1 d-none"></span>
                                </div>
                                <div class="col-md-12 mb-3">
                                    <label for="confirm_password" class="form-label">{{ __('users.lbl_confirm_password') }} <span class="text-danger">*</span></label>
                                    <div class="position-relative">
                                        <input type="password" class="form-control @error('confirm_password') is-invalid @enderror" id="confirm_password" name="confirm_password" placeholder="{{ __('users.confirm_password') }}">
                                        <span class="password-toggle" id="toggle-confirm-password">
                                            <i class="fa-solid fa-eye-slash"></i>
                                        </span>
                                    </div>
                                    @error('confirm_password')
                                        <div class="text-danger mb-1">{{ $message }}</div>
                                    @enderror
                                    <span class="text-danger field-error mb-1 d-none"></span>
                                </div>
                            </div>
                            <div class="row">
                                <div class="col-12">
                                    <div class="d-grid d-md-block setting-footer">
                                        <button type="submit" class="btn btn-primary" id="passwordSubmitBtn" >
                                            <span class="spinner-border spinner-border-sm d-none" role="status" aria-hidden="true"></span>
                                            <span class="btn-text">{{ __('messages.submit') }}</span>
                                        </button>
                                    </div>
                                </div>
                            </div>
                        </form>
                    </div>

                    {{-- @if(auth()->user()->hasRole('admin'))
                    <!-- Branch Setting Tab -->
                    <div id="branch" class="tab-content" style="display: none;">
                        <div class="alert alert-info">
                            <i class="fa-solid fa-info-circle me-2"></i>
                            {{ __('messages.branch_setting_info') }}
                        </div>
                        <!-- Add branch setting form here if needed -->
                    </div>
                    @endif --}}
                </div>
            </div>
        </div>
    </div>
</div>
@endsection

@push('after-styles')
  <style>
    .modal-backdrop {
      --bs-backdrop-zindex: 1030;
    }
    .btn-border {
        text-align: left;
        display: flex;
        align-items: center;
        gap: 0.75rem;
        width: 100%;
    }
    /* CSS for intl-tel-input flag images */
    :root {
        --iti-path-flags-1x: url("{{ asset('img/intl-tel-input/flags.webp') }}");
    }

    /* Password toggle icon styling */
    .password-toggle {
        position: absolute;
        right: 10px;
        top: 50%;
        transform: translateY(-50%);
        cursor: pointer;
        user-select: none;
        z-index: 10;
        color: #6c757d;
        padding: 5px;
    }
    .password-toggle:hover {
        color: #0d6efd;
    }
  </style>
@endpush
@push('after-scripts')
<script>
// Tab switching functionality
function showTab(tabName) {
    // Hide all tab contents
    const tabContents = document.querySelectorAll('.tab-content');
    tabContents.forEach(content => {
        content.style.display = 'none';
        content.classList.remove('active');
    });

    // Remove active class from all buttons
    const buttons = document.querySelectorAll('.btn-border');
    buttons.forEach(btn => btn.classList.remove('active'));

    // Show selected tab content
    const selectedTab = document.getElementById(tabName);
    if (selectedTab) {
        selectedTab.style.display = 'block';
        selectedTab.classList.add('active');
    }

    // Add active class to clicked button
    event.target.classList.add('active');
}

// Offcanvas toggle
function toggleOffcanvas() {
    const offcanvas = document.getElementById('offcanvas');
    offcanvas.classList.remove('show');
}

// Image preview functionality
function previewImage(input) {
    if (input.files && input.files[0]) {
        const reader = new FileReader();
        reader.onload = function(e) {
            document.getElementById('profileImage').src = e.target.result;
            document.getElementById('removeImageBtn').style.display = 'inline-block';
            // Reset the remove flag when a new image is selected
            document.getElementById('profileImageRemovedFlag').value = '0';
        };
        reader.readAsDataURL(input.files[0]);
    }
}

function removeImage() {
    document.getElementById('profileImage').src = '{{ default_user_avatar() }}';
    document.getElementById('profile_image').value = '';
    document.getElementById('removeImageBtn').style.display = 'none';
    document.getElementById('profileImageRemovedFlag').value = '1'; // Set flag to 1 when image is removed
    // Clear the file input so profile_image will be null when form is submitted
    const fileInput = document.getElementById('profile_image');
    fileInput.files = null;
}

// Password visibility toggle function
function togglePasswordVisibility(passwordFieldId, toggleIconId) {
    const passwordField = document.getElementById(passwordFieldId);
    const toggleIcon = document.getElementById(toggleIconId);
    if (passwordField.type === 'password') {
        passwordField.type = 'text';
        toggleIcon.innerHTML = '<i class="fa-solid fa-eye"></i>';
    } else {
        passwordField.type = 'password';
        toggleIcon.innerHTML = '<i class="fa-solid fa-eye-slash"></i>';
    }
}

document.addEventListener('DOMContentLoaded', function() {
    // Initialize international telephone input
    const input = document.querySelector("#mobileInput");
    if (input) {
        const iti = window.intlTelInput(input, {
            initialCountry: "gh",
            separateDialCode: true,
            utilsScript: "/node_modules/intl-tel-input/build/js/utils.js",
        });

        // Store iti instance globally if needed
        window.iti = iti;
    }

    // Check if user has an existing profile image and show/hide remove button accordingly
    const profileImage = document.getElementById('profileImage');
    const removeImageBtn = document.getElementById('removeImageBtn');
    const defaultAvatar = '{{ default_user_avatar() }}';

    if (profileImage && removeImageBtn) {
        // If the current image is not the default avatar, show the remove button
        if (profileImage.src !== defaultAvatar && !profileImage.src.includes('default')) {
            removeImageBtn.style.display = 'inline-block';
        } else {
            removeImageBtn.style.display = 'none';
        }
    }

    // Add click event listeners for password toggle buttons
    document.getElementById('toggle-old-password').addEventListener('click', function() {
        togglePasswordVisibility('old_password', 'toggle-old-password');
    });

    document.getElementById('toggle-new-password').addEventListener('click', function() {
        togglePasswordVisibility('new_password', 'toggle-new-password');
    });

    document.getElementById('toggle-confirm-password').addEventListener('click', function() {
        togglePasswordVisibility('confirm_password', 'toggle-confirm-password');
    });

    // Form submission with loading states
    document.getElementById('profileForm').addEventListener('submit', function(e) {
        // Clear all existing errors first
        clearAllFieldErrors();

        // Validate all required fields
        let hasErrors = false;

        // Validate profile fields
        const profileFields = [
            { name: 'first_name', pattern: /^[A-Za-z]+$/, errorMessage: 'First name must contain only letters' },
            { name: 'last_name', pattern: /^[A-Za-z]+$/, errorMessage: 'Last name must contain only letters' },
            { name: 'email', pattern: /^[a-zA-Z0-9._-]+@[a-zA-Z0-9.-]+\.[a-zA-Z]{2,6}$/, errorMessage: 'Please enter a valid email address' }
        ];

        profileFields.forEach(field => {
            const input = document.querySelector(`input[name="${field.name}"]`);
            if (input) {
                const value = input.value.trim();
                if (!value) {
                    showFieldError(input, 'This field is required');
                    hasErrors = true;
                } else if (field.pattern && !field.pattern.test(value)) {
                    showFieldError(input, field.errorMessage);
                    hasErrors = true;
                }
            }
        });

        // Validate mobile number
        const mobileInput = document.getElementById('mobileInput');
        if (mobileInput) {
            const mobileValue = mobileInput.value.trim();
            const digitsOnly = mobileValue.replace(/\D/g, ''); // Remove non-digits

            if (!mobileValue) {
                showFieldError(mobileInput, '{{ __("messages.phone_number_field_is_required") }}');
                hasErrors = true;
            } else if (digitsOnly.length < 10 || digitsOnly.length > 15) {
                showFieldError(mobileInput, '{{ __("messages.mobile_number_length_validation") }}');
                hasErrors = true;
            } else {
                // If validation passes, update the input with the full number from intl-tel-input
                if (window.iti && window.iti.isValidNumber()) {
                    const fullNumber = window.iti.getNumber();
                    mobileInput.value = fullNumber;
                }
            }
        }

        // If there are errors, prevent form submission
        if (hasErrors) {
            e.preventDefault();
            return false;
        }

        const submitBtn = document.getElementById('profileSubmitBtn');
        const spinner = submitBtn.querySelector('.spinner-border');

        submitBtn.disabled = true;
        spinner.classList.remove('d-none');
        submitBtn.innerHTML = spinner.outerHTML + ' <span class="btn-text">Loading...</span>';
    });

    document.getElementById('passwordForm').addEventListener('submit', function(e) {
        e.preventDefault();

        // Clear all existing errors first
        clearAllFieldErrors();

        // Validate password fields
        let hasErrors = false;

        const passwordFields = [
            { name: 'old_password', minLength: 8, maxLength: 14 },
            { name: 'new_password', minLength: 8, maxLength: 14 },
            { name: 'confirm_password', minLength: 8, maxLength: 14 }
        ];

        passwordFields.forEach(field => {
            const input = document.querySelector(`input[name="${field.name}"]`);
            if (input) {
                const value = input.value.trim();
                if (!value) {
                    showFieldError(input, '{{ __("messages.field_required") }}');
                    hasErrors = true;
                } else if (value.length < field.minLength || value.length > field.maxLength) {
                    showFieldError(input, '{{ __("messages.password_length_validation") }}'.replace('{min}', field.minLength).replace('{max}', field.maxLength));
                    hasErrors = true;
                }
            }
        });

        // Check password confirmation
        const newPassword = document.getElementById('new_password');
        const confirmPassword = document.getElementById('confirm_password');

        if (newPassword && confirmPassword) {
            const newPasswordValue = newPassword.value.trim();
            const confirmPasswordValue = confirmPassword.value.trim();

            if (confirmPasswordValue && newPasswordValue !== confirmPasswordValue) {
                showFieldError(confirmPassword, '{{ __("messages.passwords_must_match") }}');
                hasErrors = true;
            }
        }

        // If there are errors, prevent form submission
        if (hasErrors) {
            return false;
        }

        const submitBtn = document.getElementById('passwordSubmitBtn');
        const spinner = submitBtn.querySelector('.spinner-border');

        submitBtn.disabled = true;
        spinner.classList.remove('d-none');
        submitBtn.innerHTML = spinner.outerHTML + ' <span class="btn-text">Loading...</span>';

        // Submit the form
        this.submit();
    });
});

// Clear all field errors
function clearAllFieldErrors() {
    const errorElements = document.querySelectorAll('.field-error');
    errorElements.forEach(element => {
        element.classList.add('d-none');
        element.textContent = '';
    });

    const inputs = document.querySelectorAll('.form-control');
    inputs.forEach(input => {
        input.classList.remove('is-invalid');
    });
}

// Show field error
function showFieldError(input, message) {
    let errorElement = input.parentNode.querySelector('.field-error');

    if (errorElement) {
        errorElement.textContent = message;
        errorElement.classList.remove('d-none');
    } else {
        // Create error element if it doesn't exist (fallback)
        errorElement = document.createElement('span');
        errorElement.className = 'text-danger field-error mb-1';
        errorElement.textContent = message;
        input.parentNode.appendChild(errorElement);
    }
    input.classList.add('is-invalid');
}

// Password match validation
function validatePasswordMatch() {
    const newPassword = document.getElementById('new_password');
    const confirmPassword = document.getElementById('confirm_password');

    if (newPassword && confirmPassword) {
        const newPasswordValue = newPassword.value.trim();
        const confirmPasswordValue = confirmPassword.value.trim();

        if (confirmPasswordValue && newPasswordValue !== confirmPasswordValue) {
            showFieldError(confirmPassword, '{{ __("messages.passwords_must_match") }}');
            return false;
        } else {
            const errorElement = confirmPassword.parentNode.querySelector('.field-error');
            if (errorElement) {
                errorElement.classList.add('d-none');
            }
            confirmPassword.classList.remove('is-invalid');
        }
    }
    return true;
}
</script>
@endpush
