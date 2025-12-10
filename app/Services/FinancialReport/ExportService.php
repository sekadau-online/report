<?php

declare(strict_types=1);

namespace App\Services\FinancialReport;

use App\Models\FinancialReport;
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\Storage;
use ZipArchive;

class ExportService
{
    protected string $tempDir;

    public function __construct()
    {
        $this->tempDir = storage_path('app/temp/exports');
    }

    /**
     * Export financial reports for a user.
     *
     * @param  int  $userId
     * @param  string  $format  'sql' or 'json'
     * @param  bool  $includePhotos
     * @param  array<string, mixed>|null  $filters
     * @return string Path to the exported file
     */
    public function export(
        int $userId,
        string $format = 'json',
        bool $includePhotos = true,
        ?array $filters = null
    ): string {
        $reports = $this->getReports($userId, $filters);

        if ($reports->isEmpty()) {
            throw new \RuntimeException('Tidak ada data untuk diekspor.');
        }

        $this->ensureTempDirectory();

        $timestamp = now()->format('Y-m-d_His');
        $baseFilename = "financial_reports_{$timestamp}";

        if ($includePhotos && $this->hasPhotos($reports)) {
            return $this->exportWithPhotos($reports, $format, $baseFilename);
        }

        return $this->exportDataOnly($reports, $format, $baseFilename);
    }

    /**
     * Get reports based on user and filters.
     *
     * @return Collection<int, FinancialReport>
     */
    protected function getReports(int $userId, ?array $filters = null): Collection
    {
        $query = FinancialReport::where('user_id', $userId);

        if ($filters) {
            if (! empty($filters['type'])) {
                $query->where('type', $filters['type']);
            }
            if (! empty($filters['category'])) {
                $query->where('category', $filters['category']);
            }
            if (! empty($filters['date_from'])) {
                $query->where('report_date', '>=', $filters['date_from']);
            }
            if (! empty($filters['date_to'])) {
                $query->where('report_date', '<=', $filters['date_to']);
            }
        }

        return $query->orderBy('report_date', 'desc')->get();
    }

    /**
     * Check if any reports have photos.
     *
     * @param  Collection<int, FinancialReport>  $reports
     */
    protected function hasPhotos(Collection $reports): bool
    {
        return $reports->whereNotNull('photo')->isNotEmpty();
    }

    /**
     * Export data with photos as a ZIP file.
     *
     * @param  Collection<int, FinancialReport>  $reports
     */
    protected function exportWithPhotos(Collection $reports, string $format, string $baseFilename): string
    {
        $zipPath = $this->tempDir . '/' . $baseFilename . '.zip';

        $zip = new ZipArchive;
        if ($zip->open($zipPath, ZipArchive::CREATE | ZipArchive::OVERWRITE) !== true) {
            throw new \RuntimeException('Tidak dapat membuat file ZIP.');
        }

        // Create photos directory in zip
        $zip->addEmptyDir('photos');

        // Prepare data with remapped photo paths
        $exportData = $this->prepareExportData($reports, true);

        // Add photos to ZIP
        foreach ($reports as $report) {
            if ($report->photo && Storage::disk('public')->exists($report->photo)) {
                $photoContent = Storage::disk('public')->get($report->photo);
                $photoFilename = 'photos/' . basename($report->photo);
                $zip->addFromString($photoFilename, $photoContent);
            }
        }

        // Add data file
        $dataContent = $this->formatData($exportData, $format);
        $dataFilename = 'data.' . ($format === 'sql' ? 'sql' : 'json');
        $zip->addFromString($dataFilename, $dataContent);

        // Add metadata
        $metadata = $this->generateMetadata($reports, $format, true);
        $zip->addFromString('metadata.json', json_encode($metadata, JSON_PRETTY_PRINT | JSON_UNESCAPED_UNICODE));

        $zip->close();

        return $zipPath;
    }

    /**
     * Export data only without photos.
     *
     * @param  Collection<int, FinancialReport>  $reports
     */
    protected function exportDataOnly(Collection $reports, string $format, string $baseFilename): string
    {
        $exportData = $this->prepareExportData($reports, false);
        $content = $this->formatData($exportData, $format);

        $extension = $format === 'sql' ? 'sql' : 'json';
        $filePath = $this->tempDir . '/' . $baseFilename . '.' . $extension;

        file_put_contents($filePath, $content);

        return $filePath;
    }

    /**
     * Prepare export data from reports.
     *
     * @param  Collection<int, FinancialReport>  $reports
     * @return array<int, array<string, mixed>>
     */
    protected function prepareExportData(Collection $reports, bool $remapPhotos): array
    {
        return $reports->map(function (FinancialReport $report) use ($remapPhotos) {
            $data = [
                'title' => $report->title,
                'description' => $report->description,
                'type' => $report->type,
                'amount' => $report->amount,
                'report_date' => $report->report_date->format('Y-m-d'),
                'category' => $report->category,
                'photo' => null,
                'created_at' => $report->created_at->toISOString(),
                'updated_at' => $report->updated_at->toISOString(),
            ];

            if ($report->photo) {
                $data['photo'] = $remapPhotos ? 'photos/' . basename($report->photo) : $report->photo;
            }

            return $data;
        })->values()->toArray();
    }

    /**
     * Format data based on export format.
     *
     * @param  array<int, array<string, mixed>>  $data
     */
    protected function formatData(array $data, string $format): string
    {
        if ($format === 'sql') {
            return $this->formatAsSql($data);
        }

        return $this->formatAsJson($data);
    }

    /**
     * Format data as SQL INSERT statements.
     *
     * @param  array<int, array<string, mixed>>  $data
     */
    protected function formatAsSql(array $data): string
    {
        $sql = "-- Financial Reports Export\n";
        $sql .= "-- Generated at: " . now()->toISOString() . "\n";
        $sql .= "-- Total records: " . count($data) . "\n\n";

        $sql .= "-- Note: user_id will be set during import\n";
        $sql .= "-- Photo paths are relative to the photos directory in the ZIP\n\n";

        foreach ($data as $row) {
            $sql .= "INSERT INTO financial_reports (user_id, title, description, type, amount, report_date, category, photo, created_at, updated_at) VALUES (\n";
            $sql .= "    :user_id,\n";
            $sql .= "    " . $this->sqlQuote($row['title']) . ",\n";
            $sql .= "    " . $this->sqlQuote($row['description']) . ",\n";
            $sql .= "    " . $this->sqlQuote($row['type']) . ",\n";
            $sql .= "    " . $row['amount'] . ",\n";
            $sql .= "    " . $this->sqlQuote($row['report_date']) . ",\n";
            $sql .= "    " . $this->sqlQuote($row['category']) . ",\n";
            $sql .= "    " . $this->sqlQuote($row['photo']) . ",\n";
            $sql .= "    " . $this->sqlQuote($row['created_at']) . ",\n";
            $sql .= "    " . $this->sqlQuote($row['updated_at']) . "\n";
            $sql .= ");\n\n";
        }

        return $sql;
    }

    /**
     * Format data as JSON.
     *
     * @param  array<int, array<string, mixed>>  $data
     */
    protected function formatAsJson(array $data): string
    {
        return json_encode([
            'export_info' => [
                'generated_at' => now()->toISOString(),
                'total_records' => count($data),
                'version' => '1.0',
            ],
            'data' => $data,
        ], JSON_PRETTY_PRINT | JSON_UNESCAPED_UNICODE);
    }

    /**
     * Generate export metadata.
     *
     * @param  Collection<int, FinancialReport>  $reports
     * @return array<string, mixed>
     */
    protected function generateMetadata(Collection $reports, string $format, bool $includesPhotos): array
    {
        $photoCount = $reports->whereNotNull('photo')->count();

        return [
            'export_version' => '1.0',
            'generated_at' => now()->toISOString(),
            'format' => $format,
            'includes_photos' => $includesPhotos,
            'statistics' => [
                'total_records' => $reports->count(),
                'total_income' => (float) $reports->where('type', 'income')->sum('amount'),
                'total_expense' => (float) $reports->where('type', 'expense')->sum('amount'),
                'photo_count' => $photoCount,
                'date_range' => [
                    'from' => $reports->min('report_date')?->format('Y-m-d'),
                    'to' => $reports->max('report_date')?->format('Y-m-d'),
                ],
            ],
        ];
    }

    /**
     * Quote a value for SQL.
     */
    protected function sqlQuote(?string $value): string
    {
        if ($value === null) {
            return 'NULL';
        }

        return "'" . addslashes($value) . "'";
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
     * Clean up old temporary files.
     */
    public function cleanupTempFiles(int $maxAgeMinutes = 60): void
    {
        if (! is_dir($this->tempDir)) {
            return;
        }

        $files = glob($this->tempDir . '/*');
        $cutoff = time() - ($maxAgeMinutes * 60);

        foreach ($files as $file) {
            if (is_file($file) && filemtime($file) < $cutoff) {
                unlink($file);
            }
        }
    }
}
