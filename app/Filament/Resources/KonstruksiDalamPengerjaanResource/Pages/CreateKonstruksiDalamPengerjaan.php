<?php

namespace App\Filament\Resources\KonstruksiDalamPengerjaanResource\Pages;

use App\Filament\Resources\KonstruksiDalamPengerjaanResource;
use App\Filament\Resources\Pages\BaseCreateAssetRecord;

class CreateKonstruksiDalamPengerjaan extends BaseCreateAssetRecord
{
    protected static string $resource = KonstruksiDalamPengerjaanResource::class;

    protected function getRedirectUrl(): string
    {
        return $this->getResource()::getUrl('index');
    }

    protected function getCreatedNotificationTitle(): ?string
    {
        return 'Data konstruksi dalam pengerjaan berhasil dibuat';
    }
}