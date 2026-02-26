<?php

namespace App\Filament\Resources\AsetTetapLainnyaResource\Pages;

use App\Filament\Resources\AsetTetapLainnyaResource;
use App\Filament\Resources\Pages\BaseCreateAssetRecord;

class CreateAsetTetapLainnya extends BaseCreateAssetRecord
{
    protected static string $resource = AsetTetapLainnyaResource::class;

    protected function getRedirectUrl(): string
    {
        return $this->getResource()::getUrl('index');
    }

    protected function getCreatedNotificationTitle(): ?string
    {
        return 'Data aset tetap lainnya berhasil dibuat';
    }
}
