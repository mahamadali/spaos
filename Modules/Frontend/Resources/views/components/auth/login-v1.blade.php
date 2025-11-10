@extends('frontend::layouts.auth')

@section('content')
    <section class="login-page">
        <div class="container-fluid px-0 h-100">
            <div class="row align-items-center h-100">
                <div class="col-lg-6" style="background-image: url('{{ asset('/img/frontend/login-pattern.png') }}'); background-repeat: no-repeat; background-size: cover;">
                    <div class="row justify-content-center align-items-center h-100">
                        <div class="col-lg-8">
                            <div class="login-wrapper p-5 p-lg-0">
                                <div class="text-center">
                                    <a href="{{ route('index') }}">
                                        <img src="{{ setting('logo') ? asset(setting('logo')) : asset('/img/frontend/frezka-logo.png') }}" class="img-fluid mb-5 auth-logo" alt="logo" />
                                    </a>
                                    <h4 class="">{{__('messages.welcome_back!')}}</h4>
                                    <p class="mb-0 font-sze-14"> {{__('messages.you_have_been_missed_for_long_time')}}</p>
                                </div>
                                @if (session('error'))
                                    <div class="alert alert-danger alert-dismissible fade show" role="alert">
                                        {{ session('error') }}
                                    </div>
                                @endif
                                <form id="form-submit" method="post"  action="{{ route('admin-login') }}" class="requires-validation" data-toggle="validator" novalidate>
                                            @csrf
                                        <input type="hidden" name="user_type" id="user_type" value="user">

                                        <div class="form-group mb-4 pb-1">
                                            <label for="InputEmail1" class="form-label fw-medium">{{__('messages.email')}}<span
                                                    class="text-danger">*</span></label>
                                            <div class="input-group  custom-input-group ">
                                                <input type="email" name="email" class="form-control"
                                                    id="email-id" placeholder="{{__('messages.email')}}" value="{{ old('email') }}" required/>
                                                <span class="input-group-text">
                                                    <i class="ph ph-envelope-simple"></i>
                                                </span>
                                            </div>
                                            @error('email')
                                            <span class="text-danger">{{ $message }}</span>
                                            @enderror
                                            <div class="invalid-feedback" id="email_error">{{__('messages.email_field_is_required')}}</div>
                                        </div>


                                        <div class="form-group mb-4 pb-1">
                                            <label for="InputPassword" class="form-label fw-medium">{{__('messages.password')}}<span
                                                    class="text-danger">*</span></label>
                                            <div class="input-group custom-input-group">
                                                <input type="password" name="password" class="form-control"
                                                    id="password" placeholder="{{__('messages.password')}}" min="8" required/>
                                                <span class="input-group-text" id="toggle-password">
                                                    <i class="ph ph-eye-slash"></i>
                                                </span>
                                            </div>
                                            @error('password')
                                            <span class="text-danger">{{ $message }}</span>
                                            @enderror
                                            <div class="invalid-feedback" id="password_error">{{__('messages.password_field_is_required')}}</div>
                                        </div>
                                        <div class="d-flex justify-content-between flex-wrap gap-3">
<div class="form-check">
    <input class="form-check-input" type="checkbox" id="remember" name="remember" {{ old('remember') ? 'checked' : '' }}>
    <label class="form-check-label font-size-14" for="remember">{{ __('messages.remember_me') }}</label>
</div>

                                            <a href="{{ route('user.forgetpassword') }}"
                                                class="fw-semibold font-size-14 fst-italic text-secondary">{{__('messages.forgot_password?')}}</a>
                                        </div>
                                        <div class="mt-5">
                                            <button type="submit" id="login-button" class="btn btn-secondary w-100">{{__('messages.login')}}</button>
                                        </div>
                                </form>
                                @if(setting('is_demo_login') == 1)
                                <p class="text-center mt-5 mb-2 font-size-14">{{__('messages.login_as:')}}</p>
                                <div class="btn-admin-wrap d-flex align-items-center justify-content-center flex-wrap gap-3 mb-3">
                                    <button id="super_admin_btn" class="btn btn-purple d-flex align-items-center gap-1">
                                        <i class="ph ph-user-circle-gear"></i>
                                        <span>{{__('messages.super_admin')}}</span>
                                    </button>
                                    <button id="admin_btn" class="btn btn-primary d-flex align-items-center gap-1">
                                        <i class="ph ph-user-circle-check"></i>
                                        <span>{{__('messages.admin')}}</span>
                                    </button>
                                </div>
                                @endif
                                <div class="d-flex justify-content-center flex-wrap gap-1 mt-5 pt-3">
                                    <span class="font-size-14 text-body">{{__('messages.don’t_have_an_account?')}}</span>
                                    <a href="{{ route('user.register') }}"
                                        class="text-primary font-size-14 fw-bold">{{__('messages.register_now')}}</a>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
                <div class="col-lg-6 h-100 mt-lg-0 mt-5 d-none d-lg-block" style="background-image: url('{{ asset('/img/frontend/login-banner.jpg') }}'); background-repeat: no-repeat; background-size: cover;">
                </div>
            </div>
        </div>
    </section>
    <script type="text/javascript">

        document.getElementById('toggle-password').addEventListener('click', function () {
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
        document.getElementById('super_admin_btn').addEventListener('click', function () {
            document.getElementById('email-id').value = 'superadmin@salon.com';
            document.getElementById('password').value = '12345678';
        });

        document.getElementById('admin_btn').addEventListener('click', function () {
            document.getElementById('email-id').value = 'admin@salon.com';
            document.getElementById('password').value = '12345678';
        });

        document.addEventListener("DOMContentLoaded", function () {
    let emailInput = document.getElementById('email-id');
    let emailError = document.getElementById('email_error');

    emailInput.addEventListener('input', function () {
        let emailPattern = /^[a-zA-Z0-9._%+-]+@[a-zA-Z0-9.-]+\.[a-zA-Z]{2,}$/;

        // Check if the email matches the pattern
        if (this.value === '') {
            this.classList.add('is-invalid');
            emailError.style.display = 'block';
            emailError.textContent = "Email field is required";
        } else if (!emailPattern.test(this.value)) {
            this.classList.add('is-invalid');
            emailError.style.display = 'block';
            emailError.textContent = "Invalid email format";
        } else {
            this.classList.remove('is-invalid');
            emailError.style.display = 'none';
        }
    });
});

  </script>
@endsection
