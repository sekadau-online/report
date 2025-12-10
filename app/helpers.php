<?php

declare(strict_types=1);

use App\Models\SiteSetting;

if (! function_exists('site_setting')) {
    /**
     * Get a site setting value.
     *
     * @param  string  $key  The setting key
     * @param  mixed  $default  Default value if setting not found
     * @return mixed
     */
    function site_setting(string $key, mixed $default = null): mixed
    {
        return SiteSetting::getValue($key, $default);
    }
}

if (! function_exists('site_settings')) {
    /**
     * Get all site settings.
     *
     * @return array<string, mixed>
     */
    function site_settings(): array
    {
        return SiteSetting::getAllSettings();
    }
}
