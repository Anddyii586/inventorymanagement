<?php

namespace App\Filament\Resources\GedungBangunanResource\Pages;

use App\Filament\Resources\GedungBangunanResource;
use App\Filament\Resources\Pages\BaseCreateAssetRecord;

class CreateGedungBangunan extends BaseCreateAssetRecord
{
    protected static string $resource = GedungBangunanResource::class;

    protected function getRedirectUrl(): string
    {
        return $this->getResource()::getUrl('index');
    }

    protected function getCreatedNotificationTitle(): ?string
    {
        return 'Data gedung dan bangunan berhasil dibuat';
    }
}
