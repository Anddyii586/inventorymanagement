<?php

namespace App\Filament\Resources\JaringanResource\Pages;

use App\Filament\Resources\JaringanResource;
use Filament\Actions;
use Filament\Resources\Pages\EditRecord;

class EditJaringan extends EditRecord
{
    protected static string $resource = JaringanResource::class;

    protected function getHeaderActions(): array
    {
        return [
            Actions\ViewAction::make(),
            Actions\DeleteAction::make(),
        ];
    }

    protected function getRedirectUrl(): string
    {
        return $this->getResource()::getUrl('index');
    }

    protected function getSavedNotificationTitle(): ?string
    {
        return 'Data jalan, irigasi, dan jaringan berhasil diperbarui';
    }
}
