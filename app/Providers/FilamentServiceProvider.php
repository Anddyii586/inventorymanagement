<?php

namespace App\Providers;

use Illuminate\Support\ServiceProvider;
use Filament\Facades\Filament;
use Filament\Navigation\NavigationItem;

class FilamentServiceProvider extends ServiceProvider
{
    public function register(): void
    {
        //
    }

    public function boot(): void
    {
        Filament::serving(function () {
            try {
                Filament::registerNavigationItems([
                    NavigationItem::make('Koreksi Pencatatan Aset')
                        ->url(url('/koreksi'))
                        ->icon('heroicon-o-pencil')
                        ->sort(999),
                ]);
            } catch (\Throwable $e) {
                // don't break the app if Filament API differs; fallback silently
            }
        });
    }
}
