<?php

declare(strict_types=1);

namespace Database\Seeders;

use App\Models\SiteSetting;
use Illuminate\Database\Seeder;

class SiteSettingSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        $settings = [
            // Branding
            'site_name' => 'LKEU-RAPI',
            'site_title' => 'LKEU-RAPI | Laporan Keuangan',
            'site_description' => 'Sistem Laporan Keuangan RAPI - Aplikasi pencatatan dan pelaporan keuangan yang rapi dan terorganisir.',
            'favicon_ico' => '/favicon.ico',
            'favicon_svg' => '/favicon.svg',
            'logo' => '/images/lkeu-rapi-logo.svg',

            // External Links
            'repository_url' => 'https://github.com/sekadau-online/report',
            'documentation_url' => 'https://github.com/sekadau-online/report/tree/main/docs',

            // Welcome Page
            'welcome_enabled' => '1',
            'welcome_title' => 'LKEU-RAPI',
            'welcome_description' => 'Sistem Laporan Keuangan yang Rapi dan Terorganisir. Kelola pemasukan dan pengeluaran dengan mudah, import/export data, dan pantau keuangan Anda.',
            'welcome_cta_text' => 'Mulai Sekarang',
            'welcome_cta_url' => '/login',
            'welcome_primary_link_text' => 'Dokumentasi',
            'welcome_primary_link_url' => 'https://github.com/sekadau-online/report/tree/main/docs',
            'welcome_secondary_link_text' => 'Repository',
            'welcome_secondary_link_url' => 'https://github.com/sekadau-online/report',
        ];

        foreach ($settings as $key => $value) {
            $default = SiteSetting::$defaults[$key] ?? null;

            if ($default) {
                SiteSetting::updateOrCreate(
                    ['key' => $key],
                    [
                        'value' => $value,
                        'type' => $default['type'],
                        'group' => $default['group'],
                        'label' => $default['label'],
                        'description' => $default['description'],
                    ]
                );
            }
        }

        // Clear cache after seeding
        SiteSetting::clearCache();
    }
}
