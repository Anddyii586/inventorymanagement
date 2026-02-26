<?php

namespace App\Filament\Resources\Lokasi\RuanganResource\Pages;

use App\Filament\Resources\Lokasi\RuanganResource;
use Filament\Actions;
use Filament\Resources\Pages\ListRecords;

class ListRuangan extends ListRecords
{
    protected static string $resource = RuanganResource::class;

    protected function getHeaderActions(): array
    {
        return [
            Actions\CreateAction::make(),
        ];
    }
}
