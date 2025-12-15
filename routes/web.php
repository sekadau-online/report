<?php

use App\Models\SiteSetting;
use Illuminate\Support\Facades\Route;
use Laravel\Fortify\Features;
use Livewire\Volt\Volt;

Route::get('/', function () {
    $welcomeEnabled = SiteSetting::getValue('welcome_enabled', true);

    if (! $welcomeEnabled) {
        return redirect()->route('login');
    }

    return view('welcome');
})->name('home');

Route::view('dashboard', 'dashboard')
    ->middleware(['auth', 'verified'])
    ->name('dashboard');

Route::middleware(['auth'])->group(function () {
    Route::redirect('settings', 'settings/profile');

    Volt::route('settings/profile', 'settings.profile')->name('profile.edit');
    Volt::route('settings/password', 'settings.password')->name('user-password.edit');
    Volt::route('settings/appearance', 'settings.appearance')->name('appearance.edit');
    Volt::route('settings/site', 'settings.site')->name('site-settings.edit');

    Volt::route('settings/two-factor', 'settings.two-factor')
        ->middleware(
            when(
                Features::canManageTwoFactorAuthentication()
                    && Features::optionEnabled(Features::twoFactorAuthentication(), 'confirmPassword'),
                ['password.confirm'],
                [],
            ),
        )
        ->name('two-factor.show');

    // Financial Reports Routes
    Volt::route('financial-reports', 'financial-reports.index')->name('financial-reports.index');
    Volt::route('financial-reports/create', 'financial-reports.create')->name('financial-reports.create');
    Volt::route('financial-reports/{report}', 'financial-reports.show')->name('financial-reports.show');
    Volt::route('financial-reports/{report}/edit', 'financial-reports.edit')->name('financial-reports.edit');

    // Share Links Routes
    Volt::route('share-links', 'share-links.index')->name('share-links.index');
    Volt::route('share-links/create', 'share-links.create')->name('share-links.create');
    Volt::route('share-links/{shareLink}/edit', 'share-links.edit')->name('share-links.edit');
});

// Public Share Link Routes (no auth required)
Route::get('s/{token}', function (string $token) {
    return app(\App\Http\Controllers\ShareLinkController::class)->show($token);
})->name('share.view');

Route::post('s/{token}', function (string $token, \Illuminate\Http\Request $request) {
    return app(\App\Http\Controllers\ShareLinkController::class)->authenticate($token, $request);
})->name('share.authenticate');
