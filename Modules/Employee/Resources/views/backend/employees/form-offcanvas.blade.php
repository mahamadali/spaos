<form id="employee-form"
    action="{{ isset($employee) ? route('backend.employees.update', $employee->id) : route('backend.employees.store') }}"
    method="POST" enctype="multipart/form-data" novalidate>
    @csrf

    <div class="offcanvas offcanvas-end" id="form-offcanvas" tabindex="-1" aria-labelledby="form-offcanvasLabel"
        style="--bs-offcanvas-width: 95vw; max-width:60%;">
        {{-- Header --}}
        <div class="offcanvas-header">
            <h5 class="offcanvas-title">
                {{ isset($employee) ? $edit_title : $create_title }}
            </h5>
            <button type="button" class="btn-close" data-bs-dismiss="offcanvas"></button>
        </div>

        <div class="offcanvas-body">
            <div class="row">
                {{-- Left Column --}}
                <div class="col-md-8">
                    <div class="row">
                        {{-- First Name --}}
                        <div class="col-md-6 mb-3">
                            <label class="form-label">{{ __('employee.lbl_first_name') }}
                                <span class="text-danger">*</span></label>
                            <input type="text" name="first_name" placeholder="{{ __('employee.first_name') }}"
                                class="form-control" value="{{ old('first_name', $employee->first_name ?? '') }}"
                                required>
                            @error('first_name')
                                <span class="text-danger">{{ $message }}</span>
                            @enderror
                        </div>

                        {{-- Last Name --}}
                        <div class="col-md-6 mb-3">
                            <label class="form-label">{{ __('employee.lbl_last_name') }} <span
                                    class="text-danger">*</span></label>
                            <input type="text" name="last_name" class="form-control"
                                placeholder="{{ __('employee.last_name') }}"
                                value="{{ old('last_name', $employee->last_name ?? '') }}" required>
                            @error('last_name')
                                <span class="text-danger">{{ $message }}</span>
                            @enderror
                        </div>

                        {{-- Email --}}
                        <div class="col-md-6 mb-3">
                            <label class="form-label">{{ __('employee.lbl_Email') }} <span
                                    class="text-danger">*</span></label>
                            <input type="email" name="email" class="form-control"
                                placeholder="{{ __('employee.email_address') }}"
                                value="{{ old('email', $employee->email ?? '') }}" required>
                            @error('email')
                                <span class="text-danger">{{ $message }}</span>
                            @enderror
                        </div>

                        {{-- Phone --}}
                        <div class="col-md-6 mb-3">
                            <label class="form-label">{{ __('employee.lbl_phone_number') }} <span
                                    class="text-danger">*</span></label>
                            <input type="tel" id="mobile" name="mobile" class="form-control"
                                placeholder="{{ __('messages.placeholder_phone') }}"
                                value="{{ old('mobile', $employee->mobile ?? '') }}" maxlength="15" required>
                            @error('mobile')
                                <span class="text-danger">{{ $message }}</span>
                            @enderror
                        </div>

                    </div>
                </div>

                {{-- Right Column: Profile Image --}}
                <div class="col-md-4 text-center mb-3">
                    <img src="{{ $employee->profile_image ?? $default_image }}"
                        data-default-src="{{ $default_image }}" class="img-fluid avatar avatar-120 avatar-rounded mb-2"
                        alt="profile-image">
                    <div class="d-flex align-items-center justify-content-center gap-2">
                        <input type="file" class="form-control d-none" id="logo" name="profile_image"
                            accept=".jpeg, .jpg, .png, .gif" onchange="previewImage(event)" />
                        <label class="btn btn-sm btn-primary" for="logo">{{ __('messages.upload') }}</label>
                        <button type="button" class="btn btn-sm btn-secondary" name="remove"
                            onclick="removeImage(event)" style="display:none;">
                            {{ __('messages.remove') }}
                        </button>
                    </div>
                    @error('profile_image')
                        <span class="text-danger">{{ $message }}</span>
                    @enderror
                </div>

                {{-- Passwords (only for new employee) --}}

                <div class="col-md-6 mb-3 password-fields">
                    <label class="form-label">{{ __('employee.lbl_password') }} <span
                            class="text-danger">*</span></label>
                    <input type="password" name="password" class="form-control" id="employee_password"
                        placeholder="{{ __('employee.password') }}">
                    @error('password')
                        <span class="text-danger">{{ $message }}</span>
                    @enderror
                </div>
                <div class="col-md-6 mb-3 password-fields">
                    <label class="form-label">{{ __('employee.lbl_confirm_password') }} <span
                            class="text-danger">*</span></label>
                    <input type="password" name="confirm_password" class="form-control" id="employee_confirm_password"
                        placeholder="{{ __('employee.confirm_password') }}">
                    @error('confirm_password')
                        <span class="text-danger">{{ $message }}</span>
                    @enderror
                </div>


                {{-- Gender --}}
                <div class="form-group col-md-4 mb-3">
                    <label class="form-label w-100 mb-2">{{ __('employee.lbl_gender') }}</label>
                    <div class="border rounded p-3">
                        @foreach (['male' => __('messages.male'), 'female' => __('messages.female'), 'other' => __('messages.intersex')] as $value => $label)
                            <div class="form-check form-check-inline">
                                <input class="form-check-input" type="radio" name="gender"
                                    value="{{ $value }}" id="gender_{{ $value }}"
                                    {{ old('gender', $employee->gender ?? 'male') == $value ? 'checked' : '' }}>
                                <label class="form-check-label" for="gender_{{ $value }}">{{ $label }}</label>
                            </div>
                        @endforeach
                    </div>
                    @error('gender')
                        <span class="text-danger">{{ $message }}</span>
                    @enderror
                </div>

                {{-- Show in Calendar --}}
                <div class="form-group col-md-4 mb-3">
                    <label class="form-label mb-2">{{ __('employee.lbl_show_in_calender') }}</label>
                    <div class="border rounded p-3">
                        <div class="form-check">
                            <input class="form-check-input" type="checkbox" name="show_in_calender" value="1" id="show_in_calender"
                                {{ old('show_in_calender', $employee->show_in_calender ?? 1) ? 'checked' : '' }}>
                            <label class="form-check-label" for="show_in_calender">
                                {{ __('employee.lbl_show_in_calender') }}
                            </label>
                        </div>
                    </div>
                </div>

                {{-- Is Manager --}}
                <div class="form-group col-md-4 mb-3">
                    <label class="form-label mb-2">{{ __('employee.lbl_is_manager') }}</label>
                    <div class="border rounded p-3">
                        <div class="form-check">
                            <input class="form-check-input" type="checkbox" name="is_manager" value="1" id="is_manager"
                                {{ old('is_manager', $employee->is_manager ?? 0) ? 'checked' : '' }}>
                            <label class="form-check-label" for="is_manager">
                                {{ __('employee.lbl_is_manager') }}
                            </label>
                        </div>
                    </div>
                </div>

                {{-- Branch --}}
                <div class="form-group col-md-12 mb-3">
                    <label class="form-label">{{ __('employee.lbl_select_branch') }} <span
                            class="text-danger">*</span></label>
                    <select name="branch_id" id="branch" class="form-select select2 branch-select"
                        data-placeholder="{{ __('messages.select_branch') }}" required>
                        <option value="">{{ __('messages.select_branch') }}</option>
                        @foreach ($branches as $branch)
                            <option value="{{ $branch->id }}"
                                {{ old('branch_id', isset($employee) ? $employee->branch_id ?? $selected_session_branch_id : $selected_session_branch_id) == $branch->id ? 'selected' : '' }}>
                                {{ $branch->name }}
                            </option>
                        @endforeach
                    </select>
                    @error('branch_id')
                        <span class="text-danger">{{ $message }}</span>
                    @enderror
                </div>

                {{-- Services --}}
                <div class="form-group col-md-12 mb-3">
                    <label class="form-label">{{ __('employee.lbl_select_service') }}</label>
                    <select name="service_id[]" id="services" multiple class="form-select select2"
                        style="width:100%" data-placeholder="{{ __('branch.select_service') }}">
                        <option value="">{{ __('employee.lbl_select_service') }}</option>
                            <option value=""></option>

                    </select>
                    @error('service_id')
                        <span class="text-danger">{{ $message }}</span>
                    @enderror
                </div>

                {{-- Commission --}}
                <div class="form-group col-md-12 mb-3">
                    <label class="form-label">{{ __('employee.lbl_select_commission') }} <span
                            class="text-danger">*</span></label>
                    <select name="commission_id" id="commission" class="form-select select2"
                        data-placeholder="{{ __('employee.lbl_select_commission') }}" required>
                        <option value="">{{ __('employee.lbl_select_commission') }}</option>
                        @foreach ($commissions as $commission)
                            <option value="{{ $commission->id }}"
                                {{ old('commission_id', isset($employee) ? $employee->commission_id : '') == $commission->id ? 'selected' : '' }}>
                                {{ $commission->title }}
                            </option>
                        @endforeach
                    </select>
                    @error('commission_id')
                        <span class="text-danger">{{ $message }}</span>
                    @enderror
                </div>

                {{-- Custom Fields --}}
                @foreach ($customefield as $field)
                    <div class="form-group col-md-12 mb-3">
                        <label class="form-label">{{ $field['label'] }} @if ($field['required'])
                                <span class="text-danger">*</span>
                            @endif
                        </label>
                        <input type="{{ $field['type'] }}" name="custom_fields[{{ $field['id'] }}]"
                            class="form-control"
                            value="{{ old('custom_fields.' . $field['id'], $employee->custom_fields[$field['id']] ?? '') }}">
                    </div>
                @endforeach

                {{-- About Self --}}
                <div class="col-md-6 mb-3">
                    <label class="form-label">{{ __('employee.lbl_about_self') }}</label>
                    <input type="text" name="about_self" class="form-control"
                        placeholder="{{ __('employee.about_self') }}"
                        value="{{ old('about_self', $employee->about_self ?? '') }}">
                    @error('about_self')
                        <span class="text-danger">{{ $message }}</span>
                    @enderror
                </div>

                {{-- Expert --}}
                <div class="col-md-6 mb-3">
                    <label class="form-label">{{ __('employee.lbl_expert') }}</label>
                    <input type="text" name="expert" class="form-control"
                        placeholder="{{ __('employee.expert') }}"
                        value="{{ old('expert', $employee->expert ?? '') }}">
                    @error('expert')
                        <span class="text-danger">{{ $message }}</span>
                    @enderror
                </div>

                {{-- Social Links --}}
                <div class="col-md-6 mb-3">
                    <label class="form-label">{{ __('employee.lbl_facebook_link') }}</label>
                    <input type="url" name="facebook_link" class="form-control"
                        placeholder="{{ __('employee.facebook_link') }}"
                        value="{{ old('facebook_link', $employee->facebook_link ?? '') }}">
                </div>
                <div class="col-md-6 mb-3">
                    <label class="form-label">{{ __('employee.lbl_instagram_link') }}</label>
                    <input type="url" name="instagram_link" class="form-control"
                        placeholder="{{ __('employee.instagram_link') }}"
                        value="{{ old('instagram_link', $employee->instagram_link ?? '') }}">
                </div>
                <div class="col-md-6 mb-3">
                    <label class="form-label">{{ __('employee.lbl_twitter_link') }}</label>
                    <input type="url" name="twitter_link" class="form-control"
                        placeholder="{{ __('employee.lbl_twitter_link') }}"
                        value="{{ old('twitter_link', $employee->twitter_link ?? '') }}">
                </div>
                <div class="col-md-6 mb-3">
                    <label class="form-label">{{ __('employee.lbl_dribbble_link') }}</label>
                    <input type="url" name="dribbble_link" class="form-control"
                        placeholder="{{ __('employee.dribble_link') }}"
                        value="{{ old('dribbble_link', $employee->dribbble_link ?? '') }}">
                </div>

                {{-- Status --}}
                <div class="form-group col-md-12 mb-3">
                    <label class="form-label">{{ __('employee.lbl_status') }}</label>
                    <div class="form-check form-switch">
                        <input type="checkbox" class="form-check-input" name="status" value="1"
                            {{ old('status', $employee->status ?? 1) ? 'checked' : '' }}>
                    </div>
                </div>
            </div>
        </div>

        {{-- Footer --}}
        <div class="offcanvas-footer border-top">
            <div class="d-grid d-md-flex gap-3 p-3">

                {{-- Save Button --}}
                <button type="submit" class="btn btn-primary" name="submit" id="saveBtn"
                    {{ isset($isSubmitted) && $isSubmitted ? 'disabled' : '' }}>
                    @if (!empty($isSubmitted) && $isSubmitted)
                        <span class="spinner-border spinner-border-sm" role="status" aria-hidden="true"></span>
                        {{ __('messages.loading') }}
                    @else
                        <i class="fa-solid fa-floppy-disk"></i> {{ __('messages.save') }}
                    @endif
                </button>

                {{-- Close Button --}}
                <button class="btn btn-outline-primary d-block" type="button" data-bs-dismiss="offcanvas">
                    <i class="fa-solid fa-angles-left"></i>
                    {{ __('messages.close') }}
                </button>

            </div>
        </div>
    </div>
</form>
<script>
    // Error handling and validation
    document.addEventListener("DOMContentLoaded", function() {
        const form = document.getElementById("employee-form");

        if (!form) return;

        // 🔹 Common helper for showing error in correct place
        function showError(input, message) {
            const error = document.createElement("span");
            error.classList.add("text-danger", "js-error");
            error.innerText = message;

            // Try to append inside .mb-3 / .form-group
            const container = input.closest(".mb-3, .form-group");
            if (container) {
                container.appendChild(error); // ✅ always aligns below field
            } else {
                input.insertAdjacentElement("afterend", error);
            }
        }

        form.addEventListener("submit", function(e) {
            let valid = true;

            // clear old error messages
            form.querySelectorAll(".text-danger.js-error").forEach(el => el.remove());

            // Pick ONLY fields marked with required
            const requiredFields = form.querySelectorAll("[required]:not(#employee_password):not(#employee_confirm_password)");

            requiredFields.forEach(input => {
                // Handle Select2 dropdowns - check the actual select value
                let fieldValue = '';
                if (input.tagName === 'SELECT' && $(input).hasClass('select2-hidden-accessible')) {
                    fieldValue = $(input).val();
                    // For multiple select, check if array has values
                    if (input.multiple && Array.isArray(fieldValue)) {
                        fieldValue = fieldValue.length > 0 ? fieldValue.join(',') : '';
                    }
                } else {
                    fieldValue = input.value.trim();
                }

                if (!fieldValue || fieldValue === '') {
                    valid = false;

                    // Find label text
                    let label =
                        input.closest(".mb-3, .form-group")?.querySelector("label")
                        ?.innerText ||
                        input.name;

                    // Don't add is-invalid class to avoid red border
                    // input.classList.add("is-invalid");
                    // For Select2, also don't add to container
                    if ($(input).hasClass('select2-hidden-accessible')) {
                        $(input).next('.select2-container').find('.select2-selection').removeClass('is-invalid');
                    }

                    // show error with helper
                    showError(input, `${label.replace('*', '').trim()} is required.`);
                } else {
                    input.classList.remove("is-invalid");
                    // For Select2, also remove from container
                    if ($(input).hasClass('select2-hidden-accessible')) {
                        $(input).next('.select2-container').find('.select2-selection').removeClass('is-invalid');
                    }
                    // Clear error message if field is valid
                    const container = input.closest(".mb-3, .form-group");
                    const error = container?.querySelector(".text-danger.js-error");
                    if (error) error.remove();
                }
            });

            // Email validation
            const emailInput = form.querySelector('[name="email"]');
            if (emailInput && emailInput.value.trim()) {
                const emailRegex = /^[^\s@]+@[^\s@]+\.[^\s@]+$/;
                if (!emailRegex.test(emailInput.value)) {
                    valid = false;
                    // Don't add is-invalid class to avoid red border
                    // emailInput.classList.add("is-invalid");
                    showError(emailInput, "Invalid email address.");
                } else {
                    emailInput.classList.remove("is-invalid");
                    // Clear error message if field is valid
                    const container = emailInput.closest(".mb-3, .form-group");
                    const error = container?.querySelector(".text-danger.js-error");
                    if (error) error.remove();
                }
            }

            // ✅ Mobile validation (add here)
            const mobileInput = form.querySelector('[name="mobile"]');
            if (mobileInput && mobileInput.value.trim()) {
                const phoneRegex = /^(\+?\d+)?(\s?\d+)*$/;
                if (!phoneRegex.test(mobileInput.value.trim())) {
                    valid = false;
                    // Don't add is-invalid class to avoid red border
                    // mobileInput.classList.add("is-invalid");
                    showError(mobileInput, "Phone number must contain only digits");
                } else {
                    mobileInput.classList.remove("is-invalid");
                    // Clear error message if field is valid
                    const container = mobileInput.closest(".mb-3, .form-group");
                    const error = container?.querySelector(".text-danger.js-error");
                    if (error) error.remove();
                }
            }
            // Password validation
            const password = form.querySelector('#employee_password');
            const confirmPassword = form.querySelector('#employee_confirm_password');
            const isEditMode = form.querySelector('input[name="_method"]')?.value === "PUT";
            if (password && confirmPassword) {
                if (!isEditMode) {
                    if (password) password.setAttribute("required", "true");
                    if (confirmPassword) confirmPassword.setAttribute("required", "true");
                    // ✅ Only validate in create mode
                    if (!password.value.trim()) {
                        valid = false;
                        // Don't add is-invalid class to avoid red border
                        // password.classList.add("is-invalid");
                        showError(password, "Password is required.");
                    }
                    // Password length check
                    else if (password.value.length < 8 || password.value.length > 14) {
                        valid = false;
                        // Don't add is-invalid class to avoid red border
                        // password.classList.add("is-invalid");
                        showError(password, "Password length should be 8 to 14 characters long.");
                    } else {
                        password.classList.remove("is-invalid");
                        // Clear error message if field is valid
                        const container = password.closest(".mb-3, .form-group");
                        const error = container?.querySelector(".text-danger.js-error");
                        if (error) error.remove();
                    }

                    if (!confirmPassword.value.trim()) {
                        valid = false;
                        // Don't add is-invalid class to avoid red border
                        // confirmPassword.classList.add("is-invalid");
                        showError(confirmPassword, "Confirm password is required.");
                    } else if (password.value !== confirmPassword.value) {
                        valid = false;
                        // Don't add is-invalid class to avoid red border
                        // confirmPassword.classList.add("is-invalid");
                        showError(confirmPassword, "Passwords do not match.");
                    } else {
                        confirmPassword.classList.remove("is-invalid");
                        // Clear error message if field is valid
                        const container = confirmPassword.closest(".mb-3, .form-group");
                        const error = container?.querySelector(".text-danger.js-error");
                        if (error) error.remove();
                    }
                }else{
                if (password) password.removeAttribute("required");
                if (confirmPassword) confirmPassword.removeAttribute("required");
            }
            }

            if (!valid) {
                e.preventDefault(); // stop form submit
                console.log("Form validation failed");
            }
        });
    });
    // Phone input with intl-tel-input
    document.addEventListener("DOMContentLoaded", function() {
        const input = document.querySelector("#mobile");
        const iti = window.intlTelInput(input, {
            initialCountry: 'in',
            preferredCountries: ['in', 'us', 'gb', 'au', 'ca'],
            separateDialCode: true,
            utilsScript: "/js/utils.js",
            autoHideDialCode: false,
            autoPlaceholder: 'aggressive',
            formatOnDisplay: true,
            nationalMode: false,
            geoIpLookup: function(callback) {
                fetch('https://ipapi.co/json')
                    .then(response => response.json())
                    .then(data => callback(data.country_code))
                    .catch(() => callback('us'));
            },
        });

        // On form submit, update input value with full international number
        const form = input.closest("form");
        form.addEventListener("submit", function() {
            if (iti.isValidNumber()) {
                input.value = iti.getNumber(); // full number with country code
            }
        });
    });
    // Select2 initialization for single and multiple selects
    if (window.$ && $.fn.select2) {
        const initSingleSelect2 = function(selector, options) {
            const $el = $(selector);
            if (!$el.length) return;

            // If already initialized elsewhere, destroy then re-init to avoid duplicates
            if ($el.hasClass('select2-hidden-accessible')) {
                $el.select2('destroy');
            }
            // Remove any duplicate Select2 containers created by other scripts
            const $containers = $el.siblings('.select2.select2-container');
            if ($containers.length > 0) {
                $containers.remove();
            }

            // Initialize only if not already initialized
            if (!$el.hasClass('select2-hidden-accessible')) {
                $el.select2(options);
            }
            // Hide original select to avoid double boxes
            $el.css({
                display: 'none'
            });
        };

        initSingleSelect2('#services', {
            width: '100%',
            placeholder: $('#services').data('placeholder'),
            allowClear: true
        });
        initSingleSelect2('#branch', {
            width: '100%',
            placeholder: $('#branch').data('placeholder'),
            allowClear: true
        });
        initSingleSelect2('#commission', {
            width: '100%',
            placeholder: $('#commission').data('placeholder'),
            allowClear: true
        });
    }

    document.addEventListener("DOMContentLoaded", function() {
        const $branch = $('.branch-select'); // Branch dropdown
        const $services = $('#services');     // Services multi-select

        // Initialize Select2
        if ($branch.length) {
            $branch.select2({
                width: '100%',
                placeholder: $branch.data('placeholder'),
                allowClear: true
            });
        }

        if ($services.length) {
            $services.select2({
                width: '100%',
                placeholder: $services.data('placeholder'),
                allowClear: true
            });
        }

        // Function to load services based on branch ID
        function loadServices(branchId, preselected = []) {
            if (!branchId) {
                $services.empty().trigger('change');
                return;
            }

            fetch(`{{ route('backend.services.index_list') }}?branch_id=${branchId}`, {
                headers: {
                    'X-Requested-With': 'XMLHttpRequest',
                    'Accept': 'application/json'
                }
            })
            .then(res => res.json())
            .then(data => {
                $services.empty();
                if (data && data.length) {
                    data.forEach(service => {
                        const selected = preselected.includes(service.id);
                        const option = new Option(service.name, service.id, selected, selected);
                        $services.append(option);
                    });
                }
                $services.trigger('change'); // refresh Select2 UI
            })
            .catch(err => console.error("Error loading services:", err));
        }

        // Event: when branch changes
        $branch.on('select2:select', function() {
            const branchId = $(this).val();
            loadServices(branchId);
        });

        // Optional: load services on page load if a branch is already selected (edit mode)
        if ($branch.val()) {
            const preselectedServices = $services.val() || []; // array of service IDs
            loadServices($branch.val(), preselectedServices);
        }
    });

    // Image preview and remove functions
    window.previewImage = function(event) {
        const reader = new FileReader();
        reader.onload = function() {
            const container = event.target.closest('.col-md-4'); // target the correct wrapper
            const preview = container?.querySelector('img');
            if (preview) {
                preview.src = reader.result;
            }

            // show remove button if exists
            const removeBtn = container?.querySelector('button[name="remove"]');
            if (removeBtn) {
                removeBtn.style.display = 'inline-block';
            }
        };
        reader.readAsDataURL(event.target.files[0]);
    };

    window.removeImage = function(event) {
        const container = event.target.closest('.col-md-4'); // target wrapper
        const preview = container?.querySelector('img');
        const fileInput = container?.querySelector('input[type="file"]');
        const defaultSrc = preview?.getAttribute('data-default-src'); // fallback image

        if (preview && defaultSrc) {
            preview.src = defaultSrc;
        }
        if (fileInput) {
            fileInput.value = '';
        }
        if (event.target) {
            event.target.style.display = 'none';
        }
    };

    document.addEventListener("click", function(e) {
        const editBtn = e.target.closest("[data-employee-id]");
        const baseUrl = document.querySelector('meta[name="baseUrl"]').getAttribute('content');

        if (!editBtn) return;
        e.preventDefault();
        employeeId = editBtn.dataset.employeeId;

        // reset form errors
        const formEl = document.getElementById("employee-form");
        formEl.querySelectorAll(".password-fields").forEach(el => el.classList.add("d-none"));
        formEl.querySelectorAll('.text-danger.js-error').forEach(el => el.remove());

        fetch(`${baseUrl}/app/employees/${employeeId}/edit`, {
                headers: {
                    "X-Requested-With": "XMLHttpRequest"
                }
            })
            .then(res => res.json())
            .then(response => {
                if (response.status && response.data) {
                    const employee = response.data;

                    // Update Offcanvas Title
                    const titleEl = document.querySelector("#form-offcanvas .offcanvas-title");
                    titleEl.textContent = "Edit Staff";

                    // Fill inputs
                    formEl.querySelector('[name="first_name"]').value = employee.first_name || "";
                    formEl.querySelector('[name="last_name"]').value = employee.last_name || "";
                    formEl.querySelector('[name="email"]').value = employee.email || "";
                    formEl.querySelector('[name="mobile"]').value = employee.mobile || "";
                    formEl.querySelector('[name="about_self"]').value = employee.about_self || "";
                    formEl.querySelector('[name="expert"]').value = employee.expert || "";
                    formEl.querySelector('[name="facebook_link"]').value = employee.facebook_link || "";
                    formEl.querySelector('[name="instagram_link"]').value = employee.instagram_link || "";
                    formEl.querySelector('[name="twitter_link"]').value = employee.twitter_link || "";
                    formEl.querySelector('[name="dribbble_link"]').value = employee.dribbble_link || "";

                    // Gender radio
                    const genderInput = formEl.querySelector(`[name="gender"][value="${employee.gender}"]`);
                    if (genderInput) genderInput.checked = true;

                    // Checkbox fields
                    formEl.querySelector('[name="show_in_calender"]').checked = Number(employee
                        .show_in_calender) === 1;
                    formEl.querySelector('[name="is_manager"]').checked = Number(employee.is_manager) === 1;
                    formEl.querySelector('[name="status"]').checked = Number(employee.status) === 1;

                    const branchSelect = $(formEl).find('[name="branch_id"]');
                        if (branchSelect.length) {
                            branchSelect.val(employee.branch_id).trigger('change'); // update Select2 UI

                            // 🔹 Load services for this branch
                            fetch(`{{ route('backend.services.index_list') }}?branch_id=${employee.branch_id}`, {
                                headers: { 'X-Requested-With': 'XMLHttpRequest', 'Accept': 'application/json' }
                            })
                            .then(res => res.json())
                            .then(services => {
                                const serviceSelect = $('#services');
                                serviceSelect.empty();

                                if (services && services.length) {
                                    const selectedIds = employee.services.map(s => s.service_id); // employee services
                                    services.forEach(service => {
                                        const option = new Option(service.name, service.id, selectedIds.includes(service.id), selectedIds.includes(service.id));
                                        serviceSelect.append(option);
                                    });
                                }

                                serviceSelect.trigger('change'); // refresh Select2
                            })
                            .catch(err => console.error("Error fetching services for branch:", err));
                        }

                    // Commission select
                    const commissionSelect = formEl.querySelector('[name="commission_id"]');
                    if (commissionSelect) {
                        commissionSelect.value = employee.commission_id || "";
                        $(commissionSelect).trigger("change");
                    }

                    // Services multi-select
                    // const serviceSelect = $('#services');
                    // if (serviceSelect.length && employee.services) {
                    //     const selectedIds = employee.services.map(s => s.service_id); // use service_id
                    //     serviceSelect.val(selectedIds).trigger('change');
                    // }
                    // Profile Image
                    const imgPreview = formEl.querySelector('.col-md-4 img');
                    const removeBtn = formEl.querySelector('.col-md-4 button[name="remove"]');
                    if (imgPreview) {
                        if (employee.profile_image) {
                            imgPreview.src = employee.profile_image;
                            removeBtn.style.display = 'inline-block';
                        } else {
                            imgPreview.src = imgPreview.dataset.defaultSrc;
                            removeBtn.style.display = 'none';
                        }
                    }

                    // Update form action
                    formEl.action = `${baseUrl}/app/employees/${employeeId}`;
                    let methodInput = formEl.querySelector('input[name="_method"]');
                    if (!methodInput) {
                        methodInput = document.createElement("input");
                        methodInput.type = "hidden";
                        methodInput.name = "_method";
                        formEl.appendChild(methodInput);
                    }
                    methodInput.value = "PUT";

                    // Open offcanvas
                    const offcanvasEl = document.getElementById("form-offcanvas");
                    const offcanvas = new bootstrap.Offcanvas(offcanvasEl);
                    offcanvas.show();
                } else {
                    window.errorSnackbar(data.message);
                }
            })
            .catch(err => console.error("Error fetching employee:", err));
    });

    document.addEventListener("DOMContentLoaded", function() {
        const formEl = document.getElementById("employee-form");
        const offcanvasEl = document.getElementById("form-offcanvas");

        // Reset on close
        offcanvasEl.addEventListener("hidden.bs.offcanvas", function() {
            formEl.reset();
            formEl.querySelectorAll("input[type=text], input[type=url], input[type=email], textarea")
                .forEach(el => {
                    el.value = "";
                });

            // Reset checkbox (Status field) to checked = true (default for create)
            const statusField = formEl.querySelector("input[name='status']");
            if (statusField) statusField.checked = true;

            formEl.querySelectorAll(".is-invalid").forEach(el => el.classList.remove("is-invalid"));
            formEl.querySelectorAll(".text-danger.js-error").forEach(el => el.remove());

            // reset title
            const titleEl = offcanvasEl.querySelector(".offcanvas-title");
            titleEl.textContent = "Create Employee";

            // reset form action
            const storeUrl = "{{ route('backend.employees.store') }}";
            formEl.action = storeUrl;
            let methodInput = formEl.querySelector('input[name="_method"]');
            if (methodInput) methodInput.remove();

            // reset selects
            $("#branch, #commission, #services").val(null).trigger("change");

            // reset image
            const imgPreview = formEl.querySelector('.col-md-4 img');
            const removeBtn = formEl.querySelector('.col-md-4 button[name="remove"]');
            if (imgPreview) {
                imgPreview.src = imgPreview.dataset.defaultSrc;
            }
            if (removeBtn) removeBtn.style.display = "none";

            // show password fields for new employee
            formEl.querySelectorAll(".password-fields").forEach(el => el.classList.remove("d-none"));
        });



        // Validation fix: only require password on create
        // formEl.addEventListener("submit", function(e) {

        //     const isEditMode = formEl.querySelector('input[name="_method"]')?.value === "PUT";
        //     const password = formEl.querySelector('#employee_password');
        //     const confirmPassword = formEl.querySelector('#employee_confirm_password');

        //     if (isEditMode) {
        //         // skip password validation in edit
        //         if (password) password.removeAttribute("required");
        //         if (confirmPassword) confirmPassword.removeAttribute("required");
        //     } else {
        //         // enforce password rules in create
        //         if (password) password.setAttribute("required", "true");
        //         if (confirmPassword) confirmPassword.setAttribute("required", "true");
        //     }
        // });

        // Clear errors as user types or changes value
        formEl.querySelectorAll("input, textarea").forEach(input => {
            input.addEventListener("input", function() {
                input.classList.remove("is-invalid");
                const container = input.closest(".mb-3, .form-group");
                const error = container?.querySelector(".text-danger.js-error");
                if (error) error.remove();
            });
            // Also clear on blur if field has value
            input.addEventListener("blur", function() {
                if (input.value.trim()) {
                    input.classList.remove("is-invalid");
                    const container = input.closest(".mb-3, .form-group");
                    const error = container?.querySelector(".text-danger.js-error");
                    if (error) error.remove();
                }
            });
        });

        // Clear errors for Select2 dropdowns on change
        $(formEl).find("select.select2").each(function() {
            $(this).on("change select2:select select2:unselect", function() {
                const select = this;
                select.classList.remove("is-invalid");
                // Remove error from Select2 container
                $(select).next('.select2-container').find('.select2-selection').removeClass('is-invalid');
                const container = $(select).closest(".mb-3, .form-group")[0];
                if (container) {
                    const error = container.querySelector(".text-danger.js-error");
                    if (error) error.remove();
                }
            });
        });
    });
</script>
