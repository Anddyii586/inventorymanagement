<?php

namespace App\Filament\Resources\Lokasi\DirektoratResource\Pages;

use App\Filament\Resources\Lokasi\DirektoratResource;
use Filament\Actions;
use Filament\Resources\Pages\ManageRecords;

class ManageDirektorat extends ManageRecords
{
    protected static string $resource = DirektoratResource::class;

    protected function getHeaderActions(): array
    {
        return [
            Actions\CreateAction::make(),
        ];
    }
}
