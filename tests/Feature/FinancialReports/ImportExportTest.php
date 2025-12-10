<?php

declare(strict_types=1);

use App\Models\FinancialReport;
use App\Models\User;
use App\Services\FinancialReport\ExportService;
use App\Services\FinancialReport\ImportService;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\Storage;

beforeEach(function () {
    Storage::fake('public');
});

describe('Export Service', function () {
    test('exports reports as JSON', function () {
        $user = User::factory()->create();
        FinancialReport::factory()->count(3)->create(['user_id' => $user->id]);

        $exportService = new ExportService;
        $filePath = $exportService->export($user->id, 'json', false);

        expect($filePath)->toEndWith('.json');
        expect(file_exists($filePath))->toBeTrue();

        $content = json_decode(file_get_contents($filePath), true);
        expect($content['data'])->toHaveCount(3);

        unlink($filePath);
    });

    test('exports reports as SQL', function () {
        $user = User::factory()->create();
        FinancialReport::factory()->count(2)->create(['user_id' => $user->id]);

        $exportService = new ExportService;
        $filePath = $exportService->export($user->id, 'sql', false);

        expect($filePath)->toEndWith('.sql');
        expect(file_exists($filePath))->toBeTrue();

        $content = file_get_contents($filePath);
        expect($content)->toContain('INSERT INTO financial_reports');

        unlink($filePath);
    });

    test('exports reports with photos as ZIP', function () {
        $user = User::factory()->create();

        $photo = UploadedFile::fake()->image('receipt.jpg');
        $photoPath = $photo->store('financial-reports', 'public');

        FinancialReport::factory()->create([
            'user_id' => $user->id,
            'photo' => $photoPath,
        ]);

        $exportService = new ExportService;
        $filePath = $exportService->export($user->id, 'json', true);

        expect($filePath)->toEndWith('.zip');
        expect(file_exists($filePath))->toBeTrue();

        $zip = new ZipArchive;
        $zip->open($filePath);

        expect($zip->locateName('data.json'))->not->toBeFalse();
        expect($zip->locateName('metadata.json'))->not->toBeFalse();
        expect($zip->locateName('photos/'))->not->toBeFalse();

        $zip->close();
        unlink($filePath);
    });

    test('applies filters when exporting', function () {
        $user = User::factory()->create();

        FinancialReport::factory()->income()->create(['user_id' => $user->id]);
        FinancialReport::factory()->expense()->create(['user_id' => $user->id]);

        $exportService = new ExportService;
        $filePath = $exportService->export($user->id, 'json', false, ['type' => 'income']);

        $content = json_decode(file_get_contents($filePath), true);
        expect($content['data'])->toHaveCount(1);
        expect($content['data'][0]['type'])->toBe('income');

        unlink($filePath);
    });

    test('throws exception when no data to export', function () {
        $user = User::factory()->create();

        $exportService = new ExportService;

        expect(fn () => $exportService->export($user->id, 'json', false))
            ->toThrow(\RuntimeException::class, 'Tidak ada data untuk diekspor.');
    });

    test('does not include other users data', function () {
        $user1 = User::factory()->create();
        $user2 = User::factory()->create();

        FinancialReport::factory()->create(['user_id' => $user1->id, 'title' => 'User 1 Report']);
        FinancialReport::factory()->create(['user_id' => $user2->id, 'title' => 'User 2 Report']);

        $exportService = new ExportService;
        $filePath = $exportService->export($user1->id, 'json', false);

        $content = json_decode(file_get_contents($filePath), true);
        expect($content['data'])->toHaveCount(1);
        expect($content['data'][0]['title'])->toBe('User 1 Report');

        unlink($filePath);
    });
});

describe('Import Service', function () {
    test('imports from JSON file', function () {
        $user = User::factory()->create();

        $jsonData = json_encode([
            'data' => [
                [
                    'title' => 'Test Import',
                    'description' => 'Test description',
                    'type' => 'income',
                    'amount' => 100000,
                    'report_date' => '2025-01-15',
                    'category' => 'sales',
                    'photo' => null,
                    'created_at' => now()->toISOString(),
                    'updated_at' => now()->toISOString(),
                ],
            ],
        ]);

        $file = UploadedFile::fake()->createWithContent('import.json', $jsonData);

        $importService = new ImportService;
        $result = $importService->import($file, $user->id);

        expect($result['success'])->toBeTrue();
        expect($result['stats']['imported'])->toBe(1);
        expect(FinancialReport::where('user_id', $user->id)->count())->toBe(1);
    });

    test('imports from SQL file', function () {
        $user = User::factory()->create();

        $sqlContent = <<<'SQL'
-- Financial Reports Export
INSERT INTO financial_reports (user_id, title, description, type, amount, report_date, category, photo, created_at, updated_at) VALUES (
    :user_id,
    'SQL Import Test',
    'Test description',
    'expense',
    50000,
    '2025-02-20',
    'operational',
    NULL,
    '2025-01-01T00:00:00.000000Z',
    '2025-01-01T00:00:00.000000Z'
);
SQL;

        $file = UploadedFile::fake()->createWithContent('import.sql', $sqlContent);

        $importService = new ImportService;
        $result = $importService->import($file, $user->id);

        expect($result['success'])->toBeTrue();
        expect($result['stats']['imported'])->toBe(1);

        $report = FinancialReport::where('user_id', $user->id)->first();
        expect($report->title)->toBe('SQL Import Test');
        expect($report->type)->toBe('expense');
    });

    test('imports from ZIP with photos', function () {
        $user = User::factory()->create();

        // Create ZIP with data and photo
        $zipPath = storage_path('app/temp/test_import.zip');
        @mkdir(dirname($zipPath), 0755, true);

        $zip = new ZipArchive;
        $zip->open($zipPath, ZipArchive::CREATE | ZipArchive::OVERWRITE);

        $jsonData = json_encode([
            'data' => [
                [
                    'title' => 'ZIP Import Test',
                    'description' => null,
                    'type' => 'income',
                    'amount' => 200000,
                    'report_date' => '2025-03-10',
                    'category' => 'investment',
                    'photo' => 'photos/test.jpg',
                    'created_at' => now()->toISOString(),
                    'updated_at' => now()->toISOString(),
                ],
            ],
        ]);

        $zip->addFromString('data.json', $jsonData);
        $zip->addEmptyDir('photos');

        // Create a simple fake image
        $imageContent = base64_decode('iVBORw0KGgoAAAANSUhEUgAAAAEAAAABCAYAAAAfFcSJAAAADUlEQVR42mNk+M9QDwADhgGAWjR9awAAAABJRU5ErkJggg==');
        $zip->addFromString('photos/test.jpg', $imageContent);

        $zip->close();

        $file = new UploadedFile($zipPath, 'test_import.zip', 'application/zip', null, true);

        $importService = new ImportService;
        $result = $importService->import($file, $user->id);

        expect($result['success'])->toBeTrue();
        expect($result['stats']['imported'])->toBe(1);
        expect($result['stats']['photos_imported'])->toBe(1);

        $report = FinancialReport::where('user_id', $user->id)->first();
        expect($report->title)->toBe('ZIP Import Test');
        expect($report->photo)->not->toBeNull();
        Storage::disk('public')->assertExists($report->photo);

        @unlink($zipPath);
    });

    test('handles invalid JSON gracefully', function () {
        $user = User::factory()->create();

        $file = UploadedFile::fake()->createWithContent('invalid.json', 'not valid json');

        $importService = new ImportService;
        $result = $importService->import($file, $user->id);

        expect($result['success'])->toBeFalse();
        expect($result['errors'])->toHaveKey('general');
    });

    test('skips rows with missing required fields', function () {
        $user = User::factory()->create();

        $jsonData = json_encode([
            'data' => [
                [
                    'title' => 'Valid Report',
                    'type' => 'income',
                    'amount' => 100000,
                    'report_date' => '2025-01-15',
                ],
                [
                    'title' => '', // Missing title
                    'type' => 'expense',
                    'amount' => 50000,
                    'report_date' => '2025-01-16',
                ],
            ],
        ]);

        $file = UploadedFile::fake()->createWithContent('import.json', $jsonData);

        $importService = new ImportService;
        $result = $importService->import($file, $user->id);

        expect($result['stats']['imported'])->toBe(1);
        expect($result['stats']['skipped'])->toBe(1);
    });

    test('skips rows with invalid type', function () {
        $user = User::factory()->create();

        $jsonData = json_encode([
            'data' => [
                [
                    'title' => 'Invalid Type Report',
                    'type' => 'invalid_type',
                    'amount' => 100000,
                    'report_date' => '2025-01-15',
                ],
            ],
        ]);

        $file = UploadedFile::fake()->createWithContent('import.json', $jsonData);

        $importService = new ImportService;
        $result = $importService->import($file, $user->id);

        expect($result['stats']['imported'])->toBe(0);
        expect($result['stats']['skipped'])->toBe(1);
    });

    test('validates file extension', function () {
        $file = UploadedFile::fake()->createWithContent('invalid.txt', 'content');
        // Change extension to .txt for testing
        $file = new UploadedFile(
            $file->getRealPath(),
            'invalid.exe',
            'application/octet-stream',
            null,
            true
        );

        $errors = ImportService::validateFile($file);

        expect($errors)->toHaveKey('file');
    });

    test('validates file size', function () {
        // Create a mock for a large file check
        $errors = ImportService::validateFile(
            UploadedFile::fake()->create('large.zip', 60 * 1024) // 60MB
        );

        expect($errors)->toHaveKey('file');
    });

    test('skips duplicate records based on title, type, amount, and date', function () {
        $user = User::factory()->create();

        // Create existing report directly (not with factory to ensure exact values)
        FinancialReport::create([
            'user_id' => $user->id,
            'title' => 'Existing Report',
            'type' => 'income',
            'amount' => 100000,
            'report_date' => '2025-01-15',
            'category' => 'Gaji',
        ]);

        // Import same data + new data
        $jsonData = json_encode([
            'data' => [
                [
                    'title' => 'Existing Report', // Duplicate
                    'type' => 'income',
                    'amount' => 100000,
                    'report_date' => '2025-01-15',
                    'category' => 'sales',
                ],
                [
                    'title' => 'New Report', // New
                    'type' => 'expense',
                    'amount' => 50000,
                    'report_date' => '2025-01-16',
                    'category' => 'operational',
                ],
            ],
        ]);

        $file = UploadedFile::fake()->createWithContent('import.json', $jsonData);

        $importService = new ImportService;
        $result = $importService->import($file, $user->id);

        expect($result['stats']['imported'])->toBe(1); // Only new report
        expect($result['stats']['skipped'])->toBe(1); // Duplicate skipped
        expect(FinancialReport::where('user_id', $user->id)->count())->toBe(2); // 1 existing + 1 new
    });

    test('allows same title with different amount or date', function () {
        $user = User::factory()->create();

        // Create existing report
        FinancialReport::factory()->create([
            'user_id' => $user->id,
            'title' => 'Monthly Report',
            'type' => 'income',
            'amount' => 100000,
            'report_date' => '2025-01-15',
        ]);

        // Import same title but different amount/date
        $jsonData = json_encode([
            'data' => [
                [
                    'title' => 'Monthly Report', // Same title, different date
                    'type' => 'income',
                    'amount' => 100000,
                    'report_date' => '2025-02-15', // Different date
                ],
                [
                    'title' => 'Monthly Report', // Same title, different amount
                    'type' => 'income',
                    'amount' => 150000, // Different amount
                    'report_date' => '2025-01-15',
                ],
            ],
        ]);

        $file = UploadedFile::fake()->createWithContent('import.json', $jsonData);

        $importService = new ImportService;
        $result = $importService->import($file, $user->id);

        expect($result['stats']['imported'])->toBe(2); // Both imported
        expect($result['stats']['skipped'])->toBe(0);
        expect(FinancialReport::where('user_id', $user->id)->count())->toBe(3); // 1 existing + 2 new
    });
});

describe('Export/Import Integration', function () {
    test('can export and reimport data successfully', function () {
        $user = User::factory()->create();

        // Create original reports
        $original = FinancialReport::factory()->count(5)->create([
            'user_id' => $user->id,
        ]);

        // Export
        $exportService = new ExportService;
        $exportPath = $exportService->export($user->id, 'json', false);

        // Delete original data
        FinancialReport::where('user_id', $user->id)->delete();
        expect(FinancialReport::where('user_id', $user->id)->count())->toBe(0);

        // Import
        $file = new UploadedFile($exportPath, 'export.json', 'application/json', null, true);
        $importService = new ImportService;
        $result = $importService->import($file, $user->id);

        expect($result['success'])->toBeTrue();
        expect($result['stats']['imported'])->toBe(5);
        expect(FinancialReport::where('user_id', $user->id)->count())->toBe(5);

        @unlink($exportPath);
    });

    test('exported ZIP can be reimported with photos', function () {
        $user = User::factory()->create();

        // Create report with photo
        $photo = UploadedFile::fake()->image('original.jpg');
        $photoPath = $photo->store('financial-reports', 'public');

        FinancialReport::factory()->create([
            'user_id' => $user->id,
            'title' => 'Photo Report',
            'photo' => $photoPath,
        ]);

        // Export with photos
        $exportService = new ExportService;
        $exportPath = $exportService->export($user->id, 'json', true);

        // Delete original
        Storage::disk('public')->delete($photoPath);
        FinancialReport::where('user_id', $user->id)->delete();

        // Import
        $file = new UploadedFile($exportPath, 'export.zip', 'application/zip', null, true);
        $importService = new ImportService;
        $result = $importService->import($file, $user->id);

        expect($result['success'])->toBeTrue();
        expect($result['stats']['photos_imported'])->toBe(1);

        $report = FinancialReport::where('user_id', $user->id)->first();
        expect($report->photo)->not->toBeNull();
        Storage::disk('public')->assertExists($report->photo);

        @unlink($exportPath);
    });
});
