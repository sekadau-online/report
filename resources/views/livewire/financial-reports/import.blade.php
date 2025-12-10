<?php

use App\Services\FinancialReport\ImportService;
use Illuminate\Support\Facades\Auth;
use Livewire\Attributes\Validate;
use Livewire\Volt\Component;
use Livewire\WithFileUploads;

new class extends Component {
    use WithFileUploads;

    #[Validate('required|file|max:51200|mimes:zip,json,sql,txt')]
    public $importFile = null;

    public bool $isImporting = false;
    public bool $showResult = false;

    /** @var array<string, int> */
    public array $stats = [];

    /** @var array<string, string> */
    public array $importErrors = [];

    public function import(): void
    {
        $this->validate();

        $this->isImporting = true;
        $this->showResult = false;
        $this->stats = [];
        $this->importErrors = [];

        try {
            // Additional validation
            $validationErrors = ImportService::validateFile($this->importFile);
            if (! empty($validationErrors)) {
                $this->importErrors = $validationErrors;
                $this->isImporting = false;

                return;
            }

            $importService = new ImportService;
            $result = $importService->import($this->importFile, Auth::id());

            $this->stats = $result['stats'];
            $this->importErrors = $result['errors'];
            $this->showResult = true;

            if ($result['success'] && $result['stats']['imported'] > 0) {
                $this->dispatch('reports-imported');
            }
        } catch (\Throwable $e) {
            $this->importErrors = ['general' => $e->getMessage()];
        } finally {
            $this->isImporting = false;
            $this->importFile = null;
        }
    }

    public function resetImport(): void
    {
        $this->importFile = null;
        $this->showResult = false;
        $this->stats = [];
        $this->importErrors = [];
    }

    public function closeAndRefresh(): void
    {
        $this->resetImport();
        $this->dispatch('close-modal', name: 'import-modal');

        if (! empty($this->stats['imported']) && $this->stats['imported'] > 0) {
            $this->redirect(route('financial-reports.index'), navigate: true);
        }
    }
}; ?>

<div>
    <flux:modal name="import-modal" class="md:w-96">
        <div class="space-y-6">
            <div>
                <flux:heading size="lg">{{ __('Import Laporan') }}</flux:heading>
                <flux:subheading>{{ __('Import data laporan keuangan dari file') }}</flux:subheading>
            </div>

            @if (!$showResult)
                {{-- Import Form --}}
                <form wire:submit="import" class="space-y-4">
                    {{-- File Upload --}}
                    <div>
                        <flux:label>{{ __('File Import') }}</flux:label>
                        <div class="mt-2">
                            <div
                                x-data="{ isDropping: false, fileName: '' }"
                                x-on:dragover.prevent="isDropping = true"
                                x-on:dragleave.prevent="isDropping = false"
                                x-on:drop.prevent="isDropping = false"
                                class="relative"
                            >
                                <label
                                    for="import-file-upload"
                                    :class="isDropping ? 'border-blue-500 bg-blue-50 dark:bg-blue-950' : 'border-neutral-300 dark:border-neutral-600'"
                                    class="flex cursor-pointer flex-col items-center justify-center rounded-lg border-2 border-dashed p-6 transition hover:border-neutral-400 dark:hover:border-neutral-500"
                                >
                                    @if ($importFile)
                                        <flux:icon name="document-check" class="mb-2 size-10 text-green-500" />
                                        <flux:text class="text-center font-medium text-green-600 dark:text-green-400">
                                            {{ $importFile->getClientOriginalName() }}
                                        </flux:text>
                                        <flux:text class="text-xs text-neutral-500">
                                            {{ number_format($importFile->getSize() / 1024, 2) }} KB
                                        </flux:text>
                                    @else
                                        <flux:icon name="cloud-arrow-up" class="mb-2 size-10 text-neutral-400" />
                                        <flux:text class="text-center">
                                            <span class="font-medium text-blue-600 dark:text-blue-400">{{ __('Klik untuk upload') }}</span>
                                            {{ __('atau drag and drop') }}
                                        </flux:text>
                                        <flux:text class="text-xs text-neutral-500">ZIP, JSON, SQL max 50MB</flux:text>
                                    @endif
                                </label>
                                <input
                                    id="import-file-upload"
                                    type="file"
                                    wire:model="importFile"
                                    accept=".zip,.json,.sql"
                                    class="absolute inset-0 h-full w-full cursor-pointer opacity-0"
                                >
                            </div>

                            <div wire:loading wire:target="importFile" class="mt-2">
                                <flux:text class="text-sm text-blue-600 dark:text-blue-400">
                                    {{ __('Mengupload file...') }}
                                </flux:text>
                            </div>

                            @error('importFile')
                                <flux:text class="mt-2 text-sm text-red-600 dark:text-red-400">{{ $message }}</flux:text>
                            @enderror
                        </div>
                    </div>

                    {{-- Info --}}
                    <flux:callout variant="info" icon="information-circle">
                        <div class="text-sm">
                            <p class="font-medium">{{ __('Format yang didukung:') }}</p>
                            <ul class="mt-1 list-inside list-disc text-xs">
                                <li>{{ __('ZIP - Export dengan foto') }}</li>
                                <li>{{ __('JSON - Data dalam format JSON') }}</li>
                                <li>{{ __('SQL - Statement INSERT SQL') }}</li>
                            </ul>
                        </div>
                    </flux:callout>

                    {{-- Errors --}}
                    @if (!empty($importErrors))
                        <flux:callout variant="danger" icon="exclamation-triangle">
                            <div class="text-sm">
                                @foreach ($importErrors as $error)
                                    <p>{{ $error }}</p>
                                @endforeach
                            </div>
                        </flux:callout>
                    @endif

                    {{-- Actions --}}
                    <div class="flex justify-end gap-2 pt-4">
                        <flux:modal.close>
                            <flux:button variant="ghost" wire:click="resetImport">{{ __('Batal') }}</flux:button>
                        </flux:modal.close>
                        <flux:button
                            type="submit"
                            variant="primary"
                            icon="arrow-up-tray"
                            :disabled="!$importFile || $isImporting"
                        >
                            <span wire:loading.remove wire:target="import">{{ __('Import') }}</span>
                            <span wire:loading wire:target="import">{{ __('Mengimport...') }}</span>
                        </flux:button>
                    </div>
                </form>
            @else
                {{-- Import Result --}}
                <div class="space-y-4">
                    @if (empty($importErrors) || (isset($stats['imported']) && $stats['imported'] > 0))
                        <flux:callout variant="success" icon="check-circle">
                            {{ __('Import berhasil!') }}
                        </flux:callout>
                    @endif

                    {{-- Statistics --}}
                    <div class="rounded-lg border border-neutral-200 p-4 dark:border-neutral-700">
                        <flux:heading size="sm" class="mb-3">{{ __('Hasil Import') }}</flux:heading>
                        <div class="grid grid-cols-3 gap-4 text-center">
                            <div>
                                <div class="text-2xl font-bold text-green-600 dark:text-green-400">
                                    {{ $stats['imported'] ?? 0 }}
                                </div>
                                <flux:text class="text-xs">{{ __('Berhasil') }}</flux:text>
                            </div>
                            <div>
                                <div class="text-2xl font-bold text-yellow-600 dark:text-yellow-400">
                                    {{ $stats['skipped'] ?? 0 }}
                                </div>
                                <flux:text class="text-xs">{{ __('Dilewati') }}</flux:text>
                            </div>
                            <div>
                                <div class="text-2xl font-bold text-blue-600 dark:text-blue-400">
                                    {{ $stats['photos_imported'] ?? 0 }}
                                </div>
                                <flux:text class="text-xs">{{ __('Foto') }}</flux:text>
                            </div>
                        </div>
                    </div>

                    {{-- Errors if any --}}
                    @if (!empty($importErrors))
                        <div class="max-h-40 overflow-y-auto rounded-lg border border-red-200 bg-red-50 p-4 dark:border-red-800 dark:bg-red-950">
                            <flux:heading size="sm" class="mb-2 text-red-700 dark:text-red-300">{{ __('Error') }}</flux:heading>
                            <div class="space-y-1 text-xs text-red-600 dark:text-red-400">
                                @foreach ($importErrors as $key => $error)
                                    <p>{{ $key }}: {{ $error }}</p>
                                @endforeach
                            </div>
                        </div>
                    @endif

                    {{-- Actions --}}
                    <div class="flex justify-end gap-2 pt-4">
                        <flux:button variant="ghost" wire:click="resetImport">
                            {{ __('Import Lagi') }}
                        </flux:button>
                        <flux:button variant="primary" wire:click="closeAndRefresh">
                            {{ __('Selesai') }}
                        </flux:button>
                    </div>
                </div>
            @endif
        </div>
    </flux:modal>
</div>
