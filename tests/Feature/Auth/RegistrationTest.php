<?php

use App\Models\User;
use PragmaRX\Google2FA\Google2FA;

test('registration screen can be rendered', function () {
    $response = $this->get(route('register'));

    $response->assertStatus(200);
});

test('first user can register without invite code', function () {
    $response = $this->post(route('register.store'), [
        'name' => 'John Doe',
        'email' => 'test@example.com',
        'password' => 'password',
        'password_confirmation' => 'password',
    ]);

    $response->assertSessionHasNoErrors()
        ->assertRedirect(route('dashboard', absolute: false));

    $this->assertAuthenticated();
});

test('subsequent users require invite code when users exist', function () {
    // Create first user
    User::factory()->create();

    $response = $this->post(route('register.store'), [
        'name' => 'Jane Doe',
        'email' => 'jane@example.com',
        'password' => 'password',
        'password_confirmation' => 'password',
    ]);

    $response->assertSessionHasErrors(['invite_code']);
});

test('subsequent users can register with valid 2fa code', function () {
    // Create first user with 2FA enabled
    $google2fa = new Google2FA;
    $secret = $google2fa->generateSecretKey();

    User::factory()->create([
        'two_factor_secret' => encrypt($secret),
        'two_factor_confirmed_at' => now(),
    ]);

    $validCode = $google2fa->getCurrentOtp($secret);

    $response = $this->post(route('register.store'), [
        'name' => 'Jane Doe',
        'email' => 'jane@example.com',
        'password' => 'password',
        'password_confirmation' => 'password',
        'invite_code' => $validCode,
    ]);

    $response->assertSessionHasNoErrors()
        ->assertRedirect(route('dashboard', absolute: false));

    $this->assertAuthenticated();
});

test('subsequent users cannot register with invalid invite code', function () {
    // Create first user with 2FA enabled
    $google2fa = new Google2FA;
    $secret = $google2fa->generateSecretKey();

    User::factory()->create([
        'two_factor_secret' => encrypt($secret),
        'two_factor_confirmed_at' => now(),
    ]);

    $response = $this->post(route('register.store'), [
        'name' => 'Jane Doe',
        'email' => 'jane@example.com',
        'password' => 'password',
        'password_confirmation' => 'password',
        'invite_code' => '000000',
    ]);

    $response->assertSessionHasErrors(['invite_code']);
});

test('registration fails when no user has 2fa enabled', function () {
    // Create first user without 2FA
    User::factory()->create([
        'two_factor_secret' => null,
    ]);

    $response = $this->post(route('register.store'), [
        'name' => 'Jane Doe',
        'email' => 'jane@example.com',
        'password' => 'password',
        'password_confirmation' => 'password',
        'invite_code' => '123456',
    ]);

    $response->assertSessionHasErrors(['invite_code']);
});

test('registration page shows invite code field when users exist', function () {
    User::factory()->create();

    $response = $this->get(route('register'));

    $response->assertStatus(200)
        ->assertSee(__('Kode Undangan Diperlukan'))
        ->assertSee('invite_code');
});

test('registration page does not show invite code field for first user', function () {
    $response = $this->get(route('register'));

    $response->assertStatus(200)
        ->assertDontSee(__('Kode Undangan Diperlukan'));
});
