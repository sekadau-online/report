<?php

use App\Models\FinancialReport;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Storage;
use Livewire\Volt\Component;

new class extends Component {
    public FinancialReport $report;

    public function mount(FinancialReport $report): void
    {
        if ($report->user_id !== Auth::id()) {
            abort(403);
        }

        $this->report = $report;
    }

    public function delete(): void
    {
        if ($this->report->photo) {
            Storage::disk('public')->delete($this->report->photo);
        }

        $this->report->delete();

        session()->flash('status', 'Laporan keuangan berhasil dihapus.');

        $this->redirect(route('financial-reports.index'), navigate: true);
    }

    public function with(): array
    {
        return [
            'types' => FinancialReport::types(),
            'categories' => FinancialReport::categories(),
        ];
    }
}; ?>

<section class="w-full">
    @include('partials.financial-reports-heading')

    <x-financial-reports.layout :heading="__('Detail Laporan')" :subheading="__('Lihat informasi lengkap laporan keuangan')">
        <div class="space-y-6">
            {{-- Status Badge --}}
            <div class="flex items-center gap-2">
                <flux:badge size="lg" :color="$report->type === 'income' ? 'green' : 'red'">
                    {{ $types[$report->type] ?? $report->type }}
                </flux:badge>
                @if ($report->category)
                    <flux:badge size="lg" color="zinc">
                        {{ $categories[$report->category] ?? $report->category }}
                    </flux:badge>
                @endif
            </div>

            {{-- Title & Description --}}
            <div class="rounded-xl border border-neutral-200 p-6 dark:border-neutral-700">
                <flux:heading size="lg">{{ $report->title }}</flux:heading>
                @if ($report->description)
                    <flux:text class="mt-2 text-neutral-600 dark:text-neutral-400">
                        {{ $report->description }}
                    </flux:text>
                @endif
            </div>

            {{-- Amount --}}
            <div class="rounded-xl border p-6 {{ $report->type === 'income' ? 'border-green-200 bg-green-50 dark:border-green-800 dark:bg-green-950' : 'border-red-200 bg-red-50 dark:border-red-800 dark:bg-red-950' }}">
                <flux:text class="text-sm {{ $report->type === 'income' ? 'text-green-600 dark:text-green-400' : 'text-red-600 dark:text-red-400' }}">
                    {{ __('Jumlah') }}
                </flux:text>
                <flux:heading size="xl" class="{{ $report->type === 'income' ? 'text-green-700 dark:text-green-300' : 'text-red-700 dark:text-red-300' }}">
                    {{ $report->type === 'income' ? '+' : '-' }} {{ $report->formatted_amount }}
                </flux:heading>
            </div>

            {{-- Details Grid --}}
            <div class="grid gap-4 md:grid-cols-2">
                <div class="rounded-xl border border-neutral-200 p-4 dark:border-neutral-700">
                    <flux:text class="text-sm text-neutral-500 dark:text-neutral-400">{{ __('Tanggal Transaksi') }}</flux:text>
                    <flux:heading size="base" class="mt-1">{{ $report->report_date->translatedFormat('l, d F Y') }}</flux:heading>
                </div>
                <div class="rounded-xl border border-neutral-200 p-4 dark:border-neutral-700">
                    <flux:text class="text-sm text-neutral-500 dark:text-neutral-400">{{ __('Dibuat Pada') }}</flux:text>
                    <flux:heading size="base" class="mt-1">{{ $report->created_at->translatedFormat('d F Y, H:i') }}</flux:heading>
                </div>
            </div>

            {{-- Photo --}}
            @if ($report->photo)
                <div class="rounded-xl border border-neutral-200 p-4 dark:border-neutral-700">
                    <flux:text class="mb-3 text-sm text-neutral-500 dark:text-neutral-400">{{ __('Foto Bukti') }}</flux:text>
                    <div class="overflow-hidden rounded-lg">
                        <a href="{{ Storage::url($report->photo) }}" target="_blank" class="block">
                            <img
                                src="{{ Storage::url($report->photo) }}"
                                alt="{{ $report->title }}"
                                class="max-h-96 w-auto rounded-lg border border-neutral-200 object-contain transition hover:opacity-90 dark:border-neutral-700"
                            >
                        </a>
                        <flux:text class="mt-2 text-xs text-neutral-400">
                            {{ __('Klik gambar untuk melihat ukuran penuh') }}
                        </flux:text>
                    </div>
                </div>
            @endif

            {{-- Actions --}}
            <div class="flex items-center justify-between border-t border-neutral-200 pt-6 dark:border-neutral-700">
                <flux:button variant="ghost" :href="route('financial-reports.index')" icon="arrow-left" wire:navigate>
                    {{ __('Kembali') }}
                </flux:button>

                <div class="flex items-center gap-2">
                    <flux:button variant="primary" :href="route('financial-reports.edit', $report)" icon="pencil" wire:navigate>
                        {{ __('Edit') }}
                    </flux:button>
                    <flux:modal.trigger name="delete-report">
                        <flux:button variant="danger" icon="trash">
                            {{ __('Hapus') }}
                        </flux:button>
                    </flux:modal.trigger>
                </div>
            </div>
        </div>

        {{-- Delete Modal --}}
        <flux:modal name="delete-report" class="min-w-[22rem]">
            <div class="space-y-6">
                <div>
                    <flux:heading size="lg">{{ __('Hapus Laporan') }}</flux:heading>
                    <flux:text class="mt-2">
                        {{ __('Apakah Anda yakin ingin menghapus laporan ":title"? Tindakan ini tidak dapat dibatalkan.', ['title' => $report->title]) }}
                    </flux:text>
                </div>
                <div class="flex gap-2">
                    <flux:spacer />
                    <flux:modal.close>
                        <flux:button variant="ghost">{{ __('Batal') }}</flux:button>
                    </flux:modal.close>
                    <flux:button variant="danger" wire:click="delete">
                        {{ __('Hapus') }}
                    </flux:button>
                </div>
            </div>
        </flux:modal>
    </x-financial-reports.layout>
</section>
