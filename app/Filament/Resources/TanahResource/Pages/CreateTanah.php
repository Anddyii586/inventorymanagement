<?php

namespace App\Filament\Resources\TanahResource\Pages;

use App\Filament\Resources\TanahResource;
use App\Filament\Resources\Pages\BaseCreateAssetRecord;

class CreateTanah extends BaseCreateAssetRecord
{
    protected static string $resource = TanahResource::class;

    protected function getRedirectUrl(): string
    {
        return $this->getResource()::getUrl('index');
    }

    protected function getCreatedNotificationTitle(): ?string
    {
        return 'Data tanah berhasil dibuat';
    }
}
