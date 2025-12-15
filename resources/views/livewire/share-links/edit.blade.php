<?php

declare(strict_types=1);

use App\Models\ShareLink;
use Livewire\Volt\Component;

new class extends Component {
    public ShareLink $shareLink;

    public string $name = '';
    public string $password = '';
    public string $password_confirmation = '';
    public bool $has_password = false;
    public bool $change_password = false;
    public bool $has_expiry = false;
    public ?string $expires_at = null;
    public bool $is_active = true;

    public function mount(ShareLink $shareLink): void
    {
        $this->authorize('update', $shareLink);

        $this->shareLink = $shareLink;
        $this->name = $shareLink->name;
        $this->has_password = $shareLink->requiresPassword();
        $this->has_expiry = ! is_null($shareLink->expires_at);
        $this->expires_at = $shareLink->expires_at?->format('Y-m-d');
        $this->is_active = $shareLink->is_active;
    }

    public function rules(): array
    {
        $rules = [
            'name' => ['required', 'string', 'max:255'],
            'is_active' => ['boolean'],
        ];

        if ($this->has_password && $this->change_password) {
            $rules['password'] = ['required', 'string', 'min:4', 'confirmed'];
        }

        if ($this->has_expiry) {
            $rules['expires_at'] = ['required', 'date'];
        }

        return $rules;
    }

    public function save(): void
    {
        $this->validate();

        $data = [
            'name' => $this->name,
            'is_active' => $this->is_active,
            'expires_at' => $this->has_expiry ? $this->expires_at : null,
        ];

        // Handle password
        if (! $this->has_password) {
            $data['password'] = null;
        } elseif ($this->change_password && $this->password) {
            $data['password'] = $this->password;
        }

        $this->shareLink->update($data);

        session()->flash('status', 'Link berbagi berhasil diperbarui!');
        $this->redirect(route('share-links.index'), navigate: true);
    }

    public function regenerateToken(): void
    {
        $this->shareLink->update([
            'token' => ShareLink::generateUniqueToken(),
        ]);

        session()->flash('status', 'Token berhasil di-regenerate!');
    }

    public function toggleActive(): void
    {
        $this->authorize('update', $this->shareLink);

        $this->shareLink->update(['is_active' => ! $this->shareLink->is_active]);
        $this->is_active = $this->shareLink->is_active;
    }

    public function delete(): void
    {
        $this->authorize('delete', $this->shareLink);

        $this->shareLink->delete();

        session()->flash('status', 'Link berbagi berhasil dihapus!');
        $this->redirect(route('share-links.index'), navigate: true);
    }
}; ?>

<section class="w-full">
    <x-slot name="title">{{ __('Edit Share Link') }}</x-slot>

    <div class="flex h-full w-full flex-1 flex-col gap-6">
        <div class="max-w-2xl">
            <flux:heading size="xl">{{ __('Edit Share Link') }}</flux:heading>
            <flux:subheading>{{ __('Ubah pengaturan link berbagi Anda.') }}</flux:subheading>
        </div>

        <form wire:submit="save" class="max-w-2xl space-y-6">
            {{-- Current Link --}}
            <flux:field>
                <flux:label>{{ __('Link Saat Ini') }}</flux:label>
                <div class="flex items-center gap-2">
                    <flux:input
                        readonly
                        :value="$shareLink->getShareUrl()"
                        class="font-mono text-sm"
                    />
                    <flux:button
                        type="button"
                        variant="ghost"
                        icon="arrow-path"
                        wire:click="regenerateToken"
                        wire:confirm="{{ __('Regenerate token akan membuat link lama tidak berfungsi. Lanjutkan?') }}"
                    />
                </div>
                <flux:description>{{ __('Klik icon refresh untuk membuat token baru') }}</flux:description>
            </flux:field>

            {{-- Name --}}
            <flux:field>
                <flux:label>{{ __('Nama Link') }}</flux:label>
                <flux:input
                    wire:model="name"
                    placeholder="{{ __('Contoh: Laporan Bulanan, Share ke Partner') }}"
                />
                <flux:error name="name" />
            </flux:field>

            {{-- Active Status --}}
            <flux:field>
                <flux:checkbox wire:model="is_active" label="{{ __('Link Aktif') }}" />
                <flux:description>{{ __('Nonaktifkan untuk mencegah akses sementara tanpa menghapus link') }}</flux:description>
            </flux:field>

            {{-- Password Protection --}}
            <div class="space-y-4">
                <flux:field>
                    <flux:checkbox wire:model.live="has_password" label="{{ __('Lindungi dengan password') }}" />
                    <flux:description>{{ __('Pengunjung harus memasukkan password untuk melihat laporan') }}</flux:description>
                </flux:field>

                @if ($has_password)
                    <div class="pl-6 border-l-2 border-zinc-200 dark:border-zinc-700 space-y-4">
                        @if ($shareLink->requiresPassword())
                            <flux:field>
                                <flux:checkbox wire:model.live="change_password" label="{{ __('Ubah password') }}" />
                            </flux:field>
                        @endif

                        @if ($change_password || ! $shareLink->requiresPassword())
                            <div class="grid gap-4 sm:grid-cols-2">
                                <flux:field>
                                    <flux:label>{{ __('Password Baru') }}</flux:label>
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
                            />
                            <flux:error name="expires_at" />
                        </flux:field>
                    </div>
                @endif
            </div>

            {{-- Statistics --}}
            <div class="bg-zinc-50 dark:bg-zinc-900 rounded-lg p-4">
                <h3 class="font-medium text-zinc-900 dark:text-white mb-3">{{ __('Statistik') }}</h3>
                <div class="grid grid-cols-2 sm:grid-cols-4 gap-4 text-sm">
                    <div>
                        <div class="text-zinc-500 dark:text-zinc-400">{{ __('Total Views') }}</div>
                        <div class="font-semibold text-zinc-900 dark:text-white">{{ number_format($shareLink->view_count) }}</div>
                    </div>
                    <div>
                        <div class="text-zinc-500 dark:text-zinc-400">{{ __('Terakhir Dilihat') }}</div>
                        <div class="font-semibold text-zinc-900 dark:text-white">
                            {{ $shareLink->last_viewed_at?->diffForHumans() ?? '-' }}
                        </div>
                    </div>
                    <div>
                        <div class="text-zinc-500 dark:text-zinc-400">{{ __('Dibuat') }}</div>
                        <div class="font-semibold text-zinc-900 dark:text-white">{{ $shareLink->created_at->format('d M Y') }}</div>
                    </div>
                    <div>
                        <div class="text-zinc-500 dark:text-zinc-400">{{ __('Status') }}</div>
                        <div class="font-semibold">
                            @if ($shareLink->isExpired())
                                <span class="text-red-600">{{ __('Kedaluwarsa') }}</span>
                            @elseif ($shareLink->is_active)
                                <span class="text-green-600">{{ __('Aktif') }}</span>
                            @else
                                <span class="text-zinc-500">{{ __('Nonaktif') }}</span>
                            @endif
                        </div>
                    </div>
                </div>
            </div>

            {{-- Actions --}}
            <div class="flex items-center gap-4">
                <flux:button type="submit" variant="primary">
                    {{ __('Simpan Perubahan') }}
                </flux:button>
                <flux:button href="{{ route('share-links.index') }}" variant="ghost">
                    {{ __('Batal') }}
                </flux:button>
            </div>
        </form>
    </div>
</section>
