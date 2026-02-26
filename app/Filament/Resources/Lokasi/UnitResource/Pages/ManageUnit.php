<?php

namespace App\Filament\Resources\Lokasi\UnitResource\Pages;

use App\Filament\Resources\Lokasi\UnitResource;
use Filament\Actions;
use Filament\Resources\Pages\ManageRecords;

class ManageUnit extends ManageRecords
{
    protected static string $resource = UnitResource::class;

    protected function getHeaderActions(): array
    {
        return [
            Actions\CreateAction::make(),
        ];
    }
}
