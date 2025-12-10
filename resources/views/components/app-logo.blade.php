@php
    $logo = site_setting('logo');
    $siteName = site_setting('site_name', 'Laravel Starter Kit');
@endphp

@if ($logo)
    {{-- Logo sudah ada, tampilkan logo saja (logo SVG sudah termasuk teks) --}}
    <img src="{{ $logo }}" alt="{{ $siteName }}" class="h-8 max-w-[160px] object-contain" />
@else
    {{-- Tidak ada logo, tampilkan icon + nama --}}
    <div class="flex aspect-square size-8 items-center justify-center rounded-md bg-accent-content text-accent-foreground">
        <x-app-logo-icon class="size-5 fill-current text-white dark:text-black" />
    </div>
    <div class="ms-1 grid flex-1 text-start text-sm">
        <span class="mb-0.5 truncate leading-tight font-semibold">{{ $siteName }}</span>
    </div>
@endif
