<?php

declare(strict_types=1);

namespace App\Services\FinancialReport;

use App\Models\FinancialReport;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Str;
use ZipArchive;

class ImportService
{
    protected string $tempDir;

    /** @var array<string, string> */
    protected array $errors = [];

    /** @var array<string, int> */
    protected array $stats = [
        'imported' => 0,
        'skipped' => 0,
        'photos_imported' => 0,
    ];

    public function __construct()
    {
        $this->tempDir = storage_path('app/temp/imports');
    }

    /**
     * Import financial reports from file.
     *
     * @param  UploadedFile  $file
     * @param  int  $userId
     * @return array{success: bool, stats: array<string, int>, errors: array<string, string>}
     */
    public function import(UploadedFile $file, int $userId): array
    {
        $this->resetState();
        $this->ensureTempDirectory();

        $extension = strtolower($file->getClientOriginalExtension());

        try {
            DB::transaction(function () use ($file, $userId, $extension) {
                match ($extension) {
                    'zip' => $this->importFromZip($file, $userId),
                    'json' => $this->importFromJson($file, $userId),
                    'sql' => $this->importFromSql($file, $userId),
                    default => throw new \InvalidArgumentException("Format file tidak didukung: {$extension}"),
                };
            });

            return [
                'success' => empty($this->errors),
                'stats' => $this->stats,
                'errors' => $this->errors,
            ];
        } catch (\Throwable $e) {
            $this->cleanupTempFiles();

            return [
                'success' => false,
                'stats' => $this->stats,
                'errors' => ['general' => $e->getMessage()],
            ];
        }
    }

    /**
     * Import from ZIP file.
     */
    protected function importFromZip(UploadedFile $file, int $userId): void
    {
        $extractPath = $this->tempDir . '/' . Str::uuid();
        mkdir($extractPath, 0755, true);

        $zip = new ZipArchive;
        if ($zip->open($file->getRealPath()) !== true) {
            throw new \RuntimeException('Tidak dapat membuka file ZIP.');
        }

        $zip->extractTo($extractPath);
        $zip->close();

        // Find data file
        $dataFile = null;
        $format = null;

        if (file_exists($extractPath . '/data.json')) {
            $dataFile = $extractPath . '/data.json';
            $format = 'json';
        } elseif (file_exists($extractPath . '/data.sql')) {
            $dataFile = $extractPath . '/data.sql';
            $format = 'sql';
        }

        if (! $dataFile) {
            $this->cleanupDirectory($extractPath);
            throw new \RuntimeException('File data tidak ditemukan dalam ZIP.');
        }

        $photosDir = $extractPath . '/photos';
        $hasPhotos = is_dir($photosDir);

        // Parse data
        $data = $format === 'json'
            ? $this->parseJsonFile($dataFile)
            : $this->parseSqlFile($dataFile);

        // Import data
        foreach ($data as $index => $row) {
            $this->importRow($row, $userId, $hasPhotos ? $photosDir : null, $index);
        }

        // Cleanup
        $this->cleanupDirectory($extractPath);
    }

    /**
     * Import from JSON file.
     */
    protected function importFromJson(UploadedFile $file, int $userId): void
    {
        $data = $this->parseJsonFile($file->getRealPath());

        foreach ($data as $index => $row) {
            $this->importRow($row, $userId, null, $index);
        }
    }

    /**
     * Import from SQL file.
     */
    protected function importFromSql(UploadedFile $file, int $userId): void
    {
        $data = $this->parseSqlFile($file->getRealPath());

        foreach ($data as $index => $row) {
            $this->importRow($row, $userId, null, $index);
        }
    }

    /**
     * Parse JSON data file.
     *
     * @return array<int, array<string, mixed>>
     */
    protected function parseJsonFile(string $path): array
    {
        $content = file_get_contents($path);
        $decoded = json_decode($content, true);

        if (json_last_error() !== JSON_ERROR_NONE) {
            throw new \RuntimeException('Format JSON tidak valid: ' . json_last_error_msg());
        }

        // Handle both flat array and wrapped format
        if (isset($decoded['data']) && is_array($decoded['data'])) {
            return $decoded['data'];
        }

        if (isset($decoded[0]) && is_array($decoded[0])) {
            return $decoded;
        }

        throw new \RuntimeException('Struktur data JSON tidak valid.');
    }

    /**
     * Parse SQL data file.
     *
     * @return array<int, array<string, mixed>>
     */
    protected function parseSqlFile(string $path): array
    {
        $content = file_get_contents($path);
        $data = [];

        // Match INSERT statements
        $pattern = '/INSERT INTO\s+financial_reports\s*\([^)]+\)\s*VALUES\s*\(([\s\S]*?)\);/i';

        if (preg_match_all($pattern, $content, $matches)) {
            foreach ($matches[1] as $values) {
                $parsed = $this->parseSqlValues($values);
                if ($parsed) {
                    $data[] = $parsed;
                }
            }
        }

        return $data;
    }

    /**
     * Parse SQL VALUES clause.
     *
     * @return array<string, mixed>|null
     */
    protected function parseSqlValues(string $values): ?array
    {
        // Remove :user_id placeholder and newlines
        $values = preg_replace('/:\w+\s*,?/', '', $values);
        $values = str_replace(["\n", "\r"], ' ', $values);
        $values = trim($values);

        // Parse quoted strings and NULL values
        $parts = [];
        $current = '';
        $inString = false;
        $escape = false;

        for ($i = 0; $i < strlen($values); $i++) {
            $char = $values[$i];

            if ($escape) {
                $current .= $char;
                $escape = false;
                continue;
            }

            if ($char === '\\') {
                $escape = true;
                continue;
            }

            if ($char === "'" && ! $inString) {
                $inString = true;
                continue;
            }

            if ($char === "'" && $inString) {
                $inString = false;
                continue;
            }

            if ($char === ',' && ! $inString) {
                $parts[] = trim($current);
                $current = '';
                continue;
            }

            $current .= $char;
        }

        if (trim($current) !== '') {
            $parts[] = trim($current);
        }

        if (count($parts) < 9) {
            return null;
        }

        $getValue = function ($val) {
            $val = trim($val);
            if (strtoupper($val) === 'NULL') {
                return null;
            }

            return $val;
        };

        return [
            'title' => $getValue($parts[0]),
            'description' => $getValue($parts[1]),
            'type' => $getValue($parts[2]),
            'amount' => (float) $getValue($parts[3]),
            'report_date' => $getValue($parts[4]),
            'category' => $getValue($parts[5]),
            'photo' => $getValue($parts[6]),
            'created_at' => $getValue($parts[7]) ?? now()->toISOString(),
            'updated_at' => $getValue($parts[8]) ?? now()->toISOString(),
        ];
    }

    /**
     * Import a single row.
     *
     * @param  array<string, mixed>  $row
     */
    protected function importRow(array $row, int $userId, ?string $photosDir, int $index): void
    {
        // Validate required fields
        if (empty($row['title']) || empty($row['type']) || ! isset($row['amount']) || empty($row['report_date'])) {
            $this->errors["row_{$index}"] = 'Data tidak lengkap (title, type, amount, report_date diperlukan)';
            $this->stats['skipped']++;

            return;
        }

        // Validate type
        if (! in_array($row['type'], ['income', 'expense'])) {
            $this->errors["row_{$index}"] = "Tipe tidak valid: {$row['type']}";
            $this->stats['skipped']++;

            return;
        }

        // Check for duplicate based on title, type, amount, and report_date
        $amount = round((float) $row['amount'], 2);
        $reportDate = \Carbon\Carbon::parse($row['report_date'])->format('Y-m-d');

        $exists = FinancialReport::where('user_id', $userId)
            ->where('title', $row['title'])
            ->where('type', $row['type'])
            ->whereDate('report_date', $reportDate)
            ->get()
            ->contains(function ($report) use ($amount) {
                return round((float) $report->amount, 2) === $amount;
            });

        if ($exists) {
            $this->stats['skipped']++;

            return;
        }

        // Handle photo
        $photoPath = null;
        if (! empty($row['photo']) && $photosDir) {
            $photoPath = $this->importPhoto($row['photo'], $photosDir);
        }

        // Create record
        FinancialReport::create([
            'user_id' => $userId,
            'title' => $row['title'],
            'description' => $row['description'] ?? null,
            'type' => $row['type'],
            'amount' => (float) $row['amount'],
            'report_date' => $row['report_date'],
            'category' => $row['category'] ?? null,
            'photo' => $photoPath,
        ]);

        $this->stats['imported']++;

        if ($photoPath) {
            $this->stats['photos_imported']++;
        }
    }

    /**
     * Import photo from extracted ZIP.
     */
    protected function importPhoto(string $relativePath, string $photosDir): ?string
    {
        // Get filename from relative path
        $filename = basename($relativePath);
        $sourcePath = $photosDir . '/' . $filename;

        if (! file_exists($sourcePath)) {
            return null;
        }

        // Generate new filename
        $extension = pathinfo($filename, PATHINFO_EXTENSION);
        $newFilename = 'financial-reports/' . Str::uuid() . '.' . $extension;

        // Copy to storage
        Storage::disk('public')->put($newFilename, file_get_contents($sourcePath));

        return $newFilename;
    }

    /**
     * Reset import state.
     */
    protected function resetState(): void
    {
        $this->errors = [];
        $this->stats = [
            'imported' => 0,
            'skipped' => 0,
            'photos_imported' => 0,
        ];
    }

    /**
     * Ensure temp directory exists.
     */
    protected function ensureTempDirectory(): void
    {
        if (! is_dir($this->tempDir)) {
            mkdir($this->tempDir, 0755, true);
        }
    }

    /**
     * Clean up a directory recursively.
     */
    protected function cleanupDirectory(string $dir): void
    {
        if (! is_dir($dir)) {
            return;
        }

        $files = new \RecursiveIteratorIterator(
            new \RecursiveDirectoryIterator($dir, \RecursiveDirectoryIterator::SKIP_DOTS),
            \RecursiveIteratorIterator::CHILD_FIRST
        );

        foreach ($files as $file) {
            if ($file->isDir()) {
                rmdir($file->getRealPath());
            } else {
                unlink($file->getRealPath());
            }
        }

        rmdir($dir);
    }

    /**
     * Clean up temp files.
     */
    public function cleanupTempFiles(): void
    {
        if (is_dir($this->tempDir)) {
            $this->cleanupDirectory($this->tempDir);
        }
    }

    /**
     * Validate import file.
     *
     * @return array<string, string>
     */
    public static function validateFile(UploadedFile $file): array
    {
        $errors = [];

        $allowedExtensions = ['zip', 'json', 'sql'];
        $extension = strtolower($file->getClientOriginalExtension());

        if (! in_array($extension, $allowedExtensions)) {
            $errors['file'] = 'Format file harus ZIP, JSON, atau SQL.';
        }

        // Max 50MB
        if ($file->getSize() > 50 * 1024 * 1024) {
            $errors['file'] = 'Ukuran file maksimal 50MB.';
        }

        return $errors;
    }
}
