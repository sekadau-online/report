# API Reference

Dokumentasi endpoint dan routes aplikasi LKEU-RAPI.

## Overview

LKEU-RAPI menggunakan Livewire untuk interaksi frontend, sehingga tidak memiliki REST API tradisional. Semua operasi dilakukan melalui web routes dengan Livewire components.

## Web Routes

### Public Routes

| Method | URI | Name | Description |
|--------|-----|------|-------------|
| GET | `/` | - | Welcome page (jika enabled) |

### Authentication Routes

| Method | URI | Name | Description |
|--------|-----|------|-------------|
| GET | `/login` | login | Login page |
| POST | `/login` | - | Process login |
| POST | `/logout` | logout | Logout |
| GET | `/register` | register | Registration page |
| POST | `/register` | - | Process registration |
| GET | `/forgot-password` | password.request | Forgot password page |
| POST | `/forgot-password` | password.email | Send reset link |
| GET | `/reset-password/{token}` | password.reset | Reset password page |
| POST | `/reset-password` | password.update | Process reset |
| GET | `/email/verify` | verification.notice | Verification notice |
| GET | `/email/verify/{id}/{hash}` | verification.verify | Verify email |
| POST | `/email/verification-notification` | verification.send | Resend verification |
| GET | `/two-factor-challenge` | two-factor.login | 2FA challenge page |

### Dashboard

| Method | URI | Name | Description |
|--------|-----|------|-------------|
| GET | `/dashboard` | dashboard | Main dashboard |

### Financial Reports

| Method | URI | Name | Description |
|--------|-----|------|-------------|
| GET | `/financial-reports` | financial-reports.index | List reports |
| GET | `/financial-reports/create` | financial-reports.create | Create form |
| GET | `/financial-reports/export` | financial-reports.export | Export form |
| GET | `/financial-reports/import` | financial-reports.import | Import form |
| GET | `/financial-reports/{report}` | financial-reports.show | View report |
| GET | `/financial-reports/{report}/edit` | financial-reports.edit | Edit form |

### Settings

| Method | URI | Name | Description |
|--------|-----|------|-------------|
| GET | `/settings/profile` | settings.profile | Profile settings |
| GET | `/settings/password` | settings.password | Password settings |
| GET | `/settings/appearance` | settings.appearance | Appearance settings |
| GET | `/settings/two-factor` | settings.two-factor | 2FA settings |
| GET | `/settings/site` | site-settings.edit | Site settings |

## Livewire Components

### Financial Reports

#### `financial-reports.index`

List dan manage financial reports.

**Properties:**
- `search`: string - Search query
- `filterType`: string - Filter by type (income/expense)
- `sortDirection`: string - Sort direction (desc/asc)

**Actions:**
- `delete(id)`: Delete a report
- `updatedSearch()`: Search reports
- `updatedFilterType()`: Filter reports

#### `financial-reports.create`

Create new financial report.

**Properties:**
- `title`: string - Report title
- `type`: string - income/expense
- `category`: string - Category name
- `amount`: float - Amount
- `date`: string - Date (Y-m-d)
- `notes`: string - Optional notes
- `photo`: UploadedFile - Optional photo

**Actions:**
- `save()`: Save the report

#### `financial-reports.edit`

Edit existing financial report.

**Properties:**
Same as create, plus:
- `report`: FinancialReport - The report being edited
- `existingPhoto`: string - Current photo URL

**Actions:**
- `save()`: Update the report
- `deletePhoto()`: Remove existing photo

#### `financial-reports.show`

View financial report details.

**Properties:**
- `report`: FinancialReport - The report

**Actions:**
- `delete()`: Delete the report

#### `financial-reports.export`

Export reports.

**Properties:**
- `format`: string - json/sql/zip
- `startDate`: string - Filter start date
- `endDate`: string - Filter end date

**Actions:**
- `export()`: Download export file

#### `financial-reports.import`

Import reports.

**Properties:**
- `file`: UploadedFile - Import file

**Actions:**
- `import()`: Process import

### Settings

#### `settings.site`

Site settings management.

**Properties:**
- `activeGroup`: string - Current group tab
- `form`: array - Form data for current group

**Actions:**
- `save()`: Save settings
- `setGroup(group)`: Switch tab
- `deleteImage(key)`: Remove image setting

## Models

### User

```php
App\Models\User

// Relationships
$user->financialReports(): HasMany

// Attributes
- id: int
- name: string
- email: string
- email_verified_at: datetime
- password: string
- two_factor_secret: string|null
- two_factor_recovery_codes: string|null
- two_factor_confirmed_at: datetime|null
- remember_token: string|null
- created_at: datetime
- updated_at: datetime
```

### FinancialReport

```php
App\Models\FinancialReport

// Relationships
$report->user(): BelongsTo

// Attributes
- id: int
- user_id: int
- title: string
- type: string (income/expense)
- category: string
- amount: decimal(15,2)
- date: date
- notes: text|null
- photo: string|null
- created_at: datetime
- updated_at: datetime

// Accessors
$report->formatted_amount: string
$report->isIncome(): bool
$report->isExpense(): bool

// Static Methods
FinancialReport::getTypes(): array
FinancialReport::getCategories(): array
FinancialReport::getIncomeCategories(): array
FinancialReport::getExpenseCategories(): array
```

### SiteSetting

```php
App\Models\SiteSetting

// Attributes
- id: int
- key: string (unique)
- value: text|null
- type: string (string/image/boolean/url)
- group: string (branding/links/welcome)
- label: string
- description: string|null
- created_at: datetime
- updated_at: datetime

// Static Methods
SiteSetting::getValue(key, default): mixed
SiteSetting::setValue(key, value): void
SiteSetting::getAllSettings(): array
SiteSetting::getByGroup(group): array
SiteSetting::clearCache(): void
SiteSetting::initializeDefaults(): void
```

## Helper Functions

### site_setting()

Get a site setting value.

```php
site_setting('site_name');
site_setting('site_name', 'Default Name');
```

### site_settings()

Get all site settings as array.

```php
$settings = site_settings();
echo $settings['site_name'];
```

## Services

### ExportService

```php
App\Services\FinancialReport\ExportService

$service = new ExportService($user);

// Export to JSON
$service->toJson($filters): string

// Export to SQL
$service->toSql($filters): string

// Export to ZIP
$service->toZip($filters): string // Returns temp file path
```

### ImportService

```php
App\Services\FinancialReport\ImportService

$service = new ImportService($user);

// Import from file
$result = $service->import($uploadedFile);

// Result structure
[
    'imported' => 10,
    'skipped' => 2,
    'errors' => ['Row 5: Invalid type']
]
```

### SiteSettingService

```php
App\Services\SiteSettingService

$service = new SiteSettingService();

$service->get('key', 'default');
$service->set('key', 'value');
$service->all();
$service->getByGroup('branding');
$service->uploadImage('logo', $file);
$service->deleteImage('logo');
$service->updateMany(['key1' => 'value1', ...]);
$service->clearCache();
$service->getGroups();
```
