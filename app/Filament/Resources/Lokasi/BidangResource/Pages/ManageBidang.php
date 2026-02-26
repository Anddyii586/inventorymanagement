<?php

namespace App\Filament\Resources\Lokasi\BidangResource\Pages;

use App\Filament\Resources\Lokasi\BidangResource;
use Filament\Actions;
use Filament\Resources\Pages\ManageRecords;

class ManageBidang extends ManageRecords
{
    protected static string $resource = BidangResource::class;

    protected function getHeaderActions(): array
    {
        return [
            Actions\CreateAction::make(),
        ];
    }
}
