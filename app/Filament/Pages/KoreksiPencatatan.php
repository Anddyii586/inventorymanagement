<?php

namespace App\Filament\Pages;

use Filament\Pages\Page;

class KoreksiPencatatan extends Page
{
    protected static ?string $navigationLabel = 'Koreksi Pencatatan';
    protected static ?string $navigationIcon = 'heroicon-o-pencil';
    protected static ?int $navigationSort = 900;
    protected static string $view = 'filament.pages.koreksi-pencatatan';
}
