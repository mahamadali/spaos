@extends('frontend::layouts.master')
@section('content')
<div class="section-spacing">
    <div class="container">
        <div class="row">
            <div class="col-lg-3 ps-md-0 ps-3">
                <ul class="nav nav-tabs flex-column gap-4">
                    <li class="nav-item">
                        <a class="nav-link active p-3 text-center" data-bs-toggle="pill" href="#editProfile">
                            {{__('messages.update_profile')}}</a>
                    </li>
                    <li class="nav-item">
                        <a class="nav-link p-3 text-center" data-bs-toggle="pill" href="#changePassword">
                            {{__('messages.change_password')}}</a>
                    </li>
                </ul>
            </div>
            <div class="col-lg-9 mt-lg-0 mt-5">
                <div class="tab-content">
                    <div class="tab-pane active fade show" id="editProfile" role="tabpanel">
                        <div class="user-login-card rounded p-5">
                            <div class="edit-profile-content">
                                <div class="edit-profile-details">
                                    <h6 class="mb-3">{{__('messages.profiles_details')}}</h6>
                                    <div class="row">
                                        <div class="col-md-3">
                                            <div class="select-profile-card text-center position-relative">
                                                <!-- Profile Image -->
                                                <img id="profileImage" src="{{ asset(user_avatar()) }}" class="img-fluid rounded-circle object-cover"
                                                    alt="select-profile-image" style="cursor: pointer; width: 150px; height: 150px;">

                                                <!-- Hidden file input -->
                                                <input type="file" id="profileImageInput" class="d-none" accept="image/*" onchange="previewImage(event)">

                                                <!-- Pencil icon -->
                                                <i class="ph ph-pencil pencil-icon" id="triggerFileInput"></i>
                                            </div>
                                        </div>
                                        <div class="col-md-9 mt-md-0 mt-4 ps-md-0 ps-3">
                                            <form id="editProfileDetail">
                                                @csrf
                                                <input type="hidden" name="id" class="form-control" value="{{ $user->id ?? null}}" >
                                                <div class="form-group mb-3">
                                                    <div class="input-group custom-input-group">
                                                        <input type="text" name="first_name" class="form-control" value="{{ $user->first_name }}" placeholder="{{__('frontend.enter_fname')}}" required >
                                                        <span class="input-group-text"><i class="ph ph-user"></i></span>
                                                    </div>
                                                    <div class="invalid-feedback" id="first_name_error">{{__('messages.first_name_field_is_required')}}</div>
                                                </div>
                                                <div class="form-group mb-3">
                                                    <div class="input-group custom-input-group">
                                                        <input type="text" name="last_name" class="form-control" value="{{ $user->last_name }}" placeholder="{{__('frontend.enter_lname')}}" required>
                                                        <span class="input-group-text"><i class="ph ph-user"></i></span>
                                                    </div>
                                                    <div class="invalid-feedback" id="last_name_error">{{__('messages.last_name_field_is_required')}}</div>
                                                </div>
                                                <div class="form-group mb-3">
                                                    <div class="input-group custom-input-group">
                                                        <input type="text" name="username" class="form-control" value="{{ $user->username }}" placeholder="{{__('frontend.enter_username')}}" required>
                                                        <span class="input-group-text"><i class="ph ph-user"></i></span>
                                                    </div>
                                                    <div class="invalid-feedback" id="user_name_error">{{__('messages.user_name_field_is_required')}}</div>
                                                </div>
                                                <div class="form-group mb-3">
                                                    <div class="input-group custom-input-group">
                                                        <input type="email" name="email" class="form-control" value="{{ $user->email }}" required>
                                                        <span class="input-group-text"><i class="ph ph-envelope"></i></span>
                                                    </div>
                                                    <div class="invalid-feedback" id="email_error">{{__('messages.email_is_required')}}</div>
                                                </div>
                                                <div class="form-group mb-3">
                                                    <div class="input-group custom-input-group">
                                                        <input type="tel" name="mobile" class="form-control" value="{{ $user->mobile }}" id="mobileInput"  required>
                                                        <span class="input-group-text"><i class="ph ph-phone"></i></span>
                                                    </div>
                                                    <div class="invalid-feedback" id="mobile_error">{{__('messages.mobile_number_is_required')}}</div>
                                                </div>
                                                <div class="form-group mb-3">
                                                    <div class="input-group custom-input-group">
                                                        <input type="text" name="date_of_birth" id="date_of_birth" class="form-control"
                                                        value="{{ old('date_of_birth', isset($user) && $user->date_of_birth  ? date('Y-m-d', strtotime($user->date_of_birth)) : '') }}"
                                                        readonly>
                                                    <span class="input-group-text" id="open-datepicker">
                                                        <i class="ph ph-calendar"></i>
                                                    </span>
                                                    </div>
                                                    <div class="invalid-feedback" id="date_of_birth_error">{{__('messages.date_of_birth_field_is_required')}}</div>
                                                </div>
                                                <div class="form-group mb-3">
                                                    <label class="form-label mb-3">{{__('messages.gender')}}<span class="text-danger">*</span></label>
                                                    <div class="select-gender d-flex align-items-center flex-wrap gap-3">
                                                        <div class="form-check">
                                                            <label class="form-check-label "  for="male">
                                                                <input class="form-check-input" value="male" type="radio" name="gender"
                                                                id="male" {{ old('gender', isset($user) ? $user->gender : 'male') == 'male' ? 'checked' : '' }}>
                                                                {{__('messages.male')}}
                                                            </label>
                                                        </div>
                                                        <div class="form-check">
                                                            <label class="form-check-label" for="female">
                                                                <input class="form-check-input" type="radio" value="female" name="gender"
                                                                id="female" {{ old('gender', isset($user) ? $user->gender : 'male') == 'female' ? 'checked' : '' }}>
                                                                {{__('messages.female')}}
                                                            </label>
                                                        </div>
                                                        <div class="form-check">
                                                            <label class="form-check-label" for="other">
                                                                <input class="form-check-input"  value="other" type="radio" name="gender" id="other" {{ old('gender', isset($user) ? $user->gender : 'male') == 'other' ? 'checked' : '' }}>
                                                                {{__('messages.other')}}
                                                            </label>
                                                        </div>
                                                    </div>
                                                </div>
                                                <div class="text-end">
                                                    <button type="button" id="updateProfileBtn" class="btn btn-primary mt-5">{{__('messages.update')}}</button>
                                                </div>
                                            </form>
                                        </div>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>
                    <div class="tab-pane fade" id="changePassword" role="tabpanel">
                        <div class="card user-login-card p-5">
                            <div class="edit-profile-content">
                                <h6></h6>
                                <div id="profileList">
                                    <form id="changePasswordDetail">
                                        @csrf
                                        <input type="hidden" name="id" class="form-control" value="{{ $user->id }}" >

                                         <!-- Old Password -->
                                        <div class="form-group mb-3">
                                            <label for="oldpassword" class="form-label fw-medium">{{ __('messages.old_password')}}<span class="text-danger">*</span></label>
                                            <div class="input-group custom-input-group">
                                                <input type="password" name="old_password" class="form-control"
                                                    id="oldpassword" placeholder="e.g., “#123@Abc”" minlength="8" required />
                                                <span class="input-group-text" id="toggle-old-password">
                                                    <i class="ph ph-eye-slash"></i>
                                                </span>
                                            </div>
                                            @error('old_password')
                                            <span class="text-danger">{{ $message }}</span>
                                            @enderror
                                            <div class="invalid-feedback" id="oldpassword_error">{{__('messages.old_password_field_is_required')}}</div>
                                        </div>

                                        <!-- New Password -->
                                        <div class="form-group mb-3">
                                            <label for="password" class="form-label fw-medium">{{__('messages.new_password')}}<span class="text-danger">*</span></label>
                                            <div class="input-group custom-input-group">
                                                <input type="password" name="new_password" class="form-control"
                                                id="password" placeholder="e.g., “#123@Abc”" minlength="8" required />
                                                <span class="input-group-text" id="toggle-password">
                                                    <i class="ph ph-eye-slash"></i>
                                                </span>
                                            </div>
                                            @error('password')
                                            <span class="text-danger">{{ $message }}</span>
                                            @enderror
                                            <div class="invalid-feedback" id="password_error">{{('messages.new_password_field_is_required')}}</div>
                                        </div>

                                        <!-- Confirm Password -->
                                        <div class="form-group mb-3">
                                            <label for="confirm_password" class="form-label">{{__('messages.confirm_new_password')}}<span class="text-danger">*</span></label>
                                            <div class="input-group custom-input-group">
                                                <input type="password" name="confirm_password" class="form-control"
                                                    id="confirm_password" placeholder="e.g., “#123@Abc”" minlength="8" required />
                                                <span class="input-group-text" id="toggle-confirm-password">
                                                    <i class="ph ph-eye-slash"></i>
                                                </span>
                                            </div>
                                            @error('confirm_password')
                                            <span class="text-danger">{{ $message }}</span>
                                            @enderror
                                            <div class="invalid-feedback" id="confirm_password_error">{{__('messages.confirm_password_field_is_required')}}</div>
                                        </div>
                                        <div class="text-end">
                                            <button type="button" id="changePasswordBtn" class="btn btn-primary mt-5">{{__('messages.update')}}</button>
                                        </div>
                                    </form>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>

<div id="snackbar" style="display: none; position: fixed; bottom: 20px; left: 50%; transform: translateX(-50%); background: #333; color: #fff; padding: 10px 20px; border-radius: 5px; z-index: 9999;">
</div>



<script>

document.addEventListener('DOMContentLoaded', function() {
    // Initialize phone input
    // bug fixes : 86czvc1ef
    // Set CSS variables for intl-tel-input flag images
    const baseUrl = document.querySelector('meta[name="baseUrl"]').getAttribute('content');
    const style = document.createElement('style');
    style.innerHTML = `
      :root {
        --iti-path-flags-1x: url("${baseUrl}/img/intl-tel-input/flags.webp");
      }
    `;
    document.head.appendChild(style);

    const input = document.querySelector("#mobileInput");
    if (input) {
        const iti = window.intlTelInput(input, {
            initialCountry: "in",
            separateDialCode: true,
            utilsScript: "/node_modules/intl-tel-input/build/js/utils.js",
        });

        // Store iti instance globally if needed
        window.iti = iti;

        // Add validation listener
        input.addEventListener('input', function() {
            if (iti.isValidNumber()) {
                input.classList.remove('is-invalid');
                document.getElementById('mobile_error').style.display = 'none';
            } else {
                input.classList.add('is-invalid');
                document.getElementById('mobile_error').style.display = 'block';
                document.getElementById('mobile_error').textContent = 'Please enter a valid mobile number';
            }
        });
    }
});
// Function to preview the selected image
function previewProfileImage(event) {
    const reader = new FileReader();
    const fileInput = event.target;

    reader.onload = function() {
        const previewImage = document.getElementById('profile_image');
        previewImage.src = reader.result; // Update the image preview
    };

    reader.readAsDataURL(fileInput.files[0]);
}

function previewImage(event) {
    const image = document.getElementById('profileImage');
    image.src = URL.createObjectURL(event.target.files[0]);
}


const baseUrl = document.querySelector('meta[name="baseUrl"]').getAttribute('content');

// Function to show custom Snackbar
function showSnackbar(message, duration = 3000) {
    const snackbar = document.getElementById('snackbar');
    snackbar.textContent = message; // Set the message
    snackbar.style.display = 'block';

    // Hide the snackbar after a certain time (default is 3000ms)
    setTimeout(() => {
        snackbar.style.display = 'none';
    }, duration);
}

document.addEventListener('DOMContentLoaded', function () {
    $(document).ready(function() {
        // Real-time validation for each field
        const fieldsToValidate = [
            { name: 'first_name', errorElement: '#first_name_error', pattern: /^[A-Za-z]+$/, patternError: 'First name must contain only letters' },
            { name: 'last_name', errorElement: '#last_name_error', pattern: /^[A-Za-z]+$/, patternError: 'Last name must contain only letters' },
            { name: 'username', errorElement: '#user_name_error', patternError: 'User name must be unique' },
            { name: 'email', errorElement: '#email_error', pattern: /^[a-zA-Z0-9._-]+@[a-zA-Z0-9.-]+\.[a-zA-Z]{2,6}$/, patternError: 'Please enter a valid email address' },
            { name: 'date_of_birth', errorElement: '#date_of_birth_error' },
            { name: 'mobile', errorElement: '#mobile_error', patternError: 'Please enter a valid mobile number' }
        ];

        // Real-time validation for inputs
        fieldsToValidate.forEach(field => {
            const inputElement = $(`input[name="${field.name}"]`);
            inputElement.on('input', function () {
                const value = inputElement.val().trim();
                if (!value) {
                    $(field.errorElement).show();
                    inputElement.addClass('is-invalid');
                } else if (field.pattern && !field.pattern.test(value)) {
                    $(field.errorElement).show().text(field.patternError);
                    inputElement.addClass('is-invalid');
                } else {
                    $(field.errorElement).hide();
                    inputElement.removeClass('is-invalid');
                }
            });
        });
// Trigger the file input when clicking the pencil icon
        $("#triggerProfileFileInput").on("click", function () {
        $("#profileFileImageInput").trigger("click");
        });

        $("#triggerFileInput").on("click", function () {
            $("#profileImageInput").trigger("click");
        });

        // Mobile number validation with intl-tel-input
        $('#mobileInput').on('input', function () {
            console.log(iti.getNumber());
            const mobileValue = iti.getNumber();
            console.log(mobileValue);
            if (!iti.isValidNumber()) {
                $('#mobileInput').addClass('is-invalid');
                $('#mobile_error').show().text('Please enter a valid mobile number');
            } else {
                $('#mobileInput').removeClass('is-invalid');
                $('#mobile_error').hide();
            }
        });

        const minLength = 8, maxLength = 14;
            $('#password').on('input', function () {
                const passwordError = $('#password_error');
                const isValid = this.value.trim().length >= minLength && this.value.trim().length <= maxLength;
                $(this).toggleClass('is-invalid', !isValid);
                passwordError.toggle(!isValid).text(`Password must be between ${minLength} and ${maxLength} characters`);
            });


        // Submit the profile update on button click
        $('#updateProfileBtn').on('click', function(e) {
            e.preventDefault();
            // Hide previous error messages and remove invalid class
            $('.invalid-feedback').hide();
            $('input').removeClass('is-invalid');

            let valid = true;

            // Loop through each field and check for emptiness or pattern validation
            fieldsToValidate.forEach(field => {
                const inputElement = $(`input[name="${field.name}"]`);
                if (inputElement.length > 0) {
                    const value = inputElement.val().trim(); // Safe to use trim now
                        if (!value) {
                            $(field.errorElement).show();
                            inputElement.addClass('is-invalid');
                        } else if (field.pattern && !field.pattern.test(value)) {
                            $(field.errorElement).show().text(field.patternError);
                            inputElement.addClass('is-invalid');
                        } else {
                            $(field.errorElement).hide();
                            inputElement.removeClass('is-invalid');
                        }
                    } else {
                        // Handle case where the input element doesn't exist
                        console.warn(`Input field with name ${field.name} not found.`);
                    }
            });

            // Check if gender is selected
            const gender = $("input[name='gender']:checked").val();
            if (!gender) {
                showSnackbar("Gender is required");
                valid = false;
            }

            // If validation fails, stop form submission
            if (!valid) {
                return;
            }

            // Prepare the form data
            var formData = new FormData($('#editProfileDetail')[0]);
            var mobileNumber = iti.getNumber();
            // formData.append('mobile', mobileNumber);

            // Handle the image file input if available
            var imageFile = $('#profileImageInput')[0].files[0];
            if (imageFile) {
                formData.append('profile_image', imageFile);
            }

            var $btn = $(this);
            $btn.prop('disabled', true).text('Updating...');

            // AJAX request to update the profile
            $.ajax({
                url: `${baseUrl}/api/v1/update-profile`,
                type: 'POST',
                data: formData,
                processData: false,
                contentType: false,
                headers: {
                    'Authorization': 'Bearer ' + '{{ auth()->user()->api_token }}'
                },
                success: function(response) {
                    if (response.status === true) {
                        // Update form with response data
                        $('input[name="first_name"]').val(response.data.first_name);
                        $('input[name="last_name"]').val(response.data.last_name);
                        $('input[name="username"]').val(response.data.username);
                        $('input[name="email"]').val(response.data.email);
                        $('input[name="mobile"]').val(response.data.mobile);
                        $('input[name="date_of_birth"]').val(response.data.date_of_birth);
                        $('input[name="gender"][value="' + response.data.gender + '"]').prop('checked', true);
                        showSnackbar(response.message)
                        $btn.prop('disabled', false).text('Update');
                    } else {
                        showSnackbar('Error updating profile.');
                        $btn.prop('disabled', false).text('Update');
                    }
                },
                error: function(xhr, status, error) {
                    var response = JSON.parse(xhr.responseText);

                    if (response.message) {
                        showSnackbar(response.message);
                    } else if (response.errors && response.errors.mobile) {
                        showSnackbar(response.errors.mobile[0]);
                    }

                    $btn.prop('disabled', false).text('Update');
                    }
                });
            });
        });
    });


    document.addEventListener('DOMContentLoaded', function () {
    $(document).ready(function () {
        function togglePasswordVisibility(passwordFieldId, toggleIconId) {
            const passwordField = document.getElementById(passwordFieldId);
            const toggleIcon = document.getElementById(toggleIconId);
            if (passwordField.type === 'password') {
                passwordField.type = 'text';
                toggleIcon.innerHTML = '<i class="ph ph-eye"></i>';
            } else {
                passwordField.type = 'password';
                toggleIcon.innerHTML = '<i class="ph ph-eye-slash"></i>';
            }
        }

        $('#toggle-old-password').on('click', function () {
            togglePasswordVisibility('oldpassword', 'toggle-old-password');
        });

        $('#toggle-password').on('click', function () {
            togglePasswordVisibility('password', 'toggle-password');
        });

        $('#toggle-confirm-password').on('click', function () {
            togglePasswordVisibility('confirm_password', 'toggle-confirm-password');
        });

        $('#changePasswordBtn').on('click', function (e) {
            e.preventDefault();

            // Hide previous validation messages and reset validation state
            $('.invalid-feedback').hide();
            $('input').removeClass('is-invalid');

            let valid = true;

            // Validate form fields
            const fieldsToValidate = [
                { name: 'old_password', errorElement: '#oldpassword_error' },
                { name: 'new_password', errorElement: '#password_error' },
                { name: 'confirm_password', errorElement: '#confirm_password_error' },
            ];

            fieldsToValidate.forEach(field => {
                const value = $(`input[name="${field.name}"]`).val().trim();
                if (!value) {
                    $(field.errorElement).show();
                    $(`input[name="${field.name}"]`).addClass('is-invalid');
                    valid = false;
                }
            });

            // Ensure new password matches confirmation
            const password = $('input[name="new_password"]').val().trim();
            const passwordConfirmation = $('input[name="confirm_password"]').val().trim();

            if (password !== passwordConfirmation) {
                $('#confirm_password_error').show().text('Passwords do not match.');
                $('input[name="confirm_password"]').addClass('is-invalid');
                valid = false;
            }

            if (!valid) {
                return;
            }

            // Prepare form data
            const formData = new FormData($('#changePasswordDetail')[0]);

            const $btn = $(this);
            $btn.prop('disabled', true).text('Updating...');

            // Perform AJAX request
            $.ajax({
                url: `${baseUrl}/api/v1/my-profile/change-password`,
                type: 'POST',
                data: formData,
                processData: false,
                contentType: false,
                headers: {
                    'Authorization': 'Bearer ' + '{{ auth()->user()->api_token }}'
                },
                success: function (response) {
                    if (response.status === true) {
                        showSnackbar(response.message );
                        $btn.prop('disabled', false).text('Update');
                        $('#changePasswordDetail')[0].reset(); // Clear the form
                    } else {
                        showSnackbar(response.message);
                        $btn.prop('disabled', false).text('Update');
                    }

                },
                error: function (xhr, status, error) {
                    const response = JSON.parse(xhr.responseText);

                    if (response.message) {
                        showSnackbar(response.message);
                    } else if (response.errors) {
                        showSnackbar(response.errors);
                        for (const [key, messages] of Object.entries(response.errors)) {
                            const input = $(`input[name="${key}"]`);
                            input.addClass('is-invalid');
                            $(`#${key}_error`).show().text(messages[0]);
                        }
                    }
                    $btn.prop('disabled', false).text('Update');
                }
            });
        });
    });
});

document.addEventListener("DOMContentLoaded", function () {
        const dateInput = document.getElementById("date_of_birth");
        const calendarIcon = document.getElementById("open-datepicker");

        // Initialize Flatpickr
        const flatpickrInstance = flatpickr(dateInput, {
            dateFormat: "Y-m-d", // Format: YYYY-MM-DD
            maxDate: "{{ date('Y-m-d') }}", // Set max date as today
            allowInput: false, // Prevent manual input
            position: "below", // Ensure picker appears below
            onClose: function(selectedDates, dateStr, instance) {
                dateInput.value = dateStr; // Set selected date
            }
        });

        // Open the date picker when clicking the calendar icon
        calendarIcon.addEventListener("click", function () {
            flatpickrInstance.open();
        });
    });

</script>
@endsection
