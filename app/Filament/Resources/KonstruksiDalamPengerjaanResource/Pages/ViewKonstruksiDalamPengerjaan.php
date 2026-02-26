<?php

namespace App\Filament\Resources\KonstruksiDalamPengerjaanResource\Pages;

use App\Filament\Resources\KonstruksiDalamPengerjaanResource;
use Filament\Actions;
use Filament\Resources\Pages\ViewRecord;

class ViewKonstruksiDalamPengerjaan extends ViewRecord
{
    protected static string $resource = KonstruksiDalamPengerjaanResource::class;

    protected function getHeaderActions(): array
    {
        return [
            Actions\EditAction::make(),
        ];
    }
}