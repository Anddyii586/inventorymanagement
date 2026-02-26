<?php

namespace App\Filament\Resources\AsetTetapLainnyaResource\Pages;

use App\Filament\Resources\AsetTetapLainnyaResource;
use Filament\Actions;
use Filament\Resources\Pages\ListRecords;

class ListAsetTetapLainnya extends ListRecords
{
    protected static string $resource = AsetTetapLainnyaResource::class;

    protected function getHeaderActions(): array
    {
        return [
            Actions\CreateAction::make(),
        ];
    }
}
