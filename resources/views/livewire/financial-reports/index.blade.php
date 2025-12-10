<?php

use App\Models\FinancialReport;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Storage;
use Livewire\Volt\Component;
use Livewire\WithPagination;

new class extends Component {
    use WithPagination;

    public string $search = '';
    public string $typeFilter = '';
    public string $categoryFilter = '';
    public string $sortField = 'report_date';
    public string $sortDirection = 'desc';

    public function updatedSearch(): void
    {
        $this->resetPage();
    }

    public function updatedTypeFilter(): void
    {
        $this->resetPage();
    }

    public function updatedCategoryFilter(): void
    {
        $this->resetPage();
    }

    public function sortBy(string $field): void
    {
        if ($this->sortField === $field) {
            $this->sortDirection = $this->sortDirection === 'asc' ? 'desc' : 'asc';
        } else {
            $this->sortField = $field;
            $this->sortDirection = 'asc';
        }
    }

    public function delete(FinancialReport $report): void
    {
        if ($report->user_id !== Auth::id()) {
            return;
        }

        if ($report->photo) {
            Storage::disk('public')->delete($report->photo);
        }

        $report->delete();

        $this->dispatch('report-deleted');
    }

    public function with(): array
    {
        $reports = FinancialReport::query()
            ->where('user_id', Auth::id())
            ->when($this->search, function ($query) {
                $query->where(function ($q) {
                    $q->where('title', 'like', "%{$this->search}%")
                        ->orWhere('description', 'like', "%{$this->search}%");
                });
            })
            ->when($this->typeFilter, fn ($query) => $query->where('type', $this->typeFilter))
            ->when($this->categoryFilter, fn ($query) => $query->where('category', $this->categoryFilter))
            ->orderBy($this->sortField, $this->sortDirection)
            ->paginate(10);

        $totalIncome = FinancialReport::where('user_id', Auth::id())
            ->where('type', 'income')
            ->sum('amount');

        $totalExpense = FinancialReport::where('user_id', Auth::id())
            ->where('type', 'expense')
            ->sum('amount');

        return [
            'reports' => $reports,
            'types' => FinancialReport::types(),
            'categories' => FinancialReport::categories(),
            'totalIncome' => $totalIncome,
            'totalExpense' => $totalExpense,
            'balance' => $totalIncome - $totalExpense,
        ];
    }
}; ?>

<section class="w-full">
    @include('partials.financial-reports-heading')

    <div class="space-y-6">
        {{-- Summary Cards --}}
        <div class="grid gap-4 md:grid-cols-3">
            <div class="rounded-xl border border-green-200 bg-green-50 p-4 dark:border-green-800 dark:bg-green-950">
                <flux:text class="text-sm text-green-600 dark:text-green-400">{{ __('Total Pemasukan') }}</flux:text>
                <flux:heading size="lg" class="text-green-700 dark:text-green-300">
                    Rp {{ number_format($totalIncome, 0, ',', '.') }}
                </flux:heading>
            </div>
            <div class="rounded-xl border border-red-200 bg-red-50 p-4 dark:border-red-800 dark:bg-red-950">
                <flux:text class="text-sm text-red-600 dark:text-red-400">{{ __('Total Pengeluaran') }}</flux:text>
                <flux:heading size="lg" class="text-red-700 dark:text-red-300">
                    Rp {{ number_format($totalExpense, 0, ',', '.') }}
                </flux:heading>
            </div>
            <div class="rounded-xl border border-blue-200 bg-blue-50 p-4 dark:border-blue-800 dark:bg-blue-950">
                <flux:text class="text-sm text-blue-600 dark:text-blue-400">{{ __('Saldo') }}</flux:text>
                <flux:heading size="lg" class="{{ $balance >= 0 ? 'text-blue-700 dark:text-blue-300' : 'text-red-700 dark:text-red-300' }}">
                    Rp {{ number_format($balance, 0, ',', '.') }}
                </flux:heading>
            </div>
        </div>

        {{-- Filters & Actions --}}
        <div class="flex flex-col gap-4 md:flex-row md:items-center md:justify-between">
            <div class="flex flex-1 flex-col gap-4 md:flex-row md:items-center">
                <div class="w-full md:max-w-xs">
                    <flux:input
                        wire:model.live.debounce.300ms="search"
                        placeholder="{{ __('Cari laporan...') }}"
                        icon="magnifying-glass"
                    />
                </div>
                <div class="flex gap-2">
                    <flux:select wire:model.live="typeFilter" placeholder="{{ __('Semua Tipe') }}">
                        <flux:select.option value="">{{ __('Semua Tipe') }}</flux:select.option>
                        @foreach ($types as $value => $label)
                            <flux:select.option value="{{ $value }}">{{ $label }}</flux:select.option>
                        @endforeach
                    </flux:select>
                    <flux:select wire:model.live="categoryFilter" placeholder="{{ __('Semua Kategori') }}">
                        <flux:select.option value="">{{ __('Semua Kategori') }}</flux:select.option>
                        @foreach ($categories as $value => $label)
                            <flux:select.option value="{{ $value }}">{{ $label }}</flux:select.option>
                        @endforeach
                    </flux:select>
                </div>
            </div>
            <div class="flex gap-2">
                {{-- Import/Export Buttons --}}
                <flux:modal.trigger name="import-modal">
                    <flux:button variant="ghost" icon="arrow-up-tray">
                        {{ __('Import') }}
                    </flux:button>
                </flux:modal.trigger>
                <flux:modal.trigger name="export-modal">
                    <flux:button variant="ghost" icon="arrow-down-tray">
                        {{ __('Export') }}
                    </flux:button>
                </flux:modal.trigger>
                <flux:button variant="primary" :href="route('financial-reports.create')" icon="plus" wire:navigate>
                    {{ __('Tambah Laporan') }}
                </flux:button>
            </div>
        </div>

        {{-- Import/Export Modals --}}
        <livewire:financial-reports.export />
        <livewire:financial-reports.import />

        {{-- Data Table --}}
        <div class="overflow-hidden rounded-xl border border-neutral-200 dark:border-neutral-700">
            <div class="overflow-x-auto">
                <table class="w-full text-left text-sm">
                    <thead class="bg-neutral-50 dark:bg-neutral-800">
                        <tr>
                            <th class="cursor-pointer px-4 py-3 font-medium" wire:click="sortBy('report_date')">
                                <div class="flex items-center gap-1">
                                    {{ __('Tanggal') }}
                                    @if ($sortField === 'report_date')
                                        <flux:icon name="{{ $sortDirection === 'asc' ? 'chevron-up' : 'chevron-down' }}" variant="mini" class="size-4" />
                                    @endif
                                </div>
                            </th>
                            <th class="cursor-pointer px-4 py-3 font-medium" wire:click="sortBy('title')">
                                <div class="flex items-center gap-1">
                                    {{ __('Judul') }}
                                    @if ($sortField === 'title')
                                        <flux:icon name="{{ $sortDirection === 'asc' ? 'chevron-up' : 'chevron-down' }}" variant="mini" class="size-4" />
                                    @endif
                                </div>
                            </th>
                            <th class="px-4 py-3 font-medium">{{ __('Tipe') }}</th>
                            <th class="px-4 py-3 font-medium">{{ __('Kategori') }}</th>
                            <th class="cursor-pointer px-4 py-3 font-medium text-right" wire:click="sortBy('amount')">
                                <div class="flex items-center justify-end gap-1">
                                    {{ __('Jumlah') }}
                                    @if ($sortField === 'amount')
                                        <flux:icon name="{{ $sortDirection === 'asc' ? 'chevron-up' : 'chevron-down' }}" variant="mini" class="size-4" />
                                    @endif
                                </div>
                            </th>
                            <th class="px-4 py-3 font-medium text-center">{{ __('Foto') }}</th>
                            <th class="px-4 py-3 font-medium text-center">{{ __('Aksi') }}</th>
                        </tr>
                    </thead>
                    <tbody class="divide-y divide-neutral-200 dark:divide-neutral-700">
                        @forelse ($reports as $report)
                            <tr wire:key="report-{{ $report->id }}" class="hover:bg-neutral-50 dark:hover:bg-neutral-800/50">
                                <td class="px-4 py-3 whitespace-nowrap">
                                    {{ $report->report_date->format('d M Y') }}
                                </td>
                                <td class="px-4 py-3">
                                    <div class="font-medium">{{ $report->title }}</div>
                                    @if ($report->description)
                                        <div class="text-xs text-neutral-500 dark:text-neutral-400 truncate max-w-xs">
                                            {{ Str::limit($report->description, 50) }}
                                        </div>
                                    @endif
                                </td>
                                <td class="px-4 py-3">
                                    <flux:badge size="sm" :color="$report->type === 'income' ? 'green' : 'red'">
                                        {{ $types[$report->type] ?? $report->type }}
                                    </flux:badge>
                                </td>
                                <td class="px-4 py-3">
                                    {{ $categories[$report->category] ?? '-' }}
                                </td>
                                <td class="px-4 py-3 text-right whitespace-nowrap font-medium {{ $report->type === 'income' ? 'text-green-600 dark:text-green-400' : 'text-red-600 dark:text-red-400' }}">
                                    {{ $report->type === 'income' ? '+' : '-' }} {{ $report->formatted_amount }}
                                </td>
                                <td class="px-4 py-3 text-center">
                                    @if ($report->photo)
                                        <flux:badge size="sm" color="sky" icon="photo">
                                            {{ __('Ada') }}
                                        </flux:badge>
                                    @else
                                        <flux:text class="text-neutral-400">-</flux:text>
                                    @endif
                                </td>
                                <td class="px-4 py-3">
                                    <div class="flex items-center justify-center gap-1">
                                        <flux:button
                                            variant="ghost"
                                            size="sm"
                                            icon="eye"
                                            :href="route('financial-reports.show', $report)"
                                            wire:navigate
                                        />
                                        <flux:button
                                            variant="ghost"
                                            size="sm"
                                            icon="pencil"
                                            :href="route('financial-reports.edit', $report)"
                                            wire:navigate
                                        />
                                        <flux:modal.trigger name="delete-report-{{ $report->id }}">
                                            <flux:button variant="ghost" size="sm" icon="trash" />
                                        </flux:modal.trigger>
                                        <flux:modal name="delete-report-{{ $report->id }}" class="min-w-[22rem]">
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
                                                    <flux:button variant="danger" wire:click="delete({{ $report->id }})">
                                                        {{ __('Hapus') }}
                                                    </flux:button>
                                                </div>
                                            </div>
                                        </flux:modal>
                                    </div>
                                </td>
                            </tr>
                        @empty
                            <tr>
                                <td colspan="7" class="px-4 py-8 text-center">
                                    <div class="flex flex-col items-center gap-2">
                                        <flux:icon name="document-text" class="size-12 text-neutral-300 dark:text-neutral-600" />
                                        <flux:text class="text-neutral-500 dark:text-neutral-400">
                                            {{ __('Belum ada laporan keuangan.') }}
                                        </flux:text>
                                        <flux:button variant="primary" size="sm" :href="route('financial-reports.create')" icon="plus" wire:navigate>
                                            {{ __('Buat Laporan Pertama') }}
                                        </flux:button>
                                    </div>
                                </td>
                            </tr>
                        @endforelse
                    </tbody>
                </table>
            </div>
        </div>

        {{-- Pagination --}}
        @if ($reports->hasPages())
            <div class="mt-4">
                {{ $reports->links() }}
            </div>
        @endif
    </div>
</section>
