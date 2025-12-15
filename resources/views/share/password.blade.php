<!DOCTYPE html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title>{{ __('Password Required') }} - {{ site_setting('site_name', 'LKEU-RAPI') }}</title>
    <link rel="icon" href="{{ site_setting('favicon_svg', '/favicon.svg') }}" type="image/svg+xml">
    <link rel="preconnect" href="https://fonts.bunny.net">
    <link href="https://fonts.bunny.net/css?family=instrument-sans:400,500,600,700" rel="stylesheet" />
    @vite(['resources/css/app.css', 'resources/js/app.js'])
</head>
<body class="bg-gradient-to-br from-slate-50 via-blue-50 to-indigo-100 dark:from-slate-950 dark:via-slate-900 dark:to-slate-950 min-h-screen flex items-center justify-center p-4">
    <div class="w-full max-w-md">
        <div class="bg-white dark:bg-zinc-800 rounded-2xl shadow-xl border border-zinc-200 dark:border-zinc-700 p-8">
            {{-- Icon --}}
            <div class="flex justify-center mb-6">
                <div class="p-4 bg-amber-100 dark:bg-amber-900/30 rounded-full">
                    <svg class="w-8 h-8 text-amber-600 dark:text-amber-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 15v2m-6 4h12a2 2 0 002-2v-6a2 2 0 00-2-2H6a2 2 0 00-2 2v6a2 2 0 002 2zm10-10V7a4 4 0 00-8 0v4h8z"></path>
                    </svg>
                </div>
            </div>

            {{-- Title --}}
            <h1 class="text-xl font-bold text-center text-zinc-900 dark:text-white mb-2">
                {{ __('Link Dilindungi Password') }}
            </h1>
            <p class="text-center text-zinc-500 dark:text-zinc-400 mb-6">
                {{ __('Masukkan password untuk melihat :name', ['name' => $shareLink->name]) }}
            </p>

            {{-- Form --}}
            <form action="{{ route('share.authenticate', $token) }}" method="POST" class="space-y-4">
                @csrf

                <div>
                    <label for="password" class="block text-sm font-medium text-zinc-700 dark:text-zinc-300 mb-1">
                        {{ __('Password') }}
                    </label>
                    <input
                        type="password"
                        id="password"
                        name="password"
                        required
                        autofocus
                        class="w-full px-4 py-3 border border-zinc-300 dark:border-zinc-600 rounded-xl bg-white dark:bg-zinc-900 text-zinc-900 dark:text-white focus:ring-2 focus:ring-blue-500 focus:border-blue-500 transition"
                        placeholder="{{ __('Masukkan password') }}"
                    >
                    @error('password')
                        <p class="mt-2 text-sm text-red-600 dark:text-red-400">{{ $message }}</p>
                    @enderror
                </div>

                <button
                    type="submit"
                    class="w-full px-4 py-3 bg-gradient-to-r from-blue-600 to-indigo-600 hover:from-blue-700 hover:to-indigo-700 text-white font-semibold rounded-xl transition shadow-lg shadow-blue-500/25"
                >
                    {{ __('Buka Link') }}
                </button>
            </form>
        </div>

        {{-- Footer --}}
        <p class="text-center text-sm text-zinc-500 dark:text-zinc-400 mt-6">
            {{ __('Powered by') }} {{ site_setting('site_name', 'LKEU-RAPI') }}
        </p>
    </div>
</body>
</html>
