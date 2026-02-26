<?php

namespace App\Filament\Resources\Aset\SubKelompokResource\Pages;

use App\Filament\Resources\Aset\SubKelompokResource;
use Filament\Actions;
use Filament\Resources\Pages\ManageRecords;

class ManageSubKelompoks extends ManageRecords
{
    protected static string $resource = SubKelompokResource::class;

    protected function getHeaderActions(): array
    {
        return [
            Actions\CreateAction::make(),
        ];
    }
}
