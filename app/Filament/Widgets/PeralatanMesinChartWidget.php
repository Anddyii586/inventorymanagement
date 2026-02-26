<?php

namespace App\Filament\Widgets;

use App\Models\PeralatanMesin;
use Filament\Widgets\ChartWidget;
use Illuminate\Support\Facades\DB;

class PeralatanMesinChartWidget extends ChartWidget
{
    protected static ?string $heading = 'Peralatan & Mesin per Kategori';
    protected static ?int $sort = 4;
    
    protected static ?string $maxHeight = '300px';

    protected int | string | array $columnSpan = [
        'default' => 1,
        'md' => 4,
        'lg' => 12,
    ];

    protected function getHeaderActions(): array
    {
        return [
            \Filament\Actions\Action::make('Lihat Selengkapnya')
                ->url(\App\Filament\Resources\PeralatanMesinResource::getUrl())
                ->icon('heroicon-m-arrow-right')
                ->iconPosition('after')
                ->color('primary')
                ->size('xs'),
        ];
    }


    protected function getData(): array
    {
        // Get data grouped by sub-sub-kelompok (category) using JOIN
        $categoryData = PeralatanMesin::selectRaw('
                golongan_peralatan_mesin.sub_sub_kelompok_id,
                asset_sub_sub_kelompok.sub_sub_kelompok as kategori_nama,
                count(*) as total
            ')
            ->join('asset_sub_sub_kelompok', 'golongan_peralatan_mesin.sub_sub_kelompok_id', '=', 'asset_sub_sub_kelompok.id')
            ->groupBy('golongan_peralatan_mesin.sub_sub_kelompok_id', 'asset_sub_sub_kelompok.sub_sub_kelompok')
            ->orderByDesc('total')
            ->limit(10)
            ->get();

        $labels = [];
        $data = [];

        foreach ($categoryData as $item) {
            $labels[] = $item->kategori_nama;
            $data[] = $item->total;
        }

        return [
            'datasets' => [
                [
                    'label' => 'Jumlah Peralatan & Mesin',
                    'data' => $data,
                    'backgroundColor' => [
                        '#3B82F6', '#8B5CF6', '#F59E0B', '#10B981', '#EF4444',
                        '#EC4899', '#6366F1', '#14B8A6', '#F97316', '#06B6D4'
                    ],
                    'hoverOffset' => 10,
                    'borderWidth' => 0,
                    'borderRadius' => 5,
                    'cutout' => '75%',
                ],
            ],
            'labels' => $labels,
        ];
    }

    protected function getOptions(): array
    {
        return [
            'plugins' => [
                'legend' => [
                    'display' => true,
                    'position' => 'right',
                    'labels' => [
                        'usePointStyle' => true,
                        'pointStyle' => 'circle',
                        'padding' => 20,
                        'font' => [
                            'size' => 12,
                            'family' => 'Inter',
                        ],
                    ],
                ],
            ],
            'scales' => [
                'x' => [
                    'display' => false,
                ],
                'y' => [
                    'display' => false,
                ],
            ],
            'animation' => [
                'animateScale' => true,
                'animateRotate' => true,
            ],
        ];
    }

    protected function getType(): string
    {
        return 'doughnut';
    }
} 