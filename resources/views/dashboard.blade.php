<?php

use App\Models\FinancialReport;
use Illuminate\Support\Facades\Auth;
use Livewire\Volt\Component;

new class extends Component {
    public function with(): array
    {
        $userId = Auth::id();
        $currentMonth = now()->startOfMonth();
        $lastMonth = now()->subMonth()->startOfMonth();

        // Current month stats
        $currentMonthIncome = FinancialReport::where('user_id', $userId)
            ->where('type', 'income')
            ->whereMonth('report_date', now()->month)
            ->whereYear('report_date', now()->year)
            ->sum('amount');

        $currentMonthExpense = FinancialReport::where('user_id', $userId)
            ->where('type', 'expense')
            ->whereMonth('report_date', now()->month)
            ->whereYear('report_date', now()->year)
            ->sum('amount');

        // Last month stats for comparison
        $lastMonthIncome = FinancialReport::where('user_id', $userId)
            ->where('type', 'income')
            ->whereMonth('report_date', now()->subMonth()->month)
            ->whereYear('report_date', now()->subMonth()->year)
            ->sum('amount');

        $lastMonthExpense = FinancialReport::where('user_id', $userId)
            ->where('type', 'expense')
            ->whereMonth('report_date', now()->subMonth()->month)
            ->whereYear('report_date', now()->subMonth()->year)
            ->sum('amount');

        // Calculate percentage changes
        $incomeChange = $lastMonthIncome > 0
            ? round((($currentMonthIncome - $lastMonthIncome) / $lastMonthIncome) * 100, 1)
            : ($currentMonthIncome > 0 ? 100 : 0);

        $expenseChange = $lastMonthExpense > 0
            ? round((($currentMonthExpense - $lastMonthExpense) / $lastMonthExpense) * 100, 1)
            : ($currentMonthExpense > 0 ? 100 : 0);

        // Total balance (all time)
        $totalIncome = FinancialReport::where('user_id', $userId)->where('type', 'income')->sum('amount');
        $totalExpense = FinancialReport::where('user_id', $userId)->where('type', 'expense')->sum('amount');
        $balance = $totalIncome - $totalExpense;

        // Recent reports
        $recentReports = FinancialReport::where('user_id', $userId)
            ->orderBy('report_date', 'desc')
            ->orderBy('created_at', 'desc')
            ->limit(5)
            ->get();

        // Monthly summary (last 6 months)
        $monthlySummary = collect();
        for ($i = 5; $i >= 0; $i--) {
            $date = now()->subMonths($i);
            $monthIncome = FinancialReport::where('user_id', $userId)
                ->where('type', 'income')
                ->whereMonth('report_date', $date->month)
                ->whereYear('report_date', $date->year)
                ->sum('amount');
            $monthExpense = FinancialReport::where('user_id', $userId)
                ->where('type', 'expense')
                ->whereMonth('report_date', $date->month)
                ->whereYear('report_date', $date->year)
                ->sum('amount');

            $monthlySummary->push([
                'month' => $date->translatedFormat('M Y'),
                'income' => $monthIncome,
                'expense' => $monthExpense,
                'balance' => $monthIncome - $monthExpense,
            ]);
        }

        // Category breakdown (current month)
        $categoryBreakdown = FinancialReport::where('user_id', $userId)
            ->whereMonth('report_date', now()->month)
            ->whereYear('report_date', now()->year)
            ->selectRaw('category, type, SUM(amount) as total')
            ->groupBy('category', 'type')
            ->orderByDesc('total')
            ->get();

        return [
            'currentMonthIncome' => $currentMonthIncome,
            'currentMonthExpense' => $currentMonthExpense,
            'incomeChange' => $incomeChange,
            'expenseChange' => $expenseChange,
            'balance' => $balance,
            'recentReports' => $recentReports,
            'monthlySummary' => $monthlySummary,
            'categoryBreakdown' => $categoryBreakdown,
            'totalReports' => FinancialReport::where('user_id', $userId)->count(),
        ];
    }
}; ?>

<x-layouts.app :title="__('Dashboard')">
    @volt('dashboard')
    <div class="flex h-full w-full flex-1 flex-col gap-6">
        {{-- Summary Cards --}}
        <div class="grid gap-4 md:grid-cols-4">
            {{-- Total Balance --}}
            <div class="rounded-xl border border-neutral-200 bg-gradient-to-br from-blue-50 to-blue-100 p-6 dark:border-neutral-700 dark:from-blue-950 dark:to-blue-900">
                <div class="flex items-center justify-between">
                    <flux:icon name="wallet" class="size-8 text-blue-600 dark:text-blue-400" />
                </div>
                <div class="mt-4">
                    <flux:text class="text-sm text-blue-600 dark:text-blue-400">{{ __('Saldo Total') }}</flux:text>
                    <div class="mt-1 text-2xl font-bold {{ $balance >= 0 ? 'text-blue-700 dark:text-blue-300' : 'text-red-600 dark:text-red-400' }}">
                        Rp {{ number_format($balance, 0, ',', '.') }}
                    </div>
                </div>
            </div>

            {{-- Current Month Income --}}
            <div class="rounded-xl border border-neutral-200 bg-gradient-to-br from-green-50 to-green-100 p-6 dark:border-neutral-700 dark:from-green-950 dark:to-green-900">
                <div class="flex items-center justify-between">
                    <flux:icon name="arrow-trending-up" class="size-8 text-green-600 dark:text-green-400" />
                    @if ($incomeChange != 0)
                        <span class="flex items-center text-xs {{ $incomeChange > 0 ? 'text-green-600' : 'text-red-600' }}">
                            <flux:icon name="{{ $incomeChange > 0 ? 'arrow-up' : 'arrow-down' }}" variant="mini" class="size-3" />
                            {{ abs($incomeChange) }}%
                        </span>
                    @endif
                </div>
                <div class="mt-4">
                    <flux:text class="text-sm text-green-600 dark:text-green-400">{{ __('Pemasukan Bulan Ini') }}</flux:text>
                    <div class="mt-1 text-2xl font-bold text-green-700 dark:text-green-300">
                        Rp {{ number_format($currentMonthIncome, 0, ',', '.') }}
                    </div>
                </div>
            </div>

            {{-- Current Month Expense --}}
            <div class="rounded-xl border border-neutral-200 bg-gradient-to-br from-red-50 to-red-100 p-6 dark:border-neutral-700 dark:from-red-950 dark:to-red-900">
                <div class="flex items-center justify-between">
                    <flux:icon name="arrow-trending-down" class="size-8 text-red-600 dark:text-red-400" />
                    @if ($expenseChange != 0)
                        <span class="flex items-center text-xs {{ $expenseChange < 0 ? 'text-green-600' : 'text-red-600' }}">
                            <flux:icon name="{{ $expenseChange > 0 ? 'arrow-up' : 'arrow-down' }}" variant="mini" class="size-3" />
                            {{ abs($expenseChange) }}%
                        </span>
                    @endif
                </div>
                <div class="mt-4">
                    <flux:text class="text-sm text-red-600 dark:text-red-400">{{ __('Pengeluaran Bulan Ini') }}</flux:text>
                    <div class="mt-1 text-2xl font-bold text-red-700 dark:text-red-300">
                        Rp {{ number_format($currentMonthExpense, 0, ',', '.') }}
                    </div>
                </div>
            </div>

            {{-- Total Reports --}}
            <div class="rounded-xl border border-neutral-200 bg-gradient-to-br from-purple-50 to-purple-100 p-6 dark:border-neutral-700 dark:from-purple-950 dark:to-purple-900">
                <div class="flex items-center justify-between">
                    <flux:icon name="document-text" class="size-8 text-purple-600 dark:text-purple-400" />
                </div>
                <div class="mt-4">
                    <flux:text class="text-sm text-purple-600 dark:text-purple-400">{{ __('Total Laporan') }}</flux:text>
                    <div class="mt-1 text-2xl font-bold text-purple-700 dark:text-purple-300">
                        {{ number_format($totalReports, 0, ',', '.') }}
                    </div>
                </div>
            </div>
        </div>

        <div class="grid gap-6 lg:grid-cols-3">
            {{-- Monthly Summary Table --}}
            <div class="lg:col-span-2 rounded-xl border border-neutral-200 bg-white p-6 dark:border-neutral-700 dark:bg-neutral-800">
                <div class="mb-4 flex items-center justify-between">
                    <flux:heading size="lg">{{ __('Ringkasan 6 Bulan Terakhir') }}</flux:heading>
                </div>
                <div class="overflow-x-auto">
                    <table class="w-full text-sm">
                        <thead>
                            <tr class="border-b border-neutral-200 dark:border-neutral-700">
                                <th class="py-3 text-left font-medium">{{ __('Bulan') }}</th>
                                <th class="py-3 text-right font-medium text-green-600 dark:text-green-400">{{ __('Pemasukan') }}</th>
                                <th class="py-3 text-right font-medium text-red-600 dark:text-red-400">{{ __('Pengeluaran') }}</th>
                                <th class="py-3 text-right font-medium">{{ __('Saldo') }}</th>
                            </tr>
                        </thead>
                        <tbody>
                            @foreach ($monthlySummary as $month)
                                <tr class="border-b border-neutral-100 dark:border-neutral-700/50">
                                    <td class="py-3 font-medium">{{ $month['month'] }}</td>
                                    <td class="py-3 text-right text-green-600 dark:text-green-400">
                                        Rp {{ number_format($month['income'], 0, ',', '.') }}
                                    </td>
                                    <td class="py-3 text-right text-red-600 dark:text-red-400">
                                        Rp {{ number_format($month['expense'], 0, ',', '.') }}
                                    </td>
                                    <td class="py-3 text-right font-medium {{ $month['balance'] >= 0 ? 'text-blue-600 dark:text-blue-400' : 'text-red-600 dark:text-red-400' }}">
                                        Rp {{ number_format($month['balance'], 0, ',', '.') }}
                                    </td>
                                </tr>
                            @endforeach
                        </tbody>
                    </table>
                </div>
            </div>

            {{-- Category Breakdown --}}
            <div class="rounded-xl border border-neutral-200 bg-white p-6 dark:border-neutral-700 dark:bg-neutral-800">
                <div class="mb-4">
                    <flux:heading size="lg">{{ __('Kategori Bulan Ini') }}</flux:heading>
                </div>
                @if ($categoryBreakdown->isEmpty())
                    <div class="flex flex-col items-center justify-center py-8 text-center">
                        <flux:icon name="chart-pie" class="size-12 text-neutral-300 dark:text-neutral-600" />
                        <flux:text class="mt-2 text-neutral-500">{{ __('Belum ada data') }}</flux:text>
                    </div>
                @else
                    <div class="space-y-3">
                        @foreach ($categoryBreakdown->take(6) as $category)
                            <div class="flex items-center justify-between">
                                <div class="flex items-center gap-2">
                                    <div class="size-3 rounded-full {{ $category->type === 'income' ? 'bg-green-500' : 'bg-red-500' }}"></div>
                                    <span class="text-sm">{{ $category->category ?? '-' }}</span>
                                </div>
                                <span class="text-sm font-medium {{ $category->type === 'income' ? 'text-green-600 dark:text-green-400' : 'text-red-600 dark:text-red-400' }}">
                                    Rp {{ number_format($category->total, 0, ',', '.') }}
                                </span>
                            </div>
                        @endforeach
                    </div>
                @endif
            </div>
        </div>

        {{-- Recent Reports --}}
        <div class="rounded-xl border border-neutral-200 bg-white p-6 dark:border-neutral-700 dark:bg-neutral-800">
            <div class="mb-4 flex items-center justify-between">
                <flux:heading size="lg">{{ __('Laporan Terbaru') }}</flux:heading>
                <flux:button variant="ghost" size="sm" :href="route('financial-reports.index')" wire:navigate>
                    {{ __('Lihat Semua') }}
                </flux:button>
            </div>

            @if ($recentReports->isEmpty())
                <div class="flex flex-col items-center justify-center py-12 text-center">
                    <flux:icon name="document-plus" class="size-16 text-neutral-300 dark:text-neutral-600" />
                    <flux:heading size="lg" class="mt-4">{{ __('Belum ada laporan') }}</flux:heading>
                    <flux:text class="mt-2 text-neutral-500">{{ __('Mulai dengan menambahkan laporan keuangan pertama Anda') }}</flux:text>
                    <flux:button variant="primary" :href="route('financial-reports.create')" class="mt-4" wire:navigate>
                        {{ __('Tambah Laporan') }}
                    </flux:button>
                </div>
            @else
                <div class="overflow-x-auto">
                    <table class="w-full text-sm">
                        <thead>
                            <tr class="border-b border-neutral-200 dark:border-neutral-700">
                                <th class="py-3 text-left font-medium">{{ __('Tanggal') }}</th>
                                <th class="py-3 text-left font-medium">{{ __('Judul') }}</th>
                                <th class="py-3 text-left font-medium">{{ __('Tipe') }}</th>
                                <th class="py-3 text-left font-medium">{{ __('Kategori') }}</th>
                                <th class="py-3 text-right font-medium">{{ __('Jumlah') }}</th>
                            </tr>
                        </thead>
                        <tbody>
                            @foreach ($recentReports as $report)
                                <tr class="border-b border-neutral-100 hover:bg-neutral-50 dark:border-neutral-700/50 dark:hover:bg-neutral-700/50">
                                    <td class="py-3 text-neutral-600 dark:text-neutral-400">
                                        {{ $report->report_date->format('d M Y') }}
                                    </td>
                                    <td class="py-3">
                                        <a href="{{ route('financial-reports.show', $report) }}" class="font-medium hover:text-blue-600 dark:hover:text-blue-400" wire:navigate>
                                            {{ Str::limit($report->title, 30) }}
                                        </a>
                                    </td>
                                    <td class="py-3">
                                        <flux:badge size="sm" :color="$report->type === 'income' ? 'green' : 'red'">
                                            {{ $report->type === 'income' ? __('Pemasukan') : __('Pengeluaran') }}
                                        </flux:badge>
                                    </td>
                                    <td class="py-3 text-neutral-600 dark:text-neutral-400">
                                        {{ $report->category ?? '-' }}
                                    </td>
                                    <td class="py-3 text-right font-medium {{ $report->type === 'income' ? 'text-green-600 dark:text-green-400' : 'text-red-600 dark:text-red-400' }}">
                                        {{ $report->type === 'income' ? '+' : '-' }} Rp {{ number_format($report->amount, 0, ',', '.') }}
                                    </td>
                                </tr>
                            @endforeach
                        </tbody>
                    </table>
                </div>
            @endif
        </div>
    </div>
    @endvolt
</x-layouts.app>
