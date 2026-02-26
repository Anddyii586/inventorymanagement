<?php

namespace App\Filament\Resources\Lokasi\SubBidangResource\Pages;

use App\Filament\Resources\Lokasi\SubBidangResource;
use Filament\Actions;
use Filament\Resources\Pages\ManageRecords;

class ManageSubBidang extends ManageRecords
{
    protected static string $resource = SubBidangResource::class;

    protected function getHeaderActions(): array
    {
        return [
            Actions\CreateAction::make(),
        ];
    }
}
