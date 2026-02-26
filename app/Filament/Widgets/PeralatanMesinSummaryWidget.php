<?php

namespace App\Filament\Widgets;

use App\Models\PeralatanMesin;
use App\Models\Aset\SubSubKelompok;
use App\Models\Aset\SubKelompok;
use App\Models\Aset\Kelompok;
use Filament\Widgets\StatsOverviewWidget as BaseWidget;
use Filament\Widgets\StatsOverviewWidget\Stat;
use Illuminate\Support\Facades\DB;

class PeralatanMesinSummaryWidget extends BaseWidget
{
    protected static ?string $pollingInterval = null;
    protected static ?int $sort = 6;
    protected int | string | array $columnSpan = [
        'default' => 1,
        'md' => 4,
        'lg' => 12,
    ];

    protected function getColumns(): int
    {
        return 3;
    }
    protected function getStats(): array
    {
        // Get count of equipment and machinery
        $totalCount = PeralatanMesin::count();
        
        
        // Get total value
        $totalValue = PeralatanMesin::sum('harga');
        
        // Get average value
        $averageValue = $totalCount > 0 ? $totalValue / $totalCount : 0;

        return [
            Stat::make('Total Peralatan & Mesin', number_format($totalCount))
                ->description('Seluruh peralatan dan mesin')
                ->descriptionIcon('heroicon-m-truck')
                ->color('primary'),
            
            Stat::make('Total Nilai Aset', 'Rp ' . number_format($totalValue))
                ->description('Nilai total peralatan dan mesin')
                ->descriptionIcon('heroicon-m-currency-dollar')
                ->color('info'),
            
            Stat::make('Rata-rata Nilai', 'Rp ' . number_format($averageValue))
                ->description('Nilai rata-rata per item')
                ->descriptionIcon('heroicon-m-calculator')
                ->color('success'),
        ];
    }
} 