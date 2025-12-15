<?php

declare(strict_types=1);

use App\Models\ShareLink;
use Livewire\Volt\Component;

new class extends Component {
    public string $name = '';
    public string $password = '';
    public string $password_confirmation = '';
    public bool $has_password = false;
    public bool $has_expiry = false;
    public ?string $expires_at = null;

    public function rules(): array
    {
        return [
            'name' => ['required', 'string', 'max:255'],
            'password' => $this->has_password ? ['required', 'string', 'min:4', 'confirmed'] : ['nullable'],
            'expires_at' => $this->has_expiry ? ['required', 'date', 'after:today'] : ['nullable'],
        ];
    }

    public function save(): void
    {
        $this->validate();

        $shareLink = auth()->user()->shareLinks()->create([
            'name' => $this->name,
            'password' => $this->has_password ? $this->password : null,
            'expires_at' => $this->has_expiry ? $this->expires_at : null,
            'is_active' => true,
        ]);

        session()->flash('status', 'Link berbagi berhasil dibuat!');
        $this->redirect(route('share-links.index'), navigate: true);
    }
}; ?>

<section class="w-full">
    <x-slot name="title">{{ __('Buat Share Link') }}</x-slot>

    <div class="flex h-full w-full flex-1 flex-col gap-6">
        <div class="max-w-2xl">
            <flux:heading size="xl">{{ __('Buat Share Link Baru') }}</flux:heading>
            <flux:subheading>{{ __('Buat link untuk berbagi laporan keuangan Anda dengan orang lain.') }}</flux:subheading>
        </div>

        <form wire:submit="save" class="max-w-2xl space-y-6">
            {{-- Name --}}
            <flux:field>
                <flux:label>{{ __('Nama Link') }}</flux:label>
                <flux:input
                    wire:model="name"
                    placeholder="{{ __('Contoh: Laporan Bulanan, Share ke Partner') }}"
                />
                <flux:error name="name" />
                <flux:description>{{ __('Nama untuk mengidentifikasi link ini') }}</flux:description>
            </flux:field>

            {{-- Password Protection --}}
            <div class="space-y-4">
                <flux:field>
                    <flux:checkbox wire:model.live="has_password" label="{{ __('Lindungi dengan password') }}" />
                    <flux:description>{{ __('Pengunjung harus memasukkan password untuk melihat laporan') }}</flux:description>
                </flux:field>

                @if ($has_password)
                    <div class="grid gap-4 sm:grid-cols-2 pl-6 border-l-2 border-zinc-200 dark:border-zinc-700">
                        <flux:field>
                            <flux:label>{{ __('Password') }}</flux:label>
                            <flux:input
                                wire:model="password"
                                type="password"
                                placeholder="{{ __('Minimal 4 karakter') }}"
                            />
                            <flux:error name="password" />
                        </flux:field>

                        <flux:field>
                            <flux:label>{{ __('Konfirmasi Password') }}</flux:label>
                            <flux:input
                                wire:model="password_confirmation"
                                type="password"
                                placeholder="{{ __('Ulangi password') }}"
                            />
                        </flux:field>
                    </div>
                @endif
            </div>

            {{-- Expiry --}}
            <div class="space-y-4">
                <flux:field>
                    <flux:checkbox wire:model.live="has_expiry" label="{{ __('Set tanggal kedaluwarsa') }}" />
                    <flux:description>{{ __('Link akan otomatis tidak aktif setelah tanggal ini') }}</flux:description>
                </flux:field>

                @if ($has_expiry)
                    <div class="pl-6 border-l-2 border-zinc-200 dark:border-zinc-700">
                        <flux:field>
                            <flux:label>{{ __('Kedaluwarsa pada') }}</flux:label>
                            <flux:input
                                wire:model="expires_at"
                                type="date"
                                :min="now()->addDay()->format('Y-m-d')"
                            />
                            <flux:error name="expires_at" />
                        </flux:field>
                    </div>
                @endif
            </div>

            {{-- Info Box --}}
            <flux:callout icon="information-circle" color="blue">
                <flux:callout.heading>{{ __('Informasi') }}</flux:callout.heading>
                <flux:callout.text>
                    {{ __('Link ini akan menampilkan semua laporan keuangan Anda dalam mode read-only. Pengunjung tidak dapat mengedit atau menghapus data.') }}
                </flux:callout.text>
            </flux:callout>

            {{-- Actions --}}
            <div class="flex items-center gap-4">
                <flux:button type="submit" variant="primary">
                    {{ __('Buat Link') }}
                </flux:button>
                <flux:button href="{{ route('share-links.index') }}" variant="ghost">
                    {{ __('Batal') }}
                </flux:button>
            </div>
        </form>
    </div>
</section>
