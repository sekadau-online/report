# Testing

Panduan testing untuk LKEU-RAPI.

## Overview

LKEU-RAPI menggunakan [Pest](https://pestphp.com/) untuk testing. Semua tests berada di folder `tests/`.

## Menjalankan Tests

### Semua Tests

```bash
php artisan test
```

### Tests Tertentu

```bash
# By filename
php artisan test tests/Feature/FinancialReports/FinancialReportTest.php

# By filter
php artisan test --filter=FinancialReport

# By filter specific test
php artisan test --filter="user can create financial report"
```

### Dengan Coverage

```bash
php artisan test --coverage
```

### Parallel Testing

```bash
php artisan test --parallel
```

## Struktur Tests

```
tests/
├── Feature/
│   ├── Auth/
│   │   ├── AuthenticationTest.php
│   │   ├── EmailVerificationTest.php
│   │   ├── PasswordConfirmationTest.php
│   │   ├── PasswordResetTest.php
│   │   ├── RegistrationTest.php
│   │   └── TwoFactorChallengeTest.php
│   ├── FinancialReports/
│   │   ├── FinancialReportTest.php
│   │   └── ImportExportTest.php
│   ├── Settings/
│   │   ├── PasswordUpdateTest.php
│   │   ├── ProfileUpdateTest.php
│   │   └── TwoFactorAuthenticationTest.php
│   ├── SiteSettings/
│   │   └── SiteSettingTest.php
│   ├── DashboardTest.php
│   └── ExampleTest.php
├── Unit/
│   └── ExampleTest.php
├── Pest.php
└── TestCase.php
```

## Test Categories

### Authentication Tests

- Login/logout
- Registration
- Password reset
- Email verification
- Two-factor authentication

### Financial Report Tests

- CRUD operations
- Authorization (users can only access own reports)
- Validation
- Photo upload/delete
- Search & filter

### Import/Export Tests

- Export to JSON/SQL/ZIP
- Import from JSON/SQL/ZIP
- Duplicate detection
- Error handling

### Site Settings Tests

- Model operations
- Service layer
- Helper functions
- UI interactions
- Dynamic layout components

## Menulis Tests

### Basic Test

```php
it('can create a financial report', function () {
    $user = User::factory()->create();

    Livewire::actingAs($user)
        ->test('financial-reports.create')
        ->set('title', 'Gaji Bulanan')
        ->set('type', 'income')
        ->set('category', 'Gaji')
        ->set('amount', 5000000)
        ->set('date', now()->format('Y-m-d'))
        ->call('save')
        ->assertHasNoErrors();

    expect(FinancialReport::where('title', 'Gaji Bulanan')->exists())->toBeTrue();
});
```

### Testing Volt Components

```php
use Livewire\Volt\Volt;

test('site settings page works', function () {
    $user = User::factory()->create();

    Volt::test('settings.site')
        ->actingAs($user)
        ->assertOk()
        ->set('form.site_name', 'New Name')
        ->call('save')
        ->assertHasNoErrors();
});
```

### Testing HTTP Requests

```php
it('requires authentication', function () {
    $this->get(route('financial-reports.index'))
        ->assertRedirect(route('login'));
});

it('can access dashboard', function () {
    $user = User::factory()->create();

    $this->actingAs($user)
        ->get(route('dashboard'))
        ->assertOk()
        ->assertSee('Dashboard');
});
```

### Using Factories

```php
it('can view own report', function () {
    $user = User::factory()->create();
    $report = FinancialReport::factory()
        ->for($user)
        ->income()
        ->create();

    $this->actingAs($user)
        ->get(route('financial-reports.show', $report))
        ->assertOk()
        ->assertSee($report->title);
});
```

### Testing Validation

```php
it('validates required fields', function () {
    $user = User::factory()->create();

    Livewire::actingAs($user)
        ->test('financial-reports.create')
        ->set('title', '')
        ->set('amount', '')
        ->call('save')
        ->assertHasErrors(['title', 'amount']);
});
```

## Factories

### UserFactory

```php
User::factory()->create(); // Basic user
User::factory()->unverified()->create(); // Unverified email
```

### FinancialReportFactory

```php
FinancialReport::factory()->create(); // Random report
FinancialReport::factory()->income()->create(); // Income report
FinancialReport::factory()->expense()->create(); // Expense report
FinancialReport::factory()->withPhoto()->create(); // With photo
```

## Database

Tests menggunakan SQLite in-memory dengan trait `RefreshDatabase`:

```php
// tests/Pest.php
uses(RefreshDatabase::class)->in('Feature');
```

## Mocking

```php
use function Pest\Laravel\mock;

it('sends notification', function () {
    Notification::fake();

    // ... test code ...

    Notification::assertSent(ResetPassword::class);
});
```

## Tips

1. **Run tests often**: Jalankan tests setelah setiap perubahan
2. **Use filters**: Gunakan `--filter` untuk test cepat
3. **Check coverage**: Pastikan coverage tetap tinggi
4. **Test edge cases**: Jangan lupa test kasus error

## Langkah Selanjutnya

- [Deployment](./deployment.md) - Panduan deployment
- [API Reference](./api.md) - Dokumentasi API
