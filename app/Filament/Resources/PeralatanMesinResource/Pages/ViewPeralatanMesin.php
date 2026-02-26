<?php

namespace App\Filament\Resources\PeralatanMesinResource\Pages;

use App\Filament\Resources\PeralatanMesinResource;
use Filament\Actions;
use Filament\Resources\Pages\ViewRecord;

class ViewPeralatanMesin extends ViewRecord
{
    protected static string $resource = PeralatanMesinResource::class;

    protected function getHeaderActions(): array
    {
        return [
            Actions\EditAction::make(),
        ];
    }
}
