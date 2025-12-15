<x-layouts.auth>
    <div class="flex flex-col gap-6">
        <x-auth-header :title="__('Create an account')" :description="__('Enter your details below to create your account')" />

        <!-- Session Status -->
        <x-auth-session-status class="text-center" :status="session('status')" />

        @php
            $requiresInviteCode = \App\Actions\Fortify\CreateNewUser::requiresInviteCode();
        @endphp

        @if ($requiresInviteCode)
            <flux:callout icon="shield-check" color="amber">
                <flux:callout.heading>{{ __('Kode Undangan Diperlukan') }}</flux:callout.heading>
                <flux:callout.text>
                    {{ __('Registrasi memerlukan kode 2FA dari user yang sudah terdaftar.') }}
                </flux:callout.text>
            </flux:callout>
        @endif

        <form method="POST" action="{{ route('register.store') }}" class="flex flex-col gap-6">
            @csrf
            <!-- Name -->
            <flux:input
                name="name"
                :label="__('Name')"
                :value="old('name')"
                type="text"
                required
                autofocus
                autocomplete="name"
                :placeholder="__('Full name')"
            />

            <!-- Email Address -->
            <flux:input
                name="email"
                :label="__('Email address')"
                :value="old('email')"
                type="email"
                required
                autocomplete="email"
                placeholder="email@example.com"
            />

            <!-- Password -->
            <flux:input
                name="password"
                :label="__('Password')"
                type="password"
                required
                autocomplete="new-password"
                :placeholder="__('Password')"
                viewable
            />

            <!-- Confirm Password -->
            <flux:input
                name="password_confirmation"
                :label="__('Confirm password')"
                type="password"
                required
                autocomplete="new-password"
                :placeholder="__('Confirm password')"
                viewable
            />

            @if ($requiresInviteCode)
                <!-- Invite Code (2FA from existing user) -->
                <flux:input
                    name="invite_code"
                    :label="__('Kode Undangan')"
                    :value="old('invite_code')"
                    type="text"
                    inputmode="numeric"
                    pattern="[0-9]*"
                    maxlength="6"
                    required
                    autocomplete="one-time-code"
                    :placeholder="__('Masukkan 6 digit kode 2FA')"
                    :description="__('Minta kode dari user yang sudah terdaftar')"
                />
            @endif

            <div class="flex items-center justify-end">
                <flux:button type="submit" variant="primary" class="w-full" data-test="register-user-button">
                    {{ __('Create account') }}
                </flux:button>
            </div>
        </form>

        <div class="space-x-1 rtl:space-x-reverse text-center text-sm text-zinc-600 dark:text-zinc-400">
            <span>{{ __('Already have an account?') }}</span>
            <flux:link :href="route('login')" wire:navigate>{{ __('Log in') }}</flux:link>
        </div>
    </div>
</x-layouts.auth>
