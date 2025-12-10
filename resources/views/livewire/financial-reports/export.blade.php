<?php

use App\Models\FinancialReport;
use App\Services\FinancialReport\ExportService;
use Illuminate\Support\Facades\Auth;
use Livewire\Volt\Component;

new class extends Component {
    public string $format = 'json';
    public bool $includePhotos = true;
    public string $typeFilter = '';
    public string $categoryFilter = '';
    public ?string $dateFrom = null;
    public ?string $dateTo = null;

    public bool $isExporting = false;
    public ?string $errorMessage = null;

    public function export(): mixed
    {
        $this->errorMessage = null;
        $this->isExporting = true;

        try {
            $filters = array_filter([
                'type' => $this->typeFilter ?: null,
                'category' => $this->categoryFilter ?: null,
                'date_from' => $this->dateFrom ?: null,
                'date_to' => $this->dateTo ?: null,
            ]);

            $exportService = new ExportService;
            $filePath = $exportService->export(
                userId: Auth::id(),
                format: $this->format,
                includePhotos: $this->includePhotos,
                filters: $filters ?: null
            );

            $filename = basename($filePath);

            // Return download response
            return response()->download($filePath, $filename)->deleteFileAfterSend(true);
        } catch (\Throwable $e) {
            $this->errorMessage = $e->getMessage();
            $this->isExporting = false;

            return null;
        }
    }

    public function with(): array
    {
        $reportCount = FinancialReport::where('user_id', Auth::id())
            ->when($this->typeFilter, fn ($q) => $q->where('type', $this->typeFilter))
            ->when($this->categoryFilter, fn ($q) => $q->where('category', $this->categoryFilter))
            ->when($this->dateFrom, fn ($q) => $q->where('report_date', '>=', $this->dateFrom))
            ->when($this->dateTo, fn ($q) => $q->where('report_date', '<=', $this->dateTo))
            ->count();

        return [
            'types' => FinancialReport::types(),
            'categories' => FinancialReport::categories(),
            'reportCount' => $reportCount,
        ];
    }
}; ?>

<div>
    <flux:modal name="export-modal" class="md:w-96">
        <div class="space-y-6">
            <div>
                <flux:heading size="lg">{{ __('Export Laporan') }}</flux:heading>
                <flux:subheading>{{ __('Export data laporan keuangan Anda') }}</flux:subheading>
            </div>

            @if ($errorMessage)
                <flux:callout variant="danger" icon="exclamation-triangle">
                    {{ $errorMessage }}
                </flux:callout>
            @endif

            <form wire:submit="export" class="space-y-4">
                {{-- Format Selection --}}
                <div>
                    <flux:label>{{ __('Format') }}</flux:label>
                    <flux:radio.group wire:model="format" class="mt-2">
                        <flux:radio value="json" label="JSON" />
                        <flux:radio value="sql" label="SQL" />
                    </flux:radio.group>
                </div>

                {{-- Include Photos --}}
                <flux:switch wire:model.live="includePhotos" label="{{ __('Sertakan foto (ZIP)') }}" description="{{ __('File akan dikompres dalam format ZIP jika foto disertakan') }}" />

                {{-- Filters --}}
                <flux:separator />

                <flux:heading size="sm">{{ __('Filter (Opsional)') }}</flux:heading>

                <flux:select wire:model.live="typeFilter" :label="__('Tipe')">
                    <flux:select.option value="">{{ __('Semua Tipe') }}</flux:select.option>
                    @foreach ($types as $value => $label)
                        <flux:select.option value="{{ $value }}">{{ $label }}</flux:select.option>
                    @endforeach
                </flux:select>

                <flux:select wire:model.live="categoryFilter" :label="__('Kategori')">
                    <flux:select.option value="">{{ __('Semua Kategori') }}</flux:select.option>
                    @foreach ($categories as $value => $label)
                        <flux:select.option value="{{ $value }}">{{ $label }}</flux:select.option>
                    @endforeach
                </flux:select>

                <div class="grid grid-cols-2 gap-4">
                    <flux:input wire:model.live="dateFrom" type="date" :label="__('Dari Tanggal')" />
                    <flux:input wire:model.live="dateTo" type="date" :label="__('Sampai Tanggal')" />
                </div>

                {{-- Preview Count --}}
                <flux:callout variant="info" icon="information-circle">
                    {{ __(':count laporan akan diekspor', ['count' => $reportCount]) }}
                </flux:callout>

                {{-- Actions --}}
                <div class="flex justify-end gap-2 pt-4">
                    <flux:modal.close>
                        <flux:button variant="ghost">{{ __('Batal') }}</flux:button>
                    </flux:modal.close>
                    <flux:button
                        type="submit"
                        variant="primary"
                        icon="arrow-down-tray"
                        :disabled="$reportCount === 0 || $isExporting"
                    >
                        <span wire:loading.remove wire:target="export">{{ __('Export') }}</span>
                        <span wire:loading wire:target="export">{{ __('Mengexport...') }}</span>
                    </flux:button>
                </div>
            </form>
        </div>
    </flux:modal>
</div>
