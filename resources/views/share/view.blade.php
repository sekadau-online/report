<!DOCTYPE html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title>{{ $shareLink->name }} - {{ site_setting('site_name', 'LKEU-RAPI') }}</title>
    <link rel="icon" href="{{ site_setting('favicon_svg', '/favicon.svg') }}" type="image/svg+xml">
    <link rel="preconnect" href="https://fonts.bunny.net">
    <link href="https://fonts.bunny.net/css?family=instrument-sans:400,500,600,700" rel="stylesheet" />
    @vite(['resources/css/app.css', 'resources/js/app.js'])
</head>
<body class="bg-zinc-50 dark:bg-zinc-900 min-h-screen">
    {{-- Header --}}
    <header class="bg-white dark:bg-zinc-800 border-b border-zinc-200 dark:border-zinc-700 sticky top-0 z-10">
        <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8 py-4">
            <div class="flex items-center justify-between">
                <div class="flex items-center gap-3">
                    @php $logo = site_setting('logo'); @endphp
                    @if ($logo)
                        <img src="{{ $logo }}" alt="{{ site_setting('site_name') }}" class="h-8">
                    @else
                        <div class="flex items-center justify-center w-8 h-8 rounded-lg bg-gradient-to-br from-blue-500 to-indigo-600">
                            <svg class="w-5 h-5 text-white" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12h6m-6 4h6m2 5H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z"></path>
                            </svg>
                        </div>
                        <span class="text-lg font-semibold text-zinc-900 dark:text-white">{{ site_setting('site_name', 'LKEU-RAPI') }}</span>
                    @endif
                </div>
                <div class="flex items-center gap-2 text-sm text-zinc-500 dark:text-zinc-400">
                    <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 12a3 3 0 11-6 0 3 3 0 016 0z"></path>
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M2.458 12C3.732 7.943 7.523 5 12 5c4.478 0 8.268 2.943 9.542 7-1.274 4.057-5.064 7-9.542 7-4.477 0-8.268-2.943-9.542-7z"></path>
                    </svg>
                    <span>{{ __('View Only') }}</span>
                </div>
            </div>
        </div>
    </header>

    <main class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8 py-8">
        {{-- Title & Info --}}
        <div class="mb-8">
            <h1 class="text-2xl font-bold text-zinc-900 dark:text-white">{{ $shareLink->name }}</h1>
            <p class="text-zinc-500 dark:text-zinc-400 mt-1">
                {{ __('Dibagikan oleh :name', ['name' => $shareLink->user->name]) }}
            </p>
        </div>

        {{-- Statistics Cards --}}
        <div class="grid grid-cols-1 sm:grid-cols-3 gap-4 mb-8">
            <div class="bg-gradient-to-br from-green-50 to-emerald-50 dark:from-green-900/20 dark:to-emerald-900/20 rounded-xl p-5 border border-green-100 dark:border-green-800/30">
                <div class="flex items-center gap-3">
                    <div class="p-2 bg-green-100 dark:bg-green-900/50 rounded-lg">
                        <svg class="w-6 h-6 text-green-600 dark:text-green-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 10l7-7m0 0l7 7m-7-7v18"></path>
                        </svg>
                    </div>
                    <div>
                        <p class="text-sm text-green-600 dark:text-green-400 font-medium">{{ __('Total Pemasukan') }}</p>
                        <p class="text-xl font-bold text-green-700 dark:text-green-300">Rp {{ number_format($totalIncome, 0, ',', '.') }}</p>
                    </div>
                </div>
            </div>

            <div class="bg-gradient-to-br from-red-50 to-rose-50 dark:from-red-900/20 dark:to-rose-900/20 rounded-xl p-5 border border-red-100 dark:border-red-800/30">
                <div class="flex items-center gap-3">
                    <div class="p-2 bg-red-100 dark:bg-red-900/50 rounded-lg">
                        <svg class="w-6 h-6 text-red-600 dark:text-red-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 14l-7 7m0 0l-7-7m7 7V3"></path>
                        </svg>
                    </div>
                    <div>
                        <p class="text-sm text-red-600 dark:text-red-400 font-medium">{{ __('Total Pengeluaran') }}</p>
                        <p class="text-xl font-bold text-red-700 dark:text-red-300">Rp {{ number_format($totalExpense, 0, ',', '.') }}</p>
                    </div>
                </div>
            </div>

            <div class="bg-gradient-to-br from-blue-50 to-indigo-50 dark:from-blue-900/20 dark:to-indigo-900/20 rounded-xl p-5 border border-blue-100 dark:border-blue-800/30">
                <div class="flex items-center gap-3">
                    <div class="p-2 bg-blue-100 dark:bg-blue-900/50 rounded-lg">
                        <svg class="w-6 h-6 text-blue-600 dark:text-blue-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8c-1.657 0-3 .895-3 2s1.343 2 3 2 3 .895 3 2-1.343 2-3 2m0-8c1.11 0 2.08.402 2.599 1M12 8V7m0 1v8m0 0v1m0-1c-1.11 0-2.08-.402-2.599-1M21 12a9 9 0 11-18 0 9 9 0 0118 0z"></path>
                        </svg>
                    </div>
                    <div>
                        <p class="text-sm text-blue-600 dark:text-blue-400 font-medium">{{ __('Saldo') }}</p>
                        <p class="text-xl font-bold {{ $balance >= 0 ? 'text-blue-700 dark:text-blue-300' : 'text-red-700 dark:text-red-300' }}">
                            Rp {{ number_format($balance, 0, ',', '.') }}
                        </p>
                    </div>
                </div>
            </div>
        </div>

        {{-- Reports List --}}
        <div class="bg-white dark:bg-zinc-800 rounded-xl border border-zinc-200 dark:border-zinc-700 overflow-hidden">
            <div class="px-5 py-4 border-b border-zinc-200 dark:border-zinc-700">
                <h2 class="font-semibold text-zinc-900 dark:text-white">{{ __('Daftar Laporan') }} ({{ $reports->count() }})</h2>
            </div>

            @if ($reports->isEmpty())
                <div class="p-8 text-center text-zinc-500 dark:text-zinc-400">
                    <svg class="w-12 h-12 mx-auto mb-3 opacity-50" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12h6m-6 4h6m2 5H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z"></path>
                    </svg>
                    <p>{{ __('Belum ada laporan') }}</p>
                </div>
            @else
                <div class="divide-y divide-zinc-200 dark:divide-zinc-700">
                    @foreach ($reports as $report)
                        <div class="p-5 hover:bg-zinc-50 dark:hover:bg-zinc-700/50 transition-colors">
                            <div class="flex items-start justify-between gap-4">
                                <div class="flex items-start gap-4">
                                    <div class="p-2 rounded-lg {{ $report->type === 'income' ? 'bg-green-100 dark:bg-green-900/30' : 'bg-red-100 dark:bg-red-900/30' }}">
                                        @if ($report->type === 'income')
                                            <svg class="w-5 h-5 text-green-600 dark:text-green-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 10l7-7m0 0l7 7m-7-7v18"></path>
                                            </svg>
                                        @else
                                            <svg class="w-5 h-5 text-red-600 dark:text-red-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 14l-7 7m0 0l-7-7m7 7V3"></path>
                                            </svg>
                                        @endif
                                    </div>
                                    <div>
                                        <h3 class="font-medium text-zinc-900 dark:text-white">{{ $report->title }}</h3>
                                        <div class="flex items-center gap-2 mt-1 text-sm text-zinc-500 dark:text-zinc-400">
                                            <span>{{ $report->category }}</span>
                                            <span>•</span>
                                            <span>{{ $report->report_date->format('d M Y') }}</span>
                                        </div>
                                        @if ($report->description)
                                            <p class="mt-2 text-sm text-zinc-600 dark:text-zinc-400">{{ $report->description }}</p>
                                        @endif
                                    </div>
                                </div>
                                <div class="text-right">
                                    <p class="font-semibold {{ $report->type === 'income' ? 'text-green-600 dark:text-green-400' : 'text-red-600 dark:text-red-400' }}">
                                        {{ $report->type === 'income' ? '+' : '-' }}Rp {{ number_format($report->amount, 0, ',', '.') }}
                                    </p>
                                </div>
                            </div>

                            @if ($report->photo)
                                <div class="mt-4 pl-14">
                                    <img
                                        src="{{ Storage::url($report->photo) }}"
                                        alt="{{ $report->title }}"
                                        class="max-w-xs rounded-lg border border-zinc-200 dark:border-zinc-700"
                                    >
                                </div>
                            @endif
                        </div>
                    @endforeach
                </div>
            @endif
        </div>
    </main>

    {{-- Footer --}}
    <footer class="border-t border-zinc-200 dark:border-zinc-700 mt-12 py-6">
        <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8 text-center text-sm text-zinc-500 dark:text-zinc-400">
            <p>{{ __('Powered by') }} {{ site_setting('site_name', 'LKEU-RAPI') }}</p>
        </div>
    </footer>
</body>
</html>
