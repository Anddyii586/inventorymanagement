<?php

namespace App\Filament\Widgets;

use Filament\Widgets\Widget;
use Filament\Actions\Action;

class ManualBookDownloadWidget extends Widget
{
    protected static string $view = 'filament.widgets.manual-book-download-widget';

    protected static ?int $sort = 1;

    protected int | string | array $columnSpan = [
        'default' => 1,
        'md' => 4,
        'lg' => 12,
    ];

    public function getDownloadAction(): Action
    {
        return Action::make('download')
            ->label('Download Manual Book')
            ->icon('heroicon-o-document-arrow-down')
            ->color('primary')
            ->size('lg')
            ->url('/downloads/Manual Book SatSet PTAMGM.pdf')
            ->openUrlInNewTab()
            ->extraAttributes([
                'class' => 'sm:w-auto w-full flex justify-center',
            ]);
    }
}
