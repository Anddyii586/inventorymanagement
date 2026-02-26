<?php

namespace App\Filament\Pages;

use Filament\Pages\Page;

use App\Filament\Widgets\AssetConditionTableWidget;

class KondisiAsetPage extends Page
{
    protected static ?string $navigationIcon = 'heroicon-o-chart-bar';
    
    protected static ?string $navigationLabel = 'Kondisi Aset';
    
    protected static ?string $title = 'Laporan Kondisi Aset';
    
    protected static ?string $slug = 'kondisi-aset';
    
    protected static ?string $navigationGroup = 'Pemeliharaan Aset';
    
    protected static ?int $navigationSort = 1;

    protected static string $view = 'filament.pages.kondisi-aset-page';

    // Disable header widgets since we use specific widgets method
    // actually Page uses getHeaderWidgets or getFooterWidgets or getVisibleWidgets
    // For a Page to display widgets, we typically override getHeaderWidgets or define them in the view.
    // However, the easiest way in Filament 3 (assuming v3) is similar to Dashboard.
    
    public function getHeaderWidgets(): array
    {
        return [

        ];
    }

    public function getFooterWidgets(): array
    {
        return [
            AssetConditionTableWidget::class,
        ];
    }
    
    public function getHeaderWidgetsColumns(): int | array
    {
         return 1;
    }

    public function getFooterWidgetsColumns(): int | array
    {
        return 1;
    }
}
