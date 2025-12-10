<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class FinancialReport extends Model
{
    /** @use HasFactory<\Database\Factories\FinancialReportFactory> */
    use HasFactory;

    /**
     * The attributes that are mass assignable.
     *
     * @var list<string>
     */
    protected $fillable = [
        'user_id',
        'title',
        'description',
        'type',
        'amount',
        'report_date',
        'category',
        'photo',
    ];

    /**
     * Get the attributes that should be cast.
     *
     * @return array<string, string>
     */
    protected function casts(): array
    {
        return [
            'amount' => 'decimal:2',
            'report_date' => 'date',
        ];
    }

    /**
     * Get the user that owns the financial report.
     */
    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }

    /**
     * Check if the report is an income type.
     */
    public function isIncome(): bool
    {
        return $this->type === 'income';
    }

    /**
     * Check if the report is an expense type.
     */
    public function isExpense(): bool
    {
        return $this->type === 'expense';
    }

    /**
     * Get the formatted amount with currency.
     */
    public function getFormattedAmountAttribute(): string
    {
        return 'Rp ' . number_format($this->amount, 0, ',', '.');
    }

    /**
     * Get available report types.
     *
     * @return array<string, string>
     */
    public static function types(): array
    {
        return [
            'income' => 'Pemasukan',
            'expense' => 'Pengeluaran',
        ];
    }

    /**
     * Get available categories.
     *
     * @return array<string, string>
     */
    public static function categories(): array
    {
        return [
            'operational' => 'Operasional',
            'salary' => 'Gaji',
            'utilities' => 'Utilitas',
            'marketing' => 'Marketing',
            'sales' => 'Penjualan',
            'investment' => 'Investasi',
            'other' => 'Lainnya',
        ];
    }
}
