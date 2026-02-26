<?php

namespace App\Filament\Resources\AsetTetapLainnyaResource\Pages;

use App\Filament\Resources\AsetTetapLainnyaResource;
use Filament\Actions;
use Filament\Resources\Pages\ViewRecord;

class ViewAsetTetapLainnya extends ViewRecord
{
    protected static string $resource = AsetTetapLainnyaResource::class;

    protected function getHeaderActions(): array
    {
        return [
            Actions\EditAction::make(),
        ];
    }
}
