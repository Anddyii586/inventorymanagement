<?php

namespace App\Filament\Resources\PeralatanMesinResource\Pages;

use App\Filament\Resources\PeralatanMesinResource;
use App\Filament\Resources\Pages\BaseCreateAssetRecord;

class CreatePeralatanMesin extends BaseCreateAssetRecord
{
    protected static string $resource = PeralatanMesinResource::class;

    protected function getRedirectUrl(): string
    {
        return $this->getResource()::getUrl('index');
    }

    protected function getCreatedNotificationTitle(): ?string
    {
        return 'Data peralatan dan mesin berhasil dibuat';
    }
}
