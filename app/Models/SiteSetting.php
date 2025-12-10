<?php

declare(strict_types=1);

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Facades\Cache;

class SiteSetting extends Model
{
    use HasFactory;

    protected $fillable = [
        'key',
        'value',
        'type',
        'group',
        'label',
        'description',
    ];

    /**
     * Cache key prefix for settings.
     */
    public const CACHE_PREFIX = 'site_setting_';

    public const CACHE_ALL_KEY = 'site_settings_all';

    /**
     * Default settings configuration.
     *
     * @var array<string, array{value: string|null, type: string, group: string, label: string, description: string|null}>
     */
    public static array $defaults = [
        // Branding
        'site_name' => [
            'value' => 'Laravel Starter Kit',
            'type' => 'string',
            'group' => 'branding',
            'label' => 'Nama Situs',
            'description' => 'Nama aplikasi yang ditampilkan di sidebar dan browser',
        ],
        'site_title' => [
            'value' => 'Laravel',
            'type' => 'string',
            'group' => 'branding',
            'label' => 'Judul Halaman',
            'description' => 'Title default untuk halaman browser',
        ],
        'favicon_ico' => [
            'value' => '/favicon.ico',
            'type' => 'image',
            'group' => 'branding',
            'label' => 'Favicon ICO',
            'description' => 'Icon favicon format .ico',
        ],
        'favicon_svg' => [
            'value' => '/favicon.svg',
            'type' => 'image',
            'group' => 'branding',
            'label' => 'Favicon SVG',
            'description' => 'Icon favicon format .svg',
        ],
        'logo' => [
            'value' => null,
            'type' => 'image',
            'group' => 'branding',
            'label' => 'Logo',
            'description' => 'Logo aplikasi',
        ],

        // External Links
        'repository_url' => [
            'value' => 'https://github.com/laravel/livewire-starter-kit',
            'type' => 'url',
            'group' => 'links',
            'label' => 'Repository URL',
            'description' => 'Link ke repository GitHub',
        ],
        'documentation_url' => [
            'value' => 'https://laravel.com/docs/starter-kits#livewire',
            'type' => 'url',
            'group' => 'links',
            'label' => 'Documentation URL',
            'description' => 'Link ke dokumentasi',
        ],

        // Welcome Page
        'welcome_enabled' => [
            'value' => '1',
            'type' => 'boolean',
            'group' => 'welcome',
            'label' => 'Tampilkan Welcome Page',
            'description' => 'Tampilkan halaman welcome default Laravel atau redirect ke dashboard',
        ],
        'welcome_title' => [
            'value' => "Let's get started",
            'type' => 'string',
            'group' => 'welcome',
            'label' => 'Welcome Title',
            'description' => 'Judul yang ditampilkan di halaman welcome',
        ],
        'welcome_description' => [
            'value' => 'Laravel has an incredibly rich ecosystem. We suggest starting with the following.',
            'type' => 'text',
            'group' => 'welcome',
            'label' => 'Welcome Description',
            'description' => 'Deskripsi yang ditampilkan di halaman welcome',
        ],
        'welcome_primary_link_text' => [
            'value' => 'Documentation',
            'type' => 'string',
            'group' => 'welcome',
            'label' => 'Link Utama Text',
            'description' => 'Teks untuk link utama',
        ],
        'welcome_primary_link_url' => [
            'value' => 'https://laravel.com/docs',
            'type' => 'url',
            'group' => 'welcome',
            'label' => 'Link Utama URL',
            'description' => 'URL untuk link utama',
        ],
        'welcome_secondary_link_text' => [
            'value' => 'Laracasts',
            'type' => 'string',
            'group' => 'welcome',
            'label' => 'Link Sekunder Text',
            'description' => 'Teks untuk link sekunder',
        ],
        'welcome_secondary_link_url' => [
            'value' => 'https://laracasts.com',
            'type' => 'url',
            'group' => 'welcome',
            'label' => 'Link Sekunder URL',
            'description' => 'URL untuk link sekunder',
        ],
        'welcome_cta_text' => [
            'value' => 'Deploy now',
            'type' => 'string',
            'group' => 'welcome',
            'label' => 'CTA Button Text',
            'description' => 'Teks tombol CTA',
        ],
        'welcome_cta_url' => [
            'value' => 'https://cloud.laravel.com',
            'type' => 'url',
            'group' => 'welcome',
            'label' => 'CTA Button URL',
            'description' => 'URL tombol CTA',
        ],
    ];

    /**
     * Get a setting value by key.
     */
    public static function getValue(string $key, mixed $default = null): mixed
    {
        $cacheKey = self::CACHE_PREFIX . $key;

        return Cache::remember($cacheKey, now()->addDay(), function () use ($key, $default) {
            $setting = self::where('key', $key)->first();

            if ($setting) {
                return self::castValue($setting->value, $setting->type);
            }

            // Return from defaults if not in database
            if (isset(self::$defaults[$key])) {
                return self::castValue(self::$defaults[$key]['value'], self::$defaults[$key]['type']);
            }

            return $default;
        });
    }

    /**
     * Set a setting value.
     */
    public static function setValue(string $key, mixed $value): self
    {
        $defaults = self::$defaults[$key] ?? [
            'type' => 'string',
            'group' => 'general',
            'label' => $key,
            'description' => null,
        ];

        $setting = self::updateOrCreate(
            ['key' => $key],
            [
                'value' => is_bool($value) ? ($value ? '1' : '0') : $value,
                'type' => $defaults['type'],
                'group' => $defaults['group'],
                'label' => $defaults['label'],
                'description' => $defaults['description'],
            ]
        );

        // Clear cache
        Cache::forget(self::CACHE_PREFIX . $key);
        Cache::forget(self::CACHE_ALL_KEY);

        return $setting;
    }

    /**
     * Get all settings as key-value pairs.
     *
     * @return array<string, mixed>
     */
    public static function getAllSettings(): array
    {
        return Cache::remember(self::CACHE_ALL_KEY, now()->addDay(), function () {
            $settings = [];

            // Start with defaults
            foreach (self::$defaults as $key => $config) {
                $settings[$key] = self::castValue($config['value'], $config['type']);
            }

            // Override with database values
            self::all()->each(function ($setting) use (&$settings) {
                $settings[$setting->key] = self::castValue($setting->value, $setting->type);
            });

            return $settings;
        });
    }

    /**
     * Get settings by group.
     *
     * @return \Illuminate\Support\Collection<int, self>
     */
    public static function getByGroup(string $group): \Illuminate\Support\Collection
    {
        $settings = collect();

        // Get defaults for this group
        foreach (self::$defaults as $key => $config) {
            if ($config['group'] === $group) {
                $dbSetting = self::where('key', $key)->first();

                $settings->push(new self([
                    'key' => $key,
                    'value' => $dbSetting?->value ?? $config['value'],
                    'type' => $config['type'],
                    'group' => $config['group'],
                    'label' => $config['label'],
                    'description' => $config['description'],
                ]));
            }
        }

        return $settings;
    }

    /**
     * Cast value based on type.
     */
    protected static function castValue(mixed $value, string $type): mixed
    {
        return match ($type) {
            'boolean' => (bool) $value,
            'integer' => (int) $value,
            default => $value,
        };
    }

    /**
     * Clear all settings cache.
     */
    public static function clearCache(): void
    {
        foreach (array_keys(self::$defaults) as $key) {
            Cache::forget(self::CACHE_PREFIX . $key);
        }
        Cache::forget(self::CACHE_ALL_KEY);
    }

    /**
     * Initialize default settings in database.
     */
    public static function initializeDefaults(): void
    {
        foreach (self::$defaults as $key => $config) {
            self::firstOrCreate(
                ['key' => $key],
                [
                    'value' => $config['value'],
                    'type' => $config['type'],
                    'group' => $config['group'],
                    'label' => $config['label'],
                    'description' => $config['description'],
                ]
            );
        }

        self::clearCache();
    }
}
