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
                                    <img src="{{ setting('logo') ? asset(setting('logo')) : asset('/img/frontend/frezka-logo.png') }}" class="img-fluid mb-5 auth-logo" alt="logo" />
                                    <h4 class="">{{__('messages.forgot_password')}}</h4>
                                   
                                </div>
                                @if(session('status'))
                                <div class="alert alert-success" role="alert">
                                    {{ session('status') }}
                                </div>
                                @endif
                                @if($errors->has('email'))
                                    <div class="alert alert-danger" role="alert">
                                        {{ $errors->first('email') }}
                                    </div>
                                @endif
                                <form id="form-submit" method="post"  action="{{ route('password.email') }}" class="requires-validation" data-toggle="validator" novalidate>
                                            @csrf
                                        <input type="hidden" name="user_type" id="user_type" value="user">
                                         
                                        <div class="form-group mb-4 pb-1">
                                            <label for="InputEmail1" class="form-label fw-medium">{{__('messages.email')}}<span
                                                    class="text-danger">*</span></label>
                                            <div class="input-group  custom-input-group ">
                                                <input type="email" name="email" class="form-control"
                                                    id="email-id" placeholder="demo@gmail.com" value="{{ old('email') }}" required/>
                                                <span class="input-group-text">
                                                    <i class="ph ph-envelope-simple"></i>
                                                </span>
                                            </div>
                                            @error('email')
                                            <span class="text-danger">{{ $message }}</span>
                                            @enderror
                                            <div class="invalid-feedback" id="email_error">{{__('messages.email_field_is_required')}}</div>
                                        </div>
                                        
                                        <div class="mt-5">
                                            <button type="submit" id="login-button" class="btn btn-secondary w-100">{{__('messages.reset_password')}}</button>
                                        </div>
                                </form>
                               
                                <div class="d-flex justify-content-center flex-wrap gap-1 mt-5 pt-3">
                                    <span class="font-size-14 text-body">{{__('messages.already_have_an_account?')}}</span>
                                    <a href="{{ route('user.login') }}"
                                        class="text-primary font-size-14 fw-bold">{{__('messages.login_now')}}</a>
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
