<?php

declare(strict_types=1);

use App\Models\FinancialReport;
use App\Models\User;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\Storage;
use Livewire\Volt\Volt;

beforeEach(function () {
    Storage::fake('public');
});

describe('Financial Reports Index', function () {
    test('guest cannot access financial reports index', function () {
        $this->get(route('financial-reports.index'))
            ->assertRedirect(route('login'));
    });

    test('authenticated user can view financial reports index', function () {
        $user = User::factory()->create();

        $this->actingAs($user)
            ->get(route('financial-reports.index'))
            ->assertOk();
    });

    test('user can only see their own reports', function () {
        $user = User::factory()->create();
        $otherUser = User::factory()->create();

        $myReport = FinancialReport::factory()->create([
            'user_id' => $user->id,
            'title' => 'My Report',
        ]);

        $otherReport = FinancialReport::factory()->create([
            'user_id' => $otherUser->id,
            'title' => 'Other Report',
        ]);

        Volt::actingAs($user)
            ->test('financial-reports.index')
            ->assertSee('My Report')
            ->assertDontSee('Other Report');
    });

    test('user can search reports', function () {
        $user = User::factory()->create();

        FinancialReport::factory()->create([
            'user_id' => $user->id,
            'title' => 'Gaji Bulanan',
        ]);

        FinancialReport::factory()->create([
            'user_id' => $user->id,
            'title' => 'Biaya Operasional',
        ]);

        Volt::actingAs($user)
            ->test('financial-reports.index')
            ->set('search', 'Gaji')
            ->assertSee('Gaji Bulanan')
            ->assertDontSee('Biaya Operasional');
    });

    test('user can filter by type', function () {
        $user = User::factory()->create();

        FinancialReport::factory()->income()->create([
            'user_id' => $user->id,
            'title' => 'Income Report',
        ]);

        FinancialReport::factory()->expense()->create([
            'user_id' => $user->id,
            'title' => 'Expense Report',
        ]);

        Volt::actingAs($user)
            ->test('financial-reports.index')
            ->set('typeFilter', 'income')
            ->assertSee('Income Report')
            ->assertDontSee('Expense Report');
    });

    test('user can delete report', function () {
        $user = User::factory()->create();

        $report = FinancialReport::factory()->create([
            'user_id' => $user->id,
        ]);

        Volt::actingAs($user)
            ->test('financial-reports.index')
            ->call('delete', $report)
            ->assertDispatched('report-deleted');

        $this->assertDatabaseMissing('financial_reports', ['id' => $report->id]);
    });
});

describe('Financial Reports Create', function () {
    test('guest cannot access create page', function () {
        $this->get(route('financial-reports.create'))
            ->assertRedirect(route('login'));
    });

    test('authenticated user can view create page', function () {
        $user = User::factory()->create();

        $this->actingAs($user)
            ->get(route('financial-reports.create'))
            ->assertOk();
    });

    test('user can create financial report', function () {
        $user = User::factory()->create();

        Volt::actingAs($user)
            ->test('financial-reports.create')
            ->set('title', 'Test Report')
            ->set('description', 'Test Description')
            ->set('type', 'income')
            ->set('amount', '500000')
            ->set('report_date', now()->format('Y-m-d'))
            ->set('category', 'sales')
            ->call('save')
            ->assertRedirect(route('financial-reports.index'));

        $this->assertDatabaseHas('financial_reports', [
            'user_id' => $user->id,
            'title' => 'Test Report',
            'type' => 'income',
            'amount' => '500000.00',
            'category' => 'sales',
        ]);
    });

    test('user can upload photo when creating report', function () {
        $user = User::factory()->create();
        $photo = UploadedFile::fake()->image('receipt.jpg');

        Volt::actingAs($user)
            ->test('financial-reports.create')
            ->set('title', 'Report With Photo')
            ->set('type', 'expense')
            ->set('amount', '100000')
            ->set('report_date', now()->format('Y-m-d'))
            ->set('photo', $photo)
            ->call('save')
            ->assertRedirect(route('financial-reports.index'));

        $report = FinancialReport::where('title', 'Report With Photo')->first();
        expect($report->photo)->not->toBeNull();
        Storage::disk('public')->assertExists($report->photo);
    });

    test('validation fails with empty title', function () {
        $user = User::factory()->create();

        Volt::actingAs($user)
            ->test('financial-reports.create')
            ->set('title', '')
            ->set('type', 'income')
            ->set('amount', '500000')
            ->set('report_date', now()->format('Y-m-d'))
            ->call('save')
            ->assertHasErrors(['title' => 'required']);
    });

    test('validation fails with invalid type', function () {
        $user = User::factory()->create();

        Volt::actingAs($user)
            ->test('financial-reports.create')
            ->set('title', 'Test')
            ->set('type', 'invalid')
            ->set('amount', '500000')
            ->set('report_date', now()->format('Y-m-d'))
            ->call('save')
            ->assertHasErrors(['type']);
    });

    test('validation fails with future date', function () {
        $user = User::factory()->create();

        Volt::actingAs($user)
            ->test('financial-reports.create')
            ->set('title', 'Test')
            ->set('type', 'income')
            ->set('amount', '500000')
            ->set('report_date', now()->addDays(5)->format('Y-m-d'))
            ->call('save')
            ->assertHasErrors(['report_date']);
    });
});

describe('Financial Reports Edit', function () {
    test('guest cannot access edit page', function () {
        $report = FinancialReport::factory()->create();

        $this->get(route('financial-reports.edit', $report))
            ->assertRedirect(route('login'));
    });

    test('user cannot edit another users report', function () {
        $user = User::factory()->create();
        $otherUser = User::factory()->create();

        $report = FinancialReport::factory()->create([
            'user_id' => $otherUser->id,
        ]);

        $this->actingAs($user)
            ->get(route('financial-reports.edit', $report))
            ->assertForbidden();
    });

    test('user can view edit page for their own report', function () {
        $user = User::factory()->create();
        $report = FinancialReport::factory()->create([
            'user_id' => $user->id,
        ]);

        $this->actingAs($user)
            ->get(route('financial-reports.edit', $report))
            ->assertOk();
    });

    test('user can update their report', function () {
        $user = User::factory()->create();
        $report = FinancialReport::factory()->create([
            'user_id' => $user->id,
            'title' => 'Original Title',
        ]);

        Volt::actingAs($user)
            ->test('financial-reports.edit', ['report' => $report])
            ->set('title', 'Updated Title')
            ->set('amount', '750000')
            ->call('save')
            ->assertRedirect(route('financial-reports.index'));

        $this->assertDatabaseHas('financial_reports', [
            'id' => $report->id,
            'title' => 'Updated Title',
            'amount' => '750000.00',
        ]);
    });

    test('user can upload new photo replacing existing', function () {
        $user = User::factory()->create();

        $oldPhoto = UploadedFile::fake()->image('old.jpg');
        $oldPhotoPath = $oldPhoto->store('financial-reports', 'public');

        $report = FinancialReport::factory()->create([
            'user_id' => $user->id,
            'photo' => $oldPhotoPath,
        ]);

        $newPhoto = UploadedFile::fake()->image('new.jpg');

        Volt::actingAs($user)
            ->test('financial-reports.edit', ['report' => $report])
            ->set('photo', $newPhoto)
            ->call('save')
            ->assertRedirect(route('financial-reports.index'));

        $report->refresh();
        expect($report->photo)->not->toBe($oldPhotoPath);
        Storage::disk('public')->assertMissing($oldPhotoPath);
        Storage::disk('public')->assertExists($report->photo);
    });

    test('user can delete existing photo', function () {
        $user = User::factory()->create();

        $oldPhoto = UploadedFile::fake()->image('old.jpg');
        $oldPhotoPath = $oldPhoto->store('financial-reports', 'public');

        $report = FinancialReport::factory()->create([
            'user_id' => $user->id,
            'photo' => $oldPhotoPath,
        ]);

        Volt::actingAs($user)
            ->test('financial-reports.edit', ['report' => $report])
            ->call('deleteExistingPhoto')
            ->call('save')
            ->assertRedirect(route('financial-reports.index'));

        $report->refresh();
        expect($report->photo)->toBeNull();
        Storage::disk('public')->assertMissing($oldPhotoPath);
    });
});

describe('Financial Reports Show', function () {
    test('guest cannot access show page', function () {
        $report = FinancialReport::factory()->create();

        $this->get(route('financial-reports.show', $report))
            ->assertRedirect(route('login'));
    });

    test('user cannot view another users report', function () {
        $user = User::factory()->create();
        $otherUser = User::factory()->create();

        $report = FinancialReport::factory()->create([
            'user_id' => $otherUser->id,
        ]);

        $this->actingAs($user)
            ->get(route('financial-reports.show', $report))
            ->assertForbidden();
    });

    test('user can view their own report', function () {
        $user = User::factory()->create();
        $report = FinancialReport::factory()->create([
            'user_id' => $user->id,
            'title' => 'My Report Title',
        ]);

        $this->actingAs($user)
            ->get(route('financial-reports.show', $report))
            ->assertOk()
            ->assertSee('My Report Title');
    });

    test('user can delete report from show page', function () {
        $user = User::factory()->create();
        $report = FinancialReport::factory()->create([
            'user_id' => $user->id,
        ]);

        Volt::actingAs($user)
            ->test('financial-reports.show', ['report' => $report])
            ->call('delete')
            ->assertRedirect(route('financial-reports.index'));

        $this->assertDatabaseMissing('financial_reports', ['id' => $report->id]);
    });
});

describe('Financial Report Model', function () {
    test('it has correct types', function () {
        $types = FinancialReport::types();

        expect($types)->toHaveKeys(['income', 'expense']);
    });

    test('it has correct categories', function () {
        $categories = FinancialReport::categories();

        expect($categories)->toHaveKeys(['operational', 'salary', 'utilities', 'marketing', 'sales', 'investment', 'other']);
    });

    test('isIncome returns correct value', function () {
        $income = FinancialReport::factory()->income()->make();
        $expense = FinancialReport::factory()->expense()->make();

        expect($income->isIncome())->toBeTrue();
        expect($expense->isIncome())->toBeFalse();
    });

    test('isExpense returns correct value', function () {
        $income = FinancialReport::factory()->income()->make();
        $expense = FinancialReport::factory()->expense()->make();

        expect($income->isExpense())->toBeFalse();
        expect($expense->isExpense())->toBeTrue();
    });

    test('formatted_amount returns correct format', function () {
        $report = FinancialReport::factory()->make(['amount' => 1500000]);

        expect($report->formatted_amount)->toBe('Rp 1.500.000');
    });

    test('it belongs to a user', function () {
        $user = User::factory()->create();
        $report = FinancialReport::factory()->create(['user_id' => $user->id]);

        expect($report->user)->toBeInstanceOf(User::class);
        expect($report->user->id)->toBe($user->id);
    });
});
