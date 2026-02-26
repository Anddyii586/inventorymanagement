<?php

namespace App\Filament\Resources\KonstruksiDalamPengerjaanResource\Pages;

use App\Filament\Resources\KonstruksiDalamPengerjaanResource;
use Filament\Actions;
use Filament\Resources\Pages\EditRecord;

class EditKonstruksiDalamPengerjaan extends EditRecord
{
    protected static string $resource = KonstruksiDalamPengerjaanResource::class;

    protected function getHeaderActions(): array
    {
        return [
            Actions\DeleteAction::make(),
        ];
    }

    protected function getRedirectUrl(): string
    {
        return $this->getResource()::getUrl('index');
    }

    protected function getSavedNotificationTitle(): ?string
    {
        return 'Data konstruksi dalam pengerjaan berhasil diperbarui';
    }
}