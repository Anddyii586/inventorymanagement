<?php

namespace App\Filament\Resources\GedungBangunanResource\Pages;

use App\Filament\Resources\GedungBangunanResource;
use Filament\Actions;
use Filament\Resources\Pages\ViewRecord;

class ViewGedungBangunan extends ViewRecord
{
    protected static string $resource = GedungBangunanResource::class;

    protected function getHeaderActions(): array
    {
        return [
            Actions\EditAction::make(),
        ];
    }
}
