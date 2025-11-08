<x-guest-layout>
    <x-auth-card>
        <x-slot name="logo">
            <a href="/">
                <x-application-logo class="w-20 h-20 " />
            </a>
        </x-slot>

        <div class="mb-4 text-sm text-gray-600">
            {{ __('messages.thanks_for_signing_up') }}
        </div>

        @if (session('status') == 'verification-link-sent')
            <div class="mb-4 font-medium text-sm text-green-600">
                {{ __('messages.verification_link_sent') }}
            </div>
        @endif

        <div class="mt-4 flex items-center justify-between">
            <form method="POST" action="{{ route('verification.send') }}">
                @csrf

                <div>
                    <x-button>
                        {{ __('messages.resend_verification_email') }}
                    </x-button>
                </div>
            </form>

            <form method="GET" action="{{ route('logout') }}">
                @csrf

                <button type="submit" class="underline text-sm text-gray-600 hover:text-gray-900">
                    {{ __('messages.log_out') }}
                </button>
            </form>
        </div>
    </x-auth-card>
</x-guest-layout>
