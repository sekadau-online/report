# Konfigurasi

Panduan konfigurasi aplikasi LKEU-RAPI.

## Environment Variables

### Application

```env
APP_NAME="LKEU-RAPI"
APP_ENV=local
APP_KEY=base64:...
APP_DEBUG=true
APP_URL=http://localhost:8000
```

### Database

```env
DB_CONNECTION=mysql
DB_HOST=127.0.0.1
DB_PORT=3306
DB_DATABASE=lkeu_rapi
DB_USERNAME=root
DB_PASSWORD=
```

### Mail

```env
MAIL_MAILER=smtp
MAIL_HOST=smtp.mailtrap.io
MAIL_PORT=2525
MAIL_USERNAME=null
MAIL_PASSWORD=null
MAIL_ENCRYPTION=null
MAIL_FROM_ADDRESS="noreply@example.com"
MAIL_FROM_NAME="${APP_NAME}"
```

### Session & Cache

```env
SESSION_DRIVER=file
CACHE_STORE=file
QUEUE_CONNECTION=sync
```

## Site Settings

### Menggunakan Admin UI

1. Login ke aplikasi
2. Buka **Settings > Site Settings**
3. Konfigurasi masing-masing tab:
   - **Branding**: Nama, logo, favicon
   - **Links**: Repository, dokumentasi
   - **Welcome**: Halaman welcome

### Menggunakan Seeder

Edit `database/seeders/SiteSettingSeeder.php`:

```php
$settings = [
    'site_name' => 'LKEU-RAPI',
    'site_title' => 'LKEU-RAPI | Laporan Keuangan',
    'logo' => '/images/lkeu-rapi-logo.svg',
    // ... more settings
];
```

Jalankan seeder:
```bash
php artisan db:seed --class=SiteSettingSeeder
```

### Menggunakan Helper Functions

```php
// Get single setting
$siteName = site_setting('site_name', 'Default Name');

// Get all settings
$allSettings = site_settings();

// In Blade
{{ site_setting('site_name') }}
```

## Fortify Configuration

File: `config/fortify.php`

### Features

```php
'features' => [
    Features::registration(),
    Features::resetPasswords(),
    Features::emailVerification(),
    Features::updateProfileInformation(),
    Features::updatePasswords(),
    Features::twoFactorAuthentication([
        'confirm' => true,
        'confirmPassword' => true,
    ]),
],
```

### Views

```php
'views' => true, // Enable Fortify views
```

## Storage Configuration

File: `config/filesystems.php`

### Public Disk

```php
'public' => [
    'driver' => 'local',
    'root' => storage_path('app/public'),
    'url' => env('APP_URL').'/storage',
    'visibility' => 'public',
],
```

### Symbolic Link

```bash
php artisan storage:link
```

## Cache Configuration

### Clear All Cache

```bash
php artisan optimize:clear
```

### Site Settings Cache

Settings di-cache selama 1 hari. Clear manual:

```php
SiteSetting::clearCache();
```

Atau via tinker:
```bash
php artisan tinker
>>> App\Models\SiteSetting::clearCache()
```

## Langkah Selanjutnya

- [Fitur](./features.md) - Dokumentasi fitur
- [Testing](./testing.md) - Panduan testing
