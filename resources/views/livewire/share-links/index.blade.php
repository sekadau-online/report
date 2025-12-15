<?php

declare(strict_types=1);

use App\Models\ShareLink;
use Illuminate\Support\Collection;
use Livewire\Volt\Component;

new class extends Component {
    public Collection $shareLinks;

    public function mount(): void
    {
        $this->loadShareLinks();
    }

    public function loadShareLinks(): void
    {
        $this->shareLinks = auth()->user()->shareLinks()->latest()->get();
    }

    public function toggleActive(ShareLink $shareLink): void
    {
        $this->authorize('update', $shareLink);

        $shareLink->update(['is_active' => ! $shareLink->is_active]);
        $this->loadShareLinks();
    }

    public function delete(ShareLink $shareLink): void
    {
        $this->authorize('delete', $shareLink);

        $shareLink->delete();
        $this->loadShareLinks();
    }

    public function copyLink(ShareLink $shareLink): void
    {
        $this->dispatch('copy-to-clipboard', url: $shareLink->getShareUrl());
    }
}; ?>

<section class="w-full">
    <x-slot name="title">{{ __('Share Links') }}</x-slot>

    <div class="flex h-full w-full flex-1 flex-col gap-6 text-gray-500 dark:text-gray-400">
        <div class="flex items-center justify-between">
            <div>
                <flux:heading size="xl">{{ __('Share Links') }}</flux:heading>
                <flux:subheading>{{ __('Kelola link berbagi untuk laporan keuangan Anda') }}</flux:subheading>
            </div>
            <flux:button href="{{ route('share-links.create') }}" variant="primary" icon="plus">
                {{ __('Buat Link Baru') }}
            </flux:button>
        </div>

        @if ($shareLinks->isEmpty())
            <div class="flex flex-col items-center justify-center py-12 text-center">
                <div class="rounded-full bg-gray-100 dark:bg-gray-800 p-4 mb-4">
                    <flux:icon.link class="size-8 text-gray-400" />
                </div>
                <flux:heading size="lg">{{ __('Belum ada share link') }}</flux:heading>
                <flux:subheading class="mt-1">{{ __('Buat link untuk berbagi laporan keuangan Anda.') }}</flux:subheading>
                <flux:button href="{{ route('share-links.create') }}" variant="primary" class="mt-4" icon="plus">
                    {{ __('Buat Link Pertama') }}
                </flux:button>
            </div>
        @else
            <div class="grid gap-4">
                @foreach ($shareLinks as $link)
                    <div wire:key="share-link-{{ $link->id }}" class="bg-white dark:bg-zinc-800 rounded-xl border border-zinc-200 dark:border-zinc-700 p-5">
                        <div class="flex items-start justify-between gap-4">
                            <div class="flex-1 min-w-0">
                                <div class="flex items-center gap-2 mb-1">
                                    <h3 class="font-semibold text-zinc-900 dark:text-white truncate">
                                        {{ $link->name }}
                                    </h3>
                                    @if ($link->requiresPassword())
                                        <flux:badge color="amber" size="sm" icon="lock-closed">Password</flux:badge>
                                    @endif
                                    @if ($link->is_active)
                                        <flux:badge color="green" size="sm">Aktif</flux:badge>
                                    @else
                                        <flux:badge color="zinc" size="sm">Nonaktif</flux:badge>
                                    @endif
                                    @if ($link->isExpired())
                                        <flux:badge color="red" size="sm">Kedaluwarsa</flux:badge>
                                    @elseif ($link->expires_at)
                                        <flux:badge color="blue" size="sm">
                                            {{ __('Berakhir :date', ['date' => $link->expires_at->format('d M Y')]) }}
                                        </flux:badge>
                                    @endif
                                </div>

                                <div class="flex items-center gap-4 text-sm text-zinc-500 dark:text-zinc-400 mt-2">
                                    <span class="flex items-center gap-1">
                                        <flux:icon.eye class="size-4" />
                                        {{ number_format($link->view_count) }} views
                                    </span>
                                    @if ($link->last_viewed_at)
                                        <span class="flex items-center gap-1">
                                            <flux:icon.clock class="size-4" />
                                            {{ __('Terakhir dilihat :time', ['time' => $link->last_viewed_at->diffForHumans()]) }}
                                        </span>
                                    @endif
                                </div>

                                <div class="mt-3 flex items-center gap-2">
                                    <code class="text-xs bg-zinc-100 dark:bg-zinc-900 px-2 py-1 rounded truncate max-w-md">
                                        {{ $link->getShareUrl() }}
                                    </code>
                                    <flux:tooltip content="Salin Link">
                                        <flux:button
                                            size="xs"
                                            variant="ghost"
                                            icon="clipboard"
                                            wire:click="copyLink({{ $link->id }})"
                                        />
                                    </flux:tooltip>
                                </div>
                            </div>

                            <div class="flex items-center gap-2">
                                <flux:button
                                    size="sm"
                                    variant="ghost"
                                    icon="pencil"
                                    href="{{ route('share-links.edit', $link) }}"
                                />
                                <flux:button
                                    size="sm"
                                    variant="ghost"
                                    :icon="$link->is_active ? 'eye-slash' : 'eye'"
                                    wire:click="toggleActive({{ $link->id }})"
                                />
                                <flux:button
                                    size="sm"
                                    variant="ghost"
                                    icon="trash"
                                    wire:click="delete({{ $link->id }})"
                                    wire:confirm="{{ __('Apakah Anda yakin ingin menghapus link ini?') }}"
                                />
                            </div>
                        </div>
                    </div>
                @endforeach
            </div>
        @endif
    </div>

    {{-- Toast notification --}}
    <div
        x-data="{ show: false, message: '' }"
        x-on:copy-success.window="show = true; message = $event.detail.message; setTimeout(() => show = false, 2000)"
        x-show="show"
        x-transition:enter="transition ease-out duration-200"
        x-transition:enter-start="opacity-0 translate-y-2"
        x-transition:enter-end="opacity-100 translate-y-0"
        x-transition:leave="transition ease-in duration-150"
        x-transition:leave-start="opacity-100 translate-y-0"
        x-transition:leave-end="opacity-0 translate-y-2"
        x-cloak
        class="fixed bottom-4 right-4 z-50 flex items-center gap-2 rounded-lg bg-green-600 px-4 py-3 text-sm font-medium text-white shadow-lg"
    >
        <flux:icon.check-circle class="size-5" />
        <span x-text="message"></span>
    </div>

    @script
    <script>
        $wire.on('copy-to-clipboard', ({ url }) => {
            navigator.clipboard.writeText(url).then(() => {
                window.dispatchEvent(new CustomEvent('copy-success', {
                    detail: { message: 'Link berhasil disalin!' }
                }));
            });
        });
    </script>
    @endscript
</section>
