<?php

declare(strict_types=1);

namespace App\Services;

use App\Models\SiteSetting;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Str;

class SiteSettingService
{
    /**
     * Get a setting value.
     */
    public function get(string $key, mixed $default = null): mixed
    {
        return SiteSetting::getValue($key, $default);
    }

    /**
     * Set a setting value.
     */
    public function set(string $key, mixed $value): SiteSetting
    {
        return SiteSetting::setValue($key, $value);
    }

    /**
     * Get all settings.
     *
     * @return array<string, mixed>
     */
    public function all(): array
    {
        return SiteSetting::getAllSettings();
    }

    /**
     * Get settings by group.
     *
     * @return \Illuminate\Support\Collection<int, SiteSetting>
     */
    public function getByGroup(string $group): \Illuminate\Support\Collection
    {
        return SiteSetting::getByGroup($group);
    }

    /**
     * Upload and store an image setting.
     */
    public function uploadImage(string $key, UploadedFile $file): string
    {
        // Get old value to delete
        $oldValue = $this->get($key);

        // Generate unique filename
        $extension = $file->getClientOriginalExtension();
        $filename = $key . '_' . Str::uuid() . '.' . $extension;

        // Store in public disk under site-settings folder
        $path = $file->storeAs('site-settings', $filename, 'public');

        if ($path === false) {
            throw new \RuntimeException("Failed to store image for setting: {$key}");
        }

        // Delete old file if exists and is stored file (not default)
        if ($oldValue && str_starts_with((string) $oldValue, '/storage/site-settings/')) {
            $oldPath = str_replace('/storage/', '', $oldValue);
            if (Storage::disk('public')->exists($oldPath)) {
                Storage::disk('public')->delete($oldPath);
            }
        }

        // Save the public URL
        $url = '/storage/' . $path;
        $this->set($key, $url);

        return $url;
    }

    /**
     * Delete an image setting.
     */
    public function deleteImage(string $key): void
    {
        $value = $this->get($key);

        if ($value && str_starts_with((string) $value, '/storage/site-settings/')) {
            $path = str_replace('/storage/', '', $value);
            if (Storage::disk('public')->exists($path)) {
                Storage::disk('public')->delete($path);
            }
        }

        // Reset to default
        $default = SiteSetting::$defaults[$key]['value'] ?? null;
        $this->set($key, $default);
    }

    /**
     * Update multiple settings at once.
     *
     * @param  array<string, mixed>  $settings
     */
    public function updateMany(array $settings): void
    {
        foreach ($settings as $key => $value) {
            $this->set($key, $value);
        }
    }

    /**
     * Initialize default settings.
     */
    public function initializeDefaults(): void
    {
        SiteSetting::initializeDefaults();
    }

    /**
     * Clear all cache.
     */
    public function clearCache(): void
    {
        SiteSetting::clearCache();
    }

    /**
     * Get available groups.
     *
     * @return array<string, string>
     */
    public function getGroups(): array
    {
        return [
            'branding' => 'Branding',
            'links' => 'External Links',
            'welcome' => 'Welcome Page',
        ];
    }
}
