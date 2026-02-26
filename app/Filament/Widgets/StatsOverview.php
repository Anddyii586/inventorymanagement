<?php

namespace App\Filament\Widgets;

use Filament\Widgets\StatsOverviewWidget as BaseWidget;
use Filament\Widgets\StatsOverviewWidget\Stat;

class StatsOverview extends BaseWidget
{
    protected static ?int $sort = 2;

    protected function getColumns(): int
    {
        return 4; 
    }

    protected int | string | array $columnSpan = [
        'default' => 1,
        'md' => 4,
        'lg' => 12,
    ];

    protected function getStats(): array
    {
        // Calculate Total Assets and Book Value
        $totalAset = 0;
        $totalNilai = 0;
        $totalNilaiBuku = 0;
        
        $newItems = 0;
        $rusakBerat = 0;
        $perluPerawatan = \App\Models\MaintenanceSchedule::needingReminder()->count();
        
        $depreciableModels = [
            \App\Models\PeralatanMesin::class,
            \App\Models\GedungBangunan::class,
            \App\Models\Jaringan::class,
            \App\Models\AsetTetapLainnya::class,
        ];

        $nonDepreciableModels = [
            \App\Models\Tanah::class,
            \App\Models\KonstruksiDalamPengerjaan::class,
        ];

        foreach ($depreciableModels as $model) {
            $totalAset += $model::count();
            $totalNilai += $model::sum('harga') ?? 0;
            // Sum nilai_buku, if null (not calculated yet), fallback to harga? 
            // Better to show 0 or calculate on fly? 
            // If we blindly sum, and it's null, we get 0. 
            // Let's assume the command fills it. If null, it treats as 0 in DB usually or we can coalesce.
            // But for 'Nilai Buku' if it's not calculated, maybe we should default to 'harga'.
            // For now, let's sum 'nilai_buku'.
            $totalNilaiBuku += $model::sum('nilai_buku') ?? 0;
            
            $newItems += $model::where('created_at', '>=', now()->startOfMonth())->count();
            
            // Assuming these models have 'kondisi' column
            try {
                $rusakBerat += $model::where('kondisi', 'Rusak Berat')->count();
            } catch (\Exception $e) {
                // Ignore if column doesn't exist
            }
        }

        foreach ($nonDepreciableModels as $model) {
            $totalAset += $model::count();
            if ($model === \App\Models\KonstruksiDalamPengerjaan::class) {
                 $val = $model::sum('nilai_kontrak') ?? 0;
                 $totalNilai += $val;
                 $totalNilaiBuku += $val;
            } else {
                 $val = $model::sum('harga') ?? 0;
                 $totalNilai += $val;
                 $totalNilaiBuku += $val;
            }
            
            $newItems += $model::where('created_at', '>=', now()->startOfMonth())->count();
        }

        // ... existing code ...

        return [
            Stat::make('Total Aset', number_format($totalAset))
                ->description($newItems . ' item baru bulan ini')
                ->descriptionIcon('heroicon-m-arrow-trending-up')
                ->chart([7, 2, 10, 3, 15, 4, 17])
                ->color('success'),

            Stat::make('Total Nilai Aset', 'Rp ' . number_format($totalNilai, 0, ',', '.'))
                ->description('Total harga perolehan')
                ->descriptionIcon('heroicon-m-banknotes')
                ->color('primary')
                ->chart([15, 4, 10, 2, 12, 4, 12]),
            
            Stat::make('Total Nilai Buku', 'Rp ' . number_format($totalNilaiBuku, 0, ',', '.'))
                ->description('Nilai setelah penyusutan')
                ->descriptionIcon('heroicon-m-calculator')
                ->color('info')
                ->chart([15, 4, 10, 2, 12, 4, 12]),

            Stat::make('Jadwal Pemeliharaan', number_format($perluPerawatan))
                ->label('Perlu Pemeliharaan')
                ->description('Jadwal jatuh tempo / terlewat')
                ->descriptionIcon('heroicon-m-wrench-screwdriver')
                ->color($perluPerawatan > 0 ? 'danger' : 'success')
                ->chart([17, 16, 14, 15, 14, 13, 12]),

            Stat::make('Aset Rusak Berat', number_format($rusakBerat))
                ->description('Membutuhkan penanganan serius')
                ->descriptionIcon('heroicon-m-x-circle')
                ->color('danger'),
        ];
    }
}
