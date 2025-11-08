<x-guest-layout>
    <x-auth-card>
        <x-slot name="logo">
            <a href="/">
                <x-application-logo class="w-20 h-20 " />
            </a>
        </x-slot>

        <div class="my-4">
            {{ __('messages.forgot_password_no') }}
        </div>

        <!-- Session Status -->
        <x-auth-session-status class="mb-4" :status="session('status')" />

        <!-- Validation Errors -->
        <x-auth-validation-errors class="mb-4" :errors="$errors" />

        <form method="POST" action="{{ route('password.email') }}">
            @csrf

            <!-- Email Address -->
            <div>
                <x-label for="email" :value="__('messages.email')" />

                <x-input id="email" class="mt-1" type="email" name="email" :value="old('email')" required autofocus />
            </div>

            <div class="d-flex align-items-center justify-content-center mt-4">
                <x-button class="w-100">
                    {{ __('messages.email_password') }}
                </x-button>
            </div>
        </form>
    </x-auth-card>
</x-guest-layout>
