<?php

namespace App\Filament\Resources\JaringanResource\Pages;

use App\Filament\Resources\JaringanResource;
use App\Filament\Resources\Pages\BaseCreateAssetRecord;

class CreateJaringan extends BaseCreateAssetRecord
{
    protected static string $resource = JaringanResource::class;

    protected function getRedirectUrl(): string
    {
        return $this->getResource()::getUrl('index');
    }

    protected function getCreatedNotificationTitle(): ?string
    {
        return 'Data jaringan berhasil dibuat';
    }
}
