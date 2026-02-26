<?php

namespace App\Filament\Pages;

use Filament\Pages\Dashboard as BaseDashboard;
use App\Filament\Widgets\PeralatanMesinChartWidget;
use App\Filament\Widgets\PeralatanMesinTableWidget;
use App\Filament\Widgets\PeralatanMesinSummaryWidget;
use App\Filament\Widgets\PeralatanMesinBidangBarChartWidget;
use App\Filament\Widgets\ManualBookDownloadWidget;


class Dashboard extends BaseDashboard
{
    protected static ?string $navigationLabel = 'Beranda';
    protected static ?string $navigationIcon = 'heroicon-o-home';
    protected static ?string $navigationTooltip = 'Beranda';

    protected static string $view = 'filament.pages.dashboard';

    
    public function getHeading(): string
    {
        return 'Selamat Datang Di Aset PTAMGM, ' . auth()->user()->nama;
    }

    public function getHeaderWidgets(): array
    {
        return [
            ManualBookDownloadWidget::class,

            \App\Filament\Widgets\StatsOverview::class,
            PeralatanMesinSummaryWidget::class,
            PeralatanMesinChartWidget::class,
            PeralatanMesinBidangBarChartWidget::class,
        ]; 
    }
public function getColumns(): int | array
{
    return [
        'default' => 1,
        'lg' => 5,
    ];
}
    public function getFooterWidgets(): array
    {
        return [
            PeralatanMesinTableWidget::class,
        ];
    }

    public function getHeaderWidgetsColumns(): int | array
    {
        return [
            'default' => 1,
            'md' => 5,
            'lg' => 5,
        ];
    }


    public function getFooterWidgetsColumns(): int | array
    {
        return [
            'default' => 1,
            'md' => 2,
            'xl' => 12,
            '2xl' => 12,
        ];
    }
    
} 
