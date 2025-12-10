<?php

use App\Models\SiteSetting;
use App\Services\SiteSettingService;
use Illuminate\Support\Facades\Auth;
use Livewire\Volt\Component;
use Livewire\WithFileUploads;

new class extends Component {
    use WithFileUploads;

    public string $activeGroup = 'branding';

    // Branding
    public string $site_name = '';
    public string $site_title = '';
    public $favicon_ico = null;
    public $favicon_svg = null;
    public $logo = null;

    // Links
    public string $repository_url = '';
    public string $documentation_url = '';

    // Welcome
    public bool $welcome_enabled = true;
    public string $welcome_title = '';
    public string $welcome_description = '';
    public string $welcome_primary_link_text = '';
    public string $welcome_primary_link_url = '';
    public string $welcome_secondary_link_text = '';
    public string $welcome_secondary_link_url = '';
    public string $welcome_cta_text = '';
    public string $welcome_cta_url = '';

    // Current values for display
    public array $currentValues = [];

    public function mount(): void
    {
        $this->loadSettings();
    }

    protected function loadSettings(): void
    {
        $settings = SiteSetting::getAllSettings();

        $this->site_name = $settings['site_name'] ?? '';
        $this->site_title = $settings['site_title'] ?? '';
        $this->repository_url = $settings['repository_url'] ?? '';
        $this->documentation_url = $settings['documentation_url'] ?? '';
        $this->welcome_enabled = (bool) ($settings['welcome_enabled'] ?? true);
        $this->welcome_title = $settings['welcome_title'] ?? '';
        $this->welcome_description = $settings['welcome_description'] ?? '';
        $this->welcome_primary_link_text = $settings['welcome_primary_link_text'] ?? '';
        $this->welcome_primary_link_url = $settings['welcome_primary_link_url'] ?? '';
        $this->welcome_secondary_link_text = $settings['welcome_secondary_link_text'] ?? '';
        $this->welcome_secondary_link_url = $settings['welcome_secondary_link_url'] ?? '';
        $this->welcome_cta_text = $settings['welcome_cta_text'] ?? '';
        $this->welcome_cta_url = $settings['welcome_cta_url'] ?? '';

        $this->currentValues = [
            'favicon_ico' => $settings['favicon_ico'] ?? null,
            'favicon_svg' => $settings['favicon_svg'] ?? null,
            'logo' => $settings['logo'] ?? null,
        ];
    }

    public function setActiveGroup(string $group): void
    {
        $this->activeGroup = $group;
    }

    public function saveBranding(): void
    {
        $this->validate([
            'site_name' => ['required', 'string', 'max:255'],
            'site_title' => ['required', 'string', 'max:255'],
            'favicon_ico' => ['nullable', 'file', 'max:512'],
            'favicon_svg' => ['nullable', 'file', 'max:512'],
            'logo' => ['nullable', 'image', 'max:2048'],
        ]);

        $service = app(SiteSettingService::class);

        $service->set('site_name', $this->site_name);
        $service->set('site_title', $this->site_title);

        if ($this->favicon_ico) {
            $service->uploadImage('favicon_ico', $this->favicon_ico);
            $this->favicon_ico = null;
        }

        if ($this->favicon_svg) {
            $service->uploadImage('favicon_svg', $this->favicon_svg);
            $this->favicon_svg = null;
        }

        if ($this->logo) {
            $service->uploadImage('logo', $this->logo);
            $this->logo = null;
        }

        $this->loadSettings();
        $this->dispatch('settings-saved');
        session()->flash('status', 'branding-updated');
    }

    public function saveLinks(): void
    {
        $this->validate([
            'repository_url' => ['required', 'url', 'max:500'],
            'documentation_url' => ['required', 'url', 'max:500'],
        ]);

        $service = app(SiteSettingService::class);
        $service->set('repository_url', $this->repository_url);
        $service->set('documentation_url', $this->documentation_url);

        $this->dispatch('settings-saved');
        session()->flash('status', 'links-updated');
    }

    public function saveWelcome(): void
    {
        $this->validate([
            'welcome_enabled' => ['boolean'],
            'welcome_title' => ['required', 'string', 'max:255'],
            'welcome_description' => ['required', 'string', 'max:1000'],
            'welcome_primary_link_text' => ['required', 'string', 'max:100'],
            'welcome_primary_link_url' => ['required', 'url', 'max:500'],
            'welcome_secondary_link_text' => ['required', 'string', 'max:100'],
            'welcome_secondary_link_url' => ['required', 'url', 'max:500'],
            'welcome_cta_text' => ['required', 'string', 'max:100'],
            'welcome_cta_url' => ['required', 'url', 'max:500'],
        ]);

        $service = app(SiteSettingService::class);
        $service->set('welcome_enabled', $this->welcome_enabled);
        $service->set('welcome_title', $this->welcome_title);
        $service->set('welcome_description', $this->welcome_description);
        $service->set('welcome_primary_link_text', $this->welcome_primary_link_text);
        $service->set('welcome_primary_link_url', $this->welcome_primary_link_url);
        $service->set('welcome_secondary_link_text', $this->welcome_secondary_link_text);
        $service->set('welcome_secondary_link_url', $this->welcome_secondary_link_url);
        $service->set('welcome_cta_text', $this->welcome_cta_text);
        $service->set('welcome_cta_url', $this->welcome_cta_url);

        $this->dispatch('settings-saved');
        session()->flash('status', 'welcome-updated');
    }

    public function deleteImage(string $key): void
    {
        $service = app(SiteSettingService::class);
        $service->deleteImage($key);
        $this->loadSettings();
        $this->dispatch('settings-saved');
    }
}; ?>

<section class="w-full">
    <div class="relative mb-6 w-full">
        <flux:heading size="xl" level="1">{{ __('Site Settings') }}</flux:heading>
        <flux:subheading size="lg" class="mb-6">{{ __('Manage your site branding, links, and welcome page') }}</flux:subheading>
        <flux:separator variant="subtle" />
    </div>

    <div class="flex items-start max-md:flex-col">
        <div class="me-10 w-full pb-4 md:w-[220px]">
            <flux:navlist>
                <flux:navlist.item
                    wire:click="setActiveGroup('branding')"
                    :current="$activeGroup === 'branding'"
                    class="cursor-pointer"
                >
                    {{ __('Branding') }}
                </flux:navlist.item>
                <flux:navlist.item
                    wire:click="setActiveGroup('links')"
                    :current="$activeGroup === 'links'"
                    class="cursor-pointer"
                >
                    {{ __('External Links') }}
                </flux:navlist.item>
                <flux:navlist.item
                    wire:click="setActiveGroup('welcome')"
                    :current="$activeGroup === 'welcome'"
                    class="cursor-pointer"
                >
                    {{ __('Welcome Page') }}
                </flux:navlist.item>
            </flux:navlist>
        </div>

        <flux:separator class="md:hidden" />

        <div class="flex-1 self-stretch max-md:pt-6">
            {{-- Branding Settings --}}
            @if ($activeGroup === 'branding')
                <flux:heading>{{ __('Branding') }}</flux:heading>
                <flux:subheading>{{ __('Customize your site name, title, favicon, and logo') }}</flux:subheading>

                <form wire:submit="saveBranding" class="my-6 w-full max-w-lg space-y-6">
                    <flux:input
                        wire:model="site_name"
                        :label="__('Site Name')"
                        :description="__('Displayed in sidebar and navigation')"
                        type="text"
                        required
                    />

                    <flux:input
                        wire:model="site_title"
                        :label="__('Site Title')"
                        :description="__('Default browser tab title')"
                        type="text"
                        required
                    />

                    <div>
                        <flux:field>
                            <flux:label>{{ __('Favicon ICO') }}</flux:label>
                            <flux:description>{{ __('Icon format .ico for older browsers') }}</flux:description>
                            @if ($currentValues['favicon_ico'])
                                <div class="mb-2 flex items-center gap-2">
                                    <img src="{{ $currentValues['favicon_ico'] }}" alt="Current favicon" class="size-8 rounded border">
                                    <flux:text class="text-sm text-zinc-500">{{ __('Current favicon') }}</flux:text>
                                </div>
                            @endif
                            <flux:input type="file" wire:model="favicon_ico" accept=".ico" />
                            <flux:error name="favicon_ico" />
                        </flux:field>
                    </div>

                    <div>
                        <flux:field>
                            <flux:label>{{ __('Favicon SVG') }}</flux:label>
                            <flux:description>{{ __('Icon format .svg for modern browsers') }}</flux:description>
                            @if ($currentValues['favicon_svg'])
                                <div class="mb-2 flex items-center gap-2">
                                    <img src="{{ $currentValues['favicon_svg'] }}" alt="Current favicon" class="size-8 rounded border">
                                    <flux:text class="text-sm text-zinc-500">{{ __('Current favicon') }}</flux:text>
                                </div>
                            @endif
                            <flux:input type="file" wire:model="favicon_svg" accept=".svg" />
                            <flux:error name="favicon_svg" />
                        </flux:field>
                    </div>

                    <div>
                        <flux:field>
                            <flux:label>{{ __('Logo') }}</flux:label>
                            <flux:description>{{ __('Application logo (optional)') }}</flux:description>
                            @if ($currentValues['logo'])
                                <div class="mb-2 flex items-center gap-2">
                                    <img src="{{ $currentValues['logo'] }}" alt="Current logo" class="h-10 max-w-32 rounded border object-contain">
                                    <flux:button variant="ghost" size="xs" wire:click="deleteImage('logo')" type="button">
                                        {{ __('Remove') }}
                                    </flux:button>
                                </div>
                            @endif
                            <flux:input type="file" wire:model="logo" accept="image/*" />
                            <flux:error name="logo" />
                        </flux:field>
                    </div>

                    <div class="flex items-center gap-4">
                        <flux:button variant="primary" type="submit" wire:loading.attr="disabled">
                            <span wire:loading.remove wire:target="saveBranding">{{ __('Save') }}</span>
                            <span wire:loading wire:target="saveBranding">{{ __('Saving...') }}</span>
                        </flux:button>

                        @if (session('status') === 'branding-updated')
                            <x-action-message on="settings-saved">{{ __('Saved.') }}</x-action-message>
                        @endif
                    </div>
                </form>
            @endif

            {{-- Links Settings --}}
            @if ($activeGroup === 'links')
                <flux:heading>{{ __('External Links') }}</flux:heading>
                <flux:subheading>{{ __('Configure repository and documentation links') }}</flux:subheading>

                <form wire:submit="saveLinks" class="my-6 w-full max-w-lg space-y-6">
                    <flux:input
                        wire:model="repository_url"
                        :label="__('Repository URL')"
                        :description="__('Link to your GitHub or other repository')"
                        type="url"
                        required
                    />

                    <flux:input
                        wire:model="documentation_url"
                        :label="__('Documentation URL')"
                        :description="__('Link to your documentation')"
                        type="url"
                        required
                    />

                    <div class="flex items-center gap-4">
                        <flux:button variant="primary" type="submit" wire:loading.attr="disabled">
                            <span wire:loading.remove wire:target="saveLinks">{{ __('Save') }}</span>
                            <span wire:loading wire:target="saveLinks">{{ __('Saving...') }}</span>
                        </flux:button>

                        @if (session('status') === 'links-updated')
                            <x-action-message on="settings-saved">{{ __('Saved.') }}</x-action-message>
                        @endif
                    </div>
                </form>
            @endif

            {{-- Welcome Page Settings --}}
            @if ($activeGroup === 'welcome')
                <flux:heading>{{ __('Welcome Page') }}</flux:heading>
                <flux:subheading>{{ __('Customize the public welcome page content') }}</flux:subheading>

                <form wire:submit="saveWelcome" class="my-6 w-full max-w-lg space-y-6">
                    <flux:switch
                        wire:model="welcome_enabled"
                        :label="__('Enable Welcome Page')"
                        :description="__('When disabled, visitors will be redirected to login')"
                    />

                    <flux:separator />

                    <flux:input
                        wire:model="welcome_title"
                        :label="__('Title')"
                        type="text"
                        required
                    />

                    <flux:textarea
                        wire:model="welcome_description"
                        :label="__('Description')"
                        rows="3"
                        required
                    />

                    <flux:separator />

                    <flux:heading size="sm">{{ __('Primary Link') }}</flux:heading>
                    <div class="grid gap-4 sm:grid-cols-2">
                        <flux:input wire:model="welcome_primary_link_text" :label="__('Text')" type="text" required />
                        <flux:input wire:model="welcome_primary_link_url" :label="__('URL')" type="url" required />
                    </div>

                    <flux:heading size="sm">{{ __('Secondary Link') }}</flux:heading>
                    <div class="grid gap-4 sm:grid-cols-2">
                        <flux:input wire:model="welcome_secondary_link_text" :label="__('Text')" type="text" required />
                        <flux:input wire:model="welcome_secondary_link_url" :label="__('URL')" type="url" required />
                    </div>

                    <flux:heading size="sm">{{ __('CTA Button') }}</flux:heading>
                    <div class="grid gap-4 sm:grid-cols-2">
                        <flux:input wire:model="welcome_cta_text" :label="__('Text')" type="text" required />
                        <flux:input wire:model="welcome_cta_url" :label="__('URL')" type="url" required />
                    </div>

                    <div class="flex items-center gap-4">
                        <flux:button variant="primary" type="submit" wire:loading.attr="disabled">
                            <span wire:loading.remove wire:target="saveWelcome">{{ __('Save') }}</span>
                            <span wire:loading wire:target="saveWelcome">{{ __('Saving...') }}</span>
                        </flux:button>

                        @if (session('status') === 'welcome-updated')
                            <x-action-message on="settings-saved">{{ __('Saved.') }}</x-action-message>
                        @endif
                    </div>
                </form>
            @endif
        </div>
    </div>
</section>
