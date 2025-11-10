@extends('vendorwebsite::layouts.guest')

@section('content')
    <meta name="base-url" content="{{ url('/') }}">
    <meta name="csrf-token" content="{{ csrf_token() }}">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/intl-tel-input/17.0.19/css/intlTelInput.css">
    <style>
        .iti {
            width: 100%;
        }

        .iti__country-list {
            z-index: 1050;
        }

        .iti__flag-container {
            background: #f8f9fa;
            border-right: 1px solid #dee2e6;
        }

        .iti__selected-flag {
            background: #f8f9fa;
            border: 1px solid #dee2e6;
            border-radius: 0.375rem 0 0 0.375rem;
        }

        .iti__country-list {
            border: 1px solid #dee2e6;
            border-radius: 0.375rem;
        }

        .iti__country {
            padding: 8px 12px;
        }

        .iti__country:hover {
            background-color: #f8f9fa;
        }

        .iti__country.iti__highlight {
            background-color: #e9ecef;
        }
    </style>
    <section class="auth-page"
        style="background-image: url('{{ asset('img/vendorwebsite/sign-up-bg.png') }}'); backgound-repat: no-repeat; background-size: cover;">

        <div class="containe h-100">
            <div class="row align-items-center justify-content-center h-100">
                <div class="col-xl-8 col-md-10">
                    <div class="py-5 px-3">
                        <div class="register-bg register-background-padding">
                            <div class="text-center mb-5">

                                <a class="navbar-brand text-primary" href="{{ route('vendor.index') }}">
                                    <div class="logo-main">
                                        <div class="logo-mini d-none">
                                            <img src="{{ getVendorSetting('mini_logo') ? asset(getVendorSetting('mini_logo')) : asset('img/logo/mini_logo.png') }}"
                                                height="30" alt="{{ app_name() }}">
                                        </div>
                                        <div class="logo-normal">
                                            <img src="{{ getVendorSetting('logo') ? asset(getVendorSetting('logo')) : asset('img/logo/logo.png') }}"
                                                height="30" alt="{{ app_name() }}">
                                        </div>
                                        <div class="logo-dark">
                                            <img src="{{ getVendorSetting('dark_logo') ? asset(getVendorSetting('dark_logo')) : asset('img/logo/dark_logo.png') }}"
                                                height="30" alt="{{ app_name() }}">
                                        </div>
                                    </div>

                                </a>
                                <h5 class="mb-1 register-title">{{ __('vendorwebsite.create_your_account') }}</h5>
                                <p class="font-size-14 mb-5">{{ __('vendorwebsite.create_account_for_better_experience') }}
                                </p>

                            </div>


                            <form id="registerForm" method="POST"
                                action="{{ route('register', ['vendor_slug' => $vendorSlug]) }}" class="needs-validation"
                                novalidate>
                                @csrf

                                <input type="hidden" name="vendor_slug" value="{{ $vendorSlug }}">

                                <!-- <div id="error_message" class="text-danger mt-2 text-center"></div> -->
                                <div class="row gy-4 mt-5">
                                    <div class="col-lg-6">
                                        <div class="form-group">
                                            <label for="first_name"
                                                class="form-label fw-medium">{{ __('vendorwebsite.first_name') }}<span
                                                    class="text-danger">*</span></label>
                                            <div class="input-group custom-input-group">
                                                <input type="text" id="first_name" class="form-control"
                                                    placeholder="{{ __('vendorwebsite.first_name') }}'" name="first_name" required
                                                    data-error="First name is required" />
                                                <span class="input-group-text"><i class="ph ph-user"></i></span>
                                            </div>
                                            <div class="invalid-feedback" id="first_name_error">
                                                {{ __('vendorwebsite.first_name_field_is_required') }}</div>
                                        </div>
                                    </div>
                                    <div class="col-lg-6">
                                        <div class="form-group">
                                            <label for="last_name"
                                                class="form-label fw-medium">{{ __('vendorwebsite.last_name') }}<span
                                                    class="text-danger">*</span></label>
                                            <div class="input-group custom-input-group">
                                                <input type="text" id="last_name" name="last_name" class="form-control"
                                                    data-error="Last name is required" placeholder="{{ __('vendorwebsite.last_name') }}"
                                                    required />
                                                <span class="input-group-text"><i class="ph ph-user"></i></span>
                                            </div>
                                            <div class="invalid-feedback" id="last_name_error">
                                                {{ __('vendorwebsite.last_name_field_is_required') }}</div>
                                        </div>
                                    </div>
                                    <div class="col-lg-6">
                                        <div class="form-group">
                                            <label for="email"
                                                class="form-label fw-medium">{{ __('vendorwebsite.email') }}<span
                                                    class="text-danger">*</span></label>
                                            <div class="input-group custom-input-group">
                                                <input type="email" id="email" name="email" class="form-control"
                                                    placeholder="demo@gmail.com" data-error="Email is required" required />
                                                <span class="input-group-text"><i class="ph ph-envelope-simple"></i></span>
                                            </div>
                                            <div class="invalid-feedback" id="email_error">
                                                {{ __('vendorwebsite.email_field_is_required') }}</div>
                                        </div>
                                    </div>
                                    <div class="col-lg-6">
                                        <div class="form-group">
                                            <label for="password"
                                                class="form-label fw-medium">{{ __('vendorwebsite.password') }}<span
                                                    class="text-danger">*</span></label>
                                            <div class="input-group custom-input-group">
                                                <input type="password" id="password" name="password" class="form-control"
                                                    placeholder="eg '#123@Abc'" data-error="Password is required"
                                                    required />
                                                <span class="input-group-text"><i class="ph ph-eye-slash"
                                                        id="togglePassword"></i></span>
                                            </div>
                                            <div class="invalid-feedback" id="password_error">
                                                {{ __('vendorwebsite.password_field_is_required') }}</div>
                                        </div>
                                    </div>
                                    <div class="col-lg-6">
                                        <div class="form-group">
                                            <label for="confirm_password"
                                                class="form-label fw-medium">{{ __('vendorwebsite.confirm_password') }}<span
                                                    class="text-danger">*</span></label>
                                            <div class="input-group custom-input-group">
                                                <input type="password" id="confirm_password" name="confirm_password"
                                                    class="form-control" data-error="Confirm password is required"
                                                    placeholder="Confirm your password" required />
                                                <span class="input-group-text"><i class="ph ph-eye-slash"
                                                        id="toggleConfirmPassword"></i></span>
                                            </div>
                                            <div class="invalid-feedback" id="confirm_password_error">
                                                {{ __('vendorwebsite.confirm_password_field_is_required') }}</div>
                                        </div>
                                    </div>
                                    <div class="col-lg-6">
                                        <label class="form-label">{{ __('vendorwebsite.gender') }}<span
                                                class="text-danger">*</span></label>
                                        <div class="select-gender d-flex align-items-center flex-wrap gap-3">
                                            <div class="form-check">
                                                <label class="form-check-label" for="gender_male">
                                                    <input class="form-check-input" value="male" type="radio"
                                                        name="gender" id="gender_male" checked />
                                                    {{ __('vendorwebsite.male') }}
                                                </label>
                                            </div>
                                            <div class="form-check">
                                                <label class="form-check-label" for="gender_female">
                                                    <input class="form-check-input" type="radio" value="female"
                                                        name="gender" id="gender_female" />
                                                    {{ __('vendorwebsite.female') }}
                                                </label>
                                            </div>
                                            {{-- <div class="form-check">
                                                <label class="form-check-label" for="gender_other">
                                                    <input class="form-check-input" value="other" type="radio"
                                                        name="gender" id="gender_other" />
                                                    {{ __('vendorwebsite.other') }}
                                                </label>
                                            </div> --}}
                                        </div>
                                        <div class="invalid-feedback" id="gender_error">
                                            {{ __('vendorwebsite.gender_is_required') }}</div>
                                    </div>
                                    <div class="col-lg-6">
                                        <div class="form-group">
                                            <label for="mobile"
                                                class="form-label fw-medium">{{ __('vendorwebsite.contact_number') }}<span
                                                    class="text-danger">*</span></label>
                                            <div class="mb-3">
                                                <input type="tel" id="mobile" name="mobile" class="form-control"
                                                    placeholder="50 113 4311" data-error="Mobile number is required"
                                                    required />
                                            </div>
                                            <script>
                                                document.addEventListener('DOMContentLoaded', function() {
                                                    const mobileInput = document.querySelector('#mobile');
                                                    if (mobileInput && window.intlTelInput) {
                                                        // Initialize intl-tel-input
                                                        const iti = window.intlTelInput(mobileInput, {
                                                            initialCountry: "gh",
                                                            separateDialCode: true,
                                                            utilsScript: "https://cdnjs.cloudflare.com/ajax/libs/intl-tel-input/17.0.19/js/utils.js",
                                                            preferredCountries: ['in', 'us', 'gb', 'au', 'ca'],
                                                            onlyCountries: [], // Allow all countries
                                                            autoPlaceholder: "aggressive"
                                                        });

                                                        // Store iti instance globally for form submission
                                                        window.iti = iti;

                                                        console.log('intl-tel-input initialized successfully');
                                                    } else {
                                                        console.error('intl-tel-input library not loaded or mobile input not found');
                                                    }
                                                });
                                            </script>
                                            <div class="invalid-feedback" id="mobile_error">
                                                {{ __('vendorwebsite.contact_number_is_required') }}</div>
                                            <div id="mobile-error-msg" class="text-danger mt-1" style="display:none;">
                                            </div>
                                        </div>
                                    </div>
                                </div>
                                <!-- <div class="col-12 mt-5">
                                                                                    <div class="referral-code bg-purple rounded text-center">
                                                                                        <h6 class="font-size-14">Do You have a referral code ? (Optional)</h6>
                                                                                        <div class="row gy-3">
                                                                                            <div class="col-xl-4 col-lg-3 d-lg-block d-none"></div>
                                                                                            <div class="col-xl-4 col-lg-6">
                                                                                                <input type="text" id="referral_code" class="form-control referral-code-input" placeholder="Referral Code" name="referral_code">
                                                                                                <span class="text-success font-size-12 fw-medium mt-8">Success! Your referral is good to go!</span>
                                                                                                <span class="text-danger font-size-12 fw-medium mt-8">Oops! That Referral Code doesn't Exist</span>
                                                                                                <div class="invalid-feedback d-block"></div>
                                                                                            </div>
                                                                                            <div class="col-xl-4 col-lg-3 d-lg-block d-none"></div>
                                                                                        </div>
                                                                                    </div>
                                                                                </div> -->

                                <div class="col-lg-12">
                                    <div
                                        class="d-flex align-items-center justify-content-center flex-wrap gap-3 register-btn">
                                        <button id="register-button" class="btn btn-secondary px-sm-5 px-3"
                                            type="submit" data-login-text="Sign Up">
                                            {{ __('vendorwebsite.sign_up') }}
                                        </button>
                                    </div>
                                    <div class="d-flex justify-content-center flex-wrap gap-1 mt-3">
                                        <span
                                            class="font-size-14 text-body">{{ __('vendorwebsite.already_have_an_account') }}</span>
                                        <a href="{{ route('vendor.login', ['vendor_slug' => $vendorSlug]) }}"
                                            class="text-primary font-size-14 fw-medium text-decoration-underline">{{ __('vendorwebsite.sign_in') }}</a>
                                    </div>
                                </div>
                            </form>
                        </div>
                    </div>
                </div>

            </div>
        </div>
    </section>
    <!-- Intl-tel-input and other scripts are now loaded in the main layout -->
    <script src="https://cdnjs.cloudflare.com/ajax/libs/intl-tel-input/17.0.19/js/intlTelInput.min.js"></script>
    <script src="{{ asset('js/auth.min.js') }}" defer></script>


    <script>
        document.addEventListener('DOMContentLoaded', function() {
            const form = document.getElementById('registerForm');
            const errorMessage = document.getElementById('error_message');

            // Password visibility toggles
            const togglePassword = document.getElementById('togglePassword');
            const toggleConfirmPassword = document.getElementById('toggleConfirmPassword');
            const password = document.getElementById('password');
            const confirmPassword = document.getElementById('confirm_password');

            // Toggle password visibility
            if (togglePassword && password) {
                togglePassword.addEventListener('click', function() {
                    const type = password.getAttribute('type') === 'password' ? 'text' : 'password';
                    password.setAttribute('type', type);
                    this.classList.toggle('ph-eye');
                    this.classList.toggle('ph-eye-slash');
                });
            }

            if (toggleConfirmPassword && confirmPassword) {
                toggleConfirmPassword.addEventListener('click', function() {
                    const type = confirmPassword.getAttribute('type') === 'password' ? 'text' : 'password';
                    confirmPassword.setAttribute('type', type);
                    this.classList.toggle('ph-eye');
                    this.classList.toggle('ph-eye-slash');
                });
            }

            // Form validation
            if (form) {
                form.addEventListener('submit', function(event) {
                    event.preventDefault();

                    // Reset all error states
                    const invalidFeedbacks = form.querySelectorAll('.invalid-feedback');
                    invalidFeedbacks.forEach(feedback => {
                        feedback.textContent = '';
                        feedback.style.display = 'none';
                    });

                    const formControls = form.querySelectorAll('.form-control');
                    formControls.forEach(control => {
                        control.classList.remove('is-invalid');
                    });

                    let isValid = true;
                    const errors = [];

                    // Validate required fields
                    form.querySelectorAll('[required]').forEach(field => {
                        if (!field.value.trim()) {
                            isValid = false;
                            field.classList.add('is-invalid');
                            const feedback = field.closest('.form-group')?.querySelector(
                                '.invalid-feedback');
                            if (feedback) {
                                feedback.textContent = field.getAttribute('data-error') ||
                                    'This field is required';
                                feedback.style.display = 'block';
                            }
                            errors.push(field.getAttribute('data-error'));
                        }
                    });

                    // Email validation
                    const email = document.getElementById('email');
                    if (email && email.value) {
                        const emailRegex = /^[^\s@]+@[^\s@]+\.[^\s@]+$/;
                        if (!emailRegex.test(email.value)) {
                            isValid = false;
                            email.classList.add('is-invalid');
                            const feedback = email.closest('.form-group')?.querySelector(
                                '.invalid-feedback');
                            if (feedback) {
                                feedback.textContent = 'Please enter a valid email address';
                                feedback.style.display = 'block';
                            }
                            errors.push('Invalid email format');
                        }
                    }

                    // Password validation
                    if (password && password.value) {
                        if (password.value.length < 8) {
                            isValid = false;
                            password.classList.add('is-invalid');
                            const feedback = password.closest('.form-group')?.querySelector(
                                '.invalid-feedback');
                            if (feedback) {
                                feedback.textContent = 'Password must be at least 8 characters long';
                                feedback.style.display = 'block';
                            }
                            errors.push('Password must be at least 8 characters long');
                        }
                    }

                    // Confirm password validation
                    if (confirmPassword && password) {
                        if (confirmPassword.value !== password.value) {
                            isValid = false;
                            confirmPassword.classList.add('is-invalid');
                            const feedback = confirmPassword.closest('.form-group')?.querySelector(
                                '.invalid-feedback');
                            if (feedback) {
                                feedback.textContent = 'Passwords do not match';
                                feedback.style.display = 'block';
                            }
                            errors.push('Passwords do not match');
                        }
                    }

                    // Mobile number validation with intl-tel-input
                    const mobile = document.getElementById('mobile');
                    if (mobile && mobile.value) {
                        if (window.iti && !window.iti.isValidNumber()) {
                            isValid = false;
                            mobile.classList.add('is-invalid');
                            const feedback = mobile.closest('.form-group')?.querySelector(
                                '.invalid-feedback');
                            if (feedback) {
                                feedback.textContent = 'Please enter a valid mobile number';
                                feedback.style.display = 'block';
                            }
                            errors.push('Invalid mobile number format');
                        }
                    }

                    if (!isValid) {
                        if (errorMessage) {
                            errorMessage.textContent = 'Please fix the errors in the form.';
                            errorMessage.style.display = 'block';
                        }
                        return;
                    }

                    // Show loading indicator
                    const loadingIndicator = form.querySelector('.loading-indicator');
                    const submitButton = form.querySelector('button[type="submit"]');
                    const originalButtonText = submitButton ? submitButton.innerHTML : '';

                    if (loadingIndicator) loadingIndicator.classList.remove('d-none');
                    if (submitButton) {
                        submitButton.disabled = true;
                        submitButton.innerHTML = 'Processing...';
                    }

                    // Prepare form data
                    const formData = new FormData(form);


                    // Get full phone number with country code
                    if (window.iti) {
                        const fullNumber = window.iti.getNumber();

                        // Validate the phone number
                        if (window.iti.isValidNumber()) {
                            formData.set('mobile', fullNumber);
                        } else {
                            isValid = false;
                            mobile.classList.add('is-invalid');
                            const feedback = mobile.closest('.form-group')?.querySelector(
                                '.invalid-feedback');
                            if (feedback) {
                                feedback.textContent = 'Please enter a valid mobile number';
                                feedback.style.display = 'block';
                            }
                        }
                    }

                    // Get CSRF token
                    const token = document.querySelector('meta[name="csrf-token"]').getAttribute('content');
                    const baseUrl = document.querySelector('meta[name="base-url"]').getAttribute('content');

                    // Submit form via AJAX for better error handling
                    fetch(form.action, {
                            method: 'POST',
                            body: formData,
                            headers: {
                                'X-CSRF-TOKEN': token,
                                'Accept': 'application/json',
                            }
                        })
                        .then(response => {
                            return response.json().then(data => {
                                if (response.ok && data.success) {
                                    // Registration successful, redirect to vendor index
                                    if (data.redirect_url) {
                                        window.location.href = data.redirect_url;
                                    } else {
                                        const vendorSlug = formData.get('vendor_slug');
                                        if (vendorSlug) {
                                            window.location.href = `/${vendorSlug}`;
                                        } else {
                                            window.location.href = '/';
                                        }
                                    }
                                } else {
                                    throw new Error(data.message || 'Registration failed');
                                }
                            });
                        })
                        .catch(error => {
                            console.error('Registration error:', error);
                            if (errorMessage) {
                                errorMessage.textContent = error.message ||
                                    'Registration failed. Please try again.';
                                errorMessage.style.display = 'block';
                            }

                            // Reset button state
                            if (submitButton) {
                                submitButton.disabled = false;
                                submitButton.innerHTML = originalButtonText;
                            }
                        });
                });

                // Clear validation on input
                form.querySelectorAll('.form-control').forEach(input => {
                    input.addEventListener('input', function() {
                        this.classList.remove('is-invalid');
                        const feedback = this.closest('.form-group')?.querySelector(
                            '.invalid-feedback');
                        if (feedback) {
                            feedback.textContent = '';
                            feedback.style.display = 'none';
                        }
                        if (errorMessage) {
                            errorMessage.style.display = 'none';
                        }
                    });
                });
            }
        });
    </script>

    @push('after-scripts')
        <script>
            // Mobile number input validation with intl-tel-input
            $(document).ready(function() {
                $('#mobile').on('input', function(e) {
                    var $error = $('#mobile-error-msg');

                    // Use intl-tel-input validation if available
                    if (window.iti) {
                        if (window.iti.isValidNumber()) {
                            $error.hide();
                        } else {
                            $error.text('Please enter a valid mobile number').show();
                        }
                    } else {
                        // Fallback validation
                        var value = $(this).val();
                        if (/[^0-9+\s-]/.test(value)) {
                            $error.text('Only numbers, +, - and spaces are allowed.').show();
                            $(this).val(value.replace(/[^0-9+\s-]/g, ''));
                        } else {
                            $error.hide();
                        }
                    }
                });
            });
        </script>
    @endpush
