<?php

namespace App\Filament\Resources\Aset\KelompokResource\Pages;

use App\Filament\Resources\Aset\KelompokResource;
use Filament\Actions;
use Filament\Resources\Pages\ManageRecords;

class ManageKelompoks extends ManageRecords
{
    protected static string $resource = KelompokResource::class;

    protected function getHeaderActions(): array
    {
        return [
            Actions\CreateAction::make(),
        ];
    }
}
