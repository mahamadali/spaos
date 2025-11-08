<x-guest-layout>
    <x-auth-card>
        <x-slot name="logo">
            <a href="/">
                <x-application-logo class="w-20 h-20 " />
            </a>
        </x-slot>

        <!-- Validation Errors -->
        <x-auth-validation-errors class="mb-4" :errors="$errors"  />

        <form method="POST" action="{{ route('password.update') }}" id="password-reset-form">
            @csrf

            <!-- Password Reset Token -->
            <input type="hidden" name="token" value="{{ $request->route('token') }}" >

            <!-- Email Address -->
            <div>
                <x-label for="email" :value="__('messages.email')" />

                <x-input id="email" class="" type="email" name="email" :value="old('email', $request->email)" required autofocus  placeholder="{{__('profile.enter_email')}}"/>
            </div>

            <!-- Password -->
            <div class="mt-4">
                <x-label for="password" :value="__('messages.lbl_new_password')" />

                <x-input id="password" class="" type="password" name="password" required placeholder="{{__('messages.placeholder_new_password')}}" />
                <div id="password-error" class="invalid-feedback">
                    {{ __('messages.password_must_be_between_8_and_14_characters') }}
                </div>
            </div>

            <!-- Confirm Password -->
            <div class="mt-4">
                <x-label for="password_confirmation" :value="__('messages.lbl_new_confirm_password')" />

                <x-input id="password_confirmation" class="" type="password" name="password_confirmation" min=8 required  placeholder="{{__('messages.placeholder_new_confirm_password')}}"   />
                <div id="confirmation-error" class="invalid-feedback">
                    {{ __('messages.passwords_do_not_match') }}
                </div>
            </div>

            <div class="d-flex align-items-center justify-content-end mt-4">
                <x-button>
                    {{ __('messages.reset_password') }}
                </x-button>
            </div>
        </form>

        <script>
            document.getElementById('password-reset-form').addEventListener('submit', function(event) {
                let password = document.getElementById('password');
                let confirmPassword = document.getElementById('password_confirmation');

                // Reset error states
                password.classList.remove('is-invalid');
                confirmPassword.classList.remove('is-invalid');
                document.getElementById('password-error').classList.add('d-none');
                document.getElementById('confirmation-error').classList.add('d-none');

                // Check password length
                if (password.value.length < 8 || password.value.length > 14) {
                    event.preventDefault();
                    password.classList.add('is-invalid');
                    document.getElementById('password-error').classList.remove('d-none');
                }

                // Check if passwords match
                if (password.value !== confirmPassword.value) {
                    event.preventDefault();
                    confirmPassword.classList.add('is-invalid');
                    document.getElementById('confirmation-error').classList.remove('d-none');
                }
            });
        </script>
    </x-auth-card>
</x-guest-layout>
