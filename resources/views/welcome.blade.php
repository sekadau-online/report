<!DOCTYPE html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}">
    <head>
        <meta charset="utf-8">
        <meta name="viewport" content="width=device-width, initial-scale=1">

        <title>{{ site_setting('site_title', 'Laravel') }}</title>

        <link rel="icon" href="{{ site_setting('favicon_ico', '/favicon.ico') }}" sizes="any">
        <link rel="icon" href="{{ site_setting('favicon_svg', '/favicon.svg') }}" type="image/svg+xml">
        <link rel="apple-touch-icon" href="/apple-touch-icon.png">

        <!-- Fonts -->
        <link rel="preconnect" href="https://fonts.bunny.net">
        <link href="https://fonts.bunny.net/css?family=instrument-sans:400,500,600,700" rel="stylesheet" />

        @vite(['resources/css/app.css', 'resources/js/app.js'])
    </head>
    <body class="bg-gradient-to-br from-slate-50 via-blue-50 to-indigo-100 dark:from-slate-950 dark:via-slate-900 dark:to-slate-950 min-h-screen antialiased">
        {{-- Background Pattern --}}
        <div class="fixed inset-0 -z-10 overflow-hidden">
            <div class="absolute -top-40 -right-40 w-80 h-80 bg-blue-400/20 dark:bg-blue-500/10 rounded-full blur-3xl"></div>
            <div class="absolute top-1/2 -left-40 w-96 h-96 bg-indigo-400/20 dark:bg-indigo-500/10 rounded-full blur-3xl"></div>
            <div class="absolute -bottom-40 right-1/3 w-80 h-80 bg-cyan-400/20 dark:bg-cyan-500/10 rounded-full blur-3xl"></div>
        </div>

        <div class="flex flex-col min-h-screen">
            {{-- Header --}}
            <header class="w-full px-6 py-4">
                <div class="max-w-6xl mx-auto flex items-center justify-between">
                    {{-- Logo --}}
                    <div class="flex items-center gap-3">
                        @php $logo = site_setting('logo'); @endphp
                        @if ($logo)
                            <img src="{{ $logo }}" alt="{{ site_setting('site_name', 'LKEU-RAPI') }}" class="h-10">
                        @else
                            <div class="flex items-center justify-center w-10 h-10 rounded-xl bg-gradient-to-br from-blue-500 to-indigo-600 shadow-lg shadow-blue-500/25">
                                <svg class="w-6 h-6 text-white" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12h6m-6 4h6m2 5H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z"></path>
                                </svg>
                            </div>
                            <span class="text-xl font-bold bg-gradient-to-r from-blue-600 to-indigo-600 dark:from-blue-400 dark:to-indigo-400 bg-clip-text text-transparent">
                                {{ site_setting('site_name', 'LKEU-RAPI') }}
                            </span>
                        @endif
                    </div>

                    {{-- Navigation --}}
                    @if (Route::has('login'))
                        <nav class="flex items-center gap-3">
                            @auth
                                <a href="{{ url('/dashboard') }}" class="inline-flex items-center gap-2 px-5 py-2.5 bg-gradient-to-r from-blue-600 to-indigo-600 hover:from-blue-700 hover:to-indigo-700 text-white rounded-xl text-sm font-medium transition-all shadow-lg shadow-blue-500/25 hover:shadow-blue-500/40">
                                    <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 6a2 2 0 012-2h2a2 2 0 012 2v2a2 2 0 01-2 2H6a2 2 0 01-2-2V6zM14 6a2 2 0 012-2h2a2 2 0 012 2v2a2 2 0 01-2 2h-2a2 2 0 01-2-2V6zM4 16a2 2 0 012-2h2a2 2 0 012 2v2a2 2 0 01-2 2H6a2 2 0 01-2-2v-2zM14 16a2 2 0 012-2h2a2 2 0 012 2v2a2 2 0 01-2 2h-2a2 2 0 01-2-2v-2z"></path>
                                    </svg>
                                    Dashboard
                                </a>
                            @else
                                <a href="{{ route('login') }}" class="px-5 py-2.5 text-slate-600 dark:text-slate-300 hover:text-slate-900 dark:hover:text-white rounded-xl text-sm font-medium transition">
                                    Log in
                                </a>
                                @if (Route::has('register'))
                                    <a href="{{ route('register') }}" class="inline-flex items-center px-5 py-2.5 bg-gradient-to-r from-blue-600 to-indigo-600 hover:from-blue-700 hover:to-indigo-700 text-white rounded-xl text-sm font-medium transition-all shadow-lg shadow-blue-500/25 hover:shadow-blue-500/40">
                                        Daftar Gratis
                                    </a>
                                @endif
                            @endauth
                        </nav>
                    @endif
                </div>
            </header>

            {{-- Hero Section --}}
            <main class="flex-1 flex items-center justify-center px-6 py-12">
                <div class="max-w-6xl mx-auto w-full">
                    <div class="grid lg:grid-cols-2 gap-12 items-center">
                        {{-- Left Content --}}
                        <div class="text-center lg:text-left space-y-8">
                            {{-- Badge --}}
                            <div class="inline-flex items-center gap-2 px-4 py-2 bg-blue-100 dark:bg-blue-900/30 text-blue-700 dark:text-blue-300 rounded-full text-sm font-medium">
                                <span class="relative flex h-2 w-2">
                                    <span class="animate-ping absolute inline-flex h-full w-full rounded-full bg-blue-400 opacity-75"></span>
                                    <span class="relative inline-flex rounded-full h-2 w-2 bg-blue-500"></span>
                                </span>
                                Sistem Laporan Keuangan Modern
                            </div>

                            {{-- Headline --}}
                            <div class="space-y-4">
                                <h1 class="text-4xl sm:text-5xl lg:text-6xl font-bold text-slate-900 dark:text-white leading-tight">
                                    {{ site_setting('welcome_title', 'LKEU-RAPI') }}
                                </h1>
                                <p class="text-lg sm:text-xl text-slate-600 dark:text-slate-400 max-w-xl mx-auto lg:mx-0">
                                    {{ site_setting('welcome_description', 'Sistem Laporan Keuangan yang Rapi dan Terorganisir. Kelola pemasukan dan pengeluaran dengan mudah.') }}
                                </p>
                            </div>

                            {{-- Features --}}
                            <div class="grid sm:grid-cols-2 gap-4 text-left">
                                <div class="flex items-start gap-3 p-4 bg-white/60 dark:bg-slate-800/60 rounded-xl backdrop-blur-sm border border-slate-200/50 dark:border-slate-700/50">
                                    <div class="flex-shrink-0 w-10 h-10 rounded-lg bg-green-100 dark:bg-green-900/30 flex items-center justify-center">
                                        <svg class="w-5 h-5 text-green-600 dark:text-green-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8c-1.657 0-3 .895-3 2s1.343 2 3 2 3 .895 3 2-1.343 2-3 2m0-8c1.11 0 2.08.402 2.599 1M12 8V7m0 1v8m0 0v1m0-1c-1.11 0-2.08-.402-2.599-1M21 12a9 9 0 11-18 0 9 9 0 0118 0z"></path>
                                        </svg>
                                    </div>
                                    <div>
                                        <h3 class="font-semibold text-slate-900 dark:text-white">Catat Keuangan</h3>
                                        <p class="text-sm text-slate-600 dark:text-slate-400">Pemasukan & pengeluaran</p>
                                    </div>
                                </div>
                                <div class="flex items-start gap-3 p-4 bg-white/60 dark:bg-slate-800/60 rounded-xl backdrop-blur-sm border border-slate-200/50 dark:border-slate-700/50">
                                    <div class="flex-shrink-0 w-10 h-10 rounded-lg bg-purple-100 dark:bg-purple-900/30 flex items-center justify-center">
                                        <svg class="w-5 h-5 text-purple-600 dark:text-purple-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 19v-6a2 2 0 00-2-2H5a2 2 0 00-2 2v6a2 2 0 002 2h2a2 2 0 002-2zm0 0V9a2 2 0 012-2h2a2 2 0 012 2v10m-6 0a2 2 0 002 2h2a2 2 0 002-2m0 0V5a2 2 0 012-2h2a2 2 0 012 2v14a2 2 0 01-2 2h-2a2 2 0 01-2-2z"></path>
                                        </svg>
                                    </div>
                                    <div>
                                        <h3 class="font-semibold text-slate-900 dark:text-white">Laporan Visual</h3>
                                        <p class="text-sm text-slate-600 dark:text-slate-400">Grafik & statistik</p>
                                    </div>
                                </div>
                                <div class="flex items-start gap-3 p-4 bg-white/60 dark:bg-slate-800/60 rounded-xl backdrop-blur-sm border border-slate-200/50 dark:border-slate-700/50">
                                    <div class="flex-shrink-0 w-10 h-10 rounded-lg bg-blue-100 dark:bg-blue-900/30 flex items-center justify-center">
                                        <svg class="w-5 h-5 text-blue-600 dark:text-blue-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 16v1a3 3 0 003 3h10a3 3 0 003-3v-1m-4-8l-4-4m0 0L8 8m4-4v12"></path>
                                        </svg>
                                    </div>
                                    <div>
                                        <h3 class="font-semibold text-slate-900 dark:text-white">Import/Export</h3>
                                        <p class="text-sm text-slate-600 dark:text-slate-400">JSON, SQL, ZIP</p>
                                    </div>
                                </div>
                                <div class="flex items-start gap-3 p-4 bg-white/60 dark:bg-slate-800/60 rounded-xl backdrop-blur-sm border border-slate-200/50 dark:border-slate-700/50">
                                    <div class="flex-shrink-0 w-10 h-10 rounded-lg bg-amber-100 dark:bg-amber-900/30 flex items-center justify-center">
                                        <svg class="w-5 h-5 text-amber-600 dark:text-amber-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M3 9a2 2 0 012-2h.93a2 2 0 001.664-.89l.812-1.22A2 2 0 0110.07 4h3.86a2 2 0 011.664.89l.812 1.22A2 2 0 0018.07 7H19a2 2 0 012 2v9a2 2 0 01-2 2H5a2 2 0 01-2-2V9z"></path>
                                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 13a3 3 0 11-6 0 3 3 0 016 0z"></path>
                                        </svg>
                                    </div>
                                    <div>
                                        <h3 class="font-semibold text-slate-900 dark:text-white">Lampiran Foto</h3>
                                        <p class="text-sm text-slate-600 dark:text-slate-400">Bukti transaksi</p>
                                    </div>
                                </div>
                            </div>

                            {{-- CTA Buttons --}}
                            <div class="flex flex-col sm:flex-row gap-4 justify-center lg:justify-start">
                                <a href="{{ site_setting('welcome_cta_url', '/login') }}" class="inline-flex items-center justify-center gap-2 px-8 py-4 bg-gradient-to-r from-blue-600 to-indigo-600 hover:from-blue-700 hover:to-indigo-700 text-white rounded-xl text-base font-semibold transition-all shadow-lg shadow-blue-500/25 hover:shadow-blue-500/40 hover:-translate-y-0.5">
                                    {{ site_setting('welcome_cta_text', 'Mulai Sekarang') }}
                                    <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M13 7l5 5m0 0l-5 5m5-5H6"></path>
                                    </svg>
                                </a>
                                <a href="{{ site_setting('welcome_primary_link_url', 'https://github.com/sekadau-online/report/tree/main/docs') }}" target="_blank" class="inline-flex items-center justify-center gap-2 px-8 py-4 bg-white dark:bg-slate-800 hover:bg-slate-50 dark:hover:bg-slate-700 text-slate-700 dark:text-slate-300 rounded-xl text-base font-semibold transition-all border border-slate-200 dark:border-slate-700 hover:-translate-y-0.5">
                                    <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 6.253v13m0-13C10.832 5.477 9.246 5 7.5 5S4.168 5.477 3 6.253v13C4.168 18.477 5.754 18 7.5 18s3.332.477 4.5 1.253m0-13C13.168 5.477 14.754 5 16.5 5c1.747 0 3.332.477 4.5 1.253v13C19.832 18.477 18.247 18 16.5 18c-1.746 0-3.332.477-4.5 1.253"></path>
                                    </svg>
                                    {{ site_setting('welcome_primary_link_text', 'Dokumentasi') }}
                                </a>
                            </div>
                        </div>

                        {{-- Right Content - Illustration --}}
                        <div class="relative hidden lg:block">
                            <div class="relative z-10">
                                {{-- Dashboard Preview Card --}}
                                <div class="bg-white dark:bg-slate-800 rounded-2xl shadow-2xl shadow-slate-900/10 dark:shadow-black/30 border border-slate-200 dark:border-slate-700 overflow-hidden">
                                    {{-- Header Bar --}}
                                    <div class="bg-slate-50 dark:bg-slate-900 px-4 py-3 border-b border-slate-200 dark:border-slate-700 flex items-center gap-2">
                                        <div class="flex gap-1.5">
                                            <div class="w-3 h-3 rounded-full bg-red-400"></div>
                                            <div class="w-3 h-3 rounded-full bg-amber-400"></div>
                                            <div class="w-3 h-3 rounded-full bg-green-400"></div>
                                        </div>
                                        <div class="flex-1 flex justify-center">
                                            <div class="px-4 py-1 bg-slate-200 dark:bg-slate-700 rounded-md text-xs text-slate-500 dark:text-slate-400">
                                                dashboard
                                            </div>
                                        </div>
                                    </div>
                                    {{-- Content --}}
                                    <div class="p-6 space-y-4">
                                        {{-- Stats Row --}}
                                        <div class="grid grid-cols-3 gap-3">
                                            <div class="bg-gradient-to-br from-green-50 to-emerald-50 dark:from-green-900/20 dark:to-emerald-900/20 rounded-xl p-4 border border-green-100 dark:border-green-800/30">
                                                <div class="text-xs text-green-600 dark:text-green-400 font-medium">Pemasukan</div>
                                                <div class="text-lg font-bold text-green-700 dark:text-green-300 mt-1">Rp 12.5M</div>
                                            </div>
                                            <div class="bg-gradient-to-br from-red-50 to-rose-50 dark:from-red-900/20 dark:to-rose-900/20 rounded-xl p-4 border border-red-100 dark:border-red-800/30">
                                                <div class="text-xs text-red-600 dark:text-red-400 font-medium">Pengeluaran</div>
                                                <div class="text-lg font-bold text-red-700 dark:text-red-300 mt-1">Rp 8.3M</div>
                                            </div>
                                            <div class="bg-gradient-to-br from-blue-50 to-indigo-50 dark:from-blue-900/20 dark:to-indigo-900/20 rounded-xl p-4 border border-blue-100 dark:border-blue-800/30">
                                                <div class="text-xs text-blue-600 dark:text-blue-400 font-medium">Saldo</div>
                                                <div class="text-lg font-bold text-blue-700 dark:text-blue-300 mt-1">Rp 4.2M</div>
                                            </div>
                                        </div>
                                        {{-- Chart Placeholder --}}
                                        <div class="bg-slate-50 dark:bg-slate-900/50 rounded-xl p-4 border border-slate-200 dark:border-slate-700">
                                            <div class="flex items-end justify-between gap-2 h-24">
                                                <div class="w-full bg-blue-200 dark:bg-blue-700 rounded-t h-[40%]"></div>
                                                <div class="w-full bg-blue-300 dark:bg-blue-600 rounded-t h-[60%]"></div>
                                                <div class="w-full bg-blue-400 dark:bg-blue-500 rounded-t h-[45%]"></div>
                                                <div class="w-full bg-blue-500 dark:bg-blue-400 rounded-t h-[80%]"></div>
                                                <div class="w-full bg-blue-400 dark:bg-blue-500 rounded-t h-[65%]"></div>
                                                <div class="w-full bg-blue-500 dark:bg-blue-400 rounded-t h-[90%]"></div>
                                                <div class="w-full bg-indigo-500 dark:bg-indigo-400 rounded-t h-[100%]"></div>
                                            </div>
                                        </div>
                                        {{-- Recent List --}}
                                        <div class="space-y-2">
                                            <div class="flex items-center justify-between p-3 bg-slate-50 dark:bg-slate-900/50 rounded-lg">
                                                <div class="flex items-center gap-3">
                                                    <div class="w-8 h-8 rounded-full bg-green-100 dark:bg-green-900/30 flex items-center justify-center">
                                                        <svg class="w-4 h-4 text-green-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 10l7-7m0 0l7 7m-7-7v18"></path>
                                                        </svg>
                                                    </div>
                                                    <span class="text-sm text-slate-700 dark:text-slate-300">Gaji Bulanan</span>
                                                </div>
                                                <span class="text-sm font-semibold text-green-600 dark:text-green-400">+Rp 5.000.000</span>
                                            </div>
                                            <div class="flex items-center justify-between p-3 bg-slate-50 dark:bg-slate-900/50 rounded-lg">
                                                <div class="flex items-center gap-3">
                                                    <div class="w-8 h-8 rounded-full bg-red-100 dark:bg-red-900/30 flex items-center justify-center">
                                                        <svg class="w-4 h-4 text-red-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 14l-7 7m0 0l-7-7m7 7V3"></path>
                                                        </svg>
                                                    </div>
                                                    <span class="text-sm text-slate-700 dark:text-slate-300">Belanja Bulanan</span>
                                                </div>
                                                <span class="text-sm font-semibold text-red-600 dark:text-red-400">-Rp 1.500.000</span>
                                            </div>
                                        </div>
                                    </div>
                                </div>
                            </div>
                            {{-- Decorative Elements --}}
                            <div class="absolute -top-6 -right-6 w-24 h-24 bg-gradient-to-br from-blue-400 to-indigo-500 rounded-2xl rotate-12 opacity-20"></div>
                            <div class="absolute -bottom-6 -left-6 w-32 h-32 bg-gradient-to-br from-cyan-400 to-blue-500 rounded-full opacity-20"></div>
                        </div>
                    </div>
                </div>
            </main>

            {{-- Footer --}}
            <footer class="px-6 py-6">
                <div class="max-w-6xl mx-auto text-center text-sm text-slate-500 dark:text-slate-400">
                    <p>&copy; {{ date('Y') }} {{ site_setting('site_name', 'LKEU-RAPI') }}. All rights reserved.</p>
                </div>
            </footer>
        </div>
    </body>
</html>
