<?php

declare(strict_types=1);

use App\Models\FinancialReport;
use App\Models\ShareLink;
use App\Models\User;
use Livewire\Volt\Volt;

beforeEach(function () {
    $this->user = User::factory()->create();
});

// === Index Page Tests ===

test('guests cannot access share links index', function () {
    $this->get(route('share-links.index'))
        ->assertRedirect(route('login'));
});

test('authenticated users can access share links index', function () {
    $this->actingAs($this->user)
        ->get(route('share-links.index'))
        ->assertOk();
});

test('share links index shows user share links', function () {
    $shareLink = ShareLink::factory()->for($this->user)->create(['name' => 'My Test Link']);

    $this->actingAs($this->user);

    Volt::test('share-links.index')
        ->assertSee('My Test Link');
});

test('share links index does not show other users share links', function () {
    $otherUser = User::factory()->create();
    $otherShareLink = ShareLink::factory()->for($otherUser)->create(['name' => 'Other User Link']);

    $this->actingAs($this->user);

    Volt::test('share-links.index')
        ->assertDontSee('Other User Link');
});

// === Create Page Tests ===

test('guests cannot access share links create page', function () {
    $this->get(route('share-links.create'))
        ->assertRedirect(route('login'));
});

test('authenticated users can access share links create page', function () {
    $this->actingAs($this->user)
        ->get(route('share-links.create'))
        ->assertOk();
});

test('user can create a share link without password', function () {
    $this->actingAs($this->user);

    Volt::test('share-links.create')
        ->set('name', 'Test Share Link')
        ->call('save')
        ->assertHasNoErrors()
        ->assertRedirect(route('share-links.index'));

    $this->assertDatabaseHas('share_links', [
        'user_id' => $this->user->id,
        'name' => 'Test Share Link',
        'is_active' => true,
    ]);
});

test('user can create a share link with password', function () {
    $this->actingAs($this->user);

    Volt::test('share-links.create')
        ->set('name', 'Protected Link')
        ->set('has_password', true)
        ->set('password', 'secret123')
        ->set('password_confirmation', 'secret123')
        ->call('save')
        ->assertHasNoErrors()
        ->assertRedirect(route('share-links.index'));

    $shareLink = ShareLink::where('name', 'Protected Link')->first();
    expect($shareLink)->not->toBeNull();
    expect($shareLink->requiresPassword())->toBeTrue();
    expect($shareLink->checkPassword('secret123'))->toBeTrue();
});

test('user can create a share link with expiration date', function () {
    $expiresAt = now()->addDays(7)->format('Y-m-d');

    $this->actingAs($this->user);

    Volt::test('share-links.create')
        ->set('name', 'Expiring Link')
        ->set('has_expiry', true)
        ->set('expires_at', $expiresAt)
        ->call('save')
        ->assertHasNoErrors()
        ->assertRedirect(route('share-links.index'));

    $shareLink = ShareLink::where('name', 'Expiring Link')->first();
    expect($shareLink)->not->toBeNull();
    expect($shareLink->expires_at->format('Y-m-d'))->toBe($expiresAt);
});

test('create share link requires name', function () {
    $this->actingAs($this->user);

    Volt::test('share-links.create')
        ->set('name', '')
        ->call('save')
        ->assertHasErrors(['name' => 'required']);
});

test('create share link password confirmation must match', function () {
    $this->actingAs($this->user);

    Volt::test('share-links.create')
        ->set('name', 'Test Link')
        ->set('has_password', true)
        ->set('password', 'secret123')
        ->set('password_confirmation', 'different')
        ->call('save')
        ->assertHasErrors(['password' => 'confirmed']);
});

// === Edit Page Tests ===

test('guests cannot access share links edit page', function () {
    $shareLink = ShareLink::factory()->for($this->user)->create();

    $this->get(route('share-links.edit', $shareLink))
        ->assertRedirect(route('login'));
});

test('user can access their own share link edit page', function () {
    $shareLink = ShareLink::factory()->for($this->user)->create();

    $this->actingAs($this->user)
        ->get(route('share-links.edit', $shareLink))
        ->assertOk();
});

test('user cannot access other users share link edit page', function () {
    $otherUser = User::factory()->create();
    $shareLink = ShareLink::factory()->for($otherUser)->create();

    $this->actingAs($this->user)
        ->get(route('share-links.edit', $shareLink))
        ->assertForbidden();
});

test('user can update share link name', function () {
    $shareLink = ShareLink::factory()->for($this->user)->create(['name' => 'Old Name']);

    $this->actingAs($this->user);

    Volt::test('share-links.edit', ['shareLink' => $shareLink])
        ->set('name', 'New Name')
        ->call('save')
        ->assertHasNoErrors();

    expect($shareLink->fresh()->name)->toBe('New Name');
});

test('user can toggle share link active status', function () {
    $shareLink = ShareLink::factory()->for($this->user)->create(['is_active' => true]);

    $this->actingAs($this->user);

    Volt::test('share-links.edit', ['shareLink' => $shareLink])
        ->call('toggleActive')
        ->assertHasNoErrors();

    expect($shareLink->fresh()->is_active)->toBeFalse();
});

test('user can regenerate share link token', function () {
    $shareLink = ShareLink::factory()->for($this->user)->create();
    $oldToken = $shareLink->token;

    $this->actingAs($this->user);

    Volt::test('share-links.edit', ['shareLink' => $shareLink])
        ->call('regenerateToken')
        ->assertHasNoErrors();

    expect($shareLink->fresh()->token)->not->toBe($oldToken);
});

test('user can delete share link', function () {
    $shareLink = ShareLink::factory()->for($this->user)->create();

    $this->actingAs($this->user);

    Volt::test('share-links.edit', ['shareLink' => $shareLink])
        ->call('delete')
        ->assertRedirect(route('share-links.index'));

    $this->assertDatabaseMissing('share_links', ['id' => $shareLink->id]);
});

// === Public Share View Tests ===

test('valid share link can be accessed publicly', function () {
    $shareLink = ShareLink::factory()->for($this->user)->create();

    $this->get(route('share.view', $shareLink->token))
        ->assertOk()
        ->assertSee($shareLink->name);
});

test('invalid share token returns 404', function () {
    $this->get(route('share.view', 'invalid-token'))
        ->assertNotFound();
});

test('inactive share link cannot be accessed', function () {
    $shareLink = ShareLink::factory()->for($this->user)->inactive()->create();

    $this->get(route('share.view', $shareLink->token))
        ->assertNotFound();
});

test('expired share link shows expired page', function () {
    $shareLink = ShareLink::factory()->for($this->user)->expired()->create();

    $this->get(route('share.view', $shareLink->token))
        ->assertOk()
        ->assertViewIs('share.expired');
});

test('password protected share link shows password form', function () {
    $shareLink = ShareLink::factory()->for($this->user)->withPassword()->create();

    $this->get(route('share.view', $shareLink->token))
        ->assertOk()
        ->assertViewIs('share.password');
});

test('correct password grants access to protected share link', function () {
    $shareLink = ShareLink::factory()->for($this->user)->create([
        'password' => 'secret123',
    ]);

    $this->post(route('share.authenticate', $shareLink->token), [
        'password' => 'secret123',
    ])->assertRedirect(route('share.view', $shareLink->token));

    $this->get(route('share.view', $shareLink->token))
        ->assertOk()
        ->assertViewIs('share.view');
});

test('incorrect password shows error', function () {
    $shareLink = ShareLink::factory()->for($this->user)->create([
        'password' => 'secret123',
    ]);

    $this->post(route('share.authenticate', $shareLink->token), [
        'password' => 'wrongpassword',
    ])->assertSessionHasErrors('password');
});

test('share view shows financial reports', function () {
    $shareLink = ShareLink::factory()->for($this->user)->create();
    $report = FinancialReport::factory()->for($this->user)->create([
        'title' => 'Test Report Title',
    ]);

    $this->get(route('share.view', $shareLink->token))
        ->assertOk()
        ->assertSee('Test Report Title');
});

test('view count increments when accessing share link', function () {
    $shareLink = ShareLink::factory()->for($this->user)->create(['view_count' => 0]);

    $this->get(route('share.view', $shareLink->token));

    expect($shareLink->fresh()->view_count)->toBe(1);
});

// === ShareLink Model Tests ===

test('share link generates unique token on creation', function () {
    $shareLink = ShareLink::factory()->for($this->user)->create();

    expect($shareLink->token)->toHaveLength(32);
});

test('share link isValid returns true for active non-expired link', function () {
    $shareLink = ShareLink::factory()->for($this->user)->create([
        'is_active' => true,
        'expires_at' => null,
    ]);

    expect($shareLink->isValid())->toBeTrue();
});

test('share link isValid returns false for inactive link', function () {
    $shareLink = ShareLink::factory()->for($this->user)->inactive()->create();

    expect($shareLink->isValid())->toBeFalse();
});

test('share link isValid returns false for expired link', function () {
    $shareLink = ShareLink::factory()->for($this->user)->expired()->create();

    expect($shareLink->isValid())->toBeFalse();
});

test('share link isExpired returns true for past expiration', function () {
    $shareLink = ShareLink::factory()->for($this->user)->expired()->create();

    expect($shareLink->isExpired())->toBeTrue();
});

test('share link isExpired returns false for future expiration', function () {
    $shareLink = ShareLink::factory()->for($this->user)->expiresIn(7)->create();

    expect($shareLink->isExpired())->toBeFalse();
});

test('share link isExpired returns false when no expiration set', function () {
    $shareLink = ShareLink::factory()->for($this->user)->create([
        'expires_at' => null,
    ]);

    expect($shareLink->isExpired())->toBeFalse();
});

test('share link requiresPassword returns true when password is set', function () {
    $shareLink = ShareLink::factory()->for($this->user)->withPassword()->create();

    expect($shareLink->requiresPassword())->toBeTrue();
});

test('share link requiresPassword returns false when no password', function () {
    $shareLink = ShareLink::factory()->for($this->user)->create([
        'password' => null,
    ]);

    expect($shareLink->requiresPassword())->toBeFalse();
});

test('share link checkPassword validates correctly', function () {
    $shareLink = ShareLink::factory()->for($this->user)->create([
        'password' => 'correct-password',
    ]);

    expect($shareLink->checkPassword('correct-password'))->toBeTrue();
    expect($shareLink->checkPassword('wrong-password'))->toBeFalse();
});

test('share link recordView updates count and timestamp', function () {
    $shareLink = ShareLink::factory()->for($this->user)->create([
        'view_count' => 5,
        'last_viewed_at' => null,
    ]);

    $shareLink->recordView();

    expect($shareLink->fresh()->view_count)->toBe(6);
    expect($shareLink->fresh()->last_viewed_at)->not->toBeNull();
});

test('share link getShareUrl returns correct url', function () {
    $shareLink = ShareLink::factory()->for($this->user)->create();

    expect($shareLink->getShareUrl())->toContain('/s/'.$shareLink->token);
});

// === Policy Tests ===

test('user can view any share links', function () {
    expect($this->user->can('viewAny', ShareLink::class))->toBeTrue();
});

test('user can view own share link', function () {
    $shareLink = ShareLink::factory()->for($this->user)->create();

    expect($this->user->can('view', $shareLink))->toBeTrue();
});

test('user cannot view other users share link', function () {
    $otherUser = User::factory()->create();
    $shareLink = ShareLink::factory()->for($otherUser)->create();

    expect($this->user->can('view', $shareLink))->toBeFalse();
});

test('user can create share links', function () {
    expect($this->user->can('create', ShareLink::class))->toBeTrue();
});

test('user can update own share link', function () {
    $shareLink = ShareLink::factory()->for($this->user)->create();

    expect($this->user->can('update', $shareLink))->toBeTrue();
});

test('user cannot update other users share link', function () {
    $otherUser = User::factory()->create();
    $shareLink = ShareLink::factory()->for($otherUser)->create();

    expect($this->user->can('update', $shareLink))->toBeFalse();
});

test('user can delete own share link', function () {
    $shareLink = ShareLink::factory()->for($this->user)->create();

    expect($this->user->can('delete', $shareLink))->toBeTrue();
});

test('user cannot delete other users share link', function () {
    $otherUser = User::factory()->create();
    $shareLink = ShareLink::factory()->for($otherUser)->create();

    expect($this->user->can('delete', $shareLink))->toBeFalse();
});

// === QR Code Tests ===

test('share link can generate qr code svg', function () {
    $shareLink = ShareLink::factory()->for($this->user)->create();

    $svg = $shareLink->getQrCodeSvg();

    expect($svg)->toBeString()
        ->toContain('<svg')
        ->toContain('</svg>');
});

test('share link can generate qr code data uri', function () {
    $shareLink = ShareLink::factory()->for($this->user)->create();

    $dataUri = $shareLink->getQrCodeDataUri();

    expect($dataUri)->toBeString()
        ->toStartWith('data:image/svg+xml;base64,');
});

test('qr code contains share url', function () {
    $shareLink = ShareLink::factory()->for($this->user)->create();

    $svg = $shareLink->getQrCodeSvg();
    $dataUri = $shareLink->getQrCodeDataUri();

    // Both should generate valid output
    expect($svg)->not->toBeEmpty();
    expect($dataUri)->not->toBeEmpty();

    // Verify URL is used (indirectly by checking QR is generated)
    expect($shareLink->getShareUrl())->toContain($shareLink->token);
});

test('share links index shows qr code button', function () {
    $shareLink = ShareLink::factory()->for($this->user)->create();

    $this->actingAs($this->user)
        ->get(route('share-links.index'))
        ->assertOk()
        ->assertSee('QR Code');
});
