<?php

namespace App\Filament\Resources\Aset\SubSubKelompokResource\Pages;

use App\Filament\Resources\Aset\SubSubKelompokResource;
use Filament\Actions;
use Filament\Resources\Pages\ManageRecords;

class ManageSubSubKelompoks extends ManageRecords
{
    protected static string $resource = SubSubKelompokResource::class;

    protected function getHeaderActions(): array
    {
        return [
            Actions\CreateAction::make(),
        ];
    }
}
