<?php

namespace App\Filament\Resources\Aset\GolonganResource\Pages;

use App\Filament\Resources\Aset\GolonganResource;
use Filament\Actions;
use Filament\Resources\Pages\ManageRecords;

class ManageGolongan extends ManageRecords
{
    protected static string $resource = GolonganResource::class;

    protected function getHeaderActions(): array
    {
        return [
            Actions\CreateAction::make(),
        ];
    }
}
