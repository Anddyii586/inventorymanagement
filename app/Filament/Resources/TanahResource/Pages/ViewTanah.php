<?php

namespace App\Filament\Resources\TanahResource\Pages;

use App\Filament\Resources\TanahResource;
use Filament\Actions;
use Filament\Resources\Pages\ViewRecord;

class ViewTanah extends ViewRecord
{
    protected static string $resource = TanahResource::class;

    protected function getHeaderActions(): array
    {
        return [
            Actions\EditAction::make(),
        ];
    }
}
