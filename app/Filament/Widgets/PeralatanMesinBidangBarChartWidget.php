<?php

namespace App\Filament\Widgets;

use App\Models\PeralatanMesin;
use Filament\Widgets\ChartWidget;
use Illuminate\Support\Facades\DB;

class PeralatanMesinBidangBarChartWidget extends ChartWidget
{
    protected static ?string $heading = 'Statistik Nilai & Jumlah Item per Bidang';
    protected static ?int $sort = 4;
    protected static ?string $maxHeight = '350px';
    protected int | string | array $columnSpan = [
        'default' => 1,
        'md' => 4,
        'lg' => 12,
    ];

    protected function getHeaderActions(): array
    {
        return [
            \Filament\Actions\Action::make('detail')
                ->label('Detail')
                ->icon('heroicon-m-eye')
                ->color('info')
                ->modalHeading('Detail Statistik Nilai')
                ->modalContent(function () {
                    return view('filament.widgets.peralatan-mesin-bidang-detail', [
                        'data' => $this->getChartData(),
                    ]);
                })
                ->modalSubmitAction(false)
                ->modalCancelActionLabel('Tutup'),

            \Filament\Actions\Action::make('Lihat Selengkapnya')
                ->url(\App\Filament\Resources\PeralatanMesinResource::getUrl())
                ->icon('heroicon-m-arrow-right')
                ->iconPosition('after')
                ->color('primary')
                ->size('xs'),
        ];
    }

    private function getChartData(): array
    {
        return PeralatanMesin::selectRaw('
                struktur_bidang.bidang as nama_bidang,
                sum(golongan_peralatan_mesin.harga) as total_nilai,
                count(*) as total_item
            ')
            ->join('struktur_sub_bidang', 'golongan_peralatan_mesin.sub_bidang_id', '=', 'struktur_sub_bidang.id')
            ->join('struktur_bidang', 'struktur_sub_bidang.id_bidang', '=', 'struktur_bidang.id')
            ->groupBy('struktur_bidang.bidang')
            ->orderByDesc('total_nilai')
            ->get()
            ->toArray();
    }

    protected function getData(): array
    {
        $data = $this->getChartData();

        $labels = array_column($data, 'nama_bidang');
        $nilai = array_column($data, 'total_nilai');
        $jumlah = array_column($data, 'total_item');

        return [
            'datasets' => [
                [
                    'label' => 'Total Nilai (Rp)',
                    'data' => $nilai,
                    'backgroundColor' => 'rgba(59, 130, 246, 0.8)', // Modern Blue
                    'borderColor' => '#3B82F6',
                    'borderWidth' => 1,
                    'borderRadius' => 8, // Rounded bars
                    'barPercentage' => 0.6,
                ],
                [
                    'label' => 'Jumlah Item',
                    'data' => $jumlah,
                    'backgroundColor' => 'rgba(245, 158, 11, 0.8)', // Modern Amber
                    'borderColor' => '#F59E0B',
                    'borderWidth' => 1,
                    'borderRadius' => 8, // Rounded bars
                    'barPercentage' => 0.6,
                    'yAxisID' => 'jumlah',
                ],
            ],
            'labels' => $labels,
        ];
    }

    protected function getType(): string
    {
        return 'bar';
    }

    protected function getOptions(): array
    {
        return [
            'indexAxis' => 'x', // vertical bar
            'plugins' => [
                'legend' => [
                    'display' => true,
                    'position' => 'top',
                    'labels' => [
                        'usePointStyle' => true,
                        'font' => [
                            'family' => 'Inter', // Modern font
                            'size' => 12,
                        ],
                    ],
                ],
            ],
            'scales' => [
                'y' => [
                    'beginAtZero' => true,
                    'title' => [
                        'display' => true,
                        'text' => 'Total Nilai (Rp)',
                    ],
                    'grid' => [
                        'display' => false, // Cleaner look
                    ],
                    'border' => [
                        'display' => false,
                    ],
                ],
                'jumlah' => [
                    'beginAtZero' => true,
                    'position' => 'right',
                    'title' => [
                        'display' => true,
                        'text' => 'Jumlah Item',
                    ],
                    'grid' => [
                        'drawOnChartArea' => false,
                        'display' => false,
                    ],
                    'border' => [
                        'display' => false,
                    ],
                ],
                'x' => [
                    'grid' => [
                        'display' => false,
                    ],
                    'border' => [
                        'display' => false,
                    ],
                ]
            ],
            'animation' => [
                'duration' => 2000,
                'easing' => 'easeOutQuart',
            ],
        ];
    }
} 