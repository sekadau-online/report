<!DOCTYPE html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title>{{ __('Link Expired') }} - {{ site_setting('site_name', 'LKEU-RAPI') }}</title>
    <link rel="icon" href="{{ site_setting('favicon_svg', '/favicon.svg') }}" type="image/svg+xml">
    <link rel="preconnect" href="https://fonts.bunny.net">
    <link href="https://fonts.bunny.net/css?family=instrument-sans:400,500,600,700" rel="stylesheet" />
    @vite(['resources/css/app.css', 'resources/js/app.js'])
</head>
<body class="bg-gradient-to-br from-slate-50 via-blue-50 to-indigo-100 dark:from-slate-950 dark:via-slate-900 dark:to-slate-950 min-h-screen flex items-center justify-center p-4">
    <div class="w-full max-w-md text-center">
        <div class="bg-white dark:bg-zinc-800 rounded-2xl shadow-xl border border-zinc-200 dark:border-zinc-700 p-8">
            {{-- Icon --}}
            <div class="flex justify-center mb-6">
                <div class="p-4 bg-red-100 dark:bg-red-900/30 rounded-full">
                    <svg class="w-8 h-8 text-red-600 dark:text-red-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8v4m0 4h.01M21 12a9 9 0 11-18 0 9 9 0 0118 0z"></path>
                    </svg>
                </div>
            </div>

            {{-- Title --}}
            <h1 class="text-xl font-bold text-zinc-900 dark:text-white mb-2">
                {{ __('Link Kedaluwarsa') }}
            </h1>
            <p class="text-zinc-500 dark:text-zinc-400 mb-4">
                {{ __('Link berbagi ini telah kedaluwarsa pada :date', ['date' => $shareLink->expires_at->format('d M Y, H:i')]) }}
            </p>

            {{-- Info --}}
            <div class="bg-zinc-50 dark:bg-zinc-900 rounded-lg p-4 text-sm text-zinc-600 dark:text-zinc-400">
                <p>{{ __('Hubungi pemilik link untuk meminta akses baru.') }}</p>
            </div>
        </div>

        {{-- Footer --}}
        <p class="text-sm text-zinc-500 dark:text-zinc-400 mt-6">
            {{ __('Powered by') }} {{ site_setting('site_name', 'LKEU-RAPI') }}
        </p>
    </div>
</body>
</html>
