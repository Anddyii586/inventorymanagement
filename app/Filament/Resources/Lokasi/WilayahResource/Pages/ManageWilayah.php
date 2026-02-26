<?php

namespace App\Filament\Resources\Lokasi\WilayahResource\Pages;

use App\Filament\Resources\Lokasi\WilayahResource;
use Filament\Actions;
use Filament\Resources\Pages\ManageRecords;

class ManageWilayah extends ManageRecords
{
    protected static string $resource = WilayahResource::class;

    protected function getHeaderActions(): array
    {
        return [
            Actions\CreateAction::make(),
        ];
    }
}
