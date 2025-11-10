@extends('frontend::layouts.auth')

@section('content')
    <section class="register-page"
        style="background-image: url({{ asset('/img/frontend/register-page-pattern.png') }}); backgound-repat: no-repeat; background-size: cover;">
        <div class="containe h-100">
            <div class="row align-items-center justify-content-center h-100">
                <div class="col-lg-10">
                    <div class="py-5 px-3">
                        <div class="text-center mb-5">
                            <a href="{{ route('index') }}">
                                <img src="{{ setting('logo') ? asset(setting('logo')) : asset('/img/frontend/frezka-logo.png') }}"
                                    class="img-fluid mb-5 auth-logo" alt="logo" />
                            </a>
                        </div>
                        <div class="register-bg register-background-padding">
                            <h4 class="mb-1">{{ __('messages.create_your_account') }}</h4>
                            <p class="font-size-14 mb-5">{{ __('messages.Create_account_for_better_experience') }}</p>

                            <form id="form-submit" method="post" action="{{ route('store-data') }}"
                                class="requires-validation" data-toggle="validator">
                                @csrf
                                <div class="row">
                                    <div class="col-lg-6">
                                        <div class="form-group mb-4 pb-1">
                                            <label for="InputFirstName"
                                                class="form-label fw-medium">{{ __('messages.first_name') }}<span
                                                    class="text-danger">*</span></label>
                                            <div class="input-group  custom-input-group ">
                                                <input type="text" class="form-control" id="firstname"
                                                    placeholder="{{ __('messages.first_name') }}" value="{{ old('first_name') }}"
                                                    name="first_name" required />
                                                <span class="input-group-text">
                                                    <i class="ph ph-user"></i>
                                                </span>
                                            </div>
                                            @error('first_name')
                                                <span class="text-danger">{{ $message }}</span>
                                            @enderror
                                            <div class="invalid-feedback" id="first_name_error">
                                                {{ __('messages.first_name_field_is_required') }}</div>
                                        </div>
                                    </div>
                                    <div class="col-lg-6">
                                        <div class="form-group mb-4 pb-1">
                                            <label for="InputLastname"
                                                class="form-label fw-medium">{{ __('messages.last_name') }}<span
                                                    class="text-danger">*</span></label>
                                            <div class="input-group  custom-input-group ">
                                                <input type="text" name="last_name" class="form-control" id="last_name"
                                                    placeholder="{{ __('messages.last_name') }}" value="{{ old('last_name') }}" required />
                                                <span class="input-group-text">
                                                    <i class="ph ph-user"></i>
                                                </span>
                                            </div>
                                            @error('last_name')
                                                <span class="text-danger">{{ $message }}</span>
                                            @enderror
                                            <div class="invalid-feedback" id="last_name_error">
                                                {{ __('messages.last_name_field_is_required') }}</div>
                                        </div>

                                    </div>
                                    <div class="col-lg-6">
                                        <div class="form-group mb-4 pb-1">
                                            <label for="InputUserName"
                                                class="form-label fw-medium">{{ __('messages.user_name') }}<span
                                                    class="text-danger">*</span></label>
                                            <div class="input-group  custom-input-group ">
                                                <input type="text" class="form-control" id="username"
                                                    placeholder="{{ __('messages.user_name') }}" value="{{ old('username') }}"
                                                    name="username" required />
                                                <span class="input-group-text">
                                                    <i class="ph ph-user"></i>
                                                </span>
                                            </div>
                                            @error('username')
                                                <span class="text-danger">{{ $message }}</span>
                                            @enderror
                                            <div class="invalid-feedback" id="user_name_error">
                                                {{ __('messages.user_name_field_is_required') }}</div>
                                        </div>
                                    </div>
                                    <div class="col-lg-6">
                                        <div class="form-group mb-4 pb-1">
                                            <label for="InputSlug" class="form-label fw-medium">
                                                {{ __('messages.vendor_identifier') }} <span class="text-danger">*</span>
                                            </label>
                                            <div class="input-group custom-input-group">
                                                <input type="text" class="form-control" id="slug" name="slug"
                                                    placeholder="eg: glamour-zone" value="{{ old('slug') }}" required
                                                    pattern="^[a-z0-9-]+$"
                                                    title="Only lowercase letters, numbers, and hyphens are allowed." />
                                                <span class="input-group-text">
                                                    <i class="ph ph-link-simple"></i>
                                                </span>
                                            </div>
                                            <span class="text-danger">Note : Only lowercase letters, numbers, and hyphens
                                                are allowed. Do not use spaces or /.</span>
                                            @error('slug')
                                                <span class="text-danger">{{ $message }}</span>
                                            @enderror
                                            <div class="invalid-feedback" id="slug_error">
                                                {{ __('messages.slug_field_is_required') }}
                                            </div>
                                        </div>
                                    </div>
                                    <div class="col-lg-6">
                                        <div class="form-group mb-4 pb-1">
                                            <label for="InputEmail1"
                                                class="form-label fw-medium">{{ __('messages.email') }}<span
                                                    class="text-danger">*</span></label>
                                            <div class="input-group  custom-input-group ">
                                                <input type="email" name="email" class="form-control" id="email"
                                                    placeholder="demo@gmail.com" value="{{ old('email') }}" required />
                                                <span class="input-group-text">
                                                    <i class="ph ph-envelope-simple"></i>
                                                </span>
                                            </div>
                                            <div id="email_error_message">
                                                @error('email')
                                                    <div class="text-danger">{{ $message }}</div>
                                                @enderror
                                            </div>
                                            <div class="invalid-feedback" id="email_error">
                                                {{ __('messages.email_field_is_required') }}</div>
                                        </div>

                                    </div>
                                    <div class="col-lg-6">
                                        <div class="form-group mb-4 pb-1">
                                            <label for="InputPhoneNo"
                                                class="form-label fw-medium">{{ __('messages.phone_number') }}<span
                                                    class="text-danger">*</span></label>
                                            <div class="input-group custom-input-group mb-3">
                                                <input type="tel" name="mobile" class="form-control" id="mobile"
                                                    placeholder="eg “01234 - 5678”" value="{{ old('mobile') }}"
                                                    pattern="^[0-9\s]{10,15}$" required />
                                                <span class="input-group-text">
                                                    <i class="ph ph-phone-call"></i>
                                                </span>
                                                </span>
                                            </div>
                                            @error('mobile')
                                                <span
                                                    class="text-danger">{{ __('messages.the_phone_number_has_already_been_taken.') }}</span>
                                            @enderror
                                            <div class="invalid-feedback" id="mobile_error">
                                                {{ __('messages.phone_number_field_is_required') }}</div>
                                        </div>

                                    </div>
                                    <div class="col-lg-6">
                                        <div class="form-group mb-4 pb-1">
                                            <label for="InputPassword"
                                                class="form-label fw-medium">{{ __('messages.password') }}<span
                                                    class="text-danger">*</span></label>
                                            <div class="input-group custom-input-group">
                                                <input type="password" name="password" class="form-control"
                                                    id="password" placeholder="eg “#123@Abc”" min="8" required />
                                                <span class="input-group-text" id="toggle-password">
                                                    <i class="ph ph-eye-slash"></i>
                                                </span>
                                            </div>
                                            @error('password')
                                                <span class="text-danger">{{ $message }}</span>
                                            @enderror
                                            <div class="invalid-feedback" id="password_error">
                                                {{ __('messages.password_field_is_required') }}</div>
                                        </div>


                                    </div>
                                    <div class="col-lg-6">
                                        <div class="form-group mb-4 pb-1">
                                            <label for="InputConfirmPassword"
                                                class="form-label">{{ __('messages.confirm_password') }}<span
                                                    class="text-danger">*</span></label>
                                            <div class="input-group custom-input-group ">
                                                <input type="password" class="form-control" id="password_confirmation"
                                                    min="8" name="password_confirmation"
                                                    placeholder="eg “#123@Abc”" required />
                                                <span class="input-group-text" id="toggle-confirm-password">
                                                    <i class="ph ph-eye-slash"></i>
                                                </span>
                                            </div>
                                            @error('password_confirmation')
                                                <span class="text-danger">{{ $message }}</span>
                                            @enderror
                                            <div class="invalid-feedback" id="password_confirmation_error">
                                                {{ __('messages.confirm_password_field_is_required') }}</div>
                                        </div>
                                    </div>
                                    <div class="col-lg-6">
                                        <label class="form-label">{{ __('messages.gender') }}<span
                                                class="text-danger">*</span></label>
                                        <div class="select-gender d-flex align-items-center flex-wrap gap-3">
                                            <div class="form-check">
                                                <label class="form-check-label" for="female">
                                                    <input class="form-check-input" type="radio" value="female"
                                                        name="gender" id="female" />
                                                    {{ __('messages.female') }}
                                                </label>
                                            </div>
                                            <div class="form-check">
                                                <label class="form-check-label " for="male">
                                                    <input class="form-check-input" value="male" type="radio"
                                                        name="gender" id="male" checked />
                                                    {{ __('messages.male') }}
                                                </label>
                                            </div>
                                            {{-- <div class="form-check">
                                                <label class="form-check-label" for="other">
                                                    <input class="form-check-input" value="other" type="radio"
                                                        name="gender" id="other" />
                                                    {{ __('messages.other') }}
                                                </label>
                                            </div> --}}
                                        </div>
                                    </div>
                                </div>
                                <div class="col-lg-12">
                                    <div class="d-flex align-items-center justify-content-sm-end flex-wrap gap-3 mt-5">

                                        <button class="btn btn-secondary px-sm-5 px-3" type="submit" id="submit-button">
                                            {{ __('messages.register') }}
                                        </button>
                                    </div>
                                    <div class="d-flex justify-content-center flex-wrap gap-1 mt-5">
                                        <span
                                            class="font-size-14 text-body">{{ __('messages.already_have_an_account?') }}</span>
                                        <a href="{{ route('user.login') }}"
                                            class="text-primary font-size-14 fw-bold">{{ __('messages.log_in') }}</a>
                                    </div>
                                </div>
                            </form>
                        </div>
                    </div>
                </div>

            </div>
        </div>
    </section>




    <script type="text/javascript">
        document.addEventListener('DOMContentLoaded', function() {
            const baseUrl = document.querySelector('meta[name="baseUrl"]').getAttribute('content');

            const style = document.createElement('style');
            style.innerHTML = `
      :root {
        --iti-path-flags-1x: url("${baseUrl}/images/vendor/intl-tel-input/build/flags.webp");
        --iti-path-flags-2x: url("${baseUrl}/images/vendor/intl-tel-input/build/flags@2x.webp");
        --iti-path-globe-1x: url("${baseUrl}/images/vendor/intl-tel-input/build/globe.webp");
        --iti-path-globe-2x: url("${baseUrl}/images/vendor/intl-tel-input/build/globe@2x.webp");
      }
    `;
            document.head.appendChild(style);
            var input = document.querySelector("#mobile");
            var iti = window.intlTelInput(input, {
                initialCountry: "gh",
                separateDialCode: true,
                utilsScript: "/node_modules/intl-tel-input/build/js/utils.js",

            });

            // Initialize intl-tel-input AFTER setting styles
            const input = document.querySelector("#mobile");
            window.intlTelInput(input, {
                initialCountry: "gh",
                separateDialCode: true,
                utilsScript: baseUrl + "/node_modules/intl-tel-input/build/js/utils.js"
            });
        });


        document.querySelector("form").addEventListener("submit", function(e) {

            var input = document.querySelector("#mobile");
            var iti = window.intlTelInputGlobals.getInstance(input);


            var fullNumber = iti.getNumber();
            input.value = fullNumber;


            $(this).trigger("submit");
        });


        document.getElementById('toggle-password').addEventListener('click', function() {
            const passwordField = document.getElementById('password');
            const icon = this.querySelector('i');

            const passwordFieldType = passwordField.type;
            if (passwordFieldType === 'password') {
                passwordField.type = 'text';
                icon.classList.remove('ph-eye-slash');
                icon.classList.add('ph-eye');
            } else {
                passwordField.type = 'password';
                icon.classList.remove('ph-eye');
                icon.classList.add('ph-eye-slash');
            }
        });


        document.getElementById('toggle-confirm-password').addEventListener('click', function() {
            const confirmpasswordField = document.getElementById('password_confirmation');
            const icon = this.querySelector('i');

            const confirmpasswordFieldType = confirmpasswordField.type;
            if (confirmpasswordFieldType === 'password') {
                confirmpasswordField.type = 'text'; // Show password
                icon.classList.remove('ph-eye-slash');
                icon.classList.add('ph-eye');
            } else {
                confirmpasswordField.type = 'password'; // Hide password
                icon.classList.remove('ph-eye');
                icon.classList.add('ph-eye-slash');
            }
        });


        document.addEventListener("DOMContentLoaded", function() {
            const emailInput = document.getElementById("email");
            const emailErrorMessageDiv = document.getElementById("email_error_message");
            const emailErrorDiv = document.getElementById("email_error");

            emailInput.addEventListener("input", function() {
                const emailValue = emailInput.value.trim();
                const emailPattern = /^[^\s@]+@[^\s@]+\.[^\s@]+$/;

                emailErrorMessageDiv.innerHTML = '';
                emailErrorDiv.style.display = "none";

                if (emailValue === "") {
                    emailErrorDiv.textContent = "Email field is required";
                } else if (!emailPattern.test(emailValue)) {
                    const invalidEmailErrorMessage = document.createElement("div");
                    invalidEmailErrorMessage.classList.add("text-danger");
                    invalidEmailErrorMessage.textContent = "Please enter a valid email address";
                    emailErrorMessageDiv.appendChild(invalidEmailErrorMessage);
                }

            });
        });
    </script>
@endsection
