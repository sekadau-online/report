<?php

declare(strict_types=1);

use App\Models\SiteSetting;
use App\Models\User;
use App\Services\SiteSettingService;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\Storage;
use Livewire\Volt\Volt;

uses(RefreshDatabase::class);

beforeEach(function () {
    Storage::fake('public');
    Cache::flush();
});

describe('SiteSetting Model', function () {
    it('can get a setting value with default', function () {
        $value = SiteSetting::getValue('site_name');

        expect($value)->toBe('LKEU RAPI');
    });

    it('can set a setting value', function () {
        SiteSetting::setValue('site_name', 'My App');

        expect(SiteSetting::getValue('site_name'))->toBe('My App');
        $this->assertDatabaseHas('site_settings', [
            'key' => 'site_name',
            'value' => 'My App',
        ]);
    });

    it('can get all settings', function () {
        $settings = SiteSetting::getAllSettings();

        expect($settings)->toBeArray()
            ->and($settings)->toHaveKey('site_name')
            ->and($settings)->toHaveKey('site_title')
            ->and($settings)->toHaveKey('favicon_ico');
    });

    it('clears cache when setting is updated', function () {
        SiteSetting::setValue('site_name', 'Initial');
        $initial = SiteSetting::getValue('site_name');

        SiteSetting::setValue('site_name', 'Updated');
        $updated = SiteSetting::getValue('site_name');

        expect($initial)->toBe('Initial')
            ->and($updated)->toBe('Updated');
    });

    it('can get settings by group', function () {
        $brandingSettings = SiteSetting::getByGroup('branding');

        expect($brandingSettings)->toHaveCount(5);
        expect($brandingSettings->pluck('key')->toArray())
            ->toContain('site_name', 'site_title', 'favicon_ico', 'favicon_svg', 'logo');
    });

    it('casts boolean values correctly', function () {
        SiteSetting::setValue('welcome_enabled', true);
        expect(SiteSetting::getValue('welcome_enabled'))->toBeTrue();

        SiteSetting::setValue('welcome_enabled', false);
        expect(SiteSetting::getValue('welcome_enabled'))->toBeFalse();
    });

    it('initializes default settings', function () {
        SiteSetting::initializeDefaults();

        $count = SiteSetting::count();
        expect($count)->toBe(count(SiteSetting::$defaults));
    });
});

describe('SiteSettingService', function () {
    it('can get a setting', function () {
        $service = new SiteSettingService;

        expect($service->get('site_name'))->toBe('LKEU RAPI');
    });

    it('can set a setting', function () {
        $service = new SiteSettingService;
        $service->set('site_name', 'Test App');

        expect($service->get('site_name'))->toBe('Test App');
    });

    it('can get all settings', function () {
        $service = new SiteSettingService;

        expect($service->all())->toBeArray()
            ->and($service->all())->toHaveKey('site_name');
    });

    it('can upload an image', function () {
        $service = new SiteSettingService;
        $file = UploadedFile::fake()->image('logo.png');

        $url = $service->uploadImage('logo', $file);

        expect($url)->toStartWith('/storage/site-settings/')
            ->and(Storage::disk('public')->exists(str_replace('/storage/', '', $url)))->toBeTrue();
    });

    it('can delete an image', function () {
        $service = new SiteSettingService;
        $file = UploadedFile::fake()->image('logo.png');

        $url = $service->uploadImage('logo', $file);
        $path = str_replace('/storage/', '', $url);

        expect(Storage::disk('public')->exists($path))->toBeTrue();

        $service->deleteImage('logo');

        expect(Storage::disk('public')->exists($path))->toBeFalse();
    });

    it('can update multiple settings at once', function () {
        $service = new SiteSettingService;
        $service->updateMany([
            'site_name' => 'Bulk Test',
            'site_title' => 'Bulk Title',
        ]);

        expect($service->get('site_name'))->toBe('Bulk Test')
            ->and($service->get('site_title'))->toBe('Bulk Title');
    });

    it('returns available groups', function () {
        $service = new SiteSettingService;
        $groups = $service->getGroups();

        expect($groups)->toHaveKey('branding')
            ->and($groups)->toHaveKey('links')
            ->and($groups)->toHaveKey('welcome');
    });
});

describe('Site Settings Helper Functions', function () {
    it('can use site_setting helper', function () {
        expect(site_setting('site_name'))->toBe('LKEU RAPI');
        expect(site_setting('nonexistent', 'default'))->toBe('default');
    });

    it('can use site_settings helper', function () {
        $settings = site_settings();

        expect($settings)->toBeArray()
            ->and($settings)->toHaveKey('site_name');
    });
});

describe('Site Settings Page', function () {
    it('requires authentication', function () {
        $this->get(route('site-settings.edit'))
            ->assertRedirect(route('login'));
    });

    it('can be accessed by authenticated users', function () {
        $user = User::factory()->create();

        $this->actingAs($user)
            ->get(route('site-settings.edit'))
            ->assertOk();
    });

    it('can save branding settings', function () {
        $user = User::factory()->create();

        Volt::actingAs($user)
            ->test('settings.site')
            ->set('site_name', 'New Site Name')
            ->set('site_title', 'New Title')
            ->call('saveBranding')
            ->assertHasNoErrors();

        expect(SiteSetting::getValue('site_name'))->toBe('New Site Name')
            ->and(SiteSetting::getValue('site_title'))->toBe('New Title');
    });

    it('can save links settings', function () {
        $user = User::factory()->create();

        Volt::actingAs($user)
            ->test('settings.site')
            ->set('repository_url', 'https://github.com/test/repo')
            ->set('documentation_url', 'https://docs.example.com')
            ->call('saveLinks')
            ->assertHasNoErrors();

        expect(SiteSetting::getValue('repository_url'))->toBe('https://github.com/test/repo')
            ->and(SiteSetting::getValue('documentation_url'))->toBe('https://docs.example.com');
    });

    it('can save welcome page settings', function () {
        $user = User::factory()->create();

        Volt::actingAs($user)
            ->test('settings.site')
            ->set('welcome_enabled', false)
            ->set('welcome_title', 'Welcome!')
            ->set('welcome_description', 'Test description')
            ->set('welcome_primary_link_text', 'Docs')
            ->set('welcome_primary_link_url', 'https://docs.test.com')
            ->set('welcome_secondary_link_text', 'Videos')
            ->set('welcome_secondary_link_url', 'https://videos.test.com')
            ->set('welcome_cta_text', 'Get Started')
            ->set('welcome_cta_url', 'https://start.test.com')
            ->call('saveWelcome')
            ->assertHasNoErrors();

        expect(SiteSetting::getValue('welcome_enabled'))->toBeFalse()
            ->and(SiteSetting::getValue('welcome_title'))->toBe('Welcome!');
    });

    it('validates branding settings', function () {
        $user = User::factory()->create();

        Volt::actingAs($user)
            ->test('settings.site')
            ->set('site_name', '')
            ->set('site_title', '')
            ->call('saveBranding')
            ->assertHasErrors(['site_name', 'site_title']);
    });

    it('validates links settings', function () {
        $user = User::factory()->create();

        Volt::actingAs($user)
            ->test('settings.site')
            ->set('activeGroup', 'links')
            ->set('repository_url', 'not-a-url')
            ->set('documentation_url', 'also-not-a-url')
            ->call('saveLinks')
            ->assertHasErrors(['repository_url', 'documentation_url']);
    });

    it('can switch between groups', function () {
        $user = User::factory()->create();

        Volt::actingAs($user)
            ->test('settings.site')
            ->assertSet('activeGroup', 'branding')
            ->call('setActiveGroup', 'links')
            ->assertSet('activeGroup', 'links')
            ->call('setActiveGroup', 'welcome')
            ->assertSet('activeGroup', 'welcome');
    });

    it('can upload logo image', function () {
        $user = User::factory()->create();
        $file = UploadedFile::fake()->image('logo.png');

        Volt::actingAs($user)
            ->test('settings.site')
            ->set('site_name', 'Test')
            ->set('site_title', 'Test')
            ->set('logo', $file)
            ->call('saveBranding')
            ->assertHasNoErrors();

        $logo = SiteSetting::getValue('logo');
        expect($logo)->toStartWith('/storage/site-settings/');
    });

    it('can delete logo image', function () {
        $user = User::factory()->create();
        $service = new SiteSettingService;
        $file = UploadedFile::fake()->image('logo.png');
        $service->uploadImage('logo', $file);

        Volt::actingAs($user)
            ->test('settings.site')
            ->call('deleteImage', 'logo')
            ->assertHasNoErrors();

        expect(SiteSetting::getValue('logo'))->toBeNull();
    });
});

describe('Welcome Page', function () {
    it('shows welcome page when enabled', function () {
        SiteSetting::setValue('welcome_enabled', true);

        $this->get('/')
            ->assertOk()
            ->assertSee(site_setting('welcome_title'));
    });

    it('redirects to login when welcome page is disabled', function () {
        SiteSetting::setValue('welcome_enabled', false);

        $this->get('/')
            ->assertRedirect(route('login'));
    });

    it('displays custom content from settings', function () {
        SiteSetting::setValue('welcome_enabled', true);
        SiteSetting::setValue('welcome_title', 'Custom Welcome Title');
        SiteSetting::setValue('welcome_description', 'Custom description text');
        SiteSetting::setValue('site_name', 'My Custom App');

        $this->get('/')
            ->assertOk()
            ->assertSee('Custom Welcome Title')
            ->assertSee('Custom description text')
            ->assertSee('My Custom App');
    });
});

describe('Dynamic Layout Components', function () {
    it('uses site settings for default page title', function () {
        SiteSetting::setValue('site_title', 'Custom App Title');

        // Welcome page uses the site_title setting directly
        $this->get('/')
            ->assertOk()
            ->assertSee('Custom App Title', false);
    });

    it('uses site settings for sidebar branding', function () {
        $user = User::factory()->create();
        SiteSetting::setValue('site_name', 'My Custom App');

        $this->actingAs($user)
            ->get(route('dashboard'))
            ->assertOk()
            ->assertSee('My Custom App');
    });

    it('uses site settings for external links', function () {
        $user = User::factory()->create();
        SiteSetting::setValue('repository_url', 'https://github.com/custom/repo');
        SiteSetting::setValue('documentation_url', 'https://custom-docs.example.com');

        $this->actingAs($user)
            ->get(route('dashboard'))
            ->assertOk()
            ->assertSee('https://github.com/custom/repo', false)
            ->assertSee('https://custom-docs.example.com', false);
    });
});
