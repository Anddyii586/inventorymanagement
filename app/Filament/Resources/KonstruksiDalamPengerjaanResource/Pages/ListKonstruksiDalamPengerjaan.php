<?php

namespace App\Filament\Resources\KonstruksiDalamPengerjaanResource\Pages;

use App\Filament\Resources\KonstruksiDalamPengerjaanResource;
use Filament\Actions;
use Filament\Resources\Pages\ListRecords;

class ListKonstruksiDalamPengerjaan extends ListRecords
{
    protected static string $resource = KonstruksiDalamPengerjaanResource::class;

    protected function getHeaderActions(): array
    {
        return [
            Actions\CreateAction::make(),
        ];
    }
}