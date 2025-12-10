<?php

use App\Models\FinancialReport;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Storage;
use Livewire\Attributes\Validate;
use Livewire\Volt\Component;
use Livewire\WithFileUploads;

new class extends Component {
    use WithFileUploads;

    #[Validate('required|string|max:255')]
    public string $title = '';

    #[Validate('nullable|string|max:1000')]
    public ?string $description = null;

    #[Validate('required|in:income,expense')]
    public string $type = 'expense';

    #[Validate('required|numeric|min:0|max:999999999999.99')]
    public string $amount = '';

    #[Validate('required|date|before_or_equal:today')]
    public string $report_date = '';

    #[Validate('nullable|in:operational,salary,utilities,marketing,sales,investment,other')]
    public ?string $category = null;

    #[Validate('nullable|image|max:2048')]
    public $photo = null;

    public function mount(): void
    {
        $this->report_date = now()->format('Y-m-d');
    }

    public function save(): void
    {
        $validated = $this->validate();

        $photoPath = null;
        if ($this->photo) {
            $photoPath = $this->photo->store('financial-reports', 'public');
        }

        FinancialReport::create([
            'user_id' => Auth::id(),
            'title' => $validated['title'],
            'description' => $validated['description'],
            'type' => $validated['type'],
            'amount' => $validated['amount'],
            'report_date' => $validated['report_date'],
            'category' => $validated['category'],
            'photo' => $photoPath,
        ]);

        session()->flash('status', 'Laporan keuangan berhasil dibuat.');

        $this->redirect(route('financial-reports.index'), navigate: true);
    }

    public function removePhoto(): void
    {
        $this->photo = null;
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

    <x-financial-reports.layout :heading="__('Tambah Laporan')" :subheading="__('Buat laporan keuangan baru')">
        <form wire:submit="save" class="space-y-6">
            <flux:input
                wire:model="title"
                :label="__('Judul')"
                type="text"
                required
                autofocus
                placeholder="{{ __('Masukkan judul laporan') }}"
            />

            <flux:textarea
                wire:model="description"
                :label="__('Deskripsi')"
                rows="3"
                placeholder="{{ __('Deskripsi opsional tentang transaksi') }}"
            />

            <div class="grid gap-4 md:grid-cols-2">
                <flux:select wire:model="type" :label="__('Tipe Transaksi')" required>
                    @foreach ($types as $value => $label)
                        <flux:select.option value="{{ $value }}">{{ $label }}</flux:select.option>
                    @endforeach
                </flux:select>

                <flux:select wire:model="category" :label="__('Kategori')">
                    <flux:select.option value="">{{ __('Pilih Kategori') }}</flux:select.option>
                    @foreach ($categories as $value => $label)
                        <flux:select.option value="{{ $value }}">{{ $label }}</flux:select.option>
                    @endforeach
                </flux:select>
            </div>

            <div class="grid gap-4 md:grid-cols-2">
                <flux:input
                    wire:model="amount"
                    :label="__('Jumlah (Rp)')"
                    type="number"
                    step="0.01"
                    min="0"
                    required
                    placeholder="0"
                />

                <flux:input
                    wire:model="report_date"
                    :label="__('Tanggal')"
                    type="date"
                    required
                    max="{{ now()->format('Y-m-d') }}"
                />
            </div>

            {{-- Photo Upload --}}
            <div>
                <flux:label>{{ __('Foto Bukti (Opsional)') }}</flux:label>
                <div class="mt-2">
                    @if ($photo)
                        <div class="relative mb-4 inline-block">
                            <img src="{{ $photo->temporaryUrl() }}" alt="Preview" class="h-48 w-auto rounded-lg border border-neutral-200 object-cover dark:border-neutral-700">
                            <button
                                type="button"
                                wire:click="removePhoto"
                                class="absolute -top-2 -right-2 rounded-full bg-red-500 p-1 text-white shadow-lg hover:bg-red-600"
                            >
                                <flux:icon name="x-mark" variant="mini" class="size-4" />
                            </button>
                        </div>
                    @endif

                    <div
                        x-data="{ isDropping: false }"
                        x-on:dragover.prevent="isDropping = true"
                        x-on:dragleave.prevent="isDropping = false"
                        x-on:drop.prevent="isDropping = false"
                        class="relative"
                    >
                        <label
                            for="photo-upload"
                            :class="isDropping ? 'border-blue-500 bg-blue-50 dark:bg-blue-950' : 'border-neutral-300 dark:border-neutral-600'"
                            class="flex cursor-pointer flex-col items-center justify-center rounded-lg border-2 border-dashed p-6 transition hover:border-neutral-400 dark:hover:border-neutral-500"
                        >
                            <flux:icon name="cloud-arrow-up" class="mb-2 size-10 text-neutral-400" />
                            <flux:text class="text-center">
                                <span class="font-medium text-blue-600 dark:text-blue-400">{{ __('Klik untuk upload') }}</span>
                                {{ __('atau drag and drop') }}
                            </flux:text>
                            <flux:text class="text-xs text-neutral-500">PNG, JPG, GIF max 2MB</flux:text>
                        </label>
                        <input
                            id="photo-upload"
                            type="file"
                            wire:model="photo"
                            accept="image/*"
                            class="absolute inset-0 h-full w-full cursor-pointer opacity-0"
                        >
                    </div>

                    <div wire:loading wire:target="photo" class="mt-2">
                        <flux:text class="text-sm text-blue-600 dark:text-blue-400">
                            {{ __('Mengupload foto...') }}
                        </flux:text>
                    </div>

                    @error('photo')
                        <flux:text class="mt-2 text-sm text-red-600 dark:text-red-400">{{ $message }}</flux:text>
                    @enderror
                </div>
            </div>

            <div class="flex items-center justify-end gap-4 pt-4">
                <flux:button variant="ghost" :href="route('financial-reports.index')" wire:navigate>
                    {{ __('Batal') }}
                </flux:button>
                <flux:button variant="primary" type="submit" wire:loading.attr="disabled">
                    <span wire:loading.remove wire:target="save">{{ __('Simpan') }}</span>
                    <span wire:loading wire:target="save">{{ __('Menyimpan...') }}</span>
                </flux:button>
            </div>
        </form>
    </x-financial-reports.layout>
</section>
