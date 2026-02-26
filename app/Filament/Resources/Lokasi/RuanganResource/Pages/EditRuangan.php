<?php

namespace App\Filament\Resources\Lokasi\RuanganResource\Pages;

use App\Filament\Resources\Lokasi\RuanganResource;
use Filament\Actions;
use Filament\Resources\Pages\EditRecord;

class EditRuangan extends EditRecord
{
    protected static string $resource = RuanganResource::class;

    protected function getHeaderActions(): array
    {
        return [
            Actions\DeleteAction::make(),
        ];
    }
}
