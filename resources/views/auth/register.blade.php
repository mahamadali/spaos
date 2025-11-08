<x-auth-layout>
    <x-slot name="title">
        @lang('messages.register')
    </x-slot>

    <x-auth-card>
        <x-slot name="logo">
            <a href="/">
                <x-application-logo class="w-20 h-20 " />
            </a>
        </x-slot>

        <!-- Validation Errors -->
        <x-auth-validation-errors class="mb-4" :errors="$errors" />

       

        <form method="POST" action="{{ route('register') }}">
            @csrf

            <!-- First Name -->
            <div class="mt-4">
                <x-label for="first_name" :value="__('messages.first_name')" />

                <x-input id="first_name" type="text" name="first_name" :value="old('first_name')" required autofocus />
            </div>

            <!-- Last Name -->
            <div class="mt-4">
                <x-label for="last_name" :value="__('messages.last_name')" />

                <x-input id="last_name" type="text" name="last_name" :value="old('last_name')" required autofocus />
            </div>
            <!-- Mobile -->
            <div class="mt-4">
                <x-label for="mobile" :value="__('messages.phone_number')" />

                <x-input id="mobile" type="number" name="mobile" required />
            </div>
            <!-- Email Address -->
            <div class="mt-4">
                <x-label for="email" :value="__('messages.email')" />

                <x-input id="email" type="email" name="email" :value="old('email')" required />
            </div>

            <!-- Password -->
            <div class="mt-4">
                <x-label for="password" :value="__('messages.password')" />

                <x-input id="password" type="password" name="password" required autocomplete="new-password" />
            </div>

            <!-- Confirm Password -->
            <div class="mt-4">
                <x-label for="password_confirmation" :value="__('messages.confirm_password')" />

                <x-input id="password_confirmation" type="password" name="password_confirmation" required />
            </div>



            <div class="flex items-center justify-end mt-4">

                <x-button class="ms-4 w-100">
                    {{ __('messages.register') }}
                </x-button>
            </div>
        </form>

        <x-slot name="extra">
            <span>
                {{ __('messages.already_registered') }} <a href="{{ route('login') }}">{{ __('messages.login') }}</a>.
            </span>
        </x-slot>
    </x-auth-card>
</x-auth-layout>
